<template>
  <nav class="navbar navbar-expand-lg navbar-dark" :class="{ scrolled: isScrolled }">
    <div class="container">
      <router-link class="navbar-brand d-flex align-items-center gap-2" to="/">
        <i class="bi bi-gem" style="color: var(--gold); font-size: 1.2rem"></i>
        <span>Resonanz Costume Rent</span>
      </router-link>

      <button
        class="navbar-toggler border-0"
        type="button"
        :aria-expanded="navOpen"
        @click="toggleNav"
        style="color: var(--gold)"
      >
        <i class="bi bi-list" style="font-size: 1.6rem"></i>
      </button>

      <div :class="['collapse navbar-collapse', { show: navOpen }]" id="navbarNav">
        <ul class="navbar-nav ms-auto align-items-center gap-1">
          <li class="nav-item">
            <router-link class="nav-link" to="/" active-class="active" exact @click="closeNav">
              Home
            </router-link>
          </li>
          <li class="nav-item">
            <router-link class="nav-link" to="/costumes" active-class="active" @click="closeNav">
              Costumes
            </router-link>
          </li>
          <li class="nav-item">
            <router-link class="nav-link" to="/about" active-class="active" @click="closeNav">
              About
            </router-link>
          </li>
          <li class="nav-item">
            <router-link class="nav-link" to="/my-bookings" active-class="active" @click="closeNav">
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
          <li class="nav-item">
            <router-link class="nav-link" to="/manage/bookings" active-class="active" @click="closeNav">
              Manage
            </router-link>
          </li>

          <!-- ── Auth: logged out ── -->
          <template v-if="!authStore.isLoggedIn">
            <li class="nav-item ms-lg-2">
              <router-link to="/login" class="nav-link nav-link-auth">
                <i class="bi bi-box-arrow-in-right me-1"></i>Sign In
              </router-link>
            </li>
            <li class="nav-item">
              <router-link to="/register" class="btn-nav-cta">
                <i class="bi bi-person-plus me-1"></i>Register
              </router-link>
            </li>
          </template>

          <!-- ── Auth: logged in ── -->
          <template v-else>
            <li class="nav-item ms-lg-2 user-menu" ref="userMenuRef">
              <button class="btn-user-menu" @click="userOpen = !userOpen">
                <span class="user-avatar">{{ initials }}</span>
                <span class="user-name d-none d-lg-inline">{{ authStore.userName }}</span>
                <i class="bi bi-chevron-down ms-1" style="font-size: 0.7rem"></i>
              </button>
              <div class="user-dropdown" :class="{ show: userOpen }">
                <div class="dropdown-header">
                  <span class="dropdown-name">{{ authStore.userName }}</span>
                  <span class="dropdown-email">{{ authStore.user?.email }}</span>
                </div>
                <div class="dropdown-divider"></div>
                <router-link to="/my-bookings" class="dropdown-item-link" @click="userOpen = false">
                  <i class="bi bi-calendar-check me-2"></i>My Bookings
                </router-link>
                <div class="dropdown-divider"></div>
                <button class="dropdown-item-link text-danger-soft" @click="handleLogout">
                  <i class="bi bi-box-arrow-right me-2"></i>Sign Out
                </button>
              </div>
            </li>
          </template>
        </ul>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useBookingsStore } from '@/stores/bookings'
import { useAuthStore } from '@/stores/auth'

const bookingsStore = useBookingsStore()
const authStore = useAuthStore()
const router = useRouter()

const bookingCount = computed(() => bookingsStore.bookings.length)
const isScrolled = ref(false)
const userOpen = ref(false)
const userMenuRef = ref(null)
const navOpen = ref(false)

const initials = computed(() => {
  const name = authStore.userName
  if (!name) return '?'
  return name
    .split(' ')
    .map((n) => n[0])
    .slice(0, 2)
    .join('')
    .toUpperCase()
})

function handleLogout() {
  userOpen.value = false
  authStore.logout()
  router.push('/')
}

