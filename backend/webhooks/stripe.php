<?php
require_once __DIR__ . '/../config.php';

// Stripe webhook handler
class StripeWebhookHandler {
    private $db;
    private $stripe;
    
    public function __construct() {
        $this->db = getDB();
        $this->stripe = new \VisionMetrics\Integrations\StripeHandler();
    }
    
    public function handleWebhook() {
        $payload = file_get_contents('php://input');
        $sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
        
        try {
            $event = $this->stripe->verifyWebhook($payload, $sigHeader);
            
            switch ($event['type']) {
                case 'checkout.session.completed':
                    $this->handleCheckoutCompleted($event['data']['object']);
                    break;
                    
                case 'invoice.payment_succeeded':
                    $this->handlePaymentSucceeded($event['data']['object']);
                    break;
                    
                case 'invoice.payment_failed':
                    $this->handlePaymentFailed($event['data']['object']);
                    break;
                    
                case 'customer.subscription.updated':
                    $this->handleSubscriptionUpdated($event['data']['object']);
                    break;
                    
                case 'customer.subscription.deleted':
                    $this->handleSubscriptionDeleted($event['data']['object']);
                    break;
                    
                default:
                    error_log("Unhandled Stripe event type: " . $event['type']);
            }
            
            http_response_code(200);
            echo json_encode(['status' => 'success']);
            
        } catch (Exception $e) {
            error_log("Stripe webhook error: " . $e->getMessage());
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    
    private function handleCheckoutCompleted($session) {
        $customerId = $session['customer'];
        $subscriptionId = $session['subscription'];
        $workspaceId = $session['metadata']['workspace_id'] ?? null;
        
        if (!$workspaceId) {
            throw new Exception("Missing workspace_id in session metadata");
        }
        
        // Get subscription details from Stripe
        $subscription = $this->stripe->getSubscription($subscriptionId);
        
        // Create subscription record
        $stmt = $this->db->prepare("
            INSERT INTO subscriptions (workspace_id, stripe_subscription_id, plan, status, created_at) 
            VALUES (?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE 
            stripe_subscription_id = VALUES(stripe_subscription_id),
            plan = VALUES(plan),
            status = VALUES(status)
        ");
        
        $plan = $this->extractPlanFromPriceId($subscription['items']['data'][0]['price']['id']);
        $stmt->execute([$workspaceId, $subscriptionId, $plan, 'active']);
        
        // Create payment record
        $this->createPaymentRecord($workspaceId, $subscriptionId, $session['amount_total'], $session['currency']);
    }
    
    private function handlePaymentSucceeded($invoice) {
        $subscriptionId = $invoice['subscription'];
        $amount = $invoice['amount_paid'];
        $currency = $invoice['currency'];
        
        // Get workspace from subscription
        $stmt = $this->db->prepare("
            SELECT workspace_id FROM subscriptions 
            WHERE stripe_subscription_id = ?
        ");
        $stmt->execute([$subscriptionId]);
        $subscription = $stmt->fetch();
        
        if ($subscription) {
            $this->createPaymentRecord($subscription['workspace_id'], $subscriptionId, $amount, $currency);
        }
    }
    
    private function handlePaymentFailed($invoice) {
        $subscriptionId = $invoice['subscription'];
        
        // Update subscription status
        $stmt = $this->db->prepare("
            UPDATE subscriptions 
            SET status = 'past_due' 
            WHERE stripe_subscription_id = ?
        ");
        $stmt->execute([$subscriptionId]);
    }
    
    private function handleSubscriptionUpdated($subscription) {
        $subscriptionId = $subscription['id'];
        $status = $subscription['status'];
        
        $stmt = $this->db->prepare("
            UPDATE subscriptions 
            SET status = ? 
            WHERE stripe_subscription_id = ?
        ");
        $stmt->execute([$status, $subscriptionId]);
    }
    
    private function handleSubscriptionDeleted($subscription) {
        $subscriptionId = $subscription['id'];
        
        $stmt = $this->db->prepare("
            UPDATE subscriptions 
            SET status = 'cancelled', cancelled_at = NOW() 
            WHERE stripe_subscription_id = ?
        ");
        $stmt->execute([$subscriptionId]);
    }
    
    private function createPaymentRecord($workspaceId, $subscriptionId, $amount, $currency) {
        $stmt = $this->db->prepare("
            INSERT INTO payments (workspace_id, subscription_id, amount, currency, status, stripe_payment_id, created_at) 
            VALUES (?, ?, ?, ?, 'succeeded', ?, NOW())
        ");
        $stmt->execute([$workspaceId, $subscriptionId, $amount / 100, $currency, uniqid('pay_')]);
    }
    
    private function extractPlanFromPriceId($priceId) {
        // Map Stripe price IDs to plan names
        $priceMap = [
            'price_basic' => 'basic',
            'price_pro' => 'pro',
            'price_enterprise' => 'enterprise'
        ];
        
        return $priceMap[$priceId] ?? 'basic';
    }
}

// Handle the webhook
$handler = new StripeWebhookHandler();
$handler->handleWebhook();