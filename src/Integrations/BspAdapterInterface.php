<?php
/**
 * VisionMetrics - BSP Adapter Interface
 * 
 * Interface for WhatsApp Business Solution Providers (BSPs)
 * Implementations should handle provider-specific API calls for:
 * - QR code generation
 * - Session management
 * - Webhook verification
 * 
 * Supported providers: 360Dialog, Infobip, Twilio, MessageBird, etc
 * 
 * @package VisionMetrics\Integrations
 */

namespace VisionMetrics\Integrations;

interface BspAdapterInterface
{
    /**
     * Create new WhatsApp session and generate QR code
     * 
     * @param array $credentials Provider-specific credentials
     * @return array ['session_id' => string, 'qr_image_url' => string]
     * @throws \RuntimeException On API failure
     */
    public function createSession(array $credentials): array;
    
    /**
     * Get session status
     * 
     * @param string $sessionId BSP session identifier
     * @param array $credentials Provider credentials
     * @return array ['status' => 'pending|connected|disconnected|error', 'phone' => string|null]
     * @throws \RuntimeException On API failure
     */
    public function getSessionStatus(string $sessionId, array $credentials): array;
    
    /**
     * Close/disconnect session
     * 
     * @param string $sessionId BSP session identifier
     * @param array $credentials Provider credentials
     * @return bool Success
     * @throws \RuntimeException On API failure
     */
    public function closeSession(string $sessionId, array $credentials): bool;
    
    /**
     * Verify webhook signature (optional, depends on provider)
     * 
     * @param array $headers Request headers
     * @param string $payload Raw request body
     * @param string $secret Webhook secret
     * @return bool Valid signature
     */
    public function verifyWebhookSignature(array $headers, string $payload, string $secret): bool;
    
    /**
     * Get provider name
     * 
     * @return string Provider identifier
     */
    public function getProviderName(): string;
}

