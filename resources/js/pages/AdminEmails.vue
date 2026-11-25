<template>
<div>
  <div class="admin-bar">
    <div class="admin-tabs">
      <button v-for="t in tabs" :key="t" :class="{ on: filter === t }" @click="filter = t">{{ t }}</button>
    </div>
    <div class="admin-actions">
      <button class="btn" @click="showForm = !showForm">Compose email</button>
    </div>
  </div>

  <section v-if="showForm" class="admin-panel admin-form-panel">
    <div class="panel-head">
      <h3>Compose email</h3>
      <button class="mini-btn" @click="showForm = false">Close</button>
    </div>
    <form @submit.prevent="submit">
      <div class="fields-2">
        <div class="field">
          <label>Recipient</label>
          <select v-model.number="form.client_id">
            <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.name }} · {{ c.email }}</option>
          </select>
          <p v-if="form.errors.client_id" class="form-error">{{ form.errors.client_id }}</p>
        </div>
        <div class="field">
          <label>Template</label>
          <select v-model="form.template">
            <option v-for="t in templates" :key="t.value" :value="t.value">{{ t.label }}</option>
          </select>
        </div>
      </div>
      <div class="field">
        <label>Subject</label>
        <input v-model="form.subject" required />
        <p v-if="form.errors.subject" class="form-error">{{ form.errors.subject }}</p>
      </div>
      <div class="field">
        <label>Message</label>
        <textarea v-model="form.body" rows="5" placeholder="Write your message…"></textarea>
        <p v-if="form.errors.body" class="form-error">{{ form.errors.body }}</p>
      </div>
      <button class="btn" type="submit" :disabled="form.processing">
        {{ form.processing ? 'Sending…' : 'Send email' }}
      </button>
    </form>
  </section>

  <div class="table-wrap">
    <table>
      <thead>
        <tr><th>Recipient</th><th>Subject</th><th>Sent</th><th>Status</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <tr v-for="e in filtered" :key="e.id">
          <td><b>{{ e.to }}</b><br><span class="mono">{{ e.email }}</span></td>
          <td>{{ e.subject }}</td>
          <td>{{ e.sent }}</td>
          <td><span class="pill" :class="e.status === 'Sent' ? 'ok' : 'warn'">{{ e.status }}</span></td>
          <td><div class="table-actions"><button class="mini-btn" @click="resend(e)">Resend</button></div></td>
        </tr>
        <tr v-if="!filtered.length"><td colspan="5" class="empty">No emails in this view.</td></tr>
      </tbody>
    </table>
  </div>
</div>
</template>
<script setup>
import { computed, ref, watch } from 'vue'
import { router, useForm } from '@inertiajs/vue3'

const props = defineProps({
  emails: { type: Array, default: () => [] },
  clients: { type: Array, default: () => [] },
  templates: { type: Array, default: () => [] },
})

const tabs = ['All', 'Sent']
const filter = ref('All')
const showForm = ref(false)
const form = useForm({
  client_id: props.clients[0]?.id ?? '',
  template: 'blank',
  subject: '',
  body: '',
})

watch(() => form.template, value => {
  const template = props.templates.find(t => t.value === value)
  if (template) {
    form.subject = template.subject
    form.body = template.body
  }
})

const filtered = computed(() => props.emails.filter(e => filter.value === 'All' || e.status === filter.value))

function submit() {
  form.post('/admin/emails', {
    preserveScroll: true,
    onSuccess: () => {
      form.reset()
      showForm.value = false
    },
  })
}

function resend(e) {
  router.post(`/admin/emails/${e.uuid}/resend`, {}, { preserveScroll: true })
}
</script>
