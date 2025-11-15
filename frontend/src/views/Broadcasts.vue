<template>
  <div class="broadcasts-view">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h2 class="mb-1">Broadcast Campaigns</h2>
        <p class="text-muted mb-0">Send bulk WhatsApp messages to segmented audiences</p>
      </div>
      <button class="btn btn-success" @click="createCampaign">
        <i class="fas fa-bullhorn"></i> New Campaign
      </button>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
      <div class="col-md-3">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <small class="text-muted d-block">Total Campaigns</small>
                <h4 class="mb-0">{{ stats.total || 0 }}</h4>
              </div>
              <i class="fas fa-bullhorn fa-2x text-primary opacity-25"></i>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <small class="text-muted d-block">Active</small>
                <h4 class="mb-0">{{ stats.active || 0 }}</h4>
              </div>
              <i class="fas fa-play-circle fa-2x text-success opacity-25"></i>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <small class="text-muted d-block">Messages Sent</small>
                <h4 class="mb-0">{{ stats.messages_sent || 0 }}</h4>
              </div>
              <i class="fas fa-paper-plane fa-2x text-info opacity-25"></i>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <small class="text-muted d-block">Delivery Rate</small>
                <h4 class="mb-0">{{ stats.delivery_rate || 0 }}%</h4>
              </div>
              <i class="fas fa-check-circle fa-2x text-success opacity-25"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-4">
            <input
              v-model="filters.search"
              type="text"
              class="form-control"
              placeholder="Search campaigns..."
              @input="debouncedFetch"
            >
          </div>
          <div class="col-md-3">
            <select v-model="filters.status" class="form-select" @change="fetchCampaigns">
              <option value="">All Status</option>
              <option value="draft">Draft</option>
              <option value="scheduled">Scheduled</option>
              <option value="active">Active</option>
              <option value="paused">Paused</option>
              <option value="completed">Completed</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <!-- Campaigns List -->
    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status"></div>
    </div>

    <div v-else>
      <div class="row g-3">
        <div v-for="campaign in campaigns" :key="campaign.id" class="col-md-6 col-lg-4">
          <div class="card border-0 shadow-sm campaign-card">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                  <h5 class="mb-1">{{ campaign.name }}</h5>
                  <span class="badge" :class="getStatusClass(campaign.status)">
                    {{ campaign.status }}
                  </span>
                </div>
                <div class="dropdown">
                  <button
                    class="btn btn-sm btn-light"
                    type="button"
                    data-bs-toggle="dropdown"
                  >
                    <i class="fas fa-ellipsis-v"></i>
                  </button>
                  <ul class="dropdown-menu">
                    <li>
                      <a class="dropdown-item" href="#" @click.prevent="editCampaign(campaign)">
                        <i class="fas fa-edit"></i> Edit
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item" href="#" @click.prevent="duplicateCampaign(campaign)">
                        <i class="fas fa-copy"></i> Duplicate
                      </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                      <a class="dropdown-item text-danger" href="#" @click.prevent="deleteCampaign(campaign)">
                        <i class="fas fa-trash"></i> Delete
                      </a>
                    </li>
                  </ul>
                </div>
              </div>

              <p class="text-muted small mb-3">{{ campaign.message?.substring(0, 100) }}...</p>

              <div class="campaign-stats mb-3">
                <div class="stat-item">
                  <i class="fas fa-users text-primary"></i>
                  <span>{{ campaign.total_recipients || 0 }} recipients</span>
                </div>
                <div class="stat-item">
                  <i class="fas fa-paper-plane text-success"></i>
                  <span>{{ campaign.sent_count || 0 }} sent</span>
                </div>
                <div class="stat-item">
                  <i class="fas fa-check text-info"></i>
                  <span>{{ campaign.delivered_count || 0 }} delivered</span>
                </div>
                <div class="stat-item">
                  <i class="fas fa-times text-danger"></i>
                  <span>{{ campaign.failed_count || 0 }} failed</span>
                </div>
              </div>

              <div class="progress mb-3" style="height: 6px;">
                <div
                  class="progress-bar bg-success"
                  :style="{ width: getProgressPercent(campaign) + '%' }"
                ></div>
              </div>

              <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">
                  <i class="fas fa-clock"></i>
                  {{ campaign.scheduled_at ? formatDate(campaign.scheduled_at) : 'Not scheduled' }}
                </small>
                <div class="campaign-actions">
                  <button
                    v-if="campaign.status === 'draft' || campaign.status === 'scheduled'"
                    class="btn btn-sm btn-success"
                    @click="startCampaign(campaign)"
                  >
                    <i class="fas fa-play"></i> Start
                  </button>
                  <button
                    v-if="campaign.status === 'active'"
                    class="btn btn-sm btn-warning"
                    @click="pauseCampaign(campaign)"
                  >
                    <i class="fas fa-pause"></i> Pause
                  </button>
                  <button
                    v-if="campaign.status === 'paused'"
                    class="btn btn-sm btn-success"
                    @click="resumeCampaign(campaign)"
                  >
                    <i class="fas fa-play"></i> Resume
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div v-if="campaigns.length === 0" class="text-center py-5">
        <i class="fas fa-bullhorn fa-4x text-muted opacity-25 mb-3"></i>
        <h5 class="text-muted">No campaigns found</h5>
        <button class="btn btn-primary mt-3" @click="createCampaign">
          <i class="fas fa-plus"></i> Create Your First Campaign
        </button>
      </div>

      <!-- Pagination -->
      <nav v-if="pagination.last_page > 1" class="mt-4">
        <ul class="pagination justify-content-center">
          <li class="page-item" :class="{ disabled: pagination.current_page === 1 }">
            <a class="page-link" href="#" @click.prevent="fetchCampaigns(pagination.current_page - 1)">
              Previous
            </a>
          </li>
          <li
            v-for="page in pagination.last_page"
            :key="page"
            class="page-item"
            :class="{ active: page === pagination.current_page }"
          >
            <a class="page-link" href="#" @click.prevent="fetchCampaigns(page)">
              {{ page }}
            </a>
          </li>
          <li class="page-item" :class="{ disabled: pagination.current_page === pagination.last_page }">
            <a class="page-link" href="#" @click.prevent="fetchCampaigns(pagination.current_page + 1)">
              Next
            </a>
          </li>
        </ul>
      </nav>
    </div>

    <!-- Create/Edit Campaign Modal -->
    <div class="modal fade" id="campaignModal" tabindex="-1" ref="campaignModal">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ editingCampaign ? 'Edit Campaign' : 'New Campaign' }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="saveCampaign">
              <div class="mb-3">
                <label class="form-label">Campaign Name *</label>
                <input
                  v-model="campaignForm.name"
                  type="text"
                  class="form-control"
                  required
                  placeholder="e.g., Black Friday Promo"
                >
              </div>

              <div class="mb-3">
                <label class="form-label">Select Segment *</label>
                <select v-model="campaignForm.segment_id" class="form-select" required @change="previewRecipients">
                  <option value="">Choose segment...</option>
                  <option v-for="segment in segments" :key="segment.id" :value="segment.id">
                    {{ segment.name }} ({{ segment.cached_count || 0 }} contacts)
                  </option>
                </select>
                <small v-if="recipientCount > 0" class="text-muted">
                  {{ recipientCount }} recipients will receive this message
                </small>
              </div>

              <div class="mb-3">
                <label class="form-label">Message *</label>
                <textarea
                  v-model="campaignForm.message"
                  class="form-control"
                  rows="5"
                  required
                  placeholder="Type your message here... You can use {{name}} for personalization"
                ></textarea>
                <small class="text-muted">
                  Available variables: {{name}}, {{company}}, {{phone}}
                </small>
              </div>

              <div class="row g-3 mb-3">
                <div class="col-md-6">
                  <label class="form-label">Schedule Date & Time</label>
                  <input
                    v-model="campaignForm.scheduled_at"
                    type="datetime-local"
                    class="form-control"
                  >
                  <small class="text-muted">Leave empty to start immediately</small>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Rate Limit (messages/minute)</label>
                  <input
                    v-model.number="campaignForm.rate_limit"
                    type="number"
                    class="form-control"
                    min="1"
                    max="30"
                    value="20"
                  >
                  <small class="text-muted">Max 30/minute to avoid ban</small>
                </div>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary" @click="saveCampaign" :disabled="saving">
              <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
              {{ editingCampaign ? 'Update' : 'Create' }} Campaign
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../services/api'
import { Modal } from 'bootstrap'

