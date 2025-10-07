<?php
/**
 * VisionMetrics - Connect WhatsApp QR
 * 
 * Creates integration and generates QR code via BSP
 * Returns JSON: {session_id, qr_image_url, db_session_id}
 */

require_once __DIR__ . '/../../middleware.php';
require_once __DIR__ . '/../../../src/bootstrap.php';

use VisionMetrics\Integrations\WhatsappIntegration;
use VisionMetrics\Integrations\Adapters\Dialog360Adapter;

header('Content-Type: application/json');

try {
    // Only POST allowed
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed');
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $provider = $input['provider'] ?? '360dialog';
    $apiKey = $input['api_key'] ?? getenv('BSP_API_KEY');
    $name = $input['name'] ?? 'WhatsApp Integration';
    
    if (empty($apiKey)) {
        throw new Exception(
            'API Key necessária. Configure BSP_API_KEY no .env ou forneça uma API key.'
        );
    }
    
    $db = getDB();
    $integration = new WhatsappIntegration($db);
    
    // Prepare credentials
    $credentials = [
        'api_key' => $apiKey
    ];
    
    // Create or update integration
    $integrationId = $integration->createOrUpdate(
        $currentWorkspace['id'],
        $provider,
        $credentials,
        $name
    );
    
    // Select BSP adapter
    $adapter = null;
    switch ($provider) {
        case '360dialog':
            $adapter = new Dialog360Adapter();
            break;
        default:
            throw new Exception("Provider not supported: $provider");
    }
    
    // Create session via BSP
    $sessionData = $adapter->createSession($credentials);
    
    if (empty($sessionData['session_id']) || empty($sessionData['qr_image_url'])) {
        throw new Exception('BSP failed to generate QR: ' . json_encode($sessionData));
    }
    
    // Save session to database
    $dbSessionId = $integration->createSession(
        $integrationId,
        $sessionData['session_id'],
        $sessionData['qr_image_url']
    );
    
    // Update integration meta if phone_id present
    if (!empty($sessionData['phone_id'])) {
        $integration->updateMeta($integrationId, [
            'phone_id' => $sessionData['phone_id']
        ]);
    }
    
    // Log
    error_log("WhatsApp QR generated for workspace {$currentWorkspace['id']}, integration $integrationId");
    
    echo json_encode([
        'success' => true,
        'session_id' => $sessionData['session_id'],
        'qr_image_url' => $sessionData['qr_image_url'],
        'db_session_id' => $dbSessionId,
        'integration_id' => $integrationId
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
    error_log("WhatsApp connect QR error: " . $e->getMessage());
}




