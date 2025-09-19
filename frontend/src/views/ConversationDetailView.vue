<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-6">
          <div class="flex items-center space-x-4">
            <button @click="goBack" class="text-gray-600 hover:text-gray-900">
              ← Voltar
            </button>
            <div>
              <h1 class="text-2xl font-bold text-gray-900">💬 Detalhes da Conversa</h1>
              <p class="text-sm text-gray-600">{{ conversation.client_name }} - {{ conversation.phone }}</p>
            </div>
          </div>
          <div class="flex items-center space-x-4">
            <span :class="getStatusClass(conversation.status)" class="inline-flex px-3 py-1 text-sm font-semibold rounded-full">
              {{ getStatusLabel(conversation.status) }}
            </span>
            <button @click="markAsConverted" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
              💰 Marcar Conversão
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Chat Area -->
        <div class="lg:col-span-2">
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 h-96 flex flex-col">
            <!-- Chat Header -->
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
              <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white font-semibold">
                  {{ getInitials(conversation.client_name) }}
                </div>
                <div>
                  <h3 class="font-medium text-gray-900">{{ conversation.client_name }}</h3>
                  <p class="text-sm text-gray-500">{{ conversation.phone }}</p>
                </div>
              </div>
              <div class="text-sm text-gray-500">
                Online há {{ getLastSeenTime() }}
              </div>
            </div>

            <!-- Messages -->
            <div class="flex-1 overflow-y-auto p-6 space-y-4">
              <div v-for="message in messages" :key="message.id" 
                   :class="['flex', message.sender === 'client' ? 'justify-start' : 'justify-end']">
                <div :class="[
                  'max-w-xs lg:max-w-md px-4 py-2 rounded-lg',
                  message.sender === 'client' 
                    ? 'bg-gray-100 text-gray-900' 
                    : 'bg-blue-600 text-white'
                ]">
                  <p class="text-sm">{{ message.body }}</p>
                  <p :class="[
                    'text-xs mt-1',
                    message.sender === 'client' ? 'text-gray-500' : 'text-blue-100'
                  ]">
                    {{ formatTime(message.created_at) }}
                  </p>
                </div>
              </div>
            </div>

            <!-- Message Input -->
            <div class="border-t border-gray-200 p-4">
              <div class="flex space-x-3">
                <input 
                  v-model="newMessage"
                  @keypress.enter="sendMessage"
                  type="text" 
                  placeholder="Digite sua mensagem..."
                  class="flex-1 border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                <button 
                  @click="sendMessage"
                  :disabled="!newMessage.trim()"
                  class="bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors"
                >
                  Enviar
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Cliente Info -->
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">👤 Informações do Cliente</h3>
            <div class="space-y-3">
              <div>
                <label class="text-sm font-medium text-gray-700">Nome</label>
                <p class="text-sm text-gray-900">{{ conversation.client_name }}</p>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-700">Telefone</label>
                <p class="text-sm text-gray-900">{{ conversation.phone }}</p>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-700">Origem</label>
                <span :class="getOriginClass(conversation.origin)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                  {{ getOriginLabel(conversation.origin) }}
                </span>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-700">Primeiro Contato</label>
                <p class="text-sm text-gray-900">{{ formatDate(conversation.first_contact_at) }}</p>
              </div>
            </div>
          </div>

          <!-- Atribuição -->
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">👥 Atribuição</h3>
            <div class="space-y-3">
              <div>
                <label class="text-sm font-medium text-gray-700">Responsável</label>
                <select v-model="conversation.assigned_to" class="mt-1 w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                  <option value="">Não atribuído</option>
                  <option value="Maria Santos">Maria Santos</option>
                  <option value="Pedro Lima">Pedro Lima</option>
                  <option value="Ana Costa">Ana Costa</option>
                </select>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-700">Status</label>
                <select v-model="conversation.status" class="mt-1 w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                  <option value="open">🟢 Aberta</option>
                  <option value="qualified">⭐ Qualificada</option>
                  <option value="closed">💰 Convertida</option>
                  <option value="lost">❌ Perdida</option>
                </select>
              </div>
              <button @click="saveChanges" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                Salvar Alterações
              </button>
            </div>
          </div>

          <!-- Tags -->
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">🏷️ Tags</h3>
            <div class="space-y-3">
              <div class="flex flex-wrap gap-2">
                <span v-for="tag in conversation.tags" :key="tag" 
                      class="inline-flex items-center px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                  {{ tag }}
                  <button @click="removeTag(tag)" class="ml-1 text-blue-600 hover:text-blue-800">×</button>
                </span>
              </div>
              <div class="flex space-x-2">
                <input 
                  v-model="newTag"
                  @keypress.enter="addTag"
                  type="text" 
                  placeholder="Nova tag..."
                  class="flex-1 border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                <button @click="addTag" class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-2 rounded-md text-sm">
                  +
                </button>
              </div>
            </div>
          </div>

          <!-- Conversões -->
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">💰 Conversões</h3>
            <div v-if="conversation.conversions.length === 0" class="text-center py-4">
              <p class="text-gray-500 text-sm">Nenhuma conversão registrada</p>
            </div>
            <div v-else class="space-y-3">
              <div v-for="conversion in conversation.conversions" :key="conversion.id" 
                   class="border border-gray-200 rounded-lg p-3">
                <div class="flex justify-between items-start">
                  <div>
                    <p class="font-medium text-gray-900">R$ {{ conversion.value }}</p>
                    <p class="text-sm text-gray-500">{{ conversion.method }}</p>
                  </div>
                  <span class="text-xs text-gray-400">{{ formatDate(conversion.detected_at) }}</span>
                </div>
              </div>
            </div>
            <button @click="showConversionModal = true" class="w-full mt-3 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
              + Registrar Conversão
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de Conversão -->
    <div v-if="showConversionModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-medium text-gray-900 mb-4">💰 Registrar Conversão</h3>
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Valor (R$)</label>
            <input v-model="conversionForm.value" type="number" step="0.01" 
                   class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Método de Pagamento</label>
            <select v-model="conversionForm.method" 
                    class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
              <option value="pix">PIX</option>
              <option value="cartao">Cartão de Crédito</option>
              <option value="boleto">Boleto</option>
              <option value="dinheiro">Dinheiro</option>
              <option value="transferencia">Transferência</option>
            </select>
          </div>
        </div>
        <div class="flex justify-end space-x-3 mt-6">
          <button @click="showConversionModal = false" 
                  class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
            Cancelar
          </button>
          <button @click="saveConversion" 
                  class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700">
            Salvar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'

