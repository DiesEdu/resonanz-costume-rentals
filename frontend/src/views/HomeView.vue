<template>
  <div>
    <Hero />

    <!-- ── How It Works ──────────────────────────── -->
    <section class="py-section" id="how-it-works" style="background: var(--ivory)">
      <div class="container">
        <div class="text-center mb-5 reveal">
          <p class="section-eyebrow">Simple Process</p>
          <h2 class="section-title mt-2">How It Works</h2>
          <div class="gold-divider"></div>
        </div>

        <div class="row g-5">
          <div
            v-for="(step, i) in steps"
            :key="step.title"
            class="col-md-4 text-center reveal"
            :class="`reveal-delay-${i + 1}`"
          >
            <div class="feature-icon mx-auto mb-4">
              <i :class="`bi ${step.icon}`"></i>
            </div>
            <div class="step-number mb-2">0{{ i + 1 }}</div>
            <h4 class="fw-bold mb-2">{{ step.title }}</h4>
            <p class="text-muted">{{ step.desc }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- ── Featured Costumes ─────────────────────── -->
    <section class="py-section" style="background: var(--cream-dark)">
      <div class="container">
        <div
          class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-5 gap-3 reveal"
        >
          <div>
            <p class="section-eyebrow">Curated Selection</p>
            <h2 class="section-title mt-2 mb-0">Featured Costumes</h2>
            <div class="gold-divider" style="margin-left: 0"></div>
          </div>
          <router-link to="/costumes" class="btn btn-outline-primary">
            View All Collection <i class="bi bi-arrow-right ms-2"></i>
          </router-link>
        </div>

        <div class="row g-4">
          <div
            v-for="(costume, i) in featuredCostumes"
            :key="costume.id"
            class="col-md-6 col-lg-3 reveal"
            :class="i < 4 ? `reveal-delay-${i + 1}` : ''"
          >
            <CostumeCard :costume="costume" @book="openBooking" />
          </div>
        </div>
      </div>
    </section>

    <!-- ── Categories ───────────────────────────── -->
    <section class="py-section" style="background: var(--ivory)">
      <div class="container">
        <div class="text-center mb-5 reveal">
          <p class="section-eyebrow">Explore by Theme</p>
          <h2 class="section-title mt-2">Popular Collections</h2>
          <div class="gold-divider"></div>
        </div>

        <div class="row g-4">
          <div
            v-for="(cat, i) in popularCategories"
            :key="cat.name"
            class="col-md-4 reveal"
            :class="`reveal-delay-${i + 1}`"
          >
            <div class="cat-card">
              <img :src="cat.image" :alt="cat.name" />
              <div class="cat-card-border"></div>
              <div class="cat-card-overlay">
                <div>
                  <p class="section-eyebrow mb-1">{{ cat.count }} pieces</p>
                  <h4 class="fw-bold text-white mb-2">{{ cat.name }}</h4>
                  <router-link to="/costumes" class="btn btn-outline-primary btn-sm">
                    Explore <i class="bi bi-arrow-right ms-1"></i>
                  </router-link>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ── Testimonial strip ─────────────────────── -->
    <section class="py-5" style="background: var(--charcoal-2)">
      <div class="container reveal">
        <div class="row align-items-center g-4">
          <div class="col-md-4 text-center text-md-start">
            <p class="section-eyebrow">What Clients Say</p>
            <h3 class="text-white fw-bold mb-0" style="font-family: 'Playfair Display', serif">
              Loved by thousands
            </h3>
          </div>
          <div class="col-md-8">
            <div class="row g-4">
              <div v-for="t in testimonials" :key="t.name" class="col-md-4">
                <div class="testimonial-card">
                  <div class="text-warning mb-2" style="font-size: 0.75rem">★★★★★</div>
                  <p
                    class="mb-3"
                    style="color: rgba(212, 207, 202, 0.75); font-size: 0.9rem; font-style: italic"
                  >
                    "{{ t.quote }}"
                  </p>
                  <div style="color: var(--gold); font-size: 0.8rem; font-weight: 700">
                    — {{ t.name }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ── CTA ──────────────────────────────────── -->
    <section class="cta-section py-section reveal">
      <div class="container text-center py-5" style="position: relative; z-index: 1">
        <p class="section-eyebrow mb-3">Ready to Transform?</p>
        <h2
          class="display-5 fw-bold text-white mb-4"
          style="font-family: 'Playfair Display', serif"
        >
          Step Into Something <em style="color: var(--gold)">Remarkable</em>
        </h2>
        <p
          class="mb-5"
          style="
            color: rgba(255, 255, 255, 0.65);
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.2rem;
          "
        >
          Join thousands of happy clients who made their events unforgettable.
        </p>
        <router-link to="/costumes" class="btn btn-light shine-hover px-5">
          Start Exploring <i class="bi bi-arrow-right ms-2"></i>
        </router-link>
      </div>
    </section>

    <BookingModal ref="bookingModal" :costume="selectedCostume" @booked="onBooked" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import Hero from '@/components/Hero.vue'
import CostumeCard from '@/components/CostumeCard.vue'
import BookingModal from '@/components/BookingModal.vue'
import { useCostumesStore } from '@/stores/costumes'

const costumesStore = useCostumesStore()
const bookingModal = ref(null)
const selectedCostume = ref(null)

const featuredCostumes = computed(() => costumesStore.costumes.slice(0, 4))

const steps = [
  {
    icon: 'bi-search',
    title: 'Browse',
    desc: 'Explore our curated collection of premium costumes across all categories and themes.',
  },
  {
    icon: 'bi-calendar-check',
    title: 'Reserve',
    desc: 'Select your dates, choose your size, and secure your costume in minutes.',
  },
  {
    icon: 'bi-bag-heart',
    title: 'Enjoy',
    desc: "Receive delivery, wear it beautifully, and return effortlessly when you're done.",
  },
]

const popularCategories = [
  {
    name: 'Superheroes',
    count: 45,
    image:
      'https://images.unsplash.com/photo-1635805737707-575885ab0820?w=500&auto=format&fit=crop&q=60',
  },
  {
    name: 'Historical',
    count: 38,
    image:
      'https://images.unsplash.com/photo-1595777457583-95e059d581b8?w=500&auto=format&fit=crop&q=60',
  },
  {
    name: 'Fantasy',
    count: 52,
    image:
      'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?w=500&auto=format&fit=crop&q=60',
  },
]

const testimonials = [
  {
    name: 'Sophia M.',
    quote: 'Absolutely exquisite quality. My gala costume turned every head in the room.',
  },
  {
    name: 'James T.',
    quote: 'Seamless experience from booking to return. Will rent again without hesitation.',
  },
  {
    name: 'Aria L.',
    quote: 'The attention to detail is outstanding. Felt like royalty all evening.',
  },
]

const openBooking = (costume) => {
  selectedCostume.value = costume
  bookingModal.value.show()
}
const onBooked = () => alert('Booking confirmed! Check your email for details.')

// Scroll-reveal observer
onMounted(() => {
  const observer = new IntersectionObserver(
    (entries) =>
      entries.forEach((e) => {
        if (e.isIntersecting) e.target.classList.add('visible')
      }),
    { threshold: 0.12 },
  )
  document.querySelectorAll('.reveal').forEach((el) => observer.observe(el))
})
</script>

<style scoped>
.py-section {
  padding: 100px 0;
}

.step-number {
  font-family: 'Playfair Display', serif;
  font-size: 2.5rem;
  font-weight: 900;
  color: rgba(201, 168, 76, 0.15);
  line-height: 1;
}

.testimonial-card {
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(201, 168, 76, 0.15);
  padding: 20px;
  transition: border-color 0.3s;
}
.testimonial-card:hover {
  border-color: rgba(201, 168, 76, 0.4);
}
</style>
