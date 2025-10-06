<?php
/**
 * WhatsApp Cloud API - Webhook Handler
 * Recebe mensagens e eventos do WhatsApp com lead attribution
 * 
 * CONFIGURAÇÃO:
 * 1. No Meta App Dashboard > WhatsApp > Configuration
 * 2. Webhook URL: https://yourdomain.com/webhooks/whatsapp.php
 * 3. Verify Token: valor de WHATSAPP_VERIFY_TOKEN no .env
 * 4. Subscribe to: messages
 * 
 * FEATURES:
 * - Lead attribution via vm_token (extracted from message)
 * - Fallback to phone number matching
 * - Queue job creation for analytics
 * - Conversation tracking with first-touch attribution
 */

require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/adapters/WhatsAppAdapter.php';

use VisionMetrics\Adapters\WhatsAppAdapter;

$whatsapp = new WhatsAppAdapter();

// ═══════════════════════════════════════════════════════════
// WEBHOOK VERIFICATION (GET)
// ═══════════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $mode = $_GET['hub_mode'] ?? '';
    $token = $_GET['hub_verify_token'] ?? '';
    $challenge = $_GET['hub_challenge'] ?? '';
    
    $result = $whatsapp->verifyWebhook($mode, $token, $challenge);
    
    if ($result) {
        echo $result;
        exit;
    }
    
    http_response_code(403);
    exit;
}

// ═══════════════════════════════════════════════════════════
// WEBHOOK EVENT (POST)
// ═══════════════════════════════════════════════════════════
$body = file_get_contents('php://input');
$payload = json_decode($body, true);

logMessage('INFO', 'WhatsApp webhook received', [
    'payload_size' => strlen($body)
]);

$db = getDB();

