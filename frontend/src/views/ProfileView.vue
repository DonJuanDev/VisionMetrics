<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-6">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">👤 Meu Perfil</h1>
            <p class="text-sm text-gray-600">Gerencie suas informações pessoais</p>
          </div>
        </div>
      </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Sidebar -->
        <div class="lg:col-span-1">
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="text-center">
              <div class="w-20 h-20 bg-gray-300 rounded-full flex items-center justify-center mx-auto mb-4">
                <span class="text-2xl font-bold text-gray-700">{{ getInitials(user.name) }}</span>
              </div>
              <h3 class="text-lg font-medium text-gray-900">{{ user.name }}</h3>
              <p class="text-sm text-gray-500">{{ user.email }}</p>
              <span :class="getRoleClass(user.role)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full mt-2">
                {{ getRoleLabel(user.role) }}
              </span>
            </div>
            
            <div class="mt-6 space-y-4">
              <div class="text-sm">
                <span class="font-medium text-gray-500">Membro desde:</span>
                <p class="text-gray-900">{{ formatDate(user.created_at) }}</p>
              </div>
              <div class="text-sm">
                <span class="font-medium text-gray-500">Último acesso:</span>
                <p class="text-gray-900">{{ formatDate(user.last_login) }}</p>
              </div>
              <div class="text-sm">
                <span class="font-medium text-gray-500">Status:</span>
                <p :class="user.status === 'active' ? 'text-green-600' : 'text-red-600'" class="font-medium">
                  {{ user.status === 'active' ? '✅ Ativo' : '❌ Inativo' }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Conteúdo Principal -->
        <div class="lg:col-span-2">
          <div class="space-y-6">
            <!-- Informações Pessoais -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
              <h3 class="text-lg font-medium text-gray-900 mb-6">📝 Informações Pessoais</h3>
              
              <div class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nome Completo *</label>
                    <input v-model="profileForm.name" type="text" 
                           class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                  </div>
                  
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                    <input v-model="profileForm.email" type="email" 
                           class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                  </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Telefone</label>
                    <input v-model="profileForm.phone" type="tel" 
                           class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                  </div>
                  
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Função</label>
                    <input :value="getRoleLabel(user.role)" type="text" disabled
                           class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm bg-gray-50 text-gray-500">
                  </div>
                </div>

                <div class="flex justify-end">
                  <button @click="saveProfile" 
                          class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                    💾 Salvar Informações
                  </button>
                </div>
              </div>
            </div>

            <!-- Alterar Senha -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
              <h3 class="text-lg font-medium text-gray-900 mb-6">🔒 Alterar Senha</h3>
              
              <div class="space-y-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Senha Atual *</label>
                  <input v-model="passwordForm.current_password" type="password" 
                         class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nova Senha *</label>
                    <input v-model="passwordForm.new_password" type="password" 
                           class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                  </div>
                  
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Confirmar Nova Senha *</label>
                    <input v-model="passwordForm.confirm_password" type="password" 
                           class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                  </div>
                </div>

                <div class="flex justify-end">
                  <button @click="changePassword" 
                          class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                    🔄 Alterar Senha
                  </button>
                </div>
              </div>
            </div>

            <!-- Preferências -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
              <h3 class="text-lg font-medium text-gray-900 mb-6">⚙️ Preferências</h3>
              
              <div class="space-y-6">
                <div class="flex items-center justify-between">
                  <div>
                    <h4 class="text-sm font-medium text-gray-900">Notificações por Email</h4>
                    <p class="text-sm text-gray-500">Receba notificações importantes por email</p>
                  </div>
                  <input v-model="preferences.email_notifications" type="checkbox" 
                         class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                </div>

                <div class="flex items-center justify-between">
                  <div>
                    <h4 class="text-sm font-medium text-gray-900">Notificações por WhatsApp</h4>
                    <p class="text-sm text-gray-500">Receba alertas urgentes via WhatsApp</p>
                  </div>
                  <input v-model="preferences.whatsapp_notifications" type="checkbox" 
                         class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                </div>

                <div class="flex items-center justify-between">
                  <div>
                    <h4 class="text-sm font-medium text-gray-900">Modo Escuro</h4>
                    <p class="text-sm text-gray-500">Use o tema escuro na interface</p>
                  </div>
                  <input v-model="preferences.dark_mode" type="checkbox" 
                         class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Fuso Horário</label>
                  <select v-model="preferences.timezone" 
                          class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="America/Sao_Paulo">São Paulo (UTC-3)</option>
                    <option value="America/New_York">Nova York (UTC-5)</option>
                    <option value="Europe/London">Londres (UTC+0)</option>
                  </select>
                </div>

                <div class="flex justify-end">
                  <button @click="savePreferences" 
                          class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                    💾 Salvar Preferências
                  </button>
                </div>
              </div>
            </div>

            <!-- Segurança -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
              <h3 class="text-lg font-medium text-gray-900 mb-6">🛡️ Segurança</h3>
              
              <div class="space-y-6">
                <div class="flex items-center justify-between">
                  <div>
                    <h4 class="text-sm font-medium text-gray-900">Autenticação de Dois Fatores</h4>
                    <p class="text-sm text-gray-500">Adicione uma camada extra de segurança</p>
                  </div>
                  <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                    {{ security.two_factor_enabled ? 'Desativar 2FA' : 'Ativar 2FA' }}
                  </button>
                </div>

                <div class="flex items-center justify-between">
                  <div>
                    <h4 class="text-sm font-medium text-gray-900">Sessões Ativas</h4>
                    <p class="text-sm text-gray-500">Gerencie seus dispositivos conectados</p>
                  </div>
                  <button @click="viewActiveSessions" class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                    Ver Sessões
                  </button>
                </div>

                <div class="flex items-center justify-between">
                  <div>
                    <h4 class="text-sm font-medium text-gray-900">Log de Atividades</h4>
                    <p class="text-sm text-gray-500">Visualize seu histórico de atividades</p>
                  </div>
                  <button @click="viewActivityLog" class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                    Ver Log
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

// Estados
const user = ref({
  name: 'João Silva',
  email: 'joao@empresa.com',
  phone: '+55 11 99999-1234',
  role: 'company_admin',
  status: 'active',
  created_at: '2024-01-01T00:00:00Z',
  last_login: '2024-01-15T14:30:00Z'
})

const profileForm = ref({
  name: '',
  email: '',
  phone: ''
})

const passwordForm = ref({
  current_password: '',
  new_password: '',
  confirm_password: ''
})

const preferences = ref({
  email_notifications: true,
  whatsapp_notifications: false,
  dark_mode: false,
  timezone: 'America/Sao_Paulo'
})

const security = ref({
  two_factor_enabled: false
})

// Métodos
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
    company_admin: '👑 Administrador',
    company_agent: '👤 Agente',
    company_viewer: '👁️ Visualizador'
  }
  return labels[role] || labels.company_agent
}

