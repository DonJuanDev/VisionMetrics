<?php
/**
 * VisionMetrics - WhatsApp Messages View
 * 
 * Display messages for a specific conversation
 */

require_once __DIR__ . '/../middleware.php';

$db = getDB();

$conversationId = $_GET['conversation_id'] ?? null;

if (!$conversationId) {
    redirect('/backend/whatsapp/conversations.php');
}

// Get conversation
$stmt = $db->prepare("
    SELECT c.*, 
           l.name as lead_name, l.email as lead_email, l.phone as lead_phone, l.id as lead_id
    FROM whatsapp_conversations c
    LEFT JOIN leads l ON c.lead_id = l.id
    WHERE c.id = ? AND c.workspace_id = ?
");
$stmt->execute([$conversationId, $currentWorkspace['id']]);
$conversation = $stmt->fetch();

if (!$conversation) {
    $_SESSION['error'] = 'Conversa não encontrada';
    redirect('/backend/whatsapp/conversations.php');
}

// Get messages
$stmt = $db->prepare("
    SELECT * FROM whatsapp_messages
    WHERE conversation_id = ?
    ORDER BY received_at ASC
");
$stmt->execute([$conversationId]);
$messages = $stmt->fetchAll();

$pageTitle = 'Conversa WhatsApp';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="/frontend/css/style.css">
    <link rel="stylesheet" href="/frontend/css/sidebar.css">
</head>
<body>
    <div class="app-layout">
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="top-bar">
                <div class="top-bar-title">
                    <h1>💬 <?= htmlspecialchars($conversation['lead_name'] ?: formatPhone($conversation['wa_from'])) ?></h1>
                    <p>📞 <?= formatPhone($conversation['wa_from']) ?></p>
                </div>
                <div class="top-bar-actions">
                    <a href="/backend/whatsapp/conversations.php" class="btn btn-secondary">← Voltar</a>
                    <?php if ($conversation['lead_id']): ?>
                        <a href="/backend/leads/lead-profile.php?id=<?= $conversation['lead_id'] ?>" class="btn btn-primary">
                            Ver Perfil do Lead
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="container">
                <!-- Conversation Info -->
                <div class="card" style="margin-bottom: 24px;">
                    <div style="display: flex; gap: 20px; align-items: start;">
                        <div style="width: 64px; height: 64px; border-radius: 50%; background: var(--gradient-primary); display: flex; align-items: center; justify-content: center; font-size: 28px; flex-shrink: 0;">
                            👤
                        </div>
                        <div style="flex: 1;">
                            <h2 style="font-size: 20px; margin-bottom: 8px; color: var(--text-primary);">
                                <?= htmlspecialchars($conversation['lead_name'] ?: 'Lead Anônimo') ?>
                            </h2>
                            <div style="display: flex; gap: 16px; flex-wrap: wrap;">
                                <span style="color: var(--text-secondary);">
                                    📞 <?= formatPhone($conversation['wa_from']) ?>
                                </span>
                                <?php if ($conversation['lead_email']): ?>
                                    <span style="color: var(--text-secondary);">
                                        ✉️ <?= htmlspecialchars($conversation['lead_email']) ?>
                                    </span>
                                <?php endif; ?>
                                <span style="color: var(--text-muted);">
                                    💬 <?= count($messages) ?> mensagens
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Messages -->
                <div class="card">
                    <div class="card-header">
                        <h2>Mensagens</h2>
                    </div>
                    <div style="padding: 24px;">
                        <?php if (empty($messages)): ?>
                            <div class="empty-state">
                                <p style="color: var(--text-muted);">Nenhuma mensagem ainda</p>
                            </div>
                        <?php else: ?>
                            <div style="display: flex; flex-direction: column; gap: 16px;">
                                <?php foreach ($messages as $msg): ?>
                                    <div style="display: flex; justify-content: <?= $msg['direction'] === 'outbound' ? 'flex-end' : 'flex-start' ?>;">
                                        <div style="max-width: 70%; padding: 12px 16px; border-radius: 12px; 
                                                    <?= $msg['direction'] === 'outbound' 
                                                        ? 'background: var(--gradient-primary); margin-left: auto;' 
                                                        : 'background: var(--bg-glass); border: 1px solid rgba(255,255,255,0.1);' ?>">
                                            
                                            <?php if ($msg['direction'] === 'inbound'): ?>
                                                <div style="font-size: 12px; font-weight: 600; color: var(--primary); margin-bottom: 4px;">
                                                    <?= htmlspecialchars($conversation['lead_name'] ?: 'Cliente') ?>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if ($msg['media_url']): ?>
                                                <div style="margin-bottom: 8px;">
                                                    <?php if ($msg['media_type'] === 'image'): ?>
                                                        <img src="<?= htmlspecialchars($msg['media_url']) ?>" 
                                                             style="max-width: 100%; border-radius: 8px;">
                                                    <?php else: ?>
                                                        <a href="<?= htmlspecialchars($msg['media_url']) ?>" 
                                                           target="_blank" 
                                                           style="color: var(--text-primary); text-decoration: underline;">
                                                            📎 <?= strtoupper($msg['media_type']) ?>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if ($msg['text']): ?>
                                                <div style="color: var(--text-primary); line-height: 1.5; white-space: pre-wrap; word-wrap: break-word;">
                                                    <?= nl2br(htmlspecialchars($msg['text'])) ?>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div style="font-size: 11px; color: <?= $msg['direction'] === 'outbound' ? 'rgba(255,255,255,0.7)' : 'var(--text-muted)' ?>; margin-top: 6px;">
                                                <?= $msg['received_at'] ? date('d/m/Y H:i', strtotime($msg['received_at'])) : '-' ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="/frontend/js/app.js"></script>
</body>
</html>

<?php
function formatPhone($phone) {
    $phone = preg_replace('/\D/', '', $phone);
    if (strlen($phone) >= 10) {
        $ddd = substr($phone, -11, 2);
        $num = substr($phone, -9);
        return "($ddd) " . substr($num, 0, 5) . "-" . substr($num, 5);
    }
    return $phone;
}
?>



