<template>
  <div class="leads-page">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h2 class="mb-1">Leads</h2>
        <p class="text-muted mb-0">Manage your sales leads</p>
      </div>
      <button class="btn btn-primary" @click="showCreateModal = true">
        <i class="fas fa-plus me-2"></i>New Lead
      </button>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-3">
            <input
              type="text"
              class="form-control"
              v-model="filters.search"
              placeholder="Search leads..."
              @input="debouncedSearch"
            >
          </div>
          <div class="col-md-2">
            <select class="form-select" v-model="filters.status" @change="fetchLeads">
              <option value="">All Statuses</option>
              <option value="new">New</option>
              <option value="contacted">Contacted</option>
              <option value="qualified">Qualified</option>
              <option value="proposal">Proposal</option>
              <option value="negotiation">Negotiation</option>
              <option value="won">Won</option>
              <option value="lost">Lost</option>
            </select>
          </div>
          <div class="col-md-2">
            <select class="form-select" v-model="filters.source" @change="fetchLeads">
              <option value="">All Sources</option>
              <option value="whatsapp">WhatsApp</option>
              <option value="manual">Manual</option>
              <option value="website">Website</option>
              <option value="referral">Referral</option>
            </select>
          </div>
          <div class="col-md-2">
            <select class="form-select" v-model="filters.assigned_to" @change="fetchLeads">
              <option value="">All Users</option>
              <option v-for="user in users" :key="user.id" :value="user.id">
                {{ user.name }}
              </option>
            </select>
          </div>
          <div class="col-md-3 text-end">
            <button class="btn btn-outline-secondary me-2" @click="clearFilters">
              <i class="fas fa-times me-1"></i>Clear
            </button>
            <button class="btn btn-outline-primary" @click="exportLeads">
              <i class="fas fa-download me-1"></i>Export
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Stats -->
    <div class="row mb-4">
      <div class="col-md-3">
        <div class="stat-card bg-primary bg-opacity-10">
          <div class="stat-label text-primary">Total Leads</div>
          <div class="stat-value">{{ stats.total || 0 }}</div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stat-card bg-success bg-opacity-10">
          <div class="stat-label text-success">Qualified</div>
          <div class="stat-value">{{ getStatusCount('qualified') }}</div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stat-card bg-warning bg-opacity-10">
          <div class="stat-label text-warning">Need Follow-up</div>
          <div class="stat-value">{{ stats.needs_follow_up || 0 }}</div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stat-card bg-info bg-opacity-10">
          <div class="stat-label text-info">Pipeline Value</div>
          <div class="stat-value">{{ formatCurrency(stats.pipeline_value || 0) }}</div>
        </div>
      </div>
    </div>

    <!-- Leads Table -->
    <div class="card">
      <div class="card-body">
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>

        <div v-else-if="leads.length === 0" class="text-center py-5 text-muted">
          <i class="fas fa-inbox fa-3x mb-3"></i>
          <p>No leads found</p>
        </div>

        <div v-else class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th><input type="checkbox" @change="toggleSelectAll"></th>
                <th>Name</th>
                <th>Company</th>
                <th>Phone</th>
                <th>Source</th>
                <th>Status</th>
                <th>Score</th>
                <th>Value</th>
                <th>Assigned To</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="lead in leads" :key="lead.id">
                <td><input type="checkbox" v-model="selectedLeads" :value="lead.id"></td>
                <td>
                  <router-link :to="`/leads/${lead.id}`" class="text-decoration-none fw-bold">
                    {{ lead.name }}
                  </router-link>
                  <div v-if="lead.tags && lead.tags.length" class="mt-1">
                    <span
                      v-for="tag in lead.tags"
                      :key="tag.id"
                      class="badge me-1"
                      :style="{ backgroundColor: tag.color }"
                    >
                      {{ tag.name }}
                    </span>
                  </div>
                </td>
                <td>{{ lead.company || '-' }}</td>
                <td>{{ lead.phone }}</td>
                <td><span class="badge bg-secondary">{{ lead.source }}</span></td>
                <td><span class="badge" :class="getStatusClass(lead.status)">{{ lead.status }}</span></td>
                <td>
                  <div class="d-flex align-items-center">
                    <div class="progress flex-grow-1 me-2" style="height: 8px; width: 60px;">
                      <div
                        class="progress-bar"
                        :class="getScoreColor(lead.lead_score)"
                        :style="{ width: lead.lead_score + '%' }"
                      ></div>
                    </div>
                    <small>{{ lead.lead_score }}</small>
                  </div>
                </td>
                <td>{{ formatCurrency(lead.expected_revenue) }}</td>
                <td>{{ lead.assigned_user?.name || '-' }}</td>
                <td>
                  <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                      <i class="fas fa-ellipsis-v"></i>
                    </button>
                    <ul class="dropdown-menu">
                      <li><router-link :to="`/leads/${lead.id}`" class="dropdown-item">
                        <i class="fas fa-eye me-2"></i>View
                      </router-link></li>
                      <li><a class="dropdown-item" href="#" @click.prevent="editLead(lead)">
                        <i class="fas fa-edit me-2"></i>Edit
                      </a></li>
                      <li><a class="dropdown-item" href="#" @click.prevent="deleteLead(lead.id)">
                        <i class="fas fa-trash me-2"></i>Delete
                      </a></li>
                    </ul>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <nav v-if="pagination.total > pagination.per_page" class="mt-4">
          <ul class="pagination justify-content-center">
            <li class="page-item" :class="{ disabled: pagination.current_page === 1 }">
              <a class="page-link" href="#" @click.prevent="changePage(pagination.current_page - 1)">Previous</a>
            </li>
            <li
              v-for="page in paginationPages"
              :key="page"
              class="page-item"
              :class="{ active: page === pagination.current_page }"
            >
              <a class="page-link" href="#" @click.prevent="changePage(page)">{{ page }}</a>
            </li>
            <li class="page-item" :class="{ disabled: pagination.current_page === pagination.last_page }">
              <a class="page-link" href="#" @click.prevent="changePage(pagination.current_page + 1)">Next</a>
            </li>
          </ul>
        </nav>
      </div>
    </div>

    <!-- Bulk Actions -->
    <div v-if="selectedLeads.length > 0" class="floating-actions">
      <div class="card shadow-lg">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between">
            <span>{{ selectedLeads.length }} leads selected</span>
            <div>
              <button class="btn btn-sm btn-outline-primary me-2" @click="showBulkAssignModal = true">
                <i class="fas fa-user me-1"></i>Assign
              </button>
              <button class="btn btn-sm btn-outline-secondary me-2" @click="showBulkTagModal = true">
                <i class="fas fa-tag me-1"></i>Tag
              </button>
              <button class="btn btn-sm btn-outline-danger" @click="selectedLeads = []">
                <i class="fas fa-times me-1"></i>Clear
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '../services/api'
import { useToast } from '../composables/useToast'
const { success, error } = useToast()

