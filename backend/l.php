<?php
// Trackable link redirector
require_once __DIR__ . '/config.php';

$slug = $_GET['slug'] ?? '';
if (empty($slug)) {
    die('Link inválido');
}

$db = getDB();

// Get link
$stmt = $db->prepare("SELECT * FROM trackable_links WHERE slug = ? AND is_active = 1");
$stmt->execute([$slug]);
$link = $stmt->fetch();

if (!$link) {
    die('Link não encontrado ou inativo');
}

// Log click
$ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$referrer = $_SERVER['HTTP_REFERER'] ?? '';

$stmt = $db->prepare("INSERT INTO link_clicks (trackable_link_id, ip_address, user_agent, referrer) VALUES (?, ?, ?, ?)");
$stmt->execute([$link['id'], $ip, $userAgent, $referrer]);

// Update total clicks
$stmt = $db->prepare("UPDATE trackable_links SET total_clicks = total_clicks + 1 WHERE id = ?");
$stmt->execute([$link['id']]);

// Build destination URL with UTMs
$destination = $link['destination_url'];
$params = [];

if ($link['utm_source']) $params['utm_source'] = $link['utm_source'];
if ($link['utm_medium']) $params['utm_medium'] = $link['utm_medium'];
if ($link['utm_campaign']) $params['utm_campaign'] = $link['utm_campaign'];
if ($link['utm_term']) $params['utm_term'] = $link['utm_term'];
if ($link['utm_content']) $params['utm_content'] = $link['utm_content'];

if (!empty($params)) {
    $separator = strpos($destination, '?') !== false ? '&' : '?';
    $destination .= $separator . http_build_query($params);
}

// Redirect
header("Location: $destination");
exit;






