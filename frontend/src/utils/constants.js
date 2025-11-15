/**
 * Application Constants
 */

// Lead Status
export const LEAD_STATUS = {
  NEW: 'new',
  CONTACTED: 'contacted',
  QUALIFIED: 'qualified',
  PROPOSAL: 'proposal',
  NEGOTIATION: 'negotiation',
  WON: 'won',
  LOST: 'lost'
}

export const LEAD_STATUS_LABELS = {
  [LEAD_STATUS.NEW]: 'New',
  [LEAD_STATUS.CONTACTED]: 'Contacted',
  [LEAD_STATUS.QUALIFIED]: 'Qualified',
  [LEAD_STATUS.PROPOSAL]: 'Proposal',
  [LEAD_STATUS.NEGOTIATION]: 'Negotiation',
  [LEAD_STATUS.WON]: 'Won',
  [LEAD_STATUS.LOST]: 'Lost'
}

export const LEAD_STATUS_COLORS = {
  [LEAD_STATUS.NEW]: '#17a2b8',
  [LEAD_STATUS.CONTACTED]: '#007bff',
  [LEAD_STATUS.QUALIFIED]: '#ffc107',
  [LEAD_STATUS.PROPOSAL]: '#6c757d',
  [LEAD_STATUS.NEGOTIATION]: '#6f42c1',
  [LEAD_STATUS.WON]: '#28a745',
  [LEAD_STATUS.LOST]: '#dc3545'
}

// Lead Sources
export const LEAD_SOURCES = {
  WHATSAPP: 'whatsapp',
  WEBSITE: 'website',
  REFERRAL: 'referral',
  SOCIAL_MEDIA: 'social_media',
  EMAIL: 'email',
  PHONE: 'phone',
  WALK_IN: 'walk_in',
  OTHER: 'other'
}

export const LEAD_SOURCE_LABELS = {
  [LEAD_SOURCES.WHATSAPP]: 'WhatsApp',
  [LEAD_SOURCES.WEBSITE]: 'Website',
  [LEAD_SOURCES.REFERRAL]: 'Referral',
  [LEAD_SOURCES.SOCIAL_MEDIA]: 'Social Media',
  [LEAD_SOURCES.EMAIL]: 'Email',
  [LEAD_SOURCES.PHONE]: 'Phone',
  [LEAD_SOURCES.WALK_IN]: 'Walk-in',
  [LEAD_SOURCES.OTHER]: 'Other'
}

// User Roles
export const USER_ROLES = {
  ADMIN: 'admin',
  MANAGER: 'manager',
  SALES_REP: 'sales_rep'
}

export const USER_ROLE_LABELS = {
  [USER_ROLES.ADMIN]: 'Administrator',
  [USER_ROLES.MANAGER]: 'Manager',
  [USER_ROLES.SALES_REP]: 'Sales Representative'
}

// Task Priority
export const TASK_PRIORITY = {
  LOW: 'low',
  MEDIUM: 'medium',
  HIGH: 'high'
}

export const TASK_PRIORITY_LABELS = {
  [TASK_PRIORITY.LOW]: 'Low',
  [TASK_PRIORITY.MEDIUM]: 'Medium',
  [TASK_PRIORITY.HIGH]: 'High'
}

export const TASK_PRIORITY_COLORS = {
  [TASK_PRIORITY.LOW]: '#6c757d',
  [TASK_PRIORITY.MEDIUM]: '#ffc107',
  [TASK_PRIORITY.HIGH]: '#dc3545'
}

// Task Status
export const TASK_STATUS = {
  PENDING: 'pending',
  IN_PROGRESS: 'in_progress',
  COMPLETED: 'completed',
  CANCELLED: 'cancelled'
}

export const TASK_STATUS_LABELS = {
  [TASK_STATUS.PENDING]: 'Pending',
  [TASK_STATUS.IN_PROGRESS]: 'In Progress',
  [TASK_STATUS.COMPLETED]: 'Completed',
  [TASK_STATUS.CANCELLED]: 'Cancelled'
}

// Broadcast Status
export const BROADCAST_STATUS = {
  DRAFT: 'draft',
  SCHEDULED: 'scheduled',
  ACTIVE: 'active',
  PAUSED: 'paused',
  COMPLETED: 'completed',
  FAILED: 'failed'
}

export const BROADCAST_STATUS_LABELS = {
  [BROADCAST_STATUS.DRAFT]: 'Draft',
  [BROADCAST_STATUS.SCHEDULED]: 'Scheduled',
  [BROADCAST_STATUS.ACTIVE]: 'Active',
  [BROADCAST_STATUS.PAUSED]: 'Paused',
  [BROADCAST_STATUS.COMPLETED]: 'Completed',
  [BROADCAST_STATUS.FAILED]: 'Failed'
}

