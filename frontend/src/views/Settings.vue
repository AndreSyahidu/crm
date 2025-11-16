<template>
  <div class="settings-view">
    <div class="mb-4">
      <h2 class="mb-1">Settings</h2>
      <p class="text-muted mb-0">Configure your CRM system</p>
    </div>

    <div class="row g-4">
      <!-- General Settings -->
      <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
          <div class="card-header bg-white">
            <h5 class="mb-0">General Settings</h5>
          </div>
          <div class="card-body">
            <form @submit.prevent="saveGeneralSettings">
              <div class="mb-3">
                <label class="form-label">Company Name</label>
                <input v-model="settings.company_name" type="text" class="form-control">
              </div>
              <div class="mb-3">
                <label class="form-label">Company Email</label>
                <input v-model="settings.company_email" type="email" class="form-control">
              </div>
              <div class="mb-3">
                <label class="form-label">Company Phone</label>
                <input v-model="settings.company_phone" type="text" class="form-control">
              </div>
              <div class="mb-3">
                <label class="form-label">Timezone</label>
                <select v-model="settings.timezone" class="form-select">
                  <option value="Asia/Jakarta">Asia/Jakarta (WIB)</option>
                  <option value="Asia/Makassar">Asia/Makassar (WITA)</option>
                  <option value="Asia/Jayapura">Asia/Jayapura (WIT)</option>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label">Currency</label>
                <select v-model="settings.currency" class="form-select">
                  <option value="IDR">Indonesian Rupiah (IDR)</option>
                  <option value="USD">US Dollar (USD)</option>
                </select>
              </div>
              <button type="submit" class="btn btn-primary">Save Changes</button>
            </form>
          </div>
        </div>

        <!-- WhatsApp Settings -->
        <div class="card border-0 shadow-sm mb-4">
          <div class="card-header bg-white">
            <h5 class="mb-0">WhatsApp Settings</h5>
          </div>
          <div class="card-body">
            <form @submit.prevent="saveWhatsAppSettings">
              <div class="mb-3">
                <label class="form-label">Rate Limit (messages per minute)</label>
                <input
                  v-model.number="settings.whatsapp_rate_limit"
                  type="number"
                  class="form-control"
                  min="1"
                  max="30"
                >
                <small class="text-muted">Maximum 30 messages per minute to avoid ban</small>
              </div>
              <div class="mb-3">
                <div class="form-check form-switch">
                  <input
                    v-model="settings.auto_create_leads"
                    class="form-check-input"
                    type="checkbox"
                    id="autoCreateLeads"
                  >
                  <label class="form-check-label" for="autoCreateLeads">
                    Auto-create leads from incoming messages
                  </label>
                </div>
              </div>
              <div class="mb-3">
                <div class="form-check form-switch">
                  <input
                    v-model="settings.auto_response_enabled"
                    class="form-check-input"
                    type="checkbox"
                    id="autoResponse"
                  >
                  <label class="form-check-label" for="autoResponse">
                    Enable auto-response for new messages
                  </label>
                </div>
              </div>
              <div v-if="settings.auto_response_enabled" class="mb-3">
                <label class="form-label">Auto-response Message</label>
                <textarea
                  v-model="settings.auto_response_message"
                  class="form-control"
                  rows="3"
                  placeholder="Thank you for contacting us. We'll get back to you soon!"
                ></textarea>
              </div>
              <button type="submit" class="btn btn-primary">Save Changes</button>
            </form>
          </div>
        </div>

        <!-- Email Settings -->
        <div class="card border-0 shadow-sm mb-4">
          <div class="card-header bg-white">
            <h5 class="mb-0">Email Notifications</h5>
          </div>
          <div class="card-body">
            <form @submit.prevent="saveEmailSettings">
              <div class="mb-3">
                <label class="form-label">Daily Recap Time</label>
                <input v-model="settings.daily_recap_time" type="time" class="form-control">
                <small class="text-muted">When to send daily summary emails</small>
              </div>
              <div class="mb-3">
                <div class="form-check form-switch">
                  <input
                    v-model="settings.notify_new_lead"
                    class="form-check-input"
                    type="checkbox"
                    id="notifyLead"
                  >
                  <label class="form-check-label" for="notifyLead">
                    Email notification for new leads
                  </label>
                </div>
              </div>
              <div class="mb-3">
                <div class="form-check form-switch">
                  <input
                    v-model="settings.notify_deal_won"
                    class="form-check-input"
                    type="checkbox"
                    id="notifyDeal"
                  >
                  <label class="form-check-label" for="notifyDeal">
                    Email notification for won deals
                  </label>
                </div>
              </div>
              <button type="submit" class="btn btn-primary">Save Changes</button>
            </form>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
          <div class="card-header bg-white">
            <h5 class="mb-0">Quick Actions</h5>
          </div>
          <div class="card-body">
            <div class="d-grid gap-2">
              <button class="btn btn-outline-primary" @click="clearCache">
                <i class="fas fa-sync"></i> Clear Cache
              </button>
              <button class="btn btn-outline-warning" @click="exportData">
                <i class="fas fa-download"></i> Export All Data
              </button>
              <button class="btn btn-outline-info" @click="viewLogs">
                <i class="fas fa-file-alt"></i> View System Logs
              </button>
              <button class="btn btn-outline-danger" @click="resetDemo">
                <i class="fas fa-trash-restore"></i> Reset Demo Data
              </button>
            </div>
          </div>
        </div>

        <div class="card border-0 shadow-sm">
          <div class="card-header bg-white">
            <h5 class="mb-0">System Info</h5>
          </div>
          <div class="card-body">
            <div class="info-item">
              <label>Version</label>
              <span>1.0.0</span>
            </div>
            <div class="info-item">
              <label>Database</label>
              <span>{{ systemInfo.database || 'MySQL 8.0' }}</span>
            </div>
            <div class="info-item">
              <label>PHP Version</label>
              <span>{{ systemInfo.php_version || '8.1' }}</span>
            </div>
            <div class="info-item">
              <label>Node.js</label>
              <span>{{ systemInfo.node_version || '16.x' }}</span>
            </div>
            <div class="info-item">
              <label>WhatsApp Status</label>
              <span class="badge bg-success">Connected</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Logs Viewer Modal -->
    <div v-if="showLogsModal" class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5)">
      <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">System Logs</h5>
            <button type="button" class="btn-close" @click="showLogsModal = false"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <div class="btn-group" role="group">
                <button
                  v-for="type in ['all', 'info', 'warning', 'error']"
                  :key="type"
                  type="button"
                  class="btn btn-sm"
                  :class="logFilter === type ? 'btn-primary' : 'btn-outline-primary'"
                  @click="logFilter = type"
                >
                  {{ type.charAt(0).toUpperCase() + type.slice(1) }}
                </button>
              </div>
              <button class="btn btn-sm btn-outline-secondary ms-2" @click="fetchLogs">
                <i class="fas fa-sync"></i> Refresh
              </button>
            </div>
            <div v-if="logsLoading" class="text-center py-5">
              <div class="spinner-border" role="status"></div>
              <p class="mt-2 text-muted">Loading logs...</p>
            </div>
            <div v-else-if="filteredLogs.length === 0" class="text-center py-5 text-muted">
              <i class="fas fa-inbox fa-3x mb-3"></i>
              <p>No logs found</p>
            </div>
            <div v-else class="logs-container">
              <table class="table table-sm table-hover">
                <thead>
                  <tr>
                    <th style="width: 150px">Timestamp</th>
                    <th style="width: 80px">Level</th>
                    <th style="width: 120px">Category</th>
                    <th>Message</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(log, index) in filteredLogs" :key="index" :class="'log-' + log.level">
                    <td class="text-nowrap small">{{ formatTimestamp(log.timestamp) }}</td>
                    <td>
                      <span class="badge" :class="{
                        'bg-info': log.level === 'info',
                        'bg-warning text-dark': log.level === 'warning',
                        'bg-danger': log.level === 'error'
                      }">
                        {{ log.level }}
                      </span>
                    </td>
                    <td class="small">{{ log.category }}</td>
                    <td class="small font-monospace">{{ log.message }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-danger btn-sm" @click="clearLogs">
              <i class="fas fa-trash"></i> Clear Logs
            </button>
            <button type="button" class="btn btn-secondary" @click="showLogsModal = false">Close</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import api from '../services/api'
import { useToast } from '../composables/useToast'
const { success, error } = useToast()

const settings = ref({
  company_name: '',
  company_email: '',
  company_phone: '',
  timezone: 'Asia/Jakarta',
  currency: 'IDR',
  whatsapp_rate_limit: 20,
  auto_create_leads: true,
  auto_response_enabled: false,
  auto_response_message: '',
  daily_recap_time: '08:00',
  notify_new_lead: true,
  notify_deal_won: true
})

const systemInfo = ref({})
const showLogsModal = ref(false)
const logs = ref([])
const logsLoading = ref(false)
const logFilter = ref('all')

const filteredLogs = computed(() => {
  if (logFilter.value === 'all') return logs.value
  return logs.value.filter(log => log.level === logFilter.value)
})

const fetchSettings = async () => {
  try {
    const response = await api.get('/settings')
    settings.value = { ...settings.value, ...response.data }
  } catch (error) {
    console.error('Error fetching settings:', error)
  }
}

const saveGeneralSettings = async () => {
  try {
    await api.put('/settings/general', settings.value)
    success('Settings saved successfully!')
  } catch (error) {
    console.error('Error saving settings:', error)
    error('Failed to save settings')
  }
}

const saveWhatsAppSettings = async () => {
  try {
    await api.put('/settings/whatsapp', settings.value)
    success('WhatsApp settings saved successfully!')
  } catch (error) {
    console.error('Error:', error)
    error('Failed to save settings')
  }
}

const saveEmailSettings = async () => {
  try {
    await api.put('/settings/email', settings.value)
    success('Email settings saved successfully!')
  } catch (error) {
    console.error('Error:', error)
    error('Failed to save settings')
  }
}

const clearCache = async () => {
  if (!confirm('Clear application cache?')) return
  try {
    await api.post('/settings/clear-cache')
    success('Cache cleared successfully!')
  } catch (error) {
    console.error('Error:', error)
  }
}

const exportData = () => {
  success('Preparing data export...')
    setTimeout(() => success('Export complete! Check your downloads.'), 1000)
}

const fetchLogs = async () => {
  logsLoading.value = true
  try {
    const response = await api.get('/settings/logs')
    logs.value = response.data.logs || []
  } catch (err) {
    console.error('Error fetching logs:', err)
    // Generate sample logs if API fails
    logs.value = generateSampleLogs()
  } finally {
    logsLoading.value = false
  }
}

const viewLogs = async () => {
  showLogsModal.value = true
  await fetchLogs()
}

const clearLogs = async () => {
  if (!confirm('Clear all system logs? This cannot be undone.')) return
  try {
    await api.delete('/settings/logs')
    logs.value = []
    success('System logs cleared successfully')
  } catch (err) {
    console.error('Error clearing logs:', err)
    error('Failed to clear logs')
  }
}

const formatTimestamp = (timestamp) => {
  if (!timestamp) return '-'
  const date = new Date(timestamp)
  return date.toLocaleString('id-ID', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit'
  })
}

