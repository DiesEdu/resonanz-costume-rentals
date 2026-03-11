<template>
  <div class="card costume-card h-100 shine-hover">
    <div class="position-relative overflow-hidden">
      <img :src="costume.image" class="card-img-top costume-image" :alt="costume.name" />
      <span class="category-badge position-absolute top-0 start-0 m-3">{{ costume.category }}</span>

      <!-- hover overlay -->
      <div class="card-img-hover-overlay">
        <router-link :to="`/costume/${costume.id}`" class="btn btn-light btn-sm me-2">
          <i class="bi bi-eye me-1"></i> View
        </router-link>
        <button class="btn btn-primary btn-sm" @click="$emit('book', costume)">
          <i class="bi bi-calendar-plus me-1"></i> Book
        </button>
      </div>
    </div>

    <div class="card-body d-flex flex-column">
      <div class="mb-1">
        <small class="text-warning fw-bold">
          <i class="bi bi-star-fill"></i> {{ costume.rating }}
        </small>
        <small class="text-muted ms-1">({{ costume.reviews }})</small>
      </div>

      <h5 class="card-title mb-2 fw-bold">{{ costume.name }}</h5>
      <h5>
        <span class="fst-italic">{{ costume.costume_code }}</span>
        <span class="text-muted ms-2" style="font-size: 0.7em"> - ({{ costume.amount }})</span>
      </h5>

      <p class="card-text text-muted small flex-grow-1" style="line-height: 1.6">
        {{ truncatedDescription }}
      </p>

      <div
        class="d-flex align-items-center justify-content-between mt-3 pt-3"
        style="border-top: 1px solid rgba(201, 168, 76, 0.15)"
      >
        <small class="text-muted">
          <i class="bi bi-rulers me-1" style="color: var(--gold)"></i>{{ costume.size.join(', ') }}
        </small>
        <router-link :to="`/costume/${costume.id}`" class="btn btn-outline-primary btn-sm">
          Details <i class="bi bi-arrow-right ms-1"></i>
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  costume: { type: Object, required: true },
})
defineEmits(['book'])

const truncatedDescription = computed(() =>
  props.costume.description.length > 90
    ? props.costume.description.substring(0, 90) + '…'
    : props.costume.description,
)
</script>

<style scoped>
.card-img-hover-overlay {
  position: absolute;
  inset: 0;
  background: rgba(15, 15, 26, 0.55);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.4s;
}
.costume-card:hover .card-img-hover-overlay {
  opacity: 1;
}
</style>
