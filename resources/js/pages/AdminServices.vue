<template>
<div>
  <div class="admin-bar">
    <div>
      <p class="mono admin-kicker">Catalog</p>
      <h2 class="admin-title">Services &amp; packages</h2>
    </div>
    <div class="admin-actions">
      <div class="admin-tabs">
        <button v-for="t in tabs" :key="t" :class="{ on: filter === t }" @click="filter = t">{{ t }}</button>
      </div>
      <button class="btn" @click="startNew">New service</button>
    </div>
  </div>

  <section v-if="showForm" class="admin-panel service-editor">
    <div class="panel-head">
      <div>
        <p class="mono admin-kicker">{{ editId === null ? 'New entry' : 'Editing' }}</p>
        <h3>{{ editId === null ? 'Build a service' : form.name || 'Untitled service' }}</h3>
      </div>
      <button class="mini-btn" @click="closeForm">Close</button>
    </div>

    <form class="service-form" @submit.prevent="saveService">
      <section class="service-section">
        <div class="section-label">
          <span class="section-number mono">01</span>
          <div><h4>The basics</h4><p>Name it, price it, place it.</p></div>
        </div>
        <div class="field">
          <label>Service name</label>
          <input v-model="form.name" required placeholder="e.g. Wedding film" />
          <p v-if="form.errors.name" class="form-error">{{ form.errors.name }}</p>
        </div>
        <div class="field-row">
          <div class="field">
            <label>Currency</label>
            <select v-model="form.currency">
              <option v-for="c in currencies" :key="c.code" :value="c.code">{{ c.code }} — {{ c.label }}</option>
            </select>
          </div>
          <div class="field">
            <label>From price</label>
            <input v-model.number="form.price" type="number" min="0" step="0.01" required />
            <p v-if="form.errors.price" class="form-error">{{ form.errors.price }}</p>
          </div>
        </div>
        <div class="field-row">
          <div class="field"><label>Duration</label><input v-model="form.duration" placeholder="e.g. 1 hour" /></div>
          <div class="field"><label>Display order</label><input v-model.number="form.sort" type="number" min="0" /></div>
        </div>
        <div class="field">
          <label>Short description</label>
          <textarea v-model="form.desc" rows="4" placeholder="One sharp line about what this service is for."></textarea>
        </div>
      </section>

      <section class="service-section package-section">
        <div class="section-label">
          <span class="section-number mono">02</span>
          <div><h4>Pricing packages</h4><p>Give clients clear choices, not a wall of numbers.</p></div>
        </div>
        <div class="package-toolbar">
          <p class="form-note">Leave a value blank for text-only items. Values show next to each item in {{ form.currency }}.</p>
          <button type="button" class="mini-btn brand" @click="addPackage">+ Add package</button>
        </div>

        <p v-if="!form.packages.length" class="empty package-empty">No packages yet. Add one to show tiered pricing on the public page.</p>

        <div class="package-list">
          <article v-for="(pkg, pi) in form.packages" :key="pi" class="package-card">
            <header>
              <span class="package-index mono">{{ String(pi + 1).padStart(2, '0') }}</span>
              <input v-model="pkg.name" :placeholder="`Package ${pi + 1} name`" required />
              <button type="button" class="icon-btn copy" title="Duplicate package" @click="duplicatePackage(pi)">⧉</button>
              <button type="button" class="icon-btn" title="Remove package" @click="form.packages.splice(pi, 1)">×</button>
            </header>
            <div class="item-list">
              <div v-for="(item, ii) in pkg.items" :key="ii" class="item-row">
                <span class="item-grip" aria-hidden="true">⠿</span>
                <input v-model="item.label" placeholder="Item — e.g. Coverage" required />
                <div class="value-field">
                  <span class="mono">{{ form.currency }}</span>
                  <input v-model="item.value" type="number" min="0" step="0.01" placeholder="—" />
                </div>
                <button type="button" class="icon-btn" title="Remove item" @click="pkg.items.splice(ii, 1)">×</button>
              </div>
              <p v-if="!pkg.items.length" class="item-empty mono">No items yet.</p>
            </div>
            <button type="button" class="add-item" @click="addItem(pkg)">+ Add item</button>
          </article>
        </div>
        <p v-if="packageError" class="form-error">{{ packageError }}</p>
      </section>

      <div class="service-actions">
        <button type="button" class="btn ghost" @click="closeForm">Cancel</button>
        <button class="btn" type="submit" :disabled="form.processing">
          {{ form.processing ? 'Saving…' : (editId === null ? 'Create service' : 'Save changes') }}
        </button>
      </div>
    </form>
  </section>

  <div class="service-grid">
    <article v-for="s in filtered" :key="s.id" class="service-card" :class="{ paused: !s.active }">
      <div class="service-card-top">
        <span class="service-avatar mono">{{ initials(s.name) }}</span>
        <span class="pill" :class="s.active ? 'ok' : 'off'">{{ s.active ? 'Active' : 'Paused' }}</span>
      </div>
      <h3>{{ s.name }}</h3>
      <p class="service-desc">{{ s.desc || 'No description yet.' }}</p>
      <div class="service-price-row">
        <strong>{{ money(s.price, s.currency) }}</strong>
        <span class="mono currency-chip">{{ s.currency }}</span>
      </div>
      <div class="service-package-summary">
        <span class="mono">{{ (s.packages ?? []).length }} packages</span>
        <div v-for="pkg in (s.packages ?? []).slice(0, 2)" :key="pkg.name" class="summary-line">
          <span>{{ pkg.name }}</span>
          <span class="mono">{{ (pkg.items ?? []).length }} items</span>
        </div>
      </div>
      <div class="service-meta mono">{{ s.duration || 'Flexible' }} · Position {{ s.sort }}</div>
      <div class="card-actions">
        <button type="button" class="card-action pause" @click="toggle(s)">
          {{ s.active ? 'Pause' : 'Activate' }}
        </button>
        <button type="button" class="card-action" @click="duplicate(s)">Duplicate</button>
        <button type="button" class="card-action edit" @click="startEdit(s)">Edit</button>
      </div>
    </article>
    <p v-if="!filtered.length" class="empty">No services in this view.</p>
  </div>
