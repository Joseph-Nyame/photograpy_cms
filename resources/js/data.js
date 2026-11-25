export const categories = [
  { name: 'Weddings', slug: 'weddings', tone: 'sun', blurb: 'Full-day storytelling, from the prep room to the last dance.', count: '48 sets' },
  { name: 'Events', slug: 'events', tone: 'pink', blurb: 'Conferences, launches and nights that need to be remembered.', count: '32 sets' },
  { name: 'Football', slug: 'football', tone: 'green', blurb: 'Matchday action, touchline reactions and team portraits.', count: '27 sets' },
  { name: 'Birthdays', slug: 'birthdays', tone: 'red', blurb: 'Cake, chaos and the people who made the day.', count: '19 sets' },
  { name: 'Portraits', slug: 'portraits', tone: 'blue', blurb: 'One hour, one location, a version of you worth keeping.', count: '54 sets' },
]

export const services = [
  {
    name: 'Portrait session', price: 800, tag: 'Most booked', featured: false,
    desc: 'One hour, one location, 20 edited photos delivered in a private gallery.',
    includes: ['60-minute shoot', 'One location in Accra', '20 edited images', 'Private online gallery'],
  },
  {
    name: 'Event coverage', price: 2500, tag: 'Popular', featured: true,
    desc: 'Up to four hours of coverage with a same-week online gallery.',
    includes: ['Up to 4 hours', 'Two photographers', '80+ edited images', 'Private online gallery'],
  },
  {
    name: 'Wedding day', price: 6000, tag: 'Full day', featured: false,
    desc: 'Full-day coverage, a second shooter and a printed album.',
    includes: ['Full-day coverage', 'Two photographers', '400+ edited images', 'Printed 30-page album'],
  },
]

export const openDates = [
  { day: '27', month: 'Dec 2026', note: 'Wedding · Accra', status: 'Open', tone: 'ok' },
  { day: '03', month: 'Jan 2027', note: 'Portraits · East Legon', status: 'Open', tone: 'ok' },
  { day: '10', month: 'Jan 2027', note: 'Event · Labadi', status: '2 left', tone: 'warn' },
  { day: '17', month: 'Jan 2027', note: 'Wedding · Aburi', status: 'Waitlist', tone: 'off' },
]

export const stats = [
  { value: '6', label: 'Years shooting' },
  { value: '180+', label: 'Stories told' },
  { value: '24h', label: 'Gallery turnaround' },
  { value: '5', label: 'Specialities' },
]

export const studio = {
  name: 'Lumen Studio',
  email: 'hello@lumen.studio',
  phone: '+233 20 000 0000',
  whatsapp: 'https://wa.me/233200000000',
  instagram: 'https://instagram.com/',
  location: 'Accra, Ghana',
  address: '14 Boundary Road, East Legon, Accra',
  hours: 'Mon–Fri · 9:00–18:00 · By appointment',
  map: 'https://www.google.com/maps/search/?api=1&query=East+Legon+Accra+Ghana',
}

export const socials = [
  { label: 'Instagram', href: studio.instagram },
  { label: 'WhatsApp', href: studio.whatsapp },
  { label: 'Email', href: `mailto:${studio.email}` },
]
