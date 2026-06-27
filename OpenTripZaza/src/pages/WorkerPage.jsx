import { useState } from 'react'
import { jobStatuses } from '../config/constants'
import { formatDate } from '../utils/formatters'
import { getJobResultLink } from '../utils/jobResults'
import { localizedText } from '../utils/localization'
import { getRegistrationDate } from '../utils/schedules'
import { buildWhatsAppUrl } from '../utils/whatsapp'
import { AppModal, Badge, InfoBlock, Metric, NotFound, Sidebar } from './shared'

const getJobScope = (job) => job.registrationId ? `registration-${job.registrationId}` : `trip-${job.tripId}`
const completionStatusOptions = jobStatuses.filter((status) => status !== 'Tersedia' && status !== 'Selesai')
const mediaAddonIds = ['drone', 'camera360', 'documentation']
const workerText = (value) => localizedText(value, 'id') || '-'
const getTimeRangeLabel = (registration) => {
  const startTime = registration?.startTime || ''
  const endTime = registration?.endTime || ''
  if (startTime && endTime) return `${startTime} - ${endTime} WIB`
  if (startTime) return `${startTime} WIB`
  return ''
}
const getJobScheduleLabel = (job, registration, trip) => {
  const dateText = formatDate(job.requestedDate || getRegistrationDate(registration) || trip?.date)
  const details = [registration?.sessionName, getTimeRangeLabel(registration)].filter(Boolean)
  return details.length ? `${dateText} · ${details.join(' · ')}` : dateText
}

const getFirstValue = (values) => values.find((value) => String(value || '').trim())
const getCustomerName = (job, registration) => getFirstValue([
  registration?.name,
  job.customerName,
  job.customer?.name,
]) || 'Customer'
const getCustomerPhone = (job, registration) => getFirstValue([
  registration?.customerPhone,
  registration?.phone,
  registration?.whatsapp,
  registration?.whatsappNumber,
  registration?.noHp,
  registration?.nomorHp,
  registration?.customer?.whatsapp,
  registration?.customer?.phone,
  job.customerPhone,
  job.phone,
  job.whatsapp,
  job.whatsappNumber,
  job.noHp,
  job.nomorHp,
  job.customer?.whatsapp,
  job.customer?.phone,
])
const getJobSessionLabel = (registration) => [registration?.sessionName, getTimeRangeLabel(registration)].filter(Boolean).join(' ')
const getWhatsAppUrl = (job, registration, trip) => buildWhatsAppUrl({
  phone: getCustomerPhone(job, registration),
  customerName: getCustomerName(job, registration),
  tripName: trip?.name || job.tripName || '',
  date: formatDate(job.requestedDate || getRegistrationDate(registration) || trip?.date),
  session: getJobSessionLabel(registration),
})

const getCompletionType = (job) => {
  if (job.workerAction === 'drive_link') return 'drive'
  if (job.tripAddonId && job.workerAction === 'none') return 'none'
  const typeText = [job.addonId, job.addonLabel, job.jobType, job.addonType, job.category]
    .filter(Boolean)
    .join(' ')
    .toLowerCase()

  if (mediaAddonIds.includes(job.addonId) || typeText.includes('drone') || typeText.includes('camera 360') || typeText.includes('camera360') || typeText.includes('video') || typeText.includes('foto')) {
    return 'drive'
  }
  if (job.addonId === 'transport' || typeText.includes('transport')) return 'transport'
  return ''
}

function WorkerShell({ title, children, navigate, logout, path }) {
  return (
    <main className="app-shell worker-shell">
      <Sidebar title="Tim" links={[
        ['/tim/dashboard', 'Dashboard'],
        ['/tim/job', 'Job tersedia'],
        ['/tim/job-saya', 'Job saya'],
      ]} navigate={navigate} logout={logout} path={path} />
      <section className="workspace worker-workspace">
        <header className="worker-page-header">
          <h1>{title}</h1>
        </header>
        {children}
      </section>
    </main>
  )
}

