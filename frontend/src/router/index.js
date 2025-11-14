import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: () => import('../views/Login.vue'),
    meta: { guest: true }
  },
  {
    path: '/',
    component: () => import('../views/Layout.vue'),
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'Dashboard',
        component: () => import('../views/Dashboard.vue')
      },
      {
        path: '/leads',
        name: 'Leads',
        component: () => import('../views/Leads.vue')
      },
      {
        path: '/leads/:id',
        name: 'LeadDetail',
        component: () => import('../views/LeadDetail.vue')
      },
      {
        path: '/pipeline',
        name: 'Pipeline',
        component: () => import('../views/Pipeline.vue')
      },
      {
        path: '/whatsapp',
        name: 'WhatsApp',
        component: () => import('../views/WhatsApp.vue')
      },
      {
        path: '/broadcasts',
        name: 'Broadcasts',
        component: () => import('../views/Broadcasts.vue')
      },
      {
        path: '/analytics',
        name: 'Analytics',
        component: () => import('../views/Analytics.vue')
      },
      {
        path: '/settings',
        name: 'Settings',
        component: () => import('../views/Settings.vue')
      }
    ]
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next('/login')
  } else if (to.meta.guest && authStore.isAuthenticated) {
    next('/')
  } else {
    next()
  }
})

export default router
