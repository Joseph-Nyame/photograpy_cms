<template>
  <div v-if="enabled" aria-hidden="true">
    <span ref="dotEl" class="cursor-dot"></span>
    <span ref="ballEl" class="cursor-ball"></span>
  </div>
</template>

<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue'

const enabled = ref(
  window.matchMedia('(pointer: fine)').matches &&
  !window.matchMedia('(prefers-reduced-motion: reduce)').matches
)

const dotEl = ref(null)
const ballEl = ref(null)

const target = { x: 0, y: 0 }
const pos = { x: 0, y: 0 }
const prev = { x: 0, y: 0 }
let raf = 0
let active = false
let pressing = false
let hover = false

function show() {
  if (ballEl.value) ballEl.value.style.opacity = '1'
  if (dotEl.value) dotEl.value.style.opacity = '1'
}

function hide() {
  active = false
  if (ballEl.value) ballEl.value.style.opacity = '0'
  if (dotEl.value) dotEl.value.style.opacity = '0'
  document.documentElement.classList.remove('has-cursor')
}

function move(e) {
  target.x = e.clientX
  target.y = e.clientY
  if (!active) {
    active = true
    pos.x = e.clientX
    pos.y = e.clientY
    prev.x = pos.x
    prev.y = pos.y
    document.documentElement.classList.add('has-cursor')
    if (ballEl.value) {
      ballEl.value.style.transform =
        `translate3d(${e.clientX}px, ${e.clientY}px, 0) translate(-50%, -50%)`
    }
    show()
  }
  if (dotEl.value) {
    dotEl.value.style.transform =
      `translate3d(${e.clientX}px, ${e.clientY}px, 0) translate(-50%, -50%)`
  }
}

function frame() {
  if (active && ballEl.value) {
    pos.x += (target.x - pos.x) * 0.16
    pos.y += (target.y - pos.y) * 0.16
    const vx = pos.x - prev.x
    const vy = pos.y - prev.y
    prev.x = pos.x
    prev.y = pos.y
    const speed = Math.min(Math.hypot(vx, vy), 46)
    const angle = speed > 0.01 ? (Math.atan2(vy, vx) * 180) / Math.PI : 0
    const sx = 1 + speed * 0.012
    const sy = Math.max(0.7, 1 - speed * 0.007)
    const press = pressing ? 0.8 : 1
    ballEl.value.style.transform =
      `translate3d(${pos.x}px, ${pos.y}px, 0) translate(-50%, -50%) rotate(${angle}deg) scale(${sx * press}, ${sy * press})`
  }
  raf = requestAnimationFrame(frame)
}

function over(e) {
  if (!(e.target instanceof Element)) return
  const next = !!e.target.closest('a, button, [role="button"], input, textarea, select, label')
  if (next === hover) return
  hover = next
  ballEl.value?.classList.toggle('is-hover', hover)
}

function down() {
  pressing = true
}

function up() {
  pressing = false
}

onMounted(() => {
  if (!enabled.value) return
  window.addEventListener('pointermove', move, { passive: true })
  window.addEventListener('pointerdown', down, { passive: true })
  window.addEventListener('pointerup', up, { passive: true })
  window.addEventListener('blur', hide)
  document.addEventListener('pointerover', over, { passive: true })
  document.addEventListener('mouseleave', hide)
  raf = requestAnimationFrame(frame)
})

onBeforeUnmount(() => {
  document.documentElement.classList.remove('has-cursor')
  window.removeEventListener('pointermove', move)
  window.removeEventListener('pointerdown', down)
  window.removeEventListener('pointerup', up)
  window.removeEventListener('blur', hide)
  document.removeEventListener('pointerover', over)
  document.removeEventListener('mouseleave', hide)
  cancelAnimationFrame(raf)
})
</script>
