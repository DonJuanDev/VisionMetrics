<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200 fixed top-0 left-0 right-0 z-40">
      <div class="flex justify-between items-center py-4 px-4 sm:px-6 lg:px-8">
        <div class="flex items-center">
          <button
            @click="toggleSidebar"
            class="lg:hidden p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500"
          >
            <Bars3Icon class="h-6 w-6" />
          </button>
          <h1 class="ml-2 text-2xl font-bold text-gray-900">VisionMetrics</h1>
          <div class="ml-4 px-3 py-1 bg-blue-100 text-blue-800 text-sm font-medium rounded-full">
            Trial: {{ remainingDays }} dias restantes
          </div>
        </div>
        <div class="flex items-center space-x-4">
          <span class="text-sm text-gray-700">{{ user?.name }}</span>
          <button @click="logout" class="btn-outline text-sm">
            Sair
          </button>
        </div>
      </div>
    </header>

    <div class="flex pt-16">
      <!-- Sidebar -->
      <nav 
        :class="[
          'fixed inset-y-0 left-0 z-30 w-64 bg-gray-800 transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0',
          sidebarOpen ? 'translate-x-0' : '-translate-x-full'
        ]"
      >
        <div class="flex flex-col h-full">
          <div class="flex-1 flex flex-col min-h-0 pt-16 lg:pt-0">
            <div class="flex-1 flex flex-col pt-5 pb-4 overflow-y-auto">
              <div class="flex-1 px-3 space-y-1">
                <router-link 
                  v-for="item in navigation" 
                  :key="item.name"
                  :to="item.to"
                  @click="closeSidebarOnMobile"
                  :class="[
                    $route.path === item.to || $route.path.startsWith(item.activePrefix || item.to)
                      ? 'bg-gray-900 text-white' 
                      : 'text-gray-300 hover:bg-gray-700 hover:text-white',
                    'group flex items-center px-2 py-2 text-sm font-medium rounded-md'
                  ]"
                >
                  <span class="mr-3 text-lg">{{ item.icon }}</span>
                  {{ item.name }}
                </router-link>
              </div>
            </div>
          </div>
        </div>
      </nav>

      <!-- Overlay para mobile -->
      <div 
        v-if="sidebarOpen" 
        @click="closeSidebar"
        class="fixed inset-0 z-20 bg-gray-600 bg-opacity-75 lg:hidden"
      ></div>

      <!-- Main Content -->
      <main class="flex-1 min-w-0">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { Bars3Icon } from '@heroicons/vue/24/outline'

const route = useRoute()
const authStore = useAuthStore()

const sidebarOpen = ref(false)

const user = computed(() => authStore.user)
const remainingDays = computed(() => authStore.remainingTrialDays)

const navigation = computed(() => {
  const baseNav = [
    { name: 'Dashboard', to: '/dashboard', icon: '📊' },
    { name: 'Conversas', to: '/conversas', icon: '💬', activePrefix: '/conversas' },
    { name: 'Leads', to: '/leads', icon: '👥' },
    { name: 'Links Rastreáveis', to: '/links-rastreaveis', icon: '🔗' },
    { name: 'Conversões', to: '/conversoes', icon: '💰' },
    { name: 'Relatórios', to: '/relatorios', icon: '📈' },
    { name: 'Webhooks', to: '/webhooks', icon: '🔌' },
    { name: 'Configurações', to: '/configuracoes', icon: '⚙️' },
  ]

  // Adicionar menu de usuários se tiver permissão
  if (authStore.canManageUsers) {
    baseNav.push({ name: 'Usuários', to: '/usuarios', icon: '👤' })
  }

  // Adicionar menus de admin se for super admin
  if (authStore.isSuperAdmin) {
    baseNav.push(
      { name: 'Admin Dashboard', to: '/admin/dashboard', icon: '🛠️' },
      { name: 'Empresas', to: '/admin/empresas', icon: '🏢' },
      { name: 'Admin Usuários', to: '/admin/usuarios', icon: '👥' }
    )
  }

  return baseNav
})

const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value
}

const closeSidebar = () => {
  sidebarOpen.value = false
}

const closeSidebarOnMobile = () => {
  if (window.innerWidth < 1024) {
    closeSidebar()
  }
}

const logout = async () => {
  await authStore.logout()
}

// Fechar sidebar quando a tela for redimensionada
const handleResize = () => {
  if (window.innerWidth >= 1024) {
    sidebarOpen.value = false
  }
}

onMounted(() => {
  window.addEventListener('resize', handleResize)
})

onUnmounted(() => {
  window.removeEventListener('resize', handleResize)
})
</script>