function onClickOutside(e) {
  if (userMenuRef.value && !userMenuRef.value.contains(e.target)) {
    userOpen.value = false
  }
}

const onScroll = () => {
  isScrolled.value = window.scrollY > 60
}

const toggleNav = () => {
  navOpen.value = !navOpen.value
}
const closeNav = () => {
  navOpen.value = false
}

onMounted(() => {
  window.addEventListener('scroll', onScroll, { passive: true })
  document.addEventListener('click', onClickOutside)
})
onUnmounted(() => {
  window.removeEventListener('scroll', onScroll)
  document.removeEventListener('click', onClickOutside)
})
</script>

<style scoped>
.nav-link-auth {
  color: var(--text-light) !important;
  font-size: 0.88rem;
  opacity: 0.85;
  transition:
    opacity 0.2s,
    color 0.2s;
}
.nav-link-auth:hover {
  opacity: 1;
  color: var(--gold) !important;
}

/* ── User menu ─────────────────────────────────── */
.user-menu {
  position: relative;
}

.btn-user-menu {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  background: rgba(201, 168, 76, 0.1);
  border: 1px solid rgba(201, 168, 76, 0.25);
  border-radius: 2px;
  color: var(--cream);
  padding: 0.35rem 0.75rem;
  cursor: pointer;
  transition:
    background 0.2s,
    border-color 0.2s;
  font-size: 0.88rem;
}
.btn-user-menu:hover {
  background: rgba(201, 168, 76, 0.18);
  border-color: rgba(201, 168, 76, 0.5);
}

.user-avatar {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 26px;
  height: 26px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--gold), var(--gold-dark));
  color: var(--charcoal);
  font-size: 0.7rem;
  font-weight: 800;
  letter-spacing: 0;
  flex-shrink: 0;
}
.user-name {
  max-width: 110px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

/* ── Dropdown ──────────────────────────────────── */
.user-dropdown {
  position: absolute;
  top: calc(100% + 8px);
  right: 0;
  min-width: 220px;
  background: var(--charcoal-2);
  border: 1px solid rgba(201, 168, 76, 0.2);
  border-radius: 2px;
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
  z-index: 2000;
  opacity: 0;
  pointer-events: none;
  transform: translateY(-8px);
  transition:
    opacity 0.2s,
    transform 0.2s;
}
.user-dropdown.show {
  opacity: 1;
  pointer-events: all;
  transform: translateY(0);
}

.dropdown-header {
  padding: 0.8rem 1rem 0.6rem;
}
.dropdown-name {
  display: block;
  color: var(--cream);
  font-weight: 600;
  font-size: 0.9rem;
}
.dropdown-email {
  display: block;
  color: var(--text-muted);
  font-size: 0.78rem;
  margin-top: 0.1rem;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.dropdown-divider {
  height: 1px;
  background: rgba(201, 168, 76, 0.1);
  margin: 0;
}

.dropdown-item-link {
  display: flex;
  align-items: center;
  width: 100%;
  padding: 0.6rem 1rem;
  color: var(--text-light);
  font-size: 0.88rem;
  background: none;
  border: none;
  cursor: pointer;
  text-decoration: none;
  transition:
    background 0.15s,
    color 0.15s;
  text-align: left;
}
.dropdown-item-link:hover {
  background: rgba(201, 168, 76, 0.08);
  color: var(--gold);
}
.text-danger-soft {
  color: #f8adb3 !important;
}
.text-danger-soft:hover {
  background: rgba(192, 57, 43, 0.12) !important;
  color: #ff8a8a !important;
}

/* Smooth mobile collapse animation */
@media (max-width: 991.98px) {
  .navbar-collapse {
    max-height: 0;
    opacity: 0;
    overflow: hidden;
    transition:
      max-height 0.3s ease,
      opacity 0.25s ease;
  }
  .navbar-collapse.show {
    max-height: 500px; /* ample for menu content */
    opacity: 1;
  }
}
</style>
