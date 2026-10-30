import { useEffect, useState } from 'react'
import * as api from '../services/api'
import { patternToTemplateRules } from '../utils/tripTemplates'
import { AdminShell, TripForm } from './AdminPage'
import { AppModal } from './shared'
import './trip-templates.css'

const TEMPLATE_PATH = '/admin/open-trip/template'

export default function AdminTripTemplates(props) {
  const { path, navigate, showToast } = props
  const isCreate = path === `${TEMPLATE_PATH}/tambah`
  const templateId = Number(path.match(/\/template\/edit\/(\d+)$/)?.[1] || 0)
  const [templates, setTemplates] = useState([])
  const [detail, setDetail] = useState(null)
  const [isLoading, setIsLoading] = useState(true)
  const [loadError, setLoadError] = useState('')
  const [reload, setReload] = useState(0)
  const [search, setSearch] = useState('')
  const [type, setType] = useState('all')
  const [pendingDelete, setPendingDelete] = useState(null)
  const [isDeleting, setIsDeleting] = useState(false)
  const [deleteError, setDeleteError] = useState('')

  useEffect(() => {
    if (isCreate) return undefined
    const controller = new AbortController()
    Promise.resolve().then(() => {
      if (controller.signal.aborted) return null
      setIsLoading(true)
      setLoadError('')
      return templateId ? api.getTripTemplateDetail(templateId, controller.signal) : api.getManagedTripTemplates(controller.signal)
    }).then((result) => {
      if (controller.signal.aborted || !result) return
      if (templateId) setDetail(result)
      else setTemplates(result.templates)
    }).catch((error) => {
      if (!controller.signal.aborted) setLoadError(error.message || 'Template gagal dimuat.')
    }).finally(() => {
      if (!controller.signal.aborted) setIsLoading(false)
    })
    return () => controller.abort()
  }, [isCreate, templateId, reload])

  useEffect(() => {
    if (!pendingDelete) return undefined
    const previouslyFocused = document.activeElement
    document.querySelector('.app-modal .app-modal-actions button')?.focus()
    return () => previouslyFocused?.focus()
  }, [pendingDelete])

  useEffect(() => {
    if (!pendingDelete) return undefined
    const cancelOnEscape = (event) => {
      if (event.key === 'Escape' && !isDeleting) setPendingDelete(null)
      if (event.key === 'Tab') {
        const buttons = [...document.querySelectorAll('.app-modal button:not(:disabled)')]
        if (!buttons.length) { event.preventDefault(); return }
        const first = buttons[0]
        const last = buttons[buttons.length - 1]
        if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus() }
        else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus() }
      }
    }
    window.addEventListener('keydown', cancelOnEscape)
    return () => window.removeEventListener('keydown', cancelOnEscape)
  }, [pendingDelete, isDeleting])

  const saveTemplate = async (values) => {
    const { imageFiles = [], newImageIsPrimary, ...data } = values
    const saved = await api.saveTripTemplate({
      ...data,
      ...(templateId ? { templateId, expectedSourceTripId: detail.sourceTripId } : {}),
    })
    try {
      for (let index = 0; index < imageFiles.length; index++) {
        await api.uploadTripTemplateImage(imageFiles[index], saved.id, saved.sourceTripId, newImageIsPrimary && index === 0)
      }
    } catch (error) {
      showToast(`Template tersimpan, tetapi gambar gagal diunggah: ${error.message}`)
      setReload((value) => value + 1)
      navigate(`${TEMPLATE_PATH}/edit/${saved.id}`)
      return
    }
    showToast(`Template ${saved.name} berhasil disimpan.`)
    navigate(TEMPLATE_PATH)
  }

  const confirmDelete = async () => {
    if (!pendingDelete || isDeleting) return
    setIsDeleting(true)
    setDeleteError('')
    try {
      await api.deleteTripTemplate(pendingDelete.id, pendingDelete.sourceTripId)
      setTemplates((current) => current.filter((template) => template.id !== pendingDelete.id))
      showToast(`Template ${pendingDelete.name} berhasil dihapus.`)
      setPendingDelete(null)
    } catch (error) {
      setDeleteError(error.message || 'Template gagal dihapus. Silakan coba kembali.')
    } finally {
      setIsDeleting(false)
    }
  }

  if (isCreate || (templateId && !isLoading && !loadError && detail?.id === templateId)) {
    const initialValues = templateId ? { ...detail.trip, scheduleRules: patternToTemplateRules(detail.pattern) } : undefined
    return <TripForm key={templateId ? `${templateId}-${detail.sourceTripId}` : 'new-template'} {...props} initialValues={initialValues} formMode="template" saveTrip={saveTemplate} />
  }

  const query = search.trim().toLocaleLowerCase('id')
  const filtered = templates.filter((template) => (type === 'all' || template.type === type)
    && `${template.name} ${template.patternLabel}`.toLocaleLowerCase('id').includes(query))

  return (
    <AdminShell title="" {...props}>
      <section className="admin-page-stack template-manager-page">
        <div className="admin-page-head">
          <div>
            <p className="eyebrow">Paket Trip</p>
            <h2>Kelola Template</h2>
            <p className="muted">Simpan informasi trip untuk mempercepat pembuatan paket berikutnya.</p>
          </div>
          <div className="template-page-actions">
            <button className="outline-btn" type="button" onClick={() => navigate('/admin/open-trip')}>Kembali ke paket</button>
            <button className="primary-btn" type="button" onClick={() => navigate(`${TEMPLATE_PATH}/tambah`)}>+ Tambah Template</button>
          </div>
        </div>
        <p className="template-manager-note">Tambah, edit, dan hapus template tidak mengubah paket yang sudah dibuat, termasuk jadwal dan booking-nya.</p>
        {loadError ? (
          <div className="empty-state" role="alert"><p>{loadError}</p><button className="outline-btn" type="button" onClick={() => setReload((value) => value + 1)}>Coba lagi</button></div>
        ) : isLoading || templateId ? (
          <p className="empty-state" role="status">Memuat {templateId ? 'detail' : 'daftar'} template...</p>
        ) : (
          <>
            <div className="admin-list-toolbar template-manager-toolbar">
              <label className="admin-search-field"><span>Cari template</span><input type="search" placeholder="Nama template atau pola jadwal" value={search} onChange={(event) => setSearch(event.target.value)} /></label>
              <label>Jenis trip<select value={type} onChange={(event) => setType(event.target.value)}><option value="all">Semua jenis</option><option value="open">Open Trip</option><option value="private">Private Trip</option></select></label>
            </div>
            <p className="template-manager-count" role="status">{filtered.length} dari {templates.length} template</p>
            {!filtered.length ? <p className="empty-state">{templates.length ? 'Tidak ada template yang cocok dengan pencarian.' : 'Belum ada template. Tambahkan template pertama untuk memulai.'}</p> : (
              <ul className="template-list" aria-label="Daftar template trip">
                {filtered.map((template) => (
                  <li key={template.id}>
                    <article className="template-row">
                      <div><span className={`template-type ${template.type === 'private' ? 'is-private' : ''}`}>{template.type === 'private' ? 'Private Trip' : 'Open Trip'}</span><h3>{template.name}</h3></div>
                      <div className="template-row-pattern"><span>Pola jadwal</span><p>{template.patternLabel}</p></div>
                      <div className="template-row-actions">
                        <button className="outline-btn" type="button" aria-label={`Edit template ${template.name} (${template.type === 'private' ? 'Private Trip' : 'Open Trip'})`} onClick={() => navigate(`${TEMPLATE_PATH}/edit/${template.id}`)}>Edit</button>
                        <button className="outline-btn danger-btn" type="button" aria-label={`Hapus template ${template.name} (${template.type === 'private' ? 'Private Trip' : 'Open Trip'})`} onClick={() => { setDeleteError(''); setPendingDelete(template) }}>Hapus</button>
                      </div>
                    </article>
                  </li>
                ))}
              </ul>
            )}
          </>
        )}
        <AppModal isOpen={Boolean(pendingDelete)} title={`Hapus template ${pendingDelete?.name || ''}?`}
          description="Template akan dihapus dari daftar dan pilihan pembuatan paket. Paket yang sudah dibuat beserta jadwal dan booking-nya tetap tersedia."
          confirmText={isDeleting ? 'Menghapus...' : 'Hapus Template'} cancelText="Batal" variant="danger"
          confirmDisabled={isDeleting} cancelDisabled={isDeleting} onConfirm={confirmDelete}
          onCancel={() => { if (!isDeleting) setPendingDelete(null) }}>
          {deleteError && <p className="form-error" role="alert">{deleteError}</p>}
        </AppModal>
      </section>
    </AdminShell>
  )
}