// State
const loading = ref(false)
const saving = ref(false)
const campaigns = ref([])
const segments = ref([])
const stats = ref({})
const recipientCount = ref(0)
const editingCampaign = ref(null)
const campaignModal = ref(null)
let campaignModalInstance = null

const filters = ref({
  search: '',
  status: ''
})

const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 12,
  total: 0
})

const campaignForm = ref({
  name: '',
  segment_id: '',
  message: '',
  scheduled_at: '',
  rate_limit: 20
})

// Methods
const fetchCampaigns = async (page = 1) => {
  loading.value = true
  try {
    const params = { ...filters.value, page, per_page: pagination.value.per_page }
    const response = await api.get('/broadcasts', { params })
    campaigns.value = response.data.data
    pagination.value = {
      current_page: response.data.current_page,
      last_page: response.data.last_page,
      per_page: response.data.per_page,
      total: response.data.total
    }
  } catch (error) {
    console.error('Error fetching campaigns:', error)
  } finally {
    loading.value = false
  }
}

const fetchSegments = async () => {
  try {
    const response = await api.get('/segments')
    segments.value = response.data
  } catch (error) {
    console.error('Error fetching segments:', error)
  }
}

const fetchStats = async () => {
  try {
    const response = await api.get('/broadcasts/stats')
    stats.value = response.data
  } catch (error) {
    console.error('Error fetching stats:', error)
  }
}

