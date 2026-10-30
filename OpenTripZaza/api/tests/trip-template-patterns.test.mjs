import { test } from 'node:test'
import assert from 'node:assert/strict'
import { readFileSync } from 'node:fs'
import { getTemplateRulesError, newTemplateRule, patternToTemplateRules } from '../../src/utils/tripTemplates.js'

const expand = (rules, year, month, editor = false) => {
  const result = []
  const occurrences = Array(7).fill(0)
  for (let day = 1; day <= new Date(Date.UTC(year, month, 0)).getUTCDate(); day++) {
    const weekday = new Date(Date.UTC(year, month - 1, day)).getUTCDay()
    const nth = ++occurrences[weekday]
    for (const rule of rules) {
      const matches = editor
        ? rule.type === 'daily' || (rule.weekdays.includes(weekday) && (rule.type === 'weekly' || rule.occurrences.includes(nth)))
        : rule.type === 'daily' || (rule.weekday === weekday && rule.nth === nth)
      if (matches) for (const session of rule.sessions) result.push([day, session.name, session.startTime, session.endTime, session.quota])
    }
  }
  return result.sort((a, b) => JSON.stringify(a).localeCompare(JSON.stringify(b)))
}

test('All five existing SQL patterns retain dates, sessions, and varying quotas when loaded into the editor', () => {
  const sql = readFileSync(new URL('../migrations/2026-08-31-trip-generation-templates.sql', import.meta.url), 'utf8')
  const patterns = [...sql.matchAll(/'(\{"codePrefix":[^\n]+?\})'/g)].map((match) => JSON.parse(match[1]))
  assert.equal(patterns.length, 5)
  for (const pattern of patterns) {
    const editor = patternToTemplateRules(pattern)
    assert.equal(getTemplateRulesError(editor), '')
    for (const [year, month] of [[2026, 11], [2027, 2], [2028, 2], [2030, 6], [2030, 12]]) {
      assert.deepEqual(expand(editor, year, month, true), expand(pattern.rules, year, month), `${pattern.codePrefix} ${year}-${month}`)
    }
  }
})

test('Weekend and selected weekdays allow independent session quotas', () => {
  const weekend = { ...newTemplateRule(), weekdays: [6, 0] }
  assert.equal(expand([weekend], 2026, 11, true).length, 9)
  const saturday = { ...newTemplateRule(), weekdays: [6], sessions: [{ name: 'Siang', startTime: '13:30', endTime: '17:00', quota: 9 }] }
  assert.equal(getTemplateRulesError([weekend, saturday]), '')
  assert.equal(expand([weekend, saturday], 2026, 11, true).length, 13)
})

test('Overlapping weekday rules are rejected even across weekly and monthly modes', () => {
  const weekly = { ...newTemplateRule(), weekdays: [1] }
  const monthly = { ...newTemplateRule(), type: 'monthly', weekdays: [1], occurrences: [2] }
  assert.notEqual(getTemplateRulesError([weekly, monthly]), '')
  assert.notEqual(getTemplateRulesError([{ ...weekly, weekdays: [] }]), '')
})

test('Unsaved PHP preview matches all existing SQL patterns across four/five weekdays and leap February', { skip: !process.env.MAUA_TEMPLATE_PREVIEW_URL }, async () => {
  const base = process.env.MAUA_TEMPLATE_PREVIEW_URL
  assert.equal(base, 'http://127.0.0.1:8124')
  const health = await (await fetch(`${base}/__template_test_health`)).json()
  assert.equal(health.database, 'maua_template_management_test')
  const sql = readFileSync(new URL('../migrations/2026-08-31-trip-generation-templates.sql', import.meta.url), 'utf8')
  const patterns = [...sql.matchAll(/'(\{"codePrefix":[^\n]+?\})'/g)].map((match) => JSON.parse(match[1]))
  for (const pattern of patterns) {
    for (const [year, month] of [[2026, 11], [2026, 8], [2027, 2], [2028, 2]]) {
      const response = await fetch(`${base}/api/trip-templates/preview.php`, {
        method: 'POST', headers: { 'Content-Type': 'application/json', Authorization: 'Bearer local-template-admin' },
        body: JSON.stringify({ year, month, scheduleRules: patternToTemplateRules(pattern) }),
      })
      assert.equal(response.status, 200, `${pattern.codePrefix} ${year}-${month}`)
      const { data } = await response.json()
      const actual = data.schedules.map((session) => [Number(session.date.slice(8)), session.name, session.startTime, session.endTime, session.quota])
        .sort((a, b) => JSON.stringify(a).localeCompare(JSON.stringify(b)))
      assert.deepEqual(actual, expand(pattern.rules, year, month), `${pattern.codePrefix} ${year}-${month}`)
    }
  }
})
