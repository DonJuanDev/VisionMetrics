<?php
require_once __DIR__ . '/middleware.php';

$db = getDB();

// Get access logs (from events table as proxy)
$stmt = $db->prepare("
    SELECT e.*, l.name as lead_name, l.phone_number
    FROM events e
    LEFT JOIN leads l ON e.lead_id = l.id
    WHERE e.workspace_id = ?
    ORDER BY e.created_at DESC
    LIMIT 100
");
$stmt->execute([$currentWorkspace['id']]);
$accessLogs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acessos do Cliente - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="/frontend/css/style.css">
    <link rel="stylesheet" href="/frontend/css/sidebar.css">
</head>
<body>
    <div class="app-layout">
        <?php include __DIR__ . '/partials/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="top-bar">
                <div class="top-bar-title">
                    <h1>Acessos do Cliente</h1>
                    <p>Histórico de acessos e eventos rastreados</p>
                </div>
            </div>

            <div class="container">
                <div class="card">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Data/Hora</th>
                                    <th>Usuário</th>
                                    <th>Tipo de Evento</th>
                                    <th>Página/URL</th>
                                    <th>Origem</th>
                                    <th>Dispositivo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($accessLogs)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center">
                                            <div class="empty-state">
                                                <div class="empty-icon">📊</div>
                                                <h2>Nenhum acesso registrado</h2>
                                                <p>Os acessos aparecerão aqui automaticamente</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($accessLogs as $log): ?>
                                        <tr>
                                            <td>
                                                <div><?= date('d/m/Y', strtotime($log['created_at'])) ?></div>
                                                <div style="font-size: 12px; color: #6B7280;"><?= date('H:i:s', strtotime($log['created_at'])) ?></div>
                                            </td>
                                            <td>
                                                <?php if ($log['lead_name']): ?>
                                                    <div><?= htmlspecialchars($log['lead_name']) ?></div>
                                                    <div style="font-size: 12px; color: #6B7280;"><?= formatPhone($log['phone_number']) ?></div>
                                                <?php else: ?>
                                                    <span style="color: #9CA3AF;">Anônimo</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?= $log['event_type'] === 'purchase' ? 'success' : 'info' ?>">
                                                    <?= htmlspecialchars($log['event_type']) ?>
                                                </span>
                                            </td>
                                            <td class="truncate" style="max-width: 300px;"><?= htmlspecialchars($log['page_url']) ?></td>
                                            <td>
                                                <?php if ($log['utm_source']): ?>
                                                    <span class="badge badge-info"><?= htmlspecialchars($log['utm_source']) ?></span>
                                                <?php else: ?>
                                                    <span style="color: #9CA3AF;">Direct</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (strpos($log['user_agent'], 'Mobile') !== false): ?>
                                                    📱 Mobile
                                                <?php else: ?>
                                                    💻 Desktop
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="/frontend/js/app.js"></script>
</body>
</html>




