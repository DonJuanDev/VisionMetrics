<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VisionMetrics - A Plataforma de Leads Mais Avançada do Mundo</title>
    <meta name="description" content="Transforme visitantes em clientes com nossa plataforma de inteligência artificial. Mais de 10.000 empresas já aumentaram suas vendas em 300%.">
    <meta name="keywords" content="leads, conversão, marketing digital, automação, CRM, vendas, inteligência artificial">
    
    <!-- Favicons -->
    <link rel="apple-touch-icon" sizes="180x180" href="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTgwIiBoZWlnaHQ9IjE4MCIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPjxwYXRoIGQ9Ik00IDRMNCA4TDggMTJMNCAxNkw0IDIwTDEyIDEyTDQgNFoiIGZpbGw9IndoaXRlIi8+PHBhdGggZD0iTTIwIDRMMjAgOEwxNiAxMkwyMCAxNkwyMCAyMEwxMiAxMkwyMCA0WiIgZmlsbD0iI0E3OEJGQSIvPjwvc3ZnPg==">
    <link rel="icon" type="image/png" sizes="32x32" href="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzIiIGhlaWdodD0iMzIiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cGF0aCBkPSJNNCA0TDQgOEw4IDEyTDQgMTZMNCAyMEwxMiAxMkw0IDRaIiBmaWxsPSJ3aGl0ZSIvPjxwYXRoIGQ9Ik0yMCA0TDIwIDhMMTYgMTJMMjAgMTZMMjAgMjBMMTIgMTJMMjAgNFoiIGZpbGw9IiNBNzhCRkEiLz48L3N2Zz4=">
    <link rel="icon" type="image/png" sizes="16x16" href="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTYiIGhlaWdodD0iMTYiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cGF0aCBkPSJNNCA0TDQgOEw4IDEyTDQgMTZMNCAyMEwxMiAxMkw0IDRaIiBmaWxsPSJ3aGl0ZSIvPjxwYXRoIGQ9Ik0yMCA0TDIwIDhMMTYgMTJMMjAgMTZMMjAgMjBMMTIgMTJMMjAgNFoiIGZpbGw9IiNBNzhCRkEiLz48L3N2Zz4=">
    <link rel="icon" type="image/x-icon" href="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTYiIGhlaWdodD0iMTYiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cGF0aCBkPSJNNCA0TDQgOEw4IDEyTDQgMTZMNCAyMEwxMiAxMkw0IDRaIiBmaWxsPSJ3aGl0ZSIvPjxwYXRoIGQ9Ik0yMCA0TDIwIDhMMTYgMTJMMjAgMTZMMjAgMjBMMTIgMTJMMjAgNFoiIGZpbGw9IiNBNzhCRkEiLz48L3N2Zz4=">
    <meta name="theme-color" content="#2F70F8">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="frontend/css/landing.css">
