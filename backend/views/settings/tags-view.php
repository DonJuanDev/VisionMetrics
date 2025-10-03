<?php
$stmt = $db->prepare("
    SELECT t.*, COUNT(lt.id) as lead_count
    FROM tags t
    LEFT JOIN lead_tags lt ON t.id = lt.tag_id
    WHERE t.workspace_id = ?
    GROUP BY t.id
    ORDER BY t.name
");
$stmt->execute([$currentWorkspace['id']]);
$tags = $stmt->fetchAll();
?>

<div style="margin-bottom: 20px;">
    <button onclick="showCreateTagModal()" class="btn btn-primary">+ Nova Tag</button>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Tag</th>
                    <th>Leads</th>
                    <th>Descrição</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($tags)): ?>
                    <tr>
                        <td colspan="4" class="text-center">
                            <div class="empty-state" style="padding: 40px;">
                                <p>Nenhuma tag criada. Tags ajudam a organizar leads.</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($tags as $tag): ?>
                        <tr>
                            <td>
                                <span class="badge" style="background: <?= htmlspecialchars($tag['color']) ?>; color: white; font-size: 13px;">
                                    <?= htmlspecialchars($tag['name']) ?>
                                </span>
                            </td>
                            <td><span class="badge badge-info"><?= $tag['lead_count'] ?></span></td>
                            <td class="text-muted"><?= htmlspecialchars($tag['description']) ?></td>
                            <td>
                                <a href="/settings.php?tab=tags&delete=<?= $tag['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Remover esta tag?')">Excluir</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function showCreateTagModal() {
    const name = prompt('Nome da tag:');
    if (name) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="create_tag" value="1">
            <input type="hidden" name="name" value="${name}">
            <input type="hidden" name="color" value="#${Math.floor(Math.random()*16777215).toString(16)}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>





