import { useEffect, useLayoutEffect, useMemo, useRef, useState } from 'react'
import { useTranslation } from 'react-i18next'
import { PublicFooter, PublicNav } from '../../pages/UserPage'
import { articleDocument, categories, categoryLabel, contentFor, dateLabel, matchesSearch, readingMinutes } from './content'
import { useBlogRuntime } from './BlogRuntime'

export function BlogIcon({ name, size = 20 }) {
  const paths = {
    search: <><circle cx="10.5" cy="10.5" r="6.5" /><path d="m16 16 4.5 4.5" /></>,
    arrow: <><path d="M5 12h14M13 6l6 6-6 6" /></>,
    back: <><path d="M19 12H5m6-6-6 6 6 6" /></>,
    plus: <path d="M12 5v14M5 12h14" />,
    edit: <><path d="m15 5 4 4M4 20l4-1L20 7a2.8 2.8 0 0 0-4-4L4 15v5Z" /></>,
    eye: <><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7S2 12 2 12Z" /><circle cx="12" cy="12" r="3" /></>,
    image: <><rect x="3" y="3" width="18" height="18" rx="3" /><circle cx="8" cy="8" r="1.5" /><path d="m3 17 5-5 4 4 4-6 5 7" /></>,
    share: <><path d="M8 12H5v8h14v-8h-3M12 15V3m-4 4 4-4 4 4" /></>,
    clock: <><circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 2" /></>,
    trash: <><path d="M3 6h18M9 6V3h6v3M6 6l1 15h10l1-15M10 10v7m4-7v7" /></>,
    article: <><rect x="4" y="3" width="16" height="18" rx="2" /><path d="M8 7h8M8 11h8M8 15h5" /></>,
    left: <path d="M4 5h16M4 10h10M4 15h16M4 20h10" />,
    center: <path d="M4 5h16M7 10h10M4 15h16M7 20h10" />,
    right: <path d="M4 5h16M10 10h10M4 15h16M10 20h10" />,
    justify: <path d="M4 5h16M4 10h16M4 15h16M4 20h16" />,
    undo: <><path d="m8 4-5 5 5 5M3 9h11a6 6 0 0 1 0 12" /></>,
    redo: <><path d="m16 4 5 5-5 5m5-5H10a6 6 0 0 0 0 12" /></>,
    link: <><path d="m10 13 4-4M8 16l-1 1a4 4 0 0 1-6-6l5-5a4 4 0 0 1 6 0m0 2 1-1a4 4 0 0 1 6 6l-5 5a4 4 0 0 1-6 0" /></>,
  }
  return <svg width={size} height={size} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.65" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">{paths[name] || paths.article}</svg>
}