const router = useRouter()
const route = useRoute()

// Estados
const conversation = ref({
  id: 1,
  client_name: 'João Silva',
  phone: '+55 11 99999-1234',
  origin: 'meta',
  status: 'open',
  assigned_to: 'Maria Santos',
  first_contact_at: '2024-01-15T08:30:00Z',
  tags: ['Interessado', 'Produto A'],
  conversions: []
})

const messages = ref([
  {
    id: 1,
    sender: 'client',
    body: 'Olá, gostaria de saber mais sobre seus produtos',
    created_at: '2024-01-15T10:30:00Z'
  },
  {
    id: 2,
    sender: 'agent',
    body: 'Olá! Claro, ficarei feliz em ajudar. Que tipo de produto você está procurando?',
    created_at: '2024-01-15T10:32:00Z'
  },
  {
    id: 3,
    sender: 'client',
    body: 'Estou interessado no plano premium',
    created_at: '2024-01-15T10:35:00Z'
  }
])

const newMessage = ref('')
const newTag = ref('')
const showConversionModal = ref(false)
const conversionForm = ref({
  value: '',
  method: 'pix'
})

// Métodos
const goBack = () => {
  router.push('/conversations')
}

const getInitials = (name) => {
  return name.split(' ').map(n => n[0]).join('').toUpperCase()
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

const formatDate = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('pt-BR') + ' ' + date.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })
}

const formatTime = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })
}

const getLastSeenTime = () => {
  return '2 minutos'
}

const sendMessage = () => {
  if (!newMessage.value.trim()) return
  
  const message = {
    id: messages.value.length + 1,
    sender: 'agent',
    body: newMessage.value,
    created_at: new Date().toISOString()
  }
  
  messages.value.push(message)
  newMessage.value = ''
}

const addTag = () => {
  if (!newTag.value.trim()) return
  
  if (!conversation.value.tags.includes(newTag.value)) {
    conversation.value.tags.push(newTag.value)
  }
  
  newTag.value = ''
}

const removeTag = (tag) => {
  const index = conversation.value.tags.indexOf(tag)
  if (index > -1) {
    conversation.value.tags.splice(index, 1)
  }
}

const saveChanges = () => {
  alert('Alterações salvas com sucesso!')
}

const markAsConverted = () => {
  conversation.value.status = 'closed'
  showConversionModal.value = true
}

const saveConversion = () => {
  if (!conversionForm.value.value) return
  
  const conversion = {
    id: conversation.value.conversions.length + 1,
    value: parseFloat(conversionForm.value.value),
    method: conversionForm.value.method,
    detected_at: new Date().toISOString()
  }
  
  conversation.value.conversions.push(conversion)
  showConversionModal.value = false
  conversionForm.value = { value: '', method: 'pix' }
  
  alert('Conversão registrada com sucesso!')
}

// Lifecycle
onMounted(() => {
  // Carregar dados da conversa baseado no ID da rota
  const conversationId = route.params.id
  console.log('Loading conversation:', conversationId)
})
</script>
