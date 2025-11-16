<template>
  <div class="lead-detail-view">
    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status"></div>
    </div>

    <div v-else-if="lead" class="container-fluid">
      <!-- Header -->
      <div class="row mb-4">
        <div class="col-12">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <button class="btn btn-link p-0 mb-2" @click="$router.back()">
                <i class="fas fa-arrow-left"></i> Back
              </button>
              <h2 class="mb-1">{{ lead.name }}</h2>
              <div class="lead-meta">
                <span class="badge" :class="getStatusClass(lead.status)">{{ lead.status }}</span>
                <span class="text-muted ms-2">{{ lead.source }}</span>
                <span v-if="lead.company" class="text-muted ms-2">
                  <i class="fas fa-building"></i> {{ lead.company }}
                </span>
              </div>
            </div>
            <div class="d-flex gap-2">
              <button class="btn btn-outline-primary" @click="editLead">
                <i class="fas fa-edit"></i> Edit
              </button>
              <button class="btn btn-success" @click="convertToCustomer">
                <i class="fas fa-check-circle"></i> Convert
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="row g-4">
        <!-- Left Column - Main Info -->
        <div class="col-lg-8">
          <!-- Contact Info Card -->
          <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
              <h5 class="card-title mb-3">Contact Information</h5>
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="text-muted small">Email</label>
                  <p class="mb-0">{{ lead.email || '-' }}</p>
                </div>
                <div class="col-md-6">
                  <label class="text-muted small">Phone</label>
                  <p class="mb-0">{{ lead.phone || '-' }}</p>
                </div>
                <div class="col-md-6">
                  <label class="text-muted small">WhatsApp</label>
                  <p class="mb-0">
                    {{ lead.whatsapp_number || '-' }}
                    <a
                      v-if="lead.whatsapp_number"
                      :href="`https://wa.me/${lead.whatsapp_number}`"
                      target="_blank"
                      class="ms-2"
                    >
                      <i class="fab fa-whatsapp text-success"></i>
                    </a>
                  </p>
                </div>
                <div class="col-md-6">
                  <label class="text-muted small">Address</label>
                  <p class="mb-0">{{ lead.address || '-' }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Journey Timeline -->
          <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title mb-0">Customer Journey</h5>
                <button class="btn btn-sm btn-outline-primary" @click="addMilestone">
                  <i class="fas fa-plus"></i> Add Milestone
                </button>
              </div>

              <div class="timeline">
                <div
                  v-for="milestone in milestones"
                  :key="milestone.id"
                  class="timeline-item"
                >
                  <div class="timeline-marker" :style="{ backgroundColor: milestone.color || '#007bff' }"></div>
                  <div class="timeline-content">
                    <div class="d-flex justify-content-between align-items-start">
                      <div>
                        <h6 class="mb-1">{{ milestone.title }}</h6>
                        <p class="text-muted mb-0">{{ milestone.description }}</p>
                      </div>
                      <small class="text-muted">{{ formatDate(milestone.created_at) }}</small>
                    </div>
                  </div>
                </div>

                <div v-if="milestones.length === 0" class="text-center text-muted py-4">
                  No milestones yet
                </div>
              </div>
            </div>
          </div>

          <!-- Interactions -->
          <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title mb-0">Recent Interactions</h5>
                <button class="btn btn-sm btn-outline-primary" @click="logInteraction">
                  <i class="fas fa-plus"></i> Log Interaction
                </button>
              </div>

              <div class="interactions-list">
                <div
                  v-for="interaction in interactions"
                  :key="interaction.id"
                  class="interaction-item"
                >
                  <div class="interaction-icon" :class="'type-' + interaction.type">
                    <i :class="getInteractionIcon(interaction.type)"></i>
                  </div>
                  <div class="interaction-content">
                    <div class="d-flex justify-content-between align-items-start">
                      <div>
                        <h6 class="mb-1">{{ interaction.type }}</h6>
                        <p class="mb-0">{{ interaction.notes }}</p>
                      </div>
                      <small class="text-muted">{{ formatDateTime(interaction.created_at) }}</small>
                    </div>
                    <small class="text-muted">
                      by {{ interaction.user?.name || 'System' }}
                    </small>
                  </div>
                </div>

                <div v-if="interactions.length === 0" class="text-center text-muted py-4">
                  No interactions logged
                </div>
              </div>
            </div>
          </div>

          <!-- WhatsApp Messages -->
          <div class="card border-0 shadow-sm">
            <div class="card-body">
              <h5 class="card-title mb-3">WhatsApp Messages</h5>
              <div class="messages-list">
                <div
                  v-for="message in whatsappMessages"
                  :key="message.id"
                  class="message-item"
                  :class="message.direction"
                >
                  <div class="message-content">
                    <p class="mb-1">{{ message.content }}</p>
                    <small class="text-muted">{{ formatDateTime(message.timestamp) }}</small>
                  </div>
                </div>

                <div v-if="whatsappMessages.length === 0" class="text-center text-muted py-4">
                  No WhatsApp messages
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Column - Sidebar -->
        <div class="col-lg-4">
          <!-- Lead Score -->
          <div class="card border-0 shadow-sm mb-4">
            <div class="card-body text-center">
              <h6 class="text-muted mb-2">Lead Score</h6>
              <div class="score-circle" :class="getScoreClass(lead.lead_score)">
                <span class="score-value">{{ lead.lead_score || 0 }}</span>
              </div>
              <button class="btn btn-sm btn-outline-primary mt-2" @click="updateScore">
                <i class="fas fa-sync"></i> Recalculate
              </button>
            </div>
          </div>

          <!-- Deal Value -->
          <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
              <h6 class="text-muted mb-3">Deal Information</h6>
              <div class="info-item">
                <label>Expected Revenue</label>
                <h5 class="text-success mb-0">Rp {{ formatCurrency(lead.expected_revenue) }}</h5>
              </div>
              <div class="info-item">
                <label>Actual Revenue</label>
                <h5 class="mb-0">Rp {{ formatCurrency(lead.actual_revenue) }}</h5>
              </div>
              <div class="info-item">
                <label>LTV</label>
                <h5 class="mb-0">Rp {{ formatCurrency(lead.lifetime_value) }}</h5>
              </div>
              <div class="info-item">
                <label>ROI</label>
                <h5 class="mb-0" :class="lead.roi >= 0 ? 'text-success' : 'text-danger'">
                  {{ lead.roi ? lead.roi.toFixed(1) : 0 }}%
                </h5>
              </div>
            </div>
          </div>

          <!-- Assignment -->
          <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
              <h6 class="text-muted mb-3">Assignment</h6>
              <div class="info-item">
                <label>Assigned To</label>
                <p class="mb-0">{{ lead.assigned_user?.name || 'Unassigned' }}</p>
              </div>
              <div class="info-item">
                <label>Last Contact</label>
                <p class="mb-0">{{ formatDate(lead.last_contact_at) || 'Never' }}</p>
              </div>
              <div class="info-item">
                <label>Next Follow-up</label>
                <p class="mb-0">{{ formatDate(lead.next_followup_at) || 'Not scheduled' }}</p>
              </div>
            </div>
          </div>

          <!-- Tags -->
          <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
              <h6 class="text-muted mb-3">Tags</h6>
              <div class="tags-container">
                <span
                  v-for="tag in lead.tags"
                  :key="tag.id"
                  class="badge bg-primary me-1 mb-1"
                >
                  {{ tag.name }}
                </span>
                <button class="btn btn-sm btn-outline-secondary mt-2" @click="manageTags">
                  <i class="fas fa-tag"></i> Manage Tags
                </button>
              </div>
            </div>
          </div>

          <!-- Tasks -->
          <div class="card border-0 shadow-sm">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="text-muted mb-0">Tasks</h6>
                <button class="btn btn-sm btn-outline-primary" @click="createTask">
                  <i class="fas fa-plus"></i>
                </button>
              </div>
              <div class="tasks-list">
                <div v-for="task in tasks" :key="task.id" class="task-item">
                  <div class="form-check">
                    <input
                      class="form-check-input"
                      type="checkbox"
                      :checked="task.status === 'completed'"
                      @change="toggleTask(task)"
                    >
                    <label class="form-check-label">
                      {{ task.title }}
                      <br>
                      <small class="text-muted">{{ formatDate(task.due_date) }}</small>
                    </label>
                  </div>
                </div>
                <div v-if="tasks.length === 0" class="text-center text-muted py-2">
                  No tasks
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
import { useRoute, useRouter } from 'vue-router'
import api from '../services/api'
import { useToast } from '../composables/useToast'

const route = useRoute()
const router = useRouter()
const { success, error } = useToast()

// State
const loading = ref(true)
const lead = ref(null)
const milestones = ref([])
const interactions = ref([])
const whatsappMessages = ref([])
const tasks = ref([])

// Methods
const fetchLead = async () => {
  loading.value = true
  try {
    const response = await api.get(`/leads/${route.params.id}`)
    lead.value = response.data
  } catch (err) {
    console.error('Error fetching lead:', err)
    error('Lead not found')
    router.push('/leads')
  } finally {
    loading.value = false
  }
}

const fetchMilestones = async () => {
  try {
    const response = await api.get(`/leads/${route.params.id}/milestones`)
    milestones.value = response.data
  } catch (error) {
    console.error('Error fetching milestones:', error)
  }
}

const fetchInteractions = async () => {
  try {
    const response = await api.get(`/leads/${route.params.id}/interactions`)
    interactions.value = response.data
  } catch (error) {
    console.error('Error fetching interactions:', error)
  }
}

const fetchWhatsAppMessages = async () => {
  try {
    const response = await api.get(`/whatsapp/messages`, {
      params: { lead_id: route.params.id }
    })
    whatsappMessages.value = response.data
  } catch (error) {
    console.error('Error fetching messages:', error)
  }
}

const fetchTasks = async () => {
  try {
    const response = await api.get(`/tasks`, {
      params: { lead_id: route.params.id }
    })
    tasks.value = response.data
  } catch (error) {
    console.error('Error fetching tasks:', error)
  }
}

const updateScore = async () => {
  try {
    const response = await api.post(`/leads/${route.params.id}/update-score`)
    lead.value.lead_score = response.data.score
  } catch (error) {
    console.error('Error updating score:', error)
  }
}

const editLead = () => {
  // Redirect to leads page with edit mode
  router.push(`/leads?edit=${route.params.id}`)
}

const convertToCustomer = async () => {
  if (!confirm('Convert this lead to a customer?')) return
  try {
    await api.post(`/leads/${route.params.id}/convert`)
    await fetchLead()
    success('Lead converted successfully!')
  } catch (err) {
    console.error('Error converting lead:', err)
    error('Failed to convert lead')
  }
}

const addMilestone = async () => {
  const title = prompt('Milestone title:')
  if (!title) return

  const description = prompt('Milestone description (optional):') || ''

  try {
    await api.post(`/leads/${route.params.id}/milestones`, {
      title,
      description,
      color: '#007bff'
    })
    await fetchMilestones()
    success('Milestone added successfully!')
  } catch (err) {
    console.error('Error adding milestone:', err)
    error('Failed to add milestone')
  }
}

const logInteraction = async () => {
  const type = prompt('Interaction type (call/email/meeting/note/whatsapp):')
  if (!type) return

  const notes = prompt('Interaction notes:')
  if (!notes) return

  try {
    await api.post(`/interactions`, {
      lead_id: route.params.id,
      type,
      notes
    })
    await fetchInteractions()
    success('Interaction logged successfully!')
  } catch (err) {
    console.error('Error logging interaction:', err)
    error('Failed to log interaction')
  }
}

const manageTags = () => {
  // Redirect to leads page to manage tags
  router.push(`/leads?tags=${route.params.id}`)
}

const createTask = () => {
  // Redirect to tasks page with lead pre-filled
  router.push(`/tasks?lead=${route.params.id}`)
}

const toggleTask = async (task) => {
  try {
    await api.put(`/tasks/${task.id}/complete`)
    task.status = task.status === 'completed' ? 'pending' : 'completed'
  } catch (error) {
    console.error('Error toggling task:', error)
  }
}

const getStatusClass = (status) => {
  const classes = {
    new: 'bg-info',
    contacted: 'bg-primary',
    qualified: 'bg-warning',
    proposal: 'bg-secondary',
    negotiation: 'bg-purple',
    won: 'bg-success',
    lost: 'bg-danger'
  }
  return classes[status] || 'bg-secondary'
}

const getScoreClass = (score) => {
  if (score >= 70) return 'score-high'
  if (score >= 40) return 'score-medium'
  return 'score-low'
}

const getInteractionIcon = (type) => {
  const icons = {
    call: 'fas fa-phone',
    email: 'fas fa-envelope',
    meeting: 'fas fa-calendar',
    note: 'fas fa-sticky-note',
    whatsapp: 'fab fa-whatsapp'
  }
  return icons[type] || 'fas fa-comment'
}

const formatCurrency = (value) => {
  if (!value) return '0'
  return new Intl.NumberFormat('id-ID').format(value)
}

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric'
  })
}

