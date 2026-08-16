import { Routes, Route } from 'react-router-dom'
import LandingPage from './pages/LandingPage'
import LoginPage from './pages/LoginPage'
import LoginDaruratPage from './pages/LoginDaruratPage'
import LupaPasswordPage from './pages/LupaPasswordPage'
import AdminLayout from './admin/AdminLayout'
import Dashboard from './admin/Dashboard'
import ScanPage from './admin/ScanPage'
import MasterLampuPage from './admin/MasterLampuPage'
import MasterP3kPage from './admin/MasterP3kPage'
import MasterEyewashPage from './admin/MasterEyewashPage'
import AreaLinePage from './admin/AreaLinePage'
import AgendaPage from './admin/AgendaPage'
import LaporanPage from './admin/LaporanPage'
import DataInspeksiPage from './admin/DataInspeksiPage'

export default function App() {
  return (
    <Routes>
      <Route path="/" element={<LandingPage />} />
      <Route path="/login" element={<LoginPage />} />
      <Route path="/login-darurat" element={<LoginDaruratPage />} />
      <Route path="/lupa-password" element={<LupaPasswordPage />} />

      <Route path="/admin" element={<AdminLayout />}>
        <Route index element={<Dashboard />} />
        <Route path="scan" element={<ScanPage />} />
        <Route path="master-lampu" element={<MasterLampuPage type="emergency" />} />
        <Route path="lampu-exit" element={<MasterLampuPage type="exit" />} />
        <Route path="master-p3k" element={<MasterP3kPage />} />
        <Route path="master-eyewash" element={<MasterEyewashPage />} />
        <Route path="area-line/:jenis" element={<AreaLinePage />} />
        <Route path="agenda" element={<AgendaPage />} />
        <Route path="laporan/:type" element={<LaporanPage />} />
        <Route path="data-inspeksi/:type" element={<DataInspeksiPage />} />
      </Route>

      <Route path="*" element={<LandingPage />} />
    </Routes>
  )
}