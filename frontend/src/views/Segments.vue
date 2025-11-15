<template>
  <div class="segments-view">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h2 class="mb-1">Customer Segments</h2>
        <p class="text-muted mb-0">Create targeted groups for marketing campaigns</p>
      </div>
      <button class="btn btn-primary" @click="createSegment">
        <i class="fas fa-plus"></i> New Segment
      </button>
    </div>

    <!-- Segments List -->
    <div class="row g-3">
      <div v-for="segment in segments" :key="segment.id" class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-3">
              <div>
                <h5 class="mb-1">{{ segment.name }}</h5>
                <span class="badge" :class="segment.is_dynamic ? 'bg-info' : 'bg-secondary'">
                  {{ segment.is_dynamic ? 'Dynamic' : 'Static' }}
                </span>
              </div>
              <div class="dropdown">
                <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown">
                  <i class="fas fa-ellipsis-v"></i>
                </button>
                <ul class="dropdown-menu">
                  <li><a class="dropdown-item" href="#" @click.prevent="editSegment(segment)">Edit</a></li>
                  <li><a class="dropdown-item" href="#" @click.prevent="duplicateSegment(segment)">Duplicate</a></li>
                  <li><hr class="dropdown-divider"></li>
                  <li><a class="dropdown-item text-danger" href="#" @click.prevent="deleteSegment(segment)">Delete</a></li>
                </ul>
              </div>
            </div>

            <p class="text-muted small mb-3">{{ segment.description || 'No description' }}</p>

            <div class="segment-stats">
              <div class="stat-item">
                <i class="fas fa-users text-primary"></i>
                <strong>{{ segment.cached_count || 0 }}</strong> contacts
              </div>
              <div class="stat-item text-muted">
                <i class="fas fa-clock"></i>
                Updated {{ formatDate(segment.last_calculated_at) }}
              </div>
            </div>

            <div class="segment-actions mt-3">
              <button class="btn btn-sm btn-outline-primary" @click="viewContacts(segment)">
                <i class="fas fa-eye"></i> View
              </button>
              <button class="btn btn-sm btn-outline-success" @click="exportSegment(segment)">
                <i class="fas fa-download"></i> Export
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <div class="modal fade" id="segmentModal" tabindex="-1" ref="segmentModal">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ editingSegment ? 'Edit Segment' : 'New Segment' }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <form>
              <div class="mb-3">
                <label class="form-label">Name *</label>
                <input v-model="segmentForm.name" type="text" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea v-model="segmentForm.description" class="form-control" rows="2"></textarea>
              </div>
              <div class="mb-3">
                <div class="form-check form-switch">
                  <input
                    v-model="segmentForm.is_dynamic"
                    class="form-check-input"
                    type="checkbox"
                    id="isDynamic"
                  >
                  <label class="form-check-label" for="isDynamic">
                    Dynamic Segment (automatically updates)
                  </label>
                </div>
              </div>
              <div v-if="segmentForm.is_dynamic">
                <h6>Filter Criteria</h6>
                <div class="filters-builder">
                  <div v-for="(filter, index) in segmentForm.filters" :key="index" class="filter-row mb-2">
                    <select v-model="filter.field" class="form-select form-select-sm">
                      <option value="status">Status</option>
                      <option value="source">Source</option>
                      <option value="lead_score">Lead Score</option>
                      <option value="expected_revenue">Revenue</option>
                    </select>
                    <select v-model="filter.operator" class="form-select form-select-sm">
                      <option value="equals">Equals</option>
                      <option value="greater_than">Greater Than</option>
                      <option value="less_than">Less Than</option>
                    </select>
                    <input v-model="filter.value" type="text" class="form-control form-control-sm" placeholder="Value">
                    <button class="btn btn-sm btn-outline-danger" @click="removeFilter(index)">
                      <i class="fas fa-times"></i>
                    </button>
                  </div>
                  <button class="btn btn-sm btn-outline-primary" @click="addFilter">
                    <i class="fas fa-plus"></i> Add Filter
                  </button>
                </div>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary" @click="saveSegment">Save</button>
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

const segments = ref([])
const editingSegment = ref(null)
const segmentModal = ref(null)
let segmentModalInstance = null

const segmentForm = ref({
  name: '',
  description: '',
  is_dynamic: true,
  filters: []
})

const fetchSegments = async () => {
  try {
    const response = await api.get('/segments')
    segments.value = response.data
  } catch (error) {
    console.error('Error:', error)
  }
}

const createSegment = () => {
  editingSegment.value = null
  segmentForm.value = {
    name: '',
    description: '',
    is_dynamic: true,
    filters: [{ field: 'status', operator: 'equals', value: '' }]
  }
  segmentModalInstance.show()
}

const editSegment = (segment) => {
  editingSegment.value = segment
  segmentForm.value = {
    name: segment.name,
    description: segment.description,
    is_dynamic: segment.is_dynamic,
    filters: segment.filter_rules || []
  }
  segmentModalInstance.show()
}

const saveSegment = async () => {
  try {
    if (editingSegment.value) {
      await api.put(`/segments/${editingSegment.value.id}`, segmentForm.value)
    } else {
      await api.post('/segments', segmentForm.value)
    }
    segmentModalInstance.hide()
    await fetchSegments()
  } catch (error) {
    console.error('Error:', error)
  }
}

const deleteSegment = async (segment) => {
  if (!confirm('Delete this segment?')) return
  try {
    await api.delete(`/segments/${segment.id}`)
    await fetchSegments()
  } catch (error) {
    console.error('Error:', error)
  }
}

const addFilter = () => {
  segmentForm.value.filters.push({ field: 'status', operator: 'equals', value: '' })
}

const removeFilter = (index) => {
  segmentForm.value.filters.splice(index, 1)
}

const formatDate = (date) => {
  if (!date) return 'Never'
  return new Date(date).toLocaleDateString('id-ID')
}

onMounted(async () => {
  segmentModalInstance = new Modal(segmentModal.value)
  await fetchSegments()
})
</script>

<style scoped>
.segments-view {
  padding: 1.5rem;
}

.segment-stats {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.stat-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
}

.segment-actions {
  display: flex;
  gap: 0.5rem;
}

.filter-row {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr auto;
  gap: 0.5rem;
  align-items: center;
}
</style>
