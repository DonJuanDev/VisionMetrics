<?php
// Real-time dashboard stats API endpoint
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');
header('Cache-Control: no-cache');

if (!isset($_SESSION['workspace_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$workspaceId = $_SESSION['workspace_id'];
$db = getDB();

// Get stats
$stmt = $db->prepare("SELECT COUNT(*) as total FROM conversations WHERE workspace_id = ?");
$stmt->execute([$workspaceId]);
$total = $stmt->fetch()['total'];

$stmt = $db->prepare("SELECT COUNT(*) as total FROM conversations WHERE workspace_id = ? AND (utm_source LIKE '%meta%' OR utm_source LIKE '%facebook%' OR fbclid IS NOT NULL)");
$stmt->execute([$workspaceId]);
$meta = $stmt->fetch()['total'];

$stmt = $db->prepare("SELECT COUNT(*) as total FROM conversations WHERE workspace_id = ? AND (utm_source LIKE '%google%' OR gclid IS NOT NULL)");
$stmt->execute([$workspaceId]);
$google = $stmt->fetch()['total'];

$stmt = $db->prepare("SELECT COUNT(*) as total FROM conversations WHERE workspace_id = ? AND utm_source IS NOT NULL AND utm_source NOT LIKE '%meta%' AND utm_source NOT LIKE '%facebook%' AND utm_source NOT LIKE '%google%'");
$stmt->execute([$workspaceId]);
$outras = $stmt->fetch()['total'];

$stmt = $db->prepare("SELECT COUNT(*) as total FROM conversations WHERE workspace_id = ? AND utm_source IS NULL");
$stmt->execute([$workspaceId]);
$naoRastreada = $stmt->fetch()['total'];

echo json_encode([
    'total' => (int)$total,
    'meta' => (int)$meta,
    'google' => (int)$google,
    'outras' => (int)$outras,
    'nao_rastreada' => (int)$naoRastreada,
    'timestamp' => time()
]);
exit;





