<?php
/**
 * TikTok Ads - Events API Adapter (STUB)
 * Template para implementação futura
 * 
 * ENV VARS:
 * - TIKTOK_PIXEL_ID: ID do pixel TikTok
 * - TIKTOK_ACCESS_TOKEN: Access token
 * - TIKTOK_TEST_EVENT_CODE: Código de teste
 * 
 * DOCUMENTAÇÃO:
 * https://business-api.tiktok.com/portal/docs?id=1771100865818625
 * 
 * TODO: Implementar TikTok Events API v1.3
 * Endpoint: POST https://business-api.tiktok.com/open_api/v1.3/event/track/
 */

namespace VisionMetrics\Adapters;

class TikTokAdapter {
    private $pixelId;
    private $accessToken;
    private $mode;
    private $workspaceId;
    
    public function __construct($workspaceId = null) {
        $this->workspaceId = $workspaceId;
        $this->mode = env('ADAPTER_MODE', 'simulate');
        
        if ($workspaceId) {
            $this->loadWorkspaceCredentials();
        } else {
            // Fallback para credenciais globais
            $this->pixelId = env('TIKTOK_PIXEL_ID');
            $this->accessToken = env('TIKTOK_ACCESS_TOKEN');
        }
    }
    
    private function loadWorkspaceCredentials() {
        if (!$this->workspaceId) return;
        
        $db = getDB();
        $stmt = $db->prepare("
            SELECT credentials FROM integrations 
            WHERE workspace_id = ? AND provider = 'tiktok' AND is_active = 1
        ");
        $stmt->execute([$this->workspaceId]);
        $integration = $stmt->fetch();
        
        if ($integration && $integration['credentials']) {
            $creds = json_decode($integration['credentials'], true);
            $this->pixelId = $creds['pixel_id'] ?? null;
            $this->accessToken = $creds['access_token'] ?? null;
        }
    }
    
    /**
     * Enviar evento
     */
    public function sendEvent($eventName, $userData, $properties = []) {
        if ($this->mode === 'simulate') {
            logMessage('INFO', '[SIMULATE] TikTok sendEvent', [
                'event' => $eventName,
                'pixel_id' => $this->pixelId
            ]);
            
            return [
                'success' => true,
                'mode' => 'simulated',
                'message' => 'TikTok event would be sent in live mode'
            ];
        }
        
        if (!$this->pixelId || !$this->accessToken) {
            return [
                'success' => false,
                'error' => 'TikTok credentials not configured'
            ];
        }
        
        $url = 'https://business-api.tiktok.com/open_api/v1.3/event/track/';
        
        $payload = [
            'pixel_code' => $this->pixelId,
            'event' => $eventName,
            'event_id' => uniqid('vm_', true),
            'timestamp' => time(),
            'context' => [
                'user' => $this->prepareUserData($userData),
                'page' => [
                    'url' => $userData['page_url'] ?? '',
                    'referrer' => $userData['referrer'] ?? ''
                ]
            ],
            'properties' => $properties
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Access-Token: ' . $this->accessToken
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $result = json_decode($response, true);
        
        if ($httpCode !== 200) {
            logMessage('ERROR', 'TikTok API error', [
                'http_code' => $httpCode,
                'response' => $result
            ]);
            
            return [
                'success' => false,
                'error' => $result['message'] ?? 'Unknown error',
                'http_code' => $httpCode
            ];
        }
        
        return [
            'success' => true,
            'data' => $result['data'] ?? null
        ];
    }
    
    /**
     * Preparar dados do usuário para TikTok
     */
    private function prepareUserData($userData) {
        $prepared = [];
        
        // Email
        if (!empty($userData['email'])) {
            $prepared['email'] = hash('sha256', strtolower(trim($userData['email'])));
        }
        
        // Phone
        if (!empty($userData['phone'])) {
            $phone = preg_replace('/\D/', '', $userData['phone']);
            $prepared['phone_number'] = hash('sha256', $phone);
        }
        
        // IP
        if (!empty($userData['ip'])) {
            $prepared['ip'] = $userData['ip'];
        }
        
        // User Agent
        if (!empty($userData['user_agent'])) {
            $prepared['user_agent'] = $userData['user_agent'];
        }
        
        // External ID (pode ser visitor_id)
        if (!empty($userData['visitor_id'])) {
            $prepared['external_id'] = hash('sha256', $userData['visitor_id']);
        }
        
        return $prepared;
    }
}



