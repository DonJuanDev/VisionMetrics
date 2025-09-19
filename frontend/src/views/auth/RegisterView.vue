<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl w-full space-y-8">
      <div>
        <div class="mx-auto h-12 w-auto flex justify-center">
          <h1 class="text-3xl font-bold text-gray-900">VisionMetrics</h1>
        </div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
          Registre sua empresa
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          <span class="font-medium text-primary-600">7 dias grátis</span> para transformar suas conversas WhatsApp em vendas rastreáveis
        </p>
        <p class="mt-1 text-center text-sm text-gray-600">
          Já tem conta?
          <router-link to="/login" class="font-medium text-primary-600 hover:text-primary-500">
            Faça login aqui
          </router-link>
        </p>
      </div>
      
      <form class="mt-8 space-y-6" @submit.prevent="handleSubmit">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Dados da Empresa -->
          <div class="space-y-4">
            <h3 class="text-lg font-medium text-gray-900">Dados da Empresa</h3>
            
            <div>
              <label for="company_name" class="form-label">Nome da Empresa *</label>
              <input
                id="company_name"
                v-model="form.company_name"
                type="text"
                required
                class="form-input"
                placeholder="Ex: Minha Empresa Ltda"
              >
            </div>

            <div>
              <label for="cnpj" class="form-label">CNPJ (opcional)</label>
              <input
                id="cnpj"
                v-model="form.cnpj"
                type="text"
                class="form-input"
                placeholder="00.000.000/0000-00"
              >
            </div>

            <div>
              <label for="phone" class="form-label">WhatsApp da Empresa *</label>
              <input
                id="phone"
                v-model="form.phone"
                type="tel"
                required
                class="form-input"
                placeholder="(11) 99999-9999"
              >
              <p class="form-help">Este será o número usado para receber mensagens</p>
            </div>
          </div>

          <!-- Dados do Administrador -->
          <div class="space-y-4">
            <h3 class="text-lg font-medium text-gray-900">Administrador</h3>
            
            <div>
              <label for="admin_name" class="form-label">Nome Completo *</label>
              <input
                id="admin_name"
                v-model="form.admin_name"
                type="text"
                required
                class="form-input"
                placeholder="João Silva"
              >
            </div>

            <div>
              <label for="admin_email" class="form-label">Email *</label>
              <input
                id="admin_email"
                v-model="form.admin_email"
                type="email"
                required
                class="form-input"
                placeholder="joao@empresa.com"
              >
            </div>

            <div>
              <label for="password" class="form-label">Senha *</label>
              <input
                id="password"
                v-model="form.password"
                type="password"
                required
                minlength="8"
                autocomplete="new-password"
                class="form-input"
                placeholder="Mínimo 8 caracteres"
              >
            </div>

            <div>
              <label for="password_confirmation" class="form-label">Confirmar Senha *</label>
              <input
                id="password_confirmation"
                v-model="form.password_confirmation"
                type="password"
                required
                autocomplete="new-password"
                class="form-input"
                placeholder="Digite a senha novamente"
              >
            </div>
          </div>
        </div>

        <!-- Termos e Condições -->
        <div class="flex items-center">
          <input
            id="terms_accepted"
            v-model="form.terms_accepted"
            type="checkbox"
            required
            class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
          >
          <label for="terms_accepted" class="ml-2 block text-sm text-gray-900">
            Aceito os <a href="#" class="text-primary-600 hover:text-primary-500">Termos de Uso</a> e 
            <a href="#" class="text-primary-600 hover:text-primary-500">Política de Privacidade</a>
          </label>
        </div>

        <!-- Trial Info -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
          <div class="flex items-start">
            <div class="flex-shrink-0">
              <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
              </svg>
            </div>
            <div class="ml-3">
              <h3 class="text-sm font-medium text-blue-800">Trial Gratuito de 7 Dias</h3>
              <div class="mt-2 text-sm text-blue-700">
                <ul class="list-disc pl-5 space-y-1">
                  <li>Acesso completo a todas as funcionalidades</li>
                  <li>Rastreamento ilimitado de conversas</li>
                  <li>Dashboard completo com gráficos</li>
                  <li>Integração WhatsApp + Meta Ads + Google Ads</li>
                  <li>Após o trial: entre em contato para continuar usando</li>
                </ul>
              </div>
            </div>
          </div>
        </div>

        <div>
          <button
            type="submit"
            :disabled="loading || !form.terms_accepted"
            class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50"
          >
            <span v-if="loading" class="spinner w-4 h-4 mr-2"></span>
            {{ loading ? 'Criando conta...' : 'Criar Conta Grátis' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const loading = ref(false)
const form = reactive({
  company_name: '',
  admin_name: '',
  admin_email: '',
  phone: '',
  password: '',
  password_confirmation: '',
  cnpj: '',
  terms_accepted: false
})

const handleSubmit = async () => {
  if (form.password !== form.password_confirmation) {
    alert('As senhas não conferem')
    return
  }

  loading.value = true
  
  const result = await authStore.register(form)
  
  if (result.success) {
    router.push({ name: 'Dashboard' })
  }
  
  loading.value = false
}
</script>
