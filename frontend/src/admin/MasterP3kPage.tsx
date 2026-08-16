import { useEffect, useState } from 'react'
import { useLocation, useNavigate } from 'react-router-dom'
import client, { API_BASE } from '../api/client'
import { useOperator } from './AdminLayout'

const LIMIT_OPTIONS = [10, 25, 50, 100]

type Row = {
  id: number
  code: string
  line_area: string
  lokasi: string
  kondisi_kotak: string
  kelengkapan_isi: string
  expired_obat: string
  catatan: string
  username: string
  tanggal_inspeksi: string
}

export default function MasterP3kPage() {
  const navigate = useNavigate()
  const location = useLocation()
  const { namaOperator } = useOperator()

  const [rows, setRows] = useState<Row[]>([])
  const [search, setSearch] = useState('')
  const [limit, setLimit] = useState(10)
  const [page, setPage] = useState(1)
  const [totalRows, setTotalRows] = useState(0)
  const [totalPages, setTotalPages] = useState(1)
  const [offset, setOffset] = useState(0)
  const [areas, setAreas] = useState<string[]>([])
  const [nextCode, setNextCode] = useState('')

  const [modalTambah, setModalTambah] = useState(false)
  const [modalEdit, setModalEdit] = useState(false)
  const [modalBarcode, setModalBarcode] = useState(false)
  const [editTitle, setEditTitle] = useState('Edit/Inspeksi Data Master Kotak P3K')
  const [barcodeSrc, setBarcodeSrc] = useState('')
  const [barcodeCode, setBarcodeCode] = useState('')
  const [checked, setChecked] = useState<Set<number>>(new Set())
  const [loading, setLoading] = useState(true)

  const [formTambah, setFormTambah] = useState({
    code: '', line_area: '', lokasi: '',
    kondisi_kotak: 'Baik', kelengkapan_isi: 'Lengkap', expired_obat: 'Lengkap', catatan: '',
  })
  const [formEdit, setFormEdit] = useState({
    id: 0, code: '', line_area: '', lokasi: '',
    kondisi_kotak: 'Baik', kelengkapan_isi: 'Lengkap', expired_obat: 'Lengkap', catatan: '',
  })

  const loadList = (params: { search?: string; limit?: number; page?: number } = {}) => {
    setLoading(true)
    client
      .get('/master_p3k.php', {
        params: { action: 'list', search: params.search ?? search, limit: params.limit ?? limit, page: params.page ?? page },
      })
      .then((res) => {
        setRows(res.data.data)
        setTotalRows(res.data.total_rows)
        setTotalPages(res.data.total_pages)
        setOffset(res.data.offset)
        setLimit(res.data.limit)
        setSearch(res.data.search)
        setPage(res.data.page)
      })
      .finally(() => setLoading(false))
  }

  const loadOptions = () => {
    client.get('/master_p3k.php', { params: { action: 'area_list' } }).then((res) => setAreas(res.data.data))
    client.get('/master_p3k.php', { params: { action: 'next_code' } }).then((res) => {
      setNextCode(res.data.next_code)
      setFormTambah((f) => ({ ...f, code: res.data.next_code }))
    })
  }

  useEffect(() => {
    loadOptions()
  }, [])

  useEffect(() => {
    loadList()
  }, [namaOperator])

  useEffect(() => {
    const params = new URLSearchParams(location.search)
    const scanId = params.get('scan_id')
    if (params.get('action') === 'scan_popup' && scanId) {
      client
        .get('/master_p3k.php', { params: { action: 'scan_check', scan_id: scanId } })
        .then((res) => {
          if (res.data.blocked) {
            alert(`⚠️  ${res.data.message}`)
            navigate(location.pathname, { replace: true })
          } else if (res.data.data) {
            const d = res.data.data
            setFormEdit({
              id: d.id, code: d.code, line_area: d.line_area || '', lokasi: d.lokasi,
              kondisi_kotak: d.kondisi_kotak || 'Baik', kelengkapan_isi: d.kelengkapan_isi || 'Lengkap',
              expired_obat: d.expired_obat || 'Lengkap', catatan: d.catatan || '',
            })
            setEditTitle('📋 Isi Data Hasil Scan Kotak P3K')
            setModalEdit(true)
            navigate(location.pathname, { replace: true })
          }
        })
    }
  }, [location.search])

  function handleSubmitTambah(e: React.FormEvent) {
    e.preventDefault()
    client
      .post('/master_p3k.php', new URLSearchParams({ ...formTambah, action: 'create' } as any), {
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      })
      .then((res) => {
        alert(res.data.message)
        setModalTambah(false)
        loadOptions()
        loadList()
      })
  }

  function handleSubmitEdit(e: React.FormEvent) {
    e.preventDefault()
    client
      .post('/master_p3k.php', new URLSearchParams({ ...formEdit, action: 'update' } as any), {
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      })
      .then((res) => {
        alert(res.data.message)
        setModalEdit(false)
        loadList()
      })
  }

  function handleDelete(id: number) {
    if (confirm('Apakah Anda yakin ingin menghapus data master Kotak P3K ini?')) {
      client.post('/master_p3k.php', new URLSearchParams({ action: 'delete', id: String(id) } as any), {
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      }).then((res) => {
        alert(res.data.message)
        loadList()
      })
    }
  }

  function openBarcode(id: number, code: string) {
    const ts = new Date().getTime()
    setBarcodeCode(code)
    setBarcodeSrc(`${API_BASE}/cetak_barcode.php?type=p3k&id=${id}&t=${ts}`)
    setModalBarcode(true)
  }

  function downloadMassal() {
    const ids = Array.from(checked).join(',')
    if (!ids) {
      alert('Pilih minimal 1 data dulu.')
      return
    }
    window.open(`${API_BASE}/cetak_barcode_massal.php?type=p3k&ids=${ids}`, '_blank')
  }

  const exportUrl = `${API_BASE}/export_master.php?type=p3k`

  return (
    <>
      <h2 className="page-title">Data Master Kotak P3K</h2>
      <div className="top-actions">
        <button type="button" onClick={() => { loadOptions(); setModalTambah(true) }} style={{ background: '#004ef5', border: 'none', padding: '10px 15px', marginBottom: '10px', color: 'white', borderRadius: '5px', cursor: 'pointer' }}>
          <i className="fa-solid fa-plus"></i> Tambah Data Baru
        </button>
        <a href={exportUrl} style={{ background: '#20c000', border: 'none', padding: '10px 15px', marginBottom: '10px', color: 'white', borderRadius: '5px', cursor: 'pointer', textDecoration: 'none', display: 'inline-block' }}>📤 Export Data Ke Excel</a>
        <button type="button" onClick={downloadMassal} style={{ background: '#ff9800', border: 'none', padding: '10px 15px', marginBottom: '10px', color: 'white', borderRadius: '5px', cursor: 'pointer' }}>
          🏷️ Download Barcode Terpilih (<span>{checked.size}</span>)
        </button>
      </div>

      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', flexWrap: 'wrap', gap: '10px', marginBottom: '12px' }}>
        <form
          onSubmit={(e) => { e.preventDefault(); setPage(1); loadList({ page: 1, search }) }}
          style={{ display: 'flex', alignItems: 'center', gap: '8px' }}
        >
          <input type="text" value={search} onChange={(e) => setSearch(e.target.value)} placeholder="Cari kode, departemen, lokasi, inspektor..." style={{ padding: '8px', border: '1px solid #ccc', borderRadius: '4px', width: '280px' }} />
          <button type="submit" style={{ background: '#004ef5', color: 'white', border: 'none', padding: '8px 15px', borderRadius: '4px', cursor: 'pointer' }}>
            <i className="fa-solid fa-magnifying-glass"></i> Cari
          </button>
          {search !== '' && (
            <button type="button" onClick={() => { setSearch(''); loadList({ search: '' }) }} style={{ padding: '8px 12px', border: '1px solid #ccc', background: '#fff', borderRadius: '4px', cursor: 'pointer', color: '#333' }}>Reset</button>
          )}
        </form>
        <form style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
          <label style={{ fontWeight: 600 }}>Tampilkan:</label>
          <select value={limit} onChange={(e) => { const l = Number(e.target.value); setLimit(l); loadList({ limit: l }) }} style={{ padding: '8px', border: '1px solid #ccc', borderRadius: '4px' }}>
            {LIMIT_OPTIONS.map((o) => (
              <option key={o} value={o}>{o}</option>
            ))}
          </select>
          <span>data</span>
        </form>
      </div>

      <div className="table-container">
        <table>
          <thead>
            <tr>
              <th style={{ width: '3%' }} rowSpan={2}><input type="checkbox" checked={checked.size === rows.length && rows.length > 0} onChange={(e) => setChecked(e.target.checked ? new Set(rows.map((r) => r.id)) : new Set())} /></th>
              <th style={{ width: '5%' }} rowSpan={2}>No</th>
              <th rowSpan={2}>Inspektor</th>
              <th rowSpan={2}>Kode</th>
              <th rowSpan={2}>Departemen</th>
              <th rowSpan={2}>Lokasi</th>
              <th rowSpan={2}>Catatan</th>
              <th colSpan={2}>Kondisi Kotak</th>
              <th colSpan={2}>Isi Kotak</th>
              <th colSpan={2}>Obat-obatan</th>
              <th style={{ width: '12%' }} rowSpan={2}>Aksi</th>
            </tr>
            <tr className="sub-header">
              <th className="text-center col-check">Baik</th>
              <th className="text-center col-check">Tidak</th>
              <th className="text-center col-check">Lengkap</th>
              <th className="text-center col-check">Tidak</th>
              <th className="text-center col-check">Lengkap</th>
              <th className="text-center col-check">Tidak</th>
            </tr>
          </thead>
          <tbody>
            {loading ? (
              <tr><td colSpan={14} style={{ textAlign: 'center' }}>Memuat data...</td></tr>
            ) : rows.length > 0 ? (
              rows.map((r, i) => {
                const isKotakBaik = r.kondisi_kotak?.toLowerCase() === 'baik'
                const isIsiLengkap = r.kelengkapan_isi?.toLowerCase() === 'lengkap'
                const isObatLengkap = r.expired_obat?.toLowerCase() === 'lengkap'
                const centang = <i className="fa-solid fa-check icon-check"></i>
                return (
                  <tr key={r.id}>
                    <td><input type="checkbox" checked={checked.has(r.id)} onChange={(e) => { const s = new Set(checked); if (e.target.checked) s.add(r.id); else s.delete(r.id); setChecked(s) }} /></td>
                    <td>{offset + i + 1}</td>
                    <td>{r.username ? r.username : <span style={{ color: '#999', fontStyle: 'italic' }}>Belum Diinspeksi</span>}</td>
                    <td>{r.code}</td>
                    <td>{r.line_area || '-'}</td>
                    <td>{r.lokasi}</td>
                    <td>{r.catatan || '-'}</td>
                    <td className="text-center col-check">{isKotakBaik ? centang : ''}</td>
                    <td className="text-center col-check">{!isKotakBaik ? centang : ''}</td>
                    <td className="text-center col-check">{isIsiLengkap ? centang : ''}</td>
                    <td className="text-center col-check">{!isIsiLengkap ? centang : ''}</td>
                    <td className="text-center col-check">{isObatLengkap ? centang : ''}</td>
                    <td className="text-center col-check">{!isObatLengkap ? centang : ''}</td>
                    <td>
                      <button type="button" className="table-action-btn btn-barcode" onClick={() => openBarcode(r.id, r.code)}><i className="fa-solid fa-barcode"></i></button>
                      <button
                        type="button"
                        className="table-action-btn btn-edit"
                        onClick={() => {
                          setFormEdit({
                            id: r.id, code: r.code, line_area: r.line_area || '', lokasi: r.lokasi,
                            kondisi_kotak: r.kondisi_kotak || 'Baik', kelengkapan_isi: r.kelengkapan_isi || 'Lengkap',
                            expired_obat: r.expired_obat || 'Lengkap', catatan: r.catatan || '',
                          })
                          setEditTitle('Edit/Inspeksi Data Master Kotak P3K')
                          setModalEdit(true)
                        }}
                      >
                        <i className="fa-solid fa-pencil"></i>
                      </button>
                      <button type="button" className="table-action-btn btn-delete" onClick={() => handleDelete(r.id)}><i className="fa-solid fa-trash"></i></button>
                    </td>
                  </tr>
                )
              })
            ) : (
              <tr><td colSpan={14} style={{ textAlign: 'center' }}>Tidak ada data master tersedia</td></tr>
            )}
          </tbody>
        </table>
      </div>

      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', flexWrap: 'wrap', gap: '10px', marginTop: '12px' }}>
        <div>Menampilkan {totalRows > 0 ? offset + 1 : 0}-{Math.min(offset + limit, totalRows)} dari {totalRows} data</div>
        {totalPages > 1 && (
          <div style={{ display: 'flex', gap: '5px', flexWrap: 'wrap' }}>
            {page > 1 && (
              <button onClick={() => loadList({ page: page - 1 })} style={{ padding: '8px 12px', border: '1px solid #ccc', background: '#fff', borderRadius: '4px', cursor: 'pointer', color: '#333' }}>&laquo; Sebelumnya</button>
            )}
            {Array.from({ length: totalPages }, (_, i) => i + 1).map((p) => (
              <button key={p} onClick={() => loadList({ page: p })} style={{ padding: '8px 12px', border: `1px solid ${p === page ? '#004ef5' : '#ccc'}`, background: p === page ? '#004ef5' : '#fff', color: p === page ? '#fff' : '#333', borderRadius: '4px', cursor: 'pointer', fontWeight: p === page ? '700' : '400' }}>{p}</button>
            ))}
            {page < totalPages && (
              <button onClick={() => loadList({ page: page + 1 })} style={{ padding: '8px 12px', border: '1px solid #ccc', background: '#fff', borderRadius: '4px', cursor: 'pointer', color: '#333' }}>Selanjutnya &raquo;</button>
            )}
          </div>
        )}
      </div>

      {modalTambah && (
        <ModalShell onClose={() => setModalTambah(false)}>
          <h3>Tambah Data Kotak P3K</h3>
          <form onSubmit={handleSubmitTambah}>
            <Field label="Kode (Otomatis):">
              <input type="text" value={formTambah.code} readOnly style={{ width: '100%', padding: '8px', boxSizing: 'border-box', marginTop: '4px', backgroundColor: '#e9ecef', cursor: 'not-allowed', fontWeight: 'bold', border: '1px solid #ccc', borderRadius: '4px' }} />
            </Field>
            <Field label="Departemen (Area Line):">
              <select value={formTambah.line_area} onChange={(e) => setFormTambah({ ...formTambah, line_area: e.target.value })} required style={{ width: '100%', padding: '8px', boxSizing: 'border-box', marginTop: '4px' }}>
                <option value="">-- Pilih Departemen --</option>
                {areas.map((a) => <option key={a} value={a}>{a}</option>)}
              </select>
            </Field>
            <Field label="Lokasi:">
              <input type="text" value={formTambah.lokasi} onChange={(e) => setFormTambah({ ...formTambah, lokasi: e.target.value })} required style={{ width: '100%', padding: '8px', boxSizing: 'border-box', marginTop: '4px' }} />
            </Field>
            <RadioGroup label="Kondisi Kotak P3K:" value={formTambah.kondisi_kotak} onChange={(v) => setFormTambah({ ...formTambah, kondisi_kotak: v })} options={[['Baik', 'Baik'], ['Tidak', 'Tidak']]} />
            <RadioGroup label="Isi Kotak P3K:" value={formTambah.kelengkapan_isi} onChange={(v) => setFormTambah({ ...formTambah, kelengkapan_isi: v })} options={[['Lengkap', 'Lengkap'], ['Tidak', 'Tidak']]} />
            <RadioGroup label="Obat-obatan:" value={formTambah.expired_obat} onChange={(v) => setFormTambah({ ...formTambah, expired_obat: v })} options={[['Lengkap', 'Lengkap'], ['Tidak', 'Tidak']]} />
            <Field label="Catatan:">
              <textarea value={formTambah.catatan} onChange={(e) => setFormTambah({ ...formTambah, catatan: e.target.value })} style={{ width: '100%', padding: '8px', boxSizing: 'border-box', marginTop: '4px' }} rows={3}></textarea>
            </Field>
            <button type="submit" style={{ background: '#004ef5', color: 'white', border: 'none', padding: '10px 15px', borderRadius: '4px', cursor: 'pointer' }}>Simpan</button>
            <button type="button" onClick={() => setModalTambah(false)} style={{ padding: '10px 15px', border: '1px solid #ccc', background: '#fff', borderRadius: '4px', cursor: 'pointer' }}>Batal</button>
          </form>
        </ModalShell>
      )}

      {modalEdit && (
        <ModalShell onClose={() => setModalEdit(false)}>
          <h3>{editTitle}</h3>
          <form onSubmit={handleSubmitEdit}>
            <Field label="Kode:">
              <input type="text" value={formEdit.code} onChange={(e) => setFormEdit({ ...formEdit, code: e.target.value })} required style={{ width: '100%', padding: '8px', boxSizing: 'border-box', marginTop: '4px' }} />
            </Field>
            <Field label="Departemen (Area Line):">
              <select value={formEdit.line_area} onChange={(e) => setFormEdit({ ...formEdit, line_area: e.target.value })} required style={{ width: '100%', padding: '8px', boxSizing: 'border-box', marginTop: '4px' }}>
                <option value="">-- Pilih Departemen --</option>
                {areas.map((a) => <option key={a} value={a}>{a}</option>)}
              </select>
            </Field>
            <Field label="Lokasi:">
              <input type="text" value={formEdit.lokasi} onChange={(e) => setFormEdit({ ...formEdit, lokasi: e.target.value })} required style={{ width: '100%', padding: '8px', boxSizing: 'border-box', marginTop: '4px' }} />
            </Field>
            <RadioGroup label="Kondisi Kotak P3K:" value={formEdit.kondisi_kotak} onChange={(v) => setFormEdit({ ...formEdit, kondisi_kotak: v })} options={[['Baik', 'Baik'], ['Tidak', 'Tidak']]} />
            <RadioGroup label="Isi Kotak P3K:" value={formEdit.kelengkapan_isi} onChange={(v) => setFormEdit({ ...formEdit, kelengkapan_isi: v })} options={[['Lengkap', 'Lengkap'], ['Tidak', 'Tidak']]} />
            <RadioGroup label="Obat-obatan:" value={formEdit.expired_obat} onChange={(v) => setFormEdit({ ...formEdit, expired_obat: v })} options={[['Lengkap', 'Lengkap'], ['Tidak', 'Tidak']]} />
            <Field label="Catatan:">
              <textarea value={formEdit.catatan} onChange={(e) => setFormEdit({ ...formEdit, catatan: e.target.value })} style={{ width: '100%', padding: '8px', boxSizing: 'border-box', marginTop: '4px' }} rows={3}></textarea>
            </Field>
            <button type="submit" style={{ background: '#ffc107', color: 'black', border: 'none', padding: '10px 15px', borderRadius: '4px', cursor: 'pointer', fontWeight: 600 }}>Update Data</button>
            <button type="button" onClick={() => setModalEdit(false)} style={{ padding: '10px 15px', border: '1px solid #ccc', background: '#fff', borderRadius: '4px', cursor: 'pointer' }}>Batal</button>
          </form>
        </ModalShell>
      )}

      {modalBarcode && (
        <ModalShell onClose={() => setModalBarcode(false)} width={650}>
          <h3 style={{ marginBottom: '15px' }}>QR Code Generator</h3>
          <input type="text" value={barcodeCode} readOnly style={{ width: '50%', padding: '8px', textAlign: 'center', background: '#f1f1f1', border: '1px solid #ddd', marginBottom: '15px', borderRadius: '4px' }} />
          <div style={{ marginBottom: '20px', border: '1px solid #ddd', padding: '10px', background: '#fafafa', display: 'inline-block' }}>
            <img src={barcodeSrc} alt="Barcode Preview" style={{ maxWidth: '100%', height: 'auto', display: 'block' }} />
          </div>
          <br />
          <a href={barcodeSrc} download={`Barcode_P3K_${barcodeCode}.png`} style={{ background: '#28a745', color: 'white', textDecoration: 'none', padding: '10px 20px', borderRadius: '5px', display: 'inline-block', marginRight: '10px', fontWeight: 600 }}>
            <i className="fa-solid fa-download"></i> Download as Image
          </a>
          <button type="button" onClick={() => setModalBarcode(false)} style={{ padding: '10px 20px', borderRadius: '5px', border: '1px solid #ccc', cursor: 'pointer', background: '#fff' }}>Tutup</button>
        </ModalShell>
      )}

      <style>{`
        .table-container { background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,.1); overflow-x: auto; overflow-y: hidden; white-space: nowrap; }
        .table-container table { width: max-content; border-collapse: collapse; margin-top: 10px; }
        thead tr { background-color: #004ef5; color: white; }
        thead th { padding: 10px; text-align: left; font-weight: 600; border: 1px solid #0042d1; }
        tbody td { padding: 10px; border: 1px solid #ddd; }
        thead tr.sub-header { background-color: #0042d1; }
        thead tr.sub-header th { padding: 6px 4px; font-size: 12px; }
        .text-center { text-align: center; }
        .col-check { width: 40px; padding: 6px 4px !important; }
        .icon-check { color: #28a745; font-size: 14px; }
        tbody tr:nth-child(even) { background-color: #f9f9f9; }
        tbody tr:hover { background-color: #f1f1f1; }
        .table-action-btn { padding: 6px 10px; border-radius: 4px; border: none; cursor: pointer; color: white; margin-right: 3px; transition: 0.3s; }
        .btn-barcode { background-color: #17a2b8; }
        .btn-edit { background-color: #ffc107; color: #000; }
        .btn-delete { background-color: #dc3545; }
        .table-action-btn:hover { opacity: 0.8; }
        .radio-group { display: flex; gap: 20px; margin-top: 5px; margin-bottom: 5px; }
        .radio-group label { display: flex; align-items: center; gap: 6px; cursor: pointer; font-weight: 500; }
        .radio-group input[type="radio"] { cursor: pointer; width: 16px; height: 16px; }
      `}</style>
    </>
  )
}

