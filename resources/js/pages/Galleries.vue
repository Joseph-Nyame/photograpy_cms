<template>
<section class="page-hero">
  <div class="wrap">
    <p class="mono label">Galleries</p>
    <h1 v-if="!category">Selected <span class="script">work</span>.</h1>
    <h1 v-else>{{ category.name }}<span class="script">.</span></h1>
    <p v-if="!category" class="lede">{{ categories.length }} specialities, one approach. Browse by the kind of day you're planning — each set is shot close and edited honestly.</p>
    <p v-else class="lede">{{ category.count }} sets in the archive, shot close and edited honestly. Originals kept private.</p>
  </div>
</section>

<section class="section wrap" @contextmenu.prevent>
  <template v-if="category">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:1rem;flex-wrap:wrap">
      <Link href="/galleries" class="mono link-arrow">← All galleries</Link>
      <span class="mono label">{{ category.count }} images</span>
    </div>
    <div class="frames" style="margin-top:1.5rem">
      <button v-for="(image, i) in shownImages" :key="image.id" type="button"
        class="frame frame-button" :class="['tone-' + tones[i % tones.length], { wm: image.featured, 'has-image': hasImage(image) }]"
        draggable="false" @click="openLightbox(i)">
        <img v-if="hasImage(image)" class="frame-img" :src="coverUrl(image.path)" :alt="image.alt || category.name" loading="lazy" @error="markFailed(image.path)" />
        <span class="idx">0{{ i + 1 }}</span>
        <b>{{ image.alt || category.name + ' 0' + (i + 1) }}</b>
      </button>
      <p v-if="!shownImages.length" class="empty">New work is being added to this collection.</p>
    </div>
    <div v-if="galleryPagination.hasMore" class="load-more">
      <button class="btn ghost" type="button" :disabled="loadingMore" @click="loadMore">
        {{ loadingMore ? 'Loading…' : 'Load more' }}
      </button>
      <span class="mono">{{ shownImages.length }} of {{ galleryPagination.total }} images</span>
    </div>
    <div class="inner-circle" style="margin-top:3rem">
      <div>
        <p class="mono">Like this direction?</p>
        <h2 style="margin:.4rem 0">Let's shoot your <span class="script">version</span>.</h2>
        <p>Same eye, your people, your place. Check a date and we'll take it from there.</p>
      </div>
      <div class="hero-actions" style="margin:0">
        <Link href="/booking" class="btn">Check availability <span class="arrow">↗</span></Link>
        <Link href="/services" class="btn ghost">See packages</Link>
      </div>
    </div>
  </template>

  <div v-else class="frames">
    <Link v-for="(c, i) in categories" :key="c.slug" :href="'/galleries/' + c.slug"
      class="frame" :class="['tone-' + tones[i % tones.length], { 'has-cover': hasCover(c) }]">
      <img v-if="hasCover(c)" class="frame-img" :src="coverUrl(c.cover.path)" :alt="c.cover.alt || c.name"
        loading="lazy" @error="markFailed(c.cover.path)" />
      <span class="idx">0{{ i + 1 }} · {{ c.count }} sets</span>
      <b>{{ c.name }}</b>
    </Link>
    <p v-if="!categories.length" class="empty">New work is being added to the archive.</p>
  </div>

  <div v-if="activeImage" class="lightbox" @click.self="closeLightbox">
    <button class="lightbox-close" type="button" aria-label="Close image" @click="closeLightbox">×</button>
    <div class="lightbox-body">
      <img v-if="hasImage(activeImage)" class="lightbox-img" :src="coverUrl(activeImage.path)" :alt="activeImage.alt" @error="markFailed(activeImage.path)" />
      <p v-else class="lightbox-missing">Preview unavailable for this image.</p>
      <div class="lightbox-bar">
        <button class="mini-btn" type="button" :disabled="activeIndex === 0" @click="stepLightbox(-1)">‹ Previous</button>
        <span class="mono">{{ activeIndex + 1 }} / {{ shownImages.length }} · {{ activeImage.alt }}</span>
        <button class="mini-btn" type="button" :disabled="activeIndex === shownImages.length - 1" @click="stepLightbox(1)">Next ›</button>
      </div>
    </div>
  </div>
</section>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  categories: { type: Array, default: () => [] },
  category: { type: Object, default: null },
  images: { type: Array, default: () => [] },
  pagination: { type: Object, default: () => ({ page: 1, perPage: 6, total: 0, hasMore: false, nextPage: null }) },
})

const tones = ['sun', 'pink', 'green', 'red', 'blue']
const failedImages = ref([])
const shownImages = ref([...props.images])
const galleryPagination = ref({ ...props.pagination })
const loadingMore = ref(false)
const activeIndex = ref(null)
const activeImage = computed(() => activeIndex.value === null ? null : shownImages.value[activeIndex.value] ?? null)

watch(() => props.category?.slug, () => {
  shownImages.value = [...props.images]
  galleryPagination.value = { ...props.pagination }
  closeLightbox()
})

function hasImage(image) {
  return Boolean(image?.path) && !failedImages.value.includes(image.path)
}

function hasCover(category) {
  return hasImage(category.cover)
}

function coverUrl(path) {
  if (path.startsWith('http') || path.startsWith('/')) return path
  return '/storage/' + path
}

function markFailed(path) {
  if (!failedImages.value.includes(path)) failedImages.value.push(path)
}

function loadMore() {
  if (!galleryPagination.value.nextPage || loadingMore.value) return

  loadingMore.value = true
  router.get(`/galleries/${props.category.slug}`, { page: galleryPagination.value.nextPage }, {
    only: ['images', 'pagination'],
    preserveScroll: true,
    preserveState: true,
    onSuccess: page => {
      shownImages.value = [...shownImages.value, ...(page.props.images ?? [])]
      galleryPagination.value = { ...(page.props.pagination ?? galleryPagination.value) }
    },
    onFinish: () => {
      loadingMore.value = false
    },
  })
}

function openLightbox(index) {
  activeIndex.value = index
}

function closeLightbox() {
  activeIndex.value = null
}

function stepLightbox(step) {
  const next = activeIndex.value + step
  if (next >= 0 && next < shownImages.value.length) activeIndex.value = next
}

function onKeydown(event) {
  if (activeIndex.value === null) return

  if (event.key === 'Escape') closeLightbox()
  if (event.key === 'ArrowRight') stepLightbox(1)
  if (event.key === 'ArrowLeft') stepLightbox(-1)
}

onMounted(() => window.addEventListener('keydown', onKeydown))
onBeforeUnmount(() => window.removeEventListener('keydown', onKeydown))
</script>
