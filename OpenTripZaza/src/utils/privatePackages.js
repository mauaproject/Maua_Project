export const newPrivatePackage = (source = {}, index = 0) => ({
  id: source.id || null,
  packageCode: source.packageCode || `package_${index + 1}`,
  name: source.name || '',
  price: source.price ?? '',
  maxCustomPax: Math.max(1, Number(source.maxCustomPax) || Object.keys(source.pricePerPersonTiers || {}).length || 1),
  pricePerPersonTiers: source.pricePerPersonTiers && typeof source.pricePerPersonTiers === 'object'
    ? { ...source.pricePerPersonTiers }
    : {},
  destinations: Array.isArray(source.destinations) ? source.destinations : [],
  destinationsText: Array.isArray(source.destinations)
    ? source.destinations.join('\n')
    : source.destinationsText || '',
  description: source.description || '',
  status: source.status === 'inactive' ? 'inactive' : 'active',
})

export const getPrivatePackages = (trip, activeOnly = false) => {
  const packages = Array.isArray(trip?.privatePackages) ? trip.privatePackages : []
  const normalized = packages.map((item, index) => ({
    ...newPrivatePackage(item, index),
    id: Number(item.id) || item.id || `package_${index + 1}`,
    price: Number(item.price || 0),
  }))
  return activeOnly ? normalized.filter((item) => item.status === 'active') : normalized
}

export const getPrivatePackageStartingPrice = (trip) => {
  const prices = getPrivatePackages(trip, true)
    .flatMap((item) => {
      const tiers = Object.values(item.pricePerPersonTiers || {}).map(Number)
      return tiers.length ? tiers : [Number(item.price || 0)]
    })
    .filter((price) => Number.isFinite(price) && price >= 0)
  return prices.length ? Math.min(...prices) : Number(trip?.price || 0)
}

export const getPackagePricePerPerson = (privatePackage, participantCount) => {
  if (!privatePackage) return 0
  const tiers = privatePackage.pricePerPersonTiers || {}
  const maxCustomPax = Math.max(1, Number(privatePackage.maxCustomPax) || Object.keys(tiers).length || 1)
  const appliedPax = Math.min(Math.max(1, Number(participantCount) || 1), maxCustomPax)
  let lastPrice = Math.max(0, Number(privatePackage.price) || 0)
  for (let pax = 1; pax <= appliedPax; pax += 1) {
    const nextPrice = Number(tiers[pax] ?? tiers[String(pax)])
    if (Number.isFinite(nextPrice) && nextPrice > 0) lastPrice = nextPrice
  }
  return lastPrice
}

export const getPackagePriceRange = (privatePackage) => {
  const prices = Object.values(privatePackage?.pricePerPersonTiers || {})
    .map(Number)
    .filter((price) => Number.isFinite(price) && price > 0)
  const fallback = Number(privatePackage?.price || 0)
  return prices.length
    ? { min: Math.min(...prices), max: Math.max(...prices) }
    : { min: fallback, max: fallback }
}
