export const bloodTypeOptions = ['A', 'B', 'AB', 'O', 'Tidak tahu']

export const customerTripProfileFields = ['bloodType', 'heightCm', 'weightKg', 'shoeSize']

export const isCustomerTripProfileComplete = (profile = {}) => (
  customerTripProfileFields.every((field) => profile[field] !== '' && profile[field] !== null && profile[field] !== undefined)
)

export const validateCustomerTripProfile = (profile = {}, { required = false } = {}) => {
  if (required && !isCustomerTripProfileComplete(profile)) {
    return 'Lengkapi Golongan Darah, Tinggi Badan, Berat Badan, dan Ukuran Sepatu sebelum checkout.'
  }
  if (profile.bloodType && !bloodTypeOptions.includes(profile.bloodType)) {
    return 'Golongan darah tidak valid.'
  }
  const height = profile.heightCm === '' || profile.heightCm == null ? null : Number(profile.heightCm)
  const weight = profile.weightKg === '' || profile.weightKg == null ? null : Number(profile.weightKg)
  const shoeSize = profile.shoeSize === '' || profile.shoeSize == null ? null : Number(profile.shoeSize)
  if (height !== null && (!Number.isFinite(height) || height < 50 || height > 250)) {
    return 'Tinggi badan harus antara 50 sampai 250 cm.'
  }
  if (weight !== null && (!Number.isFinite(weight) || weight < 20 || weight > 300)) {
    return 'Berat badan harus antara 20 sampai 300 kg.'
  }
  if (shoeSize !== null && (!Number.isFinite(shoeSize) || shoeSize < 20 || shoeSize > 55)) {
    return 'Ukuran sepatu harus antara 20 sampai 55.'
  }
  return ''
}
