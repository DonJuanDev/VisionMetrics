<?php
/**
 * Health Check Endpoint
 * Returns system status for monitoring and load balancers
 */

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

$health = [
    'status' => 'healthy',
    'timestamp' => date('c'),
    'version' => '1.0.0',
    'checks' => []
];

// Database check
try {
    require_once __DIR__ . '/config.php';
    $db = getDB();
    $stmt = $db->prepare("SELECT 1");
    $stmt->execute();
    $health['checks']['database'] = [
        'status' => 'healthy',
        'response_time' => 0
    ];
} catch (Exception $e) {
    $health['status'] = 'unhealthy';
    $health['checks']['database'] = [
        'status' => 'unhealthy',
        'error' => $e->getMessage()
    ];
}

// Redis check (if available)
try {
    if (class_exists('Redis')) {
        $redis = new Redis();
        $redis->connect('redis', 6379);
        $redis->ping();
        $health['checks']['redis'] = [
            'status' => 'healthy',
            'response_time' => 0
        ];
    } else {
        $health['checks']['redis'] = [
            'status' => 'not_configured'
        ];
    }
} catch (Exception $e) {
    $health['checks']['redis'] = [
        'status' => 'unhealthy',
        'error' => $e->getMessage()
    ];
}

// Disk space check
$diskFree = disk_free_space('/');
$diskTotal = disk_total_space('/');
$diskUsage = (($diskTotal - $diskFree) / $diskTotal) * 100;

$health['checks']['disk'] = [
    'status' => $diskUsage > 90 ? 'warning' : 'healthy',
    'usage_percent' => round($diskUsage, 2),
    'free_bytes' => $diskFree,
    'total_bytes' => $diskTotal
];

// Memory check
$memoryUsage = memory_get_usage(true);
$memoryPeak = memory_get_peak_usage(true);
$memoryLimit = ini_get('memory_limit');

$health['checks']['memory'] = [
    'status' => 'healthy',
    'current_usage' => $memoryUsage,
    'peak_usage' => $memoryPeak,
    'limit' => $memoryLimit
];

// Worker queue check
try {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT 
            COUNT(*) as pending,
            COUNT(CASE WHEN status = 'failed' THEN 1 END) as failed,
            COUNT(CASE WHEN created_at < DATE_SUB(NOW(), INTERVAL 1 HOUR) AND status = 'pending' THEN 1 END) as stuck
        FROM jobs_log 
        WHERE status IN ('pending', 'queued')
    ");
    $stmt->execute();
    $queueStats = $stmt->fetch();
    
    $health['checks']['queue'] = [
        'status' => $queueStats['stuck'] > 10 ? 'warning' : 'healthy',
        'pending_jobs' => (int)$queueStats['pending'],
        'failed_jobs' => (int)$queueStats['failed'],
        'stuck_jobs' => (int)$queueStats['stuck']
    ];
} catch (Exception $e) {
    $health['checks']['queue'] = [
        'status' => 'unhealthy',
        'error' => $e->getMessage()
    ];
}

// Overall status determination
$unhealthyChecks = array_filter($health['checks'], function($check) {
    return $check['status'] === 'unhealthy';
});

if (!empty($unhealthyChecks)) {
    $health['status'] = 'unhealthy';
} elseif (array_filter($health['checks'], function($check) {
    return $check['status'] === 'warning';
})) {
    $health['status'] = 'degraded';
}

// Set HTTP status code
if ($health['status'] === 'unhealthy') {
    http_response_code(503);
} elseif ($health['status'] === 'degraded') {
    http_response_code(200); // Still operational
} else {
    http_response_code(200);
}

echo json_encode($health, JSON_PRETTY_PRINT);