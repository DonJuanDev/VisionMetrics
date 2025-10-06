<?php
/**
 * VisionMetrics - WhatsApp Integrations Dashboard
 * 
 * Manage WhatsApp QR integrations per workspace
 * List existing integrations and create new QR sessions
 */

require_once __DIR__ . '/../../middleware.php';
require_once __DIR__ . '/../../../src/bootstrap.php';

use VisionMetrics\Integrations\WhatsappIntegration;

$db = getDB();
$integration = new WhatsappIntegration($db);

// Get all integrations for current workspace
$integrations = $integration->getByWorkspace($currentWorkspace['id']);

// Get active sessions
$activeSessions = [];
foreach ($integrations as $int) {
    $session = $integration->getActiveSession($int['id']);
    if ($session) {
        $activeSessions[$int['id']] = $session;
    }
}

$pageTitle = 'Integrações WhatsApp';
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
        <?php include __DIR__ . '/../../partials/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="top-bar">
                <div class="top-bar-title">
                    <h1>💬 WhatsApp Integrations</h1>
                    <p>Conecte WhatsApp via QR Code para rastrear conversas</p>
                </div>
                <div class="top-bar-actions">
                    <button onclick="showConnectModal()" class="btn btn-primary">
                        + Conectar WhatsApp (QR)
                    </button>
                </div>
            </div>

            <div class="container">
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
                <?php endif; ?>

                <!-- Info Card -->
                <div class="card" style="background: rgba(59, 130, 246, 0.1); border-left: 4px solid var(--secondary); margin-bottom: 24px;">
                    <div class="card-body">
                        <h3 style="color: var(--text-primary); margin-bottom: 12px;">📱 Como funciona?</h3>
                        <ol style="color: var(--text-secondary); line-height: 1.8;">
                            <li>Clique em "Conectar WhatsApp (QR)" acima</li>
                            <li>Escaneie o QR Code com seu WhatsApp (Dispositivos vinculados)</li>
                            <li>Aguarde a confirmação de conexão</li>
                            <li>Todas as mensagens recebidas serão rastreadas automaticamente</li>
                            <li>Leads serão identificados via vm_token ou número de telefone</li>
                        </ol>
                        <p style="margin-top: 12px; color: var(--text-muted); font-size: 14px;">
                            💡 <strong>Tip:</strong> Use links rastreáveis com utm_source=whatsapp para atribuição completa
                        </p>
                    </div>
                </div>

                <!-- Integrations List -->
                <div class="card">
                    <div class="card-header">
                        <h2>Suas Integrações WhatsApp</h2>
                    </div>
                    <div class="card-body">
                        <?php if (empty($integrations)): ?>
                            <div class="empty-state">
                                <div class="empty-state-icon">💬</div>
                                <h3>Nenhuma integração WhatsApp</h3>
                                <p>Conecte seu WhatsApp via QR Code para começar</p>
                                <button onclick="showConnectModal()" class="btn btn-primary" style="margin-top: 16px;">
                                    Conectar WhatsApp
                                </button>
                            </div>
                        <?php else: ?>
                            <div style="display: grid; gap: 20px;">
                                <?php foreach ($integrations as $int): ?>
                                    <?php 
                                    $meta = $int['meta'] ? json_decode($int['meta'], true) : [];
                                    $session = $activeSessions[$int['id']] ?? null;
                                    $statusClass = $int['status'] === 'active' ? 'success' : ($int['status'] === 'error' ? 'warning' : 'secondary');
                                    ?>
                                    <div class="card" style="margin: 0;">
                                        <div style="display: flex; justify-content: space-between; align-items: start;">
                                            <div style="flex: 1;">
                                                <h3 style="font-size: 18px; margin-bottom: 8px; color: var(--text-primary);">
                                                    <?= htmlspecialchars($int['name'] ?: 'WhatsApp Integration') ?>
                                                </h3>
                                                <div style="display: flex; gap: 12px; margin-bottom: 12px;">
                                                    <span class="badge badge-<?= $statusClass ?>">
                                                        <?= strtoupper($int['status']) ?>
                                                    </span>
                                                    <span class="badge badge-info">
                                                        <?= strtoupper($int['provider']) ?>
                                                    </span>
                                                </div>
                                                
                                                <?php if (!empty($meta['phone'])): ?>
                                                    <p style="color: var(--text-secondary); margin-bottom: 8px;">
                                                        📞 <strong>Número:</strong> <?= htmlspecialchars($meta['phone']) ?>
                                                    </p>
                                                <?php endif; ?>
                                                
                                                <?php if ($session): ?>
                                                    <p style="color: var(--text-muted); font-size: 14px;">
                                                        🔗 <strong>Sessão:</strong> <?= ucfirst($session['status']) ?>
                                                        <?php if ($session['last_heartbeat']): ?>
                                                            (última verificação: <?= date('d/m/Y H:i', strtotime($session['last_heartbeat'])) ?>)
                                                        <?php endif; ?>
                                                    </p>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <div style="display: flex; gap: 8px;">
                                                <?php if ($int['status'] === 'inactive'): ?>
                                                    <button onclick="reconnect(<?= $int['id'] ?>)" class="btn btn-sm btn-primary">
                                                        Reconectar
                                                    </button>
                                                <?php endif; ?>
                                                
                                                <?php if ($int['status'] === 'active'): ?>
                                                    <button onclick="disconnect(<?= $int['id'] ?>)" class="btn btn-sm btn-secondary">
                                                        Desconectar
                                                    </button>
                                                <?php endif; ?>
                                                
                                                <button onclick="deleteIntegration(<?= $int['id'] ?>)" class="btn btn-sm btn-secondary" title="Remover">
                                                    🗑️
                                                </button>
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

    <!-- Modal: Connect WhatsApp QR -->
    <div id="modalConnect" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h2>💬 Conectar WhatsApp via QR</h2>
                <button type="button" class="modal-close" onclick="closeModal()">×</button>
            </div>
            
            <div class="modal-body">
                <div id="step1" style="display: block;">
                    <p style="margin-bottom: 20px; color: var(--text-secondary);">
                        Escolha seu provedor BSP ou use as credenciais padrão
                    </p>
                    
                    <div class="form-group">
                        <label>Provedor BSP</label>
                        <select id="provider" class="form-control">
                            <option value="360dialog">360Dialog</option>
                            <option value="infobip">Infobip</option>
                            <option value="twilio">Twilio</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>API Key</label>
                        <input type="text" id="api_key" placeholder="Sua API key do provedor">
                        <small class="help-text">Deixe vazio para usar credenciais padrão do sistema</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Nome da Integração (opcional)</label>
                        <input type="text" id="integration_name" placeholder="Ex: WhatsApp Principal">
                    </div>
                </div>
                
                <div id="step2" style="display: none; text-align: center;">
                    <div id="qr-loading" style="padding: 40px;">
                        <div class="spinner"></div>
                        <p style="margin-top: 20px; color: var(--text-secondary);">Gerando QR Code...</p>
                    </div>
                    
                    <div id="qr-display" style="display: none;">
                        <h3 style="margin-bottom: 20px; color: var(--text-primary);">Escaneie com WhatsApp</h3>
                        <img id="qr-image" style="max-width: 100%; border-radius: 12px; border: 2px solid rgba(255,255,255,0.1);">
                        
                        <div style="margin-top: 24px; padding: 16px; background: rgba(59, 130, 246, 0.1); border-radius: 8px;">
                            <p style="color: var(--text-secondary); margin-bottom: 12px;">
                                <strong>Como escanear:</strong>
                            </p>
                            <ol style="text-align: left; color: var(--text-muted); font-size: 14px; line-height: 1.8;">
                                <li>Abra WhatsApp no celular</li>
                                <li>Toque em ⋮ (Android) ou Configurações (iPhone)</li>
                                <li>Toque em "Dispositivos vinculados"</li>
                                <li>Toque em "Vincular dispositivo"</li>
                                <li>Aponte para este QR Code</li>
                            </ol>
                        </div>
                        
                        <div id="status-message" style="margin-top: 20px; padding: 12px; background: rgba(245, 158, 11, 0.1); border-radius: 8px;">
                            <p style="color: var(--warning);">⏳ Aguardando escaneamento...</p>
                        </div>
                    </div>
                </div>
                
                <div id="step3" style="display: none; text-align: center;">
                    <div style="padding: 40px;">
                        <div style="width: 80px; height: 80px; margin: 0 auto 20px; background: var(--gradient-secondary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 40px;">
                            ✓
                        </div>
                        <h3 style="color: var(--text-primary); margin-bottom: 12px;">WhatsApp Conectado!</h3>
                        <p style="color: var(--text-secondary);">Todas as mensagens serão rastreadas automaticamente.</p>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button id="btnGenerate" onclick="generateQR()" class="btn btn-primary" style="flex: 1;">
                    Gerar QR Code
                </button>
                <button onclick="closeModal()" class="btn btn-secondary">Fechar</button>
            </div>
        </div>
    </div>

    <style>
        .spinner {
            border: 4px solid rgba(255,255,255,0.1);
            border-top: 4px solid var(--primary);
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>

    <script>
        let pollingInterval = null;
        let currentSessionId = null;

        function showConnectModal() {
            document.getElementById('modalConnect').style.display = 'flex';
            document.getElementById('step1').style.display = 'block';
            document.getElementById('step2').style.display = 'none';
            document.getElementById('step3').style.display = 'none';
        }

        function closeModal() {
            document.getElementById('modalConnect').style.display = 'none';
            if (pollingInterval) {
                clearInterval(pollingInterval);
                pollingInterval = null;
            }
        }

        async function generateQR() {
            const provider = document.getElementById('provider').value;
            const apiKey = document.getElementById('api_key').value;
            const name = document.getElementById('integration_name').value;

            // Hide step 1, show step 2 loading
            document.getElementById('step1').style.display = 'none';
            document.getElementById('step2').style.display = 'block';
            document.getElementById('qr-loading').style.display = 'block';
            document.getElementById('qr-display').style.display = 'none';
            document.getElementById('btnGenerate').style.display = 'none';

            try {
                const response = await fetch('/backend/integrations/whatsapp/connect_qr.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ provider, api_key: apiKey, name })
                });

                const data = await response.json();

                if (!response.ok || data.error) {
                    throw new Error(data.error || 'Erro ao gerar QR');
                }

                // Show QR code
                currentSessionId = data.session_id;
                document.getElementById('qr-image').src = data.qr_image_url;
                document.getElementById('qr-loading').style.display = 'none';
                document.getElementById('qr-display').style.display = 'block';

                // Start polling
                startPolling(data.db_session_id);

            } catch (error) {
                alert('Erro: ' + error.message);
                closeModal();
            }
        }

        function startPolling(dbSessionId) {
            pollingInterval = setInterval(async () => {
                try {
                    const response = await fetch(`/backend/integrations/whatsapp/poll_session_status.php?session_id=${dbSessionId}`);
                    const data = await response.json();

                    const statusEl = document.getElementById('status-message');

                    if (data.status === 'connected') {
                        // Success!
                        clearInterval(pollingInterval);
                        document.getElementById('step2').style.display = 'none';
                        document.getElementById('step3').style.display = 'block';
                        
                        setTimeout(() => {
                            location.reload();
                        }, 2000);
                    } else if (data.status === 'error') {
                        clearInterval(pollingInterval);
                        statusEl.innerHTML = '<p style="color: var(--danger);">❌ Erro: ' + (data.error_message || 'Desconhecido') + '</p>';
                    }
                } catch (error) {
                    console.error('Polling error:', error);
                }
            }, 3000);
        }

        async function disconnect(integrationId) {
            if (!confirm('Desconectar WhatsApp? Você precisará escanear QR novamente.')) return;

            try {
                const response = await fetch('/backend/integrations/whatsapp/disconnect.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ integration_id: integrationId })
                });

                const data = await response.json();
                if (data.success) {
                    location.reload();
                } else {
                    alert('Erro: ' + (data.error || 'Falha ao desconectar'));
                }
            } catch (error) {
                alert('Erro: ' + error.message);
            }
        }

        async function reconnect(integrationId) {
            // Just open modal and use existing integration
            showConnectModal();
        }

        async function deleteIntegration(integrationId) {
            if (!confirm('Remover integração? Esta ação não pode ser desfeita.')) return;

            try {
                const response = await fetch('/backend/integrations/whatsapp/disconnect.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ integration_id: integrationId, delete: true })
                });

                const data = await response.json();
                if (data.success) {
                    location.reload();
                } else {
                    alert('Erro: ' + (data.error || 'Falha ao remover'));
                }
            } catch (error) {
                alert('Erro: ' + error.message);
            }
        }
    </script>
    <script src="/frontend/js/app.js"></script>
</body>
</html>

