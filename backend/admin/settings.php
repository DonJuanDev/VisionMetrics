<?php
require_once __DIR__ . '/../middleware.php';

$db = getDB();
$tab = $_GET['tab'] ?? 'general';

// Handle form submissions based on tab
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_workspace'])) {
        $name = trim($_POST['name']);
        $stmt = $db->prepare("UPDATE workspaces SET name = ? WHERE id = ?");
        $stmt->execute([$name, $currentWorkspace['id']]);
        $_SESSION['success'] = 'Workspace atualizado!';
        redirect('/backend/settings.php');
    }

    // Import handlers if they exist
    if ($tab === 'integrations' && file_exists(__DIR__ . '/handlers/integrations-handler.php')) {
        include __DIR__ . '/handlers/integrations-handler.php';
    }
    if ($tab === 'billing' && file_exists(__DIR__ . '/handlers/billing-handler.php')) {
        include __DIR__ . '/handlers/billing-handler.php';
    }
    if ($tab === 'tags' && file_exists(__DIR__ . '/handlers/tags-handler.php')) {
        include __DIR__ . '/handlers/tags-handler.php';
    }
    if ($tab === 'fields' && file_exists(__DIR__ . '/handlers/custom-fields-handler.php')) {
        include __DIR__ . '/handlers/custom-fields-handler.php';
    }
}

// Get data for tabs
if ($tab === 'integrations') {
    $stmt = $db->prepare("SELECT * FROM integrations WHERE workspace_id = ? ORDER BY type");
$stmt->execute([$currentWorkspace['id']]);
$integrations = $stmt->fetchAll();

    $stmt = $db->prepare("SELECT * FROM whatsapp_numbers WHERE workspace_id = ? ORDER BY created_at DESC");
    $stmt->execute([$currentWorkspace['id']]);
    $whatsappNumbers = $stmt->fetchAll();
}

if ($tab === 'billing') {
    $stmt = $db->prepare("SELECT * FROM subscriptions WHERE workspace_id = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([$currentWorkspace['id']]);
    $subscription = $stmt->fetch();
}

if ($tab === 'tags') {
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
}

if ($tab === 'fields') {
    $stmt = $db->prepare("SELECT * FROM custom_fields WHERE workspace_id = ? ORDER BY display_order, name");
    $stmt->execute([$currentWorkspace['id']]);
    $customFields = $stmt->fetchAll();
}

if ($tab === 'links') {
    $stmt = $db->prepare("SELECT * FROM trackable_links WHERE workspace_id = ? ORDER BY created_at DESC");
    $stmt->execute([$currentWorkspace['id']]);
    $links = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="/frontend/css/style.css">
    <link rel="stylesheet" href="/frontend/css/sidebar.css">
</head>
<body>
    <div class="app-layout">
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="top-bar">
                <div class="top-bar-title">
                    <h1>Configurações</h1>
                    <p>Gerencie seu workspace e integrações</p>
                </div>
            </div>

            <div class="container">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: 250px 1fr; gap: 32px;">
            <!-- Sidebar -->
            <div>
                <div class="card" style="padding: 16px;">
                    <div style="font-size: 12px; font-weight: 600; color: #9CA3AF; text-transform: uppercase; margin-bottom: 12px; padding: 0 12px;">Geral</div>
                    <a href="/backend/settings.php?tab=general" class="settings-menu-item <?= $tab === 'general' ? 'active' : '' ?>">
                        ⚙️ Workspace
                    </a>
                    <a href="/backend/settings.php?tab=billing" class="settings-menu-item <?= $tab === 'billing' ? 'active' : '' ?>">
                        💳 Billing & Planos
                    </a>
                    <a href="/backend/settings.php?tab=users" class="settings-menu-item <?= $tab === 'users' ? 'active' : '' ?>">
                        👥 Membros
                    </a>
                    
                    <div style="font-size: 12px; font-weight: 600; color: #9CA3AF; text-transform: uppercase; margin: 20px 0 12px; padding: 0 12px;">Integrações</div>
                    <a href="/backend/settings.php?tab=integrations" class="settings-menu-item <?= $tab === 'integrations' ? 'active' : '' ?>">
                        🔌 Ads & Analytics
                    </a>
                    <a href="/backend/settings.php?tab=whatsapp" class="settings-menu-item <?= $tab === 'whatsapp' ? 'active' : '' ?>">
                        📱 WhatsApp
                    </a>
                    
                    <div style="font-size: 12px; font-weight: 600; color: #9CA3AF; text-transform: uppercase; margin: 20px 0 12px; padding: 0 12px;">Personalização</div>
                    <a href="/backend/settings.php?tab=tags" class="settings-menu-item <?= $tab === 'tags' ? 'active' : '' ?>">
                        🏷️ Tags
                    </a>
                    <a href="/backend/settings.php?tab=fields" class="settings-menu-item <?= $tab === 'fields' ? 'active' : '' ?>">
                        🔧 Campos Customizados
                    </a>
                    
                    <div style="font-size: 12px; font-weight: 600; color: #9CA3AF; text-transform: uppercase; margin: 20px 0 12px; padding: 0 12px;">Ferramentas</div>
                    <a href="/backend/settings.php?tab=links" class="settings-menu-item <?= $tab === 'links' ? 'active' : '' ?>">
                        🔗 Links & QR Codes
                    </a>
                    <a href="/backend/settings.php?tab=api" class="settings-menu-item <?= $tab === 'api' ? 'active' : '' ?>">
                        🔑 API Keys
                    </a>
                    
                    <div style="font-size: 12px; font-weight: 600; color: #9CA3AF; text-transform: uppercase; margin: 20px 0 12px; padding: 0 12px;">Segurança</div>
                    <a href="/backend/settings.php?tab=lgpd" class="settings-menu-item <?= $tab === 'lgpd' ? 'active' : '' ?>">
                        🔒 LGPD/GDPR
                    </a>
            </div>
        </div>

            <!-- Content -->
            <div>
                <?php
                // Include the correct view
                $viewFile = __DIR__ . '/views/settings/' . $tab . '-view.php';
                if (file_exists($viewFile)) {
                    include $viewFile;
                } else {
                    echo '<div class="card"><div class="card-body"><div class="empty-state"><p>Aba em desenvolvimento</p></div></div></div>';
                }
                ?>
            </div>
        </div>
    </div>

    <style>
        .settings-menu-item {
            display: block;
            padding: 12px;
            color: #6B7280;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.2s;
            margin-bottom: 4px;
        }
        .settings-menu-item:hover {
            background: #F3F4F6;
            color: #111827;
        }
        .settings-menu-item.active {
            background: #EEF2FF;
            color: #4F46E5;
        }
    </style>

    <script src="/frontend/js/app.js"></script>
</body>
</html>
