import { useState } from 'react'
import { createRoot } from 'react-dom/client'
import '../index.css'
import '../App.css'
import '../i18n'
import { TripForm } from '../pages/AdminPage'
import { AppModal } from '../pages/shared'
import './TripFormPreview.css'

const exampleTrip = {
  name: 'Eksplor Goa — Contoh',
  destination: { id: 'Gunungkidul, Yogyakarta', en: '' },
  description: { id: 'Nikmati pengalaman menjelajahi goa bersama pemandu lokal, mulai dari briefing hingga eksplorasi dan dokumentasi perjalanan.', en: '' },
  activities: { id: ['Briefing keselamatan', 'Eksplorasi goa bersama pemandu', 'Sesi foto dan istirahat'], en: [] },
  facilities: { id: ['Pemandu lokal', 'Peralatan keselamatan', 'Air mineral'], en: [] },
  price: 250000,
  minParticipants: 1,
  maxParticipants: 10,
  maxCustomPax: 3,
  pricePerPersonTiers: { 1: 450000, 2: 350000, 3: 300000 },
  availableStartDate: '2026-11-01',
  availableEndDate: '2026-11-30',
  schedules: [{ id: 'schedule_1', name: 'Sesi pagi', date: '2026-11-08', startTime: '08:00', endTime: '12:00', quota: 10, status: 'active' }],
  sessions: [{ id: 'session_1', name: 'Sesi pagi', startTime: '08:00', endTime: '12:00', status: 'active' }],
  addons: [{ name: 'Transportasi — Contoh', price: 500000, maxParticipantsPerUnit: 5, status: 'active', workerAction: 'none' }],
}

export default function TripFormPreview() {
  const [notice, setNotice] = useState(null)
  const [example, setExample] = useState('empty')

  return <>
    <TripForm
      key={example}
      trips={[]}
      initialValues={example === 'empty' ? undefined : { ...exampleTrip, isPrivateTrip: example === 'private' }}
      registrations={[]}
      path="/admin/open-trip/new"
      navigate={() => setNotice({ title: 'Pratinjau tambah trip', description: 'Halaman ini khusus untuk mencoba form. Gunakan enam langkah di atas untuk menilai alur Open Trip dan Private Trip.' })}
      logout={() => setNotice({ title: 'Pratinjau lokal', description: 'Tidak ada akun yang sedang login di halaman pratinjau ini.' })}
      saveTrip={async (trip) => {
        setNotice({ title: 'Semua langkah sudah lengkap', description: `${trip.name} (${trip.isPrivateTrip ? 'Private Trip' : 'Open Trip'}) berhasil melewati pemeriksaan form. Ini pratinjau lokal; data tidak dikirim ke database.` })
        return true
      }}
    >
      <div className="trip-preview-toolbar"><div><strong>Pratinjau lokal</strong><span>Coba data contoh untuk meninjau setiap langkah.</span></div><div className="trip-preview-switch" role="group" aria-label="Pilih contoh form"><button type="button" aria-pressed={example === 'empty'} onClick={() => setExample('empty')}>Form kosong</button><button type="button" aria-pressed={example === 'open'} onClick={() => setExample('open')}>Contoh Open Trip</button><button type="button" aria-pressed={example === 'private'} onClick={() => setExample('private')}>Contoh Private Trip</button></div></div>
    </TripForm>
    <AppModal isOpen={Boolean(notice)} title={notice?.title} description={notice?.description} confirmText="Lanjut meninjau" cancelText="Tutup" onConfirm={() => setNotice(null)} onCancel={() => setNotice(null)} />
  </>
}

createRoot(document.getElementById('root')).render(<TripFormPreview />)
