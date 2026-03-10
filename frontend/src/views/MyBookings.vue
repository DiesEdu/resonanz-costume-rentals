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
                <img
                  :src="booking.costumeImage"
                  :alt="booking.costumeName"
                  style="width: 90px; height: 90px; object-fit: cover; display: block"
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
                {{ booking.size }}
              </p>
              <p class="text-muted mb-0" style="font-size: 0.85rem">
                <i class="bi bi-calendar3 me-1" style="color: var(--gold)"></i>
                {{ formatDate(booking.startDate) }} – {{ formatDate(booking.endDate) }}
              </p>
            </div>

            <!-- Price + date -->
            <div class="col-md-3">
              <div
                style="
                  font-family: 'Playfair Display', serif;
                  font-size: 1.4rem;
                  font-weight: 700;
                  color: var(--gold);
                "
              >
                ${{ booking.totalPrice }}
              </div>
              <p class="text-muted mb-0" style="font-size: 0.78rem; letter-spacing: 0.06em">
                Booked {{ formatDate(booking.bookingDate) }}
              </p>
            </div>

            <!-- Status + Action -->
            <div class="col-md-3 text-md-end">
              <span class="status-badge mb-3 d-inline-block" :class="`status-${booking.status}`">
                {{ booking.status.charAt(0).toUpperCase() + booking.status.slice(1) }}
              </span>
              <div v-if="booking.status === 'pending' || booking.status === 'confirmed'">
                <button class="btn btn-outline-danger btn-sm" @click="cancelBooking(booking.id)">
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
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useBookingsStore } from '@/stores/bookings'

const bookingsStore = useBookingsStore()
const bookings = computed(() => bookingsStore.getUserBookings())

const formatDate = (dateString) =>
  new Date(dateString).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  })

const cancelBooking = async (id) => {
  if (confirm('Are you sure you want to cancel this booking?')) {
    await bookingsStore.cancelBooking(id)
  }
}

onMounted(async () => {
  await bookingsStore.fetchBookings()

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
</style>
