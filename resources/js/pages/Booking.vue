<template>
<section class="page-hero">
  <div class="wrap">
    <p class="mono label">Book a date</p>
    <h1>Hold your <span class="script">date</span>.</h1>
    <p class="lede">Tell us what you're planning. We'll confirm availability within a day, then send a short brief and a {{ depositRate }}% deposit link.</p>
  </div>
</section>

<section class="section wrap">
  <div class="split" style="align-items:start">
    <form v-if="!sent" class="form-card" @submit.prevent="submit">
      <div class="form-grid">
        <div class="field">
          <label for="name">Your name</label>
          <input id="name" v-model="form.name" required placeholder="Ama Boateng">
          <p v-if="form.errors.name" class="form-error">{{ form.errors.name }}</p>
        </div>
        <div class="field">
          <label for="email">Email</label>
          <input id="email" v-model="form.email" type="email" required placeholder="you@email.com">
          <p v-if="form.errors.email" class="form-error">{{ form.errors.email }}</p>
        </div>
        <div class="field">
          <label for="phone">Phone / WhatsApp</label>
          <input id="phone" v-model="form.phone" placeholder="+233 20 000 0000">
          <p v-if="form.errors.phone" class="form-error">{{ form.errors.phone }}</p>
        </div>
        <div class="field">
          <label for="service">What are you booking?</label>
          <select id="service" v-model="form.service">
            <option v-for="s in services" :key="s.name" :value="s.name">{{ s.name }}</option>
            <option value="Not sure yet">Not sure yet</option>
            <option :value="customOption">{{ customOption }}</option>
          </select>
        </div>
        <div v-if="servicePackages.length" class="field">
          <label for="package">Package</label>
          <select id="package" v-model="form.package">
            <option v-for="pkg in servicePackages" :key="pkg.name" :value="pkg.name">{{ pkg.name }} · {{ packagePriceLabel(pkg) }}</option>
          </select>
          <p class="form-note">Prices use {{ packageCurrency }}.</p>
        </div>
        <div v-if="isCustom" class="field full custom-request">
          <label for="custom_details">Tell us what you have in mind</label>
          <textarea id="custom_details" v-model="form.custom_details" required placeholder="e.g. A sunset film shoot for two hours with a drone, plus printed albums."></textarea>
          <p v-if="form.errors.custom_details" class="form-error">{{ form.errors.custom_details }}</p>
          <label for="budget">Indicative total cost (optional)</label>
          <input id="budget" v-model="form.budget" type="number" min="0" step="0.01" :placeholder="`e.g. ${money(1500, currency)}`">
          <small>We'll confirm a proper quote after we talk. The deposit estimate uses {{ depositRate }}% of this amount.</small>
        </div>
        <div class="field full">
          <label>Preferred date</label>
          <div class="date-value">
            <span :class="{ muted: !form.date }">{{ form.date ? formatDate(form.date) : 'Choose a date from the calendar' }}</span>
            <button v-if="form.date" type="button" class="mini-btn" @click="form.date = ''">Clear</button>
          </div>
          <p v-if="form.errors.date" class="form-error">{{ form.errors.date }}</p>
        </div>
        <div class="field">
          <label for="location">Location</label>
          <select id="location" v-model="form.location" required>
            <option value="" disabled>Choose a location</option>
            <option v-for="l in locations" :key="l.name" :value="l.name">{{ l.name }} · {{ l.currency }}</option>
          </select>
          <p v-if="matchingLocation" class="form-note">Location follows the selected package automatically.</p>
          <p v-else class="form-note">No active location is mapped to {{ packageCurrency }} yet — choose one manually.</p>
          <p v-if="form.errors.location" class="form-error">{{ form.errors.location }}</p>
        </div>
        <div class="field full">
          <label for="message">Tell us more</label>
          <textarea id="message" v-model="form.message" placeholder="The plan, the vibe, the must-have shots…"></textarea>
          <p v-if="form.errors.message" class="form-error">{{ form.errors.message }}</p>
        </div>
      </div>
      <button class="btn sun" :disabled="form.processing">
        {{ form.processing ? 'Sending…' : 'Request this date' }} <span class="arrow">↗</span>
      </button>
      <p class="form-note">No payment yet. We confirm availability first, then send the deposit link.</p>
    </form>

    <div v-else class="success">
      <p class="mono">Request received</p>
      <h2 style="font-size:clamp(1.8rem,4vw,2.6rem);margin:.5rem 0">Thanks, {{ submitted?.name || 'friend' }}.</h2>
      <p style="color:rgba(251,243,228,.85)">We've got your request for {{ submitted?.date || 'your date' }}. Expect an email within one working day with availability and next steps.</p>
      <button class="btn" style="margin-top:1.4rem" @click="reset">Book another date</button>
    </div>

    <aside>
      <div class="ticket">
        <div class="ticket-main">
          <span class="mono">Session deposit · {{ depositRate }}%</span>
          <h3>Admit one</h3>
          <p class="mono">{{ totalCost ? `${money(totalCost, currency)} total cost` : 'Holds your date' }}</p>
          <b class="ticket-price">{{ money(deposit, currency) }}</b>
          <span class="mono">Balance due 7 days before the shoot</span>
        </div>
        <div class="ticket-stub">
          <span class="mono">Lumen</span>
          <span class="ticket-qr" aria-hidden="true">✶</span>
          <span class="mono">No. 006</span>
        </div>
      </div>

      <div class="section-head" style="margin:2rem 0 0">
        <div>
          <p class="mono label">Live availability</p>
          <h3 style="font-size:1.4rem">Pick a date</h3>
        </div>
      </div>
      <div class="booking-cal-wrap">
        <div class="booking-cal-head">
          <button type="button" :disabled="!calendar.canPrev" @click="goMonth(calendar.prev)">‹</button>
          <strong>{{ calendar.label }}</strong>
          <button type="button" :disabled="!calendar.canNext" @click="goMonth(calendar.next)">›</button>
        </div>
        <div class="booking-cal">
          <span v-for="day in weekdays" :key="day" class="booking-dow">{{ day }}</span>
          <button
            v-for="cell in calendar.cells"
            :key="cell.key"
            type="button"
            class="booking-day"
            :class="[cell.status, { blank: cell.blank, selected: form.date === cell.date, today: cell.today }]"
            :disabled="cell.blank || !cell.selectable"
            :aria-pressed="form.date === cell.date"
            @click="toggleDate(cell)"
          >
            <template v-if="!cell.blank">
              <span>{{ cell.day }}</span>
              <small v-if="cell.selectable">{{ cell.status === 'limited' ? '1 left' : 'Open' }}</small>
            </template>
          </button>
        </div>
        <div class="booking-cal-legend">
          <span><i class="open"></i>Open</span>
          <span><i class="limited"></i>Filling up</span>
          <span><i class="booked"></i>Booked</span>
          <span><i class="blocked"></i>Closed</span>
        </div>
      </div>
      <p v-if="form.date" class="date-chosen">Selected: {{ formatDate(form.date) }} · tap it again to clear</p>
      <p class="form-note">Tap an open day to select it, tap again to unselect. We confirm availability before taking a deposit.</p>
    </aside>
  </div>
