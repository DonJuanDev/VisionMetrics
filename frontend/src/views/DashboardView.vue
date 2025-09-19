<template>
  <div class="p-8">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <div class="stat-card">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                  <span class="text-white text-sm">💬</span>
                </div>
              </div>
              <div class="ml-4 w-full">
                <p class="text-sm text-gray-500">Total Conversas</p>
                <p class="text-2xl font-bold text-gray-900">{{ stats.conversations?.total || 0 }}</p>
                <div class="flex items-center mt-1">
                  <span class="text-sm text-green-600">+{{ stats.conversations?.this_month || 0 }}</span>
                  <span class="text-xs text-gray-500 ml-1">este mês</span>
                </div>
              </div>
            </div>
          </div>

          <div class="stat-card">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center">
                  <span class="text-white text-sm">👥</span>
                </div>
              </div>
              <div class="ml-4 w-full">
                <p class="text-sm text-gray-500">Leads Rastreados</p>
                <p class="text-2xl font-bold text-gray-900">{{ stats.leads?.tracked || 0 }}</p>
                <div class="flex items-center mt-1">
                  <span class="text-sm text-blue-600">{{ stats.leads?.tracking_rate || 0 }}%</span>
                  <span class="text-xs text-gray-500 ml-1">taxa rastreamento</span>
                </div>
              </div>
            </div>
          </div>

          <div class="stat-card">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-purple-500 rounded-lg flex items-center justify-center">
                  <span class="text-white text-sm">💰</span>
                </div>
              </div>
              <div class="ml-4 w-full">
                <p class="text-sm text-gray-500">Conversões</p>
                <p class="text-2xl font-bold text-gray-900">{{ stats.conversions?.total || 0 }}</p>
                <div class="flex items-center mt-1">
                  <span class="text-sm text-green-600">{{ stats.conversions?.conversion_rate || 0 }}%</span>
                  <span class="text-xs text-gray-500 ml-1">taxa conversão</span>
                </div>
              </div>
            </div>
          </div>

          <div class="stat-card">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-yellow-500 rounded-lg flex items-center justify-center">
                  <span class="text-white text-sm">💵</span>
                </div>
              </div>
              <div class="ml-4 w-full">
                <p class="text-sm text-gray-500">Receita Total</p>
                <p class="text-2xl font-bold text-gray-900">{{ stats.revenue?.formatted_total || 'R$ 0,00' }}</p>
                <div class="flex items-center mt-1">
                  <span class="text-sm text-green-600">{{ stats.revenue?.formatted_this_month || 'R$ 0,00' }}</span>
                  <span class="text-xs text-gray-500 ml-1">este mês</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <!-- Origem Distribution -->
          <div class="card">
            <div class="card-header">
              <h3 class="text-lg font-semibold text-gray-900">Conversas Rastreadas vs Não Rastreadas</h3>
            </div>
            <div class="card-body">
              <div class="h-64 flex items-center justify-center">
                <div class="space-y-4">
                  <div class="flex items-center space-x-4">
                    <div class="w-4 h-4 bg-blue-500 rounded"></div>
                    <span class="text-sm">Meta Ads: {{ originStats.meta || 0 }}</span>
                  </div>
                  <div class="flex items-center space-x-4">
                    <div class="w-4 h-4 bg-green-500 rounded"></div>
                    <span class="text-sm">Google Ads: {{ originStats.google || 0 }}</span>
                  </div>
                  <div class="flex items-center space-x-4">
                    <div class="w-4 h-4 bg-gray-500 rounded"></div>
                    <span class="text-sm">Outras: {{ originStats.outras || 0 }}</span>
                  </div>
                  <div class="flex items-center space-x-4">
                    <div class="w-4 h-4 bg-orange-500 rounded"></div>
                    <span class="text-sm">Não Rastreada: {{ originStats.nao_rastreada || 0 }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Recent Activity -->
          <div class="card">
            <div class="card-header">
              <h3 class="text-lg font-semibold text-gray-900">Atividade Recente</h3>
            </div>
            <div class="card-body">
              <div class="space-y-4">
                <div v-for="activity in recentActivity" :key="activity.id" class="flex items-center space-x-3">
                  <div class="flex-shrink-0 w-2 h-2 bg-blue-500 rounded-full"></div>
                  <div class="min-w-0 flex-1">
                    <p class="text-sm text-gray-900">{{ activity.title }}</p>
                    <p class="text-xs text-gray-500">{{ activity.time }}</p>
                  </div>
                </div>
                <div v-if="recentActivity.length === 0" class="text-center py-8">
                  <p class="text-gray-500">Nenhuma atividade recente</p>
                  <p class="text-sm text-gray-400 mt-1">Configure o WhatsApp para começar a receber conversas</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="mt-8">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Ações Rápidas</h3>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <router-link to="/links-rastreaveis" class="card hover:shadow-lg transition-shadow cursor-pointer">
              <div class="card-body text-center">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mx-auto mb-3">
                  <span class="text-2xl">🔗</span>
                </div>
                <h4 class="font-medium text-gray-900">Criar Link Rastreável</h4>
                <p class="text-sm text-gray-500 mt-1">Gere links para suas campanhas</p>
              </div>
            </router-link>

            <router-link to="/configuracoes" class="card hover:shadow-lg transition-shadow cursor-pointer">
              <div class="card-body text-center">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mx-auto mb-3">
                  <span class="text-2xl">📱</span>
                </div>
                <h4 class="font-medium text-gray-900">Configurar WhatsApp</h4>
                <p class="text-sm text-gray-500 mt-1">Conecte seu WhatsApp Business</p>
              </div>
            </router-link>

            <router-link to="/relatorios" class="card hover:shadow-lg transition-shadow cursor-pointer">
              <div class="card-body text-center">
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mx-auto mb-3">
                  <span class="text-2xl">📊</span>
                </div>
                <h4 class="font-medium text-gray-900">Ver Relatórios</h4>
                <p class="text-sm text-gray-500 mt-1">Analise suas métricas</p>
              </div>
            </router-link>
          </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { apiMethods } from '@/services/api'

const authStore = useAuthStore()

const stats = ref({
  conversations: { total: 0, this_month: 0 },
  leads: { tracked: 0, tracking_rate: 0 },
  conversions: { total: 0, conversion_rate: 0 },
  revenue: { formatted_total: 'R$ 0,00', formatted_this_month: 'R$ 0,00' }
})

const originStats = ref({
  meta: 0,
  google: 0,
  outras: 0,
  nao_rastreada: 0
})

const recentActivity = ref([
  { id: 1, title: 'Sistema configurado com sucesso', time: 'Agora' },
  { id: 2, title: 'Trial de 7 dias iniciado', time: 'Agora' }
])

const loadDashboard = async () => {
  try {
    const response = await apiMethods.getDashboardStats()
    stats.value = response.data
  } catch (error) {
    console.error('Erro ao carregar dashboard:', error)
  }
}


onMounted(() => {
  loadDashboard()
})
</script>
