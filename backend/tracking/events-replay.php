<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../middleware.php';

// Only admin users can replay events
if ($currentUser['role'] !== 'owner' && $currentUser['role'] !== 'admin') {
    http_response_code(403);
    die('Access denied');
}

$error = '';
$success = '';
$stats = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    
    $action = $_POST['action'] ?? '';
    $eventIds = $_POST['event_ids'] ?? [];
    $status = $_POST['status'] ?? 'queued';
    $adapter = $_POST['adapter'] ?? 'all';
    
    if ($action === 'replay' && !empty($eventIds)) {
        try {
            $db = getDB();
            $replayed = 0;
            
            foreach ($eventIds as $eventId) {
                // Get event data
                $stmt = $db->prepare("
                    SELECT e.*, l.email, l.phone_number, l.name 
                    FROM events e 
                    LEFT JOIN leads l ON e.lead_id = l.id 
                    WHERE e.id = ? AND e.workspace_id = ?
                ");
                $stmt->execute([$eventId, $currentWorkspace['id']]);
                $event = $stmt->fetch();
                
                if (!$event) continue;
                
                // Determine adapters to replay
                $adapters = $adapter === 'all' ? ['meta', 'ga4', 'tiktok'] : [$adapter];
                
                foreach ($adapters as $adapterName) {
                    // Create new job
                    $payload = [
                        'event_name' => $event['event_name'] ?? $event['event_type'],
                        'event_id' => $event['id'],
                        'user_data' => [
                            'email' => $event['email'],
                            'phone' => $event['phone_number'],
                            'first_name' => $event['name'],
                            'ip' => $event['ip_address'],
                            'user_agent' => $event['user_agent'],
                            'page_url' => $event['page_url'],
                            'country' => $event['country'],
                            'region' => $event['region'],
                            'city' => $event['city']
                        ],
                        'params' => [
                            'page_location' => $event['page_url'],
                            'page_referrer' => $event['referrer'],
                            'utm_source' => $event['utm_source'],
                            'utm_medium' => $event['utm_medium'],
                            'utm_campaign' => $event['utm_campaign']
                        ]
                    ];
                    
                    $stmt = $db->prepare("
                        INSERT INTO jobs_log (workspace_id, event_id, job_type, adapter, payload, status)
                        VALUES (?, ?, 'replay_event', ?, ?, ?)
                    ");
                    $stmt->execute([
                        $currentWorkspace['id'], 
                        $event['id'], 
                        $adapterName, 
                        json_encode($payload), 
                        $status
                    ]);
                    
                    $replayed++;
                }
            }
            
            $success = "Replay iniciado para {$replayed} jobs. Verifique o status no worker.";
            
        } catch (Exception $e) {
            $error = 'Erro ao iniciar replay: ' . $e->getMessage();
        }
    }
}

// Get recent events for replay
try {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT e.id, e.event_type, e.event_name, e.page_url, e.created_at, 
               l.email, l.name, e.country, e.region, e.city
        FROM events e 
        LEFT JOIN leads l ON e.lead_id = l.id 
        WHERE e.workspace_id = ? 
        ORDER BY e.created_at DESC 
        LIMIT 100
    ");
    $stmt->execute([$currentWorkspace['id']]);
    $events = $stmt->fetchAll();
    
    // Get job stats
    $stmt = $db->prepare("
        SELECT adapter, status, COUNT(*) as count 
        FROM jobs_log 
        WHERE workspace_id = ? 
        GROUP BY adapter, status
    ");
    $stmt->execute([$currentWorkspace['id']]);
    $jobStats = $stmt->fetchAll();
    
} catch (Exception $e) {
    $error = 'Erro ao carregar dados: ' . $e->getMessage();
    $events = [];
    $jobStats = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Replay de Eventos - VisionMetrics</title>
    <link rel="stylesheet" href="/frontend/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../partials/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="page-header">
            <h1>Replay de Eventos</h1>
            <p>Reprocesse eventos para integrações</p>
        </div>
        
        <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <!-- Job Stats -->
        <div class="card">
            <h3>Status dos Jobs</h3>
            <div class="stats-grid">
                <?php foreach ($jobStats as $stat): ?>
                <div class="stat-item">
                    <span class="stat-label"><?= htmlspecialchars($stat['adapter']) ?> - <?= htmlspecialchars($stat['status']) ?></span>
                    <span class="stat-value"><?= $stat['count'] ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Replay Form -->
        <div class="card">
            <h3>Replay de Eventos</h3>
            <form method="POST" id="replayForm">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="replay">
                
                <div class="form-group">
                    <label>Eventos para Replay</label>
                    <div class="events-list" style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px;">
                        <?php foreach ($events as $event): ?>
                        <label class="event-item" style="display: block; padding: 5px; border-bottom: 1px solid #eee;">
                            <input type="checkbox" name="event_ids[]" value="<?= $event['id'] ?>" style="margin-right: 10px;">
                            <strong><?= htmlspecialchars($event['event_type']) ?></strong> - 
                            <?= htmlspecialchars($event['event_name'] ?: 'N/A') ?> - 
                            <?= htmlspecialchars($event['email'] ?: 'N/A') ?> - 
                            <?= date('d/m/Y H:i', strtotime($event['created_at'])) ?>
                            <?php if ($event['country']): ?>
                            - <?= htmlspecialchars($event['country']) ?>
                            <?php endif; ?>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Adapter</label>
                    <select name="adapter">
                        <option value="all">Todos</option>
                        <option value="meta">Meta/Facebook</option>
                        <option value="ga4">Google Analytics</option>
                        <option value="tiktok">TikTok</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Status Inicial</label>
                    <select name="status">
                        <option value="pending">Pending</option>
                        <option value="queued">Queued</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary">Iniciar Replay</button>
            </form>
        </div>
        
        <!-- Recent Events Table -->
        <div class="card">
            <h3>Eventos Recentes</h3>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tipo</th>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Localização</th>
                            <th>Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($events, 0, 20) as $event): ?>
                        <tr>
                            <td><?= $event['id'] ?></td>
                            <td><?= htmlspecialchars($event['event_type']) ?></td>
                            <td><?= htmlspecialchars($event['event_name'] ?: 'N/A') ?></td>
                            <td><?= htmlspecialchars($event['email'] ?: 'N/A') ?></td>
                            <td>
                                <?php if ($event['city'] && $event['country']): ?>
                                    <?= htmlspecialchars($event['city']) ?>, <?= htmlspecialchars($event['country']) ?>
                                <?php else: ?>
                                    N/A
                                <?php endif; ?>
                            </td>
                            <td><?= date('d/m/Y H:i', strtotime($event['created_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <script>
        // Select all checkbox
        document.addEventListener('DOMContentLoaded', function() {
            const selectAllBtn = document.createElement('button');
            selectAllBtn.type = 'button';
            selectAllBtn.textContent = 'Selecionar Todos';
            selectAllBtn.className = 'btn btn-secondary';
            selectAllBtn.style.marginBottom = '10px';
            
            const eventsList = document.querySelector('.events-list');
            eventsList.parentNode.insertBefore(selectAllBtn, eventsList);
            
            selectAllBtn.addEventListener('click', function() {
                const checkboxes = document.querySelectorAll('input[name="event_ids[]"]');
                const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                
                checkboxes.forEach(cb => cb.checked = !allChecked);
                selectAllBtn.textContent = allChecked ? 'Selecionar Todos' : 'Desmarcar Todos';
            });
        });
    </script>
</body>
</html>
