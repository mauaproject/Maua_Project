export const templateWeekdays = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']

export const newTemplateRule = () => ({
  type: 'weekly',
  weekdays: [0],
  occurrences: [1],
  sessions: [{ name: 'Sesi Pagi', startTime: '08:00', endTime: '13:00', quota: 5 }],
})

export const patternToTemplateRules = (pattern) => {
  if (!Array.isArray(pattern?.rules) || !pattern.rules.length) return [newTemplateRule()]
  const groups = new Map()
  for (const rule of pattern.rules) {
    const sessions = (rule.sessions || []).map(({ name, startTime, endTime, quota }) => ({ name, startTime, endTime, quota }))
    const key = JSON.stringify([rule.type, rule.weekday, sessions])
    if (!groups.has(key)) groups.set(key, { type: rule.type === 'daily' ? 'daily' : 'monthly', weekdays: rule.type === 'daily' ? [] : [rule.weekday], occurrences: [], sessions })
    if (rule.type === 'nth_weekday') groups.get(key).occurrences.push(rule.nth)
  }
  const merged = new Map()
  for (const group of groups.values()) {
    group.occurrences = [...new Set(group.occurrences)].sort((a, b) => a - b)
    if (group.occurrences.join(',') === '1,2,3,4,5') group.type = 'weekly'
    const key = JSON.stringify([group.type, group.occurrences, group.sessions])
    if (!merged.has(key)) merged.set(key, { ...group, weekdays: [] })
    merged.get(key).weekdays.push(...group.weekdays)
  }
  return [...merged.values()].map((rule) => ({ ...rule, weekdays: [...new Set(rule.weekdays)] }))
}

export const getTemplateRulesError = (rules) => {
  if (!Array.isArray(rules) || !rules.length || rules.length > 30) return 'Isi 1 sampai 30 aturan jadwal.'
  const occupied = new Set()
  for (const rule of rules) {
    if (!['daily', 'weekly', 'monthly'].includes(rule.type)) return 'Pilih pola jadwal yang valid.'
    const weekdays = rule.type === 'daily' ? [0, 1, 2, 3, 4, 5, 6] : rule.weekdays
    const occurrences = rule.type === 'monthly' ? rule.occurrences : [1, 2, 3, 4, 5]
    if (!weekdays?.length || weekdays.some((day) => !Number.isInteger(day) || day < 0 || day > 6)) return 'Pilih minimal satu hari pada setiap aturan.'
    if (!occurrences?.length || occurrences.some((nth) => !Number.isInteger(nth) || nth < 1 || nth > 5)) return 'Pilih urutan kemunculan hari dalam bulan.'
    if (!rule.sessions?.length || rule.sessions.length > 20) return 'Setiap aturan memerlukan 1 sampai 20 sesi.'
    for (const session of rule.sessions) {
      if (!session.name?.trim() || !/^(?:[01]\d|2[0-3]):[0-5]\d$/.test(session.startTime)
        || !/^(?:[01]\d|2[0-3]):[0-5]\d$/.test(session.endTime) || session.endTime <= session.startTime
        || !Number.isInteger(Number(session.quota)) || Number(session.quota) < 1 || Number(session.quota) > 100000) {
        return 'Lengkapi nama, jam mulai/selesai, dan kuota positif pada setiap sesi.'
      }
      for (const day of weekdays) {
        for (const nth of occurrences) {
          const key = `${day}|${nth}|${session.startTime}|${session.endTime}`
          if (occupied.has(key)) return 'Ada aturan dengan hari dan jam sesi yang sama. Pisahkan pilihan hari atau urutannya.'
          occupied.add(key)
        }
      }
    }
  }
  return ''
}
