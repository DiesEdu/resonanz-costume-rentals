<template>
  <div>
    <!-- Page hero -->
    <div class="about-hero">
      <div class="container text-center">
        <p class="section-eyebrow reveal">Our Story</p>
        <h1
          class="display-3 fw-bold text-white mt-2 reveal reveal-delay-1"
          style="font-family: 'Playfair Display', serif"
        >
          About <em style="color: var(--gold)">CostumeRent</em>
        </h1>
        <div class="gold-divider reveal reveal-delay-2"></div>
        <p
          class="mt-4 reveal reveal-delay-3"
          style="
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.25rem;
            color: rgba(255, 255, 255, 0.65);
            max-width: 600px;
            margin: 1rem auto 0;
          "
        >
          Passionate about transforming moments into memories since 2020.
        </p>
      </div>
    </div>

    <div class="container py-5 mt-4">
      <!-- Values -->
      <div class="row g-5 mb-5">
        <div
          v-for="(val, i) in values"
          :key="val.title"
          class="col-md-4 text-center reveal"
          :class="`reveal-delay-${i + 1}`"
        >
          <div class="feature-icon mx-auto mb-4">
            <i :class="`bi ${val.icon}`"></i>
          </div>
          <h4 class="fw-bold mb-2">{{ val.title }}</h4>
          <p class="text-muted">{{ val.desc }}</p>
        </div>
      </div>

      <hr class="hr-gold" />

      <!-- Store info -->
      <div class="row g-0 overflow-hidden reveal" style="border: 1px solid rgba(201, 168, 76, 0.2)">
        <div class="col-md-6">
          <img
            src="https://images.unsplash.com/photo-1492684223066-81342ee5ff30?w=800&auto=format&fit=crop&q=80"
            class="img-fluid h-100 w-100"
            style="object-fit: cover; max-height: 480px"
            alt="Our boutique store"
          />
        </div>
        <div class="col-md-6 bg-white p-5 d-flex flex-column justify-content-center">
          <p class="section-eyebrow mb-2">Find Us</p>
          <h3 class="fw-bold mb-4" style="font-family: 'Playfair Display', serif">
            Visit Our Boutique
          </h3>
          <p class="text-muted mb-4">
            Come experience our collection in person. Our expert stylists will help you find the
            perfect silhouette for your event.
          </p>
          <ul class="list-unstyled d-flex flex-column gap-3">
            <li class="d-flex align-items-start gap-3">
              <i class="bi bi-geo-alt-fill fs-5" style="color: var(--gold); margin-top: 2px"></i>
              <span>123 Costume Street, Fashion City, FC 10001</span>
            </li>
            <li class="d-flex align-items-start gap-3">
              <i class="bi bi-clock-fill fs-5" style="color: var(--gold); margin-top: 2px"></i>
              <span>Monday – Saturday: 9AM – 8PM</span>
            </li>
            <li class="d-flex align-items-start gap-3">
              <i class="bi bi-telephone-fill fs-5" style="color: var(--gold); margin-top: 2px"></i>
              <span>(555) 123-4567</span>
            </li>
            <li class="d-flex align-items-start gap-3">
              <i class="bi bi-envelope-fill fs-5" style="color: var(--gold); margin-top: 2px"></i>
              <span>hello@costumerent.com</span>
            </li>
          </ul>
        </div>
      </div>

      <!-- Stats banner -->
      <div class="stats-banner mt-5 p-5 reveal">
        <div class="row g-4 text-center">
          <div v-for="stat in stats" :key="stat.label" class="col-6 col-md-3">
            <div class="stat-big">{{ stat.value }}</div>
            <div class="stat-label">{{ stat.label }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'

const values = [
  {
    icon: 'bi-heart-fill',
    title: 'Our Mission',
    desc: 'Providing high-quality, affordable costumes that help everyone express their unique creativity.',
  },
  {
    icon: 'bi-award-fill',
    title: 'Quality First',
    desc: 'Every costume is professionally cleaned, maintained, and inspected before each rental.',
  },
  {
    icon: 'bi-people-fill',
    title: 'Community',
    desc: 'Proud supporters of local theaters, schools, and global cosplay communities.',
  },
]

const stats = [
  { value: '500+', label: 'Costumes in Collection' },
  { value: '4.9★', label: 'Average Rating' },
  { value: '10k+', label: 'Happy Clients' },
  { value: '6yrs', label: 'In Business' },
]

onMounted(() => {
  const obs = new IntersectionObserver(
    (entries) =>
      entries.forEach((e) => {
        if (e.isIntersecting) e.target.classList.add('visible')
      }),
    { threshold: 0.12 },
  )
  document.querySelectorAll('.reveal').forEach((el) => obs.observe(el))
})
</script>

<style scoped>
.about-hero {
  min-height: 46vh;
  background: linear-gradient(
    145deg,
    var(--charcoal) 0%,
    var(--charcoal-2) 60%,
    var(--charcoal-3) 100%
  );
  display: flex;
  align-items: center;
  position: relative;
  overflow: hidden;
  padding: 100px 0 60px;
}
.about-hero::before {
  content: '';
  position: absolute;
  top: -20%;
  right: -10%;
  width: 500px;
  height: 500px;
  background: radial-gradient(circle, rgba(201, 168, 76, 0.1) 0%, transparent 70%);
  border-radius: 50%;
}

.stats-banner {
  background: var(--charcoal-2);
  border: 1px solid rgba(201, 168, 76, 0.18);
}
.stat-big {
  font-family: 'Playfair Display', serif;
  font-size: 2.4rem;
  font-weight: 900;
  color: var(--gold);
  line-height: 1;
  margin-bottom: 6px;
}
.stat-label {
  font-size: 0.75rem;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: rgba(212, 207, 202, 0.5);
}
</style>
