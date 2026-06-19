export const DP_PERCENTAGE = 0.5
export const MAX_PAYMENT_PROOF_SIZE = 5 * 1024 * 1024

export const getRequiredPaymentAmount = (totalPrice, paymentType) => {
  const normalizedTotal = Math.max(0, Number(totalPrice) || 0)
  return paymentType === 'full'
    ? normalizedTotal
    : Math.round(normalizedTotal * DP_PERCENTAGE)
}

export const getPaymentTypeLabel = (paymentType) => paymentType === 'full' ? 'Lunas' : 'DP 50%'
export const getPaymentStatusLabel = (paymentStatus) => ({
  waiting_verification: 'Menunggu verifikasi',
  verified: 'Terverifikasi',
  rejected: 'Ditolak',
  submitted: 'Terkirim',
}[paymentStatus] || paymentStatus || '-')

export const validatePaymentProof = (file) => {
  if (!(file instanceof File)) return 'Bukti pembayaran wajib diunggah.'
  if (file.size > MAX_PAYMENT_PROOF_SIZE) return 'Ukuran bukti pembayaran maksimal 5MB.'
  if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
    return 'Format bukti pembayaran harus JPG, JPEG, PNG, atau WebP.'
  }
  return ''
}
