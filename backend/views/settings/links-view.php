<?php
$stmt = $db->prepare("SELECT * FROM trackable_links WHERE workspace_id = ? ORDER BY created_at DESC LIMIT 50");
$stmt->execute([$currentWorkspace['id']]);
$links = $stmt->fetchAll();
?>

<div style="margin-bottom: 20px; display: flex; gap: 12px;">
    <a href="/trackable-links.php" class="btn btn-primary">+ Novo Link Rastreável</a>
    <a href="/qr-generator.php" class="btn btn-secondary">📱 Gerar QR Code</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Link Curto</th>
                    <th>Destino</th>
                    <th>Cliques</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($links)): ?>
                    <tr>
                        <td colspan="5" class="text-center">
                            <div class="empty-state" style="padding: 40px;">
                                <p>Nenhum link criado</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($links as $link): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($link['name']) ?></strong></td>
                            <td><code>/l/<?= htmlspecialchars($link['slug']) ?></code></td>
                            <td class="truncate"><?= htmlspecialchars($link['destination_url']) ?></td>
                            <td><span class="badge badge-info"><?= $link['click_count'] ?></span></td>
                            <td>
                                <button onclick="copyToClipboard('<?= htmlspecialchars(APP_URL) ?>/l/<?= htmlspecialchars($link['slug']) ?>')" class="btn btn-sm btn-secondary">Copiar</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>





