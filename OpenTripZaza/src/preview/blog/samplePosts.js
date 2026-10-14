import jomblang from '../../assets/loginsignuppicture.webp'
import greenery from '../../assets/testimoni3.webp'
import trip from '../../assets/backgroundlandingpageuser.webp'
import caving from './assets/sample-caving.webp'
import tubing from './assets/sample-tubing.webp'
import journey from './assets/sample-journey.webp'

// Local review content only. These examples do not represent published MAUA news.
const examples = [
  {
    slug: 'contoh-panduan-pertama-kali-ke-goa-jomblang', category: 'destinasi', image: jomblang,
    title: 'Pertama Kali ke Goa Jomblang? Mulai dari Sini', enTitle: 'Your First Visit to Goa Jomblang Starts Here',
    excerpt: 'Kenali suasana penjelajahan goa dan hal-hal yang perlu ditanyakan sebelum memilih perjalanan.',
    sections: [
      ['Mengenal perjalanan', 'Penjelajahan goa menawarkan pengalaman yang berbeda dari wisata sehari-hari. Cahaya, bebatuan, dan suasana di bawah permukaan menjadi bagian dari cerita perjalanan.'],
      ['Persiapan sebelum berangkat', 'Baca detail paket yang kamu pilih. Tanyakan titik temu, perlengkapan, dan persyaratan kegiatan kepada tim sebelum berangkat.'],
      ['Nikmati setiap momen', 'Ikuti briefing dan arahan pemandu. Sisihkan waktu untuk menikmati suasana, serta gunakan titik dokumentasi yang ditentukan oleh tim.'],
    ],
  },
  {
    slug: 'contoh-checklist-perlengkapan-caving', category: 'panduan', image: journey,
    title: 'Checklist Ringkas Sebelum Berangkat Caving', enTitle: 'A Simple Checklist Before Your Caving Trip',
    excerpt: 'Dari pakaian hingga konfirmasi perjalanan, susun persiapanmu supaya tidak terburu-buru.',
    sections: [
      ['Mulai dari informasi paket', 'Perlengkapan yang dibutuhkan mengikuti kegiatan dan paket yang dipilih. Gunakan daftar persiapan dari tim sebagai acuan utama.'],
      ['Simpan informasi penting', 'Simpan konfirmasi pemesanan, lokasi pertemuan, serta kontak tim. Pastikan semua peserta di kelompokmu mendapatkan informasi yang sama.'],
      ['Periksa kembali sebelum berangkat', 'Siapkan barang pribadi secukupnya dan periksa waktu keberangkatan. Sampaikan pertanyaan kepada admin sebelum hari perjalanan.'],
    ],
  },
  {
    slug: 'contoh-open-trip-atau-private-trip', category: 'panduan', image: trip,
    title: 'Open Trip atau Private Trip, Pilih yang Cocok untukmu', enTitle: 'Open Trip or Private Trip: Find Your Fit',
    excerpt: 'Kenali dua cara menjelajah bersama MAUA sebelum merencanakan perjalananmu.',
    sections: [
      ['Berkenalan dengan open trip', 'Open trip mempertemukan peserta dalam perjalanan dengan jadwal yang tersedia. Ini bisa menjadi awal untuk mengenal teman perjalanan baru.'],
      ['Perjalanan bersama kelompok sendiri', 'Private trip ditujukan untuk perjalanan bersama kelompokmu. Diskusikan pilihan tanggal dan kebutuhan rombongan dengan tim.'],
      ['Sebelum menentukan pilihan', 'Bandingkan detail kegiatan, fasilitas, jadwal, dan ketentuan setiap paket. Pilih pengalaman yang sesuai dengan rencana kelompokmu.'],
    ],
  },
  {
    slug: 'contoh-mengenal-vertical-caving', category: 'destinasi', image: caving,
    title: 'Mengenal Vertical Caving dari Dekat', enTitle: 'A Closer Look at Vertical Caving',
    excerpt: 'Sebuah pengantar untuk kamu yang penasaran dengan penjelajahan goa vertikal.',
    sections: [
      ['Pengalaman yang berbeda', 'Penjelajahan goa vertikal menghadirkan sudut pandang yang tidak biasa. Setiap goa memiliki karakter, rute, dan persyaratan kegiatan yang berbeda.'],
      ['Persiapan bersama tim', 'Pahami informasi paket dan ikuti penjelasan pemandu sebelum kegiatan. Jangan ragu menyampaikan pertanyaan atau kebutuhan peserta.'],
      ['Membawa pulang cerita', 'Nikmati proses perjalanan dan dokumentasikan pengalaman sesuai arahan tim. Cerita terbaik sering kali datang dari hal baru yang kamu coba.'],
    ],
  },
  {
    slug: 'contoh-cerita-akhir-pekan-bersama-teman', category: 'cerita', image: journey,
    title: 'Satu Akhir Pekan, Banyak Cerita Bersama Teman', enTitle: 'One Weekend, So Many Stories with Friends',
    excerpt: 'Contoh cerita perjalanan kelompok kecil yang ingin menikmati suasana alam bersama.',
    sections: [
      ['Berawal dari obrolan sederhana', 'Sebuah ajakan di grup pertemanan menjadi awal rencana perjalanan. Setelah menyepakati tanggal, semua mulai mencari pengalaman yang bisa dinikmati bersama.'],
      ['Menikmati perjalanan bersama', 'Ada yang sibuk mengambil foto dan ada yang lebih suka menikmati suasana. Perjalanan memberi ruang untuk obrolan yang jarang terjadi di tengah kesibukan.'],
      ['Cerita yang dibawa pulang', 'Selain dokumentasi, momen kebersamaan menjadi hal yang paling diingat. Rencana petualangan berikutnya pun mulai dibicarakan.'],
    ],
  },
  {
    slug: 'contoh-suasana-cave-tubing', category: 'destinasi', image: tubing,
    title: 'Menikmati Suasana Sungai dan Lorong Goa', enTitle: 'Discover River Scenery and Cave Passages',
    excerpt: 'Mengenal sisi lain petualangan alam melalui suasana sungai dan kawasan karst.',
    sections: [
      ['Suasana alam yang berbeda', 'Aliran sungai dan dinding goa menghadirkan pemandangan yang khas. Ini menjadi inspirasi untuk perjalanan dengan suasana yang berbeda dari keseharian.'],
      ['Kenali kegiatan yang dipilih', 'Baca detail aktivitas dan persyaratan peserta dalam paket. Pastikan pilihan perjalanan sesuai dengan kondisi dan kebutuhan kelompokmu.'],
      ['Ikuti arahan di lokasi', 'Gunakan perlengkapan sesuai instruksi tim dan ikuti briefing sebelum kegiatan. Nikmati pengalaman dengan mengikuti rute yang telah ditentukan.'],
    ],
  },
  {
    slug: 'contoh-tips-dokumentasi-perjalanan', category: 'panduan', image: jomblang,
    title: 'Merekam Momen tanpa Melewatkan Perjalanan', enTitle: 'Capture the Moment and Enjoy the Journey',
    excerpt: 'Ide sederhana untuk membawa pulang dokumentasi sekaligus menikmati pengalaman di lokasi.',
    sections: [
      ['Tentukan momen yang ingin disimpan', 'Tidak semua detik perlu direkam. Pilih beberapa momen yang paling menggambarkan pengalamanmu dan sisakan waktu untuk menikmati suasana.'],
      ['Tanyakan pilihan dokumentasi', 'Periksa layanan dokumentasi pada paket yang kamu pesan. Tanyakan mekanisme pengambilan dan penyerahan hasil kepada tim.'],
      ['Utamakan arahan pemandu', 'Gunakan kamera atau ponsel hanya di area yang diperbolehkan. Ikuti instruksi tim saat memilih posisi untuk mengambil gambar.'],
    ],
  },
  {
    slug: 'contoh-journal-maua-ruang-cerita-baru', category: 'berita', image: greenery,
    title: 'MAUA Journal: Ruang Baru untuk Berbagi Cerita', enTitle: 'MAUA Journal: A New Space for Stories',
    excerpt: 'Contoh pengumuman untuk mengenalkan ruang artikel, inspirasi, dan panduan perjalanan.',
    sections: [
      ['Cerita dari setiap perjalanan', 'MAUA Journal menjadi tempat untuk merangkum inspirasi destinasi, panduan, dan cerita perjalanan. Pengunjung dapat memilih topik sesuai rasa penasarannya.'],
      ['Temukan topik yang kamu cari', 'Gunakan kategori dan kolom pencarian untuk menjelajahi artikel. Setiap tulisan dapat dibuka pada halaman sendiri dengan daftar isi yang membantu membaca.'],
      ['Masih banyak cerita berikutnya', 'Halaman ini adalah contoh konten untuk meninjau tampilan lokal. Admin dapat menggantinya dengan artikel dan kabar resmi setelah materi siap.'],
    ],
  },
  {
    slug: 'contoh-pertanyaan-sebelum-memesan-trip', category: 'panduan', image: trip,
    title: 'Lima Hal yang Perlu Dicek Sebelum Memesan Trip', enTitle: 'Five Things to Check Before Booking a Trip',
    excerpt: 'Buat keputusan perjalanan dengan memahami informasi paket sejak awal.',
    sections: [
      ['Jadwal dan titik pertemuan', 'Pastikan tanggal, sesi, serta lokasi pertemuan sesuai dengan rencanamu. Pertimbangkan perjalanan menuju titik temu sebelum memilih jadwal.'],
      ['Aktivitas, fasilitas, dan peserta', 'Baca rincian aktivitas serta fasilitas yang termasuk dalam paket. Periksa juga ketentuan dan persyaratan untuk seluruh peserta.'],
      ['Pembayaran dan konfirmasi', 'Ikuti informasi pembayaran pada proses pemesanan. Simpan konfirmasi perjalanan dan tanyakan hal yang belum jelas kepada admin.'],
    ],
  },
  {
    slug: 'contoh-menjelajah-alam-bersama-rombongan', category: 'cerita', image: caving,
    title: 'Keluar dari Rutinitas, Menjelajah Bersama Rombongan', enTitle: 'Step Outside Your Routine with a Group Adventure',
    excerpt: 'Contoh inspirasi kegiatan bersama untuk kelompok yang ingin mencoba pengalaman berbeda.',
    sections: [
      ['Menyepakati rencana bersama', 'Mulai dari jumlah peserta dan pilihan waktu perjalanan. Bagikan informasi kegiatan kepada seluruh rombongan agar semua memahami rencananya.'],
      ['Membagi pengalaman baru', 'Perjalanan di alam dapat menjadi kesempatan untuk mengenal rekan perjalanan lebih dekat. Ada banyak momen kecil yang terasa berbeda saat dinikmati bersama.'],
      ['Menutup hari dengan cerita', 'Setelah kegiatan, luangkan waktu untuk bertukar kesan. Dokumentasi dan cerita bisa menjadi kenangan yang dibagikan kembali kepada kelompok.'],
    ],
  },
  {
    slug: 'contoh-cara-menemukan-petualangan-berikutnya', category: 'berita', image: tubing,
    title: 'Temukan Inspirasi untuk Petualangan Berikutnya', enTitle: 'Find Inspiration for Your Next Adventure',
    excerpt: 'Contoh artikel pengantar untuk menjelajahi kategori blog dan pilihan perjalanan.',
    sections: [
      ['Mulai dari rasa penasaran', 'Apakah kamu tertarik dengan goa, sungai, atau perjalanan bersama teman? Gunakan topik favorit sebagai awal untuk mencari ide kegiatan.'],
      ['Baca sebelum merencanakan', 'Artikel membantu memberi gambaran awal, sementara detail kegiatan dan ketentuan tetap mengikuti paket perjalanan yang dipilih.'],
      ['Diskusikan rencanamu', 'Setelah menemukan inspirasi, periksa pilihan paket dan jadwal yang tersedia. Tanyakan kebutuhan khusus kelompokmu kepada tim.'],
    ],
  },
]

