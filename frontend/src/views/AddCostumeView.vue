<template>
  <div class="add-costume-page">
    <!-- ── Animated Hero Header ─────────────────────────────────────────── -->
    <section class="page-hero">
      <div class="hero-particles">
        <span v-for="n in 18" :key="n" class="particle" :style="particleStyle(n)"></span>
      </div>
      <div class="hero-inner container">
        <router-link to="/costumes" class="back-link">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            width="18"
            height="18"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2.5"
            stroke-linecap="round"
            stroke-linejoin="round"
          >
            <path d="M19 12H5M12 19l-7-7 7-7" />
          </svg>
          Back to Collection
        </router-link>

        <!-- <div class="hero-badge">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            width="16"
            height="16"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
          >
            <line x1="12" y1="5" x2="12" y2="19" />
            <line x1="5" y1="12" x2="19" y2="12" />
          </svg>
          New Costume
        </div> -->

        <h1 class="hero-title">Add to the <span class="gold-text">Collection</span></h1>
        <p class="hero-subtitle">
          Bring a new piece to life — fill in the details and let it shine.
        </p>

        <!-- Step indicators -->
        <div class="step-indicators">
          <div
            v-for="(step, i) in steps"
            :key="i"
            class="step-dot"
            :class="{ active: currentStep === i, done: currentStep > i }"
            @click="goToStep(i)"
          >
            <span class="step-num">{{ i + 1 }}</span>
            <span class="step-label">{{ step }}</span>
          </div>
          <div class="step-line">
            <div class="step-line-fill" :style="{ width: stepProgress + '%' }"></div>
          </div>
        </div>
      </div>
    </section>

    <!-- ── Form ──────────────────────────────────────────────────────────── -->
    <section class="form-section">
      <div class="container">
        <!-- Alerts -->
        <transition name="slide-down">
          <div v-if="successMessage" class="alert alert-success">
            <div class="alert-icon">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="22"
                height="22"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2.5"
                stroke-linecap="round"
                stroke-linejoin="round"
              >
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                <polyline points="22 4 12 14.01 9 11.01" />
              </svg>
            </div>
            <div><strong>Success!</strong><br />{{ successMessage }}</div>
          </div>
        </transition>

        <transition name="slide-down">
          <div v-if="errorMessage" class="alert alert-error">
            <div class="alert-icon">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="22"
                height="22"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2.5"
                stroke-linecap="round"
                stroke-linejoin="round"
              >
                <circle cx="12" cy="12" r="10" />
                <line x1="12" y1="8" x2="12" y2="12" />
                <line x1="12" y1="16" x2="12.01" y2="16" />
              </svg>
            </div>
            <div><strong>Error</strong><br />{{ errorMessage }}</div>
          </div>
        </transition>

        <form class="costume-form" @submit.prevent="handleSubmit" novalidate>
          <!-- ── Step 0 : Basic Info ──────────────────────────────────── -->
          <transition name="fade-slide" mode="out-in">
            <div v-if="currentStep === 0" key="step0" class="form-card">
              <div class="card-header">
                <div class="card-icon">🎭</div>
                <div>
                  <h2 class="card-title">Basic Information</h2>
                  <p class="card-desc">Name, category, and pricing details</p>
                </div>
              </div>

              <div class="form-grid">
                <!-- Name -->
                <div
                  class="form-group span-2"
                  :class="{ 'has-error': errors.name, 'is-valid': form.name && !errors.name }"
                >
                  <label for="name" class="form-label">
                    <span class="label-icon">✦</span> Costume Name <span class="required">*</span>
                  </label>
                  <div class="input-wrapper">
                    <input
                      id="name"
                      v-model="form.name"
                      type="text"
                      class="form-input"
                      placeholder="e.g. Kain Batik Biru"
                      @blur="validateField('name')"
                      @input="clearError('name')"
                    />
                    <span class="input-valid-icon" v-if="form.name && !errors.name">✓</span>
                  </div>
                  <transition name="err"
                    ><span v-if="errors.name" class="error-text">{{
                      errors.name
                    }}</span></transition
                  >
                </div>

                <!-- Costume Code -->
                <div
                  class="form-group"
                  :class="{
                    'has-error': errors.costume_code,
                    'is-valid': form.costume_code && !errors.costume_code,
                  }"
                >
                  <label for="costume_code" class="form-label">
                    <span class="label-icon">✦</span> Costume Code <span class="required">*</span>
                  </label>
                  <div class="input-wrapper">
                    <input
                      id="costume_code"
                      v-model="form.costume_code"
                      type="text"
                      class="form-input"
                      placeholder="e.g. KBB001"
                      @blur="validateField('costume_code')"
                      @input="clearError('costume_code')"
                    />
                    <span class="input-valid-icon" v-if="form.costume_code && !errors.costume_code"
                      >✓</span
                    >
                  </div>
                  <transition name="err"
                    ><span v-if="errors.costume_code" class="error-text">{{
                      errors.costume_code
                    }}</span></transition
                  >
                </div>

                <!-- Amount -->
                <div
                  class="form-group"
                  :class="{
                    'has-error': errors.amount,
                    'is-valid': form.amount && !errors.amount,
                  }"
                >
                  <label for="amount" class="form-label">
                    <span class="label-icon">✦</span> Amount <span class="required">*</span>
                  </label>
                  <div class="input-wrapper">
                    <input
                      id="amount"
                      v-model="form.amount"
                      type="number"
                      class="form-input"
                      placeholder="e.g. KBB001"
                      @blur="validateField('amount')"
                      @input="clearError('amount')"
                    />
                    <span class="input-valid-icon" v-if="form.amount && !errors.amount">✓</span>
                  </div>
                  <transition name="err"
                    ><span v-if="errors.amount" class="error-text">{{
                      errors.amount
                    }}</span></transition
                  >
                </div>

                <!-- Category -->
                <div
                  class="form-group"
                  :class="{
                    'has-error': errors.category,
                    'is-valid': form.category && !errors.category,
                  }"
                >
                  <label for="category" class="form-label">
                    <span class="label-icon">✦</span> Category <span class="required">*</span>
                  </label>
                  <div class="input-wrapper">
                    <select
                      id="category"
                      v-model="form.category"
                      class="form-input"
                      @change="clearError('category')"
                      @blur="validateField('category')"
                    >
                      <option disabled value="">Select a category</option>
                      <option v-for="cat in existingCategories" :key="cat" :value="cat">
                        {{ cat }}
                      </option>
                    </select>
                    <span class="input-valid-icon" v-if="form.category && !errors.category">✓</span>
                  </div>
                  <transition name="err"
                    ><span v-if="errors.category" class="error-text">{{
                      errors.category
                    }}</span></transition
                  >
                </div>

                <!-- Container -->
                <div
                  class="form-group"
                  :class="{
                    'has-error': errors.container,
                    'is-valid': form.container && !errors.container,
                  }"
                >
                  <label for="container" class="form-label">
                    <span class="label-icon">✦</span> Container <span class="required">*</span>
                  </label>
                  <div class="input-wrapper">
                    <input
                      id="container"
                      v-model="form.container"
                      type="text"
                      class="form-input"
                      placeholder="e.g. Box A3, Shelf 2"
                      @blur="validateField('container')"
                      @input="clearError('container')"
                    />
                    <span class="input-valid-icon" v-if="form.container && !errors.container"
                      >✓</span
                    >
                  </div>
                  <transition name="err"
                    ><span v-if="errors.container" class="error-text">{{
                      errors.container
                    }}</span></transition
                  >
                </div>
              </div>

              <!-- Availability toggle -->
              <div class="availability-row">
                <div class="avail-info">
                  <span class="avail-title">Availability Status</span>
                  <span class="avail-sub">Is this costume ready to rent right now?</span>
                </div>
                <label class="toggle-label">
                  <input v-model="form.available" type="checkbox" class="toggle-input" />
                  <span class="toggle-track">
                    <span class="toggle-thumb"></span>
                  </span>
                  <span class="toggle-text" :class="form.available ? 'avail-on' : 'avail-off'">
                    {{ form.available ? 'Available' : 'Unavailable' }}
                  </span>
                </label>
              </div>
            </div>
          </transition>

          <!-- ── Step 1 : Sizes & Image ───────────────────────────────── -->
          <transition name="fade-slide" mode="out-in">
            <div v-if="currentStep === 1" key="step1" class="form-card">
              <div class="card-header">
                <div class="card-icon">👗</div>
                <div>
                  <h2 class="card-title">Sizes & Visuals</h2>
                  <p class="card-desc">Select available sizes and add an image</p>
                </div>
              </div>

              <!-- Sizes -->
              <div class="form-group" :class="{ 'has-error': errors.sizes }">
                <label class="form-label">
                  <span class="label-icon">✦</span> Available Sizes <span class="required">*</span>
                </label>
                <div class="size-chips">
                  <label
                    v-for="size in sizeOptions"
                    :key="size"
                    class="size-chip"
                    :class="{ selected: form.sizes.includes(size) }"
                  >
                    <input
                      type="checkbox"
                      :value="size"
                      v-model="form.sizes"
                      class="size-checkbox"
                    />
                    <span class="size-label">{{ size }}</span>
                    <span class="size-check">✓</span>
                  </label>
                </div>
                <transition name="err"
                  ><span v-if="errors.sizes" class="error-text">{{
                    errors.sizes
                  }}</span></transition
                >
              </div>

              <!-- Image URL -->
              <div class="form-group">
                <label for="image" class="form-label">
                  <span class="label-icon">✦</span> Image URL
                </label>
                <div class="input-wrapper">
                  <input
                    id="image"
                    v-model="form.image"
                    type="url"
                    class="form-input"
                    placeholder="https://example.com/costume.jpg"
                    @input="onImageUrlInput"
                  />
                </div>

                <!-- Image file upload -->
                <div class="file-input-group">
                  <label class="form-label">
                    <span class="label-icon">✦</span> Or Upload Image
                  </label>
                  <div class="input-wrapper">
                    <input
                      ref="imageInputEl"
                      type="file"
                      accept="image/*"
                      class="form-input file-input"
                      @change="onImageSelected"
                    />
                  </div>
                  <p class="help-text">Choose a file to upload, or paste a URL above.</p>
                </div>

                <transition name="fade">
                  <div v-if="previewSrc" class="image-preview-card">
                    <div class="image-preview-inner">
                      <img
                        :src="previewSrc"
                        alt="Preview"
                        class="image-preview"
                        @error="imageLoadError = true"
                        @load="imageLoadError = false"
                      />
                      <div v-if="imageLoadError" class="image-error-overlay">
                        <svg
                          xmlns="http://www.w3.org/2000/svg"
                          width="32"
                          height="32"
                          viewBox="0 0 24 24"
                          fill="none"
                          stroke="currentColor"
                          stroke-width="1.5"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                        >
                          <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                          <circle cx="8.5" cy="8.5" r="1.5" />
                          <polyline points="21 15 16 10 5 21" />
                        </svg>
                        <span>Could not load image</span>
                      </div>
                      <div v-else class="image-preview-badge">Preview</div>
                    </div>
                  </div>
                </transition>

                <div v-if="!previewSrc" class="image-placeholder">
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="40"
                    height="40"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  >
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                    <circle cx="8.5" cy="8.5" r="1.5" />
                    <polyline points="21 15 16 10 5 21" />
                  </svg>
                  <span>Paste an image URL above to see a preview</span>
                </div>
              </div>
            </div>
          </transition>

          <!-- ── Step 2 : Description ─────────────────────────────────── -->
          <transition name="fade-slide" mode="out-in">
            <div v-if="currentStep === 2" key="step2" class="form-card">
              <div class="card-header">
                <div class="card-icon">📝</div>
                <div>
                  <h2 class="card-title">Description</h2>
                  <p class="card-desc">Tell renters what makes this costume special</p>
                </div>
              </div>

              <div class="form-group" :class="{ 'has-error': errors.description }">
                <label for="description" class="form-label">
                  <span class="label-icon">✦</span> Costume Description
                </label>
                <textarea
                  id="description"
                  v-model="form.description"
                  class="form-textarea"
                  rows="6"
                  placeholder="Describe the costume — material, style, included accessories, care instructions…"
                  @blur="validateField('description')"
                  @input="clearError('description')"
                ></textarea>
                <div class="textarea-footer">
                  <transition name="err"
                    ><span v-if="errors.description" class="error-text">{{
                      errors.description
                    }}</span></transition
                  >
                  <span
                    class="char-count"
                    :class="{
                      warning: form.description.length > 450,
                      danger: form.description.length > 490,
                    }"
                  >
                    {{ form.description.length }}<span class="char-max">/500</span>
                  </span>
                </div>
              </div>

              <!-- Summary card -->
              <div v-if="form.name" class="summary-card">
                <h3 class="summary-title">Summary Preview</h3>
                <div class="summary-grid">
                  <div class="summary-item">
                    <span class="summary-key">Name</span>
                    <span class="summary-val">{{ form.name }}</span>
                  </div>
                  <div class="summary-item">
                    <span class="summary-key">Category</span>
                    <span class="summary-val">{{ form.category || '—' }}</span>
                  </div>
                  <div class="summary-item">
                    <span class="summary-key">Sizes</span>
                    <span class="summary-val">{{
                      form.sizes.length ? form.sizes.join(', ') : '—'
                    }}</span>
                  </div>
                  <div class="summary-item">
                    <span class="summary-key">Status</span>
                    <span
                      class="summary-val"
                      :class="form.available ? 'text-avail' : 'text-unavail'"
                    >
                      {{ form.available ? '✓ Available' : '✗ Unavailable' }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </transition>

          <!-- ── Navigation Buttons ──────────────────────────────────── -->
          <div class="form-nav">
            <router-link v-if="currentStep === 0" to="/costumes" class="btn btn-ghost">
              Cancel
            </router-link>
            <button v-else type="button" class="btn btn-ghost" @click="prevStep">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="16"
                height="16"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2.5"
                stroke-linecap="round"
                stroke-linejoin="round"
              >
                <path d="M19 12H5M12 19l-7-7 7-7" />
              </svg>
              Back
            </button>

            <button
              v-if="currentStep < steps.length - 1"
              type="button"
              class="btn btn-next"
              @click="nextStep"
            >
              Continue
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="16"
                height="16"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2.5"
                stroke-linecap="round"
                stroke-linejoin="round"
              >
                <path d="M5 12h14M12 5l7 7-7 7" />
              </svg>
            </button>

            <button v-else type="submit" class="btn btn-submit" :disabled="loading">
              <span v-if="loading" class="btn-spinner"></span>
              <template v-else>
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  width="18"
                  height="18"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2.5"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                >
                  <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                  <polyline points="22 4 12 14.01 9 11.01" />
                </svg>
                Add to Collection
              </template>
            </button>
          </div>
        </form>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useCostumesStore } from '@/stores/costumes'

