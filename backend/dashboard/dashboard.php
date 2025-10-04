<?php
require_once __DIR__ . '/../middleware.php';

$db = getDB();

// Real-time stats
$stmt = $db->prepare("SELECT COUNT(*) as total FROM conversations WHERE workspace_id = ?");
$stmt->execute([$currentWorkspace['id']]);
$totalConversations = $stmt->fetch()['total'];

$stmt = $db->prepare("SELECT COUNT(*) as total FROM conversations WHERE workspace_id = ? AND (utm_source LIKE '%meta%' OR utm_source LIKE '%facebook%' OR fbclid IS NOT NULL)");
$stmt->execute([$currentWorkspace['id']]);
$metaAds = $stmt->fetch()['total'];

$stmt = $db->prepare("SELECT COUNT(*) as total FROM conversations WHERE workspace_id = ? AND (utm_source LIKE '%google%' OR gclid IS NOT NULL)");
$stmt->execute([$currentWorkspace['id']]);
$googleAds = $stmt->fetch()['total'];

$stmt = $db->prepare("SELECT COUNT(*) as total FROM conversations WHERE workspace_id = ? AND utm_source IS NOT NULL AND utm_source NOT LIKE '%meta%' AND utm_source NOT LIKE '%facebook%' AND utm_source NOT LIKE '%google%'");
$stmt->execute([$currentWorkspace['id']]);
$outrasOrigens = $stmt->fetch()['total'];

$stmt = $db->prepare("SELECT COUNT(*) as total FROM conversations WHERE workspace_id = ? AND utm_source IS NULL");
$stmt->execute([$currentWorkspace['id']]);
$naoRastreada = $stmt->fetch()['total'];

// Recent conversations
$stmt = $db->prepare("
    SELECT c.*, l.name as lead_name
    FROM conversations c
    LEFT JOIN leads l ON c.lead_id = l.id
    WHERE c.workspace_id = ?
    ORDER BY c.created_at DESC
    LIMIT 10
");
$stmt->execute([$currentWorkspace['id']]);
$recentConversations = $stmt->fetchAll();

// Check integrations status
$stmt = $db->prepare("SELECT * FROM integrations WHERE workspace_id = ? AND is_active = 1");
$stmt->execute([$currentWorkspace['id']]);
$activeIntegrations = $stmt->fetchAll();

$hasMetaAds = false;
$hasGA4 = false;
foreach ($activeIntegrations as $int) {
    if ($int['type'] === 'meta_ads') $hasMetaAds = true;
    if ($int['type'] === 'google_analytics') $hasGA4 = true;
}

$stmt = $db->prepare("SELECT * FROM whatsapp_numbers WHERE workspace_id = ? AND status = 'connected' LIMIT 1");
$stmt->execute([$currentWorkspace['id']]);
$connectedWhatsApp = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="/frontend/css/style.css">
    <link rel="stylesheet" href="/frontend/css/sidebar.css">
    <style>
        .realtime-indicator {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 14px;
            background: #D1FAE5;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            color: #065F46;
        }
        .realtime-dot {
            width: 8px;
            height: 8px;
            background: #10B981;
            border-radius: 50%;
            animation: pulse 2s ease-in-out infinite;
        }
        .integration-status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
        }
        .integration-status.connected {
            background: #D1FAE5;
            color: #065F46;
        }
        .integration-status.disconnected {
            background: #FEE2E2;
            color: #991B1B;
        }
    </style>