</div>
</template>
<script setup>
import { computed, ref } from 'vue'
import { router, useForm, usePage } from '@inertiajs/vue3'
import { money } from '../money.js'
import { currencies } from '../currencies.js'

const props = defineProps({
  services: { type: Array, default: () => [] },
})

const page = usePage()

const tabs = ['All', 'Active', 'Paused']
const filter = ref('All')
const showForm = ref(false)
const editId = ref(null)
const form = useForm({ name: '', price: 0, currency: 'GHS', duration: '', sort: 1, desc: '', packages: [] })

const filtered = computed(() => props.services.filter(s =>
  filter.value === 'All' || (filter.value === 'Active' && s.active) || (filter.value === 'Paused' && !s.active)
))
const packageError = computed(() => Object.entries(form.errors)
  .find(([key]) => key.startsWith('packages'))?.[1] ?? '')

function initials(name) {
  return name.split(/\s+/).slice(0, 2).map(part => part[0]).join('').toUpperCase()
}

function startNew() {
  editId.value = null
  form.reset()
  form.clearErrors()
  form.sort = props.services.length + 1
  showForm.value = true
}

function startEdit(s) {
  editId.value = s.uuid
  form.clearErrors()
  Object.assign(form, {
    name: s.name,
    price: s.price,
    currency: s.currency ?? 'GHS',
    duration: s.duration,
    sort: s.sort,
    desc: s.description ?? s.desc,
    packages: (s.packages ?? []).map(pkg => ({
      name: pkg.name,
      items: (pkg.items ?? []).map(item => ({ label: item.label, value: item.value ?? '' })),
    })),
  })
  showForm.value = true
}

function addPackage() {
  form.packages.push({ name: '', items: [{ label: '', value: '' }] })
}

function duplicatePackage(index) {
  const source = form.packages[index]
  form.packages.splice(index + 1, 0, {
    name: `${source.name || `Package ${index + 1}`} (copy)`,
    items: source.items.map(item => ({ label: item.label, value: item.value })),
  })
}

function addItem(pkg) {
  pkg.items.push({ label: '', value: '' })
}

function closeForm() {
  showForm.value = false
}

function saveService() {
  const options = { preserveScroll: true, onSuccess: () => { showForm.value = false; form.reset() } }
  if (editId.value === null) {
    form.post('/admin/services', options)
  } else {
    form.put(`/admin/services/${editId.value}`, options)
  }
}

function duplicate(s) {
  router.post(`/admin/services/${s.uuid}/duplicate`, {}, {
    preserveScroll: true,
    onSuccess: () => {
      const copy = page.props.flash?.data
      if (copy?.uuid) startEdit(copy)
    },
  })
}

function toggle(s) {
  router.post(`/admin/services/${s.uuid}/toggle`, {}, { preserveScroll: true })
}
</script>