const router = useRouter()
const costumesStore = useCostumesStore()

const loading = ref(false)
const successMessage = ref('')
const errorMessage = ref('')
const imageLoadError = ref(false)
const imageFile = ref(null)
const imagePreviewUrl = ref('')
const imageInputEl = ref(null)

const sizeOptions = ['XS', 'S', 'M', 'L', 'XL']
const steps = ['Basic Info', 'Sizes & Image', 'Description']
const currentStep = ref(0)

const stepProgress = computed(() => (currentStep.value / (steps.length - 1)) * 100)
const previewSrc = computed(() => imagePreviewUrl.value || form.value.image)

const form = ref({
  name: '',
  category: '',
  container: '',
  amount: 0,
  description: '',
  image: '',
  available: true,
  sizes: [],
})

const errors = ref({})

const existingCategories = computed(() => costumesStore.categories.filter((c) => c !== 'All'))

// ── Particle helper ──────────────────────────────────────────────────────────
function particleStyle(n) {
  const size = 4 + (n % 5) * 3
  const delay = (n * 0.37) % 4
  const duration = 6 + (n % 4) * 2
  const left = (n * 17 + 11) % 97
  const top = (n * 23 + 7) % 85
  return {
    width: size + 'px',
    height: size + 'px',
    left: left + '%',
    top: top + '%',
    animationDelay: delay + 's',
    animationDuration: duration + 's',
    opacity: 0.15 + (n % 5) * 0.07,
  }
}