// Log webhook to database
$stmt = $db->prepare("
    INSERT INTO webhooks_logs (source, event_type, payload, received_at)
    VALUES ('whatsapp', 'message', ?, NOW())
");
$stmt->execute([$body]);

// ═══════════════════════════════════════════════════════════
// PROCESS INCOMING MESSAGE
// ═══════════════════════════════════════════════════════════
$message = $whatsapp->processIncomingMessage($payload);

if (!$message) {
    logMessage('INFO', 'No processable message in webhook');
    http_response_code(200);
    echo json_encode(['ok' => true]);
    exit;
}

$fromPhone = $message['from'];
$messageText = $message['text'] ?? '';
$messageId = $message['message_id'] ?? '';

// ═══════════════════════════════════════════════════════════
// EXTRACT VM_TOKEN FROM MESSAGE
// ═══════════════════════════════════════════════════════════
$vmToken = null;
$cleanMessage = $messageText;

// Pattern: vm_token:UUID or vm_token: UUID
if (preg_match('/vm_token:\s*([a-f0-9\-]{36})/i', $messageText, $matches)) {
    $vmToken = $matches[1];
    // Remove token from message for cleaner display
    $cleanMessage = preg_replace('/\s*vm_token:\s*[a-f0-9\-]{36}/i', '', $messageText);
    $cleanMessage = trim($cleanMessage);
    
    logMessage('INFO', 'vm_token extracted from message', [
        'token' => $vmToken,
        'from' => $fromPhone
    ]);
}

// ═══════════════════════════════════════════════════════════
// DETERMINE WORKSPACE (from phone_id or default)
// ═══════════════════════════════════════════════════════════
$workspaceId = null;
$whatsappNumberId = null;

// Try to find workspace from whatsapp_number_id in payload
$phoneNumberId = $payload['entry'][0]['changes'][0]['value']['metadata']['phone_number_id'] ?? null;

if ($phoneNumberId) {
    $stmt = $db->prepare("
        SELECT id, workspace_id 
        FROM whatsapp_numbers 
        WHERE phone_number = ? OR session_data LIKE ?
        LIMIT 1
    ");
    $stmt->execute([$phoneNumberId, "%{$phoneNumberId}%"]);
    $whatsappNumber = $stmt->fetch();
    
    if ($whatsappNumber) {
        $workspaceId = $whatsappNumber['workspace_id'];
        $whatsappNumberId = $whatsappNumber['id'];
    }
}

// Fallback to first workspace (for development)
if (!$workspaceId) {
    $stmt = $db->query("SELECT id FROM workspaces ORDER BY id ASC LIMIT 1");
    $workspace = $stmt->fetch();
    $workspaceId = $workspace['id'] ?? 1;
    
    logMessage('WARNING', 'No workspace mapping found, using default', [
        'workspace_id' => $workspaceId
    ]);
}

// ═══════════════════════════════════════════════════════════
// FIND OR CREATE LEAD
// ═══════════════════════════════════════════════════════════
$leadId = null;
$leadExists = false;

// Normalize phone number (remove non-digits)
$normalizedPhone = preg_replace('/\D/', '', $fromPhone);

// Strategy 1: Try to find lead by vm_token (highest priority)
if ($vmToken) {
    $stmt = $db->prepare("
        SELECT id FROM leads 
        WHERE workspace_id = ? AND first_touch_token = ?
        LIMIT 1
    ");
    $stmt->execute([$workspaceId, $vmToken]);
    $lead = $stmt->fetch();
    
    if ($lead) {
        $leadId = $lead['id'];
        $leadExists = true;
        
        // Update phone number if not set
        $stmt = $db->prepare("
            UPDATE leads 
            SET phone_number = ?, last_seen = NOW(), updated_at = NOW()
            WHERE id = ? AND (phone_number IS NULL OR phone_number = '')
        ");
        $stmt->execute([$normalizedPhone, $leadId]);
        
        logMessage('INFO', 'Lead found by vm_token', [
            'lead_id' => $leadId,
            'token' => $vmToken
        ]);
    }
}

// Strategy 2: Try to find lead by phone number
if (!$leadId) {
    $stmt = $db->prepare("
        SELECT id FROM leads 
        WHERE workspace_id = ? AND phone_number = ?
        LIMIT 1
    ");
    $stmt->execute([$workspaceId, $normalizedPhone]);
    $lead = $stmt->fetch();
    
    if ($lead) {
        $leadId = $lead['id'];
        $leadExists = true;
        
        // Update first_touch_token if we have one and it's not set
        if ($vmToken) {
            $stmt = $db->prepare("
                UPDATE leads 
                SET first_touch_token = ?, last_seen = NOW(), updated_at = NOW()
                WHERE id = ? AND (first_touch_token IS NULL OR first_touch_token = '')
            ");
            $stmt->execute([$vmToken, $leadId]);
        } else {
            // Just update last_seen
            $stmt = $db->prepare("UPDATE leads SET last_seen = NOW() WHERE id = ?");
            $stmt->execute([$leadId]);
        }
        
        logMessage('INFO', 'Lead found by phone number', [
            'lead_id' => $leadId,
            'phone' => $normalizedPhone
        ]);
    }
}

// Strategy 3: Create new lead
if (!$leadId) {
    $stmt = $db->prepare("
        INSERT INTO leads (
            workspace_id, phone_number, first_touch_token,
            utm_medium, stage, status, first_seen, last_seen
        ) VALUES (?, ?, ?, 'whatsapp', 'novo', 'active', NOW(), NOW())
    ");
    $stmt->execute([$workspaceId, $normalizedPhone, $vmToken]);
    $leadId = $db->lastInsertId();
    
    logMessage('INFO', 'New lead created from WhatsApp', [
        'lead_id' => $leadId,
        'phone' => $normalizedPhone,
        'vm_token' => $vmToken
    ]);
}

// ═══════════════════════════════════════════════════════════
// FIND OR CREATE CONVERSATION
// ═══════════════════════════════════════════════════════════
$stmt = $db->prepare("
    SELECT id FROM conversations
    WHERE workspace_id = ? 
    AND contact_phone = ?
    AND (whatsapp_number_id = ? OR whatsapp_number_id IS NULL)
    ORDER BY created_at DESC
    LIMIT 1
");
$stmt->execute([$workspaceId, $normalizedPhone, $whatsappNumberId]);
$conversation = $stmt->fetch();

if ($conversation) {
    $conversationId = $conversation['id'];
    
    // Update last_message_at and first_touch_token if available
    if ($vmToken) {
        $stmt = $db->prepare("
            UPDATE conversations 
            SET last_message_at = NOW(), 
                first_touch_token = COALESCE(first_touch_token, ?),
                lead_id = COALESCE(lead_id, ?)
            WHERE id = ?
        ");
        $stmt->execute([$vmToken, $leadId, $conversationId]);
    } else {
        $stmt = $db->prepare("
            UPDATE conversations 
            SET last_message_at = NOW(),
                lead_id = COALESCE(lead_id, ?)
            WHERE id = ?
        ");
        $stmt->execute([$leadId, $conversationId]);
    }
} else {
    // Create new conversation
    $stmt = $db->prepare("
        INSERT INTO conversations (
            workspace_id, whatsapp_number_id, lead_id,
            contact_phone, first_touch_token,
            status, last_message_at, created_at
        ) VALUES (?, ?, ?, ?, ?, 'active', NOW(), NOW())
    ");
    $stmt->execute([
        $workspaceId, 
        $whatsappNumberId, 
        $leadId,
        $normalizedPhone,
        $vmToken
    ]);
    $conversationId = $db->lastInsertId();
    
    logMessage('INFO', 'New conversation created', [
        'conversation_id' => $conversationId
    ]);
}

// ═══════════════════════════════════════════════════════════
// STORE MESSAGE
// ═══════════════════════════════════════════════════════════
$stmt = $db->prepare("
    INSERT INTO messages (
        conversation_id, whatsapp_message_id,
        direction, type, content,
        status, timestamp, metadata
    ) VALUES (?, ?, 'inbound', 'text', ?, 'delivered', NOW(), ?)
");
$stmt->execute([
    $conversationId,
    $messageId,
    $cleanMessage ?: $messageText,
    json_encode(['raw_message' => $messageText, 'vm_token' => $vmToken])
]);

// ═══════════════════════════════════════════════════════════
// CREATE EVENT RECORD
// ═══════════════════════════════════════════════════════════
$stmt = $db->prepare("
    INSERT INTO events (
        workspace_id, lead_id, event_type, 
        event_name, page_url, raw_data, created_at
    ) VALUES (?, ?, 'whatsapp_message', 'whatsapp_inbound', ?, ?, NOW())
");
$stmt->execute([
    $workspaceId,
    $leadId,
    'whatsapp://message/' . $messageId,
    json_encode($message)
]);

// ═══════════════════════════════════════════════════════════
// CREATE QUEUE JOB FOR ANALYTICS
// ═══════════════════════════════════════════════════════════
$jobPayload = [
    'type' => 'whatsapp_message',
    'message_id' => $messageId,
    'lead_id' => $leadId,
    'conversation_id' => $conversationId,
    'cookie_token' => $vmToken,
    'phone' => $normalizedPhone,
    'message_text' => substr($cleanMessage ?: $messageText, 0, 200),
    'timestamp' => time()
];

$stmt = $db->prepare("
    INSERT INTO queue_jobs (
        workspace_id, type, payload, 
        status, attempts, next_run_at, created_at
    ) VALUES (?, 'whatsapp_message', ?, 'pending', 0, NOW(), NOW())
");
$stmt->execute([$workspaceId, json_encode($jobPayload)]);

logMessage('INFO', 'WhatsApp message processed successfully', [
    'lead_id' => $leadId,
    'conversation_id' => $conversationId,
    'vm_token' => $vmToken,
    'phone' => $normalizedPhone
]);

// ═══════════════════════════════════════════════════════════
// RESPOND TO WEBHOOK
// ═══════════════════════════════════════════════════════════
http_response_code(200);
echo json_encode(['ok' => true]);
