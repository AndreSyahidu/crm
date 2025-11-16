<template>
  <div id="app">
    <router-view />
    <ToastContainer />
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useAuthStore } from './stores/auth'
import ToastContainer from './components/ToastContainer.vue'

const authStore = useAuthStore()

onMounted(() => {
  // Check if user is logged in
  authStore.checkAuth()
})
</script>

<style>
#app {
  min-height: 100vh;
  background-color: #f8f9fa;
}
</style>
