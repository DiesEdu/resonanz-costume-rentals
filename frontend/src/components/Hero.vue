<template>
  <section class="hero-section">
    <!-- Particle sparkles -->
    <div class="hero-particles" aria-hidden="true">
      <span
        v-for="p in particles"
        :key="p.id"
        :style="{ left: p.x + '%', top: p.y + '%', '--d': p.d + 's', '--delay': p.delay + 's' }"
      ></span>
    </div>

    <!-- Decorative gold lines -->
    <div class="hero-lines" aria-hidden="true">
      <div class="line-v line-v-1"></div>
      <div class="line-v line-v-2"></div>
    </div>

    <div class="container position-relative" style="z-index: 2">
      <div class="row align-items-center g-5">
        <!-- Left copy -->
        <div class="col-lg-6">
          <p class="section-eyebrow mb-3 reveal">✦ Premium Collection 2026</p>

          <h1 class="hero-title mb-4 reveal reveal-delay-1">
            Dress for the<br />
            <em class="gold-text">Extraordinary</em>
          </h1>

          <p class="hero-subtitle mb-5 reveal reveal-delay-2">
            Curated haute-couture costumes for every occasion — from grand galas to theatrical
            stages. Delivered to your door.
          </p>

          <div class="d-flex gap-3 flex-wrap reveal reveal-delay-3">
            <router-link to="/costumes" class="btn btn-light shine-hover">
              <i class="bi bi-collection me-2"></i>Explore Collection
            </router-link>
            <button class="btn btn-outline-light" @click="scrollToHow">
              <i class="bi bi-play-circle me-2"></i>How It Works
            </button>
          </div>

          <!-- Stats row -->
          <div class="mt-5 d-flex gap-5 reveal reveal-delay-4">
            <div>
              <div class="hero-stat-value">500+</div>
              <div class="hero-stat-label">Costumes</div>
            </div>
            <div class="stat-divider" aria-hidden="true"></div>
            <div>
              <div class="hero-stat-value">4.9★</div>
              <div class="hero-stat-label">Avg Rating</div>
            </div>
          </div>
        </div>

        <!-- Right image -->
        <div class="col-lg-6 d-none d-lg-flex justify-content-center">
          <div class="hero-image-wrapper float-anim">
            <img
              src="https://lh3.googleusercontent.com/d/1eTCuf4xBxmbgMgIkr53o4XVYBsbWwKNL=w1200?authuser=0"
              alt="Featured Costume"
              class="img-fluid"
              style="max-height: 580px; width: 100%; object-fit: cover"
            />
            <div class="hero-badge">
              <div class="badge-num">★ 4.9</div>
              <div class="badge-text">Top Rated</div>
            </div>
            <!-- corner decorations -->
            <span class="hero-corner hero-corner-tl"></span>
            <span class="hero-corner hero-corner-br"></span>
          </div>
        </div>
      </div>
    </div>

    <!-- bottom scroll indicator -->
    <div class="hero-scroll-hint">
      <span class="scroll-line"></span>
      <small class="section-eyebrow">Scroll</small>
    </div>
  </section>
</template>

<script setup>
import { onMounted } from 'vue'

// Generate random particles
const particles = Array.from({ length: 28 }, (_, i) => ({
  id: i,
  x: Math.random() * 100,
  y: Math.random() * 100,
  d: (3 + Math.random() * 5).toFixed(1),
  delay: (Math.random() * 6).toFixed(1),
}))

const scrollToHow = () => {
  document.getElementById('how-it-works')?.scrollIntoView({ behavior: 'smooth' })
}

onMounted(() => {
  // trigger initial reveals (elements already in viewport)
  document.querySelectorAll('.reveal').forEach((el) => {
    el.classList.add('visible')
  })
})
</script>

<style scoped>
.hero-lines {
  position: absolute;
  inset: 0;
  pointer-events: none;
  z-index: 1;
}
.line-v {
  position: absolute;
  top: 0;
  bottom: 0;
  width: 1px;
  background: rgba(201, 168, 76, 0.08);
}
.line-v-1 {
  left: 25%;
}
.line-v-2 {
  right: 25%;
}

.stat-divider {
  width: 1px;
  background: rgba(201, 168, 76, 0.3);
  align-self: stretch;
}

.hero-corner {
  position: absolute;
  width: 30px;
  height: 30px;
  border-color: var(--gold);
  border-style: solid;
}
.hero-corner-tl {
  top: -12px;
  left: -12px;
  border-width: 2px 0 0 2px;
}
.hero-corner-br {
  bottom: -12px;
  right: -12px;
  border-width: 0 2px 2px 0;
}

.hero-scroll-hint {
  position: absolute;
  bottom: 40px;
  left: 50%;
  transform: translateX(-50%);
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  z-index: 2;
}
.scroll-line {
  display: block;
  width: 1px;
  height: 40px;
  background: linear-gradient(to bottom, var(--gold), transparent);
  animation: scroll-pulse 2s ease-in-out infinite;
}
@keyframes scroll-pulse {
  0%,
  100% {
    opacity: 0.3;
    transform: scaleY(0.5);
    transform-origin: top;
  }
  50% {
    opacity: 1;
    transform: scaleY(1);
  }
}
</style>
