<?php
require_once __DIR__ . '/middleware.php';

$db = getDB();
$leadId = $_GET['id'] ?? 0;

// Get lead data
$stmt = $db->prepare("
    SELECT l.*, 
           w.phone_number as whatsapp_number,
           COUNT(DISTINCT c.id) as conversation_count,
           COUNT(DISTINCT e.id) as event_count
    FROM leads l
    LEFT JOIN whatsapp_numbers w ON l.whatsapp_number_id = w.id
    LEFT JOIN conversations c ON l.id = c.lead_id
    LEFT JOIN events e ON l.id = e.lead_id
    WHERE l.id = ? AND l.workspace_id = ?
    GROUP BY l.id
");
$stmt->execute([$leadId, $currentWorkspace['id']]);
$lead = $stmt->fetch();

if (!$lead) {
    $_SESSION['error'] = 'Lead não encontrado';
    redirect('/leads.php');
}

// Get timeline (eventos + conversas)
$stmt = $db->prepare("
    (SELECT 'event' as type, event_type as title, page_url as description, created_at as date, utm_source, utm_campaign
     FROM events WHERE lead_id = ?)
    UNION ALL
    (SELECT 'conversation' as type, 'Nova Conversa' as title, contact_name as description, created_at as date, utm_source, utm_campaign
     FROM conversations WHERE lead_id = ?)
    ORDER BY date DESC
    LIMIT 50
");
$stmt->execute([$leadId, $leadId]);
$timeline = $stmt->fetchAll();

// Get tags
$stmt = $db->prepare("
    SELECT t.* FROM tags t
    INNER JOIN lead_tags lt ON t.id = lt.tag_id
    WHERE lt.lead_id = ?
");
$stmt->execute([$leadId]);
$tags = $stmt->fetchAll();

// Get notes
$stmt = $db->prepare("
    SELECT n.*, u.name as user_name
    FROM notes n
    LEFT JOIN users u ON n.user_id = u.id
    WHERE n.lead_id = ?
    ORDER BY n.created_at DESC
");
$stmt->execute([$leadId]);
$notes = $stmt->fetchAll();

// Add note
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_note'])) {
    $content = trim($_POST['content']);
    
    $stmt = $db->prepare("INSERT INTO notes (workspace_id, lead_id, user_id, content) VALUES (?, ?, ?, ?)");
    $stmt->execute([$currentWorkspace['id'], $leadId, $currentUser['id'], $content]);
    
    $_SESSION['success'] = 'Nota adicionada!';
    redirect('/lead-profile.php?id=' . $leadId);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($lead['name']) ?> - Perfil 360° - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="/frontend/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/partials/header.php'; ?>

    <div class="container">
        <!-- Lead Header -->
        <div class="card">
            <div class="card-body">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 20px;">
                        <div style="width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, #6366F1, #818CF8); display: flex; align-items: center; justify-content: center; color: white; font-size: 32px; font-weight: 700;">
                            <?= strtoupper(substr($lead['name'], 0, 1)) ?>
                        </div>
                        <div>
                            <h1 style="margin: 0; font-size: 28px;"><?= htmlspecialchars($lead['name']) ?></h1>
                            <p style="margin: 5px 0; color: #6B7280;">
                                <?= htmlspecialchars($lead['email']) ?> 
                                <?php if ($lead['phone_number']): ?>
                                    • <?= formatPhone($lead['phone_number']) ?>
                                <?php endif; ?>
                            </p>
                            <div style="margin-top: 10px; display: flex; gap: 8px;">
                                <?php foreach ($tags as $tag): ?>
                                    <span class="badge" style="background: <?= $tag['color'] ?>; color: white;">
                                        <?= htmlspecialchars($tag['name']) ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <div style="text-align: center;">
                        <div class="score-badge score-<?= $lead['score'] >= 70 ? 'high' : ($lead['score'] >= 40 ? 'medium' : 'low') ?>" style="width: 60px; height: 60px; font-size: 24px;">
                            <?= $lead['score'] ?>
                        </div>
                        <small style="color: #6B7280; margin-top: 5px; display: block;">Lead Score</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="dashboard-grid">
            <!-- Stats -->
            <div class="card">
                <div class="card-header">
                    <h2>📊 Métricas de Engajamento</h2>
                </div>
                <div class="card-body">
                    <div class="stats-grid" style="grid-template-columns: 1fr 1fr;">
                        <div>
                            <div style="font-size: 32px; font-weight: 700; color: #6366F1;"><?= $lead['conversation_count'] ?></div>
                            <div style="font-size: 12px; color: #6B7280; text-transform: uppercase;">Conversas</div>
                        </div>
                        <div>
                            <div style="font-size: 32px; font-weight: 700; color: #10B981;"><?= $lead['total_messages'] ?></div>
                            <div style="font-size: 12px; color: #6B7280; text-transform: uppercase;">Mensagens</div>
                        </div>
                        <div>
                            <div style="font-size: 32px; font-weight: 700; color: #F59E0B;"><?= $lead['event_count'] ?></div>
                            <div style="font-size: 12px; color: #6B7280; text-transform: uppercase;">Eventos</div>
                        </div>
                        <div>
                            <div style="font-size: 32px; font-weight: 700; color: #10B981;"><?= formatCurrency($lead['total_sales']) ?></div>
                            <div style="font-size: 12px; color: #6B7280; text-transform: uppercase;">Vendas</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Info -->
            <div class="card">
                <div class="card-header">
                    <h2>ℹ️ Informações</h2>
                </div>
                <div class="card-body">
                    <div class="info-grid">
                        <div class="info-item">
                            <span>Etapa:</span>
                            <span class="badge badge-primary"><?= ucfirst($lead['stage']) ?></span>
                        </div>
                        <div class="info-item">
                            <span>Criado em:</span>
                            <span><?= date('d/m/Y H:i', strtotime($lead['created_at'])) ?></span>
                        </div>
                        <div class="info-item">
                            <span>Última atividade:</span>
                            <span><?= timeAgo($lead['last_seen']) ?></span>
                        </div>
                        <?php if ($lead['city']): ?>
                            <div class="info-item">
                                <span>Localização:</span>
                                <span><?= htmlspecialchars($lead['city']) ?>, <?= htmlspecialchars($lead['state']) ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if ($lead['browser']): ?>
                            <div class="info-item">
                                <span>Navegador:</span>
                                <span><?= htmlspecialchars($lead['browser']) ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if ($lead['device_type']): ?>
                            <div class="info-item">
                                <span>Dispositivo:</span>
                                <span><?= ucfirst($lead['device_type']) ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Timeline -->
        <div class="card">
            <div class="card-header">
                <h2>⏱️ Timeline de Atividades</h2>
            </div>
            <div class="card-body">
                <div style="position: relative;">
                    <?php foreach ($timeline as $index => $item): ?>
                        <div style="padding: 20px 0; border-left: 2px solid #E5E7EB; padding-left: 30px; position: relative;">
                            <div style="position: absolute; left: -9px; top: 20px; width: 16px; height: 16px; border-radius: 50%; background: <?= $item['type'] === 'event' ? '#6366F1' : '#10B981' ?>;"></div>
                            <div style="font-size: 14px; font-weight: 600; color: #111827; margin-bottom: 4px;">
                                <?= htmlspecialchars($item['title']) ?>
                                <?php if ($item['utm_source']): ?>
                                    <span class="badge badge-info" style="margin-left: 8px;"><?= htmlspecialchars($item['utm_source']) ?></span>
                                <?php endif; ?>
                            </div>
                            <?php if ($item['description']): ?>
                                <div style="font-size: 13px; color: #6B7280; margin-bottom: 4px;"><?= htmlspecialchars($item['description']) ?></div>
                            <?php endif; ?>
                            <div style="font-size: 12px; color: #9CA3AF;"><?= timeAgo($item['date']) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Notes -->
        <div class="card">
            <div class="card-header">
                <h2>📝 Notas</h2>
            </div>
            <div class="card-body">
                <form method="POST" style="margin-bottom: 20px;">
                    <div class="form-group">
                        <textarea name="content" rows="3" placeholder="Adicionar uma nota..." required></textarea>
                    </div>
                    <button type="submit" name="add_note" class="btn btn-primary">Adicionar Nota</button>
                </form>

                <?php foreach ($notes as $note): ?>
                    <div style="background: #F9FAFB; padding: 16px; border-radius: 8px; margin-bottom: 12px; border-left: 3px solid #6366F1;">
                        <div style="font-size: 14px; color: #111827; margin-bottom: 8px;"><?= nl2br(htmlspecialchars($note['content'])) ?></div>
                        <div style="font-size: 12px; color: #6B7280;">
                            <?= htmlspecialchars($note['user_name']) ?> • <?= timeAgo($note['created_at']) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script src="/frontend/js/app.js"></script>
</body>
</html>





