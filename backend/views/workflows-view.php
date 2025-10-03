<?php
// Get workflows
$stmt = $db->prepare("SELECT * FROM workflows WHERE workspace_id = ? ORDER BY created_at DESC");
$stmt->execute([$currentWorkspace['id']]);
$workflows = $stmt->fetchAll();
?>

<div style="margin-bottom: 20px;">
    <a href="/workflows-basic.php" class="btn btn-primary">+ Nova Automação</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Status</th>
                    <th>Nome</th>
                    <th>Gatilho</th>
                    <th>Execuções</th>
                    <th>Última Execução</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($workflows)): ?>
                    <tr>
                        <td colspan="5" class="text-center">
                            <div class="empty-state" style="padding: 40px;">
                                <div class="empty-icon">⚡</div>
                                <p>Nenhuma automação criada</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($workflows as $wf): ?>
                        <tr>
                            <td>
                                <span class="badge badge-<?= $wf['is_active'] ? 'success' : 'secondary' ?>">
                                    <?= $wf['is_active'] ? 'Ativa' : 'Inativa' ?>
                                </span>
                            </td>
                            <td><strong><?= htmlspecialchars($wf['name']) ?></strong></td>
                            <td><span class="badge badge-info"><?= ucfirst(str_replace('_', ' ', $wf['trigger_type'])) ?></span></td>
                            <td><?= $wf['execution_count'] ?></td>
                            <td><?= $wf['last_executed_at'] ? timeAgo($wf['last_executed_at']) : 'Nunca' ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>