// ── Validation ───────────────────────────────────────────────────────────────
function clearError(field) {
  errors.value[field] = ''
}

function validateField(field) {
  // Clear error for this field
  if (errors.value[field] !== undefined) {
    errors.value[field] = ''
  }

  // Validate based on field
  switch (field) {
    case 'name':
      if (!form.value.name?.trim()) errors.value.name = 'Costume name is required.'
      break

    case 'costume_code':
      if (!form.value.costume_code?.trim()) errors.value.costume_code = 'Costume code is required.'
      break

    case 'category':
      if (!form.value.category?.trim()) errors.value.category = 'Category is required.'
      break

    case 'container':
      if (!form.value.container?.trim()) errors.value.container = 'Container is required.'
      break

    case 'amount':
      if (form.value.amount < 0) errors.value.amount = 'Amount must be a positive number.'
      break

    case 'description':
      if (form.value.description?.length > 500)
        errors.value.description = 'Description must be 500 characters or fewer.'
      break

    case 'sizes':
      if (!form.value.sizes || form.value.sizes.length === 0)
        errors.value.sizes = 'Select at least one size.'
      break
  }
}

function validateStep(step) {
  if (step === 0) {
    ;['name', 'category'].forEach(validateField)
    return !errors.value.name && !errors.value.category
  }
  if (step === 1) {
    validateField('sizes')
    return !errors.value.sizes
  }
  if (step === 2) {
    validateField('description')
    return !errors.value.description
  }
  return true
}

