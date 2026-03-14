<template>
  <div class="bookings-page">
    <!-- Page header -->
    <div class="page-header">
      <div class="container">
        <p class="section-eyebrow">Your Rentals</p>
        <h1
          class="fw-bold text-white mt-2"
          style="font-family: 'Playfair Display', serif; font-size: clamp(2rem, 5vw, 3.5rem)"
        >
          My <em style="color: var(--gold)">Bookings</em>
        </h1>
        <div class="gold-divider" style="margin-left: 0; margin-top: 12px"></div>
      </div>
    </div>

    <div class="container py-5">
      <div v-if="bookings.length > 0">
        <div
          v-for="(booking, i) in bookings"
          :key="booking.id"
          class="booking-card reveal"
          :style="{ transitionDelay: i * 0.08 + 's' }"
        >
          <div class="row align-items-center g-4">
            <!-- Image -->
            <div class="col-auto">
              <div class="booking-img-wrap">
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
              </div>
            </div>

            <!-- Info -->
            <div class="col-md-4">
              <h5 class="fw-bold mb-1" style="font-family: 'Playfair Display', serif">
                {{ booking.costumeName }}
              </h5>
              <p class="text-muted mb-1" style="font-size: 0.85rem">
                <i class="bi bi-rulers me-1" style="color: var(--gold)"></i> Size:
                <span class="fst-italic">{{ booking.costumeSize }}</span>
              </p>
              <p class="text-muted mb-0" style="font-size: 0.85rem">
                <i class="bi bi-calendar3 me-1" style="color: var(--gold)"></i>
                {{ formatDate(booking.startDate) }} – {{ formatDate(booking.endDate) }}
              </p>
            </div>

            <!-- Date -->
            <div class="col-md-3">
              <p class="text-muted mb-0" style="font-size: 0.78rem; letter-spacing: 0.06em">
                Booked {{ formatDate(booking.bookingDate) }}
              </p>
            </div>

            <!-- Status + Action -->
            <div class="col-md-3 text-md-end">
              <span class="status-badge mb-3 d-inline-block" :class="`status-${booking.status}`">
                {{ formatStatus(booking.status) }}
              </span>
              <div v-if="['waiting_approval', 'processing'].includes(booking.status)">
                <button class="btn btn-outline-danger btn-sm" @click="openCancelModal(booking.id)">
                  <i class="bi bi-x-circle me-1"></i>Cancel
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty -->
      <div v-else class="empty-state mt-4 reveal">
        <i class="bi bi-calendar-x"></i>
        <h3 class="fw-bold mt-3" style="font-family: 'Playfair Display', serif">No bookings yet</h3>
        <p class="text-muted mb-4">
          You haven't reserved any costumes. Start exploring our collection!
        </p>
        <router-link to="/costumes" class="btn btn-primary px-5">
          Browse Collection <i class="bi bi-arrow-right ms-2"></i>
        </router-link>
      </div>
    </div>

    <!-- Cancel confirmation modal -->
    <div v-if="cancelModal.open" class="modal-backdrop show" @click.self="closeCancelModal">
      <div class="modal-card show">
        <div class="modal-header">
          <h5 class="fw-bold mb-0">Cancel Booking</h5>
          <button class="btn btn-link text-muted p-0" @click="closeCancelModal">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>
        <p class="text-muted mb-3">
          Are you sure you want to cancel this booking? This action cannot be undone.
        </p>
        <div class="d-flex justify-content-end gap-2">
          <button class="btn btn-outline-secondary btn-sm" @click="closeCancelModal">
            Keep Booking
          </button>
          <button class="btn btn-danger btn-sm" @click="confirmCancel">Cancel Booking</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { useBookingsStore } from '@/stores/bookings'
import { useCostumesStore } from '@/stores/costumes'
import LazyDriveImage from '@/components/LazyDriveImage.vue'

const bookingsStore = useBookingsStore()
const costumesStore = useCostumesStore()
const bookings = computed(() => bookingsStore.getUserBookings())
const cancelModal = reactive({ open: false, bookingId: null })

// Map to store image URLs for each booking
const bookingImageUrls = ref({})

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

const openCancelModal = (id) => {
  cancelModal.open = true
  cancelModal.bookingId = id
}

const closeCancelModal = () => {
  cancelModal.open = false
  cancelModal.bookingId = null
}

const confirmCancel = async () => {
  if (!cancelModal.bookingId) return
  await bookingsStore.cancelBooking(cancelModal.bookingId)
  closeCancelModal()
}

onMounted(async () => {
  await bookingsStore.fetchBookings()

  // Load image URLs for all bookings
  const loadedBookings = bookingsStore.getUserBookings()
  for (const booking of loadedBookings) {
    const imageName = booking.costumeImage || booking.costumeName
    if (imageName) {
      const url = await costumesStore.getDriveImageUrl(imageName)
      bookingImageUrls.value[booking.id] = url
    }
  }

  const obs = new IntersectionObserver(
    (entries) =>
      entries.forEach((e) => {
        if (e.isIntersecting) e.target.classList.add('visible')
      }),
    { threshold: 0.1 },
  )
  document.querySelectorAll('.reveal').forEach((el) => obs.observe(el))
})
</script>

<style scoped>
.bookings-page {
  background: var(--ivory);
  min-height: 100vh;
}

.page-header {
  background: linear-gradient(
    145deg,
    var(--charcoal) 0%,
    var(--charcoal-2) 60%,
    var(--charcoal-3) 100%
  );
  padding: 120px 0 60px;
}

.booking-img-wrap {
  border: 1px solid rgba(201, 168, 76, 0.25);
  overflow: hidden;
}

.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.55);
  display: grid;
  place-items: center;
  padding: 1rem;
  z-index: 1050;
  opacity: 0;
  animation: fadeIn 250ms ease forwards;
}

.modal-card {
  background: #fff;
  border-radius: 14px;
  padding: 20px 24px;
  max-width: 420px;
  width: 100%;
  box-shadow: 0 24px 70px rgba(0, 0, 0, 0.28);
  transform: translateY(12px) scale(0.96);
  opacity: 0;
  animation: floatUp 320ms cubic-bezier(0.22, 1, 0.36, 1) forwards;
}

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 8px;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

@keyframes floatUp {
  0% {
    transform: translateY(12px) scale(0.96);
    opacity: 0;
  }
  65% {
    transform: translateY(-6px) scale(1.02);
    opacity: 1;
  }
  100% {
    transform: translateY(0) scale(1);
    opacity: 1;
  }
}
</style>
