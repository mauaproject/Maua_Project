export function normalizeWhatsAppNumber(phone) {
  const digits = String(phone || '').replace(/\D/g, '')
  if (!digits) return ''

  const normalized = digits.startsWith('0')
    ? `62${digits.slice(1)}`
    : digits.startsWith('8') ? `62${digits}` : digits

  if (!normalized.startsWith('62') || normalized.length < 10 || normalized.length > 15) return ''
  return normalized
}

export function formatWhatsAppDisplay(phone) {
  const normalizedPhone = normalizeWhatsAppNumber(phone)
  if (!normalizedPhone) return ''

  const localPhone = normalizedPhone.startsWith('62') ? `0${normalizedPhone.slice(2)}` : normalizedPhone
  const head = localPhone.slice(0, 4)
  const middle = localPhone.slice(4, 8)
  const tail = localPhone.slice(8).match(/.{1,4}/g) || []
  return [head, middle, ...tail].filter(Boolean).join(' ')
}

export function buildWhatsAppUrl({ phone, customerName, tripName, date, session }) {
  const normalizedPhone = normalizeWhatsAppNumber(phone)
  if (!normalizedPhone) return ''

  const bookingContext = [
    tripName ? `terkait booking ${tripName}` : 'terkait booking trip',
    date && date !== '-' ? `pada ${date}` : '',
    session ? `sesi ${session}` : '',
  ].filter(Boolean)
  const message = `Halo Kak ${customerName || 'Customer'}, saya dari tim MAUA ${bookingContext.join(' ')}. Saya ingin konfirmasi detail tripnya ya.`

  return `https://wa.me/${normalizedPhone}?text=${encodeURIComponent(message)}`
}
