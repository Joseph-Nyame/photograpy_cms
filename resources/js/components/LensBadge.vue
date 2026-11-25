<template>
<svg class="lens-badge" viewBox="0 0 200 200" role="img" :aria-label="label">
  <defs>
    <path :id="uid" d="M100,20 a80,80 0 1,1 0,160 a80,80 0 1,1 0,-160" />
  </defs>

  <g class="lens-rotor lens-rotor-slow">
    <circle cx="100" cy="100" r="94" fill="none" stroke="currentColor" stroke-width="1.4" stroke-dasharray="1 7" stroke-linecap="round" />
    <text class="lens-ring-text">
      <textPath :href="'#' + uid" startOffset="0">SHOT WITH INTENT · ACCRA, GHANA · SHOT WITH INTENT · ACCRA, GHANA · </textPath>
    </text>
  </g>

  <g class="lens-rotor lens-rotor-reverse">
    <circle cx="100" cy="100" r="64" fill="none" stroke="currentColor" stroke-width="1.5" stroke-dasharray="3 7" opacity=".45" />
  </g>

  <circle cx="100" cy="100" r="54" fill="none" stroke="currentColor" stroke-width="3" stroke-dasharray="2 9" opacity=".28" />

  <g class="lens-blades">
    <path v-for="n in 6" :key="n" d="M100 46 L142 70 L128 100 L100 100 Z" fill="var(--sun)" :transform="`rotate(${n * 60} 100 100)`" />
  </g>

  <circle cx="100" cy="100" r="27" fill="var(--ink)" stroke="currentColor" stroke-width="1.5" />

  <g fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="square">
    <path d="M80 66 v-6 h6" />
    <path d="M120 60 h6 v6" />
    <path d="M126 134 v6 h-6" />
    <path d="M74 140 h-6 v-6" />
  </g>

  <text x="100" y="99" text-anchor="middle" class="lens-word">LUMEN</text>
  <text x="100" y="110" text-anchor="middle" class="lens-sub">STUDIO</text>
</svg>
</template>

<script setup>
defineProps({ label: { type: String, default: 'Lumen Studio camera lens mark' } })
const uid = 'lens-ring-' + Math.random().toString(36).slice(2, 8)
</script>
