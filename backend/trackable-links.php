<?php
require_once __DIR__ . '/middleware.php';

$db = getDB();

// Create link
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_link'])) {
    $name = trim($_POST['name']);
    $destinationUrl = trim($_POST['destination_url']);
    $slug = !empty(trim($_POST['slug'])) ? trim($_POST['slug']) : generateSlug();
    
    $stmt = $db->prepare("INSERT INTO trackable_links (workspace_id, name, slug, destination_url) VALUES (?, ?, ?, ?)");
    $stmt->execute([$currentWorkspace['id'], $name, $slug, $destinationUrl]);
    
    $_SESSION['success'] = 'Link criado com sucesso!';
    redirect('/backend/trackable-links.php');
}

// Generate QR Code
if (isset($_GET['qr']) && isset($_GET['link_id'])) {
    $stmt = $db->prepare("SELECT * FROM trackable_links WHERE id = ? AND workspace_id = ?");
    $stmt->execute([$_GET['link_id'], $currentWorkspace['id']]);
    $link = $stmt->fetch();
    
    if ($link) {
        $shortUrl = APP_URL . '/l/' . $link['slug'];
        $qrUrl = 'https://chart.googleapis.com/chart?cht=qr&chs=400x400&chl=' . urlencode($shortUrl);
                                                                                                                                                                                2     
        // Download QR Code
        header('Content-Type: image/png');
        header('Content-Disposition: attachment; filename="qr-' . $link['slug'] . '.png"');
        readfile($qrUrl);
        exit;
    }
}

// Get links
$stmt = $db->prepare("
    SELECT *
    FROM trackable_links
    WHERE workspace_id = ?
    ORDER BY created_at DESC
");
$stmt->execute([$currentWorkspace['id']]);
$links = $stmt->fetchAll();

// Add clicks count for each link
foreach ($links as &$link) {
    $link['clicks'] = 0; // Default to 0, can be updated with real tracking later
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Links Rastreáveis - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="/frontend/css/style.css">
    <link rel="stylesheet" href="/frontend/css/sidebar.css">
</head>
<body>
    <div class="app-layout">
        <?php include __DIR__ . '/partials/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="top-bar">
                <div class="top-bar-title">
                    <h1>Links Rastreáveis</h1>
                    <p>Crie links curtos e rastreáveis com QR Codes</p>
                </div>
                <div class="top-bar-actions">
                    <button onclick="showCreateModal()" class="btn btn-primary">+ Criar Novo Link</button>
                </div>
            </div>

            <div class="container">
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
                <?php endif; ?>

                <div class="card" style="background: #EFF6FF; border-left: 4px solid #3B82F6; margin-bottom: 20px;">
                    <div class="card-body">
                        <div style="display: flex; align-items: flex-start; gap: 12px;">
                            <svg width="20" height="20" fill="#3B82F6" viewBox="0 0 24 24">
                                <path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div>
                                <strong style="color: #1E40AF;">Dúvidas sobre Links rastreáveis?</strong>
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
                                    <th>Nome</th>
                                    <th>Código</th>
                                    <th>Destino</th>
                                    <th>Cliques</th>
                                    <th>Criado em</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($links)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center">
                                            <div class="empty-state">
                                                <div class="empty-icon">🔗</div>
                                                <h2>Nenhum link criado</h2>
                                                <p>Crie seu primeiro link rastreável para começar</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($links as $link): ?>
                                        <tr>
                                            <td>
                                                <a href="<?= APP_URL ?>/l/<?= htmlspecialchars($link['slug']) ?>" target="_blank" style="color: #4F46E5; text-decoration: none; font-weight: 600;">
                                                    <?= htmlspecialchars($link['name']) ?>
                                                </a>
                                            </td>
                                            <td>
                                                <code style="background: #F3F4F6; padding: 6px 12px; border-radius: 6px; color: #EC4899; font-size: 13px;">
                                                    <?= htmlspecialchars($link['slug']) ?>
                                                </code>
                                            </td>
                                            <td class="truncate" style="max-width: 300px;">
                                                <a href="<?= htmlspecialchars($link['destination_url']) ?>" target="_blank" style="color: #6B7280; text-decoration: none; font-size: 13px;">
                                                    <?= htmlspecialchars($link['destination_url']) ?>
                                                </a>
                                            </td>
                                            <td>
                                                <span style="font-weight: 700; color: #4F46E5;"><?= number_format($link['clicks']) ?></span>
                                            </td>
                                            <td><?= date('d/m/Y H:i:s', strtotime($link['created_at'])) ?></td>
                                            <td>
                                                <div style="display: flex; gap: 6px;">
                                                    <button onclick="copyLink('<?= APP_URL ?>/l/<?= htmlspecialchars($link['slug']) ?>')" class="btn btn-sm btn-secondary" title="Copiar link">
                                                        📋
                                                    </button>
                                                    <a href="/trackable-links.php?qr=1&link_id=<?= $link['id'] ?>" class="btn btn-sm btn-secondary" title="Baixar QR Code">
                                                        📱
                                                    </a>
                                                    <button class="btn btn-sm btn-secondary" title="Mais opções">⋯</button>
                                                </div>
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

    <!-- Modal Create Link -->
    <div id="modalCreate" style="display:none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 2000; align-items: center; justify-content: center;">
        <div style="background: white; padding: 32px; border-radius: 12px; max-width: 600px; width: 90%;">
            <h2 style="margin-bottom: 24px;">Criar Novo Link</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Nome do Link *</label>
                    <input type="text" name="name" required placeholder="Ex: Campanha Black Friday">
                </div>

                <div class="form-group">
                    <label>URL de Destino *</label>
                    <input type="url" name="destination_url" required placeholder="https://seusite.com/produto">
                </div>

                <div class="form-group">
                    <label>Código Curto (slug)</label>
                    <input type="text" name="slug" placeholder="Deixe vazio para gerar automaticamente" pattern="[a-z0-9-]+">
                    <small class="help-text">Apenas letras minúsculas, números e hífens</small>
                </div>

                <div style="display: flex; gap: 12px; margin-top: 24px;">
                    <button type="submit" name="create_link" class="btn btn-primary" style="flex: 1;">Criar Link</button>
                    <button type="button" onclick="document.getElementById('modalCreate').style.display='none'" class="btn btn-secondary">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showCreateModal() {
            document.getElementById('modalCreate').style.display = 'flex';
        }

        function copyLink(url) {
            navigator.clipboard.writeText(url).then(() => {
                if (window.VisionMetrics) {
                    VisionMetrics.showToast('Link copiado!', 'success');
                } else {
                    alert('Link copiado: ' + url);
                }
            });
        }
    </script>
    <script src="/frontend/js/app.js"></script>
</body>
</html>