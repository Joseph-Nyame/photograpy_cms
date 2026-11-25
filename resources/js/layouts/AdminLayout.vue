<template>
<div class="admin">
  <aside class="side">
    <Link href="/" class="logo">Lumen<span class="dot"></span></Link>
    <p class="side-label">Studio administration</p>
    <nav class="side-nav">
      <div v-for="group in groups" :key="group.label" class="side-group">
        <p class="mono side-group-label">{{ group.label }}</p>
        <Link v-for="item in group.items" :key="item.href" :href="item.href">
          <span>{{ item.label }}</span>
          <span v-if="item.count" class="side-count">{{ item.count }}</span>
        </Link>
      </div>
    </nav>
    <div class="side-foot">
      <div class="side-user">
        <span class="avatar">{{ userInitials }}</span>
        <div><b>{{ userName }}</b><span class="mono">Studio owner</span></div>
      </div>
      <Link href="/">View site ↗</Link>
      <button type="button" class="side-logout" @click="logout">
        <span aria-hidden="true">↩</span> Sign out
      </button>
    </div>
  </aside>
  <div class="admin-main">
    <header class="admin-top">
      <div>
        <p class="mono admin-crumb">{{ current.group }}</p>
        <h1>{{ current.title }}</h1>
      </div>
      <div class="admin-top-actions">
        <div class="admin-search">
          <span class="mono">Search</span>
          <input
            v-model="query"
            type="search"
            placeholder="Clients, bookings, galleries…"
            @input="onSearch"
            @focus="open = results.length > 0"
            @blur="onBlur"
            @keydown.enter.prevent="openFirst"
            @keydown.esc="closeSearch"
          />
          <div v-if="open" class="search-results">
            <p v-if="loading" class="search-empty mono">Searching…</p>
            <template v-else-if="results.length">
              <div v-for="group in results" :key="group.label" class="search-group">
                <p class="mono side-group-label">{{ group.label }}</p>
                <Link
                  v-for="item in group.items"
                  :key="item.href + item.label"
                  :href="item.href"
                  class="search-item"
                  @mousedown.prevent
                  @click="closeSearch"
                >
                  <span>{{ item.label }}</span>
                  <small>{{ item.sub }}</small>
                </Link>
              </div>
            </template>
            <p v-else class="search-empty">No matches found.</p>
          </div>
        </div>
        <span class="admin-date">Friday, 13 November 2026</span>
        <button type="button" class="btn ghost mobile-logout" @click="logout">Sign out</button>
        <Link href="/admin/bookings" class="btn">New booking</Link>
      </div>
    </header>
    <main>
      <slot />
    </main>
  </div>
</div>
</template>
<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import axios from 'axios'
import Swal from 'sweetalert2'

const page = usePage()
const flash = computed(() => page.props.flash ?? {})
const navigation = computed(() => page.props.navigation ?? {})
const currentUser = computed(() => page.props.auth?.user ?? null)
const userName = computed(() => currentUser.value?.name ?? 'Studio owner')
const userInitials = computed(() => {
  const parts = userName.value.trim().split(/\s+/).filter(Boolean)
  const initials = parts.length > 1 ? parts[0][0] + parts[1][0] : (parts[0] ?? 'S').slice(0, 2)
  return initials.toUpperCase()
})
const titles = {
  '/admin': { title: 'Dashboard', group: 'Overview' },
  '/admin/bookings': { title: 'Bookings', group: 'Bookings' },
  '/admin/calendar': { title: 'Availability', group: 'Bookings' },
  '/admin/clients': { title: 'Clients', group: 'People' },
  '/admin/messages': { title: 'Messages', group: 'People' },
  '/admin/services': { title: 'Services & pricing', group: 'Studio' },
  '/admin/locations': { title: 'Booking locations', group: 'Studio' },
  '/admin/portfolio': { title: 'Portfolio', group: 'Studio' },
  '/admin/galleries': { title: 'Client galleries', group: 'Deliveries' },
  '/admin/campaigns': { title: 'Campaigns', group: 'Marketing' },
  '/admin/emails': { title: 'Emails', group: 'Marketing' },
  '/admin/settings': { title: 'Settings', group: 'System' },
}
const current = computed(() => titles[page.url.split('?')[0]] || { title: 'Admin', group: 'Studio admin' })
const groups = computed(() => [
  { label: 'Overview', items: [{ label: 'Dashboard', href: '/admin' }] },
  { label: 'Bookings', items: [
    { label: 'Bookings', href: '/admin/bookings', count: navigation.value.pendingBookings },
    { label: 'Availability', href: '/admin/calendar' },
  ] },
  { label: 'People', items: [
    { label: 'Clients', href: '/admin/clients' },
    { label: 'Messages', href: '/admin/messages', count: navigation.value.unreadMessages },
  ] },
  { label: 'Studio', items: [
    { label: 'Services', href: '/admin/services' },
    { label: 'Locations', href: '/admin/locations' },
    { label: 'Portfolio', href: '/admin/portfolio' },
  ] },
  { label: 'Deliveries', items: [{ label: 'Client galleries', href: '/admin/galleries', count: navigation.value.activeGalleries }] },
  { label: 'Marketing', items: [
    { label: 'Campaigns', href: '/admin/campaigns' },
    { label: 'Emails', href: '/admin/emails' },
  ] },
  { label: 'System', items: [{ label: 'Settings', href: '/admin/settings' }] },
])

