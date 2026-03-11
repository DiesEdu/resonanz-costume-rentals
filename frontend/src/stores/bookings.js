import { defineStore } from 'pinia'
import { ref } from 'vue'
import { Toast } from 'bootstrap'
import { useAuthStore } from './auth'

const API_BASE = import.meta.env.VITE_API_BASE ?? 'http://localhost:8000/costume-rental/backend'

export const useBookingsStore = defineStore('bookings', () => {
  const manageBookings = ref([])
  const bookings = ref([])
  const loading = ref(false)
  const error = ref(null)

  const token = localStorage.getItem('auth_token') || null

  const fetchBookingsManager = async () => {
    const authStore = useAuthStore()
    const currentUserId = authStore.user?.id ?? null

    loading.value = true
    error.value = null
    try {
      // For regular users, always scope the request to their own bookings.
      // Managers/admins can optionally pass a customerId (or none to fetch all).
      if (!currentUserId) {
        manageBookings.value = []
        throw new Error('Please sign in to view your bookings.')
      }
      const res = await fetch(`${API_BASE}/api/bookings/list-admin`, {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      })
      if (!res.ok) throw new Error(`HTTP ${res.status}`)
      const json = await res.json()
      manageBookings.value = json.data
    } catch (err) {
      error.value = err.message
    } finally {
      loading.value = false
    }
  }

  // ── Fetch bookings (optionally filter by email) ────────────────────────────
  const fetchBookings = async () => {
    const authStore = useAuthStore()
    const currentUserId = authStore.user?.id ?? null

    loading.value = true
    error.value = null
    try {
      // For regular users, always scope the request to their own bookings.
      // Managers/admins can optionally pass a customerId (or none to fetch all).
      if (!currentUserId) {
        bookings.value = []
        throw new Error('Please sign in to view your bookings.')
      }
      const res = await fetch(`${API_BASE}/api/bookings`, {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      })
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
        headers: {
          Authorization: `Bearer ${token}`,
        },
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

  // Lightweight shared toast for booking events (UI helper used by views)
  let toastEl = null
  let toastInstance = null
  const ensureToast = () => {
    if (typeof document === 'undefined') return null
    if (toastEl) return toastEl

    const container = document.createElement('div')
    container.className = 'toast-container position-fixed top-0 end-0 p-3'
    container.style.zIndex = 1100

    toastEl = document.createElement('div')
    toastEl.className = 'toast align-items-center text-white border-0 shadow-lg bg-success'
    toastEl.setAttribute('role', 'alert')
    toastEl.setAttribute('aria-live', 'assertive')
    toastEl.setAttribute('aria-atomic', 'true')
    toastEl.innerHTML = `
      <div class="d-flex">
        <div class="toast-body d-flex align-items-center gap-2">
          <i class="bi bi-check2-circle fs-5"></i>
          <span class="toast-text">Booking submitted. We will notify you after approval.</span>
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    `
    container.appendChild(toastEl)
    document.body.appendChild(container)
    toastInstance = new Toast(toastEl, { delay: 2400 })
    return toastEl
  }

  const showBookingToast = (message = 'Booking submitted. We will notify you after approval.') => {
    const el = ensureToast()
    if (!el || !toastInstance) return
    const textSpan = el.querySelector('.toast-text')
    if (textSpan) textSpan.textContent = message
    toastInstance.show()
  }

  return {
    manageBookings,
    bookings,
    loading,
    error,
    fetchBookingsManager,
    fetchBookings,
    addBooking,
    cancelBooking,
    updateStatus,
    getUserBookings,
    showBookingToast,
  }
})
