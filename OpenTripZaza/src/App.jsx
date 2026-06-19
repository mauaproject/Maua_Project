import { useCallback, useEffect, useMemo, useState } from 'react'
import './App.css'
import i18n from './i18n'
import { AdminDashboard, AdminSchedule, AdminTrips, AdminWorkers, TripForm } from './pages/AdminPage'
import { CustomerAccountPage, CustomerCatalog, CustomerLoginPage, CustomerSignupPage, DestinationPage, EmailVerificationPage, PaymentConfirmationPage, RegistrationPage, TripDetail } from './pages/UserPage'
import { MyJobs, WorkerDashboard, WorkerJobDetail, WorkerJobs } from './pages/WorkerPage'
import { LoginPage, NotFound } from './pages/shared'
import * as api from './services/api'
import { ABOVE_MAX_PAX_RULE, normalizePricePerPersonTiers } from './utils/pricing'
import { getRequiredPaymentAmount } from './utils/payments'
import { getPrivatePackages } from './utils/privatePackages'
import {
  getOpenTripScheduleOptions,
  getPrivateSessionOptions,
  isDateWithinPrivateRange,
  isPrivateSessionBooked,
} from './utils/schedules'

const getJobScope = (job) => job.registrationId ? `registration-${job.registrationId}` : `trip-${job.tripId}`
const CHECKOUT_DRAFT_KEY = 'mauaCheckoutDraft'

const readCheckoutDraft = () => {
  try {
    return JSON.parse(window.sessionStorage.getItem(CHECKOUT_DRAFT_KEY) || 'null')
  } catch {
    return null
  }
}

