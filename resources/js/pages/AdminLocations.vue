<template>
<div>
  <div class="admin-bar">
    <div class="admin-tabs">
      <button v-for="t in tabs" :key="t" :class="{ on: filter === t }" @click="filter = t">
        {{ t }} <span v-if="t !== 'All'">({{ count(t) }})</span>
      </button>
    </div>
    <div class="admin-actions">
      <input v-model="q" class="admin-filter" type="search" placeholder="Search locations…" />
      <button class="btn" @click="startNew">Add location</button>
    </div>
  </div>

  <section v-if="showForm" class="admin-panel admin-form-panel">
    <div class="panel-head">
      <h3>{{ editId ? 'Edit location' : 'New location' }}</h3>
      <button class="mini-btn" @click="closeForm">Close</button>
    </div>
    <form class="fields-2" @submit.prevent="save">
      <div class="field">
        <label>Location name</label>
        <input v-model="form.name" required placeholder="Accra, Ghana" />
        <p v-if="form.errors.name" class="form-error">{{ form.errors.name }}</p>
      </div>
      <div class="field">
        <label>Currency for this location</label>
        <select v-model="form.currency" required>
          <option v-for="c in currencies" :key="c.code" :value="c.code">{{ c.code }} — {{ c.label }}</option>
        </select>
        <p v-if="form.errors.currency" class="form-error">{{ form.errors.currency }}</p>
      </div>
      <div class="field">
        <label>Sort order</label>
        <input v-model.number="form.sort" type="number" min="0" />
        <p v-if="form.errors.sort" class="form-error">{{ form.errors.sort }}</p>
      </div>
      <div class="field full">
        <p class="form-note">Only active locations appear on the public booking form. Choosing a location sets the currency for that booking.</p>
      </div>
      <div class="field full">
        <button class="btn" type="submit" :disabled="form.processing">
          {{ form.processing ? 'Saving…' : (editId ? 'Save location' : 'Add location') }}
        </button>
      </div>
    </form>
  </section>

  <div class="table-wrap">
    <table>
      <thead>
        <tr><th>Location</th><th>Currency</th><th>Order</th><th>Availability</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <tr v-for="l in filtered" :key="l.uuid">
          <td><b>{{ l.name }}</b></td>
          <td><span class="pill info mono">{{ l.currency }}</span></td>
          <td class="mono">{{ l.sort }}</td>
          <td><span class="pill" :class="l.active ? 'ok' : 'warn'">{{ l.active ? 'Active' : 'Inactive' }}</span></td>
          <td>
            <div class="table-actions">
              <button class="mini-btn" @click="startEdit(l)">Edit</button>
              <button class="mini-btn" :class="l.active ? 'off' : 'ok'" @click="toggle(l)">
                {{ l.active ? 'Deactivate' : 'Activate' }}
              </button>
            </div>
          </td>
        </tr>
        <tr v-if="!filtered.length"><td colspan="5" class="empty">No locations match this view.</td></tr>
      </tbody>
    </table>
  </div>
</div>
</template>
<script setup>
import { computed, ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'

const props = defineProps({
  locations: { type: Array, default: () => [] },
  currencies: { type: Array, default: () => [] },
})

const tabs = ['All', 'Active', 'Inactive']
const filter = ref('All')
const q = ref('')
const showForm = ref(false)
const editId = ref(null)
const form = useForm({ name: '', currency: 'GHS', sort: 0 })

const filtered = computed(() => props.locations.filter(l =>
  (filter.value === 'All' || (filter.value === 'Active' && l.active) || (filter.value === 'Inactive' && !l.active))
  && l.name.toLowerCase().includes(q.value.trim().toLowerCase()),
))

function count(tab) {
  if (tab === 'Active') return props.locations.filter(l => l.active).length
  return props.locations.filter(l => !l.active).length
}

function startNew() {
  editId.value = null
  form.reset()
  form.clearErrors()
  form.sort = props.locations.length + 1
  showForm.value = true
}

function startEdit(l) {
  editId.value = l.uuid
  form.clearErrors()
  Object.assign(form, { name: l.name, currency: l.currency, sort: l.sort })
  showForm.value = true
}

function closeForm() {
  showForm.value = false
}

function save() {
  const options = { preserveScroll: true, onSuccess: () => { showForm.value = false; form.reset() } }
  if (editId.value === null) {
    form.post('/admin/locations', options)
  } else {
    form.put(`/admin/locations/${editId.value}`, options)
  }
}

function toggle(l) {
  router.post(`/admin/locations/${l.uuid}/toggle`, {}, { preserveScroll: true })
}
</script>
