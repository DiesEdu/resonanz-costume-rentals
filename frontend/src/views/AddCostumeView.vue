<template>
  <div class="add-page">
    <section class="hero">
      <div class="container">
        <router-link to="/costumes" class="back-link">&larr; Back to collection</router-link>
        <p class="eyebrow">Inventory</p>
        <h1>Add a new costume</h1>
        <p class="lede">
          Capture the essentials, attach a photo, and the piece is ready for rentals.
        </p>
      </div>
    </section>

    <section class="content container">
      <div class="grid">
        <form class="card form" @submit.prevent="submitForm" novalidate>
          <div v-if="successMessage" class="alert success">{{ successMessage }}</div>
          <div v-if="errorMessage" class="alert error">{{ errorMessage }}</div>

          <div class="section">
            <div class="section-title">Basics</div>
            <div class="fields two-col">
              <label class="field">
                <span class="label">Costume name *</span>
                <input v-model.trim="form.name" type="text" placeholder="Eg. Kain Batik Biru" />
                <p v-if="errors.name" class="hint">{{ errors.name }}</p>
              </label>
              <label class="field">
                <span class="label">Code *</span>
                <input v-model.trim="form.costume_code" type="text" placeholder="KBB001" />
                <p v-if="errors.costume_code" class="hint">{{ errors.costume_code }}</p>
              </label>
            </div>
            <div class="fields three-col">
              <label class="field">
                <span class="label">Category *</span>
                <select v-model="form.category">
                  <option disabled value="">Pick one</option>
                  <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                </select>
                <p v-if="errors.category" class="hint">{{ errors.category }}</p>
              </label>
              <label class="field">
                <span class="label">Stock *</span>
                <input v-model.number="form.amount" min="0" type="number" />
                <p v-if="errors.amount" class="hint">{{ errors.amount }}</p>
              </label>
              <label class="field toggle-field">
                <span class="label">Available</span>
                <div class="toggle">
                  <input id="available" v-model="form.available" type="checkbox" />
                  <span>{{ form.available ? 'Listed' : 'Hidden' }}</span>
                </div>
              </label>
            </div>
          </div>

          <div class="section">
            <div class="section-title">Sizing</div>
            <div class="chips">
              <label v-for="size in sizeOptions" :key="size" class="chip">
                <input v-model="form.sizes" type="checkbox" :value="size" />
                <span>{{ size }}</span>
              </label>
            </div>
            <p v-if="errors.sizes" class="hint">{{ errors.sizes }}</p>
          </div>

          <div class="section">
            <div class="section-title">Image</div>
            <div class="fields two-col">
              <label class="field">
                <span class="label">Image URL (Google Drive / CDN)</span>
                <input
                  v-model.trim="form.image"
                  type="url"
                  placeholder="https://..."
                  @input="clearFile"
                />
              </label>
              <label class="field file-field">
                <span class="label">Or upload</span>
                <div class="file-row">
                  <button type="button" class="ghost" @click="chooseFile">Choose file</button>
                  <span class="file-name">{{ imageFileName }}</span>
                </div>
                <input
                  ref="fileInput"
                  type="file"
                  accept="image/*"
                  class="hidden"
                  @change="onFileChange"
                />
              </label>
            </div>
          </div>

          <div class="section">
            <div class="section-title">Description</div>
            <textarea
              v-model.trim="form.description"
              rows="4"
              placeholder="Fabric, embellishments, condition, styling tips"
            ></textarea>
            <p v-if="errors.description" class="hint">{{ errors.description }}</p>
          </div>

          <div class="actions">
            <router-link to="/costumes" class="ghost">Cancel</router-link>
            <button type="submit" class="primary" :disabled="loading">
              <span v-if="loading" class="spinner"></span>
              {{ loading ? 'Saving...' : 'Add costume' }}
            </button>
          </div>
        </form>

        <aside class="card preview">
          <div class="preview-label">Live preview</div>
          <div class="preview-media" :style="{ backgroundImage: previewBackground }">
            <img v-if="previewImage" :src="previewImage" alt="Preview" @error="imageError" />
          </div>
          <div class="preview-body">
            <p class="eyebrow">{{ form.category || 'Category' }}</p>
            <h3>{{ form.name || 'Untitled costume' }}</h3>
            <p class="muted">
              Code: {{ form.costume_code || '-' }} | Stock: {{ form.amount || 0 }}
            </p>
            <div class="sizes" v-if="form.sizes.length">
              <span v-for="size in form.sizes" :key="size" class="size-pill">{{ size }}</span>
            </div>
            <p class="muted description">
              {{ form.description || 'Add a short description to help renters decide.' }}
            </p>
          </div>
        </aside>
      </div>

      <!-- Costumes Table Section -->
      <div class="table-section">
        <div class="table-header">
          <h2>Costumes Inventory</h2>
          <p class="table-subtitle">Manage your costume collection</p>
        </div>

        <!-- Search and Filter Controls -->
        <div class="table-controls">
          <div class="search-box">
            <i class="bi bi-search"></i>
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search costumes..."
              @input="handleSearch"
            />
          </div>
          <div class="filter-box">
            <select v-model="selectedCategory" @change="handleFilter">
              <option value="">All Categories</option>
              <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
            </select>
          </div>
        </div>

        <div class="table-container">
          <table class="costumes-table">
            <thead>
              <tr>
                <th>Image</th>
                <th>Name</th>
                <th>Code</th>
                <th>Category</th>
                <th>Stock</th>
                <th>Sizes</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="costume in costumes" :key="costume.id">
                <td class="image-cell">
                  <div class="table-image">
                    <div v-if="costume.image"></div>
                    <img
                      v-if="costume.image"
                      :src="getImageUrl(costume.image)"
                      :alt="costume.name"
                      @error="handleImageError($event, costume)"
                    />
                    <div v-else class="no-image">
                      <i class="bi bi-image"></i>
                    </div>
                  </div>
                </td>
                <td class="name-cell">{{ costume.name }}</td>
                <td>{{ costume.costume_code }}</td>
                <td>
                  <span class="category-badge">{{ costume.category }}</span>
                </td>
                <td>{{ costume.amount }}</td>
                <td>
                  <div class="sizes-cell">
                    <span v-for="size in parseSizes(costume.sizes)" :key="size" class="size-tag">{{
                      size
                    }}</span>
                  </div>
                </td>
                <td class="actions-cell">
                  <router-link :to="`/costume/${costume.id}`" class="action-btn view">
                    <i class="bi bi-eye"></i>
                  </router-link>
                  <button class="action-btn edit" @click="editCostume(costume.id)">
                    <i class="bi bi-pencil"></i>
                  </button>
                  <button class="action-btn delete" @click="deleteCostume(costume.id)">
                    <i class="bi bi-trash"></i>
                  </button>
                </td>
              </tr>
              <tr v-if="costumes.length === 0">
                <td colspan="8" class="empty-state">
                  <i class="bi bi-inbox"></i>
                  <p>No costumes found</p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination Controls -->
        <div class="pagination-controls" v-if="pagination.total_pages > 1">
          <button
            class="pagination-btn"
            :disabled="pagination.current_page === 1"
            @click="goToPage(pagination.current_page - 1)"
          >
            <i class="bi bi-chevron-left"></i>
            Previous
          </button>
          <div class="pagination-info">
            <span>Page {{ pagination.current_page }} of {{ pagination.total_pages }}</span>
            <span class="pagination-total">({{ pagination.total }} items)</span>
          </div>
          <button
            class="pagination-btn"
            :disabled="pagination.current_page === pagination.total_pages"
            @click="goToPage(pagination.current_page + 1)"
          >
            Next
            <i class="bi bi-chevron-right"></i>
          </button>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useCostumesStore } from '@/stores/costumes'

