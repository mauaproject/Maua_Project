import { useState } from 'react'
import { jobStatuses, registrationStatuses } from '../config/constants'
import { Badge } from './shared'
import './admin-dashboard.css'

const DAY_MS = 86_400_000
const dayInJakarta = new Intl.DateTimeFormat('sv-SE', { dateStyle: 'short', timeZone: 'Asia/Jakarta' })
const headingDate = new Intl.DateTimeFormat('id-ID', { dateStyle: 'full', timeZone: 'Asia/Jakarta' })
const shortDate = new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'short', timeZone: 'UTC' })
const fullDate = new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium', timeZone: 'UTC' })
const number = new Intl.NumberFormat('id-ID')
const compactNumber = new Intl.NumberFormat('id-ID', { notation: 'compact' })
const periods = [
  { label: '7 hari', buckets: 7, daysPerBucket: 1 },
  { label: '4 minggu', buckets: 4, daysPerBucket: 7 },
]
const bookingColors = ['#bd8527', '#568260', '#ba6258', '#a29a8d', '#846b92', '#4b6d83', '#756957']

const dateKey = (date) => date.toISOString().slice(0, 10)
const bookingDay = (booking) => String(booking.createdAt ?? '').slice(0, 10)

function getBookingTrend(registrations, today, { buckets, daysPerBucket }) {
  const counts = new Map()
  for (const booking of registrations) {
    const day = bookingDay(booking)
    counts.set(day, (counts.get(day) || 0) + 1)
  }

  const lastDay = Date.parse(today + 'T00:00:00Z')
  const firstDay = lastDay - (buckets * daysPerBucket - 1) * DAY_MS

  return Array.from({ length: buckets }, (_, index) => {
    const start = new Date(firstDay + index * daysPerBucket * DAY_MS)
    const end = new Date(start.getTime() + (daysPerBucket - 1) * DAY_MS)
    let count = 0
    for (let day = start.getTime(); day <= end.getTime(); day += DAY_MS) {
      count += counts.get(dateKey(new Date(day))) || 0
    }
    return { start, end, count }
  })
}

function summarizeStatuses(items, labels) {
  const counts = new Map(labels.map((label) => [label, 0]))
  for (const item of items) {
    const label = labels.includes(item.status) ? item.status : 'Lainnya'
    counts.set(label, (counts.get(label) || 0) + 1)
  }
  return Array.from(counts, ([label, count]) => ({ label, count }))
}

function BookingTrend({ registrations, today }) {
  const [period, setPeriod] = useState(periods[1])
  const [selectedDay, setSelectedDay] = useState(null)
  const series = getBookingTrend(registrations, today, period)
  const total = series.reduce((sum, bucket) => sum + bucket.count, 0)
  const peak = Math.max(...series.map((bucket) => bucket.count))
  const step = Math.max(1, Math.ceil(peak / 4))
  const max = step * 4
  const selected = series.find((bucket) => dateKey(bucket.start) === selectedDay)
  const range = shortDate.formatRange(series[0].start, series.at(-1).end)

  return (
    <section className="overview-panel" aria-labelledby="overview-trend-title">
      <div className="overview-panel-head">
        <div>
          <h2 id="overview-trend-title">Tren booking</h2>
          <p className="overview-muted">Pendaftaran {period.daysPerBucket === 1 ? 'per hari' : 'per minggu'} · WIB</p>
        </div>
        <div className="overview-periods" role="group" aria-label="Periode grafik booking">
          {periods.map((option) => (
            <button
              key={option.label}
              type="button"
              aria-pressed={period === option}
              onClick={() => {
                setPeriod(option)
                setSelectedDay(null)
              }}
            >
              {option.label}
            </button>
          ))}
        </div>
      </div>

      <div className="overview-trend-summary">
        <span><strong>{number.format(total)}</strong> booking masuk</span>
        <output>
          {selected
            ? <>{shortDate.formatRange(selected.start, selected.end)} · {number.format(selected.count)} booking</>
            : range}
        </output>
      </div>

      {total > 0 ? (
        <div className="overview-plot">
          <div className="overview-grid-lines" aria-hidden="true">
            {[4, 3, 2, 1, 0].map((level) => (
              <div key={level}><span>{compactNumber.format(level * step)}</span></div>
            ))}
          </div>
          <div className="overview-bars" style={{ '--columns': series.length }}>
            {series.map(({ start, end, count }) => {
              const key = dateKey(start)
              const label = shortDate.formatRange(start, end) + ' · ' + number.format(count) + ' booking'
              return (
                <button
                  className="overview-bar-button"
                  key={key}
                  type="button"
                  title={label}
                  aria-label={label}
                  aria-pressed={selectedDay === key}
                  onClick={() => setSelectedDay(selectedDay === key ? null : key)}
                >
                  <span className="overview-bar-track" aria-hidden="true">
                    <span className="overview-bar" style={{ height: (count / max * 100) + '%' }} />
                  </span>
                  <span className="overview-bar-date" aria-hidden="true">{shortDate.format(start)}</span>
                </button>
              )
            })}
          </div>
        </div>
      ) : (
        <p className="overview-trend-empty">Belum ada booking pada periode ini.</p>
      )}
    </section>
  )
}

