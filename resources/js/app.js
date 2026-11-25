import './bootstrap'
import './style.css'
import { createApp, h } from 'vue'
import { createInertiaApp, Head, Link } from '@inertiajs/vue3'
import Root from './App.vue'
import PublicLayout from './layouts/PublicLayout.vue'
import AdminLayout from './layouts/AdminLayout.vue'

const pages = import.meta.glob('./pages/**/*.vue')

createInertiaApp({
  title: title => title ? `${title} — Lumen Studio` : 'Lumen Studio — Photography in Accra',
  resolve: name => pages[`./pages/${name}.vue`](),
  layout: (name, page) => {
    if (name === 'Auth/Login') return null
    return page.url.startsWith('/admin') ? AdminLayout : PublicLayout
  },
  setup({ el, App, props, plugin }) {
    createApp({
      render: () => h(Root, null, { default: () => h(App, props) }),
    })
      .use(plugin)
      .component('Link', Link)
      .component('Head', Head)
      .mount(el)
  },
  progress: {
    color: '#ffb810',
  },
})
