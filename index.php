<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VisionMetrics - Rastreie suas conversas e alavance suas vendas pelo WhatsApp</title>
    <meta name="description" content="Utilize o poder dos dados para alavancar suas vendas pelo WhatsApp! Rastreie suas conversas e acesse insights valiosos para otimizar suas campanhas.">
    <meta name="keywords" content="whatsapp, rastreamento, vendas, conversas, meta ads, google ads, leads, marketing digital, automação">
    
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
                <a href="#features" class="nav-link">Funcionalidades</a>
                <a href="#how-it-works" class="nav-link">Como Funciona</a>
                <a href="#testimonials" class="nav-link">Depoimentos</a>
                <a href="#pricing" class="nav-link">Planos</a>
            </nav>
            
            <div class="header-actions">
                <a href="/backend/login.php" class="btn-login">Entrar</a>
                <a href="/backend/register.php" class="btn-primary">Teste Grátis</a>
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
            <h1>Utilize o poder dos <span class="highlight">dados</span> para alavancar suas <span class="highlight">vendas</span> pelo WhatsApp!</h1>
            <p>Rastreie suas conversas e acesse insights valiosos para otimizar suas campanhas. Mais de 5.000 empresas confiam nos insights do VisionMetrics para alavancar suas vendas.</p>
            <div class="hero-cta">
                <a href="/backend/register.php" class="btn-primary">
                    TESTAR GRÁTIS
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </a>
                <a href="#features" class="btn-secondary">
                    Ver Funcionalidades
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
            <h2 class="section-title">Descubra como o VisionMetrics vai alavancar suas vendas:</h2>
            <p class="section-subtitle">Uma série de recursos únicos feitos com um único objetivo: aumentar suas vendas!</p>
            
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
                                <source src="videos/video-teste.webm" type="video/mp4">
                            </video>
                        </div>
                        <div class="play-button">▶</div>
                    </div>
                    <div class="feature-icon blue">💰</div>
                    <h3 class="feature-title">Identificação Automática de Vendas</h3>
                    <p class="feature-description">O VisionMetrics é capaz de ler suas conversas e identificar quando acontece uma venda. Além disso, o VisionMetrics também consegue identificar o valor da venda. Tudo de forma automática!</p>
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
                                <source src="videos/video-teste.webm" type="video/mp4">
                            </video>
                        </div>
                        <div class="play-button">▶</div>
                    </div>
                    <div class="feature-icon yellow">🔍</div>
                    <h3 class="feature-title">Rastreamento de Conversas</h3>
                    <p class="feature-description">O VisionMetrics é capaz de descobrir, de forma automática, qual a campanha, conjunto e anúncio que originou aquela conversa.</p>
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
                                <source src="videos/video-teste.webm" type="video/mp4">
                            </video>
                        </div>
                        <div class="play-button">▶</div>
                    </div>
                    <div class="feature-icon green">🔗</div>
                    <h3 class="feature-title">Integração Meta Ads e Google Ads</h3>
                    <p class="feature-description">O VisionMetrics envia dados para o Meta Ads de forma 100% automática e disponibiliza relatórios prontos para serem importados no Google Ads.</p>
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
                                    <source src="videos/video-teste.webm" type="video/mp4">
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
                                    <source src="videos/video-teste.webm" type="video/mp4">
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
                                    <source src="videos/video-teste.webm" type="video/mp4">
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
                                    <source src="videos/video-teste.webm" type="video/mp4">
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
                    <div class="integration-screenshot">
                        <img src="videos/JUAN-DUNDAS-SQUARE.webp" 
                             alt="Slack Integration Screenshot" 
                             style="width: 100%; height: 100%; object-fit: cover; border-radius: 12px;">
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
                    <h3 class="pricing-title">Starter</h3>
                    <p class="pricing-description">Para quem está começando a vender pela internet</p>
                    <div class="pricing-price">R$ 99<span class="pricing-period">,99/mês</span></div>
                    <ul class="pricing-features">
                        <li><span class="check-icon">✓</span> 1 número de WhatsApp rastreado</li>
                        <li><span class="check-icon">✓</span> Conversas ilimitadas</li>
                        <li><span class="check-icon">✓</span> Rastreamento de Leads</li>
                        <li><span class="check-icon">✓</span> Dashboard para visualização dos dados</li>
                    </ul>
                    <button class="pricing-button secondary">Testar grátis por 7 dias</button>
                </div>
                
                <div class="pricing-card popular">
                    <div class="popular-badge">MAIS POPULAR</div>
                    <h3 class="pricing-title">Advanced</h3>
                    <p class="pricing-description">Para quem já vende pela internet</p>
                    <div class="pricing-price">R$ 180<span class="pricing-period">/mês</span></div>
                    <ul class="pricing-features">
                        <li><span class="check-icon">✓</span> Tudo do plano Inicial e mais...</li>
                        <li><span class="check-icon">✓</span> Disparo de Webhook</li>
                        <li><span class="check-icon">✓</span> Envio de dados para o Meta Ads e Google Ads</li>
                        <li><span class="check-icon">✓</span> Acesso à relatórios e dashboard para terceiros</li>
                    </ul>
                    <button class="pricing-button primary">Começar Agora</button>
                </div>
                
                <div class="pricing-card">
                    <h3 class="pricing-title">Custom</h3>
                    <p class="pricing-description">Para empresas com múltiplas equipes</p>
                    <div class="pricing-price">Valor a combinar</div>
                    <ul class="pricing-features">
                        <li><span class="check-icon">✓</span> Tudo do plano Escala e mais...</li>
                        <li><span class="check-icon">✓</span> Descontos a partir de 65%</li>
                        <li><span class="check-icon">✓</span> Atendimento Prioritário</li>
                        <li><span class="check-icon">✓</span> Call de implementação com nosso time</li>
                    </ul>
                    <button class="pricing-button secondary">Testar grátis por 7 dias</button>
            </div>
        </div>
        
            <p style="text-align: center; margin-top: 40px; font-size: 14px; color: var(--text-muted);">
                * Todos os planos possuem um período grátis de 7 dias e você será notificado quando estiver perto do fim para cancelar ou continuar conosco! :)<br>
                OBS: se torna responsabilidade do usuário cancelar antes do fim do período para não ser cobrado o valor do plano escolhido.
            </p>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-main">
                <div class="footer-brand">
                    <a href="#" class="footer-logo">
                        <div class="logo-icon">V</div>
                        <span>VisionMetrics</span>
                    </a>
                    <p class="footer-description">
                        Revolucionando o rastreamento de vendas pelo WhatsApp com tecnologia avançada e insights poderosos para impulsionar seu negócio.
                    </p>
                    <div class="footer-social">
                        <a href="https://www.instagram.com/visionmetricsapp/" target="_blank" class="social-link instagram" title="Instagram">
                            <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                            </svg>
                        </a>
                        <a href="mailto:contato@visionmetrics.com" class="social-link email" title="Email">
                            <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/>
                            </svg>
                        </a>
                    </div>
                </div>

                <div class="footer-links">
                    <div class="footer-section">
                        <h4 class="footer-title">Produto</h4>
                        <ul class="footer-list">
                            <li><a href="#features">Funcionalidades</a></li>
                            <li><a href="#pricing">Planos</a></li>
                            <li><a href="/backend/login.php">Login</a></li>
                            <li><a href="/backend/register.php">Teste Grátis</a></li>
                        </ul>
                    </div>

                    <div class="footer-section">
                        <h4 class="footer-title">Suporte</h4>
                        <ul class="footer-list">
                            <li><a href="mailto:contato@visionmetrics.com">contato@visionmetrics.com</a></li>
                            <li><a href="tel:+5548985020495">(48) 98502-0495</a></li>
                            <li><a href="https://www.instagram.com/visionmetricsapp/" target="_blank">@visionmetricsapp</a></li>
                        </ul>
                    </div>

                    <div class="footer-section">
                        <h4 class="footer-title">Empresa</h4>
                        <ul class="footer-list">
                            <li><span class="company-info">VisionMetrics Tecnologia Ltda</span></li>
                            <li><span class="company-info">CNPJ: 00.000.000/0000-00</span></li>
                            <li><span class="company-info">Santa Catarina, Brasil</span></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="footer-bottom">
                <p class="copyright">© 2025 VisionMetrics. Todos os direitos reservados.</p>
                <div class="footer-legal">
                    <a href="#privacy">Política de Privacidade</a>
                    <a href="#terms">Termos de Uso</a>
                    <a href="#lgpd">LGPD</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- WhatsApp Button -->
    <a href="https://wa.me/5548985020495?text=Olá!%20Gostaria%20de%20saber%20mais%20sobre%20o%20VisionMetrics."
       target="_blank"
       class="whatsapp-button"
       aria-label="Falar no WhatsApp">
        <svg width="28" height="28" fill="currentColor" viewBox="0 0 24 24">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.465 3.516"/>
        </svg>
        <span class="whatsapp-text">WhatsApp</span>
    </a>

    <style>
        .whatsapp-button {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
            border: none;
            border-radius: 50px;
            color: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px 12px 16px;
            box-shadow: 0 4px 20px rgba(37, 211, 102, 0.4);
            opacity: 0;
            visibility: hidden;
            transform: translateY(100px);
            transition: all 0.3s ease;
            z-index: 1000;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
        }

        .whatsapp-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
            border-radius: 50px;
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: -1;
        }

        .whatsapp-button.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .whatsapp-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 30px rgba(37, 211, 102, 0.6);
        }

        .whatsapp-button:hover::before {
            opacity: 1;
        }

        .whatsapp-button:active {
            transform: translateY(-1px);
        }

        .whatsapp-text {
            font-family: 'Inter', sans-serif;
            white-space: nowrap;
        }

        @media (max-width: 768px) {
            .whatsapp-button {
                bottom: 20px;
                right: 20px;
                padding: 10px 16px 10px 12px;
                gap: 8px;
                font-size: 13px;
            }

            .whatsapp-button svg {
                width: 24px;
                height: 24px;
            }
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

        /* Footer Professional Styles */
        .footer {
            background: linear-gradient(135deg, #0A0A0B 0%, #111113 50%, #1a1a1a 100%);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding: 60px 0 20px;
            margin-top: 80px;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 32px;
        }

        .footer-main {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 60px;
            margin-bottom: 40px;
        }

        .footer-brand {
            max-width: 400px;
        }

        .footer-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            margin-bottom: 20px;
        }

        .footer-logo .logo-icon {
            width: 40px;
            height: 40px;
            background: var(--gradient-primary);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 20px;
        }

        .footer-logo span {
            font-size: 24px;
            font-weight: 700;
            color: var(--text-primary);
            font-family: 'Poppins', sans-serif;
        }

        .footer-description {
            color: var(--text-secondary);
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 24px;
        }

        .footer-social {
            display: flex;
            gap: 16px;
        }

        .footer-social .social-link {
            width: 44px;
            height: 44px;
            background: var(--bg-glass);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-primary);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .footer-social .social-link:hover {
            background: var(--gradient-primary);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(139, 92, 246, 0.3);
        }

        .footer-social .social-link.instagram:hover {
            background: linear-gradient(45deg, #f09433 0%,#e6683c 25%,#dc2743 50%,#cc2366 75%,#bc1888 100%);
        }

        .footer-social .social-link.email:hover {
            background: linear-gradient(45deg, #4285f4, #34a853, #fbbc05, #ea4335);
        }

        .footer-links {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 40px;
        }

        .footer-section {
            min-width: 0;
        }

        .footer-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 20px;
            font-family: 'Poppins', sans-serif;
        }

        .footer-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .footer-list li {
            margin-bottom: 12px;
        }

        .footer-list a {
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s ease;
            display: block;
            padding: 4px 0;
        }

        .footer-list a:hover {
            color: var(--primary);
            padding-left: 8px;
        }

        .company-info {
            color: var(--text-secondary);
            font-size: 14px;
            display: block;
            padding: 4px 0;
        }

        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .copyright {
            color: var(--text-muted);
            font-size: 14px;
            margin: 0;
        }

        .footer-legal {
            display: flex;
            gap: 24px;
        }

        .footer-legal a {
            color: var(--text-muted);
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s ease;
        }

        .footer-legal a:hover {
            color: var(--text-primary);
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

            .footer {
                padding: 40px 0 20px;
                margin-top: 60px;
            }

            .footer-content {
                padding: 0 20px;
            }

            .footer-main {
                grid-template-columns: 1fr;
                gap: 40px;
            }

            .footer-links {
                grid-template-columns: 1fr;
                gap: 32px;
            }

            .footer-bottom {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }

            .footer-legal {
                flex-wrap: wrap;
                justify-content: center;
                gap: 16px;
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

        // WhatsApp Button
        const whatsappButton = document.querySelector('.whatsapp-button');

        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                whatsappButton.classList.add('show');
            } else {
                whatsappButton.classList.remove('show');
            }
        });

        // WhatsApp button click is handled by the href attribute (opens WhatsApp directly)

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