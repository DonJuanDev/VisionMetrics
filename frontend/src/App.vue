<template>
  <div id="app">
    <!-- Layout Wrapper -->
    <component :is="layoutComponent">
      <router-view />
    </component>
    
    <!-- Loading overlay -->
    <div
      v-if="isLoading"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    >
      <div class="bg-white rounded-lg p-6 flex items-center space-x-3">
        <div class="spinner w-5 h-5"></div>
        <span class="text-gray-700">Carregando...</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useAppStore } from '@/stores/app'
import Layout from '@/components/Layout.vue'

const route = useRoute()
const authStore = useAuthStore()
const appStore = useAppStore()

const isLoading = computed(() => appStore.isLoading)

// Determinar qual layout usar baseado na rota
const layoutComponent = computed(() => {
  const layoutType = route.meta?.layout
  
  switch (layoutType) {
    case 'auth':
      return 'div' // Layout simples para páginas de autenticação
    case 'minimal':
      return 'div' // Layout mínimo para páginas como 404, trial expirado
    default:
      // Layout padrão com sidebar para páginas autenticadas
      return authStore.isAuthenticated ? Layout : 'div'
  }
})

onMounted(async () => {
  // Verificar se há token armazenado e tentar restaurar sessão
  const token = localStorage.getItem('auth_token')
  if (token) {
    await authStore.checkAuth()
  }
})
</script>
