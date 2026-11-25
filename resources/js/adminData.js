export const adminBookings = [
  { id: 'BK-1042', client: 'Ama Boateng', email: 'ama@example.com', phone: '+233 24 111 2200', service: 'Wedding day', date: '12 Dec 2026', time: '08:00 – 18:00', location: 'Aburi Gardens', status: 'Confirmed', note: 'Second shooter and album confirmed.' },
  { id: 'BK-1043', client: 'Kojo Mensah', email: 'kojo@example.com', phone: '+233 20 442 1188', service: 'Football coverage', date: '19 Dec 2026', time: '15:00 – 19:00', location: 'Baba Yara Stadium', status: 'Pending', note: 'Waiting on team access approval.' },
  { id: 'BK-1044', client: 'Esi Owusu', email: 'esi@example.com', phone: '+233 55 802 4410', service: 'Portrait session', date: '21 Dec 2026', time: '10:00 – 11:00', location: 'East Legon studio', status: 'Pending', note: 'Preferred backdrop: warm paper.' },
  { id: 'BK-1045', client: 'Nii Adjei', email: 'nii@example.com', phone: '+233 27 330 9055', service: 'Event coverage', date: '03 Jan 2027', time: '17:00 – 21:00', location: 'Labadi Beach Hotel', status: 'Confirmed', note: 'Launch night, two photographers.' },
  { id: 'BK-1046', client: 'Akosua Frimpong', email: 'akosua@example.com', phone: '+233 24 918 7744', service: 'Birthday party', date: '09 Jan 2027', time: '13:00 – 16:00', location: 'Tema Community 7', status: 'Declined', note: 'Date already blocked for travel.' },
  { id: 'BK-1047', client: 'Kwesi Appiah', email: 'kwesi@example.com', phone: '+233 54 221 6600', service: 'Portrait session', date: '17 Jan 2027', time: '09:00 – 10:00', location: 'Jamestown, Accra', status: 'Pending', note: 'Golden-hour outdoor portraits.' },
]

export const adminClients = [
  { name: 'Ama Boateng', email: 'ama@example.com', phone: '+233 24 111 2200', source: 'Booking', subscribed: true, bookings: 3, last: '2m ago' },
  { name: 'Kojo Mensah', email: 'kojo@example.com', phone: '+233 20 442 1188', source: 'Booking', subscribed: true, bookings: 2, last: '1h ago' },
  { name: 'Esi Owusu', email: 'esi@example.com', phone: '+233 55 802 4410', source: 'Contact', subscribed: false, bookings: 1, last: '4h ago' },
  { name: 'Nii Adjei', email: 'nii@example.com', phone: '+233 27 330 9055', source: 'Booking', subscribed: true, bookings: 4, last: '1d ago' },
  { name: 'Akosua Frimpong', email: 'akosua@example.com', phone: '+233 24 918 7744', source: 'Manual', subscribed: false, bookings: 1, last: '3d ago' },
  { name: 'Kwesi Appiah', email: 'kwesi@example.com', phone: '+233 54 221 6600', source: 'Contact', subscribed: true, bookings: 0, last: '5d ago' },
]

export const adminMessages = [
  {
    id: 1, name: 'Esi Owusu', email: 'esi@example.com', subject: 'Portrait session in December',
    body: 'Hi Lumen, I would love a one-hour portrait session before the holidays. I am flexible between 18 and 22 December, preferably outdoors in East Legon. Could you share availability and the final price?',
    time: '09:42', read: false,
  },
  {
    id: 2, name: 'Kwesi Appiah', email: 'kwesi@example.com', subject: 'Football team portraits',
    body: 'Good afternoon. We need team portraits and touchline coverage for a match on 19 December. There are 22 players and the club would also like a few sponsor shots.',
    time: '08:15', read: false,
  },
  {
    id: 3, name: 'Adjoa Serwaa', email: 'adjoa@example.com', subject: 'Wedding album question',
    body: 'We received the gallery and absolutely love it. Can we add six more spreads to the printed album, and what would the turnaround be?',
    time: 'Yesterday', read: true,
  },
  {
    id: 4, name: 'Yaw Darko', email: 'yaw@example.com', subject: 'Corporate event enquiry',
    body: 'We are hosting a product launch at Labadi in January and would like coverage for four hours, including headshots for the team.',
    time: 'Tue', read: true,
  },
]

