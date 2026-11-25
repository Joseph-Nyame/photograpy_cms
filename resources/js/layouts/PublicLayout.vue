<template>
<header class="nav">
  <div class="wrap">
    <Link href="/" class="logo">Lumen<span class="dot"></span>Studio</Link>
    <nav>
      <Link href="/about">About</Link>
      <Link href="/services">Services</Link>
      <Link href="/galleries">Galleries</Link>
      <Link href="/contact">Contact</Link>
    </nav>
    <Link href="/booking" class="btn sun">Book a date <span class="arrow">↗</span></Link>
  </div>
  <span class="nav-progress" :style="{ transform: `scaleX(${progress})` }"></span>
</header>
<slot />
<footer>
  <div class="wrap footer-grid">
    <div>
      <Link href="/" class="logo">Lumen<span class="dot"></span>Studio</Link>
      <p class="mono" style="margin-top:.8rem">Photography in Accra, Ghana</p>
      <p style="margin-top:.6rem;max-width:34ch;color:rgba(251,243,228,.72)">Weddings, events, football, birthdays and portraits — shot with intent, delivered fast.</p>
    </div>
    <div>
      <h4>Explore</h4>
      <div class="footer-links">
        <Link href="/about">About the studio</Link>
        <Link href="/services">Services & pricing</Link>
        <Link href="/galleries">Galleries</Link>
        <Link href="/booking">Book a date</Link>
        <Link href="/contact">Contact</Link>
      </div>
    </div>
    <div>
      <h4>Say hello</h4>
      <div class="footer-links">
        <a :href="'mailto:'+studio.email">{{ studio.email }}</a>
        <a :href="'tel:'+studio.phone.replace(/\s/g,'')">{{ studio.phone }}</a>
        <span>{{ studio.address }}</span>
        <span class="mono" style="margin-top:.4rem">{{ studio.hours }}</span>
      </div>
    </div>
  </div>
  <div class="wrap footer-bottom">
    <span class="mono">© 2026 Lumen Studio · Accra, Ghana</span>
    <div class="socials">
      <a v-for="s in socials" :key="s.label" :href="s.href" target="_blank" rel="noopener">{{ s.label }}</a>
    </div>
    <Link href="/admin" class="mono">Admin ↗</Link>
  </div>
</footer>
</template>
<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { usePage } from '@inertiajs/vue3'

const page = usePage()
const studio = computed(() => page.props.settings?.studio ?? {})
const socials = computed(() => page.props.settings?.socials ?? [])
const progress = ref(0)
let raf = 0

function onScroll() {
  if (raf) return
  raf = requestAnimationFrame(() => {
    raf = 0
    const doc = document.documentElement
    const max = doc.scrollHeight - doc.clientHeight
    progress.value = max > 0 ? Math.min(1, doc.scrollTop / max) : 0
  })
}

onMounted(() => {
  window.addEventListener('scroll', onScroll, { passive: true })
  window.addEventListener('resize', onScroll)
  onScroll()
})

onBeforeUnmount(() => {
  window.removeEventListener('scroll', onScroll)
  window.removeEventListener('resize', onScroll)
  cancelAnimationFrame(raf)
})
</script>
