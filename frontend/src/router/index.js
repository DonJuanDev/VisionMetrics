import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes = [
  {
    path: '/',
    redirect: '/dashboard'
  },
  {
    path: '/login',
    name: 'Login',
    component: () => import('@/views/auth/LoginView.vue'),
    meta: { 
      requiresAuth: false,
      layout: 'auth' 
    }
  },
  {
    path: '/register',
    name: 'Register',
    component: () => import('@/views/auth/RegisterView.vue'),
    meta: { 
      requiresAuth: false,
      layout: 'auth' 
    }
  },
  {
    path: '/forgot-password',
    name: 'ForgotPassword',
    component: () => import('@/views/auth/ForgotPasswordView.vue'),
    meta: { 
      requiresAuth: false,
      layout: 'auth' 
    }
  },
  {
    path: '/dashboard',
    name: 'Dashboard',
    component: () => import('@/views/DashboardView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/conversas',
    name: 'Conversations',
    component: () => import('@/views/ConversationsView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/conversas/:id',
    name: 'ConversationDetail',
    component: () => import('@/views/ConversationDetailView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/leads',
    name: 'Leads',
    component: () => import('@/views/LeadsView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/links-rastreaveis',
    name: 'TrackingLinks',
    component: () => import('@/views/TrackingLinksView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/conversoes',
    name: 'Conversions',
    component: () => import('@/views/ConversionsView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/relatorios',
    name: 'Reports',
    component: () => import('@/views/ReportsView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/webhooks',
    name: 'Webhooks',
    component: () => import('@/views/WebhooksView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/configuracoes',
    name: 'Settings',
    component: () => import('@/views/SettingsView.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/usuarios',
    name: 'Users',
    component: () => import('@/views/UsersView.vue'),
    meta: { 
      requiresAuth: true,
      requiresRole: ['company_admin', 'super_admin']
    }
  },
  {
    path: '/perfil',
    name: 'Profile',
    component: () => import('@/views/ProfileView.vue'),
    meta: { requiresAuth: true }
  },
  // Admin routes
  {
    path: '/admin',
    redirect: '/admin/dashboard'
  },
  {
    path: '/admin/dashboard',
    name: 'AdminDashboard',
    component: () => import('@/views/admin/AdminDashboardView.vue'),
    meta: { 
      requiresAuth: true,
      requiresRole: ['super_admin']
    }
  },
  {
    path: '/admin/empresas',
    name: 'AdminCompanies',
    component: () => import('@/views/admin/AdminCompaniesView.vue'),
    meta: { 
      requiresAuth: true,
      requiresRole: ['super_admin']
    }
  },
  {
    path: '/admin/usuarios',
    name: 'AdminUsers',
    component: () => import('@/views/admin/AdminUsersView.vue'),
    meta: { 
      requiresAuth: true,
      requiresRole: ['super_admin']
    }
  },
  // Trial expired page
  {
    path: '/trial-expirado',
    name: 'TrialExpired',
    component: () => import('@/views/TrialExpiredView.vue'),
    meta: { 
      requiresAuth: true,
      layout: 'minimal' 
    }
  },
  // 404 page
  {
    path: '/:pathMatch(.*)*',
    name: 'NotFound',
    component: () => import('@/views/NotFoundView.vue'),
    meta: { 
      requiresAuth: false,
      layout: 'minimal' 
    }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition
    }
    return { top: 0 }
  }
})

// Navigation guards
router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()
  
  // Check if route requires authentication
  if (to.meta.requiresAuth) {
    if (!authStore.isAuthenticated) {
      return next({
        name: 'Login',
        query: { redirect: to.fullPath }
      })
    }
    
    // Check if user's trial has expired
    if (authStore.isTrialExpired && to.name !== 'TrialExpired') {
      return next({ name: 'TrialExpired' })
    }
    
    // Check role requirements
    if (to.meta.requiresRole) {
      const requiredRoles = Array.isArray(to.meta.requiresRole) 
        ? to.meta.requiresRole 
        : [to.meta.requiresRole]
      
      if (!requiredRoles.includes(authStore.user?.role)) {
        return next({ name: 'Dashboard' })
      }
    }
  }
  
  // Redirect authenticated users away from auth pages
  if (!to.meta.requiresAuth && authStore.isAuthenticated) {
    if (['Login', 'Register', 'ForgotPassword'].includes(to.name)) {
      return next({ name: 'Dashboard' })
    }
  }
  
  next()
})

export default router
