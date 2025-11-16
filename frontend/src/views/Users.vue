<template>
  <div class="users-view">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h2 class="mb-1">User Management</h2>
        <p class="text-muted mb-0">Manage team members and permissions</p>
      </div>
      <button class="btn btn-primary" @click="createUser">
        <i class="fas fa-user-plus"></i> Add User
      </button>
    </div>

    <!-- Users Table -->
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Leads Assigned</th>
                <th>Last Login</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="user in users" :key="user.id">
                <td>
                  <div class="d-flex align-items-center">
                    <div class="user-avatar me-2">{{ user.name.charAt(0) }}</div>
                    <strong>{{ user.name }}</strong>
                  </div>
                </td>
                <td>{{ user.email }}</td>
                <td>
                  <span class="badge" :class="getRoleClass(user.role)">{{ user.role }}</span>
                </td>
                <td>
                  <span class="badge" :class="user.is_active ? 'bg-success' : 'bg-secondary'">
                    {{ user.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td>{{ user.leads_count || 0 }}</td>
                <td>{{ formatDate(user.last_login_at) }}</td>
                <td>
                  <button class="btn btn-sm btn-outline-primary" @click="editUser(user)">
                    <i class="fas fa-edit"></i>
                  </button>
                  <button
                    v-if="user.id !== currentUserId"
                    class="btn btn-sm btn-outline-danger"
                    @click="deleteUser(user)"
                  >
                    <i class="fas fa-trash"></i>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <div class="modal fade" id="userModal" tabindex="-1" ref="userModal">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ editingUser ? 'Edit User' : 'New User' }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <form>
              <div class="mb-3">
                <label class="form-label">Name *</label>
                <input v-model="userForm.name" type="text" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Email *</label>
                <input v-model="userForm.email" type="email" class="form-control" required>
              </div>
              <div v-if="!editingUser" class="mb-3">
                <label class="form-label">Password *</label>
                <input v-model="userForm.password" type="password" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Role *</label>
                <select v-model="userForm.role" class="form-select" required>
                  <option value="sales_rep">Sales Representative</option>
                  <option value="manager">Manager</option>
                  <option value="admin">Administrator</option>
                </select>
              </div>
              <div class="mb-3">
                <div class="form-check form-switch">
                  <input v-model="userForm.is_active" class="form-check-input" type="checkbox">
                  <label class="form-check-label">Active</label>
                </div>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary" @click="saveUser">Save</button>
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
import { Modal } from 'bootstrap'
import { useAuthStore } from '../stores/auth'

const authStore = useAuthStore()
const users = ref([])
const editingUser = ref(null)
const userModal = ref(null)
let userModalInstance = null

const currentUserId = ref(null)

const userForm = ref({
  name: '',
  email: '',
  password: '',
  role: 'sales_rep',
  is_active: true
})

const fetchUsers = async () => {
  try {
    const response = await api.get('/users')
    users.value = response.data
  } catch (error) {
    console.error('Error:', error)
  }
}

const createUser = () => {
  editingUser.value = null
  userForm.value = {
    name: '',
    email: '',
    password: '',
    role: 'sales_rep',
    is_active: true
  }
  userModalInstance.show()
}

const editUser = (user) => {
  editingUser.value = user
  userForm.value = {
    name: user.name,
    email: user.email,
    password: '',
    role: user.role,
    is_active: user.is_active
  }
  userModalInstance.show()
}

const saveUser = async () => {
  try {
    if (editingUser.value) {
      await api.put(`/users/${editingUser.value.id}`, userForm.value)
    } else {
      await api.post('/users', userForm.value)
    }
    userModalInstance.hide()
    await fetchUsers()
  } catch (error) {
    console.error('Error:', error)
    error('Failed to save user')
  }
}

const deleteUser = async (user) => {
  if (!confirm(`Delete user ${user.name}?`)) return
  try {
    await api.delete(`/users/${user.id}`)
    await fetchUsers()
  } catch (error) {
    console.error('Error:', error)
  }
}

const getRoleClass = (role) => {
  return {
    admin: 'bg-danger',
    manager: 'bg-warning',
    sales_rep: 'bg-primary'
  }[role] || 'bg-secondary'
}

const formatDate = (date) => {
  if (!date) return 'Never'
  return new Date(date).toLocaleString('id-ID')
}

onMounted(async () => {
  userModalInstance = new Modal(userModal.value)
  currentUserId.value = authStore.user?.id
  await fetchUsers()
})
</script>

<style scoped>
.users-view {
  padding: 1.5rem;
}

.user-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
}
</style>
