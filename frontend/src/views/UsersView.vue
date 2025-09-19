<template>
  <div class="p-6">
    <!-- Header -->
    <div class="mb-6">
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">👥 Usuários</h1>
          <p class="text-sm text-gray-600">Gerencie usuários e permissões da empresa</p>
        </div>
        <div class="flex items-center space-x-4">
          <button @click="showCreateModal = true" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
            ➕ Novo Usuário
          </button>
        </div>
      </div>
    </div>
      <!-- Filtros -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Função</label>
            <select v-model="filters.role" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
              <option value="">Todas</option>
              <option value="company_admin">Administrador</option>
              <option value="company_agent">Agente</option>
              <option value="company_viewer">Visualizador</option>
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
              🗑️ Limpar Filtros
            </button>
          </div>
        </div>
      </div>

      <!-- Lista de Usuários -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-medium text-gray-900">Usuários da Empresa</h3>
        </div>
        
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuário</th>
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
                    </div>
                  </div>
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
          <p class="text-gray-500 mb-4">Adicione usuários para colaborar na gestão da empresa</p>
          <button @click="showCreateModal = true" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
            ➕ Adicionar Primeiro Usuário
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
              <label class="block text-sm font-medium text-gray-700 mb-2">Função *</label>
              <select v-model="userForm.role" 
                      class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="company_agent">Agente</option>
                <option value="company_viewer">Visualizador</option>
                <option value="company_admin">Administrador</option>
              </select>
            </div>
          </div>

          <div v-if="!editingUser">
            <label class="block text-sm font-medium text-gray-700 mb-2">Senha *</label>
            <input v-model="userForm.password" type="password" placeholder="Senha segura" 
                   class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <div class="flex items-center space-x-4">
              <label class="flex items-center">
                <input v-model="userForm.status" value="active" type="radio" 
                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                <span class="ml-2 text-sm text-gray-900">Ativo</span>
              </label>
              <label class="flex items-center">
                <input v-model="userForm.status" value="inactive" type="radio" 
                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                <span class="ml-2 text-sm text-gray-900">Inativo</span>
              </label>
            </div>
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
const showCreateModal = ref(false)
const editingUser = ref(null)

const filters = ref({
  role: '',
  status: '',
  search: ''
})

const userForm = ref({
  name: '',
  email: '',
  phone: '',
  role: 'company_agent',
  password: '',
  status: 'active'
})

// Dados simulados
const mockUsers = [
  {
    id: 1,
    name: 'João Silva',
    email: 'joao@empresa.com',
    phone: '+55 11 99999-1234',
    role: 'company_admin',
    status: 'active',
    last_login: '2024-01-15T14:30:00Z'
  },
  {
    id: 2,
    name: 'Ana Costa',
    email: 'ana@empresa.com',
    phone: '+55 11 99999-5678',
    role: 'company_agent',
    status: 'active',
    last_login: '2024-01-15T10:15:00Z'
  },
  {
    id: 3,
    name: 'Carlos Oliveira',
    email: 'carlos@empresa.com',
    phone: '+55 11 99999-9012',
    role: 'company_viewer',
    status: 'inactive',
    last_login: '2024-01-10T16:45:00Z'
  }
]

// Usuários filtrados
const filteredUsers = computed(() => {
  let filtered = users.value

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
      user.email.toLowerCase().includes(search)
    )
  }

  return filtered
})

// Métodos
const loadUsers = async () => {
  users.value = mockUsers
}

const getInitials = (name) => {
  return name.split(' ').map(n => n[0]).join('').toUpperCase()
}

const getRoleClass = (role) => {
  const classes = {
    company_admin: 'bg-red-100 text-red-800',
    company_agent: 'bg-blue-100 text-blue-800',
    company_viewer: 'bg-green-100 text-green-800'
  }
  return classes[role] || classes.company_agent
}

const getRoleLabel = (role) => {
  const labels = {
    company_admin: '👑 Admin',
    company_agent: '👤 Agente',
    company_viewer: '👁️ Visualizador'
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

const editUser = (user) => {
  editingUser.value = user
  userForm.value = {
    name: user.name,
    email: user.email,
    phone: user.phone,
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
  if (!userForm.value.name || !userForm.value.email) {
    alert('Preencha os campos obrigatórios')
    return
  }

  if (!editingUser.value && !userForm.value.password) {
    alert('A senha é obrigatória para novos usuários')
    return
  }

  if (editingUser.value) {
    // Atualizar usuário existente
    const index = users.value.findIndex(u => u.id === editingUser.value.id)
    if (index > -1) {
      users.value[index] = {
        ...users.value[index],
        name: userForm.value.name,
        email: userForm.value.email,
        phone: userForm.value.phone,
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
      role: userForm.value.role,
      status: userForm.value.status,
      last_login: null
    }
    
    users.value.unshift(newUser)
    alert('Usuário criado com sucesso!')
  }
  
  closeModal()
}

const clearFilters = () => {
  filters.value = {
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

