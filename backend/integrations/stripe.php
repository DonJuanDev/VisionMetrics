<?php
/**
 * Stripe Integration Handler
 * Basic billing and subscription management
 */

namespace VisionMetrics\Integrations;

class StripeHandler {
    private $secretKey;
    private $publishableKey;
    private $webhookSecret;
    private $mode;
    
    public function __construct() {
        $this->secretKey = getenv('STRIPE_SECRET_KEY');
        $this->publishableKey = getenv('STRIPE_PUBLISHABLE_KEY');
        $this->webhookSecret = getenv('STRIPE_WEBHOOK_SECRET');
        $this->mode = getenv('STRIPE_MODE', 'test');
    }
    
    /**
     * Create a customer
     */
    public function createCustomer($email, $name, $metadata = []) {
        if ($this->mode === 'simulate') {
            return [
                'success' => true,
                'customer_id' => 'cus_simulated_' . uniqid(),
                'mode' => 'simulated'
            ];
        }
        
        try {
            $stripe = new \Stripe\StripeClient($this->secretKey);
            
            $customer = $stripe->customers->create([
                'email' => $email,
                'name' => $name,
                'metadata' => $metadata
            ]);
            
            return [
                'success' => true,
                'customer_id' => $customer->id
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Create a subscription
     */
    public function createSubscription($customerId, $priceId, $metadata = []) {
        if ($this->mode === 'simulate') {
            return [
                'success' => true,
                'subscription_id' => 'sub_simulated_' . uniqid(),
                'client_secret' => 'pi_simulated_' . uniqid(),
                'mode' => 'simulated'
            ];
        }
        
        try {
            $stripe = new \Stripe\StripeClient($this->secretKey);
            
            $subscription = $stripe->subscriptions->create([
                'customer' => $customerId,
                'items' => [['price' => $priceId]],
                'payment_behavior' => 'default_incomplete',
                'payment_settings' => ['save_default_payment_method' => 'on_subscription'],
                'expand' => ['latest_invoice.payment_intent'],
                'metadata' => $metadata
            ]);
            
            return [
                'success' => true,
                'subscription_id' => $subscription->id,
                'client_secret' => $subscription->latest_invoice->payment_intent->client_secret
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Create a payment intent
     */
    public function createPaymentIntent($amount, $currency = 'usd', $metadata = []) {
        if ($this->mode === 'simulate') {
            return [
                'success' => true,
                'client_secret' => 'pi_simulated_' . uniqid(),
                'mode' => 'simulated'
            ];
        }
        
        try {
            $stripe = new \Stripe\StripeClient($this->secretKey);
            
            $intent = $stripe->paymentIntents->create([
                'amount' => $amount * 100, // Convert to cents
                'currency' => $currency,
                'metadata' => $metadata
            ]);
            
            return [
                'success' => true,
                'client_secret' => $intent->client_secret
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Handle webhook events
     */
    public function handleWebhook($payload, $signature) {
        if ($this->mode === 'simulate') {
            return [
                'success' => true,
                'event_type' => 'simulated',
                'mode' => 'simulated'
            ];
        }
        
        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $signature,
                $this->webhookSecret
            );
            
            switch ($event->type) {
                case 'customer.subscription.created':
                case 'customer.subscription.updated':
                    $this->handleSubscriptionEvent($event->data->object);
                    break;
                    
                case 'customer.subscription.deleted':
                    $this->handleSubscriptionCancelled($event->data->object);
                    break;
                    
                case 'invoice.payment_succeeded':
                    $this->handlePaymentSucceeded($event->data->object);
                    break;
                    
                case 'invoice.payment_failed':
                    $this->handlePaymentFailed($event->data->object);
                    break;
            }
            
            return [
                'success' => true,
                'event_type' => $event->type
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    private function handleSubscriptionEvent($subscription) {
        // Update subscription in database
        $db = getDB();
        $stmt = $db->prepare("
            UPDATE subscriptions 
            SET status = ?, 
                current_period_start = FROM_UNIXTIME(?), 
                current_period_end = FROM_UNIXTIME(?),
                updated_at = NOW()
            WHERE stripe_subscription_id = ?
        ");
        $stmt->execute([
            $subscription->status,
            $subscription->current_period_start,
            $subscription->current_period_end,
            $subscription->id
        ]);
    }
    
    private function handleSubscriptionCancelled($subscription) {
        $db = getDB();
        $stmt = $db->prepare("
            UPDATE subscriptions 
            SET status = 'cancelled', 
                cancelled_at = NOW(),
                updated_at = NOW()
            WHERE stripe_subscription_id = ?
        ");
        $stmt->execute([$subscription->id]);
    }
    
    private function handlePaymentSucceeded($invoice) {
        // Log successful payment
        logMessage('INFO', 'Payment succeeded', [
            'invoice_id' => $invoice->id,
            'amount' => $invoice->amount_paid,
            'currency' => $invoice->currency
        ]);
    }
    
    private function handlePaymentFailed($invoice) {
        // Log failed payment
        logMessage('WARNING', 'Payment failed', [
            'invoice_id' => $invoice->id,
            'amount' => $invoice->amount_due,
            'currency' => $invoice->currency
        ]);
    }
    
    /**
     * Get pricing plans
     */
    public function getPricingPlans() {
        return [
            'free' => [
                'name' => 'Free',
                'price' => 0,
                'currency' => 'usd',
                'interval' => 'month',
                'features' => [
                    '1,000 events/month',
                    'Basic tracking',
                    'Email support'
                ]
            ],
            'starter' => [
                'name' => 'Starter',
                'price' => 29,
                'currency' => 'usd',
                'interval' => 'month',
                'features' => [
                    '10,000 events/month',
                    'Advanced tracking',
                    'Priority support',
                    'Custom domains'
                ]
            ],
            'pro' => [
                'name' => 'Pro',
                'price' => 99,
                'currency' => 'usd',
                'interval' => 'month',
                'features' => [
                    '100,000 events/month',
                    'All features',
                    '24/7 support',
                    'API access'
                ]
            ]
        ];
    }
}
