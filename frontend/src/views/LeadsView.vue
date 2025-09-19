<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-6">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">👥 Leads</h1>
            <p class="text-sm text-gray-600">Gerencie todos os leads e potenciais clientes</p>
          </div>
          <div class="flex items-center space-x-4">
            <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
              📊 Relatório
            </button>
            <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
              ➕ Novo Lead
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
            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select v-model="filters.status" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
              <option value="">Todos</option>
              <option value="novo">Novo</option>
              <option value="contatado">Contatado</option>
              <option value="qualificado">Qualificado</option>
              <option value="convertido">Convertido</option>
              <option value="perdido">Perdido</option>
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
              placeholder="Nome, telefone ou email..."
              class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                <span class="text-blue-600 text-lg">👥</span>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Total de Leads</p>
              <p class="text-2xl font-bold text-gray-900">{{ stats.total }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                <span class="text-yellow-600 text-lg">🆕</span>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Novos</p>
              <p class="text-2xl font-bold text-gray-900">{{ stats.new }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                <span class="text-orange-600 text-lg">📞</span>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Contatados</p>
              <p class="text-2xl font-bold text-gray-900">{{ stats.contacted }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                <span class="text-green-600 text-lg">⭐</span>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Qualificados</p>
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
              <p class="text-sm font-medium text-gray-600">Convertidos</p>
              <p class="text-2xl font-bold text-gray-900">{{ stats.converted }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Lista de Leads -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-medium text-gray-900">Lista de Leads</h3>
        </div>
        
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Lead
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Contato
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Origem
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Status
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Primeiro Contato
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
              <tr v-for="lead in filteredLeads" :key="lead.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10">
                      <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                        <span class="text-sm font-medium text-gray-700">{{ getInitials(lead.name) }}</span>
                      </div>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">{{ lead.name }}</div>
                      <div class="text-sm text-gray-500">{{ lead.email }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ lead.phone }}</div>
                  <div class="text-sm text-gray-500">{{ lead.whatsapp ? '✅ WhatsApp' : '❌ Sem WhatsApp' }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getOriginClass(lead.origin)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                    {{ getOriginLabel(lead.origin) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getStatusClass(lead.status)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                    {{ getStatusLabel(lead.status) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatDate(lead.first_contact_at) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ lead.assigned_to || 'Não atribuído' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <div class="flex space-x-2">
                    <button @click="viewLead(lead)" class="text-blue-600 hover:text-blue-900">
                      👁️ Ver
                    </button>
                    <button @click="editLead(lead)" class="text-indigo-600 hover:text-indigo-900">
                      ✏️ Editar
                    </button>
                    <button @click="startConversation(lead)" class="text-green-600 hover:text-green-900">
                      💬 Conversar
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <!-- Empty State -->
        <div v-if="filteredLeads.length === 0" class="text-center py-12">
          <div class="text-gray-400 text-6xl mb-4">👥</div>
          <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum lead encontrado</h3>
          <p class="text-gray-500">{{ loading ? 'Carregando leads...' : 'Não há leads com os filtros selecionados.' }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

// Estados
const loading = ref(true)
const leads = ref([])

// Filtros
const filters = ref({
  origin: '',
  status: '',
  period: 'month',
  search: ''
})

// Stats
const stats = ref({
  total: 0,
  new: 0,
  contacted: 0,
  qualified: 0,
  converted: 0
})

// Dados de exemplo (simulados)
const mockLeads = [
  {
    id: 1,
    name: 'João Silva',
    email: 'joao.silva@email.com',
    phone: '+55 11 99999-1234',
    whatsapp: true,
    origin: 'meta',
    status: 'novo',
    first_contact_at: '2024-01-15T10:30:00Z',
    assigned_to: 'Maria Santos'
  },
  {
    id: 2,
    name: 'Ana Costa',
    email: 'ana.costa@email.com',
    phone: '+55 11 99999-5678',
    whatsapp: true,
    origin: 'google',
    status: 'qualificado',
    first_contact_at: '2024-01-15T09:15:00Z',
    assigned_to: 'Pedro Lima'
  },
  {
    id: 3,
    name: 'Carlos Oliveira',
    email: 'carlos@email.com',
    phone: '+55 11 99999-9012',
    whatsapp: false,
    origin: 'nao_rastreada',
    status: 'contatado',
    first_contact_at: '2024-01-14T16:45:00Z',
    assigned_to: null
  },
  {
    id: 4,
    name: 'Mariana Santos',
    email: 'mariana@email.com',
    phone: '+55 11 99999-3456',
    whatsapp: true,
    origin: 'outras',
    status: 'convertido',
    first_contact_at: '2024-01-13T14:20:00Z',
    assigned_to: 'Ana Costa'
  },
  {
    id: 5,
    name: 'Roberto Lima',
    email: 'roberto@email.com',
    phone: '+55 11 99999-7890',
    whatsapp: true,
    origin: 'meta',
    status: 'perdido',
    first_contact_at: '2024-01-12T11:10:00Z',
    assigned_to: 'Maria Santos'
  }
]

// Leads filtrados
const filteredLeads = computed(() => {
  let filtered = leads.value

  if (filters.value.origin) {
    filtered = filtered.filter(lead => lead.origin === filters.value.origin)
  }

  if (filters.value.status) {
    filtered = filtered.filter(lead => lead.status === filters.value.status)
  }

  if (filters.value.search) {
    const search = filters.value.search.toLowerCase()
    filtered = filtered.filter(lead => 
      lead.name.toLowerCase().includes(search) ||
      lead.email.toLowerCase().includes(search) ||
      lead.phone.includes(search)
    )
  }

  return filtered
})

// Métodos
const loadLeads = async () => {
  loading.value = true
  
  // Simular chamada da API
  setTimeout(() => {
    leads.value = mockLeads
    stats.value = {
      total: mockLeads.length,
      new: mockLeads.filter(l => l.status === 'novo').length,
      contacted: mockLeads.filter(l => l.status === 'contatado').length,
      qualified: mockLeads.filter(l => l.status === 'qualificado').length,
      converted: mockLeads.filter(l => l.status === 'convertido').length
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
    novo: 'bg-yellow-100 text-yellow-800',
    contatado: 'bg-blue-100 text-blue-800',
    qualificado: 'bg-green-100 text-green-800',
    convertido: 'bg-purple-100 text-purple-800',
    perdido: 'bg-red-100 text-red-800'
  }
  return classes[status] || classes.novo
}

const getStatusLabel = (status) => {
  const labels = {
    novo: '🆕 Novo',
    contatado: '📞 Contatado',
    qualificado: '⭐ Qualificado',
    convertido: '💰 Convertido',
    perdido: '❌ Perdido'
  }
  return labels[status] || labels.novo
}

const formatDate = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('pt-BR') + ' ' + date.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })
}

const viewLead = (lead) => {
  alert(`Ver detalhes de ${lead.name}`)
}

const editLead = (lead) => {
  alert(`Editar ${lead.name}`)
}

const startConversation = (lead) => {
  if (lead.whatsapp) {
    alert(`Iniciar conversa no WhatsApp com ${lead.name}`)
  } else {
    alert(`${lead.name} não tem WhatsApp cadastrado`)
  }
}

// Lifecycle
onMounted(() => {
  loadLeads()
})
</script>
