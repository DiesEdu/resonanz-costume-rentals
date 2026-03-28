<template>
  <div class="card costume-card h-100 shine-hover">
    <div class="position-relative overflow-hidden">
      <LazyDriveImage v-if="imageUrl" :fileId="imageUrl" :alt="costume.name" />
      <span class="category-badge position-absolute top-0 start-0 m-3">{{
        costume.group_category
      }}</span>

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
      <h5 class="card-title mb-2 fw-bold">{{ costume.name }}</h5>
      <h5>
        <span class="fst-italic">{{ costume.costume_code }}</span>
      </h5>
      <small class="text-muted">
        <i class="bi bi-rulers me-1" style="color: var(--gold)"> </i>
        <div class="wrap-size">
          <span v-for="size in costume.sizes" :key="size" class="size-chip"
            ><i :class="['me-1', genderIcon(size.gender)]"></i> {{ size.size }} ({{
              size.quantity
            }})</span
          >
        </div>
      </small>

      <div
        class="d-flex align-items-center justify-content-between mt-3 pt-3"
        style="border-top: 1px solid rgba(201, 168, 76, 0.15)"
      >
        <router-link :to="`/costume/${costume.id}`" class="btn btn-outline-primary btn-sm">
          Details <i class="bi bi-arrow-right ms-1"></i>
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import LazyDriveImage from './LazyDriveImage.vue'

const props = defineProps({
  costume: { type: Object, required: true },
})

defineEmits(['book'])

const imageUrl = ref(null)

function genderIcon(gender) {
  const g = (gender || 'unisex').toLowerCase()
  if (g === 'male') return 'bi bi-gender-male'
  if (g === 'female') return 'bi bi-gender-female'
  return 'bi bi-people'
}

onMounted(async () => {
  imageUrl.value = `https://drive.google.com/thumbnail?id=${props.costume.image}&sz=w1200`
})
</script>

<style scoped>
.text-muted {
  display: flex;
  flex-direction: row;
  justify-content: start;
  align-items: center;
}

.wrap-size {
  display: flex;
  flex-wrap: wrap; /* 🔥 THIS is the key */
  justify-content: start;
  align-items: center;
  gap: 6px; /* optional spacing between chips */
}

.size-chip {
  margin: 0 5px;
  padding: 4px 8px;
  border: 1px solid rgba(201, 168, 76, 0.4);
  border-radius: 6px;
  font-size: 0.82rem;
  font-weight: 700;
  color: var(--charcoal-2);
  cursor: default;
  white-space: nowrap;
  transition: all 0.25s;
}
.size-chip:hover {
  background: var(--gold);
  border-color: var(--gold);
  color: var(--charcoal);
}

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
