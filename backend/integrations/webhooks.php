<?php
require_once __DIR__ . '/../middleware.php';

$db = getDB();

// Get webhook fires from jobs_log
$stmt = $db->prepare("
    SELECT jl.*, e.event_type
    FROM jobs_log jl
    LEFT JOIN events e ON jl.event_id = e.id
    WHERE jl.workspace_id = ? AND jl.job_type = 'webhook'
    ORDER BY jl.created_at DESC
    LIMIT 100
");
$stmt->execute([$currentWorkspace['id']]);
$webhookFires = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Disparos de Webhook - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="/frontend/css/style.css">
    <link rel="stylesheet" href="/frontend/css/sidebar.css">
</head>
<body>
    <div class="app-layout">
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="top-bar">
                <div class="top-bar-title">
                    <h1>Disparos de Webhook</h1>
                    <p>Histórico de webhooks enviados</p>
                </div>
            </div>

            <div class="container">
                <div class="card" style="background: #EFF6FF; border-left: 4px solid #3B82F6; margin-bottom: 20px;">
                    <div class="card-body">
                        <div style="display: flex; align-items: flex-start; gap: 12px;">
                            <svg width="20" height="20" fill="#3B82F6" viewBox="0 0 24 24">
                                <path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div>
                                <strong style="color: #1E40AF;">Dúvidas sobre Disparos de Webhook?</strong>
                                <p style="margin: 4px 0 0; font-size: 14px; color: #1E40AF;">
                                    🎥 <a href="#" style="color: #3B82F6; font-weight: 600;">Saiba mais no vídeo</a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Data de Disparo</th>
                                    <th>Evento</th>
                                    <th>Status de Processamento</th>
                                    <th>Quantidade de Tentativas</th>
                                    <th>Retorno</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($webhookFires)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center">
                                            <div class="empty-state">
                                                <div class="empty-icon">🔗</div>
                                                <h2>Nenhum webhook disparado</h2>
                                                <p>Configure webhooks nas suas integrações</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($webhookFires as $fire): ?>
                                        <tr>
                                            <td><?= date('d/m/Y H:i:s', strtotime($fire['created_at'])) ?></td>
                                            <td><?= htmlspecialchars($fire['event_type'] ?? 'nova mensagem') ?></td>
                                            <td>
                                                <?php if ($fire['status'] === 'completed'): ?>
                                                    <span class="badge badge-success">processado com sucesso</span>
                                                <?php elseif ($fire['status'] === 'failed'): ?>
                                                    <span class="badge badge-danger">Erro</span>
                                                <?php else: ?>
                                                    <span class="badge badge-warning">Pendente</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= $fire['retry_count'] ?></td>
                                            <td>
                                                <?php if ($fire['status'] === 'completed'): ?>
                                                    <span style="color: #10B981;">✓</span>
                                                <?php else: ?>
                                                    <span style="color: #6B7280;">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-secondary" title="Ver payload">👁️</button>
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




