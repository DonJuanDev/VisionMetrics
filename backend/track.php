<?php
/**
 * VisionMetrics - Tracking Endpoint (POST JSON)
 * Recebe eventos de tracking e cria leads
 * 
 * HEADERS:
 * - X-API-KEY: API key do workspace
 * - Idempotency-Key: UUID para deduplicação
 * 
 * BODY (JSON):
 * {
 *   "event": "pageview|form_submit|custom",
 *   "event_name": "Nome do evento",
 *   "page_url": "https://...",
 *   "email": "user@example.com",
 *   "phone": "+5511999999999",
 *   "name": "João Silva",
 *   "utm_source": "google",
 *   "utm_medium": "cpc",
 *   "utm_campaign": "...",
 *   "gclid": "...",
 *   "fbclid": "...",
 *   "_ga": "...",
 *   "_fbp": "...",
 *   "_fbc": "..."
 * }
 */

// Remove CSP para tracking endpoint
header_remove('Content-Security-Policy');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-API-KEY, Idempotency-Key');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/rate_limiter.php';

// Only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Method not allowed'], 405);
}

// Rate limit
$identifier = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
checkRateLimit($identifier);

// Get API Key
$apiKey = $_SERVER['HTTP_X_API_KEY'] ?? $_POST['api_key'] ?? null;

if (!$apiKey) {
    json_response(['error' => 'API key required'], 401);
}

// Validate API Key
$db = getDB();
$stmt = $db->prepare("SELECT workspace_id FROM api_keys WHERE key_hash = ?");
$stmt->execute([hash('sha256', $apiKey)]);
$apiKeyRecord = $stmt->fetch();

if (!$apiKeyRecord) {
    json_response(['error' => 'Invalid API key'], 401);
}

$workspaceId = $apiKeyRecord['workspace_id'];

// Update last used
$stmt = $db->prepare("UPDATE api_keys SET last_used_at = NOW() WHERE key_hash = ?");
$stmt->execute([hash('sha256', $apiKey)]);

// Get payload
$body = file_get_contents('php://input');
$data = json_decode($body, true) ?? $_POST;

// Idempotency check
$idempotencyKey = $_SERVER['HTTP_IDEMPOTENCY_KEY'] ?? $data['idempotency_key'] ?? null;

