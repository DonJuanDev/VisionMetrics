<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../middleware.php';

// Only admin users can test integrations
if ($currentUser['role'] !== 'owner' && $currentUser['role'] !== 'admin') {
    http_response_code(403);
    json_response(['error' => 'Access denied']);
}

$provider = $_GET['provider'] ?? $_POST['provider'] ?? '';
$action = $_POST['action'] ?? 'test';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    
    if ($action === 'test') {
        $result = testIntegration($provider, $currentWorkspace['id']);
        json_response($result);
    } elseif ($action === 'save') {
        $result = saveIntegrationCredentials($provider, $_POST, $currentWorkspace['id']);
        json_response($result);
    }
}

function testIntegration($provider, $workspaceId) {
    try {
        switch ($provider) {
            case 'meta':
                $adapter = new \VisionMetrics\Adapters\MetaAdapter($workspaceId);
                $result = $adapter->sendConversion('TestEvent', [
                    'email' => 'test@example.com',
                    'first_name' => 'Test',
                    'ip' => '127.0.0.1',
                    'user_agent' => 'VisionMetrics Test',
                    'page_url' => 'https://example.com/test'
                ], [], 'test_' . time());
                break;
                
            case 'ga4':
                $adapter = new \VisionMetrics\Adapters\GA4Adapter($workspaceId);
                $result = $adapter->sendEvent('test_event', 'test_client_' . time(), [
                    'page_location' => 'https://example.com/test',
                    'test_mode' => true
                ]);
                break;
                
            case 'tiktok':
                $adapter = new \VisionMetrics\Adapters\TikTokAdapter($workspaceId);
                $result = $adapter->sendEvent('TestEvent', [
                    'email' => 'test@example.com',
                    'ip' => '127.0.0.1',
                    'user_agent' => 'VisionMetrics Test',
                    'page_url' => 'https://example.com/test'
                ], ['test_mode' => true]);
                break;
                
            default:
                return ['success' => false, 'error' => 'Unknown provider'];
        }
        
        // Update test result in database
        $db = getDB();
        $stmt = $db->prepare("
            UPDATE integrations 
            SET last_test_at = NOW(), test_result = ?, status = ?
            WHERE workspace_id = ? AND provider = ?
        ");
        $status = $result['success'] ? 'active' : 'error';
        $stmt->execute([
            json_encode($result),
            $status,
            $workspaceId,
            $provider
        ]);
        
        return $result;
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

function saveIntegrationCredentials($provider, $data, $workspaceId) {
    try {
        $db = getDB();
        
        $credentials = [];
        switch ($provider) {
            case 'meta':
                $credentials = [
                    'pixel_id' => $data['pixel_id'] ?? '',
                    'access_token' => $data['access_token'] ?? '',
                    'test_event_code' => $data['test_event_code'] ?? ''
                ];
                break;
                
            case 'ga4':
                $credentials = [
                    'measurement_id' => $data['measurement_id'] ?? '',
                    'api_secret' => $data['api_secret'] ?? ''
                ];
                break;
                
            case 'tiktok':
                $credentials = [
                    'pixel_id' => $data['pixel_id'] ?? '',
                    'access_token' => $data['access_token'] ?? ''
                ];
                break;
                
            default:
                return ['success' => false, 'error' => 'Unknown provider'];
        }
        
        // Upsert integration
        $stmt = $db->prepare("
            INSERT INTO integrations (workspace_id, provider, credentials, is_active, status, created_at, updated_at)
            VALUES (?, ?, ?, 1, 'inactive', NOW(), NOW())
            ON DUPLICATE KEY UPDATE
            credentials = VALUES(credentials),
            is_active = VALUES(is_active),
            status = 'inactive',
            updated_at = NOW()
        ");
        $stmt->execute([$workspaceId, $provider, json_encode($credentials)]);
        
        return ['success' => true, 'message' => 'Credentials saved successfully'];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

// Get integration status
$db = getDB();
$stmt = $db->prepare("
    SELECT provider, status, last_test_at, test_result, credentials
    FROM integrations 
    WHERE workspace_id = ? AND provider = ?
");
$stmt->execute([$currentWorkspace['id'], $provider]);
$integration = $stmt->fetch();

json_response([
    'provider' => $provider,
    'integration' => $integration,
    'available_providers' => ['meta', 'ga4', 'tiktok']
]);