function BookingStatus({ registrations }) {
  const [activeOnly, setActiveOnly] = useState(false)
  const bookings = activeOnly ? registrations.filter((item) => !item.isCompleted) : registrations
  const total = bookings.length
  const statuses = summarizeStatuses(bookings, registrationStatuses)
    .map((status, index) => ({ ...status, color: bookingColors[index] }))
  let offset = 0
  const segments = statuses.filter(({ count }) => count > 0).map(({ count, color }) => {
    const start = offset
    offset += count
    return [color, (start / total * 100) + '%', (offset / total * 100) + '%'].join(' ')
  })

  return (
    <section className="overview-panel" aria-labelledby="overview-status-title">
      <div className="overview-panel-head">
        <div>
          <h2 id="overview-status-title">Status booking</h2>
          <p className="overview-muted">{activeOnly ? 'Jadwal belum selesai' : 'Seluruh riwayat pendaftaran'}</p>
        </div>
        <div className="overview-periods" role="group" aria-label="Cakupan status booking">
          <button type="button" aria-pressed={activeOnly} onClick={() => setActiveOnly(true)}>Aktif</button>
          <button type="button" aria-pressed={!activeOnly} onClick={() => setActiveOnly(false)}>Semua</button>
        </div>
      </div>
      <div className="overview-status-body">
        <div
          className="overview-ring"
          style={total ? { background: 'conic-gradient(' + segments.join(', ') + ')' } : undefined}
          role="img"
          aria-label={number.format(total) + ' total booking'}
        >
          <div><strong>{number.format(total)}</strong><span>booking</span></div>
        </div>
        <dl className="overview-status-list">
          {statuses.map(({ label, count, color }) => (
            <div key={label}>
              <dt><span style={{ backgroundColor: color }} aria-hidden="true" />{label}</dt>
              <dd>{number.format(count)}</dd>
            </div>
          ))}
        </dl>
      </div>
    </section>
  )
}

function RecentBookings({ registrations }) {
  const recent = registrations
    .map((booking) => ({ booking, date: new Date(bookingDay(booking) + 'T00:00:00Z') }))
    .filter(({ date }) => Number.isFinite(date.getTime()))
    .sort((a, b) => b.booking.createdAt.localeCompare(a.booking.createdAt))
    .slice(0, 4)

  return (
    <section className="overview-panel" aria-labelledby="overview-recent-title">
      <div className="overview-panel-head">
        <h2 id="overview-recent-title">Booking terbaru</h2>
      </div>
      {recent.length > 0 ? (
        <ul className="overview-recent-list">
          {recent.map(({ booking, date }) => (
            <li key={booking.id}>
              <div className="overview-recent-main">
                <strong>{booking.tripName || 'Booking #' + booking.id}</strong>
                {booking.name && <span>{booking.name}</span>}
              </div>
              <div className="overview-recent-meta">
                <time dateTime={dateKey(date)}>{fullDate.format(date)}</time>
                <Badge status={booking.status || 'Lainnya'} />
              </div>
            </li>
          ))}
        </ul>
      ) : (
        <p className="overview-muted overview-empty">Belum ada booking yang bisa ditampilkan.</p>
      )}
    </section>
  )
}