export const demoPosts = examples.map((example, index) => {
  const date = `2026-10-${String(13 - index).padStart(2, '0')}T09:00:00+07:00`
  return {
    id: `maua-demo-${index + 1}`, slug: example.slug, category: example.category, author: 'Tim MAUA', status: 'published', isDemo: true,
    cover: example.image, coverAlt: 'Dokumentasi petualangan bersama MAUA', coverWidth: 100, coverAlign: 'center',
    createdAt: date, publishedAt: date, updatedAt: date,
    idContent: {
      title: example.title, excerpt: example.excerpt, seoTitle: '', seoDescription: '',
      body: `<p><em>Artikel contoh untuk pratinjau lokal. Konten ini dapat diganti sebelum digunakan sebagai artikel resmi.</em></p><p>${example.excerpt}</p>${example.sections.map(([title, paragraph]) => `<h2>${title}</h2><p>${paragraph}</p>`).join('')}`,
    },
    enContent: { title: example.enTitle, excerpt: 'A sample MAUA Journal article for reviewing the local blog layout.', body: `<p><em>This sample article is for local preview only.</em></p><h2>A new story to discover</h2><p>Explore destinations, travel inspiration, and moments shared with your travel companions.</p><h2>Plan your experience</h2><p>Review your selected trip details, meeting point, and participant requirements. Contact the MAUA team with any questions before booking.</p><h2>Make it your own</h2><p>The administrator can replace this example with a complete story in the editor.</p>`, seoTitle: '', seoDescription: '' },
  }
})
