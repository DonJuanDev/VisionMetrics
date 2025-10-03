<?php
require_once __DIR__ . '/middleware.php';

$db = getDB();

// Get view mode
$view = $_GET['view'] ?? 'list';

// Filters
$stage = $_GET['stage'] ?? 'all';
$search = $_GET['search'] ?? '';
$tag = $_GET['tag'] ?? '';

$query = "
    SELECT l.*, 
           w.phone_number as whatsapp_number,
           (SELECT COUNT(*) FROM conversations WHERE lead_id = l.id) as conversation_count,
           (SELECT GROUP_CONCAT(t.name SEPARATOR ', ') FROM tags t INNER JOIN lead_tags lt ON t.id = lt.tag_id WHERE lt.lead_id = l.id) as tags_list
    FROM leads l
    LEFT JOIN whatsapp_numbers w ON l.whatsapp_number_id = w.id
    WHERE l.workspace_id = ?
";
$params = [$currentWorkspace['id']];

if ($stage !== 'all') {
    $query .= " AND l.stage = ?";
    $params[] = $stage;
}

if ($search) {
    $query .= " AND (l.name LIKE ? OR l.email LIKE ? OR l.phone_number LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($tag) {
    $query .= " AND l.id IN (SELECT lead_id FROM lead_tags WHERE tag_id = ?)";
    $params[] = $tag;
}

$query .= " ORDER BY l.last_seen DESC LIMIT 100";

$stmt = $db->prepare($query);
$stmt->execute($params);
$leads = $stmt->fetchAll();

// Get tags for filter
$stmt = $db->prepare("SELECT * FROM tags WHERE workspace_id = ? ORDER BY name");
$stmt->execute([$currentWorkspace['id']]);
$availableTags = $stmt->fetchAll();

