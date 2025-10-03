<?php
$stmt = $db->prepare("
    SELECT u.*, wm.role, wm.created_at as joined_at
    FROM users u
    INNER JOIN workspace_members wm ON u.id = wm.user_id
    WHERE wm.workspace_id = ?
    ORDER BY wm.created_at ASC
");
$stmt->execute([$currentWorkspace['id']]);
$members = $stmt->fetchAll();
?>

<div class="card">
    <div class="card-header">
        <h2>Membros do Workspace</h2>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Usuário</th>
                    <th>Email</th>
                    <th>Função</th>
                    <th>Membro desde</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($members as $member): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($member['name']) ?></strong></td>
                        <td><?= htmlspecialchars($member['email']) ?></td>
                        <td>
                            <span class="badge badge-<?= $member['role'] === 'owner' ? 'primary' : 'secondary' ?>">
                                <?= ucfirst($member['role']) ?>
                            </span>
                        </td>
                        <td><?= date('d/m/Y', strtotime($member['joined_at'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>





