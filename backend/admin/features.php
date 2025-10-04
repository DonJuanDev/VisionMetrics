<?php
require_once __DIR__ . '/../middleware.php';

// Handle feature suggestion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_suggestion'])) {
    $_SESSION['success'] = 'Sugestão enviada! Obrigado pelo feedback.';
    redirect('/features.php');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sugira Funcionalidades - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="/frontend/css/style.css">
    <link rel="stylesheet" href="/frontend/css/sidebar.css">
</head>
<body>
    <div class="app-layout">
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="top-bar">
                <div class="top-bar-title">
                    <h1>Sugira Funcionalidades</h1>
                    <p>Ajude-nos a melhorar o <?= APP_NAME ?></p>
                </div>
            </div>

            <div class="container">
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header">
                        <h2>💡 Envie sua Ideia</h2>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="form-group">
                                <label>Título da Sugestão *</label>
                                <input type="text" name="title" required placeholder="Ex: Integração com Instagram">
                            </div>
                            
                            <div class="form-group">
                                <label>Descrição Detalhada *</label>
                                <textarea name="description" rows="6" required placeholder="Descreva sua ideia..."></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label>Categoria</label>
                                <select name="category">
                                    <option value="integration">Integração</option>
                                    <option value="feature">Nova Funcionalidade</option>
                                    <option value="improvement">Melhoria</option>
                                    <option value="bug">Reportar Bug</option>
                                </select>
                            </div>
                            
                            <button type="submit" name="send_suggestion" class="btn btn-primary">Enviar Sugestão</button>
                        </form>
                    </div>
                </div>

                <div class="card" style="margin-top: 24px;">
                    <div class="card-header">
                        <h2>🔥 Sugestões Populares</h2>
                    </div>
                    <div class="card-body">
                        <div style="display: grid; gap: 16px;">
                            <div style="padding: 16px; background: #F9FAFB; border-radius: 8px; border-left: 3px solid #4F46E5;">
                                <h4 style="color: #4F46E5; margin-bottom: 4px;">Integração com Instagram Direct</h4>
                                <p style="color: #6B7280; font-size: 14px; margin: 0;">Rastrear conversas do Instagram Direct automaticamente</p>
                                <div style="margin-top: 8px; display: flex; gap: 8px;">
                                    <span class="badge badge-primary">Em Análise</span>
                                    <span class="badge badge-secondary">23 votos</span>
                                </div>
                            </div>
                            
                            <div style="padding: 16px; background: #F9FAFB; border-radius: 8px; border-left: 3px solid #10B981;">
                                <h4 style="color: #10B981; margin-bottom: 4px;">Dashboard Customizável</h4>
                                <p style="color: #6B7280; font-size: 14px; margin: 0;">Permitir reorganizar widgets do dashboard</p>
                                <div style="margin-top: 8px; display: flex; gap: 8px;">
                                    <span class="badge badge-success">Em Desenvolvimento</span>
                                    <span class="badge badge-secondary">18 votos</span>
                                </div>
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




