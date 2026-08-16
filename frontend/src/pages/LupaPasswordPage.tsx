import { useState } from 'react'
import { Link } from 'react-router-dom'
import client from '../api/client'
import { getErrorMessage } from '../utils'

export default function LupaPasswordPage() {
  const [username, setUsername] = useState('')
  const [passwordBaru, setPasswordBaru] = useState('')
  const [konfirmasi, setKonfirmasi] = useState('')
  const [success, setSuccess] = useState('')
  const [error, setError] = useState('')
  const [loading, setLoading] = useState(false)

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault()
    setLoading(true)
    setError('')
    setSuccess('')
    try {
      const res = await client.post('/auth.php', new URLSearchParams({ action: 'reset_password', username, password_baru: passwordBaru, konfirmasi_password: konfirmasi }), {
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      })
      if (res.data.success) {
        setSuccess(res.data.message)
      }
    } catch (err: unknown) {
      setError(getErrorMessage(err))
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="lupa-page">
      <div className="login-box">
        <div className="logo-container">
          <img src={`${import.meta.env.BASE_URL}foto/logo.png`} alt="Safety Facility Logo" className="logo-img" />
        </div>
        <h2>FORGET PASSWORD</h2>

        {success && (
          <div className="msg success"><i className="fa-solid fa-circle-check"></i> {success}</div>
        )}
        {error && (
          <div className="msg error"><i className="fa-solid fa-triangle-exclamation"></i> {error}</div>
        )}

        <form onSubmit={handleSubmit} style={{ width: '100%' }}>
          <div className="input-group">
            <input type="text" value={username} onChange={(e) => setUsername(e.target.value)} placeholder="Username" required autoComplete="off" />
          </div>
          <div className="input-group">
            <input type="password" value={passwordBaru} onChange={(e) => setPasswordBaru(e.target.value)} placeholder="Password Baru" required />
          </div>
          <div className="input-group">
            <input type="password" value={konfirmasi} onChange={(e) => setKonfirmasi(e.target.value)} placeholder="Konfirmasi Password" required />
          </div>
          <button type="submit" className="btn-signin" disabled={loading}>{loading ? 'Memproses...' : 'Reset Password'}</button>
        </form>

        <div className="extra-links">
          <Link to="/login"><i className="fa-solid fa-arrow-left"></i> Kembali ke Login</Link>
        </div>

        <div className="footer-text">Silahkan reset password terlebih dahulu</div>
      </div>
      <style>{`
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        .lupa-page { background: url('https://images.unsplash.com/photo-1516549655169-df83a0774514?q=80&w=1920') no-repeat center center fixed; background-size: cover; height: 100vh; display: flex; justify-content: center; align-items: center; }
        .login-box { background: rgba(255,255,255,0.5); backdrop-filter: blur(15px); -webkit-backdrop-filter: blur(15px); width: 400px; padding: 40px 30px; border-radius: 30px; box-shadow: 0px 15px 25px rgba(0,0,0,0.2); text-align: center; height: 93vh; display: flex; flex-direction: column; justify-content: center; align-items: center; border: 1px solid rgba(255,255,255,0.3); animation: lupaSlideInUp 0.6s cubic-bezier(0.16,1,0.3,1); }
        @keyframes lupaSlideInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        .logo-container { margin-bottom: 25px; display: flex; justify-content: center; align-items: center; }
        .logo-img { max-width: 260px; height: auto; }
        h2 { font-size: 26px; font-weight: 700; color: #333; margin-bottom: 30px; letter-spacing: 1px; text-transform: uppercase; }
        .input-group { width: 100%; margin-bottom: 20px; }
        .input-group input { width: 100%; padding: 15px 25px; border: 1px solid #ccc; border-radius: 50px; font-size: 15px; outline: none; transition: 0.3s; text-align: center; }
        .input-group input:focus { border-color: #615bb0; box-shadow: 0 0 8px rgba(97,91,176,0.3); }
        .btn-signin { width: 100%; padding: 15px; border: none; border-radius: 50px; background: #615bb0; color: white; font-size: 16px; font-weight: 600; cursor: pointer; transition: 0.3s; margin-top: 10px; margin-bottom: 25px; }
        .btn-signin:hover { background: #4f4999; box-shadow: 0px 5px 12px rgba(97,91,176,0.4); }
        .extra-links { font-size: 14px; margin-bottom: 15px; }
        .extra-links a { color: #0056b3; text-decoration: none; display: inline-block; margin: 4px 0; }
        .extra-links a:hover { text-decoration: underline; }
        .footer-text { font-size: 12px; color: #777; margin-top: 20px; }
        .msg { padding: 10px 20px; border-radius: 25px; font-size: 13px; margin-bottom: 20px; width: 100%; font-weight: 600; }
        .msg.success { color: #155724; background: #d4edda; }
        .msg.error { color: #dc3545; background: #f8d7da; }
        @media (max-width: 450px) {
          .login-box { width: 90% !important; padding: 30px 20px !important; height: auto !important; min-height: 80vh !important; border-radius: 20px !important; }
          h2 { font-size: 22px !important; }
          .logo-img { max-width: 200px !important; }
        }
      `}</style>
    </div>
  )
}