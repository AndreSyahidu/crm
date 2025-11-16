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
          <i class="fas fa-download"></i> Export CSV
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
              <canvas ref="revenueChart" height="80"></canvas>
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
              <canvas ref="sourcesChart" height="80"></canvas>
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
              <canvas ref="pipelineChart" height="80"></canvas>
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
import { ref, onMounted } from 'vue'
import api from '../services/api'
import { useToast } from '../composables/useToast'

const { success, error } = useToast()

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
  revenue_trend: [],
  lead_sources: {},
  pipeline_stages: {},
  team_performance: [],
  whatsapp: {},
  recent_activities: []
})

// Chart refs
const revenueChart = ref(null)
const sourcesChart = ref(null)
const pipelineChart = ref(null)

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
  } catch (err) {
    console.error('Error fetching analytics:', err)
    error('Failed to load analytics data')
  } finally {
    loading.value = false
  }
}

const renderCharts = () => {
  renderRevenueChart()
  renderSourcesChart()
  renderPipelineChart()
}

// Revenue Line Chart
const renderRevenueChart = () => {
  if (!revenueChart.value) return

  const canvas = revenueChart.value
  const ctx = canvas.getContext('2d')
  const data = analytics.value.revenue_trend || generateSampleRevenueTrend()

  // Clear canvas
  ctx.clearRect(0, 0, canvas.width, canvas.height)

  // Set canvas size
  canvas.width = canvas.offsetWidth
  canvas.height = 300

  const padding = 40
  const chartWidth = canvas.width - padding * 2
  const chartHeight = canvas.height - padding * 2

  // Find max value
  const maxValue = Math.max(...data.map(d => d.value), 1)

  // Draw axes
  ctx.strokeStyle = '#e0e0e0'
  ctx.lineWidth = 1
  ctx.beginPath()
  ctx.moveTo(padding, padding)
  ctx.lineTo(padding, canvas.height - padding)
  ctx.lineTo(canvas.width - padding, canvas.height - padding)
  ctx.stroke()

  // Draw grid lines
  ctx.strokeStyle = '#f5f5f5'
  ctx.lineWidth = 1
  for (let i = 0; i <= 5; i++) {
    const y = padding + (chartHeight / 5) * i
    ctx.beginPath()
    ctx.moveTo(padding, y)
    ctx.lineTo(canvas.width - padding, y)
    ctx.stroke()
  }

  // Draw line
  ctx.strokeStyle = '#007bff'
  ctx.lineWidth = 3
  ctx.beginPath()

  data.forEach((point, index) => {
    const x = padding + (chartWidth / (data.length - 1)) * index
    const y = canvas.height - padding - (point.value / maxValue) * chartHeight

    if (index === 0) {
      ctx.moveTo(x, y)
    } else {
      ctx.lineTo(x, y)
    }
  })

  ctx.stroke()

  // Draw points
  ctx.fillStyle = '#007bff'
  data.forEach((point, index) => {
    const x = padding + (chartWidth / (data.length - 1)) * index
    const y = canvas.height - padding - (point.value / maxValue) * chartHeight

    ctx.beginPath()
    ctx.arc(x, y, 4, 0, Math.PI * 2)
    ctx.fill()
  })

  // Draw labels
  ctx.fillStyle = '#666'
  ctx.font = '12px Arial'
  ctx.textAlign = 'center'
  data.forEach((point, index) => {
    const x = padding + (chartWidth / (data.length - 1)) * index
    const y = canvas.height - padding + 20
    ctx.fillText(point.label, x, y)
  })

  // Draw Y-axis labels
  ctx.textAlign = 'right'
  for (let i = 0; i <= 5; i++) {
    const value = (maxValue / 5) * (5 - i)
    const y = padding + (chartHeight / 5) * i + 5
    ctx.fillText(formatCurrency(value), padding - 10, y)
  }
}

