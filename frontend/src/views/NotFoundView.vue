<template>
  <div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      <div class="text-center">
        <div class="text-8xl mb-4">🔍</div>
        <h1 class="text-4xl font-bold text-gray-900 mb-2">404</h1>
        <h2 class="text-2xl font-semibold text-gray-700 mb-4">Página não encontrada</h2>
        <p class="text-lg text-gray-600 mb-8">
          A página que você está procurando não existe ou foi movida.
        </p>
      </div>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
      <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
        <div class="text-center">
          <h3 class="text-lg font-medium text-gray-900 mb-4">O que você pode fazer?</h3>
          
          <div class="space-y-4">
            <button @click="goHome" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
              🏠 Ir para o Dashboard
            </button>
            
            <button @click="goBack" class="w-full flex justify-center py-3 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
              ⬅️ Voltar
            </button>
            
            <button @click="contactSupport" class="w-full flex justify-center py-3 px-4 border border-green-300 rounded-md shadow-sm text-sm font-medium text-green-700 bg-green-50 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
              💬 Falar com Suporte
            </button>
          </div>
        </div>

        <!-- Links Úteis -->
        <div class="mt-8 pt-6 border-t border-gray-200">
          <h4 class="text-sm font-medium text-gray-900 mb-3">Links Úteis</h4>
          <div class="grid grid-cols-2 gap-2 text-sm">
            <a href="/dashboard" class="text-blue-600 hover:text-blue-900 flex items-center">
              <span class="mr-1">📊</span>
              Dashboard
            </a>
            <a href="/conversas" class="text-blue-600 hover:text-blue-900 flex items-center">
              <span class="mr-1">💬</span>
              Conversas
            </a>
            <a href="/leads" class="text-blue-600 hover:text-blue-900 flex items-center">
              <span class="mr-1">👥</span>
              Leads
            </a>
            <a href="/relatorios" class="text-blue-600 hover:text-blue-900 flex items-center">
              <span class="mr-1">📊</span>
              Relatórios
            </a>
            <a href="/configuracoes" class="text-blue-600 hover:text-blue-900 flex items-center">
              <span class="mr-1">⚙️</span>
              Configurações
            </a>
            <a href="/perfil" class="text-blue-600 hover:text-blue-900 flex items-center">
              <span class="mr-1">👤</span>
              Perfil
            </a>
          </div>
        </div>

        <!-- Informações de Contato -->
        <div class="mt-6 text-center">
          <p class="text-sm text-gray-500 mb-2">
            Ainda precisa de ajuda?
          </p>
          
          <div class="space-y-1 text-sm text-gray-600">
            <div class="flex items-center justify-center">
              <span class="mr-2">📧</span>
              <span>suporte@visionmetrics.com</span>
            </div>
            <div class="flex items-center justify-center">
              <span class="mr-2">📱</span>
              <span>+55 11 99999-9999</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'

const router = useRouter()

// Métodos
const goHome = () => {
  router.push('/dashboard')
}

const goBack = () => {
  if (window.history.length > 1) {
    window.history.back()
  } else {
    router.push('/dashboard')
  }
}

const contactSupport = () => {
  const message = encodeURIComponent('Olá! Preciso de ajuda com o VisionMetrics. Encontrei um erro 404.')
  const phone = '5511999999999'
  window.open(`https://wa.me/${phone}?text=${message}`, '_blank')
}
</script>

