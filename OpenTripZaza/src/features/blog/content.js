export const categories = [
  { id: 'berita', idLabel: 'Berita MAUA', enLabel: 'MAUA News' },
  { id: 'destinasi', idLabel: 'Destinasi', enLabel: 'Destinations' },
  { id: 'panduan', idLabel: 'Tips Perjalanan', enLabel: 'Travel Tips' },
  { id: 'cerita', idLabel: 'Cerita Trip', enLabel: 'Trip Stories' },
]
export const categoryLabel = (id, lang = 'id') => categories.find((item) => item.id === id)?.[`${lang}Label`] || id
export const escapeHtml = (text) => String(text || '').replace(/[&<>"']/g, (character) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[character])

export const emptyPost = () => ({
  id: crypto.randomUUID(), slug: '', category: 'berita', author: 'Tim MAUA', status: 'draft', keywords: [],
  cover: '', coverAlt: '', coverWidth: 100, coverAlign: 'center', createdAt: new Date().toISOString(), publishedAt: null, updatedAt: null,
  idContent: { title: '', excerpt: '', body: '', seoTitle: '', seoDescription: '' },
  enContent: { title: '', excerpt: '', body: '', seoTitle: '', seoDescription: '' },
})

export const slugify = (text) => text.normalize('NFKD').replace(/[\u0300-\u036f]/g, '').toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '')
export const contentFor = (post, lang) => lang === 'en' && post.enContent?.title?.trim() && (post.hasEnglish || plainText(post.enContent.body)) ? post.enContent : post.idContent
export const plainText = (html) => new DOMParser().parseFromString(html || '', 'text/html').body.textContent.replace(/\s+/g, ' ').trim()
export const readingMinutes = (body) => Math.max(1, Math.ceil(plainText(body).split(/\s+/).length / 200))
export const dateLabel = (date, lang = 'id') => date ? new Intl.DateTimeFormat(lang === 'en' ? 'en-GB' : 'id-ID', { day: 'numeric', month: 'long', year: 'numeric', timeZone: 'Asia/Jakarta' }).format(new Date(date)) : '—'
export const matchesSearch = (post, query, lang = 'id') => {
  const content = contentFor(post, lang)
  const text = [content.title, content.excerpt, plainText(content.body), post.author, categoryLabel(post.category, lang), ...keywordList(post.keywords)].join(' ').toLocaleLowerCase()
  return query.trim().toLocaleLowerCase().split(/\s+/).every((word) => text.includes(word))
}

export function keywordList(value) {
  const phrases = Array.isArray(value) ? value : String(value || '').split(',')
  const seen = new Set()
  return phrases.map((phrase) => String(phrase).replace(/\s+/gu, ' ').trim()).filter((phrase) => {
    const key = phrase.toLocaleLowerCase()
    if (!phrase || seen.has(key)) return false
    seen.add(key)
    return true
  })
}

export function keywordError(value) {
  const phrases = keywordList(value)
  if (phrases.some((phrase) => /[<>\u0000-\u001f\u007f,]/u.test(phrase))) return 'Keyword hanya boleh berisi teks. Pisahkan setiap frasa dengan koma.'
  if (phrases.some((phrase) => [...phrase].length > 80)) return 'Setiap keyword maksimal 80 karakter.'
  return phrases.length > 10 ? 'Maksimal 10 keyword per artikel.' : ''
}

export function relatedPosts(posts, post) {
  const keywords = new Set(keywordList(post.keywords).map((phrase) => phrase.toLocaleLowerCase()))
  const shared = (item) => keywordList(item.keywords).filter((phrase) => keywords.has(phrase.toLocaleLowerCase())).length
  return posts.filter((item) => item.status === 'published' && String(item.id) !== String(post.id) && (shared(item) || item.category === post.category))
    .sort((a, b) => shared(b) - shared(a) || Number(b.category === post.category) - Number(a.category === post.category) || new Date(b.publishedAt) - new Date(a.publishedAt))
    .slice(0, 3)
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

