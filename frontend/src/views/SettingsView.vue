<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-6">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">⚙️ Configurações</h1>
            <p class="text-sm text-gray-600">Gerencie as configurações da sua empresa</p>
          </div>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Menu Lateral -->
        <div class="lg:col-span-1">
          <nav class="space-y-1">
            <button v-for="tab in tabs" :key="tab.id" 
                    @click="activeTab = tab.id"
                    :class="[
                      'w-full text-left px-3 py-2 text-sm font-medium rounded-md transition-colors',
                      activeTab === tab.id 
                        ? 'bg-blue-100 text-blue-700' 
                        : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'
                    ]">
              {{ tab.icon }} {{ tab.name }}
            </button>
          </nav>
        </div>

        <!-- Conteúdo -->
        <div class="lg:col-span-2">
          <!-- Configurações Gerais -->
          <div v-if="activeTab === 'general'" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-6">🏢 Informações da Empresa</h3>
            
            <div class="space-y-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Nome da Empresa *</label>
                  <input v-model="companyForm.name" type="text" 
                         class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">CNPJ</label>
                  <input v-model="companyForm.cnpj" type="text" 
                         class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                  <input v-model="companyForm.email" type="email" 
                         class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Telefone</label>
                  <input v-model="companyForm.phone" type="tel" 
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

              <div class="flex justify-end">
                <button @click="saveCompanySettings" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                  💾 Salvar Configurações
                </button>
              </div>
            </div>
          </div>

          <!-- Integrações -->
          <div v-if="activeTab === 'integrations'" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-6">🔗 Integrações</h3>
            
            <div class="space-y-6">
              <!-- WhatsApp -->
              <div class="border border-gray-200 rounded-lg p-4">
                <div class="flex items-center justify-between">
                  <div class="flex items-center">
                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                      <span class="text-green-600 text-lg">💬</span>
                    </div>
                    <div>
                      <h4 class="text-sm font-medium text-gray-900">WhatsApp Cloud API</h4>
                      <p class="text-sm text-gray-500">Configure tokens do WhatsApp</p>
                    </div>
                  </div>
                  <span :class="integrations.whatsapp.configured ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" 
                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                    {{ integrations.whatsapp.configured ? '✅ Configurado' : '❌ Não Configurado' }}
                  </span>
                </div>
                <div class="mt-4">
                  <input v-model="integrations.whatsapp.token" type="password" placeholder="Token do WhatsApp" 
                         class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
              </div>

              <!-- Meta Ads -->
              <div class="border border-gray-200 rounded-lg p-4">
                <div class="flex items-center justify-between">
                  <div class="flex items-center">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                      <span class="text-blue-600 text-lg">📘</span>
                    </div>
                    <div>
                      <h4 class="text-sm font-medium text-gray-900">Meta Ads</h4>
                      <p class="text-sm text-gray-500">Integração com Facebook/Meta Ads</p>
                    </div>
                  </div>
                  <span :class="integrations.meta.configured ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" 
                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                    {{ integrations.meta.configured ? '✅ Configurado' : '❌ Não Configurado' }}
                  </span>
                </div>
                <div class="mt-4 grid grid-cols-2 gap-4">
                  <input v-model="integrations.meta.access_token" type="password" placeholder="Access Token" 
                         class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                  <input v-model="integrations.meta.pixel_id" type="text" placeholder="Pixel ID" 
                         class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
              </div>

              <!-- Google Ads -->
              <div class="border border-gray-200 rounded-lg p-4">
                <div class="flex items-center justify-between">
                  <div class="flex items-center">
                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                      <span class="text-green-600 text-lg">🟢</span>
                    </div>
                    <div>
                      <h4 class="text-sm font-medium text-gray-900">Google Ads</h4>
                      <p class="text-sm text-gray-500">Integração com Google Ads</p>
                    </div>
                  </div>
                  <span :class="integrations.google.configured ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" 
                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                    {{ integrations.google.configured ? '✅ Configurado' : '❌ Não Configurado' }}
                  </span>
                </div>
                <div class="mt-4 grid grid-cols-2 gap-4">
                  <input v-model="integrations.google.client_id" type="text" placeholder="Client ID" 
                         class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                  <input v-model="integrations.google.client_secret" type="password" placeholder="Client Secret" 
                         class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
              </div>

              <div class="flex justify-end">
                <button @click="saveIntegrations" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                  💾 Salvar Integrações
                </button>
              </div>
            </div>
          </div>

          <!-- Notificações -->
          <div v-if="activeTab === 'notifications'" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-6">🔔 Notificações</h3>
            
            <div class="space-y-6">
              <div class="flex items-center justify-between">
                <div>
                  <h4 class="text-sm font-medium text-gray-900">Email de Novas Conversas</h4>
                  <p class="text-sm text-gray-500">Receba notificações por email quando houver novas conversas</p>
                </div>
                <input v-model="notifications.email.new_conversations" type="checkbox" 
                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
              </div>

              <div class="flex items-center justify-between">
                <div>
                  <h4 class="text-sm font-medium text-gray-900">Email de Conversões</h4>
                  <p class="text-sm text-gray-500">Receba notificações quando houver novas conversões</p>
                </div>
                <input v-model="notifications.email.conversions" type="checkbox" 
                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
              </div>

              <div class="flex items-center justify-between">
                <div>
                  <h4 class="text-sm font-medium text-gray-900">WhatsApp de Alertas</h4>
                  <p class="text-sm text-gray-500">Receba alertas importantes via WhatsApp</p>
                </div>
                <input v-model="notifications.whatsapp.alerts" type="checkbox" 
                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
              </div>

              <div class="flex justify-end">
                <button @click="saveNotifications" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                  💾 Salvar Notificações
                </button>
              </div>
            </div>
          </div>

          <!-- Segurança -->
          <div v-if="activeTab === 'security'" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-6">🔒 Segurança</h3>
            
            <div class="space-y-6">
              <div>
                <h4 class="text-sm font-medium text-gray-900 mb-2">Alterar Senha</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                  <input v-model="securityForm.current_password" type="password" placeholder="Senha Atual" 
                         class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                  <input v-model="securityForm.new_password" type="password" placeholder="Nova Senha" 
                         class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                  <input v-model="securityForm.confirm_password" type="password" placeholder="Confirmar Nova Senha" 
                         class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
              </div>

              <div class="flex items-center justify-between">
                <div>
                  <h4 class="text-sm font-medium text-gray-900">Autenticação de Dois Fatores</h4>
                  <p class="text-sm text-gray-500">Adicione uma camada extra de segurança à sua conta</p>
                </div>
                <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                  {{ security.two_factor_enabled ? 'Desativar 2FA' : 'Ativar 2FA' }}
                </button>
              </div>

              <div class="flex justify-end">
                <button @click="saveSecurity" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                  💾 Salvar Configurações de Segurança
                </button>
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
const activeTab = ref('general')

