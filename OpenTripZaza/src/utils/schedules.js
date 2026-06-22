const fullScheduleStatuses = ['full', 'penuh']
const inactiveScheduleStatuses = ['inactive', 'ditutup', 'selesai']
const approvedStatuses = ['approved', 'disetujui', 'selesai']
const privateBlockingStatuses = ['pending', 'menunggu approval', 'approved', 'disetujui']

const normalized = (value) => String(value || '').trim().toLowerCase()

const jakartaNowValue = () => {
  const parts = new Intl.DateTimeFormat('en-CA', {
    timeZone: 'Asia/Jakarta',
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
    hourCycle: 'h23',
  }).formatToParts(new Date())
  const value = Object.fromEntries(parts.map((part) => [part.type, part.value]))
  return `${value.year}-${value.month}-${value.day}T${value.hour}:${value.minute}:${value.second}`
}

const scheduleEndValue = (date, endTime) => `${date || ''}T${endTime || '23:59:59'}`

export const getJakartaToday = () => jakartaNowValue().slice(0, 10)

export const isScheduleUpcoming = (date, endTime) => Boolean(date) && scheduleEndValue(date, endTime) > jakartaNowValue()

export const getRegistrationDate = (registration) => registration?.selectedDate || registration?.requestedDate || ''

export const isApprovedRegistration = (registration) => approvedStatuses.includes(normalized(registration?.status))

export const isPrivateBlockingRegistration = (registration) => privateBlockingStatuses.includes(normalized(registration?.status))

export const isSharedPrivateBooking = (trip) => trip?.privateBookingMode === 'shared'

export function getPrivateDateRange(trip) {
  return {
    startDate: trip?.availableStartDate || trip?.privateStartDate || '',
    endDate: trip?.availableEndDate || trip?.privateEndDate || '',
  }
}

export function isDateWithinPrivateRange(trip, selectedDate) {
  if (!selectedDate) return false
  const { startDate, endDate } = getPrivateDateRange(trip)
  const today = getJakartaToday()
  if (selectedDate < today) return false
  if (startDate && selectedDate < startDate) return false
  if (endDate && selectedDate > endDate) return false
  return true
}

export function normalizeScheduleStatus(status, fallback = 'active') {
  const value = normalized(status || fallback)
  if (fullScheduleStatuses.includes(value)) return 'full'
  if (inactiveScheduleStatuses.includes(value)) return 'inactive'
  return 'active'
}

export function scheduleStatusLabel(status) {
  const normalizedStatus = normalizeScheduleStatus(status)
  if (normalizedStatus === 'full') return 'Penuh'
  if (normalizedStatus === 'inactive') return 'Nonaktif'
  return 'Tersedia'
}

export function getTripSchedules(trip) {
  if (Array.isArray(trip?.schedules) && trip.schedules.length) {
    return trip.schedules.map((schedule, index) => ({
      id: schedule.id || `schedule_${index + 1}`,
      name: schedule.name || schedule.sessionName || `Sesi ${index + 1}`,
      date: schedule.date || '',
      startTime: schedule.startTime || '',
      endTime: schedule.endTime || '',
      visibleUntil: schedule.visibleUntil || '',
      isArchived: Boolean(schedule.isArchived),
      lifecycleStatus: schedule.lifecycleStatus || (isScheduleUpcoming(schedule.date, schedule.endTime) ? 'upcoming' : 'completed'),
      isBookable: schedule.isBookable !== false,
      quota: Number(schedule.quota || trip?.quota || 0),
      bookedCount: Number(schedule.bookedCount || 0),
      status: schedule.isArchived ? 'inactive' : normalizeScheduleStatus(schedule.status),
    }))
  }

  if (!trip?.date && !trip?.quota) return []
  const status = trip?.status === 'Penuh' ? 'full' : trip?.status === 'Ditutup' || trip?.status === 'Selesai' ? 'inactive' : 'active'
  return [{
    id: 'legacy_date',
    name: 'Sesi 1',
    date: trip?.date || '',
    startTime: '',
    endTime: '',
    quota: Number(trip?.quota || trip?.slots || 0),
    bookedCount: 0,
    status,
  }]
}

export function getTripSessions(trip) {
  if (Array.isArray(trip?.sessions) && trip.sessions.length) {
    return trip.sessions.map((session, index) => ({
      id: session.id || `session_${index + 1}`,
      name: session.name || `Sesi ${index + 1}`,
      startTime: session.startTime || '',
      endTime: session.endTime || '',
      status: normalizeScheduleStatus(session.status),
    }))
  }

  return [{
    id: 'legacy_session',
    name: 'Sesi fleksibel',
    startTime: '',
    endTime: '',
    status: 'active',
    isLegacy: true,
  }]
}

export function isSameScheduleRegistration(registration, schedule) {
  if (!registration || !schedule) return false
  if (registration.scheduleId) return registration.scheduleId === schedule.id
  const registrationDate = getRegistrationDate(registration)
  if (!registrationDate && schedule.id === 'legacy_date') return true
  return registrationDate === schedule.date
}

export function getScheduleBookedCount(registrations = [], tripId, schedule) {
  return registrations
    .filter((registration) => Number(registration.tripId) === Number(tripId))
    .filter(isApprovedRegistration)
    .filter((registration) => isSameScheduleRegistration(registration, schedule))
    .reduce((total, registration) => total + Number(registration.participants || registration.participantCount || 0), 0)
}

export function getScheduleAvailability(trip, registrations, schedule) {
  const bookedCount = Math.max(
    getScheduleBookedCount(registrations, trip?.id, schedule),
    Number(schedule?.bookedCount || 0),
  )
  const quota = Number(schedule?.quota || 0)
  const status = normalizeScheduleStatus(schedule?.status)
  const remaining = Math.max(quota - bookedCount, 0)
  const isFull = status === 'full' || remaining <= 0
  return {
    ...schedule,
    bookedCount,
    quota,
    remaining,
    status: isFull ? 'full' : status,
    isSelectable: status === 'active' && remaining > 0,
  }
}

export function getOpenTripScheduleOptions(trip, registrations = []) {
  return getTripSchedules(trip)
    .filter((schedule) => schedule.lifecycleStatus === 'upcoming' && schedule.isBookable !== false)
    .map((schedule) => getScheduleAvailability(trip, registrations, schedule))
}

export function isPrivateSessionBooked(registrations = [], tripId, selectedDate, sessionId) {
  if (!selectedDate || !sessionId) return false
  return registrations
    .filter((registration) => Number(registration.tripId) === Number(tripId))
    .filter((registration) => registration.isPrivateTrip || registration.isPrivateTour || registration.tripType === 'private')
    .filter(isPrivateBlockingRegistration)
    .some((registration) => getRegistrationDate(registration) === selectedDate && registration.sessionId === sessionId)
}

export function getPrivateSessionOptions(trip, registrations = [], selectedDate) {
  return getTripSessions(trip).map((session) => {
    const booked = !isSharedPrivateBooking(trip)
      && !session.isLegacy
      && isPrivateSessionBooked(registrations, trip?.id, selectedDate, session.id)
    const status = normalizeScheduleStatus(session.status)
    return {
      ...session,
      isBooked: booked,
      isSelectable: status === 'active' && !booked && isScheduleUpcoming(selectedDate, session.endTime),
    }
  })
}

export function hasScheduleRegistrations(registrations = [], tripId, schedule) {
  return registrations
    .filter((registration) => Number(registration.tripId) === Number(tripId))
    .some((registration) => isSameScheduleRegistration(registration, schedule))
}
