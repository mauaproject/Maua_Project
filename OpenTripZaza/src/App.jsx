import { lazy, Suspense, useCallback, useEffect, useRef, useState } from 'react'
import './App.css'
import i18n from './i18n'
import { CustomerAccountPage, CustomerCatalog, CustomerLoginPage, CustomerSignupPage, DestinationPage, EmailVerificationPage, PaymentConfirmationPage, RegistrationPage, ReviewsPage, TripDetail } from './pages/UserPage'
import { LoginPage, NotFound } from './pages/shared'
import * as api from './services/api'
import { ABOVE_MAX_PAX_RULE, getPrivatePricePerPerson, normalizePricePerPersonTiers } from './utils/pricing'
import { getRequiredPaymentAmount } from './utils/payments'
import { getPackagePricePerPerson, getPrivatePackages } from './utils/privatePackages'
import { validateCustomerTripProfile } from './utils/customerProfile'
import { getAddonLineTotal, hydrateAddonForParticipants, isTripAddonActive } from './utils/addons'
import { localizedText } from './utils/localization'
import {
  getOpenTripScheduleOptions,
  getPrivateSessionOptions,
  isDateWithinPrivateRange,
  isPrivateSessionBooked,
  isSharedPrivateBooking,
} from './utils/schedules'
const CHECKOUT_DRAFT_KEY = 'mauaCheckoutDraft'
const SITE_URL = (import.meta.env.VITE_SITE_URL || 'https://mauaproject.com').replace(/\/$/, '')
const DEFAULT_SEO = {
  title: 'MAUA Project | Open Trip Goa Yogyakarta',
  description: 'MAUA Project menyediakan open trip goa dan private cave tour di Yogyakarta dengan jadwal fleksibel, booking mudah, dan pengalaman jelajah goa yang tertata.',
  robots: 'index, follow',
}
const lazyNamed = (loader, name) => lazy(() => loader().then((module) => ({ default: module[name] })))
const loadAdminPage = () => import('./pages/AdminPage')
const loadWorkerPage = () => import('./pages/WorkerPage')
const AdminDashboard = lazyNamed(loadAdminPage, 'AdminDashboard')
const AdminBookingCalendar = lazyNamed(loadAdminPage, 'AdminBookingCalendar')
const AdminReviews = lazyNamed(loadAdminPage, 'AdminReviews')
const AdminSchedule = lazyNamed(loadAdminPage, 'AdminSchedule')
const AdminTripArchive = lazyNamed(loadAdminPage, 'AdminTripArchive')
const AdminTrips = lazyNamed(loadAdminPage, 'AdminTrips')
const AdminWorkers = lazyNamed(loadAdminPage, 'AdminWorkers')
const TripForm = lazyNamed(loadAdminPage, 'TripForm')
const MyJobs = lazyNamed(loadWorkerPage, 'MyJobs')
const WorkerDashboard = lazyNamed(loadWorkerPage, 'WorkerDashboard')
const WorkerJobDetail = lazyNamed(loadWorkerPage, 'WorkerJobDetail')
const WorkerJobs = lazyNamed(loadWorkerPage, 'WorkerJobs')

const canonicalPath = (target) => {
  if (target === '/pekerja') return '/tim'
  if (target.startsWith('/pekerja/')) return target.replace(/^\/pekerja/, '/tim')
  if (target === '/admin/pekerja') return '/admin/tim'
  return target
}

const readCurrentPath = () => {
  const currentPath = window.location.pathname
  const nextPath = canonicalPath(currentPath)
  if (nextPath !== currentPath) {
    window.history.replaceState({}, '', nextPath)
  }
  return nextPath
}

const readCheckoutDraft = () => {
  try {
    return JSON.parse(window.sessionStorage.getItem(CHECKOUT_DRAFT_KEY) || 'null')
  } catch {
    return null
  }
}

const getPublicPath = (path) => {
  const currentPath = path.split('?')[0] || '/'
  if (currentPath === '/open-trip') return '/'
  if (currentPath === '/review') return '/reviews'
  return currentPath
}

