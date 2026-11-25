<template>
  <transition name="shutter">
    <div v-if="show" class="loader" :class="{ leaving }">
      <div class="loader-corner mono tl">Lumen Studio © 2026</div>
      <div class="loader-corner mono tr">AF · LOCKED</div>

      <div class="loader-inner">
        <LensBadge class="loader-lens" label="Lumen Studio loading" />
        <p class="loader-name">Lumen Studio</p>
        <p class="mono loader-tag">Framing the light · Accra</p>
        <div class="loader-bar"><span :style="{ width: pct + '%' }"></span></div>
        <p class="mono loader-pct">{{ String(pct).padStart(3, '0') }}%</p>
      </div>

      <div class="loader-corner mono bl">Weddings · Events · Football</div>
      <div class="loader-corner mono br">Birthdays · Portraits</div>
    </div>
  </transition>
</template>

<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue'
import LensBadge from './LensBadge.vue'

const show = ref(true)
const leaving = ref(false)
const pct = ref(0)
let tick = null
let hold = null
let close = null

onMounted(() => {
  document.documentElement.style.overflow = 'hidden'
  tick = setInterval(() => {
    pct.value = Math.min(100, pct.value + 3 + Math.floor(Math.random() * 5))
    if (pct.value >= 100) {
      clearInterval(tick)
      pct.value = 100
      hold = setTimeout(() => { leaving.value = true }, 320)
      close = setTimeout(() => {
        show.value = false
        document.documentElement.style.overflow = ''
      }, 1180)
    }
  }, 45)
})

onBeforeUnmount(() => {
  clearInterval(tick)
  clearTimeout(hold)
  clearTimeout(close)
  document.documentElement.style.overflow = ''
})
</script>
