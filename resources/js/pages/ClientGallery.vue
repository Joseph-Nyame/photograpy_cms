<template>
<section v-if="!unlocked" class="section wrap auth-wrap">
  <div class="form-card auth-card">
    <span class="eyebrow"><span class="blip"></span> Private gallery</span>
    <h2>Your <span class="script">set</span>.</h2>
    <p>Enter the passcode from your delivery email to open this gallery. Links expire as shown below.</p>
    <form @submit.prevent="unlockGallery">
      <div class="field">
        <label for="code">Passcode</label>
        <input id="code" v-model="form.passcode" type="password" placeholder="••••••" required>
        <p v-if="form.errors.passcode" class="form-error">{{ form.errors.passcode }}</p>
      </div>
      <button class="btn sun" type="submit" :disabled="form.processing">
        {{ form.processing ? 'Unlocking…' : 'Unlock gallery' }} <span class="arrow">↗</span>
      </button>
    </form>
    <p class="form-note">Protected access · {{ gallery.token }} · {{ gallery.expires }} · Need help? {{ studio.email }}</p>
  </div>
</section>

<section v-else class="section wrap gallery-view" @contextmenu.prevent>
  <div class="gallery-head">
    <div>
      <p class="mono label">Private gallery · {{ gallery.token }}</p>
      <h1>{{ gallery.title }}</h1>
      <p class="lede">{{ favourites.length }} of {{ images.length }} frames saved. Tap save on the ones you love — we'll use them for your final edit.</p>
    </div>
    <div class="gallery-meta">
      <span class="pill ok">{{ gallery.expires }}</span>
      <span class="pill">{{ favourites.length }} saved</span>
      <button class="pill pink" style="cursor:pointer" @click="lockGallery">Lock</button>
    </div>
  </div>

  <div class="frames">
    <div v-for="image in images" :key="image.id" class="frame wm" :class="'tone-' + tones[(image.id - 1) % tones.length]" draggable="false" @dragstart.prevent>
      <span class="idx">{{ image.label }}</span>
      <img class="frame-img" :src="image.preview_url" :alt="image.label" draggable="false" @dragstart.prevent @contextmenu.prevent />
      <div class="frame-actions">
        <button class="fav" :class="{ on: image.favourite }" @click="toggleFavourite(image)">
          {{ image.favourite ? 'Saved' : 'Save' }}
        </button>
        <a v-if="image.download_url" class="fav" :href="image.download_url">Original</a>
      </div>
    </div>
    <p v-if="!images.length" class="empty">No images have been added to this gallery yet.</p>
  </div>

  <div class="inner-circle" style="margin-top:3rem">
    <div>
      <p class="mono">Need a hand?</p>
      <h2 style="margin:.4rem 0">Downloads &amp; <span class="script">questions</span>.</h2>
      <p v-if="gallery.allow_download">Originals are available through the signed links above. Please only share them with your chosen print lab.</p>
      <p v-else>This gallery is preview quality and watermarked by design. Originals stay on our private disk and are released on request.</p>
    </div>
    <div class="hero-actions" style="margin:0">
      <a :href="'mailto:' + studio.email" class="btn">Request originals <span class="arrow">↗</span></a>
      <a :href="studio.whatsapp" target="_blank" rel="noopener" class="btn ghost">WhatsApp us</a>
    </div>
  </div>
</section>
</template>

<script setup>
import { computed } from 'vue'
import { router, useForm, usePage } from '@inertiajs/vue3'

const page = usePage()
const studio = computed(() => page.props.settings?.studio ?? {})

const props = defineProps({
  gallery: { type: Object, required: true },
  images: { type: Array, default: () => [] },
  favourites: { type: Array, default: () => [] },
  unlocked: { type: Boolean, default: false },
})

const tones = ['sun', 'pink', 'green', 'red', 'blue']
const form = useForm({ passcode: '' })

function unlockGallery() {
  form.post(`/g/${props.gallery.token}/unlock`, {
    preserveScroll: true,
    onSuccess: () => form.reset(),
  })
}

function lockGallery() {
  router.post(`/g/${props.gallery.token}/lock`, {}, { preserveScroll: true })
}

function toggleFavourite(image) {
  router.post(`/g/${props.gallery.token}/favourites/${image.uuid}`, {}, { preserveScroll: true })
}
</script>