const formatDateTime = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// Lifecycle
onMounted(async () => {
  await Promise.all([
    fetchLead(),
    fetchMilestones(),
    fetchInteractions(),
    fetchWhatsAppMessages(),
    fetchTasks()
  ])
})
</script>

<style scoped>
.lead-detail-view {
  padding: 1.5rem;
}

.lead-meta {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-top: 0.5rem;
}

.score-circle {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto;
  border: 4px solid;
}

.score-circle.score-high {
  border-color: #28a745;
  background: #d4edda;
}

.score-circle.score-medium {
  border-color: #ffc107;
  background: #fff3cd;
}

.score-circle.score-low {
  border-color: #dc3545;
  background: #f8d7da;
}

.score-value {
  font-size: 2rem;
  font-weight: bold;
}

.info-item {
  margin-bottom: 1rem;
}

.info-item:last-child {
  margin-bottom: 0;
}

.info-item label {
  display: block;
  font-size: 0.875rem;
  color: #6c757d;
  margin-bottom: 0.25rem;
}

.timeline {
  position: relative;
  padding-left: 2rem;
}

.timeline::before {
  content: '';
  position: absolute;
  left: 8px;
  top: 0;
  bottom: 0;
  width: 2px;
  background: #e9ecef;
}

.timeline-item {
  position: relative;
  margin-bottom: 1.5rem;
}

