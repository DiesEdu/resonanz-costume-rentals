<template>
  <footer class="footer">
    <div class="container">
      <div class="row g-5">
        <!-- Brand -->
        <div class="col-lg-4">
          <div class="footer-brand mb-3"><i class="bi bi-gem me-2"></i>CostumeRent</div>
          <p style="color: rgba(212, 207, 202, 0.5); font-size: 0.9rem; line-height: 1.8">
            Your premier destination for haute-couture costume rentals. Making every event
            unforgettable since 2020.
          </p>
          <div class="footer-social d-flex gap-2 mt-4">
            <a href="#" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
            <a href="#" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
            <a href="#" aria-label="Twitter"><i class="bi bi-twitter-x"></i></a>
            <a href="#" aria-label="TikTok"><i class="bi bi-tiktok"></i></a>
          </div>
        </div>

        <!-- Quick Links -->
        <div class="col-lg-2 col-6">
          <h6 class="footer-heading">Navigation</h6>
          <ul class="list-unstyled d-flex flex-column gap-2">
            <li><router-link to="/">Home</router-link></li>
            <li><router-link to="/costumes">Costumes</router-link></li>
            <li><router-link to="/my-bookings">My Bookings</router-link></li>
            <li><router-link to="/about">About Us</router-link></li>
          </ul>
        </div>

        <!-- Categories -->
        <div class="col-lg-3 col-6">
          <h6 class="footer-heading">Collections</h6>
          <ul class="list-unstyled d-flex flex-column gap-2">
            <li><a href="#">Superheroes</a></li>
            <li><a href="#">Historical Figures</a></li>
            <li><a href="#">Disney & Fantasy</a></li>
            <li><a href="#">Horror & Halloween</a></li>
            <li><a href="#">Anime & Cosplay</a></li>
          </ul>
        </div>

        <!-- Contact -->
        <div class="col-lg-3">
          <h6 class="footer-heading">Contact</h6>
          <ul
            class="list-unstyled d-flex flex-column gap-2"
            style="color: rgba(212, 207, 202, 0.5); font-size: 0.88rem"
          >
            <li>
              <i class="bi bi-geo-alt me-2" style="color: var(--gold)"></i>123 Costume St, Fashion
              City
            </li>
            <li><i class="bi bi-telephone me-2" style="color: var(--gold)"></i>(555) 123-4567</li>
            <li>
              <i class="bi bi-envelope me-2" style="color: var(--gold)"></i>hello@costumerent.com
            </li>
            <li><i class="bi bi-clock me-2" style="color: var(--gold)"></i>Mon–Sat: 9AM – 8PM</li>
          </ul>
        </div>
      </div>

      <hr class="hr-gold mt-5" />

      <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
        <small style="color: rgba(212, 207, 202, 0.35); font-size: 0.75rem; letter-spacing: 0.06em">
          &copy; 2026 CostumeRent. All rights reserved.
        </small>
        <small style="color: rgba(212, 207, 202, 0.35); font-size: 0.75rem; letter-spacing: 0.06em">
          Crafted with <span style="color: var(--gold)">♦</span> Vue.js & Bootstrap
        </small>
      </div>
    </div>
  </footer>
</template>
