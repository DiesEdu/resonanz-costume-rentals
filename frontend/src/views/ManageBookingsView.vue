<template>
  <div class="manage-page">
    <div class="page-header fade-in">
      <div class="container d-flex flex-column flex-md-row align-items-md-center gap-3 slide-up">
        <div>
          <p class="section-eyebrow mb-1">Operations</p>
          <h1 class="fw-bold text-white" style="font-family: 'Playfair Display', serif">
            Booking Dashboard
          </h1>
          <div class="gold-divider" style="margin-left: 0; margin-top: 10px"></div>
        </div>
        <div class="ms-md-auto d-flex gap-3 flex-wrap align-items-center header-controls">
          <div class="role-pill">
            <label class="small mb-1">Acting as</label>
            <select :value="actingRole" disabled class="form-select form-select-sm fancy-select">
              <option value="costume_management">Costume Management</option>
              <option value="admin">Admin</option>
            </select>
          </div>
          <div class="role-pill">
            <label class="small mb-1">Status filter</label>
            <select v-model="statusFilter" class="form-select form-select-sm fancy-select">
              <option class="text-muted" value="all">All</option>
              <option class="text-muted" value="waiting_approval">Waiting Approval</option>
              <option class="text-muted" value="processing">Processing</option>
              <option class="text-muted" value="completed">Completed</option>
              <option class="text-muted" value="cancelled">Cancelled</option>
            </select>
          </div>
          <button
            class="btn btn-outline-light btn-sm shine"
            @click="refresh"
            :disabled="bookingsStore.loading"
          >
            <span v-if="bookingsStore.loading" class="spinner-border spinner-border-sm me-2"></span>
            Refresh
          </button>
        </div>
      </div>
      <div class="container mt-4">
        <div class="row g-3">
          <div class="col-md-3" v-for="stat in statCards" :key="stat.key">
            <div class="stat-card float-in" :style="`animation-delay: ${stat.index * 80}ms`">
              <p class="mb-1 small">{{ stat.label }}</p>
              <div class="d-flex align-items-baseline gap-2">
                <span class="display-6 fw-bold">{{ stat.value }}</span>
                <span class="stat-dot" :class="stat.class"></span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="container py-5 fade-in" style="animation-delay: 120ms">
      <div class="glass-card shadow-lg rise-in">
        <div class="table-responsive">
          <table class="table align-middle mb-0 luxe-table">
            <thead>
              <tr>
                <th style="min-width: 220px">Costume</th>
                <th>Customer</th>
                <th>Period</th>
                <th>Status</th>
                <th class="text-end" style="min-width: 230px">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="booking in filteredBookings" :key="booking.id" class="row-hover">
                <td>
                  <div class="d-flex align-items-center gap-3">
                    <div class="thumb-wrap">
                      <LazyDriveImage
                        :fileId="bookingImageUrls[booking.id]"
                        :alt="booking.costumeName"
                        style="
                          width: 90px;
                          height: 90px;
                          object-fit: cover;
                          display: block;
                          border-radius: 10px;
                        "
                      />
                      <img :src="booking.costumeImage" :alt="booking.costumeName" class="thumb" />
                    </div>
                    <div>
                      <div class="fw-semibold">{{ booking.costumeName }}</div>
                      <div class="small">
                        Size: <span class="fst-italic">{{ booking.costumeSize }}</span>
                      </div>
                    </div>
                  </div>
                </td>
                <td>
                  <div class="fw-semibold">{{ booking.customerName }}</div>
                  <div class="small">{{ booking.email }}</div>
                  <div class="small">{{ booking.phone }}</div>
                </td>
                <td>
                  <div class="fw-semibold">{{ formatDate(booking.startDate) }}</div>
                  <div class="small">to {{ formatDate(booking.endDate) }}</div>
                </td>
                <td>
                  <span class="status-badge" :class="`status-${booking.status}`">
                    {{ formatStatus(booking.status) }}
                  </span>
                </td>
                <td class="text-end">
                  <div class="btn-stack">
                    <button
                      class="btn btn-outline-primary btn-sm"
                      :disabled="booking.status !== 'waiting_approval' || isActing"
                      @click="changeStatus(booking, 'processing')"
                    >
                      Approve
                    </button>
                    <button
                      class="btn btn-outline-success btn-sm"
                      :disabled="booking.status !== 'processing' || isActing"
                      @click="changeStatus(booking, 'completed')"
                    >
                      Complete
                    </button>
                    <button
                      class="btn btn-outline-danger btn-sm"
                      :disabled="
                        !['waiting_approval', 'processing'].includes(booking.status) || isActing
                      "
                      @click="cancel(booking)"
                    >
                      Cancel
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="!bookingsStore.loading && filteredBookings.length === 0">
                <td colspan="5" class="text-center py-4">No bookings found for this filter.</td>
              </tr>
              <tr v-if="bookingsStore.loading">
                <td colspan="5" class="text-center py-4">
                  <span class="spinner-border spinner-border-sm me-2"></span> Loading bookings...
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useBookingsStore } from '@/stores/bookings'
import { useAuthStore } from '@/stores/auth'
import { useCostumesStore } from '@/stores/costumes'
import { useRouter } from 'vue-router'

import LazyDriveImage from '@/components/LazyDriveImage.vue'

const bookingsStore = useBookingsStore()
const authStore = useAuthStore()
const costumesStore = useCostumesStore()
const router = useRouter()
const statusFilter = ref('all')
const isActing = ref(false)
const actingRole = computed(() => authStore.role || 'costume_management')

