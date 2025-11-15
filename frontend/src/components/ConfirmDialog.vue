<template>
  <div class="modal fade" :id="id" tabindex="-1" ref="modalRef">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header" :class="headerClass">
          <h5 class="modal-title">
            <i v-if="icon" :class="icon" class="me-2"></i>
            {{ title }}
          </h5>
          <button type="button" class="btn-close" :class="{ 'btn-close-white': variant === 'danger' }" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p class="mb-0">{{ message }}</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            {{ cancelText }}
          </button>
          <button
            type="button"
            :class="confirmButtonClass"
            @click="handleConfirm"
            :disabled="loading"
          >
            <span v-if="loading" class="spinner-border spinner-border-sm me-1"></span>
            {{ confirmText }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Modal } from 'bootstrap'

const props = defineProps({
  id: {
    type: String,
    default: 'confirmDialog'
  },
  title: {
    type: String,
    default: 'Confirm Action'
  },
  message: {
    type: String,
    required: true
  },
  confirmText: {
    type: String,
    default: 'Confirm'
  },
  cancelText: {
    type: String,
    default: 'Cancel'
  },
  variant: {
    type: String,
    default: 'primary',
    validator: (value) => ['primary', 'danger', 'warning', 'success'].includes(value)
  },
  icon: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['confirm', 'cancel'])

const modalRef = ref(null)
const loading = ref(false)
let modalInstance = null

const headerClass = computed(() => {
  const classes = {
    danger: 'bg-danger text-white',
    warning: 'bg-warning',
    success: 'bg-success text-white',
    primary: ''
  }
  return classes[props.variant]
})

const confirmButtonClass = computed(() => {
  return `btn btn-${props.variant}`
})

const show = () => {
  modalInstance?.show()
}

const hide = () => {
  modalInstance?.hide()
}

const handleConfirm = async () => {
  loading.value = true
  try {
    await emit('confirm')
    hide()
  } catch (error) {
    console.error('Confirm error:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  modalInstance = new Modal(modalRef.value)
})

defineExpose({ show, hide })
</script>

<style scoped>
.modal-header.bg-danger,
.modal-header.bg-success {
  border-bottom-color: rgba(255, 255, 255, 0.2);
}
</style>
