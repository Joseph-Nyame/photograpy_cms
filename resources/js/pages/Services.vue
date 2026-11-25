<template>
<section class="page-hero">
  <div class="wrap">
    <p class="mono label">Services &amp; pricing</p>
    <h1>Pick your <span class="script">frame</span>.</h1>
    <p class="lede">Clear packages, honest pricing and no awkward surprises. Every booking records its own currency, so your quote stays consistent from first message to final invoice.</p>
  </div>
</section>

<section class="section wrap">
  <div class="section-head pricing-head">
    <div>
      <p class="mono label">The line-up</p>
      <h2>Built around <span class="script">your day</span></h2>
    </div>
    <p class="pricing-note mono">Prices are shown in each service's currency · final quote is confirmed on your booking</p>
  </div>

  <div class="plans">
    <article v-for="(s, i) in services" :key="s.id" class="plan" :class="{ featured: i === 1 }">
      <div class="plan-head">
        <span class="plan-index mono">0{{ i + 1 }}</span>
        <span class="plan-duration mono">{{ s.duration || 'Flexible' }}</span>
        <span v-if="i === 1" class="plan-badge mono">Most popular</span>
      </div>
      <h3>{{ s.name }}</h3>
      <p class="plan-summary">{{ s.summary }}</p>

      <div class="plan-price">
        <span class="plan-price-label mono">Starting at</span>
        <strong>{{ money(fromPrice(s), s.currency) }}</strong>
        <span class="currency-chip mono">{{ s.currency }}</span>
      </div>
      <p class="plan-deposit mono">{{ depositRate }}% deposit · {{ money(s.deposit ?? 0, s.currency) }} to hold the date</p>

      <div v-if="s.packages?.length" class="tiers">
        <div v-for="pkg in s.packages" :key="pkg.name" class="tier">
          <div class="tier-head">
            <b>{{ pkg.name }}</b>
            <span class="tier-from mono">{{ tierPrice(pkg) !== null ? `from ${money(tierPrice(pkg), s.currency)}` : 'tailored' }}</span>
          </div>
          <ul class="tier-list">
            <li v-for="(item, ii) in pkg.items" :key="ii">
              <span class="tick" aria-hidden="true">✓</span>
              <span class="tier-label">{{ item.label }}</span>
              <span v-if="item.value !== null" class="tier-value mono">{{ money(item.value, s.currency) }}</span>
              <span v-else class="tier-included mono">included</span>
            </li>
          </ul>
        </div>
      </div>
      <ul v-else class="plan-list">
        <li v-for="item in includes(s)" :key="item"><span class="tick" aria-hidden="true">✓</span>{{ item }}</li>
      </ul>

      <Link :href="bookingHref(s)" class="plan-cta">
        Check availability <span aria-hidden="true">↗</span>
      </Link>
      <p class="plan-foot mono">Private gallery · honest retouching · 72h delivery</p>
    </article>
  </div>

  <div class="custom-callout">
    <div>
      <p class="mono label">Something else entirely?</p>
      <h3>Tell us the brief and we'll build it.</h3>
      <p>Films, drone coverage, multi-day stories, printed albums — if it is not listed, it is still possible.</p>
    </div>
    <Link :href="customHref" class="btn ghost">Start a custom request <span class="arrow">↗</span></Link>
  </div>
</section>

<section class="section wrap">
  <div class="section-head">
    <div>
      <p class="mono label">The process</p>
      <h2>From hello to <span class="script">gallery</span></h2>
    </div>
  </div>
  <div class="rows">
    <div class="row">
      <span class="num">01</span>
      <div><h3>Check the date</h3><p class="mono">Send your date and location. We confirm availability within a day.</p></div>
      <span class="pill warn">Day 1</span>
    </div>
    <div class="row">
      <span class="num">02</span>
      <div><h3>Hold it with a deposit</h3><p class="mono">A {{ depositRate }}% deposit (from {{ money(deposit, displayCurrency) }}) secures the date. Your booking keeps the currency you selected.</p></div>
      <span class="pill pink">Day 2</span>
    </div>
    <div class="row">
      <span class="num">03</span>
      <div><h3>Shoot day</h3><p class="mono">We arrive early, stay close and keep the poses to a minimum.</p></div>
      <span class="pill ok">The day</span>
    </div>
    <div class="row">
      <span class="num">04</span>
      <div><h3>Your private gallery</h3><p class="mono">Edited photos land in a password-protected gallery within 72 hours.</p></div>
      <span class="pill">+72h</span>
    </div>
  </div>
</section>

<section class="section wrap">
  <div class="split">
    <div>
      <p class="mono label">Good to know</p>
      <h2>Questions, <span class="script">answered</span>.</h2>
      <p class="lede">Still unsure? Send a message and we'll answer honestly.</p>
      <Link href="/contact" class="btn ghost" style="margin-top:1.4rem">Ask us anything <span class="arrow">↗</span></Link>
    </div>
    <div class="faq">
      <details open>
        <summary>How far do you travel?</summary>
        <p>Anywhere in Greater Accra is included. Outside the region we add a travel fee, agreed upfront.</p>
      </details>
      <details>
        <summary>How fast is delivery?</summary>
        <p>Portraits in 24 hours, events and weddings within 72 hours. Printed albums take two to three weeks.</p>
      </details>
      <details>
        <summary>Can we choose the photos?</summary>
        <p>Yes. We send a preview gallery and you flag favourites for the final edit — included in every package.</p>
      </details>
      <details>
        <summary>Do you shoot football matches?</summary>
        <p>Regularly. Matchday coverage includes action, touchline reactions and a team portrait set.</p>
      </details>
    </div>
  </div>
</section>
</template>

<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { money } from '../money.js'
import { lowestDeposit } from '../deposit.js'

const props = defineProps({
  services: { type: Array, default: () => [] },
})

const customOption = 'Something else / custom request'
const page = usePage()
const depositRate = computed(() => Number(page.props.settings?.booking?.deposit_rate ?? 30))
const deposit = computed(() => lowestDeposit(props.services, depositRate.value))
const displayCurrency = computed(() => props.services[0]?.currency ?? 'GHS')
const customHref = `/booking?service=${encodeURIComponent(customOption)}`

function fromPrice(service) {
  const values = (service.packages ?? []).flatMap(pkg => pkg.items ?? [])
    .map(item => item.value)
    .filter(value => value !== null && value !== undefined)

  return values.length ? Math.min(...values) : service.price
}

function tierPrice(pkg) {
  const values = (pkg.items ?? [])
    .map(item => item.value)
    .filter(value => value !== null && value !== undefined)

  return values.length ? Math.min(...values) : null
}

function includes(service) {
  const value = service.includes ?? []
  if (Array.isArray(value)) return value

  return value.split('\n').map(line => line.trim()).filter(Boolean)
}

function bookingHref(service) {
  return `/booking?service=${encodeURIComponent(service.name)}`
}
</script>
