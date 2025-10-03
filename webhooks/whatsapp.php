<?php
/**
 * WhatsApp Cloud API - Webhook Handler
 * Recebe mensagens e eventos do WhatsApp
 * 
 * CONFIGURAÇÃO:
 * 1. No Meta App Dashboard > WhatsApp > Configuration
 * 2. Webhook URL: https://SEU-DOMINIO/webhooks/whatsapp.php
 * 3. Verify Token: valor de WHATSAPP_VERIFY_TOKEN no .env
 * 4. Subscribe to: messages
 * 
 * TESTE LOCAL (ngrok):
 * ngrok http 3000
 * Usar URL: https://xxxxx.ngrok.io/webhooks/whatsapp.php
 */

require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/adapters/WhatsAppAdapter.php';

use VisionMetrics\Adapters\WhatsAppAdapter;

$whatsapp = new WhatsAppAdapter();

// Webhook verification (GET)
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

// Webhook event (POST)
$body = file_get_contents('php://input');
$payload = json_decode($body, true);

logMessage('INFO', 'WhatsApp webhook received', [
    'payload_size' => strlen($body)
]);

// Log no banco
$db = getDB();
$stmt = $db->prepare("
    INSERT INTO webhooks_logs (source, event_type, payload, received_at)
    VALUES ('whatsapp', 'message', ?, NOW())
");
$stmt->execute([$body]);

// Processar mensagem
$message = $whatsapp->processIncomingMessage($payload);

if ($message) {
    // Encontrar ou criar lead
    $phoneNumber = $message['from'];
    
    $stmt = $db->prepare("SELECT id, workspace_id FROM leads WHERE phone_number = ? LIMIT 1");
    $stmt->execute([$phoneNumber]);
    $lead = $stmt->fetch();
    
    if (!$lead) {
        // Criar novo lead
        // TODO: Associar ao workspace correto (pode usar phone_id mapeamento)
        $stmt = $db->prepare("
            INSERT INTO leads (workspace_id, phone_number, stage, first_seen)
            VALUES (1, ?, 'novo', NOW())
        ");
        $stmt->execute([$phoneNumber]);
        $leadId = $db->lastInsertId();
        $workspaceId = 1;
    } else {
        $leadId = $lead['id'];
        $workspaceId = $lead['workspace_id'];
    }
    
    // Criar evento de mensagem
    $stmt = $db->prepare("
        INSERT INTO events (workspace_id, lead_id, event_type, page_url, raw_data, created_at)
        VALUES (?, ?, 'whatsapp_message', ?, ?, NOW())
    ");
    $stmt->execute([
        $workspaceId,
        $leadId,
        'whatsapp://message/' . $message['message_id'],
        $body
    ]);
    
    logMessage('INFO', 'WhatsApp message processed', [
        'from' => $phoneNumber,
        'lead_id' => $leadId,
        'text' => substr($message['text'] ?? '', 0, 50)
    ]);
}

http_response_code(200);
echo json_encode(['ok' => true]);



