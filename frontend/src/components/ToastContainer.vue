<template>
  <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
    <transition-group name="toast">
      <div
        v-for="toast in toasts"
        :key="toast.id"
        class="toast show align-items-center border-0"
        :class="toastClass(toast.type)"
        role="alert"
      >
        <div class="d-flex">
          <div class="toast-body">
            <i :class="toastIcon(toast.type)" class="me-2"></i>
            {{ toast.message }}
          </div>
          <button
            type="button"
            class="btn-close btn-close-white me-2 m-auto"
            @click="removeToast(toast.id)"
          ></button>
        </div>
      </div>
    </transition-group>
  </div>
</template>

<script setup>
import { useToast } from '../composables/useToast'

const { toasts, removeToast } = useToast()

const toastClass = (type) => {
  const classes = {
    success: 'bg-success text-white',
    error: 'bg-danger text-white',
    warning: 'bg-warning text-dark',
    info: 'bg-info text-white'
  }
  return classes[type] || classes.info
}

const toastIcon = (type) => {
  const icons = {
    success: 'fas fa-check-circle',
    error: 'fas fa-exclamation-circle',
    warning: 'fas fa-exclamation-triangle',
    info: 'fas fa-info-circle'
  }
  return icons[type] || icons.info
}
</script>

<style scoped>
.toast {
  min-width: 300px;
  margin-bottom: 0.5rem;
}

.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}

.toast-enter-from {
  opacity: 0;
  transform: translateX(100%);
}

.toast-leave-to {
  opacity: 0;
  transform: translateX(100%);
}

.toast-move {
  transition: transform 0.3s ease;
}
</style>
