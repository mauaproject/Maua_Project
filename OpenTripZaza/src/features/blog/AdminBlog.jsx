import { useState } from 'react'
import { AdminShell } from '../../pages/AdminPage'
import { AppModal, Metric } from '../../pages/shared'
import { articleDocument, categories, categoryLabel, dateLabel, emptyPost, matchesSearch, plainText, sanitizeHtml, slugify } from './content'
import { useBlogRuntime } from './BlogRuntime'
import { BlogIcon, BlogLink } from './PublicBlog'
import RichEditor from './RichEditor'

const extraLinks = []
const statusLabel = (status) => ({ published: 'Published', draft: 'Draft', trashed: 'Sampah' })[status]
const Status = ({ status }) => <span className={`bp-status bp-status-${status}`}><span />{statusLabel(status)}</span>

export function AdminBlogList({ posts, navigate, updatePosts, onStatusChange, notify, shellProps = {} }) {
  const { logout } = useBlogRuntime()
  const [filter, setFilter] = useState('all')
  const [search, setSearch] = useState('')
  const [deleting, setDeleting] = useState(null)
  const [error, setError] = useState('')
  const [processing, setProcessing] = useState(false)
  const count = (status) => posts.filter((post) => post.status === status).length
  const filtered = posts.filter((post) => (filter === 'all' ? post.status !== 'trashed' : post.status === filter) && matchesSearch(post, search)).sort((a, b) => new Date(b.updatedAt || b.createdAt) - new Date(a.updatedAt || a.createdAt))
  const changeStatus = async (post, status) => {
    if (processing) return
    setProcessing(true)
    try {
      if (onStatusChange) await onStatusChange(post, status)
      else await updatePosts(posts.map((item) => item.id === post.id ? { ...item, status, updatedAt: new Date().toISOString() } : item))
      notify(status === 'trashed' ? 'Artikel dipindahkan ke sampah. Kamu dapat memulihkannya.' : 'Artikel dipulihkan sebagai draft.')
      setDeleting(null)
      setError('')
    } catch (error) { setError(error.message) }
    finally { setProcessing(false) }
  }
  return <AdminShell {...shellProps} title="Pengelolaan Blog" navigate={navigate} path="/admin/blog" extraLinks={extraLinks} logout={logout}>
    <div className="admin-page-stack bp-admin">
      <header className="admin-page-head bp-admin-heading"><div><p className="eyebrow">PENGELOLAAN WEB / BLOG</p><h2>Cerita baru, petualangan baru.</h2><p className="muted">Tulis, kelola, dan terbitkan artikel untuk pengunjung MAUA.</p></div><div className="bp-head-actions"><BlogLink className="outline-btn" to="/blog" navigate={navigate}><BlogIcon name="eye" size={17} />Lihat blog</BlogLink><button className="primary-btn" onClick={() => navigate('/admin/blog/new')}><BlogIcon name="plus" size={19} />Tulis artikel</button></div></header>
      <div className="bp-metrics"><Metric label="Total Artikel" value={posts.length - count('trashed')} /><Metric label="Published" value={count('published')} /><Metric label="Draft" value={count('draft')} /></div>
      <section className="bp-manage-panel">
        <div className="bp-manage-toolbar"><div className="bp-manage-tabs" role="group" aria-label="Status artikel">{[['all', 'Semua', posts.length - count('trashed')], ['published', 'Published', count('published')], ['draft', 'Draft', count('draft')], ['trashed', 'Sampah', count('trashed')]].map(([id, label, number]) => <button type="button" key={id} className={filter === id ? 'is-active' : ''} aria-pressed={filter === id} onClick={() => setFilter(id)}>{label}<span>{number}</span></button>)}</div><label className="bp-search"><BlogIcon name="search" size={18} /><input type="search" aria-label="Cari artikel admin" placeholder="Cari judul atau isi artikel..." value={search} onChange={(event) => setSearch(event.target.value)} /></label></div>
        {error && <p className="form-error" role="alert">{error}</p>}
        {filtered.length ? <div className="bp-table-scroll"><table className="bp-manage-table"><thead><tr><th>ARTIKEL</th><th>KATEGORI</th><th>STATUS</th><th>DIPERBARUI</th><th>AKSI</th></tr></thead><tbody>{filtered.map((post) => <tr key={post.id}><td><div className="bp-table-article">{post.cover ? <img src={post.thumbnail || post.cover} alt="" /> : <span className="bp-table-no-image"><BlogIcon name="image" size={23} /></span>}<div><strong>{post.idContent.title || 'Artikel tanpa judul'}</strong><span>{post.author} <span aria-hidden="true">·</span> /blog/{post.slug || 'belum-ada-url'}</span></div></div></td><td>{categoryLabel(post.category)}</td><td><Status status={post.status} /></td><td><time dateTime={post.updatedAt || post.createdAt}>{dateLabel(post.updatedAt || post.createdAt)}</time></td><td><div className="bp-row-actions">{post.status === 'trashed' ? <button className="outline-btn" disabled={processing} onClick={() => changeStatus(post, 'draft')}>Pulihkan</button> : <><button type="button" className="bp-icon-button" aria-label={`Edit ${post.idContent.title || 'artikel'}`} title="Edit artikel" onClick={() => navigate(`/admin/blog/edit/${post.id}`)}><BlogIcon name="edit" size={18} /></button>{post.status === 'published' && <BlogLink className="bp-icon-button" to={`/blog/${post.slug}`} navigate={navigate} aria-label={`Lihat ${post.idContent.title}`} title="Lihat artikel"><BlogIcon name="eye" size={18} /></BlogLink>}<button type="button" className="bp-icon-button bp-danger" aria-label={`Hapus ${post.idContent.title || 'artikel'}`} title="Pindahkan ke sampah" onClick={() => setDeleting(post)}><BlogIcon name="trash" size={17} /></button></>}</div></td></tr>)}</tbody></table></div> : <div className="bp-empty"><BlogIcon name={search ? 'search' : 'article'} size={34} /><h3>{search ? 'Artikel tidak ditemukan' : filter === 'trashed' ? 'Sampah masih kosong' : 'Belum ada artikel di sini'}</h3><p>{search ? 'Coba kata kunci lainnya.' : filter === 'draft' ? 'Simpan tulisan sebagai draft untuk melanjutkannya nanti.' : filter === 'trashed' ? 'Artikel yang dihapus dapat dipulihkan dari sini.' : 'Mulai bagikan cerita pertamamu.'}</p>{filter !== 'trashed' && <button className="outline-btn" onClick={() => navigate('/admin/blog/new')}>Tulis artikel</button>}</div>}
        <div className="bp-table-footer"><span>{filtered.length} artikel ditampilkan</span><span>Hanya artikel Published yang tampil di blog.</span></div>
      </section>
      <div className="bp-admin-tip"><BlogIcon name="article" size={21} /><p><strong>Ceritamu dimulai di sini.</strong> Simpan tulisan sebagai draft, periksa lewat Preview, lalu terbitkan saat sudah siap.</p></div>
    </div>
    <AppModal isOpen={Boolean(deleting)} title="Pindahkan artikel ke sampah?" description={`“${deleting?.idContent.title || 'Artikel tanpa judul'}” akan disembunyikan dari blog. Kamu bisa memulihkannya sebagai draft.`} confirmText={processing ? 'Menyimpan...' : 'Pindahkan ke sampah'} cancelText="Batal" variant="warning" confirmDisabled={processing} cancelDisabled={processing} onConfirm={() => changeStatus(deleting, 'trashed')} onCancel={() => { if (!processing) setDeleting(null) }} />
  </AdminShell>
}

