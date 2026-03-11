<template>
  <div class="manage-page">
    <div class="page-header">
      <div class="container d-flex flex-column flex-md-row align-items-md-center gap-3">
        <div>
          <p class="section-eyebrow mb-1">Operations</p>
          <h1 class="fw-bold text-white" style="font-family: 'Playfair Display', serif">
            Booking Dashboard
          </h1>
          <div class="gold-divider" style="margin-left: 0; margin-top: 10px"></div>
        </div>
        <div class="ms-md-auto d-flex gap-3 flex-wrap align-items-center">
          <div class="role-pill">
            <label class="text-muted small mb-1">Acting as</label>
            <select v-model="role" class="form-select form-select-sm">
              <option value="costume_management">Costume Management</option>
              <option value="admin">Admin</option>
            </select>
          </div>
          <div>
            <label class="text-muted small mb-1">Status filter</label>
            <select v-model="statusFilter" class="form-select form-select-sm">
              <option value="all">All</option>
              <option value="waiting_approval">Waiting Approval</option>
              <option value="processing">Processing</option>
              <option value="completed">Completed</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>
          <button class="btn btn-outline-light btn-sm" @click="refresh" :disabled="bookingsStore.loading">
            <span v-if="bookingsStore.loading" class="spinner-border spinner-border-sm me-2"></span>
            Refresh
          </button>
        </div>
      </div>
    </div>

    <div class="container py-4">
      <div class="card shadow-sm border-0">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th style="min-width: 200px">Costume</th>
                  <th>Customer</th>
                  <th>Period</th>
                  <th>Total</th>
                  <th>Status</th>
                  <th class="text-end" style="min-width: 220px">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="booking in filteredBookings" :key="booking.id">
                  <td>
                    <div class="d-flex align-items-center gap-3">
                      <img
                        :src="booking.costumeImage"
                        :alt="booking.costumeName"
                        class="rounded-1"
                        style="width: 60px; height: 60px; object-fit: cover"
                      />
                      <div>
                        <div class="fw-semibold">{{ booking.costumeName }}</div>
                        <div class="text-muted small">Size {{ booking.size }}</div>
                      </div>
                    </div>
                  </td>
                  <td>
                    <div class="fw-semibold">{{ booking.customerName }}</div>
                    <div class="text-muted small">{{ booking.email }}</div>
                    <div class="text-muted small">{{ booking.phone }}</div>
                  </td>
                  <td>
                    <div class="fw-semibold">{{ formatDate(booking.startDate) }}</div>
                    <div class="text-muted small">to {{ formatDate(booking.endDate) }}</div>
                  </td>
                  <td class="fw-semibold">${{ booking.totalPrice }}</td>
                  <td>
                    <span class="status-badge" :class="`status-${booking.status}`">
                      {{ formatStatus(booking.status) }}
                    </span>
                  </td>
                  <td class="text-end">
                    <div class="btn-group btn-group-sm">
                      <button
                        class="btn btn-outline-primary"
                        :disabled="booking.status !== 'waiting_approval' || isActing"
                        @click="changeStatus(booking, 'processing')"
                      >
                        Approve
                      </button>
                      <button
                        class="btn btn-outline-success"
                        :disabled="booking.status !== 'processing' || isActing"
                        @click="changeStatus(booking, 'completed')"
                      >
                        Complete
                      </button>
                      <button
                        class="btn btn-outline-danger"
                        :disabled="!['waiting_approval', 'processing'].includes(booking.status) || isActing"
                        @click="cancel(booking)"
                      >
                        Cancel
                      </button>
                    </div>
                  </td>
                </tr>
                <tr v-if="!bookingsStore.loading && filteredBookings.length === 0">
                  <td colspan="6" class="text-center py-4 text-muted">
                    No bookings found for this filter.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useBookingsStore } from '@/stores/bookings'

const bookingsStore = useBookingsStore()
const role = ref('costume_management')
const statusFilter = ref('waiting_approval')
const isActing = ref(false)

const refresh = () => bookingsStore.fetchBookings()

const filteredBookings = computed(() => {
  const list = bookingsStore.bookings
  if (statusFilter.value === 'all') return list
  return list.filter((b) => b.status === statusFilter.value)
})

const formatDate = (dateString) =>
  new Date(dateString).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  })

const formatStatus = (status) =>
  status
    .replace(/_/g, ' ')
    .split(' ')
    .map((s) => s.charAt(0).toUpperCase() + s.slice(1))
    .join(' ')

const changeStatus = async (booking, status) => {
  try {
    isActing.value = true
    await bookingsStore.updateStatus(booking.id, status, role.value)
  } finally {
    isActing.value = false
  }
}

const cancel = async (booking) => {
  if (!confirm('Cancel this booking?')) return
  isActing.value = true
  try {
    await bookingsStore.cancelBooking(booking.id)
  } finally {
    isActing.value = false
  }
}

onMounted(async () => {
  await bookingsStore.fetchBookings()
})
</script>

<style scoped>
.manage-page {
  background: var(--ivory);
  min-height: 100vh;
}
.page-header {
  background: linear-gradient(135deg, var(--charcoal-2), var(--charcoal-3));
  color: white;
  padding: 90px 0 50px;
}
.role-pill {
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 6px;
  padding: 10px 12px;
}
.status-badge {
  padding: 4px 10px;
  border-radius: 4px;
  font-size: 0.78rem;
  font-weight: 700;
  letter-spacing: 0.08em;
}
</style>
