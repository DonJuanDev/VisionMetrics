<?php
require_once __DIR__ . '/middleware.php';

$db = getDB();

// Filters
$origem = $_GET['origem'] ?? 'all';
$search = $_GET['search'] ?? '';

$query = "
    SELECT c.*, 
           l.name as lead_name,
           l.phone_number,
           w.phone_number as whatsapp_number,
           (SELECT content FROM messages WHERE conversation_id = c.id ORDER BY timestamp DESC LIMIT 1) as last_message
    FROM conversations c
    LEFT JOIN leads l ON c.lead_id = l.id
    LEFT JOIN whatsapp_numbers w ON c.whatsapp_number_id = w.id
    WHERE c.workspace_id = ?
";
$params = [$currentWorkspace['id']];

if ($origem !== 'all') {
    if ($origem === 'meta') {
        $query .= " AND (c.utm_source LIKE '%meta%' OR c.utm_source LIKE '%facebook%' OR c.fbclid IS NOT NULL)";
    } elseif ($origem === 'google') {
        $query .= " AND (c.utm_source LIKE '%google%' OR c.gclid IS NOT NULL)";
    } elseif ($origem === 'direct') {
        $query .= " AND c.utm_source IS NULL";
    }
}

if ($search) {
    $query .= " AND (c.contact_name LIKE ? OR c.contact_phone LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$query .= " ORDER BY c.last_message_at DESC LIMIT 100";

$stmt = $db->prepare($query);
$stmt->execute($params);
$conversations = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conversas - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="/frontend/css/style.css">
    <link rel="stylesheet" href="/frontend/css/sidebar.css">
</head>
<body>
    <div class="app-layout">
        <?php include __DIR__ . '/partials/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="top-bar">
                <div class="top-bar-title">
                    <h1>Conversas</h1>
                    <p><?= number_format($totalConversations) ?> conversas rastreadas</p>
                </div>
                <div class="top-bar-actions">
                    <!-- Stats Pills -->
                    <div style="display: flex; gap: 12px;">
                        <div style="display: flex; align-items: center; gap: 8px; padding: 8px 16px; background: #EFF6FF; border-radius: 20px;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="#3B82F6">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"/>
                            </svg>
                            <span style="font-weight: 600; font-size: 13px; color: #1E40AF;">Meta Ads: <?= $metaAds ?></span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px; padding: 8px 16px; background: #FEF3C7; border-radius: 20px;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="#F59E0B">
                                <path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5z"/>
                            </svg>
                            <span style="font-weight: 600; font-size: 13px; color: #92400E;">Google Ads: <?= $googleAds ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="container">
                <!-- Filters -->
                <div class="card" style="margin-bottom: 20px;">
                    <div class="card-body" style="padding: 20px;">
                        <form method="GET" style="display: flex; gap: 16px; align-items: center;">
                            <div style="flex: 1;">
                                <input type="text" name="search" placeholder="🔍 Buscar por nome ou telefone..." value="<?= htmlspecialchars($search) ?>" style="width: 100%; padding: 10px 16px; border: 1px solid #E5E7EB; border-radius: 8px;">
                            </div>
                            <select name="origem" style="padding: 10px 16px; border: 1px solid #E5E7EB; border-radius: 8px;">
                                <option value="all" <?= $origem === 'all' ? 'selected' : '' ?>>💬 Todas as Origens</option>
                                <option value="meta" <?= $origem === 'meta' ? 'selected' : '' ?>>Meta Ads</option>
                                <option value="google" <?= $origem === 'google' ? 'selected' : '' ?>>Google Ads</option>
                                <option value="direct" <?= $origem === 'direct' ? 'selected' : '' ?>>Não rastreada</option>
                            </select>
                            <button type="submit" class="btn btn-primary">Filtrar</button>
                            <?php if ($search || $origem !== 'all'): ?>
                                <a href="/conversations.php" class="btn btn-secondary">Limpar</a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <!-- Conversations Table -->
                <div class="card">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th style="width: 60px;"></th>
                                    <th>Contato</th>
                                    <th>Origem</th>
                                    <th>Etapa da Jornada</th>
                                    <th>Primeira Mensagem</th>
                                    <th>Última Mensagem</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($conversations)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center">
                                            <div class="empty-state">
                                                <div class="empty-icon">💬</div>
                                                <h2>Nenhuma conversa encontrada</h2>
                                                <p>Conecte um WhatsApp para começar a rastrear conversas</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($conversations as $conv): ?>
                                        <tr style="cursor: pointer;" onclick="window.location='/conversation-detail.php?id=<?= $conv['id'] ?>'">
                                            <td>
                                                <div style="width: 40px; height: 40px; border-radius: 8px; background: linear-gradient(135deg, #6366F1, #818CF8); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700;">
                                                    <?= strtoupper(substr($conv['contact_name'] ?? 'U', 0, 1)) ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <div style="font-weight: 600; color: #111827; margin-bottom: 2px;">
                                                        <?= htmlspecialchars($conv['contact_name'] ?? 'Sem nome') ?>
                                                    </div>
                                                    <div style="font-size: 13px; color: #6B7280;">
                                                        <?= formatPhone($conv['contact_phone']) ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if ($conv['fbclid'] || (isset($conv['utm_source']) && (strpos($conv['utm_source'], 'meta') !== false || strpos($conv['utm_source'], 'facebook') !== false))): ?>
                                                    <div style="display: flex; flex-direction: column; gap: 4px;">
                                                        <div style="display: flex; align-items: center; gap: 6px;">
                                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="#3B82F6">
                                                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"/>
                                                            </svg>
                                                            <span style="font-weight: 600; font-size: 13px; color: #1E40AF;">Meta Ads</span>
                                                        </div>
                                                        <?php if ($conv['utm_campaign']): ?>
                                                            <div style="font-size: 12px; color: #6B7280;">
                                                                Via <?= htmlspecialchars($conv['utm_campaign']) ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php elseif ($conv['gclid'] || (isset($conv['utm_source']) && strpos($conv['utm_source'], 'google') !== false)): ?>
                                                    <div style="display: flex; flex-direction: column; gap: 4px;">
                                                        <div style="display: flex; align-items: center; gap: 6px;">
                                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="#F59E0B">
                                                                <path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5z"/>
                                                            </svg>
                                                            <span style="font-weight: 600; font-size: 13px; color: #92400E;">Google Ads via Link Rastreável</span>
                                                        </div>
                                                    </div>
                                                <?php elseif ($conv['utm_source']): ?>
                                                    <span class="badge badge-info"><?= htmlspecialchars($conv['utm_source']) ?></span>
                                                <?php else: ?>
                                                    <div style="display: flex; align-items: center; gap: 6px;">
                                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="#9CA3AF">
                                                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"/>
                                                        </svg>
                                                        <span style="font-size: 13px; color: #6B7280;">Não rastreada</span>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                $stage = 'Fez Contato';
                                                $stageColor = '#818CF8';
                                                if ($conv['is_sale']) {
                                                    $stage = 'Comprou';
                                                    $stageColor = '#10B981';
                                                } elseif ($conv['total_messages'] > 10) {
                                                    $stage = 'LEAD QUALIFICADO (SOLICITOU ORÇAMENTO)';
                                                    $stageColor = '#F59E0B';
                                                }
                                                ?>
                                                <span style="color: <?= $stageColor ?>; font-weight: 600; font-size: 13px;">
                                                    <?= $stage ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div style="font-size: 13px; color: #111827;">
                                                    <?= date('d/m/Y', strtotime($conv['created_at'])) ?>
                                                </div>
                                                <div style="font-size: 12px; color: #6B7280;">
                                                    <?= date('H:i:s', strtotime($conv['created_at'])) ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div style="font-size: 13px; color: #111827;">
                                                    <?= date('d/m/Y', strtotime($conv['last_message_at'])) ?>
                                                </div>
                                                <div style="font-size: 12px; color: #6B7280;">
                                                    <?= date('H:i:s', strtotime($conv['last_message_at'])) ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div style="display: flex; gap: 8px;">
                                                    <button class="btn btn-sm btn-secondary" onclick="event.stopPropagation();" title="Ver detalhes">
                                                        👁️
                                                    </button>
                                                    <button class="btn btn-sm btn-secondary" onclick="event.stopPropagation();" title="Enviar mensagem">
                                                        💬
                                                    </button>
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

    <script src="/frontend/js/app.js"></script>
</body>
</html>