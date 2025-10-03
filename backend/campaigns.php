<?php
require_once __DIR__ . '/middleware.php';

$db = getDB();

// Get campaigns grouped from conversations
$stmt = $db->prepare("
    SELECT 
        utm_campaign as campaign_name,
        utm_source as platform,
        COUNT(*) as total_conversations,
        SUM(CASE WHEN is_sale = 1 THEN 1 ELSE 0 END) as total_sales,
        MIN(created_at) as first_conversation,
        MAX(created_at) as last_conversation
    FROM conversations
    WHERE workspace_id = ? AND utm_campaign IS NOT NULL
    GROUP BY utm_campaign, utm_source
    ORDER BY total_conversations DESC
");
$stmt->execute([$currentWorkspace['id']]);
$campaigns = $stmt->fetchAll();

// Get template messages for each campaign
$campaignMessages = [];
foreach ($campaigns as $camp) {
    $stmt = $db->prepare("
        SELECT m.*, c.contact_name, c.contact_phone
        FROM messages m
        INNER JOIN conversations c ON m.conversation_id = c.id
        WHERE c.workspace_id = ? AND c.utm_campaign = ?
        ORDER BY m.timestamp DESC
        LIMIT 10
    ");
    $stmt->execute([$currentWorkspace['id'], $camp['campaign_name']]);
    $campaignMessages[$camp['campaign_name']] = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mensagens Rastreáveis - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="/frontend/css/style.css">
    <link rel="stylesheet" href="/frontend/css/sidebar.css">
</head>
<body>
    <div class="app-layout">
        <?php include __DIR__ . '/partials/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="top-bar">
                <div class="top-bar-title">
                    <h1>Mensagens Rastreáveis para Google Ads</h1>
                    <p>Campanhas e mensagens vinculadas</p>
                </div>
                <div class="top-bar-actions">
                    <button class="btn btn-primary">+ Criar Mensagem para Campanhas</button>
                    <button class="btn btn-secondary">Gerenciar Campanhas</button>
                </div>
            </div>

            <div class="container">
                <div class="card" style="background: #EFF6FF; border-left: 4px solid #3B82F6; margin-bottom: 20px;">
                    <div class="card-body">
                        <div style="display: flex; align-items: flex-start; gap: 12px;">
                            <svg width="20" height="20" fill="#3B82F6" viewBox="0 0 24 24">
                                <path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div>
                                <strong style="color: #1E40AF;">Dúvidas sobre Mensagens Rastreáveis para Google Ads?</strong>
                                <p style="margin: 4px 0 0; font-size: 14px; color: #1E40AF;">
                                    🎥 <a href="#" style="color: #3B82F6; font-weight: 600;">Saiba mais no vídeo</a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (empty($campaigns)): ?>
                    <div class="card">
                        <div class="card-body">
                            <div class="empty-state">
                                <div class="empty-icon">📢</div>
                                <h2>Nenhuma campanha encontrada</h2>
                                <p>Configure UTMs nas suas campanhas para rastrear mensagens por campanha</p>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($campaigns as $campaign): ?>
                        <div class="card" style="margin-bottom: 16px;">
                            <div class="card-header" style="cursor: pointer;" onclick="toggleCampaignDetails('campaign-<?= md5($campaign['campaign_name']) ?>')">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div style="width: 8px; height: 8px; border-radius: 50%; background: #3B82F6;"></div>
                                    <strong><?= htmlspecialchars($campaign['campaign_name']) ?></strong>
                                    <span style="font-size: 12px; color: #6B7280;">(<?= htmlspecialchars($campaign['platform']) ?>)</span>
                                </div>
                                <svg width="20" height="20" fill="none" stroke="#6B7280" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                            
                            <div id="campaign-<?= md5($campaign['campaign_name']) ?>" style="display: none;">
                                <div class="card-body" style="background: #F9FAFB;">
                                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 20px;">
                                        <div style="text-align: center;">
                                            <div style="font-size: 24px; font-weight: 700; color: #4F46E5;"><?= $campaign['total_conversations'] ?></div>
                                            <div style="font-size: 12px; color: #6B7280;">Conversas</div>
                                        </div>
                                        <div style="text-align: center;">
                                            <div style="font-size: 24px; font-weight: 700; color: #10B981;"><?= $campaign['total_sales'] ?></div>
                                            <div style="font-size: 12px; color: #6B7280;">Vendas</div>
                                        </div>
                                        <div style="text-align: center;">
                                            <div style="font-size: 24px; font-weight: 700; color: #F59E0B;">
                                                <?= $campaign['total_conversations'] > 0 ? number_format(($campaign['total_sales'] / $campaign['total_conversations']) * 100, 1) : 0 ?>%
                                            </div>
                                            <div style="font-size: 12px; color: #6B7280;">Taxa de Conversão</div>
                                        </div>
                                    </div>

                                    <div style="background: white; padding: 16px; border-radius: 8px;">
                                        <h4 style="margin-bottom: 12px; font-size: 14px; font-weight: 600;">Mensagens Recentes</h4>
                                        <?php if (!empty($campaignMessages[$campaign['campaign_name']])): ?>
                                            <?php foreach (array_slice($campaignMessages[$campaign['campaign_name']], 0, 5) as $msg): ?>
                                                <div style="padding: 12px; background: #F9FAFB; border-radius: 6px; margin-bottom: 8px;">
                                                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 6px;">
                                                        <strong style="font-size: 13px;"><?= htmlspecialchars($msg['contact_name'] ?? $msg['contact_phone']) ?></strong>
                                                        <span style="font-size: 12px; color: #6B7280;"><?= date('d/m/Y H:i', strtotime($msg['timestamp'])) ?></span>
                                                    </div>
                                                    <div style="font-size: 13px; color: #4B5563;">
                                                        <strong>Mensagem:</strong> <?= htmlspecialchars(substr($msg['content'], 0, 100)) ?><?= strlen($msg['content']) > 100 ? '...' : '' ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function toggleCampaignDetails(id) {
            const el = document.getElementById(id);
            el.style.display = el.style.display === 'none' ? 'block' : 'none';
        }
    </script>
    <script src="/frontend/js/app.js"></script>
</body>
</html>




