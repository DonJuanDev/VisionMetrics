<?php
require_once __DIR__ . '/middleware.php';

$db = getDB();

// Get workspace/client info
$stmt = $db->prepare("SELECT * FROM workspaces WHERE id = ?");
$stmt->execute([$currentWorkspace['id']]);
$workspace = $stmt->fetch();

// Get WhatsApp info
$stmt = $db->prepare("SELECT * FROM whatsapp_numbers WHERE workspace_id = ? LIMIT 1");
$stmt->execute([$currentWorkspace['id']]);
$whatsapp = $stmt->fetch();

// Get integrations
$stmt = $db->prepare("SELECT * FROM integrations WHERE workspace_id = ? AND is_active = 1");
$stmt->execute([$currentWorkspace['id']]);
$integrations = $stmt->fetchAll();

// Get API key
$stmt = $db->prepare("SELECT * FROM api_keys WHERE workspace_id = ? LIMIT 1");
$stmt->execute([$currentWorkspace['id']]);
$apiKey = $stmt->fetch();

// Check if api_keys table exists, if not set to false
if (!$apiKey) {
    $apiKey = false;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informações do Cliente - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="/frontend/css/style.css">
    <link rel="stylesheet" href="/frontend/css/sidebar.css">
</head>
<body>
    <div class="app-layout">
        <?php include __DIR__ . '/partials/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="top-bar">
                <div class="top-bar-title">
                    <h1>Informações do Cliente - <?= htmlspecialchars($workspace['name']) ?></h1>
                    <p>Início > Informações do Cliente <?= htmlspecialchars($workspace['name']) ?></p>
                </div>
                <div class="top-bar-actions">
                    <button class="btn btn-primary">Editar</button>
                </div>
            </div>

            <div class="container">
                <div style="display: grid; grid-template-columns: 300px 1fr; gap: 24px;">
                    <!-- Sidebar Info -->
                    <div>
                        <div class="card">
                            <div class="card-body" style="text-align: center;">
                                <div style="width: 120px; height: 120px; margin: 0 auto 20px; border-radius: 50%; background: linear-gradient(135deg, #6366F1, #818CF8); display: flex; align-items: center; justify-content: center; color: white; font-size: 48px; font-weight: 700;">
                                    <?= strtoupper(substr($workspace['name'], 0, 2)) ?>
                                </div>
                                
                                <h3 style="margin-bottom: 8px;">Nome do Estabelecimento</h3>
                                <h2 style="color: #111827; margin-bottom: 16px;"><?= htmlspecialchars($workspace['name']) ?></h2>
                                
                                <?php if ($whatsapp): ?>
                                    <div style="background: #D1FAE5; padding: 12px; border-radius: 8px; margin-bottom: 12px;">
                                        <div style="display: flex; align-items: center; justify-content: center; gap: 8px;">
                                            <svg width="18" height="18" fill="#10B981" viewBox="0 0 24 24">
                                                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <span style="color: #065F46; font-weight: 600; font-size: 14px;">WhatsApp Conectado</span>
                                        </div>
                                    </div>
                                    
                                    <div style="text-align: left; margin-bottom: 16px;">
                                        <div style="font-size: 13px; color: #6B7280; margin-bottom: 4px;">WhatsApp de Atendimento</div>
                                        <div style="font-weight: 600;"><?= formatPhone($whatsapp['phone_number']) ?></div>
                                    </div>
                                    
                                    <div style="text-align: left; margin-bottom: 16px;">
                                        <div style="font-size: 13px; color: #6B7280; margin-bottom: 4px;">Mensagem Inicial de Atendimento</div>
                                        <div style="font-size: 13px; color: #111827;">Olá, gostaria de mais informações.</div>
                                    </div>
                                    
                                    <button class="btn btn-danger btn-block">Desconectar WhatsApp</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div>
                        <!-- Meta Business Info -->
                        <?php
                        $metaIntegration = null;
                        foreach ($integrations as $int) {
                            if ($int['type'] === 'meta_ads') {
                                $metaIntegration = $int;
                                break;
                            }
                        }
                        ?>
                        
                        <?php if ($metaIntegration): ?>
                            <?php $credentials = json_decode($metaIntegration['credentials'], true); ?>
                            <div class="card" style="margin-bottom: 20px;">
                                <div class="card-header">
                                    <h2>🔍 Pixel do Tintim</h2>
                                    <button class="btn btn-sm btn-primary">Editar</button>
                                </div>
                                <div class="card-body">
                                    <div style="margin-bottom: 16px;">
                                        <div style="font-size: 13px; color: #6B7280; margin-bottom: 6px;">ID do Meta Ads</div>
                                        <div style="background: #F9FAFB; padding: 12px; border-radius: 6px; font-family: monospace; font-size: 14px;">
                                            <?= htmlspecialchars($credentials['pixel_id']) ?>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <div style="font-size: 13px; color: #6B7280; margin-bottom: 6px;">Token de Conversão da API</div>
                                        <div style="background: #F9FAFB; padding: 12px; border-radius: 6px; font-family: monospace; font-size: 11px; word-break: break-all;">
                                            <?= substr($credentials['access_token'], 0, 40) ?>...
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Webhook Endpoints -->
                        <div class="card">
                            <div class="card-header">
                                <h2>🌐 Endereço de Webhooks</h2>
                                <button class="btn btn-sm btn-primary">Editar</button>
                            </div>
                            <div class="card-body">
                                <?php if ($whatsapp && $whatsapp['webhook_url']): ?>
                                    <div style="margin-bottom: 20px;">
                                        <div style="font-weight: 600; margin-bottom: 8px;">Criação de Conversa</div>
                                        <div style="background: #F9FAFB; padding: 12px; border-radius: 6px; font-family: monospace; font-size: 12px; word-break: break-all;">
                                            <?= htmlspecialchars($whatsapp['webhook_url']) ?>
                                        </div>
                                    </div>
                                    
                                    <div style="margin-bottom: 20px;">
                                        <div style="font-weight: 600; margin-bottom: 8px;">Alteração de Conversa</div>
                                        <div style="background: #F9FAFB; padding: 12px; border-radius: 6px; font-family: monospace; font-size: 12px; word-break: break-all;">
                                            <?= htmlspecialchars($whatsapp['webhook_url']) ?>
                                        </div>
                                    </div>
                                    
                                    <div style="margin-bottom: 20px;">
                                        <div style="font-weight: 600; margin-bottom: 8px;">Criação de Mensagem</div>
                                        <div style="background: #F9FAFB; padding: 12px; border-radius: 6px; font-family: monospace; font-size: 12px; word-break: break-all;">
                                            <?= htmlspecialchars($whatsapp['webhook_url']) ?>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <div style="font-weight: 600; margin-bottom: 8px;">Alteração da Origem de Conversa</div>
                                        <div style="background: #F9FAFB; padding: 12px; border-radius: 6px; font-family: monospace; font-size: 12px; word-break: break-all;">
                                            <?= htmlspecialchars($whatsapp['webhook_url']) ?>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <p style="color: #6B7280;">Nenhum webhook configurado</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="/frontend/js/app.js"></script>
</body>
</html>




