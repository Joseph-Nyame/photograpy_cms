<template>
<section class="hero hero-centre">
  <div class="wrap">
    <div class="hero-centre-top">
      <span class="eyebrow"><span class="blip"></span> Accra · Ghana · Booking 2026</span>
    </div>

    <div class="lens-orbit">
      <div class="lens-main float-slow" style="--tilt:-1deg">
        <HeroLens />
        <div class="lens-hub">
          <span class="mono">Lumen Studio</span>
          <h1>Moments<br><span class="script">kept sharp.</span></h1>
          <span class="mono">Accra · Since 2020</span>
        </div>
      </div>

      <div class="orbit-pills">
        <Link href="/booking" class="orbit-pill book float" style="--tilt:2deg">Book a date <span class="arrow">↗</span></Link>
        <Link href="/galleries" class="orbit-pill p1 float" style="--tilt:-2deg;animation-delay:-1s">View work ↗</Link>
        <Link href="/services" class="orbit-pill p2 float" style="--tilt:1.5deg;animation-delay:-2.2s">Pricing ↗</Link>
        <Link href="/about" class="orbit-pill p3 float" style="--tilt:-1.5deg;animation-delay:-3.1s">About ↗</Link>
        <Link href="/contact" class="orbit-pill p4 float" style="--tilt:2deg;animation-delay:-1.8s">Contact ↗</Link>
      </div>
    </div>

    <div class="hero-centre-foot">
      <p class="hero-lead">Weddings, events, football, birthdays and portraits across Accra and beyond. Shot with intent, delivered before the feeling fades.</p>
      <div class="hero-meta mono">
        <span>180+ stories</span><span>·</span><span>Accra &amp; beyond</span><span>·</span><span>24h galleries</span>
      </div>
    </div>
  </div>
</section>

<div class="marquee" aria-hidden="true">
  <div>
    <template v-for="n in 2" :key="n">
      <span v-for="c in categories" :key="c.slug + n">{{ c.name }}<i>✦</i></span>
    </template>
  </div>
</div>

<section class="section wrap">
  <div class="section-head">
    <div>
      <p class="mono label">Latest work</p>
      <h2>Fresh out the <span class="script">darkroom</span></h2>
    </div>
    <Link href="/galleries" class="mono link-arrow">All galleries ↗</Link>
  </div>
  <div class="feature">
    <div class="feature-art tone-sun">
      <span class="badge">New · 2026</span>
      <span class="feature-title script">Adinkra</span>
    </div>
    <div class="feature-body">
      <p class="mono">Wedding · Accra</p>
      <h3>Ama &amp; Kojo</h3>
      <p style="color:var(--mute)">A full day in East Legon: the quiet prep, the family aisle, and a reception that refused to end. Shot by two, edited in three days.</p>
      <Link href="/galleries/weddings" class="btn">View the set <span class="arrow">↗</span></Link>
      <div class="feature-meta">
        <div><span class="mono">Category</span><b>Weddings</b></div>
        <div><span class="mono">Images</span><b>412 edited</b></div>
        <div><span class="mono">Location</span><b>East Legon</b></div>
        <div><span class="mono">Delivery</span><b>72 hours</b></div>
      </div>
    </div>
  </div>
</section>

<section class="section wrap">
  <div class="section-head">
    <div>
      <p class="mono label">The catalogue</p>
      <h2>Ways to <span class="script">work</span> together</h2>
    </div>
    <Link href="/services" class="mono link-arrow">Full pricing ↗</Link>
  </div>
  <div class="rows">
    <Link v-for="(s, i) in services" :key="s.name" href="/services" class="row">
      <span class="num">0{{ i + 1 }}</span>
      <div>
        <h3>{{ s.name }}</h3>
        <p class="mono">{{ s.summary }}</p>
      </div>
      <span class="price">from {{ money(s.price, s.currency) }}</span>
    </Link>
  </div>
</section>