function ModalShell({ children, onClose, width = 420 }: { children: React.ReactNode; onClose: () => void; width?: number }) {
  return (
    <div style={{ display: 'block', position: 'fixed', top: 0, left: 0, width: '100%', height: '100%', background: 'rgba(0,0,0,0.5)', zIndex: 1000, overflowY: 'auto', padding: '16px' }} onClick={onClose}>
      <div style={{ background: '#fff', width: '100%', maxWidth: width, margin: '16px auto', padding: '20px', borderRadius: '8px', boxSizing: 'border-box' }} onClick={(e) => e.stopPropagation()}>
        {children}
      </div>
    </div>
  )
}

function Field({ label, children }: { label: string; children: React.ReactNode }) {
  return (
    <div style={{ marginBottom: '12px' }}>
      <label style={{ fontWeight: 600 }}>{label}</label><br />
      {children}
    </div>
  )
}

function RadioGroup({ label, value, onChange, options }: { label: string; value: string; onChange: (v: string) => void; options: [string, string][] }) {
  return (
    <div style={{ marginBottom: '12px' }}>
      <label style={{ fontWeight: 600 }}>{label}</label>
      <div className="radio-group">
        {options.map(([val, text]) => (
          <label key={val}>
            <input type="radio" name={label} value={val} checked={value === val} onChange={() => onChange(val)} /> {text}
          </label>
        ))}
      </div>
    </div>
  )
}