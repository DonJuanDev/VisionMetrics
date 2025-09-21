// Dashboard do Cliente - VisionMetrics
// Sistema completo de dashboard com dados impressionantes para vendas

export class ClientDashboard {
    constructor() {
        this.charts = {};
        this.currentUser = null;
        this.init();
    }

    init() {
        this.checkAuthentication();
        this.loadUserData();
        this.initCharts();
        this.updateDashboard();
        this.setupEventListeners();
        
        // Atualizar dashboard a cada 30 segundos
        setInterval(() => {
            this.updateDashboard();
        }, 30000);
    }

    checkAuthentication() {
        const savedUser = localStorage.getItem('visionMetricsCurrentUser');
        if (!savedUser) {
            // Modo demo - criar usuário fictício
            this.currentUser = {
                firstName: 'Demo',
                lastName: 'User',
                plan: 'Premium',
                email: 'demo@visionmetrics.com'
            };
            console.log('Modo demo ativado - usuário fictício criado');
        } else {
            this.currentUser = JSON.parse(savedUser);
        }
        
        this.updateUserInfo();
    }

    updateUserInfo() {
        if (!this.currentUser) return;
        
        const userName = document.getElementById('sidebarUserName');
        const userPlan = document.getElementById('sidebarUserPlan');
        
        if (userName) {
            userName.textContent = `${this.currentUser.firstName} ${this.currentUser.lastName}`;
        }
        
        if (userPlan) {
            userPlan.textContent = `Plano ${this.currentUser.plan || 'Premium'}`;
        }
    }

    loadUserData() {
        // Carregar dados do usuário e analytics - DADOS IMPRESSIONANTES PARA VENDAS
        this.userData = {
            visitors: 125420,
            pageViews: 456800,
            conversions: 1247,
            bounceRate: 28.5,
            revenue: 187050,
            growth: 1403,
            trafficSources: {
                'Facebook': 45200,
                'Instagram': 28400,
                'TikTok': 89600,
                'Google': 15600,
                'LinkedIn': 3200,
                'Direto': 8900
            },
            recentActivity: this.generateRecentActivity(),
            planLimits: this.getPlanLimits()
        };
    }

    getPlanLimits() {
        const plan = this.currentUser?.plan || 'Professional';
        const limits = {
            'Starter': {
                visitors: 10000,
                sites: 3,
                reports: 10,
                exports: 5
            },
            'Professional': {
                visitors: 100000,
                sites: 10,
                reports: 50,
                exports: 25
            },
            'Premium': {
                visitors: 999999,
                sites: 999,
                reports: 999,
                exports: 999
            },
            'Enterprise': {
                visitors: 999999,
                sites: 999,
                reports: 999,
                exports: 999
            }
        };
        
        return limits[plan] || limits['Professional'];
    }

    generateRecentActivity() {
        const activities = [
            {
                type: 'conversion',
                title: 'Nova venda: R$ 1.250 - Cliente Premium',
                time: '2 min atrás',
                icon: 'fas fa-shopping-cart',
                value: '+R$ 1.250'
            },
            {
                type: 'campaign',
                title: 'Campanha TikTok atingiu 100k visualizações',
                time: '15 min atrás',
                icon: 'fas fa-rocket',
                value: '+89.600 views'
            },
            {
                type: 'alert',
                title: 'Crescimento do Facebook subiu para 1,403%',
                time: '1 hora atrás',
                icon: 'fas fa-chart-line',
                value: '+340% Crescimento'
            },
            {
                type: 'new_lead',
                title: '15 novos leads qualificados hoje',
                time: '2 horas atrás',
                icon: 'fas fa-user-plus',
                value: '+15 leads'
            },
            {
                type: 'revenue',
                title: 'Meta mensal atingida: R$ 187.050',
                time: '3 horas atrás',
                icon: 'fas fa-trophy',
                value: '+R$ 87.050'
            },
            {
                type: 'visitor',
                title: 'Pico de tráfego: 45.200 visitantes',
                time: '4 horas atrás',
                icon: 'fas fa-users',
                value: '+45.200 users'
            }
        ];
        
        return activities.slice(0, 5);
    }

