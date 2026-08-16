import { useState } from 'react'
import axios from 'axios'

export function formatTanggalIndonesia(): string {
  const dh: Record<string, string> = {
    Sunday: 'Minggu', Monday: 'Senin', Tuesday: 'Selasa', Wednesday: 'Rabu',
    Thursday: 'Kamis', Friday: 'Jumat', Saturday: 'Sabtu',
  }
  const db: Record<string, string> = {
    January: 'Januari', February: 'Februari', March: 'Maret', April: 'April',
    May: 'Mei', June: 'Juni', July: 'Juli', August: 'Agustus',
    September: 'September', October: 'Oktober', November: 'November', December: 'Desember',
  }
  const now = new Date()
  return `${dh[now.toLocaleString('en-US', { weekday: 'long' })]}, ${now.getDate()} ${db[now.toLocaleString('en-US', { month: 'long' })]} ${now.getFullYear()}`
}

export const NAMA_BULAN = [
  '', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
  'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
]

export function useModal(initial = false) {
  const [open, setOpen] = useState(initial)
  return { open, setOpen, openModal: () => setOpen(true), closeModal: () => setOpen(false) }
}

export function getErrorMessage(err: unknown, fallback = 'Terjadi kesalahan.'): string {
  if (axios.isAxiosError(err)) {
    return err.response?.data?.message || err.message || fallback
  }
  if (err instanceof Error) return err.message
  return fallback
}