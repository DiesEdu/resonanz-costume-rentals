<template>
  <div class="d-flex flex-wrap justify-content-center mb-4">
    <button
      v-for="cat in categories"
      :key="cat"
      class="btn filter-btn"
      :class="{ active: modelValue === cat }"
      @click="$emit('update:modelValue', cat)"
    >
      {{ cat }}
    </button>
  </div>
</template>

<script setup>
defineProps({
  categories: {
    type: Array,
    required: true,
  },
  modelValue: {
    type: String,
    default: 'All',
  },
})

defineEmits(['update:modelValue'])
</script>
