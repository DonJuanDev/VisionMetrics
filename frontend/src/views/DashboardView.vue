<template>
  <div class="dashboard-container">
    <!-- Header do Dashboard -->
    <div class="dashboard-header">
      <div class="header-left">
        <h1 class="dashboard-title">Dashboard</h1>
        <p class="dashboard-subtitle">Bem-vindo de volta! Aqui está um resumo do seu desempenho.</p>
      </div>
      <div class="header-right">
        <div class="date-range-selector">
          <button 
            v-for="range in dateRanges" 
            :key="range.value"
            @click="handleUpdateDateRange(range.value)"
            :class="['date-btn', { active: selectedDateRange === range.value }]"
          >
            {{ range.label }}
          </button>
        </div>
        <button @click="refreshDashboard" class="btn btn-primary">
          <i class="fas fa-sync-alt"></i>
          Atualizar
        </button>
        <button @click="handleExportData" class="btn btn-secondary">
          <i class="fas fa-download"></i>
          Exportar
        </button>
      </div>
    </div>

    <!-- Stats Cards Impressionantes -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon">
          <i class="fas fa-users"></i>
        </div>
        <div class="stat-content">
          <h3>Visitantes</h3>
          <div class="stat-value" id="dashboardVisitors">{{ formatNumber(userData.visitors) }}</div>
          <div class="stat-change positive">+{{ userData.growth }}%</div>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon">
          <i class="fas fa-eye"></i>
        </div>
        <div class="stat-content">
          <h3>Visualizações</h3>
          <div class="stat-value" id="dashboardPageViews">{{ formatNumber(userData.pageViews) }}</div>
          <div class="stat-change positive">+12.5%</div>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon">
          <i class="fas fa-shopping-cart"></i>
        </div>
        <div class="stat-content">
          <h3>Conversões</h3>
          <div class="stat-value" id="dashboardConversions">{{ formatNumber(userData.conversions) }}</div>
          <div class="stat-change positive">+8.3%</div>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon">
          <i class="fas fa-percentage"></i>
        </div>
        <div class="stat-content">
          <h3>Taxa de Rejeição</h3>
          <div class="stat-value" id="dashboardBounceRate">{{ userData.bounceRate }}%</div>
          <div class="stat-change negative">-2.1%</div>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon">
          <i class="fas fa-dollar-sign"></i>
        </div>
        <div class="stat-content">
          <h3>Receita</h3>
          <div class="stat-value" id="dashboardRevenue">{{ formatCurrency(userData.revenue) }}</div>
          <div class="stat-change positive">+15.2%</div>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon">
          <i class="fas fa-chart-line"></i>
        </div>
        <div class="stat-content">
          <h3>Crescimento</h3>
          <div class="stat-value" id="dashboardGrowth">{{ userData.growth }}%</div>
          <div class="stat-change positive">+340%</div>
        </div>
      </div>
    </div>

    <!-- Charts Section -->
    <div class="charts-grid">
      <!-- Gráfico de Tráfego -->
      <div class="chart-card">
        <div class="chart-header">
          <h3>Tráfego e Receita (Últimos 7 dias)</h3>
          <div class="chart-actions">
            <button class="btn-small" @click="updateDateRange('7d')">7d</button>
            <button class="btn-small" @click="updateDateRange('30d')">30d</button>
            <button class="btn-small" @click="updateDateRange('90d')">90d</button>
          </div>
        </div>
        <div class="chart-content">
          <canvas id="dashboardTrafficChart" width="400" height="200"></canvas>
        </div>
      </div>
      
      <!-- Gráfico de Fontes -->
      <div class="chart-card">
        <div class="chart-header">
          <h3>Fontes de Tráfego</h3>
        </div>
        <div class="chart-content">
          <canvas id="dashboardSourcesChart" width="400" height="200"></canvas>
        </div>
      </div>
    </div>

    <!-- Atividade Recente e Limites do Plano -->
    <div class="bottom-grid">
      <!-- Atividade Recente -->
      <div class="activity-card">
        <div class="card-header">
          <h3>Atividade Recente</h3>
          <button @click="handleRefreshActivity" class="btn-small">
            <i class="fas fa-sync-alt"></i>
          </button>
        </div>
        <div class="activity-list" id="recentActivityList">
          <div v-for="activity in userData.recentActivity" :key="activity.id" class="activity-item">
            <div :class="['activity-icon', activity.type]">
              <i :class="activity.icon"></i>
            </div>
            <div class="activity-content">
              <div class="activity-title">{{ activity.title }}</div>
              <div class="activity-time">{{ activity.time }}</div>
            </div>
            <div v-if="activity.value" :class="['activity-value', activity.type]">
              {{ activity.value }}
            </div>
          </div>
        </div>
      </div>

      <!-- Limites do Plano -->
      <div class="limits-card">
        <div class="card-header">
          <h3>Limites do Plano</h3>
          <span class="plan-badge">{{ currentUser.plan }}</span>
        </div>
        <div class="limits-list" id="planLimitsList">
          <div v-for="limit in planLimits" :key="limit.name" class="limit-item">
            <div class="limit-info">
              <div class="limit-name">{{ limit.name }}</div>
              <div class="limit-usage">{{ formatNumber(limit.used) }} / {{ formatNumber(limit.limit) }}</div>
            </div>
            <div class="limit-bar">
              <div 
                :class="['limit-fill', getLimitBarClass(limit.percentage)]" 
                :style="{ width: Math.min(limit.percentage, 100) + '%' }"
              ></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Ações Rápidas -->
    <div class="quick-actions">
      <h3>Ações Rápidas</h3>
      <div class="actions-grid">
        <button @click="handleCreateCampaign" class="action-card">
          <div class="action-icon">
            <i class="fas fa-rocket"></i>
          </div>
          <h4>Nova Campanha</h4>
          <p>Crie campanhas de marketing</p>
        </button>
        
        <button @click="handleGenerateReport" class="action-card">
          <div class="action-icon">
            <i class="fas fa-chart-bar"></i>
          </div>
          <h4>Gerar Relatório</h4>
          <p>Relatórios personalizados</p>
        </button>
        
        <button @click="handleExportData" class="action-card">
          <div class="action-icon">
            <i class="fas fa-download"></i>
          </div>
          <h4>Exportar Dados</h4>
          <p>Baixe seus dados</p>
        </button>
        
        <button @click="handleShowSettings" class="action-card">
          <div class="action-icon">
            <i class="fas fa-cog"></i>
          </div>
          <h4>Configurações</h4>
          <p>Personalize sua conta</p>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { apiMethods } from '@/services/api'
