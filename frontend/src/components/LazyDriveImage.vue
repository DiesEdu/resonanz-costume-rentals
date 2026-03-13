<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  /**
   * Can be a Drive file ID (e.g. "1abc...") or a full image URL.
   */
  fileId: {
    type: String,
    default: '',
  },
  alt: {
    type: String,
    default: '',
  },
})

const loaded = ref(false)

const imageUrl = computed(() => {
  if (!props.fileId) return ''
  // If caller already passes a full URL, use it as-is; otherwise build Drive URL
  if (props.fileId.startsWith('http')) return props.fileId
  return `https://lh3.googleusercontent.com/d/${props.fileId}`
})
</script>

<template>
  <div class="image-wrapper">
    <!-- skeleton -->
    <div v-if="!loaded" class="skeleton"></div>

    <img
      v-if="imageUrl"
      loading="lazy"
      :src="imageUrl"
      :alt="alt"
      @load="loaded = true"
      :class="{ visible: loaded }"
    />
  </div>
</template>

<style scoped>
.image-wrapper {
  position: relative;
  width: 100%;
  height: 200px;
  overflow: hidden;
}

/* skeleton loading */
.skeleton {
  position: absolute;
  inset: 0;
  background: linear-gradient(90deg, #eee 25%, #ddd 37%, #eee 63%);
  animation: shimmer 1.4s infinite;
}

/* image */
img {
  width: 100%;
  max-height: 100%;
  object-fit: cover;
  opacity: 1;
  transition: opacity 0.4s ease;
}

img.visible {
  opacity: 1;
}

@keyframes shimmer {
  0% {
    background-position: -400px 0;
  }
  100% {
    background-position: 400px 0;
  }
}
</style>
