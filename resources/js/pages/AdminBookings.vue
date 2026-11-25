<template>
<div>
  <div class="admin-bar">
    <div class="admin-tabs">
      <button v-for="t in tabs" :key="t" :class="{ on: filter === t }" @click="filter = t">
        {{ t }} <span v-if="t !== 'All'">({{ count(t) }})</span>
      </button>
    </div>
    <div class="admin-actions">
      <input v-model="q" class="admin-filter" type="search" placeholder="Search bookings…" />
      <button class="btn ghost" @click="exportRows">Export CSV</button>
      <button class="btn" @click="showForm = !showForm">New booking</button>
    </div>
  </div>

  <section v-if="showForm" class="admin-panel admin-form-panel">
    <div class="panel-head">
      <h3>New booking request</h3>
      <button class="mini-btn" @click="showForm = false">Close</button>
    </div>
    <form class="fields-2" @submit.prevent="submit">
      <div class="field">
        <label>Client name</label>
        <input v-model="form.client" required />
        <p v-if="form.errors.client" class="form-error">{{ form.errors.client }}</p>
      </div>
      <div class="field">
        <label>Email</label>
        <input v-model="form.email" type="email" required />
        <p v-if="form.errors.email" class="form-error">{{ form.errors.email }}</p>
      </div>
      <div class="field"><label>Phone</label><input v-model="form.phone" /></div>
      <div class="field">
        <label>Service</label>
        <select v-model="form.service_id">
          <option :value="null">Not specified</option>
          <option v-for="s in services" :key="s.id" :value="s.id">{{ s.name }}</option>
        </select>
      </div>
      <div class="field">
        <label>Date</label>
        <input v-model="form.date" type="date" required />
        <p v-if="form.errors.date" class="form-error">{{ form.errors.date }}</p>
      </div>
      <div class="field">
        <label>Start</label>
        <input v-model="form.start_time" type="time" />
        <p v-if="form.errors.start_time" class="form-error">{{ form.errors.start_time }}</p>
      </div>
      <div class="field">
        <label>End</label>
        <input v-model="form.end_time" type="time" />
        <p v-if="form.errors.end_time" class="form-error">{{ form.errors.end_time }}</p>
      </div>
      <div class="field">
        <label>Location</label>
        <select v-model="form.location">
          <option :value="null">Not specified</option>
          <option v-for="l in locations" :key="l.name" :value="l.name">{{ l.name }} · {{ l.currency }}</option>
        </select>
        <p class="form-note">The booking currency is taken from the selected location.</p>
      </div>
      <div class="field">
        <label>Status</label>
        <select v-model="form.status">
          <option value="pending">Pending</option>
          <option value="confirmed">Confirmed</option>
        </select>
      </div>
      <div class="field full"><label>Notes</label><textarea v-model="form.note" rows="3"></textarea></div>
      <div class="field full">
        <button class="btn" type="submit" :disabled="form.processing">
          {{ form.processing ? 'Creating…' : 'Create booking' }}
        </button>
      </div>
    </form>
  </section>

  <div class="table-wrap">
    <table>
      <thead>
        <tr><th>Reference</th><th>Client</th><th>Service</th><th>Date & time</th><th>Location</th><th>Status</th><th>Payment</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <tr v-for="b in filtered" :key="b.booking_id">
          <td><span class="mono">{{ b.id }}</span></td>
          <td><b>{{ b.client }}</b><br><span class="mono">{{ b.email }}</span></td>
          <td>
            {{ b.service }}
            <span class="booking-meta">
              <span v-if="b.booking_type === 'custom'" class="pill warn">Custom</span>
              <span class="pill info mono">{{ b.currency }}</span>
            </span>
            <span v-if="b.budget" class="budget mono">Budget {{ money(b.budget, b.currency) }}</span>
            <span v-if="b.deposit_amount" class="budget mono">Deposit {{ money(b.deposit_amount, b.currency) }}</span>
          </td>
          <td>{{ b.date }}<br><span class="mono">{{ b.time }}</span></td>
          <td>{{ b.location }}</td>
          <td><span class="pill" :class="statusTone(b.status)">{{ b.status }}</span></td>
          <td><span class="pill" :class="paymentTone(b.payment_status_value)">{{ b.payment_status }}</span></td>
          <td>
            <div class="table-actions">
              <button class="mini-btn ok" @click="openUpdate(b)">Update</button>
              <button v-if="b.status === 'Pending'" class="mini-btn" @click="act(b, 'confirm')">Confirm</button>
              <button v-if="b.status === 'Pending'" class="mini-btn off" @click="act(b, 'decline')">Decline</button>
              <button v-if="b.status !== 'Pending'" class="mini-btn off" @click="act(b, 'reopen')">Reopen</button>
            </div>
          </td>
        </tr>
        <tr v-if="!filtered.length"><td colspan="8" class="empty">No bookings match this view.</td></tr>
      </tbody>
    </table>
  </div>

  <section v-if="selected" class="booking-drawer">
    <div class="panel-head">
      <div>
        <h3>{{ selected.id }} · {{ selected.client }}</h3>
        <p>{{ selected.date }} · {{ selected.service }}</p>
      </div>
      <button class="mini-btn" @click="selectedUuid = null">Close</button>
    </div>
    <form class="fields-3" @submit.prevent="saveUpdate">
      <div class="field">
        <label>Booking status</label>
        <select v-model="update.status">
          <option value="pending">Pending</option>
          <option value="confirmed">Confirmed</option>
          <option value="declined">Declined</option>
          <option value="cancelled">Cancelled</option>
        </select>
      </div>
      <div class="field">
        <label>Payment status</label>
        <select v-model="update.payment_status">
          <option value="unpaid">Unpaid</option>
          <option value="part_paid">Part payment</option>
          <option value="paid">Paid</option>
          <option value="refunded">Refunded</option>
        </select>
      </div>
      <div class="field">
        <label>Record note</label>
        <input v-model="update.note" placeholder="e.g. Deposit received via bank transfer" />
        <p v-if="update.errors.note" class="form-error">{{ update.errors.note }}</p>
      </div>
      <div class="field full">
        <button class="btn" type="submit" :disabled="update.processing">
          {{ update.processing ? 'Saving…' : 'Save update' }}
        </button>
      </div>
    </form>
    <div class="history">
      <h4>History</h4>
      <p v-if="!selected.history.length" class="empty">No activity recorded yet.</p>
      <div v-for="entry in selected.history" :key="entry.id" class="timeline-item">
        <span class="timeline-dot"></span>
        <div>
          <b>{{ entry.action_label }}</b>
          <span v-if="entry.from || entry.to" class="mono">{{ entry.from || '—' }} → {{ entry.to || '—' }}</span>
          <p v-if="entry.note">{{ entry.note }}</p>
          <small>{{ entry.actor }} · {{ entry.date }}</small>
        </div>
      </div>
    </div>
  </section>
