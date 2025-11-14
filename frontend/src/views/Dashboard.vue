<template>
  <div class="dashboard">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h2 class="mb-0">Dashboard</h2>
        <p class="text-muted">Welcome back, {{ user?.name }}!</p>
      </div>
      <div class="text-muted">
        <i class="fas fa-calendar me-2"></i>
        {{ currentDate }}
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="spinner-wrapper">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
    </div>

    <!-- Dashboard Content -->
    <div v-else>
      <!-- Stats Cards -->
      <div class="row mb-4">
        <div class="col-md-3 mb-3">
          <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <p class="text-muted mb-1">Total Leads</p>
                <h3 class="mb-0">{{ stats.total_leads || 0 }}</h3>
                <small class="text-success">
                  <i class="fas fa-arrow-up"></i>
                  +{{ stats.new_leads_this_month || 0 }} this month
                </small>
              </div>
              <div class="stat-card-icon bg-primary bg-opacity-10 text-primary">
                <i class="fas fa-users"></i>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-3 mb-3">
          <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <p class="text-muted mb-1">Active Deals</p>
                <h3 class="mb-0">{{ stats.active_deals || 0 }}</h3>
                <small class="text-info">
                  {{ formatCurrency(stats.pipeline_value || 0) }} in pipeline
                </small>
              </div>
              <div class="stat-card-icon bg-info bg-opacity-10 text-info">
                <i class="fas fa-briefcase"></i>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-3 mb-3">
          <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <p class="text-muted mb-1">Revenue (MTD)</p>
                <h3 class="mb-0">{{ formatCurrency(stats.revenue_this_month || 0) }}</h3>
                <small class="text-success">
                  <i class="fas fa-arrow-up"></i>
                  {{ stats.won_deals_this_month || 0 }} deals won
                </small>
              </div>
              <div class="stat-card-icon bg-success bg-opacity-10 text-success">
                <i class="fas fa-dollar-sign"></i>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-3 mb-3">
          <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <p class="text-muted mb-1">Conversion Rate</p>
                <h3 class="mb-0">{{ stats.conversion_rate || 0 }}%</h3>
                <small class="text-warning">
                  <i class="fas fa-chart-line"></i>
                  {{ stats.total_customers || 0 }} customers
                </small>
              </div>
              <div class="stat-card-icon bg-warning bg-opacity-10 text-warning">
                <i class="fas fa-percentage"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Charts Row -->
      <div class="row mb-4">
        <div class="col-md-8 mb-3">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title mb-4">Leads Over Time</h5>
              <div style="height: 300px;">
                <p class="text-muted text-center mt-5">Chart will be rendered here</p>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-4 mb-3">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title mb-4">Leads by Source</h5>
              <div style="height: 300px;">
                <p class="text-muted text-center mt-5">Pie chart will be rendered here</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Activity & Needs Attention -->
      <div class="row">
        <div class="col-md-8 mb-3">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title mb-4">Recent Activity</h5>
              <div class="activity-list">
                <div class="activity-item d-flex align-items-start mb-3" v-for="i in 5" :key="i">
                  <div class="activity-icon bg-primary bg-opacity-10 text-primary rounded-circle p-2 me-3">
                    <i class="fas fa-user-plus"></i>
                  </div>
                  <div class="flex-grow-1">
                    <p class="mb-0"><strong>New lead</strong> added from WhatsApp</p>
                    <small class="text-muted">2 hours ago</small>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-4 mb-3">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title mb-4">Needs Attention</h5>

              <div class="alert alert-warning mb-3" v-if="stats.leads_need_follow_up > 0">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <strong>{{ stats.leads_need_follow_up }}</strong> leads need follow-up
                  </div>
                  <router-link to="/leads" class="btn btn-sm btn-warning">View</router-link>
                </div>
              </div>

              <div class="alert alert-info mb-3" v-if="stats.deals_closing_soon > 0">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <strong>{{ stats.deals_closing_soon }}</strong> deals closing soon
                  </div>
                  <router-link to="/pipeline" class="btn btn-sm btn-info">View</router-link>
                </div>
              </div>

              <div class="alert alert-success mb-0">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <strong>{{ stats.whatsapp_inbound_today || 0 }}</strong> messages today
                  </div>
                  <router-link to="/whatsapp" class="btn btn-sm btn-success">Chat</router-link>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '../stores/auth'
import api from '../services/api'
import { format } from 'date-fns'

const authStore = useAuthStore()
const user = computed(() => authStore.user)

const loading = ref(true)
const stats = ref({})

const currentDate = computed(() => {
  return format(new Date(), 'EEEE, MMMM dd, yyyy')
})

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(amount)
}

const fetchDashboardStats = async () => {
  try {
    loading.value = true
    const response = await api.get('/analytics/dashboard')
    stats.value = response.data
  } catch (error) {
    console.error('Error fetching dashboard stats:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchDashboardStats()
})
</script>

<style scoped>
.activity-icon {
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
}
</style>
