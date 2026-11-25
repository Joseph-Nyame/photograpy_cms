<template>
<Head title="Sign in" />

<section class="section wrap auth-wrap">
  <div class="form-card auth-card">
    <p class="eyebrow"><span class="blip"></span> Studio access</p>
    <h2>Welcome <span class="script">back</span>.</h2>
    <p>Sign in to manage bookings, clients, galleries and marketing.</p>

    <form @submit.prevent="submit">
      <div class="field">
        <label for="email">Email</label>
        <input id="email" v-model="form.email" type="email" autocomplete="email" required autofocus placeholder="you@lumen.studio">
        <p v-if="form.errors.email" class="form-error">{{ form.errors.email }}</p>
      </div>
      <div class="field">
        <label for="password">Password</label>
        <input id="password" v-model="form.password" type="password" autocomplete="current-password" required placeholder="••••••••">
        <p v-if="form.errors.password" class="form-error">{{ form.errors.password }}</p>
      </div>
      <label class="check">
        <input v-model="form.remember" type="checkbox">
        <span>Keep me signed in</span>
      </label>
      <p v-if="status" class="form-note">{{ status }}</p>
      <button class="btn sun" :disabled="form.processing">
        {{ form.processing ? 'Signing in…' : 'Sign in' }} <span class="arrow">↗</span>
      </button>
    </form>

    <p class="form-note">Admin access only. Contact the studio owner if you need an account.</p>
  </div>
</section>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'

defineProps({ status: { type: String, default: null } })

const form = useForm({
  email: '',
  password: '',
  remember: false,
})

function submit() {
  form.post('/login', {
    onFinish: () => form.reset('password'),
  })
}
</script>
