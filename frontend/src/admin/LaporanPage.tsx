import { useEffect, useState } from 'react'
import { Link, useParams } from 'react-router-dom'
import client from '../api/client'

type Config = {
  title: string
  desc: string
  icon: string
  accent: string
  iconBg: string
  badgeBg: string
  badgeColor: string
  folderColor: string
}

const CONFIGS: Record<string, Config> = {
  p3k: {
    title: 'Laporan Inspeksi Kotak P3K',
    desc: 'Klik bulan untuk melihat data hasil inspeksi Kotak P3K',
    icon: 'fa-kit-medical',
    accent: '#ef4444',
    iconBg: '#fee2e2',
    badgeBg: '#fee2e2',
    badgeColor: '#ef4444',
    folderColor: '#f0a500',
  },
  lampu_exit: {
    title: 'Laporan Inspeksi Lampu Exit',
    desc: 'Klik bulan untuk melihat data hasil inspeksi Lampu Exit',
    icon: 'fa-door-open',
    accent: '#10b981',
    iconBg: '#d1fae5',
    badgeBg: '#d1fae5',
    badgeColor: '#10b981',
    folderColor: '#f0a500',
  },
  lampu_emergency: {
    title: 'Laporan Inspeksi Lampu Emergency',
    desc: 'Klik bulan untuk melihat data hasil inspeksi Lampu Emergency',
    icon: 'fa-bolt',
    accent: '#f59e0b',
    iconBg: '#fef3c7',
    badgeBg: '#fef3c7',
    badgeColor: '#f59e0b',
    folderColor: '#f0a500',
  },
  eyewash: {
    title: 'Laporan Inspeksi Eye Wash',
    desc: 'Klik bulan untuk melihat data hasil inspeksi Eye Wash',
    icon: 'fa-eye',
    accent: '#3b82f6',
    iconBg: '#dbeafe',
    badgeBg: '#dbeafe',
    badgeColor: '#3b82f6',
    folderColor: '#f0a500',
  },
}

type Month = { bulan: string; total: number; has_data: boolean }

export default function LaporanPage() {
  const { type = 'p3k' } = useParams()
  const cfg: Config = CONFIGS[type] ?? CONFIGS.p3k

  const [tahun, setTahun] = useState(new Date().getFullYear())
  const [months, setMonths] = useState<Month[]>([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    setLoading(true)
    client
      .get('/laporan.php', { params: { type, tahun } })
      .then((res) => {
        setMonths(res.data.months || [])
        setTahun(res.data.tahun)
      })
      .finally(() => setLoading(false))
  }, [type, tahun])

  return (
    <>
      <style>{`
        .laporan-header { display: flex; align-items: center; gap: 14px; border-left: 6px solid ${cfg.accent}; background: #fff; padding: 18px 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,.1); margin-bottom: 20px; }
        .lh-icon { width: 52px; height: 52px; border-radius: 12px; background: ${cfg.iconBg}; color: ${cfg.accent}; display: flex; align-items: center; justify-content: center; font-size: 22px; }
        .laporan-header h2 { font-size: 20px; font-weight: 700; color: #1e293b; margin: 0; }
        .laporan-header p { font-size: 13px; color: #64748b; margin: 3px 0 0; }
        .year-nav { display: flex; align-items: center; justify-content: center; gap: 18px; margin-bottom: 24px; }
        .year-nav h3 { font-size: 28px; min-width: 110px; text-align: center; color: #1e293b; margin: 0; }
        .btn-year { background: ${cfg.accent}; color: #fff; text-decoration: none; padding: 10px 16px; border-radius: 6px; font-size: 13px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; }
        .btn-year:hover { opacity: .85; }
        .month-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; }
        @media (max-width: 900px) { .month-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 520px) { .month-grid { grid-template-columns: 1fr; } }
        .month-card { background: #fff; border: 2px solid transparent; border-radius: 12px; padding: 22px 16px; text-align: center; text-decoration: none; box-shadow: 0 2px 6px rgba(0,0,0,.05); transition: .2s; }
        .month-card:hover { border-color: ${cfg.accent}; transform: translateY(-2px); }
        .folder-icon { font-size: 52px; margin-bottom: 14px; display: block; }
        .month-card.has-data .folder-icon { color: ${cfg.folderColor}; }
        .month-card.no-data .folder-icon { color: #cbd5e1; }
        .month-name { font-size: 14px; font-weight: 600; color: #1e293b; margin-bottom: 10px; }
        .month-card.no-data .month-name { color: #94a3b8; }
        .record-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .month-card.has-data .record-badge { background: ${cfg.badgeBg}; color: ${cfg.badgeColor}; }
        .month-card.no-data .record-badge { background: #f1f5f9; color: #94a3b8; }
      `}</style>

      <div className="laporan-header">
        <div className="lh-icon"><i className={`fa-solid ${cfg.icon}`}></i></div>
        <div>
          <h2>{cfg.title}</h2>
          <p>{cfg.desc}</p>
        </div>
      </div>

      <div className="year-nav">
        <a href="#" className="btn-year" onClick={(e) => { e.preventDefault(); setTahun(tahun - 1) }}>
          <i className="fa-solid fa-chevron-left"></i> Tahun Sebelumnya
        </a>
        <h3>{tahun}</h3>
        <a href="#" className="btn-year" onClick={(e) => { e.preventDefault(); setTahun(tahun + 1) }}>
          Tahun Berikutnya <i className="fa-solid fa-chevron-right"></i>
        </a>
      </div>

      <div className="month-grid">
        {loading
          ? Array.from({ length: 12 }, (_, i) => (
              <div key={i} className="month-card no-data" style={{ color: '#94a3b8', fontSize: 13 }}>Memuat...</div>
            ))
          : months.map((m) => (
              <Link
                key={m.bulan}
                to={`/admin/data-inspeksi/${type}?bulan=${encodeURIComponent(m.bulan)}&tahun=${tahun}`}
                className={`month-card ${m.has_data ? 'has-data' : 'no-data'}`}
              >
                <i className={`fa-solid ${m.has_data ? 'fa-folder-open' : 'fa-folder'} folder-icon`}></i>
                <div className="month-name">{m.bulan} {tahun}</div>
                <span className="record-badge">{m.has_data ? `${m.total} data` : 'Kosong'}</span>
              </Link>
            ))}
      </div>
    </>
  )
}