<?php
/**
 * Health Check Endpoint
 * Para Docker health checks e monitoramento
 */

require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/db.php';

header('Content-Type: application/json');

$health = [
    'status' => 'healthy',
    'timestamp' => date('c'),
    'checks' => []
];

// Check Database
try {
    $db = getDB();
    $stmt = $db->query("SELECT 1");
    $health['checks']['database'] = 'ok';
} catch (Exception $e) {
    $health['status'] = 'unhealthy';
    $health['checks']['database'] = 'error: ' . $e->getMessage();
}

// Check Redis
try {
    $redis = getRedis();
    if ($redis && $redis->ping()) {
        $health['checks']['redis'] = 'ok';
    } else {
        $health['checks']['redis'] = 'unavailable';
    }
} catch (Exception $e) {
    $health['checks']['redis'] = 'error: ' . $e->getMessage();
}

// Response
http_response_code($health['status'] === 'healthy' ? 200 : 503);
echo json_encode($health, JSON_PRETTY_PRINT);