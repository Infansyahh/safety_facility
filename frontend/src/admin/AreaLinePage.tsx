import { useCallback, useEffect, useState } from 'react'
import { useParams } from 'react-router-dom'
import client from '../api/client'

type Row = {
  id_line: number
  nama_line: string
  jenis: string
  created_at: string
  total_item: string
}

type Config = {
  title: string
  desc: string
  icon: string
  badgeIcon: string
  accent: string
  iconBg: string
  itemLabel: string
  tambahTitle: string
  emptyText: string
  tambahHint: string
  editHint: string
}

const CONFIGS: Record<string, Config> = {
  lampu_emergency: {
    title: 'Area Line Lampu Emergency',
    desc: 'Kelola daftar area / line untuk inspeksi Lampu Emergency',
    icon: 'fa-bolt',
    badgeIcon: 'fa-bolt',
    accent: '#f59e0b',
    iconBg: '#fef3c7',
    itemLabel: 'Lampu Emergency',
    tambahTitle: 'Tambah Area Line  Lampu Emergency',
    emptyText: 'Belum ada area line untuk Lampu Emergency.',
    tambahHint: 'Area ini khusus untuk Lampu Emergency.',
    editHint: 'Perubahan nama akan otomatis berlaku di data master Lampu Emergency.',
  },
  lampu_exit: {
    title: 'Area Line Lampu Exit',
    desc: 'Kelola daftar area / line untuk inspeksi Lampu Exit',
    icon: 'fa-door-open',
    badgeIcon: 'fa-door-open',
    accent: '#10b981',
    iconBg: '#d1fae5',
    itemLabel: 'Lampu Exit',
    tambahTitle: 'Tambah Area Line \u00e2\u20ac\u201d Lampu Exit',
    emptyText: 'Belum ada area line untuk Lampu Exit.',
    tambahHint: 'Area ini khusus untuk Lampu Exit.',
    editHint: 'Perubahan nama akan otomatis berlaku di data master Lampu Exit.',
  },
  p3k: {
    title: 'Area Line Kotak P3K',
    desc: 'Kelola daftar area / line untuk inspeksi Kotak P3K',
    icon: 'fa-kit-medical',
    badgeIcon: 'fa-kit-medical',
    accent: '#ef4444',
    iconBg: '#fee2e2',
    itemLabel: 'Kotak P3K',
    tambahTitle: 'Tambah Area Line \u00e2\u20ac\u201d Kotak P3K',
    emptyText: 'Belum ada area line untuk Kotak P3K.',
    tambahHint: 'Area ini khusus untuk Kotak P3K.',
    editHint: 'Perubahan nama akan otomatis berlaku di data master Kotak P3K.',
  },
  eyewash: {
    title: 'Area Line Eye Wash',
    desc: 'Kelola daftar area / line untuk inspeksi Eye Wash',
    icon: 'fa-eye',
    badgeIcon: 'fa-eye',
    accent: '#3b82f6',
    iconBg: '#dbeafe',
    itemLabel: 'Eye Wash',
    tambahTitle: 'Tambah Area Line \u2014 Eye Wash',
    emptyText: 'Belum ada area line untuk Eye Wash.',
    tambahHint: 'Area ini khusus untuk Eye Wash.',
    editHint: 'Perubahan nama akan otomatis berlaku di data master Eye Wash.',
  },
}

