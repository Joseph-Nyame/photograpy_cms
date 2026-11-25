<template>
<form @submit.prevent="save">
  <div class="admin-bar">
    <p class="admin-note">Manage the studio profile, booking rules, notifications and publishing defaults.</p>
    <div class="admin-actions">
      <button class="btn" type="submit" :disabled="form.processing">
        {{ form.processing ? 'Saving…' : 'Save settings' }}
      </button>
    </div>
  </div>

  <div v-if="saved" class="success admin-form-panel">Settings saved successfully.</div>

  <div class="settings-grid">
    <section class="admin-panel">
      <h3>Studio profile</h3>
      <div class="fields-2">
        <div class="field">
          <label>Studio name</label>
          <input v-model="form.name" required />
          <p v-if="form.errors.name" class="form-error">{{ form.errors.name }}</p>
        </div>
        <div class="field">
          <label>Contact email</label>
          <input v-model="form.email" type="email" required />
          <p v-if="form.errors.email" class="form-error">{{ form.errors.email }}</p>
        </div>
        <div class="field"><label>Phone</label><input v-model="form.phone" /></div>
        <div class="field"><label>WhatsApp number</label><input v-model="form.whatsapp" /></div>
      </div>
      <div class="field"><label>Address</label><input v-model="form.address" /></div>
      <div class="field"><label>Default hours</label><input v-model="form.hours" /></div>
      <div class="field"><label>Instagram URL</label><input v-model="form.instagram" /></div>
    </section>

    <section class="admin-panel">
      <h3>Booking rules</h3>
      <div class="fields-2">
        <div class="field">
          <label>Max bookings per day</label>
          <input v-model.number="form.maxBookings" type="number" min="1" />
          <p v-if="form.errors.maxBookings" class="form-error">{{ form.errors.maxBookings }}</p>
        </div>
        <div class="field">
          <label>Buffer between sessions</label>
          <select v-model.number="form.bufferMinutes">
            <option :value="30">30 minutes</option>
            <option :value="60">1 hour</option>
            <option :value="120">2 hours</option>
          </select>
        </div>
        <div class="field">
          <label>Opening time</label>
          <input v-model="form.open" type="time" />
          <p v-if="form.errors.open" class="form-error">{{ form.errors.open }}</p>
        </div>
        <div class="field">
          <label>Closing time</label>
          <input v-model="form.close" type="time" />
          <p v-if="form.errors.close" class="form-error">{{ form.errors.close }}</p>
        </div>
        <div class="field">
          <label>Gallery expiry (days)</label>
          <input v-model.number="form.expiry" type="number" min="1" />
          <p v-if="form.errors.expiry" class="form-error">{{ form.errors.expiry }}</p>
        </div>
        <div class="field">
          <label>Deposit (% of total cost)</label>
          <input v-model.number="form.depositRate" type="number" min="0" max="100" step="0.01" />
          <p v-if="form.errors.depositRate" class="form-error">{{ form.errors.depositRate }}</p>
        </div>
      </div>
      <label class="check"><input v-model="form.deposit" type="checkbox" /> Require a deposit to confirm a booking</label>
      <label class="check"><input v-model="form.autoConfirm" type="checkbox" /> Auto-confirm returning clients</label>
    </section>

    <section class="admin-panel">
      <h3>Notifications</h3>
      <div class="field">
        <label>Notification recipient</label>
        <input v-model="form.notifyEmail" type="email" />
        <p v-if="form.errors.notifyEmail" class="form-error">{{ form.errors.notifyEmail }}</p>
      </div>
      <label class="check"><input v-model="form.notifyBooking" type="checkbox" /> Email me when a booking request arrives</label>
      <label class="check"><input v-model="form.notifyContact" type="checkbox" /> Email me when a contact message arrives</label>
      <label class="check"><input v-model="form.notifyDownload" type="checkbox" /> Email me when a client downloads a gallery</label>
    </section>

    <section class="admin-panel">
      <h3>Publishing</h3>
      <div class="field"><label>SEO title</label><input v-model="form.seoTitle" /></div>
      <div class="field"><label>Meta description</label><textarea v-model="form.seoDescription" rows="3"></textarea></div>
      <div class="fields-2">
        <div class="field"><label>Canonical domain</label><input v-model="form.domain" /></div>
        <div class="field"><label>Analytics ID</label><input v-model="form.analytics" /></div>
      </div>
    </section>

    <section class="admin-panel">
      <h3>Integrations</h3>
      <div class="field">
        <label>Email provider</label>
        <select v-model="form.mailProvider">
          <option value="smtp">SMTP</option>
          <option value="ahasend">AhaSend</option>
          <option value="resend">Resend</option>
          <option value="postmark">Postmark</option>
        </select>
      </div>
      <div v-if="form.mailProvider === 'ahasend'" class="ahasend-fields">
        <p v-if="form.ahasendApiKeyHint" class="field-hint">
          Current API key: <code>{{ form.ahasendApiKeyHint }}</code> — leave blank to keep it.
        </p>
        <div class="field">
          <label>AhaSend API key</label>
          <input v-model="form.ahasendApiKey" type="password" autocomplete="off" placeholder="AGT… (leave blank to keep current key)" />
          <p v-if="form.errors.ahasendApiKey" class="form-error">{{ form.errors.ahasendApiKey }}</p>
        </div>
        <div class="fields-2">
          <div class="field">
            <label>AhaSend account ID</label>
            <input v-model="form.ahasendAccountId" />
            <p v-if="form.errors.ahasendAccountId" class="form-error">{{ form.errors.ahasendAccountId }}</p>
          </div>
          <div class="field">
            <label>From email</label>
            <input v-model="form.ahasendFrom" type="email" />
            <p v-if="form.errors.ahasendFrom" class="form-error">{{ form.errors.ahasendFrom }}</p>
          </div>
        </div>
        <label class="check">
          <input v-model="form.ahasendActive" type="checkbox" /> Send outgoing email through AhaSend
        </label>
      </div>
      <div class="field"><label>Map link</label><input v-model="form.map" /></div>
    </section>
  </div>

  <div class="panel-actions">
    <button class="btn" type="submit" :disabled="form.processing">
      {{ form.processing ? 'Saving…' : 'Save settings' }}
    </button>
  </div>
</form>
</template>
<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
  settings: { type: Object, default: () => ({}) },
})

const saved = ref(false)
const form = useForm({ ...props.settings })

function save() {
  form.put('/admin/settings', {
    preserveScroll: true,
    onSuccess: () => {
      saved.value = true
      setTimeout(() => { saved.value = false }, 2500)
    },
  })
}
</script>
