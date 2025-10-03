<?php
session_start();

// Se já está logado, redireciona para dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: /backend/dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VisionMetrics - Lead Tracking & WhatsApp Attribution</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .hero {
            text-align: center;
            color: white;
            max-width: 1200px;
            width: 100%;
        }
        
        .logo {
            font-size: 56px;
            font-weight: 800;
            margin-bottom: 20px;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }
        
        .tagline {
            font-size: 24px;
            margin-bottom: 15px;
            opacity: 0.95;
        }
        
        .description {
            font-size: 18px;
            margin-bottom: 50px;
            opacity: 0.9;
            line-height: 1.6;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .cta-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 60px;
        }
        
        .btn {
            padding: 18px 40px;
            font-size: 18px;
            font-weight: 600;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
        
        .btn-primary {
            background: white;
            color: #667eea;
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
        }
        
        .btn-secondary {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 2px solid white;
        }
        
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
        }
        
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }
        
        .feature {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            padding: 30px;
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .feature-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
        
        .feature-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .feature-text {
            font-size: 15px;
            opacity: 0.9;
            line-height: 1.5;
        }
        
        .admin-box {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 16px;
            padding: 25px;
            margin-top: 50px;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .admin-box h3 {
            font-size: 18px;
            margin-bottom: 15px;
        }
        
        .admin-box code {
            background: rgba(0, 0, 0, 0.3);
            padding: 4px 8px;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
        }
        
        .admin-box p {
            margin: 8px 0;
            font-size: 15px;
        }
        
        @media (max-width: 768px) {
            .logo {
                font-size: 40px;
            }
            
            .tagline {
                font-size: 20px;
            }
            
            .description {
                font-size: 16px;
            }
            
            .cta-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="hero">
        <div class="logo">
            📊 VisionMetrics
        </div>
        
        <div class="tagline">
            Lead Tracking & WhatsApp Attribution Platform
        </div>
        
        <div class="description">
            Rastreie leads de todas as suas campanhas (Meta Ads, Google Ads, TikTok), 
            atribua conversas do WhatsApp às suas fontes de tráfego e tenha métricas 
            precisas para otimizar seus investimentos em marketing.
        </div>
        
        <div class="cta-buttons">
            <a href="/backend/register.php" class="btn btn-primary">
                🚀 Criar Conta Grátis
            </a>
            <a href="/backend/login.php" class="btn btn-secondary">
                🔐 Fazer Login
            </a>
        </div>
        
        <div class="features">
            <div class="feature">
                <div class="feature-icon">📱</div>
                <div class="feature-title">WhatsApp Tracking</div>
                <div class="feature-text">
                    Conecte múltiplos números e rastreie todas as conversas com atribuição de origem
                </div>
            </div>
            
            <div class="feature">
                <div class="feature-icon">🎯</div>
                <div class="feature-title">Multi-Touch Attribution</div>
                <div class="feature-text">
                    6 modelos de atribuição: First Touch, Last Touch, Linear, Time Decay e mais
                </div>
            </div>
            
            <div class="feature">
                <div class="feature-icon">🔌</div>
                <div class="feature-title">Integrações Nativas</div>
                <div class="feature-text">
                    Meta Ads CAPI, Google Analytics 4, TikTok Pixel - tudo integrado
                </div>
            </div>
            
            <div class="feature">
                <div class="feature-icon">📈</div>
                <div class="feature-title">Dashboard em Tempo Real</div>
                <div class="feature-text">
                    Veja métricas atualizadas ao vivo, ROI por canal e jornada do cliente
                </div>
            </div>
        </div>
        
        <div class="admin-box">
            <h3>🔑 Credenciais de Teste (Admin)</h3>
            <p><strong>Email:</strong> <code>admin@visionmetrics.com</code></p>
            <p><strong>Senha:</strong> <code>password</code></p>
            <p style="margin-top: 15px; font-size: 13px; opacity: 0.8;">
                Ou crie sua própria conta com plano PRO gratuito!
            </p>
        </div>
    </div>
</body>
</html>