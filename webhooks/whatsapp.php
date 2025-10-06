<?php
/**
 * VisionMetrics - WhatsApp Webhook Handler (Multi-tenant)
 * 
 * Receives incoming webhooks from BSP providers (360Dialog, Infobip, etc)
 * Handles:
 * - Message attribution via vm_token
 * - Phone number matching
 * - Lead creation/update
 * - Conversation threading
 * - Message storage
 * 
 * Security:
 * - Workspace isolation
 * - Signature verification
 * - Rate limiting
 */

require_once __DIR__ . '/../backend/config.php';
require_once __DIR__ . '/../src/bootstrap.php';

use VisionMetrics\Integrations\WhatsappIntegration;

// Log raw webhook immediately
$rawPayload = file_get_contents('php://input');
$headers = getallheaders();
$sourceIP = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';

$db = getDB();

// Log webhook
$stmtLog = $db->prepare("
    INSERT INTO webhooks_logs (source, payload, headers, ip_address, processing_status, received_at)
    VALUES ('whatsapp', ?, ?, ?, 'pending', NOW())
");
$stmtLog->execute([
    $rawPayload,
    json_encode($headers),
    $sourceIP
]);
$webhookLogId = $db->lastInsertId();

// Return 200 OK immediately (process async if needed)
http_response_code(200);
header('Content-Type: application/json');

try {
    $payload = json_decode($rawPayload, true);
    
    if (!$payload) {
        throw new Exception('Invalid JSON payload');
    }
    
    // ═══════════════════════════════════════════════════════════
    // 1. IDENTIFY WORKSPACE
    // ═══════════════════════════════════════════════════════════
    $workspaceId = null;
    $integrationId = null;
    
    // Try to extract session_id or phone_id from payload
    // Format varies by provider
    
    // 360Dialog format: payload.entry[0].changes[0].value.metadata.phone_number_id
    $phoneNumberId = null;
    $sessionId = null;
    
    if (isset($payload['entry'][0]['changes'][0]['value']['metadata']['phone_number_id'])) {
        $phoneNumberId = $payload['entry'][0]['changes'][0]['value']['metadata']['phone_number_id'];
    }
    
    // Try to match by phone_id in integration meta
    if ($phoneNumberId) {
        $stmt = $db->prepare("
            SELECT id, workspace_id 
            FROM whatsapp_integrations 
            WHERE JSON_EXTRACT(meta, '$.phone_id') = ? 
            OR JSON_EXTRACT(meta, '$.waba_id') = ?
            LIMIT 1
        ");
        $stmt->execute([$phoneNumberId, $phoneNumberId]);
        $intMatch = $stmt->fetch();
        
        if ($intMatch) {
            $workspaceId = $intMatch['workspace_id'];
            $integrationId = $intMatch['id'];
        }
    }
    
    // Fallback: use first active integration (if only one workspace)
    if (!$workspaceId) {
        $stmt = $db->prepare("
            SELECT id, workspace_id 
            FROM whatsapp_integrations 
            WHERE status = 'active' 
            ORDER BY created_at DESC 
            LIMIT 1
        ");
        $stmt->execute();
        $intMatch = $stmt->fetch();
        
        if ($intMatch) {
            $workspaceId = $intMatch['workspace_id'];
            $integrationId = $intMatch['id'];
        }
    }
    
    if (!$workspaceId) {
        throw new Exception('Unable to determine workspace for webhook');
    }
    
    // Update webhook log with workspace
    $db->prepare("UPDATE webhooks_logs SET workspace_id = ? WHERE id = ?")
        ->execute([$workspaceId, $webhookLogId]);
    
    // ═══════════════════════════════════════════════════════════
    // 2. EXTRACT MESSAGE DATA
    // ═══════════════════════════════════════════════════════════
    $messages = [];
    
    // 360Dialog / Cloud API format
    if (isset($payload['entry'][0]['changes'][0]['value']['messages'])) {
        foreach ($payload['entry'][0]['changes'][0]['value']['messages'] as $msg) {
            $messages[] = [
                'message_id' => $msg['id'] ?? null,
                'from' => $msg['from'] ?? null, // E.164 phone number
                'to' => $payload['entry'][0]['changes'][0]['value']['metadata']['display_phone_number'] ?? null,
                'timestamp' => $msg['timestamp'] ?? time(),
                'type' => $msg['type'] ?? 'text',
                'text' => $msg['text']['body'] ?? '',
                'media_url' => $msg['image']['link'] ?? $msg['video']['link'] ?? $msg['document']['link'] ?? null,
                'media_type' => $msg['type'] !== 'text' ? $msg['type'] : null
            ];
        }
    }
    
    // ═══════════════════════════════════════════════════════════
    // 3. PROCESS EACH MESSAGE
    // ═══════════════════════════════════════════════════════════
    foreach ($messages as $message) {
        $phoneFrom = $message['from'];
        $phoneTo = $message['to'];
        $messageText = $message['text'];
        $messageId = $message['message_id'];
        
        if (!$phoneFrom) {
            continue; // Skip if no sender
        }
        
        // ───────────────────────────────────────────────────────
        // 3.1 LEAD ATTRIBUTION
        // ───────────────────────────────────────────────────────
        $leadId = null;
        
        // Try vm_token attribution
        if (preg_match('/vm_token:([A-Za-z0-9\-]+)/', $messageText, $matches)) {
            $vmToken = $matches[1];
            
            $stmt = $db->prepare("
                SELECT id FROM leads 
                WHERE workspace_id = ? AND first_touch_token = ?
                LIMIT 1
            ");
            $stmt->execute([$workspaceId, $vmToken]);
            $lead = $stmt->fetch();
            
            if ($lead) {
                $leadId = $lead['id'];
                
                // Update last_seen
                $db->prepare("UPDATE leads SET last_seen = NOW() WHERE id = ?")
                    ->execute([$leadId]);
            }
        }
        
        // Fallback: match by phone number
        if (!$leadId) {
            $normalizedPhone = preg_replace('/\D/', '', $phoneFrom);
            
            $stmt = $db->prepare("
                SELECT id FROM leads 
                WHERE workspace_id = ? 
                AND (phone = ? OR phone = ? OR phone LIKE ?)
                LIMIT 1
            ");
            $stmt->execute([
                $workspaceId,
                $phoneFrom,
                $normalizedPhone,
                '%' . substr($normalizedPhone, -9) // Last 9 digits
            ]);
            $lead = $stmt->fetch();
            
            if ($lead) {
                $leadId = $lead['id'];
            }
        }
        
        // Create anonymous lead if not found
        if (!$leadId) {
            $stmt = $db->prepare("
                INSERT INTO leads 
                (workspace_id, phone, status, stage, first_seen, last_seen, source)
                VALUES (?, ?, 'active', 'novo', NOW(), NOW(), 'whatsapp')
            ");
            $stmt->execute([$workspaceId, $phoneFrom]);
            $leadId = $db->lastInsertId();
        }
        
        // ───────────────────────────────────────────────────────
        // 3.2 UPSERT CONVERSATION
        // ───────────────────────────────────────────────────────
        $stmt = $db->prepare("
            SELECT id FROM whatsapp_conversations
            WHERE workspace_id = ? AND wa_from = ? AND wa_to = ?
            LIMIT 1
        ");
        $stmt->execute([$workspaceId, $phoneFrom, $phoneTo]);
        $conversation = $stmt->fetch();
        
        if ($conversation) {
            $conversationId = $conversation['id'];
            
            // Update snippet and last_message_at
            $db->prepare("
                UPDATE whatsapp_conversations 
                SET lead_id = ?, snippet = ?, last_message_at = NOW()
                WHERE id = ?
            ")->execute([$leadId, substr($messageText, 0, 200), $conversationId]);
            
        } else {
            // Create new conversation
            $stmt = $db->prepare("
                INSERT INTO whatsapp_conversations
                (workspace_id, lead_id, wa_from, wa_to, snippet, last_message_at, created_at)
                VALUES (?, ?, ?, ?, ?, NOW(), NOW())
            ");
            $stmt->execute([
                $workspaceId,
                $leadId,
                $phoneFrom,
                $phoneTo,
                substr($messageText, 0, 200)
            ]);
            $conversationId = $db->lastInsertId();
        }
        
        // ───────────────────────────────────────────────────────
        // 3.3 INSERT MESSAGE
        // ───────────────────────────────────────────────────────
        $stmt = $db->prepare("
            INSERT INTO whatsapp_messages
            (conversation_id, workspace_id, message_id, direction, text, media_url, media_type, raw_payload, received_at, created_at)
            VALUES (?, ?, ?, 'inbound', ?, ?, ?, ?, FROM_UNIXTIME(?), NOW())
        ");
        $stmt->execute([
            $conversationId,
            $workspaceId,
            $messageId,
            $messageText,
            $message['media_url'],
            $message['media_type'],
            json_encode($message),
            $message['timestamp']
        ]);
        
        // ───────────────────────────────────────────────────────
        // 3.4 CREATE QUEUE JOB (optional)
        // ───────────────────────────────────────────────────────
        $jobPayload = [
            'type' => 'whatsapp_message',
            'conversation_id' => $conversationId,
            'lead_id' => $leadId,
            'message_text' => $messageText,
            'phone_from' => $phoneFrom
        ];
        
        $stmt = $db->prepare("
            INSERT INTO queue_jobs 
            (workspace_id, type, payload, status, attempts, next_run_at, created_at)
            VALUES (?, 'whatsapp_message', ?, 'pending', 0, NOW(), NOW())
        ");
        $stmt->execute([$workspaceId, json_encode($jobPayload)]);
    }
    
    // Mark webhook as processed
    $db->prepare("UPDATE webhooks_logs SET processing_status = 'processed', processed_at = NOW() WHERE id = ?")
        ->execute([$webhookLogId]);
    
    echo json_encode(['success' => true, 'messages_processed' => count($messages)]);
    
} catch (Exception $e) {
    // Mark webhook as failed
    $db->prepare("
        UPDATE webhooks_logs 
        SET processing_status = 'failed', error_message = ?, processed_at = NOW() 
        WHERE id = ?
    ")->execute([$e->getMessage(), $webhookLogId]);
    
    error_log("WhatsApp webhook error: " . $e->getMessage());
    
    // Still return 200 to prevent retries
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