import { 
  ClientDashboard, 
  showNotification, 
  createCampaign, 
  generateReport, 
  exportData, 
  showSettings,
  refreshData,
  refreshActivity,
  updateDateRange,
  initializeDashboard
} from '@/utils/dashboard'

const authStore = useAuthStore()

// Dados do usuário - DADOS IMPRESSIONANTES PARA VENDAS
const currentUser = ref({
  firstName: 'Demo',
  lastName: 'User',
  plan: 'Premium',
  email: 'demo@visionmetrics.com'
})

const userData = ref({
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
  recentActivity: [],
  planLimits: {}
})

const dateRanges = ref([
  { label: '7d', value: '7d' },
  { label: '30d', value: '30d' },
  { label: '90d', value: '90d' }
])

const selectedDateRange = ref('30d')
const charts = ref({})

// Limites do plano
const planLimits = computed(() => {
  const limits = userData.value.planLimits
  const currentUsage = {
    visitors: userData.value.visitors,
    sites: Math.floor(Math.random() * 3) + 1,
    reports: Math.floor(Math.random() * 5) + 1,
    exports: Math.floor(Math.random() * 3) + 1
  }
  
  return [
    { 
      name: 'Visitantes/mês', 
      used: currentUsage.visitors, 
      limit: limits.visitors,
      percentage: (currentUsage.visitors / limits.visitors) * 100
    },
    { 
      name: 'Sites', 
      used: currentUsage.sites, 
      limit: limits.sites,
      percentage: (currentUsage.sites / limits.sites) * 100
    },
    { 
      name: 'Relatórios', 
      used: currentUsage.reports, 
      limit: limits.reports,
      percentage: (currentUsage.reports / limits.reports) * 100
    },
    { 
      name: 'Exportações', 
      used: currentUsage.exports, 
      limit: limits.exports,
      percentage: (currentUsage.exports / limits.exports) * 100
    }
  ]
})

