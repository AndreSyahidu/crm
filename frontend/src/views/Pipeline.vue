<template>
  <div class="pipeline-view">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h2 class="mb-1">Sales Pipeline</h2>
        <p class="text-muted mb-0">Drag and drop deals between stages</p>
      </div>
      <div class="d-flex gap-2">
        <div class="card border-0 shadow-sm">
          <div class="card-body py-2 px-3">
            <small class="text-muted d-block">Total Value</small>
            <h5 class="mb-0">Rp {{ formatCurrency(totalPipelineValue) }}</h5>
          </div>
        </div>
        <div class="card border-0 shadow-sm">
          <div class="card-body py-2 px-3">
            <small class="text-muted d-block">Active Deals</small>
            <h5 class="mb-0">{{ totalDeals }}</h5>
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
              placeholder="Search deals..."
              @input="debouncedFetch"
            >
          </div>
          <div class="col-md-3">
            <select v-model="filters.assigned_to" class="form-select" @change="fetchPipeline">
              <option value="">All Users</option>
              <option v-for="user in users" :key="user.id" :value="user.id">
                {{ user.name }}
              </option>
            </select>
          </div>
          <div class="col-md-3">
            <select v-model="filters.sort" class="form-select" @change="fetchPipeline">
              <option value="expected_close_date">Close Date</option>
              <option value="expected_revenue">Value (High to Low)</option>
              <option value="created_at">Recently Added</option>
              <option value="last_contact_at">Last Contact</option>
            </select>
          </div>
          <div class="col-md-2">
            <button class="btn btn-primary w-100" @click="showCreateDealModal">
              <i class="fas fa-plus"></i> New Deal
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Pipeline Board -->
    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
    </div>

    <div v-else class="pipeline-board">
      <div
        v-for="stage in stages"
        :key="stage.id"
        class="pipeline-column"
        :style="{ borderTopColor: stage.color }"
      >
        <!-- Stage Header -->
        <div class="stage-header" :style="{ backgroundColor: stage.color + '20' }">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h5 class="mb-0">{{ stage.name }}</h5>
              <small class="text-muted">
                {{ stage.deals?.length || 0 }} deals · Rp {{ formatCurrency(getStageValue(stage)) }}
              </small>
            </div>
            <span class="badge rounded-pill" :style="{ backgroundColor: stage.color }">
              {{ Math.round(stage.probability) }}%
            </span>
          </div>
        </div>

        <!-- Deals List -->
        <div
          class="deals-container"
          @drop="handleDrop($event, stage.id)"
          @dragover.prevent
          @dragenter.prevent
        >
          <div
            v-for="deal in stage.deals"
            :key="deal.id"
            class="deal-card"
            draggable="true"
            @dragstart="handleDragStart($event, deal)"
            @click="openDealDetail(deal)"
          >
            <div class="deal-header">
              <h6 class="deal-title mb-1">{{ deal.lead?.name || 'Unnamed Lead' }}</h6>
              <span class="deal-value">Rp {{ formatCurrency(deal.expected_revenue) }}</span>
            </div>

            <div class="deal-meta">
              <small class="text-muted d-block mb-1">
                <i class="fas fa-building"></i> {{ deal.lead?.company || 'No company' }}
              </small>
              <small class="text-muted d-block mb-1">
                <i class="fas fa-calendar"></i>
                Close: {{ formatDate(deal.expected_close_date) }}
              </small>
              <small class="text-muted d-block">
                <i class="fas fa-user"></i> {{ deal.assigned_user?.name || 'Unassigned' }}
              </small>
            </div>

            <div class="deal-footer mt-2">
              <div class="d-flex justify-content-between align-items-center">
                <div class="deal-tags">
                  <span
                    v-for="tag in deal.lead?.tags?.slice(0, 2)"
                    :key="tag.id"
                    class="badge bg-secondary me-1"
                    style="font-size: 0.7rem;"
                  >
                    {{ tag.name }}
                  </span>
                </div>
                <small :class="getDaysUntilCloseClass(deal.expected_close_date)">
                  {{ getDaysUntilClose(deal.expected_close_date) }}
                </small>
              </div>
            </div>
          </div>

          <!-- Empty State -->
          <div v-if="!stage.deals || stage.deals.length === 0" class="empty-stage">
            <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
            <p class="text-muted mb-0">No deals in this stage</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit Deal Modal -->
    <div
      class="modal fade"
      id="dealModal"
      tabindex="-1"
      ref="dealModal"
    >
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ editingDeal ? 'Edit Deal' : 'New Deal' }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="saveDeal">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Lead *</label>
                  <select v-model="dealForm.lead_id" class="form-select" required>
                    <option value="">Select lead...</option>
                    <option v-for="lead in availableLeads" :key="lead.id" :value="lead.id">
                      {{ lead.name }} - {{ lead.company }}
                    </option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Pipeline Stage *</label>
                  <select v-model="dealForm.pipeline_stage_id" class="form-select" required>
                    <option v-for="stage in stages" :key="stage.id" :value="stage.id">
                      {{ stage.name }}
                    </option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Expected Revenue *</label>
                  <input
                    v-model.number="dealForm.expected_revenue"
                    type="number"
                    class="form-control"
                    required
                    min="0"
                    step="1000"
                  >
                </div>
                <div class="col-md-6">
                  <label class="form-label">Expected Close Date</label>
                  <input
                    v-model="dealForm.expected_close_date"
                    type="date"
                    class="form-control"
                  >
                </div>
                <div class="col-md-6">
                  <label class="form-label">Assigned To</label>
                  <select v-model="dealForm.assigned_to" class="form-select">
                    <option value="">Unassigned</option>
                    <option v-for="user in users" :key="user.id" :value="user.id">
                      {{ user.name }}
                    </option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Probability (%)</label>
                  <input
                    v-model.number="dealForm.probability"
                    type="number"
                    class="form-control"
                    min="0"
                    max="100"
                  >
                </div>
                <div class="col-12">
                  <label class="form-label">Notes</label>
                  <textarea
                    v-model="dealForm.notes"
                    class="form-control"
                    rows="3"
                  ></textarea>
                </div>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary" @click="saveDeal" :disabled="saving">
              <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
              {{ editingDeal ? 'Update' : 'Create' }} Deal
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'
import { useToast } from '../composables/useToast'
const { success, error } = useToast()
import { Modal } from 'bootstrap'

