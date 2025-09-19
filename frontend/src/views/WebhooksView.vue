<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-6">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">🔗 Webhooks</h1>
            <p class="text-sm text-gray-600">Configure webhooks para integrações externas</p>
          </div>
          <div class="flex items-center space-x-4">
            <button @click="showCreateModal = true" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
              ➕ Novo Webhook
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
      <!-- Lista de Webhooks -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-medium text-gray-900">Webhooks Configurados</h3>
        </div>
        
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">URL</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Eventos</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Última Execução</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="webhook in webhooks" :key="webhook.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0">
                      <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                        <span class="text-blue-600 text-sm">🔗</span>
                      </div>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">{{ webhook.name }}</div>
                      <div class="text-sm text-gray-500">{{ webhook.description }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900 font-mono">{{ webhook.url }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex flex-wrap gap-1">
                    <span v-for="event in webhook.events" :key="event" 
                          class="inline-flex px-2 py-1 text-xs font-semibold bg-blue-100 text-blue-800 rounded-full">
                      {{ event }}
                    </span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getStatusClass(webhook.status)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                    {{ getStatusLabel(webhook.status) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatDate(webhook.last_execution) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <div class="flex space-x-2">
                    <button @click="testWebhook(webhook)" class="text-blue-600 hover:text-blue-900">
                      🧪 Testar
                    </button>
                    <button @click="editWebhook(webhook)" class="text-indigo-600 hover:text-indigo-900">
                      ✏️ Editar
                    </button>
                    <button @click="toggleWebhook(webhook)" :class="webhook.status === 'active' ? 'text-red-600 hover:text-red-900' : 'text-green-600 hover:text-green-900'">
                      {{ webhook.status === 'active' ? '⏸️' : '▶️' }}
                    </button>
                    <button @click="deleteWebhook(webhook)" class="text-red-600 hover:text-red-900">
                      🗑️
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <!-- Empty State -->
        <div v-if="webhooks.length === 0" class="text-center py-12">
          <div class="text-gray-400 text-6xl mb-4">🔗</div>
          <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum webhook configurado</h3>
          <p class="text-gray-500 mb-4">Configure webhooks para receber notificações em tempo real</p>
          <button @click="showCreateModal = true" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
            ➕ Configurar Primeiro Webhook
          </button>
        </div>
      </div>
    </div>

    <!-- Modal de Criar/Editar Webhook -->
    <div v-if="showCreateModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-2xl max-h-screen overflow-y-auto">
        <h3 class="text-lg font-medium text-gray-900 mb-6">🔗 {{ editingWebhook ? 'Editar Webhook' : 'Configurar Novo Webhook' }}</h3>
        
        <div class="space-y-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Nome do Webhook *</label>
            <input v-model="webhookForm.name" type="text" placeholder="Ex: Notificações Slack" 
                   class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">URL do Webhook *</label>
            <input v-model="webhookForm.url" type="url" placeholder="https://hooks.slack.com/services/..." 
                   class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Descrição</label>
            <textarea v-model="webhookForm.description" placeholder="Descrição opcional do webhook..." rows="3"
                      class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Eventos a Notificar *</label>
            <div class="grid grid-cols-2 gap-2">
              <label v-for="event in availableEvents" :key="event.value" class="flex items-center">
                <input v-model="webhookForm.events" :value="event.value" type="checkbox" 
                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                <span class="ml-2 text-sm text-gray-900">{{ event.label }}</span>
              </label>
            </div>
          </div>

          <div class="flex items-center">
            <input v-model="webhookForm.is_active" type="checkbox" id="active" 
                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
            <label for="active" class="ml-2 block text-sm text-gray-900">
              Webhook ativo
            </label>
          </div>
        </div>
        
        <div class="flex justify-end space-x-3 mt-8">
          <button @click="closeModal" 
                  class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
            Cancelar
          </button>
          <button @click="saveWebhook" 
                  class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700">
            {{ editingWebhook ? 'Atualizar' : 'Criar Webhook' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

// Estados
const webhooks = ref([])
const showCreateModal = ref(false)
const editingWebhook = ref(null)

const webhookForm = ref({
  name: '',
  url: '',
  description: '',
  events: [],
  is_active: true
})

const availableEvents = [
  { value: 'conversation.created', label: 'Nova Conversa' },
  { value: 'conversation.updated', label: 'Conversa Atualizada' },
  { value: 'conversation.closed', label: 'Conversa Fechada' },
  { value: 'conversion.created', label: 'Nova Conversão' },
  { value: 'conversion.confirmed', label: 'Conversão Confirmada' },
  { value: 'lead.created', label: 'Novo Lead' },
  { value: 'lead.updated', label: 'Lead Atualizado' },
  { value: 'message.received', label: 'Mensagem Recebida' }
]

// Dados simulados
const mockWebhooks = [
  {
    id: 1,
    name: 'Slack Notifications',
    description: 'Notificações para canal #vendas',
    url: 'https://hooks.slack.com/services/T00000000/B00000000/XXXXXXXXXXXXXXXXXXXXXXXX',
    events: ['conversation.created', 'conversion.created'],
    status: 'active',
    last_execution: '2024-01-15T14:30:00Z'
  },
  {
    id: 2,
    name: 'CRM Integration',
    description: 'Sincronização com CRM externo',
    url: 'https://api.crm.com/webhooks/visionmetrics',
    events: ['lead.created', 'lead.updated', 'conversion.confirmed'],
    status: 'inactive',
    last_execution: '2024-01-14T09:15:00Z'
  }
]

// Métodos
const loadWebhooks = async () => {
  webhooks.value = mockWebhooks
}

const getStatusClass = (status) => {
  const classes = {
    active: 'bg-green-100 text-green-800',
    inactive: 'bg-red-100 text-red-800',
    error: 'bg-yellow-100 text-yellow-800'
  }
  return classes[status] || classes.inactive
}

const getStatusLabel = (status) => {
  const labels = {
    active: '✅ Ativo',
    inactive: '❌ Inativo',
    error: '⚠️ Erro'
  }
  return labels[status] || labels.inactive
}

const formatDate = (dateString) => {
  if (!dateString) return 'Nunca'
  const date = new Date(dateString)
  return date.toLocaleDateString('pt-BR') + ' ' + date.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })
}

const testWebhook = (webhook) => {
  alert(`Testando webhook: ${webhook.name}`)
}

const editWebhook = (webhook) => {
  editingWebhook.value = webhook
  webhookForm.value = {
    name: webhook.name,
    url: webhook.url,
    description: webhook.description,
    events: [...webhook.events],
    is_active: webhook.status === 'active'
  }
  showCreateModal.value = true
}

const toggleWebhook = (webhook) => {
  webhook.status = webhook.status === 'active' ? 'inactive' : 'active'
  alert(`Webhook ${webhook.status === 'active' ? 'ativado' : 'desativado'} com sucesso!`)
}

const deleteWebhook = (webhook) => {
  if (confirm(`Tem certeza que deseja deletar o webhook "${webhook.name}"?`)) {
    const index = webhooks.value.findIndex(w => w.id === webhook.id)
    if (index > -1) {
      webhooks.value.splice(index, 1)
      alert('Webhook deletado com sucesso!')
    }
  }
}

const saveWebhook = () => {
  if (!webhookForm.value.name || !webhookForm.value.url || webhookForm.value.events.length === 0) {
    alert('Preencha os campos obrigatórios')
    return
  }

  if (editingWebhook.value) {
    // Atualizar webhook existente
    const index = webhooks.value.findIndex(w => w.id === editingWebhook.value.id)
    if (index > -1) {
      webhooks.value[index] = {
        ...webhooks.value[index],
        name: webhookForm.value.name,
        url: webhookForm.value.url,
        description: webhookForm.value.description,
        events: webhookForm.value.events,
        status: webhookForm.value.is_active ? 'active' : 'inactive'
      }
    }
    alert('Webhook atualizado com sucesso!')
  } else {
    // Criar novo webhook
    const newWebhook = {
      id: webhooks.value.length + 1,
      name: webhookForm.value.name,
      url: webhookForm.value.url,
      description: webhookForm.value.description,
      events: webhookForm.value.events,
      status: webhookForm.value.is_active ? 'active' : 'inactive',
      last_execution: null
    }
    
    webhooks.value.unshift(newWebhook)
    alert('Webhook criado com sucesso!')
  }
  
  closeModal()
}

const closeModal = () => {
  showCreateModal.value = false
  editingWebhook.value = null
  webhookForm.value = {
    name: '',
    url: '',
    description: '',
    events: [],
    is_active: true
  }
}

// Lifecycle
onMounted(() => {
  loadWebhooks()
})
</script>

