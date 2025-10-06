<?php
/**
 * VisionMetrics - Poll WhatsApp Session Status
 * 
 * AJAX endpoint to check session connection status
 * Returns JSON: {status, phone, error_message}
 */

require_once __DIR__ . '/../../middleware.php';
require_once __DIR__ . '/../../../src/bootstrap.php';

use VisionMetrics\Integrations\WhatsappIntegration;
use VisionMetrics\Integrations\Adapters\Dialog360Adapter;

header('Content-Type: application/json');

try {
    $dbSessionId = $_GET['session_id'] ?? null;
    
    if (!$dbSessionId) {
        throw new Exception('session_id required');
    }
    
    $db = getDB();
    $integration = new WhatsappIntegration($db);
    
    // Get session
    $session = $integration->getSessionById($dbSessionId);
    
    if (!$session) {
        throw new Exception('Session not found');
    }
    
    // Verify workspace access
    if ($session['workspace_id'] != $currentWorkspace['id']) {
        throw new Exception('Unauthorized');
    }
    
    // Get integration
    $int = $integration->getById($session['integration_id']);
    
    // Check if already connected in database
    if ($session['status'] === 'connected') {
        echo json_encode([
            'status' => 'connected',
            'phone' => json_decode($int['meta'], true)['phone'] ?? null
        ]);
        exit;
    }
    
    if ($session['status'] === 'error') {
        echo json_encode([
            'status' => 'error',
            'error_message' => $session['error_message']
        ]);
        exit;
    }
    
    // Poll BSP for status
    try {
        $credentials = $integration->getCredentialsDecrypted($session['integration_id']);
        
        // Select adapter
        $adapter = null;
        switch ($int['provider']) {
            case '360dialog':
                $adapter = new Dialog360Adapter();
                break;
            default:
                throw new Exception('Provider not supported');
        }
        
        $statusData = $adapter->getSessionStatus($session['session_id'], $credentials);
        
        // Update database
        if ($statusData['status'] === 'connected') {
            $integration->updateSessionStatus($dbSessionId, 'connected');
            $integration->setStatus($session['integration_id'], 'active');
            
            // Update meta with phone
            if (!empty($statusData['phone'])) {
                $integration->updateMeta($session['integration_id'], [
                    'phone' => $statusData['phone'],
                    'waba_id' => $statusData['waba_id'] ?? null
                ]);
            }
            
            error_log("WhatsApp session connected: workspace {$currentWorkspace['id']}, session $dbSessionId");
        } elseif ($statusData['status'] === 'error' || $statusData['status'] === 'disconnected') {
            $integration->updateSessionStatus($dbSessionId, 'error', 'Connection failed');
        }
        
        echo json_encode($statusData);
        
    } catch (Exception $e) {
        // BSP polling failed
        error_log("WhatsApp status poll error: " . $e->getMessage());
        echo json_encode([
            'status' => 'pending',
            'message' => 'Checking...'
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'error' => $e->getMessage()
    ]);
}

