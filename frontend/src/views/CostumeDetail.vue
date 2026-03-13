<template>
  <div v-if="costume" class="costume-detail-page">
    <!-- header bar -->
    <div class="detail-header">
      <div class="container">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0" style="font-size: 0.8rem; letter-spacing: 0.08em">
            <li class="breadcrumb-item">
              <router-link to="/" style="color: var(--gold)">Home</router-link>
            </li>
            <li class="breadcrumb-item">
              <router-link to="/costumes" style="color: var(--gold)">Costumes</router-link>
            </li>
            <li class="breadcrumb-item active text-white-50">{{ costume.name }}</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="container py-5 mt-3">
      <div class="row g-5">
        <!-- Image -->
        <div class="col-lg-6">
          <div class="detail-image-wrapper position-sticky" style="top: 90px">
            <LazyDriveImage v-if="imageUrl" :fileId="imageUrl" :alt="costume.name" />
            <div v-else class="image-fallback">
              <div class="image-fallback__badge">No image</div>
            </div>
            <div class="detail-image-corners" aria-hidden="true">
              <span class="dc dc-tl"></span>
              <span class="dc dc-tr"></span>
              <span class="dc dc-bl"></span>
              <span class="dc dc-br"></span>
            </div>
          </div>
        </div>

        <!-- Details -->
        <div class="col-lg-6">
          <span class="category-badge">{{ costume.group_category }}</span>

          <h1
            class="fw-bold mt-3 mb-3"
            style="font-family: 'Playfair Display', serif; font-size: clamp(1.8rem, 4vw, 3rem)"
          >
            {{ costume.name }}
          </h1>

          <h3>
            <span class="fst-italic">{{ costume.costume_code }}</span>
            <span class="text-muted ms-2" style="font-size: 0.7em">
              - ({{ costume.quantity }}) Availability</span
            >
          </h3>

          <hr class="hr-gold" style="margin: 1.5rem 0" />

          <p
            class="mb-4"
            style="
              font-family: 'Cormorant Garamond', serif;
              font-size: 1.15rem;
              line-height: 1.8;
              color: #444;
            "
          >
            {{ costume.description }}
          </p>

          <!-- Sizes -->
          <div class="mb-4">
            <p class="section-eyebrow mb-3">Available Sizes</p>
            <div class="d-flex gap-2 flex-wrap">
              <span class="size-chip">{{ costume.size }}</span>
            </div>
          </div>

          <!-- Actions -->
          <div class="d-grid gap-3 mb-5">
            <button class="btn btn-primary btn-lg shine-hover" @click="openBooking">
              <i class="bi bi-calendar-plus me-2"></i>Reserve This Costume
            </button>
            <router-link to="/costumes" class="btn btn-outline-primary btn-lg">
              <i class="bi bi-arrow-left me-2"></i>Back to Collection
            </router-link>
          </div>

          <!-- Assurances -->
          <div class="assurance-grid">
            <div v-for="a in assurances" :key="a.text" class="assurance-item">
              <i :class="`bi ${a.icon}`" style="color: var(--gold); font-size: 1.1rem"></i>
              <span>{{ a.text }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <BookingModal ref="bookingModal" :costume="costume" @booked="onBooked" />
  </div>

  <div v-else class="container py-5 text-center empty-state">
    <i class="bi bi-exclamation-diamond"></i>
    <h3 class="mt-3 fw-bold" style="font-family: 'Playfair Display', serif">Costume Not Found</h3>
    <router-link to="/costumes" class="btn btn-primary mt-4">Browse Our Collection</router-link>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import LazyDriveImage from '@/components/LazyDriveImage.vue'
import BookingModal from '@/components/BookingModal.vue'
import { useCostumesStore } from '@/stores/costumes'
import { useBookingsStore } from '@/stores/bookings'

const route = useRoute()
const costumesStore = useCostumesStore()
const bookingsStore = useBookingsStore()
const bookingModal = ref(null)
const costume = ref(null)
const imageUrl = ref(null)

onMounted(async () => {
  costume.value = await costumesStore.getCostumeById(route.params.id)
  imageUrl.value = await costumesStore.getDriveImageUrl(costume.value.image)
})

const assurances = [
  { icon: 'bi-shield-check', text: 'Professionally cleaned & inspected' },
  { icon: 'bi-scissors', text: 'Free alterations included' },
  { icon: 'bi-headset', text: '24/7 customer support' },
  { icon: 'bi-arrow-repeat', text: 'Easy no-fuss returns' },
]

const openBooking = () => bookingModal.value.show()
const onBooked = () => bookingsStore.showBookingToast()
</script>

<style scoped>
.costume-detail-page {
  background: var(--ivory);
  min-height: 100vh;
}

.detail-header {
  background: var(--charcoal-2);
  padding: 16px 0;
  border-bottom: 1px solid rgba(201, 168, 76, 0.15);
}

.detail-image-wrapper {
  position: relative;
}
.detail-image-corners {
  position: absolute;
  inset: 0;
  pointer-events: none;
}
.dc {
  position: absolute;
  width: 24px;
  height: 24px;
  border-color: var(--gold);
  border-style: solid;
  opacity: 0.6;
}
.dc-tl {
  top: 0;
  left: 0;
  border-width: 2px 0 0 2px;
}
.dc-tr {
  top: 0;
  right: 0;
  border-width: 2px 2px 0 0;
}
.dc-bl {
  bottom: 0;
  left: 0;
  border-width: 0 0 2px 2px;
}
.dc-br {
  bottom: 0;
  right: 0;
  border-width: 0 2px 2px 0;
}

.size-chip {
  padding: 8px 18px;
  border: 1px solid rgba(201, 168, 76, 0.4);
  font-size: 0.82rem;
  font-weight: 700;
  letter-spacing: 0.1em;
  color: var(--charcoal-2);
  cursor: default;
  transition: all 0.25s;
}
.size-chip:hover {
  background: var(--gold);
  border-color: var(--gold);
  color: var(--charcoal);
}

.assurance-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 14px;
}
.assurance-item {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 0.88rem;
  padding: 12px;
  border: 1px solid rgba(201, 168, 76, 0.12);
  background: rgba(201, 168, 76, 0.03);
}

.image-fallback {
  height: 620px;
  display: grid;
  place-items: center;
  background: repeating-linear-gradient(
    45deg,
    rgba(0, 0, 0, 0.03),
    rgba(0, 0, 0, 0.03) 10px,
    rgba(0, 0, 0, 0.06) 10px,
    rgba(0, 0, 0, 0.06) 20px
  );
  border: 1px dashed rgba(201, 168, 76, 0.35);
  color: #8a7a52;
  text-transform: uppercase;
  letter-spacing: 0.2em;
  font-size: 0.85rem;
}
.image-fallback__badge {
  padding: 10px 16px;
  border: 1px solid rgba(201, 168, 76, 0.5);
  background: rgba(255, 255, 255, 0.85);
}
</style>
