<template>
<div class="admin-split">
  <section class="admin-panel">
    <div class="panel-head">
      <div>
        <h3>{{ month.label }}</h3>
        <p>Studio availability, confirmed sessions and blocked dates.</p>
      </div>
      <div class="admin-actions">
        <button class="btn ghost" @click="go(month.prev)">Previous</button>
        <button class="btn ghost" @click="go(month.next)">Next</button>
      </div>
    </div>
    <div class="cal">
      <span v-for="d in dows" :key="d" class="dow">{{ d }}</span>
      <button v-for="c in month.cells" :key="c.key" class="cal-day" :class="[c.status, { blank: c.blank, selected: !c.blank && c.day === selected }]" @click="!c.blank && (selected = c.day)">
        <template v-if="!c.blank"><span>{{ c.day }}</span><span class="slots">{{ label(c.status) }}</span></template>
      </button>
    </div>
    <div class="legend">
      <span><i class="open"></i>Open</span>
      <span><i class="booked"></i>Booked</span>
      <span><i class="limited"></i>Limited</span>
      <span><i class="blocked"></i>Closed</span>
    </div>
  </section>

  <div class="admin-col">
    <section class="admin-panel">
      <div class="panel-head">
        <h3>{{ selectedDate }}</h3>
        <span class="pill" :class="tone(selectedStatus)">{{ label(selectedStatus) }}</span>
      </div>
      <ul v-if="dayBookings.length" class="admin-list">
        <li v-for="b in dayBookings" :key="b.booking_id">
          <span><b>{{ b.client }}</b><br><span class="mono">{{ b.service }} · {{ b.time }}</span></span>
          <span class="pill" :class="statusTone(b.status)">{{ b.status }}</span>
        </li>
      </ul>
      <p v-else-if="selectedStatus === 'blocked'">This date is closed for studio work. Reason: {{ selectedReason }}.</p>
      <p v-else-if="selectedStatus === 'booked'">Sessions are confirmed for this date. Open the bookings page to review the details.</p>
      <p v-else-if="selectedStatus === 'limited'">Slots remain. Review the sessions listed above.</p>
      <p v-else>Open for bookings. No sessions are scheduled on this date.</p>
      <form v-if="selectedStatus !== 'blocked'" class="panel-actions" @submit.prevent="blockForm.date = selectedIso; blockForm.post('/admin/calendar/block', { preserveScroll: true, onSuccess: () => blockForm.reset() })">
        <input v-model="blockForm.reason" class="admin-filter" placeholder="Reason (optional)" />
        <button class="btn" type="submit">Block this date</button>
      </form>
    </section>

    <section class="admin-panel">
      <div class="panel-head">
        <h3>Blocked dates</h3>
      </div>
      <ul class="admin-list">
        <li v-for="d in blockedDates" :key="d.id">
          <span>{{ d.date }}<br><span class="mono">{{ d.reason }}</span></span>
          <button class="mini-btn" @click="unblock(d.uuid)">Re-open</button>
        </li>
        <li v-if="!blockedDates.length" class="empty">No blocked dates.</li>
      </ul>
    </section>

    <section class="admin-panel">
      <h3>Availability settings</h3>
      <form @submit.prevent="availabilityForm.post('/admin/calendar/availability', { preserveScroll: true })">
        <div class="fields-2">
          <div class="field">
            <label>Opening time</label>
            <input v-model="availabilityForm.open" type="time" />
            <p v-if="availabilityForm.errors.open" class="form-error">{{ availabilityForm.errors.open }}</p>
          </div>
          <div class="field">
            <label>Closing time</label>
            <input v-model="availabilityForm.close" type="time" />
            <p v-if="availabilityForm.errors.close" class="form-error">{{ availabilityForm.errors.close }}</p>
          </div>
          <div class="field">
            <label>Max bookings per day</label>
            <input v-model.number="availabilityForm.maxBookings" type="number" min="1" />
            <p v-if="availabilityForm.errors.maxBookings" class="form-error">{{ availabilityForm.errors.maxBookings }}</p>
          </div>
          <div class="field">
            <label>Buffer between sessions</label>
            <select v-model.number="availabilityForm.bufferMinutes">
              <option :value="30">30 minutes</option>
              <option :value="60">1 hour</option>
              <option :value="120">2 hours</option>
            </select>
          </div>
        </div>
        <button class="btn" type="submit" :disabled="availabilityForm.processing">
          {{ availabilityForm.processing ? 'Saving…' : 'Save availability' }}
        </button>
      </form>
    </section>
  </div>
</div>
</template>
<script setup>
import { computed, ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import { statusTone } from '../adminData'

const props = defineProps({
  month: { type: Object, required: true },
  bookingsByDay: { type: Object, default: () => ({}) },
  blockedDates: { type: Array, default: () => [] },
  availability: { type: Object, default: () => ({}) },
})

const dows = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa']

const today = new Date()
const selected = ref(
  today.getFullYear() === props.month.year && today.getMonth() + 1 === props.month.month
    ? today.getDate()
    : 1,
)

const blockForm = useForm({ date: '', reason: '' })
const availabilityForm = useForm({ ...props.availability })

const label = s => ({ booked: 'Booked', blocked: 'Closed', limited: 'Limited', open: 'Open' }[s] || 'Open')
const tone = s => ({ booked: 'blue', blocked: 'off', limited: 'warn', open: 'ok' }[s] || 'ok')

const selectedCell = computed(() => props.month.cells.find(c => !c.blank && c.day === selected.value) || {})
const selectedStatus = computed(() => selectedCell.value.status || 'open')
const selectedReason = computed(() => selectedCell.value.reason || 'Studio maintenance')
const selectedIso = computed(() => selectedCell.value.date || '')
const dayBookings = computed(() => props.bookingsByDay[selected.value] || [])
const selectedDate = computed(() => new Date(props.month.year, props.month.month - 1, selected.value).toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' }))

function go(target) {
  router.get('/admin/calendar', { year: target.year, month: target.month }, { preserveState: true, preserveScroll: true })
}

function unblock(id) {
  router.delete(`/admin/calendar/blocked/${id}`, { preserveScroll: true })
}
</script>