// Map to store image URLs for each booking
const bookingImageUrls = ref({})

const refresh = () => bookingsStore.fetchBookingsManager()

const filteredBookings = computed(() => {
  const list = bookingsStore.manageBookings
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
    await bookingsStore.updateStatus(booking.id, status, actingRole.value)
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
  const allowedRoles = ['costume_management', 'admin']
  if (!allowedRoles.includes(authStore.userRole)) {
    alert('Access denied: management only.')
    router.replace('/')
    return
  }
  await bookingsStore.fetchBookingsManager()

  // Load image URLs for all bookings
  const loadedBookings = bookingsStore.manageBookings
  console.log('loaded bookings: ', loadedBookings)
  for (const booking of loadedBookings) {
    const imageName = booking.costumeImage || booking.costumeName
    if (imageName) {
      const url = await costumesStore.getDriveImageUrl(imageName)
      bookingImageUrls.value[booking.id] = url
    }
  }
  console.log('booking image urls: ', bookingImageUrls.value)
})

const statCards = computed(() => [
  {
    key: 'waiting',
    label: 'Waiting Approval',
    value: filteredCount('waiting_approval'),
    class: 'dot-waiting',
    index: 0,
  },
  {
    key: 'processing',
    label: 'Processing',
    value: filteredCount('processing'),
    class: 'dot-processing',
    index: 1,
  },
  {
    key: 'completed',
    label: 'Completed',
    value: filteredCount('completed'),
    class: 'dot-completed',
    index: 2,
  },
  {
    key: 'cancelled',
    label: 'Cancelled',
    value: filteredCount('cancelled'),
    class: 'dot-cancelled',
    index: 3,
  },
])

function filteredCount(status) {
  return bookingsStore.bookings.filter((b) => b.status === status).length
}
</script>

<style scoped>
.manage-page {
  background:
    radial-gradient(circle at 10% 20%, rgba(201, 168, 76, 0.08), transparent 25%),
    radial-gradient(circle at 90% 10%, rgba(114, 47, 55, 0.08), transparent 22%), var(--ivory);
  min-height: 100vh;
}
.page-header {
  background: linear-gradient(135deg, var(--charcoal-2), var(--charcoal-3));
  color: white;
  padding: 90px 0 50px;
  position: relative;
  overflow: hidden;
}
.page-header::after {
  content: '';
  position: absolute;
  inset: 0;
  background: radial-gradient(circle at 70% 20%, rgba(255, 255, 255, 0.08), transparent 40%);
  pointer-events: none;
}
.role-pill {
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 6px;
  padding: 10px 12px;
}
.header-controls .form-select {
  min-width: 180px;
}
.fancy-select {
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: white;
}
.shine {
  position: relative;
  overflow: hidden;
}
.shine::after {
  content: '';
  position: absolute;
  top: -50%;
  left: -80%;
  width: 40%;
  height: 200%;
  background: linear-gradient(
    105deg,
    transparent 30%,
    rgba(255, 255, 255, 0.35) 50%,
    transparent 70%
  );
  transform: skewX(-15deg);
  transition: left 0.6s ease;
}
.shine:hover::after {
  left: 140%;
}
.stat-card {
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.12);
  padding: 14px 16px;
  border-radius: 10px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
}
.stat-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
}
.dot-waiting {
  background: #b45309;
}
.dot-processing {
  background: #0f4f91;
}
.dot-completed {
  background: #166534;
}
.dot-cancelled {
  background: #b91c1c;
}
.status-badge {
  padding: 4px 10px;
  border-radius: 4px;
  font-size: 0.78rem;
  font-weight: 700;
  letter-spacing: 0.08em;
}
.glass-card {
  background: rgba(255, 255, 255, 0.9);
  border: 1px solid rgba(201, 168, 76, 0.25);
  border-radius: 14px;
  overflow: hidden;
}
.luxe-table thead {
  background: linear-gradient(90deg, rgba(201, 168, 76, 0.18), rgba(255, 255, 255, 0.8));
  border-bottom: 1px solid rgba(201, 168, 76, 0.2);
}
.luxe-table th {
  text-transform: uppercase;
  letter-spacing: 0.1em;
  font-size: 0.8rem;
  color: var(--charcoal-2);
}
.fade-in {
  animation: fadeIn 0.6s ease forwards;
  opacity: 0;
}
.slide-up {
  animation: slideUp 0.7s ease forwards;
}
.rise-in {
  animation: riseIn 0.7s ease forwards;
}
.float-in {
  animation: floatIn 0.7s ease forwards;
  opacity: 0;
}
.row-hover {
  transition:
    background 0.25s,
    transform 0.25s;
}
.row-hover:hover {
  background: rgba(201, 168, 76, 0.06);
  transform: translateX(4px);
}
.thumb-wrap {
  width: 64px;
  height: 64px;
  border: 1px solid rgba(201, 168, 76, 0.25);
  border-radius: 10px;
  overflow: hidden;
}
.thumb {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}
.btn-stack .btn {
  margin-left: 6px;
}
.btn-stack .btn + .btn {
  margin-top: 4px;
}

@keyframes fadeIn {
  to {
    opacity: 1;
  }
}
@keyframes slideUp {
  from {
    transform: translateY(20px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}
@keyframes riseIn {
  from {
    transform: translateY(25px) scale(0.98);
    opacity: 0;
  }
  to {
    transform: translateY(0) scale(1);
    opacity: 1;
  }
}
@keyframes floatIn {
  from {
    transform: translateY(12px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}
</style>
