<template>
<div>
  <div class="admin-bar">
    <div>
      <Link href="/admin/galleries" class="link-arrow">← All galleries</Link>
      <h2>{{ gallery.title }}</h2>
      <p class="admin-note">{{ gallery.client }} · <span class="mono">#{{ gallery.token }}</span></p>
    </div>
    <div class="admin-actions">
      <span class="pill" :class="statusTone(gallery.status)">{{ gallery.status }}</span>
      <button class="btn ghost" @click="copyLink">{{ copied ? 'Copied' : 'Copy link' }}</button>
      <button class="btn ghost" @click="toggleDownload">{{ gallery.download ? 'Disable downloads' : 'Allow downloads' }}</button>
    </div>
  </div>

  <section class="admin-panel">
    <div class="panel-head">
      <div>
        <h3>Images ({{ images.length }})</h3>
        <p>Expires {{ gallery.expires }}<span v-if="gallery.has_passcode"> · passcode protected</span></p>
      </div>
    </div>

    <form class="fields-2" @submit.prevent="upload">
      <div class="field full">
        <label>Add images</label>
        <input type="file" accept="image/*" multiple @change="onFiles" />
        <p v-if="uploadForm.errors.images" class="form-error">{{ uploadForm.errors.images }}</p>
        <p v-if="uploadForm.errors['images.0']" class="form-error">{{ uploadForm.errors['images.0'] }}</p>
      </div>
      <div class="field full">
        <button class="btn" type="submit" :disabled="uploadForm.processing || !uploadForm.images.length">
          {{ uploadForm.processing ? 'Uploading…' : 'Upload' }}
        </button>
      </div>
    </form>

    <div v-if="images.length" class="gallery-thumbs">
      <figure v-for="img in images" :key="img.id">
        <img :src="img.url" :alt="gallery.title" loading="lazy" />
        <button class="mini-btn" @click="removeImage(img)">Remove</button>
      </figure>
    </div>
    <p v-else class="empty">No images yet. Upload the first set above.</p>
  </section>
</div>
</template>
<script setup>
import { ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import { statusTone } from '../adminData'

const props = defineProps({
  gallery: { type: Object, required: true },
  images: { type: Array, default: () => [] },
})

const copied = ref(false)
const uploadForm = useForm({ images: [] })

function onFiles(e) {
  uploadForm.images = Array.from(e.target.files)
}

function upload() {
  uploadForm.post(`/admin/galleries/${props.gallery.uuid}/images`, {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => {
      uploadForm.reset()
    },
  })
}

function removeImage(img) {
  if (confirm('Remove this image?')) {
    router.delete(`/admin/galleries/images/${img.uuid}`, { preserveScroll: true })
  }
}

function toggleDownload() {
  router.post(`/admin/galleries/${props.gallery.uuid}/toggle-download`, {}, { preserveScroll: true })
}

async function copyLink() {
  try {
    await navigator.clipboard.writeText(props.gallery.url)
    copied.value = true
    setTimeout(() => { copied.value = false }, 2000)
  } catch {
    copied.value = false
  }
}
</script>
