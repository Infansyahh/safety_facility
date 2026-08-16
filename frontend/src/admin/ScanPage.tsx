import { useEffect, useRef, useState } from 'react'
import { useNavigate } from 'react-router-dom'
import client from '../api/client'

declare global {
  interface Window {
    Html5QrcodeScanner: any
    Html5QrcodeSupportedFormats: any
  }
}

export default function ScanPage() {
  const navigate = useNavigate()
  const readerRef = useRef<HTMLDivElement>(null)
  const scannerRef = useRef<any>(null)
  const [error, setError] = useState('')
  const [loading, setLoading] = useState(false)

  useEffect(() => {
    let mounted = true

    async function loadScanner() {
      if (document.getElementById('html5-qrcode-lib')) {
        initScanner()
        return
      }
      const script = document.createElement('script')
      script.id = 'html5-qrcode-lib'
      script.src = 'https://unpkg.com/html5-qrcode'
      script.async = true
      script.onload = () => mounted && initScanner()
      document.body.appendChild(script)
    }

    function initScanner() {
      if (!readerRef.current) return
      const Html5QrcodeScanner = window.Html5QrcodeScanner
      if (!Html5QrcodeScanner) return
      const scanner = new Html5QrcodeScanner('reader', {
        fps: 15,
        qrbox: { width: 300, height: 300 },
        formatsToSupport: [window.Html5QrcodeSupportedFormats.QR_CODE],
        experimentalFeatures: { useBarCodeDetectorIfSupported: true },
      })
      scannerRef.current = scanner
      scanner.render(async (decodedText: string) => {
        const cleanText = decodedText.trim()
        if (cleanText === '') {
          alert('QR Code kosong atau tidak terbaca dengan jelas.')
          return
        }
        setLoading(true)
        try {
          const res = await client.get('/scan.php', { params: { scan_id: cleanText } })
          if (res.data.target === 'scan') {
            setError('scan_id kosong.')
            return
          }
          const map: Record<string, string> = {
            master_lampu: '/admin/master-lampu',
            lampu_exit: '/admin/lampu-exit',
            master_p3k: '/admin/master-p3k',
            master_eyewash: '/admin/master-eyewash',
          }
          const path = map[res.data.target] || '/admin/master-lampu'
          navigate(`${path}?scan_id=${encodeURIComponent(cleanText)}&action=scan_popup`)
        } catch {
          setError('Gagal memproses QR Code.')
        } finally {
          setLoading(false)
        }
      })
    }

    loadScanner()
    return () => {
      mounted = false
      if (scannerRef.current) {
        try {
          scannerRef.current.clear()
        } catch {
          /* ignore */
        }
        scannerRef.current = null
      }
    }
  }, [])

  return (
    <>
      <h2 className="page-title">Scan QR Code</h2>
      <div style={{ background: '#fff', padding: '20px', borderRadius: '10px', border: '2px solid #000', maxWidth: '500px', margin: 'auto' }}>
        <div ref={readerRef} id="reader"></div>
      </div>
      {error && <p style={{ textAlign: 'center', color: '#dc3545', marginTop: '16px' }}>{error}</p>}
      {loading && <p style={{ textAlign: 'center', color: '#64748b', marginTop: '16px' }}>Memproses QR Code...</p>}
    </>
  )
}