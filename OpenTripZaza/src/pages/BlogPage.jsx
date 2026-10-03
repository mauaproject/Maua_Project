import { useEffect, useRef, useState } from 'react'
import { useTranslation } from 'react-i18next'
import { PublicFooter, PublicNav } from './UserPage'
import blogContent from '../content/blog.json'
import heroImage from '../assets/loginsignuppicture.webp'
import aboutImage from '../assets/testimoni3.webp'
import './BlogPage.css'

function PageLink({ navigate, href, children, ...props }) {
  return <a href={href} {...props} onClick={(event) => {
    if (event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return
    event.preventDefault()
    navigate(href)
  }}>{children}</a>
}

function RichText({ text }) {
  return text.split(/(\*\*[^*]+\*\*|\*[^*]+\*)/g).map((part, index) => {
    if (part.startsWith('**')) return <strong key={index}>{part.slice(2, -2)}</strong>
    if (part.startsWith('*')) return <em key={index}>{part.slice(1, -1)}</em>
    return part
  })
}

export default function BlogPage({ navigate, session, logout }) {
  const { i18n } = useTranslation()
  const language = i18n.language?.startsWith('en') ? 'en' : 'id'
  const copy = blogContent[language]
  const [activeSection, setActiveSection] = useState('tentang')
  const mainRef = useRef(null)

  useEffect(() => {
    if (!('IntersectionObserver' in window)) return undefined
    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) setActiveSection(entry.target.id)
      })
    }, { rootMargin: '-110px 0px -55% 0px', threshold: 0 })
    mainRef.current.querySelectorAll('.blog-page-section[id]').forEach((section) => observer.observe(section))
    return () => observer.disconnect()
  }, [])

  return (
    <div className="public-page blog-page" id="blog-top">
      <PublicNav navigate={navigate} session={session} logout={logout} />
      <main className="blog-main" ref={mainRef}>
        <section className="blog-hero blog-container" aria-labelledby="blog-title">
          <div>
            <p className="blog-kicker">{copy.hero.eyebrow}</p>
            <h1 id="blog-title">{copy.hero.title}</h1>
            <p className="blog-hero-copy">{copy.hero.copy}</p>
            <div className="blog-hero-actions">
              <a href="#destinasi" className="blog-button">{copy.hero.explore} <span aria-hidden="true">↓</span></a>
              <a href="#tentang" className="blog-text-link">{copy.hero.about} <span aria-hidden="true">↗</span></a>
            </div>
            <p className="blog-hero-footnote">{copy.hero.footnote}</p>
          </div>
          <div className="blog-hero-visual">
            <img src={heroImage} alt={copy.hero.imageAlt} width="1600" height="1067" fetchPriority="high" decoding="async" />
            <div className="blog-hero-photo-label"><span>{copy.hero.photoLabel}</span><p>{copy.hero.photoCaption}</p></div>
          </div>
        </section>

        <div className="blog-page-nav-shell">
          <nav className="blog-page-nav blog-container" aria-label={copy.contentsAria}>
            <span className="blog-page-nav-label">{copy.contentsLabel}</span>
            <div className="blog-page-nav-links">
              {copy.sections.map((section) => <a href={`#${section.id}`} key={section.id} aria-current={activeSection === section.id ? 'location' : undefined}>{section.label}</a>)}
            </div>
          </nav>
        </div>

        <section className="blog-page-section blog-container" id="tentang" aria-labelledby="blog-about-title">
          <div className="blog-about-grid">
            <div className="blog-about-picture">
              <img src={aboutImage} alt={copy.about.imageAlt} width="600" height="400" loading="lazy" decoding="async" />
              <div className="blog-about-caption"><p>{copy.about.caption}</p></div>
            </div>
            <div className="blog-about-copy">
              <p className="blog-kicker">{copy.about.eyebrow}</p><h2 id="blog-about-title">{copy.about.title}</h2>
              {copy.about.paragraphs.map((paragraph) => <p key={paragraph}>{paragraph}</p>)}
              <div className="blog-about-tags">{copy.about.tags.map((tag) => <span key={tag}>{tag}</span>)}</div>
            </div>
          </div>
        </section>

        <section className="blog-page-section blog-guide-section" id="panduan" aria-labelledby="blog-guide-title">
          <div className="blog-container">
            <div className="blog-guide-intro">
              <div className="blog-guide-head">
                <p className="blog-kicker">{copy.guide.eyebrow}</p><h2 id="blog-guide-title">{copy.guide.title}</h2>
                <p>{copy.guide.intro}</p><a className="blog-text-link" href="#booking">{copy.guide.bookingLink} <span aria-hidden="true">↓</span></a>
              </div>
              <div className="blog-service-grid">{copy.guide.services.map((service, index) => (
                <article className="blog-service-card" key={service.title}>
                  <span className="blog-service-number" aria-hidden="true">0{index + 1}.</span><h3>{service.title}</h3>
                  <p>{service.copy}</p><p className="blog-service-note">{service.note}</p>
                </article>
              ))}</div>
            </div>
            <div className="blog-booking" id="booking">
              <h3 className="blog-booking-title">{copy.guide.bookingTitle}</h3>
              <ol className="blog-booking-steps">{copy.guide.steps.map((step, index) => (
                <li key={step.title}><span className="blog-step-number" aria-hidden="true">0{index + 1}</span><h4>{step.title}</h4><p>{step.copy}</p></li>
              ))}</ol>
              <p className="blog-booking-note">{copy.guide.bookingNote}</p>
            </div>
          </div>
        </section>

        <section className="blog-page-section blog-container" id="destinasi" aria-labelledby="blog-destinations-title">
          <div className="blog-section-heading"><div><p className="blog-kicker">{copy.destinations.eyebrow}</p><h2 id="blog-destinations-title">{copy.destinations.title}</h2></div><p>{copy.destinations.intro}</p></div>
          <div className="blog-destination-stories">{copy.destinations.groups.map((group, index) => (
            <section className="blog-destination-story" aria-labelledby={group.id} key={group.id}>
              <div className="blog-destination-heading"><span className="blog-destination-number" aria-hidden="true">0{index + 1}</span><h3 id={group.id}>{group.title}</h3><p>{group.subtitle}</p></div>
              <div className="blog-destination-prose">{group.paragraphs.map((paragraph) => <p key={paragraph}><RichText text={paragraph} /></p>)}</div>
            </section>
          ))}</div>
          <div className="blog-destinations-bottom"><p className="blog-destinations-note">{copy.destinations.note}</p><PageLink className="blog-text-link" navigate={navigate} href="/destinasi">{copy.destinations.link} <span aria-hidden="true">↗</span></PageLink></div>
        </section>

        <section className="blog-page-section blog-reviews-section" id="review" aria-labelledby="blog-reviews-title">
          <div className="blog-container">
            <div className="blog-section-heading"><div><p className="blog-kicker">{copy.reviews.eyebrow}</p><h2 id="blog-reviews-title">{copy.reviews.title}</h2></div><p>{copy.reviews.intro}</p></div>
            <div className="blog-reviews-grid">{blogContent.featuredReviews.map((review) => (
              <article className="blog-review" key={review.name}>
                <div className="blog-review-heading"><div><h3>{review.name}</h3><p>{review.trip}</p></div><span className="blog-stars" role="img" aria-label={copy.reviews.ratingLabel}>★★★★★</span></div>
                <blockquote lang="id">{review.content}</blockquote>
                <time dateTime={review.date}>{new Intl.DateTimeFormat(language === 'en' ? 'en-US' : 'id-ID', { day: 'numeric', month: 'short', year: 'numeric', timeZone: 'Asia/Jakarta' }).format(new Date(`${review.date}T00:00:00+07:00`))}</time>
              </article>
            ))}</div>
            <div className="blog-reviews-bottom"><p>{copy.reviews.originalLanguageNote}</p><PageLink className="blog-text-link" navigate={navigate} href="/reviews">{copy.reviews.allLink} <span aria-hidden="true">↗</span></PageLink></div>
          </div>
        </section>

        <section className="blog-page-section blog-container" id="persiapan" aria-labelledby="blog-preparation-title">
          <div className="blog-preparation-grid">
            <div><p className="blog-kicker">{copy.preparation.eyebrow}</p><h2 id="blog-preparation-title">{copy.preparation.title}</h2><p className="blog-intro-text">{copy.preparation.intro}</p></div>
            <ul className="blog-checklist">{copy.preparation.items.map((item) => <li key={item.title}><h3>{item.title}</h3><p>{item.copy}</p></li>)}</ul>
          </div>
        </section>
      </main>
      <PublicFooter navigate={navigate} />
      <a className="blog-back-top" href="#blog-top" aria-label={copy.backTop}>↑</a>
    </div>
  )
}
