<?php
/**
 * Meta Ads Conversions API (CAPI) Adapter
 * Server-side conversion tracking para Facebook/Instagram
 * 
 * ENV VARS:
 * - META_PIXEL_ID: ID do pixel (obter no Events Manager)
 * - META_ACCESS_TOKEN: Token de acesso (https://developers.facebook.com/tools/accesstoken/)
 * - META_TEST_EVENT_CODE: Código de teste (opcional, para Test Events)
 * 
 * TESTE (simulate mode):
 * curl -X POST http://localhost:3000/api/test-integrations.php \
 *   -H "Content-Type: application/json" \
 *   -d '{"integration":"meta","event":"Lead","email":"test@example.com"}'
 * 
 * TESTE (live mode - sandbox):
 * Use Test Events no Meta Events Manager
 */

namespace VisionMetrics\Adapters;

class MetaAdapter {
    private $pixelId;
    private $accessToken;
    private $testEventCode;
    private $apiVersion = 'v18.0';
    private $mode;
    private $workspaceId;
    
    public function __construct($workspaceId = null) {
        $this->workspaceId = $workspaceId;
        $this->mode = env('ADAPTER_MODE', 'simulate');
        
        if ($workspaceId) {
            $this->loadWorkspaceCredentials();
        } else {
            // Fallback para credenciais globais
            $this->pixelId = env('META_PIXEL_ID');
            $this->accessToken = env('META_ACCESS_TOKEN');
            $this->testEventCode = env('META_TEST_EVENT_CODE');
        }
    }
    
    private function loadWorkspaceCredentials() {
        if (!$this->workspaceId) return;
        
        $db = getDB();
        $stmt = $db->prepare("
            SELECT credentials FROM integrations 
            WHERE workspace_id = ? AND provider = 'meta' AND is_active = 1
        ");
        $stmt->execute([$this->workspaceId]);
        $integration = $stmt->fetch();
        
        if ($integration && $integration['credentials']) {
            $creds = json_decode($integration['credentials'], true);
            $this->pixelId = $creds['pixel_id'] ?? null;
            $this->accessToken = $creds['access_token'] ?? null;
            $this->testEventCode = $creds['test_event_code'] ?? null;
        }
    }
    
    /**
     * Enviar evento de conversão
     */
    public function sendConversion($eventName, $userData, $customData = [], $eventId = null) {
        if ($this->mode === 'simulate') {
            logMessage('INFO', '[SIMULATE] Meta CAPI sendConversion', [
                'event_name' => $eventName,
                'event_id' => $eventId,
                'user_data' => array_keys($userData)
            ]);
            
            return [
                'success' => true,
                'mode' => 'simulated',
                'events_received' => 1,
                'messages' => ['Event simulated successfully']
            ];
        }
        
        $url = "https://graph.facebook.com/{$this->apiVersion}/{$this->pixelId}/events";
        
        $event = [
            'event_name' => $eventName,
            'event_time' => time(),
            'event_id' => $eventId ?: uniqid('vm_', true),
            'event_source_url' => $userData['page_url'] ?? '',
            'action_source' => 'website',
            'user_data' => $this->hashUserData($userData),
            'custom_data' => $customData
        ];
        
        if ($this->testEventCode) {
            $event['test_event_code'] = $this->testEventCode;
        }
        
        $payload = [
            'data' => [$event],
            'access_token' => $this->accessToken
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $result = json_decode($response, true);
        
        if ($httpCode !== 200) {
            logMessage('ERROR', 'Meta CAPI error', [
                'http_code' => $httpCode,
                'response' => $result
            ]);
            
            return [
                'success' => false,
                'error' => $result['error']['message'] ?? 'Unknown error',
                'http_code' => $httpCode
            ];
        }
        
        return [
            'success' => true,
            'events_received' => $result['events_received'] ?? 1,
            'messages' => $result['messages'] ?? []
        ];
    }
    
    /**
     * Hash user data conforme Meta guidelines
     * Todos os dados devem ser lowercase + trimmed + hashed com SHA256
     */
    private function hashUserData($userData) {
        $hashed = [];
        
        // Email
        if (!empty($userData['email'])) {
            $hashed['em'] = [hash('sha256', strtolower(trim($userData['email'])))];
        }
        
        // Phone - remover espaços, parênteses, hífens
        if (!empty($userData['phone'])) {
            $phone = preg_replace('/\D/', '', $userData['phone']);
            $hashed['ph'] = [hash('sha256', $phone)];
        }
        
        // First name
        if (!empty($userData['first_name'])) {
            $hashed['fn'] = [hash('sha256', strtolower(trim($userData['first_name'])))];
        }
        
        // Last name
        if (!empty($userData['last_name'])) {
            $hashed['ln'] = [hash('sha256', strtolower(trim($userData['last_name'])))];
        }
        
        // IP address
        if (!empty($userData['ip'])) {
            $hashed['client_ip_address'] = $userData['ip'];
        }
        
        // User agent
        if (!empty($userData['user_agent'])) {
            $hashed['client_user_agent'] = $userData['user_agent'];
        }
        
        // FBP cookie
        if (!empty($userData['fbp'])) {
            $hashed['fbp'] = $userData['fbp'];
        }
        
        // FBC cookie
        if (!empty($userData['fbc'])) {
            $hashed['fbc'] = $userData['fbc'];
        }
        
        return $hashed;
    }
}



