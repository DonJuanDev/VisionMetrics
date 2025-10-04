<?php
require_once __DIR__ . '/../middleware.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Central de Ajuda - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="/frontend/css/style.css">
    <link rel="stylesheet" href="/frontend/css/sidebar.css">
</head>
<body>
    <div class="app-layout">
        <?php include __DIR__ . '/../partials/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="top-bar">
                <div class="top-bar-title">
                    <h1>Central de Ajuda</h1>
                    <p>Documentação e guias de uso</p>
                </div>
            </div>

            <div class="container">
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
                    <div class="card">
                        <div class="card-body" style="text-align: center; padding: 32px;">
                            <div style="font-size: 48px; margin-bottom: 16px;">📚</div>
                            <h3>Documentação</h3>
                            <p style="color: #6B7280; margin: 12px 0 20px;">Guias completos de uso da plataforma</p>
                            <button class="btn btn-primary">Acessar Docs</button>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body" style="text-align: center; padding: 32px;">
                            <div style="font-size: 48px; margin-bottom: 16px;">🎥</div>
                            <h3>Vídeo Tutoriais</h3>
                            <p style="color: #6B7280; margin: 12px 0 20px;">Aprenda assistindo passo a passo</p>
                            <button class="btn btn-primary">Ver Vídeos</button>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body" style="text-align: center; padding: 32px;">
                            <div style="font-size: 48px; margin-bottom: 16px;">❓</div>
                            <h3>FAQ</h3>
                            <p style="color: #6B7280; margin: 12px 0 20px;">Perguntas frequentes</p>
                            <button class="btn btn-primary">Ver FAQ</button>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body" style="text-align: center; padding: 32px;">
                            <div style="font-size: 48px; margin-bottom: 16px;">💬</div>
                            <h3>Comunidade</h3>
                            <p style="color: #6B7280; margin: 12px 0 20px;">Conecte-se com outros usuários</p>
                            <button class="btn btn-primary">Entrar</button>
                        </div>
                    </div>
                </div>

                <div class="card" style="margin-top: 32px;">
                    <div class="card-header">
                        <h2>Artigos Populares</h2>
                    </div>
                    <div class="card-body">
                        <div style="display: grid; gap: 16px;">
                            <a href="#" style="padding: 16px; background: #F9FAFB; border-radius: 8px; text-decoration: none; color: inherit; display: block;">
                                <h4 style="color: #4F46E5; margin-bottom: 4px;">Como conectar WhatsApp</h4>
                                <p style="color: #6B7280; font-size: 14px; margin: 0;">Passo a passo para conectar seu número WhatsApp Business</p>
                            </a>
                            
                            <a href="#" style="padding: 16px; background: #F9FAFB; border-radius: 8px; text-decoration: none; color: inherit; display: block;">
                                <h4 style="color: #4F46E5; margin-bottom: 4px;">Configurar Meta Ads CAPI</h4>
                                <p style="color: #6B7280; font-size: 14px; margin: 0;">Envie conversões server-side para Facebook e Instagram</p>
                            </a>
                            
                            <a href="#" style="padding: 16px; background: #F9FAFB; border-radius: 8px; text-decoration: none; color: inherit; display: block;">
                                <h4 style="color: #4F46E5; margin-bottom: 4px;">Criar links rastreáveis</h4>
                                <p style="color: #6B7280; font-size: 14px; margin: 0;">Track origem de cada conversão com links curtos</p>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="/frontend/js/app.js"></script>
</body>
</html>




