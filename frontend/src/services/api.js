import axios from 'axios'
import { useToast } from 'vue-toastification'

const toast = useToast()

// Create axios instance
const api = axios.create({
  baseURL: import.meta.env.DEV ? 'http://localhost/api' : '/api',
  timeout: 30000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  }
})

// Request interceptor
api.interceptors.request.use(
  (config) => {
    // Add auth token if available
    const token = localStorage.getItem('auth_token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    
    // Add request timestamp
    config.metadata = { startTime: new Date() }
    
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Response interceptor
api.interceptors.response.use(
  (response) => {
    // Calculate request duration
    const endTime = new Date()
    const duration = endTime - response.config.metadata.startTime
    
    // Log slow requests in development
    if (import.meta.env.DEV && duration > 2000) {
      console.warn(`Slow API request: ${response.config.url} took ${duration}ms`)
    }
    
    return response
  },
  async (error) => {
    const { response, config } = error
    
    // Handle network errors
    if (!response) {
      toast.error('Erro de conexão. Verifique sua internet.')
      return Promise.reject(error)
    }
    
    const status = response.status
    const data = response.data
    
    // Handle different error statuses
    switch (status) {
      case 401:
        // Unauthorized - token expired or invalid
        if (!config.url.includes('/auth/login')) {
          localStorage.removeItem('auth_token')
          delete api.defaults.headers.common['Authorization']
          
          // Only redirect if not already on login page
          if (!window.location.pathname.includes('/login')) {
            window.location.href = '/login'
          }
        }
        break
        
      case 402:
        // Payment required - trial expired
        if (data.error === 'trial_expired') {
          window.location.href = '/trial-expirado'
          return Promise.reject(error)
        }
        break
        
      case 403:
        // Forbidden
        if (!config.skipErrorToast) {
          toast.error(data.message || 'Acesso negado')
        }
        break
        
      case 404:
        // Not found
        if (!config.skipErrorToast) {
          toast.error(data.message || 'Recurso não encontrado')
        }
        break
        
      case 422:
        // Validation error
        if (data.errors && !config.skipErrorToast) {
          const firstError = Object.values(data.errors)[0]
          if (Array.isArray(firstError)) {
            toast.error(firstError[0])
          } else {
            toast.error(firstError)
          }
        } else if (data.message && !config.skipErrorToast) {
          toast.error(data.message)
        }
        break
        
      case 429:
        // Rate limit exceeded
        toast.error(data.message || 'Muitas solicitações. Tente novamente em alguns minutos.')
        break
        
      case 500:
      case 502:
      case 503:
      case 504:
        // Server errors
        if (!config.skipErrorToast) {
          toast.error(data.message || 'Erro interno do servidor. Tente novamente.')
        }
        break
        
      default:
        // Other errors
        if (!config.skipErrorToast) {
          toast.error(data.message || 'Ocorreu um erro inesperado')
        }
    }
    
    return Promise.reject(error)
  }
)

// Helper methods
export const apiMethods = {
  // Dashboard
  getDashboard: () => api.get('/dashboard'),
  getDashboardStats: () => api.get('/dashboard/stats'),
  getDashboardCharts: (params = {}) => api.get('/dashboard/charts', { params }),
  
  // Auth
  login: (credentials) => api.post('/login', credentials),
  logout: () => api.post('/logout'),
  register: (data) => api.post('/register-company', data),
  checkAuth: () => api.get('/me'),
  refreshToken: () => api.post('/refresh'),
  forgotPassword: (email) => api.post('/forgot-password', { email }),
  resetPassword: (data) => api.post('/reset-password', data),
  
  // Profile
  getProfile: () => api.get('/user/profile'),
  updateProfile: (data) => api.put('/user/profile', data),
  changePassword: (data) => api.post('/user/change-password', data),
  
  // Leads
  getLeads: (params = {}) => api.get('/leads', { params }),
  getLead: (id) => api.get(`/leads/${id}`),
  createLead: (data) => api.post('/leads', data),
  updateLead: (id, data) => api.put(`/leads/${id}`, data),
  deleteLead: (id) => api.delete(`/leads/${id}`),
  addLeadTag: (id, tag) => api.post(`/leads/${id}/tags`, { tag }),
  removeLeadTag: (id, tag) => api.delete(`/leads/${id}/tags/${tag}`),
  updateLeadStatus: (id, status) => api.put(`/leads/${id}/status`, { status }),
  
  // Conversations
  getConversations: (params = {}) => api.get('/conversations', { params }),
  getConversation: (id) => api.get(`/conversations/${id}`),
  assignConversation: (id, userId) => api.post(`/conversations/${id}/assign`, { user_id: userId }),
  unassignConversation: (id) => api.post(`/conversations/${id}/unassign`),
  closeConversation: (id) => api.post(`/conversations/${id}/close`),
  reopenConversation: (id) => api.post(`/conversations/${id}/reopen`),
  markConversationAsRead: (id) => api.post(`/conversations/${id}/mark-read`),
  
  // Messages
  getMessages: (conversationId, params = {}) => api.get(`/conversations/${conversationId}/messages`, { params }),
  sendMessage: (conversationId, data) => api.post(`/conversations/${conversationId}/messages`, data),
  
  // Conversions
  getConversions: (params = {}) => api.get('/conversions', { params }),
  getConversion: (id) => api.get(`/conversions/${id}`),
  createConversion: (data) => api.post('/conversions', data),
  confirmConversion: (id, data = {}) => api.post(`/conversions/${id}/confirm`, data),
  cancelConversion: (id, data = {}) => api.post(`/conversions/${id}/cancel`, data),
  detectConversionFromMessage: (data) => api.post('/conversions/detect-from-message', data),
  
  // Tracking Links
  getTrackingLinks: (params = {}) => api.get('/tracking-links', { params }),
  getTrackingLink: (id) => api.get(`/tracking-links/${id}`),
  createTrackingLink: (data) => api.post('/tracking-links', data),
  updateTrackingLink: (id, data) => api.put(`/tracking-links/${id}`, data),
  deleteTrackingLink: (id) => api.delete(`/tracking-links/${id}`),
  toggleTrackingLink: (id) => api.post(`/tracking-links/${id}/toggle`),
  getTrackingLinkStats: (id) => api.get(`/tracking-links/${id}/stats`),
  
  // Reports
  getConversionsReport: (params = {}) => api.get('/reports/conversions', { params }),
  getLeadsReport: (params = {}) => api.get('/reports/leads', { params }),
  getConversationsReport: (params = {}) => api.get('/reports/conversations', { params }),
  getPerformanceReport: (params = {}) => api.get('/reports/performance', { params }),
  getAttributionReport: (params = {}) => api.get('/reports/attribution', { params }),
  exportConversions: (params = {}) => api.post('/reports/export/conversions', params, { responseType: 'blob' }),
  exportLeads: (params = {}) => api.post('/reports/export/leads', params, { responseType: 'blob' }),
  
  // Webhooks
  getWebhooks: (params = {}) => api.get('/webhooks', { params }),
  getWebhook: (id) => api.get(`/webhooks/${id}`),
  createWebhook: (data) => api.post('/webhooks', data),
  updateWebhook: (id, data) => api.put(`/webhooks/${id}`, data),
  deleteWebhook: (id) => api.delete(`/webhooks/${id}`),
  toggleWebhook: (id) => api.post(`/webhooks/${id}/toggle`),
  testWebhook: (id) => api.post(`/webhooks/${id}/test`),
  
  // Users (Company)
  getUsers: (params = {}) => api.get('/company/users', { params }),
  createUser: (data) => api.post('/company/users', data),
  updateUser: (id, data) => api.put(`/company/users/${id}`, data),
  deleteUser: (id) => api.delete(`/company/users/${id}`),
  toggleUserStatus: (id) => api.post(`/company/users/${id}/toggle-status`),
  
  // Company Settings
  getCompanySettings: () => api.get('/company/settings'),
  updateCompanySettings: (data) => api.put('/company/settings', data),
  getIntegrations: () => api.get('/company/integrations'),
  updateIntegrations: (data) => api.put('/company/integrations', data),
  
  // Trial
  getTrialStatus: () => api.get('/trial/status'),
  getSupportContact: () => api.get('/trial/support-contact'),
  
  // Admin (Super Admin)
  getAdminDashboard: () => api.get('/admin/dashboard'),
  getAdminStats: () => api.get('/admin/stats'),
  getAdminCompanies: (params = {}) => api.get('/admin/companies', { params }),
  getAdminCompany: (id) => api.get(`/admin/companies/${id}`),
  updateAdminCompany: (id, data) => api.put(`/admin/companies/${id}`, data),
  extendTrial: (id, data) => api.post(`/admin/companies/${id}/extend-trial`, data),
  toggleCompanyStatus: (id) => api.post(`/admin/companies/${id}/toggle-status`),
  getCompanyAuditLogs: (id, params = {}) => api.get(`/admin/companies/${id}/audit-logs`, { params }),
  getCompanyStats: (id) => api.get(`/admin/companies/${id}/stats`),
  getAdminUsers: (params = {}) => api.get('/admin/users', { params }),
  impersonateUser: (id) => api.post(`/admin/users/${id}/impersonate`),
  getAuditLogs: (params = {}) => api.get('/admin/audit-logs', { params }),
  getSystemHealth: () => api.get('/admin/system/health'),
  getSystemMetrics: () => api.get('/admin/system/metrics'),
}

export default api