function App() {
  const [path, setPath] = useState(window.location.pathname)
  const [session, setSession] = useState(null)
  const [trips, setTrips] = useState([])
  const [registrations, setRegistrations] = useState([])
  const [jobs, setJobs] = useState([])
  const [customerAccounts, setCustomerAccounts] = useState([])
  const [workerAccounts, setWorkerAccounts] = useState([])
  const [checkoutDraft, setCheckoutDraft] = useState(readCheckoutDraft)
  const [toast, setToast] = useState('')

  const navigate = (target) => {
    window.history.pushState({}, '', target)
    setPath(target)
    window.scrollTo(0, 0)
  }

  const showToast = (message) => {
    setToast(message)
    window.setTimeout(() => setToast(''), 3200)
  }

  useEffect(() => {
    const handlePopState = () => setPath(window.location.pathname)
    window.addEventListener('popstate', handlePopState)
    return () => window.removeEventListener('popstate', handlePopState)
  }, [])

  const refreshData = async () => {
    const detailMatch = window.location.pathname.match(/^\/open-trip\/(\d+)$/)
    const [tripData, bookingData, taskData, customerData, workerData, detailTrip] = await Promise.all([
      api.getTrips(true),
      api.getBookings(),
      api.getWorkerTasks(),
      api.getUsers('customer'),
      api.getUsers('worker'),
      detailMatch ? api.getTripDetail(Number(detailMatch[1])) : Promise.resolve(null),
    ])
    setTrips(detailTrip ? tripData.map((trip) => trip.id === detailTrip.id ? detailTrip : trip) : tripData)
    setRegistrations(bookingData)
    setJobs(taskData)
    setCustomerAccounts(customerData)
    setWorkerAccounts(workerData)
  }

  useEffect(() => {
    const timer = window.setTimeout(() => {
      refreshData().catch((error) => showToast(error.message))
    }, 0)
    return () => window.clearTimeout(timer)
  }, [])

  const login = async (role, form) => {
    try {
      const account = await api.loginUser(form.email, form.password, role)
      setSession(account)
      await refreshData()
      navigate(role === 'admin' ? '/admin/dashboard' : '/pekerja/dashboard')
      return true
    } catch {
      return false
    }
  }

  const loginCustomer = async (form, redirectTo = '/open-trip') => {
    try {
      const account = await api.loginUser(form.email, form.password, 'customer')
      setSession(account)
      setRegistrations(await api.getUserBookings(account.email))
      navigate(redirectTo)
      return true
    } catch {
      return false
    }
  }

  const signupCustomer = async (form) => {
    const exists = customerAccounts.some((item) => item.email === form.email)
    if (exists) return false
    try {
      const nextAccount = await api.registerCustomer({
        name: form.name,
        whatsapp: form.whatsapp,
        email: form.email,
        password: form.password,
        address: form.address || '',
        age: form.age || '',
        gender: form.gender || '',
        healthNotes: form.healthNotes || '',
      })
      navigate(`/verify-email?email=${encodeURIComponent(nextAccount.email)}`)
      showToast(i18n.t('toast.signupVerificationSent'))
      return true
    } catch {
      return false
    }
  }

  const resendVerification = async (email = session?.email) => {
    if (!email) return false
    try {
      await api.resendEmailVerification(email)
      showToast(i18n.t('toast.verificationResent'))
      return true
    } catch (error) {
      showToast(error.message)
      return false
    }
  }

  const refreshEmailVerification = async () => {
    if (!session?.email) return false
    try {
      const account = await api.getCurrentCustomer(session.email)
      setSession((current) => current?.email === account.email ? { ...current, ...account } : current)
      setCustomerAccounts((current) => current.map((item) => item.email === account.email ? { ...item, ...account } : item))
      if (account.emailVerified) showToast(i18n.t('toast.emailVerified'))
      return account.emailVerified
    } catch {
      return false
    }
  }

  const verifyEmailOtp = useCallback(async (email, otp) => {
    return api.verifyEmail(email, otp)
  }, [])

  const createWorkerAccount = async (form) => {
    const normalizedEmail = form.email.trim().toLowerCase()
    const exists = workerAccounts.some((item) => item.email === normalizedEmail)
    if (exists) return false

    const nextWorker = await api.createUser({
      name: form.name.trim(),
      email: normalizedEmail,
      password: form.password,
      role: 'pekerja',
    })
    setWorkerAccounts((current) => [...current, nextWorker])
    showToast('Akun pekerja berhasil dibuat.')
    return true
  }

  const logout = () => {
    setSession(null)
    setCheckoutDraft(null)
    window.sessionStorage.removeItem(CHECKOUT_DRAFT_KEY)
    navigate('/')
  }

  const preparePayment = (draft) => {
    const nextDraft = { ...draft, paymentProof: undefined }
    setCheckoutDraft(nextDraft)
    window.sessionStorage.setItem(CHECKOUT_DRAFT_KEY, JSON.stringify(nextDraft))
    navigate('/payment-confirmation')
  }

  const clearCheckoutDraft = () => {
    setCheckoutDraft(null)
    window.sessionStorage.removeItem(CHECKOUT_DRAFT_KEY)
  }

  const approvedByTrip = useMemo(() => {
    return registrations.reduce((result, item) => {
      if (item.status === 'Disetujui' || item.status === 'Selesai') {
        result[item.tripId] = (result[item.tripId] || 0) + item.participants
      }
      return result
    }, {})
  }, [registrations])

  const submitRegistration = async (form) => {
    if (!session?.emailVerified || Number(session.id) !== Number(form.userId)) return false
    if (!['dp', 'full'].includes(form.paymentType) || !(form.paymentProof instanceof File)) return false
    const trip = trips.find((item) => item.id === Number(form.tripId))
    if (!trip || trip.status !== 'Tersedia') return false
    const approvedRegistrations = registrations.filter((item) => item.tripId === Number(form.tripId) && (item.status === 'Disetujui' || item.status === 'Selesai'))
    const isPrivateTour = Boolean(form.isPrivateTour || trip.isPrivateTrip)
    const participantCount = isPrivateTour ? Number(form.participants) : Number(form.participants || 1)
    if (!Number.isFinite(participantCount) || participantCount < 1) return false
    if (isPrivateTour && participantCount < Number(trip.minParticipants || 1)) return false
    if (isPrivateTour && participantCount > Number(trip.maxParticipants || trip.quota || participantCount)) return false
    let selectedSchedule = null
    let selectedSession = null
    let selectedDate = form.selectedDate || form.requestedDate || ''
    if (!isPrivateTour) {
      const scheduleOptions = getOpenTripScheduleOptions(trip, registrations)
      selectedSchedule = scheduleOptions.find((schedule) => schedule.id === form.scheduleId)
      if (!selectedSchedule || !selectedSchedule.isSelectable || selectedSchedule.remaining < participantCount) return false
      selectedDate = selectedSchedule.date
    }
    const privateTourTaken = approvedRegistrations.some((item) => item.isPrivateTour)
    if (!trip.isPrivateTrip && (privateTourTaken || (isPrivateTour && approvedRegistrations.length))) return false
    if (isPrivateTour) {
      if (!selectedDate || !form.sessionId) return false
      if (!isDateWithinPrivateRange(trip, selectedDate)) return false
      const sessionOptions = getPrivateSessionOptions(trip, registrations, selectedDate)
      selectedSession = sessionOptions.find((session) => session.id === form.sessionId)
      if (!selectedSession || !selectedSession.isSelectable) return false
      if (!selectedSession.isLegacy && isPrivateSessionBooked(registrations, trip.id, selectedDate, selectedSession.id)) return false
    }
    const participantDetails = Array.isArray(form.participantDetails) && form.participantDetails.length
      ? form.participantDetails
      : [{ name: form.name, address: form.address || '', age: form.age || '', gender: form.gender || '', healthNotes: form.healthNotes || '' }]
    const primaryParticipant = participantDetails[0] || {}
    const tripAddons = Array.isArray(trip.addons) ? trip.addons : []
    const selectedAddonIds = Array.isArray(form.addons)
      ? form.addons.map(Number).filter((addonId) => tripAddons.some((option) => Number(option.id) === addonId))
      : []
    const selectedAddonTotal = tripAddons
      .filter((option) => selectedAddonIds.includes(Number(option.id)))
      .reduce((total, option) => total + Number(option.price || 0), 0)
    const privatePackages = isPrivateTour ? getPrivatePackages(trip, true) : []
    const selectedPackage = isPrivateTour
      ? privatePackages.find((item) => String(item.id) === String(form.selectedPackageId))
      : null
    if (isPrivateTour && !selectedPackage) return false
    const hargaPerOrang = isPrivateTour ? 0 : Number(trip.price || 0)
    const totalHarga = isPrivateTour
      ? Number(selectedPackage.price) + selectedAddonTotal
      : (participantCount * hargaPerOrang) + selectedAddonTotal
    const requiredPaymentAmount = getRequiredPaymentAmount(totalHarga, form.paymentType)
    const nextItem = {
      name: form.name,
      whatsapp: form.whatsapp,
      email: form.email,
      userId: Number(session.id),
      participants: isPrivateTour ? participantCount : 1,
      participantCount: isPrivateTour ? participantCount : 1,
      tripId: Number(form.tripId),
      tripType: isPrivateTour ? 'private' : 'open',
      experienceType: trip.experienceType === 'custom' ? 'custom' : 'cave',
      notes: form.notes || '-',
      isPrivateTour,
      isPrivateTrip: Boolean(trip.isPrivateTrip),
      scheduleId: isPrivateTour ? '' : selectedSchedule.id,
      selectedDate,
      requestedDate: selectedDate,
      sessionId: isPrivateTour ? selectedSession.id : '',
      sessionName: isPrivateTour ? selectedSession.name : '',
      startTime: isPrivateTour ? selectedSession.startTime : '',
      endTime: isPrivateTour ? selectedSession.endTime : '',
      selectedPackageId: selectedPackage?.id || null,
      selectedPackageName: selectedPackage?.name || '',
      selectedPackagePrice: Number(selectedPackage?.price || 0),
      selectedPackageDestinations: selectedPackage?.destinations || [],
      address: primaryParticipant.address || '',
      age: primaryParticipant.age || '',
      gender: primaryParticipant.gender || '',
      healthNotes: primaryParticipant.healthNotes || '',
      participantDetails,
      addons: selectedAddonIds,
      transportFrom: '',
      hargaPerOrang,
      totalHarga,
      pricePerPerson: hargaPerOrang,
      totalPrice: totalHarga,
      paymentType: form.paymentType,
      paymentStatus: 'waiting_verification',
      paidAmount: requiredPaymentAmount,
      requiredPaymentAmount,
      bcaAccountNumber: form.bcaAccountNumber || '',
      paymentChannel: 'qris_or_bca',
      status: 'Menunggu Approval',
    }
    await api.createBooking(nextItem, form.paymentProof)
    await refreshData()
    setSession((current) => current?.email === form.email ? {
      ...current,
      name: form.name,
      whatsapp: form.whatsapp,
      address: primaryParticipant.address || '',
      age: primaryParticipant.age || '',
      gender: primaryParticipant.gender || '',
      healthNotes: primaryParticipant.healthNotes || '',
    } : current)
    clearCheckoutDraft()
    showToast(i18n.t('toast.registrationSuccess'))
    navigate('/open-trip')
    return true
  }

  const setRegistrationStatus = async (id, status) => {
    await api.updateBookingStatus(id, status)
    await refreshData()
  }

  const saveTrip = async (trip) => {
    const imageFiles = Array.isArray(trip.imageFiles) ? trip.imageFiles : []
    const tripData = { ...trip }
    delete tripData.imageFiles
    const schedules = Array.isArray(trip.schedules)
      ? trip.schedules.map((schedule, index) => ({
        id: schedule.id || `schedule_${index + 1}`,
        date: schedule.date || '',
        quota: Number(schedule.quota || 0),
        bookedCount: Number(schedule.bookedCount || 0),
        status: schedule.status || 'active',
      }))
      : []
    const sessions = Array.isArray(trip.sessions)
      ? trip.sessions.map((sessionItem, index) => ({
        id: sessionItem.id || `session_${index + 1}`,
        name: sessionItem.name || `Sesi ${index + 1}`,
        startTime: sessionItem.startTime || '',
        endTime: sessionItem.endTime || '',
        status: sessionItem.status || 'active',
      }))
      : []
    const isPrivateTrip = Boolean(trip.isPrivateTrip)
    const maxParticipants = Number(trip.maxParticipants || trip.quota || 1)
    const maxCustomPax = isPrivateTrip
      ? Math.min(Math.max(1, Number(trip.maxCustomPax) || 1), Math.max(1, maxParticipants))
      : 0
    const aggregateQuota = schedules.reduce((total, schedule) => total + Number(schedule.quota || 0), 0)
    const aggregateSlots = schedules.reduce((total, schedule) => total + Math.max(Number(schedule.quota || 0) - Number(schedule.bookedCount || 0), 0), 0)
    const normalizedTrip = {
      ...tripData,
      type: isPrivateTrip ? 'private' : 'open',
      experienceType: trip.experienceType === 'custom' ? 'custom' : 'cave',
      schedules: isPrivateTrip ? [] : schedules,
      sessions: isPrivateTrip ? sessions : [],
      availableStartDate: isPrivateTrip ? trip.availableStartDate || '' : '',
      availableEndDate: isPrivateTrip ? trip.availableEndDate || '' : '',
      date: !isPrivateTrip ? schedules[0]?.date || trip.date || '' : trip.date || '',
      slots: !isPrivateTrip && schedules.length ? aggregateSlots : Number(trip.slots),
      quota: !isPrivateTrip && schedules.length ? aggregateQuota : Number(trip.quota),
      price: Number(trip.price),
      pricePerPersonTiers: isPrivateTrip
        ? normalizePricePerPersonTiers(trip.pricePerPersonTiers, trip.price, maxCustomPax)
        : {},
      maxCustomPax,
      aboveMaxPaxRule: isPrivateTrip ? ABOVE_MAX_PAX_RULE : '',
      minParticipants: Number(trip.minParticipants || 1),
      maxParticipants,
    }
    if (trip.id) {
      const nextTrip = { ...normalizedTrip, id: Number(trip.id) }
      const savedTrip = await api.updateTrip(nextTrip)
      await Promise.all(imageFiles.map((file) => api.uploadTripImage(file, savedTrip.id)))
    } else {
      const savedTrip = await api.createTrip(normalizedTrip)
      await Promise.all(imageFiles.map((file) => api.uploadTripImage(file, savedTrip.id)))
    }
    await refreshData()
    navigate('/admin/open-trip')
  }

  const deleteTrip = async (id) => {
    await api.deleteTrip(id)
    await refreshData()
  }

  const takeJob = async (id) => {
    const job = jobs.find((item) => item.id === id)
    if (!job || job.status !== 'Tersedia') return
    const workerName = session?.name || 'Worker'
    const jobScope = getJobScope(job)
    const alreadyTookScope = jobs.some((item) => getJobScope(item) === jobScope && item.worker === workerName)
    if (alreadyTookScope) {
      showToast('Kamu sudah mengambil job untuk booking ini.')
      return
    }
    await api.takeWorkerTask(id, { workerEmail: session?.email, workerName })
    await refreshData()
    showToast('Job berhasil diambil.')
  }

  const updateJobStatus = async (id, status, extraFields = {}) => {
    await api.completeWorkerTask(id, { status, ...extraFields })
    await refreshData()
  }

  const props = {
    path,
    session,
    trips,
    registrations,
    jobs,
    customerAccounts,
    workerAccounts,
    approvedByTrip,
    navigate,
    login,
    loginCustomer,
    signupCustomer,
    resendVerification,
    refreshEmailVerification,
    verifyEmailOtp,
    createWorkerAccount,
    logout,
    checkoutDraft,
    preparePayment,
    clearCheckoutDraft,
    submitRegistration,
    setRegistrationStatus,
    saveTrip,
    deleteTrip,
    takeJob,
    updateJobStatus,
    showToast,
  }

  return (
    <>
      {toast && <div className="toast">{toast}</div>}
      <RouteRenderer {...props} />
    </>
  )
}