const upsertMetaTag = (selector, attributes) => {
  let element = document.head.querySelector(selector)
  if (!element) {
    element = document.createElement('meta')
    document.head.appendChild(element)
  }
  Object.entries(attributes).forEach(([key, value]) => element.setAttribute(key, value))
}

const upsertLinkTag = (selector, attributes) => {
  let element = document.head.querySelector(selector)
  if (!element) {
    element = document.createElement('link')
    document.head.appendChild(element)
  }
  Object.entries(attributes).forEach(([key, value]) => element.setAttribute(key, value))
}

const buildSeo = (path, trips) => {
  const cleanPath = path.split('?')[0] || '/'
  const parts = cleanPath.split('/').filter(Boolean)
  const tripId = parts[0] === 'open-trip' ? Number(parts[1]) : 0
  const trip = tripId ? trips.find((item) => Number(item.id) === tripId) : null
  const privatePath = cleanPath.startsWith('/admin') || cleanPath.startsWith('/tim') || cleanPath.startsWith('/akun') || cleanPath.startsWith('/payment-confirmation') || cleanPath.startsWith('/daftar') || cleanPath.startsWith('/verify-email')

  if (privatePath) {
    return {
      ...DEFAULT_SEO,
      title: 'Area Akun | MAUA Project',
      description: 'Area akun MAUA Project.',
      robots: 'noindex, nofollow',
      canonicalPath: '/',
    }
  }

  if (trip) {
    const destination = localizedText(trip.destination, 'id') || trip.destination || 'Yogyakarta'
    return {
      title: `${trip.name} | MAUA Project`,
      description: `Lihat detail ${trip.name} di ${destination}, termasuk jadwal, harga, kuota, dan informasi booking open trip atau private trip goa.`,
      robots: 'index, follow',
      canonicalPath: `/open-trip/${trip.id}`,
    }
  }

  if (cleanPath.startsWith('/destinasi')) {
    return {
      title: 'Destinasi Open Trip Goa | MAUA Project',
      description: 'Jelajahi daftar destinasi wisata goa, open trip, private tour, jadwal, dan pilihan paket pengalaman bersama MAUA Project.',
      robots: 'index, follow',
      canonicalPath: '/destinasi',
    }
  }

  if (cleanPath === '/reviews' || cleanPath === '/review') {
    return {
      title: 'Review Peserta | MAUA Project',
      description: 'Baca pengalaman peserta yang sudah mengikuti open trip goa dan private cave tour bersama MAUA Project.',
      robots: 'index, follow',
      canonicalPath: '/reviews',
    }
  }

  if (cleanPath === '/login' || cleanPath === '/customer/login' || cleanPath === '/signup' || cleanPath === '/customer/signup') {
    return {
      ...DEFAULT_SEO,
      title: 'Masuk atau Daftar | MAUA Project',
      description: 'Masuk atau daftar akun pelanggan MAUA Project.',
      robots: 'noindex, follow',
      canonicalPath: '/',
    }
  }

  return {
    ...DEFAULT_SEO,
    canonicalPath: getPublicPath(cleanPath),
  }
}

