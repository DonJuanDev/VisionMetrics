<?php
/**
 * QR Code Generator for Trackable Links
 * Uses Google Charts API (free, no registration needed)
 */

require_once __DIR__ . '/config.php';

$linkId = $_GET['link_id'] ?? null;
$size = $_GET['size'] ?? 300;
$format = $_GET['format'] ?? 'png';

if (!$linkId) {
    die('Link ID required');
}

// Get link from database
$db = getDB();
$stmt = $db->prepare("SELECT * FROM trackable_links WHERE id = ?");
$stmt->execute([$linkId]);
$link = $stmt->fetch();

if (!$link) {
    die('Link not found');
}

// Build full URL
$fullUrl = APP_URL . '/l/' . $link['slug'];

// Generate QR Code using Google Charts API
$qrUrl = 'https://chart.googleapis.com/chart?' . http_build_query([
    'cht' => 'qr',
    'chs' => $size . 'x' . $size,
    'chl' => $fullUrl,
    'choe' => 'UTF-8'
]);

// If download format, proxy and serve
if ($format === 'download') {
    $qrImage = file_get_contents($qrUrl);
    
    header('Content-Type: image/png');
    header('Content-Disposition: attachment; filename="qrcode_' . $link['slug'] . '.png"');
    echo $qrImage;
    exit;
}

// Otherwise redirect to Google's URL
header("Location: $qrUrl");
exit;