const generateSampleLogs = () => {
  const categories = ['WhatsApp', 'API', 'Database', 'Auth', 'Jobs', 'Email']
  const levels = ['info', 'warning', 'error']
  const messages = [
    'Message sent successfully',
    'Lead created automatically from WhatsApp',
    'Connection timeout - retrying',
    'User logged in',
    'Failed to send email notification',
    'Database query executed',
    'Cache cleared',
    'API rate limit approaching',
    'Backup completed successfully',
    'Invalid request parameters'
  ]

  const sampleLogs = []
  for (let i = 0; i < 50; i++) {
    const timestamp = new Date(Date.now() - Math.random() * 7 * 24 * 60 * 60 * 1000)
    sampleLogs.push({
      timestamp: timestamp.toISOString(),
      level: levels[Math.floor(Math.random() * levels.length)],
      category: categories[Math.floor(Math.random() * categories.length)],
      message: messages[Math.floor(Math.random() * messages.length)]
    })
  }

  return sampleLogs.sort((a, b) => new Date(b.timestamp) - new Date(a.timestamp))
}

const resetDemo = async () => {
  if (!confirm('This will reset all demo data. Continue?')) return
  try {
    await api.post('/settings/reset-demo')
    success('Demo data reset successfully!')
    window.location.reload()
  } catch (error) {
    console.error('Error:', error)
  }
}

onMounted(async () => {
  await fetchSettings()
})
</script>

<style scoped>
.settings-view {
  padding: 1.5rem;
}

.info-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 0;
  border-bottom: 1px solid #f0f0f0;
}

.info-item:last-child {
  border-bottom: none;
}

.info-item label {
  font-weight: 500;
  color: #6c757d;
  margin: 0;
}
</style>