export function AdminBlogEditor({ initialPost, initialDirty = false, posts = [], navigate, onSave, onPreview, onDirtyChange = () => {}, shellProps = {} }) {
  const { uploadImage, logout } = useBlogRuntime()
  const [post, setPost] = useState(() => initialPost || emptyPost())
  const [language, setLanguage] = useState('id')
  const [error, setError] = useState('')
  const [uploading, setUploading] = useState(false)
  const [inlineUploading, setInlineUploading] = useState(false)
  const [saving, setSaving] = useState(false)
  const busy = uploading || inlineUploading || saving
  const [publishOpen, setPublishOpen] = useState(false)
  const [saved, setSaved] = useState(!initialDirty)
  const [slugEdited, setSlugEdited] = useState(Boolean(initialPost?.slug))
  const contentKey = language === 'en' ? 'enContent' : 'idContent'
  const content = post[contentKey]
  const update = (patch) => { setPost((current) => ({ ...current, ...patch })); setSaved(false); onDirtyChange(true) }
  const updateContent = (patch) => {
    setPost((current) => ({ ...current, [contentKey]: { ...current[contentKey], ...patch }, ...(language === 'id' && 'title' in patch && !slugEdited ? { slug: slugify(patch.title) } : {}) }))
    setSaved(false)
    onDirtyChange(true)
  }
  const validate = (status) => {
    if (!post.idContent.title.trim()) return 'Isi judul artikel dalam Bahasa Indonesia terlebih dahulu.'
    if (!post.slug || post.slug !== slugify(post.slug)) return 'Isi URL artikel menggunakan huruf kecil, angka, dan tanda hubung.'
    if (posts.some((item) => item.id !== post.id && item.slug === post.slug)) return 'URL artikel sudah digunakan. Pilih URL yang berbeda.'
    if (!post.author.trim()) return 'Isi nama penulis artikel.'
    if (status === 'published') {
      if (!post.idContent.excerpt.trim()) return 'Lengkapi ringkasan artikel sebelum diterbitkan.'
      if (!plainText(post.idContent.body)) return 'Isi artikel belum ditulis.'
      if (!post.cover) return 'Tambahkan gambar sampul sebelum diterbitkan.'
      if (!post.coverAlt.trim()) return 'Tambahkan deskripsi gambar sampul.'
    }
    return ''
  }
  const save = async (status) => {
    if (busy) return
    const validation = validate(status)
    if (validation) { setError(validation); setPublishOpen(false); return }
    setSaving(true)
    try {
      const now = new Date().toISOString()
      const next = { ...post, coverWidth: Math.min(100, Math.max(10, Number(post.coverWidth) || 100)), coverAlign: ['left', 'center', 'right'].includes(post.coverAlign) ? post.coverAlign : 'center', author: post.author.trim(), status, updatedAt: now, publishedAt: status === 'published' ? post.publishedAt || now : post.publishedAt, idContent: { ...post.idContent, title: post.idContent.title.trim(), body: sanitizeHtml(post.idContent.body) }, enContent: { ...post.enContent, body: sanitizeHtml(post.enContent.body) } }
      const savedPost = await onSave(next)
      setPost(savedPost || next)
      setSaved(true)
      onDirtyChange(false)
      setError('')
      setPublishOpen(false)
    } catch (error) { setError(error.message); setPublishOpen(false) }
    finally { setSaving(false) }
  }
  const chooseCover = async (event) => {
    const file = event.target.files[0]
    if (!file) return
    setError('')
    setUploading(true)
    try { update({ cover: await uploadImage(file) }) }
    catch (error) { setError(error.message) }
    finally { setUploading(false); event.target.value = '' }
  }
  const sections = articleDocument(content.body).contents
  return <AdminShell {...shellProps} title={initialPost ? 'Edit Artikel' : 'Tulis Artikel'} navigate={navigate} path="/admin/blog/editor" extraLinks={extraLinks} logout={logout}>
    <div className="bp-admin bp-editor-page">
      <div className="bp-editor-top"><button type="button" className="bp-back-link" onClick={() => navigate('/admin/blog')}><BlogIcon name="back" size={18} />Kembali ke daftar artikel</button><span className="bp-save-indicator" role="status"><span className={saved ? 'is-saved' : ''} />{saved ? (post.updatedAt ? 'Perubahan tersimpan' : 'Artikel baru') : 'Ada perubahan belum disimpan'}</span></div>
      <header className="bp-editor-heading"><div><p className="eyebrow">PENGELOLAAN WEB / BLOG</p><h2>{initialPost ? 'Sempurnakan ceritamu.' : 'Bagikan cerita berikutnya.'}</h2></div><div className="bp-head-actions"><button type="button" className="outline-btn" disabled={busy} onClick={() => onPreview(post)}><BlogIcon name="eye" size={17} />Preview</button><button type="button" className="outline-btn" disabled={busy} onClick={() => save('draft')}>{post.status === 'published' ? 'Jadikan draft' : 'Simpan draft'}</button><button type="button" className="primary-btn" disabled={busy} onClick={() => { const validation = validate('published'); if (validation) setError(validation); else { setError(''); setPublishOpen(true) } }}>{post.status === 'published' ? 'Simpan perubahan' : 'Publish artikel'}<BlogIcon name="arrow" size={17} /></button></div></header>
      {error && <p className="form-error bp-editor-error" role="alert">{error}</p>}
      <div className="bp-editor-layout" inert={saving}>
        <div className="bp-editor-primary">
          <section className="bp-form-panel"><div className="bp-panel-top"><h3>Konten artikel</h3><div className="bp-language-tabs" role="group" aria-label="Bahasa konten"><button type="button" className={language === 'id' ? 'is-active' : ''} aria-pressed={language === 'id'} disabled={busy} onClick={() => setLanguage('id')}>Indonesia</button><button type="button" className={language === 'en' ? 'is-active' : ''} aria-pressed={language === 'en'} disabled={busy} onClick={() => setLanguage('en')}>English <span>opsional</span></button></div></div>{language === 'en' && <p className="bp-language-note">Jika judul atau isi English belum diisi, pengunjung akan melihat versi Indonesia.</p>}
            <div className="bp-fields"><label>Judul artikel {language === 'id' && <span>*</span>}<input aria-label={`Judul artikel ${language === 'id' ? 'Indonesia' : 'English'}`} type="text" maxLength={180} value={content.title} placeholder={language === 'id' ? 'Judul yang mengundang rasa penasaran...' : 'Give your story a title...'} onChange={(event) => updateContent({ title: event.target.value })} /></label><label>Ringkasan singkat<textarea aria-label={`Ringkasan ${language === 'id' ? 'Indonesia' : 'English'}`} rows={3} maxLength={300} value={content.excerpt} placeholder="Ceritakan garis besar artikel dalam 1–2 kalimat." onChange={(event) => updateContent({ excerpt: event.target.value })} /><small>{content.excerpt.length}/300 karakter · Ditampilkan di pembuka halaman artikel.</small></label><div className="bp-content-label">Isi artikel <span>*</span></div><RichEditor key={language} onBusyChange={setInlineUploading} value={content.body} onChange={(body) => updateContent({ body })} /></div>
          </section>
          <section className="bp-form-panel"><div className="bp-panel-top"><h3>Gambar sampul</h3><span>JPG, PNG, WebP · maks. 10 MB</span></div><div className="bp-cover-field">{post.cover ? <>
            <div className="bp-cover-preview"><div className="bp-cover-display"><img src={post.cover} alt={post.coverAlt || 'Pratinjau sampul'} style={{ width: `${post.coverWidth || 100}%` }} data-align={post.coverAlign || 'center'} /></div><div className="bp-cover-buttons"><label className="outline-btn">Ganti gambar<input type="file" accept="image/jpeg,image/png,image/webp" hidden onChange={chooseCover} disabled={uploading} /></label><button type="button" className="outline-btn" onClick={() => update({ cover: '' })}>Hapus</button></div></div>
            <div className="bp-cover-sizing"><div className="bp-image-size-label"><label htmlFor="bp-cover-width">Lebar sampul di halaman artikel</label><span>{post.coverWidth || 100}%</span></div><input id="bp-cover-width" aria-label="Lebar gambar sampul" type="range" min="10" max="100" step="5" value={post.coverWidth || 100} onChange={(event) => update({ coverWidth: Number(event.target.value) })} /><div className="bp-image-presets" role="group" aria-label="Ukuran sampul cepat">{[25, 50, 75, 100].map((width) => <button type="button" key={width} aria-pressed={(post.coverWidth || 100) === width} onClick={() => update({ coverWidth: width })}>{width}%</button>)}</div><div className="bp-image-position"><span>Posisi sampul</span><div role="group" aria-label="Posisi sampul">{[['left', 'Kiri'], ['center', 'Tengah'], ['right', 'Kanan']].map(([align, label]) => <button type="button" key={align} aria-label={`Sampul rata ${label.toLowerCase()}`} title={label} aria-pressed={(post.coverAlign || 'center') === align} onClick={() => update({ coverAlign: align })}><BlogIcon name={align} size={17} /></button>)}</div></div><p>Ukuran di halaman artikel mengikuti pengaturan ini. Thumbnail pada card tetap seragam.</p></div>
          </> : <label className="bp-upload-box"><BlogIcon name="image" size={34} /><strong>{uploading ? 'Menyiapkan gambar...' : 'Pilih gambar sampul'}</strong><span>Gunakan foto landscape untuk hasil terbaik.</span><input type="file" aria-label="Upload gambar sampul" accept="image/jpeg,image/png,image/webp" hidden onChange={chooseCover} disabled={uploading} /></label>}<label className="bp-cover-alt">Deskripsi gambar<input type="text" maxLength={200} value={post.coverAlt} onChange={(event) => update({ coverAlt: event.target.value })} placeholder="Contoh: Cahaya matahari masuk ke Goa Jomblang" /><small>Membantu pembaca yang memakai pembaca layar.</small></label></div></section>
        </div>
        <aside className="bp-editor-sidebar">
          <section className="bp-form-panel"><div className="bp-panel-top"><h3>Pengaturan artikel</h3></div><div className="bp-fields"><div className="bp-setting-status"><span>Status</span><Status status={post.status} /></div><label>Kategori<select aria-label="Kategori artikel" value={post.category} onChange={(event) => update({ category: event.target.value })}>{categories.map((item) => <option key={item.id} value={item.id}>{item.idLabel}</option>)}</select></label><label>Penulis<input value={post.author} maxLength={100} onChange={(event) => update({ author: event.target.value })} /></label><label>URL artikel<input aria-label="URL artikel" value={post.slug} maxLength={180} placeholder="judul-artikel" onChange={(event) => { setSlugEdited(true); update({ slug: event.target.value }) }} onBlur={() => update({ slug: slugify(post.slug) })} /><small>/blog/{post.slug || 'judul-artikel'}</small></label></div></section>
          <section className="bp-form-panel"><div className="bp-panel-top"><h3>Daftar isi otomatis</h3><span className="bp-auto-badge">OTOMATIS</span></div><div className="bp-generated-toc">{sections.length ? <ol>{sections.map((section) => <li className={section.level === 3 ? 'is-child' : ''} key={section.id}>{section.title}</li>)}</ol> : <p>Tambahkan subjudul H2 atau H3 di editor. Daftar isi akan muncul otomatis di artikel.</p>}</div></section>
          <details className="bp-form-panel bp-seo"><summary>Pengaturan pencarian <span>opsional</span></summary><div className="bp-fields"><label>Judul SEO<input value={content.seoTitle} maxLength={70} placeholder={content.title || 'Mengikuti judul artikel'} onChange={(event) => updateContent({ seoTitle: event.target.value })} /><small>{content.seoTitle.length}/70 karakter</small></label><label>Deskripsi SEO<textarea value={content.seoDescription} maxLength={160} rows={3} placeholder={content.excerpt || 'Mengikuti ringkasan artikel'} onChange={(event) => updateContent({ seoDescription: event.target.value })} /><small>{content.seoDescription.length}/160 karakter</small></label></div></details>
        </aside>
      </div>
    </div>
    <AppModal isOpen={publishOpen} title={post.status === 'published' ? 'Simpan perubahan artikel?' : 'Terbitkan artikel sekarang?'} description={`“${post.idContent.title}” akan tampil di halaman blog. Kamu bisa mengeditnya kembali kapan saja.`} confirmText={post.status === 'published' ? 'Simpan perubahan' : 'Ya, publish artikel'} cancelText="Periksa lagi" confirmDisabled={busy} cancelDisabled={busy} onConfirm={() => save('published')} onCancel={() => { if (!busy) setPublishOpen(false) }} />
  </AdminShell>
}
