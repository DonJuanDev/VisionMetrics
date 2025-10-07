<?php
/**
 * VisionMetrics - Disconnect WhatsApp Integration
 * 
 * Closes BSP session and updates database status
 * Can also delete integration if delete=true
 */

require_once __DIR__ . '/../../middleware.php';
require_once __DIR__ . '/../../../src/bootstrap.php';

use VisionMetrics\Integrations\WhatsappIntegration;
use VisionMetrics\Integrations\Adapters\Dialog360Adapter;

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed');
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $integrationId = $input['integration_id'] ?? null;
    $delete = $input['delete'] ?? false;
    
    if (!$integrationId) {
        throw new Exception('integration_id required');
    }
    
    $db = getDB();
    $integration = new WhatsappIntegration($db);
    
    // Get integration
    $int = $integration->getById($integrationId);
    
    if (!$int || $int['workspace_id'] != $currentWorkspace['id']) {
        throw new Exception('Integration not found or unauthorized');
    }
    
    // Get active session
    $session = $integration->getActiveSession($integrationId);
    
    if ($session && $session['status'] === 'connected') {
        // Close session via BSP
        try {
            $credentials = $integration->getCredentialsDecrypted($integrationId);
            
            // Select adapter
            $adapter = null;
            switch ($int['provider']) {
                case '360dialog':
                    $adapter = new Dialog360Adapter();
                    break;
            }
            
            if ($adapter) {
                $adapter->closeSession($session['session_id'], $credentials);
            }
        } catch (Exception $e) {
            error_log("BSP disconnect failed: " . $e->getMessage());
            // Continue anyway to update database
        }
        
        // Update session status
        $integration->updateSessionStatus($session['id'], 'disconnected');
    }
    
    if ($delete) {
        // Delete integration
        $integration->delete($integrationId, $currentWorkspace['id']);
        error_log("WhatsApp integration deleted: workspace {$currentWorkspace['id']}, integration $integrationId");
    } else {
        // Just mark as inactive
        $integration->setStatus($integrationId, 'inactive');
        error_log("WhatsApp integration disconnected: workspace {$currentWorkspace['id']}, integration $integrationId");
    }
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
    error_log("WhatsApp disconnect error: " . $e->getMessage());
}




