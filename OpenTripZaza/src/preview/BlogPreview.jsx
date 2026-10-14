import { useCallback, useEffect, useState } from 'react'
import { createRoot } from 'react-dom/client'
import { useTranslation } from 'react-i18next'
import '../index.css'
import '../App.css'
import '../i18n'
import { AppModal } from '../pages/shared'
import { AdminBlogEditor, AdminBlogList } from './blog/AdminBlog'
import { BlogArticle, BlogCatalog, BlogIcon, BlogLink, PublicBlogFrame } from './blog/PublicBlog'
import { contentFor, imageFromFile, initialPosts, loadPosts, previewUrl, savePosts } from './blog/model'
import { BlogRuntimeProvider } from '../features/blog/BlogRuntime'
import './blog/BlogPreview.css'

function readRecords() {
  try { return { posts: loadPosts(), error: '' } }
  catch (error) { return { posts: initialPosts, error: error.message } }
}
const readRoute = () => window.location.search

function BlogPreview() {
  const { i18n } = useTranslation()
  const [records] = useState(readRecords)
  const [posts, setPosts] = useState(records.posts)
  const [route, setRoute] = useState(readRoute)
  const [notice, setNotice] = useState('')
  const [dirty, setDirty] = useState(false)
  const [pendingPath, setPendingPath] = useState('')
  const [editorDraft, setEditorDraft] = useState(null)
  const params = new URLSearchParams(route)
  const page = params.get('page') || 'admin'
  const editingId = params.get('id')
  const activePost = posts.find((post) => post.slug === params.get('slug') && post.status === 'published')
  const notify = useCallback((message) => setNotice(message), [])
  const go = (path, replace = false) => {
    const url = previewUrl(path)
    if (!url.startsWith('/blog-preview.html')) { window.location.assign(url); return }
    window.history[replace ? 'replaceState' : 'pushState']({}, '', url)
    setRoute(window.location.search)
    window.scrollTo({ top: 0, behavior: 'instant' })
  }
  const navigate = (path) => {
    if (dirty) { setPendingPath(path); return }
    setEditorDraft(null)
    go(path)
  }
  const updatePosts = (next) => {
    if (records.error) throw new Error('Penyimpanan awal tidak dapat dibaca. Data lama tetap disimpan; penyimpanan baru dinonaktifkan.')
    savePosts(next)
    setPosts(next)
  }
  useEffect(() => {
    const pop = () => { setRoute(readRoute()); setDirty(false) }
    window.addEventListener('popstate', pop)
    return () => window.removeEventListener('popstate', pop)
  }, [])
  useEffect(() => {
    const warn = (event) => { if (dirty) { event.preventDefault(); event.returnValue = '' } }
    window.addEventListener('beforeunload', warn)
    return () => window.removeEventListener('beforeunload', warn)
  }, [dirty])
  useEffect(() => {
    if (!notice) return
    const timer = window.setTimeout(() => setNotice(''), 5000)
    return () => window.clearTimeout(timer)
  }, [notice])
  useEffect(() => {
    const lang = i18n.language.startsWith('en') ? 'en' : 'id'
    const post = page === 'preview' ? editorDraft : activePost
    const content = post ? contentFor(post, lang) : null
    window.document.title = content ? `${content.seoTitle || content.title} | MAUA Journal` : page === 'blog' ? 'Blog & Cerita Perjalanan | MAUA Project' : 'Pengelolaan Blog — Pratinjau MAUA'
    window.document.querySelector('meta[name="description"]')?.remove()
    const description = window.document.createElement('meta')
    description.name = 'description'
    description.content = content?.seoDescription || content?.excerpt || 'Pratinjau lokal fitur blog MAUA Project.'
    window.document.head.appendChild(description)
  }, [page, activePost, editorDraft, i18n.language])

  const notFound = <PublicBlogFrame navigate={navigate}><main className="bp-container bp-empty bp-not-found"><BlogIcon name="article" size={40} /><h1>{page === 'preview' ? 'Pratinjau belum tersedia' : 'Artikel tidak ditemukan'}</h1><p>{page === 'preview' ? 'Buka editor artikel lalu pilih Preview untuk melihat tulisanmu.' : 'Artikel mungkin masih berupa draft atau sudah dipindahkan ke sampah.'}</p><BlogLink className="outline-btn" to={page === 'preview' ? '/admin/blog' : '/blog'} navigate={navigate}>{page === 'preview' ? 'Buka pengelolaan blog' : 'Kembali ke blog'}</BlogLink></main></PublicBlogFrame>
  let screen
  if (page === 'blog') screen = <BlogCatalog key={route} posts={posts} navigate={navigate} initialSearch={params.get('search') || ''} />
  else if (page === 'article') screen = activePost ? <BlogArticle key={activePost.id} post={activePost} posts={posts} navigate={navigate} notify={notify} /> : notFound
  else if (page === 'preview') screen = editorDraft ? <BlogArticle key={`preview-${editorDraft.id}`} post={editorDraft} navigate={navigate} isPreview notify={notify} onReturn={() => { go(editingId ? `/admin/blog/edit/${editingId}` : '/admin/blog/new'); setDirty(true) }} /> : notFound
  else if (page === 'editor') {
    const post = editorDraft || posts.find((item) => item.id === editingId && item.status !== 'trashed')
    screen = editingId && !post ? notFound : <AdminBlogEditor key={editingId || 'new'} initialPost={post} initialDirty={Boolean(editorDraft)} posts={posts} navigate={navigate} onDirtyChange={setDirty} onPreview={(draft) => {
      setEditorDraft(draft)
      setDirty(false)
      window.history.pushState({}, '', `/blog-preview.html?page=preview${editingId ? `&id=${encodeURIComponent(editingId)}` : ''}`)
      setRoute(window.location.search)
      window.scrollTo({ top: 0, behavior: 'instant' })
    }} onSave={(post) => {
      const next = posts.some((item) => item.id === post.id) ? posts.map((item) => item.id === post.id ? post : item) : [...posts, post]
      updatePosts(next)
      setDirty(false)
      setEditorDraft(null)
      notify(post.status === 'published' ? 'Artikel berhasil diterbitkan di blog pratinjau.' : 'Draft berhasil disimpan. Artikel belum tampil di blog.')
      go(`/admin/blog/edit/${post.id}`, true)
    }} />
  } else screen = <AdminBlogList posts={posts} navigate={navigate} updatePosts={updatePosts} notify={notify} />

  return <div className="bp-preview"><div className="bp-local-bar"><div><span className="bp-local-dot" /><strong>Pratinjau lokal</strong><span className="bp-local-description">Data tersimpan di browser ini.</span></div><nav aria-label="Navigasi pratinjau"><BlogLink to="/admin/blog" navigate={navigate} aria-current={['admin', 'editor'].includes(page) ? 'page' : undefined}>Admin blog</BlogLink><BlogLink to="/blog" navigate={navigate} aria-current={['blog', 'article'].includes(page) ? 'page' : undefined}>Halaman blog <BlogIcon name="arrow" size={14} /></BlogLink></nav></div>{records.error && <div className="bp-storage-error" role="alert">{records.error} Pratinjau menampilkan artikel awal tanpa menimpa data lama.</div>}{screen}{notice && <div className="toast" role="status">{notice}</div>}<AppModal isOpen={Boolean(pendingPath)} title="Tinggalkan perubahan yang belum disimpan?" description="Simpan draft terlebih dahulu jika ingin melanjutkan tulisan ini nanti." confirmText="Ya, tinggalkan" cancelText="Lanjut menulis" variant="warning" onCancel={() => setPendingPath('')} onConfirm={() => { const path = pendingPath; setPendingPath(''); setDirty(false); setEditorDraft(null); go(path) }} /></div>
}

createRoot(document.getElementById('root')).render(<BlogRuntimeProvider value={{ hrefFor: previewUrl, session: null, logout: () => window.location.assign(previewUrl('/blog')), uploadImage: imageFromFile }}><BlogPreview /></BlogRuntimeProvider>)
