<template>
<section class="page-hero">
  <div class="wrap">
    <p class="mono label">About the studio</p>
    <h1>Two people,<br><span class="script">good light.</span></h1>
    <p class="lede">Lumen is a photography studio in Accra, Ghana. We cover weddings, events, football, birthdays and portraits — quietly, closely, and with an eye for the frames in between.</p>
  </div>
</section>

<section class="section wrap">
  <div class="split">
    <div>
      <p class="mono label">The story</p>
      <h2 style="margin:.4rem 0 1rem">Built on <span class="script">patience</span>.</h2>
      <p style="color:var(--mute);margin-bottom:1rem">We started in 2020 with one camera, a borrowed lens and a stubborn belief that the best photograph is usually the one nobody posed for. Six years and 180 stories later, that belief still runs the studio.</p>
      <p style="color:var(--mute);margin-bottom:1rem">Today Lumen is a two-person team: one behind the camera, one reading the room. We keep our calendar small so every shoot gets our full attention — and we deliver galleries in 24 to 72 hours, not weeks.</p>
      <p style="color:var(--mute)">Based in East Legon. Working across Accra, Aburi, Labadi and anywhere the story takes us.</p>
    </div>
    <div class="frame tone-green" style="aspect-ratio:4/5;box-shadow:var(--shadow)">
      <span class="idx">Est. 2020</span>
      <b>The Lumen approach</b>
    </div>
  </div>
</section>

<section class="section wrap">
  <div class="section-head">
    <div>
      <p class="mono label">How we work</p>
      <h2>Three things we <span class="script">never</span> rush</h2>
    </div>
  </div>
  <div class="rows">
    <div class="row">
      <span class="num">01</span>
      <div><h3>Patience</h3><p class="mono">We wait for the real moment instead of directing a fake one.</p></div>
      <span class="pill warn">Time</span>
    </div>
    <div class="row">
      <span class="num">02</span>
      <div><h3>Presence</h3><p class="mono">We stay close to the people, not the shot list.</p></div>
      <span class="pill pink">People</span>
    </div>
    <div class="row">
      <span class="num">03</span>
      <div><h3>Pace</h3><p class="mono">Fast edits, honest retouching, galleries out the door.</p></div>
      <span class="pill ok">Delivery</span>
    </div>
  </div>
</section>

<section class="section block-sun">
  <div class="wrap">
    <div class="stats">
      <div v-for="(s, i) in stats" :key="s.label" class="stat" :class="i === 0 ? '' : 'sun'">
        <b>{{ s.value }}</b><span>{{ s.label }}</span>
      </div>
    </div>
  </div>
</section>

<section class="section wrap">
  <div class="inner-circle">
    <div>
      <p class="mono">Next step</p>
      <h2 style="margin:.4rem 0">Tell us what you're <span class="script">planning</span>.</h2>
      <p>Send a date, a place and a rough idea. We'll reply with availability and the right package.</p>
    </div>
    <div class="hero-actions" style="margin:0">
      <Link href="/booking" class="btn">Check availability <span class="arrow">↗</span></Link>
      <Link href="/contact" class="btn ghost">Contact us</Link>
    </div>
  </div>
</section>
</template>

<script setup>
import { stats } from '../data.js'
</script>