export function WorkerDashboard(props) {
  const ownJobs = props.jobs.filter((job) => job.worker === props.session?.name)
  const takenScopes = new Set(ownJobs.map(getJobScope))
  return (
    <WorkerShell title="Dashboard Tim" {...props}>
      <section className="stat-grid worker-stat-grid">
        <Metric label="Job tersedia" value={props.jobs.filter((job) => job.status === 'Tersedia' && !takenScopes.has(getJobScope(job))).length} />
        <Metric label="Job saya" value={ownJobs.length} />
        <Metric label="Sedang berjalan" value={ownJobs.filter((job) => job.status === 'Sedang Berjalan').length} />
      </section>
      <section className="worker-section-head">
        <h2>Job tersedia</h2>
      </section>
      <WorkerJobs {...props} embedded />
    </WorkerShell>
  )
}

export function WorkerJobs(props) {
  const takenScopes = new Set(props.jobs.filter((job) => job.worker === props.session?.name).map(getJobScope))
  const content = (
    <div className="job-grid">
      {props.jobs.filter((job) => job.status === 'Tersedia' && !takenScopes.has(getJobScope(job))).map((job) => <JobCard key={job.id} job={job} {...props} />)}
    </div>
  )
  if (props.embedded) return content
  return <WorkerShell title="Job Open Trip Tersedia" {...props}>{content}</WorkerShell>
}

export function MyJobs(props) {
  return (
    <WorkerShell title="Job Saya" {...props}>
      <div className="job-grid">
        {props.jobs.filter((job) => job.worker === props.session?.name).map((job) => <JobCard key={job.id} job={job} mine {...props} />)}
      </div>
    </WorkerShell>
  )
}

export function WorkerJobDetail({ jobId, jobs, trips, takeJob, updateJobStatus, navigate, ...props }) {
  const job = jobs.find((item) => item.id === jobId)
  if (!job) return <NotFound navigate={navigate} />
  const trip = trips.find((item) => item.id === job.tripId)
  const registration = props.registrations?.find((item) => Number(item.id) === Number(job.registrationId))
  const scheduleLabel = getJobScheduleLabel(job, registration, trip)
  const whatsappUrl = getWhatsAppUrl(job, registration, trip)
  const alreadyTookScope = jobs.some((item) => getJobScope(item) === getJobScope(job) && item.worker === props.session?.name)
  const showCompletionChecklist = job.status !== 'Tersedia' && Boolean(job.worker)
  return (
    <WorkerShell title="Detail Job" navigate={navigate} {...props}>
      <article className="detail-panel standalone worker-detail-panel">
        <div className="worker-detail-hero">
          <Badge status={job.status} />
          <div>
            <h2>{job.addonLabel || 'Job trip'} - {trip?.name || 'Cave trip'}</h2>
            <p className="muted">{workerText(trip?.destination)} - {scheduleLabel}</p>
          </div>
        </div>
        <div className="metric-row worker-info-grid">
          <Metric label="Customer" value={registration?.name || job.customerName || '-'} />
          <Metric label="Peserta" value={registration?.participants || (trip ? trip.quota - trip.slots : 0)} />
          <Metric label="Jadwal" value={scheduleLabel} />
          <Metric label="Status job" value={job.status} />
          <Metric label="Tim" value={job.worker || '-'} />
        </div>
        <div className="worker-task-section">
          <InfoBlock title="Detail tugas" text={job.task} />
        </div>
        <div className="worker-detail-actions">
          {job.status === 'Tersedia' ? <button className="primary-btn" disabled={alreadyTookScope} onClick={() => takeJob(job.id)}>{alreadyTookScope ? 'Sudah ambil booking ini' : 'Ambil job'}</button> : (
            <label className="status-control">Update status<select value={job.status === 'Selesai' ? 'Selesai' : job.status} disabled={job.status === 'Selesai'} onChange={(e) => updateJobStatus(job.id, e.target.value)}>{job.status === 'Selesai' && <option>Selesai</option>}{completionStatusOptions.map((status) => <option key={status}>{status}</option>)}</select></label>
          )}
          {whatsappUrl ? (
            <a className="whatsapp-action-btn" href={whatsappUrl} target="_blank" rel="noreferrer">Hubungi Customer</a>
          ) : (
            <p className="worker-whatsapp-note">Nomor WhatsApp customer belum tersedia.</p>
          )}
        </div>
        {showCompletionChecklist && <JobCompletionChecklist key={`${job.id}-${job.status}-${getJobResultLink(job)}-${job.proofPhotoName || ''}`} job={job} updateJobStatus={updateJobStatus} session={props.session} />}
      </article>
    </WorkerShell>
  )
}

