<template>
  <div class="whatsapp-view">
    <div class="row g-0 h-100">
      <!-- Sidebar - Chat List -->
      <div class="col-md-4 border-end">
        <div class="whatsapp-sidebar">
          <!-- Header -->
          <div class="sidebar-header">
            <h5 class="mb-0">WhatsApp Chats</h5>
            <div class="mt-3 d-flex gap-2">
              <button
                class="btn btn-sm flex-fill"
                :class="connectionStatus === 'connected' ? 'btn-success' : 'btn-warning'"
                @click="checkConnection"
              >
                <i class="fas fa-circle" style="font-size: 0.5rem;"></i>
                {{ connectionStatus === 'connected' ? 'Connected' : 'Disconnected' }}
              </button>
              <button
                v-if="connectionStatus !== 'connected'"
                class="btn btn-sm btn-primary"
                @click="showQRCode"
              >
                <i class="fas fa-qrcode"></i> Scan QR
              </button>
            </div>
          </div>

          <!-- Search -->
          <div class="p-3 border-bottom">
            <input
              v-model="searchQuery"
              type="text"
              class="form-control"
              placeholder="Search chats..."
              @input="filterChats"
            >
          </div>

          <!-- Chat List -->
          <div class="chat-list">
            <div
              v-for="chat in filteredChats"
              :key="chat.phone"
              class="chat-item"
              :class="{ active: selectedChat?.phone === chat.phone }"
              @click="selectChat(chat)"
            >
              <div class="chat-avatar">
                <i class="fas fa-user"></i>
              </div>
              <div class="chat-info">
                <div class="d-flex justify-content-between align-items-start">
                  <h6 class="chat-name">{{ chat.name }}</h6>
                  <small class="chat-time">{{ formatTime(chat.last_message_at) }}</small>
                </div>
                <p class="chat-preview">{{ chat.last_message || 'No messages' }}</p>
                <div class="chat-meta">
                  <span v-if="chat.lead" class="badge bg-primary badge-sm">
                    {{ chat.lead.status }}
                  </span>
                  <span v-if="chat.unread_count > 0" class="badge bg-success">
                    {{ chat.unread_count }}
                  </span>
                </div>
              </div>
            </div>

            <div v-if="filteredChats.length === 0" class="text-center p-4 text-muted">
              <i class="fas fa-inbox fa-3x mb-3 opacity-25"></i>
              <p>No chats found</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Chat Area -->
      <div class="col-md-8">
        <div v-if="!selectedChat" class="empty-chat">
          <i class="fab fa-whatsapp fa-4x text-success opacity-25 mb-3"></i>
          <h5 class="text-muted">Select a chat to start messaging</h5>
        </div>

        <div v-else class="chat-container">
          <!-- Chat Header -->
          <div class="chat-header">
            <div class="d-flex align-items-center">
              <div class="chat-avatar me-3">
                <i class="fas fa-user"></i>
              </div>
              <div class="flex-fill">
                <h6 class="mb-0">{{ selectedChat.name }}</h6>
                <small class="text-muted">{{ selectedChat.phone }}</small>
              </div>
              <button
                v-if="selectedChat.lead"
                class="btn btn-sm btn-outline-primary"
                @click="viewLead(selectedChat.lead.id)"
              >
                <i class="fas fa-user-circle"></i> View Lead
              </button>
              <button
                v-else
                class="btn btn-sm btn-outline-success"
                @click="createLeadFromChat"
              >
                <i class="fas fa-plus"></i> Create Lead
              </button>
            </div>
          </div>

          <!-- Messages -->
          <div class="messages-container" ref="messagesContainer">
            <div v-if="loadingMessages" class="text-center py-4">
              <div class="spinner-border text-primary" role="status"></div>
            </div>

            <div v-else>
              <div
                v-for="message in messages"
                :key="message.id"
                class="message"
                :class="message.direction"
              >
                <div class="message-bubble">
                  <div v-if="message.type === 'image' && message.media_url" class="message-media">
                    <img :src="message.media_url" alt="Image" class="img-fluid rounded">
                  </div>
                  <div v-else-if="message.type === 'document'" class="message-document">
                    <i class="fas fa-file"></i>
                    <span>{{ message.content }}</span>
                  </div>
                  <p v-if="message.content" class="message-text">{{ message.content }}</p>
                  <div class="message-meta">
                    <small>{{ formatMessageTime(message.timestamp) }}</small>
                    <i
                      v-if="message.direction === 'outbound'"
                      class="fas ms-1"
                      :class="{
                        'fa-check': message.status === 'sent',
                        'fa-check-double': message.status === 'delivered',
                        'fa-check-double text-primary': message.status === 'read'
                      }"
                    ></i>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Message Input -->
          <div class="message-input-container">
            <form @submit.prevent="sendMessage" class="message-form">
              <button
                type="button"
                class="btn btn-light"
                @click="$refs.fileInput.click()"
              >
                <i class="fas fa-paperclip"></i>
              </button>
              <input
                type="file"
                ref="fileInput"
                class="d-none"
                @change="handleFileUpload"
                accept="image/*,application/pdf"
              >
              <textarea
                v-model="messageText"
                class="form-control message-input"
                placeholder="Type a message..."
                rows="1"
                @keydown.enter.exact.prevent="sendMessage"
                @keydown.shift.enter="messageText += '\n'"
              ></textarea>
              <button
                type="submit"
                class="btn btn-success"
                :disabled="!messageText.trim() || sending"
              >
                <span v-if="sending" class="spinner-border spinner-border-sm"></span>
                <i v-else class="fas fa-paper-plane"></i>
              </button>
            </form>
            <div v-if="selectedFile" class="selected-file mt-2">
              <span>{{ selectedFile.name }}</span>
              <button class="btn btn-sm btn-link text-danger" @click="selectedFile = null">
                <i class="fas fa-times"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- QR Code Modal -->
    <div class="modal fade" id="qrModal" tabindex="-1" ref="qrModal">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Scan QR Code</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body text-center">
            <p class="text-muted">Scan this QR code with WhatsApp on your phone</p>
            <div v-if="loadingQR" class="py-5">
              <div class="spinner-border text-primary" role="status"></div>
            </div>
            <div v-else-if="qrCode" class="qr-container">
              <img :src="qrCode" alt="QR Code" class="img-fluid" style="max-width: 300px;">
            </div>
            <div v-else class="alert alert-warning">
              Failed to load QR code. Please try again.
            </div>
            <small class="text-muted d-block mt-3">
              Open WhatsApp on your phone → Settings → Linked Devices → Link a Device
            </small>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'
