<?php
/**
 * VisionMetrics - WhatsApp Conversations List
 * 
 * Display all WhatsApp conversations for workspace
 * With search and filtering
 */

require_once __DIR__ . '/../middleware.php';

$db = getDB();

// Search
$search = $_GET['search'] ?? '';

// Get conversations
$sql = "
    SELECT c.*, 
           l.name as lead_name, l.email as lead_email, l.phone as lead_phone,
           (SELECT COUNT(*) FROM whatsapp_messages WHERE conversation_id = c.id) as message_count
    FROM whatsapp_conversations c
    LEFT JOIN leads l ON c.lead_id = l.id
    WHERE c.workspace_id = ?
";

$params = [$currentWorkspace['id']];

if ($search) {
    $sql .= " AND (c.wa_from LIKE ? OR l.name LIKE ? OR c.snippet LIKE ?)";
    $searchParam = '%' . $search . '%';
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
}

$sql .= " ORDER BY c.last_message_at DESC LIMIT 100";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$conversations = $stmt->fetchAll();

$pageTitle = 'Conversas WhatsApp';
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
                    <h1>💬 Conversas WhatsApp</h1>
                    <p>Todas as conversas recebidas via WhatsApp</p>
                </div>
            </div>

            <div class="container">
                <!-- Search -->
                <div class="card" style="margin-bottom: 24px;">
                    <form method="GET" style="display: flex; gap: 12px;">
                        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                               placeholder="Buscar por telefone, nome ou mensagem..." 
                               style="flex: 1; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1); background: var(--bg-glass); color: var(--text-primary);">
                        <button type="submit" class="btn btn-primary">🔍 Buscar</button>
                        <?php if ($search): ?>
                            <a href="/backend/whatsapp/conversations.php" class="btn btn-secondary">Limpar</a>
                        <?php endif; ?>
                    </form>
                </div>

                <!-- Conversations List -->
                <div class="card">
                    <?php if (empty($conversations)): ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">💬</div>
                            <h3>Nenhuma conversa ainda</h3>
                            <p>As conversas aparecerão aqui quando você receber mensagens no WhatsApp</p>
                        </div>
                    <?php else: ?>
                        <div style="display: grid; gap: 16px;">
                            <?php foreach ($conversations as $conv): ?>
                                <a href="/backend/whatsapp/messages.php?conversation_id=<?= $conv['id'] ?>" 
                                   class="card" 
                                   style="margin: 0; text-decoration: none; transition: all 0.3s ease; cursor: pointer;"
                                   onmouseover="this.style.transform='translateX(8px)'"
                                   onmouseout="this.style.transform='translateX(0)'">
                                    <div style="display: flex; gap: 16px; align-items: start;">
                                        <!-- Avatar -->
                                        <div style="width: 48px; height: 48px; border-radius: 50%; background: var(--gradient-primary); display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                                            👤
                                        </div>
                                        
                                        <!-- Content -->
                                        <div style="flex: 1; min-width: 0;">
                                            <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                                                <h3 style="font-size: 16px; font-weight: 600; color: var(--text-primary); margin: 0;">
                                                    <?= htmlspecialchars($conv['lead_name'] ?: formatPhone($conv['wa_from'])) ?>
                                                </h3>
                                                <span style="font-size: 12px; color: var(--text-muted); white-space: nowrap;">
                                                    <?= $conv['last_message_at'] ? date('d/m H:i', strtotime($conv['last_message_at'])) : '-' ?>
                                                </span>
                                            </div>
                                            
                                            <p style="font-size: 14px; color: var(--text-muted); margin: 0;">
                                                📞 <?= formatPhone($conv['wa_from']) ?>
                                            </p>
                                            
                                            <?php if ($conv['snippet']): ?>
                                                <p style="font-size: 14px; color: var(--text-secondary); margin: 8px 0 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                    <?= htmlspecialchars($conv['snippet']) ?>
                                                </p>
                                            <?php endif; ?>
                                            
                                            <div style="margin-top: 8px;">
                                                <span class="badge badge-info" style="font-size: 11px;">
                                                    <?= $conv['message_count'] ?> mensagens
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
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



