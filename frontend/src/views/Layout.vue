<template>
  <div class="d-flex">
    <!-- Sidebar -->
    <div class="sidebar" :class="{ show: sidebarOpen }">
      <div class="sidebar-logo">
        <h4><i class="fab fa-whatsapp me-2"></i>WhatsApp CRM</h4>
      </div>

      <ul class="sidebar-menu">
        <li class="sidebar-menu-item">
          <router-link to="/" class="sidebar-menu-link" exact-active-class="active">
            <i class="fas fa-home"></i>
            <span>Dashboard</span>
          </router-link>
        </li>
        <li class="sidebar-menu-item">
          <router-link to="/leads" class="sidebar-menu-link" active-class="active">
            <i class="fas fa-users"></i>
            <span>Leads</span>
          </router-link>
        </li>
        <li class="sidebar-menu-item">
          <router-link to="/pipeline" class="sidebar-menu-link" active-class="active">
            <i class="fas fa-columns"></i>
            <span>Pipeline</span>
          </router-link>
        </li>
        <li class="sidebar-menu-item">
          <router-link to="/whatsapp" class="sidebar-menu-link" active-class="active">
            <i class="fab fa-whatsapp"></i>
            <span>WhatsApp</span>
          </router-link>
        </li>
        <li class="sidebar-menu-item">
          <router-link to="/broadcasts" class="sidebar-menu-link" active-class="active">
            <i class="fas fa-bullhorn"></i>
            <span>Broadcasts</span>
          </router-link>
        </li>
        <li class="sidebar-menu-item">
          <router-link to="/tasks" class="sidebar-menu-link" active-class="active">
            <i class="fas fa-tasks"></i>
            <span>Tasks</span>
          </router-link>
        </li>
        <li class="sidebar-menu-item">
          <router-link to="/segments" class="sidebar-menu-link" active-class="active">
            <i class="fas fa-layer-group"></i>
            <span>Segments</span>
          </router-link>
        </li>
        <li class="sidebar-menu-item">
          <router-link to="/followups" class="sidebar-menu-link" active-class="active">
            <i class="fas fa-redo"></i>
            <span>Follow-ups</span>
          </router-link>
        </li>
        <li class="sidebar-menu-item">
          <router-link to="/analytics" class="sidebar-menu-link" active-class="active">
            <i class="fas fa-chart-line"></i>
            <span>Analytics</span>
          </router-link>
        </li>
        <li class="sidebar-menu-item">
          <router-link to="/users" class="sidebar-menu-link" active-class="active">
            <i class="fas fa-user-friends"></i>
            <span>Users</span>
          </router-link>
        </li>
        <li class="sidebar-menu-item">
          <router-link to="/settings" class="sidebar-menu-link" active-class="active">
            <i class="fas fa-cog"></i>
            <span>Settings</span>
          </router-link>
        </li>
      </ul>
    </div>

    <!-- Main Content -->
    <div class="flex-grow-1">
      <!-- Top Navbar -->
      <nav class="top-navbar d-flex justify-content-between align-items-center">
        <div>
          <button class="btn btn-link d-md-none" @click="toggleSidebar">
            <i class="fas fa-bars"></i>
          </button>
        </div>

        <div class="d-flex align-items-center gap-3">
          <!-- Notifications -->
          <div class="dropdown">
            <button class="btn btn-link position-relative" data-bs-toggle="dropdown">
              <i class="fas fa-bell fa-lg"></i>
              <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                {{ unreadCount }}
              </span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><h6 class="dropdown-header">Notifications</h6></li>
              <li><a class="dropdown-item" href="#">New lead from WhatsApp</a></li>
              <li><a class="dropdown-item" href="#">Deal moved to proposal</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-center" href="#">View all</a></li>
            </ul>
          </div>

          <!-- User Menu -->
          <div class="dropdown">
            <button class="btn btn-link d-flex align-items-center gap-2" data-bs-toggle="dropdown">
              <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
                   style="width: 35px; height: 35px;">
                {{ userInitials }}
              </div>
              <span class="d-none d-md-inline">{{ user?.name }}</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Profile</a></li>
              <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Settings</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="#" @click.prevent="handleLogout">
                <i class="fas fa-sign-out-alt me-2"></i>Logout
              </a></li>
            </ul>
          </div>
        </div>
      </nav>

      <!-- Page Content -->
      <div class="p-4">
        <router-view />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const sidebarOpen = ref(false)
const unreadCount = ref(5)

const user = computed(() => authStore.user)
const userInitials = computed(() => {
  if (!user.value) return ''
  return user.value.name
    .split(' ')
    .map(n => n[0])
    .join('')
    .toUpperCase()
    .slice(0, 2)
})

const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value
}

const handleLogout = async () => {
  await authStore.logout()
  router.push('/login')
}
</script>

<style scoped>
.sidebar {
  width: 250px;
  position: sticky;
  top: 0;
}

@media (max-width: 768px) {
  .sidebar {
    position: fixed;
    left: -250px;
    top: 0;
    bottom: 0;
    z-index: 1000;
    transition: left 0.3s;
  }

  .sidebar.show {
    left: 0;
  }
}
</style>
