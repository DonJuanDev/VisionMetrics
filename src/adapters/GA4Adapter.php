<?php
/**
 * Google Analytics 4 - Measurement Protocol Adapter
 * Server-side event tracking
 * 
 * ENV VARS:
 * - GA4_MEASUREMENT_ID: ID de medição (formato: G-XXXXXXXXXX)
 * - GA4_API_SECRET: Secret da API (obter em Admin > Data Streams > Measurement Protocol)
 * 
 * TESTE:
 * curl -X POST "https://www.google-analytics.com/mp/collect?measurement_id=G-XXXXXXXXXX&api_secret=SECRET" \
 *   -d '{"client_id":"test.123","events":[{"name":"page_view","params":{"page_location":"https://example.com"}}]}'
 */

namespace VisionMetrics\Adapters;

class GA4Adapter {
    private $measurementId;
    private $apiSecret;
    private $mode;
    private $apiUrl = 'https://www.google-analytics.com/mp/collect';
    private $workspaceId;
    
    public function __construct($workspaceId = null) {
        $this->workspaceId = $workspaceId;
        $this->mode = env('ADAPTER_MODE', 'simulate');
        
        if ($workspaceId) {
            $this->loadWorkspaceCredentials();
        } else {
            // Fallback para credenciais globais
            $this->measurementId = env('GA4_MEASUREMENT_ID');
            $this->apiSecret = env('GA4_API_SECRET');
        }
    }
    
    private function loadWorkspaceCredentials() {
        if (!$this->workspaceId) return;
        
        $db = getDB();
        $stmt = $db->prepare("
            SELECT credentials FROM integrations 
            WHERE workspace_id = ? AND provider = 'ga4' AND is_active = 1
        ");
        $stmt->execute([$this->workspaceId]);
        $integration = $stmt->fetch();
        
        if ($integration && $integration['credentials']) {
            $creds = json_decode($integration['credentials'], true);
            $this->measurementId = $creds['measurement_id'] ?? null;
            $this->apiSecret = $creds['api_secret'] ?? null;
        }
    }
    
    /**
     * Enviar evento
     */
    public function sendEvent($eventName, $clientId, $params = []) {
        if ($this->mode === 'simulate') {
            logMessage('INFO', '[SIMULATE] GA4 sendEvent', [
                'event_name' => $eventName,
                'client_id' => $clientId
            ]);
            
            return ['success' => true, 'mode' => 'simulated'];
        }
        
        $url = $this->apiUrl . '?' . http_build_query([
            'measurement_id' => $this->measurementId,
            'api_secret' => $this->apiSecret
        ]);
        
        $payload = [
            'client_id' => $clientId,
            'events' => [[
                'name' => $eventName,
                'params' => $params
            ]]
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        // GA4 retorna 204 No Content em sucesso
        if ($httpCode === 204 || $httpCode === 200) {
            return ['success' => true];
        }
        
        logMessage('ERROR', 'GA4 API error', [
            'http_code' => $httpCode,
            'response' => $response
        ]);
        
        return ['success' => false, 'error' => 'GA4 API error', 'http_code' => $httpCode];
    }
}



