<template>
  <div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      <div class="text-center">
        <div class="text-6xl mb-4">⏰</div>
        <h2 class="text-3xl font-bold text-gray-900 mb-2">Trial Expirado</h2>
        <p class="text-lg text-gray-600 mb-8">Seu período de teste gratuito de 7 dias chegou ao fim</p>
      </div>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
      <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
        <div class="text-center mb-8">
          <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <span class="text-yellow-600 text-2xl">💰</span>
          </div>
          <h3 class="text-xl font-semibold text-gray-900 mb-2">Continue usando o VisionMetrics</h3>
          <p class="text-gray-600">Escolha um plano que se adapte às suas necessidades</p>
        </div>

        <!-- Planos -->
        <div class="space-y-4 mb-8">
          <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition-colors">
            <div class="flex justify-between items-start">
              <div>
                <h4 class="font-semibold text-gray-900">Plano Básico</h4>
                <p class="text-sm text-gray-600">Até 100 conversas/mês</p>
              </div>
              <div class="text-right">
                <div class="text-2xl font-bold text-gray-900">R$ 97</div>
                <div class="text-sm text-gray-500">/mês</div>
              </div>
            </div>
          </div>

          <div class="border-2 border-blue-500 rounded-lg p-4 bg-blue-50">
            <div class="flex justify-between items-start">
              <div>
                <h4 class="font-semibold text-gray-900">Plano Profissional</h4>
                <p class="text-sm text-gray-600">Até 1.000 conversas/mês</p>
                <span class="inline-flex px-2 py-1 text-xs font-semibold bg-blue-100 text-blue-800 rounded-full mt-1">
                  Mais Popular
                </span>
              </div>
              <div class="text-right">
                <div class="text-2xl font-bold text-gray-900">R$ 197</div>
                <div class="text-sm text-gray-500">/mês</div>
              </div>
            </div>
          </div>

          <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition-colors">
            <div class="flex justify-between items-start">
              <div>
                <h4 class="font-semibold text-gray-900">Plano Empresarial</h4>
                <p class="text-sm text-gray-600">Conversas ilimitadas</p>
              </div>
              <div class="text-right">
                <div class="text-2xl font-bold text-gray-900">R$ 397</div>
                <div class="text-sm text-gray-500">/mês</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Botões de Ação -->
        <div class="space-y-4">
          <button @click="choosePlan" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
            💳 Escolher Plano
          </button>
          
          <button @click="contactWhatsApp" class="w-full flex justify-center py-3 px-4 border border-green-300 rounded-md shadow-sm text-sm font-medium text-green-700 bg-green-50 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
            💬 Falar no WhatsApp
          </button>
          
          <button @click="extendTrial" class="w-full flex justify-center py-3 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
            ⏰ Solicitar Extensão
          </button>
        </div>

        <!-- Informações Adicionais -->
        <div class="mt-8 text-center">
          <p class="text-sm text-gray-500 mb-4">
            Precisa de ajuda? Nossa equipe está aqui para você!
          </p>
          
          <div class="space-y-2 text-sm text-gray-600">
            <div class="flex items-center justify-center">
              <span class="mr-2">📧</span>
              <span>contato@visionmetrics.com</span>
            </div>
            <div class="flex items-center justify-center">
              <span class="mr-2">📱</span>
              <span>+55 11 99999-9999</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de Extensão de Trial -->
    <div v-if="showExtensionModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4">
        <h3 class="text-lg font-medium text-gray-900 mb-4">⏰ Solicitar Extensão de Trial</h3>
        
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Motivo da Extensão</label>
            <textarea v-model="extensionForm.reason" placeholder="Explique por que precisa de mais tempo para testar..." rows="4"
                      class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Dias Adicionais</label>
            <select v-model="extensionForm.days" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
              <option value="3">3 dias</option>
              <option value="7">7 dias</option>
              <option value="14">14 dias</option>
            </select>
          </div>
        </div>
        
        <div class="flex justify-end space-x-3 mt-6">
          <button @click="closeExtensionModal" 
                  class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
            Cancelar
          </button>
          <button @click="submitExtension" 
                  class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
            Enviar Solicitação
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

// Estados
const showExtensionModal = ref(false)
const extensionForm = ref({
  reason: '',
  days: '7'
})

// Métodos
const choosePlan = () => {
  alert('Redirecionando para página de planos...')
}

const contactWhatsApp = () => {
  const message = encodeURIComponent('Olá! Gostaria de contratar o VisionMetrics. Preciso estender meu período de teste.')
  const phone = '5511999999999'
  window.open(`https://wa.me/${phone}?text=${message}`, '_blank')
}

const extendTrial = () => {
  showExtensionModal.value = true
}

const submitExtension = () => {
  if (!extensionForm.value.reason.trim()) {
    alert('Por favor, explique o motivo da extensão')
    return
  }
  
  alert(`Solicitação de extensão de ${extensionForm.value.days} dias enviada! Nossa equipe entrará em contato em breve.`)
  closeExtensionModal()
}

const closeExtensionModal = () => {
  showExtensionModal.value = false
  extensionForm.value = {
    reason: '',
    days: '7'
  }
}
</script>

