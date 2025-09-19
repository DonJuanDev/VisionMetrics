<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-6">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">📊 Relatórios</h1>
            <p class="text-sm text-gray-600">Análise detalhada de performance e conversões</p>
          </div>
          <div class="flex items-center space-x-4">
            <button @click="exportReport" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
              📥 Exportar
            </button>
            <button @click="refreshData" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
              🔄 Atualizar
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Filtros -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Período</label>
            <select v-model="filters.period" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
              <option value="today">Hoje</option>
              <option value="week">Esta Semana</option>
              <option value="month">Este Mês</option>
              <option value="quarter">Este Trimestre</option>
              <option value="year">Este Ano</option>
              <option value="custom">Personalizado</option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Relatório</label>
            <select v-model="filters.reportType" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
              <option value="overview">Visão Geral</option>
              <option value="conversions">Conversões</option>
              <option value="leads">Leads</option>
              <option value="conversations">Conversas</option>
              <option value="attribution">Atribuição</option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Formato</label>
            <select v-model="filters.format" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
              <option value="pdf">PDF</option>
              <option value="excel">Excel</option>
              <option value="csv">CSV</option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Ação</label>
            <button @click="generateReport" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
              📊 Gerar Relatório
            </button>
          </div>
        </div>
      </div>

      <!-- Cards de Resumo -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                <span class="text-blue-600 text-lg">💬</span>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Total Conversas</p>
              <p class="text-2xl font-bold text-gray-900">{{ stats.totalConversations }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                <span class="text-green-600 text-lg">💰</span>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Receita Total</p>
              <p class="text-2xl font-bold text-gray-900">R$ {{ stats.totalRevenue.toLocaleString() }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                <span class="text-purple-600 text-lg">📈</span>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Taxa de Conversão</p>
              <p class="text-2xl font-bold text-gray-900">{{ stats.conversionRate }}%</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                <span class="text-yellow-600 text-lg">⏱️</span>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Tempo Médio</p>
              <p class="text-2xl font-bold text-gray-900">{{ stats.avgResponseTime }}min</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Gráficos -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Gráfico de Conversões por Dia -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Conversões por Dia</h3>
          <div class="h-64 flex items-center justify-center bg-gray-50 rounded-lg">
            <div class="text-center">
              <div class="text-4xl mb-2">📊</div>
              <p class="text-gray-500">Gráfico de conversões</p>
              <p class="text-sm text-gray-400">Chart.js será implementado aqui</p>
            </div>
          </div>
        </div>

        <!-- Gráfico de Origem -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Conversões por Origem</h3>
          <div class="h-64 flex items-center justify-center bg-gray-50 rounded-lg">
            <div class="text-center">
              <div class="text-4xl mb-2">🥧</div>
              <p class="text-gray-500">Gráfico de pizza</p>
              <p class="text-sm text-gray-400">Chart.js será implementado aqui</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Tabela de Dados -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-medium text-gray-900">Dados Detalhados</h3>
        </div>
        
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Conversas</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Conversões</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Receita</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Taxa</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="row in reportData" :key="row.date" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ row.date }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ row.conversations }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ row.conversions }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">R$ {{ row.revenue.toLocaleString() }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ row.rate }}%</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

// Estados
const loading = ref(false)
const filters = ref({
  period: 'month',
  reportType: 'overview',
  format: 'pdf'
})

const stats = ref({
  totalConversations: 0,
  totalRevenue: 0,
  conversionRate: 0,
  avgResponseTime: 0
})

const reportData = ref([])

// Dados simulados
const mockData = [
  { date: '2024-01-15', conversations: 45, conversions: 8, revenue: 12000, rate: 17.8 },
  { date: '2024-01-14', conversations: 38, conversions: 6, revenue: 9500, rate: 15.8 },
  { date: '2024-01-13', conversations: 52, conversions: 12, revenue: 18500, rate: 23.1 },
  { date: '2024-01-12', conversations: 41, conversions: 7, revenue: 10500, rate: 17.1 },
  { date: '2024-01-11', conversations: 29, conversions: 4, revenue: 6800, rate: 13.8 }
]

// Métodos
const loadData = async () => {
  loading.value = true
  
  setTimeout(() => {
    reportData.value = mockData
    stats.value = {
      totalConversations: mockData.reduce((sum, row) => sum + row.conversations, 0),
      totalRevenue: mockData.reduce((sum, row) => sum + row.revenue, 0),
      conversionRate: 18.5,
      avgResponseTime: 12
    }
    loading.value = false
  }, 1000)
}

const generateReport = () => {
  alert(`Gerando relatório ${filters.value.reportType} em formato ${filters.value.format} para o período ${filters.value.period}`)
}

const exportReport = () => {
  alert('Exportando relatório...')
}

const refreshData = () => {
  loadData()
}

// Lifecycle
onMounted(() => {
  loadData()
})
</script>

