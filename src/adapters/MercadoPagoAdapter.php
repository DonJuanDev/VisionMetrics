<?php
/**
 * MercadoPago Adapter
 * Billing, payments e subscriptions
 * 
 * ENV VARS REQUIRED:
 * - MERCADOPAGO_ACCESS_TOKEN: Token de acesso (obter em https://www.mercadopago.com.br/developers/panel/credentials)
 * - MERCADOPAGO_PUBLIC_KEY: Chave pública
 * - MERCADOPAGO_WEBHOOK_TOKEN: Token secreto para validar webhooks
 * 
 * TESTE (Sandbox):
 * curl -X POST https://api.mercadopago.com/checkout/preferences \
 *   -H "Authorization: Bearer $MERCADOPAGO_ACCESS_TOKEN" \
 *   -H "Content-Type: application/json" \
 *   -d '{"items":[{"title":"Plano Pro","unit_price":297,"quantity":1}],"back_urls":{"success":"http://localhost:3000/billing-success.php"}}'
 */

namespace VisionMetrics\Adapters;

class MercadoPagoAdapter {
    private $accessToken;
    private $publicKey;
    private $mode;
    private $apiUrl;
    
    public function __construct() {
        $this->accessToken = env('MERCADOPAGO_ACCESS_TOKEN');
        $this->publicKey = env('MERCADOPAGO_PUBLIC_KEY');
        $this->mode = env('ADAPTER_MODE', 'simulate');
        
        // Detectar sandbox vs production pelo token
        $this->apiUrl = (strpos($this->accessToken, 'TEST-') === 0) 
            ? 'https://api.mercadopago.com' 
            : 'https://api.mercadopago.com';
    }
    
    /**
     * Criar preference de checkout
     */
    public function createPreference($items, $metadata = [], $backUrls = []) {
        if ($this->mode === 'simulate') {
            logMessage('INFO', '[SIMULATE] MercadoPago createPreference', compact('items', 'metadata'));
            
            return [
                'success' => true,
                'preference_id' => 'SIMULATE-' . bin2hex(random_bytes(8)),
                'init_point' => 'http://localhost:3000/mercadopago-simulate.php',
                'sandbox_init_point' => 'http://localhost:3000/mercadopago-simulate.php'
            ];
        }
        
        $payload = [
            'items' => $items,
            'metadata' => $metadata,
            'back_urls' => array_merge([
                'success' => APP_URL . '/billing-success.php',
                'failure' => APP_URL . '/billing-failure.php',
                'pending' => APP_URL . '/billing-pending.php'
            ], $backUrls),
            'auto_return' => 'approved',
            'notification_url' => APP_URL . '/mercadopago/webhook.php'
        ];
        
        $ch = curl_init($this->apiUrl . '/checkout/preferences');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->accessToken,
            'Content-Type: application/json'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 201 && $httpCode !== 200) {
            logMessage('ERROR', 'MercadoPago API error', [
                'http_code' => $httpCode,
                'response' => $response
            ]);
            
            return ['success' => false, 'error' => 'Failed to create preference'];
        }
        
        $data = json_decode($response, true);
        
        return [
            'success' => true,
            'preference_id' => $data['id'] ?? null,
            'init_point' => $data['init_point'] ?? null,
            'sandbox_init_point' => $data['sandbox_init_point'] ?? null
        ];
    }
    
    /**
     * Validar webhook signature
     */
    public function validateWebhook($headers, $body) {
        $webhookToken = env('MERCADOPAGO_WEBHOOK_TOKEN');
        
        if ($this->mode === 'simulate') {
            return true;
        }
        
        // MercadoPago pode enviar x-signature header
        // Implementar validação conforme documentação oficial
        return true; // TODO: Implementar validação real
    }
    
    /**
     * Buscar informação de pagamento
     */
    public function getPayment($paymentId) {
        if ($this->mode === 'simulate') {
            return [
                'success' => true,
                'status' => 'approved',
                'amount' => 297.00,
                'payer_email' => 'test@example.com'
            ];
        }
        
        $ch = curl_init($this->apiUrl . '/v1/payments/' . $paymentId);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->accessToken
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            return ['success' => false, 'error' => 'Payment not found'];
        }
        
        $data = json_decode($response, true);
        
        return [
            'success' => true,
            'status' => $data['status'] ?? 'unknown',
            'amount' => $data['transaction_amount'] ?? 0,
            'payer_email' => $data['payer']['email'] ?? null
        ];
    }
}