function RouteRenderer(props) {
  const { path, session, navigate } = props
  const parts = path.split('/').filter(Boolean)
  const id = Number(parts[1] || parts[2] || 0)

  if (path.startsWith('/admin') && path !== '/admin/login' && session?.role !== 'admin') {
    return <LoginPage role="admin" {...props} />
  }
  if (path.startsWith('/pekerja') && path !== '/pekerja/login' && session?.role !== 'pekerja') {
    return <LoginPage role="pekerja" {...props} />
  }

  if (path === '/' || path === '/open-trip') return <CustomerCatalog {...props} />
  if (path.startsWith('/destinasi')) return <DestinationPage {...props} />
  if (path === '/akun') {
    if (session?.role !== 'customer') return <CustomerLoginPage afterLoginPath="/akun" {...props} />
    return <CustomerAccountPage {...props} />
  }
  if (path === '/login' || path === '/customer/login') return <CustomerLoginPage {...props} />
  if (path === '/signup' || path === '/customer/signup') return <CustomerSignupPage {...props} />
  if (path.startsWith('/verify-email')) return <EmailVerificationPage {...props} />
  if (parts[0] === 'open-trip' && id) return <TripDetail tripId={id} {...props} />
  if (path === '/payment-confirmation') {
    if (session?.role !== 'customer') return <CustomerLoginPage afterLoginPath="/payment-confirmation" {...props} />
    return <PaymentConfirmationPage {...props} />
  }
  if (parts[0] === 'daftar' && id) {
    if (session?.role !== 'customer') return <CustomerLoginPage afterLoginPath={`/daftar/${id}`} {...props} />
    return <RegistrationPage tripId={id} {...props} />
  }
  if (path === '/admin/login') return <LoginPage role="admin" {...props} />
  if (path === '/admin/dashboard') return <AdminDashboard {...props} />
  if (path === '/admin/open-trip') return <AdminTrips {...props} />
  if (path === '/admin/open-trip/tambah') return <TripForm {...props} />
  if (parts[0] === 'admin' && parts[1] === 'open-trip' && parts[2] === 'edit') return <TripForm tripId={Number(parts[3])} {...props} />
  if (path === '/admin/pendaftaran') return <AdminSchedule {...props} />
  if (path === '/admin/jadwal') return <AdminSchedule {...props} />
  if (parts[0] === 'admin' && parts[1] === 'jadwal' && parts[2] === 'private-trip' && Number(parts[3])) return <AdminSchedule privateTripId={Number(parts[3])} {...props} />
  if (parts[0] === 'admin' && parts[1] === 'jadwal' && parts[2] === 'private' && Number(parts[3])) return <AdminSchedule scheduleRegistrationId={Number(parts[3])} {...props} />
  if (parts[0] === 'admin' && parts[1] === 'jadwal' && Number(parts[2])) return <AdminSchedule scheduleTripId={Number(parts[2])} scheduleId={parts[3] || ''} {...props} />
  if (path === '/admin/pekerja') return <AdminWorkers {...props} />
  if (path === '/pekerja/login') return <LoginPage role="pekerja" {...props} />
  if (path === '/pekerja/dashboard') return <WorkerDashboard {...props} />
  if (path === '/pekerja/job') return <WorkerJobs {...props} />
  if (parts[0] === 'pekerja' && parts[1] === 'job' && Number(parts[2])) return <WorkerJobDetail jobId={Number(parts[2])} {...props} />
  if (path === '/pekerja/job-saya') return <MyJobs {...props} />

  return <NotFound navigate={navigate} />
}

export default App