const router = useRouter()
const store = useCostumesStore()

const costumes = computed(() => store.costumes)
const pagination = computed(() => store.pagination)

// Search and filter state
const searchQuery = ref('')
const selectedCategory = ref('')
const currentPage = ref(1)

const sizeOptions = ['XS', 'S', 'M', 'L', 'XL']
const form = reactive({
  name: '',
  costume_code: '',
  category: '',
  amount: 1,
  description: '',
  image: '',
  available: true,
  sizes: [],
})

const errors = reactive({})
const loading = ref(false)
const successMessage = ref('')
const errorMessage = ref('')
const fileInput = ref(null)
const imageFile = ref(null)
const previewUrl = ref('')

const categories = computed(() => store.categories.filter((c) => c !== 'All'))
const previewImage = computed(() => (previewUrl.value ? previewUrl.value : form.image || ''))
const previewBackground = computed(() => 'linear-gradient(135deg, #0f0f1a, #1c2742)')
const imageFileName = computed(() => imageFile.value?.name || 'No file chosen')

function clearFile() {
  if (previewUrl.value) URL.revokeObjectURL(previewUrl.value)
  previewUrl.value = ''
  imageFile.value = null
  if (fileInput.value) fileInput.value.value = ''
}

function chooseFile() {
  if (fileInput.value) fileInput.value.click()
}

