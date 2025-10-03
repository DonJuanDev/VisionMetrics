<?php
require_once __DIR__ . '/middleware.php';

// Handle support ticket
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_ticket'])) {
    $_SESSION['success'] = 'Ticket enviado! Nossa equipe responderá em breve.';
    redirect('/backend/support.php');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suporte - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="/frontend/css/style.css">
    <link rel="stylesheet" href="/frontend/css/sidebar.css">
</head>
<body>
    <div class="app-layout">
        <?php include __DIR__ . '/partials/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="top-bar">
                <div class="top-bar-title">
                    <h1>Suporte</h1>
                    <p>Entre em contato com nossa equipe</p>
                </div>
            </div>

            <div class="container">
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
                <?php endif; ?>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 32px;">
                    <div class="card">
                        <div class="card-body" style="text-align: center; padding: 40px;">
                            <div style="font-size: 56px; margin-bottom: 16px;">💬</div>
                            <h3>Chat ao Vivo</h3>
                            <p style="color: #6B7280; margin: 12px 0 24px;">Converse com nossa equipe agora</p>
                            <button class="btn btn-primary">Iniciar Chat</button>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body" style="text-align: center; padding: 40px;">
                            <div style="font-size: 56px; margin-bottom: 16px;">📧</div>
                            <h3>Email</h3>
                            <p style="color: #6B7280; margin: 12px 0 24px;">Envie um email para suporte@visionmetrics.com</p>
                            <a href="mailto:suporte@visionmetrics.com" class="btn btn-secondary">Enviar Email</a>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h2>Abrir Ticket de Suporte</h2>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="form-group">
                                <label>Assunto</label>
                                <input type="text" name="subject" required placeholder="Ex: Erro ao conectar WhatsApp">
                            </div>
                            
                            <div class="form-group">
                                <label>Descrição do Problema</label>
                                <textarea name="description" rows="6" required placeholder="Descreva o problema em detalhes..."></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label>Prioridade</label>
                                <select name="priority">
                                    <option value="low">Baixa</option>
                                    <option value="medium" selected>Média</option>
                                    <option value="high">Alta</option>
                                    <option value="urgent">Urgente</option>
                                </select>
                            </div>
                            
                            <button type="submit" name="send_ticket" class="btn btn-primary">Enviar Ticket</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="/frontend/js/app.js"></script>
</body>
</html>