const router = useRouter()

// State
const loading = ref(false)
const saving = ref(false)
const stages = ref([])
const users = ref([])
const availableLeads = ref([])
const editingDeal = ref(null)
const dealModal = ref(null)
let dealModalInstance = null

const filters = ref({
  search: '',
  assigned_to: '',
  sort: 'expected_close_date'
})

const dealForm = ref({
  lead_id: '',
  pipeline_stage_id: '',
  expected_revenue: 0,
  expected_close_date: '',
  assigned_to: '',
  probability: 0,
  notes: ''
})

// Computed
const totalPipelineValue = computed(() => {
  return stages.value.reduce((total, stage) => {
    const stageValue = stage.deals?.reduce((sum, deal) => sum + (deal.expected_revenue || 0), 0) || 0
    return total + stageValue
  }, 0)
})

const totalDeals = computed(() => {
  return stages.value.reduce((total, stage) => total + (stage.deals?.length || 0), 0)
})

// Methods
const fetchPipeline = async () => {
  loading.value = true
  try {
    const params = {
      ...filters.value,
      with_deals: true
    }
    const response = await api.get('/pipeline/stages', { params })
    stages.value = response.data
  } catch (error) {
    console.error('Error fetching pipeline:', error)
  } finally {
    loading.value = false
  }
}

const fetchUsers = async () => {
  try {
    const response = await api.get('/users')
    users.value = response.data
  } catch (error) {
    console.error('Error fetching users:', error)
  }
}

const fetchAvailableLeads = async () => {
  try {
    const response = await api.get('/leads', { params: { status: 'open', per_page: 100 } })
    availableLeads.value = response.data.data
  } catch (error) {
    console.error('Error fetching leads:', error)
  }
}

const handleDragStart = (event, deal) => {
  event.dataTransfer.effectAllowed = 'move'
  event.dataTransfer.setData('dealId', deal.id)
  event.dataTransfer.setData('currentStageId', deal.pipeline_stage_id)
}

const handleDrop = async (event, newStageId) => {
  event.preventDefault()
  const dealId = event.dataTransfer.getData('dealId')
  const currentStageId = event.dataTransfer.getData('currentStageId')

  if (currentStageId === newStageId.toString()) return

  try {
    await api.put(`/deals/${dealId}/move`, {
      pipeline_stage_id: newStageId
    })
    await fetchPipeline()
  } catch (error) {
    console.error('Error moving deal:', error)
    error('Failed to move deal')
  }
}

