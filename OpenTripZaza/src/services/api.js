const API_BASE_URL = (import.meta.env.VITE_API_BASE_URL || '/api').replace(/\/$/, '')

const request = async (path, options = {}) => {
  const response = await fetch(`${API_BASE_URL}/${path.replace(/^\//, '')}`, options)
  let payload
  try {
    payload = await response.json()
  } catch {
    throw new Error('Server tidak mengembalikan JSON yang valid.')
  }
  if (!response.ok || !payload.success) {
    throw new Error(payload.message || `Request gagal (${response.status}).`)
  }
  return payload.data
}

const jsonPost = (path, data) => request(path, {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify(data),
})

export const getTrips = (includeAll = true) => request(`trips/index.php${includeAll ? '?all=1' : ''}`)
export const getTripSummaries = (includeAll = false) => request(`trips/index.php?summary=1${includeAll ? '&all=1' : ''}`)
export const getTripDetail = (id) => request(`trips/detail.php?id=${encodeURIComponent(id)}`)
export const createTrip = (data) => jsonPost('trips/create.php', data)
export const updateTrip = (data) => jsonPost('trips/update.php', data)
export const deleteTrip = (id) => jsonPost('trips/delete.php', { id })

export const createBooking = (data, paymentProof = null) => {
  if (paymentProof instanceof File) {
    const form = new FormData()
    form.append('booking_data', JSON.stringify(data))
    form.append('proof', paymentProof)
    return request('bookings/create.php', { method: 'POST', body: form })
  }
  return jsonPost('bookings/create.php', data)
}
export const getBookings = (archived = false) => request(`bookings/index.php${archived ? '?archived=1' : ''}`)
export const getUserBookings = (email, archived = false) => request(`bookings/user.php?email=${encodeURIComponent(email)}${archived ? '&archived=1' : ''}`)
export const getPrivateBookingAvailability = (tripId) => request(`bookings/availability.php?trip_id=${encodeURIComponent(tripId)}`)
export const updateBookingStatus = (id, status) => jsonPost('bookings/update-status.php', { id, status })

export const getReviews = (all = false, adminEmail = '') => request(`reviews/index.php${all ? `?all=1&admin_email=${encodeURIComponent(adminEmail)}` : ''}`)
export const getUserReviews = (email, userId) => request(`reviews/index.php?mine=1&email=${encodeURIComponent(email)}&user_id=${encodeURIComponent(userId)}`)
export const createReview = (data) => jsonPost('reviews/create.php', data)
export const updateReviewStatus = (id, status, adminEmail) => jsonPost('reviews/update-status.php', { id, status, adminEmail })

export const getAddons = () => request('addons/index.php')
export const getWorkerTasks = () => request('worker-tasks/index.php')
export const takeWorkerTask = (id, workerData) => jsonPost('worker-tasks/take.php', { id, ...workerData })
export const completeWorkerTask = (id, data = {}) => {
  if (data.proofPhotoFile instanceof File) {
    const form = new FormData()
    form.append('id', id)
    Object.entries(data).forEach(([key, value]) => {
      if (key === 'proofPhotoFile' || value === undefined || value === null) return
      form.append(key, typeof value === 'boolean' ? String(Number(value)) : value)
    })
    form.append('proof', data.proofPhotoFile)
    return request('worker-tasks/complete.php', { method: 'POST', body: form })
  }
  return jsonPost('worker-tasks/complete.php', { id, ...data })
}

export const uploadTripImage = (file, tripId) => {
  const form = new FormData()
  form.append('image', file)
  form.append('trip_id', tripId)
  return request('uploads/trip-image.php', { method: 'POST', body: form })
}

export const uploadPaymentProof = (file, bookingId, payment = {}) => {
  const form = new FormData()
  form.append('proof', file)
  form.append('booking_id', bookingId)
  form.append('amount', payment.amount || 0)
  form.append('payment_method', payment.paymentMethod || 'transfer')
  return request('uploads/payment-proof.php', { method: 'POST', body: form })
}

export const getUsers = (role = '') => request(`users/index.php${role ? `?role=${encodeURIComponent(role)}` : ''}`)
export const createUser = (data) => jsonPost('users/create.php', data)
export const loginUser = (email, password, role) => jsonPost('users/login.php', { email, password, role })
export const registerCustomer = (data) => jsonPost('auth/register.php', data)
export const resendEmailVerification = (email) => jsonPost('auth/resend-verification.php', { email })
export const verifyEmail = (email, otp) => jsonPost('auth/verify-email.php', { email, otp })
export const getCurrentCustomer = (email) => request(`auth/me.php?email=${encodeURIComponent(email)}`)

export { API_BASE_URL }
