<?php
require_once __DIR__ . '/../middleware.php';

$db = getDB();

// Get journey stages with events  
$journeyStages = [
    [
        'stage_name' => 'Fez Contato',
        'stage_order' => 1,
        'events' => '-',
        'count' => 0
    ],
    [
        'stage_name' => 'NÃO QUALIFICADO',
        'stage_order' => 2,
        'events' => '-',
        'count' => 0
    ],
    [
        'stage_name' => 'LEAD QUALIFICADO (SOLICITOU ORÇAMENTO)',
        'stage_order' => 3,
        'events' => 'Lead Qualificado, Lead',
        'count' => 0
    ],
    [
        'stage_name' => 'Comprou',
        'stage_order' => 4,
        'events' => 'Comprador, Purchase',
        'count' => 0
    ]
];

// Count for each stage
$stmt = $db->prepare("SELECT COUNT(*) as total FROM conversations WHERE workspace_id = ?");
$stmt->execute([$currentWorkspace['id']]);
$journeyStages[0]['count'] = $stmt->fetch()['total'];

$stmt = $db->prepare("SELECT COUNT(*) as total FROM conversations WHERE workspace_id = ? AND is_sale = 0 AND journey_stage = 'awareness'");
$stmt->execute([$currentWorkspace['id']]);
$journeyStages[1]['count'] = $stmt->fetch()['total'];

$stmt = $db->prepare("SELECT COUNT(*) as total FROM conversations WHERE workspace_id = ? AND is_sale = 0 AND journey_stage IN ('consideration', 'decision')");
$stmt->execute([$currentWorkspace['id']]);
$journeyStages[2]['count'] = $stmt->fetch()['total'];

$stmt = $db->prepare("SELECT COUNT(*) as total FROM conversations WHERE workspace_id = ? AND is_sale = 1");
$stmt->execute([$currentWorkspace['id']]);
$journeyStages[3]['count'] = $stmt->fetch()['total'];

