<template>
  <SiteLoader v-if="showIntro && !isBare" />
  <CursorBall v-if="!isAdmin && !isBare" />
  <slot />
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { usePage } from '@inertiajs/vue3'
import Lenis from 'lenis'
import SiteLoader from './components/SiteLoader.vue'
import CursorBall from './components/CursorBall.vue'

const page = usePage()
const isAdmin = computed(() => page.url.startsWith('/admin'))
const isBare = computed(() => page.component === 'Auth/Login')
const showIntro = ref(!window.location.pathname.startsWith('/admin') && window.location.pathname !== '/login')

const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches
const supportsScrollTimeline = CSS.supports('animation-timeline: view()')
const selector = [
  '.page-hero .wrap', '.section-head', '.feature', '.plan', '.row', '.frame',
  '.ticket', '.area-card', '.about-strip', '.inner-circle', '.form-card',
  '.contact-card', '.gallery-head', '.auth-card', '.success', '.table-wrap',
].join(',')

let mutations = null
let scheduled = false
let lenis = null
let lenisRaf = 0
let flowRaf = 0
let flowEls = []

function startLenis() {
  if (reduced || isAdmin.value || lenis) return
  lenis = new Lenis({ lerp: 0.09, smoothWheel: true })
  const loop = time => {
    lenis?.raf(time)
    lenisRaf = requestAnimationFrame(loop)
  }
  lenisRaf = requestAnimationFrame(loop)
}

function stopLenis() {
  cancelAnimationFrame(lenisRaf)
  lenisRaf = 0
  lenis?.destroy()
  lenis = null
}

function updateFlow() {
  flowRaf = 0
  if (reduced || isAdmin.value) return
  const vh = window.innerHeight
  for (const el of flowEls) {
    if (!el.isConnected) continue
    const r = el.getBoundingClientRect()
    const p = Math.max(0, Math.min(1, (vh - r.top) / (vh + r.height)))
    const enter = Math.min(1, p / 0.46)
    const ease = 1 - Math.pow(1 - enter, 3)
    const exit = p > 0.46 ? (p - 0.46) / 0.54 : 0
    el.style.opacity = (0.05 + 0.95 * ease).toFixed(3)
    el.style.translate = `0 ${(80 * (1 - ease) - 42 * exit).toFixed(2)}px`
    el.style.scale = (0.955 + 0.045 * ease).toFixed(3)
  }
}

function queueFlow() {
  if (flowRaf) return
  flowRaf = requestAnimationFrame(updateFlow)
}

function scan() {
  scheduled = false
  if (reduced || isAdmin.value) return
  document.querySelectorAll(selector).forEach(el => {
    if (!el.classList.contains('flow')) {
      el.classList.add('flow')
      if (!supportsScrollTimeline) el.style.transition = 'none'
    }
    if (!supportsScrollTimeline && !flowEls.includes(el)) flowEls.push(el)
  })
  flowEls = flowEls.filter(el => el.isConnected)
  if (!supportsScrollTimeline) queueFlow()
}

function schedule() {
  if (scheduled) return
  scheduled = true
  requestAnimationFrame(scan)
}

watch(() => page.url, () => {
  if (isAdmin.value) {
    stopLenis()
  } else {
    startLenis()
    lenis?.scrollTo(0, { immediate: true })
  }
  nextTick(scan)
}, { immediate: true })

onMounted(() => {
  startLenis()
  nextTick(scan)
  if (!supportsScrollTimeline && !reduced) {
    window.addEventListener('scroll', queueFlow, { passive: true })
    window.addEventListener('resize', queueFlow)
    queueFlow()
  }
  const app = document.getElementById('app')
  if (app) {
    mutations = new MutationObserver(schedule)
    mutations.observe(app, { childList: true, subtree: true })
  }
})

onBeforeUnmount(() => {
  mutations?.disconnect()
  window.removeEventListener('scroll', queueFlow)
  window.removeEventListener('resize', queueFlow)
  cancelAnimationFrame(flowRaf)
  stopLenis()
})
</script>
