import { defineStore } from 'pinia'
import { ref } from 'vue'

const API_BASE = import.meta.env.VITE_API_BASE ?? 'http://localhost:8000/costume-rental/backend'

export const useBookingsStore = defineStore('bookings', () => {
  const bookings = ref([])
  const loading = ref(false)
  const error = ref(null)

  // ── Fetch bookings (optionally filter by email) ────────────────────────────
  const fetchBookings = async ({ email = '', customerId = null } = {}) => {
    loading.value = true
    error.value = null
    try {
      const params = new URLSearchParams()
      if (email) params.set('email', email)
      if (customerId) params.set('customerId', customerId)
      const qs = params.toString() ? `?${params.toString()}` : ''
      const res = await fetch(`${API_BASE}/api/bookings${qs}`)
      if (!res.ok) throw new Error(`HTTP ${res.status}`)
      const json = await res.json()
      bookings.value = json.data
    } catch (err) {
      error.value = err.message
    } finally {
      loading.value = false
    }
  }

  // ── Add a new booking ──────────────────────────────────────────────────────
  const addBooking = async (booking) => {
    error.value = null
    try {
      const res = await fetch(`${API_BASE}/api/bookings`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(booking),
      })
      if (!res.ok) {
        const json = await res.json()
        throw new Error(json.error ?? `HTTP ${res.status}`)
      }
      const json = await res.json()
      bookings.value.unshift(json.data)
      return json.data
    } catch (err) {
      error.value = err.message
      throw err
    }
  }

  // ── Cancel a booking ───────────────────────────────────────────────────────
  const cancelBooking = async (id) => {
    error.value = null
    try {
      const res = await fetch(`${API_BASE}/api/bookings/${id}/cancel`, {
        method: 'PUT',
      })
      if (!res.ok) {
        const json = await res.json()
        throw new Error(json.error ?? `HTTP ${res.status}`)
      }
      const json = await res.json()
      const index = bookings.value.findIndex((b) => b.id === id)
      if (index !== -1) {
        bookings.value[index] = json.data
      }
    } catch (err) {
      error.value = err.message
      throw err
    }
  }

  // ── Update booking status (management/admin) ────────────────────────────────
  const updateStatus = async (id, status, role = '') => {
    error.value = null
    try {
      const headers = { 'Content-Type': 'application/json' }
      if (role) headers['X-Role'] = role

      const res = await fetch(`${API_BASE}/api/bookings/${id}/status`, {
        method: 'PUT',
        headers,
        body: JSON.stringify({ status }),
      })
      if (!res.ok) {
        const json = await res.json()
        throw new Error(json.error ?? `HTTP ${res.status}`)
      }
      const json = await res.json()
      const index = bookings.value.findIndex((b) => b.id === id)
      if (index !== -1) {
        bookings.value[index] = json.data
      }
      return json.data
    } catch (err) {
      error.value = err.message
      throw err
    }
  }

  const getUserBookings = () => {
    return [...bookings.value].sort((a, b) => new Date(b.bookingDate) - new Date(a.bookingDate))
  }

  return {
    bookings,
    loading,
    error,
    fetchBookings,
    addBooking,
    cancelBooking,
    updateStatus,
    getUserBookings,
  }
})