const tabs = [
  { id: 'general', name: 'Informações Gerais', icon: '🏢' },
  { id: 'integrations', name: 'Integrações', icon: '🔗' },
  { id: 'notifications', name: 'Notificações', icon: '🔔' },
  { id: 'security', name: 'Segurança', icon: '🔒' }
]

const companyForm = ref({
  name: 'Minha Empresa',
  cnpj: '12.345.678/0001-90',
  email: 'contato@minhaempresa.com',
  phone: '+55 11 99999-9999',
  timezone: 'America/Sao_Paulo'
})

const integrations = ref({
  whatsapp: {
    configured: false,
    token: ''
  },
  meta: {
    configured: false,
    access_token: '',
    pixel_id: ''
  },
  google: {
    configured: false,
    client_id: '',
    client_secret: ''
  }
})

const notifications = ref({
  email: {
    new_conversations: true,
    conversions: true
  },
  whatsapp: {
    alerts: false
  }
})

const security = ref({
  two_factor_enabled: false
})

const securityForm = ref({
  current_password: '',
  new_password: '',
  confirm_password: ''
})

// Métodos
const saveCompanySettings = () => {
  alert('Configurações da empresa salvas com sucesso!')
}

const saveIntegrations = () => {
  alert('Integrações salvas com sucesso!')
}

const saveNotifications = () => {
  alert('Configurações de notificações salvas com sucesso!')
}

const saveSecurity = () => {
  if (securityForm.value.new_password && securityForm.value.new_password !== securityForm.value.confirm_password) {
    alert('As senhas não coincidem!')
    return
  }
  alert('Configurações de segurança salvas com sucesso!')
}

// Lifecycle
onMounted(() => {
  // Carregar configurações existentes
})
</script>

