<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-6">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">💬 Conversas</h1>
            <p class="text-sm text-gray-600">Gerencie todas as conversas do WhatsApp</p>
          </div>
          <div class="flex items-center space-x-4">
            <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
              📊 Relatório
            </button>
            <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
              ➕ Nova Conversa
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
            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select v-model="filters.status" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
              <option value="">Todos</option>
              <option value="open">Abertas</option>
              <option value="closed">Fechadas</option>
              <option value="qualified">Qualificadas</option>
              <option value="lost">Perdidas</option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Origem</label>
            <select v-model="filters.origin" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
              <option value="">Todas</option>
              <option value="meta">Meta Ads</option>
              <option value="google">Google Ads</option>
              <option value="outras">Outras</option>
              <option value="nao_rastreada">Não Rastreada</option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Período</label>
            <select v-model="filters.period" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
              <option value="today">Hoje</option>
              <option value="week">Esta Semana</option>
              <option value="month">Este Mês</option>
              <option value="all">Todos</option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
            <input 
              v-model="filters.search" 
              type="text" 
              placeholder="Nome ou telefone..."
              class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                <span class="text-blue-600 text-lg">💬</span>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Total de Conversas</p>
              <p class="text-2xl font-bold text-gray-900">{{ stats.total }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                <span class="text-green-600 text-lg">✅</span>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Abertas</p>
              <p class="text-2xl font-bold text-gray-900">{{ stats.open }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                <span class="text-yellow-600 text-lg">⭐</span>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Qualificadas</p>
              <p class="text-2xl font-bold text-gray-900">{{ stats.qualified }}</p>
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
              <p class="text-sm font-medium text-gray-600">Convertidas</p>
              <p class="text-2xl font-bold text-gray-900">{{ stats.converted }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Lista de Conversas -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-medium text-gray-900">Lista de Conversas</h3>
        </div>
        
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Cliente
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Origem
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Status
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Última Mensagem
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Responsável
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Ações
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="conversation in filteredConversations" :key="conversation.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10">
                      <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                        <span class="text-sm font-medium text-gray-700">{{ getInitials(conversation.client_name) }}</span>
                      </div>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">{{ conversation.client_name }}</div>
                      <div class="text-sm text-gray-500">{{ conversation.phone }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getOriginClass(conversation.origin)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                    {{ getOriginLabel(conversation.origin) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getStatusClass(conversation.status)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                    {{ getStatusLabel(conversation.status) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatDate(conversation.last_message_at) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ conversation.assigned_to || 'Não atribuído' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <div class="flex space-x-2">
                    <button @click="viewConversation(conversation)" class="text-blue-600 hover:text-blue-900">
                      👁️ Ver
                    </button>
                    <button @click="editConversation(conversation)" class="text-indigo-600 hover:text-indigo-900">
                      ✏️ Editar
                    </button>
                    <button @click="markAsConverted(conversation)" class="text-green-600 hover:text-green-900">
                      💰 Converter
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <!-- Empty State -->
        <div v-if="filteredConversations.length === 0" class="text-center py-12">
          <div class="text-gray-400 text-6xl mb-4">💬</div>
          <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhuma conversa encontrada</h3>
          <p class="text-gray-500">{{ loading ? 'Carregando conversas...' : 'Não há conversas com os filtros selecionados.' }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

// Estados
const loading = ref(true)
const conversations = ref([])

// Filtros
const filters = ref({
  status: '',
  origin: '',
  period: 'month',
  search: ''
})

// Stats
const stats = ref({
  total: 0,
  open: 0,
  qualified: 0,
  converted: 0
})

// Dados de exemplo (simulados)
const mockConversations = [
  {
    id: 1,
    client_name: 'João Silva',
    phone: '+55 11 99999-1234',
    origin: 'meta',
    status: 'open',
    last_message_at: '2024-01-15T10:30:00Z',
    assigned_to: 'Maria Santos'
  },
  {
    id: 2,
    client_name: 'Ana Costa',
    phone: '+55 11 99999-5678',
    origin: 'google',
    status: 'qualified',
    last_message_at: '2024-01-15T09:15:00Z',
    assigned_to: 'Pedro Lima'
  },
  {
    id: 3,
    client_name: 'Carlos Oliveira',
    phone: '+55 11 99999-9012',
    origin: 'nao_rastreada',
    status: 'closed',
    last_message_at: '2024-01-14T16:45:00Z',
    assigned_to: null
  }
]

// Conversas filtradas
const filteredConversations = computed(() => {
  let filtered = conversations.value

  if (filters.value.status) {
    filtered = filtered.filter(conv => conv.status === filters.value.status)
  }

  if (filters.value.origin) {
    filtered = filtered.filter(conv => conv.origin === filters.value.origin)
  }

  if (filters.value.search) {
    const search = filters.value.search.toLowerCase()
    filtered = filtered.filter(conv => 
      conv.client_name.toLowerCase().includes(search) ||
      conv.phone.includes(search)
    )
  }

  return filtered
})

// Métodos
const loadConversations = async () => {
  loading.value = true
  
  // Simular chamada da API
  setTimeout(() => {
    conversations.value = mockConversations
    stats.value = {
      total: mockConversations.length,
      open: mockConversations.filter(c => c.status === 'open').length,
      qualified: mockConversations.filter(c => c.status === 'qualified').length,
      converted: mockConversations.filter(c => c.status === 'closed').length
    }
    loading.value = false
  }, 1000)
}

const getInitials = (name) => {
  return name.split(' ').map(n => n[0]).join('').toUpperCase()
}

const getOriginClass = (origin) => {
  const classes = {
    meta: 'bg-blue-100 text-blue-800',
    google: 'bg-green-100 text-green-800',
    outras: 'bg-yellow-100 text-yellow-800',
    nao_rastreada: 'bg-gray-100 text-gray-800'
  }
  return classes[origin] || classes.nao_rastreada
}

const getOriginLabel = (origin) => {
  const labels = {
    meta: '📘 Meta Ads',
    google: '🟢 Google Ads',
    outras: '🔗 Outras',
    nao_rastreada: '❓ Não Rastreada'
  }
  return labels[origin] || labels.nao_rastreada
}

const getStatusClass = (status) => {
  const classes = {
    open: 'bg-green-100 text-green-800',
    qualified: 'bg-yellow-100 text-yellow-800',
    closed: 'bg-purple-100 text-purple-800',
    lost: 'bg-red-100 text-red-800'
  }
  return classes[status] || classes.open
}

const getStatusLabel = (status) => {
  const labels = {
    open: '🟢 Aberta',
    qualified: '⭐ Qualificada',
    closed: '💰 Convertida',
    lost: '❌ Perdida'
  }
  return labels[status] || labels.open
}

const formatDate = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('pt-BR') + ' ' + date.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })
}

const viewConversation = (conversation) => {
  alert(`Ver conversa de ${conversation.client_name}`)
}

const editConversation = (conversation) => {
  alert(`Editar conversa de ${conversation.client_name}`)
}

const markAsConverted = (conversation) => {
  alert(`Marcar conversa de ${conversation.client_name} como convertida`)
}

// Lifecycle
onMounted(() => {
  loadConversations()
})
</script>
