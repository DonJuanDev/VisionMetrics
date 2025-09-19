import { format, parseISO, formatDistanceToNow, isValid } from 'date-fns'
import { ptBR } from 'date-fns/locale'

// Date formatting
export const formatDate = (date, pattern = 'dd/MM/yyyy') => {
  if (!date) return ''
  
  try {
    const parsedDate = typeof date === 'string' ? parseISO(date) : date
    if (!isValid(parsedDate)) return ''
    return format(parsedDate, pattern, { locale: ptBR })
  } catch (error) {
    return ''
  }
}

export const formatDateTime = (date, pattern = 'dd/MM/yyyy HH:mm') => {
  return formatDate(date, pattern)
}

export const formatTime = (date, pattern = 'HH:mm') => {
  return formatDate(date, pattern)
}

export const formatRelativeTime = (date) => {
  if (!date) return ''
  
  try {
    const parsedDate = typeof date === 'string' ? parseISO(date) : date
    if (!isValid(parsedDate)) return ''
    return formatDistanceToNow(parsedDate, { 
      addSuffix: true, 
      locale: ptBR 
    })
  } catch (error) {
    return ''
  }
}

// Currency formatting
export const formatCurrency = (value, currency = 'BRL') => {
  if (value === null || value === undefined) return 'R$ 0,00'
  
  const numValue = typeof value === 'string' ? parseFloat(value) : value
  
  if (isNaN(numValue)) return 'R$ 0,00'
  
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: currency
  }).format(numValue)
}

// Number formatting
export const formatNumber = (value, decimals = 0) => {
  if (value === null || value === undefined) return '0'
  
  const numValue = typeof value === 'string' ? parseFloat(value) : value
  
  if (isNaN(numValue)) return '0'
  
  return new Intl.NumberFormat('pt-BR', {
    minimumFractionDigits: decimals,
    maximumFractionDigits: decimals
  }).format(numValue)
}

export const formatPercentage = (value, decimals = 1) => {
  if (value === null || value === undefined) return '0%'
  
  const numValue = typeof value === 'string' ? parseFloat(value) : value
  
  if (isNaN(numValue)) return '0%'
  
  return `${formatNumber(numValue, decimals)}%`
}

// Phone formatting
export const formatPhone = (phone) => {
  if (!phone) return ''
  
  // Remove non-digits
  const digits = phone.replace(/\D/g, '')
  
  // Brazilian mobile: +55 11 99999-9999
  if (digits.length === 13 && digits.startsWith('55')) {
    const country = digits.slice(0, 2)
    const area = digits.slice(2, 4)
    const first = digits.slice(4, 9)
    const second = digits.slice(9)
    return `+${country} ${area} ${first}-${second}`
  }
  
  // Brazilian mobile without country: 11 99999-9999
  if (digits.length === 11) {
    const area = digits.slice(0, 2)
    const first = digits.slice(2, 7)
    const second = digits.slice(7)
    return `${area} ${first}-${second}`
  }
  
  // Brazilian landline without country: 11 9999-9999
  if (digits.length === 10) {
    const area = digits.slice(0, 2)
    const first = digits.slice(2, 6)
    const second = digits.slice(6)
    return `${area} ${first}-${second}`
  }
  
  return phone
}

// CNPJ formatting
export const formatCnpj = (cnpj) => {
  if (!cnpj) return ''
  
  const digits = cnpj.replace(/\D/g, '')
  
  if (digits.length === 14) {
    return digits.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5')
  }
  
  return cnpj
}

// File size formatting
export const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes'
  
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

// Text truncation
export const truncateText = (text, maxLength = 50, suffix = '...') => {
  if (!text) return ''
  if (text.length <= maxLength) return text
  return text.substring(0, maxLength) + suffix
}

// Capitalize first letter
export const capitalize = (text) => {
  if (!text) return ''
  return text.charAt(0).toUpperCase() + text.slice(1).toLowerCase()
}

// Origin formatting
export const formatOrigin = (origin) => {
  const origins = {
    'meta': 'Meta Ads',
    'google': 'Google Ads',
    'outras': 'Outras Origens',
    'nao_rastreada': 'Não Rastreada'
  }
  
  return origins[origin] || origin
}

// Status formatting
export const formatStatus = (status, type = 'conversation') => {
  const statusMap = {
    conversation: {
      'open': 'Aberta',
      'closed': 'Fechada',
      'qualified': 'Qualificada',
      'converted': 'Convertida',
      'lost': 'Perdida'
    },
    lead: {
      'new': 'Novo',
      'contacted': 'Contatado',
      'qualified': 'Qualificado',
      'converted': 'Convertido',
      'lost': 'Perdido'
    },
    conversion: {
      'pending': 'Pendente',
      'confirmed': 'Confirmada',
      'cancelled': 'Cancelada'
    },
    user: {
      'active': 'Ativo',
      'inactive': 'Inativo'
    },
    webhook: {
      'success': 'Sucesso',
      'failed': 'Falhou',
      'timeout': 'Timeout'
    }
  }
  
  return statusMap[type]?.[status] || status
}

// Role formatting
export const formatRole = (role) => {
  const roles = {
    'super_admin': 'Super Administrador',
    'company_admin': 'Administrador',
    'company_agent': 'Agente/Vendedor',
    'company_viewer': 'Visualizador'
  }
  
  return roles[role] || role
}

// Payment method formatting
export const formatPaymentMethod = (method) => {
  const methods = {
    'pix': 'PIX',
    'boleto': 'Boleto',
    'cartao_credito': 'Cartão de Crédito',
    'cartao_debito': 'Cartão de Débito',
    'transferencia': 'Transferência',
    'dinheiro': 'Dinheiro',
    'outro': 'Outro'
  }
  
  return methods[method] || method
}

// URL formatting
export const formatUrl = (url) => {
  if (!url) return ''
  
  // Add protocol if missing
  if (!url.startsWith('http://') && !url.startsWith('https://')) {
    return `https://${url}`
  }
  
  return url
}

// Initials from name
export const getInitials = (name) => {
  if (!name) return ''
  
  return name
    .split(' ')
    .map(part => part.charAt(0).toUpperCase())
    .slice(0, 2)
    .join('')
}
