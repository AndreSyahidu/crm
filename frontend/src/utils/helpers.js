/**
 * Format currency to Indonesian Rupiah
 * @param {number} value - The value to format
 * @param {boolean} withSymbol - Include Rp symbol
 * @returns {string} Formatted currency string
 */
export const formatCurrency = (value, withSymbol = false) => {
  if (!value && value !== 0) return withSymbol ? 'Rp 0' : '0'

  const formatted = new Intl.NumberFormat('id-ID').format(value)
  return withSymbol ? `Rp ${formatted}` : formatted
}

/**
 * Format date to Indonesian locale
 * @param {string|Date} date - The date to format
 * @param {object} options - Intl.DateTimeFormat options
 * @returns {string} Formatted date string
 */
export const formatDate = (date, options = {}) => {
  if (!date) return '-'

  const defaultOptions = {
    day: 'numeric',
    month: 'short',
    year: 'numeric'
  }

  return new Date(date).toLocaleDateString('id-ID', { ...defaultOptions, ...options })
}

/**
 * Format datetime to Indonesian locale
 * @param {string|Date} date - The datetime to format
 * @param {object} options - Intl.DateTimeFormat options
 * @returns {string} Formatted datetime string
 */
export const formatDateTime = (date, options = {}) => {
  if (!date) return '-'

  const defaultOptions = {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  }

  return new Date(date).toLocaleString('id-ID', { ...defaultOptions, ...options })
}

/**
 * Format time to Indonesian locale
 * @param {string|Date} date - The time to format
 * @returns {string} Formatted time string
 */
export const formatTime = (date) => {
  if (!date) return '-'

  return new Date(date).toLocaleTimeString('id-ID', {
    hour: '2-digit',
    minute: '2-digit'
  })
}

/**
 * Get relative time (e.g., "2 days ago", "in 3 hours")
 * @param {string|Date} date - The date to compare
 * @returns {string} Relative time string
 */
export const getRelativeTime = (date) => {
  if (!date) return '-'

  const now = new Date()
  const then = new Date(date)
  const diffMs = then - now
  const diffSec = Math.round(diffMs / 1000)
  const diffMin = Math.round(diffSec / 60)
  const diffHour = Math.round(diffMin / 60)
  const diffDay = Math.round(diffHour / 24)

  if (Math.abs(diffSec) < 60) return 'just now'
  if (Math.abs(diffMin) < 60) return `${Math.abs(diffMin)} minute${Math.abs(diffMin) > 1 ? 's' : ''} ${diffMin > 0 ? 'from now' : 'ago'}`
  if (Math.abs(diffHour) < 24) return `${Math.abs(diffHour)} hour${Math.abs(diffHour) > 1 ? 's' : ''} ${diffHour > 0 ? 'from now' : 'ago'}`
  if (Math.abs(diffDay) < 7) return `${Math.abs(diffDay)} day${Math.abs(diffDay) > 1 ? 's' : ''} ${diffDay > 0 ? 'from now' : 'ago'}`

  return formatDate(date)
}

/**
 * Calculate days until/since a date
 * @param {string|Date} date - The date to compare
 * @returns {number} Number of days (negative if in the past)
 */
export const getDaysUntil = (date) => {
  if (!date) return null

  const now = new Date()
  const then = new Date(date)
  const diffMs = then - now
  return Math.ceil(diffMs / (1000 * 60 * 60 * 24))
}

/**
 * Format phone number
 * @param {string} phone - Phone number to format
 * @returns {string} Formatted phone number
 */
export const formatPhoneNumber = (phone) => {
  if (!phone) return '-'

  // Remove all non-digit characters
  const cleaned = phone.replace(/\D/g, '')

  // Format: +62 812-3456-7890
  if (cleaned.startsWith('62')) {
    return `+62 ${cleaned.slice(2, 5)}-${cleaned.slice(5, 9)}-${cleaned.slice(9)}`
  }

  // Format: 0812-3456-7890
  if (cleaned.startsWith('0')) {
    return `${cleaned.slice(0, 4)}-${cleaned.slice(4, 8)}-${cleaned.slice(8)}`
  }

  return phone
}