export function BlogLink({ to, navigate, children, ...props }) {
  const { hrefFor } = useBlogRuntime()
  return <a href={hrefFor(to)} {...props} onClick={(event) => {
    if (event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return
    event.preventDefault()
    navigate(to)
  }}>{children}</a>
}

export function PublicBlogFrame({ navigate, children }) {
  const { session, logout } = useBlogRuntime()
  const frameRef = useRef(null)
  useLayoutEffect(() => {
    const frame = frameRef.current
    const navbar = frame.querySelector('.public-nav')
    const preview = frame.closest('.bp-preview') || frame
    let reservedBottom = 0
    const measure = () => {
      const bottom = Math.ceil(navbar.getBoundingClientRect().bottom)
      if (bottom <= 0) return
      // Keep the initial clearance when the navbar becomes shorter during scrolling.
      reservedBottom = navbar.classList.contains('is-scrolled') ? Math.max(reservedBottom, bottom) : bottom
      preview.style.setProperty('--bp-navbar-bottom', `${reservedBottom}px`)
    }
    const resize = () => { reservedBottom = 0; measure() }
    measure()
    const observer = typeof window.ResizeObserver === 'function' ? new window.ResizeObserver(measure) : null
    observer?.observe(navbar)
    window.addEventListener('resize', resize)
    return () => {
      observer?.disconnect()
      window.removeEventListener('resize', resize)
      preview.style.removeProperty('--bp-navbar-bottom')
    }
  }, [])
  return <div ref={frameRef} className="public-page blog-feature bp-public"><PublicNav navigate={navigate} session={session} logout={logout} />{children}<PublicFooter navigate={navigate} /></div>
}

function ArticleCard({ post, lang, navigate }) {
  const content = contentFor(post, lang)
  return <article className="bp-story-card">
    <BlogLink to={`/blog/${post.slug}`} navigate={navigate} className="bp-story-image" aria-label={content.title}>
      {post.cover ? <img src={post.thumbnail || post.cover} alt={post.coverAlt || content.title} loading="lazy" /> : <span className="bp-image-placeholder"><BlogIcon name="image" size={36} /></span>}
    </BlogLink>
    <div className="bp-story-copy">
      <h2><BlogLink to={`/blog/${post.slug}`} navigate={navigate}>{content.title}</BlogLink></h2>
      <div className="bp-meta"><span className="bp-category">{categoryLabel(post.category, lang)}</span><time dateTime={post.publishedAt}>{dateLabel(post.publishedAt, lang)}</time></div>
    </div>
  </article>
}

export function BlogCatalog({ posts = [], navigate, initialSearch = '', remote = null }) {
  const { i18n } = useTranslation()
  const lang = i18n.language.startsWith('en') ? 'en' : 'id'
  const [localSearch, setLocalSearch] = useState(initialSearch)
  const [localCategory, setLocalCategory] = useState('all')
  const search = remote ? remote.search : localSearch
  const category = remote ? remote.category : localCategory
  const setSearch = remote ? remote.setSearch : setLocalSearch
  const setCategory = remote ? remote.setCategory : setLocalCategory
  const [page, setPage] = useState(1)
  const filtered = remote ? remote.items : posts.filter((post) => post.status === 'published' && (category === 'all' || post.category === category) && matchesSearch(post, search, lang)).sort((a, b) => new Date(b.publishedAt) - new Date(a.publishedAt))
  const visible = remote ? filtered : filtered.slice(0, page * 8)
  const total = remote ? remote.total : filtered.length
  const english = lang === 'en'
  return <PublicBlogFrame navigate={navigate}>
    <main className="bp-container bp-catalog">
      <header className="bp-catalog-heading">
        <div><p className="bp-eyebrow">MAUA JOURNAL</p><h1>{english ? 'Every trip has a story.' : 'Setiap perjalanan\npunya cerita.'}</h1><p>{english ? 'Travel inspiration, destination guides, and stories from our adventures.' : 'Inspirasi perjalanan, panduan destinasi, dan cerita dari setiap petualangan bersama MAUA.'}</p></div>
        <div className="bp-heading-note"><span>EXPLORE. EXPERIENCE. SHARE.</span><span>Yogyakarta & sekitarnya <BlogIcon name="arrow" size={16} /></span></div>
      </header>
      <section className="bp-discovery destination-toolbar" aria-label={english ? 'Find an article' : 'Temukan artikel'}>
        <div className="bp-category-filters destination-filter-chips catalog-filter-chips" role="group" aria-label={english ? 'Article category' : 'Kategori artikel'}>
          {[{ id: 'all', idLabel: 'Semua Artikel', enLabel: 'All Articles' }, ...categories].map((item) => <button type="button" key={item.id} className={category === item.id ? 'is-active' : ''} aria-pressed={category === item.id} onClick={() => { setCategory(item.id); setPage(1) }}>{item[`${lang}Label`]}</button>)}
        </div>
        <form className="hero-search-form bp-catalog-search" role="search" onSubmit={(event) => { event.preventDefault(); setSearch(search.trim()); setPage(1) }}>
          <span className="search-icon" aria-hidden="true" />
          <label><input type="search" maxLength={200} aria-label={english ? 'Search articles' : 'Cari artikel'} placeholder={english ? 'Search articles' : 'Cari artikel'} value={search} onChange={(event) => { setSearch(event.target.value); setPage(1) }} /></label>
          <button type="submit" className="search-submit" aria-label={english ? 'Find articles' : 'Temukan artikel'}><BlogIcon name="arrow" size={18} /></button>
        </form>
      </section>
      <div className="bp-section-top"><h2>{search || category !== 'all' ? (english ? 'Articles for you' : 'Artikel untukmu') : (english ? 'Latest stories' : 'Cerita terbaru')}</h2><span role="status">{total} {english ? 'articles' : 'artikel'}</span></div>
      {remote?.error && <div className="bp-fetch-error" role="alert"><p>{remote.error}</p><button className="outline-btn" onClick={remote.retry}>{english ? 'Try again' : 'Coba lagi'}</button></div>}
      {remote?.loading && visible.length === 0 ? <div className="bp-empty" role="status">{english ? 'Loading articles...' : 'Memuat artikel...'}</div> : visible.length ? <><div className="bp-story-grid">{visible.map((post) => <ArticleCard post={post} key={post.id} lang={lang} navigate={navigate} />)}</div>{(remote ? remote.hasMore : visible.length < total) && <div className="bp-load-more"><span>{visible.length} {english ? 'of' : 'dari'} {total} {english ? 'articles' : 'artikel'}</span><button className="outline-btn" disabled={remote?.loading} onClick={() => remote ? remote.loadMore() : setPage(page + 1)}>{remote?.loading ? (english ? 'Loading...' : 'Memuat...') : english ? 'Load more' : 'Muat artikel lainnya'}</button></div>}</> : !remote?.error && <div className="bp-empty"><BlogIcon name={search ? 'search' : 'article'} size={36} /><h2>{search || category !== 'all' ? (english ? 'No articles found' : 'Artikel tidak ditemukan') : (english ? 'New stories are on their way' : 'Cerita baru sedang disiapkan')}</h2><p>{search || category !== 'all' ? (english ? 'Try a different keyword or category.' : 'Coba kata kunci lain atau lihat semua kategori.') : (english ? 'Travel stories and the latest updates will appear here.' : 'Cerita perjalanan dan informasi terbaru akan hadir di sini.')}</p>{(search || category !== 'all') && <button className="outline-btn" onClick={() => { setSearch(''); setCategory('all') }}>{english ? 'Show all articles' : 'Lihat semua artikel'}</button>}</div>}
      <aside className="bp-journal-note"><span>MAUA PROJECT</span><p>{english ? 'A little wonder. A new story.' : 'Petualangan baru dimulai dari rasa penasaran.'}</p><BlogLink to="/destinasi" navigate={navigate}>{english ? 'Explore our trips' : 'Temukan perjalananmu'} <BlogIcon name="arrow" size={18} /></BlogLink></aside>
    </main>
  </PublicBlogFrame>
}

export function BlogArticle({ post, posts = [], navigate, isPreview = false, onReturn, notify }) {
  const { i18n } = useTranslation()
  const lang = i18n.language.startsWith('en') ? 'en' : 'id'
  const english = lang === 'en'
  const content = contentFor(post, lang)
  const document = useMemo(() => articleDocument(content.body), [content.body])
  const [activeSection, setActiveSection] = useState('')
  const [search, setSearch] = useState('')
  const related = posts.filter((item) => item.status === 'published' && item.id !== post.id).sort((a, b) => Number(b.category === post.category) - Number(a.category === post.category) || new Date(b.publishedAt) - new Date(a.publishedAt)).slice(0, 3)
  useEffect(() => {
    if (typeof window.IntersectionObserver !== 'function') return undefined
    const observer = new window.IntersectionObserver((entries) => entries.forEach((entry) => { if (entry.isIntersecting) setActiveSection(entry.target.id) }), { rootMargin: '-110px 0px -65% 0px' })
    window.document.querySelectorAll('.bp-prose h2[id], .bp-prose h3[id]').forEach((element) => observer.observe(element))
    return () => observer.disconnect()
  }, [document.html])
  const copyLink = async () => {
    try { await navigator.clipboard.writeText(window.location.href); notify(english ? 'Article link copied.' : 'Tautan artikel berhasil disalin.') }
    catch { notify(english ? 'Copy the address from your browser.' : 'Salin tautan melalui kolom alamat browser.') }
  }
  return <PublicBlogFrame navigate={navigate}>
    <main className="bp-container bp-article">
      {isPreview && <div className="bp-preview-notice"><div><BlogIcon name="eye" /><span><strong>Pratinjau artikel</strong> Belum diterbitkan dari tampilan ini.</span></div><button className="outline-btn" onClick={onReturn}>Kembali ke editor</button></div>}
      <BlogLink className="bp-back-link" to="/blog" navigate={navigate}><BlogIcon name="back" size={17} />{english ? 'All articles' : 'Semua artikel'}</BlogLink>
      <header className="bp-article-heading"><span className="bp-category">{categoryLabel(post.category, lang)}</span><h1>{content.title || (english ? 'Untitled article' : 'Artikel tanpa judul')}</h1><p>{content.excerpt}</p><div className="bp-article-meta"><div className="bp-author"><span className="bp-author-avatar">M</span><div><strong>{post.author || 'Tim MAUA'}</strong><span>{dateLabel(post.publishedAt || post.createdAt, lang)} <span aria-hidden="true">·</span> {readingMinutes(content.body)} {english ? 'min read' : 'menit baca'}</span></div></div><button type="button" className="bp-share" onClick={copyLink}><BlogIcon name="share" size={18} />{english ? 'Share' : 'Bagikan'}</button></div></header>
      {post.cover && <figure className="bp-article-cover" style={{ width: `${post.coverWidth || 100}%` }} data-align={post.coverAlign || 'center'}><img src={post.cover} alt={post.coverAlt || content.title} fetchPriority="high" />{post.coverAlt && <figcaption>{post.coverAlt}</figcaption>}</figure>}
      <div className="bp-reading-layout">
        <aside className="bp-article-sidebar">
          {document.contents.length > 0 && <details className="bp-toc" open><summary>{english ? 'Table of contents' : 'Daftar isi'}</summary><nav aria-label={english ? 'Table of contents' : 'Daftar isi'}>{document.contents.map((section) => <a key={section.id} href={`#${section.id}`} className={section.level === 3 ? 'bp-toc-child' : ''} aria-current={activeSection === section.id ? 'location' : undefined}>{section.title}</a>)}</nav></details>}
          <form className="bp-sidebar-search" onSubmit={(event) => { event.preventDefault(); navigate(`/blog?search=${encodeURIComponent(search)}`) }}><label htmlFor="bp-other-search">{english ? 'Find another story' : 'Cari artikel lainnya'}</label><div><input id="bp-other-search" type="search" maxLength={200} placeholder={english ? 'Search articles...' : 'Ketik kata kunci...'} value={search} onChange={(event) => setSearch(event.target.value)} /><button type="submit" aria-label={english ? 'Search' : 'Cari'}><BlogIcon name="search" size={18} /></button></div></form>
        </aside>
        <div className="bp-article-main"><article className="bp-prose" lang={lang === 'en' && content === post.idContent ? 'id' : lang} dangerouslySetInnerHTML={{ __html: document.html }} />{post.updatedAt && post.publishedAt && new Date(post.updatedAt) - new Date(post.publishedAt) > 60000 && <p className="bp-updated">{english ? 'Last updated' : 'Terakhir diperbarui'}: {dateLabel(post.updatedAt, lang)}</p>}<div className="bp-article-end"><span>MAUA JOURNAL</span><BlogLink to="/blog" navigate={navigate}>{english ? 'More stories' : 'Jelajahi cerita lainnya'} <BlogIcon name="arrow" size={18} /></BlogLink></div></div>
      </div>
      {!isPreview && related.length > 0 && <section className="bp-related"><div className="bp-section-top"><h2>{english ? 'More to explore' : 'Cerita lainnya untukmu'}</h2></div><div className="bp-story-grid">{related.map((item) => <ArticleCard key={item.id} post={item} lang={lang} navigate={navigate} />)}</div></section>}
    </main>
  </PublicBlogFrame>
}