function JobCard({ job, trips, registrations, navigate, takeJob, mine, updateJobStatus, session }) {
  const trip = trips.find((item) => item.id === job.tripId)
  const registration = registrations?.find((item) => Number(item.id) === Number(job.registrationId))
  const scheduleLabel = getJobScheduleLabel(job, registration, trip)
  const whatsappUrl = getWhatsAppUrl(job, registration, trip)
  const participantCount = registration?.participants || (trip ? trip.quota - trip.slots : 0)
  return (
    <article className="job-card">
      <div className="job-card-head">
        <div>
          <h3>{job.addonLabel || 'Job trip'}</h3>
          <p>{trip?.name} - {workerText(trip?.destination)}</p>
        </div>
        <Badge status={job.status} />
      </div>
      <p className="job-slot-label">{job.addonLabel ? `Kebutuhan ${job.addonLabel}` : `Slot tim ${job.slot || 1} dari ${job.totalWorkers || trip?.workerCount || 1}`}</p>
      <div className="job-card-meta">
        <p>{scheduleLabel}</p>
        <p>{registration?.name || job.customerName || 'Customer'} ({participantCount} peserta)</p>
      </div>
      <p className="muted job-card-task">{job.task}</p>
      {job.status === 'Tersedia' && !mine && <button className="primary-btn" onClick={() => takeJob(job.id)}>Ambil job</button>}
      {mine && <select className="status-select" value={job.status === 'Selesai' ? 'Selesai' : job.status} disabled={job.status === 'Selesai'} onChange={(e) => updateJobStatus(job.id, e.target.value)}>{job.status === 'Selesai' && <option>Selesai</option>}{completionStatusOptions.map((status) => <option key={status}>{status}</option>)}</select>}
      {mine && <JobCompletionChecklist key={`${job.id}-${job.status}-${getJobResultLink(job)}-${job.proofPhotoName || ''}`} job={job} updateJobStatus={updateJobStatus} session={session} compact />}
      <div className="job-card-actions">
        {whatsappUrl && <a className="whatsapp-action-btn compact" href={whatsappUrl} target="_blank" rel="noreferrer">WhatsApp</a>}
        <button className="outline-btn" onClick={() => navigate(`/tim/job/${job.id}`)}>Detail</button>
      </div>
    </article>
  )
}

