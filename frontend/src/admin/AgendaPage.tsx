import { useCallback, useEffect, useState } from 'react'
import client from '../api/client'

const JENIS_OPTIONS = ['Lampu Emergency', 'Lampu Fire Emergency', 'Kotak P3K', 'Eye Wash']
const STATUS_OPTIONS = ['Terjadwal', 'Berlangsung', 'Selesai', 'Terlewat']

type Row = {
  id_agenda: number
  jenis_inspeksi: string
  line_area: string
  id_lampu: string | null
  id_user: number | null
  tanggal_jadwal: string
  jam_jadwal: string | null
  status: string
  catatan: string | null
  created_at: string
  nama_lengkap: string | null
}

type Lampu = { code: string; lokasi: string }
type Line = { id_line: number; nama_line: string }
type User = { id_user: number; nama_lengkap: string }

const nowMonth = () => {
  const d = new Date()
  return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`
}

const badgeClass: Record<string, string> = {
  Terjadwal: 'badge-blue',
  Berlangsung: 'badge-orange',
  Selesai: 'badge-green',
  Terlewat: 'badge-red',
}

export default function AgendaPage() {
  const [rows, setRows] = useState<Row[]>([])
  const [stat, setStat] = useState<Record<string, number>>({ Terjadwal: 0, Berlangsung: 0, Selesai: 0, Terlewat: 0 })
  const [loading, setLoading] = useState(true)

  const [cari, setCari] = useState('')
  const [filterStatus, setFilterStatus] = useState('')
  const [filterBulan, setFilterBulan] = useState(nowMonth())

  const [listLampu, setListLampu] = useState<Lampu[]>([])
  const [listLine, setListLine] = useState<Line[]>([])
  const [listUser, setListUser] = useState<User[]>([])

  const [modalTambah, setModalTambah] = useState(false)
  const [modalEdit, setModalEdit] = useState(false)

  const [formTambah, setFormTambah] = useState({
    jenis_inspeksi: 'Lampu Emergency', tanggal_jadwal: '', jam_jadwal: '',
    line_area: '', id_lampu: '', catatan: '',
  })
  const [formEdit, setFormEdit] = useState({
    id_agenda: 0, jenis_inspeksi: 'Lampu Emergency', tanggal_jadwal: '', jam_jadwal: '',
    line_area: '', id_lampu: '', id_user: '', status: 'Terjadwal', catatan: '',
  })

  const loadOptions = useCallback(() => {
    client.get('/agenda.php', { params: { action: 'options' } }).then((res) => {
      setListLampu(res.data.list_lampu || [])
      setListLine(res.data.list_line || [])
      setListUser(res.data.list_user || [])
    })
  }, [])

  const loadList = useCallback((params: { cari?: string; filter_status?: string; filter_bulan?: string } = {}) => {
    setLoading(true)
    client
      .get('/agenda.php', {
        params: {
          action: 'list',
          cari: params.cari ?? cari,
          filter_status: params.filter_status ?? filterStatus,
          filter_bulan: params.filter_bulan ?? filterBulan,
        },
      })
      .then((res) => {
        setRows(res.data.data)
        setStat(res.data.stat)
        setCari(res.data.cari)
        setFilterStatus(res.data.filter_status)
        setFilterBulan(res.data.filter_bulan)
      })
      .finally(() => setLoading(false))
  }, [cari, filterStatus, filterBulan])

  useEffect(() => {
    loadOptions()
    loadList()
  }, [])

  function handleTambah(e: React.FormEvent) {
    e.preventDefault()
    client
      .post('/agenda.php', new URLSearchParams({ action: 'create', ...formTambah } as any), {
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      })
      .then((res) => {
        alert(res.data.message)
        setModalTambah(false)
        setFormTambah({ jenis_inspeksi: 'Lampu Emergency', tanggal_jadwal: '', jam_jadwal: '', line_area: '', id_lampu: '', catatan: '' })
        loadList()
      })
  }

  function handleEdit(e: React.FormEvent) {
    e.preventDefault()
    client
      .post('/agenda.php', new URLSearchParams({ action: 'update', ...formEdit } as any), {
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      })
      .then((res) => {
        alert(res.data.message)
        setModalEdit(false)
        loadList()
      })
  }

  function handleStatus(id: number) {
    if (confirm('Tandai agenda ini sebagai Selesai?')) {
      client
        .post('/agenda.php', new URLSearchParams({ action: 'status', id: String(id), status: 'Selesai' } as any), {
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        })
        .then((res) => {
          alert(res.data.message)
          loadList()
        })
    }
  }

  function handleDelete(id: number) {
    if (confirm('Yakin ingin menghapus agenda ini?')) {
      client
        .post('/agenda.php', new URLSearchParams({ action: 'delete', id: String(id) } as any), {
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        })
        .then((res) => {
          alert(res.data.message)
          loadList()
        })
    }
  }

  function openEdit(r: Row) {
    setFormEdit({
      id_agenda: r.id_agenda,
      jenis_inspeksi: r.jenis_inspeksi,
      tanggal_jadwal: r.tanggal_jadwal,
      jam_jadwal: r.jam_jadwal ? r.jam_jadwal.substring(0, 5) : '',
      line_area: r.line_area,
      id_lampu: r.id_lampu || '',
      id_user: r.id_user ? String(r.id_user) : '',
      status: r.status,
      catatan: r.catatan || '',
    })
    setModalEdit(true)
  }

  const fmtTanggal = (d: string) => {
    if (!d) return '-'
    const [y, m, day] = d.split('-')
    return `${day}-${m}-${y}`
  }

  return (
    <>
      <style>{`
        .page-toolbar { display: flex; flex-wrap: wrap; gap: 12px; align-items: center; justify-content: space-between; margin-bottom: 20px; }
        .grid-stats-mini { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 24px; }
        .mini-card { background: #fff; border-radius: 10px; padding: 16px 18px; border-left: 5px solid #2b75cc; box-shadow: 0 2px 6px rgba(0,0,0,.05); }
        .mini-card.c-orange { border-left-color: #f0a500; }
        .mini-card.c-green { border-left-color: #2bb673; }
        .mini-card.c-red { border-left-color: #e15554; }
        .mini-card h3 { font-size: 13px; color: #64748b; font-weight: 500; margin-bottom: 6px; }
        .mini-card p { font-size: 24px; font-weight: 700; color: #1e293b; margin: 0; }
        .filter-bar { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }
        .filter-bar select, .filter-bar input[type="month"], .filter-bar input[type="text"] { padding: 9px 12px; border: 1px solid #d8dee9; border-radius: 6px; font-size: 13px; font-family: inherit; outline: none; }
        .btn { display: inline-flex; align-items: center; gap: 6px; padding: 9px 16px; border-radius: 6px; border: none; font-size: 13px; font-weight: 500; cursor: pointer; text-decoration: none; white-space: nowrap; }
        .btn-primary { background: #000766; color: #fff; }
        .btn-primary:hover { background: #1a2a8c; }
        .btn-outline { background: #fff; color: #000766; border: 1px solid #000766; }
        .btn-sm { padding: 6px 10px; font-size: 12px; }
        .table-card { background: #fff; border-radius: 10px; box-shadow: 0 2px 6px rgba(0,0,0,.05); overflow-x: auto; }
        table.agenda-table { width: 100%; border-collapse: collapse; min-width: 950px; }
        table.agenda-table th { background: #f4f6f9; text-align: left; font-size: 12px; text-transform: uppercase; letter-spacing: .5px; color: #64748b; padding: 14px 16px; border-bottom: 1px solid #e2e8f0; }
        table.agenda-table td { padding: 13px 16px; font-size: 13.5px; color: #334155; border-bottom: 1px solid #eef1f5; }
        table.agenda-table tr:hover td { background: #fafbfd; }
        .badge { padding: 4px 10px; border-radius: 20px; font-size: 11.5px; font-weight: 600; white-space: nowrap; }
        .badge-blue { background: #e3edfb; color: #2b75cc; }
        .badge-orange { background: #fdf0d8; color: #b8790a; }
        .badge-green { background: #ddf3e8; color: #1e9e63; }
        .badge-red { background: #fbe2e1; color: #d33a39; }
        .action-icons a { display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 30px; border-radius: 6px; margin-right: 4px; color: #fff; text-decoration: none; font-size: 12px; }
        .ic-edit { background: #2b75cc; }
        .ic-done { background: #2bb673; }
        .ic-delete { background: #e15554; }
        .empty-row td { text-align: center; padding: 40px; color: #94a3b8; }
        .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(15,23,42,.5); z-index: 999; align-items: center; justify-content: center; }
        .modal-overlay.show { display: flex; }
        .modal-box { background: #fff; width: 100%; max-width: 520px; border-radius: 12px; padding: 26px; max-height: 90vh; overflow-y: auto; box-sizing: border-box; }
        .modal-box h3 { margin-bottom: 18px; color: #000766; }
        .form-group { margin-bottom: 14px; }
        .form-group label { display: block; font-size: 13px; font-weight: 500; margin-bottom: 6px; color: #334155; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 10px 12px; border: 1px solid #d8dee9; border-radius: 6px; font-size: 13.5px; font-family: inherit; outline: none; box-sizing: border-box; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .modal-footer { display: flex; justify-content: flex-end; gap: 10px; margin-top: 18px; }
        .alert-success { background: #ddf3e8; color: #1e9e63; padding: 12px 18px; border-radius: 8px; margin-bottom: 18px; font-size: 13.5px; }
      `}</style>

      <h2 className="page-title">Agenda Inspeksi</h2>

      <div className="grid-stats-mini">
        <div className="mini-card"><h3>Terjadwal</h3><p>{stat.Terjadwal ?? 0}</p></div>
        <div className="mini-card c-orange"><h3>Berlangsung</h3><p>{stat.Berlangsung ?? 0}</p></div>
        <div className="mini-card c-green"><h3>Selesai</h3><p>{stat.Selesai ?? 0}</p></div>
        <div className="mini-card c-red"><h3>Terlewat</h3><p>{stat.Terlewat ?? 0}</p></div>
      </div>

      <div className="page-toolbar">
        <form
          className="filter-bar"
          onSubmit={(e) => { e.preventDefault(); loadList() }}
        >
          <input type="text" value={cari} onChange={(e) => setCari(e.target.value)} placeholder="Cari area / id lampu / jenis..." />
          <select value={filterStatus} onChange={(e) => setFilterStatus(e.target.value)}>
            <option value="">Semua Status</option>
            {STATUS_OPTIONS.map((s) => <option key={s} value={s}>{s}</option>)}
          </select>
          <input type="month" value={filterBulan} onChange={(e) => setFilterBulan(e.target.value)} />
          <button type="submit" className="btn btn-outline"><i className="fa-solid fa-filter"></i> Filter</button>
          <button type="button" className="btn btn-outline" onClick={() => { setCari(''); setFilterStatus(''); setFilterBulan(nowMonth()); loadList({ cari: '', filter_status: '', filter_bulan: nowMonth() }) }}>
            <i className="fa-solid fa-rotate-left"></i> Reset
          </button>
        </form>

        <button type="button" className="btn btn-primary" onClick={() => setModalTambah(true)}>
          <i className="fa-solid fa-plus"></i> Tambah Agenda
        </button>
      </div>

      <div className="table-card">
        <table className="agenda-table">
          <thead>
            <tr>
              <th>Tanggal</th>
              <th>Jam</th>
              <th>Jenis Inspeksi</th>
              <th>Area / Line</th>
              <th>Item</th>
              <th>Petugas</th>
              <th>Status</th>
              <th>Catatan</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            {loading ? (
              <tr className="empty-row"><td colSpan={9}>Memuat data...</td></tr>
            ) : rows.length > 0 ? (
              rows.map((r) => (
                <tr key={r.id_agenda}>
                  <td>{fmtTanggal(r.tanggal_jadwal)}</td>
                  <td>{r.jam_jadwal ? r.jam_jadwal.substring(0, 5) : '-'}</td>
                  <td>{r.jenis_inspeksi}</td>
                  <td>{r.line_area}</td>
                  <td>{r.id_lampu ?? '-'}</td>
                  <td>{r.nama_lengkap ?? 'Belum ditentukan'}</td>
                  <td><span className={`badge ${badgeClass[r.status] ?? 'badge-blue'}`}>{r.status}</span></td>
                  <td>{r.catatan ? r.catatan : '-'}</td>
                  <td className="action-icons">
                    <a href="#" className="ic-edit" title="Edit" onClick={(e) => { e.preventDefault(); openEdit(r) }}>
                      <i className="fa-solid fa-pen"></i>
                    </a>
                    {r.status !== 'Selesai' && (
                      <a href="#" className="ic-done" title="Tandai Selesai" onClick={(e) => { e.preventDefault(); handleStatus(r.id_agenda) }}>
                        <i className="fa-solid fa-check"></i>
                      </a>
                    )}
                    <a href="#" className="ic-delete" title="Hapus" onClick={(e) => { e.preventDefault(); handleDelete(r.id_agenda) }}>
                      <i className="fa-solid fa-trash"></i>
                    </a>
                  </td>
                </tr>
              ))
            ) : (
              <tr className="empty-row"><td colSpan={9}>Belum ada agenda inspeksi untuk filter ini.</td></tr>
            )}
          </tbody>
        </table>
      </div>

      {modalTambah && (
        <div className="modal-overlay show" onClick={(e) => { if (e.target === e.currentTarget) setModalTambah(false) }}>
          <div className="modal-box">
            <h3><i className="fa-solid fa-calendar-plus"></i> Tambah Agenda Inspeksi</h3>
            <form onSubmit={handleTambah}>
              <div className="form-group">
                <label>Jenis Inspeksi</label>
                <select value={formTambah.jenis_inspeksi} onChange={(e) => setFormTambah({ ...formTambah, jenis_inspeksi: e.target.value })} required>
                  {JENIS_OPTIONS.map((j) => <option key={j} value={j}>{j}</option>)}
                </select>
              </div>
              <div className="form-row">
                <div className="form-group">
                  <label>Tanggal Jadwal</label>
                  <input type="date" value={formTambah.tanggal_jadwal} onChange={(e) => setFormTambah({ ...formTambah, tanggal_jadwal: e.target.value })} required />
                </div>
                <div className="form-group">
                  <label>Jam</label>
                  <input type="time" value={formTambah.jam_jadwal} onChange={(e) => setFormTambah({ ...formTambah, jam_jadwal: e.target.value })} />
                </div>
              </div>
              <div className="form-group">
                <label>Area / Line</label>
                <select value={formTambah.line_area} onChange={(e) => setFormTambah({ ...formTambah, line_area: e.target.value })} required>
                  <option value="">-- Pilih Area / Line --</option>
                  {listLine.map((l) => <option key={l.id_line} value={l.nama_line}>{l.nama_line}</option>)}
                </select>
              </div>
              <div className="form-group">
                <label>Item</label>
                <select value={formTambah.id_lampu} onChange={(e) => setFormTambah({ ...formTambah, id_lampu: e.target.value })}>
                  <option value="">-- Tidak spesifik --</option>
                  {listLampu.map((lp) => <option key={lp.code} value={lp.code}>{lp.code} - {lp.lokasi}</option>)}
                </select>
              </div>
              <div className="form-group">
                <label>Catatan (opsional)</label>
                <textarea value={formTambah.catatan} onChange={(e) => setFormTambah({ ...formTambah, catatan: e.target.value })} rows={3} placeholder="Contoh: bawa unit pengganti baterai"></textarea>
              </div>
              <div className="modal-footer">
                <button type="button" className="btn btn-outline" onClick={() => setModalTambah(false)}>Batal</button>
                <button type="submit" className="btn btn-primary">Simpan Agenda</button>
              </div>
            </form>
          </div>
        </div>
      )}

      {modalEdit && (
        <div className="modal-overlay show" onClick={(e) => { if (e.target === e.currentTarget) setModalEdit(false) }}>
          <div className="modal-box">
            <h3><i className="fa-solid fa-pen"></i> Edit Agenda Inspeksi</h3>
            <form onSubmit={handleEdit}>
              <div className="form-group">
                <label>Jenis Inspeksi</label>
                <select value={formEdit.jenis_inspeksi} onChange={(e) => setFormEdit({ ...formEdit, jenis_inspeksi: e.target.value })} required>
                  {JENIS_OPTIONS.map((j) => <option key={j} value={j}>{j}</option>)}
                </select>
              </div>
              <div className="form-row">
                <div className="form-group">
                  <label>Tanggal Jadwal</label>
                  <input type="date" value={formEdit.tanggal_jadwal} onChange={(e) => setFormEdit({ ...formEdit, tanggal_jadwal: e.target.value })} required />
                </div>
                <div className="form-group">
                  <label>Jam (opsional)</label>
                  <input type="time" value={formEdit.jam_jadwal} onChange={(e) => setFormEdit({ ...formEdit, jam_jadwal: e.target.value })} />
                </div>
              </div>
              <div className="form-group">
                <label>Area / Line</label>
                <select value={formEdit.line_area} onChange={(e) => setFormEdit({ ...formEdit, line_area: e.target.value })} required>
                  <option value="">-- Pilih Area / Line --</option>
                  {listLine.map((l) => <option key={l.id_line} value={l.nama_line}>{l.nama_line}</option>)}
                </select>
              </div>
              <div className="form-group">
                <label>Item / ID Lampu (opsional)</label>
                <input type="text" value={formEdit.id_lampu} onChange={(e) => setFormEdit({ ...formEdit, id_lampu: e.target.value })} placeholder="Kosongkan jika tidak spesifik" />
              </div>
              <div className="form-group">
                <label>Petugas (id_user, opsional)</label>
                <select value={formEdit.id_user} onChange={(e) => setFormEdit({ ...formEdit, id_user: e.target.value })}>
                  <option value="">-- Belum ditentukan --</option>
                  {listUser.map((u) => <option key={u.id_user} value={u.id_user}>{u.nama_lengkap}</option>)}
                </select>
              </div>
              <div className="form-group">
                <label>Status</label>
                <select value={formEdit.status} onChange={(e) => setFormEdit({ ...formEdit, status: e.target.value })} required>
                  {STATUS_OPTIONS.map((s) => <option key={s} value={s}>{s}</option>)}
                </select>
              </div>
              <div className="form-group">
                <label>Catatan (opsional)</label>
                <textarea value={formEdit.catatan} onChange={(e) => setFormEdit({ ...formEdit, catatan: e.target.value })} rows={3}></textarea>
              </div>
              <div className="modal-footer">
                <button type="button" className="btn btn-outline" onClick={() => setModalEdit(false)}>Batal</button>
                <button type="submit" className="btn btn-primary">Simpan Perubahan</button>
              </div>
            </form>
          </div>
        </div>
      )}
    </>
  )
}