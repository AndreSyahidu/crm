/**
 * Form Validation Utilities
 */

import { REGEX, ERROR_MESSAGES } from './constants'

/**
 * Validation result object
 * @typedef {Object} ValidationResult
 * @property {boolean} valid - Whether the validation passed
 * @property {string} error - Error message if validation failed
 */

/**
 * Check if value is required
 * @param {any} value - Value to validate
 * @returns {ValidationResult}
 */
export const required = (value) => {
  const isEmpty = value === null || value === undefined || value === '' ||
    (Array.isArray(value) && value.length === 0)

  return {
    valid: !isEmpty,
    error: isEmpty ? ERROR_MESSAGES.REQUIRED : ''
  }
}

/**
 * Validate email format
 * @param {string} value - Email to validate
 * @returns {ValidationResult}
 */
export const email = (value) => {
  if (!value) return { valid: true, error: '' }

  const isValid = REGEX.EMAIL.test(value)
  return {
    valid: isValid,
    error: isValid ? '' : ERROR_MESSAGES.INVALID_EMAIL
  }
}

/**
 * Validate phone number (Indonesian format)
 * @param {string} value - Phone number to validate
 * @returns {ValidationResult}
 */
export const phone = (value) => {
  if (!value) return { valid: true, error: '' }

  const cleaned = value.replace(/[\s-]/g, '')
  const isValid = REGEX.PHONE.test(cleaned)

  return {
    valid: isValid,
    error: isValid ? '' : ERROR_MESSAGES.INVALID_PHONE
  }
}

/**
 * Validate URL format
 * @param {string} value - URL to validate
 * @returns {ValidationResult}
 */
export const url = (value) => {
  if (!value) return { valid: true, error: '' }

  const isValid = REGEX.URL.test(value)
  return {
    valid: isValid,
    error: isValid ? '' : ERROR_MESSAGES.INVALID_URL
  }
}

/**
 * Validate minimum length
 * @param {number} min - Minimum length
 * @returns {Function} Validator function
 */
export const minLength = (min) => {
  return (value) => {
    if (!value) return { valid: true, error: '' }

    const isValid = value.length >= min
    return {
      valid: isValid,
      error: isValid ? '' : ERROR_MESSAGES.MIN_LENGTH(min)
    }
  }
}

/**
 * Validate maximum length
 * @param {number} max - Maximum length
 * @returns {Function} Validator function
 */
export const maxLength = (max) => {
  return (value) => {
    if (!value) return { valid: true, error: '' }

    const isValid = value.length <= max
    return {
      valid: isValid,
      error: isValid ? '' : ERROR_MESSAGES.MAX_LENGTH(max)
    }
  }
}

/**
 * Validate minimum value
 * @param {number} min - Minimum value
 * @returns {Function} Validator function
 */
export const minValue = (min) => {
  return (value) => {
    if (value === null || value === undefined || value === '') {
      return { valid: true, error: '' }
    }

    const numValue = Number(value)
    const isValid = numValue >= min

    return {
      valid: isValid,
      error: isValid ? '' : ERROR_MESSAGES.MIN_VALUE(min)
    }
  }
}

/**
 * Validate maximum value
 * @param {number} max - Maximum value
 * @returns {Function} Validator function
 */
export const maxValue = (max) => {
  return (value) => {
    if (value === null || value === undefined || value === '') {
      return { valid: true, error: '' }
    }

    const numValue = Number(value)
    const isValid = numValue <= max

    return {
      valid: isValid,
      error: isValid ? '' : ERROR_MESSAGES.MAX_VALUE(max)
    }
  }
}

/**
 * Validate numeric value
 * @param {any} value - Value to validate
 * @returns {ValidationResult}
 */
export const numeric = (value) => {
  if (!value && value !== 0) return { valid: true, error: '' }

  const isValid = REGEX.NUMERIC.test(String(value))
  return {
    valid: isValid,
    error: isValid ? '' : 'Must be a number'
  }
}

/**
 * Validate alphanumeric value
 * @param {string} value - Value to validate
 * @returns {ValidationResult}
 */
export const alphanumeric = (value) => {
  if (!value) return { valid: true, error: '' }

  const isValid = REGEX.ALPHANUMERIC.test(value)
  return {
    valid: isValid,
    error: isValid ? '' : 'Must contain only letters and numbers'
  }
}

/**
 * Validate that value matches another value (e.g., password confirmation)
 * @param {any} compareValue - Value to compare against
 * @param {string} fieldName - Name of field being compared
 * @returns {Function} Validator function
 */
