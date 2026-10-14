import legacy from '../../content/blog.json'
import cover from '../../assets/loginsignuppicture.webp'
import aboutImage from '../../assets/testimoni3.webp'
import { demoPosts } from './samplePosts'

export const STORAGE_KEY = 'mauaBlogLocalPreview.v1'
export const DEMO_SEED_KEY = 'mauaBlogLocalPreview.sampleArticles.v1'
export const categories = [
  { id: 'berita', idLabel: 'Berita MAUA', enLabel: 'MAUA News' },
  { id: 'destinasi', idLabel: 'Destinasi', enLabel: 'Destinations' },
  { id: 'panduan', idLabel: 'Tips Perjalanan', enLabel: 'Travel Tips' },
  { id: 'cerita', idLabel: 'Cerita Trip', enLabel: 'Trip Stories' },
]
export const categoryLabel = (id, lang = 'id') => categories.find((item) => item.id === id)?.[`${lang}Label`] || id
export const escapeHtml = (text) => String(text || '').replace(/[&<>"']/g, (character) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[character])
const rich = (text) => escapeHtml(text).replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>').replace(/\*([^*]+)\*/g, '<em>$1</em>')
const paragraphs = (items) => items.map((item) => `<p>${rich(item)}</p>`).join('')
const heading = (text, level = 2) => `<h${level}>${escapeHtml(text.replace(/\n/g, ' '))}</h${level}>`

function legacyBody(lang) {
  const copy = legacy[lang]
  return [
    paragraphs([copy.hero.copy]),
    heading(copy.sections[0].label),
    paragraphs(copy.about.paragraphs),
    `<figure><img src="${aboutImage}" alt="${escapeHtml(copy.about.imageAlt)}"><figcaption>${escapeHtml(copy.about.caption.replace(/\n/g, ' '))}</figcaption></figure>`,
    heading(copy.sections[1].label),
    paragraphs([copy.guide.intro]),
    ...copy.guide.services.map((service) => heading(service.title, 3) + paragraphs([service.copy, service.note])),
    heading(copy.guide.bookingTitle, 3),
    `<ol>${copy.guide.steps.map((step) => `<li><strong>${escapeHtml(step.title)}.</strong> ${escapeHtml(step.copy)}</li>`).join('')}</ol>`,
    paragraphs([copy.guide.bookingNote]),
    heading(copy.sections[2].label),
    paragraphs([copy.destinations.intro]),
    ...copy.destinations.groups.map((group) => heading(group.title, 3) + paragraphs([group.subtitle.replace(/\n/g, ' '), ...group.paragraphs])),
    paragraphs([copy.destinations.note]),
    '<p><a href="/destinasi">' + escapeHtml(copy.destinations.link) + '</a></p>',
    heading(copy.sections[3].label),
    paragraphs([copy.reviews.intro]),
    ...legacy.featuredReviews.map((review) => `<blockquote><p>${escapeHtml(review.content)}</p><p><strong>${escapeHtml(review.name)}</strong> — ${escapeHtml(review.trip)}</p></blockquote>`),
    paragraphs([copy.reviews.originalLanguageNote]),
    heading(copy.sections[4].label),
    paragraphs([copy.preparation.intro]),
    ...copy.preparation.items.map((item) => heading(item.title, 3) + paragraphs([item.copy])),
  ].join('')
}

export const initialPosts = [{
  id: 'maua-first-article',
  slug: 'kenali-maua-destinasi-dan-panduan-perjalanan',
  category: 'panduan', author: 'Tim MAUA', status: 'published', cover,
  coverAlt: 'Cahaya matahari memasuki ruang goa', coverWidth: 100, coverAlign: 'center',
  createdAt: '2026-10-14T08:00:00+07:00',
  publishedAt: '2026-10-14T08:00:00+07:00', updatedAt: '2026-10-14T08:00:00+07:00',
  idContent: {
    title: 'Kenali MAUA: Destinasi dan Panduan Perjalanan',
    excerpt: 'Dari kedalaman goa hingga pesisir Jogja. Kenali MAUA, pilihan petualangan, dan persiapan untuk perjalanan pertamamu.',
    body: legacyBody('id'), seoTitle: '', seoDescription: '',
  },
  enContent: {
    title: 'Get to Know MAUA: Destinations and Travel Guide',
    excerpt: 'From the depths of caves to the Jogja coast. Discover MAUA, your next adventure, and how to prepare for your first trip.',
    body: legacyBody('en'), seoTitle: '', seoDescription: '',
  },
}]

export const emptyPost = () => ({
  id: crypto.randomUUID(), slug: '', category: 'berita', author: 'Tim MAUA', status: 'draft',
  cover: '', coverAlt: '', coverWidth: 100, coverAlign: 'center', createdAt: new Date().toISOString(), publishedAt: null, updatedAt: null,
  idContent: { title: '', excerpt: '', body: '', seoTitle: '', seoDescription: '' },
  enContent: { title: '', excerpt: '', body: '', seoTitle: '', seoDescription: '' },
})

export const slugify = (text) => text.normalize('NFKD').replace(/[\u0300-\u036f]/g, '').toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '')
export const contentFor = (post, lang) => lang === 'en' && post.enContent?.title?.trim() && plainText(post.enContent.body) ? post.enContent : post.idContent
export const plainText = (html) => new DOMParser().parseFromString(html || '', 'text/html').body.textContent.replace(/\s+/g, ' ').trim()
export const readingMinutes = (body) => Math.max(1, Math.ceil(plainText(body).split(/\s+/).length / 200))
export const dateLabel = (date, lang = 'id') => date ? new Intl.DateTimeFormat(lang === 'en' ? 'en-GB' : 'id-ID', { day: 'numeric', month: 'long', year: 'numeric', timeZone: 'Asia/Jakarta' }).format(new Date(date)) : '—'
export const matchesSearch = (post, query, lang = 'id') => {
  const content = contentFor(post, lang)
  const text = [content.title, content.excerpt, plainText(content.body), post.author, categoryLabel(post.category, lang)].join(' ').toLocaleLowerCase()
  return query.trim().toLocaleLowerCase().split(/\s+/).every((word) => text.includes(word))
}

// Only keep the formatting supported by the local editor. Never render pasted HTML directly.
export function sanitizeHtml(html) {
  const document = new DOMParser().parseFromString(html || '', 'text/html')
  const allowed = new Set(['P', 'DIV', 'H2', 'H3', 'STRONG', 'B', 'EM', 'I', 'U', 'S', 'STRIKE', 'UL', 'OL', 'LI', 'BLOCKQUOTE', 'BR', 'HR', 'FIGURE', 'FIGCAPTION', 'IMG', 'A'])
  const alignable = new Set(['P', 'DIV', 'H2', 'H3', 'LI', 'BLOCKQUOTE', 'FIGCAPTION'])
  const forbidden = new Set(['SCRIPT', 'STYLE', 'IFRAME', 'OBJECT', 'EMBED', 'SVG', 'MATH', 'FORM', 'INPUT', 'BUTTON', 'TEMPLATE'])
  const clean = (node) => {
    for (const element of [...node.children]) {
      if (forbidden.has(element.tagName)) { element.remove(); continue }
      clean(element)
      if (!allowed.has(element.tagName)) { element.replaceWith(...element.childNodes); continue }
      const href = element.getAttribute('href') || ''
      const src = element.getAttribute('src') || ''
      const alt = element.getAttribute('alt') || ''
      const alignment = element.style.textAlign || element.getAttribute('align') || ''
      const width = element.style.width || ''
      const imageAlignment = element.getAttribute('data-align') || 'left'
      for (const attribute of [...element.attributes]) element.removeAttribute(attribute.name)
      if (alignable.has(element.tagName) && ['left', 'center', 'right', 'justify'].includes(alignment)) element.style.textAlign = alignment
      if (element.tagName === 'A') {
        if (/^(https?:\/\/|mailto:|\/(?!\/)|#)/i.test(href)) element.setAttribute('href', href)
        element.setAttribute('rel', 'noopener noreferrer')
      }
      if (element.tagName === 'IMG') {
        if (!/^(\/(?!\/)|data:image\/(png|jpeg|webp);base64,)/i.test(src)) { element.remove(); continue }
        element.setAttribute('src', src)
        element.setAttribute('alt', alt)
        element.setAttribute('loading', 'lazy')
        if (/^\d{1,3}(\.\d+)?%$/.test(width)) element.style.width = `${Math.min(100, Math.max(10, parseFloat(width)))}%`
        if (['left', 'center', 'right'].includes(imageAlignment)) element.setAttribute('data-align', imageAlignment)
      }
    }
  }
  clean(document.body)
  return document.body.innerHTML
}

export function articleDocument(body) {
  const document = new DOMParser().parseFromString(sanitizeHtml(body), 'text/html')
  const contents = [...document.querySelectorAll('h2, h3')].map((element, index) => {
    const id = `section-${index + 1}-${slugify(element.textContent)}`
    element.id = id
    return { id, title: element.textContent, level: element.tagName === 'H3' ? 3 : 2 }
  })
  return { html: document.body.innerHTML, contents }
}

export function loadPosts() {
  const saved = localStorage.getItem(STORAGE_KEY)
  const parsed = saved ? JSON.parse(saved) : initialPosts
  if (!Array.isArray(parsed) || parsed.some((post) => !post.id || !post.idContent || !post.enContent || !['draft', 'published', 'trashed'].includes(post.status))) throw new Error('Data pratinjau tidak dapat dibaca. Data lama tetap disimpan.')
  // Add review examples once, preserving existing articles, edits, drafts, and trash.
  if (!localStorage.getItem(DEMO_SEED_KEY)) {
    const additions = demoPosts.filter((example) => !parsed.some((post) => post.id === example.id || post.slug === example.slug))
    const seeded = [...parsed, ...additions]
    savePosts(seeded)
    localStorage.setItem(DEMO_SEED_KEY, '1')
    return seeded
  }
  return parsed
}

export function savePosts(posts) {
  try { localStorage.setItem(STORAGE_KEY, JSON.stringify(posts)) }
  catch { throw new Error('Penyimpanan browser penuh atau tidak tersedia. Kurangi jumlah gambar lalu coba simpan kembali.') }
}

export function previewUrl(path) {
  if (path.startsWith('/blog?')) return `/blog-preview.html?page=blog&${path.split('?')[1]}`
  if (path === '/blog') return '/blog-preview.html?page=blog'
  if (path === '/admin/blog/new') return '/blog-preview.html?page=editor'
  if (path.startsWith('/admin/blog/edit/')) return `/blog-preview.html?page=editor&id=${encodeURIComponent(path.slice('/admin/blog/edit/'.length))}`
  if (path === '/admin/blog') return '/blog-preview.html?page=admin'
  if (path.startsWith('/blog/')) return `/blog-preview.html?page=article&slug=${encodeURIComponent(path.slice('/blog/'.length))}`
  return path
}

export async function imageFromFile(file) {
  if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) throw new Error('Gunakan gambar JPG, PNG, atau WebP.')
  if (file.size > 10 * 1024 * 1024) throw new Error('Ukuran gambar maksimal 10 MB.')
  const url = URL.createObjectURL(file)
  try {
    const image = new Image()
    image.src = url
    await image.decode()
    const scale = Math.min(1, 1400 / image.width, 1400 / image.height)
    const canvas = document.createElement('canvas')
    canvas.width = Math.round(image.width * scale)
    canvas.height = Math.round(image.height * scale)
    canvas.getContext('2d').drawImage(image, 0, 0, canvas.width, canvas.height)
    return canvas.toDataURL('image/webp', 0.78)
  } finally { URL.revokeObjectURL(url) }
}
