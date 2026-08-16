import { useState, type ReactNode } from 'react'
import { Link } from 'react-router-dom'

type Feature = { icon: ReactNode; title: string; desc: string }

const foto = (f: string) => `${import.meta.env.BASE_URL}foto/${f}`

const img = (src: string) => <img src={src} alt="" />

const features: Feature[] = [
  { icon: img(foto('Pengecekan.png')), title: 'Pengecekan Rutin', desc: 'Pantau jadwal pengecekan Alat keamanan secara berkala agar selalu siap pakai.' },
  { icon: img(foto('laporan.png')), title: 'Laporan Digital', desc: 'Semua laporan pengecekan tersimpan rapi dan bisa diakses kapan saja.' },
  { icon: <span>🔔</span>, title: 'Notifikasi Otomatis', desc: 'Dapatkan pengingat otomatis untuk pengecekan dan perawatan.' },
  { icon: img(foto('qr.png')), title: 'QR Code Scanner', desc: 'Akses data pengecekan Alat lebih cepat dengan memindai QR Code pada perangkat.' },
  { icon: img(foto('manajemen.png')), title: 'Manajemen Pengguna & Admin', desc: 'Kelola peran pengguna dan admin dengan akses yang terstruktur dan aman.' },
  { icon: img(foto('Visualisasi.png')), title: 'Visualisasi Data', desc: 'Pantau hasil pengecekan dengan grafik interaktif untuk analisis yang lebih mudah.' },
]

const footerLinks = [
  ['Tentang Kami', 'Aplikasi Pengecekan Fasilitas Keselamatan membantu perusahaan melakukan monitoring dan pengecekan Fasilitas keselamatan berbasis QR Code agar lebih cepat, efisien, dan terdokumentasi.'],
  ['Layanan', 'Daftar layanan kami...'],
  ['Kebijakan Privasi', 'Kami menghargai privasi pengguna. Data hanya digunakan untuk kepentingan monitoring APAR dan tidak akan dibagikan kepada pihak ketiga tanpa izin resmi.'],
  ['Bantuan', 'Butuh bantuan? hubungi...'],
  ['Kontak', 'Informasi kontak kami...'],
]

