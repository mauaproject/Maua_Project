import { useEffect, useMemo, useState } from 'react'
import { useTranslation } from 'react-i18next'
import * as api from '../../services/api'
import { BlogArticle, BlogCatalog, BlogIcon, BlogLink, PublicBlogFrame } from './PublicBlog'
import { BlogRuntimeProvider } from './BlogRuntime'
import { contentFor } from './content'
import './Blog.css'

const siteUrl = (import.meta.env.VITE_SITE_URL || 'https://mauaproject.com').replace(/\/$/, '')
const identity = (path) => path

function useArticleMetadata(post, language, loading) {
  useEffect(() => {
    const content = post ? contentFor(post, language) : null
    const title = content ? `${content.seoTitle || content.title} | MAUA Project` : loading ? 'Memuat artikel | MAUA Project' : 'Artikel Tidak Ditemukan | MAUA Project'
    const description = content?.seoDescription || content?.excerpt || 'Cerita dan panduan perjalanan bersama MAUA.'
    const canonical = `${siteUrl}${post ? `/blog/${post.slug}` : '/blog'}`
    const image = post?.cover ? new URL(post.cover, siteUrl).href : `${siteUrl}/favicon.svg`
    document.title = title
    document.documentElement.lang = language
    for (const [key, value] of Object.entries({ description, robots: post ? 'index, follow' : 'noindex, follow', googlebot: post ? 'index, follow, max-image-preview:large' : 'noindex, follow', 'og:title': title, 'og:description': description, 'og:type': 'article', 'og:url': canonical, 'og:image': image, 'twitter:title': title, 'twitter:description': description, 'twitter:image': image })) {
      const attribute = key.startsWith('og:') ? 'property' : 'name'
      let element = document.head.querySelector(`meta[${attribute}="${key}"]`)
      if (!element) { element = document.createElement('meta'); element.setAttribute(attribute, key); document.head.appendChild(element) }
      element.content = value
    }
    let link = document.head.querySelector('link[rel="canonical"]')
    if (!link) { link = document.createElement('link'); link.rel = 'canonical'; document.head.appendChild(link) }
    link.href = canonical
    let schema = document.getElementById('maua-structured-data')
    if (!schema) { schema = document.createElement('script'); schema.id = 'maua-structured-data'; schema.type = 'application/ld+json'; document.head.appendChild(schema) }
    schema.textContent = JSON.stringify(post ? { '@context': 'https://schema.org', '@type': post.category === 'berita' ? 'NewsArticle' : 'BlogPosting', headline: content.title, description, image, datePublished: post.publishedAt, dateModified: post.updatedAt, mainEntityOfPage: canonical, author: { '@type': 'Person', name: post.author }, publisher: { '@type': 'Organization', name: 'MAUA Project', logo: { '@type': 'ImageObject', url: `${siteUrl}/favicon.svg` } } } : { '@context': 'https://schema.org', '@type': 'WebPage', name: title })
  }, [post, language, loading])
}

function CatalogPage({ navigate }) {
  const { i18n } = useTranslation()
  const lang = i18n.language.startsWith('en') ? 'en' : 'id'
  const [search, setSearch] = useState(() => new URLSearchParams(window.location.search).get('search') || '')
  const [category, setCategory] = useState('all')
  const [query, setQuery] = useState(() => ({ search, category: 'all', language: lang, page: 1, revision: 0 }))
  const [data, setData] = useState({ items: [], total: 0, hasMore: false, loading: true, error: '' })
  useEffect(() => {
    const timer = window.setTimeout(() => setQuery((current) => current.search === search && current.category === category && current.language === lang ? current : { search, category, language: lang, page: 1, revision: current.revision }), 250)
    return () => window.clearTimeout(timer)
  }, [search, category, lang])
  useEffect(() => {
    const controller = new AbortController()
    setData((current) => ({ ...current, loading: true, error: '', ...(query.page === 1 ? { items: [], total: 0 } : {}) }))
    api.getBlogPosts({ ...query, signal: controller.signal }).then((result) => {
      if (!controller.signal.aborted) setData((current) => ({ ...result, items: query.page === 1 ? result.items : [...current.items, ...result.items.filter((post) => !current.items.some((item) => item.id === post.id))], loading: false, error: '' }))
    }).catch((error) => { if (!controller.signal.aborted) setData((current) => ({ ...current, loading: false, error: error.message })) })
    return () => controller.abort()
  }, [query])
  return <BlogCatalog navigate={navigate} remote={{ ...data, search, setSearch, category, setCategory, loadMore: () => setQuery((current) => data.error ? { ...current, revision: current.revision + 1 } : { ...current, page: current.page + 1 }), retry: () => setQuery((current) => ({ ...current, revision: current.revision + 1 })) }} />
}

function ArticlePage({ slug, navigate, showToast }) {
  const { i18n } = useTranslation()
  const lang = i18n.language.startsWith('en') ? 'en' : 'id'
  const [revision, setRevision] = useState(0)
  const [data, setData] = useState({ post: null, related: [], loading: true, missing: false, error: '' })
  useEffect(() => {
    const controller = new AbortController()
    setData({ post: null, related: [], loading: true, missing: false, error: '' })
    Promise.all([api.getBlogPost({ slug, signal: controller.signal }), api.getBlogPosts({ limit: 4, signal: controller.signal }).catch(() => ({ items: [] }))]).then(([post, related]) => {
      if (!controller.signal.aborted) setData({ post, related: related.items, loading: false, missing: false, error: '' })
    }).catch((error) => { if (!controller.signal.aborted) setData({ post: null, related: [], loading: false, missing: error.status === 404, error: error.message }) })
    return () => controller.abort()
  }, [slug, revision])
  useArticleMetadata(data.post, lang, data.loading)
  if (data.post) return <BlogArticle post={data.post} posts={data.related} navigate={navigate} notify={showToast} />
  return <PublicBlogFrame navigate={navigate}><main className="bp-container bp-empty bp-not-found"><BlogIcon name="article" size={36} /><h1>{data.loading ? 'Memuat artikel...' : data.missing ? 'Artikel tidak ditemukan' : 'Artikel belum dapat dimuat'}</h1>{!data.loading && <><p>{data.missing ? 'Artikel belum diterbitkan atau sudah tidak tersedia.' : data.error}</p>{!data.missing && <button className="outline-btn" onClick={() => setRevision(revision + 1)}>Coba lagi</button>}<BlogLink className="outline-btn" to="/blog" navigate={navigate}>Kembali ke blog</BlogLink></>}</main></PublicBlogFrame>
}

export default function PublicBlogPage({ path, navigate, session, logout, showToast }) {
  const runtime = useMemo(() => ({ hrefFor: identity, session, logout, uploadImage: api.uploadBlogImage }), [session, logout])
  const slug = path.split('?')[0].match(/^\/blog\/([a-z0-9-]+)\/?$/)?.[1]
  return <BlogRuntimeProvider value={runtime}>{slug ? <ArticlePage key={slug} slug={slug} navigate={navigate} showToast={showToast} /> : <CatalogPage key={path} navigate={navigate} />}</BlogRuntimeProvider>
}