export const matches = (compareValue, fieldName = 'field') => {
  return (value) => {
    const isValid = value === compareValue
    return {
      valid: isValid,
      error: isValid ? '' : `Must match ${fieldName}`
    }
  }
}

/**
 * Validate file size
 * @param {File} file - File to validate
 * @param {number} maxSize - Maximum size in bytes
 * @returns {ValidationResult}
 */
export const fileSize = (file, maxSize) => {
  if (!file) return { valid: true, error: '' }

  const isValid = file.size <= maxSize
  return {
    valid: isValid,
    error: isValid ? '' : ERROR_MESSAGES.FILE_TOO_LARGE
  }
}

/**
 * Validate file type
 * @param {File} file - File to validate
 * @param {string[]} allowedTypes - Array of allowed MIME types
 * @returns {ValidationResult}
 */
export const fileType = (file, allowedTypes) => {
  if (!file) return { valid: true, error: '' }

  const isValid = allowedTypes.includes(file.type)
  return {
    valid: isValid,
    error: isValid ? '' : ERROR_MESSAGES.INVALID_FILE_TYPE
  }
}

/**
 * Validate date is in the future
 * @param {string|Date} value - Date to validate
 * @returns {ValidationResult}
 */
export const futureDate = (value) => {
  if (!value) return { valid: true, error: '' }

  const date = new Date(value)
  const now = new Date()
  const isValid = date > now

  return {
    valid: isValid,
    error: isValid ? '' : 'Date must be in the future'
  }
}

/**
 * Validate date is in the past
 * @param {string|Date} value - Date to validate
 * @returns {ValidationResult}
 */
export const pastDate = (value) => {
  if (!value) return { valid: true, error: '' }

  const date = new Date(value)
  const now = new Date()
  const isValid = date < now

  return {
    valid: isValid,
    error: isValid ? '' : 'Date must be in the past'
  }
}

/**
 * Validate date range
 * @param {string|Date} startDate - Start date
 * @param {string|Date} endDate - End date
 * @returns {ValidationResult}
 */
export const dateRange = (startDate, endDate) => {
  if (!startDate || !endDate) return { valid: true, error: '' }

  const start = new Date(startDate)
  const end = new Date(endDate)
  const isValid = start <= end

  return {
    valid: isValid,
    error: isValid ? '' : 'End date must be after start date'
  }
}

/**
 * Validate custom pattern
 * @param {RegExp} pattern - Regular expression pattern
 * @param {string} errorMessage - Custom error message
 * @returns {Function} Validator function
 */
export const pattern = (pattern, errorMessage = 'Invalid format') => {
  return (value) => {
    if (!value) return { valid: true, error: '' }

    const isValid = pattern.test(value)
    return {
      valid: isValid,
      error: isValid ? '' : errorMessage
    }
  }
}

/**
 * Combine multiple validators
 * @param {Function[]} validators - Array of validator functions
 * @returns {Function} Combined validator function
 */
export const combineValidators = (...validators) => {
  return (value) => {
    for (const validator of validators) {
      const result = validator(value)
      if (!result.valid) {
        return result
      }
    }
    return { valid: true, error: '' }
  }
}

/**
 * Validate form fields
 * @param {Object} formData - Form data object
 * @param {Object} rules - Validation rules object
 * @returns {Object} Validation errors object
 */
export const validateForm = (formData, rules) => {
  const errors = {}

  for (const [field, validators] of Object.entries(rules)) {
    const value = formData[field]
    const fieldValidators = Array.isArray(validators) ? validators : [validators]

    for (const validator of fieldValidators) {
      const result = validator(value)
      if (!result.valid) {
        errors[field] = result.error
        break
      }
    }
  }

  return errors
}

/**
 * Check if form has errors
 * @param {Object} errors - Errors object
 * @returns {boolean} True if form has errors
 */
export const hasErrors = (errors) => {
  return Object.keys(errors).length > 0
}

/**
 * Clear specific field error
 * @param {Object} errors - Errors object
 * @param {string} field - Field name to clear
 * @returns {Object} Updated errors object
 */
export const clearError = (errors, field) => {
  const newErrors = { ...errors }
  delete newErrors[field]
  return newErrors
}

/**
 * Clear all errors
 * @returns {Object} Empty errors object
 */
export const clearAllErrors = () => {
  return {}
}

// Export all validators
export default {
  required,
  email,
  phone,
  url,
  minLength,
  maxLength,
  minValue,
  maxValue,
  numeric,
  alphanumeric,
  matches,
  fileSize,
  fileType,
  futureDate,
  pastDate,
  dateRange,
  pattern,
  combineValidators,
  validateForm,
  hasErrors,
  clearError,
  clearAllErrors
}
