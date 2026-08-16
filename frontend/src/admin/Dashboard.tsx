import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import client from '../api/client'
import { useOperator } from './AdminLayout'

type DashboardData = {
  total_pengguna: number
  total_fasilitas: number
  total_rusak: number
}

export default function Dashboard() {
  const { namaOperator } = useOperator()
  const [data, setData] = useState<DashboardData>({ total_pengguna: 0, total_fasilitas: 0, total_rusak: 0 })
  const [alertHidden, setAlertHidden] = useState(false)

  useEffect(() => {
    client.get('/dashboard.php').then((res) => {
      setData({
        total_pengguna: res.data.total_pengguna,
        total_fasilitas: res.data.total_fasilitas,
        total_rusak: res.data.total_rusak,
      })
    })
  }, [namaOperator])

  const shortcuts = [
    { to: '/admin/scan', icon: 'fa-qrcode', title: 'Scan QR Code' },
    { to: '/admin/master-lampu', icon: 'fa-boxes-stacked', title: 'Data Master' },
    { to: '/admin/agenda', icon: 'fa-calendar-check', title: 'Agenda Inspeksi' },
    { to: '/admin/laporan/lampu_emergency', icon: 'fa-triangle-exclamation', title: 'Rusak & Expired' },
    { to: '/admin/laporan/lampu_emergency', icon: 'fa-file-lines', title: 'Laporan Inspeksi' },
    { to: '/admin', icon: 'fa-users', title: 'Pengguna' },
  ]

  return (
    <>
      <h2 className="page-title">Dashboard</h2>

      {data.total_rusak > 0 && !alertHidden && (
        <div className="alert-panel" id="alertNotification">
          <div className="alert-header">
            <span>Peringatan</span>
            <span className="alert-close" onClick={() => setAlertHidden(true)}>&times;</span>
          </div>
          <div className="alert-body">
            Ada {data.total_rusak} Fasilitas Keselamatan yang rusak, tidak lengkap, atau butuh perbaikan pada bulan ini.
          </div>
        </div>
      )}

      <div className="grid-shortcuts">
        {shortcuts.map((s) => (
          <Link key={s.title} to={s.to} className="shortcut-card">
            <i className={`fa-solid ${s.icon} shortcut-icon`}></i>
            <span className="shortcut-title">{s.title}</span>
          </Link>
        ))}
      </div>

      <div className="grid-stats">
        <div className="stat-card">
          <div className="stat-info">
            <h2>Total Pengguna</h2>
            <p>{data.total_pengguna}</p>
          </div>
          <div className="stat-icon-box bg-blue">
            <i className="fa-solid fa-users"></i>
          </div>
        </div>
        <div className="stat-card">
          <div className="stat-info">
            <h2>Total Fasilitas</h2>
            <p>{data.total_fasilitas}</p>
          </div>
          <div className="stat-icon-box bg-lightblue">
            <i className="fa-solid fa-shield-heart"></i>
          </div>
        </div>
        <div className="stat-card">
          <div className="stat-info">
            <h2>Total Masalah</h2>
            <p>{data.total_rusak}</p>
          </div>
          <div className="stat-icon-box bg-red">
            <i className="fa-solid fa-triangle-exclamation"></i>
          </div>
        </div>
      </div>
    </>
  )
}