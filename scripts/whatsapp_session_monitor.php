<?php
/**
 * VisionMetrics - WhatsApp Session Monitor (CRON)
 * 
 * Monitors pending WhatsApp sessions and updates their status
 * Run via CRON every 1-5 minutes:
 * 
 * */1 * * * * php /path/to/scripts/whatsapp_session_monitor.php >> /path/to/logs/cron.log 2>&1
 * 
 * Purpose:
 * - Poll pending sessions for QR scan completion
 * - Update connected sessions heartbeat
 * - Detect disconnected sessions
 */

require_once __DIR__ . '/../backend/config.php';
require_once __DIR__ . '/../src/bootstrap.php';

use VisionMetrics\Integrations\WhatsappIntegration;
use VisionMetrics\Integrations\Adapters\Dialog360Adapter;

$db = getDB();
$integration = new WhatsappIntegration($db);

echo "[" . date('Y-m-d H:i:s') . "] WhatsApp Session Monitor started\n";

// ═══════════════════════════════════════════════════════════
// 1. MONITOR PENDING SESSIONS (waiting for QR scan)
// ═══════════════════════════════════════════════════════════
$stmt = $db->prepare("
    SELECT s.*, i.provider, i.workspace_id
    FROM whatsapp_sessions s
    JOIN whatsapp_integrations i ON s.integration_id = i.id
    WHERE s.status = 'pending'
    AND s.created_at > DATE_SUB(NOW(), INTERVAL 10 MINUTE)
    ORDER BY s.created_at DESC
    LIMIT 50
");
$stmt->execute();
$pendingSessions = $stmt->fetchAll();

echo "Found " . count($pendingSessions) . " pending sessions to check\n";

foreach ($pendingSessions as $session) {
    try {
        // Get credentials
        $credentials = $integration->getCredentialsDecrypted($session['integration_id']);
        
        // Select adapter
        $adapter = null;
        switch ($session['provider']) {
            case '360dialog':
                $adapter = new Dialog360Adapter();
                break;
            default:
                continue 2; // Skip unsupported provider
        }
        
        // Check status
        $statusData = $adapter->getSessionStatus($session['session_id'], $credentials);
        
        if ($statusData['status'] === 'connected') {
            // Session connected!
            $integration->updateSessionStatus($session['id'], 'connected');
            $integration->setStatus($session['integration_id'], 'active');
            
            // Update meta with phone
            if (!empty($statusData['phone'])) {
                $integration->updateMeta($session['integration_id'], [
                    'phone' => $statusData['phone'],
                    'waba_id' => $statusData['waba_id'] ?? null
                ]);
            }
            
            echo "  ✓ Session {$session['id']} CONNECTED (phone: {$statusData['phone']})\n";
            
        } elseif ($statusData['status'] === 'error' || $statusData['status'] === 'disconnected') {
            $integration->updateSessionStatus($session['id'], 'error', 'Failed to connect');
            echo "  ✗ Session {$session['id']} FAILED\n";
        } else {
            // Still pending
            echo "  ⏳ Session {$session['id']} still pending\n";
        }
        
    } catch (Exception $e) {
        echo "  ✗ Error checking session {$session['id']}: " . $e->getMessage() . "\n";
    }
    
    usleep(500000); // 0.5s delay between requests
}

// ═══════════════════════════════════════════════════════════
// 2. UPDATE HEARTBEAT FOR CONNECTED SESSIONS
// ═══════════════════════════════════════════════════════════
$stmt = $db->prepare("
    SELECT s.*, i.provider, i.workspace_id
    FROM whatsapp_sessions s
    JOIN whatsapp_integrations i ON s.integration_id = i.id
    WHERE s.status = 'connected'
    AND (s.last_heartbeat IS NULL OR s.last_heartbeat < DATE_SUB(NOW(), INTERVAL 30 MINUTE))
    ORDER BY s.last_heartbeat ASC
    LIMIT 20
");
$stmt->execute();
$connectedSessions = $stmt->fetchAll();

echo "\nChecking " . count($connectedSessions) . " connected sessions\n";

foreach ($connectedSessions as $session) {
    try {
        $credentials = $integration->getCredentialsDecrypted($session['integration_id']);
        
        // Select adapter
        $adapter = null;
        switch ($session['provider']) {
            case '360dialog':
                $adapter = new Dialog360Adapter();
                break;
            default:
                continue 2;
        }
        
        // Check if still connected
        $statusData = $adapter->getSessionStatus($session['session_id'], $credentials);
        
        if ($statusData['status'] === 'connected') {
            // Update heartbeat
            $integration->updateSessionStatus($session['id'], 'connected');
            echo "  ✓ Session {$session['id']} heartbeat updated\n";
        } else {
            // Disconnected
            $integration->updateSessionStatus($session['id'], 'disconnected', 'Session lost');
            $integration->setStatus($session['integration_id'], 'inactive');
            echo "  ✗ Session {$session['id']} DISCONNECTED\n";
        }
        
    } catch (Exception $e) {
        echo "  ✗ Error updating session {$session['id']}: " . $e->getMessage() . "\n";
    }
    
    usleep(500000); // 0.5s delay
}

// ═══════════════════════════════════════════════════════════
// 3. CLEANUP OLD PENDING SESSIONS (>15 min)
// ═══════════════════════════════════════════════════════════
$stmt = $db->prepare("
    UPDATE whatsapp_sessions 
    SET status = 'error', error_message = 'QR scan timeout'
    WHERE status = 'pending' 
    AND created_at < DATE_SUB(NOW(), INTERVAL 15 MINUTE)
");
$stmt->execute();
$expiredCount = $stmt->rowCount();

if ($expiredCount > 0) {
    echo "\n✗ Expired $expiredCount pending sessions (timeout)\n";
}

echo "\n[" . date('Y-m-d H:i:s') . "] WhatsApp Session Monitor completed\n\n";