function onFileChange(event) {
  const file = event?.target?.files?.[0]
  if (!file) return
  if (previewUrl.value) URL.revokeObjectURL(previewUrl.value)
  imageFile.value = file
  previewUrl.value = URL.createObjectURL(file)
  form.image = ''
}

function imageError() {
  clearFile()
}

function validate() {
  errors.name = form.name ? '' : 'Name is required'
  errors.costume_code = form.costume_code ? '' : 'Code is required'
  errors.category = form.category ? '' : 'Category is required'
  errors.amount = form.amount >= 0 ? '' : 'Stock must be zero or more'
  errors.sizes = form.sizes.length ? '' : 'Pick at least one size'
  errors.description = form.description.length > 400 ? 'Keep it under 400 characters' : ''
  return Object.values(errors).every((v) => !v)
}

async function submitForm() {
  successMessage.value = ''
  errorMessage.value = ''
  if (!validate()) return

  const base = {
    name: form.name.trim(),
    costume_code: form.costume_code.trim(),
    category: form.category.trim(),
    amount: Number(form.amount) || 0,
    description: form.description.trim(),
    available: form.available ? 1 : 0,
    sizes: form.sizes,
  }

  const useFormData = imageFile.value && typeof FormData !== 'undefined'
  const payload = useFormData ? new FormData() : { ...base, image: form.image.trim() }

  if (useFormData) {
    Object.entries(base).forEach(([key, value]) => {
      if (key === 'sizes') {
        payload.append('sizes', JSON.stringify(value))
      } else {
        payload.append(key, value)
      }
    })
    payload.append('image', imageFile.value)
  }

  loading.value = true
  try {
    const created = await store.addCostume(payload)
    successMessage.value = '"' + created.name + '" added. Redirecting...'
    resetForm()
    setTimeout(() => router.push(`/costume/${created.id}`), 900)
  } catch (err) {
    errorMessage.value = err.message || 'Unable to save costume'
  } finally {
    loading.value = false
  }
}

function resetForm() {
  form.name = ''
  form.costume_code = ''
  form.category = ''
  form.amount = 1
  form.description = ''
  form.image = ''
  form.available = true
  form.sizes = []
  clearFile()
  Object.keys(errors).forEach((k) => (errors[k] = ''))
}

onMounted(async () => {
  fetchCostumesWithFilters()
})

onBeforeUnmount(() => {
  if (previewUrl.value) URL.revokeObjectURL(previewUrl.value)
})

function getImageUrl(image) {
  if (!image) return ''
  if (image.startsWith('http')) return image
  return `https://drive.google.com/thumbnail?id=${image}&sz=w1200`
}

function handleImageError(event) {
  event.target.style.display = 'none'
}

function parseSizes(sizes) {
  if (!sizes) return []
  if (Array.isArray(sizes)) return sizes
  try {
    return JSON.parse(sizes)
  } catch {
    return []
  }
}

function editCostume(id) {
  router.push(`/costume/${id}`)
}

