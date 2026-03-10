<template>
  <div id="app">
    <Navbar />
    <main>
      <router-view />
    </main>
    <Footer />
  </div>
</template>

<script setup>
import Navbar from './components/Navbar.vue'
import Footer from './components/Footer.vue'
</script>

<style>
/* Make navbar fixed so it overlays hero */
.navbar {
  position: fixed !important;
  top: 0;
  left: 0;
  right: 0;
  z-index: 1030;
}

/* Offset non-hero pages */
main > *:not(:first-child) {
  padding-top: 0;
}

/* For pages that don't start with a full-height hero,
   add top padding to compensate for fixed navbar */
.py-5:first-child,
.py-section:first-child,
.about-hero,
.detail-header,
.costume-detail-page > *:first-child {
  padding-top: 90px !important;
}
</style>
