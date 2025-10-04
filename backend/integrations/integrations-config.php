<?php
require_once __DIR__ . '/../middleware.php';

$db = getDB();

// Handle Meta Ads configuration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_meta_ads'])) {
    $accessToken = trim($_POST['meta_access_token']);
    $pixelId = trim($_POST['meta_pixel_id']);
    $testMode = isset($_POST['meta_test_mode']) ? 1 : 0;
    
    $stmt = $db->prepare("SELECT id FROM integrations WHERE workspace_id = ? AND type = 'meta_ads'");
    $stmt->execute([$currentWorkspace['id']]);
    $existing = $stmt->fetch();
    
    $credentials = json_encode(['access_token' => $accessToken, 'pixel_id' => $pixelId]);
    $settings = json_encode(['test_mode' => $testMode, 'auto_send_conversions' => 1]);
    
    if ($existing) {
        $stmt = $db->prepare("UPDATE integrations SET credentials = ?, settings = ?, is_active = 1 WHERE id = ?");
        $stmt->execute([$credentials, $settings, $existing['id']]);
    } else {
        $stmt = $db->prepare("INSERT INTO integrations (workspace_id, type, name, credentials, settings, is_active) VALUES (?, 'meta_ads', 'Meta Ads CAPI', ?, ?, 1)");
        $stmt->execute([$currentWorkspace['id'], $credentials, $settings]);
    }
    
    $_SESSION['success'] = 'Meta Ads configurado com sucesso!';
    redirect('/integrations-config.php');
}

// Handle GA4 configuration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_ga4'])) {
    $measurementId = trim($_POST['ga4_measurement_id']);
    $apiSecret = trim($_POST['ga4_api_secret']);
    
    $stmt = $db->prepare("SELECT id FROM integrations WHERE workspace_id = ? AND type = 'google_analytics'");
    $stmt->execute([$currentWorkspace['id']]);
    $existing = $stmt->fetch();
    
    $credentials = json_encode(['measurement_id' => $measurementId, 'api_secret' => $apiSecret]);
    $settings = json_encode(['auto_send_events' => 1]);
    
    if ($existing) {
        $stmt = $db->prepare("UPDATE integrations SET credentials = ?, settings = ?, is_active = 1 WHERE id = ?");
        $stmt->execute([$credentials, $settings, $existing['id']]);
    } else {
        $stmt = $db->prepare("INSERT INTO integrations (workspace_id, type, name, credentials, settings, is_active) VALUES (?, 'google_analytics', 'Google Analytics 4', ?, ?, 1)");
        $stmt->execute([$currentWorkspace['id'], $credentials, $settings]);
    }
    
    $_SESSION['success'] = 'Google Analytics 4 configurado com sucesso!';
    redirect('/integrations-config.php');
}

// Get integrations
$stmt = $db->prepare("SELECT * FROM integrations WHERE workspace_id = ?");
$stmt->execute([$currentWorkspace['id']]);
$integrations = $stmt->fetchAll();

$metaConfig = null;
$ga4Config = null;

foreach ($integrations as $int) {
    if ($int['type'] === 'meta_ads') $metaConfig = $int;
    if ($int['type'] === 'google_analytics') $ga4Config = $int;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Integrações - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="/frontend/css/style.css">
    <link rel="stylesheet" href="/frontend/css/sidebar.css">
</head>
<body>
    <div class="app-layout">
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="top-bar">
                <div class="top-bar-title">
                    <h1>Integrações</h1>
                    <p>Conecte Meta Ads e Google Analytics 4</p>
                </div>
            </div>

            <div class="container">
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                    </div>
                <?php endif; ?>

                <!-- Meta Ads -->
                <div class="card" style="margin-bottom: 32px;">
                    <div class="card-header" style="background: linear-gradient(135deg, #EFF6FF 0%, #DBEAFE 100%);">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 48px; height: 48px; border-radius: 12px; background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%); display: flex; align-items: center; justify-content: center;">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="white">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                </svg>
                            </div>
                            <div>
                                <h2 style="color: #1E40AF;">Meta Ads - Conversions API (CAPI)</h2>
                                <p style="margin: 0; font-size: 13px; color: #3B82F6; font-weight: 500;">
                                    <?= $metaConfig && $metaConfig['is_active'] ? '✓ Configurado e Ativo' : 'Não configurado' ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="form-group">
                                <label>Access Token *</label>
                                <input type="text" name="meta_access_token" 
                                       value="<?= $metaConfig ? json_decode($metaConfig['credentials'], true)['access_token'] : '' ?>" 
                                       placeholder="EAAxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" required>
                                <small class="help-text">
                                    Gere em: <a href="https://developers.facebook.com/tools/accesstoken/" target="_blank" style="color: #4F46E5;">Facebook Developer Tools</a>
                                </small>
                            </div>

                            <div class="form-group">
                                <label>Pixel ID *</label>
                                <input type="text" name="meta_pixel_id" 
                                       value="<?= $metaConfig ? json_decode($metaConfig['credentials'], true)['pixel_id'] : '' ?>" 
                                       placeholder="1234567890123456" required>
                                <small class="help-text">Encontre no Events Manager do Facebook</small>
                            </div>

                            <div class="form-group">
                                <label style="display: flex; align-items: center; gap: 8px;">
                                    <input type="checkbox" name="meta_test_mode" value="1" 
                                           <?= $metaConfig && json_decode($metaConfig['settings'], true)['test_mode'] ? 'checked' : '' ?>>
                                    Modo de Teste (Test Events)
                                </label>
                            </div>

                            <button type="submit" name="save_meta_ads" class="btn btn-primary" style="padding: 12px 32px;">
                                <svg width="18" height="18" fill="white" viewBox="0 0 24 24">
                                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Salvar Configurações Meta Ads
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Google Analytics 4 -->
                <div class="card">
                    <div class="card-header" style="background: linear-gradient(135deg, #FEF3C7 0%, #FDE68A 100%);">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 48px; height: 48px; border-radius: 12px; background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%); display: flex; align-items: center; justify-content: center;">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="white">
                                    <path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5z"/>
                                </svg>
                            </div>
                            <div>
                                <h2 style="color: #92400E;">Google Analytics 4</h2>
                                <p style="margin: 0; font-size: 13px; color: #D97706; font-weight: 500;">
                                    <?= $ga4Config && $ga4Config['is_active'] ? '✓ Configurado e Ativo' : 'Não configurado' ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="form-group">
                                <label>Measurement ID *</label>
                                <input type="text" name="ga4_measurement_id" 
                                       value="<?= $ga4Config ? json_decode($ga4Config['credentials'], true)['measurement_id'] : '' ?>" 
                                       placeholder="G-XXXXXXXXXX" required>
                                <small class="help-text">Encontre em: Admin > Data Streams > Web Stream Details</small>
                            </div>

                            <div class="form-group">
                                <label>API Secret *</label>
                                <input type="text" name="ga4_api_secret" 
                                       value="<?= $ga4Config ? json_decode($ga4Config['credentials'], true)['api_secret'] : '' ?>" 
                                       placeholder="xxxxxxxxxxxxxxxxxxxxxxxx" required>
                                <small class="help-text">
                                    Gere em: Admin > Data Streams > Measurement Protocol API secrets
                                </small>
                            </div>

                            <button type="submit" name="save_ga4" class="btn btn-primary" style="padding: 12px 32px;">
                                <svg width="18" height="18" fill="white" viewBox="0 0 24 24">
                                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Salvar Configurações GA4
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="/frontend/js/app.js"></script>
</body>
</html>