const createCampaign = () => {
  editingCampaign.value = null
  campaignForm.value = {
    name: '',
    segment_id: '',
    message: '',
    scheduled_at: '',
    rate_limit: 20
  }
  recipientCount.value = 0
  campaignModalInstance.show()
}

const editCampaign = (campaign) => {
  editingCampaign.value = campaign
  campaignForm.value = {
    name: campaign.name,
    segment_id: campaign.segment_id,
    message: campaign.message,
    scheduled_at: campaign.scheduled_at,
    rate_limit: campaign.rate_limit || 20
  }
  recipientCount.value = campaign.total_recipients
  campaignModalInstance.show()
}

const saveCampaign = async () => {
  saving.value = true
  try {
    if (editingCampaign.value) {
      await api.put(`/broadcasts/${editingCampaign.value.id}`, campaignForm.value)
    } else {
      await api.post('/broadcasts', campaignForm.value)
    }
    campaignModalInstance.hide()
    await fetchCampaigns()
    await fetchStats()
  } catch (error) {
    console.error('Error saving campaign:', error)
    alert('Failed to save campaign: ' + (error.response?.data?.message || error.message))
  } finally {
    saving.value = false
  }
}

const previewRecipients = async () => {
  if (!campaignForm.value.segment_id) {
    recipientCount.value = 0
    return
  }
  try {
    const response = await api.get(`/broadcasts/preview/${campaignForm.value.segment_id}`)
    recipientCount.value = response.data.count
  } catch (error) {
    console.error('Error previewing recipients:', error)
  }
}

const startCampaign = async (campaign) => {
  if (!confirm(`Start campaign "${campaign.name}"? This will send messages to ${campaign.total_recipients} recipients.`)) {
    return
  }
  try {
    await api.post(`/broadcasts/${campaign.id}/start`)
    await fetchCampaigns()
    await fetchStats()
  } catch (error) {
    console.error('Error starting campaign:', error)
    alert('Failed to start campaign')
  }
}

const pauseCampaign = async (campaign) => {
  try {
    await api.post(`/broadcasts/${campaign.id}/pause`)
    await fetchCampaigns()
  } catch (error) {
    console.error('Error pausing campaign:', error)
  }
}

const resumeCampaign = async (campaign) => {
  try {
    await api.post(`/broadcasts/${campaign.id}/resume`)
    await fetchCampaigns()
  } catch (error) {
    console.error('Error resuming campaign:', error)
  }
}

const duplicateCampaign = async (campaign) => {
  try {
    await api.post(`/broadcasts/${campaign.id}/duplicate`)
    await fetchCampaigns()
  } catch (error) {
    console.error('Error duplicating campaign:', error)
  }
}

const deleteCampaign = async (campaign) => {
  if (!confirm(`Delete campaign "${campaign.name}"?`)) return
  try {
    await api.delete(`/broadcasts/${campaign.id}`)
    await fetchCampaigns()
    await fetchStats()
  } catch (error) {
    console.error('Error deleting campaign:', error)
  }
}

const getStatusClass = (status) => {
  const classes = {
    draft: 'bg-secondary',
    scheduled: 'bg-info',
    active: 'bg-success',
    paused: 'bg-warning',
    completed: 'bg-primary',
    failed: 'bg-danger'
  }
  return classes[status] || 'bg-secondary'
}

const getProgressPercent = (campaign) => {
  if (!campaign.total_recipients) return 0
  return Math.round((campaign.sent_count / campaign.total_recipients) * 100)
}

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const debouncedFetch = (() => {
  let timeout
  return () => {
    clearTimeout(timeout)
    timeout = setTimeout(() => fetchCampaigns(), 500)
  }
})()

// Lifecycle
onMounted(async () => {
  campaignModalInstance = new Modal(campaignModal.value)
  await Promise.all([
    fetchCampaigns(),
    fetchSegments(),
    fetchStats()
  ])
})
</script>

<style scoped>
.broadcasts-view {
  padding: 1.5rem;
}

.campaign-card {
  transition: transform 0.2s;
}

.campaign-card:hover {
  transform: translateY(-4px);
}

.campaign-stats {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.5rem;
}

.stat-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
}

.stat-item i {
  width: 16px;
}

.campaign-actions {
  display: flex;
  gap: 0.5rem;
}
</style>
