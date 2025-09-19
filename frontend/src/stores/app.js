import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useAppStore = defineStore('app', () => {
  // State
  const isLoading = ref(false)
  const sidebarCollapsed = ref(false)
  const breadcrumbs = ref([])
  const pageTitle = ref('')
  const notifications = ref([])

  // Getters
  const hasNotifications = computed(() => notifications.value.length > 0)
  const unreadNotifications = computed(() => 
    notifications.value.filter(n => !n.read)
  )

  // Actions
  const setLoading = (loading) => {
    isLoading.value = loading
  }

  const toggleSidebar = () => {
    sidebarCollapsed.value = !sidebarCollapsed.value
    localStorage.setItem('sidebarCollapsed', sidebarCollapsed.value.toString())
  }

  const setSidebarCollapsed = (collapsed) => {
    sidebarCollapsed.value = collapsed
    localStorage.setItem('sidebarCollapsed', collapsed.toString())
  }

  const initializeSidebar = () => {
    const stored = localStorage.getItem('sidebarCollapsed')
    if (stored !== null) {
      sidebarCollapsed.value = stored === 'true'
    }
  }

  const setBreadcrumbs = (crumbs) => {
    breadcrumbs.value = crumbs
  }

  const setPageTitle = (title) => {
    pageTitle.value = title
    document.title = title ? `${title} - VisionMetrics` : 'VisionMetrics'
  }

  const addNotification = (notification) => {
    const id = Date.now().toString()
    notifications.value.unshift({
      id,
      ...notification,
      read: false,
      created_at: new Date().toISOString()
    })
    
    // Keep only last 50 notifications
    if (notifications.value.length > 50) {
      notifications.value = notifications.value.slice(0, 50)
    }
  }

  const markNotificationAsRead = (id) => {
    const notification = notifications.value.find(n => n.id === id)
    if (notification) {
      notification.read = true
    }
  }

  const markAllNotificationsAsRead = () => {
    notifications.value.forEach(n => n.read = true)
  }

  const removeNotification = (id) => {
    const index = notifications.value.findIndex(n => n.id === id)
    if (index > -1) {
      notifications.value.splice(index, 1)
    }
  }

  const clearAllNotifications = () => {
    notifications.value = []
  }

  const showErrorNotification = (message, title = 'Erro') => {
    addNotification({
      type: 'error',
      title,
      message,
      persistent: false
    })
  }

  const showSuccessNotification = (message, title = 'Sucesso') => {
    addNotification({
      type: 'success',
      title,
      message,
      persistent: false
    })
  }

  const showInfoNotification = (message, title = 'Informação') => {
    addNotification({
      type: 'info',
      title,
      message,
      persistent: false
    })
  }

  const showWarningNotification = (message, title = 'Atenção') => {
    addNotification({
      type: 'warning',
      title,
      message,
      persistent: false
    })
  }

  return {
    // State
    isLoading,
    sidebarCollapsed,
    breadcrumbs,
    pageTitle,
    notifications,
    
    // Getters
    hasNotifications,
    unreadNotifications,
    
    // Actions
    setLoading,
    toggleSidebar,
    setSidebarCollapsed,
    initializeSidebar,
    setBreadcrumbs,
    setPageTitle,
    addNotification,
    markNotificationAsRead,
    markAllNotificationsAsRead,
    removeNotification,
    clearAllNotifications,
    showErrorNotification,
    showSuccessNotification,
    showInfoNotification,
    showWarningNotification
  }
})