const leads = ref([])
const users = ref([])
const loading = ref(false)
const selectedLeads = ref([])
const showCreateModal = ref(false)
const showBulkAssignModal = ref(false)
const showBulkTagModal = ref(false)

const filters = ref({
  search: '',
  status: '',
  source: '',
  assigned_to: ''
})

const stats = ref({})
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0
})

const fetchLeads = async (page = 1) => {
  loading.value = true
  try {
    const params = { ...filters.value, page }
    const response = await api.get('/leads', { params })
    leads.value = response.data.data
    pagination.value = {
      current_page: response.data.current_page,
      last_page: response.data.last_page,
      per_page: response.data.per_page,
      total: response.data.total
    }
  } catch (error) {
    console.error('Error fetching leads:', error)
  } finally {
    loading.value = false
  }
}

const fetchStats = async () => {
  try {
    const response = await api.get('/leads/stats')
    stats.value = response.data
  } catch (error) {
    console.error('Error fetching stats:', error)
  }
}

const fetchUsers = async () => {
  try {
    const response = await api.get('/users/sales-reps')
    users.value = response.data
  } catch (error) {
    console.error('Error fetching users:', error)
  }
}

const debouncedSearch = (() => {
  let timeout
  return () => {
    clearTimeout(timeout)
    timeout = setTimeout(() => fetchLeads(), 500)
  }
})()

