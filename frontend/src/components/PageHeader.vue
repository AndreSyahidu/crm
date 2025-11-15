<template>
  <div class="page-header mb-4">
    <div class="d-flex justify-content-between align-items-start">
      <div class="flex-grow-1">
        <!-- Back button -->
        <button
          v-if="showBack"
          class="btn btn-link p-0 mb-2"
          @click="handleBack"
        >
          <i class="fas fa-arrow-left"></i> Back
        </button>

        <!-- Title and description -->
        <h2 class="page-title mb-1">
          <i v-if="icon" :class="icon" class="me-2"></i>
          {{ title }}
        </h2>
        <p v-if="description" class="page-description text-muted mb-0">
          {{ description }}
        </p>

        <!-- Breadcrumbs slot -->
        <div v-if="$slots.breadcrumbs" class="mt-2">
          <slot name="breadcrumbs"></slot>
        </div>
      </div>

      <!-- Actions slot -->
      <div v-if="$slots.actions" class="page-actions">
        <slot name="actions"></slot>
      </div>
    </div>

    <!-- Tabs slot -->
    <div v-if="$slots.tabs" class="page-tabs mt-3">
      <slot name="tabs"></slot>
    </div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'

const props = defineProps({
  title: {
    type: String,
    required: true
  },
  description: {
    type: String,
    default: ''
  },
  icon: {
    type: String,
    default: ''
  },
  showBack: {
    type: Boolean,
    default: false
  },
  backPath: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['back'])
const router = useRouter()

const handleBack = () => {
  if (props.backPath) {
    router.push(props.backPath)
  } else {
    emit('back')
    router.back()
  }
}
</script>

<style scoped>
.page-header {
  padding-bottom: 1rem;
  border-bottom: 1px solid #e9ecef;
}

.page-title {
  font-size: 1.75rem;
  font-weight: 600;
  color: #212529;
  margin: 0;
}

.page-description {
  font-size: 0.95rem;
  line-height: 1.5;
}

.page-actions {
  display: flex;
  gap: 0.5rem;
  flex-shrink: 0;
  margin-left: 1rem;
}

.page-tabs {
  border-top: 1px solid #e9ecef;
  padding-top: 1rem;
}

@media (max-width: 768px) {
  .page-header {
    flex-direction: column;
  }

  .page-actions {
    margin-left: 0;
    margin-top: 1rem;
    width: 100%;
  }

  .page-title {
    font-size: 1.5rem;
  }
}
</style>
