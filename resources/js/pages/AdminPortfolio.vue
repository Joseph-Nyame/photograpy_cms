<template>
<div>
  <div class="admin-bar">
    <div class="admin-tabs">
      <button :class="{ on: filter === 'All' }" @click="filter = 'All'">All</button>
      <button v-for="c in categories" :key="c.id" :class="{ on: filter === c.name }" @click="filter = c.name">{{ c.name }}</button>
    </div>
    <div class="admin-actions">
      <button class="btn ghost" @click="showCategoryList = !showCategoryList">Manage categories</button>
      <button class="btn ghost" @click="startCategory()">Add category</button>
      <button class="btn" @click="showForm = !showForm">Add image</button>
    </div>
  </div>

  <section v-if="showCategoryList" class="admin-panel">
    <div class="panel-head">
      <h3>Portfolio categories</h3>
      <button class="mini-btn" @click="showCategoryList = false">Close</button>
    </div>
    <div class="category-list">
      <div v-for="c in categories" :key="c.id" class="campaign">
        <div><b>{{ c.name }}</b><span class="mono">{{ c.slug }} · {{ c.count }} images · order {{ c.sort }}</span></div>
        <div class="table-actions">
          <button class="mini-btn" @click="startCategory(c)">Edit</button>
          <button class="mini-btn off" @click="removeCategory(c)">Delete</button>
        </div>
      </div>
      <p v-if="!categories.length" class="empty">No categories yet.</p>
    </div>
  </section>

  <section v-if="showCategoryForm" class="admin-panel admin-form-panel">
    <div class="panel-head">
      <h3>{{ editingCategoryId ? 'Edit category' : 'Add category' }}</h3>
      <button class="mini-btn" @click="showCategoryForm = false">Close</button>
    </div>
    <form class="fields-2" @submit.prevent="saveCategory">
      <div class="field">
        <label>Name</label>
        <input v-model="categoryForm.name" required @input="slugFromName" />
        <p v-if="categoryForm.errors.name" class="form-error">{{ categoryForm.errors.name }}</p>
      </div>
      <div class="field">
        <label>Slug</label>
        <input v-model="categoryForm.slug" required />
        <p v-if="categoryForm.errors.slug" class="form-error">{{ categoryForm.errors.slug }}</p>
      </div>
      <div class="field">
        <label>Order</label>
        <input v-model.number="categoryForm.sort" type="number" min="0" />
        <p v-if="categoryForm.errors.sort" class="form-error">{{ categoryForm.errors.sort }}</p>
      </div>
      <div class="field full">
        <button class="btn" type="submit" :disabled="categoryForm.processing">
          {{ categoryForm.processing ? 'Saving…' : 'Save category' }}
        </button>
      </div>
    </form>
  </section>

  <section v-if="showForm" class="admin-panel admin-form-panel">
    <div class="panel-head">
      <h3>Add portfolio image</h3>
      <button class="mini-btn" @click="showForm = false">Close</button>
    </div>
    <form class="fields-2" @submit.prevent="submit">
      <div class="field">
        <label>Caption</label>
        <input v-model="form.alt" required />
        <p v-if="form.errors.alt" class="form-error">{{ form.errors.alt }}</p>
      </div>
      <div class="field">
        <label>Category</label>
        <select v-model="form.category_id">
          <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
        </select>
        <p v-if="form.errors.category_id" class="form-error">{{ form.errors.category_id }}</p>
      </div>
      <div class="field full">
        <label for="image">Photograph</label>
        <input id="image" ref="fileInput" type="file" accept="image/jpeg,image/png,image/webp" required @change="onFile" />
        <p v-if="form.errors.image" class="form-error">{{ form.errors.image }}</p>
      </div>
      <div class="field full"><label class="check"><input v-model="form.featured" type="checkbox" /> Feature on the homepage</label></div>
      <div class="field full">
        <button class="btn" type="submit" :disabled="form.processing">
          {{ form.processing ? 'Adding…' : 'Add to portfolio' }}
        </button>
      </div>
    </form>
  </section>

  <button type="button" class="upload-zone" @click="chooseFile" @dragover.prevent @drop.prevent="onDrop">
    <b>Click to upload or drop new photographs</b>
    <p>JPG, PNG or WebP up to 20 MB. The chosen photo appears on its gallery card.</p>
  </button>

  <div class="portfolio-grid">
    <article v-for="(img, i) in filtered" :key="img.id" class="portfolio-item">
      <div class="frame" :class="['tone-' + tones[i % tones.length], { 'has-image': hasImage(img) }]">
        <img v-if="hasImage(img)" class="frame-img" :src="mediaUrl(img.path)" :alt="img.alt" loading="lazy" @error="markFailed(img.path)" />
        <span class="idx">{{ img.category }}</span>
        <span v-if="img.cover" class="cover-badge">Card cover</span>
      </div>
        <div class="portfolio-meta">
          <div><b>{{ img.title }}</b><span class="mono">{{ img.alt }}</span></div>
          <label class="order-control">
            <span class="mono">#{{ positionFor(img) }}</span>
            <select :value="positionFor(img)" :aria-label="`Move ${img.title} to position`" @change="moveTo(img, $event)">
              <option v-for="n in positionOptions(img)" :key="n" :value="n">{{ n }}</option>
            </select>
          </label>
          <div class="table-actions">
          <button class="mini-btn" :class="{ ok: img.cover }" @click="setCover(img)">{{ img.cover ? 'Card cover' : 'Set cover' }}</button>
          <button class="mini-btn" :class="{ ok: img.featured }" @click="toggle(img)">{{ img.featured ? 'Featured' : 'Feature' }}</button>
          <button class="mini-btn off" @click="remove(img)">Delete</button>
        </div>
      </div>
    </article>
    <p v-if="!filtered.length" class="empty">No images in this category.</p>
  </div>