</div>
</template>
<script setup>
import { computed, ref, watch } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import { statusTone } from '../adminData'
import { money } from '../money'

const props = defineProps({
  bookings: { type: Array, default: () => [] },
  services: { type: Array, default: () => [] },
  locations: { type: Array, default: () => [] },
  initialQuery: { type: String, default: '' },
})

const tabs = ['All', 'Pending', 'Confirmed', 'Declined']
const filter = ref('All')
const q = ref(props.initialQuery)
const showForm = ref(false)

watch(() => props.initialQuery, value => { q.value = value })

const form = useForm({
  client: '',
  email: '',
  phone: '',
  service_id: null,
  date: '',
  start_time: '',
  end_time: '',
  location: null,
  status: 'pending',
  note: '',
})

const selectedUuid = ref(null)
const update = useForm({ status: '', payment_status: '', note: '' })
const selected = computed(() => props.bookings.find(b => b.uuid === selectedUuid.value) ?? null)

const count = t => props.bookings.filter(b => b.status === t).length
const filtered = computed(() => props.bookings.filter(b => {
  const match = filter.value === 'All' || b.status === filter.value
  const term = q.value.toLowerCase()
  return match && (!term || [b.id, b.client, b.email, b.service, b.location].join(' ').toLowerCase().includes(term))
}))

function openUpdate(b) {
  selectedUuid.value = b.uuid
  update.status = b.status_value
  update.payment_status = b.payment_status_value
  update.note = ''
}

function saveUpdate() {
  if (!selected.value) return
  update.put(`/admin/bookings/${selected.value.uuid}`, {
    preserveScroll: true,
    onSuccess: () => { update.note = '' },
  })
}

function paymentTone(value) {
  return value === 'paid' ? 'ok' : value === 'part_paid' ? 'warn' : value === 'refunded' ? 'info' : 'off'
}

function act(b, action) {
  router.post(`/admin/bookings/${b.uuid}/${action}`, {}, { preserveScroll: true })
}

function submit() {
  form.post('/admin/bookings', {
    preserveScroll: true,
    onSuccess: () => {
      form.reset()
      showForm.value = false
    },
  })
}

function exportRows() {
  const rows = [['Reference', 'Client', 'Email', 'Service', 'Date', 'Time', 'Location', 'Status', 'Payment'], ...filtered.value.map(b => [b.id, b.client, b.email, b.service, b.date, b.time, b.location, b.status, b.payment_status])]
  const csv = rows.map(r => r.map(v => `"${v}"`).join(',')).join('\n')
  const url = URL.createObjectURL(new Blob([csv], { type: 'text/csv' }))
  const a = document.createElement('a')
  a.href = url
  a.download = 'lumen-bookings.csv'
  a.click()
  URL.revokeObjectURL(url)
}
</script>