watch(
  () => flash.value.message,
  (message) => {
    if (!message) return
    Swal.fire({
      icon: flash.value.success ? 'success' : 'error',
      title: flash.value.success ? 'Done' : 'Check that',
      text: message,
      confirmButtonText: 'OK',
    }).then(() => {
      page.props.flash.message = null
    })
  },
  { immediate: true },
)

let notificationChannelName = null

onMounted(() => {
  const userId = page.props.auth?.user?.id
  if (!window.Echo || !userId) return

  notificationChannelName = `App.Models.User.${userId}`
  window.Echo.private(notificationChannelName)
    .listen('.admin.notification', (notification) => {
      router.reload({ only: ['navigation'] })
      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: notification.tone === 'warning' ? 'warning' : 'info',
        title: notification.title,
        text: notification.body,
        timer: 5000,
        timerProgressBar: true,
        showConfirmButton: false,
        didOpen: (toast) => {
          toast.addEventListener('click', () => {
            if (notification.url) router.visit(notification.url)
            Swal.close()
          })
        },
      })
    })
})

onUnmounted(() => {
  if (!notificationChannelName) return
  window.Echo?.leave(notificationChannelName)
  notificationChannelName = null
})

const query = ref('')
const results = ref([])
const open = ref(false)
const loading = ref(false)
let timer
let searchId = 0

function onSearch() {
  clearTimeout(timer)
  const term = query.value.trim()
  if (term.length < 2) {
    searchId++
    results.value = []
    open.value = false
    return
  }
  timer = setTimeout(async () => {
    const id = ++searchId
    loading.value = true
    open.value = true
    try {
      const { data } = await axios.get('/admin/search', { params: { q: term } })
      if (id === searchId) results.value = data.groups ?? []
    } catch {
      if (id === searchId) results.value = []
    } finally {
      if (id === searchId) loading.value = false
    }
  }, 250)
}

function openFirst() {
  const first = results.value.flatMap(group => group.items)[0]
  if (!first) return
  router.visit(first.href)
  closeSearch()
}

function onBlur() {
  setTimeout(() => { open.value = false }, 150)
}

function closeSearch() {
  clearTimeout(timer)
  searchId++
  open.value = false
  query.value = ''
  results.value = []
}

function logout() {
  Swal.fire({
    title: 'Sign out?',
    text: 'You will need to sign in again to manage the studio.',
    icon: 'question',
    iconColor: '#ffb810',
    showCancelButton: true,
    reverseButtons: true,
    focusCancel: true,
    confirmButtonText: 'Sign out',
    confirmButtonColor: '#ffb810',
    cancelButtonText: 'Stay signed in',
    cancelButtonColor: '#374151',
  }).then(({ isConfirmed }) => {
    if (isConfirmed) router.post('/logout')
  })
}
</script>