if ($idempotencyKey) {
    $stmt = $db->prepare("
        SELECT id FROM events 
        WHERE workspace_id = ? AND idempotency_key = ? 
        AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
        LIMIT 1
    ");
    $stmt->execute([$workspaceId, $idempotencyKey]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        json_response([
            'ok' => true,
            'event_id' => $existing['id'],
            'deduped' => true
        ]);
    }
}

// Extract data
$eventType = $data['event'] ?? 'pageview';
$eventName = $data['event_name'] ?? null;
$pageUrl = $data['page_url'] ?? null;
$email = $data['email'] ?? null;
$phone = $data['phone'] ?? null;
$name = $data['name'] ?? null;

$utmSource = $data['utm_source'] ?? null;
$utmMedium = $data['utm_medium'] ?? null;
$utmCampaign = $data['utm_campaign'] ?? null;
$utmTerm = $data['utm_term'] ?? null;
$utmContent = $data['utm_content'] ?? null;

$gclid = $data['gclid'] ?? null;
$fbclid = $data['fbclid'] ?? null;
$ttclid = $data['ttclid'] ?? null;

$referrer = $data['referrer'] ?? $_SERVER['HTTP_REFERER'] ?? null;
$userAgent = $data['user_agent'] ?? $_SERVER['HTTP_USER_AGENT'] ?? null;
$ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? null;

// Get geolocation
$geoData = getGeolocationFromIP($ipAddress);
$country = $geoData['country'];
$region = $geoData['region'];
$city = $geoData['city'];

// Fingerprint para deduplicação
$fingerprint = hash('sha256', $ipAddress . $userAgent . ($email ?? ''));

// Find or create lead
$leadId = null;

if ($email || $phone) {
    // Try to find existing lead
    $stmt = $db->prepare("
        SELECT id FROM leads 
        WHERE workspace_id = ? AND (email = ? OR phone_number = ?)
        LIMIT 1
    ");
    $stmt->execute([$workspaceId, $email, $phone]);
    $lead = $stmt->fetch();
    
    if ($lead) {
        $leadId = $lead['id'];
        
        // Update last seen
        $stmt = $db->prepare("UPDATE leads SET last_seen = NOW() WHERE id = ?");
        $stmt->execute([$leadId]);
        
    } else {
        // Create new lead
        $stmt = $db->prepare("
            INSERT INTO leads (
                workspace_id, email, phone_number, name,
                utm_source, utm_medium, utm_campaign, utm_term, utm_content,
                gclid, fbclid, ttclid, referrer,
                first_seen, last_seen
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        $stmt->execute([
            $workspaceId, $email, $phone, $name,
            $utmSource, $utmMedium, $utmCampaign, $utmTerm, $utmContent,
            $gclid, $fbclid, $ttclid, $referrer
        ]);
        $leadId = $db->lastInsertId();
    }
}

// Create event
$stmt = $db->prepare("
    INSERT INTO events (
        workspace_id, lead_id, event_type, event_name,
        page_url, referrer,
        utm_source, utm_medium, utm_campaign, utm_term, utm_content,
        gclid, fbclid, ttclid,
        user_agent, ip_address, country, region, city,
        fingerprint, idempotency_key, raw_data
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");
$stmt->execute([
    $workspaceId, $leadId, $eventType, $eventName,
    $pageUrl, $referrer,
    $utmSource, $utmMedium, $utmCampaign, $utmTerm, $utmContent,
    $gclid, $fbclid, $ttclid,
    $userAgent, $ipAddress, $country, $region, $city,
    $fingerprint, $idempotencyKey, $body
]);
$eventId = $db->lastInsertId();

// Create attribution record (simple first touch)
if ($leadId && ($utmSource || $gclid || $fbclid)) {
    $channel = $utmSource ?? ($gclid ? 'google_ads' : ($fbclid ? 'facebook_ads' : 'unknown'));
    
    $stmt = $db->prepare("
        INSERT INTO attribution_records (
            workspace_id, lead_id, event_id, model, channel, campaign
        ) VALUES (?, ?, ?, 'first_touch', ?, ?)
    ");
    $stmt->execute([$workspaceId, $leadId, $eventId, $channel, $utmCampaign]);
}

// Queue jobs for adapters
$adapters = ['meta', 'ga4'];

foreach ($adapters as $adapter) {
    $payload = [
        'event_name' => $eventName ?? $eventType,
        'event_id' => $eventId,
        'user_data' => [
            'email' => $email,
            'phone' => $phone,
            'first_name' => $name,
            'ip' => $ipAddress,
            'user_agent' => $userAgent,
            'page_url' => $pageUrl,
            'fbp' => $data['_fbp'] ?? null,
            'fbc' => $data['_fbc'] ?? null
        ],
        'client_id' => $data['_ga'] ?? null,
        'params' => [
            'page_location' => $pageUrl,
            'page_referrer' => $referrer
        ]
    ];
    
    $stmt = $db->prepare("
        INSERT INTO jobs_log (workspace_id, event_id, job_type, adapter, payload)
        VALUES (?, ?, 'forward_event', ?, ?)
    ");
    $stmt->execute([$workspaceId, $eventId, $adapter, json_encode($payload)]);
}

logMessage('INFO', 'Event tracked', [
    'event_id' => $eventId,
    'lead_id' => $leadId,
    'event_type' => $eventType
]);

json_response([
    'ok' => true,
    'event_id' => $eventId,
    'lead_id' => $leadId,
    'deduped' => false
]);