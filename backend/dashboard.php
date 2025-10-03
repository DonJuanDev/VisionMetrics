<?php
require_once __DIR__ . '/middleware.php';

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
        <?php include __DIR__ . '/partials/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="top-bar">
                <div class="top-bar-title">
                    <h1>Dashboard</h1>
                    <p>Visão geral em tempo real</p>
                </div>
                <div class="top-bar-actions">
                    <div class="realtime-indicator">
                        <div class="realtime-dot"></div>
                        <span>Ao Vivo</span>
                    </div>
                    <div class="workspace-badge">
                        🏢 <?= htmlspecialchars($currentWorkspace['name']) ?>
                    </div>
                </div>
            </div>

            <div class="container">
                <!-- Integration Status Banner -->
                <?php if (!$hasMetaAds || !$hasGA4 || !$connectedWhatsApp): ?>
                    <div class="card" style="background: linear-gradient(135deg, #667EEA 0%, #764BA2 100%); border: none; color: white; margin-bottom: 24px;">
                        <div class="card-body">
                            <h3 style="color: white; margin-bottom: 16px; display: flex; align-items: center; gap: 10px;">
                                <svg width="24" height="24" fill="white" viewBox="0 0 24 24">
                                    <path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Configure suas Integrações para Começar
                            </h3>
                            <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                                <?php if (!$connectedWhatsApp): ?>
                                    <a href="/backend/whatsapp.php" class="btn btn-primary" style="background: white; color: #4F46E5;">
                                        📱 Conectar WhatsApp
                                    </a>
                                <?php endif; ?>
                                <?php if (!$hasMetaAds): ?>
                                    <a href="/backend/integrations-config.php" class="btn btn-primary" style="background: white; color: #4F46E5;">
                                        🔌 Configurar Meta Ads
                                    </a>
                                <?php endif; ?>
                                <?php if (!$hasGA4): ?>
                                    <a href="/backend/integrations-config.php" class="btn btn-primary" style="background: white; color: #4F46E5;">
                                        📊 Configurar Google Analytics 4
                                    </a>
                                <?php endif; ?>
                            </div>
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
                        <div style="display: flex; align-items: center; gap: 16px;">
                            <div style="width: 64px; height: 64px; border-radius: 16px; background: linear-gradient(135deg, #667EEA 0%, #764BA2 100%); display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 16px rgba(102, 126, 234, 0.25);">
                                <svg width="32" height="32" fill="white" viewBox="0 0 24 24">
                                    <path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="stat-label">Total</div>
                                <div class="stat-value" id="total-conversations"><?= number_format($totalConversations) ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div style="display: flex; align-items: center; gap: 16px;">
                            <div style="width: 64px; height: 64px; border-radius: 16px; background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%); display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 16px rgba(59, 130, 246, 0.25);">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="white">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="stat-label">Meta Ads</div>
                                <div class="stat-value" id="meta-ads-count"><?= number_format($metaAds) ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div style="display: flex; align-items: center; gap: 16px;">
                            <div style="width: 64px; height: 64px; border-radius: 16px; background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%); display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 16px rgba(245, 158, 11, 0.25);">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="white">
                                    <path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="stat-label">Google Ads</div>
                                <div class="stat-value" id="google-ads-count"><?= number_format($googleAds) ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div style="display: flex; align-items: center; gap: 16px;">
                            <div style="width: 64px; height: 64px; border-radius: 16px; background: linear-gradient(135deg, #6B7280 0%, #4B5563 100%); display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 16px rgba(107, 114, 128, 0.25);">
                                <svg width="32" height="32" fill="white" viewBox="0 0 24 24">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="stat-label">Outras Origens</div>
                                <div class="stat-value" id="outras-origens-count"><?= number_format($outrasOrigens) ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div style="display: flex; align-items: center; gap: 16px;">
                            <div style="width: 64px; height: 64px; border-radius: 16px; background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%); display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 16px rgba(239, 68, 68, 0.25);">
                                <svg width="32" height="32" fill="white" viewBox="0 0 24 24">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="stat-label">Não Rastreada</div>
                                <div class="stat-value" id="nao-rastreada-count"><?= number_format($naoRastreada) ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="card">
                    <div class="card-header">
                        <h2>Atividade Recente</h2>
                        <span style="font-size: 12px; color: #6B7280;" id="last-update">Atualizado agora</span>
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
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-top: 32px;">
                    <div class="card" style="text-align: center; padding: 32px; background: linear-gradient(135deg, #EFF6FF 0%, #DBEAFE 100%); border: 1px solid #BFDBFE;">
                        <div style="width: 72px; height: 72px; margin: 0 auto 20px; border-radius: 20px; background: white; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 20px rgba(59, 130, 246, 0.15);">
                            <svg width="36" height="36" fill="#3B82F6" viewBox="0 0 24 24">
                                <path d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                            </svg>
                        </div>
                        <h3 style="margin-bottom: 10px; color: #1E40AF;">Links Rastreáveis</h3>
                        <p style="color: #3B82F6; margin-bottom: 20px; font-size: 14px;">Crie links curtos com tracking e QR Codes</p>
                        <a href="/backend/trackable-links.php" class="btn btn-primary btn-block">Criar Link</a>
                    </div>

                    <div class="card" style="text-align: center; padding: 32px; background: linear-gradient(135deg, #ECFDF5 0%, #D1FAE5 100%); border: 1px solid #A7F3D0;">
                        <div style="width: 72px; height: 72px; margin: 0 auto 20px; border-radius: 20px; background: white; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 20px rgba(16, 185, 129, 0.15);">
                            <svg width="36" height="36" fill="#10B981" viewBox="0 0 24 24">
                                <path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                        </div>
                        <h3 style="margin-bottom: 10px; color: #065F46;">WhatsApp Business</h3>
                        <p style="color: #10B981; margin-bottom: 20px; font-size: 14px;">Gerencie suas conversas e números</p>
                        <a href="/backend/whatsapp.php" class="btn btn-success btn-block">Gerenciar</a>
                    </div>

                    <div class="card" style="text-align: center; padding: 32px; background: linear-gradient(135deg, #FEF3C7 0%, #FDE68A 100%); border: 1px solid #FCD34D;">
                        <div style="width: 72px; height: 72px; margin: 0 auto 20px; border-radius: 20px; background: white; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 20px rgba(245, 158, 11, 0.15);">
                            <svg width="36" height="36" fill="#F59E0B" viewBox="0 0 24 24">
                                <path d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <h3 style="margin-bottom: 10px; color: #92400E;">Relatórios</h3>
                        <p style="color: #D97706; margin-bottom: 20px; font-size: 14px;">Exporte dados e analise resultados</p>
                        <a href="/backend/reports.php" class="btn btn-primary btn-block" style="background: #F59E0B; border-color: #F59E0B;">Ver Relatórios</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="/frontend/js/app.js"></script>
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
                el.style.transform = 'scale(1.1)';
                el.style.color = '#4F46E5';
                el.textContent = newValue.toLocaleString('pt-BR');
                setTimeout(() => {
                    el.style.transform = 'scale(1)';
                    el.style.color = '';
                }, 300);
            }
        }
    </script>
</body>
</html>