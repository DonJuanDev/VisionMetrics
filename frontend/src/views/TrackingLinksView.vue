<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-6">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">🔗 Links Rastreáveis</h1>
            <p class="text-sm text-gray-600">Gerencie links de rastreamento e atribuição de origem</p>
          </div>
          <div class="flex items-center space-x-4">
            <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
              📊 Relatório
            </button>
            <button @click="showCreateModal = true" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
              ➕ Criar Link
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
              <option value="active">Ativo</option>
              <option value="inactive">Inativo</option>
              <option value="expired">Expirado</option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Campanha</label>
            <select v-model="filters.campaign" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
              <option value="">Todas</option>
              <option value="meta_ads">Meta Ads</option>
              <option value="google_ads">Google Ads</option>
              <option value="email_marketing">Email Marketing</option>
              <option value="social_media">Redes Sociais</option>
              <option value="outros">Outros</option>
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
              placeholder="Nome, URL ou descrição..."
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
                <span class="text-blue-600 text-lg">🔗</span>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Total de Links</p>
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
              <p class="text-sm font-medium text-gray-600">Ativos</p>
              <p class="text-2xl font-bold text-gray-900">{{ stats.active }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                <span class="text-purple-600 text-lg">👁️</span>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Total Cliques</p>
              <p class="text-2xl font-bold text-gray-900">{{ stats.clicks }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                <span class="text-yellow-600 text-lg">💰</span>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Conversões</p>
              <p class="text-2xl font-bold text-gray-900">{{ stats.conversions }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Lista de Links -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-medium text-gray-900">Links Rastreáveis</h3>
        </div>
        
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Link
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Destino
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Campanha
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Status
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Cliques
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Conversões
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Criado
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Ações
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="link in filteredLinks" :key="link.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0">
                      <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                        <span class="text-blue-600 text-sm">🔗</span>
                      </div>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">{{ link.name }}</div>
                      <div class="text-xs text-blue-600 font-mono">{{ link.short_url }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900 truncate max-w-xs">{{ link.destination_url }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getCampaignClass(link.campaign_type)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                    {{ getCampaignLabel(link.campaign_type) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getStatusClass(link.status)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                    {{ getStatusLabel(link.status) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ link.clicks.toLocaleString() }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ link.conversions }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatDate(link.created_at) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <div class="flex space-x-2">
                    <button @click="copyLink(link)" class="text-blue-600 hover:text-blue-900" title="Copiar Link">
                      📋
                    </button>
                    <button @click="showQRCode(link)" class="text-purple-600 hover:text-purple-900" title="QR Code">
                      📱
                    </button>
                    <button @click="viewStats(link)" class="text-green-600 hover:text-green-900" title="Estatísticas">
                      📊
                    </button>
                    <button @click="editLink(link)" class="text-indigo-600 hover:text-indigo-900" title="Editar">
                      ✏️
                    </button>
                    <button @click="toggleStatus(link)" :class="link.status === 'active' ? 'text-red-600 hover:text-red-900' : 'text-green-600 hover:text-green-900'" :title="link.status === 'active' ? 'Desativar' : 'Ativar'">
                      {{ link.status === 'active' ? '⏸️' : '▶️' }}
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <!-- Empty State -->
        <div v-if="filteredLinks.length === 0" class="text-center py-12">
          <div class="text-gray-400 text-6xl mb-4">🔗</div>
          <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum link encontrado</h3>
          <p class="text-gray-500 mb-4">{{ loading ? 'Carregando links...' : 'Não há links com os filtros selecionados.' }}</p>
          <button @click="showCreateModal = true" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
            ➕ Criar Primeiro Link
          </button>
        </div>
      </div>
    </div>

    <!-- Modal de Criar/Editar Link -->
    <div v-if="showCreateModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-2xl max-h-screen overflow-y-auto">
        <h3 class="text-lg font-medium text-gray-900 mb-6">🔗 {{ editingLink ? 'Editar Link' : 'Criar Novo Link' }}</h3>
        
        <div class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Nome do Link *</label>
              <input v-model="linkForm.name" type="text" placeholder="Ex: Campanha Black Friday" 
                     class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Campanha *</label>
              <select v-model="linkForm.campaign_type" 
                      class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="meta_ads">Meta Ads</option>
                <option value="google_ads">Google Ads</option>
                <option value="email_marketing">Email Marketing</option>
                <option value="social_media">Redes Sociais</option>
                <option value="outros">Outros</option>
              </select>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">URL de Destino *</label>
            <input v-model="linkForm.destination_url" type="url" placeholder="https://exemplo.com/landing-page" 
                   class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">UTM Source</label>
              <input v-model="linkForm.utm_source" type="text" placeholder="facebook" 
                     class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">UTM Medium</label>
              <input v-model="linkForm.utm_medium" type="text" placeholder="cpc" 
                     class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">UTM Campaign</label>
              <input v-model="linkForm.utm_campaign" type="text" placeholder="black-friday-2024" 
                     class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Descrição</label>
            <textarea v-model="linkForm.description" placeholder="Descrição opcional do link..." rows="3"
                      class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
          </div>

          <div class="flex items-center">
            <input v-model="linkForm.expires_at_enabled" type="checkbox" id="expires" 
                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
            <label for="expires" class="ml-2 block text-sm text-gray-900">
              Definir data de expiração
            </label>
          </div>

          <div v-if="linkForm.expires_at_enabled">
            <label class="block text-sm font-medium text-gray-700 mb-2">Data de Expiração</label>
            <input v-model="linkForm.expires_at" type="datetime-local" 
                   class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>

          <div v-if="linkForm.short_url" class="bg-green-50 border border-green-200 rounded-lg p-4">
            <h4 class="text-sm font-medium text-green-900 mb-2">🎉 Link Gerado:</h4>
            <div class="flex items-center space-x-2">
              <input :value="linkForm.short_url" readonly 
                     class="flex-1 bg-white border border-green-300 rounded-md px-3 py-2 text-sm font-mono">
              <button @click="copyToClipboard(linkForm.short_url)" 
                      class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-md text-sm">
                📋 Copiar
              </button>
            </div>
          </div>
        </div>
        
        <div class="flex justify-end space-x-3 mt-8">
          <button @click="closeModal" 
                  class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
            Cancelar
          </button>
          <button @click="saveLink" 
                  class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700">
            {{ editingLink ? 'Atualizar' : 'Criar Link' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Modal QR Code -->
    <div v-if="showQRModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-medium text-gray-900 mb-4 text-center">📱 QR Code</h3>
        
        <div class="text-center mb-4">
          <div class="inline-block p-4 bg-gray-100 rounded-lg">
            <!-- QR Code placeholder -->
            <div class="w-48 h-48 bg-white border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center">
              <div class="text-gray-500 text-center">
                <div class="text-4xl mb-2">📱</div>
                <div class="text-sm">QR Code</div>
                <div class="text-xs">{{ selectedLink?.name }}</div>
              </div>
            </div>
          </div>
        </div>
        
        <div class="text-center mb-4">
          <p class="text-sm text-gray-600 mb-2">{{ selectedLink?.short_url }}</p>
        </div>
        
        <div class="flex justify-center space-x-3">
          <button @click="showQRModal = false" 
                  class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
            Fechar
          </button>
          <button class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
            💾 Baixar QR
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
const links = ref([])
const showCreateModal = ref(false)
const showQRModal = ref(false)
const editingLink = ref(null)
const selectedLink = ref(null)

// Filtros
const filters = ref({
  status: '',
  campaign: '',
  period: 'month',
  search: ''
})

// Stats
const stats = ref({
  total: 0,
  active: 0,
  clicks: 0,
  conversions: 0
})

// Form
const linkForm = ref({
  name: '',
  destination_url: '',
  campaign_type: 'meta_ads',
  utm_source: '',
  utm_medium: '',
  utm_campaign: '',
  description: '',
  expires_at_enabled: false,
  expires_at: '',
  short_url: ''
})

// Dados de exemplo (simulados)
const mockLinks = [
  {
    id: 1,
    name: 'Campanha Black Friday',
    short_url: 'https://vm.ly/bf2024',
    destination_url: 'https://exemplo.com/black-friday',
    campaign_type: 'meta_ads',
    status: 'active',
    clicks: 1250,
    conversions: 45,
    created_at: '2024-01-15T10:30:00Z'
  },
  {
    id: 2,
    name: 'Email Newsletter',
    short_url: 'https://vm.ly/news01',
    destination_url: 'https://exemplo.com/newsletter',
    campaign_type: 'email_marketing',
    status: 'active',
    clicks: 890,
    conversions: 23,
    created_at: '2024-01-14T14:20:00Z'
  },
  {
    id: 3,
    name: 'Instagram Stories',
    short_url: 'https://vm.ly/ig2024',
    destination_url: 'https://exemplo.com/instagram-promo',
    campaign_type: 'social_media',
    status: 'inactive',
    clicks: 567,
    conversions: 12,
    created_at: '2024-01-13T09:15:00Z'
  },
  {
    id: 4,
    name: 'Google Ads Produto A',
    short_url: 'https://vm.ly/ga-prod-a',
    destination_url: 'https://exemplo.com/produto-a',
    campaign_type: 'google_ads',
    status: 'active',
    clicks: 2340,
    conversions: 78,
    created_at: '2024-01-12T16:45:00Z'
  }
]

// Links filtrados
const filteredLinks = computed(() => {
  let filtered = links.value

  if (filters.value.status) {
    filtered = filtered.filter(link => link.status === filters.value.status)
  }

  if (filters.value.campaign) {
    filtered = filtered.filter(link => link.campaign_type === filters.value.campaign)
  }

  if (filters.value.search) {
    const search = filters.value.search.toLowerCase()
    filtered = filtered.filter(link => 
      link.name.toLowerCase().includes(search) ||
      link.short_url.toLowerCase().includes(search) ||
      link.destination_url.toLowerCase().includes(search)
    )
  }

  return filtered
})

// Métodos
const loadLinks = async () => {
  loading.value = true
  
  // Simular chamada da API
  setTimeout(() => {
    links.value = mockLinks
    stats.value = {
      total: mockLinks.length,
      active: mockLinks.filter(l => l.status === 'active').length,
      clicks: mockLinks.reduce((sum, l) => sum + l.clicks, 0),
      conversions: mockLinks.reduce((sum, l) => sum + l.conversions, 0)
    }
    loading.value = false
  }, 1000)
}

const getCampaignClass = (campaign) => {
  const classes = {
    meta_ads: 'bg-blue-100 text-blue-800',
    google_ads: 'bg-green-100 text-green-800',
    email_marketing: 'bg-purple-100 text-purple-800',
    social_media: 'bg-pink-100 text-pink-800',
    outros: 'bg-gray-100 text-gray-800'
  }
  return classes[campaign] || classes.outros
}

const getCampaignLabel = (campaign) => {
  const labels = {
    meta_ads: '📘 Meta Ads',
    google_ads: '🟢 Google Ads',
    email_marketing: '📧 Email Marketing',
    social_media: '📱 Social Media',
    outros: '🔗 Outros'
  }
  return labels[campaign] || labels.outros
}

const getStatusClass = (status) => {
  const classes = {
    active: 'bg-green-100 text-green-800',
    inactive: 'bg-red-100 text-red-800',
    expired: 'bg-yellow-100 text-yellow-800'
  }
  return classes[status] || classes.active
}

const getStatusLabel = (status) => {
  const labels = {
    active: '✅ Ativo',
    inactive: '❌ Inativo',
    expired: '⏰ Expirado'
  }
  return labels[status] || labels.active
}

const formatDate = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('pt-BR')
}

const copyLink = async (link) => {
  try {
    await navigator.clipboard.writeText(link.short_url)
    alert(`Link copiado: ${link.short_url}`)
  } catch (err) {
    alert('Erro ao copiar link')
  }
}

const showQRCode = (link) => {
  selectedLink.value = link
  showQRModal.value = true
}

const viewStats = (link) => {
  alert(`Estatísticas de ${link.name}:\n\nCliques: ${link.clicks.toLocaleString()}\nConversões: ${link.conversions}\nTaxa de Conversão: ${((link.conversions / link.clicks) * 100).toFixed(2)}%`)
}

const editLink = (link) => {
  editingLink.value = link
  linkForm.value = {
    name: link.name,
    destination_url: link.destination_url,
    campaign_type: link.campaign_type,
    utm_source: '',
    utm_medium: '',
    utm_campaign: '',
    description: '',
    expires_at_enabled: false,
    expires_at: '',
    short_url: link.short_url
  }
  showCreateModal.value = true
}

const toggleStatus = (link) => {
  const newStatus = link.status === 'active' ? 'inactive' : 'active'
  link.status = newStatus
  alert(`Link ${newStatus === 'active' ? 'ativado' : 'desativado'} com sucesso!`)
}

const saveLink = () => {
  if (!linkForm.value.name || !linkForm.value.destination_url) {
    alert('Preencha os campos obrigatórios')
    return
  }

  if (editingLink.value) {
    // Atualizar link existente
    const index = links.value.findIndex(l => l.id === editingLink.value.id)
    if (index > -1) {
      links.value[index] = {
        ...links.value[index],
        name: linkForm.value.name,
        destination_url: linkForm.value.destination_url,
        campaign_type: linkForm.value.campaign_type
      }
    }
    alert('Link atualizado com sucesso!')
  } else {
    // Criar novo link
    const newLink = {
      id: links.value.length + 1,
      name: linkForm.value.name,
      short_url: `https://vm.ly/${Math.random().toString(36).substr(2, 8)}`,
      destination_url: linkForm.value.destination_url,
      campaign_type: linkForm.value.campaign_type,
      status: 'active',
      clicks: 0,
      conversions: 0,
      created_at: new Date().toISOString()
    }
    
    linkForm.value.short_url = newLink.short_url
    links.value.unshift(newLink)
    
    // Atualizar stats
    stats.value.total++
    stats.value.active++
    
    alert('Link criado com sucesso!')
  }
}

const closeModal = () => {
  showCreateModal.value = false
  editingLink.value = null
  linkForm.value = {
    name: '',
    destination_url: '',
    campaign_type: 'meta_ads',
    utm_source: '',
    utm_medium: '',
    utm_campaign: '',
    description: '',
    expires_at_enabled: false,
    expires_at: '',
    short_url: ''
  }
}

const copyToClipboard = async (text) => {
  try {
    await navigator.clipboard.writeText(text)
    alert('Link copiado para a área de transferência!')
  } catch (err) {
    alert('Erro ao copiar link')
  }
}

// Lifecycle
onMounted(() => {
  loadLinks()
})
</script>
