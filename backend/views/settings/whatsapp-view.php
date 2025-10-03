<?php
$stmt = $db->prepare("SELECT * FROM whatsapp_numbers WHERE workspace_id = ? ORDER BY created_at DESC");
$stmt->execute([$currentWorkspace['id']]);
$whatsappNumbers = $stmt->fetchAll();
?>

<div style="margin-bottom: 20px;">
    <a href="/whatsapp.php" class="btn btn-primary">+ Conectar Novo Número</a>
</div>

<div class="whatsapp-grid">
    <?php if (empty($whatsappNumbers)): ?>
        <div class="card">
            <div class="card-body">
                <div class="empty-state">
                    <div class="empty-icon">📱</div>
                    <h2>Nenhum WhatsApp conectado</h2>
                    <p>Conecte um número para começar a rastrear conversas</p>
                </div>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($whatsappNumbers as $wa): ?>
            <div class="whatsapp-card">
                <div class="whatsapp-header">
                    <div class="whatsapp-info">
                        <h3><?= htmlspecialchars($wa['display_name']) ?></h3>
                        <div class="phone"><?= formatPhone($wa['phone_number']) ?></div>
                    </div>
                    <span class="status-badge status-<?= $wa['status'] ?>">
                        <?= ucfirst($wa['status']) ?>
                    </span>
                </div>
                
                <?php
                $stmt = $db->prepare("SELECT COUNT(*) as count FROM conversations WHERE whatsapp_number_id = ?");
                $stmt->execute([$wa['id']]);
                $convCount = $stmt->fetch()['count'];
                ?>
                
                <div class="whatsapp-stats">
                    <div class="stat-item">
                        <div class="stat-value"><?= $convCount ?></div>
                        <div class="stat-label">Conversas</div>
                    </div>
                </div>
                
                <div class="whatsapp-actions">
                    <a href="/whatsapp.php?id=<?= $wa['id'] ?>" class="btn btn-sm btn-secondary">Gerenciar</a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>





