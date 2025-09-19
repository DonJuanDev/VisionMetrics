<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-6">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">👑 Admin Dashboard</h1>
            <p class="text-sm text-gray-600">Painel administrativo do VisionMetrics</p>
          </div>
          <div class="flex items-center space-x-4">
            <span class="text-sm text-gray-500">Super Admin</span>
            <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
              <span class="text-red-600 text-sm">👑</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
      <!-- Cards de Resumo -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                <span class="text-blue-600 text-lg">🏢</span>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Total de Empresas</p>
              <p class="text-2xl font-bold text-gray-900">{{ stats.totalCompanies }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                <span class="text-green-600 text-lg">👥</span>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Total de Usuários</p>
              <p class="text-2xl font-bold text-gray-900">{{ stats.totalUsers }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                <span class="text-yellow-600 text-lg">⏰</span>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Trials Expirados</p>
              <p class="text-2xl font-bold text-gray-900">{{ stats.expiredTrials }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                <span class="text-purple-600 text-lg">💰</span>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Receita Total</p>
              <p class="text-2xl font-bold text-gray-900">R$ {{ stats.totalRevenue.toLocaleString() }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Gráficos -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Empresas por Mês -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Empresas Cadastradas por Mês</h3>
          <div class="h-64 flex items-center justify-center bg-gray-50 rounded-lg">
            <div class="text-center">
              <div class="text-4xl mb-2">📊</div>
              <p class="text-gray-500">Gráfico de crescimento</p>
              <p class="text-sm text-gray-400">Chart.js será implementado aqui</p>
            </div>
          </div>
        </div>

        <!-- Status dos Trials -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Status dos Trials</h3>
          <div class="h-64 flex items-center justify-center bg-gray-50 rounded-lg">
            <div class="text-center">
              <div class="text-4xl mb-2">🥧</div>
              <p class="text-gray-500">Gráfico de pizza</p>
              <p class="text-sm text-gray-400">Chart.js será implementado aqui</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Empresas Recentes -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
          <div class="flex justify-between items-center">
            <h3 class="text-lg font-medium text-gray-900">Empresas Recentes</h3>
            <button @click="viewAllCompanies" class="text-blue-600 hover:text-blue-900 text-sm font-medium">
              Ver Todas →
            </button>
          </div>
        </div>
        
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Empresa</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status Trial</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuários</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cadastro</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="company in recentCompanies" :key="company.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10">
                      <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                        <span class="text-sm font-medium text-gray-700">{{ getInitials(company.name) }}</span>
                      </div>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">{{ company.name }}</div>
                      <div class="text-sm text-gray-500">{{ company.email }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getTrialStatusClass(company.trial_status)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                    {{ getTrialStatusLabel(company.trial_status) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ company.user_count }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatDate(company.created_at) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <div class="flex space-x-2">
                    <button @click="viewCompany(company)" class="text-blue-600 hover:text-blue-900">
                      👁️ Ver
                    </button>
                    <button @click="editCompany(company)" class="text-indigo-600 hover:text-indigo-900">
                      ✏️ Editar
                    </button>
                  </div>
                </td>
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
const stats = ref({
  totalCompanies: 0,
  totalUsers: 0,
  expiredTrials: 0,
  totalRevenue: 0
})

const recentCompanies = ref([])

// Dados simulados
const mockStats = {
  totalCompanies: 156,
  totalUsers: 423,
  expiredTrials: 23,
  totalRevenue: 125000
}

const mockCompanies = [
  {
    id: 1,
    name: 'Tech Solutions',
    email: 'contato@techsolutions.com',
    trial_status: 'active',
    user_count: 5,
    created_at: '2024-01-15T10:30:00Z'
  },
  {
    id: 2,
    name: 'Digital Marketing',
    email: 'admin@digitalmarketing.com',
    trial_status: 'expired',
    user_count: 3,
    created_at: '2024-01-14T14:15:00Z'
  },
  {
    id: 3,
    name: 'E-commerce Store',
    email: 'suporte@ecommerce.com',
    trial_status: 'active',
    user_count: 8,
    created_at: '2024-01-13T09:45:00Z'
  }
]

// Métodos
const loadData = async () => {
  stats.value = mockStats
  recentCompanies.value = mockCompanies
}

const getInitials = (name) => {
  return name.split(' ').map(n => n[0]).join('').toUpperCase()
}

const getTrialStatusClass = (status) => {
  const classes = {
    active: 'bg-green-100 text-green-800',
    expired: 'bg-red-100 text-red-800',
    cancelled: 'bg-gray-100 text-gray-800'
  }
  return classes[status] || classes.active
}

const getTrialStatusLabel = (status) => {
  const labels = {
    active: '✅ Ativo',
    expired: '❌ Expirado',
    cancelled: '⏹️ Cancelado'
  }
  return labels[status] || labels.active
}

const formatDate = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('pt-BR')
}

const viewAllCompanies = () => {
  // Navegar para página de empresas
  alert('Navegando para página de empresas...')
}

const viewCompany = (company) => {
  alert(`Visualizando empresa: ${company.name}`)
}

const editCompany = (company) => {
  alert(`Editando empresa: ${company.name}`)
}

// Lifecycle
onMounted(() => {
  loadData()
})
</script>