export const BROADCAST_STATUS_COLORS = {
  [BROADCAST_STATUS.DRAFT]: '#6c757d',
  [BROADCAST_STATUS.SCHEDULED]: '#17a2b8',
  [BROADCAST_STATUS.ACTIVE]: '#28a745',
  [BROADCAST_STATUS.PAUSED]: '#ffc107',
  [BROADCAST_STATUS.COMPLETED]: '#007bff',
  [BROADCAST_STATUS.FAILED]: '#dc3545'
}

// Interaction Types
export const INTERACTION_TYPES = {
  CALL: 'call',
  EMAIL: 'email',
  MEETING: 'meeting',
  NOTE: 'note',
  WHATSAPP: 'whatsapp',
  SMS: 'sms'
}

export const INTERACTION_TYPE_LABELS = {
  [INTERACTION_TYPES.CALL]: 'Phone Call',
  [INTERACTION_TYPES.EMAIL]: 'Email',
  [INTERACTION_TYPES.MEETING]: 'Meeting',
  [INTERACTION_TYPES.NOTE]: 'Note',
  [INTERACTION_TYPES.WHATSAPP]: 'WhatsApp',
  [INTERACTION_TYPES.SMS]: 'SMS'
}

export const INTERACTION_TYPE_ICONS = {
  [INTERACTION_TYPES.CALL]: 'fas fa-phone',
  [INTERACTION_TYPES.EMAIL]: 'fas fa-envelope',
  [INTERACTION_TYPES.MEETING]: 'fas fa-calendar',
  [INTERACTION_TYPES.NOTE]: 'fas fa-sticky-note',
  [INTERACTION_TYPES.WHATSAPP]: 'fab fa-whatsapp',
  [INTERACTION_TYPES.SMS]: 'fas fa-sms'
}

export const INTERACTION_TYPE_COLORS = {
  [INTERACTION_TYPES.CALL]: '#17a2b8',
  [INTERACTION_TYPES.EMAIL]: '#6f42c1',
  [INTERACTION_TYPES.MEETING]: '#fd7e14',
  [INTERACTION_TYPES.NOTE]: '#6c757d',
  [INTERACTION_TYPES.WHATSAPP]: '#25d366',
  [INTERACTION_TYPES.SMS]: '#007bff'
}

// Message Status
export const MESSAGE_STATUS = {
  PENDING: 'pending',
  SENT: 'sent',
  DELIVERED: 'delivered',
  READ: 'read',
  FAILED: 'failed'
}

export const MESSAGE_STATUS_LABELS = {
  [MESSAGE_STATUS.PENDING]: 'Pending',
  [MESSAGE_STATUS.SENT]: 'Sent',
  [MESSAGE_STATUS.DELIVERED]: 'Delivered',
  [MESSAGE_STATUS.READ]: 'Read',
  [MESSAGE_STATUS.FAILED]: 'Failed'
}

// Pagination
export const DEFAULT_PAGE_SIZE = 20
export const PAGE_SIZE_OPTIONS = [10, 20, 50, 100]

// Date Ranges
export const DATE_RANGES = {
  TODAY: 'today',
  YESTERDAY: 'yesterday',
  LAST_7_DAYS: 'last_7_days',
  LAST_30_DAYS: 'last_30_days',
  LAST_90_DAYS: 'last_90_days',
  THIS_MONTH: 'this_month',
  LAST_MONTH: 'last_month',
  THIS_YEAR: 'this_year',
  CUSTOM: 'custom'
}

export const DATE_RANGE_LABELS = {
  [DATE_RANGES.TODAY]: 'Today',
  [DATE_RANGES.YESTERDAY]: 'Yesterday',
  [DATE_RANGES.LAST_7_DAYS]: 'Last 7 Days',
  [DATE_RANGES.LAST_30_DAYS]: 'Last 30 Days',
  [DATE_RANGES.LAST_90_DAYS]: 'Last 90 Days',
  [DATE_RANGES.THIS_MONTH]: 'This Month',
  [DATE_RANGES.LAST_MONTH]: 'Last Month',
  [DATE_RANGES.THIS_YEAR]: 'This Year',
  [DATE_RANGES.CUSTOM]: 'Custom Range'
}

// Currencies
export const CURRENCIES = {
  IDR: 'IDR',
  USD: 'USD',
  EUR: 'EUR'
}

export const CURRENCY_SYMBOLS = {
  [CURRENCIES.IDR]: 'Rp',
  [CURRENCIES.USD]: '$',
  [CURRENCIES.EUR]: '€'
}

// Timezones (Indonesia)
export const TIMEZONES = {
  WIB: 'Asia/Jakarta',
  WITA: 'Asia/Makassar',
  WIT: 'Asia/Jayapura'
}

export const TIMEZONE_LABELS = {
  [TIMEZONES.WIB]: 'WIB (Jakarta)',
  [TIMEZONES.WITA]: 'WITA (Makassar)',
  [TIMEZONES.WIT]: 'WIT (Jayapura)'
}

// WhatsApp Rate Limits
export const WHATSAPP_RATE_LIMIT = {
  MIN: 1,
  MAX: 30,
  DEFAULT: 20,
  RECOMMENDED: 15
}