// For Kanban view - get leads by stage
if ($view === 'kanban') {
    $stages = ['novo', 'contatado', 'qualificado', 'proposta', 'negociacao', 'ganho', 'perdido'];
    $leadsByStage = [];
    
    foreach ($stages as $stg) {
        $stmt = $db->prepare("
            SELECT l.*, w.phone_number as whatsapp_number,
                   (SELECT COUNT(*) FROM conversations WHERE lead_id = l.id) as conversation_count
            FROM leads l
            LEFT JOIN whatsapp_numbers w ON l.whatsapp_number_id = w.id
            WHERE l.workspace_id = ? AND l.stage = ?
            ORDER BY l.last_seen DESC
            LIMIT 50
        ");
        $stmt->execute([$currentWorkspace['id'], $stg]);
        $leadsByStage[$stg] = $stmt->fetchAll();
    }
    
    // Calculate totals
    $stageTotals = [];
    $stageValues = [];
    foreach ($stages as $stg) {
        $stageTotals[$stg] = count($leadsByStage[$stg]);
        $stageValues[$stg] = array_sum(array_column($leadsByStage[$stg], 'total_sales'));
    }
}

// Handle stage update (AJAX for kanban)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_stage'])) {
    $leadId = (int)$_POST['lead_id'];
    $newStage = $_POST['new_stage'];
    
    $stmt = $db->prepare("UPDATE leads SET stage = ? WHERE id = ? AND workspace_id = ?");
    $stmt->execute([$newStage, $leadId, $currentWorkspace['id']]);
    
    json_response(['success' => true]);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leads - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="/frontend/css/style.css">
    <link rel="stylesheet" href="/frontend/css/kanban.css">
</head>
<body>
    <?php include __DIR__ . '/partials/header.php'; ?>

    <div class="container<?= $view === 'kanban' ? '-fluid' : '' ?>">
        <div class="page-header">
            <div>
                <h1>Leads</h1>
                <p>Gerencie e acompanhe todos os seus leads</p>
            </div>
            <div style="display: flex; gap: 12px;">
                <!-- View Switcher -->
                <div style="display: flex; gap: 4px; background: #F3F4F6; padding: 4px; border-radius: 8px;">
                    <a href="/leads.php?view=list" class="btn btn-sm <?= $view === 'list' ? 'btn-primary' : 'btn-secondary' ?>">Lista</a>
                    <a href="/leads.php?view=kanban" class="btn btn-sm <?= $view === 'kanban' ? 'btn-primary' : 'btn-secondary' ?>">Kanban</a>
                </div>
                <a href="/export-advanced.php" class="btn btn-secondary">Exportar</a>
            </div>
        </div>

        <?php if ($view === 'list'): ?>
            <!-- LIST VIEW -->
            <div class="card">
                <div class="card-header">
                    <h2>Filtros</h2>
                </div>
                <form method="GET" class="filters-form">
                    <input type="hidden" name="view" value="list">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Buscar</label>
                            <input type="text" name="search" placeholder="Nome, email ou telefone" value="<?= htmlspecialchars($search) ?>">
                        </div>
                        <div class="form-group">
                            <label>Etapa</label>
                            <select name="stage">
                                <option value="all" <?= $stage === 'all' ? 'selected' : '' ?>>Todas</option>
                                <option value="novo" <?= $stage === 'novo' ? 'selected' : '' ?>>Novo</option>
                                <option value="contatado" <?= $stage === 'contatado' ? 'selected' : '' ?>>Contatado</option>
                                <option value="qualificado" <?= $stage === 'qualificado' ? 'selected' : '' ?>>Qualificado</option>
                                <option value="proposta" <?= $stage === 'proposta' ? 'selected' : '' ?>>Proposta</option>
                                <option value="negociacao" <?= $stage === 'negociacao' ? 'selected' : '' ?>>Negociação</option>
                                <option value="ganho" <?= $stage === 'ganho' ? 'selected' : '' ?>>Ganho</option>
                                <option value="perdido" <?= $stage === 'perdido' ? 'selected' : '' ?>>Perdido</option>
                            </select>
                        </div>
                        <?php if (!empty($availableTags)): ?>
                            <div class="form-group">
                                <label>Tag</label>
                                <select name="tag">
                                    <option value="">Todas</option>
                                    <?php foreach ($availableTags as $t): ?>
                                        <option value="<?= $t['id'] ?>" <?= $tag == $t['id'] ? 'selected' : '' ?>><?= htmlspecialchars($t['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary">Filtrar</button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="card">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Lead</th>
                                <th>Contato</th>
                                <th>Tags</th>
                                <th>Etapa</th>
                                <th>Score</th>
                                <th>Atividade</th>
                                <th>Vendas</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($leads)): ?>
                                <tr>
                                    <td colspan="8" class="text-center">
                                        <div class="empty-state">
                                            <div class="empty-icon">👥</div>
                                            <h2>Nenhum lead encontrado</h2>
                                            <p>Conecte um WhatsApp ou comece a rastrear eventos para capturar leads!</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($leads as $lead): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($lead['name'] ?? 'Sem nome') ?></strong><br>
                                            <small class="text-muted">ID: #<?= $lead['id'] ?></small>
                                        </td>
                                        <td>
                                            <?php if ($lead['email']): ?>
                                                ✉️ <?= htmlspecialchars($lead['email']) ?><br>
                                            <?php endif; ?>
                                            <?php if ($lead['phone_number']): ?>
                                                <small>📱 <?= formatPhone($lead['phone_number']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($lead['tags_list']): ?>
                                                <?php foreach (explode(', ', $lead['tags_list']) as $tagName): ?>
                                                    <span class="badge badge-secondary" style="font-size: 11px;"><?= htmlspecialchars($tagName) ?></span>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?php
                                                echo match($lead['stage']) {
                                                    'novo' => 'info',
                                                    'contatado' => 'primary',
                                                    'qualificado' => 'warning',
                                                    'proposta', 'negociacao' => 'warning',
                                                    'ganho' => 'success',
                                                    'perdido' => 'danger',
                                                    default => 'secondary'
                                                };
                                            ?>">
                                                <?= ucfirst($lead['stage']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="score-badge score-<?= $lead['score'] >= 70 ? 'high' : ($lead['score'] >= 40 ? 'medium' : 'low') ?>">
                                                <?= $lead['score'] ?>
                                            </div>
                                        </td>
                                        <td>
                                            <small><?= $lead['conversation_count'] ?> conversas</small><br>
                                            <small class="text-muted"><?= timeAgo($lead['last_seen']) ?></small>
                                        </td>
                                        <td>
                                            <?php if ($lead['total_sales'] > 0): ?>
                                                <span class="text-success"><strong><?= formatCurrency($lead['total_sales']) ?></strong></span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="/lead-profile.php?id=<?= $lead['id'] ?>" class="btn btn-sm btn-primary">Ver</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php else: ?>
            <!-- KANBAN VIEW -->
            <div class="kanban-board">
                <?php foreach ($stages as $stage): ?>
                    <div class="kanban-column" data-stage="<?= $stage ?>">
                        <div class="kanban-column-header stage-<?= $stage ?>">
                            <h3>
                                <?php
                                $stageLabels = [
                                    'novo' => 'Novo',
                                    'contatado' => 'Contatado',
                                    'qualificado' => 'Qualificado',
                                    'proposta' => 'Proposta',
                                    'negociacao' => 'Negociação',
                                    'ganho' => 'Ganho',
                                    'perdido' => 'Perdido'
                                ];
                                echo $stageLabels[$stage];
                                ?>
                            </h3>
                            <div class="kanban-stats">
                                <span class="count"><?= $stageTotals[$stage] ?> leads</span>
                                <?php if ($stageValues[$stage] > 0): ?>
                                    <span class="value"><?= formatCurrency($stageValues[$stage]) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="kanban-column-body" id="stage-<?= $stage ?>">
                            <?php foreach ($leadsByStage[$stage] as $lead): ?>
                                <div class="kanban-card" draggable="true" data-lead-id="<?= $lead['id'] ?>">
                                    <div class="kanban-card-header">
                                        <strong><?= htmlspecialchars($lead['name'] ?? 'Sem nome') ?></strong>
                                        <span class="lead-score score-<?= $lead['score'] >= 70 ? 'high' : ($lead['score'] >= 40 ? 'medium' : 'low') ?>">
                                            <?= $lead['score'] ?>
                                        </span>
                                    </div>
                                    
                                    <div class="kanban-card-body">
                                        <?php if ($lead['email']): ?>
                                            <div class="card-info">✉️ <?= htmlspecialchars($lead['email']) ?></div>
                                        <?php endif; ?>
                                        <?php if ($lead['phone_number']): ?>
                                            <div class="card-info">📱 <?= formatPhone($lead['phone_number']) ?></div>
                                        <?php endif; ?>
                                        
                                        <div class="card-stats">
                                            <span>💬 <?= $lead['conversation_count'] ?></span>
                                            <span>💭 <?= $lead['total_messages'] ?></span>
                                            <?php if ($lead['total_sales'] > 0): ?>
                                                <span class="text-success">💰 <?= formatCurrency($lead['total_sales']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="card-footer">
                                            <small class="text-muted"><?= timeAgo($lead['last_seen']) ?></small>
                                            <a href="/lead-profile.php?id=<?= $lead['id'] ?>" class="btn btn-sm" style="font-size: 11px; padding: 4px 8px;">Ver</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <?php if (empty($leadsByStage[$stage])): ?>
                                <div class="kanban-empty">
                                    <p>Nenhum lead</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="/frontend/js/app.js"></script>
    <?php if ($view === 'kanban'): ?>
        <script src="/frontend/js/kanban.js"></script>
    <?php endif; ?>
</body>
</html>