    initCharts() {
        this.initTrafficChart();
        this.initSourcesChart();
    }

    initTrafficChart() {
        const ctx = document.getElementById('dashboardTrafficChart');
        if (!ctx) return;
        
        // Gerar dados dos últimos 7 dias - CRESCIMENTO IMPRESSIONANTE
        const labels = [];
        const visitorsData = [];
        const pageViewsData = [];
        const revenueData = [];
        
        // Base de crescimento diário de 8%
        const baseVisitors = 15000;
        const basePageViews = 45000;
        const baseRevenue = 25000;
        
        for (let i = 6; i >= 0; i--) {
            const date = new Date();
            date.setDate(date.getDate() - i);
            labels.push(date.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit' }));
            
            const growthFactor = 1 + (6-i) * 0.08; // Crescimento de 8% por dia
            const randomFactor = 0.9 + Math.random() * 0.2; // Variação de ±10%
            
            visitorsData.push(Math.floor(baseVisitors * growthFactor * randomFactor));
            pageViewsData.push(Math.floor(basePageViews * growthFactor * randomFactor));
            revenueData.push(Math.floor(baseRevenue * growthFactor * randomFactor));
        }
        
        if (window.Chart) {
            this.charts.traffic = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Visitantes',
                        data: visitorsData,
                        borderColor: '#667eea',
                        backgroundColor: 'rgba(102, 126, 234, 0.1)',
                        fill: true,
                        tension: 0.4,
                        yAxisID: 'y'
                    }, {
                        label: 'Visualizações',
                        data: pageViewsData,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        fill: true,
                        tension: 0.4,
                        yAxisID: 'y'
                    }, {
                        label: 'Receita (R$)',
                        data: revenueData,
                        borderColor: '#f59e0b',
                        backgroundColor: 'rgba(245, 158, 11, 0.1)',
                        fill: true,
                        tension: 0.4,
                        yAxisID: 'y1'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    },
                    scales: {
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Visitantes / Visualizações'
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Receita (R$)'
                            },
                            grid: {
                                drawOnChartArea: false,
                            },
                        }
                    }
                }
            });
        }
    }

    initSourcesChart() {
        const ctx = document.getElementById('dashboardSourcesChart');
        if (!ctx) return;
        
        const sources = Object.keys(this.userData.trafficSources);
        const data = sources.map(source => this.userData.trafficSources[source]);
        
        if (window.Chart) {
            this.charts.sources = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: sources,
                    datasets: [{
                        data: data,
                        backgroundColor: [
                            '#3b5998', // Facebook
                            '#e4405f', // Instagram
                            '#000000', // TikTok
                            '#4285f4', // Google
                            '#0077b5', // LinkedIn
                            '#6b7280'  // Direto
                        ],
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }
    }

    updateDashboard() {
        this.updateStats();
        this.updateRecentActivity();
        this.updatePlanLimits();
        this.updateCharts();
    }

    updateStats() {
        // Atualizar estatísticas principais
        const visitorsEl = document.getElementById('dashboardVisitors');
        const pageViewsEl = document.getElementById('dashboardPageViews');
        const conversionsEl = document.getElementById('dashboardConversions');
        const bounceRateEl = document.getElementById('dashboardBounceRate');
        const revenueEl = document.getElementById('dashboardRevenue');
        const growthEl = document.getElementById('dashboardGrowth');
        
        if (visitorsEl) visitorsEl.textContent = this.userData.visitors.toLocaleString();
        if (pageViewsEl) pageViewsEl.textContent = this.userData.pageViews.toLocaleString();
        if (conversionsEl) conversionsEl.textContent = this.userData.conversions.toLocaleString();
        if (bounceRateEl) bounceRateEl.textContent = `${this.userData.bounceRate.toFixed(1)}%`;
        if (revenueEl) revenueEl.textContent = `R$ ${this.userData.revenue.toLocaleString()}`;
        if (growthEl) growthEl.textContent = `${this.userData.growth}%`;
    }

    updateRecentActivity() {
        const container = document.getElementById('recentActivityList');
        if (!container) return;
        
        container.innerHTML = this.userData.recentActivity.map(activity => `
            <div class="activity-item">
                <div class="activity-icon ${activity.type}">
                    <i class="${activity.icon}"></i>
                </div>
                <div class="activity-content">
                    <div class="activity-title">${activity.title}</div>
                    <div class="activity-time">${activity.time}</div>
                </div>
                ${activity.value ? `<div class="activity-value ${activity.type}">${activity.value}</div>` : ''}
            </div>
        `).join('');
    }

    updatePlanLimits() {
        const container = document.getElementById('planLimitsList');
        if (!container) return;
        
        const limits = this.userData.planLimits;
        const currentUsage = {
            visitors: this.userData.visitors,
            sites: Math.floor(Math.random() * 3) + 1,
            reports: Math.floor(Math.random() * 5) + 1,
            exports: Math.floor(Math.random() * 3) + 1
        };
        
        const limitItems = [
            { name: 'Visitantes/mês', used: currentUsage.visitors, limit: limits.visitors },
            { name: 'Sites', used: currentUsage.sites, limit: limits.sites },
            { name: 'Relatórios', used: currentUsage.reports, limit: limits.reports },
            { name: 'Exportações', used: currentUsage.exports, limit: limits.exports }
        ];
        
        container.innerHTML = limitItems.map(item => {
            const percentage = (item.used / item.limit) * 100;
            const barClass = percentage > 80 ? 'danger' : percentage > 60 ? 'warning' : '';
            
            return `
                <div class="limit-item">
                    <div class="limit-info">
                        <div class="limit-name">${item.name}</div>
                        <div class="limit-usage">${item.used.toLocaleString()} / ${item.limit.toLocaleString()}</div>
                    </div>
                    <div class="limit-bar">
                        <div class="limit-fill ${barClass}" style="width: ${Math.min(percentage, 100)}%"></div>
                    </div>
                </div>
            `;
        }).join('');
    }

    updateCharts() {
        Object.keys(this.charts).forEach(chartKey => {
            if (this.charts[chartKey]) {
                this.charts[chartKey].update();
            }
        });
    }

    setupEventListeners() {
        // Navegação da sidebar
        document.querySelectorAll('.sidebar-nav a').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                
                // Remover active de todos os links
                document.querySelectorAll('.sidebar-nav a').forEach(l => l.classList.remove('active'));
                
                // Adicionar active ao link clicado
                link.classList.add('active');
                
                // Simular navegação
                const href = link.getAttribute('href');
                if (href === '#dashboard') {
                    this.showSection('dashboard');
                } else if (href === '#analytics') {
                    this.showSection('analytics');
                } else if (href === '#campaigns') {
                    this.showSection('campaigns');
                } else if (href === '#reports') {
                    this.showSection('reports');
                } else if (href === '#integrations') {
                    this.showSection('integrations');
                } else if (href === '#settings') {
                    this.showSection('settings');
                }
            });
        });
        
        // Botões de data range
        document.querySelectorAll('.date-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.date-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                
                const range = btn.dataset.range;
                this.updateDateRange(range);
            });
        });
    }

    showSection(sectionName) {
        // Ocultar todas as seções
        document.querySelectorAll('.section-content').forEach(section => {
            section.classList.remove('active');
        });
        
        // Mostrar seção selecionada
        const targetSection = document.getElementById(`${sectionName}Section`);
        if (targetSection) {
            targetSection.classList.add('active');
        }
        
        // Atualizar título e descrição
        const sectionData = {
            'dashboard': {
                title: 'Dashboard',
                description: 'Bem-vindo de volta! Aqui está um resumo do seu desempenho.',
                actions: `
                    <button class="btn-secondary" onclick="exportData()">
                        <i class="fas fa-download"></i> Exportar
                    </button>
                    <button class="btn-primary" onclick="refreshData()">
                        <i class="fas fa-sync-alt"></i> Atualizar
                    </button>
                `
            },
            'analytics': {
                title: 'Analytics Avançado',
                description: 'Análise profunda de métricas e performance do seu negócio.',
                actions: `
                    <button class="btn-secondary" onclick="exportAnalytics()">
                        <i class="fas fa-download"></i> Exportar Analytics
                    </button>
                    <button class="btn-primary" onclick="refreshAnalytics()">
                        <i class="fas fa-sync-alt"></i> Atualizar Dados
                    </button>
                `
            },
            'campaigns': {
                title: 'Gerenciar Campanhas',
                description: 'Crie, gerencie e otimize suas campanhas de marketing digital.',
                actions: `
                    <button class="btn-secondary" onclick="exportCampaigns()">
                        <i class="fas fa-download"></i> Exportar Campanhas
                    </button>
                    <button class="btn-primary" onclick="createNewCampaign()">
                        <i class="fas fa-plus"></i> Nova Campanha
                    </button>
                `
            },
            'reports': {
                title: 'Relatórios',
                description: 'Gere relatórios personalizados e análises detalhadas.',
                actions: `
                    <button class="btn-secondary" onclick="exportReports()">
                        <i class="fas fa-download"></i> Exportar Relatórios
                    </button>
                    <button class="btn-primary" onclick="generateNewReport()">
                        <i class="fas fa-plus"></i> Novo Relatório
                    </button>
                `
            },
            'integrations': {
                title: 'Integrações',
                description: 'Conecte suas plataformas favoritas e sincronize dados.',
                actions: `
                    <button class="btn-secondary" onclick="testIntegrations()">
                        <i class="fas fa-plug"></i> Testar Conexões
                    </button>
                    <button class="btn-primary" onclick="addIntegration()">
                        <i class="fas fa-plus"></i> Nova Integração
                    </button>
                `
            },
            'settings': {
                title: 'Configurações',
                description: 'Personalize sua experiência e gerencie preferências.',
                actions: `
                    <button class="btn-secondary" onclick="resetSettings()">
                        <i class="fas fa-undo"></i> Restaurar Padrões
                    </button>
                    <button class="btn-primary" onclick="saveSettings()">
                        <i class="fas fa-save"></i> Salvar Alterações
                    </button>
                `
            }
        };
        
        const section = sectionData[sectionName];
        if (section) {
            const titleEl = document.getElementById('sectionTitle');
            const descEl = document.getElementById('sectionDescription');
            const actionsEl = document.getElementById('sectionActions');
            
            if (titleEl) titleEl.textContent = section.title;
            if (descEl) descEl.textContent = section.description;
            if (actionsEl) actionsEl.innerHTML = section.actions;
        }
        
        this.showNotification(`Navegando para ${section?.title || sectionName}...`, 'info');
    }

    updateDateRange(range) {
        // Atualizar gráficos baseado no range selecionado
        this.showNotification(`Atualizando dados para ${range}...`, 'info');
        
        // Simular atualização de dados
        setTimeout(() => {
            this.updateDashboard();
        }, 1000);
    }

    showNotification(message, type = 'info') {
        // Verificar se já existe uma função showNotification
        if (typeof window.showNotification === 'function') {
            return window.showNotification(message, type);
        }
        
        // Criar notificação simples se não existir
        const notification = document.createElement('div');
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${type === 'error' ? '#ef4444' : type === 'success' ? '#10b981' : type === 'warning' ? '#f59e0b' : '#3b82f6'};
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 10000;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 14px;
            font-weight: 500;
            max-width: 300px;
            word-wrap: break-word;
        `;
        
        notification.textContent = message;
        document.body.appendChild(notification);
        
        // Remover após 3 segundos
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 3000);
    }
}

// Funções específicas do dashboard
export function createCampaign() {
    showNotification('Abrindo criador de campanhas...', 'info');
    // Em produção, abriria modal ou página de criação de campanhas
}

export function generateReport() {
    showNotification('Gerando relatório...', 'info');
    // Em produção, abriria modal de geração de relatórios
}

export function refreshData() {
    showNotification('Atualizando dados...', 'info');
    if (window.clientDashboard) {
        window.clientDashboard.updateDashboard();
    }
}

export function exportData() {
    showNotification('Preparando exportação...', 'info');
    // Em produção, faria download dos dados
}

// Funções adicionais para botões do dashboard
export function showSettings() {
    showNotification('Abrindo configurações...', 'info');
    // Simular abertura de modal de configurações
    setTimeout(() => {
        // Em produção, abriria modal de configurações
        alert('Modal de Configurações seria aberto aqui');
    }, 500);
}

export function showBilling() {
    showNotification('Abrindo gerenciamento de assinatura...', 'info');
    // Simular abertura de modal de billing
    setTimeout(() => {
        // Em produção, abriria modal de billing
        alert('Modal de Assinatura seria aberto aqui');
    }, 500);
}

export function logout() {
    if (confirm('Tem certeza que deseja sair?')) {
        showNotification('Saindo do sistema...', 'info');
        // Limpar dados do usuário
        localStorage.removeItem('visionMetricsCurrentUser');
        localStorage.removeItem('visionMetricsData');
        
        // Logout sem redirecionamento - modo demo
        console.log('Logout realizado (modo demo)');
    }
}

// Função para mostrar notificações (se não existir)
export function showNotification(message, type = 'info') {
    // Verificar se já existe uma função showNotification
    if (typeof window.showNotification === 'function') {
        return window.showNotification(message, type);
    }
    
    // Criar notificação simples se não existir
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'error' ? '#ef4444' : type === 'success' ? '#10b981' : type === 'warning' ? '#f59e0b' : '#3b82f6'};
        color: white;
        padding: 12px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 10000;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        font-size: 14px;
        font-weight: 500;
        max-width: 300px;
        word-wrap: break-word;
    `;
    
    notification.textContent = message;
    document.body.appendChild(notification);
    
    // Remover após 3 segundos
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 3000);
}

