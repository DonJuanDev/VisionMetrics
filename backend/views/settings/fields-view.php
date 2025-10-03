<?php
$stmt = $db->prepare("SELECT * FROM custom_fields WHERE workspace_id = ? ORDER BY display_order, name");
$stmt->execute([$currentWorkspace['id']]);
$customFields = $stmt->fetchAll();
?>

<div style="margin-bottom: 20px;">
    <a href="/custom-fields.php" class="btn btn-primary">+ Novo Campo Customizado</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Nome do Campo</th>
                    <th>Tipo</th>
                    <th>Obrigatório</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($customFields)): ?>
                    <tr>
                        <td colspan="4" class="text-center">
                            <div class="empty-state" style="padding: 40px;">
                                <p>Nenhum campo customizado. Crie campos personalizados para seus leads.</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($customFields as $field): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($field['name']) ?></strong></td>
                            <td><span class="badge badge-info"><?= ucfirst($field['field_type']) ?></span></td>
                            <td><?= $field['is_required'] ? '<span class="badge badge-warning">Sim</span>' : '<span class="text-muted">Não</span>' ?></td>
                            <td>
                                <a href="/settings.php?tab=fields&delete=<?= $field['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Excluir campo?')">Excluir</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>