<section class="section block-sun">
  <div class="wrap split">
    <div>
      <p class="mono label">Live availability</p>
      <h2>Dates &amp; <span class="script">bookings</span></h2>
      <p style="margin-top:1rem;color:rgba(29,20,16,.72)">A {{ depositRate }}% deposit holds your date. The rest is due seven days before the shoot. Fewer than three confirmed bookings per date, always.</p>
      <div class="rows compact" style="margin-top:1.5rem;border-color:var(--ink)">
        <div v-for="d in openDates" :key="d.day + d.month" class="row" style="border-color:rgba(29,20,16,.2)">
          <span class="num">{{ d.day }}</span>
          <div>
            <h3>{{ d.month }}</h3>
            <p class="mono">{{ d.note }}</p>
          </div>
          <span class="pill" :class="d.tone">{{ d.status }}</span>
        </div>
      </div>
    </div>
    <div class="ticket">
      <div class="ticket-main">
        <span class="mono">Session deposit · {{ depositRate }}%</span>
        <h3>Admit one</h3>
        <p class="mono">{{ depositRate }}% of your total cost</p>
        <b class="ticket-price">{{ money(deposit, displayCurrency) }}</b>
        <Link href="/booking" class="btn">Reserve a date <span class="arrow">↗</span></Link>
      </div>
      <div class="ticket-stub">
        <span class="mono">Lumen Studio</span>
        <span class="ticket-qr" aria-hidden="true">✶</span>
        <span class="mono">No. 006</span>
      </div>
    </div>
  </div>
</section>

<section class="section wrap area-grid">
  <div class="area-card">
    <span class="area-pin">East Legon</span>
  </div>
  <div>
    <p class="mono label">Location</p>
    <h2>Find the <span class="script">studio</span></h2>
    <h3 style="margin-top:1rem">14 Boundary Road</h3>
    <p style="color:var(--mute)">East Legon, Accra</p>
    <p class="mono" style="margin-top:.8rem">Mon–Fri · 9:00–18:00 · By appointment</p>
    <div class="hero-actions" style="margin-top:1.5rem">
      <a :href="studio.map" target="_blank" rel="noopener" class="btn">Get directions <span class="arrow">↗</span></a>
      <a :href="studio.whatsapp" target="_blank" rel="noopener" class="btn ghost">WhatsApp us</a>
    </div>
  </div>
</section>

<section class="section wrap">
  <div class="about-strip">
    <div>
      <p class="mono label">About Lumen</p>
      <h2 style="margin:.4rem 0 1rem">We shoot the <span class="script">in-between</span>.</h2>
      <p style="color:var(--mute)">The glance before the vows. The bench after the match. The birthday laugh nobody planned. Lumen is a two-person studio in Accra, built on patience, good light and fast delivery.</p>
      <Link href="/about" class="btn ghost" style="margin-top:1.4rem">Read our story <span class="arrow">↗</span></Link>
    </div>
    <div class="stats">
      <div v-for="(s, i) in stats" :key="s.label" class="stat" :class="['', 'sun', 'pink', 'green'][i % 4]">
        <b>{{ s.value }}</b><span>{{ s.label }}</span>
      </div>
    </div>
  </div>
</section>

<section class="section">
  <div class="wrap">
    <div class="inner-circle">
      <div>
        <p class="mono">The inner circle</p>
        <h2 style="margin:.4rem 0">First look at new <span class="script">sets</span>.</h2>
        <p>New galleries, open dates and the odd behind-the-scenes frame. One short email a month, no noise.</p>
      </div>
      <form class="join" @submit.prevent="joined = true">
        <template v-if="!joined">
          <input v-model="email" type="email" placeholder="you@email.com" required aria-label="Email address">
          <button class="btn">Join by email <span class="arrow">↗</span></button>
        </template>
        <p v-else class="mono" style="font-size:.78rem">You're in. Check your inbox.</p>
      </form>
    </div>
  </div>
</section>
</template>

<script setup>
import { computed, ref } from 'vue'
import { usePage } from '@inertiajs/vue3'
import HeroLens from '../components/HeroLens.vue'
import { openDates, stats } from '../data.js'
import { money } from '../money.js'
import { lowestDeposit } from '../deposit.js'

const props = defineProps({
  categories: { type: Array, default: () => [] },
  services: { type: Array, default: () => [] },
})

const page = usePage()
const studio = computed(() => page.props.settings?.studio ?? {})
const displayCurrency = computed(() => props.services[0]?.currency ?? 'GHS')
const depositRate = computed(() => Number(page.props.settings?.booking?.deposit_rate ?? 30))
const deposit = computed(() => lowestDeposit(props.services, depositRate.value))
const joined = ref(false)
const email = ref('')
</script>
