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
    
    public function __construct() {
        $this->pixelId = env('TIKTOK_PIXEL_ID');
        $this->accessToken = env('TIKTOK_ACCESS_TOKEN');
        $this->mode = env('ADAPTER_MODE', 'simulate');
    }
    
    /**
     * Enviar evento (STUB)
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
        
        // TODO: Implementar chamada real para TikTok Events API
        // URL: https://business-api.tiktok.com/open_api/v1.3/event/track/
        // Headers: Access-Token: $this->accessToken
        // Body: {pixel_code, event, context:{user:{...}, page:{...}}, properties:{...}}
        
        logMessage('WARNING', 'TikTok adapter not fully implemented (live mode)', [
            'event' => $eventName
        ]);
        
        return [
            'success' => false,
            'error' => 'TikTok adapter stub - implement live mode',
            'docs' => 'https://business-api.tiktok.com/portal/docs?id=1771100865818625'
        ];
    }
}



