<?php
/**
 * VisionMetrics - Trackable Link Redirector (Shortener)
 * 
 * Features:
 * - First-touch cookie attribution (vm_first_touch, 365 days)
 * - UTM parameter capture from querystring and referer
 * - Click logging to database
 * - Queue job creation for server-side analytics (GA4, Meta)
 * - WhatsApp deeplink generation with token injection
 * - Rate limiting per IP
 * - Expiry checking
 * 
 * Usage: https://yourdomain.com/backend/l.php?slug=abc123 OR /r/abc123 (with routing)
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../src/bootstrap.php';

// ═══════════════════════════════════════════════════════════
// 1. EXTRACT SLUG
// ═══════════════════════════════════════════════════════════
$slug = $_GET['slug'] ?? '';
if (empty($slug)) {
    http_response_code(400);
    die('Link inválido - slug não fornecido');
}

$db = getDB();

// ═══════════════════════════════════════════════════════════
// 2. RATE LIMITING (prevent abuse)
// ═══════════════════════════════════════════════════════════
$clientIp = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$rateLimitMax = (int)(getenv('RATE_LIMIT_SHORTENER') ?: 30); // requests per minute

// Clean old rate limit logs (older than 1 minute)
$stmt = $db->prepare("DELETE FROM rate_limit_log WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 MINUTE)");
$stmt->execute();

// Check rate limit
$stmt = $db->prepare("
    SELECT COUNT(*) as count 
    FROM rate_limit_log 
    WHERE identifier = ? AND action = 'redirect' 
    AND created_at > DATE_SUB(NOW(), INTERVAL 1 MINUTE)
");
$stmt->execute([$clientIp]);
$rateLimitData = $stmt->fetch();

if ($rateLimitData['count'] >= $rateLimitMax) {
    http_response_code(429);
    header('Retry-After: 60');
    die('Too many requests - please wait a moment');
}

// Record this request
$stmt = $db->prepare("INSERT INTO rate_limit_log (identifier, action, created_at) VALUES (?, 'redirect', NOW())");
$stmt->execute([$clientIp]);

// ═══════════════════════════════════════════════════════════
// 3. FETCH LINK FROM DATABASE
// ═══════════════════════════════════════════════════════════
$stmt = $db->prepare("
    SELECT * FROM trackable_links 
    WHERE slug = ? OR short_code = ?
    LIMIT 1
");
$stmt->execute([$slug, $slug]);
$link = $stmt->fetch();

if (!$link) {
    http_response_code(404);
    die('Link não encontrado');
}

// Check if active
$isActive = $link['is_active'] ?? $link['active'] ?? true;
if (!$isActive) {
    http_response_code(410);
    die('Link desativado');
}

// Check if expired
if (!empty($link['expires_at'])) {
    $expiresAt = strtotime($link['expires_at']);
    if ($expiresAt < time()) {
        http_response_code(410);
        die('Link expirado');
    }
}

$workspaceId = $link['workspace_id'];
$linkId = $link['id'];

// ═══════════════════════════════════════════════════════════
// 4. EXTRACT UTM PARAMETERS
// ═══════════════════════════════════════════════════════════
function extractUtmParams($url) {
    $parsed = parse_url($url);
    if (empty($parsed['query'])) {
        return [];
    }
    parse_str($parsed['query'], $params);
    
    $utmParams = [];
    $utmKeys = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'gclid', 'fbclid', 'ttclid'];
    
    foreach ($utmKeys as $key) {
        if (!empty($params[$key])) {
            $utmParams[$key] = $params[$key];
        }
    }
    
    return $utmParams;
}

// Try to extract UTMs from current querystring first
$currentUrl = $_SERVER['REQUEST_URI'] ?? '';
$utmParams = extractUtmParams($currentUrl);

// Fallback to referer if no UTMs in querystring
if (empty($utmParams)) {
    $referrer = $_SERVER['HTTP_REFERER'] ?? '';
    if (!empty($referrer)) {
        $utmParams = extractUtmParams($referrer);
    }
}

// Fallback to link's own UTM configuration
$utmSource = $utmParams['utm_source'] ?? $link['utm_source'] ?? null;
$utmMedium = $utmParams['utm_medium'] ?? $link['utm_medium'] ?? null;
$utmCampaign = $utmParams['utm_campaign'] ?? $link['utm_campaign'] ?? null;
$utmTerm = $utmParams['utm_term'] ?? $link['utm_term'] ?? null;
$utmContent = $utmParams['utm_content'] ?? $link['utm_content'] ?? null;
$gclid = $utmParams['gclid'] ?? null;
$fbclid = $utmParams['fbclid'] ?? null;
$ttclid = $utmParams['ttclid'] ?? null;

// ═══════════════════════════════════════════════════════════
// 5. COOKIE MANAGEMENT (vm_first_touch)
// ═══════════════════════════════════════════════════════════
$cookieName = 'vm_first_touch';
$cookieToken = $_COOKIE[$cookieName] ?? null;

// If no cookie exists, create one
if (empty($cookieToken)) {
    // Generate UUID v4
    $cookieToken = sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
    
    // Set cookie for 365 days
    $cookieLifetimeDays = (int)(getenv('COOKIE_LIFETIME_DAYS') ?: 365);
    $cookieExpiry = time() + ($cookieLifetimeDays * 24 * 60 * 60);
    
    $cookiePath = '/';
    $cookieDomain = parse_url(APP_URL, PHP_URL_HOST) ?: '';
    $cookieSecure = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
    $cookieHttpOnly = false; // Allow JavaScript access for client-side tracking
    $cookieSameSite = 'Lax';
    
    setcookie(
        $cookieName, 
        $cookieToken, 
        [
            'expires' => $cookieExpiry,
            'path' => $cookiePath,
            'domain' => $cookieDomain,
            'secure' => $cookieSecure,
            'httponly' => $cookieHttpOnly,
            'samesite' => $cookieSameSite
        ]
    );
}

// ═══════════════════════════════════════════════════════════
// 6. CAPTURE CLIENT INFORMATION
// ═══════════════════════════════════════════════════════════
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$referrer = $_SERVER['HTTP_REFERER'] ?? '';

// Attempt geolocation (optional - uses helper from config.php)
$geo = ['country' => null, 'region' => null, 'city' => null];
if (function_exists('getGeolocationFromIP')) {
    $geo = getGeolocationFromIP($clientIp);
}

// ═══════════════════════════════════════════════════════════
// 7. TRY TO IDENTIFY LEAD
// ═══════════════════════════════════════════════════════════
$leadId = null;

// Try to find existing lead by cookie token
if (!empty($cookieToken)) {
    $stmt = $db->prepare("
        SELECT id FROM leads 
        WHERE workspace_id = ? AND first_touch_token = ? 
        LIMIT 1
    ");
    $stmt->execute([$workspaceId, $cookieToken]);
    $leadData = $stmt->fetch();
    
    if ($leadData) {
        $leadId = $leadData['id'];
        
        // Update last_seen
        $stmt = $db->prepare("UPDATE leads SET last_seen = NOW() WHERE id = ?");
        $stmt->execute([$leadId]);
    } else {
        // Create anonymous lead with first_touch_token
        $stmt = $db->prepare("
            INSERT INTO leads (
                workspace_id, first_touch_token, 
                utm_source, utm_medium, utm_campaign, utm_term, utm_content,
                gclid, fbclid, ttclid,
                referrer, stage, status, first_seen, last_seen
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'novo', 'active', NOW(), NOW())
        ");
        $stmt->execute([
            $workspaceId, $cookieToken,
            $utmSource, $utmMedium, $utmCampaign, $utmTerm, $utmContent,
            $gclid, $fbclid, $ttclid,
            $referrer
        ]);
        $leadId = $db->lastInsertId();
    }
}

// ═══════════════════════════════════════════════════════════
// 8. LOG CLICK TO DATABASE
// ═══════════════════════════════════════════════════════════
$stmt = $db->prepare("
    INSERT INTO link_clicks (
        trackable_link_id, workspace_id, cookie_token, lead_id,
        ip_address, user_agent, referrer,
        utm_source, utm_medium, utm_campaign, utm_term, utm_content,
        gclid, fbclid, ttclid,
        country, region, city,
        created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
");

$stmt->execute([
    $linkId, $workspaceId, $cookieToken, $leadId,
    $clientIp, $userAgent, $referrer,
    $utmSource, $utmMedium, $utmCampaign, $utmTerm, $utmContent,
    $gclid, $fbclid, $ttclid,
    $geo['country'], $geo['region'], $geo['city']
]);

$clickId = $db->lastInsertId();

// Update total clicks counter
$stmt = $db->prepare("
    UPDATE trackable_links 
    SET total_clicks = total_clicks + 1, clicks = clicks + 1 
    WHERE id = ?
");
$stmt->execute([$linkId]);

// ═══════════════════════════════════════════════════════════
// 9. CREATE QUEUE JOB FOR ANALYTICS
// ═══════════════════════════════════════════════════════════
$jobPayload = [
    'type' => 'click',
    'link_id' => $linkId,
    'click_id' => $clickId,
    'lead_id' => $leadId,
    'cookie_token' => $cookieToken,
    'utm' => [
        'source' => $utmSource,
        'medium' => $utmMedium,
        'campaign' => $utmCampaign,
        'term' => $utmTerm,
        'content' => $utmContent,
        'gclid' => $gclid,
        'fbclid' => $fbclid,
        'ttclid' => $ttclid
    ],
    'url' => $link['destination_url'],
    'ip' => $clientIp,
    'user_agent' => $userAgent,
    'timestamp' => time()
];

$stmt = $db->prepare("
    INSERT INTO queue_jobs (workspace_id, type, payload, status, attempts, next_run_at, created_at)
    VALUES (?, 'click', ?, 'pending', 0, NOW(), NOW())
");
$stmt->execute([$workspaceId, json_encode($jobPayload)]);

// ═══════════════════════════════════════════════════════════
// 10. BUILD DESTINATION URL
// ═══════════════════════════════════════════════════════════
$destination = $link['destination_url'];
$linkType = $link['type'] ?? 'redirect';

// If WhatsApp type, generate deeplink
if ($linkType === 'whatsapp' && !empty($link['whatsapp_phone'])) {
    $phone = preg_replace('/\D/', '', $link['whatsapp_phone']);
    
    // Build message with token injection
    $message = $link['whatsapp_message_template'] ?? '';
    if (!empty($message)) {
        // Inject vm_token at the end of message
        $message .= " vm_token:{$cookieToken}";
    } else {
        $message = "vm_token:{$cookieToken}";
    }
    
    // URL encode message
    $messageEncoded = urlencode($message);
    
    // Generate WhatsApp deeplink
    $destination = "https://wa.me/{$phone}?text={$messageEncoded}";
} else {
    // Regular redirect - append UTMs if configured
    $params = [];
    
    if ($utmSource) $params['utm_source'] = $utmSource;
    if ($utmMedium) $params['utm_medium'] = $utmMedium;
    if ($utmCampaign) $params['utm_campaign'] = $utmCampaign;
    if ($utmTerm) $params['utm_term'] = $utmTerm;
    if ($utmContent) $params['utm_content'] = $utmContent;
    
    if (!empty($params)) {
        $separator = strpos($destination, '?') !== false ? '&' : '?';
        $destination .= $separator . http_build_query($params);
    }
}

// ═══════════════════════════════════════════════════════════
// 11. LOG AND REDIRECT
// ═══════════════════════════════════════════════════════════
if (function_exists('logMessage')) {
    logMessage('INFO', 'Link redirect', [
        'slug' => $slug,
        'link_id' => $linkId,
        'click_id' => $clickId,
        'lead_id' => $leadId,
        'cookie_token' => $cookieToken,
        'type' => $linkType,
        'destination' => substr($destination, 0, 100)
    ]);
}

// Perform redirect
header("Location: $destination", true, 302);
exit;
