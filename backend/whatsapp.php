<?php
require_once __DIR__ . '/middleware.php';

$db = getDB();

// Add WhatsApp number
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_whatsapp'])) {
    $phoneNumber = preg_replace('/\D/', '', $_POST['phone_number']);
    $displayName = trim($_POST['display_name']);
    
    $stmt = $db->prepare("INSERT INTO whatsapp_numbers (workspace_id, phone_number, display_name, status) VALUES (?, ?, ?, 'connected')");
    $stmt->execute([$currentWorkspace['id'], $phoneNumber, $displayName]);
    
    $_SESSION['success'] = 'WhatsApp conectado com sucesso!';
    redirect('/whatsapp.php');
}

// Get WhatsApp numbers
$stmt = $db->prepare("SELECT * FROM whatsapp_numbers WHERE workspace_id = ? ORDER BY created_at DESC");
$stmt->execute([$currentWorkspace['id']]);
$whatsappNumbers = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatsApp - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="/frontend/css/style.css">
    <link rel="stylesheet" href="/frontend/css/sidebar.css">
</head>
<body>
    <div class="app-layout">
        <?php include __DIR__ . '/partials/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="top-bar">
                <div class="top-bar-title">
                    <h1>WhatsApp Business</h1>
                    <p>Conecte números e rastreie conversas</p>
                </div>
                <div class="top-bar-actions">
                    <button onclick="showAddModal()" class="btn btn-primary">+ Conectar Novo Número</button>
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

                <?php if (empty($whatsappNumbers)): ?>
                    <div class="card">
                        <div class="card-body">
                            <div class="empty-state">
                                <div style="width: 120px; height: 120px; margin: 0 auto 24px; border-radius: 30px; background: linear-gradient(135deg, #10B981 0%, #059669 100%); display: flex; align-items: center; justify-content: center; box-shadow: 0 20px 40px rgba(16, 185, 129, 0.3);">
                                    <svg width="64" height="64" fill="white" viewBox="0 0 24 24">
                                        <path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                    </svg>
                                </div>
                                <h2 style="font-size: 28px; margin-bottom: 12px;">Conecte seu WhatsApp Business</h2>
                                <p style="font-size: 16px; max-width: 500px; margin: 0 auto 32px;">
                                    Rastreie automaticamente todas as conversas e atribua vendas às suas campanhas de marketing
                                </p>
                                <button onclick="showAddModal()" class="btn btn-primary" style="padding: 14px 32px; font-size: 16px;">
                                    <svg width="20" height="20" fill="white" viewBox="0 0 24 24">
                                        <path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                    </svg>
                                    Conectar WhatsApp Agora
                                </button>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 20px;">
                        <?php foreach ($whatsappNumbers as $wa): ?>
                            <div class="card" style="border: 2px solid <?= $wa['status'] === 'connected' ? '#10B981' : '#E5E7EB' ?>;">
                                <div class="card-body">
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
                                        <div style="display: flex; align-items: center; gap: 16px;">
                                            <div style="width: 56px; height: 56px; border-radius: 14px; background: linear-gradient(135deg, #10B981 0%, #059669 100%); display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 16px rgba(16, 185, 129, 0.25);">
                                                <svg width="28" height="28" fill="white" viewBox="0 0 24 24">
                                                    <path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <h3 style="font-weight: 700; margin-bottom: 4px;"><?= htmlspecialchars($wa['display_name']) ?></h3>
                                                <div style="font-size: 13px; color: #6B7280;"><?= formatPhone($wa['phone_number']) ?></div>
                                            </div>
                                        </div>
                                        <span class="badge badge-<?= $wa['status'] === 'connected' ? 'success' : 'warning' ?>">
                                            <?= $wa['status'] === 'connected' ? '● Conectado' : '○ Desconectado' ?>
                                        </span>
                                    </div>

                                    <?php
                                    $stmt = $db->prepare("SELECT COUNT(*) as count FROM conversations WHERE whatsapp_number_id = ?");
                                    $stmt->execute([$wa['id']]);
                                    $convCount = $stmt->fetch()['count'];
                                    ?>

                                    <div style="background: var(--gray-50); padding: 16px; border-radius: 10px; margin-bottom: 16px;">
                                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; text-align: center;">
                                            <div>
                                                <div style="font-size: 24px; font-weight: 700; color: #4F46E5;"><?= $convCount ?></div>
                                                <div style="font-size: 12px; color: #6B7280;">Conversas</div>
                                            </div>
                                            <div>
                                                <div style="font-size: 24px; font-weight: 700; color: #10B981;">0</div>
                                                <div style="font-size: 12px; color: #6B7280;">Vendas</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div style="display: flex; gap: 10px;">
                                        <button class="btn btn-secondary btn-block">Configurar</button>
                                        <button class="btn btn-danger btn-block">Desconectar</button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal Add WhatsApp -->
    <div id="modalAdd" style="display:none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 2000; align-items: center; justify-content: center;">
        <div style="background: white; padding: 40px; border-radius: 20px; max-width: 500px; width: 90%; box-shadow: 0 25px 50px rgba(0,0,0,0.3);">
            <div style="text-align: center; margin-bottom: 32px;">
                <div style="width: 80px; height: 80px; margin: 0 auto 20px; border-radius: 20px; background: linear-gradient(135deg, #10B981 0%, #059669 100%); display: flex; align-items: center; justify-content: center;">
                    <svg width="40" height="40" fill="white" viewBox="0 0 24 24">
                        <path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </div>
                <h2 style="font-size: 24px; font-weight: 700; margin-bottom: 8px;">Conectar WhatsApp</h2>
                <p style="color: #6B7280;">Adicione um número para rastrear conversas</p>
            </div>

            <form method="POST">
                <div class="form-group">
                    <label>Nome de Exibição *</label>
                    <input type="text" name="display_name" required placeholder="Ex: Atendimento Principal" autofocus>
                </div>

                <div class="form-group">
                    <label>Número do WhatsApp *</label>
                    <input type="tel" name="phone_number" required placeholder="Ex: 5511999999999">
                    <small class="help-text">Formato: Código do país + DDD + Número (somente números)</small>
                </div>

                <div style="display: flex; gap: 12px; margin-top: 32px;">
                    <button type="submit" name="add_whatsapp" class="btn btn-primary" style="flex: 1; padding: 14px;">
                        Conectar WhatsApp
                    </button>
                    <button type="button" onclick="document.getElementById('modalAdd').style.display='none'" class="btn btn-secondary">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showAddModal() {
            document.getElementById('modalAdd').style.display = 'flex';
        }
    </script>
    <script src="/frontend/js/app.js"></script>
</body>
</html>