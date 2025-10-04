<?php
require_once __DIR__ . '/../middleware.php';

$db = getDB();

// Get date range
$startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
$endDate = $_GET['end_date'] ?? date('Y-m-d');
$channel = $_GET['channel'] ?? 'all';

// Build query
$query = "
    SELECT 
        date,
        channel,
        campaign,
        SUM(CASE WHEN metric_type = 'conversations' THEN value ELSE 0 END) as conversations,
        SUM(CASE WHEN metric_type = 'sales' THEN value ELSE 0 END) as sales,
        SUM(CASE WHEN metric_type = 'events' THEN value ELSE 0 END) as events,
        SUM(CASE WHEN metric_type = 'unique_leads' THEN value ELSE 0 END) as unique_leads,
        AVG(CASE WHEN metric_type = 'roas' THEN value ELSE NULL END) as avg_roas,
        AVG(CASE WHEN metric_type = 'cpa' THEN value ELSE NULL END) as avg_cpa
    FROM metrics_daily 
    WHERE workspace_id = ? 
    AND date BETWEEN ? AND ?
";

$params = [$currentWorkspace['id'], $startDate, $endDate];

if ($channel !== 'all') {
    $query .= " AND channel = ?";
    $params[] = $channel;
}

$query .= " GROUP BY date, channel, campaign ORDER BY date DESC, conversations DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$metrics = $stmt->fetchAll();

// Get channel summary
$stmt = $db->prepare("
    SELECT 
        channel,
        SUM(CASE WHEN metric_type = 'conversations' THEN value ELSE 0 END) as total_conversations,
        SUM(CASE WHEN metric_type = 'sales' THEN value ELSE 0 END) as total_sales,
        AVG(CASE WHEN metric_type = 'roas' THEN value ELSE NULL END) as avg_roas,
        AVG(CASE WHEN metric_type = 'cpa' THEN value ELSE NULL END) as avg_cpa
    FROM metrics_daily 
    WHERE workspace_id = ? AND date BETWEEN ? AND ?
    GROUP BY channel
    ORDER BY total_conversations DESC
");
$stmt->execute([$currentWorkspace['id'], $startDate, $endDate]);
$channelSummary = $stmt->fetchAll();

// Get available channels
$stmt = $db->prepare("
    SELECT DISTINCT channel 
    FROM metrics_daily 
    WHERE workspace_id = ? 
    ORDER BY channel
");
$stmt->execute([$currentWorkspace['id']]);
$availableChannels = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Métricas de Performance - VisionMetrics</title>
    <link rel="stylesheet" href="/frontend/css/style.css">
    <link rel="stylesheet" href="/frontend/css/sidebar.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include __DIR__ . '/../partials/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="page-header">
            <h1>📊 Métricas de Performance</h1>
            <p>ROAS, CPA e análise de canais</p>
        </div>
        
        <!-- Filters -->
        <div class="card">
            <div class="card-body">
                <form method="GET" style="display: flex; gap: 16px; align-items: end; flex-wrap: wrap;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>Data Inicial</label>
                        <input type="date" name="start_date" value="<?= htmlspecialchars($startDate) ?>" required>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>Data Final</label>
                        <input type="date" name="end_date" value="<?= htmlspecialchars($endDate) ?>" required>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>Canal</label>
                        <select name="channel">
                            <option value="all">Todos os Canais</option>
                            <?php foreach ($availableChannels as $ch): ?>
                                <option value="<?= htmlspecialchars($ch['channel']) ?>" 
                                        <?= $channel === $ch['channel'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $ch['channel']))) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Filtrar</button>
                </form>
            </div>
        </div>
        
        <!-- Channel Summary -->
        <div class="card">
            <div class="card-header">
                <h2>Resumo por Canal</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Canal</th>
                                <th>Conversas</th>
                                <th>Vendas</th>
                                <th>Taxa Conversão</th>
                                <th>ROAS Médio</th>
                                <th>CPA Médio</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($channelSummary as $summary): ?>
                                <tr>
                                    <td>
                                        <span class="badge badge-primary">
                                            <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $summary['channel']))) ?>
                                        </span>
                                    </td>
                                    <td><?= number_format($summary['total_conversations']) ?></td>
                                    <td><?= number_format($summary['total_sales']) ?></td>
                                    <td>
                                        <?php 
                                        $conversionRate = $summary['total_conversations'] > 0 
                                            ? ($summary['total_sales'] / $summary['total_conversations']) * 100 
                                            : 0;
                                        echo number_format($conversionRate, 1) . '%';
                                        ?>
                                    </td>
                                    <td>
                                        <?php if ($summary['avg_roas']): ?>
                                            <span class="badge badge-success">
                                                <?= number_format($summary['avg_roas'], 2) ?>x
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($summary['avg_cpa']): ?>
                                            $<?= number_format($summary['avg_cpa'], 2) ?>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Charts -->
        <div class="dashboard-grid">
            <div class="card">
                <div class="card-header">
                    <h3>Conversas por Dia</h3>
                </div>
                <div class="card-body">
                    <canvas id="conversationsChart" width="400" height="200"></canvas>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3>ROAS por Canal</h3>
                </div>
                <div class="card-body">
                    <canvas id="roasChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Detailed Metrics Table -->
        <div class="card">
            <div class="card-header">
                <h2>Métricas Detalhadas</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Canal</th>
                                <th>Campanha</th>
                                <th>Conversas</th>
                                <th>Vendas</th>
                                <th>Eventos</th>
                                <th>Leads Únicos</th>
                                <th>ROAS</th>
                                <th>CPA</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($metrics as $metric): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($metric['date'])) ?></td>
                                    <td>
                                        <span class="badge badge-secondary">
                                            <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $metric['channel']))) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($metric['campaign'] ?: 'N/A') ?></td>
                                    <td><?= number_format($metric['conversations']) ?></td>
                                    <td><?= number_format($metric['sales']) ?></td>
                                    <td><?= number_format($metric['events']) ?></td>
                                    <td><?= number_format($metric['unique_leads']) ?></td>
                                    <td>
                                        <?php if ($metric['avg_roas']): ?>
                                            <span class="badge badge-success">
                                                <?= number_format($metric['avg_roas'], 2) ?>x
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($metric['avg_cpa']): ?>
                                            $<?= number_format($metric['avg_cpa'], 2) ?>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Prepare data for charts
        const metricsData = <?= json_encode($metrics) ?>;
        const channelSummary = <?= json_encode($channelSummary) ?>;
        
        // Conversations Chart
        const conversationsCtx = document.getElementById('conversationsChart').getContext('2d');
        const conversationsByDate = {};
        
        metricsData.forEach(metric => {
            if (!conversationsByDate[metric.date]) {
                conversationsByDate[metric.date] = 0;
            }
            conversationsByDate[metric.date] += parseInt(metric.conversations);
        });
        
        new Chart(conversationsCtx, {
            type: 'line',
            data: {
                labels: Object.keys(conversationsByDate).sort(),
                datasets: [{
                    label: 'Conversas',
                    data: Object.values(conversationsByDate),
                    borderColor: '#3B82F6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        
        // ROAS Chart
        const roasCtx = document.getElementById('roasChart').getContext('2d');
        new Chart(roasCtx, {
            type: 'bar',
            data: {
                labels: channelSummary.map(c => c.channel.replace('_', ' ').toUpperCase()),
                datasets: [{
                    label: 'ROAS',
                    data: channelSummary.map(c => c.avg_roas || 0),
                    backgroundColor: [
                        '#3B82F6',
                        '#10B981',
                        '#F59E0B',
                        '#EF4444',
                        '#8B5CF6'
                    ]
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>