// Funções de formatação
const formatNumber = (num) => {
  return new Intl.NumberFormat('pt-BR').format(num)
}

const formatCurrency = (num) => {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(num)
}

const getLimitBarClass = (percentage) => {
  if (percentage > 80) return 'danger'
  if (percentage > 60) return 'warning'
  return ''
}

// Gerar atividade recente impressionante
const generateRecentActivity = () => {
  return [
    {
      id: 1,
      type: 'conversion',
      title: 'Nova venda: R$ 1.250 - Cliente Premium',
      time: '2 min atrás',
      icon: 'fas fa-shopping-cart',
      value: '+R$ 1.250'
    },
    {
      id: 2,
      type: 'campaign',
      title: 'Campanha TikTok atingiu 100k visualizações',
      time: '15 min atrás',
      icon: 'fas fa-rocket',
      value: '+89.600 views'
    },
    {
      id: 3,
      type: 'alert',
      title: 'Crescimento do Facebook subiu para 1,403%',
      time: '1 hora atrás',
      icon: 'fas fa-chart-line',
      value: '+340% Crescimento'
    },
    {
      id: 4,
      type: 'new_lead',
      title: '15 novos leads qualificados hoje',
      time: '2 horas atrás',
      icon: 'fas fa-user-plus',
      value: '+15 leads'
    },
    {
      id: 5,
      type: 'revenue',
      title: 'Meta mensal atingida: R$ 187.050',
      time: '3 horas atrás',
      icon: 'fas fa-trophy',
      value: '+R$ 87.050'
    }
  ]
}

// Obter limites do plano
const getPlanLimits = () => {
  const plan = currentUser.value?.plan || 'Professional'
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
  }
  
  return limits[plan] || limits['Professional']
}

// Inicializar gráficos
const initCharts = async () => {
  await nextTick()
  initTrafficChart()
  initSourcesChart()
}