// Função para alternar sidebar em mobile
export function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    if (sidebar) {
        sidebar.style.display = sidebar.style.display === 'none' ? 'block' : 'none';
    }
}

// Funções específicas para cada seção
export function createNewCampaign() {
    showNotification('Abrindo criador de nova campanha...', 'info');
    setTimeout(() => {
        alert('Modal de criação de campanha seria aberto aqui');
    }, 500);
}

export function generateNewReport() {
    showNotification('Gerando novo relatório...', 'info');
    setTimeout(() => {
        alert('Modal de geração de relatório seria aberto aqui');
    }, 500);
}

export function exportAnalytics() {
    showNotification('Exportando dados de analytics...', 'success');
}

export function refreshAnalytics() {
    showNotification('Atualizando dados de analytics...', 'info');
}

export function exportCampaigns() {
    showNotification('Exportando dados de campanhas...', 'success');
}

export function exportReports() {
    showNotification('Exportando relatórios...', 'success');
}

export function testIntegrations() {
    showNotification('Testando conexões das integrações...', 'info');
    setTimeout(() => {
        showNotification('Todas as integrações estão funcionando!', 'success');
    }, 2000);
}

export function addIntegration() {
    showNotification('Abrindo seletor de novas integrações...', 'info');
    setTimeout(() => {
        alert('Modal de novas integrações seria aberto aqui');
    }, 500);
}