export default function LandingPage() {
  const [learnOpen, setLearnOpen] = useState(false)
  const [dynamic, setDynamic] = useState<{ title: string; content: string } | null>(null)

  return (
    <div className="bg-gray-50 text-gray-800 antialiased">
      <header className="bg-white shadow-sm sticky top-0 z-50">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
          <div className="flex items-center space-x-3">
            <img src={foto('logo.png')} alt="Safety Facility Logo" className="h-12 w-auto object-contain" />
            <span className="text-xl font-bold text-slate-800 sm:inline">Pengecekan fasilitas keselamatan</span>
          </div>
          <div className="flex items-center space-x-3">
            <Link to="/login" className="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-md text-sm transition unique-btn">Login Bogor</Link>
            <Link to="/login" className="border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium px-4 py-2 rounded-md text-sm transition">Login Maja</Link>
          </div>
        </div>
      </header>

      <section className="bg-[#000766] text-white overflow-hidden py-16 lg:py-24">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
          <div className="lg:col-span-7 space-y-6 animate-slide-up">
            <h1 className="text-4xl sm:text-5xl font-extrabold tracking-tight leading-tight">
              Sistem Manajemen <br />
              <span className="text-yellow-300">Pengecekan fasilitas keselamatan</span>
            </h1>
            <p className="text-lg text-red-100 max-w-xl leading-relaxed">
              Kelola pengecekan dan perawatan fasilitas keselamatan dengan lebih mudah, cepat, dan terorganisir.
            </p>
            <div>
              <button
                onClick={() => setLearnOpen(true)}
                className="bg-yellow-400 hover:bg-yellow-500 text-slate-900 font-semibold px-6 py-3 rounded-lg shadow-md transition flex items-center space-x-2 text-sm"
              >
                <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
                  <path strokeLinecap="round" strokeLinejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <span>Learn</span>
              </button>
            </div>
          </div>
          <div className="lg:col-span-5 flex justify-center lg:justify-end animate-slide-right">
            <div className="relative max-w-md w-full rounded-2xl overflow-hidden shadow-2xl border border-white/10">
              <img src={foto('foto.png')} alt="Petugas K3 melakukan pengecekan APAR" className="w-full h-auto object-cover block" />
            </div>
          </div>
        </div>
      </section>

      <section className="bg-dots py-20 border-b border-gray-100">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center max-w-3xl mx-auto mb-16 space-y-3">
            <h2 className="text-3xl font-bold text-slate-800 tracking-tight">Fitur Utama</h2>
            <p className="text-gray-500 text-sm">Semua yang kamu butuhkan untuk mengelola pengecekan FASILITAS KESELAMATAN lebih mudah.</p>
          </div>
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            {features.map((f, i) => (
              <div
                key={f.title}
                className="bg-white p-8 rounded-2xl border border-gray-100 shadow-xs hover:shadow-md transition duration-200 text-center flex flex-col items-center animate-slide-up"
                style={{ animationDelay: `${(i + 1) * 0.15}s` }}
              >
                <div className="w-14 h-14 bg-gray-50 rounded-xl flex items-center justify-center text-2xl mb-5">{f.icon}</div>
                <h3 className="text-lg font-bold text-slate-800 mb-3">{f.title}</h3>
                <p className="text-gray-500 text-sm leading-relaxed max-w-xs">{f.desc}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      <footer className="bg-[#111827] text-gray-400 pt-16 pb-8 border-t border-gray-800">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-12 gap-12 pb-12 border-b border-gray-800">
          <div className="md:col-span-5 space-y-4">
            <h3 className="text-xl font-bold text-white">Pengecekan FASILITAS KESELAMATAN</h3>
            <p className="text-sm leading-relaxed max-w-sm text-gray-400">
              Sistem digital untuk memudahkan pengecekan dan pelaporan FASILITAS KESELAMATAN secara efisien, cepat, dan terstruktur.
            </p>
          </div>
          <div className="md:col-span-3 space-y-4">
            <h4 className="text-sm font-semibold text-white uppercase tracking-wider">Navigasi</h4>
            <ul className="space-y-2.5 text-sm">
              {footerLinks.map(([title, content]) => (
                <li key={title}>
                  <button onClick={() => setDynamic({ title, content })} className="hover:text-white transition">{title}</button>
                </li>
              ))}
            </ul>
          </div>
          <div className="md:col-span-4 space-y-4">
            <h4 className="text-sm font-semibold text-white uppercase tracking-wider">Kontak</h4>
            <ul className="space-y-2 text-sm text-gray-400">
              <li>Email: <span className="text-gray-300">support@gmail.com</span></li>
              <li>Telp: <span className="text-gray-300">+62 821-2509-8439</span></li>
            </ul>
            <div className="flex items-center space-x-4 pt-2">
              <a href="https://facebook.com" className="w-8 h-8 rounded-full bg-gray-800 hover:bg-blue-600 text-white flex items-center justify-center transition text-sm"><img src={foto('facebook.png')} alt="" /></a>
              <a href="https://instagram.com" className="w-8 h-8 rounded-full bg-gray-800 hover:bg-pink-600 text-white flex items-center justify-center transition text-sm"><img src={foto('instagram.png')} alt="" /></a>
              <a href="#" className="w-8 h-8 rounded-full bg-gray-800 hover:bg-blue-700 text-white flex items-center justify-center transition text-sm"><img src={foto('linkedin.png')} alt="" /></a>
              <a href="#" className="w-8 h-8 rounded-full bg-gray-800 hover:bg-emerald-600 text-white flex items-center justify-center transition text-sm">w</a>
            </div>
          </div>
        </div>
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 flex flex-col sm:flex-row items-center justify-between text-xs text-gray-500">
          <p>&copy; {new Date().getFullYear()} Pengecekan FASILITAS KESELAMATAN. All rights reserved.</p>
          <p className="mt-2 sm:mt-0">v1.0.0</p>
        </div>
      </footer>

      {learnOpen && (
        <div className="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
          <div className="bg-white rounded-2xl max-w-lg w-full p-8 shadow-2xl relative">
            <button onClick={() => setLearnOpen(false)} className="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
              <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
            <h3 className="text-2xl font-bold text-slate-800 mb-4">Tentang Fasilitas Keselamatan</h3>
            <p className="text-gray-600 leading-relaxed mb-4">
              Fasilitas keselamatan adalah perangkat penting yang dirancang untuk melindungi jiwa dan aset dari bahaya.
              Contohnya meliputi APAR (Alat Pemadam Api Ringan), Hydrant, Jalur Evakuasi, dan Detektor Asap.
              Sistem ini membantu Anda memastikan setiap perangkat tersebut dalam kondisi prima melalui inspeksi berkala.
            </p>
            <button onClick={() => setLearnOpen(false)} className="w-full bg-blue-600 text-white font-medium py-2 rounded-lg hover:bg-blue-700 transition">Mengerti</button>
          </div>
        </div>
      )}

      {dynamic && (
        <div className="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
          <div className="bg-white rounded-2xl max-w-lg w-full p-8 shadow-2xl relative">
            <button onClick={() => setDynamic(null)} className="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
              <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
            <h3 className="text-2xl font-bold text-slate-800 mb-4">{dynamic.title}</h3>
            <div className="text-gray-600 leading-relaxed mb-6">{dynamic.content}</div>
            <button onClick={() => setDynamic(null)} className="w-full bg-blue-600 text-white font-medium py-2 rounded-lg hover:bg-blue-700 transition">Tutup</button>
          </div>
        </div>
      )}
    </div>
  )
}