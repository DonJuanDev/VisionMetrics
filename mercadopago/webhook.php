<?php
/**
 * MercadoPago - Webhook Handler
 * Recebe notificações de pagamentos
 * 
 * Configurar em: https://www.mercadopago.com.br/developers/panel/webhooks
 * URL: https://SEU-DOMINIO.com/mercadopago/webhook.php
 * 
 * TESTE LOCAL (ngrok):
 * 1. ngrok http 3000
 * 2. Configurar URL: https://xxxxx.ngrok.io/mercadopago/webhook.php
 * 3. Fazer pagamento de teste no sandbox
 * 
 * SIMULAR WEBHOOK:
 * curl -X POST http://localhost:3000/mercadopago/webhook.php \
 *   -H "Content-Type: application/json" \
 *   -d '{"action":"payment.created","data":{"id":"123456789"}}'
 */

require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/adapters/MercadoPagoAdapter.php';

use VisionMetrics\Adapters\MercadoPagoAdapter;

// Log webhook recebido
$body = file_get_contents('php://input');
$payload = json_decode($body, true);

logMessage('INFO', 'MercadoPago webhook received', [
    'action' => $payload['action'] ?? 'unknown',
    'type' => $payload['type'] ?? 'unknown',
    'data_id' => $payload['data']['id'] ?? null
]);

// Verificar webhook token (opcional mas recomendado)
$mercadopago = new MercadoPagoAdapter();
$headers = getallheaders();

if (!$mercadopago->validateWebhook($headers, $body)) {
    logMessage('WARNING', 'Invalid webhook signature');
    http_response_code(401);
    exit;
}

// Processar evento
$action = $payload['action'] ?? '';
$type = $payload['type'] ?? '';

// Log no banco
$db = getDB();
$stmt = $db->prepare("
    INSERT INTO webhooks_logs (source, event_type, payload, received_at)
    VALUES ('mercadopago', ?, ?, NOW())
");
$stmt->execute([$action, $body]);

// Processar payment
if ($type === 'payment' && isset($payload['data']['id'])) {
    $paymentId = $payload['data']['id'];
    
    // Buscar informações do pagamento
    $paymentInfo = $mercadopago->getPayment($paymentId);
    
    if ($paymentInfo['success'] && $paymentInfo['status'] === 'approved') {
        // Atualizar subscription
        $stmt = $db->prepare("
            UPDATE subscriptions 
            SET status = 'active',
                mercadopago_payment_id = ?,
                current_period_start = NOW(),
                current_period_end = DATE_ADD(NOW(), INTERVAL 1 MONTH),
                updated_at = NOW()
            WHERE mercadopago_preference_id IN (
                SELECT preference_id FROM (
                    SELECT preference_id FROM mercadopago_payments WHERE payment_id = ?
                ) as tmp
            )
        ");
        // $stmt->execute([$paymentId, $paymentId]);
        
        logMessage('INFO', 'Subscription activated', [
            'payment_id' => $paymentId,
            'amount' => $paymentInfo['amount']
        ]);
    }
}

http_response_code(200);
echo json_encode(['ok' => true]);



