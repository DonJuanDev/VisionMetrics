<?php
/**
 * MercadoPago - Webhook Handler (Hardened with Idempotency)
 * Recebe notificações de pagamentos com proteção contra duplicatas
 * 
 * Configurar em: https://www.mercadopago.com.br/developers/panel/webhooks
 * URL: https://yourdomain.com/mercadopago/webhook.php
 * 
 * FEATURES:
 * - Idempotent processing (prevents duplicate conversions)
 * - Lead mapping via cookie token or session
 * - Conversion record creation with attribution
 * - Queue job creation for analytics
 * - Subscription status update
 * 
 * TESTE LOCAL (ngrok):
 * 1. ngrok http 3000
 * 2. Configurar URL: https://xxxxx.ngrok.io/mercadopago/webhook.php
 * 3. Fazer pagamento de teste no sandbox
 */

require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/adapters/MercadoPagoAdapter.php';

use VisionMetrics\Adapters\MercadoPagoAdapter;

// ═══════════════════════════════════════════════════════════
// RECEIVE WEBHOOK PAYLOAD
// ═══════════════════════════════════════════════════════════
$body = file_get_contents('php://input');
$payload = json_decode($body, true);
$headers = getallheaders();

logMessage('INFO', 'MercadoPago webhook received', [
    'action' => $payload['action'] ?? 'unknown',
    'type' => $payload['type'] ?? 'unknown',
    'data_id' => $payload['data']['id'] ?? null
]);

$db = getDB();

