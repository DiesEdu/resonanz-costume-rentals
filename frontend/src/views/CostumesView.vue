<template>
  <div class="costumes-page">
    <!-- Page Header -->
    <div class="page-header">
      <div class="container text-center">
        <p class="section-eyebrow">Discover & Reserve</p>
        <h1
          class="display-4 fw-bold text-white mt-2"
          style="font-family: 'Playfair Display', serif"
        >
          Our <em style="color: var(--gold)">Collection</em>
        </h1>
        <div class="gold-divider mt-3 mb-4"></div>
        <p
          style="
            color: rgba(255, 255, 255, 0.6);
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.15rem;
          "
        >
          Handpicked costumes for every occasion and theme
        </p>
      </div>
    </div>

    <div class="container py-5">
      <!-- Search + Filter bar -->
      <div class="filter-bar reveal">
        <div class="row g-3 align-items-center">
          <div class="col-md-5">
            <div class="luxury-search">
              <i class="bi bi-search"></i>
              <input type="text" placeholder="Search costumes, categories…" v-model="searchQuery" />
            </div>
          </div>
          <div class="col-md-7">
            <CategoryFilter :categories="costumesStore.categories" v-model="selectedCategory" />
          </div>
        </div>
      </div>

      <!-- Results count -->
      <div class="d-flex justify-content-between align-items-center my-4 reveal">
        <p class="text-muted mb-0" style="font-size: 0.85rem; letter-spacing: 0.08em">
          Showing <strong style="color: var(--gold)">{{ totalItems }}</strong> costumes
        </p>
        <div class="d-flex gap-2 align-items-center">
          <button
            v-if="selectedCategory !== 'All' || searchQuery"
            class="btn btn-outline-primary btn-sm"
            @click="resetFilters"
          >
            <i class="bi bi-x-circle me-1"></i> Clear Filters
          </button>
          <router-link v-if="canAddCostume" to="/costumes/add" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> Add Costume
          </router-link>
        </div>
      </div>

      <!-- Grid -->
      <div v-if="paginatedCostumes.length > 0" class="row g-4">
        <div
          v-for="(costume, i) in paginatedCostumes"
          :key="costume.id"
          class="col-md-6 col-lg-4 col-xl-3 reveal"
          :style="{ transitionDelay: (i % 4) * 0.1 + 's' }"
        >
          <CostumeCard :costume="costume" @book="openBooking" />
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="empty-state mt-5">
        <i class="bi bi-search"></i>
        <h4 class="fw-bold mt-3" style="font-family: 'Playfair Display', serif">
          No costumes found
        </h4>
        <p class="text-muted">Try adjusting your search or category filter</p>
        <button class="btn btn-primary mt-3" @click="resetFilters">Clear Filters</button>
      </div>

      <!-- Pagination -->
      <div v-if="totalPages > 1" class="d-flex justify-content-center mt-4">
        <nav class="pagination-nav">
          <button class="page-btn" :disabled="currentPage === 1" @click="goToPage(currentPage - 1)">
            Prev
          </button>
          <button
            v-for="page in visiblePages"
            :key="page"
            class="page-btn"
            :class="{ active: page === currentPage }"
            @click="goToPage(page)"
          >
            {{ page }}
          </button>
          <button
            class="page-btn"
            :disabled="currentPage === totalPages"
            @click="goToPage(currentPage + 1)"
          >
            Next
          </button>
        </nav>
      </div>
    </div>

    <BookingModal ref="bookingModal" :costume="selectedCostume" @booked="onBooked" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue'
import CostumeCard from '@/components/CostumeCard.vue'
import CategoryFilter from '@/components/CategoryFilter.vue'
import BookingModal from '@/components/BookingModal.vue'
import { useCostumesStore } from '@/stores/costumes'
import { useBookingsStore } from '@/stores/bookings'
import { useAuthStore } from '@/stores/auth'

const costumesStore = useCostumesStore()
const bookingsStore = useBookingsStore()
const authStore = useAuthStore()
const bookingModal = ref(null)
const selectedCostume = ref(null)
const selectedCategory = ref('All')
const searchQuery = ref('')
const currentPage = ref(1)
const pageSize = 8
const canAddCostume = computed(() => ['costume_management', 'admin'].includes(authStore.role))

