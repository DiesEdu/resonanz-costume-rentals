<template>
  <nav class="navbar navbar-expand-lg navbar-dark" :class="{ scrolled: isScrolled }">
    <div class="container">
      <router-link class="navbar-brand d-flex align-items-center gap-2" to="/">
        <i class="bi bi-gem" style="color: var(--gold); font-size: 1.2rem"></i>
        <span>CostumeRent</span>
      </router-link>

      <button
        class="navbar-toggler border-0"
        type="button"
        data-bs-toggle="collapse"
        data-bs-target="#navbarNav"
        style="color: var(--gold)"
      >
        <i class="bi bi-list" style="font-size: 1.6rem"></i>
      </button>

      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto align-items-center gap-1">
          <li class="nav-item">
            <router-link class="nav-link" to="/" active-class="active" exact>Home</router-link>
          </li>
          <li class="nav-item">
            <router-link class="nav-link" to="/costumes" active-class="active"
              >Costumes</router-link
            >
          </li>
          <li class="nav-item">
            <router-link class="nav-link" to="/about" active-class="active">About</router-link>
          </li>
          <li class="nav-item">
            <router-link class="nav-link" to="/my-bookings" active-class="active">
              My Bookings
              <span
                v-if="bookingCount > 0"
                class="badge ms-1"
                style="
                  background: var(--gold);
                  color: var(--charcoal);
                  font-size: 0.65rem;
                  border-radius: 0;
                "
                >{{ bookingCount }}</span
              >
            </router-link>
          </li>
          <li class="nav-item ms-lg-3">
            <router-link to="/costumes" class="btn-nav-cta">
              <i class="bi bi-search me-1"></i> Browse
            </router-link>
          </li>
        </ul>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { useBookingsStore } from '@/stores/bookings'

const bookingsStore = useBookingsStore()
const bookingCount = computed(() => bookingsStore.bookings.length)

const isScrolled = ref(false)

const onScroll = () => {
  isScrolled.value = window.scrollY > 60
}

onMounted(() => window.addEventListener('scroll', onScroll, { passive: true }))
onUnmounted(() => window.removeEventListener('scroll', onScroll))
</script>