export const adminServices = [
  { name: 'Portrait session', price: 800, duration: '1 hour', active: true, sort: 1, desc: 'One hour, one location, 20 edited images.' },
  { name: 'Event coverage', price: 2500, duration: '4 hours', active: true, sort: 2, desc: 'Two photographers and a same-week gallery.' },
  { name: 'Wedding day', price: 6000, duration: 'Full day', active: true, sort: 3, desc: 'Full-day coverage, second shooter and album.' },
  { name: 'Football matchday', price: 1800, duration: '3 hours', active: false, sort: 4, desc: 'Match action, touchline reactions and team shots.' },
]

export const adminGalleries = [
  { title: 'Ama & Kojo', client: 'Ama Boateng', token: 'a1f9c2', status: 'Active', images: 428, expires: '12 Jan 2027', download: true },
  { title: 'Hearts vs Kotoko', client: 'Kojo Mensah', token: 'b7c2e4', status: 'Expiring', images: 196, expires: '18 Dec 2026', download: false },
  { title: 'Esi · Portraits', client: 'Esi Owusu', token: 'c4d0a8', status: 'Active', images: 24, expires: '04 Feb 2027', download: true },
  { title: 'Labadi launch', client: 'Nii Adjei', token: 'd9e1f3', status: 'Expiring', images: 312, expires: '21 Dec 2026', download: true },
  { title: 'Akosua · 30th', client: 'Akosua Frimpong', token: 'e2b8c1', status: 'Expired', images: 158, expires: '28 Nov 2026', download: false },
]

export const adminEmails = [
  { to: 'Ama Boateng', subject: 'Your wedding gallery is ready', sent: '12 Nov · 16:08', status: 'Sent' },
  { to: 'Kojo Mensah', subject: 'Matchday gallery preview', sent: '11 Nov · 09:24', status: 'Sent' },
  { to: 'Esi Owusu', subject: 'Portrait availability', sent: '10 Nov · 14:52', status: 'Sent' },
  { to: 'Nii Adjei', subject: 'Labadi launch coverage', sent: '08 Nov · 11:30', status: 'Sent' },
]

export const adminCampaigns = [
  { subject: 'December sessions are open', status: 'Sent', recipients: 238, sent: '01 Nov 2026' },
  { subject: 'New football gallery', status: 'Sent', recipients: 184, sent: '24 Oct 2026' },
  { subject: 'End-of-year studio offer', status: 'Draft', recipients: 0, sent: '—' },
]

export const adminBlockedDates = [
  { date: '08 Dec 2026', reason: 'Studio maintenance' },
  { date: '24 – 26 Dec 2026', reason: 'Christmas break' },
  { date: '01 Jan 2027', reason: 'New Year' },
]

export const adminActivity = [
  { text: 'Ama & Kojo gallery opened', time: '2m ago' },
  { text: 'New booking · Esi Owusu', time: '1h ago' },
  { text: 'Campaign sent to 238 clients', time: '3h ago' },
  { text: 'Gallery #b7c2 downloaded', time: '5h ago' },
  { text: 'Contact message · Kwesi Appiah', time: '6h ago' },
]

export const adminPortfolio = [
  { title: 'Ama & Kojo · vows', category: 'Weddings', tone: 'sun', featured: true, alt: 'Bride and groom under palms' },
  { title: 'Hearts vs Kotoko', category: 'Football', tone: 'green', featured: true, alt: 'Matchday action at the touchline' },
  { title: 'Golden hour portrait', category: 'Portraits', tone: 'pink', featured: false, alt: 'Portrait in warm evening light' },
  { title: 'Labadi launch', category: 'Events', tone: 'blue', featured: false, alt: 'Guests at a product launch' },
  { title: 'Thirty & thriving', category: 'Birthdays', tone: 'red', featured: false, alt: 'Birthday celebration with sparklers' },
  { title: 'Courtside reactions', category: 'Football', tone: 'green', featured: false, alt: 'Fans reacting from the stands' },
]

export const adminStats = [
  { label: 'Pending bookings', value: 4, tone: 'sun', sub: '2 need a reply today' },
  { label: 'Confirmed this month', value: 9, tone: 'green', sub: '+3 vs last month' },
  { label: 'Unread messages', value: 2, tone: 'pink', sub: 'From the contact form' },
  { label: 'Active galleries', value: 12, tone: 'blue', sub: '2 expire this week' },
]

export const statusTone = s => ({ Confirmed: 'ok', Active: 'ok', Sent: 'ok', Pending: 'warn', Limited: 'warn', Expiring: 'warn', Draft: 'warn', Declined: 'off', Cancelled: 'off', Expired: 'off' }[s] || '')
