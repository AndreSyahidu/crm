<template>
  <span
    class="badge custom-badge"
    :class="badgeClass"
    :style="customStyle"
  >
    <i v-if="icon" :class="icon" class="me-1"></i>
    <slot>{{ text }}</slot>
  </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  text: {
    type: String,
    default: ''
  },
  variant: {
    type: String,
    default: 'primary',
    validator: (value) => [
      'primary', 'secondary', 'success', 'danger', 'warning',
      'info', 'light', 'dark', 'custom'
    ].includes(value)
  },
  icon: {
    type: String,
    default: ''
  },
  pill: {
    type: Boolean,
    default: false
  },
  outline: {
    type: Boolean,
    default: false
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  },
  color: {
    type: String,
    default: ''
  },
  backgroundColor: {
    type: String,
    default: ''
  }
})

const badgeClass = computed(() => {
  const classes = []

  if (props.variant !== 'custom') {
    if (props.outline) {
      classes.push(`badge-outline-${props.variant}`)
    } else {
      classes.push(`bg-${props.variant}`)
    }
  }

  if (props.pill) {
    classes.push('rounded-pill')
  }

  classes.push(`badge-${props.size}`)

  return classes.join(' ')
})

const customStyle = computed(() => {
  if (props.variant !== 'custom') return {}

  return {
    backgroundColor: props.backgroundColor,
    color: props.color,
    border: props.outline ? `1px solid ${props.backgroundColor}` : 'none'
  }
})
</script>

<style scoped>
.custom-badge {
  font-weight: 500;
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
}

.badge-sm {
  font-size: 0.7rem;
  padding: 0.25rem 0.5rem;
}

.badge-md {
  font-size: 0.8rem;
  padding: 0.35rem 0.65rem;
}

.badge-lg {
  font-size: 0.9rem;
  padding: 0.5rem 0.85rem;
}

/* Outline variants */
.badge-outline-primary {
  background: transparent;
  color: #007bff;
  border: 1px solid #007bff;
}

.badge-outline-success {
  background: transparent;
  color: #28a745;
  border: 1px solid #28a745;
}

.badge-outline-danger {
  background: transparent;
  color: #dc3545;
  border: 1px solid #dc3545;
}

.badge-outline-warning {
  background: transparent;
  color: #ffc107;
  border: 1px solid #ffc107;
}

.badge-outline-info {
  background: transparent;
  color: #17a2b8;
  border: 1px solid #17a2b8;
}

.badge-outline-secondary {
  background: transparent;
  color: #6c757d;
  border: 1px solid #6c757d;
}
</style>
