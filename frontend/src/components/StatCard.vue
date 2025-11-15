<template>
  <div class="card border-0 shadow-sm stat-card" :class="{ 'stat-card-clickable': clickable }">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center">
        <div class="flex-grow-1">
          <small class="text-muted d-block stat-label">{{ label }}</small>
          <h4 class="mb-0 stat-value" :style="{ color: valueColor }">{{ formattedValue }}</h4>

          <!-- Change indicator -->
          <small v-if="change !== null" :class="changeClass" class="d-block mt-1">
            <i :class="changeIcon"></i>
            {{ Math.abs(change) }}{{ changeUnit }} {{ changeLabel }}
          </small>
        </div>

        <!-- Icon -->
        <div v-if="icon" class="stat-icon-container">
          <i :class="icon" :style="{ color: iconColor }"></i>
        </div>
      </div>

      <!-- Footer slot -->
      <div v-if="$slots.footer" class="stat-footer mt-2 pt-2 border-top">
        <slot name="footer"></slot>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  label: {
    type: String,
    required: true
  },
  value: {
    type: [String, Number],
    required: true
  },
  valueColor: {
    type: String,
    default: '#212529'
  },
  icon: {
    type: String,
    default: ''
  },
  iconColor: {
    type: String,
    default: '#007bff'
  },
  change: {
    type: Number,
    default: null
  },
  changeUnit: {
    type: String,
    default: '%'
  },
  changeLabel: {
    type: String,
    default: 'vs last period'
  },
  clickable: {
    type: Boolean,
    default: false
  },
  prefix: {
    type: String,
    default: ''
  },
  suffix: {
    type: String,
    default: ''
  }
})

const formattedValue = computed(() => {
  return `${props.prefix}${props.value}${props.suffix}`
})

const changeClass = computed(() => {
  if (props.change === null) return ''
  return props.change >= 0 ? 'text-success' : 'text-danger'
})

const changeIcon = computed(() => {
  if (props.change === null) return ''
  return props.change >= 0 ? 'fas fa-arrow-up' : 'fas fa-arrow-down'
})
</script>

<style scoped>
.stat-card {
  transition: transform 0.2s, box-shadow 0.2s;
  height: 100%;
}

.stat-card-clickable {
  cursor: pointer;
}

.stat-card-clickable:hover {
  transform: translateY(-4px);
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.stat-label {
  font-size: 0.875rem;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.stat-value {
  font-size: 1.75rem;
  font-weight: 700;
  line-height: 1.2;
}

.stat-icon-container {
  font-size: 2.5rem;
  opacity: 0.25;
  margin-left: 1rem;
}

.stat-footer {
  font-size: 0.875rem;
}
</style>
