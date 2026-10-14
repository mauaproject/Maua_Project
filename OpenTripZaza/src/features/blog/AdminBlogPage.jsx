import { useEffect, useMemo, useState } from 'react'
import * as api from '../../services/api'
import { AdminShell } from '../../pages/AdminPage'
import { AppModal } from '../../pages/shared'
import { AdminBlogEditor, AdminBlogList } from './AdminBlog'
import { BlogArticle } from './PublicBlog'
import { BlogRuntimeProvider } from './BlogRuntime'
import './Blog.css'

const identity = (path) => path

export default function AdminBlogPage(props) {
  const { path, session, logout, showToast } = props
  const runtime = useMemo(() => ({ hrefFor: identity, session, logout, uploadImage: api.uploadBlogImage }), [session, logout])
  const [posts, setPosts] = useState([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')
  const [revision, setRevision] = useState(0)
  const [dirty, setDirty] = useState(false)
  const [pendingPath, setPendingPath] = useState('')
  const [snapshot, setSnapshot] = useState(null)
  const [preview, setPreview] = useState(false)
  const editingId = path.match(/^\/admin\/blog\/edit\/(\d+)$/)?.[1]
  const isEditor = path === '/admin/blog/new' || Boolean(editingId)
  const currentSnapshot = snapshot?.path === path ? snapshot : null
  const initialPost = currentSnapshot?.post || posts.find((post) => post.id === editingId && post.status !== 'trashed')
  const shellProps = { registrations: props.registrations, rescheduleRequests: props.rescheduleRequests }
  useEffect(() => {
    const controller = new AbortController()
    setLoading(true)
    api.getBlogPosts({ all: true, signal: controller.signal }).then((result) => { if (!controller.signal.aborted) { setPosts(result); setError(''); setLoading(false) } }).catch((error) => { if (!controller.signal.aborted) { setError(error.message); setLoading(false) } })
    return () => controller.abort()
  }, [revision])
  useEffect(() => {
    const warn = (event) => { if (dirty) { event.preventDefault(); event.returnValue = '' } }
    window.addEventListener('beforeunload', warn)
    return () => window.removeEventListener('beforeunload', warn)
  }, [dirty])
  const go = (target) => { setSnapshot(null); setPreview(false); setDirty(false); props.navigate(target) }
  const navigate = (target) => { if (dirty) setPendingPath(target); else go(target) }
  const save = async (post) => {
    const saved = await api.saveBlogPost(post)
    setPosts((current) => [...current.filter((item) => item.id !== saved.id), saved])
    setDirty(false)
    setSnapshot(null)
    showToast(saved.status === 'published' ? 'Artikel berhasil diterbitkan.' : 'Draft berhasil disimpan.')
    if (path === '/admin/blog/new') props.navigate(`/admin/blog/edit/${saved.id}`)
    return saved
  }
  let screen
  if (loading || error) screen = <AdminShell {...shellProps} title="Pengelolaan Blog" path="/admin/blog" navigate={navigate} logout={logout}><div className="bp-empty" role={error ? 'alert' : 'status'}><h2>{loading ? 'Memuat artikel...' : 'Blog belum dapat dimuat'}</h2>{error && <><p>{error}</p><button className="outline-btn" onClick={() => setRevision(revision + 1)}>Coba lagi</button></>}</div></AdminShell>
  else if (preview && currentSnapshot) screen = <BlogArticle post={currentSnapshot.post} navigate={navigate} isPreview notify={showToast} onReturn={() => setPreview(false)} />
  else if (isEditor && editingId && !initialPost) screen = <AdminShell {...shellProps} title="Artikel Tidak Ditemukan" path="/admin/blog" navigate={navigate} logout={logout}><div className="bp-empty"><p>Artikel tidak tersedia atau masih berada di sampah.</p><button className="outline-btn" onClick={() => navigate('/admin/blog')}>Kembali ke daftar</button></div></AdminShell>
  else if (isEditor) screen = <AdminBlogEditor key={editingId || 'new'} initialPost={initialPost} initialDirty={dirty} posts={posts} navigate={navigate} onSave={save} onPreview={(post) => { setSnapshot({ path, post }); setPreview(true) }} onDirtyChange={setDirty} shellProps={shellProps} />
  else screen = <AdminBlogList posts={posts} navigate={navigate} notify={showToast} shellProps={shellProps} onStatusChange={async (post, status) => { const saved = await api.setBlogPostStatus(post.id, status, post.version); setPosts((current) => current.map((item) => item.id === saved.id ? saved : item)) }} />
  return <BlogRuntimeProvider value={runtime}>{screen}<AppModal isOpen={Boolean(pendingPath)} title="Tinggalkan perubahan yang belum disimpan?" description="Simpan draft terlebih dahulu jika ingin melanjutkan tulisan ini nanti." confirmText="Ya, tinggalkan" cancelText="Lanjut menulis" variant="warning" onCancel={() => setPendingPath('')} onConfirm={() => { const target = pendingPath; setPendingPath(''); go(target) }} /></BlogRuntimeProvider>
}
