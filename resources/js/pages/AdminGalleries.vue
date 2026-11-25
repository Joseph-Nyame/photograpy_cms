<template>
<div>
  <div class="admin-bar">
    <div class="admin-tabs">
      <button v-for="t in tabs" :key="t" :class="{ on: filter === t }" @click="filter = t">{{ t }}</button>
    </div>
    <div class="admin-actions">
      <input v-model="q" class="admin-filter" type="search" placeholder="Search galleries…" />
      <button class="btn" @click="showForm = !showForm">Create gallery</button>
    </div>
  </div>

  <section v-if="showForm" class="admin-panel admin-form-panel">
    <div class="panel-head">
      <h3>Create client gallery</h3>
      <button class="mini-btn" @click="showForm = false">Close</button>
    </div>
    <form class="fields-2" @submit.prevent="submit">
      <div class="field">
        <label>Gallery title</label>
        <input v-model="form.title" required />
        <p v-if="form.errors.title" class="form-error">{{ form.errors.title }}</p>
      </div>
      <div class="field">
        <label>Client</label>
        <select v-model.number="form.client_id" required>
          <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.name }}</option>
        </select>
        <p v-if="form.errors.client_id" class="form-error">{{ form.errors.client_id }}</p>
      </div>
      <div class="field">
        <label>Expiry date</label>
        <input v-model="form.expires_at" type="date" />
        <p v-if="form.errors.expires_at" class="form-error">{{ form.errors.expires_at }}</p>
      </div>
      <div class="field">
        <label>Passcode (optional)</label>
        <input v-model="form.passcode" />
        <p v-if="form.errors.passcode" class="form-error">{{ form.errors.passcode }}</p>
      </div>
      <div class="field full">
        <label class="check"><input v-model="form.allow_download" type="checkbox" /> Allow full-resolution downloads</label>
        <button class="btn" type="submit" :disabled="form.processing">{{ form.processing ? 'Creating…' : 'Create gallery' }}</button>
      </div>
    </form>
  </section>

  <div class="table-wrap">
    <table>
      <thead>
        <tr><th>Gallery</th><th>Client</th><th>Access token</th><th>Images</th><th>Expires</th><th>Downloads</th><th>Status</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <tr v-for="g in filtered" :key="g.id">
          <td><b>{{ g.title }}</b><br><span v-if="g.has_passcode" class="mono">Passcode set</span></td>
          <td>{{ g.client }}</td>
          <td><span class="mono">#{{ g.token }}</span></td>
          <td>{{ g.images }}</td>
          <td>{{ g.expires }}</td>
          <td><span class="pill" :class="g.download ? 'ok' : 'off'">{{ g.download ? 'Allowed' : 'Off' }}</span></td>
          <td><span class="pill" :class="statusTone(g.status)">{{ g.status }}</span></td>
          <td>
            <div class="table-actions">
              <button class="mini-btn" @click="copyLink(g)">{{ copied === g.id ? 'Copied' : 'Copy link' }}</button>
              <button class="mini-btn" @click="toggleDownload(g)">{{ g.download ? 'Disable' : 'Allow' }}</button>
              <Link class="mini-btn" :href="`/admin/galleries/${g.uuid}`">Manage</Link>
              <button class="mini-btn" @click="remove(g)">Delete</button>
            </div>
          </td>
        </tr>
        <tr v-if="!filtered.length"><td colspan="8" class="empty">No galleries match this view.</td></tr>
      </tbody>
    </table>
  </div>
</div>
</template>
<script setup>
import { computed, ref, watch } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import { statusTone } from '../adminData'

const props = defineProps({
  galleries: { type: Array, default: () => [] },
  clients: { type: Array, default: () => [] },
  initialQuery: { type: String, default: '' },
})

const tabs = ['All', 'Active', 'Expiring', 'Expired']
const filter = ref('All')
const q = ref(props.initialQuery)
const copied = ref(null)

watch(() => props.initialQuery, value => { q.value = value })
const showForm = ref(false)

const form = useForm({
  client_id: props.clients[0]?.id ?? null,
  title: '',
  expires_at: '',
  allow_download: true,
  passcode: '',
})

const filtered = computed(() => props.galleries.filter(g => {
  const match = filter.value === 'All' || g.status === filter.value
  const term = q.value.toLowerCase()
  return match && (!term || [g.title, g.client, g.token].join(' ').toLowerCase().includes(term))
}))

function submit() {
  form.post('/admin/galleries', {
    preserveScroll: true,
    onSuccess: () => {
      form.reset('title', 'expires_at', 'passcode')
      showForm.value = false
    },
  })
}

function toggleDownload(g) {
  router.post(`/admin/galleries/${g.uuid}/toggle-download`, {}, { preserveScroll: true })
}

function remove(g) {
  if (confirm(`Delete “${g.title}” and all its images?`)) {
    router.delete(`/admin/galleries/${g.uuid}`, { preserveScroll: true })
  }
}

async function copyLink(g) {
  try {
    await navigator.clipboard.writeText(g.url)
    copied.value = g.id
    setTimeout(() => { if (copied.value === g.id) copied.value = null }, 2000)
  } catch {
    copied.value = null
  }
}
</script>