function TeamJobs({ jobs }) {
  const statuses = summarizeStatuses(jobs, jobStatuses)

  return (
    <section className="overview-panel" aria-labelledby="overview-team-title">
      <div className="overview-panel-head">
        <div>
          <h2 id="overview-team-title">Pekerjaan tim</h2>
          <p className="overview-muted">{number.format(jobs.length)} job tercatat</p>
        </div>
      </div>
      <dl className="overview-jobs">
        {statuses.map(({ label, count }) => (
          <div key={label}>
            <dt>{label}</dt>
            <dd>{number.format(count)}</dd>
            <dd className="overview-job-track" aria-hidden="true">
              <span style={{ width: (count / Math.max(1, jobs.length) * 100) + '%' }} />
            </dd>
          </div>
        ))}
      </dl>
    </section>
  )
}

export default function AdminDashboardOverview({ trips, registrations, jobs, pendingBookings, pendingParticipants, navigate }) {
  const [now] = useState(() => new Date())
  const today = dayInJakarta.format(now)
  const availableTrips = trips.filter((trip) => trip.status === 'Tersedia' && !trip.isArchived).length
  const availableJobs = jobs.filter((job) => job.status === 'Tersedia').length
  const assignedJobs = jobs.filter((job) => job.workerId || job.worker).length
  const metrics = [
    { label: 'Paket trip', count: trips.length, detail: number.format(availableTrips) + ' tersedia', icon: 'geo', tone: 'gold' },
    { label: 'Total booking', count: registrations.length, detail: 'Termasuk riwayat booking', icon: 'ticket', tone: 'blue' },
    { label: 'Menunggu approval', count: pendingBookings, detail: number.format(pendingParticipants) + ' peserta', icon: 'calendar', tone: 'gold' },
    { label: 'Job tersedia', count: availableJobs, detail: number.format(assignedJobs) + ' sudah ditugaskan', icon: 'people', tone: 'green' },
  ]

  return (
    <section className="admin-dashboard admin-overview" aria-labelledby="admin-overview-title">
      <header className="overview-header">
        <div>
          <h1 id="admin-overview-title">Dashboard Admin</h1>
          <time dateTime={today}>{headingDate.format(now)}</time>
        </div>
        <div className="dashboard-actions">
          <button className="outline-btn" type="button" onClick={() => navigate('/admin/open-trip')}>Kelola paket trip</button>
          <button className="primary-btn" type="button" onClick={() => navigate('/admin/jadwal')}>Lihat jadwal <span aria-hidden="true">→</span></button>
        </div>
      </header>

      <dl className="stat-grid dashboard-stats overview-metrics" aria-label="Ringkasan operasional">
        {metrics.map(({ label, count, detail, icon, tone }) => (
          <div className="overview-metric" key={label}>
            <dt>
              {label}
              <span className="overview-metric-icon" data-tone={tone} aria-hidden="true">
                <span className={'asset-icon icon-' + icon} />
              </span>
            </dt>
            <dd><strong>{number.format(count)}</strong><span>{detail}</span></dd>
          </div>
        ))}
      </dl>

      <div className="overview-panels">
        <BookingTrend registrations={registrations} today={today} />
        <BookingStatus registrations={registrations} />
      </div>

      <div className="overview-panels">
        <RecentBookings registrations={registrations} />
        <TeamJobs jobs={jobs} />
      </div>
    </section>
  )
}