const clearFilters = () => {
  filters.value = { search: '', status: '', source: '', assigned_to: '' }
  fetchLeads()
}

const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    fetchLeads(page)
  }
}

const paginationPages = computed(() => {
  const pages = []
  const current = pagination.value.current_page
  const last = pagination.value.last_page
  const delta = 2

  for (let i = Math.max(2, current - delta); i <= Math.min(last - 1, current + delta); i++) {
    pages.push(i)
  }

  if (current - delta > 2) pages.unshift('...')
  if (current + delta < last - 1) pages.push('...')

  pages.unshift(1)
  if (last > 1) pages.push(last)

  return pages.filter((page, index, self) => self.indexOf(page) === index)
})

const getStatusClass = (status) => {
  const classes = {
    new: 'bg-primary',
    contacted: 'bg-info',
    qualified: 'bg-success',
    proposal: 'bg-warning',
    negotiation: 'bg-secondary',
    won: 'bg-success',
    lost: 'bg-danger'
  }
  return classes[status] || 'bg-secondary'
}

const getScoreColor = (score) => {
  if (score >= 70) return 'bg-success'
  if (score >= 40) return 'bg-warning'
  return 'bg-danger'
}

const getStatusCount = (status) => {
  const statusData = stats.value.by_status?.find(s => s.status === status)
  return statusData?.count || 0
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(amount)
}

const toggleSelectAll = (event) => {
  if (event.target.checked) {
    selectedLeads.value = leads.value.map(l => l.id)
  } else {
    selectedLeads.value = []
  }
}

const deleteLead = async (id) => {
  if (confirm('Are you sure you want to delete this lead?')) {
    try {
      await api.delete(`/leads/${id}`)
      fetchLeads()
      fetchStats()
    } catch (error) {
      console.error('Error deleting lead:', error)
    }
  }
}

const exportLeads = async () => {
  try {
    // Fetch all leads for export (without pagination)
    const response = await api.get('/leads', {
      params: {
        ...filters.value,
        per_page: 10000 // Get all leads
      }
    })

    const leadsData = response.data.data || []

    if (leadsData.length === 0) {
      error('No leads to export')
      return
    }

    // Create CSV content
    let csv = 'WhatsApp CRM - Leads Export\n\n'
    csv += `Export Date: ${new Date().toLocaleString('id-ID')}\n`
    csv += `Total Leads: ${leadsData.length}\n\n`

    // Headers
    csv += 'ID,Name,Email,Phone,Company,Status,Source,Value,Assigned To,Created At,Last Contact\n'

    // Data rows
    leadsData.forEach(lead => {
      const row = [
        lead.id,
        `"${lead.name || ''}"`,
        lead.email || '',
        lead.phone || '',
        `"${lead.company || ''}"`,
        lead.status || '',
        lead.source || '',
        lead.estimated_value || 0,
        `"${lead.assigned_user?.name || 'Unassigned'}"`,
        lead.created_at ? new Date(lead.created_at).toLocaleDateString('id-ID') : '',
        lead.last_contact_at ? new Date(lead.last_contact_at).toLocaleDateString('id-ID') : 'Never'
      ]
      csv += row.join(',') + '\n'
    })

    // Create and download file
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' })
    const link = document.createElement('a')
    const url = URL.createObjectURL(blob)
    link.setAttribute('href', url)
    link.setAttribute('download', `leads-export-${Date.now()}.csv`)
    link.style.visibility = 'hidden'
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)

    success(`Successfully exported ${leadsData.length} leads`)
  } catch (err) {
    console.error('Export error:', err)
    error('Failed to export leads: ' + (err.response?.data?.message || err.message))
  }
}

onMounted(() => {
  fetchLeads()
  fetchStats()
  fetchUsers()
})
</script>

<style scoped>
.stat-card {
  padding: 1rem;
  border-radius: 8px;
  text-align: center;
}

.stat-label {
  font-size: 0.875rem;
  font-weight: 600;
  margin-bottom: 0.5rem;
}

.stat-value {
  font-size: 1.5rem;
  font-weight: bold;
}

.floating-actions {
  position: fixed;
  bottom: 2rem;
  right: 2rem;
  z-index: 1000;
}
</style>