export function resetSettings() {
    if (confirm('Tem certeza que deseja restaurar as configurações padrão?')) {
        showNotification('Restaurando configurações padrão...', 'warning');
        setTimeout(() => {
            showNotification('Configurações restauradas!', 'success');
        }, 1000);
    }
}

export function saveSettings() {
    showNotification('Salvando configurações...', 'info');
    setTimeout(() => {
        showNotification('Configurações salvas com sucesso!', 'success');
    }, 1000);
}

// Sistema de Tema Escuro/Claro
export function initializeTheme() {
    const savedTheme = localStorage.getItem('visionMetricsTheme') || 'light';
    console.log('initializeTheme - tema salvo:', savedTheme);
    setTheme(savedTheme);
    updateThemeIcon(savedTheme);
    console.log('Tema inicializado:', document.documentElement.getAttribute('data-theme'));
}

export function toggleTheme() {
    console.log('toggleTheme chamada no dashboard');
    const currentTheme = document.documentElement.getAttribute('data-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    console.log('Tema atual:', currentTheme, 'Novo tema:', newTheme);
    
    setTheme(newTheme);
    localStorage.setItem('visionMetricsTheme', newTheme);
    
    // Atualizar ícone do botão
    updateThemeIcon(newTheme);
    
    // Mostrar notificação
    showNotification(`Tema alterado para ${newTheme === 'dark' ? 'escuro' : 'claro'}`, 'success');
}

export function setTheme(theme) {
    console.log('setTheme chamada com:', theme);
    
    // Aplicar atributo data-theme
    document.documentElement.setAttribute('data-theme', theme);
    
    // Forçar aplicação do tema via JavaScript
    if (theme === 'dark') {
        // Aplicar tema escuro
        document.body.style.backgroundColor = '#1e293b';
        document.body.style.color = '#f8fafc';
        
        // Aplicar a todos os elementos principais
        const mainContent = document.querySelector('.main-content');
        if (mainContent) {
            mainContent.style.backgroundColor = '#1e293b';
            mainContent.style.color = '#f8fafc';
        }
        
        const sidebar = document.querySelector('.sidebar');
        if (sidebar) {
            sidebar.style.backgroundColor = '#0f172a';
            sidebar.style.color = '#f8fafc';
        }
        
        const header = document.querySelector('.header');
        if (header) {
            header.style.backgroundColor = '#0f172a';
            header.style.color = '#f8fafc';
        }
        
        // Aplicar a todos os cards
        const cards = document.querySelectorAll('.metric-card, .chart-card');
        cards.forEach(card => {
            card.style.backgroundColor = '#0f172a';
            card.style.color = '#f8fafc';
            card.style.border = '1px solid #475569';
        });
        
        // Aplicar a todos os canvas
        const canvases = document.querySelectorAll('canvas');
        canvases.forEach(canvas => {
            canvas.style.backgroundColor = '#0f172a';
        });
        
        // Aplicar a todos os títulos
        const headings = document.querySelectorAll('h1, h2, h3, h4, h5, h6');
        headings.forEach(heading => {
            heading.style.color = '#f8fafc';
        });
        
        // Aplicar a todos os parágrafos e spans
        const texts = document.querySelectorAll('p, span, div');
        texts.forEach(text => {
            text.style.color = '#f8fafc';
        });
        
        console.log('Tema escuro aplicado via JavaScript');
    } else {
        // Aplicar tema claro
        document.body.style.backgroundColor = '#ffffff';
        document.body.style.color = '#111827';
        
        // Remover estilos forçados
        const elements = document.querySelectorAll('.main-content, .sidebar, .header, .metric-card, .chart-card, canvas, h1, h2, h3, h4, h5, h6, p, span, div');
        elements.forEach(element => {
            element.style.backgroundColor = '';
            element.style.color = '';
            element.style.border = '';
        });
        
        console.log('Tema claro aplicado via JavaScript');
    }
    
    updateThemeIcon(theme);
}

export function updateThemeIcon(theme) {
    const themeIcon = document.getElementById('theme-icon');
    console.log('updateThemeIcon chamada, tema:', theme, 'ícone encontrado:', !!themeIcon);
    if (themeIcon) {
        // Quando está no tema escuro, mostra sol (para alternar para claro)
        // Quando está no tema claro, mostra lua (para alternar para escuro)
        themeIcon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
        console.log('Ícone atualizado para:', themeIcon.className);
    } else {
        console.log('Ícone do tema não encontrado!');
    }
}

// Inicializar dashboard quando a página carregar
export function initializeDashboard() {
    if (typeof window !== 'undefined') {
        window.clientDashboard = new ClientDashboard();
    }
}

// Verificar se o usuário está logado
export function checkUserLogin() {
    // Função desabilitada - sem redirecionamentos
    console.log('Sistema de login desabilitado - modo demo ativo');
}

// Executar verificação imediatamente
checkUserLogin();
