import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { apiMethods } from '@/services/api'
import router from '@/router'
import { useToast } from 'vue-toastification'

const toast = useToast()

export const useAuthStore = defineStore('auth', () => {
  // State
  const user = ref(null)
  const token = ref(localStorage.getItem('auth_token'))
  const company = ref(null)
  const permissions = ref({})

  // Getters
  const isAuthenticated = computed(() => !!token.value && !!user.value)
  
  const isTrialExpired = computed(() => {
    if (!company.value?.trial_expires_at) return false
    return new Date(company.value.trial_expires_at) < new Date()
  })
  
  const remainingTrialDays = computed(() => {
    if (!company.value?.trial_expires_at) return 0
    const expiresAt = new Date(company.value.trial_expires_at)
    const now = new Date()
    const diffTime = expiresAt - now
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
    return Math.max(0, diffDays)
  })

  const userRole = computed(() => user.value?.role || null)
  
  const isSuperAdmin = computed(() => userRole.value === 'super_admin')
  
  const isCompanyAdmin = computed(() => 
    ['super_admin', 'company_admin'].includes(userRole.value)
  )
  
  const canManageUsers = computed(() => 
    permissions.value.can_manage_users || false
  )
  
  const canViewReports = computed(() => 
    permissions.value.can_view_reports || false
  )

  // Actions
  const login = async (credentials) => {
    try {
      const response = await api.post('/login', credentials)
      const { user: userData, token: authToken, company: companyData } = response.data

      user.value = userData
      token.value = authToken
      company.value = companyData
      
      localStorage.setItem('auth_token', authToken)
      
      // Set default authorization header
      api.defaults.headers.common['Authorization'] = `Bearer ${authToken}`
      
      toast.success('Login realizado com sucesso!')
      
      return { success: true }
    } catch (error) {
      const message = error.response?.data?.message || 'Erro ao fazer login'
      toast.error(message)
      return { success: false, message }
    }
  }

  const register = async (data) => {
    try {
      const response = await api.post('/register-company', data)
      const { user: userData, token: authToken, company: companyData } = response.data

      user.value = userData
      token.value = authToken
      company.value = companyData
      
      localStorage.setItem('auth_token', authToken)
      api.defaults.headers.common['Authorization'] = `Bearer ${authToken}`
      
      toast.success('Empresa cadastrada com sucesso!')
      
      return { success: true }
    } catch (error) {
      const message = error.response?.data?.message || 'Erro ao cadastrar empresa'
      toast.error(message)
      return { success: false, message }
    }
  }

  const checkAuth = async () => {
    if (!token.value) {
      return false
    }

    try {
      const response = await apiMethods.checkAuth()
      const { user: userData, company: companyData, permissions: userPermissions } = response.data

      user.value = userData
      company.value = companyData
      permissions.value = userPermissions
      
      return true
    } catch (error) {
      // Token is invalid
      logout()
      return false
    }
  }

  const logout = async () => {
    try {
      if (token.value) {
        await apiMethods.logout()
      }
    } catch (error) {
      // Ignore logout errors
    } finally {
      // Clear local state
      user.value = null
      token.value = null
      company.value = null
      permissions.value = {}
      
      localStorage.removeItem('auth_token')
      
      router.push({ name: 'Login' })
      toast.info('Você foi desconectado')
    }
  }

  const updateUser = (userData) => {
    user.value = { ...user.value, ...userData }
  }

  const updateCompany = (companyData) => {
    company.value = { ...company.value, ...companyData }
  }

  const refreshToken = async () => {
    try {
      const response = await apiMethods.refreshToken()
      const { token: newToken } = response.data
      
      token.value = newToken
      localStorage.setItem('auth_token', newToken)
      
      return true
    } catch (error) {
      logout()
      return false
    }
  }

  const forgotPassword = async (email) => {
    try {
      await api.post('/forgot-password', { email })
      toast.success('Link de recuperação enviado para seu email!')
      return { success: true }
    } catch (error) {
      const message = error.response?.data?.message || 'Erro ao enviar link de recuperação'
      toast.error(message)
      return { success: false, message }
    }
  }

  const resetPassword = async (data) => {
    try {
      await api.post('/reset-password', data)
      toast.success('Senha alterada com sucesso!')
      return { success: true }
    } catch (error) {
      const message = error.response?.data?.message || 'Erro ao alterar senha'
      toast.error(message)
      return { success: false, message }
    }
  }

  const changePassword = async (data) => {
    try {
      await api.post('/user/change-password', data)
      toast.success('Senha alterada com sucesso!')
      return { success: true }
    } catch (error) {
      const message = error.response?.data?.message || 'Erro ao alterar senha'
      toast.error(message)
      return { success: false, message }
    }
  }

  const updateProfile = async (data) => {
    try {
      const response = await api.put('/user/profile', data)
      updateUser(response.data.user)
      toast.success('Perfil atualizado com sucesso!')
      return { success: true }
    } catch (error) {
      const message = error.response?.data?.message || 'Erro ao atualizar perfil'
      toast.error(message)
      return { success: false, message }
    }
  }

  const getWhatsAppSupportUrl = () => {
    return company.value?.whatsapp_support_url || '#'
  }

  const getTrialStatus = () => {
    if (!company.value) return null
    
    return {
      expires_at: company.value.trial_expires_at,
      is_expired: isTrialExpired.value,
      remaining_days: remainingTrialDays.value,
      support_url: getWhatsAppSupportUrl()
    }
  }

  return {
    // State
    user,
    token,
    company,
    permissions,
    
    // Getters
    isAuthenticated,
    isTrialExpired,
    remainingTrialDays,
    userRole,
    isSuperAdmin,
    isCompanyAdmin,
    canManageUsers,
    canViewReports,
    
    // Actions
    login,
    register,
    checkAuth,
    logout,
    updateUser,
    updateCompany,
    refreshToken,
    forgotPassword,
    resetPassword,
    changePassword,
    updateProfile,
    getWhatsAppSupportUrl,
    getTrialStatus
  }
})