import { useToast } from '../composables/useToast'
const { success, error } = useToast()
import { Modal } from 'bootstrap'

const router = useRouter()

// State
const connectionStatus = ref('checking')
const chats = ref([])
const selectedChat = ref(null)
const messages = ref([])
const messageText = ref('')
const searchQuery = ref('')
const loadingMessages = ref(false)
const sending = ref(false)
const qrCode = ref('')
const loadingQR = ref(false)
const selectedFile = ref(null)
const messagesContainer = ref(null)
const qrModal = ref(null)
let qrModalInstance = null
let refreshInterval = null

// Computed
const filteredChats = computed(() => {
  if (!searchQuery.value) return chats.value
  const query = searchQuery.value.toLowerCase()
  return chats.value.filter(chat =>
    chat.name.toLowerCase().includes(query) ||
    chat.phone.includes(query) ||
    chat.last_message?.toLowerCase().includes(query)
  )
})

// Methods
const checkConnection = async () => {
  try {
    const response = await api.get('/whatsapp/status')
    connectionStatus.value = response.data.status
  } catch (error) {
    console.error('Error checking connection:', error)
    connectionStatus.value = 'disconnected'
  }
}

const showQRCode = async () => {
  loadingQR.value = true
  qrModalInstance.show()
  try {
    const response = await api.get('/whatsapp/qr')
    qrCode.value = response.data.qr
  } catch (error) {
    console.error('Error fetching QR code:', error)
  } finally {
    loadingQR.value = false
  }
}

const fetchChats = async () => {
  try {
    const response = await api.get('/whatsapp/chats')
    chats.value = response.data
  } catch (error) {
    console.error('Error fetching chats:', error)
  }
}

const selectChat = async (chat) => {
  selectedChat.value = chat
  await fetchMessages(chat.phone)
  // Mark as read
  if (chat.unread_count > 0) {
    try {
      await api.post(`/whatsapp/chats/${chat.phone}/read`)
      chat.unread_count = 0
    } catch (error) {
      console.error('Error marking as read:', error)
    }
  }
}

const fetchMessages = async (phone) => {
  loadingMessages.value = true
  try {
    const response = await api.get(`/whatsapp/chats/${phone}`)
    messages.value = response.data
    await nextTick()
    scrollToBottom()
  } catch (error) {
    console.error('Error fetching messages:', error)
  } finally {
    loadingMessages.value = false
  }
}

const sendMessage = async () => {
  if (!messageText.value.trim() && !selectedFile.value) return

  sending.value = true
  try {
    const formData = new FormData()
    formData.append('to', selectedChat.value.phone)
    formData.append('message', messageText.value)
    if (selectedFile.value) {
      formData.append('media', selectedFile.value)
    }

    await api.post('/whatsapp/send', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })

    // Add message to list optimistically
    messages.value.push({
      id: Date.now(),
      direction: 'outbound',
      type: selectedFile.value ? 'image' : 'text',
      content: messageText.value,
      timestamp: new Date().toISOString(),
      status: 'sent'
    })

    messageText.value = ''
    selectedFile.value = null
    await nextTick()
    scrollToBottom()
  } catch (error) {
    console.error('Error sending message:', error)
    error('Failed to send message: ' + (err.response?.data?.error || err.message))
  } finally {
    sending.value = false
  }
}

const handleFileUpload = (event) => {
  const file = event.target.files[0]
  if (file) {
    selectedFile.value = file
  }
}