.timeline-marker {
  position: absolute;
  left: -2rem;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  border: 3px solid white;
  box-shadow: 0 0 0 2px #e9ecef;
}

.timeline-content {
  background: #f8f9fa;
  padding: 1rem;
  border-radius: 8px;
}

.interactions-list {
  max-height: 400px;
  overflow-y: auto;
}

.interaction-item {
  display: flex;
  gap: 1rem;
  padding: 1rem;
  border-bottom: 1px solid #f0f0f0;
}

.interaction-item:last-child {
  border-bottom: none;
}

.interaction-icon {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  flex-shrink: 0;
}

.interaction-icon.type-call {
  background: #17a2b8;
}

.interaction-icon.type-email {
  background: #6f42c1;
}

.interaction-icon.type-meeting {
  background: #fd7e14;
}

.interaction-icon.type-note {
  background: #6c757d;
}

.interaction-icon.type-whatsapp {
  background: #25d366;
}

.interaction-content {
  flex: 1;
}

.messages-list {
  max-height: 300px;
  overflow-y: auto;
}

.message-item {
  padding: 0.75rem;
  margin-bottom: 0.5rem;
  border-radius: 8px;
  max-width: 80%;
}

.message-item.inbound {
  background: #e9ecef;
  margin-right: auto;
}

.message-item.outbound {
  background: #d4edda;
  margin-left: auto;
}

.task-item {
  padding: 0.5rem 0;
  border-bottom: 1px solid #f0f0f0;
}

.task-item:last-child {
  border-bottom: none;
}
</style>
