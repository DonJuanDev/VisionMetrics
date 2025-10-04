<?php
// Server-Sent Events endpoint for real-time dashboard
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('X-Accel-Buffering: no'); // Nginx

require_once __DIR__ . '/config.php';

// Check auth
session_start();
if (!isset($_SESSION['user_id'])) {
    echo "event: error\n";
    echo "data: {\"message\": \"Unauthorized\"}\n\n";
    exit;
}

$userId = $_SESSION['user_id'];
$workspaceId = $_SESSION['workspace_id'] ?? null;

if (!$workspaceId) {
    echo "event: error\n";
    echo "data: {\"message\": \"No workspace selected\"}\n\n";
    exit;
}

$db = getDB();

// Keep connection alive and send updates
while (true) {
    // Get real-time stats
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM leads WHERE workspace_id = ?");
    $stmt->execute([$workspaceId]);
    $totalLeads = $stmt->fetch()['count'];
    
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM leads WHERE workspace_id = ? AND DATE(created_at) = CURDATE()");
    $stmt->execute([$workspaceId]);
    $leadsToday = $stmt->fetch()['count'];
    
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM sales WHERE workspace_id = ? AND status = 'confirmed'");
    $stmt->execute([$workspaceId]);
    $totalSales = $stmt->fetch()['count'];
    
    $stmt = $db->prepare("SELECT COALESCE(SUM(sale_value), 0) as total FROM sales WHERE workspace_id = ? AND status = 'confirmed'");
    $stmt->execute([$workspaceId]);
    $totalRevenue = $stmt->fetch()['total'];
    
    // Recent notifications
    $stmt = $db->prepare("
        SELECT * FROM notifications 
        WHERE workspace_id = ? AND user_id = ? AND is_read = FALSE 
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    $stmt->execute([$workspaceId, $userId]);
    $notifications = $stmt->fetchAll();
    
    // Send data
    $data = [
        'totalLeads' => (int)$totalLeads,
        'leadsToday' => (int)$leadsToday,
        'totalSales' => (int)$totalSales,
        'totalRevenue' => (float)$totalRevenue,
        'notifications' => $notifications,
        'timestamp' => time()
    ];
    
    echo "event: stats\n";
    echo "data: " . json_encode($data) . "\n\n";
    
    ob_flush();
    flush();
    
    // Wait 5 seconds before next update
    sleep(5);
    
    // Check if connection is still alive
    if (connection_aborted()) {
        break;
    }
}





