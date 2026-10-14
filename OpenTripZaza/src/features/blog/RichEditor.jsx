import { useEffect, useRef, useState } from 'react'
import { AppModal } from '../../pages/shared'
import { BlogIcon } from './PublicBlog'
import { escapeHtml, plainText, sanitizeHtml } from './content'
import { useBlogRuntime } from './BlogRuntime'

const alignments = [['left', 'Rata kiri'], ['center', 'Rata tengah'], ['right', 'Rata kanan'], ['justify', 'Rata kiri-kanan (justify)']]

export default function RichEditor({ value, onChange, onBusyChange = () => {} }) {
  const { uploadImage } = useBlogRuntime()
  const editor = useRef(null)
  const selection = useRef(null)
  const imageInput = useRef(null)
  const imageTarget = useRef(null)
  const [linkOpen, setLinkOpen] = useState(false)
  const [link, setLink] = useState('')
  const [error, setError] = useState('')
  const [uploading, setUploading] = useState(false)
  const [imageForm, setImageForm] = useState(null)
  const [toolbarState, setToolbarState] = useState({ block: 'p', align: 'left' })

  useEffect(() => {
    if (document.activeElement !== editor.current && editor.current.innerHTML !== value) editor.current.innerHTML = sanitizeHtml(value)
  }, [value])

  const rememberSelection = () => {
    const current = window.getSelection()
    if (!current?.rangeCount || !editor.current.contains(current.anchorNode)) return
    selection.current = current.getRangeAt(0).cloneRange()
    const anchor = current.anchorNode.nodeType === Node.ELEMENT_NODE ? current.anchorNode : current.anchorNode.parentElement
    const block = anchor.closest('h2,h3,p,div,blockquote,li')
    const aligned = anchor.closest('[style*="text-align"],[align]')
    setToolbarState({
      block: ['h2', 'h3', 'blockquote'].includes(block?.tagName.toLowerCase()) ? block.tagName.toLowerCase() : 'p',
      align: aligned && editor.current.contains(aligned) ? aligned.style.textAlign || aligned.getAttribute('align') : 'left',
      bold: document.queryCommandState('bold'), italic: document.queryCommandState('italic'), underline: document.queryCommandState('underline'),
      strikeThrough: document.queryCommandState('strikeThrough'), insertUnorderedList: document.queryCommandState('insertUnorderedList'), insertOrderedList: document.queryCommandState('insertOrderedList'),
    })
  }
  const restoreSelection = () => {
    const savedSelection = selection.current
    editor.current.focus()
    const current = window.getSelection()
    current.removeAllRanges()
    if (savedSelection && editor.current.contains(savedSelection.startContainer)) current.addRange(savedSelection)
    else {
      const range = document.createRange()
      range.selectNodeContents(editor.current)
      range.collapse(false)
      current.addRange(range)
    }
  }
  const sync = () => onChange(sanitizeHtml(editor.current.innerHTML))
  const command = (name, argument = null) => {
    restoreSelection()
    document.execCommand(name, false, argument)
    sync()
    rememberSelection()
  }
  const handleKeyDown = (event) => {
    if (event.key !== 'Enter' || event.shiftKey || event.ctrlKey || event.metaKey || event.altKey || event.nativeEvent.isComposing) return
    const current = window.getSelection()
    if (!current?.rangeCount) return
    const range = current.getRangeAt(0)
    const anchor = range.startContainer.nodeType === Node.ELEMENT_NODE ? range.startContainer : range.startContainer.parentElement
    const heading = anchor.closest('h2,h3')
    if (!heading || !editor.current.contains(heading) || !heading.contains(range.endContainer)) return
    event.preventDefault()
    // Keep native editing history while making the next block a real paragraph.
    document.execCommand('insertParagraph', false)
    document.execCommand('formatBlock', false, 'p')
    sync()
    rememberSelection()
  }
  const chooseImage = async (file) => {
    if (!file) return
    setUploading(true)
    onBusyChange(true)
    setError('')
    try {
      const src = await uploadImage(file)
      setImageForm((current) => ({ src, alt: current?.alt || file.name.replace(/\.[^.]+$/, ''), width: current?.width || 100, align: current?.align || 'center' }))
    } catch (error) { setError(error.message) }
    finally { setUploading(false); onBusyChange(false); if (imageInput.current) imageInput.current.value = '' }
  }
  const editImage = (image) => {
    imageTarget.current = image
    rememberSelection()
    setImageForm({ src: image.getAttribute('src'), alt: image.getAttribute('alt') || '', width: parseFloat(image.style.width) || 100, align: image.getAttribute('data-align') || 'left' })
  }
  const applyImage = () => {
    const width = Math.min(100, Math.max(10, Number(imageForm.width) || 100))
    if (imageTarget.current && editor.current.contains(imageTarget.current)) {
      const image = imageTarget.current
      image.setAttribute('src', imageForm.src)
      image.setAttribute('alt', imageForm.alt.trim())
      image.style.width = `${width}%`
      image.setAttribute('data-align', imageForm.align)
      sync()
    } else command('insertHTML', `<figure><img src="${escapeHtml(imageForm.src)}" alt="${escapeHtml(imageForm.alt.trim())}" style="width:${width}%" data-align="${imageForm.align}"></figure><p><br></p>`)
    imageTarget.current = null
    setImageForm(null)
  }
  const removeImage = () => {
    const image = imageTarget.current
    if (image && editor.current.contains(image)) { (image.closest('figure') || image).remove(); sync() }
    imageTarget.current = null
    setImageForm(null)
  }
  const button = (name, label, children, argument = null, toggle = false) => <button type="button" key={name} title={label} aria-label={label} aria-pressed={toggle ? Boolean(toolbarState[name]) : undefined} onMouseDown={(event) => event.preventDefault()} onClick={() => command(name, argument)}>{children}</button>

  return <div className="bp-rich-editor">
    <div className="bp-editor-toolbar" role="toolbar" aria-label="Format isi artikel">
      <div className="bp-toolbar-group">{button('undo', 'Urungkan (Ctrl+Z)', <BlogIcon name="undo" size={17} />)}{button('redo', 'Ulangi (Ctrl+Y)', <BlogIcon name="redo" size={17} />)}</div><span className="bp-toolbar-divider" />
      <select aria-label="Gaya paragraf" value={toolbarState.block} onPointerDown={rememberSelection} onChange={(event) => command('formatBlock', event.target.value)}><option value="p">Paragraf</option><option value="h2">Subjudul (H2)</option><option value="h3">Subjudul kecil (H3)</option><option value="blockquote">Kutipan</option></select><span className="bp-toolbar-divider" />
      <div className="bp-toolbar-group">{button('bold', 'Tebal (Ctrl+B)', <strong>B</strong>, null, true)}{button('italic', 'Miring (Ctrl+I)', <em>I</em>, null, true)}{button('underline', 'Garis bawah (Ctrl+U)', <u>U</u>, null, true)}{button('strikeThrough', 'Coret teks', <s>S</s>, null, true)}</div><span className="bp-toolbar-divider" />
      <div className="bp-toolbar-group" role="group" aria-label="Perataan teks">{alignments.map(([align, label]) => <button key={align} type="button" aria-label={label} title={label} aria-pressed={toolbarState.align === align} onMouseDown={(event) => event.preventDefault()} onClick={() => command(`justify${align === 'justify' ? 'Full' : align.charAt(0).toUpperCase() + align.slice(1)}`)}><BlogIcon name={align} size={17} /></button>)}</div><span className="bp-toolbar-divider" />
      <div className="bp-toolbar-group">{button('insertUnorderedList', 'Daftar poin', <span>• ≡</span>, null, true)}{button('insertOrderedList', 'Daftar nomor', <span>1. ≡</span>, null, true)}{button('outdent', 'Kurangi indentasi', <span>‹≡</span>)}{button('indent', 'Tambah indentasi', <span>›≡</span>)}</div><span className="bp-toolbar-divider" />
      <div className="bp-toolbar-group"><button type="button" title="Sisipkan tautan" aria-label="Sisipkan tautan" onMouseDown={(event) => event.preventDefault()} onClick={() => { setLink(''); setLinkOpen(true) }}><BlogIcon name="link" size={17} /></button>{button('unlink', 'Hapus tautan', <span>↗̸</span>)}<button type="button" title="Sisipkan gambar" aria-label="Sisipkan gambar" disabled={uploading} onMouseDown={(event) => event.preventDefault()} onClick={() => { imageTarget.current = null; imageInput.current.click() }}><BlogIcon name="image" size={18} /></button>{button('insertHorizontalRule', 'Garis pemisah', <span>―</span>)}{button('removeFormat', 'Hapus format', <span>Tₓ</span>)}</div>
      <input ref={imageInput} type="file" aria-label="Pilih gambar artikel" accept="image/jpeg,image/png,image/webp" hidden onChange={(event) => chooseImage(event.target.files[0])} />
    </div>
    <div ref={editor} className="bp-editor-content bp-prose" contentEditable suppressContentEditableWarning role="textbox" aria-label="Isi artikel" aria-multiline="true" data-placeholder="Mulai tulis ceritamu di sini..." onInput={() => { sync(); rememberSelection() }} onKeyDown={handleKeyDown} onKeyUp={rememberSelection} onMouseUp={rememberSelection} onSelect={rememberSelection} onFocus={rememberSelection} onBlur={sync} onClick={(event) => { if (event.target.tagName === 'IMG') { event.preventDefault(); editImage(event.target) } }} onPaste={(event) => { event.preventDefault(); command('insertText', event.clipboardData.getData('text/plain')) }} />
    <div className="bp-editor-foot"><span>H2 / H3 membuat daftar isi. Klik gambar untuk mengatur ukuran dan posisinya.</span><span>{plainText(value).split(/\s+/).filter(Boolean).length} kata</span></div>
    {uploading && <p className="bp-field-note" role="status">Menyiapkan gambar...</p>}{error && <p className="form-error" role="alert">{error}</p>}
    <AppModal isOpen={linkOpen} title="Sisipkan tautan" description="Pilih teks di editor, lalu masukkan alamat tujuannya." confirmText="Sisipkan" cancelText="Batal" confirmDisabled={!/^https?:\/\/\S+$/i.test(link.trim())} onCancel={() => setLinkOpen(false)} onConfirm={() => { command('createLink', link.trim()); setLinkOpen(false) }}><label className="bp-modal-field">Alamat tautan<input type="url" value={link} onChange={(event) => setLink(event.target.value)} placeholder="https://..." autoFocus /></label></AppModal>
    <AppModal isOpen={Boolean(imageForm)} title={imageTarget.current ? 'Edit gambar artikel' : 'Sisipkan gambar artikel'} description="Persentase mengikuti lebar area teks artikel. Pratinjau foto tetap; pengaturan diterapkan saat kamu menekan tombol di bawah." confirmText={imageTarget.current ? 'Terapkan perubahan' : 'Sisipkan gambar'} cancelText="Batal" confirmDisabled={uploading} onCancel={() => { setImageForm(null); imageTarget.current = null }} onConfirm={applyImage}>
      {imageForm && <div className="bp-image-settings">
        <div className="bp-image-settings-preview"><img src={imageForm.src} alt={imageForm.alt || 'Pratinjau gambar'} /></div>
        <div className="bp-image-size-label"><label htmlFor="bp-image-width">Lebar gambar di artikel</label><div><input id="bp-image-width" type="number" aria-label="Lebar gambar persen" min="10" max="100" value={imageForm.width} onChange={(event) => setImageForm({ ...imageForm, width: Math.min(100, Math.max(10, Number(event.target.value) || 10)) })} /><span>%</span></div></div>
        <input type="range" aria-label="Geser lebar gambar" min="10" max="100" step="1" value={imageForm.width} onChange={(event) => setImageForm({ ...imageForm, width: Number(event.target.value) })} />
        <div className="bp-image-presets" role="group" aria-label="Ukuran gambar cepat">{[25, 50, 75, 100].map((width) => <button type="button" key={width} aria-pressed={imageForm.width === width} onClick={() => setImageForm({ ...imageForm, width })}>{width}%</button>)}</div>
        <div className="bp-image-position"><span>Posisi gambar</span><div role="group" aria-label="Posisi gambar">{alignments.slice(0, 3).map(([align, label]) => <button type="button" key={align} aria-label={`Gambar ${label.toLowerCase()}`} title={label} aria-pressed={imageForm.align === align} onClick={() => setImageForm({ ...imageForm, align })}><BlogIcon name={align} size={17} /></button>)}</div></div>
        <label className="bp-modal-field">Deskripsi gambar<input type="text" value={imageForm.alt} maxLength={200} onChange={(event) => setImageForm({ ...imageForm, alt: event.target.value })} placeholder="Jelaskan isi foto untuk pembaca layar" /></label>
        <div className="bp-image-dialog-actions"><button type="button" className="outline-btn" disabled={uploading} onClick={() => imageInput.current.click()}>Ganti gambar</button>{imageTarget.current && <button type="button" className="outline-btn bp-danger" onClick={removeImage}>Hapus gambar</button>}</div>
      </div>}
    </AppModal>
  </div>
}