</head>
<body>
    <div class="app-layout">
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="top-bar">
                <div class="top-bar-title">
                    <h1>Dashboard</h1>
                    <p>Visão geral em tempo real</p>
                </div>
                <div class="top-bar-actions">
                    <div class="live-indicator">
                        <div class="live-dot"></div>
                        <span>Ao Vivo</span>
                    </div>
                    <div class="notification-badge">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.89 2 2 2zm6-6v-5c0-3.07-1.64-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/>
                        </svg>
                        <div class="notification-count">1</div>
                    </div>
                    <div class="workspace-badge">
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 7V3H2v18h20V7H12zM6 19H4v-2h2v2zm0-4H4v-2h2v2zm0-4H4V9h2v2zm0-4H4V5h2v2zm4 12H8v-2h2v2zm0-4H8v-2h2v2zm0-4H8V9h2v2zm0-4H8V5h2v2zm10 12h-8v-2h2v-2h-2v-2h2v-2h-2V9h8v10z"/>
                        </svg>
                        <?= htmlspecialchars($currentWorkspace['name']) ?>
                    </div>
                </div>
            </div>

            <div class="container">
                <!-- Integration Status Banner -->
                <?php if (!$hasMetaAds || !$hasGA4 || !$connectedWhatsApp): ?>
                    <div class="integration-banner">
                        <h3>
                            <svg width="32" height="32" fill="white" viewBox="0 0 24 24" style="margin-right: 12px;">
                                <path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Configure suas Integrações para Começar
                        </h3>
                        <div style="display: flex; gap: 16px; flex-wrap: wrap;">
                            <?php if (!$connectedWhatsApp): ?>
                                <a href="/backend/whatsapp.php" class="btn btn-secondary">
                                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24" style="margin-right: 8px;">
                                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488"/>
                                    </svg>
                                    Conectar WhatsApp
                                </a>
                            <?php endif; ?>
                            <?php if (!$hasMetaAds): ?>
                                <a href="/backend/integrations-config.php" class="btn btn-secondary">
                                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24" style="margin-right: 8px;">
                                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                    </svg>
                                    Configurar Meta Ads
                                </a>
                            <?php endif; ?>
                            <?php if (!$hasGA4): ?>
                                <a href="/backend/integrations-config.php" class="btn btn-secondary">
                                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24" style="margin-right: 8px;">
                                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                                    </svg>
                                    Configurar Google Analytics
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Integration Status - All Connected -->
                    <div style="display: flex; gap: 12px; margin-bottom: 24px; flex-wrap: wrap;">
                        <div class="integration-status connected">
                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            WhatsApp Conectado
                        </div>
                        <div class="integration-status connected">
                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Meta Ads CAPI Ativo
                        </div>
                        <div class="integration-status connected">
                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Google Analytics 4 Ativo
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Stats Overview -->
                <div class="stats-grid" style="margin-bottom: 32px;">
                    <div class="stat-card">
                        <div class="stat-card-icon gradient-1">
                            <svg width="24" height="24" fill="white" viewBox="0 0 24 24">
                                <path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                        </div>
                        <div class="stat-label">Total</div>
                        <div class="stat-value" id="total-conversations"><?= number_format($totalConversations) ?></div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-icon gradient-2">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="white">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                        </div>
                        <div class="stat-label">Meta Ads</div>
                        <div class="stat-value" id="meta-ads-count"><?= number_format($metaAds) ?></div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-icon gradient-2">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="white">
                                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                            </svg>
                        </div>
                        <div class="stat-label">Google Ads</div>
                        <div class="stat-value" id="google-ads-count"><?= number_format($googleAds) ?></div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-icon gradient-1">
                            <svg width="24" height="24" fill="white" viewBox="0 0 24 24">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/>
                            </svg>
                        </div>
                        <div class="stat-label">Outras Origens</div>
                        <div class="stat-value" id="outras-origens-count"><?= number_format($outrasOrigens) ?></div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-icon gradient-3">
                            <svg width="24" height="24" fill="white" viewBox="0 0 24 24">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                            </svg>
                        </div>
                        <div class="stat-label">Não Rastreada</div>
                        <div class="stat-value" id="nao-rastreada-count"><?= number_format($naoRastreada) ?></div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="card">
                    <div class="card-header">
                        <h2>Atividade Recente</h2>
                        <span style="font-size: 14px; color: var(--text-muted); font-weight: 500;" id="last-update">Atualizado agora</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Contato</th>
                                    <th>Origem</th>
                                    <th>Status</th>
                                    <th>Data</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody id="recent-conversations">
                                <?php foreach ($recentConversations as $conv): ?>
                                    <tr>
                                        <td>
                                            <div style="display: flex; align-items: center; gap: 12px;">
                                                <div style="width: 40px; height: 40px; border-radius: 10px; background: linear-gradient(135deg, #6366F1, #818CF8); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 16px;">
                                                    <?= strtoupper(substr($conv['contact_name'] ?? 'U', 0, 1)) ?>
                                                </div>
                                                <div>
                                                    <div style="font-weight: 600;"><?= htmlspecialchars($conv['contact_name'] ?? 'Sem nome') ?></div>
                                                    <div style="font-size: 12px; color: #6B7280;"><?= formatPhone($conv['contact_phone']) ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ($conv['fbclid'] || (isset($conv['utm_source']) && strpos($conv['utm_source'], 'meta') !== false)): ?>
                                                <span class="badge badge-info">Meta Ads</span>
                                            <?php elseif ($conv['gclid'] || (isset($conv['utm_source']) && strpos($conv['utm_source'], 'google') !== false)): ?>
                                                <span class="badge badge-warning">Google Ads</span>
                                            <?php elseif ($conv['utm_source']): ?>
                                                <span class="badge badge-secondary"><?= htmlspecialchars($conv['utm_source']) ?></span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Direto</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($conv['is_sale']): ?>
                                                <span class="badge badge-success">Comprou ✓</span>
                                            <?php else: ?>
                                                <span class="badge badge-warning">Em andamento</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= timeAgo($conv['created_at']) ?></td>
                                        <td>
                                            <a href="/conversation-detail.php?id=<?= $conv['id'] ?>" class="btn btn-sm btn-secondary">Ver</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px; margin-top: 40px;">
                    <div class="card" style="text-align: center; padding: 40px; position: relative; overflow: hidden;">
                        <div class="stat-card-icon gradient-1" style="width: 80px; height: 80px; margin: 0 auto 24px; display: flex; align-items: center; justify-content: center;">
                            <svg width="40" height="40" fill="white" viewBox="0 0 24 24">
                                <path d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                            </svg>
                        </div>
                        <h3 style="margin-bottom: 12px; color: var(--text-primary); font-family: 'Poppins', sans-serif; font-size: 20px;">Links Rastreáveis</h3>
                        <p style="color: var(--text-muted); margin-bottom: 24px; font-size: 16px; font-weight: 500;">Crie links curtos com tracking e QR Codes</p>
                        <a href="/backend/trackable-links.php" class="btn btn-primary btn-lg btn-block">Criar Link</a>
                    </div>

                    <div class="card" style="text-align: center; padding: 40px; position: relative; overflow: hidden;">
                        <div class="stat-card-icon gradient-2" style="width: 80px; height: 80px; margin: 0 auto 24px; display: flex; align-items: center; justify-content: center;">
                            <svg width="40" height="40" fill="white" viewBox="0 0 24 24">
                                <path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                        </div>
                        <h3 style="margin-bottom: 12px; color: var(--text-primary); font-family: 'Poppins', sans-serif; font-size: 20px;">WhatsApp Business</h3>
                        <p style="color: var(--text-muted); margin-bottom: 24px; font-size: 16px; font-weight: 500;">Gerencie suas conversas e números</p>
                        <a href="/backend/whatsapp.php" class="btn btn-success btn-lg btn-block">Gerenciar</a>
                    </div>

                    <div class="card" style="text-align: center; padding: 40px; position: relative; overflow: hidden;">
                        <div class="stat-card-icon gradient-3" style="width: 80px; height: 80px; margin: 0 auto 24px; display: flex; align-items: center; justify-content: center;">
                            <svg width="40" height="40" fill="white" viewBox="0 0 24 24">
                                <path d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <h3 style="margin-bottom: 12px; color: var(--text-primary); font-family: 'Poppins', sans-serif; font-size: 20px;">Relatórios</h3>
                        <p style="color: var(--text-muted); margin-bottom: 24px; font-size: 16px; font-weight: 500;">Exporte dados e analise resultados</p>
                        <a href="/backend/reports.php" class="btn btn-warning btn-lg btn-block">Ver Relatórios</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="/frontend/js/app.js"></script>
    <script src="/frontend/js/theme-animations.js"></script>
    <script>
        // Real-time updates via polling (every 10 seconds)
        setInterval(async () => {
            try {
                const response = await fetch('/backend/api/dashboard-stats.php');
                const data = await response.json();
                
                // Update counters with animation
                updateCounter('total-conversations', data.total);
                updateCounter('meta-ads-count', data.meta);
                updateCounter('google-ads-count', data.google);
                updateCounter('outras-origens-count', data.outras);
                updateCounter('nao-rastreada-count', data.nao_rastreada);
                
                // Update timestamp
                document.getElementById('last-update').textContent = 'Atualizado ' + new Date().toLocaleTimeString('pt-BR');
            } catch (error) {
                console.log('Update error:', error);
            }
        }, 10000); // Update every 10 seconds

        function updateCounter(id, newValue) {
            const el = document.getElementById(id);
            if (el && el.textContent !== newValue.toLocaleString('pt-BR')) {
                // Add loading animation to parent card
                const card = el.closest('.stat-card');
                if (card && window.addLoadingAnimation) {
                    window.addLoadingAnimation(card);
                }
                
                // Animate the counter
                el.style.transform = 'scale(1.1)';
                el.style.color = 'var(--primary)';
                el.textContent = newValue.toLocaleString('pt-BR');
                
                setTimeout(() => {
                    el.style.transform = 'scale(1)';
                    el.style.color = '';
                }, 500);
            }
        }

    </script>
</body>
</html>