const formatDate = (dateString) => {
  if (!dateString) return 'Nunca'
  const date = new Date(dateString)
  return date.toLocaleDateString('pt-BR') + ' às ' + date.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })
}

const saveProfile = () => {
  if (!profileForm.value.name || !profileForm.value.email) {
    alert('Preencha os campos obrigatórios')
    return
  }

  user.value.name = profileForm.value.name
  user.value.email = profileForm.value.email
  user.value.phone = profileForm.value.phone
  
  alert('Perfil atualizado com sucesso!')
}

const changePassword = () => {
  if (!passwordForm.value.current_password || !passwordForm.value.new_password || !passwordForm.value.confirm_password) {
    alert('Preencha todos os campos')
    return
  }

  if (passwordForm.value.new_password !== passwordForm.value.confirm_password) {
    alert('As senhas não coincidem')
    return
  }

  if (passwordForm.value.new_password.length < 8) {
    alert('A nova senha deve ter pelo menos 8 caracteres')
    return
  }

  alert('Senha alterada com sucesso!')
  
  passwordForm.value = {
    current_password: '',
    new_password: '',
    confirm_password: ''
  }
}

const savePreferences = () => {
  alert('Preferências salvas com sucesso!')
}

const viewActiveSessions = () => {
  alert('Abrindo gerenciador de sessões...')
}

const viewActivityLog = () => {
  alert('Abrindo log de atividades...')
}

// Lifecycle
onMounted(() => {
  // Carregar dados do usuário
  profileForm.value = {
    name: user.value.name,
    email: user.value.email,
    phone: user.value.phone
  }
})
</script>