// Sources Bar Chart
const renderSourcesChart = () => {
  if (!sourcesChart.value) return

  const canvas = sourcesChart.value
  const ctx = canvas.getContext('2d')
  const data = analytics.value.lead_sources || generateSampleSources()

  // Clear canvas
  ctx.clearRect(0, 0, canvas.width, canvas.height)

  // Set canvas size
  canvas.width = canvas.offsetWidth
  canvas.height = 300

  const entries = Object.entries(data)
  if (entries.length === 0) return

  const padding = 40
  const chartWidth = canvas.width - padding * 2
  const chartHeight = canvas.height - padding * 2
  const barWidth = chartWidth / entries.length - 10
  const maxValue = Math.max(...entries.map(([, value]) => value), 1)

  const colors = ['#007bff', '#28a745', '#dc3545', '#ffc107', '#17a2b8', '#6f42c1', '#fd7e14']

  // Draw bars
  entries.forEach(([source, value], index) => {
    const x = padding + (chartWidth / entries.length) * index + 5
    const barHeight = (value / maxValue) * chartHeight
    const y = canvas.height - padding - barHeight

    // Draw bar
    ctx.fillStyle = colors[index % colors.length]
    ctx.fillRect(x, y, barWidth, barHeight)

    // Draw value on top
    ctx.fillStyle = '#333'
    ctx.font = 'bold 12px Arial'
    ctx.textAlign = 'center'
    ctx.fillText(value, x + barWidth / 2, y - 5)

    // Draw label
    ctx.fillStyle = '#666'
    ctx.font = '11px Arial'
    ctx.save()
    ctx.translate(x + barWidth / 2, canvas.height - padding + 15)
    ctx.rotate(-Math.PI / 6)
    ctx.fillText(source, 0, 0)
    ctx.restore()
  })
}

// Pipeline Doughnut Chart
const renderPipelineChart = () => {
  if (!pipelineChart.value) return

  const canvas = pipelineChart.value
  const ctx = canvas.getContext('2d')
  const data = analytics.value.pipeline_stages || generateSamplePipeline()

  // Clear canvas
  ctx.clearRect(0, 0, canvas.width, canvas.height)

  // Set canvas size
  canvas.width = canvas.offsetWidth
  canvas.height = 300

  const entries = Object.entries(data)
  if (entries.length === 0) return

  const centerX = canvas.width / 2
  const centerY = canvas.height / 2
  const radius = Math.min(centerX, centerY) - 60
  const innerRadius = radius * 0.6

  const total = entries.reduce((sum, [, value]) => sum + value, 0)
  let currentAngle = -Math.PI / 2

  const colors = ['#007bff', '#28a745', '#ffc107', '#dc3545', '#17a2b8', '#6f42c1']

  // Draw slices
  entries.forEach(([stage, value], index) => {
    const sliceAngle = (value / total) * Math.PI * 2
    const endAngle = currentAngle + sliceAngle

    // Draw outer arc
    ctx.fillStyle = colors[index % colors.length]
    ctx.beginPath()
    ctx.arc(centerX, centerY, radius, currentAngle, endAngle)
    ctx.arc(centerX, centerY, innerRadius, endAngle, currentAngle, true)
    ctx.closePath()
    ctx.fill()

    // Draw label
    const labelAngle = currentAngle + sliceAngle / 2
    const labelX = centerX + Math.cos(labelAngle) * (radius + 30)
    const labelY = centerY + Math.sin(labelAngle) * (radius + 30)

    ctx.fillStyle = '#333'
    ctx.font = 'bold 11px Arial'
    ctx.textAlign = labelX > centerX ? 'left' : 'right'
    ctx.textBaseline = 'middle'
    ctx.fillText(`${stage}: ${value}`, labelX, labelY)

    currentAngle = endAngle
  })

  // Draw center circle
  ctx.fillStyle = 'white'
  ctx.beginPath()
  ctx.arc(centerX, centerY, innerRadius, 0, Math.PI * 2)
  ctx.fill()

  // Draw total in center
  ctx.fillStyle = '#333'
  ctx.font = 'bold 18px Arial'
  ctx.textAlign = 'center'
  ctx.textBaseline = 'middle'
  ctx.fillText(`Total: ${total}`, centerX, centerY)
}