const createLeadFromChat = async () => {
  try {
    const response = await api.post('/leads', {
      name: selectedChat.value.name,
      phone: selectedChat.value.phone,
      whatsapp_number: selectedChat.value.phone,
      source: 'whatsapp',
      status: 'new'
    })
    selectedChat.value.lead = response.data
    success('Lead created successfully!')
  } catch (error) {
    console.error('Error creating lead:', error)
    error('Failed to create lead')
  }
}

const viewLead = (leadId) => {
  router.push(`/leads/${leadId}`)
}

const filterChats = () => {
  // Debouncing is handled by v-model reactivity
}

const scrollToBottom = () => {
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
  }
}

const formatTime = (timestamp) => {
  if (!timestamp) return ''
  const date = new Date(timestamp)
  const now = new Date()
  const diffDays = Math.floor((now - date) / (1000 * 60 * 60 * 24))

  if (diffDays === 0) {
    return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
  } else if (diffDays === 1) {
    return 'Yesterday'
  } else if (diffDays < 7) {
    return date.toLocaleDateString('id-ID', { weekday: 'short' })
  } else {
    return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' })
  }
}

const formatMessageTime = (timestamp) => {
  if (!timestamp) return ''
  return new Date(timestamp).toLocaleTimeString('id-ID', {
    hour: '2-digit',
    minute: '2-digit'
  })
}

// Lifecycle
onMounted(async () => {
  qrModalInstance = new Modal(qrModal.value)
  await checkConnection()
  await fetchChats()

  // Refresh chats every 10 seconds
  refreshInterval = setInterval(() => {
    fetchChats()
    if (selectedChat.value) {
      fetchMessages(selectedChat.value.phone)
    }
  }, 10000)
})

onUnmounted(() => {
  if (refreshInterval) {
    clearInterval(refreshInterval)
  }
})
</script>

<style scoped>
.whatsapp-view {
  height: calc(100vh - 60px);
  background: #f0f2f5;
}

.whatsapp-sidebar {
  height: 100%;
  background: white;
  display: flex;
  flex-direction: column;
}

.sidebar-header {
  padding: 1.5rem;
  border-bottom: 1px solid #e9ecef;
}

.chat-list {
  flex: 1;
  overflow-y: auto;
}

.chat-item {
  display: flex;
  gap: 1rem;
  padding: 1rem;
  border-bottom: 1px solid #f0f0f0;
  cursor: pointer;
  transition: background 0.2s;
}

.chat-item:hover {
  background: #f8f9fa;
}

.chat-item.active {
  background: #e7f3ff;
}

.chat-avatar {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  flex-shrink: 0;
}

.chat-info {
  flex: 1;
  min-width: 0;
}

.chat-name {
  font-size: 1rem;
  font-weight: 600;
  margin: 0;
}

.chat-preview {
  font-size: 0.875rem;
  color: #65676b;
  margin: 0.25rem 0 0.5rem 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.chat-time {
  font-size: 0.75rem;
  color: #65676b;
}

.chat-meta {
  display: flex;
  gap: 0.5rem;
}

.empty-chat {
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background: white;
}

.chat-container {
  height: 100%;
  display: flex;
  flex-direction: column;
  background: #e5ddd5;
}

.chat-header {
  background: #f0f2f5;
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #d1d7db;
}

.messages-container {
  flex: 1;
  overflow-y: auto;
  padding: 1.5rem;
  background-image: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGcgZmlsbD0ibm9uZSIgZmlsbC1ydWxlPSJldmVub2RkIj48cGF0aCBmaWxsPSIjRjBGMkY1IiBkPSJNMCAwaDQwdjQwSDB6Ii8+PC9nPjwvc3ZnPg==');
}

.message {
  margin-bottom: 1rem;
  display: flex;
}

.message.inbound {
  justify-content: flex-start;
}

.message.outbound {
  justify-content: flex-end;
}

.message-bubble {
  max-width: 65%;
  padding: 0.5rem 0.75rem;
  border-radius: 8px;
  box-shadow: 0 1px 2px rgba(0,0,0,0.1);
}

.message.inbound .message-bubble {
  background: white;
}

.message.outbound .message-bubble {
  background: #d9fdd3;
}

.message-text {
  margin: 0;
  word-wrap: break-word;
  white-space: pre-wrap;
}

.message-media img {
  max-width: 100%;
  border-radius: 6px;
  margin-bottom: 0.5rem;
}

.message-document {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem;
  background: rgba(0,0,0,0.05);
  border-radius: 6px;
  margin-bottom: 0.5rem;
}

.message-meta {
  text-align: right;
  margin-top: 0.25rem;
}

.message-meta small {
  font-size: 0.75rem;
  color: #667781;
}

.message-input-container {
  background: #f0f2f5;
  padding: 0.75rem 1rem;
  border-top: 1px solid #d1d7db;
}

.message-form {
  display: flex;
  gap: 0.5rem;
  align-items: flex-end;
}

.message-input {
  flex: 1;
  border-radius: 20px;
  resize: none;
  max-height: 100px;
}

.selected-file {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.5rem;
  background: white;
  border-radius: 6px;
}

.qr-container {
  padding: 1rem;
  background: white;
  border-radius: 8px;
  display: inline-block;
}
</style>