const getStageValue = (stage) => {
  return stage.deals?.reduce((sum, deal) => sum + (deal.expected_revenue || 0), 0) || 0
}

const showCreateDealModal = () => {
  editingDeal.value = null
  dealForm.value = {
    lead_id: '',
    pipeline_stage_id: stages.value[0]?.id || '',
    expected_revenue: 0,
    expected_close_date: '',
    assigned_to: '',
    probability: stages.value[0]?.probability || 0,
    notes: ''
  }
  dealModalInstance.show()
}

const openDealDetail = (deal) => {
  router.push(`/leads/${deal.lead_id}`)
}

const saveDeal = async () => {
  saving.value = true
  try {
    if (editingDeal.value) {
      await api.put(`/deals/${editingDeal.value.id}`, dealForm.value)
    } else {
      await api.post('/deals', dealForm.value)
    }
    dealModalInstance.hide()
    await fetchPipeline()
  } catch (error) {
    console.error('Error saving deal:', error)
    error('Failed to save deal')
  } finally {
    saving.value = false
  }
}

const formatCurrency = (value) => {
  if (!value) return '0'
  return new Intl.NumberFormat('id-ID').format(value)
}

const formatDate = (date) => {
  if (!date) return 'Not set'
  return new Date(date).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric'
  })
}

const getDaysUntilClose = (date) => {
  if (!date) return ''
  const days = Math.ceil((new Date(date) - new Date()) / (1000 * 60 * 60 * 24))
  if (days < 0) return `${Math.abs(days)}d overdue`
  if (days === 0) return 'Today'
  if (days === 1) return 'Tomorrow'
  return `${days}d left`
}

const getDaysUntilCloseClass = (date) => {
  if (!date) return 'text-muted'
  const days = Math.ceil((new Date(date) - new Date()) / (1000 * 60 * 60 * 24))
  if (days < 0) return 'text-danger fw-bold'
  if (days <= 7) return 'text-warning fw-bold'
  return 'text-muted'
}

const debouncedFetch = (() => {
  let timeout
  return () => {
    clearTimeout(timeout)
    timeout = setTimeout(() => fetchPipeline(), 500)
  }
})()

// Lifecycle
onMounted(async () => {
  dealModalInstance = new Modal(dealModal.value)
  await Promise.all([
    fetchPipeline(),
    fetchUsers(),
    fetchAvailableLeads()
  ])
})
</script>

<style scoped>
.pipeline-view {
  padding: 1.5rem;
}

.pipeline-board {
  display: flex;
  gap: 1.5rem;
  overflow-x: auto;
  padding-bottom: 1rem;
  min-height: 600px;
}

.pipeline-column {
  flex: 0 0 320px;
  background: #f8f9fa;
  border-radius: 8px;
  border-top: 4px solid;
  display: flex;
  flex-direction: column;
  max-height: 80vh;
}

.stage-header {
  padding: 1rem;
  border-radius: 8px 8px 0 0;
  border-bottom: 1px solid rgba(0,0,0,0.1);
}

.stage-header h5 {
  font-size: 1rem;
  font-weight: 600;
}

.deals-container {
  flex: 1;
  overflow-y: auto;
  padding: 1rem;
  min-height: 200px;
}

.deal-card {
  background: white;
  border-radius: 6px;
  padding: 1rem;
  margin-bottom: 0.75rem;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  cursor: move;
  transition: all 0.2s;
}

.deal-card:hover {
  box-shadow: 0 4px 8px rgba(0,0,0,0.15);
  transform: translateY(-2px);
}

.deal-header {
  display: flex;
  justify-content: space-between;
  align-items: start;
  margin-bottom: 0.5rem;
}

.deal-title {
  font-size: 0.95rem;
  font-weight: 600;
  margin: 0;
  flex: 1;
}

.deal-value {
  font-size: 0.9rem;
  font-weight: 600;
  color: #28a745;
  white-space: nowrap;
  margin-left: 0.5rem;
}

.deal-meta small {
  font-size: 0.8rem;
  line-height: 1.6;
}

.deal-meta i {
  width: 14px;
  opacity: 0.7;
}

.deal-footer {
  padding-top: 0.75rem;
  border-top: 1px solid #f0f0f0;
}

.empty-stage {
  text-align: center;
  padding: 3rem 1rem;
}

.empty-stage i {
  opacity: 0.3;
}
</style>
