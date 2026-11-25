<template>
<section class="page-hero">
  <div class="wrap">
    <p class="mono label">Contact</p>
    <h1>Say <span class="script">hello</span>.</h1>
    <p class="lede">Questions, commissions, or a date you want to hold — this reaches us directly. We reply within one working day.</p>
  </div>
</section>

<section class="section wrap">
  <div class="split" style="align-items:start">
    <form v-if="!sent" class="form-card" @submit.prevent="submit">
      <div class="form-grid">
        <div class="field">
          <label for="c-name">Your name</label>
          <input id="c-name" v-model="form.name" required placeholder="Kojo Mensah">
          <p v-if="form.errors.name" class="form-error">{{ form.errors.name }}</p>
        </div>
        <div class="field">
          <label for="c-email">Email</label>
          <input id="c-email" v-model="form.email" type="email" required placeholder="you@email.com">
          <p v-if="form.errors.email" class="form-error">{{ form.errors.email }}</p>
        </div>
        <div class="field full">
          <label for="c-phone">Phone / WhatsApp</label>
          <input id="c-phone" v-model="form.phone" placeholder="+233 20 000 0000">
          <p v-if="form.errors.phone" class="form-error">{{ form.errors.phone }}</p>
        </div>
        <div class="field full">
          <label for="c-message">Message</label>
          <textarea id="c-message" v-model="form.message" required placeholder="What can we help with?"></textarea>
          <p v-if="form.errors.message" class="form-error">{{ form.errors.message }}</p>
        </div>
      </div>
      <button class="btn sun" :disabled="form.processing">
        {{ form.processing ? 'Sending…' : 'Send message' }} <span class="arrow">↗</span>
      </button>
      <p class="form-note">Prefer email? Reach us at {{ studio.email }}.</p>
    </form>

    <div v-else class="success">
      <p class="mono">Message sent</p>
      <h2 style="font-size:clamp(1.8rem,4vw,2.6rem);margin:.5rem 0">Talk soon, {{ form.name || 'friend' }}.</h2>
      <p style="color:rgba(251,243,228,.85)">We've received your message and will reply to {{ form.email || 'your inbox' }} within one working day.</p>
      <button class="btn" style="margin-top:1.4rem" @click="reset">Send another</button>
    </div>

    <aside class="contact-list">
      <div class="contact-card">
        <span class="mono">Email</span>
        <a :href="'mailto:'+studio.email"><b>{{ studio.email }}</b></a>
      </div>
      <div class="contact-card">
        <span class="mono">Phone / WhatsApp</span>
        <a :href="'tel:'+studio.phone.replace(/\s/g,'')"><b>{{ studio.phone }}</b></a>
      </div>
      <div class="contact-card">
        <span class="mono">Studio</span>
        <b>{{ studio.address }}</b>
        <p style="color:var(--mute);font-size:.85rem;margin-top:.3rem">{{ studio.hours }}</p>
      </div>
      <div class="contact-card">
        <span class="mono">Follow</span>
        <div class="socials" style="margin-top:.6rem">
          <a v-for="s in socials" :key="s.label" :href="s.href" target="_blank" rel="noopener"
            style="border-color:var(--ink);color:var(--ink)">{{ s.label }}</a>
        </div>
      </div>
    </aside>
  </div>
</section>

<section class="section wrap">
  <div class="inner-circle">
    <div>
      <p class="mono">Ready when you are</p>
      <h2 style="margin:.4rem 0">Have a date in <span class="script">mind</span>?</h2>
      <p>Skip the back-and-forth. Send the date and we'll tell you straight away if we're free.</p>
    </div>
    <div class="hero-actions" style="margin:0">
      <Link href="/booking" class="btn">Check availability <span class="arrow">↗</span></Link>
      <a :href="studio.map" target="_blank" rel="noopener" class="btn ghost">Get directions</a>
    </div>
  </div>
</section>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'

const page = usePage()
const studio = computed(() => page.props.settings?.studio ?? {})
const socials = computed(() => page.props.settings?.socials ?? [])
const sent = ref(false)

const form = useForm({
  name: '',
  email: '',
  phone: '',
  message: '',
})

function submit() {
  form.post('/contact', {
    preserveScroll: true,
    onSuccess: () => {
      sent.value = true
      form.reset()
    },
  })
}

function reset() {
  sent.value = false
  form.reset()
}
</script>
