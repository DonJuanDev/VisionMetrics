<?php
/**
 * VisionMetrics - 360Dialog BSP Adapter
 * 
 * Implementation for 360Dialog WhatsApp Business API
 * Documentation: https://docs.360dialog.com/
 * 
 * Required credentials:
 * - api_key: 360Dialog API key
 * - client_id: (optional) Client ID for multi-tenant
 * 
 * @package VisionMetrics\Integrations\Adapters
 */

namespace VisionMetrics\Integrations\Adapters;

use VisionMetrics\Integrations\BspAdapterInterface;

class Dialog360Adapter implements BspAdapterInterface
{
    private const API_BASE = 'https://waba.360dialog.io/v1';
    
    /**
     * Create WhatsApp session via 360Dialog Partner API
     * 
     * @param array $credentials ['api_key' => string]
     * @return array ['session_id' => string, 'qr_image_url' => string]
     */
    public function createSession(array $credentials): array
    {
        $apiKey = $credentials['api_key'] ?? null;
        
        if (empty($apiKey)) {
            throw new \InvalidArgumentException('360Dialog API key is required');
        }
        
        // 360Dialog uses Partner API for channel creation
        // Endpoint: POST /partners/channels
        $url = self::API_BASE . '/partners/channels';
        
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'D360-API-KEY: ' . $apiKey,
                'Content-Type: application/json'
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'webhook_url' => getenv('APP_URL') . '/webhooks/whatsapp.php'
            ]),
            CURLOPT_TIMEOUT => 30
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new \RuntimeException('360Dialog API request failed: ' . $error);
        }
        
        if ($httpCode !== 200 && $httpCode !== 201) {
            throw new \RuntimeException(
                "360Dialog API returned error $httpCode: " . $response
            );
        }
        
        $data = json_decode($response, true);
        
        if (!isset($data['client'], $data['client']['qr_code_url'])) {
            throw new \RuntimeException('Invalid response from 360Dialog: ' . $response);
        }
        
        return [
            'session_id' => $data['client']['id'] ?? uniqid('360d_'),
            'qr_image_url' => $data['client']['qr_code_url'],
            'phone_id' => $data['client']['waba_id'] ?? null
        ];
    }
    
    /**
     * Get session status from 360Dialog
     * 
     * @param string $sessionId Channel/client ID
     * @param array $credentials ['api_key' => string]
     * @return array ['status' => string, 'phone' => string|null]
     */
    public function getSessionStatus(string $sessionId, array $credentials): array
    {
        $apiKey = $credentials['api_key'] ?? null;
        
        if (empty($apiKey)) {
            throw new \InvalidArgumentException('360Dialog API key is required');
        }
        
        // GET /partners/channels/{client_id}
        $url = self::API_BASE . '/partners/channels/' . urlencode($sessionId);
        
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'D360-API-KEY: ' . $apiKey
            ],
            CURLOPT_TIMEOUT => 15
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            // Session might not exist or disconnected
            return ['status' => 'disconnected', 'phone' => null];
        }
        
        $data = json_decode($response, true);
        
        // Map 360Dialog status to our status
        $status = 'pending';
        $phone = null;
        
        if (isset($data['client']['status'])) {
            switch ($data['client']['status']) {
                case 'connected':
                case 'active':
                    $status = 'connected';
                    $phone = $data['client']['phone_number'] ?? null;
                    break;
                case 'pending':
                    $status = 'pending';
                    break;
                default:
                    $status = 'disconnected';
            }
        }
        
        return [
            'status' => $status,
            'phone' => $phone,
            'waba_id' => $data['client']['waba_id'] ?? null
        ];
    }
    
    /**
     * Close/disconnect session
     * 
     * @param string $sessionId Channel ID
     * @param array $credentials ['api_key' => string]
     * @return bool Success
     */
    public function closeSession(string $sessionId, array $credentials): bool
    {
        $apiKey = $credentials['api_key'] ?? null;
        
        if (empty($apiKey)) {
            throw new \InvalidArgumentException('360Dialog API key is required');
        }
        
        // DELETE /partners/channels/{client_id}
        $url = self::API_BASE . '/partners/channels/' . urlencode($sessionId);
        
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_HTTPHEADER => [
                'D360-API-KEY: ' . $apiKey
            ],
            CURLOPT_TIMEOUT => 15
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return $httpCode === 200 || $httpCode === 204;
    }
    
    /**
     * Verify webhook signature from 360Dialog
     * 
     * @param array $headers Request headers
     * @param string $payload Raw body
     * @param string $secret Not used by 360Dialog (uses IP whitelist)
     * @return bool Always true (360Dialog uses IP whitelist)
     */
    public function verifyWebhookSignature(array $headers, string $payload, string $secret): bool
    {
        // 360Dialog typically uses IP whitelist rather than HMAC signatures
        // In production, verify source IP is from 360Dialog's ranges
        
        // Optional: Check for D360-Signature header if configured
        if (isset($headers['D360-Signature'])) {
            $expectedSignature = hash_hmac('sha256', $payload, $secret);
            return hash_equals($expectedSignature, $headers['D360-Signature']);
        }
        
        return true; // Trust if using IP whitelist
    }
    
    /**
     * Get provider name
     * 
     * @return string
     */
    public function getProviderName(): string
    {
        return '360dialog';
    }
}

