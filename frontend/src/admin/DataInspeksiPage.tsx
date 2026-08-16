import { useEffect, useState } from 'react'
import { Link, useParams, useSearchParams } from 'react-router-dom'
import client, { API_BASE } from '../api/client'

const BULAN_INDONESIA = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']
const BAIK_WORDS = ['Baik', 'Layak', 'Lengkap', 'Belum Expired', 'Mengalir', 'Bersih']
const TIDAK_WORDS = ['Tidak', 'Tidak Layak', 'Tidak Lengkap', 'Ada yang Expired', 'Tidak Mengalir', 'Kotor']

type Config = {
  title: string
  emptyText: string
  icon: string
  accent: string
  iconBg: string
  statBg: string
}

const CONFIGS: Record<string, Config> = {
  p3k: {
    title: 'Laporan Inspeksi Kotak P3K',
    emptyText: 'Tidak ada data Kotak P3K untuk',
    icon: 'fa-kit-medical',
    accent: '#ef4444',
    iconBg: '#fee2e2',
    statBg: '#ef4444',
  },
  lampu_exit: {
    title: 'Laporan Inspeksi Lampu Exit',
    emptyText: 'Tidak ada data inspeksi Lampu Exit untuk',
    icon: 'fa-door-open',
    accent: '#10b981',
    iconBg: '#d1fae5',
    statBg: '#10b981',
  },
  lampu_emergency: {
    title: 'Laporan Inspeksi Lampu Emergency',
    emptyText: 'Tidak ada data inspeksi Lampu Emergency untuk',
    icon: 'fa-bolt',
    accent: '#f59e0b',
    iconBg: '#fef3c7',
    statBg: '#f59e0b',
  },
  eyewash: {
    title: 'Laporan Inspeksi Eye Wash',
    emptyText: 'Tidak ada data inspeksi Eye Wash untuk',
    icon: 'fa-eye',
    accent: '#3b82f6',
    iconBg: '#dbeafe',
    statBg: '#3b82f6',
  },
}

type Row = Record<string, any>

const fmtDT = (v: any, withTime: boolean) => {
  if (!v) return '-'
  const d = new Date(String(v).replace(' ', 'T'))
  if (isNaN(d.getTime())) return String(v)
  const pad = (n: number) => String(n).padStart(2, '0')
  const base = `${pad(d.getDate())}-${pad(d.getMonth() + 1)}-${d.getFullYear()}`
  return withTime ? `${base} ${pad(d.getHours())}:${pad(d.getMinutes())}` : base
}

const badgeFor = (v: any) => {
  if (v === null || v === undefined || v === '') return null
  if (BAIK_WORDS.includes(v)) return <span className="badge b-green">{v}</span>
  if (TIDAK_WORDS.includes(v)) return <span className="badge b-red">{v}</span>
  return <>{v}</>
}