// Export to CSV
const exportReport = () => {
  try {
    let csv = 'WhatsApp CRM Analytics Report\n\n'
    csv += `Date Range: Last ${dateRange.value} days\n`
    csv += `Generated: ${new Date().toLocaleString('id-ID')}\n\n`

    // Overview Stats
    csv += 'OVERVIEW\n'
    csv += 'Metric,Value,Change\n'
    csv += `Total Revenue,Rp ${formatCurrency(analytics.value.total_revenue)},${analytics.value.revenue_change}%\n`
    csv += `Total Leads,${analytics.value.total_leads},${analytics.value.leads_change}%\n`
    csv += `Conversion Rate,${analytics.value.conversion_rate}%,${analytics.value.conversion_change}%\n`
    csv += `Avg Deal Value,Rp ${formatCurrency(analytics.value.avg_deal_value)},${analytics.value.deal_value_change}%\n\n`

    // Team Performance
    if (analytics.value.team_performance && analytics.value.team_performance.length > 0) {
      csv += 'TEAM PERFORMANCE\n'
      csv += 'Name,Leads,Deals Won,Revenue,Conversion Rate,Avg Response Time\n'
      analytics.value.team_performance.forEach(member => {
        csv += `${member.name},${member.leads_count},${member.deals_won},Rp ${formatCurrency(member.revenue)},${member.conversion_rate}%,${member.avg_response_time}\n`
      })
      csv += '\n'
    }

    // Lead Sources
    if (analytics.value.lead_sources) {
      csv += 'LEAD SOURCES\n'
      csv += 'Source,Count\n'
      Object.entries(analytics.value.lead_sources).forEach(([source, count]) => {
        csv += `${source},${count}\n`
      })
      csv += '\n'
    }

    // Pipeline Stages
    if (analytics.value.pipeline_stages) {
      csv += 'PIPELINE DISTRIBUTION\n'
      csv += 'Stage,Count\n'
      Object.entries(analytics.value.pipeline_stages).forEach(([stage, count]) => {
        csv += `${stage},${count}\n`
      })
      csv += '\n'
    }

    // WhatsApp Stats
    if (analytics.value.whatsapp) {
      csv += 'WHATSAPP ACTIVITY\n'
      csv += `Messages Sent,${analytics.value.whatsapp.sent || 0}\n`
      csv += `Messages Received,${analytics.value.whatsapp.received || 0}\n`
      csv += `Response Rate,${analytics.value.whatsapp.response_rate || 0}%\n`
      csv += `Avg Response Time,${analytics.value.whatsapp.avg_response_time || 'N/A'}\n`
    }

    // Download CSV
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' })
    const link = document.createElement('a')
    const url = URL.createObjectURL(blob)
    link.setAttribute('href', url)
    link.setAttribute('download', `crm-analytics-${Date.now()}.csv`)
    link.style.visibility = 'hidden'
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)

    success('Analytics report exported successfully!')
  } catch (err) {
    console.error('Export error:', err)
    error('Failed to export report')
  }
}

// Sample data generators (fallback if API doesn't return data)
const generateSampleRevenueTrend = () => {
  const days = parseInt(dateRange.value)
  const data = []
  const interval = days > 30 ? 7 : 1
  const points = Math.min(Math.ceil(days / interval), 30)

  for (let i = 0; i < points; i++) {
    const date = new Date()
    date.setDate(date.getDate() - (points - i - 1) * interval)
    data.push({
      label: date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' }),
      value: Math.floor(Math.random() * 10000000) + 1000000
    })
  }
  return data
}

const generateSampleSources = () => {
  return {
    'WhatsApp': Math.floor(Math.random() * 100) + 50,
    'Website': Math.floor(Math.random() * 80) + 30,
    'Referral': Math.floor(Math.random() * 60) + 20,
    'Social Media': Math.floor(Math.random() * 70) + 25,
    'Email': Math.floor(Math.random() * 40) + 10
  }
}

const generateSamplePipeline = () => {
  return {
    'New': Math.floor(Math.random() * 50) + 20,
    'Contacted': Math.floor(Math.random() * 40) + 15,
    'Qualified': Math.floor(Math.random() * 30) + 10,
    'Proposal': Math.floor(Math.random() * 20) + 8,
    'Won': Math.floor(Math.random() * 15) + 5
  }
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

  // Re-render charts on window resize
  window.addEventListener('resize', renderCharts)
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