/**
 * Truncate text to specified length
 * @param {string} text - Text to truncate
 * @param {number} length - Maximum length
 * @param {string} suffix - Suffix to add (default: '...')
 * @returns {string} Truncated text
 */
export const truncate = (text, length = 100, suffix = '...') => {
  if (!text) return ''
  if (text.length <= length) return text

  return text.substring(0, length).trim() + suffix
}

/**
 * Debounce function
 * @param {Function} func - Function to debounce
 * @param {number} wait - Wait time in milliseconds
 * @returns {Function} Debounced function
 */
export const debounce = (func, wait = 300) => {
  let timeout
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout)
      func(...args)
    }
    clearTimeout(timeout)
    timeout = setTimeout(later, wait)
  }
}

/**
 * Deep clone an object
 * @param {object} obj - Object to clone
 * @returns {object} Cloned object
 */
export const deepClone = (obj) => {
  return JSON.parse(JSON.stringify(obj))
}

/**
 * Generate initials from name
 * @param {string} name - Full name
 * @param {number} max - Maximum initials (default: 2)
 * @returns {string} Initials
 */
export const getInitials = (name, max = 2) => {
  if (!name) return ''

  return name
    .split(' ')
    .map(n => n[0])
    .join('')
    .toUpperCase()
    .slice(0, max)
}

/**
 * Get random color from predefined palette
 * @returns {string} Hex color code
 */
export const getRandomColor = () => {
  const colors = [
    '#007bff', '#28a745', '#dc3545', '#ffc107', '#17a2b8',
    '#6f42c1', '#fd7e14', '#20c997', '#e83e8c', '#6c757d'
  ]
  return colors[Math.floor(Math.random() * colors.length)]
}

/**
 * Download data as file
 * @param {string} data - Data to download
 * @param {string} filename - File name
 * @param {string} type - MIME type
 */
export const downloadFile = (data, filename, type = 'text/plain') => {
  const blob = new Blob([data], { type })
  const url = window.URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.href = url
  link.download = filename
  link.click()
  window.URL.revokeObjectURL(url)
}

/**
 * Copy text to clipboard
 * @param {string} text - Text to copy
 * @returns {Promise<boolean>} Success status
 */
export const copyToClipboard = async (text) => {
  try {
    await navigator.clipboard.writeText(text)
    return true
  } catch (error) {
    console.error('Failed to copy to clipboard:', error)
    return false
  }
}

/**
 * Parse JSON safely
 * @param {string} json - JSON string
 * @param {any} defaultValue - Default value if parsing fails
 * @returns {any} Parsed object or default value
 */
export const safeJsonParse = (json, defaultValue = null) => {
  try {
    return JSON.parse(json)
  } catch (error) {
    return defaultValue
  }
}

/**
 * Calculate percentage
 * @param {number} value - Current value
 * @param {number} total - Total value
 * @param {number} decimals - Number of decimal places
 * @returns {number} Percentage
 */
export const calculatePercentage = (value, total, decimals = 0) => {
  if (!total || total === 0) return 0

  const percentage = (value / total) * 100
  return Number(percentage.toFixed(decimals))
}

/**
 * Sleep/delay execution
 * @param {number} ms - Milliseconds to sleep
 * @returns {Promise} Promise that resolves after delay
 */
export const sleep = (ms) => {
  return new Promise(resolve => setTimeout(resolve, ms))
}

/**
 * Check if value is empty (null, undefined, '', [], {})
 * @param {any} value - Value to check
 * @returns {boolean} True if empty
 */
export const isEmpty = (value) => {
  if (value === null || value === undefined) return true
  if (typeof value === 'string' && value.trim() === '') return true
  if (Array.isArray(value) && value.length === 0) return true
  if (typeof value === 'object' && Object.keys(value).length === 0) return true

  return false
}
