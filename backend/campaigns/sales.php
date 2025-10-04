<?php
require_once __DIR__ . '/../middleware.php';

$db = getDB();

// Get sales stats
$stmt = $db->prepare("
    SELECT 
        COUNT(*) as total_sales,
        SUM(sale_value) as total_revenue,
        AVG(sale_value) as average_ticket
    FROM sales 
    WHERE workspace_id = ? AND status = 'confirmed'
");
$stmt->execute([$currentWorkspace['id']]);
$stats = $stmt->fetch();

// Get sales list
$stmt = $db->prepare("
    SELECT s.*, l.name as lead_name, l.phone_number, l.email,
           c.utm_source, c.utm_medium, c.utm_campaign
    FROM sales s
    LEFT JOIN leads l ON s.lead_id = l.id
    LEFT JOIN conversations c ON s.conversation_id = c.id
    WHERE s.workspace_id = ?
    ORDER BY s.created_at DESC
    LIMIT 100
");
$stmt->execute([$currentWorkspace['id']]);
$sales = $stmt->fetchAll();

// Sales by source
$stmt = $db->prepare("
    SELECT 
        COALESCE(utm_source, 'direct') as source,
        COUNT(*) as count,
        SUM(sale_value) as revenue
    FROM sales
    WHERE workspace_id = ? AND status = 'confirmed'
    GROUP BY utm_source
    ORDER BY revenue DESC
");
$stmt->execute([$currentWorkspace['id']]);
$salesBySource = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendas - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="/frontend/css/style.css">
    <link rel="stylesheet" href="/frontend/css/sidebar.css">
</head>
<body>
    <div class="app-layout">
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="top-bar">
                <div class="top-bar-title">
                    <h1>💰 Vendas</h1>
                    <p>Acompanhe todas as vendas identificadas automaticamente</p>
                </div>
            </div>

            <div class="container">

        <div class="stats-grid">
            <div class="stat-card stat-success">
                <div class="stat-icon">✅</div>
                <div class="stat-value"><?= number_format($stats['total_sales']) ?></div>
                <div class="stat-label">Total de Vendas</div>
            </div>
            <div class="stat-card stat-primary">
                <div class="stat-icon">💰</div>
                <div class="stat-value"><?= formatCurrency($stats['total_revenue']) ?></div>
                <div class="stat-label">Faturamento Total</div>
            </div>
            <div class="stat-card stat-info">
                <div class="stat-icon">📊</div>
                <div class="stat-value"><?= formatCurrency($stats['average_ticket']) ?></div>
                <div class="stat-label">Ticket Médio</div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>Vendas por Origem</h2>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Origem</th>
                            <th>Quantidade</th>
                            <th>Faturamento</th>
                            <th>Ticket Médio</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($salesBySource as $row): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($row['source']) ?></strong></td>
                                <td><?= number_format($row['count']) ?> vendas</td>
                                <td class="text-success"><strong><?= formatCurrency($row['revenue']) ?></strong></td>
                                <td><?= formatCurrency($row['revenue'] / $row['count']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>Histórico de Vendas</h2>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Lead</th>
                            <th>Contato</th>
                            <th>Produto</th>
                            <th>Valor</th>
                            <th>Status</th>
                            <th>Origem</th>
                            <th>Detecção</th>
                            <th>Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($sales)): ?>
                            <tr>
                                <td colspan="9" class="text-center">
                                    Nenhuma venda registrada ainda. As vendas são identificadas automaticamente nas conversas do WhatsApp!
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($sales as $sale): ?>
                                <tr>
                                    <td>#<?= $sale['id'] ?></td>
                                    <td><strong><?= htmlspecialchars($sale['lead_name'] ?? 'N/A') ?></strong></td>
                                    <td>
                                        <?php if ($sale['phone_number']): ?>
                                            <?= formatPhone($sale['phone_number']) ?><br>
                                        <?php endif; ?>
                                        <?php if ($sale['email']): ?>
                                            <small class="text-muted"><?= htmlspecialchars($sale['email']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($sale['product_name'] ?? 'N/A') ?></td>
                                    <td class="text-success"><strong><?= formatCurrency($sale['sale_value']) ?></strong></td>
                                    <td>
                                        <span class="badge badge-<?= $sale['status'] === 'confirmed' ? 'success' : ($sale['status'] === 'pending' ? 'warning' : 'danger') ?>">
                                            <?= ucfirst($sale['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($sale['utm_source']): ?>
                                            <strong><?= htmlspecialchars($sale['utm_source']) ?></strong><br>
                                            <small class="text-muted"><?= htmlspecialchars($sale['utm_campaign'] ?? '') ?></small>
                                        <?php else: ?>
                                            <span class="text-muted">Direct</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($sale['detected_automatically']): ?>
                                            <span class="badge badge-info" title="Detectada automaticamente">🤖 Auto</span>
                                        <?php else: ?>
                                            <span class="badge" title="Manual">👤 Manual</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('d/m/Y H:i', strtotime($sale['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            </div>
        </div>
    </div>

    <script src="/frontend/js/app.js"></script>
</body>
</html>






