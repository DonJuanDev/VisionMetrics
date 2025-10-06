<?php
require_once __DIR__ . '/middleware.php';

$db = getDB();

// Create workflow
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_workflow'])) {
    $name = trim($_POST['name']);
    $triggerType = $_POST['trigger_type'];
    $actionType = $_POST['action_type'];
    $actionData = $_POST['action_data'] ?? '';
    
    $triggerConfig = json_encode([
        'type' => $triggerType,
        'conditions' => []
    ]);
    
    $actions = json_encode([[
        'type' => $actionType,
        'data' => $actionData
    ]]);
    
    $stmt = $db->prepare("INSERT INTO workflows (workspace_id, name, trigger_type, trigger_config, actions) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$currentWorkspace['id'], $name, $triggerType, $triggerConfig, $actions]);
    
    $_SESSION['success'] = 'Automação criada!';
    redirect('/workflows-basic.php');
}

// Toggle active
if (isset($_GET['toggle'])) {
    $stmt = $db->prepare("UPDATE workflows SET is_active = NOT is_active WHERE id = ? AND workspace_id = ?");
    $stmt->execute([$_GET['toggle'], $currentWorkspace['id']]);
    $_SESSION['success'] = 'Status atualizado!';
    redirect('/workflows-basic.php');
}

// Get workflows
$stmt = $db->prepare("SELECT * FROM workflows WHERE workspace_id = ? ORDER BY created_at DESC");
$stmt->execute([$currentWorkspace['id']]);
$workflows = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Automações - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="/frontend/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/partials/header.php'; ?>

    <div class="container">
        <div class="page-header">
            <div>
                <h1>⚡ Automações (Beta)</h1>
                <p>Workflows automáticos baseados em gatilhos</p>
            </div>
            <button onclick="document.getElementById('modalCreate').style.display='flex'" class="btn btn-primary">
                + Nova Automação
            </button>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Nome</th>
                            <th>Gatilho</th>
                            <th>Ação</th>
                            <th>Execuções</th>
                            <th>Última Exec.</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($workflows)): ?>
                            <tr>
                                <td colspan="7" class="text-center">
                                    <div class="empty-state">
                                        <div class="empty-icon">⚡</div>
                                        <h2>Nenhuma automação criada</h2>
                                        <p>Crie sua primeira automação para economizar tempo</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($workflows as $wf): ?>
                                <tr>
                                    <td>
                                        <a href="/workflows-basic.php?toggle=<?= $wf['id'] ?>" class="toggle-switch <?= $wf['is_active'] ? 'active' : '' ?>"></a>
                                    </td>
                                    <td><strong><?= htmlspecialchars($wf['name']) ?></strong></td>
                                    <td><span class="badge badge-info"><?= ucfirst(str_replace('_', ' ', $wf['trigger_type'])) ?></span></td>
                                    <td>
                                        <?php
                                        $actions = json_decode($wf['actions'], true);
                                        echo '<span class="badge badge-success">' . ucfirst($actions[0]['type'] ?? 'N/A') . '</span>';
                                        ?>
                                    </td>
                                    <td><?= $wf['execution_count'] ?></td>
                                    <td><?= $wf['last_executed_at'] ? timeAgo($wf['last_executed_at']) : 'Nunca' ?></td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-secondary">Editar</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Info -->
        <div class="card" style="background: #EFF6FF; border-left: 4px solid #3B82F6;">
            <div class="card-body">
                <h3>💡 Exemplos de Automações</h3>
                <ul>
                    <li>✅ <strong>Novo Lead:</strong> Enviar notificação no Slack</li>
                    <li>✅ <strong>Score Alto:</strong> Criar tarefa para vendedor</li>
                    <li>✅ <strong>Venda Confirmada:</strong> Enviar webhook para sistema externo</li>
                    <li>✅ <strong>Lead Inativo 7 dias:</strong> Adicionar tag "Frio"</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Modal Create -->
    <div id="modalCreate" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h2>⚡ Nova Automação</h2>
                <button type="button" class="modal-close" onclick="document.getElementById('modalCreate').style.display='none'">×</button>
            </div>
            
            <div class="modal-body">
                <form method="POST">
                    <div class="form-group">
                        <label>Nome da Automação *</label>
                        <input type="text" name="name" required placeholder="Ex: Notificar vendedor de lead quente">
                    </div>

                    <div class="form-group">
                        <label>Gatilho (Quando executar) *</label>
                        <select name="trigger_type" required>
                            <option value="new_lead">Novo Lead Criado</option>
                            <option value="score_high">Score Alto (>70)</option>
                            <option value="sale_confirmed">Venda Confirmada</option>
                            <option value="stage_changed">Mudança de Etapa</option>
                            <option value="inactive_days">Lead Inativo X Dias</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Ação (O que fazer) *</label>
                        <select name="action_type" required>
                            <option value="notification">Enviar Notificação</option>
                            <option value="webhook">Chamar Webhook</option>
                            <option value="add_tag">Adicionar Tag</option>
                            <option value="change_stage">Mudar Etapa</option>
                            <option value="create_task">Criar Tarefa</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Dados da Ação</label>
                        <input type="text" name="action_data" placeholder="Ex: URL do webhook ou nome da tag">
                    </div>

                    <div class="modal-footer">
                        <button type="submit" name="create_workflow" class="btn btn-primary" style="flex: 1;">Criar Automação</button>
                        <button type="button" onclick="document.getElementById('modalCreate').style.display='none'" class="btn btn-secondary">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .toggle-switch {
            display: inline-block;
            width: 44px;
            height: 24px;
            background: #D1D5DB;
            border-radius: 12px;
            position: relative;
            cursor: pointer;
            transition: background 0.3s;
        }
        .toggle-switch::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            background: white;
            border-radius: 50%;
            top: 2px;
            left: 2px;
            transition: transform 0.3s;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        .toggle-switch.active {
            background: #10B981;
        }
        .toggle-switch.active::after {
            transform: translateX(20px);
        }
    </style>

    <script src="/frontend/js/app.js"></script>
</body>
</html>