async function deleteCostume(id) {
  if (!confirm('Are you sure you want to delete this costume?')) return
  try {
    await store.deleteCostume(id)
  } catch (err) {
    console.error('Failed to delete costume:', err)
  }
}

function handleSearch() {
  currentPage.value = 1
  fetchCostumesWithFilters()
}

function handleFilter() {
  currentPage.value = 1
  fetchCostumesWithFilters()
}

function goToPage(page) {
  if (page < 1 || page > pagination.value.total_pages) return
  currentPage.value = page
  fetchCostumesWithFilters()
}

function fetchCostumesWithFilters() {
  store.fetchCostumes({
    search: searchQuery.value,
    category: selectedCategory.value,
    page: currentPage.value,
  })
}
</script>

<style scoped>
:global(body) {
  background: #0b0c10;
}

.add-page {
  color: #0f1016;
  background: #0b0c10;
}

.hero {
  background: linear-gradient(135deg, #0f0f1a 0%, #1c2742 50%, #0f1016 100%);
  color: #f5f0e8;
  padding: 120px 0 72px;
}

.container {
  width: min(1180px, 92vw);
  margin: 0 auto;
}

.back-link {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  color: rgba(245, 240, 232, 0.7);
  text-decoration: none;
  font-size: 0.9rem;
}
.back-link:hover {
  color: #e7c060;
}

.eyebrow {
  letter-spacing: 0.2em;
  text-transform: uppercase;
  font-size: 0.7rem;
  color: rgba(231, 192, 96, 0.85);
  margin-top: 18px;
}

.hero h1 {
  font-family: 'Playfair Display', serif;
  font-size: clamp(2.2rem, 5vw, 3.4rem);
  margin: 12px 0 6px;
}

.lede {
  max-width: 600px;
  color: rgba(245, 240, 232, 0.75);
  line-height: 1.6;
}

.content {
  margin-top: -60px;
  padding-bottom: 64px;
}

.grid {
  display: grid;
  grid-template-columns: 3fr 2fr;
  gap: 24px;
}

.card {
  background: #fff;
  border-radius: 18px;
  box-shadow: 0 16px 50px rgba(0, 0, 0, 0.12);
  padding: 24px;
  border: 1px solid #e8e3d9;
}

.form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.section-title {
  font-weight: 700;
  color: #0f1016;
  margin-bottom: 10px;
  letter-spacing: 0.02em;
}

.fields {
  display: grid;
  gap: 16px;
}

.two-col {
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
}
.three-col {
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
}

.field {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.label {
  font-size: 0.9rem;
  color: #2a2b32;
  font-weight: 600;
}

input,
select,
textarea {
  width: 100%;
  border: 1px solid #dcd7ce;
  border-radius: 12px;
  padding: 12px 14px;
  font-size: 1rem;
  transition:
    border-color 0.2s,
    box-shadow 0.2s;
  background: #fbf9f6;
}

input:focus,
select:focus,
textarea:focus {
  outline: none;
  border-color: #e7c060;
  box-shadow: 0 0 0 3px rgba(231, 192, 96, 0.24);
  background: #fff;
}

textarea {
  resize: vertical;
}

.hint {
  color: #c24533;
  font-size: 0.85rem;
}

.chips {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.chip {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 12px;
  border-radius: 12px;
  border: 1px solid #e0d8cb;
  background: #fbf9f6;
  cursor: pointer;
  font-weight: 600;
  color: #1d1f29;
}

.chip input {
  width: 16px;
  height: 16px;
}

.toggle-field {
  justify-content: center;
}

.toggle {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  padding: 10px 12px;
  border: 1px solid #e0d8cb;
  border-radius: 12px;
  background: #fbf9f6;
}

.file-field .file-row {
  display: flex;
  align-items: center;
  gap: 12px;
}

.file-name {
  color: #6b6f7c;
  font-size: 0.9rem;
}

.hidden {
  display: none;
}

.ghost,
.primary {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  border-radius: 12px;
  font-weight: 700;
  padding: 12px 16px;
  text-decoration: none;
  cursor: pointer;
  border: 1px solid transparent;
}

.ghost {
  border-color: #dcd7ce;
  color: #2a2b32;
  background: #fff;
}
.ghost:hover {
  border-color: #c9bda5;
}

.primary {
  background: linear-gradient(120deg, #e7c060, #f4d58d);
  color: #0f1016;
  border: none;
  box-shadow: 0 12px 32px rgba(231, 192, 96, 0.35);
}
.primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  box-shadow: none;
}

.actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 4px;
}

.alert {
  border-radius: 12px;
  padding: 12px 14px;
  font-weight: 600;
}

.alert.success {
  background: #edf8ef;
  color: #1e7f4c;
  border: 1px solid #c7ebd5;
}

.alert.error {
  background: #fff2f0;
  color: #b5463b;
  border: 1px solid #f0c8c1;
}

.preview {
  padding: 0;
  overflow: hidden;
}

.preview-label {
  padding: 18px 20px 0;
  color: #6b6f7c;
  font-size: 0.85rem;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.preview-media {
  position: relative;
  padding: 18px;
  height: 240px;
  background-size: cover;
  background-position: center;
  display: grid;
  place-items: center;
}

.preview-media img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 14px;
  border: 1px solid rgba(0, 0, 0, 0.08);
  box-shadow: 0 14px 38px rgba(0, 0, 0, 0.18);
}

.preview-body {
  padding: 16px 20px 22px;
}

.preview-body h3 {
  margin: 6px 0;
  font-family: 'Playfair Display', serif;
}

.muted {
  color: #6b6f7c;
  font-size: 0.95rem;
}

.sizes {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
  margin: 10px 0;
}

.size-pill {
  padding: 6px 10px;
  border-radius: 10px;
  background: #f1ecdf;
  color: #2a2b32;
  font-weight: 700;
  font-size: 0.9rem;
}

.description {
  margin-top: 8px;
  line-height: 1.5;
}

.spinner {
  width: 16px;
  height: 16px;
  border-radius: 50%;
  border: 3px solid rgba(0, 0, 0, 0.15);
  border-top-color: #0f1016;
  animation: spin 0.7s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

@media (max-width: 1024px) {
  .grid {
    grid-template-columns: 1fr;
  }
  .content {
    margin-top: -40px;
  }
}

/* Table Section Styles */
.table-section {
  margin-top: 48px;
  background: #fff;
  border-radius: 18px;
  box-shadow: 0 16px 50px rgba(0, 0, 0, 0.12);
  border: 1px solid #e8e3d9;
  overflow: hidden;
}

.table-header {
  padding: 24px 24px 0;
  border-bottom: 1px solid #e8e3d9;
  margin-bottom: 0;
}

.table-header h2 {
  font-family: 'Playfair Display', serif;
  font-size: 1.8rem;
  color: #0f1016;
  margin: 0 0 4px;
}

.table-subtitle {
  color: #6b6f7c;
  font-size: 0.95rem;
  margin: 0 0 16px;
}

.table-container {
  overflow-x: auto;
}

.costumes-table {
  width: 100%;
  border-collapse: collapse;
}

.costumes-table thead {
  background: #f8f6f1;
}

.costumes-table th {
  padding: 14px 16px;
  text-align: left;
  font-weight: 700;
  color: #2a2b32;
  font-size: 0.85rem;
  letter-spacing: 0.03em;
  border-bottom: 2px solid #e8e3d9;
}

.costumes-table td {
  padding: 16px;
  border-bottom: 1px solid #f0ece4;
  vertical-align: middle;
}

.costumes-table tbody tr:hover {
  background: #faf8f4;
}

.image-cell {
  width: 80px;
}

.table-image {
  width: 60px;
  height: 60px;
  border-radius: 8px;
  overflow: hidden;
  background: #f0ece4;
  display: flex;
  align-items: center;
  justify-content: center;
}

.table-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.no-image {
  color: #c9bda5;
  font-size: 1.5rem;
}

.name-cell {
  font-weight: 600;
  color: #0f1016;
  max-width: 200px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.category-badge {
  display: inline-block;
  padding: 4px 10px;
  background: #f1ecdf;
  color: #2a2b32;
  border-radius: 6px;
  font-size: 0.85rem;
  font-weight: 600;
}

.sizes-cell {
  display: flex;
  gap: 6px;
  flex-wrap: wrap;
}

.size-tag {
  padding: 4px 8px;
  background: #e8e3d9;
  color: #2a2b32;
  border-radius: 4px;
  font-size: 0.8rem;
  font-weight: 600;
}

.status-badge {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 0.85rem;
  font-weight: 600;
}

.status-badge.available {
  background: #edf8ef;
  color: #1e7f4c;
}

.status-badge.hidden {
  background: #fff2f0;
  color: #b5463b;
}

.actions-cell {
  display: flex;
  gap: 8px;
}

.action-btn {
  width: 32px;
  height: 32px;
  border-radius: 6px;
  border: 1px solid #e8e3d9;
  background: #fff;
  color: #6b6f7c;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
  text-decoration: none;
}

.action-btn:hover {
  border-color: #c9bda5;
  color: #2a2b32;
}

.action-btn.view:hover {
  background: #e7f0ff;
  border-color: #a8c4e0;
  color: #2a6cb5;
}

.action-btn.edit:hover {
  background: #fff8e7;
  border-color: #e7c060;
  color: #b5892a;
}

.action-btn.delete:hover {
  background: #fff2f0;
  border-color: #f0c8c1;
  color: #b5463b;
}

.empty-state {
  text-align: center;
  padding: 48px 24px !important;
  color: #6b6f7c;
}

.empty-state i {
  font-size: 3rem;
  margin-bottom: 12px;
  display: block;
}

.empty-state p {
  font-size: 1rem;
  margin: 0;
}

/* Table Controls Styles */
.table-controls {
  display: flex;
  gap: 16px;
  padding: 20px 24px;
  border-bottom: 1px solid #e8e3d9;
  flex-wrap: wrap;
}

.search-box {
  flex: 1;
  min-width: 200px;
  position: relative;
}

.search-box i {
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  color: #6b6f7c;
  font-size: 1rem;
}

.search-box input {
  width: 100%;
  padding: 10px 14px 10px 40px;
  border: 1px solid #dcd7ce;
  border-radius: 10px;
  font-size: 0.95rem;
  background: #fbf9f6;
  transition:
    border-color 0.2s,
    box-shadow 0.2s;
}

.search-box input:focus {
  outline: none;
  border-color: #e7c060;
  box-shadow: 0 0 0 3px rgba(231, 192, 96, 0.24);
  background: #fff;
}

.filter-box select {
  padding: 10px 14px;
  border: 1px solid #dcd7ce;
  border-radius: 10px;
  font-size: 0.95rem;
  background: #fbf9f6;
  cursor: pointer;
  min-width: 180px;
  transition:
    border-color 0.2s,
    box-shadow 0.2s;
}

.filter-box select:focus {
  outline: none;
  border-color: #e7c060;
  box-shadow: 0 0 0 3px rgba(231, 192, 96, 0.24);
  background: #fff;
}

/* Pagination Styles */
.pagination-controls {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 24px;
  border-top: 1px solid #e8e3d9;
  flex-wrap: wrap;
  gap: 16px;
}

.pagination-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 16px;
  border: 1px solid #dcd7ce;
  border-radius: 10px;
  background: #fff;
  color: #2a2b32;
  font-weight: 600;
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.2s;
}

.pagination-btn:hover:not(:disabled) {
  border-color: #c9bda5;
  background: #faf8f4;
}

.pagination-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.pagination-info {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
}

.pagination-info span {
  color: #2a2b32;
  font-size: 0.9rem;
}

.pagination-total {
  color: #6b6f7c;
  font-size: 0.85rem;
}

@media (max-width: 768px) {
  .costumes-table th,
  .costumes-table td {
    padding: 12px 8px;
    font-size: 0.85rem;
  }

  .table-image {
    width: 50px;
    height: 50px;
  }

  .name-cell {
    max-width: 120px;
  }

  .actions-cell {
    flex-direction: column;
    gap: 4px;
  }
}
</style>
