<template>
  <div class="tasks-view">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h2 class="mb-1">Tasks</h2>
        <p class="text-muted mb-0">Manage your tasks and reminders</p>
      </div>
      <button class="btn btn-primary" @click="showCreateModal">
        <i class="fas fa-plus"></i> New Task
      </button>
    </div>

    <!-- Filter Tabs -->
    <ul class="nav nav-tabs mb-4">
      <li class="nav-item">
        <a class="nav-link" :class="{ active: filter === 'all' }" href="#" @click.prevent="filter = 'all'">
          All Tasks ({{ tasks.length }})
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" :class="{ active: filter === 'pending' }" href="#" @click.prevent="filter = 'pending'">
          Pending ({{ tasks.filter(t => t.status === 'pending').length }})
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" :class="{ active: filter === 'completed' }" href="#" @click.prevent="filter = 'completed'">
          Completed ({{ tasks.filter(t => t.status === 'completed').length }})
        </a>
      </li>
    </ul>

    <!-- Tasks List -->
    <div class="row g-3">
      <div v-for="task in filteredTasks" :key="task.id" class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm task-card" :class="{ 'task-completed': task.status === 'completed' }">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-2">
              <div class="form-check">
                <input
                  class="form-check-input"
                  type="checkbox"
                  :checked="task.status === 'completed'"
                  @change="toggleTask(task)"
                >
                <label class="form-check-label">
                  <h6 class="mb-0">{{ task.title }}</h6>
                </label>
              </div>
              <span
                class="badge"
                :class="getPriorityClass(task.priority)"
              >
                {{ task.priority }}
              </span>
            </div>

            <p class="text-muted small mb-2">{{ task.description }}</p>

            <div class="task-meta">
              <small v-if="task.lead" class="d-block">
                <i class="fas fa-user"></i> {{ task.lead.name }}
              </small>
              <small class="d-block">
                <i class="fas fa-calendar"></i> {{ formatDate(task.due_date) }}
              </small>
              <small v-if="task.assigned_user" class="d-block">
                <i class="fas fa-user-circle"></i> {{ task.assigned_user.name }}
              </small>
            </div>

            <div class="task-actions mt-3">
              <button class="btn btn-sm btn-outline-primary" @click="editTask(task)">
                <i class="fas fa-edit"></i>
              </button>
              <button class="btn btn-sm btn-outline-danger" @click="deleteTask(task)">
                <i class="fas fa-trash"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <div class="modal fade" id="taskModal" tabindex="-1" ref="taskModal">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ editingTask ? 'Edit Task' : 'New Task' }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <form>
              <div class="mb-3">
                <label class="form-label">Title *</label>
                <input v-model="taskForm.title" type="text" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea v-model="taskForm.description" class="form-control" rows="3"></textarea>
              </div>
              <div class="row g-3 mb-3">
                <div class="col-md-6">
                  <label class="form-label">Priority</label>
                  <select v-model="taskForm.priority" class="form-select">
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Due Date</label>
                  <input v-model="taskForm.due_date" type="date" class="form-control">
                </div>
              </div>
              <div class="mb-3">
                <label class="form-label">Assign To</label>
                <select v-model="taskForm.assigned_to" class="form-select">
                  <option value="">Select user...</option>
                  <option v-for="user in users" :key="user.id" :value="user.id">
                    {{ user.name }}
                  </option>
                </select>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary" @click="saveTask">Save</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '../services/api'
import { Modal } from 'bootstrap'

const tasks = ref([])
const users = ref([])
const filter = ref('all')
const editingTask = ref(null)
const taskModal = ref(null)
let taskModalInstance = null

const taskForm = ref({
  title: '',
  description: '',
  priority: 'medium',
  due_date: '',
  assigned_to: ''
})

const filteredTasks = computed(() => {
  if (filter.value === 'all') return tasks.value
  return tasks.value.filter(t => t.status === filter.value)
})

const fetchTasks = async () => {
  try {
    const response = await api.get('/tasks')
    tasks.value = response.data
  } catch (error) {
    console.error('Error fetching tasks:', error)
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

const showCreateModal = () => {
  editingTask.value = null
  taskForm.value = {
    title: '',
    description: '',
    priority: 'medium',
    due_date: '',
    assigned_to: ''
  }
  taskModalInstance.show()
}

const editTask = (task) => {
  editingTask.value = task
  taskForm.value = {
    title: task.title,
    description: task.description,
    priority: task.priority,
    due_date: task.due_date,
    assigned_to: task.assigned_to
  }
  taskModalInstance.show()
}

const saveTask = async () => {
  try {
    if (editingTask.value) {
      await api.put(`/tasks/${editingTask.value.id}`, taskForm.value)
    } else {
      await api.post('/tasks', taskForm.value)
    }
    taskModalInstance.hide()
    await fetchTasks()
  } catch (error) {
    console.error('Error saving task:', error)
  }
}

const toggleTask = async (task) => {
  try {
    await api.put(`/tasks/${task.id}/complete`)
    task.status = task.status === 'completed' ? 'pending' : 'completed'
  } catch (error) {
    console.error('Error toggling task:', error)
  }
}

const deleteTask = async (task) => {
  if (!confirm('Delete this task?')) return
  try {
    await api.delete(`/tasks/${task.id}`)
    await fetchTasks()
  } catch (error) {
    console.error('Error deleting task:', error)
  }
}

const getPriorityClass = (priority) => {
  return {
    low: 'bg-secondary',
    medium: 'bg-warning',
    high: 'bg-danger'
  }[priority] || 'bg-secondary'
}

const formatDate = (date) => {
  if (!date) return 'No due date'
  return new Date(date).toLocaleDateString('id-ID')
}

onMounted(async () => {
  taskModalInstance = new Modal(taskModal.value)
  await Promise.all([fetchTasks(), fetchUsers()])
})
</script>

<style scoped>
.tasks-view {
  padding: 1.5rem;
}

.task-card {
  transition: transform 0.2s;
}

.task-card:hover {
  transform: translateY(-4px);
}

.task-completed {
  opacity: 0.7;
}

.task-completed h6 {
  text-decoration: line-through;
}

.task-meta small {
  display: block;
  margin-top: 0.25rem;
}

.task-actions {
  display: flex;
  gap: 0.5rem;
}
</style>
