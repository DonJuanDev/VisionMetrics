<?php
/**
 * VisionMetrics - Pixel Tracking (GET fallback)
 * 1x1 transparent GIF
 */

header('Access-Control-Allow-Origin: *');
header('Content-Type: image/gif');
header('Cache-Control: no-cache, no-store, must-revalidate');

require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/db.php';

// Get API Key from query
$apiKey = $_GET['api_key'] ?? null;

if (!$apiKey) {
    // Return 1x1 transparent GIF anyway
    echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
    exit;
}

// Validate API Key
$db = getDB();
$stmt = $db->prepare("SELECT workspace_id FROM api_keys WHERE key_hash = ?");
$stmt->execute([hash('sha256', $apiKey)]);
$apiKeyRecord = $stmt->fetch();

if ($apiKeyRecord) {
    $workspaceId = $apiKeyRecord['workspace_id'];
    
    // Create simple pageview event
    $pageUrl = $_GET['url'] ?? $_SERVER['HTTP_REFERER'] ?? 'unknown';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
    $ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? null;
    
    $stmt = $db->prepare("
        INSERT INTO events (
            workspace_id, event_type, page_url, user_agent, ip_address
        ) VALUES (?, 'pageview', ?, ?, ?)
    ");
    $stmt->execute([$workspaceId, $pageUrl, $userAgent, $ipAddress]);
}

// Return 1x1 transparent GIF
echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');