import { debounce as _debounce, throttle as _throttle } from 'lodash-es'

// Debounce function
export const debounce = (func, wait = 300) => {
  return _debounce(func, wait)
}

// Throttle function
export const throttle = (func, wait = 300) => {
  return _throttle(func, wait)
}

// Deep clone object
export const deepClone = (obj) => {
  return JSON.parse(JSON.stringify(obj))
}

// Check if object is empty
export const isEmpty = (obj) => {
  if (obj === null || obj === undefined) return true
  if (typeof obj === 'string') return obj.trim() === ''
  if (Array.isArray(obj)) return obj.length === 0
  if (typeof obj === 'object') return Object.keys(obj).length === 0
  return false
}

// Generate random ID
export const generateId = (length = 8) => {
  const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'
  let result = ''
  for (let i = 0; i < length; i++) {
    result += chars.charAt(Math.floor(Math.random() * chars.length))
  }
  return result
}

// Sleep function
export const sleep = (ms) => {
  return new Promise(resolve => setTimeout(resolve, ms))
}

// Download file from blob
export const downloadBlob = (blob, filename) => {
  const url = window.URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.href = url
  link.download = filename
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
  window.URL.revokeObjectURL(url)
}

// Copy text to clipboard
export const copyToClipboard = async (text) => {
  if (navigator.clipboard) {
    try {
      await navigator.clipboard.writeText(text)
      return true
    } catch (error) {
      console.error('Failed to copy to clipboard:', error)
      return false
    }
  } else {
    // Fallback for older browsers
    const textArea = document.createElement('textarea')
    textArea.value = text
    document.body.appendChild(textArea)
    textArea.select()
    try {
      document.execCommand('copy')
      document.body.removeChild(textArea)
      return true
    } catch (error) {
      console.error('Failed to copy to clipboard:', error)
      document.body.removeChild(textArea)
      return false
    }
  }
}

// Format query parameters
export const buildQueryString = (params) => {
  const query = new URLSearchParams()
  
  for (const [key, value] of Object.entries(params)) {
    if (value !== null && value !== undefined && value !== '') {
      if (Array.isArray(value)) {
        value.forEach(item => query.append(key, item))
      } else {
        query.append(key, value)
      }
    }
  }
  
  return query.toString()
}

// Parse query string to object
export const parseQueryString = (queryString) => {
  const params = new URLSearchParams(queryString)
  const result = {}
  
  for (const [key, value] of params.entries()) {
    if (result[key]) {
      if (Array.isArray(result[key])) {
        result[key].push(value)
      } else {
        result[key] = [result[key], value]
      }
    } else {
      result[key] = value
    }
  }
  
  return result
}

// Scroll to element
export const scrollToElement = (element, offset = 0) => {
  if (typeof element === 'string') {
    element = document.querySelector(element)
  }
  
  if (element) {
    const elementPosition = element.offsetTop - offset
    window.scrollTo({
      top: elementPosition,
      behavior: 'smooth'
    })
  }
}

// Get color by origin
export const getOriginColor = (origin) => {
  const colors = {
    'meta': '#1877f2',
    'google': '#4285f4',
    'outras': '#6b7280',
    'nao_rastreada': '#f97316'
  }
  return colors[origin] || colors['outras']
}

// Get color by status
export const getStatusColor = (status, type = 'conversation') => {
  const colors = {
    conversation: {
      'open': '#3b82f6',      // blue
      'closed': '#6b7280',    // gray
      'qualified': '#f59e0b', // yellow
      'converted': '#10b981', // green
      'lost': '#ef4444'       // red
    },
    lead: {
      'new': '#3b82f6',       // blue
      'contacted': '#f59e0b', // yellow
      'qualified': '#8b5cf6', // purple
      'converted': '#10b981', // green
      'lost': '#ef4444'       // red
    },
    conversion: {
      'pending': '#f59e0b',   // yellow
      'confirmed': '#10b981', // green
      'cancelled': '#ef4444'  // red
    }
  }
  
  return colors[type]?.[status] || '#6b7280'
}

// Format bytes to human readable
export const formatBytes = (bytes, decimals = 2) => {
  if (bytes === 0) return '0 Bytes'
  
  const k = 1024
  const dm = decimals < 0 ? 0 : decimals
  const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB']
  
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  
  return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i]
}

// Generate WhatsApp URL
export const generateWhatsAppUrl = (phone, message = '') => {
  const cleanPhone = phone.replace(/\D/g, '')
  const encodedMessage = encodeURIComponent(message)
  return `https://wa.me/${cleanPhone}${message ? `?text=${encodedMessage}` : ''}`
}

// Check if browser supports feature
export const supportsFeature = (feature) => {
  const features = {
    webp: () => {
      const canvas = document.createElement('canvas')
      canvas.width = 1
      canvas.height = 1
      return canvas.toDataURL('image/webp').indexOf('data:image/webp') === 0
    },
    clipboard: () => {
      return !!navigator.clipboard
    },
    notification: () => {
      return 'Notification' in window
    },
    serviceWorker: () => {
      return 'serviceWorker' in navigator
    }
  }
  
  return features[feature] ? features[feature]() : false
}

// Request notification permission
export const requestNotificationPermission = async () => {
  if (!supportsFeature('notification')) {
    return false
  }
  
  if (Notification.permission === 'granted') {
    return true
  }
  
  if (Notification.permission === 'denied') {
    return false
  }
  
  const permission = await Notification.requestPermission()
  return permission === 'granted'
}

// Show browser notification
export const showNotification = (title, options = {}) => {
  if (!supportsFeature('notification') || Notification.permission !== 'granted') {
    return null
  }
  
  return new Notification(title, {
    icon: '/favicon.ico',
    badge: '/favicon.ico',
    ...options
  })
}

// Local storage helpers with JSON support
export const storage = {
  get: (key, defaultValue = null) => {
    try {
      const item = localStorage.getItem(key)
      return item ? JSON.parse(item) : defaultValue
    } catch (error) {
      console.error('Error parsing localStorage item:', error)
      return defaultValue
    }
  },
  
  set: (key, value) => {
    try {
      localStorage.setItem(key, JSON.stringify(value))
      return true
    } catch (error) {
      console.error('Error setting localStorage item:', error)
      return false
    }
  },
  
  remove: (key) => {
    try {
      localStorage.removeItem(key)
      return true
    } catch (error) {
      console.error('Error removing localStorage item:', error)
      return false
    }
  },
  
  clear: () => {
    try {
      localStorage.clear()
      return true
    } catch (error) {
      console.error('Error clearing localStorage:', error)
      return false
    }
  }
}

// Session storage helpers
export const sessionStorage = {
  get: (key, defaultValue = null) => {
    try {
      const item = window.sessionStorage.getItem(key)
      return item ? JSON.parse(item) : defaultValue
    } catch (error) {
      console.error('Error parsing sessionStorage item:', error)
      return defaultValue
    }
  },
  
  set: (key, value) => {
    try {
      window.sessionStorage.setItem(key, JSON.stringify(value))
      return true
    } catch (error) {
      console.error('Error setting sessionStorage item:', error)
      return false
    }
  },
  
  remove: (key) => {
    try {
      window.sessionStorage.removeItem(key)
      return true
    } catch (error) {
      console.error('Error removing sessionStorage item:', error)
      return false
    }
  },
  
  clear: () => {
    try {
      window.sessionStorage.clear()
      return true
    } catch (error) {
      console.error('Error clearing sessionStorage:', error)
      return false
    }
  }
}
