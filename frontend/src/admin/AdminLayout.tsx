import { createContext, useContext, useEffect, useState } from 'react'
import { Link, NavLink, Outlet, useLocation, useNavigate } from 'react-router-dom'
import client from '../api/client'
import '../styles/admin.css'
import { formatTanggalIndonesia } from '../utils'

type OperatorContextType = {
  namaOperator: string
  setNamaOperator: (name: string) => void
}

const OperatorContext = createContext<OperatorContextType>({ namaOperator: '', setNamaOperator: () => {} })

export const useOperator = () => useContext(OperatorContext)

const navItems = [
  { to: '/admin', label: 'Dashboard', icon: 'fa-gauge', end: true },
  { to: '/admin/scan', label: 'Scan Code', icon: 'fa-qrcode' },
  { to: '/login', label: 'Data Pengguna', icon: 'fa-users' },
]

const submenus = [
  {
    icon: 'fa-boxes-stacked',
    label: 'Data Master',
    items: [
      { to: '/admin/master-lampu', label: '• Lampu Emergency' },
      { to: '/admin/lampu-exit', label: '• Lampu Exit' },
      { to: '/admin/master-p3k', label: '• Kotak P3K' },
      { to: '/admin/master-eyewash', label: '• Eye Wash' },
    ],
  },
  {
    icon: 'fa-location-dot',
    label: 'Area Line',
    items: [
      { to: '/admin/area-line/lampu_emergency', label: '• Lampu Emergency' },
      { to: '/admin/area-line/lampu_exit', label: '• Lampu Exit' },
      { to: '/admin/area-line/p3k', label: '• Kotak P3K' },
      { to: '/admin/area-line/eyewash', label: '• Eye Wash' },
    ],
  },
  {
    icon: 'fa-file-invoice',
    label: 'Laporan Inspeksi',
    items: [
      { to: '/admin/laporan/lampu_emergency', label: '• Lampu Emergency' },
      { to: '/admin/laporan/lampu_exit', label: '• Lampu Exit' },
      { to: '/admin/laporan/p3k', label: '• Kotak P3K' },
      { to: '/admin/laporan/eyewash', label: '• Eye Wash' },
    ],
  },
]

function useMediaQuery(query: string) {
  const [matches, setMatches] = useState(() => window.matchMedia(query).matches)
  useEffect(() => {
    const mql = window.matchMedia(query)
    const handler = (e: MediaQueryListEvent) => setMatches(e.matches)
    mql.addEventListener('change', handler)
    setMatches(mql.matches)
    return () => mql.removeEventListener('change', handler)
  }, [query])
  return matches
}