// Recent conversations per stage
$recentByStage = [];
foreach ($journeyStages as $stage) {
    if ($stage['stage_name'] === 'Comprou') {
        $stmt = $db->prepare("
            SELECT c.*, l.name as lead_name
            FROM conversations c
            LEFT JOIN leads l ON c.lead_id = l.id
            WHERE c.workspace_id = ? AND c.is_sale = 1
            ORDER BY c.created_at DESC
            LIMIT 5
        ");
    } else {
        $stmt = $db->prepare("
            SELECT c.*, l.name as lead_name
            FROM conversations c
            LEFT JOIN leads l ON c.lead_id = l.id
            WHERE c.workspace_id = ?
            ORDER BY c.created_at DESC
            LIMIT 5
        ");
    }
    $stmt->execute([$currentWorkspace['id']]);
    $recentByStage[$stage['stage_name']] = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jornada de Compra - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="/frontend/css/style.css">
    <link rel="stylesheet" href="/frontend/css/sidebar.css">
</head>
<body>
    <div class="app-layout">
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="top-bar">
                <div class="top-bar-title">
                    <h1>Jornada de Compra</h1>
                    <p>Acompanhe as etapas do funil de vendas</p>
                </div>
                <div class="top-bar-actions">
                    <button class="btn btn-primary">Adicionar Nova Etapa</button>
                    <button class="btn btn-secondary">Ordenar Etapas do Funil</button>
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
                                <strong style="color: #1E40AF;">Dúvidas sobre Jornada de Compra?</strong>
                                <p style="margin: 4px 0 0; font-size: 14px; color: #1E40AF;">
                                    🎥 <a href="#" style="color: #3B82F6; font-weight: 600;">Saiba mais no vídeo</a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Journey Funnel -->
                <div class="card">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">ID</th>
                                    <th style="width: 100px;">Ordem no Funil</th>
                                    <th>Nome da Etapa</th>
                                    <th>Eventos de Conversão</th>
                                    <th style="width: 150px;">Criado em</th>
                                    <th style="width: 100px;">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($journeyStages as $index => $stage): ?>
                                    <tr style="cursor: pointer;" onclick="toggleStageDetails('stage-<?= $index ?>')">
                                        <td><strong><?= $index + 1 ?></strong></td>
                                        <td style="text-align: center;"><?= $stage['stage_order'] ?></td>
                                        <td>
                                            <div style="display: flex; align-items: center; gap: 12px;">
                                                <?php if ($stage['stage_name'] === 'Fez Contato'): ?>
                                                    <div style="width: 32px; height: 32px; border-radius: 50%; background: #818CF8; display: flex; align-items: center; justify-content: center;">
                                                        <svg width="18" height="18" fill="white" viewBox="0 0 24 24"><path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                                    </div>
                                                <?php elseif ($stage['stage_name'] === 'Comprou'): ?>
                                                    <div style="width: 32px; height: 32px; border-radius: 50%; background: #10B981; display: flex; align-items: center; justify-content: center;">
                                                        <span style="color: white;">✓</span>
                                                    </div>
                                                <?php else: ?>
                                                    <div style="width: 32px; height: 32px; border-radius: 50%; background: <?= $stage['stage_name'] === 'NÃO QUALIFICADO' ? '#EF4444' : '#F59E0B' ?>; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 12px;">
                                                        <?= $stage['count'] ?>
                                                    </div>
                                                <?php endif; ?>
                                                <strong><?= htmlspecialchars($stage['stage_name']) ?></strong>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ($stage['events'] !== '-'): ?>
                                                <?php foreach (explode(', ', $stage['events']) as $event): ?>
                                                    <div style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; background: #DBEAFE; border-radius: 12px; margin-right: 6px; margin-bottom: 4px;">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="#3B82F6">
                                                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"/>
                                                        </svg>
                                                        <span style="font-size: 12px; color: #1E40AF; font-weight: 500;"><?= $event ?></span>
                                                    </div>
                                                    <?php if ($event === 'Lead Qualificado'): ?>
                                                        <div style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; background: #DBEAFE; border-radius: 12px; margin-right: 6px;">
                                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="#3B82F6">
                                                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"/>
                                                            </svg>
                                                            <span style="font-size: 12px; color: #1E40AF; font-weight: 500;">Lead</span>
                                                        </div>
                                                    <?php elseif ($event === 'Comprador'): ?>
                                                        <div style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; background: #DBEAFE; border-radius: 12px; margin-right: 6px;">
                                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="#3B82F6">
                                                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"/>
                                                            </svg>
                                                            <span style="font-size: 12px; color: #1E40AF; font-weight: 500;">Purchase</span>
                                                        </div>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <span style="color: #9CA3AF;">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= date('d/m/Y H:i:s', strtotime($stage['first_conversation'])) ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-secondary">⋯</button>
                                        </td>
                                    </tr>
                                    
                                    <tr id="stage-<?= $index ?>" style="display: none;">
                                        <td colspan="6" style="background: #F9FAFB; padding: 20px;">
                                            <h4 style="margin-bottom: 12px;">Conversas Recentes Nesta Etapa</h4>
                                            <?php if (!empty($recentByStage[$stage['stage_name']])): ?>
                                                <div style="display: grid; gap: 12px;">
                                                    <?php foreach (array_slice($recentByStage[$stage['stage_name']], 0, 3) as $conv): ?>
                                                        <div style="background: var(--bg-glass); padding: 12px; border-radius: 6px; border: 1px solid rgba(255, 255, 255, 0.1);">
                                                            <div style="display: flex; justify-content: space-between;">
                                                                <div>
                                                                    <strong style="color: var(--text-primary);"><?= htmlspecialchars($conv['contact_name'] ?? 'Sem nome') ?></strong>
                                                                    <span style="color: var(--text-muted); margin-left: 8px;"><?= formatPhone($conv['contact_phone']) ?></span>
                                                                </div>
                                                                <span style="font-size: 12px; color: var(--text-muted);"><?= date('d/m/Y H:i', strtotime($conv['created_at'])) ?></span>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
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
    </div>

    <script>
        function toggleStageDetails(id) {
            const el = document.getElementById(id);
            el.style.display = el.style.display === 'none' ? 'table-row' : 'none';
        }
    </script>
    <script src="/frontend/js/app.js"></script>
</body>
</html>




