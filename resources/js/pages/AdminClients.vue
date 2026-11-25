<template>
<div>
  <div class="admin-bar">
    <div class="admin-tabs">
      <button v-for="t in tabs" :key="t" :class="{ on: filter === t }" @click="filter = t">{{ t }}</button>
    </div>
    <div class="admin-actions">
      <input v-model="q" class="admin-filter" type="search" placeholder="Search clients…" />
      <button class="btn ghost" @click="exportRows">Export CSV</button>
      <button class="btn" @click="showForm = !showForm">Add client</button>
    </div>
  </div>

  <section v-if="showForm" class="admin-panel admin-form-panel">
    <div class="panel-head">
      <h3>Add client</h3>
      <button class="mini-btn" @click="showForm = false">Close</button>
    </div>
    <form class="fields-2" @submit.prevent="submit">
      <div class="field">
        <label>Full name</label>
        <input v-model="form.name" required />
        <p v-if="form.errors.name" class="form-error">{{ form.errors.name }}</p>
      </div>
      <div class="field">
        <label>Email</label>
        <input v-model="form.email" type="email" required />
        <p v-if="form.errors.email" class="form-error">{{ form.errors.email }}</p>
      </div>
      <div class="field"><label>Phone</label><input v-model="form.phone" /></div>
      <div class="field">
        <label>Source</label>
        <select v-model="form.source">
          <option value="manual">Manual</option>
          <option value="booking">Booking</option>
          <option value="contact">Contact</option>
        </select>
      </div>
      <div class="field full"><label>Marketing</label><label class="check"><input v-model="form.subscribed" type="checkbox" /> Subscribed to studio emails</label></div>
      <div class="field full">
        <button class="btn" type="submit" :disabled="form.processing">
          {{ form.processing ? 'Saving…' : 'Save client' }}
        </button>
      </div>
    </form>
  </section>

  <div class="table-wrap">
    <table>
      <thead>
        <tr><th>Client</th><th>Contact</th><th>Source</th><th>Bookings</th><th>Last activity</th><th>Marketing</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <tr v-for="c in filtered" :key="c.id">
          <td><b>{{ c.name }}</b><br><span class="mono">Client</span></td>
          <td>{{ c.email }}<br><span class="mono">{{ c.phone }}</span></td>
          <td>{{ c.source }}</td>
          <td>{{ c.bookings }}</td>
          <td>{{ c.last }}</td>
          <td><span class="pill" :class="c.subscribed ? 'ok' : 'off'">{{ c.subscribed ? 'Subscribed' : 'Opted out' }}</span></td>
          <td>
            <div class="table-actions">
              <button class="mini-btn" @click="toggle(c)">{{ c.subscribed ? 'Unsubscribe' : 'Subscribe' }}</button>
            </div>
          </td>
        </tr>
        <tr v-if="!filtered.length"><td colspan="7" class="empty">No clients match this view.</td></tr>
      </tbody>
    </table>
  </div>
</div>
</template>
<script setup>
import { computed, ref, watch } from 'vue'
import { router, useForm } from '@inertiajs/vue3'

const props = defineProps({
  clients: { type: Array, default: () => [] },
  initialQuery: { type: String, default: '' },
})

const tabs = ['All', 'Subscribed', 'Opted out', 'Has bookings']
const filter = ref('All')
const q = ref(props.initialQuery)
const showForm = ref(false)

watch(() => props.initialQuery, value => { q.value = value })

const form = useForm({ name: '', email: '', phone: '', source: 'manual', subscribed: true })

const filtered = computed(() => props.clients.filter(c => {
  const match = filter.value === 'All'
    || (filter.value === 'Subscribed' && c.subscribed)
    || (filter.value === 'Opted out' && !c.subscribed)
    || (filter.value === 'Has bookings' && c.bookings > 0)
  const term = q.value.toLowerCase()
  return match && (!term || [c.name, c.email, c.phone, c.source].join(' ').toLowerCase().includes(term))
}))

function toggle(c) {
  router.post(`/admin/clients/${c.uuid}/toggle-subscription`, {}, { preserveScroll: true })
}

function submit() {
  form.post('/admin/clients', {
    preserveScroll: true,
    onSuccess: () => {
      form.reset()
      showForm.value = false
    },
  })
}

function exportRows() {
  const rows = [['Name', 'Email', 'Phone', 'Source', 'Bookings', 'Subscribed'], ...filtered.value.map(c => [c.name, c.email, c.phone, c.source, c.bookings, c.subscribed ? 'Yes' : 'No'])]
  const csv = rows.map(r => r.map(v => `"${v}"`).join(',')).join('\n')
  const url = URL.createObjectURL(new Blob([csv], { type: 'text/csv' }))
  const a = document.createElement('a')
  a.href = url
  a.download = 'lumen-clients.csv'
  a.click()
  URL.revokeObjectURL(url)
}
</script>
