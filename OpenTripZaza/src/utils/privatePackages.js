export const newPrivatePackage = (source = {}, index = 0) => ({
  id: source.id || null,
  packageCode: source.packageCode || `package_${index + 1}`,
  name: source.name || '',
  price: source.price ?? '',
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
    .map((item) => Number(item.price))
    .filter((price) => Number.isFinite(price) && price >= 0)
  return prices.length ? Math.min(...prices) : Number(trip?.price || 0)
}
