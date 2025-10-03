<?php
require_once __DIR__ . '/middleware.php';

$db = getDB();

// Handle data export request (LGPD/GDPR Right to Portability)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['export_my_data'])) {
    $leadId = $_POST['lead_id'];
    
    // Get all lead data
    $stmt = $db->prepare("SELECT * FROM leads WHERE id = ? AND workspace_id = ?");
    $stmt->execute([$leadId, $currentWorkspace['id']]);
    $lead = $stmt->fetch();
    
    if ($lead) {
        $export = [
            'lead' => $lead,
            'events' => [],
            'conversations' => [],
            'messages' => []
        ];
        
        // Events
        $stmt = $db->prepare("SELECT * FROM events WHERE lead_id = ?");
        $stmt->execute([$leadId]);
        $export['events'] = $stmt->fetchAll();
        
        // Conversations
        $stmt = $db->prepare("SELECT * FROM conversations WHERE lead_id = ?");
        $stmt->execute([$leadId]);
        $export['conversations'] = $stmt->fetchAll();
        
        // Export as JSON
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="meus-dados-' . $leadId . '.json"');
        echo json_encode($export, JSON_PRETTY_PRINT);
        exit;
    }
}

// Handle data deletion request (LGPD/GDPR Right to Erasure)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_my_data'])) {
    $leadId = $_POST['lead_id'];
    $confirm = $_POST['confirm'] ?? '';
    
    if ($confirm === 'DELETE') {
        // Anonymize or delete
        $stmt = $db->prepare("
            UPDATE leads SET
                email = NULL,
                phone_number = NULL,
                name = 'Usuário Removido',
                fingerprint = NULL,
                city = NULL,
                state = NULL,
                country = NULL
            WHERE id = ? AND workspace_id = ?
        ");
        $stmt->execute([$leadId, $currentWorkspace['id']]);
        
        // Delete events (optional - pode ser anonimizado)
        $stmt = $db->prepare("DELETE FROM events WHERE lead_id = ?");
        $stmt->execute([$leadId]);
        
        $_SESSION['success'] = 'Dados removidos conforme LGPD';
        redirect('/lgpd-compliance.php');
    }
}

// Get all leads for selection
$stmt = $db->prepare("SELECT id, name, email, phone_number FROM leads WHERE workspace_id = ? ORDER BY created_at DESC LIMIT 100");
$stmt->execute([$currentWorkspace['id']]);
$leads = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LGPD/GDPR Compliance - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="/frontend/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/partials/header.php'; ?>

    <div class="container">
        <div class="page-header">
            <h1>🔒 LGPD/GDPR Compliance</h1>
            <p>Direitos dos titulares de dados</p>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>

        <!-- Cookie Consent Banner -->
        <div class="card" style="background: #EFF6FF; border-left: 4px solid #3B82F6;">
            <div class="card-body">
                <h3>🍪 Cookie Consent</h3>
                <p>Adicione este código no seu site para compliance com LGPD:</p>
                <div class="code-block" style="font-size: 12px;">
&lt;div id="cookie-banner" style="position: fixed; bottom: 0; left: 0; right: 0; background: #1E293B; color: white; padding: 20px; z-index: 9999;"&gt;
    &lt;div style="max-width: 1200px; margin: 0 auto; display: flex; align-items: center; justify-content: space-between; gap: 20px;"&gt;
        &lt;p&gt;Usamos cookies para melhorar sua experiência. Ao continuar navegando, você concorda com nossa &lt;a href="/privacy" style="color: #60A5FA;"&gt;Política de Privacidade&lt;/a&gt;.&lt;/p&gt;
        &lt;button onclick="this.closest('#cookie-banner').remove(); localStorage.setItem('cookies-accepted', 'true');" style="padding: 10px 20px; background: #3B82F6; color: white; border: none; border-radius: 6px; cursor: pointer;"&gt;Aceitar&lt;/button&gt;
    &lt;/div&gt;
&lt;/div&gt;
&lt;script&gt;
    if (localStorage.getItem('cookies-accepted')) {
        document.getElementById('cookie-banner').remove();
    }
&lt;/script&gt;
                </div>
                <button class="btn btn-sm btn-primary" onclick="copyToClipboard(this.previousElementSibling.textContent)" style="margin-top: 12px;">Copiar Código</button>
            </div>
        </div>

        <!-- Data Export (Right to Portability) -->
        <div class="card">
            <div class="card-header">
                <h2>📤 Exportar Dados (Direito à Portabilidade)</h2>
            </div>
            <div class="card-body">
                <p>Permite que o usuário exporte todos os seus dados em formato JSON.</p>
                <form method="POST">
                    <div class="form-group">
                        <label>Selecione o Lead</label>
                        <select name="lead_id" required>
                            <option value="">Selecione...</option>
                            <?php foreach ($leads as $lead): ?>
                                <option value="<?= $lead['id'] ?>">
                                    <?= htmlspecialchars($lead['name']) ?> - <?= htmlspecialchars($lead['email']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" name="export_my_data" class="btn btn-primary">Exportar Dados</button>
                </form>
            </div>
        </div>

        <!-- Data Deletion (Right to Erasure) -->
        <div class="card" style="border-left: 4px solid #EF4444;">
            <div class="card-header">
                <h2>🗑️ Deletar Dados (Direito ao Esquecimento)</h2>
            </div>
            <div class="card-body">
                <p><strong>ATENÇÃO:</strong> Esta ação irá anonimizar/remover todos os dados pessoais do lead.</p>
                <form method="POST" onsubmit="return confirm('Tem CERTEZA que deseja deletar os dados? Esta ação é IRREVERSÍVEL!')">
                    <div class="form-group">
                        <label>Selecione o Lead</label>
                        <select name="lead_id" required>
                            <option value="">Selecione...</option>
                            <?php foreach ($leads as $lead): ?>
                                <option value="<?= $lead['id'] ?>">
                                    <?= htmlspecialchars($lead['name']) ?> - <?= htmlspecialchars($lead['email']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Digite "DELETE" para confirmar</label>
                        <input type="text" name="confirm" required placeholder="DELETE">
                    </div>
                    <button type="submit" name="delete_my_data" class="btn btn-danger">Deletar Dados</button>
                </form>
            </div>
        </div>

        <!-- Privacy Policy Template -->
        <div class="card">
            <div class="card-header">
                <h2>📄 Política de Privacidade (Template)</h2>
            </div>
            <div class="card-body">
                <p>Crie uma página /privacy-policy.php com este conteúdo:</p>
                <ul>
                    <li>✅ Quais dados coletamos (nome, email, telefone, navegação)</li>
                    <li>✅ Como usamos (análise, atribuição, remarketing)</li>
                    <li>✅ Compartilhamento (Google Ads, Meta Ads)</li>
                    <li>✅ Direitos do titular (acessar, corrigir, deletar)</li>
                    <li>✅ Cookies e tracking</li>
                    <li>✅ Contato do DPO/Encarregado</li>
                </ul>
                <a href="https://www.iubenda.com/en/privacy-and-cookie-policy-generator" target="_blank" class="btn btn-primary">
                    Gerar Política Automática
                </a>
            </div>
        </div>
    </div>

    <script src="/frontend/js/app.js"></script>
</body>
</html>