// ═══════════════════════════════════════════════════════════
// LOG RAW WEBHOOK (for debugging and audit)
// ═══════════════════════════════════════════════════════════
$stmt = $db->prepare("
    INSERT INTO webhooks_logs (source, event_type, payload, received_at)
    VALUES ('mercadopago', ?, ?, NOW())
");
$stmt->execute([
    $payload['action'] ?? 'unknown',
    $body
]);

$webhookLogId = $db->lastInsertId();

// ═══════════════════════════════════════════════════════════
// VALIDATE WEBHOOK SIGNATURE (optional but recommended)
// ═══════════════════════════════════════════════════════════
$mercadopago = new MercadoPagoAdapter();

if (!$mercadopago->validateWebhook($headers, $body)) {
    logMessage('WARNING', 'Invalid MercadoPago webhook signature', [
        'webhook_log_id' => $webhookLogId
    ]);
    // In production, you might want to reject invalid signatures
    // For now, we'll log and continue
}

// ═══════════════════════════════════════════════════════════
// EXTRACT EVENT DATA
// ═══════════════════════════════════════════════════════════
$action = $payload['action'] ?? '';
$type = $payload['type'] ?? '';
$dataId = $payload['data']['id'] ?? null;

// Only process payment events
if ($type !== 'payment' || empty($dataId)) {
    logMessage('INFO', 'Non-payment event, skipping', [
        'type' => $type,
        'action' => $action
    ]);
    
    http_response_code(200);
    echo json_encode(['ok' => true, 'message' => 'Event received but not processed']);
    exit;
}

$paymentId = $dataId;

// ═══════════════════════════════════════════════════════════
// CHECK IDEMPOTENCY (prevent duplicate processing)
// ═══════════════════════════════════════════════════════════
$stmt = $db->prepare("
    SELECT id FROM conversions
    WHERE provider = 'mercadopago' 
    AND provider_payment_id = ?
    LIMIT 1
");
$stmt->execute([$paymentId]);
$existingConversion = $stmt->fetch();

if ($existingConversion) {
    logMessage('INFO', 'Payment already processed (idempotent)', [
        'payment_id' => $paymentId,
        'conversion_id' => $existingConversion['id']
    ]);
    
    http_response_code(200);
    echo json_encode([
        'ok' => true, 
        'message' => 'Payment already processed',
        'conversion_id' => $existingConversion['id']
    ]);
    exit;
}

// ═══════════════════════════════════════════════════════════
// FETCH PAYMENT DETAILS FROM MERCADOPAGO API
// ═══════════════════════════════════════════════════════════
$paymentInfo = $mercadopago->getPayment($paymentId);

if (!$paymentInfo['success']) {
    logMessage('ERROR', 'Failed to fetch payment info from MercadoPago', [
        'payment_id' => $paymentId,
        'error' => $paymentInfo['error'] ?? 'Unknown error'
    ]);
    
    http_response_code(200);
    echo json_encode(['ok' => true, 'message' => 'Payment info fetch failed']);
    exit;
}

$paymentStatus = $paymentInfo['status'] ?? 'unknown';
$paymentAmount = $paymentInfo['amount'] ?? 0;
$payerEmail = $paymentInfo['payer_email'] ?? null;

logMessage('INFO', 'Payment info fetched', [
    'payment_id' => $paymentId,
    'status' => $paymentStatus,
    'amount' => $paymentAmount
]);

// Only process approved payments
if ($paymentStatus !== 'approved') {
    logMessage('INFO', 'Payment not approved, skipping conversion', [
        'payment_id' => $paymentId,
        'status' => $paymentStatus
    ]);
    
    http_response_code(200);
    echo json_encode(['ok' => true, 'message' => 'Payment status: ' . $paymentStatus]);
    exit;
}

// ═══════════════════════════════════════════════════════════
// FIND ASSOCIATED SUBSCRIPTION
// ═══════════════════════════════════════════════════════════
$stmt = $db->prepare("
    SELECT * FROM subscriptions
    WHERE mercadopago_preference_id IS NOT NULL
    AND mercadopago_payment_id IS NULL
    ORDER BY created_at DESC
    LIMIT 1
");
$stmt->execute();
$subscription = $stmt->fetch();

$subscriptionId = null;
$workspaceId = null;
$leadId = null;

if ($subscription) {
    $subscriptionId = $subscription['id'];
    $workspaceId = $subscription['workspace_id'];
    
    // Update subscription with payment info
    $stmt = $db->prepare("
        UPDATE subscriptions
        SET status = 'active',
            mercadopago_payment_id = ?,
            current_period_start = NOW(),
            current_period_end = DATE_ADD(NOW(), INTERVAL 1 MONTH),
            updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$paymentId, $subscriptionId]);
    
    logMessage('INFO', 'Subscription activated', [
        'subscription_id' => $subscriptionId,
        'payment_id' => $paymentId
    ]);
}

// If no subscription found, try to get workspace from email
if (!$workspaceId && $payerEmail) {
    $stmt = $db->prepare("
        SELECT w.id FROM workspaces w
        JOIN users u ON w.owner_id = u.id
        WHERE u.email = ?
        LIMIT 1
    ");
    $stmt->execute([$payerEmail]);
    $workspace = $stmt->fetch();
    
    if ($workspace) {
        $workspaceId = $workspace['id'];
    }
}

// Fallback to first workspace
if (!$workspaceId) {
    $stmt = $db->query("SELECT id FROM workspaces ORDER BY id ASC LIMIT 1");
    $workspace = $stmt->fetch();
    $workspaceId = $workspace['id'] ?? 1;
    
    logMessage('WARNING', 'No workspace mapping found, using default', [
        'workspace_id' => $workspaceId
    ]);
}

// ═══════════════════════════════════════════════════════════
// TRY TO FIND LEAD (by email or cookie token from metadata)
// ═══════════════════════════════════════════════════════════
// Check if payment metadata contains cookie_token or lead_id
$paymentMetadata = $paymentInfo['metadata'] ?? [];
$cookieToken = $paymentMetadata['cookie_token'] ?? $paymentMetadata['vm_first_touch'] ?? null;
$leadIdFromMeta = $paymentMetadata['lead_id'] ?? null;

// Strategy 1: Lead ID from metadata
if ($leadIdFromMeta) {
    $stmt = $db->prepare("SELECT id FROM leads WHERE id = ? AND workspace_id = ? LIMIT 1");
    $stmt->execute([$leadIdFromMeta, $workspaceId]);
    $lead = $stmt->fetch();
    if ($lead) {
        $leadId = $lead['id'];
    }
}

// Strategy 2: Cookie token
if (!$leadId && $cookieToken) {
    $stmt = $db->prepare("
        SELECT id FROM leads 
        WHERE workspace_id = ? AND first_touch_token = ?
        LIMIT 1
    ");
    $stmt->execute([$workspaceId, $cookieToken]);
    $lead = $stmt->fetch();
    
    if ($lead) {
        $leadId = $lead['id'];
    }
}

// Strategy 3: Email
if (!$leadId && $payerEmail) {
    $stmt = $db->prepare("
        SELECT id FROM leads
        WHERE workspace_id = ? AND email = ?
        LIMIT 1
    ");
    $stmt->execute([$workspaceId, $payerEmail]);
    $lead = $stmt->fetch();
    
    if ($lead) {
        $leadId = $lead['id'];
    } else {
        // Create lead from payer email
        $stmt = $db->prepare("
            INSERT INTO leads (
                workspace_id, email, first_touch_token,
                utm_source, stage, status, first_seen, last_seen
            ) VALUES (?, ?, ?, 'mercadopago', 'novo', 'converted', NOW(), NOW())
        ");
        $stmt->execute([$workspaceId, $payerEmail, $cookieToken]);
        $leadId = $db->lastInsertId();
        
        logMessage('INFO', 'Lead created from payment', [
            'lead_id' => $leadId,
            'email' => $payerEmail
        ]);
    }
}

// Update lead conversion status
if ($leadId) {
    $stmt = $db->prepare("
        UPDATE leads 
        SET status = 'converted', converted_at = NOW(), updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$leadId]);
}

// ═══════════════════════════════════════════════════════════
// CREATE CONVERSION RECORD (with idempotency key)
// ═══════════════════════════════════════════════════════════
$idempotencyKey = 'mp_' . $paymentId . '_' . $workspaceId;

try {
    $stmt = $db->prepare("
        INSERT INTO conversions (
            workspace_id, lead_id, subscription_id,
            conversion_type, provider, provider_payment_id,
            value, currency,
            idempotency_key, metadata,
            created_at
        ) VALUES (?, ?, ?, 'subscription', 'mercadopago', ?, ?, 'BRL', ?, ?, NOW())
    ");
    
    $stmt->execute([
        $workspaceId,
        $leadId,
        $subscriptionId,
        $paymentId,
        $paymentAmount,
        $idempotencyKey,
        json_encode([
            'payer_email' => $payerEmail,
            'payment_status' => $paymentStatus,
            'cookie_token' => $cookieToken
        ])
    ]);
    
    $conversionId = $db->lastInsertId();
    
    logMessage('INFO', 'Conversion created', [
        'conversion_id' => $conversionId,
        'workspace_id' => $workspaceId,
        'lead_id' => $leadId,
        'amount' => $paymentAmount
    ]);
    
} catch (PDOException $e) {
    // If unique constraint violation, it's already processed (idempotency)
    if (strpos($e->getMessage(), 'unique_idempotency') !== false) {
        logMessage('INFO', 'Conversion already exists (idempotency key)', [
            'idempotency_key' => $idempotencyKey
        ]);
        
        http_response_code(200);
        echo json_encode(['ok' => true, 'message' => 'Already processed']);
        exit;
    }
    
    // Re-throw if it's a different error
    throw $e;
}

// ═══════════════════════════════════════════════════════════
// CREATE QUEUE JOB FOR ANALYTICS (purchase event)
// ═══════════════════════════════════════════════════════════
$jobPayload = [
    'type' => 'conversion',
    'conversion_id' => $conversionId,
    'conversion_type' => 'subscription',
    'lead_id' => $leadId,
    'subscription_id' => $subscriptionId,
    'cookie_token' => $cookieToken,
    'client_id' => $cookieToken,
    'value' => $paymentAmount,
    'currency' => 'BRL',
    'transaction_id' => $paymentId,
    'email' => $payerEmail,
    'timestamp' => time()
];

$stmt = $db->prepare("
    INSERT INTO queue_jobs (
        workspace_id, type, payload,
        status, attempts, next_run_at, created_at
    ) VALUES (?, 'conversion', ?, 'pending', 0, NOW(), NOW())
");
$stmt->execute([$workspaceId, json_encode($jobPayload)]);

logMessage('INFO', 'Queue job created for conversion analytics', [
    'conversion_id' => $conversionId,
    'workspace_id' => $workspaceId
]);

// ═══════════════════════════════════════════════════════════
// RESPOND TO WEBHOOK
// ═══════════════════════════════════════════════════════════
http_response_code(200);
echo json_encode([
    'ok' => true,
    'conversion_id' => $conversionId,
    'payment_id' => $paymentId,
    'amount' => $paymentAmount
]);
