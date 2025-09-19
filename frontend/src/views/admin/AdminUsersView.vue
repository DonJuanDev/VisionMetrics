<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-6">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">👥 Usuários</h1>
            <p class="text-sm text-gray-600">Gerencie todos os usuários do sistema</p>
          </div>
          <div class="flex items-center space-x-4">
            <button @click="exportUsers" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
              📥 Exportar
            </button>
            <button @click="showCreateModal = true" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
              ➕ Novo Usuário
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
            <label class="block text-sm font-medium text-gray-700 mb-2">Empresa</label>
            <select v-model="filters.company_id" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
              <option value="">Todas</option>
              <option v-for="company in companies" :key="company.id" :value="company.id">
                {{ company.name }}
              </option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Função</label>
            <select v-model="filters.role" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
              <option value="">Todas</option>
              <option value="super_admin">Super Admin</option>
              <option value="company_admin">Company Admin</option>
              <option value="company_agent">Company Agent</option>
              <option value="company_viewer">Company Viewer</option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select v-model="filters.status" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
              <option value="">Todos</option>
              <option value="active">Ativo</option>
              <option value="inactive">Inativo</option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
            <input v-model="filters.search" type="text" placeholder="Nome ou email..." 
                   class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>
          
          <div class="flex items-end">
            <button @click="clearFilters" class="w-full bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-md text-sm font-medium transition-colors">
              🗑️ Limpar
            </button>
          </div>
        </div>
      </div>

      <!-- Lista de Usuários -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-medium text-gray-900">Lista de Usuários ({{ filteredUsers.length }})</h3>
        </div>
        
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuário</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Empresa</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Função</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Último Acesso</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="user in filteredUsers" :key="user.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10">
                      <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                        <span class="text-sm font-medium text-gray-700">{{ getInitials(user.name) }}</span>
                      </div>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                      <div class="text-sm text-gray-500">{{ user.email }}</div>
                      <div class="text-xs text-gray-400">{{ user.phone || 'Sem telefone' }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ user.company_name }}</div>
                  <div class="text-xs text-gray-500">{{ user.company_email }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getRoleClass(user.role)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                    {{ getRoleLabel(user.role) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getStatusClass(user.status)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                    {{ getStatusLabel(user.status) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatDate(user.last_login) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <div class="flex space-x-2">
                    <button @click="viewUser(user)" class="text-blue-600 hover:text-blue-900">
                      👁️ Ver
                    </button>
                    <button @click="editUser(user)" class="text-indigo-600 hover:text-indigo-900">
                      ✏️ Editar
                    </button>
                    <button @click="toggleUserStatus(user)" :class="user.status === 'active' ? 'text-red-600 hover:text-red-900' : 'text-green-600 hover:text-green-900'">
                      {{ user.status === 'active' ? '⏸️' : '▶️' }}
                    </button>
                    <button @click="deleteUser(user)" class="text-red-600 hover:text-red-900">
                      🗑️
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <!-- Empty State -->
        <div v-if="filteredUsers.length === 0" class="text-center py-12">
          <div class="text-gray-400 text-6xl mb-4">👥</div>
          <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum usuário encontrado</h3>
          <p class="text-gray-500 mb-4">Não há usuários com os filtros selecionados</p>
          <button @click="clearFilters" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
            🔄 Limpar Filtros
          </button>
        </div>
      </div>
    </div>

    <!-- Modal de Criar/Editar Usuário -->
    <div v-if="showCreateModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-2xl max-h-screen overflow-y-auto">
        <h3 class="text-lg font-medium text-gray-900 mb-6">👥 {{ editingUser ? 'Editar Usuário' : 'Novo Usuário' }}</h3>
        
        <div class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Nome Completo *</label>
              <input v-model="userForm.name" type="text" placeholder="João Silva" 
                     class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
              <input v-model="userForm.email" type="email" placeholder="joao@empresa.com" 
                     class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Telefone</label>
              <input v-model="userForm.phone" type="tel" placeholder="+55 11 99999-9999" 
                     class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Empresa *</label>
              <select v-model="userForm.company_id" 
                      class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Selecione uma empresa</option>
                <option v-for="company in companies" :key="company.id" :value="company.id">
                  {{ company.name }}
                </option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Função *</label>
              <select v-model="userForm.role" 
                      class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="company_agent">Company Agent</option>
                <option value="company_viewer">Company Viewer</option>
                <option value="company_admin">Company Admin</option>
                <option value="super_admin">Super Admin</option>
              </select>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
              <select v-model="userForm.status" 
                      class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="active">Ativo</option>
                <option value="inactive">Inativo</option>
              </select>
            </div>
          </div>

          <div v-if="!editingUser">
            <label class="block text-sm font-medium text-gray-700 mb-2">Senha *</label>
            <input v-model="userForm.password" type="password" placeholder="Senha segura" 
                   class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>
        </div>
        
        <div class="flex justify-end space-x-3 mt-8">
          <button @click="closeModal" 
                  class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
            Cancelar
          </button>
          <button @click="saveUser" 
                  class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700">
            {{ editingUser ? 'Atualizar' : 'Criar Usuário' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

// Estados
const users = ref([])
const companies = ref([])
const showCreateModal = ref(false)
const editingUser = ref(null)

const filters = ref({
  company_id: '',
  role: '',
  status: '',
  search: ''
})

const userForm = ref({
  name: '',
  email: '',
  phone: '',
  company_id: '',
  role: 'company_agent',
  password: '',
  status: 'active'
})

// Dados simulados
const mockUsers = [
  {
    id: 1,
    name: 'João Silva',
    email: 'joao@techsolutions.com',
    phone: '+55 11 99999-1234',
    company_id: 1,
    company_name: 'Tech Solutions',
    company_email: 'contato@techsolutions.com',
    role: 'company_admin',
    status: 'active',
    last_login: '2024-01-15T14:30:00Z'
  },
  {
    id: 2,
    name: 'Ana Costa',
    email: 'ana@digitalmarketing.com',
    phone: '+55 11 99999-5678',
    company_id: 2,
    company_name: 'Digital Marketing',
    company_email: 'admin@digitalmarketing.com',
    role: 'company_agent',
    status: 'active',
    last_login: '2024-01-15T10:15:00Z'
  },
  {
    id: 3,
    name: 'Carlos Oliveira',
    email: 'carlos@ecommerce.com',
    phone: '+55 11 99999-9012',
    company_id: 3,
    company_name: 'E-commerce Store',
    company_email: 'suporte@ecommerce.com',
    role: 'company_viewer',
    status: 'inactive',
    last_login: '2024-01-10T16:45:00Z'
  },
  {
    id: 4,
    name: 'Admin Sistema',
    email: 'admin@visionmetrics.com',
    phone: '+55 11 99999-0000',
    company_id: null,
    company_name: 'VisionMetrics',
    company_email: 'admin@visionmetrics.com',
    role: 'super_admin',
    status: 'active',
    last_login: '2024-01-15T16:20:00Z'
  }
]

const mockCompanies = [
  { id: 1, name: 'Tech Solutions', email: 'contato@techsolutions.com' },
  { id: 2, name: 'Digital Marketing', email: 'admin@digitalmarketing.com' },
  { id: 3, name: 'E-commerce Store', email: 'suporte@ecommerce.com' }
]

// Usuários filtrados
const filteredUsers = computed(() => {
  let filtered = users.value

  if (filters.value.company_id) {
    filtered = filtered.filter(user => user.company_id == filters.value.company_id)
  }

  if (filters.value.role) {
    filtered = filtered.filter(user => user.role === filters.value.role)
  }

  if (filters.value.status) {
    filtered = filtered.filter(user => user.status === filters.value.status)
  }

  if (filters.value.search) {
    const search = filters.value.search.toLowerCase()
    filtered = filtered.filter(user => 
      user.name.toLowerCase().includes(search) ||
      user.email.toLowerCase().includes(search) ||
      user.company_name.toLowerCase().includes(search)
    )
  }

  return filtered
})

// Métodos
const loadUsers = async () => {
  users.value = mockUsers
  companies.value = mockCompanies
}

const getInitials = (name) => {
  return name.split(' ').map(n => n[0]).join('').toUpperCase()
}

const getRoleClass = (role) => {
  const classes = {
    super_admin: 'bg-red-100 text-red-800',
    company_admin: 'bg-purple-100 text-purple-800',
    company_agent: 'bg-blue-100 text-blue-800',
    company_viewer: 'bg-green-100 text-green-800'
  }
  return classes[role] || classes.company_agent
}

const getRoleLabel = (role) => {
  const labels = {
    super_admin: '👑 Super Admin',
    company_admin: '👑 Company Admin',
    company_agent: '👤 Company Agent',
    company_viewer: '👁️ Company Viewer'
  }
  return labels[role] || labels.company_agent
}

const getStatusClass = (status) => {
  const classes = {
    active: 'bg-green-100 text-green-800',
    inactive: 'bg-red-100 text-red-800'
  }
  return classes[status] || classes.active
}

const getStatusLabel = (status) => {
  const labels = {
    active: '✅ Ativo',
    inactive: '❌ Inativo'
  }
  return labels[status] || labels.active
}

const formatDate = (dateString) => {
  if (!dateString) return 'Nunca'
  const date = new Date(dateString)
  return date.toLocaleDateString('pt-BR') + ' ' + date.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })
}

const viewUser = (user) => {
  alert(`Visualizando usuário: ${user.name}`)
}

const editUser = (user) => {
  editingUser.value = user
  userForm.value = {
    name: user.name,
    email: user.email,
    phone: user.phone,
    company_id: user.company_id || '',
    role: user.role,
    password: '',
    status: user.status
  }
  showCreateModal.value = true
}

const toggleUserStatus = (user) => {
  user.status = user.status === 'active' ? 'inactive' : 'active'
  alert(`Usuário ${user.status === 'active' ? 'ativado' : 'desativado'} com sucesso!`)
}

const deleteUser = (user) => {
  if (confirm(`Tem certeza que deseja deletar o usuário "${user.name}"?`)) {
    const index = users.value.findIndex(u => u.id === user.id)
    if (index > -1) {
      users.value.splice(index, 1)
      alert('Usuário deletado com sucesso!')
    }
  }
}

const saveUser = () => {
  if (!userForm.value.name || !userForm.value.email || !userForm.value.company_id) {
    alert('Preencha os campos obrigatórios')
    return
  }

  if (!editingUser.value && !userForm.value.password) {
    alert('A senha é obrigatória para novos usuários')
    return
  }

  const company = companies.value.find(c => c.id == userForm.value.company_id)

  if (editingUser.value) {
    // Atualizar usuário existente
    const index = users.value.findIndex(u => u.id === editingUser.value.id)
    if (index > -1) {
      users.value[index] = {
        ...users.value[index],
        name: userForm.value.name,
        email: userForm.value.email,
        phone: userForm.value.phone,
        company_id: userForm.value.company_id,
        company_name: company.name,
        company_email: company.email,
        role: userForm.value.role,
        status: userForm.value.status
      }
    }
    alert('Usuário atualizado com sucesso!')
  } else {
    // Criar novo usuário
    const newUser = {
      id: users.value.length + 1,
      name: userForm.value.name,
      email: userForm.value.email,
      phone: userForm.value.phone,
      company_id: userForm.value.company_id,
      company_name: company.name,
      company_email: company.email,
      role: userForm.value.role,
      status: userForm.value.status,
      last_login: null
    }
    
    users.value.unshift(newUser)
    alert('Usuário criado com sucesso!')
  }
  
  closeModal()
}

const exportUsers = () => {
  alert('Exportando lista de usuários...')
}

const clearFilters = () => {
  filters.value = {
    company_id: '',
    role: '',
    status: '',
    search: ''
  }
}

const closeModal = () => {
  showCreateModal.value = false
  editingUser.value = null
  userForm.value = {
    name: '',
    email: '',
    phone: '',
    company_id: '',
    role: 'company_agent',
    password: '',
    status: 'active'
  }
}

// Lifecycle
onMounted(() => {
  loadUsers()
})
</script>

