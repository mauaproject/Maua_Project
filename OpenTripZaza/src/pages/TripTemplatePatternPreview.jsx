import { useEffect, useRef, useState } from 'react'
import { previewTripTemplate } from '../services/api'
import { getTemplateRulesError } from '../utils/tripTemplates'

const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']
const years = Array.from({ length: 81 }, (_, index) => index + 2020)
const dateFormatter = new Intl.DateTimeFormat('id-ID', { weekday: 'long', day: 'numeric', month: 'short', year: 'numeric', timeZone: 'Asia/Jakarta' })

export default function TripTemplatePatternPreview({ rules, disabled }) {
  const [period, setPeriod] = useState(() => {
    const today = new Date()
    const nextMonth = new Date(today.getFullYear(), today.getMonth() + 1, 1)
    return { year: nextMonth.getFullYear(), month: nextMonth.getMonth() + 1 }
  })
  const [preview, setPreview] = useState(null)
  const requestRef = useRef(null)
  const requestKey = JSON.stringify([rules, period.year, period.month])
  const currentPreview = preview?.key === requestKey ? preview : null
  const rulesError = getTemplateRulesError(rules)

  useEffect(() => () => requestRef.current?.abort(), [])

  const loadPreview = async () => {
    if (disabled || rulesError || currentPreview?.loading) return
    requestRef.current?.abort()
    const controller = new AbortController()
    requestRef.current = controller
    setPreview({ key: requestKey, loading: true })
    try {
      const data = await previewTripTemplate({ scheduleRules: rules, ...period }, controller.signal)
      if (!controller.signal.aborted) setPreview({ key: requestKey, data })
    } catch (error) {
      if (!controller.signal.aborted) setPreview({ key: requestKey, error: error.message || 'Preview gagal dimuat. Coba kembali.' })
    }
  }

  return (
    <section className="template-pattern-preview" aria-labelledby="template-preview-title">
      <div className="template-pattern-heading">
        <div><h4 id="template-preview-title">Preview jadwal template</h4><p>Pilih bulan contoh untuk memeriksa hasil pola. Periode paket ditentukan saat membuat paket.</p></div>
      </div>
      <div className="template-preview-controls">
        <label>Bulan contoh<select disabled={disabled} value={period.month} onChange={(event) => setPeriod({ ...period, month: Number(event.target.value) })}>{months.map((month, index) => <option value={index + 1} key={month}>{month}</option>)}</select></label>
        <label>Tahun contoh<select disabled={disabled} value={period.year} onChange={(event) => setPeriod({ ...period, year: Number(event.target.value) })}>{years.map((year) => <option value={year} key={year}>{year}</option>)}</select></label>
        <button className="outline-btn" type="button" disabled={disabled || Boolean(rulesError) || currentPreview?.loading} onClick={loadPreview}>{currentPreview?.loading ? 'Menghitung jadwal...' : 'Lihat Preview'}</button>
      </div>
      {rulesError ? <p className="template-preview-hint">{rulesError}</p> : !currentPreview && <p className="template-preview-hint">{preview ? 'Pola atau bulan contoh berubah. Klik Lihat Preview untuk memperbarui hasil.' : 'Klik Lihat Preview untuk melihat tanggal, sesi, dan kuota sebelum menyimpan template.'}</p>}
      {currentPreview?.loading && <p className="template-preview-hint" role="status">Menghitung tanggal dan sesi...</p>}
      {currentPreview?.error && <p className="form-error" role="alert">{currentPreview.error}</p>}
      {currentPreview?.data && !rulesError && (
        <>
          <div className="template-preview-summary" role="status"><strong>{currentPreview.data.period.label}</strong><span>{currentPreview.data.dateCount} tanggal · {currentPreview.data.sessionCount} sesi · Total kuota seluruh sesi: {currentPreview.data.totalQuota}</span></div>
          <ol className="template-preview-rule-summary">
            {currentPreview.data.rules.map((rule) => <li key={rule.number}><div><strong>Aturan {rule.number}: {rule.label}</strong><span>{rule.dateCount} tanggal · {rule.sessionCount} sesi</span></div>{rule.notice && <p className="template-preview-notice">{rule.notice}</p>}</li>)}
          </ol>
          {!currentPreview.data.schedules.length ? <p className="template-manager-note">Tidak ada jadwal pada bulan contoh ini. Pola tetap dapat digunakan pada bulan yang memiliki hari pilihan tersebut.</p> : (
            <div className="template-preview-table-wrap" tabIndex={0} role="region" aria-label="Hasil preview jadwal Open Trip">
              <table className="template-preview-table">
                <caption>Jadwal contoh {currentPreview.data.period.label}</caption>
                <thead><tr><th scope="col">Tanggal</th><th scope="col">Sesi</th><th scope="col">Jam (WIB)</th><th scope="col">Kuota</th></tr></thead>
                <tbody>{currentPreview.data.schedules.map((schedule) => <tr key={schedule.id}><td>{dateFormatter.format(new Date(`${schedule.date}T00:00:00+07:00`))}</td><td>{schedule.name}</td><td>{schedule.startTime} - {schedule.endTime}</td><td>{schedule.quota}</td></tr>)}</tbody>
              </table>
            </div>
          )}
        </>
      )}
    </section>
  )
}
