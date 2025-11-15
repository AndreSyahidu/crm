<template>
  <div class="empty-state" :class="{ 'empty-state-sm': size === 'sm' }">
    <i :class="icon" class="empty-state-icon"></i>
    <h5 v-if="title" class="empty-state-title">{{ title }}</h5>
    <p v-if="description" class="empty-state-description">{{ description }}</p>
    <slot name="action"></slot>
  </div>
</template>

<script setup>
defineProps({
  icon: {
    type: String,
    default: 'fas fa-inbox'
  },
  title: {
    type: String,
    default: 'No data found'
  },
  description: {
    type: String,
    default: ''
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  }
})
</script>

<style scoped>
.empty-state {
  text-align: center;
  padding: 4rem 2rem;
  color: #6c757d;
}

.empty-state-sm {
  padding: 2rem 1rem;
}

.empty-state-icon {
  font-size: 4rem;
  opacity: 0.25;
  margin-bottom: 1rem;
  display: block;
}

.empty-state-sm .empty-state-icon {
  font-size: 2.5rem;
}

.empty-state-title {
  color: #495057;
  margin-bottom: 0.5rem;
}

.empty-state-description {
  color: #6c757d;
  margin-bottom: 1rem;
}
</style>