</section>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { router, useForm, usePage } from '@inertiajs/vue3'
import { money } from '../money.js'
import { depositFor } from '../deposit.js'

const props = defineProps({
  services: { type: Array, default: () => [] },
  locations: { type: Array, default: () => [] },
  calendar: { type: Object, default: () => ({ label: '', cells: [], prev: {}, next: {}, canPrev: false, canNext: false }) },
})

const page = usePage()
const depositRate = computed(() => Number(page.props.settings?.booking?.deposit_rate ?? 30))
const customOption = 'Something else / custom request'
const requestedService = new URLSearchParams(window.location.search).get('service')
const serviceFor = name => props.services.find(s => s.name === name) ?? null
const defaultService = () => {
  if (requestedService && serviceFor(requestedService)) return requestedService
  return props.services[0]?.name ?? 'Not sure yet'
}
const sent = ref(false)
const submitted = ref(null)
const weekdays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']

const form = useForm({
  name: '',
  email: '',
  phone: '',
  service: defaultService(),
  package: '',
  date: '',
  location: props.locations[0]?.name ?? '',
  message: '',
  booking_type: 'standard',
  custom_details: '',
  budget: '',
})

const isCustom = computed(() => form.booking_type === 'custom')
const selectedService = computed(() => serviceFor(form.service))
const servicePackages = computed(() => selectedService.value?.packages ?? [])
const selectedPackage = computed(() => servicePackages.value.find(pkg => pkg.name === form.package) ?? servicePackages.value[0] ?? null)
const packageCurrency = computed(() => selectedService.value?.currency ?? 'GHS')
const matchingLocation = computed(() => props.locations.find(l => l.currency === packageCurrency.value) ?? null)
const currency = computed(() => props.locations.find(l => l.name === form.location)?.currency ?? 'GHS')

function packagePrice(pkg) {
  const values = (pkg?.items ?? [])
    .map(item => item?.value)
    .filter(value => value !== null && value !== undefined)
    .map(Number)

  return values.length ? Math.min(...values) : selectedService.value?.price ?? 0
}

function packagePriceLabel(pkg) {
  const price = packagePrice(pkg)

  return price > 0 ? money(price, packageCurrency.value) : 'tailored'
}

const totalCost = computed(() => {
  if (isCustom.value) return Number(form.budget) || 0

  return selectedPackage.value ? packagePrice(selectedPackage.value) : selectedService.value?.price ?? 0
})
const deposit = computed(() => depositFor(totalCost.value, depositRate.value))

function syncLocationFromPackage() {
  if (matchingLocation.value) form.location = matchingLocation.value.name
}

function setDefaultPackage() {
  form.package = servicePackages.value[0]?.name ?? ''
  syncLocationFromPackage()
}

watch(() => form.service, setDefaultPackage)
watch(() => form.package, syncLocationFromPackage)
setDefaultPackage()

function formatDate(iso) {
  return new Intl.DateTimeFormat(undefined, { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })
    .format(new Date(`${iso}T12:00:00`))
}

function toggleDate(cell) {
  if (cell.blank || !cell.selectable) return
  form.date = form.date === cell.date ? '' : cell.date
}

function goMonth(target) {
  if (!target?.year || !target?.month) return

  router.get('/booking', { year: target.year, month: target.month }, {
    only: ['calendar'],
    preserveScroll: true,
    preserveState: true,
  })
}

function submit() {
  form.post('/booking', {
    preserveScroll: true,
    onSuccess: () => {
      submitted.value = { name: form.name, date: formatDate(form.date) }
      sent.value = true
      form.reset()
    },
  })
}

function reset() {
  sent.value = false
  submitted.value = null
  form.reset()
}
</script>