</div>
</template>
<script setup>
import { computed, nextTick, ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'

const props = defineProps({
  images: { type: Array, default: () => [] },
  categories: { type: Array, default: () => [] },
})

const tones = ['sun', 'pink', 'green', 'red', 'blue']
const filter = ref('All')
const showForm = ref(false)
const showCategoryList = ref(false)
const showCategoryForm = ref(false)
const editingCategoryId = ref(null)
const fileInput = ref(null)
const failedImages = ref([])

const form = useForm({ alt: '', category_id: props.categories[0]?.id ?? null, image: null, featured: false })
const categoryForm = useForm({ name: '', slug: '', sort: 0 })

const filtered = computed(() => props.images.filter(i => filter.value === 'All' || i.category === filter.value))

const orderedByCategory = computed(() => {
  const groups = {}

  for (const img of props.images) {
    groups[img.category] ??= []
    groups[img.category].push(img)
  }

  for (const list of Object.values(groups)) {
    list.sort((a, b) => (a.sort ?? 0) - (b.sort ?? 0) || a.id - b.id)
  }

  return groups
})

function positionFor(img) {
  const index = (orderedByCategory.value[img.category] ?? []).findIndex(item => item.id === img.id)

  return index === -1 ? (img.sort ?? 1) : index + 1
}

function positionOptions(img) {
  return (orderedByCategory.value[img.category] ?? []).length
}

function moveTo(img, event) {
  router.post(`/admin/portfolio/${img.uuid}/reorder`, { position: Number(event.target.value) }, { preserveScroll: true })
}

function mediaUrl(path) {
  if (path.startsWith('http') || path.startsWith('/')) return path

  return '/storage/' + path
}

function hasImage(img) {
  return Boolean(img.path) && !failedImages.value.includes(img.path)
}

function markFailed(path) {
  if (!failedImages.value.includes(path)) failedImages.value.push(path)
}

function setFile(file) {
  if (!file) return

  form.image = file
  if (!form.alt) form.alt = file.name.replace(/\.[^.]+$/, '').replace(/[-_]+/g, ' ')
}

function onFile(event) {
  setFile(event.target.files[0])
}

function onDrop(event) {
  setFile(event.dataTransfer?.files?.[0])
}

function chooseFile() {
  showForm.value = true
  nextTick(() => fileInput.value?.click())
}

function submit() {
  form.post('/admin/portfolio', {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => {
      form.reset()
      if (fileInput.value) fileInput.value.value = ''
      showForm.value = false
    },
  })
}

function slugFromName() {
  categoryForm.slug = categoryForm.name.toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '')
}

function startCategory(category = null) {
  editingCategoryId.value = category?.id ?? null
  categoryForm.name = category?.name ?? ''
  categoryForm.slug = category?.slug ?? ''
  categoryForm.sort = category?.sort ?? props.categories.length + 1
  categoryForm.clearErrors()
  showCategoryForm.value = true
}

function saveCategory() {
  const options = {
    preserveScroll: true,
    onSuccess: () => {
      categoryForm.reset()
      showCategoryForm.value = false
      showCategoryList.value = true
    },
  }

  if (editingCategoryId.value) {
    categoryForm.put(`/admin/portfolio/categories/${editingCategoryId.value}`, options)
  } else {
    categoryForm.post('/admin/portfolio/categories', options)
  }
}

function removeCategory(category) {
  if (!window.confirm(`Delete ${category.name} and all of its images?`)) return

  router.delete(`/admin/portfolio/categories/${category.id}`, {
    preserveScroll: true,
    onSuccess: () => {
      if (filter.value === category.name) filter.value = 'All'
    },
  })
}

function setCover(img) {
  router.post(`/admin/portfolio/${img.uuid}/set-cover`, {}, { preserveScroll: true })
}

function toggle(img) {
  router.post(`/admin/portfolio/${img.uuid}/toggle-featured`, {}, { preserveScroll: true })
}

function remove(img) {
  router.delete(`/admin/portfolio/${img.uuid}`, { preserveScroll: true })
}
</script>
