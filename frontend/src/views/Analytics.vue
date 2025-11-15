<template>
  <div class="analytics-view">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h2 class="mb-1">Analytics & Reports</h2>
        <p class="text-muted mb-0">Comprehensive insights into your CRM performance</p>
      </div>
      <div class="d-flex gap-2">
        <select v-model="dateRange" class="form-select" @change="fetchAnalytics" style="width: auto;">
          <option value="7">Last 7 days</option>
          <option value="30">Last 30 days</option>
          <option value="90">Last 90 days</option>
          <option value="365">Last year</option>
        </select>
        <button class="btn btn-outline-primary" @click="exportReport">
          <i class="fas fa-download"></i> Export
        </button>
      </div>
    </div>

    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status"></div>
    </div>

    <div v-else>
      <!-- Overview Cards -->
      <div class="row g-3 mb-4">
        <div class="col-md-3">
          <div class="card border-0 shadow-sm stat-card">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <small class="text-muted d-block">Total Revenue</small>
                  <h4 class="mb-0">Rp {{ formatCurrency(analytics.total_revenue) }}</h4>
                  <small :class="analytics.revenue_change >= 0 ? 'text-success' : 'text-danger'">
                    <i :class="analytics.revenue_change >= 0 ? 'fas fa-arrow-up' : 'fas fa-arrow-down'"></i>
                    {{ Math.abs(analytics.revenue_change || 0) }}% vs last period
                  </small>
                </div>
                <i class="fas fa-dollar-sign fa-2x text-success opacity-25"></i>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card border-0 shadow-sm stat-card">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <small class="text-muted d-block">Total Leads</small>
                  <h4 class="mb-0">{{ analytics.total_leads || 0 }}</h4>
                  <small :class="analytics.leads_change >= 0 ? 'text-success' : 'text-danger'">
                    <i :class="analytics.leads_change >= 0 ? 'fas fa-arrow-up' : 'fas fa-arrow-down'"></i>
                    {{ Math.abs(analytics.leads_change || 0) }}% vs last period
                  </small>
                </div>
                <i class="fas fa-users fa-2x text-primary opacity-25"></i>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card border-0 shadow-sm stat-card">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <small class="text-muted d-block">Conversion Rate</small>
                  <h4 class="mb-0">{{ analytics.conversion_rate || 0 }}%</h4>
                  <small :class="analytics.conversion_change >= 0 ? 'text-success' : 'text-danger'">
                    <i :class="analytics.conversion_change >= 0 ? 'fas fa-arrow-up' : 'fas fa-arrow-down'"></i>
                    {{ Math.abs(analytics.conversion_change || 0) }}% vs last period
                  </small>
                </div>
                <i class="fas fa-chart-line fa-2x text-info opacity-25"></i>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card border-0 shadow-sm stat-card">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <small class="text-muted d-block">Avg Deal Value</small>
                  <h4 class="mb-0">Rp {{ formatCurrency(analytics.avg_deal_value) }}</h4>
                  <small :class="analytics.deal_value_change >= 0 ? 'text-success' : 'text-danger'">
                    <i :class="analytics.deal_value_change >= 0 ? 'fas fa-arrow-up' : 'fas fa-arrow-down'"></i>
                    {{ Math.abs(analytics.deal_value_change || 0) }}% vs last period
                  </small>
                </div>
                <i class="fas fa-hand-holding-usd fa-2x text-warning opacity-25"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Charts Row 1 -->
      <div class="row g-4 mb-4">
        <!-- Revenue Trend Chart -->
        <div class="col-lg-8">
          <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
              <h5 class="card-title mb-0">Revenue Over Time</h5>
            </div>
            <div class="card-body">
              <div class="chart-container">
                <canvas ref="revenueChart"></canvas>
              </div>
            </div>
          </div>
        </div>

        <!-- Conversion Funnel -->
        <div class="col-lg-4">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white">
              <h5 class="card-title mb-0">Conversion Funnel</h5>
            </div>
            <div class="card-body">
              <div class="funnel-chart">
                <div
                  v-for="(stage, index) in analytics.funnel"
                  :key="index"
                  class="funnel-stage"
                  :style="{ width: (100 - index * 15) + '%' }"
                >
                  <div class="funnel-label">
                    <strong>{{ stage.name }}</strong>
                    <span>{{ stage.count }} ({{ stage.percentage }}%)</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Charts Row 2 -->
      <div class="row g-4 mb-4">
        <!-- Lead Sources -->
        <div class="col-lg-6">
          <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
              <h5 class="card-title mb-0">Lead Sources</h5>
            </div>
            <div class="card-body">
              <div class="chart-container" style="max-height: 300px;">
                <canvas ref="sourcesChart"></canvas>
              </div>
            </div>
          </div>
        </div>

        <!-- Pipeline Distribution -->
        <div class="col-lg-6">
          <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
              <h5 class="card-title mb-0">Pipeline Distribution</h5>
            </div>
            <div class="card-body">
              <div class="chart-container" style="max-height: 300px;">
                <canvas ref="pipelineChart"></canvas>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Team Performance -->
      <div class="row g-4 mb-4">
        <div class="col-12">
          <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
              <h5 class="card-title mb-0">Team Performance</h5>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-hover">
                  <thead>
                    <tr>
                      <th>Name</th>
                      <th>Leads</th>
                      <th>Deals Won</th>
                      <th>Revenue</th>
                      <th>Conversion Rate</th>
                      <th>Avg Response Time</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="member in analytics.team_performance" :key="member.id">
                      <td>
                        <div class="d-flex align-items-center">
                          <div class="avatar-sm me-2">
                            {{ member.name.charAt(0) }}
                          </div>
                          <strong>{{ member.name }}</strong>
                        </div>
                      </td>
                      <td>{{ member.leads_count }}</td>
                      <td>
                        <span class="badge bg-success">{{ member.deals_won }}</span>
                      </td>
                      <td>Rp {{ formatCurrency(member.revenue) }}</td>
                      <td>
                        <div class="progress" style="height: 20px;">
                          <div
                            class="progress-bar"
                            :class="member.conversion_rate >= 30 ? 'bg-success' : 'bg-warning'"
                            :style="{ width: member.conversion_rate + '%' }"
                          >
                            {{ member.conversion_rate }}%
                          </div>
                        </div>
                      </td>
                      <td>{{ member.avg_response_time }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- WhatsApp Activity -->
      <div class="row g-4">
        <div class="col-lg-4">
          <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
              <h5 class="card-title mb-0">WhatsApp Activity</h5>
            </div>
            <div class="card-body">
              <div class="activity-stats">
                <div class="stat-row">
                  <span class="stat-label">Messages Sent</span>
                  <span class="stat-value text-primary">{{ analytics.whatsapp?.sent || 0 }}</span>
                </div>
                <div class="stat-row">
                  <span class="stat-label">Messages Received</span>
                  <span class="stat-value text-success">{{ analytics.whatsapp?.received || 0 }}</span>
                </div>
                <div class="stat-row">
                  <span class="stat-label">Response Rate</span>
                  <span class="stat-value text-info">{{ analytics.whatsapp?.response_rate || 0 }}%</span>
                </div>
                <div class="stat-row">
                  <span class="stat-label">Avg Response Time</span>
                  <span class="stat-value text-warning">{{ analytics.whatsapp?.avg_response_time || 'N/A' }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-8">
          <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
              <h5 class="card-title mb-0">Recent Activities</h5>
            </div>
            <div class="card-body">
              <div class="activities-list">
                <div v-for="activity in analytics.recent_activities" :key="activity.id" class="activity-item">
                  <div class="activity-icon" :class="'type-' + activity.type">
                    <i :class="getActivityIcon(activity.type)"></i>
                  </div>
                  <div class="activity-content">
                    <p class="mb-0">{{ activity.description }}</p>
                    <small class="text-muted">{{ formatDateTime(activity.created_at) }}</small>
                  </div>
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
import { ref, onMounted, onUnmounted } from 'vue'
import api from '../services/api'

// State
const loading = ref(true)
const dateRange = ref('30')
const analytics = ref({
  total_revenue: 0,
  total_leads: 0,
  conversion_rate: 0,
  avg_deal_value: 0,
  revenue_change: 0,
  leads_change: 0,
  conversion_change: 0,
  deal_value_change: 0,
  funnel: [],
  team_performance: [],
  whatsapp: {},
  recent_activities: []
})

// Chart refs
const revenueChart = ref(null)
const sourcesChart = ref(null)
const pipelineChart = ref(null)
let chartInstances = []

// Methods
const fetchAnalytics = async () => {
  loading.value = true
  try {
    const response = await api.get('/analytics/dashboard', {
      params: { days: dateRange.value }
    })
    analytics.value = response.data

    // Wait for next tick then render charts
    setTimeout(() => {
      renderCharts()
    }, 100)
  } catch (error) {
    console.error('Error fetching analytics:', error)
  } finally {
    loading.value = false
  }
}

const renderCharts = () => {
  // Destroy existing charts
  chartInstances.forEach(chart => chart?.destroy())
  chartInstances = []

  // Revenue Chart (placeholder - requires Chart.js)
  if (revenueChart.value) {
    const ctx = revenueChart.value.getContext('2d')
    ctx.font = '16px Arial'
    ctx.fillStyle = '#6c757d'
    ctx.textAlign = 'center'
    ctx.fillText('Chart.js required for visualization', revenueChart.value.width / 2, revenueChart.value.height / 2)
  }

  // Sources Chart (placeholder)
  if (sourcesChart.value) {
    const ctx = sourcesChart.value.getContext('2d')
    ctx.font = '16px Arial'
    ctx.fillStyle = '#6c757d'
    ctx.textAlign = 'center'
    ctx.fillText('Chart.js required for visualization', sourcesChart.value.width / 2, sourcesChart.value.height / 2)
  }

  // Pipeline Chart (placeholder)
  if (pipelineChart.value) {
    const ctx = pipelineChart.value.getContext('2d')
    ctx.font = '16px Arial'
    ctx.fillStyle = '#6c757d'
    ctx.textAlign = 'center'
    ctx.fillText('Chart.js required for visualization', pipelineChart.value.width / 2, pipelineChart.value.height / 2)
  }
}

const exportReport = () => {
  // TODO: Implement export functionality
  alert('Export functionality will generate PDF/Excel report')
}

const getActivityIcon = (type) => {
  const icons = {
    lead_created: 'fas fa-user-plus',
    deal_won: 'fas fa-trophy',
    message_sent: 'fas fa-paper-plane',
    task_completed: 'fas fa-check-circle'
  }
  return icons[type] || 'fas fa-info-circle'
}

const formatCurrency = (value) => {
  if (!value) return '0'
  return new Intl.NumberFormat('id-ID').format(value)
}

const formatDateTime = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleString('id-ID', {
    day: 'numeric',
    month: 'short',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// Lifecycle
onMounted(async () => {
  await fetchAnalytics()
})

onUnmounted(() => {
  chartInstances.forEach(chart => chart?.destroy())
})
</script>

<style scoped>
.analytics-view {
  padding: 1.5rem;
}

.stat-card {
  transition: transform 0.2s;
}

.stat-card:hover {
  transform: translateY(-4px);
}

.chart-container {
  position: relative;
  height: 300px;
}

.chart-container canvas {
  max-width: 100%;
}

.funnel-chart {
  padding: 2rem 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
}

.funnel-stage {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 1rem;
  border-radius: 4px;
  transition: all 0.3s;
}

.funnel-stage:nth-child(1) { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.funnel-stage:nth-child(2) { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
.funnel-stage:nth-child(3) { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
.funnel-stage:nth-child(4) { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }
.funnel-stage:nth-child(5) { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }

.funnel-stage:hover {
  transform: scale(1.05);
}

.funnel-label {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.avatar-sm {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
}

.activity-stats {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.stat-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem;
  background: #f8f9fa;
  border-radius: 6px;
}

.stat-label {
  font-weight: 500;
  color: #6c757d;
}

.stat-value {
  font-size: 1.25rem;
  font-weight: bold;
}

.activities-list {
  max-height: 300px;
  overflow-y: auto;
}

.activity-item {
  display: flex;
  gap: 1rem;
  padding: 1rem;
  border-bottom: 1px solid #f0f0f0;
}

.activity-item:last-child {
  border-bottom: none;
}

.activity-icon {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  flex-shrink: 0;
}

.activity-icon.type-lead_created {
  background: #28a745;
}

.activity-icon.type-deal_won {
  background: #ffc107;
}

.activity-icon.type-message_sent {
  background: #007bff;
}

.activity-icon.type-task_completed {
  background: #17a2b8;
}

.activity-content {
  flex: 1;
}
</style>