</head>
<body class="landing-page">
    <!-- Top Banner -->
    <div class="top-banner">
        <span class="rocket-icon">🚀</span>
        Lançamento MVP! Qualquer problema, entre em contato: @visionmetrics (Instagram) ou contato@visionmetrics.com
    </div>

    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <a href="#" class="logo">
                <div class="logo-icon">V</div>
                VisionMetrics
            </a>
            
            <nav class="nav">
                <a href="#features" class="nav-link">Recursos</a>
                <a href="#how-it-works" class="nav-link">Como Funciona</a>
                <a href="#security" class="nav-link">Segurança</a>
                <a href="#pricing" class="nav-link">Planos</a>
            </nav>
            
            <div class="header-actions">
                <a href="/backend/login.php" class="btn-login">Entrar</a>
                <a href="/backend/register.php" class="btn-primary">Começar Grátis</a>
            </div>

            <!-- Mobile Menu Button -->
            <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Menu">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </header>

    <!-- Mobile Menu Overlay -->
    <div class="mobile-menu" id="mobileMenu">
        <div class="mobile-menu-header">
            <div class="logo">
                <div class="logo-icon">V</div>
                VisionMetrics
            </div>
            <button class="mobile-menu-close" id="mobileMenuClose" aria-label="Fechar menu">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <nav class="mobile-nav">
            <a href="#features" class="mobile-nav-link">Recursos</a>
            <a href="#how-it-works" class="mobile-nav-link">Como Funciona</a>
            <a href="#security" class="mobile-nav-link">Segurança</a>
            <a href="#pricing" class="mobile-nav-link">Planos</a>
        </nav>
        <div class="mobile-menu-actions">
            <a href="/backend/login.php" class="btn-login">Entrar</a>
            <a href="/backend/register.php" class="btn-primary">Começar Grátis</a>
        </div>
    </div>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Transforme <span class="highlight">Leads</span> em <span class="highlight">Clientes</span></h1>
            <p>A plataforma mais completa para gestão de leads com inteligência artificial, tracking preciso e automação completa.</p>
            <div class="hero-cta">
                <a href="/backend/register.php" class="btn-primary">
                    Começar Grátis
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </a>
                <a href="#features" class="btn-secondary">
                    Ver Recursos
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <div class="features-content">
            <h2 class="section-title">Tudo o que você precisa para gerenciar leads</h2>
            <p class="section-subtitle">Nossa plataforma oferece recursos que otimizam sua operação e garantem conversões com transparência.</p>
            
            <div class="features-grid">
                <div class="feature-card fade-in-up">
                    <div class="feature-video">
                        <div class="feature-video-wrapper">
                            <div class="feature-video-header">
                                <div class="feature-video-dot red"></div>
                                <div class="feature-video-dot yellow"></div>
                                <div class="feature-video-dot green"></div>
                            </div>
                            <video autoplay loop muted playsinline>
                                <source src="videos/video teste bino.mp4" type="video/mp4">
                            </video>
                        </div>
                        <div class="play-button">▶</div>
                    </div>
                    <div class="feature-icon blue">📊</div>
                    <h3 class="feature-title">Dashboard Inteligente</h3>
                    <p class="feature-description">Visualize métricas em tempo real, acompanhe conversões e tome decisões baseadas em dados precisos.</p>
        </div>
        
                <div class="feature-card fade-in-up">
                    <div class="feature-video">
                        <div class="feature-video-wrapper">
                            <div class="feature-video-header">
                                <div class="feature-video-dot red"></div>
                                <div class="feature-video-dot yellow"></div>
                                <div class="feature-video-dot green"></div>
                            </div>
                            <video autoplay loop muted playsinline>
                                <source src="videos/video teste bino.mp4" type="video/mp4">
                            </video>
                        </div>
                        <div class="play-button">▶</div>
                    </div>
                    <div class="feature-icon yellow">⚡</div>
                    <h3 class="feature-title">Tracking Avançado</h3>
                    <p class="feature-description">Rastreie cada interação do usuário, desde a origem até a conversão, com precisão milimétrica.</p>
        </div>
        
                <div class="feature-card fade-in-up">
                    <div class="feature-video">
                        <div class="feature-video-wrapper">
                            <div class="feature-video-header">
                                <div class="feature-video-dot red"></div>
                                <div class="feature-video-dot yellow"></div>
                                <div class="feature-video-dot green"></div>
                            </div>
                            <video autoplay loop muted playsinline>
                                <source src="videos/video teste bino.mp4" type="video/mp4">
                            </video>
                        </div>
                        <div class="play-button">▶</div>
                    </div>
                    <div class="feature-icon green">💬</div>
                    <h3 class="feature-title">Integração WhatsApp</h3>
                    <p class="feature-description">Conecte diretamente com seus leads via WhatsApp e automatize o processo de qualificação.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- How it Works Section -->
    <section class="how-it-works" id="how-it-works">
        <div class="how-it-works-content">
            <h2 class="section-title">Começar a Usar é Simples e Rápido</h2>
            <p class="section-subtitle">Em apenas 4 passos, sua empresa deixa a papelada para trás e passa a ter um controle de leads moderno e eficiente. Veja como é fácil:</p>
            
            <div class="steps-grid">
                <div class="step-card">
                    <div class="step-content">
                        <div class="step-number">Passo 1</div>
                        <h3 class="step-title">O Ponto de Partida: A Conta do Gestor</h3>
                        <p class="step-description">Primeiro, o gestor cria a conta principal da empresa. É como montar o escritório central que vai organizar todas as campanhas e leads da equipe.</p>
                    </div>
                    <div class="step-visual">
                        <div class="step-video">
                            <div class="step-video-wrapper">
                                <div class="step-video-header">
                                    <div class="step-video-dot red"></div>
                                    <div class="step-video-dot yellow"></div>
                                    <div class="step-video-dot green"></div>
                                </div>
                                <video autoplay loop muted playsinline>
                                    <source src="videos/video teste bino.mp4" type="video/mp4">
                                </video>
                            </div>
                            <div class="play-button">▶</div>
                        </div>
                    </div>
                </div>
                
                <div class="step-card reverse">
                    <div class="step-content">
                        <div class="step-number">Passo 2</div>
                        <h3 class="step-title">É Hora de Chamar a Equipe</h3>
                        <p class="step-description">Com a base pronta, o gestor envia um convite por e-mail para cada colaborador. É como entregar a chave da porta para que eles possam entrar no sistema.</p>
                    </div>
                    <div class="step-visual">
                        <div class="step-video">
                            <div class="step-video-wrapper">
                                <div class="step-video-header">
                                    <div class="step-video-dot red"></div>
                                    <div class="step-video-dot yellow"></div>
                                    <div class="step-video-dot green"></div>
                                </div>
                                <video autoplay loop muted playsinline>
                                    <source src="videos/video teste bino.mp4" type="video/mp4">
                                </video>
                            </div>
                            <div class="play-button">▶</div>
                        </div>
                    </div>
                </div>
        
                <div class="step-card">
                    <div class="step-content">
                        <div class="step-number">Passo 3</div>
                        <h3 class="step-title">O Colaborador Entra em Campo</h3>
                        <p class="step-description">O colaborador recebe o convite por e-mail, faz um cadastro rápido e já está pronto para começar a trabalhar com leads de forma organizada.</p>
                    </div>
                    <div class="step-visual">
                        <div class="step-video">
                            <div class="step-video-wrapper">
                                <div class="step-video-header">
                                    <div class="step-video-dot red"></div>
                                    <div class="step-video-dot yellow"></div>
                                    <div class="step-video-dot green"></div>
                                </div>
                                <video autoplay loop muted playsinline>
                                    <source src="videos/video teste bino.mp4" type="video/mp4">
                                </video>
                            </div>
                            <div class="play-button">▶</div>
                        </div>
                    </div>
                </div>
        
                <div class="step-card reverse">
                    <div class="step-content">
                        <div class="step-number">Passo 4</div>
                        <h3 class="step-title">Equipe Pronta para a Ação!</h3>
                        <p class="step-description">Com todos a bordo, a desorganização dos leads acabou. A equipe já pode gerenciar campanhas de forma 100% digital, e o gestor acompanha tudo em tempo real.</p>
                    </div>
                    <div class="step-visual">
                        <div class="step-video">
                            <div class="step-video-wrapper">
                                <div class="step-video-header">
                                    <div class="step-video-dot red"></div>
                                    <div class="step-video-dot yellow"></div>
                                    <div class="step-video-dot green"></div>
                                </div>
                                <video autoplay loop muted playsinline>
                                    <source src="videos/video teste bino.mp4" type="video/mp4">
                                </video>
                            </div>
                            <div class="play-button">▶</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Integration Section -->
    <section class="integration-section">
        <div class="integration-content">
            <h2 class="section-title">Gestão de leads direto do seu <span style="color: var(--accent);">Slack</span></h2>
            <p class="section-subtitle">A burocracia não precisa sair do seu ambiente de trabalho. Com a integração do VisionMetrics, sua equipe pode iniciar e aprovar campanhas sem sair da ferramenta de comunicação que já utiliza todos os dias.</p>
            
            <div class="integration-grid">
                <div class="integration-visual">
                    <div class="integration-screenshot" style="background: linear-gradient(135deg, #4A154B 0%, #36C5F0 50%, #2EB67D 100%); height: 300px; display: flex; align-items: center; justify-content: center; color: white; font-size: 18px; font-weight: 600;">
                        <div style="text-align: center;">
                            <div style="font-size: 48px; margin-bottom: 16px;">💬</div>
                            <div>Slack Integration</div>
                            <div style="font-size: 14px; opacity: 0.8; margin-top: 8px;">Gestão de leads direto no Slack</div>
                        </div>
                    </div>
                    <div class="integration-icon" style="background: linear-gradient(45deg, #4A154B, #36C5F0, #2EB67D, #ECB22E); color: white;">💬</div>
                </div>
                
                <div class="integration-features">
                    <div class="integration-feature">
                        <div class="integration-feature-icon blue">🔺</div>
                        <div class="integration-feature-text">
                            <div class="integration-feature-title">Crie novas campanhas</div>
                            <div class="integration-feature-description">Crie novas campanhas com um simples comando como <span class="command">/nova-campanha</span></div>
                        </div>
                    </div>
                    
                    <div class="integration-feature">
                        <div class="integration-feature-icon green">✅</div>
                        <div class="integration-feature-text">
                            <div class="integration-feature-title">Notificações em tempo real</div>
                            <div class="integration-feature-description">Receba notificações em tempo real sempre que uma campanha for criada, aprovada, recusada ou enviada para revisão.</div>
                        </div>
                    </div>
                    
                    <div class="integration-feature">
                        <div class="integration-feature-icon orange">👁️</div>
                        <div class="integration-feature-text">
                            <div class="integration-feature-title">Consulte o status</div>
                            <div class="integration-feature-description">Consulte o status de uma campanha a qualquer momento, mantendo a equipe informada.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Security Section -->
    <section class="security-section" id="security">
        <div class="security-content">
            <h2 class="section-title">Segurança e Conformidade em Primeiro Lugar</h2>
            <p class="section-subtitle">Levamos a proteção dos seus dados a sério. Nossa plataforma é construída sobre as melhores práticas do mercado para garantir que suas informações financeiras e de seus colaboradores estejam sempre seguras.</p>
            
            <div class="security-cards">
                <div class="security-card fade-in-up">
                    <div class="security-icon blue">🛡️</div>
                    <h3 class="security-title">Conformidade com a LGPD</h3>
                    <p class="security-description">Operamos em total conformidade com a Lei Geral de Proteção de Dados, assegurando a privacidade e o tratamento correto das informações.</p>
                </div>
                
                <div class="security-card fade-in-up">
                    <div class="security-icon orange">🔒</div>
                    <h3 class="security-title">Criptografia de Ponta a Ponta</h3>
                    <p class="security-description">Todos os dados, desde o upload de um comprovante até relatórios, são protegidos com os mais altos padrões de criptografia.</p>
                </div>
                
                <div class="security-card fade-in-up">
                    <div class="security-icon green">☁️</div>
                    <h3 class="security-title">Infraestrutura Robusta</h3>
                    <p class="security-description">Utilizamos servidores em nuvem de ponta (AWS), com monitoramento 24/7 para garantir a disponibilidade e a integridade do sistema.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section class="pricing-section" id="pricing">
        <div class="pricing-content">
            <h2 class="section-title">Um plano transparente para sua empresa crescer</h2>
            <p class="section-subtitle">Escolha a opção que melhor se adapta ao tamanho da sua equipe e só pague a partir do segundo mês!</p>
            
            <div class="pricing-cards">
                <div class="pricing-card">
                    <h3 class="pricing-title">Start</h3>
                    <p class="pricing-description">Ideal para gestores individuais</p>
                    <div class="pricing-price">R$80<span class="pricing-period">/mês</span></div>
                    <ul class="pricing-features">
                        <li><span class="check-icon">✓</span> Convide mais 1 gestor parceiro</li>
                        <li><span class="check-icon">✓</span> Colaboradores ilimitados</li>
                    </ul>
                    <button class="pricing-button secondary">Testar grátis por 30 dias</button>
                </div>
                
                <div class="pricing-card popular">
                    <div class="popular-badge">MAIS POPULAR</div>
                    <h3 class="pricing-title">Business</h3>
                    <p class="pricing-description">Perfeito para equipes em crescimento</p>
                    <div class="pricing-price">R$150<span class="pricing-period">/mês</span></div>
                    <ul class="pricing-features">
                        <li><span class="check-icon">✓</span> Convide mais 3 gestores parceiros</li>
                        <li><span class="check-icon">✓</span> Colaboradores ilimitados</li>
                    </ul>
                    <button class="pricing-button primary">Começar Agora</button>
                </div>
                
                <div class="pricing-card">
                    <h3 class="pricing-title">Enterprise</h3>
                    <p class="pricing-description">Para empresas com múltiplas equipes</p>
                    <div class="pricing-price">R$300<span class="pricing-period">/mês</span></div>
                    <ul class="pricing-features">
                        <li><span class="check-icon">✓</span> Convide mais 6 gestores parceiros</li>
                        <li><span class="check-icon">✓</span> Colaboradores ilimitados</li>
                    </ul>
                    <button class="pricing-button secondary">Testar grátis por 30 dias</button>
            </div>
        </div>
        
            <p style="text-align: center; margin-top: 40px; font-size: 14px; color: var(--text-muted);">
                * Todos os planos possuem um período grátis de 30 dias e você será notificado quando estiver perto do fim para cancelar ou continuar conosco! :)<br>
                OBS: se torna responsabilidade do usuário cancelar antes do fim do período para não ser cobrado o valor do plano escolhido.
            </p>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-left">
                <a href="#" class="footer-logo">
                    <div class="logo-icon">V</div>
                    <span>VisionMetrics</span>
                </a>
                <div class="footer-text">© 2025 VisionMetrics. Todos os direitos reservados.</div>
            </div>
            <div class="social-links">
                <a href="#" class="social-link" title="LinkedIn">💼</a>
                <a href="#" class="social-link" title="Instagram">📷</a>
                <a href="#" class="social-link" title="Email">✉️</a>
                <a href="#" class="social-link" title="WhatsApp">💬</a>
                <a href="#" class="social-link" title="Discord">🎧</a>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <button id="backToTop" class="back-to-top" aria-label="Voltar ao topo">
        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
        </svg>
    </button>

    <style>
        .back-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            background: var(--gradient-primary);
            border: none;
            border-radius: 50%;
            color: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 20px rgba(139, 92, 246, 0.4);
            opacity: 0;
            visibility: hidden;
            transform: translateY(100px);
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .back-to-top.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .back-to-top:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(139, 92, 246, 0.6);
        }

        .back-to-top:active {
            transform: translateY(-2px);
        }

        /* Mobile Menu Styles */
        .mobile-menu-btn {
            display: none;
            flex-direction: column;
            justify-content: space-around;
            width: 30px;
            height: 24px;
            background: transparent;
            border: none;
            cursor: pointer;
            padding: 0;
            z-index: 1001;
        }

        .mobile-menu-btn span {
            width: 100%;
            height: 3px;
            background: var(--text-primary);
            border-radius: 3px;
            transition: all 0.3s ease;
        }

        .mobile-menu-btn.active span:nth-child(1) {
            transform: translateY(10px) rotate(45deg);
        }

        .mobile-menu-btn.active span:nth-child(2) {
            opacity: 0;
        }

        .mobile-menu-btn.active span:nth-child(3) {
            transform: translateY(-11px) rotate(-45deg);
        }

        .mobile-menu {
            position: fixed;
            top: 0;
            right: -100%;
            width: 80%;
            max-width: 400px;
            height: 100vh;
            background: var(--bg-secondary);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            box-shadow: -4px 0 20px rgba(0, 0, 0, 0.5);
            transition: right 0.3s ease;
            z-index: 1002;
            overflow-y: auto;
            padding: 24px;
        }

        .mobile-menu.active {
            right: 0;
        }

        .mobile-menu-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }

        .mobile-menu-close {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            border: none;
            border-radius: 8px;
            color: var(--text-primary);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .mobile-menu-close:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .mobile-nav {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 40px;
        }

        .mobile-nav-link {
            color: var(--text-secondary);
            text-decoration: none;
            font-weight: 600;
            font-size: 18px;
            padding: 16px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .mobile-nav-link:hover {
            background: rgba(255, 255, 255, 0.05);
            color: var(--text-primary);
            transform: translateX(8px);
        }

        .mobile-menu-actions {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .mobile-menu-actions .btn-login,
        .mobile-menu-actions .btn-primary {
            width: 100%;
            text-align: center;
            padding: 14px 24px;
        }

        @media (max-width: 768px) {
            .nav {
                display: none;
            }

            .header-actions {
                display: none;
            }

            .mobile-menu-btn {
                display: flex;
            }

            .back-to-top {
                width: 45px;
                height: 45px;
                bottom: 20px;
                right: 20px;
            }
        }
    </style>

    <script>
        // Smooth scrolling for navigation links with offset for fixed header
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    const headerOffset = 80;
                    const elementPosition = target.getBoundingClientRect().top;
                    const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

                    window.scrollTo({
                        top: offsetPosition,
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Intersection Observer for animations with stagger effect
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -100px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0) translateX(0)';
                }
            });
        }, observerOptions);

        // Observe all animated elements
        document.querySelectorAll('.fade-in-up').forEach((el, index) => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(40px)';
            el.style.transition = `opacity 0.7s ease-out ${index * 0.1}s, transform 0.7s ease-out ${index * 0.1}s`;
            observer.observe(el);
        });

        document.querySelectorAll('.fade-in-left').forEach((el, index) => {
            el.style.opacity = '0';
            el.style.transform = 'translateX(-40px)';
            el.style.transition = `opacity 0.7s ease-out ${index * 0.15}s, transform 0.7s ease-out ${index * 0.15}s`;
            observer.observe(el);
        });

        document.querySelectorAll('.fade-in-right').forEach((el, index) => {
            el.style.opacity = '0';
            el.style.transform = 'translateX(40px)';
            el.style.transition = `opacity 0.7s ease-out ${index * 0.15}s, transform 0.7s ease-out ${index * 0.15}s`;
            observer.observe(el);
        });

        // Play button interactions with ripple effect and video control
        document.querySelectorAll('.play-button').forEach(button => {
            button.addEventListener('click', function(e) {
                e.stopPropagation();
                
                // Find the video element
                const videoContainer = this.parentElement;
                const video = videoContainer.querySelector('video');
                
                if (video) {
                    if (video.paused) {
                        video.play();
                        this.textContent = '⏸';
                    } else {
                        video.pause();
                        this.textContent = '▶';
                    }
                }
                
                // Create ripple effect
                const ripple = document.createElement('span');
                ripple.style.position = 'absolute';
                ripple.style.borderRadius = '50%';
                ripple.style.background = 'rgba(255, 255, 255, 0.5)';
                ripple.style.width = '100px';
                ripple.style.height = '100px';
                ripple.style.top = '50%';
                ripple.style.left = '50%';
                ripple.style.transform = 'translate(-50%, -50%)';
                ripple.style.animation = 'ripple 0.6s';
                ripple.style.pointerEvents = 'none';
                
                this.appendChild(ripple);
                
                setTimeout(() => {
                    ripple.remove();
                }, 600);
            });
        });
        
        // Hide play button when video is playing, show when paused
        document.querySelectorAll('video').forEach(video => {
            const videoContainer = video.closest('.feature-video, .step-video');
            const playButton = videoContainer ? videoContainer.querySelector('.play-button') : null;
            
            video.addEventListener('play', () => {
                if (playButton) playButton.style.opacity = '0';
            });
            
            video.addEventListener('pause', () => {
                if (playButton) playButton.style.opacity = '1';
            });
            
            // Click on video to pause/play
            video.addEventListener('click', () => {
                if (video.paused) {
                    video.play();
                    if (playButton) {
                        playButton.textContent = '⏸';
                        playButton.style.opacity = '0';
                    }
                } else {
                    video.pause();
                    if (playButton) {
                        playButton.textContent = '▶';
                        playButton.style.opacity = '1';
                    }
                }
            });
        });

        // Toggle switch functionality
        document.querySelectorAll('.toggle').forEach(toggle => {
            toggle.addEventListener('click', function() {
                this.classList.toggle('active');
            });
        });

        // Pricing card interactions with enhanced effects
        document.querySelectorAll('.pricing-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                if (!this.classList.contains('popular')) {
                    this.style.transform = 'translateY(-12px) scale(1.02)';
                    this.style.boxShadow = '0 20px 60px rgba(139, 92, 246, 0.25)';
                }
            });
            
            card.addEventListener('mouseleave', function() {
                if (!this.classList.contains('popular')) {
                    this.style.transform = 'translateY(0) scale(1)';
                    this.style.boxShadow = '';
                }
            });
        });

        // Header scroll effect
        let lastScrollTop = 0;
        const header = document.querySelector('.header');
        
        window.addEventListener('scroll', function() {
            let scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            
            if (scrollTop > lastScrollTop && scrollTop > 100) {
                // Scrolling down
                header.style.transform = 'translateY(-100%)';
            } else {
                // Scrolling up
                header.style.transform = 'translateY(0)';
            }
            
            // Add shadow on scroll
            if (scrollTop > 50) {
                header.style.boxShadow = '0 4px 20px rgba(0, 0, 0, 0.3)';
            } else {
                header.style.boxShadow = 'none';
            }
            
            lastScrollTop = scrollTop;
        }, false);

        // Parallax effect for hero section
        window.addEventListener('scroll', function() {
            const scrolled = window.pageYOffset;
            const hero = document.querySelector('.hero');
            if (hero && scrolled < window.innerHeight) {
                hero.style.transform = `translateY(${scrolled * 0.5}px)`;
                hero.style.opacity = 1 - (scrolled / window.innerHeight);
            }
        });

        // Add particle effects to hero section
        function createParticles() {
            const hero = document.querySelector('.hero');
            const particleCount = 20;
            
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.className = 'hero-particle';
                particle.style.cssText = `
                    position: absolute;
                    width: 3px;
                    height: 3px;
                    background: rgba(139, 92, 246, 0.5);
                    border-radius: 50%;
                    left: ${Math.random() * 100}%;
                    top: ${Math.random() * 100}%;
                    animation: floatParticle ${10 + Math.random() * 10}s linear infinite;
                    animation-delay: ${Math.random() * 5}s;
                    pointer-events: none;
                `;
                hero.appendChild(particle);
            }
        }

        // Add CSS animation for particles
        const style = document.createElement('style');
        style.textContent = `
            @keyframes floatParticle {
                0% {
                    transform: translateY(0) translateX(0);
                    opacity: 0;
                }
                10% {
                    opacity: 1;
                }
                90% {
                    opacity: 1;
                }
                100% {
                    transform: translateY(-100vh) translateX(${Math.random() * 100 - 50}px);
                    opacity: 0;
                }
            }
            
            @keyframes ripple {
                0% {
                    transform: scale(0);
                    opacity: 1;
                }
                100% {
                    transform: scale(2);
                    opacity: 0;
                }
            }

            .header {
                transition: transform 0.3s ease, box-shadow 0.3s ease;
            }
        `;
        document.head.appendChild(style);

        // Initialize particles
        createParticles();

        // Back to Top Button
        const backToTopButton = document.getElementById('backToTop');
        
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                backToTopButton.classList.add('show');
            } else {
                backToTopButton.classList.remove('show');
            }
        });

        backToTopButton.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });

        // Mobile Menu Toggle
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const mobileMenu = document.getElementById('mobileMenu');
        const mobileMenuClose = document.getElementById('mobileMenuClose');
        const mobileNavLinks = document.querySelectorAll('.mobile-nav-link');

        function toggleMobileMenu() {
            mobileMenu.classList.toggle('active');
            mobileMenuBtn.classList.toggle('active');
            document.body.style.overflow = mobileMenu.classList.contains('active') ? 'hidden' : '';
        }

        mobileMenuBtn.addEventListener('click', toggleMobileMenu);
        mobileMenuClose.addEventListener('click', toggleMobileMenu);

        // Close mobile menu when clicking on a link
        mobileNavLinks.forEach(link => {
            link.addEventListener('click', function() {
                toggleMobileMenu();
                // The smooth scroll will be handled by the existing code
            });
        });

        // Close mobile menu when clicking outside
        mobileMenu.addEventListener('click', function(e) {
            if (e.target === this) {
                toggleMobileMenu();
            }
        });

        // Smooth number counting animation for stats (if you add any)
        function animateValue(element, start, end, duration) {
            let startTimestamp = null;
            const step = (timestamp) => {
                if (!startTimestamp) startTimestamp = timestamp;
                const progress = Math.min((timestamp - startTimestamp) / duration, 1);
                element.textContent = Math.floor(progress * (end - start) + start);
                if (progress < 1) {
                    window.requestAnimationFrame(step);
                }
            };
            window.requestAnimationFrame(step);
        }

        // Add hover effects to feature cards
        document.querySelectorAll('.feature-card, .security-card').forEach(card => {
            card.addEventListener('mousemove', function(e) {
                const rect = this.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                
                this.style.setProperty('--mouse-x', `${x}px`);
                this.style.setProperty('--mouse-y', `${y}px`);
            });
        });

        // Lazy load images if any
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        if (img.dataset.src) {
                            img.src = img.dataset.src;
                            img.classList.add('loaded');
                            imageObserver.unobserve(img);
                        }
                    }
                });
            });

            document.querySelectorAll('img[data-src]').forEach(img => {
                imageObserver.observe(img);
            });
        }
    </script>
</body>
</html>