<template>
<div>
  <div class="stats">
    <div v-for="s in stats" :key="s.label" class="stat" :class="s.tone">
      <span>{{ s.label }}</span>
      <b>{{ s.value }}</b>
      <small>{{ s.sub }}</small>
    </div>
  </div>

  <div class="admin-grid">
    <div class="admin-col">
      <section class="admin-panel">
        <div class="panel-head">
          <div>
            <h3>Upcoming bookings</h3>
            <p>Confirmed and pending sessions that need preparation.</p>
          </div>
          <Link href="/admin/bookings" class="link-arrow">View all bookings</Link>
        </div>
        <div class="table-wrap plain">
          <table>
            <thead>
              <tr><th>Client</th><th>Service</th><th>Date</th><th>Status</th></tr>
            </thead>
            <tbody>
              <tr v-for="b in bookings" :key="b.booking_id">
                <td><b>{{ b.client }}</b><br><span class="mono">{{ b.id }}</span></td>
                <td>{{ b.service }}</td>
                <td>{{ b.date }}<br><span class="mono">{{ b.time }}</span></td>
                <td><span class="pill" :class="statusTone(b.status)">{{ b.status }}</span></td>
              </tr>
              <tr v-if="!bookings.length"><td colspan="4" class="empty">No upcoming bookings.</td></tr>
            </tbody>
          </table>
        </div>
      </section>

      <section class="admin-panel">
        <div class="panel-head">
          <div>
            <h3>Recent client galleries</h3>
            <p>Delivery links and expiry windows.</p>
          </div>
          <Link href="/admin/galleries" class="link-arrow">Manage galleries</Link>
        </div>
        <div class="table-wrap plain">
          <table>
            <thead>
              <tr><th>Gallery</th><th>Client</th><th>Images</th><th>Expires</th><th>Status</th></tr>
            </thead>
            <tbody>
              <tr v-for="g in galleries" :key="g.token">
                <td><b>{{ g.title }}</b><br><span class="mono">#{{ g.token }}</span></td>
                <td>{{ g.client }}</td>
                <td>{{ g.images }}</td>
                <td>{{ g.expires }}</td>
                <td><span class="pill" :class="statusTone(g.status)">{{ g.status }}</span></td>
              </tr>
              <tr v-if="!galleries.length"><td colspan="5" class="empty">No client galleries yet.</td></tr>
            </tbody>
          </table>
        </div>
      </section>
    </div>

    <div class="admin-col">
      <section class="admin-panel attention">
        <div class="panel-head">
          <h3>Needs attention</h3>
          <span class="mono">Today</span>
        </div>
        <ul class="admin-list">
          <li v-for="a in attention" :key="a.href"><Link :href="a.href">{{ a.label }}</Link><span class="count">{{ a.count }}</span></li>
        </ul>
      </section>

      <section class="admin-panel">
        <div class="panel-head">
          <div>
            <h3>Availability</h3>
            <p>{{ month.label }}</p>
          </div>
          <Link href="/admin/calendar" class="link-arrow">Open calendar</Link>
        </div>
        <div class="mini-cal">
          <span v-for="d in dows" :key="d" class="dow">{{ d }}</span>
          <span v-for="c in month.cells" :key="c.key" class="mini-day" :class="[c.status, { blank: c.blank }]">
            <template v-if="!c.blank">{{ c.day }}<small v-if="c.status === 'booked'">Booked</small><small v-else-if="c.status === 'blocked'">Closed</small><small v-else-if="c.status === 'limited'">Limited</small></template>
          </span>
        </div>
        <div class="legend">
          <span><i class="open"></i>Open</span>
          <span><i class="booked"></i>Booked</span>
          <span><i class="limited"></i>Limited</span>
          <span><i class="blocked"></i>Closed</span>
        </div>
      </section>

      <section class="admin-panel">
        <div class="panel-head">
          <h3>Quick actions</h3>
        </div>
        <div class="qa">
          <Link href="/admin/bookings"><span>Review booking requests</span><span>→</span></Link>
          <Link href="/admin/calendar"><span>Update availability</span><span>→</span></Link>
          <Link href="/admin/galleries"><span>Create a client gallery</span><span>→</span></Link>
          <Link href="/admin/campaigns"><span>Draft a campaign</span><span>→</span></Link>
        </div>
      </section>

      <section class="admin-panel">
        <div class="panel-head">
          <h3>Recent activity</h3>
        </div>
        <ul class="activity">
          <li v-for="(a, i) in activity" :key="i"><b>{{ a.text }}</b><span>{{ a.time }}</span></li>
          <li v-if="!activity.length" class="empty">Nothing yet.</li>
        </ul>
      </section>
    </div>
  </div>
</div>
</template>
<script setup>
import { statusTone } from '../adminData'

defineProps({
  stats: { type: Array, default: () => [] },
  bookings: { type: Array, default: () => [] },
  galleries: { type: Array, default: () => [] },
  attention: { type: Array, default: () => [] },
  activity: { type: Array, default: () => [] },
  month: { type: Object, default: () => ({ label: '', cells: [] }) },
})

const dows = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa']
</script>
