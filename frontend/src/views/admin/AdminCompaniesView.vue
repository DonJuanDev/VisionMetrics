<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-6">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">🏢 Empresas</h1>
            <p class="text-sm text-gray-600">Gerencie todas as empresas cadastradas</p>
          </div>
          <div class="flex items-center space-x-4">
            <button @click="exportCompanies" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
              📥 Exportar
            </button>
            <button @click="showCreateModal = true" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
              ➕ Nova Empresa
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
      <!-- Filtros -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Status Trial</label>
            <select v-model="filters.trial_status" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
              <option value="">Todos</option>
              <option value="active">Ativo</option>
              <option value="expired">Expirado</option>
              <option value="cancelled">Cancelado</option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Período</label>
            <select v-model="filters.period" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
              <option value="">Todos</option>
              <option value="today">Hoje</option>
              <option value="week">Esta Semana</option>
              <option value="month">Este Mês</option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
            <input v-model="filters.search" type="text" placeholder="Nome ou email..." 
                   class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Ordenar</label>
            <select v-model="filters.sort" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
              <option value="created_at">Data de Cadastro</option>
              <option value="name">Nome</option>
              <option value="trial_expires_at">Expiração do Trial</option>
            </select>
          </div>
          
          <div class="flex items-end">
            <button @click="clearFilters" class="w-full bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-md text-sm font-medium transition-colors">
              🗑️ Limpar
            </button>
          </div>
        </div>
      </div>

      <!-- Lista de Empresas -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-medium text-gray-900">Lista de Empresas ({{ filteredCompanies.length }})</h3>
        </div>
        
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Empresa</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trial</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuários</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cadastro</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="company in filteredCompanies" :key="company.id" class="hover:bg-gray-50">
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
                      <div class="text-xs text-gray-400">{{ company.cnpj || 'Sem CNPJ' }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">
                    <span :class="getTrialStatusClass(company.trial_status)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full mb-1">
                      {{ getTrialStatusLabel(company.trial_status) }}
                    </span>
                    <div class="text-xs text-gray-500">
                      {{ formatTrialDate(company.trial_expires_at) }}
                    </div>
                  </div>
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
                    <button @click="extendTrial(company)" class="text-green-600 hover:text-green-900">
                      ⏰ Estender
                    </button>
                    <button @click="deleteCompany(company)" class="text-red-600 hover:text-red-900">
                      🗑️
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <!-- Empty State -->
        <div v-if="filteredCompanies.length === 0" class="text-center py-12">
          <div class="text-gray-400 text-6xl mb-4">🏢</div>
          <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhuma empresa encontrada</h3>
          <p class="text-gray-500 mb-4">Não há empresas com os filtros selecionados</p>
          <button @click="clearFilters" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
            🔄 Limpar Filtros
          </button>
        </div>
      </div>
    </div>

    <!-- Modal de Criar/Editar Empresa -->
    <div v-if="showCreateModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-2xl max-h-screen overflow-y-auto">
        <h3 class="text-lg font-medium text-gray-900 mb-6">🏢 {{ editingCompany ? 'Editar Empresa' : 'Nova Empresa' }}</h3>
        
        <div class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Nome da Empresa *</label>
              <input v-model="companyForm.name" type="text" placeholder="Tech Solutions" 
                     class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
              <input v-model="companyForm.email" type="email" placeholder="contato@techsolutions.com" 
                     class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Telefone</label>
              <input v-model="companyForm.phone" type="tel" placeholder="+55 11 99999-9999" 
                     class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">CNPJ</label>
              <input v-model="companyForm.cnpj" type="text" placeholder="12.345.678/0001-90" 
                     class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Fuso Horário</label>
            <select v-model="companyForm.timezone" 
                    class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
              <option value="America/Sao_Paulo">São Paulo (UTC-3)</option>
              <option value="America/New_York">Nova York (UTC-5)</option>
              <option value="Europe/London">Londres (UTC+0)</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Data de Expiração do Trial</label>
            <input v-model="companyForm.trial_expires_at" type="datetime-local" 
                   class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>
        </div>
        
        <div class="flex justify-end space-x-3 mt-8">
          <button @click="closeModal" 
                  class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
            Cancelar
          </button>
          <button @click="saveCompany" 
                  class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700">
            {{ editingCompany ? 'Atualizar' : 'Criar Empresa' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

// Estados
const companies = ref([])
const showCreateModal = ref(false)
const editingCompany = ref(null)

const filters = ref({
  trial_status: '',
  period: '',
  search: '',
  sort: 'created_at'
})

const companyForm = ref({
  name: '',
  email: '',
  phone: '',
  cnpj: '',
  timezone: 'America/Sao_Paulo',
  trial_expires_at: ''
})

// Dados simulados
const mockCompanies = [
  {
    id: 1,
    name: 'Tech Solutions',
    email: 'contato@techsolutions.com',
    phone: '+55 11 99999-1234',
    cnpj: '12.345.678/0001-90',
    timezone: 'America/Sao_Paulo',
    trial_status: 'active',
    trial_expires_at: '2024-01-22T23:59:59Z',
    user_count: 5,
    created_at: '2024-01-15T10:30:00Z'
  },
  {
    id: 2,
    name: 'Digital Marketing',
    email: 'admin@digitalmarketing.com',
    phone: '+55 11 99999-5678',
    cnpj: '98.765.432/0001-10',
    timezone: 'America/Sao_Paulo',
    trial_status: 'expired',
    trial_expires_at: '2024-01-10T23:59:59Z',
    user_count: 3,
    created_at: '2024-01-14T14:15:00Z'
  },
  {
    id: 3,
    name: 'E-commerce Store',
    email: 'suporte@ecommerce.com',
    phone: '+55 11 99999-9012',
    cnpj: null,
    timezone: 'America/Sao_Paulo',
    trial_status: 'active',
    trial_expires_at: '2024-01-25T23:59:59Z',
    user_count: 8,
    created_at: '2024-01-13T09:45:00Z'
  }
]

// Empresas filtradas
const filteredCompanies = computed(() => {
  let filtered = companies.value

  if (filters.value.trial_status) {
    filtered = filtered.filter(company => company.trial_status === filters.value.trial_status)
  }

  if (filters.value.search) {
    const search = filters.value.search.toLowerCase()
    filtered = filtered.filter(company => 
      company.name.toLowerCase().includes(search) ||
      company.email.toLowerCase().includes(search)
    )
  }

  // Ordenação
  filtered.sort((a, b) => {
    switch (filters.value.sort) {
      case 'name':
        return a.name.localeCompare(b.name)
      case 'trial_expires_at':
        return new Date(a.trial_expires_at) - new Date(b.trial_expires_at)
      default:
        return new Date(b.created_at) - new Date(a.created_at)
    }
  })

  return filtered
})

// Métodos
const loadCompanies = async () => {
  companies.value = mockCompanies
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

const formatTrialDate = (dateString) => {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return date.toLocaleDateString('pt-BR')
}

const viewCompany = (company) => {
  alert(`Visualizando empresa: ${company.name}`)
}

const editCompany = (company) => {
  editingCompany.value = company
  companyForm.value = {
    name: company.name,
    email: company.email,
    phone: company.phone,
    cnpj: company.cnpj || '',
    timezone: company.timezone,
    trial_expires_at: company.trial_expires_at ? new Date(company.trial_expires_at).toISOString().slice(0, 16) : ''
  }
  showCreateModal.value = true
}

const extendTrial = (company) => {
  const newDate = new Date()
  newDate.setDate(newDate.getDate() + 7) // Adicionar 7 dias
  
  company.trial_expires_at = newDate.toISOString()
  company.trial_status = 'active'
  
  alert(`Trial da empresa ${company.name} estendido por 7 dias!`)
}

const deleteCompany = (company) => {
  if (confirm(`Tem certeza que deseja deletar a empresa "${company.name}"? Esta ação não pode ser desfeita.`)) {
    const index = companies.value.findIndex(c => c.id === company.id)
    if (index > -1) {
      companies.value.splice(index, 1)
      alert('Empresa deletada com sucesso!')
    }
  }
}

const saveCompany = () => {
  if (!companyForm.value.name || !companyForm.value.email) {
    alert('Preencha os campos obrigatórios')
    return
  }

  if (editingCompany.value) {
    // Atualizar empresa existente
    const index = companies.value.findIndex(c => c.id === editingCompany.value.id)
    if (index > -1) {
      companies.value[index] = {
        ...companies.value[index],
        name: companyForm.value.name,
        email: companyForm.value.email,
        phone: companyForm.value.phone,
        cnpj: companyForm.value.cnpj,
        timezone: companyForm.value.timezone,
        trial_expires_at: companyForm.value.trial_expires_at ? new Date(companyForm.value.trial_expires_at).toISOString() : null
      }
    }
    alert('Empresa atualizada com sucesso!')
  } else {
    // Criar nova empresa
    const newCompany = {
      id: companies.value.length + 1,
      name: companyForm.value.name,
      email: companyForm.value.email,
      phone: companyForm.value.phone,
      cnpj: companyForm.value.cnpj,
      timezone: companyForm.value.timezone,
      trial_status: 'active',
      trial_expires_at: companyForm.value.trial_expires_at ? new Date(companyForm.value.trial_expires_at).toISOString() : null,
      user_count: 1,
      created_at: new Date().toISOString()
    }
    
    companies.value.unshift(newCompany)
    alert('Empresa criada com sucesso!')
  }
  
  closeModal()
}

const exportCompanies = () => {
  alert('Exportando lista de empresas...')
}

const clearFilters = () => {
  filters.value = {
    trial_status: '',
    period: '',
    search: '',
    sort: 'created_at'
  }
}

const closeModal = () => {
  showCreateModal.value = false
  editingCompany.value = null
  companyForm.value = {
    name: '',
    email: '',
    phone: '',
    cnpj: '',
    timezone: 'America/Sao_Paulo',
    trial_expires_at: ''
  }
}

// Lifecycle
onMounted(() => {
  loadCompanies()
})
</script>