function App() {
  const [path, setPath] = useState(readCurrentPath)
  const [session, setSession] = useState(null)
  const [trips, setTrips] = useState([])
  const [reviewTrips, setReviewTrips] = useState([])
  const [registrations, setRegistrations] = useState([])
  const [jobs, setJobs] = useState([])
  const [customerAccounts, setCustomerAccounts] = useState([])
  const [workerAccounts, setWorkerAccounts] = useState([])
  const [reviews, setReviews] = useState([])
  const [userReviews, setUserReviews] = useState([])
  const [adminReviews, setAdminReviews] = useState([])
  const [checkoutDraft, setCheckoutDraft] = useState(readCheckoutDraft)
  const [toast, setToast] = useState('')
  const [isSessionRestoring, setIsSessionRestoring] = useState(() => Boolean(api.getSessionToken()))
  const detailRequestsRef = useRef(new Set())

  const navigate = (target) => {
    const nextPath = canonicalPath(target)
    window.history.pushState({}, '', nextPath)
    setPath(nextPath)
    window.scrollTo(0, 0)
  }

  const showToast = (message) => {
    setToast(message)
    window.setTimeout(() => setToast(''), 3200)
  }

  useEffect(() => {
    const handlePopState = () => setPath(readCurrentPath())
    window.addEventListener('popstate', handlePopState)
    return () => window.removeEventListener('popstate', handlePopState)
  }, [])

  const refreshData = async (activeSession = session) => {
    const role = activeSession?.role

    if (role === 'admin') {
      const [tripData, bookingData, taskData, customerData, workerData, reviewData] = await Promise.all([
        api.getTrips(true),
        api.getBookings('all'),
        api.getWorkerTasks(),
        api.getUsers('customer'),
        api.getUsers('worker'),
        api.getReviews(),
      ])
      setTrips(tripData)
      setRegistrations(bookingData)
      setJobs(taskData)
      setCustomerAccounts(customerData)
      setWorkerAccounts(workerData)
      setReviews(reviewData)
      setReviewTrips([])
      return
    }

    if (role === 'pekerja') {
      const [tripData, bookingData, taskData] = await Promise.all([
        api.getTripSummaries(true),
        api.getBookings(),
        api.getWorkerTasks(),
      ])
      setTrips(tripData)
      setRegistrations(bookingData)
      setJobs(taskData)
      setReviewTrips([])
      return
    }

    const [tripData, reviewData] = await Promise.all([
      api.getTripSummaries(),
      api.getReviews(),
    ])
    setTrips(tripData)
    setReviews(reviewData)
    if (role !== 'customer') setReviewTrips([])
    if (role === 'customer') {
      const [bookingData, taskData, userReviewData, reviewTripData] = await Promise.all([
        Promise.all([
          api.getUserBookings(activeSession.email, 'active'),
          api.getUserBookings(activeSession.email, 'history'),
        ]).then(([activeBookings, historyBookings]) => [...activeBookings, ...historyBookings]),
        api.getWorkerTasks(),
        api.getUserReviews(activeSession.email, activeSession.id),
        api.getTripSummaries(true),
      ])
      setRegistrations(bookingData)
      setJobs(taskData)
      setUserReviews(userReviewData)
      setReviewTrips(reviewTripData)
    }
  }

  useEffect(() => {
    const timer = window.setTimeout(() => {
      const restore = async () => {
        if (api.getSessionToken()) {
          try {
            const account = await api.getSessionUser()
            setSession(account)
            await refreshData(account)
            if (account.role === 'admin') setAdminReviews(await api.getReviews(true, account.email))
            return
          } catch {
            api.clearSessionToken()
            setSession(null)
          } finally {
            setIsSessionRestoring(false)
          }
        }
        setIsSessionRestoring(false)
        await refreshData(null)
      }
      restore().catch((error) => {
        setIsSessionRestoring(false)
        showToast(error.message)
      })
    }, 0)
    return () => window.clearTimeout(timer)
    // Initial public bootstrap only; later refreshes are driven by mutations and authentication.
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [])

  useEffect(() => {
    const detailMatch = path.match(/^\/(?:open-trip|daftar)\/(\d+)$/)
    if (!detailMatch) return
    const tripId = Number(detailMatch[1])
    if (trips.some((trip) => trip.id === tripId && Array.isArray(trip.imageUrls))) return
    if (detailRequestsRef.current.has(tripId)) return
    detailRequestsRef.current.add(tripId)
    Promise.all([
      api.getTripDetail(tripId),
      path.startsWith('/daftar/') ? api.getPrivateBookingAvailability(tripId) : Promise.resolve([]),
    ]).then(([detailTrip, availability]) => {
      setTrips((current) => [...current.filter((trip) => trip.id !== detailTrip.id), detailTrip])
      if (availability.length) {
        setRegistrations((current) => [...current, ...availability.filter((item) => !current.some((existing) => existing.id === item.id))])
      }
    }).catch((error) => showToast(error.message))
      .finally(() => detailRequestsRef.current.delete(tripId))
  }, [path, trips])

  useEffect(() => {
    const seo = buildSeo(path, trips)
    const canonicalUrl = `${SITE_URL}${seo.canonicalPath === '/' ? '/' : seo.canonicalPath}`
    document.documentElement.lang = i18n.language?.startsWith('en') ? 'en' : 'id'
    document.title = seo.title
    upsertMetaTag('meta[name="description"]', { name: 'description', content: seo.description })
    upsertMetaTag('meta[name="robots"]', { name: 'robots', content: seo.robots })
    upsertMetaTag('meta[name="googlebot"]', { name: 'googlebot', content: `${seo.robots}, max-snippet:-1, max-image-preview:large, max-video-preview:-1` })
    upsertLinkTag('link[rel="canonical"]', { rel: 'canonical', href: canonicalUrl })
    upsertMetaTag('meta[property="og:title"]', { property: 'og:title', content: seo.title })
    upsertMetaTag('meta[property="og:description"]', { property: 'og:description', content: seo.description })
    upsertMetaTag('meta[property="og:url"]', { property: 'og:url', content: canonicalUrl })
    upsertMetaTag('meta[name="twitter:title"]', { name: 'twitter:title', content: seo.title })
    upsertMetaTag('meta[name="twitter:description"]', { name: 'twitter:description', content: seo.description })
  }, [path, trips])

  const login = async (role, form) => {
    try {
      const account = await api.loginUser(form.email, form.password, role)
      setSession(account)
      await refreshData(account)
      if (role === 'admin') setAdminReviews(await api.getReviews(true, account.email))
      navigate(role === 'admin' ? '/admin/dashboard' : '/tim/dashboard')
      return true
    } catch {
      return false
    }
  }

  const loginCustomer = async (form, redirectTo = '/open-trip') => {
    try {
      const account = await api.loginUser(form.email, form.password, 'customer')
      setSession(account)
      const [tripData, reviewTripData, publicReviewData, bookingData, reviewData] = await Promise.all([
        api.getTripSummaries(),
        api.getTripSummaries(true),
        api.getReviews(),
        Promise.all([
          api.getUserBookings(account.email, 'active'),
          api.getUserBookings(account.email, 'history'),
        ]).then(([activeBookings, historyBookings]) => [...activeBookings, ...historyBookings]),
        api.getUserReviews(account.email, account.id),
      ])
      setTrips(tripData)
      setReviewTrips(reviewTripData)
      setReviews(publicReviewData)
      setRegistrations(bookingData)
      setUserReviews(reviewData)
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
        bloodType: form.bloodType || '',
        heightCm: form.heightCm || '',
        weightKg: form.weightKg || '',
        shoeSize: form.shoeSize || '',
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

  const updateCustomerProfile = async (form) => {
    if (session?.role !== 'customer') return false
    const validationError = validateCustomerTripProfile(form)
    if (validationError) throw new Error(validationError)
    const account = await api.updateCustomerProfile({
      ...form,
      id: session.id,
      email: session.email,
    })
    setSession((current) => current?.id === account.id ? { ...current, ...account } : current)
    setCustomerAccounts((current) => {
      const hasAccount = current.some((item) => item.id === account.id)
      return hasAccount
        ? current.map((item) => item.id === account.id ? { ...item, ...account } : item)
        : [...current, account]
    })
    showToast(i18n.t('account.profileSaved'))
    return account
  }

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
    showToast('Akun tim berhasil dibuat.')
    return true
  }

  const updateWorkerAccount = async (form) => {
    const normalizedEmail = form.email.trim().toLowerCase()
    const exists = workerAccounts.some((item) => item.id !== form.id && item.email === normalizedEmail)
    if (exists) return false

    await api.updateUser({
      id: form.id,
      name: form.name.trim(),
      email: normalizedEmail,
      password: form.password || '',
    })
    await refreshData()
    showToast('Akun tim berhasil diperbarui.')
    return true
  }

  const deleteWorkerAccount = async (id) => {
    await api.deleteUser(id)
    await refreshData()
    showToast('Akun tim berhasil dihapus.')
  }

  const logout = () => {
    api.clearSessionToken()
    setSession(null)
    setUserReviews([])
    setReviewTrips([])
    setAdminReviews([])
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

  const submitRegistration = async (form) => {
    if (!session?.emailVerified || Number(session.id) !== Number(form.userId)) return false
    if (!['dp', 'full'].includes(form.paymentType) || !(form.paymentProof instanceof File)) return false
    const profileValidationError = validateCustomerTripProfile(session, { required: true })
    if (profileValidationError) throw new Error(profileValidationError)
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
      if (!isSharedPrivateBooking(trip) && !selectedSession.isLegacy && isPrivateSessionBooked(registrations, trip.id, selectedDate, selectedSession.id)) return false
    }
    const participantDetails = Array.isArray(form.participantDetails) && form.participantDetails.length
      ? form.participantDetails
      : [{ name: form.name, email: form.email, whatsapp: form.whatsapp, address: form.address || '', age: form.age || '', gender: form.gender || '', healthNotes: form.healthNotes || '', bloodType: form.bloodType || '', heightCm: form.heightCm || '', weightKg: form.weightKg || '', shoeSize: form.shoeSize || '' }]
    const primaryParticipant = participantDetails[0] || {}
    const tripAddons = Array.isArray(trip.addons) ? trip.addons : []
    const activeTripAddons = tripAddons.filter(isTripAddonActive)
    const selectedAddonIds = Array.isArray(form.addons)
      ? form.addons.map(Number).filter((addonId) => activeTripAddons.some((option) => Number(option.id) === addonId))
      : []
    if (Array.isArray(form.addons) && form.addons.some((addonId) => !activeTripAddons.some((option) => Number(option.id) === Number(addonId)))) {
      throw new Error('Salah satu add-on sedang tidak tersedia. Silakan kembali ke checkout dan pilih ulang add-on.')
    }
    const selectedAddonTotal = activeTripAddons
      .filter((option) => selectedAddonIds.includes(Number(option.id)))
      .reduce((total, option) => total + getAddonLineTotal(option, participantCount), 0)
    const privatePackages = isPrivateTour ? getPrivatePackages(trip, true) : []
    const selectedPackage = isPrivateTour
      ? privatePackages.find((item) => String(item.id) === String(form.selectedPackageId))
      : null
    if (isPrivateTour && privatePackages.length > 0 && !selectedPackage) return false
    const hargaPerOrang = isPrivateTour
      ? selectedPackage
        ? getPackagePricePerPerson(selectedPackage, participantCount)
        : getPrivatePricePerPerson(trip, participantCount)
      : Number(trip.price || 0)
    const subtotalTrip = participantCount * hargaPerOrang
    const totalHarga = subtotalTrip + selectedAddonTotal
    const requiredPaymentAmount = getRequiredPaymentAmount(totalHarga, form.paymentType)
    const nextItem = {
      name: form.name,
      whatsapp: form.whatsapp,
      email: form.email,
      userId: Number(session.id),
      participants: participantCount,
      participantCount,
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
      sessionName: isPrivateTour ? selectedSession.name : selectedSchedule.name,
      startTime: isPrivateTour ? selectedSession.startTime : selectedSchedule.startTime,
      endTime: isPrivateTour ? selectedSession.endTime : selectedSchedule.endTime,
      selectedPackageId: selectedPackage?.id || null,
      selectedPackageName: selectedPackage?.name || '',
      selectedPackagePrice: selectedPackage ? hargaPerOrang : 0,
      selectedPackagePricePerPerson: selectedPackage ? hargaPerOrang : 0,
      selectedPackageSubtotal: selectedPackage ? subtotalTrip : 0,
      selectedPackageDestinations: selectedPackage?.destinations || [],
      address: primaryParticipant.address || '',
      age: primaryParticipant.age || '',
      gender: primaryParticipant.gender || '',
      healthNotes: primaryParticipant.healthNotes || '',
      participantDetails,
      addons: selectedAddonIds,
      selectedAddonDetails: activeTripAddons
        .filter((option) => selectedAddonIds.includes(Number(option.id)))
        .map((option) => hydrateAddonForParticipants(option, participantCount)),
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
      paymentChannel: 'bca',
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
      bloodType: primaryParticipant.bloodType || current.bloodType || '',
      heightCm: primaryParticipant.heightCm || current.heightCm || '',
      weightKg: primaryParticipant.weightKg || current.weightKg || '',
      shoeSize: primaryParticipant.shoeSize || current.shoeSize || '',
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

  const updateTripDriveLink = async (payload) => {
    await api.updateTripDriveLink(payload)
    await refreshData()
    showToast('Link dokumentasi trip berhasil diperbarui.')
  }

  const submitReview = async (form) => {
    if (session?.role !== 'customer') return false
    const created = await api.createReview({
      tripId: Number(form.tripId),
      rating: Number(form.rating),
      content: form.content,
    })
    setUserReviews((current) => [created, ...current])
    setReviews((current) => [created, ...current])
    showToast(i18n.t('reviews.submitSuccess'))
    return true
  }

  const setReviewStatus = async (id, status) => {
    if (session?.role !== 'admin') return
    await api.updateReviewStatus(id, status, session.email)
    const [publicData, allData] = await Promise.all([api.getReviews(), api.getReviews(true, session.email)])
    setReviews(publicData)
    setAdminReviews(allData)
  }

  const saveTrip = async (trip) => {
    const imageFiles = Array.isArray(trip.imageFiles) ? trip.imageFiles : []
    const newImageIsPrimary = Boolean(trip.newImageIsPrimary)
    const tripData = { ...trip }
    delete tripData.imageFiles
    delete tripData.newImageIsPrimary
    const schedules = Array.isArray(trip.schedules)
      ? trip.schedules.map((schedule, index) => ({
        id: schedule.id || `schedule_${index + 1}`,
        name: schedule.name || schedule.sessionName || `Sesi ${index + 1}`,
        date: schedule.date || '',
        startTime: schedule.startTime || '',
        endTime: schedule.endTime || '',
        quota: Number(schedule.quota || 0),
        bookedCount: Number(schedule.bookedCount || 0),
        driveLinkUrl: schedule.driveLinkUrl || '',
        status: schedule.status || 'active',
      }))
      : []
    const sessions = Array.isArray(trip.sessions)
      ? trip.sessions.map((sessionItem, index) => ({
        id: sessionItem.id || `session_${index + 1}`,
        name: sessionItem.name || `Sesi ${index + 1}`,
        startTime: sessionItem.startTime || '',
        endTime: sessionItem.endTime || '',
        driveLinkUrl: sessionItem.driveLinkUrl || '',
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
      includeDriveLink: Boolean(trip.includeDriveLink),
    }
    let savedTrip
    if (trip.id) {
      const nextTrip = { ...normalizedTrip, id: Number(trip.id) }
      savedTrip = await api.updateTrip(nextTrip)
    } else {
      savedTrip = await api.createTrip(normalizedTrip)
    }
    try {
      for (let index = 0; index < imageFiles.length; index += 1) {
        await api.uploadTripImage(imageFiles[index], savedTrip.id, newImageIsPrimary && index === 0)
      }
    } catch (error) {
      await refreshData()
      showToast(`Data trip sudah tersimpan, tetapi gambar gagal di-upload: ${error.message}`)
      navigate(`/admin/open-trip/edit/${savedTrip.id}`)
      return false
    }
    await refreshData()
    navigate('/admin/open-trip')
  }

  const deleteTrip = async (id) => {
    await api.deleteTrip(id)
    await refreshData()
  }

  const permanentlyDeleteTrip = async (id, confirmation) => {
    if (session?.role !== 'admin') throw new Error('Akses admin diperlukan.')
    await api.permanentlyDeleteTrip(id, confirmation, session.email)
    await refreshData()
    showToast('Trip arsip dan seluruh data terkait berhasil dihapus permanen.')
  }

  const takeJob = async (id) => {
    const job = jobs.find((item) => item.id === id)
    if (!job || job.status !== 'Tersedia') return
    const workerName = session?.name || 'Worker'
    await api.takeWorkerTask(id, { workerEmail: session?.email, workerName })
    await refreshData()
    showToast('Job berhasil diambil.')
  }

  const releaseJob = async (id) => {
    await api.releaseWorkerTask(id, { workerEmail: session?.email })
    await refreshData()
    showToast('Job berhasil dicancel dan kembali tersedia.')
  }

  const updateJobStatus = async (id, status, extraFields = {}) => {
    await api.completeWorkerTask(id, { status, ...extraFields })
    await refreshData()
  }

  const props = {
    path,
    session,
    trips,
    reviewTrips,
    registrations,
    jobs,
    customerAccounts,
    workerAccounts,
    reviews,
    userReviews,
    adminReviews,
    navigate,
    login,
    loginCustomer,
    signupCustomer,
    updateCustomerProfile,
    resendVerification,
    refreshEmailVerification,
    verifyEmailOtp,
    createWorkerAccount,
    updateWorkerAccount,
    deleteWorkerAccount,
    logout,
    checkoutDraft,
    preparePayment,
    clearCheckoutDraft,
    submitRegistration,
    setRegistrationStatus,
    updateTripDriveLink,
    submitReview,
    setReviewStatus,
    saveTrip,
    deleteTrip,
    permanentlyDeleteTrip,
    takeJob,
    releaseJob,
    updateJobStatus,
    showToast,
    isSessionRestoring,
  }

  return (
    <>
      {toast && <div className="toast">{toast}</div>}
      <Suspense fallback={<div className="route-loading" role="status">Memuat halaman...</div>}>
        <RouteRenderer {...props} />
      </Suspense>
    </>
  )
}

function RouteRenderer(props) {
  const { path, session, navigate, trips, isSessionRestoring } = props
  const parts = path.split('/').filter(Boolean)
  const id = Number(parts[1] || parts[2] || 0)

  if (isSessionRestoring && (path.startsWith('/admin') || path.startsWith('/tim') || path === '/akun' || path === '/payment-confirmation' || parts[0] === 'daftar')) {
    return <div className="route-loading" role="status">Memulihkan session...</div>
  }

  if (path.startsWith('/admin') && path !== '/admin/login' && session?.role !== 'admin') {
    return <LoginPage role="admin" {...props} />
  }
  if (path.startsWith('/tim') && path !== '/tim/login' && session?.role !== 'pekerja') {
    return <LoginPage role="pekerja" {...props} />
  }

  if (path === '/' || path === '/open-trip') return <CustomerCatalog {...props} />
  if (path === '/review' || path === '/reviews') return <ReviewsPage {...props} />
  if (path.startsWith('/destinasi')) return <DestinationPage {...props} />
  if (path === '/akun') {
    if (session?.role !== 'customer') return <CustomerLoginPage afterLoginPath="/akun" {...props} />
    return <CustomerAccountPage {...props} />
  }
  if (path === '/login' || path === '/customer/login') return <CustomerLoginPage {...props} />
  if (path === '/signup' || path === '/customer/signup') return <CustomerSignupPage {...props} />
  if (path.startsWith('/verify-email')) return <EmailVerificationPage {...props} />
  if (parts[0] === 'open-trip' && id) {
    if (!trips.some((trip) => trip.id === id && Array.isArray(trip.imageUrls))) return <div className="route-loading">Memuat detail trip...</div>
    return <TripDetail tripId={id} {...props} />
  }
  if (path === '/payment-confirmation') {
    if (session?.role !== 'customer') return <CustomerLoginPage afterLoginPath="/payment-confirmation" {...props} />
    return <PaymentConfirmationPage {...props} />
  }
  if (parts[0] === 'daftar' && id) {
    if (session?.role !== 'customer') return <CustomerLoginPage afterLoginPath={`/daftar/${id}`} {...props} />
    if (!trips.some((trip) => trip.id === id && Array.isArray(trip.imageUrls))) return <div className="route-loading">Memuat checkout...</div>
    return <RegistrationPage tripId={id} {...props} />
  }
  if (path === '/admin/login') return <LoginPage role="admin" {...props} />
  if (path === '/admin') return <AdminDashboard {...props} />
  if (path === '/admin/dashboard') return <AdminDashboard {...props} />
  if (path === '/admin/reviews') return <AdminReviews {...props} />
  if (path === '/admin/open-trip') return <AdminTrips {...props} />
  if (path === '/admin/arsip-trip') return <AdminTripArchive {...props} />
  if (path === '/admin/open-trip/tambah') return <TripForm {...props} />
  if (parts[0] === 'admin' && parts[1] === 'open-trip' && parts[2] === 'edit') {
    const editTripId = Number(parts[3])
    const editTrip = trips.find((trip) => Number(trip.id) === editTripId)
    return <TripForm key={`${editTripId}-${editTrip ? 'ready' : 'loading'}-${editTrip?.updatedAt || editTrip?.addons?.length || 0}`} tripId={editTripId} {...props} />
  }
  if (path === '/admin/pendaftaran') return <AdminSchedule {...props} />
  if (path === '/admin/jadwal') return <AdminSchedule {...props} />
  if (path === '/admin/kalender-booking') return <AdminBookingCalendar {...props} />
  if (parts[0] === 'admin' && parts[1] === 'jadwal' && parts[2] === 'private-trip' && Number(parts[3])) return <AdminSchedule privateTripId={Number(parts[3])} {...props} />
  if (parts[0] === 'admin' && parts[1] === 'jadwal' && parts[2] === 'private' && Number(parts[3])) return <AdminSchedule scheduleRegistrationId={Number(parts[3])} {...props} />
  if (parts[0] === 'admin' && parts[1] === 'jadwal' && Number(parts[2])) return <AdminSchedule scheduleTripId={Number(parts[2])} scheduleId={parts[3] || ''} {...props} />
  if (parts[0] === 'admin' && parts[1] === 'arsip-trip' && parts[2] === 'private-trip' && Number(parts[3])) return <AdminSchedule privateTripId={Number(parts[3])} archivedView {...props} />
  if (parts[0] === 'admin' && parts[1] === 'arsip-trip' && parts[2] === 'private' && Number(parts[3])) return <AdminSchedule scheduleRegistrationId={Number(parts[3])} archivedView {...props} />
  if (parts[0] === 'admin' && parts[1] === 'arsip-trip' && Number(parts[2])) return <AdminSchedule scheduleTripId={Number(parts[2])} scheduleId={parts[3] || ''} archivedView {...props} />
  if (path === '/admin/tim') return <AdminWorkers {...props} />
  if (path === '/tim/login') return <LoginPage role="pekerja" {...props} />
  if (path === '/tim' || path === '/tim/dashboard') return <WorkerDashboard {...props} />
  if (path === '/tim/job') return <WorkerJobs {...props} />
  if (parts[0] === 'tim' && parts[1] === 'job' && Number(parts[2])) return <WorkerJobDetail jobId={Number(parts[2])} {...props} />
  if (path === '/tim/job-saya') return <MyJobs {...props} />

  return <NotFound navigate={navigate} />
}

export default App