// ── Step navigation ──────────────────────────────────────────────────────────
function nextStep() {
  if (validateStep(currentStep.value)) currentStep.value++
}
function prevStep() {
  if (currentStep.value > 0) currentStep.value--
}
function goToStep(i) {
  if (i < currentStep.value) currentStep.value = i
}

function onImageUrlInput() {
  if (!imageFile.value && !imagePreviewUrl.value) return
  if (imagePreviewUrl.value) URL.revokeObjectURL(imagePreviewUrl.value)
  imagePreviewUrl.value = ''
  imageFile.value = null
  if (imageInputEl.value) {
    imageInputEl.value.value = ''
  }
}

function onImageSelected(event) {
  const file = event?.target?.files?.[0]
  if (!file) {
    if (imagePreviewUrl.value) URL.revokeObjectURL(imagePreviewUrl.value)
    imageFile.value = null
    imagePreviewUrl.value = ''
    return
  }

  imageFile.value = file
  imageLoadError.value = false
  if (imagePreviewUrl.value) URL.revokeObjectURL(imagePreviewUrl.value)
  imagePreviewUrl.value = URL.createObjectURL(file)
  form.value.image = ''
}

// ── Submit ───────────────────────────────────────────────────────────────────
async function handleSubmit() {
  successMessage.value = ''
  errorMessage.value = ''
  if (!validateStep(2)) return

  loading.value = true
  try {
    const baseFields = {
      name: form.value.name.trim(),
      costume_code: form.value.costume_code.trim(),
      category: form.value.category.trim(),
      amount: form.value.amount,
      description: form.value.description.trim(),
      available: form.value.available ? 1 : 0,
      sizes: form.value.sizes,
    }

    const payload =
      imageFile.value && typeof FormData !== 'undefined'
        ? (() => {
            const fd = new FormData()
            fd.append('name', baseFields.name)
            fd.append('costume_code', baseFields.costume_code)
            fd.append('category', baseFields.category)
            fd.append('amount', baseFields.amount)
            fd.append('description', baseFields.description)
            fd.append('available', String(baseFields.available))
            fd.append('sizes', JSON.stringify(baseFields.sizes))
            fd.append('image', imageFile.value)
            return fd
          })()
        : {
            ...baseFields,
            image: form.value.image.trim(),
          }

    const created = await costumesStore.addCostume(payload)
    successMessage.value = `"${created.name}" has been added to the collection!`
    form.value = {
      name: '',
      costume_code: '',
      category: '',
      amount: 0,
      description: '',
      image: '',
      available: true,
      sizes: [],
    }
    if (imagePreviewUrl.value) URL.revokeObjectURL(imagePreviewUrl.value)
    imagePreviewUrl.value = ''
    imageFile.value = null
    if (imageInputEl.value) {
      imageInputEl.value.value = ''
    }
    errors.value = {}
    setTimeout(() => router.push(`/costume/${created.id}`), 1800)
  } catch (err) {
    errorMessage.value = err.message || 'Failed to add costume. Please try again.'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
/* ── Page ─────────────────────────────────────────────────────────────────── */
.add-costume-page {
  min-height: 100vh;
  background: var(--ivory, #faf7f2);
}

/* ── Hero ─────────────────────────────────────────────────────────────────── */
.page-hero {
  position: relative;
  overflow: hidden;
  background: linear-gradient(
    135deg,
    var(--charcoal, #0f0f1a) 0%,
    var(--charcoal-3, #16213e) 60%,
    #1e0a3c 100%
  );
  /* Extra top padding so the fixed navbar never overlaps */
  padding: 6.5rem 0 4rem;
  color: #fff;
}

/* Floating particles */
.hero-particles {
  position: absolute;
  inset: 0;
  pointer-events: none;
}
.particle {
  position: absolute;
  border-radius: 50%;
  background: var(--gold, #c9a84c);
  animation: float-particle linear infinite;
}
@keyframes float-particle {
  0% {
    transform: translateY(0) scale(1);
    opacity: inherit;
  }
  50% {
    transform: translateY(-30px) scale(1.3);
  }
  100% {
    transform: translateY(0) scale(1);
    opacity: inherit;
  }
}

.hero-inner {
  position: relative;
  z-index: 1;
  max-width: 860px;
  margin: 0 auto;
  padding: 0 1.5rem;
  animation: hero-enter 0.7s cubic-bezier(0.22, 1, 0.36, 1) both;
}
@keyframes hero-enter {
  from {
    opacity: 0;
    transform: translateY(28px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.back-link {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  color: rgba(255, 255, 255, 0.55);
  text-decoration: none;
  font-size: 0.85rem;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  margin-bottom: 1.5rem;
  transition: color 0.2s;
}
.back-link:hover {
  color: var(--gold, #c9a84c);
}

.hero-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  background: rgba(201, 168, 76, 0.15);
  border: 1px solid rgba(201, 168, 76, 0.35);
  color: var(--gold, #c9a84c);
  font-size: 0.78rem;
  font-weight: 700;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  padding: 0.35rem 0.9rem;
  border-radius: 999px;
  margin-bottom: 1rem;
}

.hero-title {
  font-family: 'Playfair Display', Georgia, serif;
  font-size: clamp(2rem, 5vw, 3.2rem);
  font-weight: 800;
  margin: 0 0 0.6rem;
  line-height: 1.15;
}
.gold-text {
  background: linear-gradient(90deg, var(--gold, #c9a84c), var(--gold-light, #e8c97e));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.hero-subtitle {
  color: rgba(255, 255, 255, 0.55);
  font-size: 1rem;
  margin: 0 0 2.5rem;
}

/* ── Step indicators ─────────────────────────────────────────────────────── */
.step-indicators {
  position: relative;
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 1rem 1.4rem;
  padding: 1.1rem 1.4rem;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 16px;
}

.step-line {
  display: none;
}
.step-line-fill {
  height: 100%;
  background: linear-gradient(90deg, var(--gold, #c9a84c), var(--gold-light, #e8c97e));
  transition: width 0.5s cubic-bezier(0.4, 0, 0.2, 1);
}

.step-dot {
  position: relative;
  z-index: 1;
  display: flex;
  align-items: center;
  gap: 0.6rem;
  padding: 0.55rem 0.7rem;
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.25s;
}

.step-num {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.8rem;
  font-weight: 700;
  background: rgba(255, 255, 255, 0.1);
  border: 2px solid rgba(255, 255, 255, 0.22);
  color: rgba(255, 255, 255, 0.7);
  transition: all 0.3s;
}
.step-dot.active .step-num {
  background: var(--gold, #c9a84c);
  border-color: var(--gold, #c9a84c);
  color: var(--charcoal, #0f0f1a);
  box-shadow: 0 0 0 4px rgba(201, 168, 76, 0.25);
}
.step-dot.done .step-num {
  background: rgba(201, 168, 76, 0.2);
  border-color: var(--gold, #c9a84c);
  color: var(--gold, #c9a84c);
}

.step-label {
  font-size: 0.78rem;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  color: rgba(255, 255, 255, 0.65);
  white-space: normal;
  line-height: 1.35;
  transition: color 0.3s;
}
.step-dot.active .step-label {
  color: var(--gold, #c9a84c);
}
.step-dot.done .step-label {
  color: rgba(255, 255, 255, 0.55);
}

@media (max-width: 768px) {
  .page-hero {
    padding-top: 7.5rem; /* slightly more on mobile where nav is taller */
  }
  .step-indicators {
    grid-template-columns: 1fr;
  }
}

@media (min-width: 992px) {
  .step-indicators {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.6rem 0;
    background: transparent;
    border: none;
  }
  .step-line {
    display: block;
    position: absolute;
    top: 100%; /* align with center of 34px step circle */
    left: 17px;
    right: 17px;
    height: 2px;
    background: rgba(255, 255, 255, 0.12);
    z-index: 0;
    pointer-events: none;
  }
  .step-dot {
    flex-direction: column;
    background: transparent;
    border: none;
    padding: 0;
  }
  .step-label {
    white-space: nowrap;
    text-align: center;
  }
}

/* ── Form Section ─────────────────────────────────────────────────────────── */
.form-section {
  padding: 2.5rem 0 5rem;
}

.container {
  max-width: 860px;
  margin: 0 auto;
  padding: 0 1.5rem;
}

/* ── Alerts ───────────────────────────────────────────────────────────────── */
.alert {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  padding: 1.1rem 1.4rem;
  border-radius: 14px;
  font-size: 0.9rem;
  margin-bottom: 1.5rem;
  line-height: 1.5;
}
.alert-success {
  background: linear-gradient(135deg, #d1fae5, #a7f3d0);
  color: #065f46;
  border: 1px solid #6ee7b7;
}
.alert-error {
  background: linear-gradient(135deg, #fee2e2, #fecaca);
  color: #991b1b;
  border: 1px solid #fca5a5;
}
.alert-icon {
  flex-shrink: 0;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 0.5);
}

/* ── Form Card ────────────────────────────────────────────────────────────── */
.form-card {
  background: #fff;
  border-radius: 20px;
  padding: 2.5rem;
  box-shadow:
    0 8px 40px rgba(15, 15, 26, 0.08),
    0 2px 8px rgba(15, 15, 26, 0.04);
  border: 1px solid rgba(201, 168, 76, 0.12);
  margin-bottom: 1.5rem;
  animation: card-enter 0.45s cubic-bezier(0.22, 1, 0.36, 1) both;
}
@keyframes card-enter {
  from {
    opacity: 0;
    transform: translateY(20px) scale(0.98);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

.card-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 2rem;
  padding-bottom: 1.5rem;
  border-bottom: 1px solid #f1ece0;
}
.card-icon {
  font-size: 2rem;
  width: 56px;
  height: 56px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #faf3e0, #f5e6c0);
  border-radius: 14px;
  border: 1px solid rgba(201, 168, 76, 0.25);
  flex-shrink: 0;
}
.card-title {
  font-family: 'Playfair Display', Georgia, serif;
  font-size: 1.35rem;
  font-weight: 700;
  color: var(--charcoal, #0f0f1a);
  margin: 0 0 0.2rem;
}
.card-desc {
  font-size: 0.85rem;
  color: var(--text-muted, #8a8078);
  margin: 0;
}

/* ── Form Grid ────────────────────────────────────────────────────────────── */
.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.5rem;
}
.span-2 {
  grid-column: 1 / -1;
}

@media (max-width: 600px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
  .span-2 {
    grid-column: 1;
  }
}

/* ── Form Groups ─────────────────────────────────────────────────────────── */
.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.45rem;
}

.form-label {
  font-weight: 700;
  font-size: 0.82rem;
  letter-spacing: 0.07em;
  text-transform: uppercase;
  color: var(--charcoal, #0f0f1a);
  display: flex;
  align-items: center;
  gap: 0.4rem;
}
.label-icon {
  color: var(--gold, #c9a84c);
  font-size: 0.65rem;
}
.required {
  color: #ef4444;
}

/* ── Inputs ───────────────────────────────────────────────────────────────── */
.input-wrapper {
  position: relative;
}

.form-input,
.form-textarea {
  width: 100%;
  padding: 0.8rem 1.1rem;
  border: 1.5px solid #e8e0d0;
  border-radius: 10px;
  font-size: 0.95rem;
  color: var(--charcoal, #0f0f1a);
  background: #faf7f2;
  transition:
    border-color 0.2s,
    box-shadow 0.2s,
    background 0.2s;
  box-sizing: border-box;
  font-family: inherit;
}
.form-input:focus,
.form-textarea:focus {
  outline: none;
  border-color: var(--gold, #c9a84c);
  background: #fff;
  box-shadow: 0 0 0 3px rgba(201, 168, 76, 0.18);
}
.has-error .form-input,
.has-error .form-textarea {
  border-color: #ef4444;
  background: #fff5f5;
}
.is-valid .form-input {
  border-color: #10b981;
}

.input-valid-icon {
  position: absolute;
  right: 0.9rem;
  top: 50%;
  transform: translateY(-50%);
  color: #10b981;
  font-weight: 700;
  font-size: 0.9rem;
  pointer-events: none;
}

.form-textarea {
  resize: vertical;
  min-height: 140px;
  line-height: 1.7;
}

.error-text {
  font-size: 0.78rem;
  color: #ef4444;
  display: flex;
  align-items: center;
  gap: 0.3rem;
}
.error-text::before {
  content: '⚠';
  font-size: 0.7rem;
}

/* ── Input prefix (Rp) ───────────────────────────────────────────────────── */
.input-prefix-wrapper {
  position: relative;
}
.input-prefix {
  position: absolute;
  left: 1rem;
  top: 50%;
  transform: translateY(-50%);
  color: var(--text-muted, #8a8078);
  font-size: 0.9rem;
  font-weight: 600;
  pointer-events: none;
  z-index: 1;
}
.form-input.with-prefix {
  padding-left: 2.8rem;
}

/* ── Availability row ────────────────────────────────────────────────────── */
.availability-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: linear-gradient(135deg, #faf3e0, #fdf8ee);
  border: 1px solid rgba(201, 168, 76, 0.2);
  border-radius: 12px;
  padding: 1.1rem 1.4rem;
  margin-top: 1.5rem;
  gap: 1rem;
  flex-wrap: wrap;
}
.avail-info {
  display: flex;
  flex-direction: column;
  gap: 0.2rem;
}
.avail-title {
  font-weight: 700;
  font-size: 0.9rem;
  color: var(--charcoal, #0f0f1a);
}
.avail-sub {
  font-size: 0.8rem;
  color: var(--text-muted, #8a8078);
}

.toggle-label {
  display: inline-flex;
  align-items: center;
  gap: 0.75rem;
  cursor: pointer;
  user-select: none;
}
.toggle-input {
  display: none;
}
.toggle-track {
  position: relative;
  width: 50px;
  height: 26px;
  background: #d1d5db;
  border-radius: 999px;
  transition: background 0.3s;
  flex-shrink: 0;
}
.toggle-input:checked + .toggle-track {
  background: var(--gold, #c9a84c);
}
.toggle-thumb {
  position: absolute;
  top: 3px;
  left: 3px;
  width: 20px;
  height: 20px;
  background: #fff;
  border-radius: 50%;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
  transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.toggle-input:checked + .toggle-track .toggle-thumb {
  transform: translateX(24px);
}
.toggle-text {
  font-size: 0.9rem;
  font-weight: 700;
}
.avail-on {
  color: #059669;
}
.avail-off {
  color: #9ca3af;
}

/* ── Size chips ──────────────────────────────────────────────────────────── */
.size-chips {
  display: flex;
  flex-wrap: wrap;
  gap: 0.6rem;
}
.size-chip {
  position: relative;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 56px;
  padding: 0.6rem 1rem;
  border: 1.5px solid #e8e0d0;
  border-radius: 10px;
  font-size: 0.9rem;
  font-weight: 700;
  cursor: pointer;
  color: var(--charcoal, #0f0f1a);
  background: #faf7f2;
  transition: all 0.22s cubic-bezier(0.34, 1.56, 0.64, 1);
  user-select: none;
  overflow: hidden;
}
.size-chip:hover {
  border-color: var(--gold, #c9a84c);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(201, 168, 76, 0.2);
}
.size-chip.selected {
  background: linear-gradient(135deg, var(--charcoal, #0f0f1a), var(--charcoal-3, #16213e));
  border-color: var(--gold, #c9a84c);
  color: var(--gold, #c9a84c);
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(15, 15, 26, 0.25);
}
.size-checkbox {
  display: none;
}
.size-check {
  position: absolute;
  top: 2px;
  right: 4px;
  font-size: 0.6rem;
  color: var(--gold, #c9a84c);
  opacity: 0;
  transition: opacity 0.2s;
}
.size-chip.selected .size-check {
  opacity: 1;
}

/* ── Image preview ───────────────────────────────────────────────────────── */
.image-preview-card {
  margin-top: 0.75rem;
  border-radius: 14px;
  overflow: hidden;
  border: 1.5px solid rgba(201, 168, 76, 0.25);
  box-shadow: 0 4px 20px rgba(15, 15, 26, 0.08);
}
.image-preview-inner {
  position: relative;
}
.image-preview {
  width: 100%;
  max-height: 260px;
  object-fit: cover;
  display: block;
}
.image-preview-badge {
  position: absolute;
  top: 10px;
  right: 10px;
  background: rgba(15, 15, 26, 0.7);
  color: var(--gold, #c9a84c);
  font-size: 0.72rem;
  font-weight: 700;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  padding: 0.3rem 0.7rem;
  border-radius: 999px;
  backdrop-filter: blur(4px);
}
.image-error-overlay {
  position: absolute;
  inset: 0;
  background: #fef2f2;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  color: #ef4444;
  font-size: 0.85rem;
  min-height: 120px;
}

.image-placeholder {
  margin-top: 0.75rem;
  border: 2px dashed #e8e0d0;
  border-radius: 14px;
  padding: 2.5rem 1rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.75rem;
  color: #c4b89a;
  font-size: 0.85rem;
  text-align: center;
  background: #faf7f2;
}

/* ── Textarea footer ─────────────────────────────────────────────────────── */
.textarea-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  min-height: 1.2rem;
}
.char-count {
  font-size: 0.78rem;
  color: var(--text-muted, #8a8078);
  font-variant-numeric: tabular-nums;
}
.char-count.warning {
  color: #f59e0b;
}
.char-count.danger {
  color: #ef4444;
}
.char-max {
  opacity: 0.5;
}

/* ── Summary card ────────────────────────────────────────────────────────── */
.summary-card {
  margin-top: 2rem;
  background: linear-gradient(135deg, #faf3e0, #fdf8ee);
  border: 1px solid rgba(201, 168, 76, 0.25);
  border-radius: 14px;
  padding: 1.5rem;
}
.summary-title {
  font-family: 'Playfair Display', Georgia, serif;
  font-size: 1rem;
  font-weight: 700;
  color: var(--charcoal, #0f0f1a);
  margin: 0 0 1rem;
}
.summary-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
  gap: 0.75rem;
}
.summary-item {
  display: flex;
  flex-direction: column;
  gap: 0.2rem;
}
.summary-key {
  font-size: 0.72rem;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: var(--text-muted, #8a8078);
}
.summary-val {
  font-size: 0.9rem;
  font-weight: 600;
  color: var(--charcoal, #0f0f1a);
}
.text-avail {
  color: #059669;
}
.text-unavail {
  color: #9ca3af;
}

/* ── Navigation buttons ──────────────────────────────────────────────────── */
.form-nav {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
}

.btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.8rem 1.8rem;
  border-radius: 12px;
  font-size: 0.9rem;
  font-weight: 700;
  cursor: pointer;
  text-decoration: none;
  border: none;
  transition: all 0.22s cubic-bezier(0.34, 1.56, 0.64, 1);
  letter-spacing: 0.03em;
}

.btn-ghost {
  background: transparent;
  color: var(--text-muted, #8a8078);
  border: 1.5px solid #e8e0d0;
}
.btn-ghost:hover {
  background: #f5f0e8;
  color: var(--charcoal, #0f0f1a);
  transform: translateX(-2px);
}

.btn-next {
  background: linear-gradient(135deg, var(--charcoal, #0f0f1a), var(--charcoal-3, #16213e));
  color: var(--gold, #c9a84c);
  border: 1px solid rgba(201, 168, 76, 0.3);
  margin-left: auto;
}
.btn-next:hover {
  transform: translateX(3px);
  box-shadow: 0 6px 20px rgba(15, 15, 26, 0.25);
}

.btn-submit {
  background: linear-gradient(135deg, var(--gold, #c9a84c), var(--gold-light, #e8c97e));
  color: var(--charcoal, #0f0f1a);
  margin-left: auto;
  box-shadow: 0 4px 16px rgba(201, 168, 76, 0.35);
}
.btn-submit:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(201, 168, 76, 0.45);
}
.btn-submit:disabled {
  opacity: 0.55;
  cursor: not-allowed;
  transform: none;
}

.btn-spinner {
  width: 18px;
  height: 18px;
  border: 2.5px solid rgba(15, 15, 26, 0.25);
  border-top-color: var(--charcoal, #0f0f1a);
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}
@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

/* ── Transitions ─────────────────────────────────────────────────────────── */
.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: all 0.35s cubic-bezier(0.22, 1, 0.36, 1);
}
.fade-slide-enter-from {
  opacity: 0;
  transform: translateX(30px);
}
.fade-slide-leave-to {
  opacity: 0;
  transform: translateX(-30px);
}

.slide-down-enter-active,
.slide-down-leave-active {
  transition: all 0.35s ease;
}
.slide-down-enter-from {
  opacity: 0;
  transform: translateY(-12px);
}
.slide-down-leave-to {
  opacity: 0;
  transform: translateY(-12px);
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.err-enter-active,
.err-leave-active {
  transition: all 0.2s ease;
}
.err-enter-from,
.err-leave-to {
  opacity: 0;
  transform: translateY(-4px);
}
</style>
