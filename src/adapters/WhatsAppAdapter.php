<?php
/**
 * WhatsApp Cloud API Adapter
 * Envio e recebimento de mensagens
 * 
 * ENV VARS:
 * - WHATSAPP_PHONE_ID: ID do número de telefone
 * - WHATSAPP_ACCESS_TOKEN: Token de acesso permanente
 * - WHATSAPP_VERIFY_TOKEN: Token para verificar webhook
 * - WHATSAPP_BUSINESS_ACCOUNT_ID: ID da conta business
 * 
 * SETUP:
 * 1. Criar App em https://developers.facebook.com
 * 2. Adicionar produto WhatsApp
 * 3. Obter Phone Number ID e Access Token
 * 4. Configurar webhook: https://YOUR-DOMAIN/webhooks/whatsapp.php
 * 5. Verify Token: usar o valor de WHATSAPP_VERIFY_TOKEN
 * 
 * TESTE (envio):
 * curl -X POST "https://graph.facebook.com/v18.0/PHONE_ID/messages" \
 *   -H "Authorization: Bearer $WHATSAPP_ACCESS_TOKEN" \
 *   -d '{"messaging_product":"whatsapp","to":"5511999999999","type":"text","text":{"body":"Test"}}'
 */

namespace VisionMetrics\Adapters;

class WhatsAppAdapter {
    private $phoneId;
    private $accessToken;
    private $verifyToken;
    private $apiVersion = 'v18.0';
    private $mode;
    
    public function __construct() {
        $this->phoneId = env('WHATSAPP_PHONE_ID');
        $this->accessToken = env('WHATSAPP_ACCESS_TOKEN');
        $this->verifyToken = env('WHATSAPP_VERIFY_TOKEN');
        $this->mode = env('ADAPTER_MODE', 'simulate');
    }
    
    /**
     * Enviar mensagem de texto
     */
    public function sendMessage($to, $message, $context = null) {
        if ($this->mode === 'simulate') {
            logMessage('INFO', '[SIMULATE] WhatsApp sendMessage', [
                'to' => $to,
                'message' => substr($message, 0, 50)
            ]);
            
            return [
                'success' => true,
                'mode' => 'simulated',
                'message_id' => 'wamid.SIMULATE=' . bin2hex(random_bytes(8))
            ];
        }
        
        $url = "https://graph.facebook.com/{$this->apiVersion}/{$this->phoneId}/messages";
        
        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => preg_replace('/\D/', '', $to),
            'type' => 'text',
            'text' => ['body' => $message]
        ];
        
        if ($context) {
            $payload['context'] = ['message_id' => $context];
        }
        
        $ch = curl_init($url);
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
        
        if ($httpCode !== 200) {
            logMessage('ERROR', 'WhatsApp API error', [
                'http_code' => $httpCode,
                'response' => $response
            ]);
            
            return ['success' => false, 'error' => 'WhatsApp API error'];
        }
        
        $data = json_decode($response, true);
        
        return [
            'success' => true,
            'message_id' => $data['messages'][0]['id'] ?? null
        ];
    }
    
    /**
     * Verificar webhook token
     */
    public function verifyWebhook($mode, $token, $challenge) {
        if ($mode === 'subscribe' && $token === $this->verifyToken) {
            return $challenge;
        }
        
        return false;
    }
    
    /**
     * Processar mensagem recebida
     */
    public function processIncomingMessage($payload) {
        $entry = $payload['entry'][0] ?? null;
        if (!$entry) return null;
        
        $change = $entry['changes'][0] ?? null;
        if (!$change || $change['field'] !== 'messages') return null;
        
        $value = $change['value'] ?? null;
        $message = $value['messages'][0] ?? null;
        
        if (!$message) return null;
        
        return [
            'from' => $message['from'],
            'message_id' => $message['id'],
            'timestamp' => $message['timestamp'],
            'type' => $message['type'],
            'text' => $message['text']['body'] ?? null,
            'context' => $message['context'] ?? null
        ];
    }
}



