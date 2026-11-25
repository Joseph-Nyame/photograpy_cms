<template>
<svg class="hero-lens-svg" viewBox="0 0 200 200" aria-hidden="true">
  <defs>
    <path :id="uid" d="M100,36 a64,64 0 1,1 0,128 a64,64 0 1,1 0,-128" />
  </defs>

  <g class="lens-rotor lens-rotor-slow">
    <circle cx="100" cy="100" r="90" fill="none" stroke="currentColor" stroke-width="1.4" stroke-dasharray="1 7" stroke-linecap="round" />
  </g>

  <g class="lens-rotor lens-rotor-reverse">
    <circle cx="100" cy="100" r="74" fill="none" stroke="currentColor" stroke-width="3" stroke-dasharray="2 12" opacity=".28" />
  </g>

  <g class="lens-rotor lens-rotor-slow">
    <text class="hero-ring-text">
      <textPath :href="'#' + uid" startOffset="0">LUMEN STUDIO · ACCRA, GHANA · SINCE 2020 · LUMEN STUDIO · ACCRA, GHANA · </textPath>
    </text>
  </g>

  <g class="hero-blades">
    <g transform="translate(100 100) scale(1.17) translate(-100 -100)">
      <path v-for="n in 6" :key="n" d="M100 46 L142 70 L128 100 L100 100 Z" fill="var(--sun)" :transform="`rotate(${n * 60} 100 100)`" />
    </g>
  </g>

  <circle cx="100" cy="100" r="46" fill="var(--ink)" stroke="rgba(251,243,228,.35)" stroke-width="1.5" />
</svg>
</template>

<script setup>
const uid = 'hero-lens-ring-' + Math.random().toString(36).slice(2, 8)
</script>