export default function AdminLayout() {
  const location = useLocation()
  const navigate = useNavigate()
  const isMobile = useMediaQuery('(max-width: 768px)')
  const [minimized, setMinimized] = useState(false)
  const [openSub, setOpenSub] = useState<string | null>(null)
  const [notifOpen, setNotifOpen] = useState(false)
  const [namaOperator, setNamaOperator] = useState('')
  const [namaLengkap, setNamaLengkap] = useState('')
  const [totalRusak, setTotalRusak] = useState(0)
  const [detailNotifikasi, setDetailNotifikasi] = useState<{ icon: string; text: string; color: string; url: string }[]>([])
  const [operatorModal, setOperatorModal] = useState(false)
  const [operatorInput, setOperatorInput] = useState('')
  const [tanggal, setTanggal] = useState(formatTanggalIndonesia())

  useEffect(() => {
    const now = new Date()
    const pad = (n: number) => String(n).padStart(2, '0')
    const min = 60_000 - now.getSeconds() * 1000
    const t = setTimeout(() => setTanggal(formatTanggalIndonesia()), min)
    return () => clearTimeout(t)
  }, [tanggal])

  useEffect(() => {
    Promise.all([
      client.get('/auth.php?action=check'),
      client.get('/dashboard.php'),
      client.get('/dashboard.php?action=check_operator'),
    ])
      .then(([authRes, dashRes, opRes]) => {
        setNamaLengkap(authRes.data.nama_lengkap || 'Admin')
        setTotalRusak(dashRes.data.total_rusak || 0)
        setDetailNotifikasi(dashRes.data.detail_notifikasi || [])
        setTanggal(dashRes.data.tanggal_format || tanggal)
        setNamaOperator(opRes.data.nama_operator || '')
        if (!opRes.data.has_operator) {
          setOperatorModal(true)
        }
      })
      .catch(() => {
        navigate('/login')
      })
  }, [])

  useEffect(() => {
    setNotifOpen(false)
    if (isMobile) setMinimized(false)
  }, [location.pathname, isMobile])

  async function saveOperator(e: React.FormEvent) {
    e.preventDefault()
    try {
      const res = await client.post('/dashboard.php?action=set_operator', new URLSearchParams({ nama_operator: operatorInput }), {
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      })
      if (res.data.success) {
        setNamaOperator(res.data.nama_operator)
        setOperatorModal(false)
      }
    } catch {
      setOperatorModal(true)
    }
  }

  async function handleLogout() {
    await client.post('/auth.php', new URLSearchParams({ action: 'logout' }), {
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    })
    navigate('/login')
  }

  const isActiveSub = (items: { to: string }[]) => items.some((it) => location.pathname.startsWith(it.to))
  const autoOpen = submenus.find((s) => isActiveSub(s.items))

  return (
    <OperatorContext.Provider value={{ namaOperator, setNamaOperator }}>
      <div className="admin-app">
      <aside className={`sidebar${minimized ? ' minimized' : ''}`} id="sidebar">
        <div className="sidebar-brand">
          <img src={`${import.meta.env.BASE_URL}foto/logo.png`} alt="Safety Facility Logo" className="sidebar-logo" />
        </div>
        <ul className="sidebar-menu">
          {navItems.map((item) => (
            <li key={item.label} className={location.pathname === item.to || (item.end && location.pathname === '/admin') ? 'active' : ''}>
              <Link to={item.to}><i className={`fa-solid ${item.icon}`}></i> <span>{item.label}</span></Link>
            </li>
          ))}

          {submenus.map((sub) => {
            const open = openSub === sub.label || (autoOpen?.label === sub.label && openSub === null)
            return (
              <li className="has-submenu" key={sub.label}>
                <a
                  href="#"
                  onClick={(e) => {
                    e.preventDefault()
                    setOpenSub(open ? null : sub.label)
                  }}
                >
                  <i className={`fa-solid ${sub.icon}`}></i>
                  <span>{sub.label}</span>
                  <i className="fa-solid fa-chevron-down submenu-icon" style={{ transform: open ? 'rotate(180deg)' : 'rotate(0deg)' }}></i>
                </a>
                <ul className="submenu" style={{ display: open ? 'block' : 'none' }}>
                  {sub.items.map((it) => (
                    <li key={it.to} className={location.pathname === it.to ? 'active' : ''}>
                      <Link to={it.to}>{it.label}</Link>
                    </li>
                  ))}
                </ul>
              </li>
            )
          })}

          <li><Link to="/admin/agenda"><i className="fa-solid fa-calendar-check"></i> <span>Agenda Inspeksi</span></Link></li>

          <li style={{ marginTop: '20px' }}>
            <a href="#" onClick={(e) => { e.preventDefault(); handleLogout() }}>
              <i className="fa-solid fa-right-from-bracket"></i> <span>Log out</span>
            </a>
          </li>
        </ul>
      </aside>

      <div
        className={`sidebar-overlay${isMobile && minimized ? ' show' : ''}`}
        onClick={() => setMinimized(false)}
      ></div>

      <main className="main-content" style={{ marginLeft: minimized ? '70px' : '230px' }}>
        <header className="topbar">
          <div className="topbar-left">
            <button className="toggle-sidebar-btn" onClick={() => setMinimized(!minimized)}>
              <i className="fa-solid fa-bars"></i>
            </button>
            <div className="topbar-date">{tanggal}</div>
          </div>
          <div className="topbar-right">
            {location.pathname === '/admin' && (
              <div className="notification-container">
                <div className="notification-icon" onClick={() => setNotifOpen(!notifOpen)}>
                  <i className="fa-regular fa-bell"></i>
                  {totalRusak > 0 && <span className="notification-badge">{totalRusak}</span>}
                </div>
                {notifOpen && (
                  <div className="notification-dropdown show" id="notificationDropdown">
                    <div className="noti-header">
                      <span>Notifikasi Masalah</span>
                      <small>{totalRusak} Temuan</small>
                    </div>
                    <div className="noti-body">
                      {detailNotifikasi.length > 0 ? (
                        detailNotifikasi.map((n, i) => (
                          <Link key={i} to={`/admin/${n.url}`} className="noti-item">
                            <i className={`fa-solid ${n.icon}`} style={{ color: n.color }}></i>
                            <div>{n.text}</div>
                          </Link>
                        ))
                      ) : (
                        <div className="noti-empty">SEMUA FASILITAS DALAM KONDISI BAIK!</div>
                      )}
                    </div>
                  </div>
                )}
              </div>
            )}
            <div className="user-profile">
              <span>Hi, <strong>{namaLengkap || 'Admin'}</strong></span>
              <div className="user-avatar">
                <img src="https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?q=80&w=100" alt="Avatar" />
              </div>
            </div>
          </div>
        </header>

        <section className="content-body">
          <Outlet />
        </section>
      </main>

      {operatorModal && (
        <div className="modal" style={{ display: 'flex' }} id="operatorModal">
          <div className="modal-content">
            <h2 style={{ marginBottom: '20px' }}>Masukkan Nama Anda</h2>
            <form onSubmit={saveOperator}>
              <label style={{ display: 'block', marginBottom: '8px' }}>Nama Operator</label>
              <input
                type="text"
                value={operatorInput}
                onChange={(e) => setOperatorInput(e.target.value)}
                placeholder="Masukkan nama operator"
                required
                style={{ width: '100%', padding: '12px', border: '1px solid #ddd', borderRadius: '6px', marginBottom: '15px' }}
              />
              <button type="submit" style={{ background: '#0d6efd', color: 'white', border: 'none', padding: '10px 20px', borderRadius: '6px', cursor: 'pointer' }}>
                Simpan
              </button>
            </form>
          </div>
        </div>
      )}
      </div>
      <style>{`
        .notification-container { position: relative; display: inline-block; }
        .notification-icon { cursor: pointer; padding: 5px; position: relative; }
        .notification-dropdown { display: block; position: absolute; right: 0; top: 40px; background-color: #ffffff; width: 320px; max-width: calc(100vw - 24px); box-shadow: 0px 8px 24px rgba(0,0,0,0.15); border-radius: 8px; z-index: 1000; border: 1px solid #eef2f5; overflow: hidden; }
        .noti-header { padding: 12px 16px; background: #f8f9fa; border-bottom: 1px solid #eee; font-weight: 600; font-size: 14px; color: #333; display: flex; justify-content: space-between; align-items: center; }
        .noti-body { max-height: 280px; overflow-y: auto; }
        .noti-item { display: flex; align-items: flex-start; padding: 12px 16px; border-bottom: 1px solid #f1f1f1; transition: background 0.2s; text-decoration: none; color: #444; font-size: 13px; }
        .noti-item:hover { background-color: #f9fafb; }
        .noti-item i { margin-right: 12px; margin-top: 2px; font-size: 16px; }
        .noti-empty { padding: 20px; text-align: center; color: #888; font-size: 13px; }
      `}</style>
    </OperatorContext.Provider>
  )
}