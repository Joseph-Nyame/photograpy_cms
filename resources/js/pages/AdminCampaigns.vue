<template>
<div>
  <div class="admin-bar">
    <div class="admin-tabs">
      <button v-for="t in tabs" :key="t" :class="{ on: filter === t }" @click="filter = t">{{ t }}</button>
    </div>
    <div class="admin-actions">
      <button class="btn" @click="showForm = !showForm">New campaign</button>
    </div>
  </div>

  <section v-if="showForm" class="admin-panel admin-form-panel">
    <div class="panel-head">
      <h3>New campaign</h3>
      <button class="mini-btn" @click="showForm = false">Close</button>
    </div>
    <form @submit.prevent="submit">
      <div class="fields-2">
        <div class="field">
          <label>Subject</label>
          <input v-model="form.subject" required />
          <p v-if="form.errors.subject" class="form-error">{{ form.errors.subject }}</p>
        </div>
        <div class="field">
          <label>Audience</label>
          <select v-model="form.audience">
            <option v-for="a in audiences" :key="a.value" :value="a.value">{{ a.label }}</option>
          </select>
          <p v-if="form.errors.audience" class="form-error">{{ form.errors.audience }}</p>
        </div>
      </div>
      <div class="field">
        <label>Message</label>
        <textarea v-model="form.body" rows="5" placeholder="Write the campaign message…"></textarea>
        <p v-if="form.errors.body" class="form-error">{{ form.errors.body }}</p>
      </div>
      <button class="btn" type="submit" :disabled="form.processing">
        {{ form.processing ? 'Queueing…' : 'Queue campaign' }}
      </button>
    </form>
  </section>

  <div class="campaign-list">
    <article v-for="c in filtered" :key="c.id" class="campaign">
      <div>
        <b>{{ c.subject }}</b>
        <div class="mono">{{ c.status === 'Sent' ? `Sent ${c.sent}` : `${c.status} · not sent` }}</div>
      </div>
      <span class="pill" :class="c.status === 'Sent' ? 'ok' : 'warn'">{{ c.status }}</span>
      <div class="camp-foot">
        <span class="mono">{{ c.recipients }} recipients</span>
        <div class="admin-actions">
          <button v-if="c.status_value !== 'sent'" class="mini-btn ok" @click="send(c)">Send now</button>
          <button class="mini-btn" @click="duplicate(c)">Duplicate</button>
          <button class="mini-btn off" @click="remove(c)">Delete</button>
        </div>
      </div>
    </article>
    <p v-if="!filtered.length" class="empty">No campaigns in this view.</p>
  </div>
</div>
</template>
<script setup>
import { computed, ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'

const props = defineProps({
  campaigns: { type: Array, default: () => [] },
  audiences: { type: Array, default: () => [] },
})

const tabs = ['All', 'Sent', 'Drafts']
const filter = ref('All')
const showForm = ref(false)
const form = useForm({ subject: '', audience: 'subscribed', body: '' })

const filtered = computed(() => props.campaigns.filter(c => {
  return filter.value === 'All'
    || (filter.value === 'Sent' && c.status_value === 'sent')
    || (filter.value === 'Drafts' && c.status_value === 'draft')
}))

function submit() {
  form.post('/admin/campaigns', {
    preserveScroll: true,
    onSuccess: () => {
      form.reset()
      showForm.value = false
    },
  })
}

function send(c) {
  router.post(`/admin/campaigns/${c.uuid}/send`, {}, { preserveScroll: true })
}

function duplicate(c) {
  router.post(`/admin/campaigns/${c.uuid}/duplicate`, {}, { preserveScroll: true })
}

function remove(c) {
  if (confirm(`Delete “${c.subject}”?`)) {
    router.delete(`/admin/campaigns/${c.uuid}`, { preserveScroll: true })
  }
}
</script>
