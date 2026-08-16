import axios from 'axios'

export const API_BASE = import.meta.env.VITE_API_BASE || '/api'

const client = axios.create({
  baseURL: API_BASE,
  timeout: 30000,
  headers: { 'X-Requested-With': 'XMLHttpRequest' },
})

client.interceptors.response.use(
  (res) => res,
  (err) => {
    if (err.response?.status === 401) {
      window.location.href = `${import.meta.env.BASE_URL}login`
    }
    return Promise.reject(err)
  },
)

export default client