function JobCompletionChecklist({ job, updateJobStatus, session, compact = false }) {
  const completionType = getCompletionType(job)
  const isDone = job.status === 'Selesai'
  const initialChecked = isDone || Boolean(job.completionChecked || job.transportCompleted)
  const initialDriveLink = getJobResultLink(job)
  const [checked, setChecked] = useState(initialChecked)
  const [driveLink, setDriveLink] = useState(initialDriveLink)
  const [photoName, setPhotoName] = useState(job.proofPhotoName || '')
  const [proofPhotoFile, setProofPhotoFile] = useState(null)
  const [error, setError] = useState('')
  const [isConfirmModalOpen, setIsConfirmModalOpen] = useState(false)

  if (!completionType) return null

  const openCompletionConfirmation = () => {
    if (!checked) {
      setError('Lengkapi checklist dan bukti pekerjaan terlebih dahulu.')
      return
    }
    if (completionType === 'drive' && !driveLink.trim()) {
      setError('Link hasil pekerjaan wajib diisi.')
      return
    }
    if (driveLink.trim()) {
      try {
        new URL(driveLink.trim())
      } catch {
        setError('Link hasil pekerjaan harus berupa URL yang valid.')
        return
      }
    }
    if (completionType === 'transport' && !photoName.trim()) {
      setError('Lengkapi checklist dan bukti pekerjaan terlebih dahulu.')
      return
    }

    setError('')
    setIsConfirmModalOpen(true)
  }

  const completeJob = () => {
    const completedAt = new Date().toISOString()
    const completionFields = completionType === 'drive'
      ? {
        completionChecked: true,
        resultStatus: 'completed',
        resultLink: driveLink.trim(),
        driveLink: driveLink.trim(),
        completionLink: driveLink.trim(),
        completedAt,
        completedByName: session?.name || job.worker || '',
        completedById: session?.email || job.workerId || '',
        bookingId: job.bookingId || job.registrationId || '',
        addonType: job.addonType || job.addonId || '',
      }
      : completionType === 'transport' ? {
        completionChecked: true,
        resultStatus: 'completed',
        transportCompleted: true,
        proofPhotoName: photoName,
        proofPhotoFile,
        completedAt,
        completedByName: session?.name || job.worker || '',
        completedById: session?.email || job.workerId || '',
        bookingId: job.bookingId || job.registrationId || '',
        addonType: job.addonType || job.addonId || '',
      } : {
        completionChecked: true,
        resultStatus: 'completed',
        completedAt,
        completedByName: session?.name || job.worker || '',
        completedById: session?.email || job.workerId || '',
        bookingId: job.bookingId || job.registrationId || '',
        addonType: job.addonType || job.addonId || '',
      }
    updateJobStatus(job.id, 'Selesai', completionFields)
    setIsConfirmModalOpen(false)
    setError('')
  }

  return (
    <section className={`job-completion-card ${compact ? 'is-compact' : ''}`}>
      <div className="job-completion-head">
        <h4>Checklist penyelesaian</h4>
        {isDone && <Badge status="Selesai" />}
      </div>
      <label className="completion-check">
        <input type="checkbox" checked={checked} disabled={isDone} onChange={(event) => setChecked(event.target.checked)} />
        <span>{completionType === 'drive' ? 'Pekerjaan selesai dan link hasil siap dibagikan' : 'Pekerjaan sudah selesai'}</span>
      </label>

      {completionType === 'drive' ? (
        <label className="completion-field">Link hasil pekerjaan
          <input type="url" placeholder="Tempel link Google Drive hasil dokumentasi di sini" value={driveLink} disabled={isDone} onChange={(event) => setDriveLink(event.target.value)} />
        </label>
      ) : completionType === 'transport' ? (
        <div className="completion-field">
          <span>Kirim Foto Bukti</span>
          <label className="proof-upload-btn">Pilih Foto
            <input type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" disabled={isDone} onChange={(event) => {
              const file = event.target.files?.[0] || null
              setProofPhotoFile(file)
              setPhotoName(file?.name || '')
            }} />
          </label>
          {photoName && <small className="proof-file-name">{photoName}</small>}
          <small>Foto bukti akan disimpan saat pekerjaan diselesaikan.</small>
        </div>
      ) : <p className="muted">Add-on ini tidak memerlukan upload hasil. Centang checklist untuk menyelesaikan pekerjaan.</p>}

      {error && <p className="form-error job-completion-error">{error}</p>}
      {!isDone && <button className="primary-btn" type="button" onClick={openCompletionConfirmation}>Selesaikan Pekerjaan</button>}
      <AppModal
        isOpen={isConfirmModalOpen}
        title="Selesaikan pekerjaan?"
        description="Pastikan checklist pekerjaan sudah dipenuhi dan bukti pekerjaan sudah diisi dengan benar sebelum menyelesaikan tugas."
        confirmText="Ya, Selesaikan"
        cancelText="Batal"
        variant="success"
        onConfirm={completeJob}
        onCancel={() => setIsConfirmModalOpen(false)}
      />
    </section>
  )
}
