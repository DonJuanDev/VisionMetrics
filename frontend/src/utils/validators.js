// Email validation
export const isValidEmail = (email) => {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return emailRegex.test(email)
}

// Phone validation (Brazilian format)
export const isValidPhone = (phone) => {
  if (!phone) return false
  
  const digits = phone.replace(/\D/g, '')
  
  // Brazilian mobile with country code: 5511999999999
  if (digits.length === 13 && digits.startsWith('55')) {
    return true
  }
  
  // Brazilian mobile: 11999999999
  if (digits.length === 11) {
    const areaCode = digits.slice(0, 2)
    const firstDigit = digits.charAt(2)
    
    // Area codes 11-99 and mobile first digit 9
    return parseInt(areaCode) >= 11 && parseInt(areaCode) <= 99 && firstDigit === '9'
  }
  
  // Brazilian landline: 1199999999
  if (digits.length === 10) {
    const areaCode = digits.slice(0, 2)
    return parseInt(areaCode) >= 11 && parseInt(areaCode) <= 99
  }
  
  return false
}

// CNPJ validation
export const isValidCnpj = (cnpj) => {
  if (!cnpj) return false
  
  const digits = cnpj.replace(/\D/g, '')
  
  if (digits.length !== 14) return false
  
  // Check for repeated digits
  if (/^(\d)\1{13}$/.test(digits)) return false
  
  // Validate check digits
  let sum = 0
  let weight = 2
  
  for (let i = 11; i >= 0; i--) {
    sum += parseInt(digits.charAt(i)) * weight
    weight = weight === 9 ? 2 : weight + 1
  }
  
  const remainder1 = sum % 11
  const digit1 = remainder1 < 2 ? 0 : 11 - remainder1
  
  if (digit1 !== parseInt(digits.charAt(12))) return false
  
  sum = 0
  weight = 2
  
  for (let i = 12; i >= 0; i--) {
    sum += parseInt(digits.charAt(i)) * weight
    weight = weight === 9 ? 2 : weight + 1
  }
  
  const remainder2 = sum % 11
  const digit2 = remainder2 < 2 ? 0 : 11 - remainder2
  
  return digit2 === parseInt(digits.charAt(13))
}

// URL validation
export const isValidUrl = (url) => {
  if (!url) return false
  
  try {
    new URL(url.startsWith('http') ? url : `https://${url}`)
    return true
  } catch {
    return false
  }
}

// Password strength validation
export const getPasswordStrength = (password) => {
  if (!password) return { score: 0, feedback: 'Digite uma senha' }
  
  let score = 0
  let feedback = []
  
  // Length check
  if (password.length >= 8) {
    score += 1
  } else {
    feedback.push('Pelo menos 8 caracteres')
  }
  
  // Lowercase check
  if (/[a-z]/.test(password)) {
    score += 1
  } else {
    feedback.push('Pelo menos uma letra minúscula')
  }
  
  // Uppercase check
  if (/[A-Z]/.test(password)) {
    score += 1
  } else {
    feedback.push('Pelo menos uma letra maiúscula')
  }
  
  // Number check
  if (/\d/.test(password)) {
    score += 1
  } else {
    feedback.push('Pelo menos um número')
  }
  
  // Special character check
  if (/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)) {
    score += 1
  } else {
    feedback.push('Pelo menos um caractere especial')
  }
  
  const strength = ['Muito fraca', 'Fraca', 'Regular', 'Boa', 'Forte'][score]
  
  return {
    score,
    strength,
    feedback: feedback.length > 0 ? feedback.join(', ') : 'Senha forte!'
  }
}

// Required field validation
export const required = (value) => {
  if (value === null || value === undefined) return 'Este campo é obrigatório'
  if (typeof value === 'string' && value.trim() === '') return 'Este campo é obrigatório'
  if (Array.isArray(value) && value.length === 0) return 'Este campo é obrigatório'
  return true
}

// Min length validation
export const minLength = (min) => (value) => {
  if (!value) return true // Let required handle empty values
  if (value.length < min) return `Deve ter pelo menos ${min} caracteres`
  return true
}

// Max length validation
export const maxLength = (max) => (value) => {
  if (!value) return true
  if (value.length > max) return `Deve ter no máximo ${max} caracteres`
  return true
}

// Email validation rule
export const email = (value) => {
  if (!value) return true // Let required handle empty values
  if (!isValidEmail(value)) return 'Email inválido'
  return true
}

// Phone validation rule
export const phone = (value) => {
  if (!value) return true // Let required handle empty values
  if (!isValidPhone(value)) return 'Telefone inválido'
  return true
}

// CNPJ validation rule
export const cnpj = (value) => {
  if (!value) return true // CNPJ is optional
  if (!isValidCnpj(value)) return 'CNPJ inválido'
  return true
}

// URL validation rule
export const url = (value) => {
  if (!value) return true // Let required handle empty values
  if (!isValidUrl(value)) return 'URL inválida'
  return true
}

// Numeric validation
export const numeric = (value) => {
  if (!value) return true
  if (isNaN(value)) return 'Deve ser um número'
  return true
}

// Min value validation
export const min = (minValue) => (value) => {
  if (!value) return true
  if (parseFloat(value) < minValue) return `Deve ser pelo menos ${minValue}`
  return true
}

// Max value validation
export const max = (maxValue) => (value) => {
  if (!value) return true
  if (parseFloat(value) > maxValue) return `Deve ser no máximo ${maxValue}`
  return true
}

// Confirmation validation (for password confirmation)
export const confirmed = (confirmValue) => (value) => {
  if (value !== confirmValue) return 'As senhas não conferem'
  return true
}

// Custom validation runner
export const validateField = (value, rules) => {
  for (const rule of rules) {
    const result = typeof rule === 'function' ? rule(value) : rule
    if (result !== true) {
      return result
    }
  }
  return true
}

// Validate entire form
export const validateForm = (data, rules) => {
  const errors = {}
  let hasErrors = false
  
  for (const [field, fieldRules] of Object.entries(rules)) {
    const result = validateField(data[field], fieldRules)
    if (result !== true) {
      errors[field] = result
      hasErrors = true
    }
  }
  
  return {
    valid: !hasErrors,
    errors
  }
}
