<template>
<div>
  <div class="admin-bar">
    <div class="admin-tabs">
      <button :class="{ on: filter === 'All' }" @click="filter = 'All'">All messages ({{ messages.length }})</button>
      <button :class="{ on: filter === 'Unread' }" @click="filter = 'Unread'">Unread ({{ unread }})</button>
    </div>
    <div class="admin-actions">
      <button class="btn ghost" @click="markAllRead" :disabled="!unread">Mark all read</button>
    </div>
  </div>

  <div class="inbox">
    <div class="inbox-list">
      <button v-for="m in filtered" :key="m.id" class="inbox-item" :class="{ on: m.id === selectedId, unread: !m.read }" @click="open(m)">
        <div class="row-top"><b>{{ m.name }}</b><time>{{ m.time }}</time></div>
        <div class="row-top"><span>{{ m.subject }}</span></div>
        <p>{{ m.body }}</p>
      </button>
      <p v-if="!filtered.length" class="empty">No messages in this view.</p>
    </div>

    <div v-if="selected" class="inbox-body">
      <div>
        <h3>{{ selected.subject }}</h3>
        <p class="inbox-meta">{{ selected.name }} · {{ selected.email }} · {{ selected.time }}</p>
      </div>
      <p>{{ selected.body }}</p>
      <div class="inbox-reply">
        <div class="field">
          <label>Reply to {{ selected.name }}</label>
          <textarea v-model="replyForm.body" placeholder="Write a reply…"></textarea>
          <p v-if="replyForm.errors.body" class="form-error">{{ replyForm.errors.body }}</p>
        </div>
        <div class="admin-actions">
          <button class="btn ghost" @click="markUnread">Mark unread</button>
          <button class="btn" @click="sendReply" :disabled="replyForm.processing">
            {{ replyForm.processing ? 'Sending…' : 'Send reply' }}
          </button>
          <span v-if="replyStatus" class="pill ok">{{ replyStatus }}</span>
        </div>
      </div>
    </div>
    <div v-else class="empty">Select a message to read and reply.</div>
  </div>
</div>
</template>
<script setup>
import { computed, ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'

const props = defineProps({
  messages: { type: Array, default: () => [] },
  unread: { type: Number, default: 0 },
})

const filter = ref('All')
const selectedId = ref(props.messages[0]?.id ?? null)
const replyStatus = ref('')

const replyForm = useForm({ body: '' })

const filtered = computed(() => filter.value === 'Unread' ? props.messages.filter(m => !m.read) : props.messages)
const selected = computed(() => props.messages.find(m => m.id === selectedId.value) ?? null)

function open(m) {
  selectedId.value = m.id
  replyStatus.value = ''
  if (!m.read) router.post(`/admin/messages/${m.uuid}/read`, {}, { preserveScroll: true })
}

function markUnread() {
  if (selected.value) router.post(`/admin/messages/${selected.value.uuid}/unread`, {}, { preserveScroll: true })
}

function markAllRead() {
  router.post('/admin/messages/read-all', {}, { preserveScroll: true })
}

function sendReply() {
  if (!selected.value) return
  replyForm.post(`/admin/messages/${selected.value.uuid}/reply`, {
    preserveScroll: true,
    onSuccess: () => {
      replyStatus.value = 'Reply sent'
      replyForm.reset()
    },
  })
}
</script>