const totalItems = computed(
  () => costumesStore.pagination.total ?? costumesStore.costumes.length ?? 0,
)
const totalPages = computed(() => Math.max(costumesStore.pagination.total_pages ?? 1, 1))
const visiblePages = computed(() => {
  const total = totalPages.value
  const current = currentPage.value
  const maxButtons = 5
  const half = Math.floor(maxButtons / 2)

  let start = Math.max(1, current - half)
  let end = Math.min(total, current + half)

  const visibleCount = end - start + 1
  if (visibleCount < maxButtons) {
    const remaining = maxButtons - visibleCount
    if (start === 1) end = Math.min(total, end + remaining)
    else start = Math.max(1, start - remaining)
  }

  const pages = []
  for (let p = start; p <= end; p += 1) pages.push(p)
  return pages
})
const paginatedCostumes = computed(() => costumesStore.costumes)

const loadCostumes = async (page = 1) => {
  await costumesStore.fetchCostumes({
    category: selectedCategory.value,
    search: searchQuery.value,
    page,
    perPage: pageSize,
  })
  currentPage.value = costumesStore.pagination.current_page ?? page
}

// Fetch costumes & categories from API
onMounted(async () => {
  await loadCostumes(currentPage.value)
})

const goToPage = async (page) => {
  const target = Math.min(Math.max(page, 1), totalPages.value)
  await loadCostumes(target)
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

const resetFilters = () => {
  selectedCategory.value = 'All'
  searchQuery.value = ''
  currentPage.value = 1
}
const openBooking = (costume) => {
  selectedCostume.value = costume
  bookingModal.value.show()
}
const onBooked = () => bookingsStore.showBookingToast()

// Animate elements with the "reveal" helper, including ones rendered after data loads
let revealObserver
const observeRevealElements = () => {
  if (!revealObserver) {
    revealObserver = new IntersectionObserver(
      (entries) =>
        entries.forEach((e) => {
          if (e.isIntersecting) e.target.classList.add('visible')
        }),
      { threshold: 0.08 },
    )
  }
  document.querySelectorAll('.reveal').forEach((el) => revealObserver.observe(el))
}

onMounted(() => {
  observeRevealElements()
  // Re-run observer when the grid updates so new cards become visible
  watch(
    () => costumesStore.costumes,
    async () => {
      await nextTick()
      observeRevealElements()
    },
    { flush: 'post', deep: true },
  )
  // Also when switching pages so new page cards animate in
  watch(
    paginatedCostumes,
    async () => {
      await nextTick()
      observeRevealElements()
    },
    { flush: 'post' },
  )
})

watch([selectedCategory, searchQuery], async () => {
  await loadCostumes(1)
})
</script>

<style scoped>
.costumes-page {
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
  position: relative;
  overflow: hidden;
}
.page-header::before {
  content: '';
  position: absolute;
  top: -30%;
  right: -8%;
  width: 500px;
  height: 500px;
  background: radial-gradient(circle, rgba(201, 168, 76, 0.1) 0%, transparent 70%);
  border-radius: 50%;
}

.filter-bar {
  background: white;
  border: 1px solid rgba(201, 168, 76, 0.18);
  padding: 20px 24px;
}

.luxury-search {
  display: flex;
  align-items: center;
  gap: 12px;
  border: 1px solid rgba(201, 168, 76, 0.3);
  padding: 10px 16px;
  background: var(--ivory);
  transition: border-color 0.3s;
}
.luxury-search:focus-within {
  border-color: var(--gold);
}
.luxury-search i {
  color: var(--gold);
  font-size: 0.9rem;
}
.luxury-search input {
  border: none;
  background: transparent;
  outline: none;
  flex: 1;
  font-family: 'Lato', sans-serif;
  font-size: 0.9rem;
  color: var(--charcoal-2);
}
.luxury-search input::placeholder {
  color: var(--text-muted);
}

.pagination-nav {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  background: #fff;
  border: 1px solid rgba(201, 168, 76, 0.2);
  border-radius: 999px;
  padding: 0.35rem 0.6rem;
  box-shadow: 0 8px 26px rgba(15, 15, 26, 0.08);
}
.page-btn {
  min-width: 36px;
  height: 36px;
  border-radius: 50%;
  border: 1px solid transparent;
  background: transparent;
  color: var(--charcoal-2);
  font-weight: 700;
  letter-spacing: 0.02em;
  transition: all 0.18s ease;
}
.page-btn:hover:not(:disabled) {
  border-color: rgba(201, 168, 76, 0.6);
  color: var(--charcoal);
}
.page-btn.active {
  background: linear-gradient(135deg, var(--charcoal), var(--charcoal-2));
  color: var(--gold);
  border-color: var(--charcoal-2);
  box-shadow: 0 6px 14px rgba(15, 15, 26, 0.18);
}
.page-btn:disabled {
  opacity: 0.45;
  cursor: not-allowed;
}
</style>