// Lead Score Ranges
export const LEAD_SCORE_RANGES = {
  LOW: { min: 0, max: 39, label: 'Low', color: '#dc3545' },
  MEDIUM: { min: 40, max: 69, label: 'Medium', color: '#ffc107' },
  HIGH: { min: 70, max: 100, label: 'High', color: '#28a745' }
}

// Chart Colors
export const CHART_COLORS = [
  '#007bff', '#28a745', '#dc3545', '#ffc107', '#17a2b8',
  '#6f42c1', '#fd7e14', '#20c997', '#e83e8c', '#6c757d'
]

// File Upload
export const MAX_FILE_SIZE = 5 * 1024 * 1024 // 5MB
export const ALLOWED_IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp']
export const ALLOWED_DOCUMENT_TYPES = [
  'application/pdf',
  'application/msword',
  'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
  'application/vnd.ms-excel',
  'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
]

// API Configuration
export const API_TIMEOUT = 30000 // 30 seconds
export const API_RETRY_ATTEMPTS = 3
export const API_RETRY_DELAY = 1000 // 1 second

// Local Storage Keys
export const STORAGE_KEYS = {
  TOKEN: 'token',
  REFRESH_TOKEN: 'refresh_token',
  USER: 'user',
  SETTINGS: 'settings',
  THEME: 'theme',
  SIDEBAR_COLLAPSED: 'sidebar_collapsed'
}

// Notification Types
export const NOTIFICATION_TYPES = {
  SUCCESS: 'success',
  ERROR: 'error',
  WARNING: 'warning',
  INFO: 'info'
}

// Default Values
export const DEFAULTS = {
  AVATAR_COLOR: '#667eea',
  CURRENCY: CURRENCIES.IDR,
  TIMEZONE: TIMEZONES.WIB,
  LANGUAGE: 'id-ID',
  PAGE_SIZE: DEFAULT_PAGE_SIZE,
  WHATSAPP_RATE_LIMIT: WHATSAPP_RATE_LIMIT.DEFAULT
}

// Regular Expressions
export const REGEX = {
  EMAIL: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
  PHONE: /^(\+62|62|0)[0-9]{9,12}$/,
  URL: /^https?:\/\/.+/,
  ALPHANUMERIC: /^[a-zA-Z0-9]+$/,
  NUMERIC: /^[0-9]+$/
}

// Error Messages
export const ERROR_MESSAGES = {
  REQUIRED: 'This field is required',
  INVALID_EMAIL: 'Invalid email address',
  INVALID_PHONE: 'Invalid phone number',
  INVALID_URL: 'Invalid URL',
  MIN_LENGTH: (min) => `Minimum ${min} characters required`,
  MAX_LENGTH: (max) => `Maximum ${max} characters allowed`,
  MIN_VALUE: (min) => `Minimum value is ${min}`,
  MAX_VALUE: (max) => `Maximum value is ${max}`,
  FILE_TOO_LARGE: `File size exceeds ${MAX_FILE_SIZE / 1024 / 1024}MB`,
  INVALID_FILE_TYPE: 'Invalid file type',
  NETWORK_ERROR: 'Network error. Please check your connection.',
  SERVER_ERROR: 'Server error. Please try again later.',
  UNAUTHORIZED: 'You are not authorized to perform this action.',
  NOT_FOUND: 'Resource not found.'
}

export default {
  LEAD_STATUS,
  LEAD_STATUS_LABELS,
  LEAD_STATUS_COLORS,
  LEAD_SOURCES,
  LEAD_SOURCE_LABELS,
  USER_ROLES,
  USER_ROLE_LABELS,
  TASK_PRIORITY,
  TASK_PRIORITY_LABELS,
  TASK_PRIORITY_COLORS,
  TASK_STATUS,
  TASK_STATUS_LABELS,
  BROADCAST_STATUS,
  BROADCAST_STATUS_LABELS,
  BROADCAST_STATUS_COLORS,
  INTERACTION_TYPES,
  INTERACTION_TYPE_LABELS,
  INTERACTION_TYPE_ICONS,
  INTERACTION_TYPE_COLORS,
  MESSAGE_STATUS,
  MESSAGE_STATUS_LABELS,
  DEFAULT_PAGE_SIZE,
  PAGE_SIZE_OPTIONS,
  DATE_RANGES,
  DATE_RANGE_LABELS,
  CURRENCIES,
  CURRENCY_SYMBOLS,
  TIMEZONES,
  TIMEZONE_LABELS,
  WHATSAPP_RATE_LIMIT,
  LEAD_SCORE_RANGES,
  CHART_COLORS,
  MAX_FILE_SIZE,
  ALLOWED_IMAGE_TYPES,
  ALLOWED_DOCUMENT_TYPES,
  API_TIMEOUT,
  API_RETRY_ATTEMPTS,
  API_RETRY_DELAY,
  STORAGE_KEYS,
  NOTIFICATION_TYPES,
  DEFAULTS,
  REGEX,
  ERROR_MESSAGES
}
