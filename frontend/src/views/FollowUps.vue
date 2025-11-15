<template>
  <div class="followups-view">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h2 class="mb-1">Follow-Up Sequences</h2>
        <p class="text-muted mb-0">Automated follow-up workflows</p>
      </div>
      <button class="btn btn-primary" @click="createSequence">
        <i class="fas fa-plus"></i> New Sequence
      </button>
    </div>

    <!-- Sequences List -->
    <div class="row g-3">
      <div v-for="sequence in sequences" :key="sequence.id" class="col-md-12">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-3">
              <div>
                <h5 class="mb-1">{{ sequence.name }}</h5>
                <p class="text-muted mb-0">{{ sequence.description }}</p>
              </div>
              <div class="d-flex gap-2">
                <span class="badge" :class="sequence.is_active ? 'bg-success' : 'bg-secondary'">
                  {{ sequence.is_active ? 'Active' : 'Inactive' }}
                </span>
                <div class="dropdown">
                  <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-ellipsis-v"></i>
                  </button>
                  <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" @click.prevent="editSequence(sequence)">Edit</a></li>
                    <li><a class="dropdown-item" href="#" @click.prevent="toggleActive(sequence)">
                      {{ sequence.is_active ? 'Deactivate' : 'Activate' }}
                    </a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="#" @click.prevent="deleteSequence(sequence)">Delete</a></li>
                  </ul>
                </div>
              </div>
            </div>

            <!-- Steps -->
            <div class="steps-timeline">
              <div v-for="(step, index) in sequence.steps" :key="step.id" class="step-item">
                <div class="step-number">{{ index + 1 }}</div>
                <div class="step-content">
                  <div class="d-flex justify-content-between align-items-start">
                    <div>
                      <strong>{{ step.action_type }}</strong>
                      <p class="mb-0 text-muted small">{{ step.message_template }}</p>
                    </div>
                    <small class="text-muted">Wait {{ step.delay_hours }}h</small>
                  </div>
                </div>
              </div>
            </div>

            <div class="sequence-stats mt-3">
              <small class="text-muted">
                <i class="fas fa-users"></i> {{ sequence.enrollments_count || 0 }} enrolled
                <span class="ms-3"><i class="fas fa-check-circle"></i> {{ sequence.completed_count || 0 }} completed</span>
              </small>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <div class="modal fade" id="sequenceModal" tabindex="-1" ref="sequenceModal">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ editingSequence ? 'Edit Sequence' : 'New Sequence' }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Name *</label>
              <input v-model="sequenceForm.name" type="text" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Description</label>
              <textarea v-model="sequenceForm.description" class="form-control" rows="2"></textarea>
            </div>
            <div class="mb-3">
              <div class="form-check form-switch">
                <input v-model="sequenceForm.is_active" class="form-check-input" type="checkbox">
                <label class="form-check-label">Active</label>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary" @click="saveSequence">Save</button>
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

const sequences = ref([])
const editingSequence = ref(null)
const sequenceModal = ref(null)
let sequenceModalInstance = null

const sequenceForm = ref({
  name: '',
  description: '',
  is_active: true
})

const fetchSequences = async () => {
  try {
    const response = await api.get('/followup-sequences')
    sequences.value = response.data
  } catch (error) {
    console.error('Error:', error)
  }
}

const createSequence = () => {
  editingSequence.value = null
  sequenceForm.value = { name: '', description: '', is_active: true }
  sequenceModalInstance.show()
}

const editSequence = (sequence) => {
  editingSequence.value = sequence
  sequenceForm.value = { ...sequence }
  sequenceModalInstance.show()
}

const saveSequence = async () => {
  try {
    if (editingSequence.value) {
      await api.put(`/followup-sequences/${editingSequence.value.id}`, sequenceForm.value)
    } else {
      await api.post('/followup-sequences', sequenceForm.value)
    }
    sequenceModalInstance.hide()
    await fetchSequences()
  } catch (error) {
    console.error('Error:', error)
  }
}

const toggleActive = async (sequence) => {
  try {
    await api.put(`/followup-sequences/${sequence.id}`, { is_active: !sequence.is_active })
    await fetchSequences()
  } catch (error) {
    console.error('Error:', error)
  }
}

const deleteSequence = async (sequence) => {
  if (!confirm('Delete this sequence?')) return
  try {
    await api.delete(`/followup-sequences/${sequence.id}`)
    await fetchSequences()
  } catch (error) {
    console.error('Error:', error)
  }
}

onMounted(async () => {
  sequenceModalInstance = new Modal(sequenceModal.value)
  await fetchSequences()
})
</script>

<style scoped>
.followups-view {
  padding: 1.5rem;
}

.steps-timeline {
  position: relative;
  padding-left: 3rem;
}

.steps-timeline::before {
  content: '';
  position: absolute;
  left: 18px;
  top: 0;
  bottom: 0;
  width: 2px;
  background: #e9ecef;
}

.step-item {
  position: relative;
  margin-bottom: 1.5rem;
}

.step-number {
  position: absolute;
  left: -3rem;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: #007bff;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  z-index: 1;
}

.step-content {
  background: #f8f9fa;
  padding: 1rem;
  border-radius: 6px;
}
</style>