export default function AreaLinePage() {
  const { jenis = 'lampu_emergency' } = useParams()
  const cfg: Config = CONFIGS[jenis] ?? CONFIGS.lampu_emergency

  const [rows, setRows] = useState<Row[]>([])
  const [cari, setCari] = useState('')
  const [loading, setLoading] = useState(true)
  const [flash, setFlash] = useState<string | null>(null)
  const [modalTambah, setModalTambah] = useState(false)
  const [modalEdit, setModalEdit] = useState(false)
  const [namaLineTambah, setNamaLineTambah] = useState('')
  const [editData, setEditData] = useState<{ id_line: number; nama_line: string } | null>(null)
  const [namaLineEdit, setNamaLineEdit] = useState('')

  const loadList = useCallback((term: string) => {
    setLoading(true)
    client
      .get('/area_line.php', {
        params: { action: 'list', jenis, cari: term },
      })
      .then((res) => {
        setRows(res.data.data || [])
      })
      .finally(() => setLoading(false))
  }, [jenis])

  useEffect(() => {
    setCari('')
    loadList('')
  }, [jenis, loadList])

  function handleTambah(e: React.FormEvent) {
    e.preventDefault()
    client
      .post('/area_line.php', new URLSearchParams({ action: 'create', jenis, nama_line: namaLineTambah }), {
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      })
      .then((res) => {
        if (res.data.success) {
          setModalTambah(false)
          setNamaLineTambah('')
          setFlash(res.data.message)
          loadList(cari)
        } else {
          alert(res.data.message)
        }
      })
  }

  function handleEdit(e: React.FormEvent) {
    e.preventDefault()
    client
      .post('/area_line.php', new URLSearchParams({ action: 'update', jenis, id_line: String(editData?.id_line ?? 0), nama_line: namaLineEdit }), {
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      })
      .then((res) => {
        if (res.data.success) {
          setModalEdit(false)
          setEditData(null)
          setFlash(res.data.message)
          loadList(cari)
        } else {
          alert(res.data.message)
        }
      })
  }

  function handleDelete(id: number) {
    if (confirm('Hapus area ini? Pastikan tidak ada data yang masih menggunakannya.')) {
      client
        .post('/area_line.php', new URLSearchParams({ action: 'delete', jenis, id_line: String(id) }), {
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        })
        .then((res) => {
          if (res.data.success) {
            setFlash(res.data.message)
            loadList(cari)
          } else {
            alert(res.data.message)
          }
        })
    }
  }

  return (
    <>
      <style>{`
        .page-head { display: flex; align-items: center; gap: 14px; border-left: 6px solid ${cfg.accent}; background: #fff; padding: 16px 18px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,.1); margin-bottom: 16px; }
        .ph-icon { width: 46px; height: 46px; border-radius: 10px; background: ${cfg.iconBg}; color: ${cfg.accent}; display: flex; align-items: center; justify-content: center; font-size: 20px; }
        .ph-info h2 { font-size: 18px; font-weight: 700; color: #1e293b; margin: 0; }
        .ph-info p { font-size: 13px; color: #64748b; margin: 3px 0 0; }
        .stat-mini { display: flex; align-items: center; gap: 14px; background: #fff; padding: 16px 18px; border-radius: 10px; border-left: 5px solid ${cfg.accent}; box-shadow: 0 2px 5px rgba(0,0,0,.1); margin-bottom: 16px; max-width: 260px; }
        .stat-mini i { font-size: 24px; color: ${cfg.accent}; }
        .stat-mini h3 { font-size: 12px; color: #64748b; font-weight: 500; margin: 0; }
        .stat-mini p { font-size: 24px; font-weight: 700; color: #1e293b; line-height: 1.1; margin: 0; }
        .page-toolbar { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; margin-bottom: 16px; }
        .search-bar { display: flex; gap: 8px; align-items: center; }
        .search-bar input { padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px; width: 260px; font-size: 13px; outline: none; }
        .search-bar input:focus { border-color: ${cfg.accent}; }
        .btn { padding: 10px 16px; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; border: none; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
        .btn-primary { background: ${cfg.accent}; color: #fff; }
        .btn-primary:hover { opacity: .88; }
        .btn-outline { background: #fff; color: #000766; border: 1px solid #000766; }
        .table-card { background: #fff; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,.1); overflow-x: auto; }
        table.area-table { width: 100%; border-collapse: collapse; }
        table.area-table thead tr { background: #1e293b; color: #fff; }
        table.area-table th { padding: 12px 14px; text-align: left; font-weight: 600; font-size: 13px; }
        table.area-table td { padding: 12px 14px; border-bottom: 1px solid #f1f5f9; font-size: 13px; color: #334155; }
        table.area-table tbody tr:hover { background: #f8fafc; }
        table.area-table tbody tr:nth-child(even) { background: #fafbfc; }
        table.area-table tbody tr:nth-child(even):hover { background: #f1f5f9; }
        .badge-item { display: inline-flex; align-items: center; gap: 6px; background: ${cfg.iconBg}; color: ${cfg.accent}; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .action-icons { display: flex; gap: 8px; }
        .ic-edit { color: #2b75cc; cursor: pointer; font-size: 15px; }
        .ic-delete { color: #e15554; cursor: pointer; font-size: 15px; }
        .empty-row td { text-align: center; padding: 48px; color: #94a3b8; font-size: 14px; }
        .alert-success { background: #ddf3e8; color: #1e9e63; padding: 12px 18px; border-radius: 8px; margin-bottom: 18px; font-size: 13.5px; }
        .alert-danger { background: #fbe2e1; color: #d33a39; padding: 12px 18px; border-radius: 8px; margin-bottom: 18px; font-size: 13.5px; }
        .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,.5); z-index: 1000; align-items: flex-start; justify-content: center; overflow-y: auto; }
        .modal-overlay.show { display: flex; }
        .modal-box { background: #fff; width: 420px; margin: 60px auto; padding: 22px 24px; border-radius: 10px; box-sizing: border-box; }
        .modal-box h3 { margin-bottom: 18px; color: ${cfg.accent}; font-size: 16px; }
        .form-group { margin-bottom: 16px; }
        .form-group label { font-size: 13px; font-weight: 600; color: #334155; display: block; margin-bottom: 6px; }
        .form-group input { width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 13px; outline: none; box-sizing: border-box; }
        .form-group input:focus { border-color: ${cfg.accent}; }
        .input-hint { font-size: 12px; color: #94a3b8; margin-top: 6px; }
        .modal-footer { display: flex; justify-content: flex-end; gap: 10px; margin-top: 18px; }
      `}</style>

      {flash && (
        <div className="alert-success">
          <i className="fa-solid fa-circle-check"></i> {flash}
        </div>
      )}

      <div className="page-head">
        <div className="ph-icon"><i className={`fa-solid ${cfg.icon}`}></i></div>
        <div className="ph-info">
          <h2>{cfg.title}</h2>
          <p>{cfg.desc}</p>
        </div>
      </div>

      <div className="stat-mini">
        <i className="fa-solid fa-location-dot"></i>
        <div>
          <h3>Total Area Terdaftar</h3>
          <p>{loading ? '...' : rows.length}</p>
        </div>
      </div>

      <div className="page-toolbar">
        <form
          className="search-bar"
          onSubmit={(e) => { e.preventDefault(); loadList(cari) }}
        >
          <input type="text" value={cari} onChange={(e) => setCari(e.target.value)} placeholder="Cari nama area..." />
          <button type="submit" className="btn btn-outline"><i className="fa-solid fa-magnifying-glass"></i> Cari</button>
          {cari !== '' && (
            <button type="button" className="btn btn-outline" onClick={() => { setCari(''); loadList('') }}>
              <i className="fa-solid fa-rotate-left"></i> Reset
            </button>
          )}
        </form>
        <button type="button" className="btn btn-primary" onClick={() => setModalTambah(true)}>
          <i className="fa-solid fa-plus"></i> Tambah Area Line
        </button>
      </div>

      <div className="table-card">
        <table className="area-table">
          <thead>
            <tr>
              <th style={{ width: 52 }}>No</th>
              <th>Nama Area / Line</th>
              <th>Item Terdaftar</th>
              <th>Dibuat Pada</th>
              <th style={{ width: 110 }}>Aksi</th>
            </tr>
          </thead>
          <tbody>
            {loading ? (
              <tr className="empty-row"><td colSpan={5}>Memuat data...</td></tr>
            ) : rows.length > 0 ? (
              rows.map((r, i) => (
                <tr key={r.id_line}>
                  <td style={{ textAlign: 'center', fontWeight: 600, color: '#94a3b8' }}>{i + 1}</td>
                  <td><strong>{r.nama_line}</strong></td>
                  <td>
                    <span className="badge-item">
                      <i className={`fa-solid ${cfg.badgeIcon}`}></i>
                      {r.total_item} item
                    </span>
                  </td>
                  <td>{r.created_at ? new Date(r.created_at).toLocaleString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit', hour12: false }).replace(',', '') : '-'}</td>
                  <td className="action-icons">
                    <a
                      href="#"
                      className="ic-edit"
                      title="Edit"
                      onClick={(e) => {
                        e.preventDefault()
                        setEditData({ id_line: r.id_line, nama_line: r.nama_line })
                        setNamaLineEdit(r.nama_line)
                        setModalEdit(true)
                      }}
                    >
                      <i className="fa-solid fa-pen"></i>
                    </a>
                    <a href="#" className="ic-delete" title="Hapus" onClick={(e) => { e.preventDefault(); handleDelete(r.id_line) }}>
                      <i className="fa-solid fa-trash"></i>
                    </a>
                  </td>
                </tr>
              ))
            ) : (
              <tr className="empty-row">
                <td colSpan={5}>
                  <i className="fa-solid fa-folder-open" style={{ fontSize: 28, marginBottom: 8, display: 'block' }}></i>
                  {cfg.emptyText}
                </td>
              </tr>
            )}
          </tbody>
        </table>
      </div>

      {modalTambah && (
        <div className="modal-overlay show" onClick={(e) => { if (e.target === e.currentTarget) setModalTambah(false) }}>
          <div className="modal-box">
            <h3><i className={`fa-solid ${cfg.icon}`}></i> {cfg.tambahTitle}</h3>
            <form onSubmit={handleTambah}>
              <div className="form-group">
                <label>Nama Area / Line <span style={{ color: '#e15554' }}>*</span></label>
                <input type="text" value={namaLineTambah} onChange={(e) => setNamaLineTambah(e.target.value)} placeholder="Contoh: FA, Line A, Gudang B..." required autoFocus />
                <p className="input-hint">{cfg.tambahHint}</p>
              </div>
              <div className="modal-footer">
                <button type="button" className="btn btn-outline" onClick={() => setModalTambah(false)}>Batal</button>
                <button type="submit" className="btn btn-primary">
                  <i className="fa-solid fa-floppy-disk"></i> Simpan
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {modalEdit && editData && (
        <div className="modal-overlay show" onClick={(e) => { if (e.target === e.currentTarget) setModalEdit(false) }}>
          <div className="modal-box">
            <h3><i className="fa-solid fa-pen"></i> Edit Area Line</h3>
            <form onSubmit={handleEdit}>
              <div className="form-group">
                <label>Nama Area / Line <span style={{ color: '#e15554' }}>*</span></label>
                <input type="text" value={namaLineEdit} onChange={(e) => setNamaLineEdit(e.target.value)} required />
                <p className="input-hint">{cfg.editHint}</p>
              </div>
              <div className="modal-footer">
                <button type="button" className="btn btn-outline" onClick={() => setModalEdit(false)}>Batal</button>
                <button type="submit" className="btn btn-primary">
                  <i className="fa-solid fa-floppy-disk"></i> Simpan Perubahan
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </>
  )
}