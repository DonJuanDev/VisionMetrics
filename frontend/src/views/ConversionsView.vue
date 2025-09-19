<template>
  <div class="p-6">
    <!-- Header -->
    <div class="mb-6">
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">💰 Conversões</h1>
          <p class="text-sm text-gray-600">Acompanhe vendas e conversões do WhatsApp</p>
        </div>
        <div class="flex items-center space-x-4">
          <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
            📊 Relatório
          </button>
          <button @click="showCreateModal = true" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
            ➕ Nova Conversão
          </button>
        </div>
      </div>
    </div>

    <!-- Filtros -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select v-model="filters.status" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
              <option value="">Todos</option>
              <option value="pending">Pendente</option>
              <option value="confirmed">Confirmada</option>
              <option value="cancelled">Cancelada</option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Método de Pagamento</label>
            <select v-model="filters.payment_method" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
              <option value="">Todos</option>
              <option value="pix">PIX</option>
              <option value="cartao">Cartão de Crédito</option>
              <option value="boleto">Boleto</option>
              <option value="dinheiro">Dinheiro</option>
              <option value="transferencia">Transferência</option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Detecção</label>
            <select v-model="filters.detection_type" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
              <option value="">Todas</option>
              <option value="manual">Manual</option>
              <option value="nlp">NLP Automática</option>
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
              placeholder="Cliente, valor ou ID..."
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
              <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                <span class="text-green-600 text-lg">💰</span>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Total de Conversões</p>
              <p class="text-2xl font-bold text-gray-900">{{ stats.total }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                <span class="text-blue-600 text-lg">💵</span>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Valor Total</p>
              <p class="text-2xl font-bold text-gray-900">R$ {{ stats.totalValue.toLocaleString() }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                <span class="text-yellow-600 text-lg">⏳</span>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Pendentes</p>
              <p class="text-2xl font-bold text-gray-900">{{ stats.pending }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                <span class="text-purple-600 text-lg">📈</span>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Ticket Médio</p>
              <p class="text-2xl font-bold text-gray-900">R$ {{ stats.averageTicket.toLocaleString() }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Lista de Conversões -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-medium text-gray-900">Lista de Conversões</h3>
        </div>
        
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Cliente
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Valor
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Método
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Status
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Detecção
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Data
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Ações
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="conversion in filteredConversions" :key="conversion.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10">
                      <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                        <span class="text-sm font-medium text-gray-700">{{ getInitials(conversion.client_name) }}</span>
                      </div>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">{{ conversion.client_name }}</div>
                      <div class="text-sm text-gray-500">{{ conversion.phone }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-bold text-gray-900">R$ {{ conversion.value.toLocaleString() }}</div>
                  <div class="text-xs text-gray-500">{{ conversion.currency }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getPaymentMethodClass(conversion.payment_method)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                    {{ getPaymentMethodLabel(conversion.payment_method) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getStatusClass(conversion.status)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                    {{ getStatusLabel(conversion.status) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getDetectionClass(conversion.detection_type)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                    {{ getDetectionLabel(conversion.detection_type) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatDate(conversion.detected_at) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <div class="flex space-x-2">
                    <button @click="viewConversion(conversion)" class="text-blue-600 hover:text-blue-900">
                      👁️ Ver
                    </button>
                    <button @click="editConversion(conversion)" class="text-indigo-600 hover:text-indigo-900">
                      ✏️ Editar
                    </button>
                    <button v-if="conversion.status === 'pending'" @click="confirmConversion(conversion)" class="text-green-600 hover:text-green-900">
                      ✅ Confirmar
                    </button>
                    <button v-if="conversion.status === 'pending'" @click="cancelConversion(conversion)" class="text-red-600 hover:text-red-900">
                      ❌ Cancelar
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <!-- Empty State -->
        <div v-if="filteredConversions.length === 0" class="text-center py-12">
          <div class="text-gray-400 text-6xl mb-4">💰</div>
          <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhuma conversão encontrada</h3>
          <p class="text-gray-500 mb-4">{{ loading ? 'Carregando conversões...' : 'Não há conversões com os filtros selecionados.' }}</p>
          <button @click="showCreateModal = true" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
            ➕ Registrar Primeira Conversão
          </button>
        </div>
      </div>
    </div>

    <!-- Modal de Criar/Editar Conversão -->
    <div v-if="showCreateModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-2xl max-h-screen overflow-y-auto">
        <h3 class="text-lg font-medium text-gray-900 mb-6">💰 {{ editingConversion ? 'Editar Conversão' : 'Registrar Nova Conversão' }}</h3>
        
        <div class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Cliente *</label>
              <select v-model="conversionForm.client_id" 
                      class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Selecione um cliente</option>
                <option v-for="client in clients" :key="client.id" :value="client.id">
                  {{ client.name }} - {{ client.phone }}
                </option>
              </select>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Valor (R$) *</label>
              <input v-model="conversionForm.value" type="number" step="0.01" min="0" 
                     class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Método de Pagamento *</label>
              <select v-model="conversionForm.payment_method" 
                      class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="pix">PIX</option>
                <option value="cartao">Cartão de Crédito</option>
                <option value="boleto">Boleto</option>
                <option value="dinheiro">Dinheiro</option>
                <option value="transferencia">Transferência</option>
              </select>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
              <select v-model="conversionForm.status" 
                      class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="pending">Pendente</option>
                <option value="confirmed">Confirmada</option>
                <option value="cancelled">Cancelada</option>
              </select>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Observações</label>
            <textarea v-model="conversionForm.notes" placeholder="Observações sobre a conversão..." rows="3"
                      class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
          </div>

          <div class="flex items-center">
            <input v-model="conversionForm.detection_type" value="manual" type="radio" id="manual" 
                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
            <label for="manual" class="ml-2 block text-sm text-gray-900">
              Detecção Manual
            </label>
            <input v-model="conversionForm.detection_type" value="nlp" type="radio" id="nlp" 
                   class="ml-4 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
            <label for="nlp" class="ml-2 block text-sm text-gray-900">
              Detecção por NLP
            </label>
          </div>
        </div>
        
        <div class="flex justify-end space-x-3 mt-8">
          <button @click="closeModal" 
                  class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
            Cancelar
          </button>
          <button @click="saveConversion" 
                  class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700">
            {{ editingConversion ? 'Atualizar' : 'Registrar Conversão' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

// Estados
const loading = ref(true)
const conversions = ref([])
const clients = ref([])
const showCreateModal = ref(false)
const editingConversion = ref(null)

// Filtros
const filters = ref({
  status: '',
  payment_method: '',
  detection_type: '',
  period: 'month',
  search: ''
})

// Stats
const stats = ref({
  total: 0,
  totalValue: 0,
  pending: 0,
  averageTicket: 0
})

// Form
const conversionForm = ref({
  client_id: '',
  value: '',
  payment_method: 'pix',
  status: 'pending',
  notes: '',
  detection_type: 'manual'
})

// Dados de exemplo (simulados)
const mockConversions = [
  {
    id: 1,
    client_name: 'João Silva',
    phone: '+55 11 99999-1234',
    value: 1200.00,
    currency: 'BRL',
    payment_method: 'pix',
    status: 'confirmed',
    detection_type: 'manual',
    detected_at: '2024-01-15T14:30:00Z',
    notes: 'Venda confirmada via WhatsApp'
  },
  {
    id: 2,
    client_name: 'Ana Costa',
    phone: '+55 11 99999-5678',
    value: 850.50,
    currency: 'BRL',
    payment_method: 'cartao',
    status: 'pending',
    detection_type: 'nlp',
    detected_at: '2024-01-15T10:15:00Z',
    notes: 'Detectada automaticamente: "paguei 850,50 no cartão"'
  },
  {
    id: 3,
    client_name: 'Carlos Oliveira',
    phone: '+55 11 99999-9012',
    value: 2500.00,
    currency: 'BRL',
    payment_method: 'boleto',
    status: 'confirmed',
    detection_type: 'manual',
    detected_at: '2024-01-14T16:45:00Z',
    notes: 'Boleto gerado e pago'
  },
  {
    id: 4,
    client_name: 'Mariana Santos',
    phone: '+55 11 99999-3456',
    value: 450.00,
    currency: 'BRL',
    payment_method: 'dinheiro',
    status: 'cancelled',
    detection_type: 'manual',
    detected_at: '2024-01-13T11:20:00Z',
    notes: 'Cliente cancelou a compra'
  }
]

const mockClients = [
  { id: 1, name: 'João Silva', phone: '+55 11 99999-1234' },
  { id: 2, name: 'Ana Costa', phone: '+55 11 99999-5678' },
  { id: 3, name: 'Carlos Oliveira', phone: '+55 11 99999-9012' },
  { id: 4, name: 'Mariana Santos', phone: '+55 11 99999-3456' }
]

// Conversões filtradas
const filteredConversions = computed(() => {
  let filtered = conversions.value

  if (filters.value.status) {
    filtered = filtered.filter(conv => conv.status === filters.value.status)
  }

  if (filters.value.payment_method) {
    filtered = filtered.filter(conv => conv.payment_method === filters.value.payment_method)
  }

  if (filters.value.detection_type) {
    filtered = filtered.filter(conv => conv.detection_type === filters.value.detection_type)
  }

  if (filters.value.search) {
    const search = filters.value.search.toLowerCase()
    filtered = filtered.filter(conv => 
      conv.client_name.toLowerCase().includes(search) ||
      conv.phone.includes(search) ||
      conv.value.toString().includes(search) ||
      conv.id.toString().includes(search)
    )
  }

  return filtered
})

// Métodos
const loadConversions = async () => {
  loading.value = true
  
  // Simular chamada da API
  setTimeout(() => {
    conversions.value = mockConversions
    clients.value = mockClients
    
    const totalValue = mockConversions.reduce((sum, conv) => sum + conv.value, 0)
    const confirmedConversions = mockConversions.filter(c => c.status === 'confirmed')
    const averageTicket = confirmedConversions.length > 0 ? totalValue / confirmedConversions.length : 0
    
    stats.value = {
      total: mockConversions.length,
      totalValue: totalValue,
      pending: mockConversions.filter(c => c.status === 'pending').length,
      averageTicket: averageTicket
    }
    loading.value = false
  }, 1000)
}

const getInitials = (name) => {
  return name.split(' ').map(n => n[0]).join('').toUpperCase()
}

const getPaymentMethodClass = (method) => {
  const classes = {
    pix: 'bg-green-100 text-green-800',
    cartao: 'bg-blue-100 text-blue-800',
    boleto: 'bg-yellow-100 text-yellow-800',
    dinheiro: 'bg-purple-100 text-purple-800',
    transferencia: 'bg-indigo-100 text-indigo-800'
  }
  return classes[method] || classes.pix
}

const getPaymentMethodLabel = (method) => {
  const labels = {
    pix: '💚 PIX',
    cartao: '💳 Cartão',
    boleto: '📄 Boleto',
    dinheiro: '💵 Dinheiro',
    transferencia: '🏦 Transferência'
  }
  return labels[method] || labels.pix
}

const getStatusClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800',
    confirmed: 'bg-green-100 text-green-800',
    cancelled: 'bg-red-100 text-red-800'
  }
  return classes[status] || classes.pending
}

const getStatusLabel = (status) => {
  const labels = {
    pending: '⏳ Pendente',
    confirmed: '✅ Confirmada',
    cancelled: '❌ Cancelada'
  }
  return labels[status] || labels.pending
}

const getDetectionClass = (type) => {
  const classes = {
    manual: 'bg-blue-100 text-blue-800',
    nlp: 'bg-purple-100 text-purple-800'
  }
  return classes[type] || classes.manual
}

const getDetectionLabel = (type) => {
  const labels = {
    manual: '👤 Manual',
    nlp: '🤖 NLP'
  }
  return labels[type] || labels.manual
}

const formatDate = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('pt-BR') + ' ' + date.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })
}

const viewConversion = (conversion) => {
  alert(`Detalhes da conversão:\n\nCliente: ${conversion.client_name}\nValor: R$ ${conversion.value.toLocaleString()}\nMétodo: ${conversion.payment_method}\nStatus: ${conversion.status}\nObservações: ${conversion.notes}`)
}

const editConversion = (conversion) => {
  editingConversion.value = conversion
  conversionForm.value = {
    client_id: conversion.client_id || '',
    value: conversion.value,
    payment_method: conversion.payment_method,
    status: conversion.status,
    notes: conversion.notes || '',
    detection_type: conversion.detection_type
  }
  showCreateModal.value = true
}

const confirmConversion = (conversion) => {
  conversion.status = 'confirmed'
  alert('Conversão confirmada com sucesso!')
}

const cancelConversion = (conversion) => {
  if (confirm('Tem certeza que deseja cancelar esta conversão?')) {
    conversion.status = 'cancelled'
    alert('Conversão cancelada!')
  }
}

const saveConversion = () => {
  if (!conversionForm.value.client_id || !conversionForm.value.value) {
    alert('Preencha os campos obrigatórios')
    return
  }

  const client = clients.value.find(c => c.id == conversionForm.value.client_id)
  
  if (editingConversion.value) {
    // Atualizar conversão existente
    const index = conversions.value.findIndex(c => c.id === editingConversion.value.id)
    if (index > -1) {
      conversions.value[index] = {
        ...conversions.value[index],
        client_name: client.name,
        phone: client.phone,
        value: parseFloat(conversionForm.value.value),
        payment_method: conversionForm.value.payment_method,
        status: conversionForm.value.status,
        notes: conversionForm.value.notes,
        detection_type: conversionForm.value.detection_type
      }
    }
    alert('Conversão atualizada com sucesso!')
  } else {
    // Criar nova conversão
    const newConversion = {
      id: conversions.value.length + 1,
      client_name: client.name,
      phone: client.phone,
      value: parseFloat(conversionForm.value.value),
      currency: 'BRL',
      payment_method: conversionForm.value.payment_method,
      status: conversionForm.value.status,
      detection_type: conversionForm.value.detection_type,
      detected_at: new Date().toISOString(),
      notes: conversionForm.value.notes
    }
    
    conversions.value.unshift(newConversion)
    
    // Atualizar stats
    stats.value.total++
    if (newConversion.status === 'pending') {
      stats.value.pending++
    }
    
    alert('Conversão registrada com sucesso!')
  }
  
  closeModal()
}

const closeModal = () => {
  showCreateModal.value = false
  editingConversion.value = null
  conversionForm.value = {
    client_id: '',
    value: '',
    payment_method: 'pix',
    status: 'pending',
    notes: '',
    detection_type: 'manual'
  }
}

// Lifecycle
onMounted(() => {
  loadConversions()
})
</script>

