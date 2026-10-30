import { newTemplateRule, templateWeekdays } from '../utils/tripTemplates'
import TripTemplatePatternPreview from './TripTemplatePatternPreview'
import './trip-templates.css'

const toggleValue = (values, value) => values.includes(value) ? values.filter((item) => item !== value) : [...values, value]

export default function TripTemplatePatternEditor({ rules, onChange, disabled }) {
  const updateRule = (index, changes) => onChange(rules.map((rule, itemIndex) => itemIndex === index ? { ...rule, ...changes } : rule))
  const updateSession = (ruleIndex, sessionIndex, field, value) => updateRule(ruleIndex, {
    sessions: rules[ruleIndex].sessions.map((session, index) => index === sessionIndex ? { ...session, [field]: value } : session),
  })

  return (
    <div className="template-pattern-editor full">
      <div className="template-pattern-heading">
        <div><h4>Pola jadwal Open Trip</h4><p>Pilih hari berulang. Tanggal dibuat otomatis mengikuti bulan paket yang dipilih.</p></div>
        <button className="outline-btn" type="button" disabled={disabled || rules.length >= 30} onClick={() => onChange([...rules, newTemplateRule()])}>+ Tambah aturan</button>
      </div>
      {rules.map((rule, ruleIndex) => (
        <fieldset className="template-pattern-rule" disabled={disabled} key={ruleIndex}>
          <legend>Aturan {ruleIndex + 1}</legend>
          <div className="template-pattern-rule-head">
            <label>Pola pengulangan
              <select value={rule.type} onChange={(event) => updateRule(ruleIndex, { type: event.target.value, weekdays: rule.weekdays?.length ? rule.weekdays : [0], occurrences: rule.occurrences?.length ? rule.occurrences : [1] })}>
                <option value="daily">Setiap hari</option>
                <option value="weekly">Setiap pekan</option>
                <option value="monthly">Hari tertentu dalam bulan</option>
              </select>
            </label>
            <button className="outline-btn danger-btn" type="button" disabled={rules.length === 1} aria-label={`Hapus aturan ${ruleIndex + 1}`} onClick={() => onChange(rules.filter((_, index) => index !== ruleIndex))}>Hapus aturan</button>
          </div>
          {rule.type !== 'daily' && (
            <fieldset className="template-choice-group">
              <legend>Hari keberangkatan</legend>
              <div className="template-choice-options">
                {[1, 2, 3, 4, 5, 6, 0].map((day) => (
                  <label className={`template-choice ${rule.weekdays.includes(day) ? 'is-selected' : ''}`} key={day}>
                    <input type="checkbox" checked={rule.weekdays.includes(day)} onChange={() => updateRule(ruleIndex, { weekdays: toggleValue(rule.weekdays, day) })} />
                    {templateWeekdays[day]}
                  </label>
                ))}
              </div>
              {rule.type === 'weekly' && <button className="template-weekend-shortcut" type="button" onClick={() => updateRule(ruleIndex, { weekdays: [6, 0] })}>Pilih weekend: Sabtu & Minggu</button>}
            </fieldset>
          )}
          {rule.type === 'monthly' && (
            <fieldset className="template-choice-group">
              <legend>Urutan kemunculan hari dalam bulan</legend>
              <div className="template-choice-options">
                {[1, 2, 3, 4, 5].map((nth) => (
                  <label className={`template-choice ${rule.occurrences.includes(nth) ? 'is-selected' : ''}`} key={nth}>
                    <input type="checkbox" checked={rule.occurrences.includes(nth)} onChange={() => updateRule(ruleIndex, { occurrences: toggleValue(rule.occurrences, nth) })} />
                    Ke-{nth}
                  </label>
                ))}
              </div>
              <p className="muted">Contoh: pilih Minggu dan ke-2 untuk hari Minggu kedua dalam bulan. Urutan ke-5 hanya dibuat jika tersedia.</p>
            </fieldset>
          )}
          <div className="template-rule-sessions">
            {rule.sessions.map((session, sessionIndex) => (
              <div className="template-rule-session" key={sessionIndex}>
                <label>Nama sesi<input required maxLength={100} value={session.name} onChange={(event) => updateSession(ruleIndex, sessionIndex, 'name', event.target.value)} /></label>
                <label>Jam mulai<input required type="time" value={session.startTime} onChange={(event) => updateSession(ruleIndex, sessionIndex, 'startTime', event.target.value)} /></label>
                <label>Jam selesai<input required type="time" value={session.endTime} onChange={(event) => updateSession(ruleIndex, sessionIndex, 'endTime', event.target.value)} /></label>
                <label>Kuota per sesi<input required type="number" min="1" max="100000" step="1" value={session.quota} onChange={(event) => updateSession(ruleIndex, sessionIndex, 'quota', event.target.value)} /></label>
                <button className="outline-btn danger-btn" type="button" aria-label={`Hapus sesi ${sessionIndex + 1} aturan ${ruleIndex + 1}`} disabled={rule.sessions.length === 1} onClick={() => updateRule(ruleIndex, { sessions: rule.sessions.filter((_, index) => index !== sessionIndex) })}>Hapus sesi</button>
              </div>
            ))}
          </div>
          <button className="outline-btn template-add-session" type="button" disabled={rule.sessions.length >= 20} onClick={() => updateRule(ruleIndex, { sessions: [...rule.sessions, { name: `Sesi ${rule.sessions.length + 1}`, startTime: '13:30', endTime: '17:00', quota: 5 }] })}>+ Tambah sesi</button>
        </fieldset>
      ))}
      <TripTemplatePatternPreview rules={rules} disabled={disabled} />
    </div>
  )
}