export default function DataInspeksiPage() {
  const { type = 'p3k' } = useParams()
  const cfg: Config = CONFIGS[type] ?? CONFIGS.p3k
  const [searchParams] = useSearchParams()

  const nowMonth = new Date().getMonth()
  const [bulan, setBulan] = useState(searchParams.get('bulan') || BULAN_INDONESIA[nowMonth])
  const [tahun, setTahun] = useState(searchParams.get('tahun') || String(new Date().getFullYear()))
  const [cari, setCari] = useState('')

  const [rows, setRows] = useState<Row[]>([])
  const [total, setTotal] = useState(0)
  const [baikCount, setBaikCount] = useState(0)
  const [tidakCount, setTidakCount] = useState(0)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    setBulan(searchParams.get('bulan') || BULAN_INDONESIA[nowMonth])
    setTahun(searchParams.get('tahun') || String(new Date().getFullYear()))
  }, [searchParams])

  const loadList = (term = cari) => {
    setLoading(true)
    client
      .get('/data_inspeksi.php', {
        params: { action: 'list', type, bulan, tahun, cari: term },
      })
      .then((res) => {
        setRows(res.data.data)
        setTotal(res.data.total)
        setBaikCount(res.data.baik_count)
        setTidakCount(res.data.tidak_count)
        setCari(res.data.cari)
        setBulan(res.data.bulan)
        setTahun(String(res.data.tahun))
      })
      .finally(() => setLoading(false))
  }

  useEffect(() => {
    loadList(cari)
  }, [type])

  function handleDelete(id: number) {
    if (confirm('Yakin hapus data ini? Tidak bisa dibatalkan.')) {
      client
        .post('/data_inspeksi.php', new URLSearchParams({ action: 'delete', type, id: String(id) } as any), {
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        })
        .then((res) => {
          alert(res.data.message)
          loadList()
        })
    }
  }

  const exportUrl = `${API_BASE}/export_excel.php?type=${type}&bulan=${encodeURIComponent(bulan)}&tahun=${tahun}&cari=${encodeURIComponent(cari)}`

  const isP3k = type === 'p3k'
  const isExit = type === 'lampu_exit'
  const isEmergency = type === 'lampu_emergency'

  const headerCols = (): string[] => {
    if (isP3k) return ['No', 'Nama Operator', 'Tanggal Inspeksi', 'Kode P3K', 'Lokasi', 'Line / Area', 'Kondisi Kotak', 'Kelengkapan Isi', 'Expired Obat', 'Keterangan', 'Aksi']
    if (isExit) return ['No', 'Nama Inspektor', 'Tanggal Inspeksi', 'ID Lampu', 'Kondisi Fisik', 'Kondisi Lampu', 'Kondisi Tulisan', 'Keterangan', 'Aksi']
    if (isEmergency) return ['No', 'Nama Operator', 'Tanggal Inspeksi', 'Code Lampu', 'Lokasi', 'Indikator', 'Lampu Mati', 'Nyala Otomatis', 'Catatan', 'Aksi']
    return ['No', 'Nama Inspektor', 'Tanggal Inspeksi', 'Kode Eye Wash', 'Lokasi', 'Aliran Air', 'Kondisi Air', 'Kondisi Kotak', 'Catatan', 'Aksi']
  }

  function renderBodyCells(r: Row): React.ReactNode {
    if (isP3k) {
      return (
        <>
          <td>{r.nama_operator ?? '-'}</td>
          <td className="td-date">{fmtDT(r.tanggal_inspeksi, true)}</td>
          {[r.code_p3k, r.lokasi, r.line_area, r.kondisi_kotak, r.kelengkapan_isi, r.expired_obat, r.keterangan].map((cv, i) => (
            <td key={i}>{badgeFor(cv ?? '-')}</td>
          ))}
        </>
      )
    }
    if (isExit) {
      return (
        <>
          <td>{r.nama_operator ?? '-'}</td>
          <td className="td-date">{fmtDT(r.tanggal_cek, true)}</td>
          {[r.id_lampu, r.kondisi_fisik, r.kondisi_lampu, r.kondisi_tulisan, r.keterangan].map((cv, i) => (
            <td key={i}>{badgeFor(cv ?? '-')}</td>
          ))}
        </>
      )
    }
    if (isEmergency) {
      const ind = r.indikator_mati_menyala ?? '-'
      const lm = r.lampu_mati ?? '-'
      const oto = r.nyala_otomatis ?? '-'
      return (
        <>
          <td>{r.nama_operator ?? '-'}</td>
          <td className="td-date">{fmtDT(r.tanggal_inspeksi, false)}</td>
          <td>{r.code_lampu ?? '-'}</td>
          <td>{r.lokasi ?? '-'}</td>
          <td><span className={`badge ${String(ind).toLowerCase() === 'mati' ? 'b-red' : 'b-green'}`}>{ind}</span></td>
          <td><span className={`badge ${String(lm).toLowerCase() === 'ya' ? 'b-red' : 'b-green'}`}>{lm}</span></td>
          <td><span className={`badge ${String(oto).toLowerCase() === 'ya' ? 'b-green' : 'b-red'}`}>{oto}</span></td>
          <td>{r.catatan ?? '-'}</td>
        </>
      )
    }
    // eyewash
    let valAir = '-', valKondisiAir = '-', valKotak = '-'
    if (r.catatan) {
      const parts = String(r.catatan).split(', ')
      if (parts.length === 3) {
        valAir = parts[0]; valKondisiAir = parts[1]; valKotak = parts[2]
      } else {
        valAir = r.catatan
      }
    }
    return (
      <>
        <td>{r.nama_lengkap ?? '-'}</td>
        <td className="td-date">{fmtDT(r.tanggal_inspeksi, false)}</td>
        <td>{r.code_eyewash}</td>
        <td>{r.lokasi ?? '-'}</td>
        <td><span className={`badge ${String(valAir).includes('Tidak') ? 'b-red' : 'b-green'}`}>{valAir}</span></td>
        <td><span className={`badge ${String(valKondisiAir).includes('Kotor') ? 'b-red' : 'b-green'}`}>{valKondisiAir}</span></td>
        <td><span className={`badge ${String(valKotak).includes('Tidak') ? 'b-red' : 'b-green'}`}>{valKotak}</span></td>
        <td>{r.catatan !== '' ? r.catatan : '-'}</td>
      </>
    )
  }

  const cols = headerCols()
  const showDelete = (r: Row) => {
    if (isP3k) return !!r.id_inspeksi
    return true
  }

  return (
    <>
      <style>{`
        .page-head { display: flex; align-items: center; gap: 16px; background: #fff; border-radius: 14px; padding: 20px 26px; margin-bottom: 22px; box-shadow: 0 2px 8px rgba(0,0,0,.05); border-left: 6px solid ${cfg.accent}; }
        .ph-icon { width: 52px; height: 52px; border-radius: 11px; background: ${cfg.iconBg}; color: ${cfg.accent}; display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0; }
        .ph-info h2 { font-size: 18px; font-weight: 700; color: #1e293b; margin: 0; }
        .ph-info p { font-size: 13px; color: #64748b; margin-top: 3px; }
        .stats-row { display: flex; gap: 14px; flex-wrap: wrap; margin-bottom: 20px; }
        .s-card { background: #fff; border-radius: 10px; padding: 14px 20px; box-shadow: 0 2px 6px rgba(0,0,0,.05); display: flex; align-items: center; gap: 12px; min-width: 155px; }
        .s-icon { width: 38px; height: 38px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 16px; color: #fff; flex-shrink: 0; }
        .s-info h4 { font-size: 11px; color: #64748b; font-weight: 500; margin: 0; }
        .s-info p { font-size: 20px; font-weight: 700; color: #1e293b; margin: 0; }
        .action-bar { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; justify-content: space-between; margin-bottom: 20px; }
        .ab-left, .ab-right { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
        .btn { display: inline-flex; align-items: center; gap: 7px; padding: 9px 18px; border-radius: 8px; border: none; font-size: 13px; font-weight: 500; cursor: pointer; text-decoration: none; font-family: inherit; white-space: nowrap; }
        .btn-back { background: #64748b; color: #fff; }
        .btn-excel { background: #1d6f42; color: #fff; }
        .btn-pdf { background: #c0392b; color: #fff; }
        .btn-back:hover { background: #475569; }
        .btn-excel:hover { background: #155534; }
        .btn-pdf:hover { background: #a93226; }
        .btn-search { background: ${cfg.accent}; color: #fff; }
        .btn-reset { background: #e2e8f0; color: #64748b; }
        .search-wrap { display: flex; gap: 7px; }
        .search-wrap input { padding: 9px 14px; border: 1px solid #d8dee9; border-radius: 8px; font-size: 13px; font-family: inherit; outline: none; width: 240px; }
        .table-card { background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,.06); overflow-x: auto; }
        table.dt { width: 100%; border-collapse: collapse; min-width: 880px; }
        table.dt thead tr { background: #1e293b; }
        table.dt th { text-align: left; font-size: 11.5px; text-transform: uppercase; letter-spacing: .5px; color: #94a3b8; padding: 13px 15px; font-weight: 600; white-space: nowrap; }
        table.dt td { padding: 12px 15px; font-size: 13px; color: #334155; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        table.dt tr:last-child td { border-bottom: none; }
        table.dt tbody tr:hover td { background: #f8fafc; }
        table.dt tbody tr:nth-child(even) td { background: #fafbfd; }
        table.dt tbody tr:nth-child(even):hover td { background: #f1f5f9; }
        .no-col { width: 46px; text-align: center; font-weight: 600; color: #94a3b8; }
        .td-date { white-space: nowrap; }
        .badge { padding: 4px 10px; border-radius: 20px; font-size: 11.5px; font-weight: 600; white-space: nowrap; }
        .b-green { background: #ddf3e8; color: #1e9e63; }
        .b-red { background: #fbe2e1; color: #d33a39; }
        .b-orange { background: #fdf0d8; color: #b8790a; }
        .b-blue { background: #e3edfb; color: #2b75cc; }
        .btn-del { display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 30px; border-radius: 7px; background: #fbe2e1; color: #d33a39; border: none; cursor: pointer; text-decoration: none; font-size: 13px; transition: background .2s; }
        .btn-del:hover { background: #d33a39; color: #fff; }
        .empty-state { text-align: center; padding: 60px 20px; color: #94a3b8; }
        .empty-state i { font-size: 48px; margin-bottom: 14px; display: block; }
        .alert-ok { background: #ddf3e8; color: #1e9e63; padding: 12px 18px; border-radius: 8px; margin-bottom: 18px; font-size: 13.5px; }
        @media print { .sidebar, .topbar, .action-bar, .btn-del { display: none !important; } table.dt thead tr { background: #1e293b !important; -webkit-print-color-adjust: exact; } }
      `}</style>

      <div className="page-head">
        <div className="ph-icon"><i className={`fa-solid ${cfg.icon}`}></i></div>
        <div className="ph-info">
          <h2>{cfg.title}</h2>
          <p>{bulan} {tahun}</p>
        </div>
      </div>

      <div className="stats-row">
        <div className="s-card">
          <div className="s-icon" style={{ background: cfg.statBg }}><i className="fa-solid fa-list-check"></i></div>
          <div className="s-info"><h4>Total Data</h4><p>{total}</p></div>
        </div>
        <div className="s-card">
          <div className="s-icon" style={{ background: '#1e9e63' }}><i className="fa-solid fa-circle-check"></i></div>
          <div className="s-info"><h4>Kondisi Baik</h4><p>{baikCount}</p></div>
        </div>
        <div className="s-card">
          <div className="s-icon" style={{ background: '#d33a39' }}><i className="fa-solid fa-triangle-exclamation"></i></div>
          <div className="s-info"><h4>Perlu Perhatian</h4><p>{tidakCount}</p></div>
        </div>
      </div>

      <div className="action-bar">
        <div className="ab-left">
          <Link to={`/admin/laporan/${type}`} className="btn btn-back">
            <i className="fa-solid fa-arrow-left"></i> Kembali
          </Link>
          <a href={exportUrl} className="btn btn-excel">
            <i className="fa-solid fa-file-excel"></i> Export Excel
          </a>
          <button type="button" onClick={() => window.print()} className="btn btn-pdf">
            <i className="fa-solid fa-file-pdf"></i> Export PDF
          </button>
        </div>
        <div className="ab-right">
          <form className="search-wrap" onSubmit={(e) => { e.preventDefault(); loadList() }}>
            <input type="text" value={cari} onChange={(e) => setCari(e.target.value)} placeholder="Cari nama, ID, lokasi..." />
            <button type="submit" className="btn btn-search"><i className="fa-solid fa-magnifying-glass"></i></button>
            {cari !== '' && (
              <button type="button" className="btn btn-reset" onClick={() => loadList('')}>
                <i className="fa-solid fa-xmark"></i>
              </button>
            )}
          </form>
        </div>
      </div>

      <div className="table-card">
        <table className="dt">
          <thead>
            <tr>{cols.map((c) => <th key={c}>{c}</th>)}</tr>
          </thead>
          <tbody>
            {loading ? (
              <tr><td colSpan={cols.length}><div className="empty-state">Memuat data...</div></td></tr>
            ) : total > 0 ? (
              rows.map((r, i) => (
                <tr key={r.id_inspeksi ?? i}>
                  <td className="no-col">{i + 1}</td>
                  {renderBodyCells(r)}
                  <td>
                    {showDelete(r) ? (
                      <a href="#" className="btn-del" title="Hapus" onClick={(e) => { e.preventDefault(); handleDelete(r.id_inspeksi) }}>
                        <i className="fa-solid fa-trash"></i>
                      </a>
                    ) : '-'}
                  </td>
                </tr>
              ))
            ) : (
              <tr>
                <td colSpan={cols.length}>
                  <div className="empty-state">
                    <i className="fa-solid fa-folder-open"></i>
                    <p>{cfg.emptyText} {bulan} {tahun}</p>
                    {cari !== '' && (
                      <p style={{ marginTop: 6, fontSize: 13 }}>Kata kunci: "<strong>{cari}</strong>"</p>
                    )}
                  </div>
                </td>
              </tr>
            )}
          </tbody>
        </table>
      </div>
      <p style={{ marginTop: 12, fontSize: 13, color: '#94a3b8' }}>
        Menampilkan <strong>{total}</strong> data inspeksi
        {cari !== '' ? ` — pencarian: "<em>${cari}</em>"` : ''}
      </p>
    </>
  )
}