const initTrafficChart = () => {
  const ctx = document.getElementById('dashboardTrafficChart')
  if (!ctx) return
  
  // Gerar dados dos últimos 7 dias - CRESCIMENTO IMPRESSIONANTE
  const labels = []
  const visitorsData = []
  const pageViewsData = []
  const revenueData = []
  
  // Base de crescimento diário de 8%
  const baseVisitors = 15000
  const basePageViews = 45000
  const baseRevenue = 25000
  
  for (let i = 6; i >= 0; i--) {
    const date = new Date()
    date.setDate(date.getDate() - i)
    labels.push(date.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit' }))
    
    const growthFactor = 1 + (6-i) * 0.08 // Crescimento de 8% por dia
    const randomFactor = 0.9 + Math.random() * 0.2 // Variação de ±10%
    
    visitorsData.push(Math.floor(baseVisitors * growthFactor * randomFactor))
    pageViewsData.push(Math.floor(basePageViews * growthFactor * randomFactor))
    revenueData.push(Math.floor(baseRevenue * growthFactor * randomFactor))
  }
  
  if (window.Chart) {
    charts.value.traffic = new Chart(ctx, {
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
    })
  }
}

const initSourcesChart = () => {
  const ctx = document.getElementById('dashboardSourcesChart')
  if (!ctx) return
  
  const sources = Object.keys(userData.value.trafficSources)
  const data = sources.map(source => userData.value.trafficSources[source])
  
  if (window.Chart) {
    charts.value.sources = new Chart(ctx, {
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
    })
  }
}

// Funções de ação - usando as funções do dashboard
const refreshDashboard = () => {
  refreshData()
  // Simular atualização de dados
  setTimeout(() => {
    userData.value.recentActivity = generateRecentActivity()
  }, 1000)
}

const handleRefreshActivity = () => {
  refreshActivity()
  userData.value.recentActivity = generateRecentActivity()
}

const handleUpdateDateRange = (range) => {
  selectedDateRange.value = range
  updateDateRange(range)
  
  // Simular atualização de dados
  setTimeout(() => {
    refreshDashboard()
  }, 1000)
}

const handleExportData = () => {
  exportData()
}

const handleCreateCampaign = () => {
  createCampaign()
}

const handleGenerateReport = () => {
  generateReport()
}

const handleShowSettings = () => {
  showSettings()
}

// Carregar dados do dashboard
const loadDashboard = async () => {
  try {
    // Carregar dados do usuário
    userData.value.recentActivity = generateRecentActivity()
    userData.value.planLimits = getPlanLimits()
    
    // Tentar carregar dados da API se disponível
    if (apiMethods && apiMethods.getDashboardStats) {
      const response = await apiMethods.getDashboardStats()
      if (response && response.data) {
        // Mesclar dados da API com dados demo
        Object.assign(userData.value, response.data)
      }
    }
  } catch (error) {
    console.error('Erro ao carregar dashboard:', error)
    // Continuar com dados demo em caso de erro
  }
}

onMounted(async () => {
  await loadDashboard()
  
  // Inicializar dashboard completo
  initializeDashboard()
  
  // Aguardar Chart.js carregar
  if (typeof Chart !== 'undefined') {
    await initCharts()
  } else {
    // Aguardar Chart.js carregar
    const checkChart = setInterval(() => {
      if (typeof Chart !== 'undefined') {
        clearInterval(checkChart)
        initCharts()
      }
    }, 100)
  }
  
  // Atualizar dashboard a cada 30 segundos
  setInterval(() => {
    refreshDashboard()
  }, 30000)
})
</script>

<style scoped>
/* Dashboard Container */
.dashboard-container {
  padding: 2rem;
  background: #f8fafc;
  min-height: 100vh;
}

/* Header do Dashboard */
.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  padding: 1.5rem;
  background: white;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.header-left h1 {
  font-size: 2rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
}

.dashboard-subtitle {
  color: #64748b;
  margin: 0.5rem 0 0 0;
  font-size: 1rem;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.date-range-selector {
  display: flex;
  gap: 0.5rem;
}

.date-btn {
  padding: 0.5rem 1rem;
  border: 1px solid #e2e8f0;
  background: white;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s;
  font-size: 0.875rem;
  font-weight: 500;
}

.date-btn:hover {
  background: #f1f5f9;
}

.date-btn.active {
  background: #3b82f6;
  color: white;
  border-color: #3b82f6;
}

/* Botões */
.btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  text-decoration: none;
}

.btn-primary {
  background: #3b82f6;
  color: white;
}

.btn-primary:hover {
  background: #2563eb;
}

.btn-secondary {
  background: #f1f5f9;
  color: #475569;
  border: 1px solid #e2e8f0;
}

.btn-secondary:hover {
  background: #e2e8f0;
}

.btn-small {
  padding: 0.5rem 1rem;
  font-size: 0.875rem;
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.stat-card {
  background: white;
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  display: flex;
  align-items: center;
  gap: 1rem;
  transition: transform 0.2s, box-shadow 0.2s;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.stat-icon {
  width: 3rem;
  height: 3rem;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
  color: white;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.stat-content {
  flex: 1;
}

.stat-content h3 {
  font-size: 0.875rem;
  color: #64748b;
  margin: 0 0 0.5rem 0;
  font-weight: 500;
}

.stat-value {
  font-size: 2rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
}

.stat-change {
  font-size: 0.875rem;
  font-weight: 500;
  margin-top: 0.25rem;
}

.stat-change.positive {
  color: #10b981;
}

.stat-change.negative {
  color: #ef4444;
}

/* Charts Grid */
.charts-grid {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 2rem;
  margin-bottom: 2rem;
}

.chart-card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.chart-header {
  padding: 1.5rem;
  border-bottom: 1px solid #e2e8f0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.chart-header h3 {
  font-size: 1.125rem;
  font-weight: 600;
  color: #1e293b;
  margin: 0;
}

.chart-actions {
  display: flex;
  gap: 0.5rem;
}

.chart-content {
  padding: 1.5rem;
  height: 300px;
}

/* Bottom Grid */
.bottom-grid {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 2rem;
  margin-bottom: 2rem;
}

.activity-card,
.limits-card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.card-header {
  padding: 1.5rem;
  border-bottom: 1px solid #e2e8f0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.card-header h3 {
  font-size: 1.125rem;
  font-weight: 600;
  color: #1e293b;
  margin: 0;
}

.plan-badge {
  background: #10b981;
  color: white;
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
}

/* Activity List */
.activity-list {
  padding: 1.5rem;
  max-height: 400px;
  overflow-y: auto;
}

.activity-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem 0;
  border-bottom: 1px solid #f1f5f9;
}

.activity-item:last-child {
  border-bottom: none;
}

.activity-icon {
  width: 2.5rem;
  height: 2.5rem;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
  color: white;
}

.activity-icon.conversion {
  background: #10b981;
}

.activity-icon.campaign {
  background: #3b82f6;
}

.activity-icon.alert {
  background: #f59e0b;
}

.activity-icon.new_lead {
  background: #8b5cf6;
}

.activity-icon.revenue {
  background: #ef4444;
}

.activity-content {
  flex: 1;
}

.activity-title {
  font-weight: 500;
  color: #1e293b;
  margin-bottom: 0.25rem;
}

.activity-time {
  font-size: 0.875rem;
  color: #64748b;
}

.activity-value {
  font-weight: 600;
  font-size: 0.875rem;
}

.activity-value.conversion {
  color: #10b981;
}

.activity-value.campaign {
  color: #3b82f6;
}

.activity-value.alert {
  color: #f59e0b;
}

.activity-value.new_lead {
  color: #8b5cf6;
}

.activity-value.revenue {
  color: #ef4444;
}

/* Limits List */
.limits-list {
  padding: 1.5rem;
}

.limit-item {
  margin-bottom: 1.5rem;
}

.limit-item:last-child {
  margin-bottom: 0;
}

.limit-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.5rem;
}

.limit-name {
  font-weight: 500;
  color: #1e293b;
}

.limit-usage {
  font-size: 0.875rem;
  color: #64748b;
}

.limit-bar {
  height: 8px;
  background: #f1f5f9;
  border-radius: 4px;
  overflow: hidden;
}

.limit-fill {
  height: 100%;
  background: #10b981;
  border-radius: 4px;
  transition: width 0.3s ease;
}

.limit-fill.warning {
  background: #f59e0b;
}

.limit-fill.danger {
  background: #ef4444;
}

/* Quick Actions */
.quick-actions {
  margin-top: 2rem;
}

.quick-actions h3 {
  font-size: 1.25rem;
  font-weight: 600;
  color: #1e293b;
  margin-bottom: 1rem;
}

.actions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}

.action-card {
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 1.5rem;
  text-align: center;
  cursor: pointer;
  transition: all 0.2s;
  text-decoration: none;
  color: inherit;
}

.action-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  border-color: #3b82f6;
}

.action-icon {
  width: 3rem;
  height: 3rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1rem;
  font-size: 1.25rem;
  color: white;
}

.action-card h4 {
  font-size: 1rem;
  font-weight: 600;
  color: #1e293b;
  margin: 0 0 0.5rem 0;
}

.action-card p {
  font-size: 0.875rem;
  color: #64748b;
  margin: 0;
}

/* Responsive */
@media (max-width: 1024px) {
  .charts-grid {
    grid-template-columns: 1fr;
  }
  
  .bottom-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .dashboard-container {
    padding: 1rem;
  }
  
  .dashboard-header {
    flex-direction: column;
    gap: 1rem;
    align-items: flex-start;
  }
  
  .header-right {
    width: 100%;
    justify-content: space-between;
  }
  
  .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .actions-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 480px) {
  .actions-grid {
    grid-template-columns: 1fr;
  }
  
  .date-range-selector {
    flex-wrap: wrap;
  }
}
</style>
