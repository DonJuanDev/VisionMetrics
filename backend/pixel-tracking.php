<?php
require_once __DIR__ . '/middleware.php';

$db = getDB();

// Get pixel fires/events from jobs_log
$stmt = $db->prepare("
    SELECT jl.*, 
           e.event_type,
           e.utm_source
    FROM jobs_log jl
    LEFT JOIN events e ON jl.event_id = e.id
    WHERE jl.workspace_id = ? AND jl.job_type IN ('meta_conversion', 'ga4_event')
    ORDER BY jl.created_at DESC
    LIMIT 100
");
$stmt->execute([$currentWorkspace['id']]);
$pixelFires = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Disparos de Pixel - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="/frontend/css/style.css">
    <link rel="stylesheet" href="/frontend/css/sidebar.css">
</head>
<body>
    <div class="app-layout">
        <?php include __DIR__ . '/partials/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="top-bar">
                <div class="top-bar-title">
                    <h1>Disparos de Pixel</h1>
                    <p>Histórico de envios para Meta Ads e Google Ads</p>
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
                                <strong style="color: #1E40AF;">Dúvidas sobre Disparos de Pixel?</strong>
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
                                    <th>Conversa</th>
                                    <th>Retorno</th>
                                    <th>Etapa da Jornada</th>
                                    <th>Evento da Plataforma</th>
                                    <th>Plataforma</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($pixelFires)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center">
                                            <div class="empty-state">
                                                <div class="empty-icon">📡</div>
                                                <h2>Nenhum disparo registrado</h2>
                                                <p>Conecte Meta Ads ou GA4 para começar a enviar eventos</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($pixelFires as $fire): ?>
                                        <tr>
                                            <td>
                                                <div><?= date('d/m/Y H:i:s', strtotime($fire['created_at'])) ?></div>
                                            </td>
                                            <td>
                                                <div style="font-size: 13px; color: #111827;">
                                                    <?php 
                                                    $payload = json_decode($fire['payload'], true);
                                                    echo htmlspecialchars($payload['phone'] ?? 'N/A');
                                                    ?>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if ($fire['status'] === 'completed'): ?>
                                                    <span class="badge badge-success">✓ Processado com sucesso</span>
                                                <?php elseif ($fire['status'] === 'failed'): ?>
                                                    <span class="badge badge-danger">✕ Erro</span>
                                                <?php else: ?>
                                                    <span class="badge badge-warning">⏳ Pendente</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span style="color: <?= $fire['event_type'] === 'purchase' ? '#10B981' : '#F59E0B' ?>; font-weight: 600; font-size: 13px;">
                                                    <?= $fire['event_type'] === 'purchase' ? 'Comprou' : 'LEAD QUALIFICADO (SOLICITOU ORÇAMENTO)' ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div style="display: flex; align-items: center; gap: 6px;">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="#3B82F6">
                                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"/>
                                                    </svg>
                                                    <span class="badge badge-info" style="font-size: 11px;">
                                                        <?= $fire['job_type'] === 'meta_conversion' ? 'Purchase' : 'Lead' ?>
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                <span style="font-weight: 500; font-size: 13px;">
                                                    <?= $fire['job_type'] === 'meta_conversion' ? 'Meta Ads' : 'Google Ads' ?>
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-secondary" title="Ver detalhes">👁️</button>
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




