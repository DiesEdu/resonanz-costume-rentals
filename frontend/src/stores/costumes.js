import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

const API_BASE = import.meta.env.VITE_API_BASE ?? 'http://localhost:8000/costume-rental/backend'

export const useCostumesStore = defineStore('costumes', () => {
  const costumes = ref([])
  const loading = ref(false)
  const error = ref(null)
  const pagination = ref({
    current_page: 1,
    per_page: 8,
    total: 0,
    total_pages: 1,
  })

  // cache for filename → driveId
  const driveIdCache = ref({})

  const token = localStorage.getItem('auth_token')

  // ── Fetch all costumes ─────────────────────────────────────────────────────
  const fetchCostumes = async ({ category = '', search = '', page = 1, perPage = pagination.value.per_page } = {}) => {
    loading.value = true
    error.value = null

    try {
      const params = new URLSearchParams()
      if (category && category !== 'All') params.set('category', category)
      if (search) params.set('search', search)
      params.set('page', page)
      params.set('per_page', perPage)

      const qs = params.toString()

      const res = await fetch(`${API_BASE}/api/costumes${qs ? '?' + qs : ''}`, {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      })

      if (!res.ok) throw new Error(`HTTP ${res.status}`)

      const json = await res.json()
      costumes.value = json.data ?? []
      pagination.value = {
        current_page: json.pagination?.current_page ?? page,
        per_page: json.pagination?.per_page ?? perPage,
        total: json.pagination?.total ?? costumes.value.length,
        total_pages: Math.max(json.pagination?.total_pages ?? 1, 1),
      }
    } catch (err) {
      error.value = err.message
    } finally {
      loading.value = false
    }
  }

  // ── Categories ─────────────────────────────────────────────────────────────
  const categories = ref(['All', 'BMS', 'JCO', 'TRCC', 'ARMONIA', 'TRMS'])

  // ── Get single costume ─────────────────────────────────────────────────────
  const getCostumeById = async (id) => {
    const cached = costumes.value.find((c) => c.id === Number(id))
    if (cached) return cached

    try {
      const res = await fetch(`${API_BASE}/api/costumes/${id}`, {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      })

      if (!res.ok) throw new Error(`HTTP ${res.status}`)

      const json = await res.json()
      return json.data
    } catch (err) {
      console.error('Failed to fetch costume:', err)
      return null
    }
  }

  // ── Filters ────────────────────────────────────────────────────────────────
  const getCostumesByCategory = computed(() => (category) => {
    if (category === 'All') return costumes.value
    return costumes.value.filter((c) => c.category === category)
  })

  const searchCostumes = computed(() => (query) => {
    const lowerQuery = query.toLowerCase()

    return costumes.value.filter(
      (c) =>
        c.name.toLowerCase().includes(lowerQuery) ||
        c.category.toLowerCase().includes(lowerQuery) ||
        c.description.toLowerCase().includes(lowerQuery),
    )
  })

  // ── Add new costume ────────────────────────────────────────────────────────
  const addCostume = async (costumeData) => {
    loading.value = true
    error.value = null

    try {
      const isFormData = typeof FormData !== 'undefined' && costumeData instanceof FormData

      const res = await fetch(`${API_BASE}/api/costumes`, {
        method: 'POST',
        headers: {
          Authorization: `Bearer ${token}`,
          ...(isFormData ? {} : { 'Content-Type': 'application/json' }),
        },
        body: isFormData ? costumeData : JSON.stringify(costumeData),
      })

      const json = await res.json()

      if (!res.ok) throw new Error(json.error || `HTTP ${res.status}`)

      costumes.value.push(json.data)

      return json.data
    } catch (err) {
      error.value = err.message
      throw err
    } finally {
      loading.value = false
    }
  }

  // ── Resolve Google Drive fileId from filename (with cache) ─────────────────
  const idDriveFile = async (fileName) => {
    if (!fileName) return null

    // already cached
    if (driveIdCache.value[fileName]) {
      return driveIdCache.value[fileName]
    }

    try {
      const res = await fetch(`${API_BASE}/api/drive/files?name=${fileName}`)
      const json = await res.json()

      const fileId = json.files?.[0]?.id || null

      // save to cache
      driveIdCache.value[fileName] = fileId

      return fileId
    } catch (err) {
      console.error('Drive API error:', err)
      return null
    }
  }

  // ── Helper to get image URL directly ───────────────────────────────────────
  const getDriveImageUrl = async (fileName) => {
    const id = await idDriveFile(fileName)

    if (!id) return null

    console.log('id', id)
    //drive.google.com/thumbnail?id=${fileId}&sz=w1200

    return `https://drive.google.com/thumbnail?id=${id}&sz=w1200`
  }

  return {
    costumes,
    categories,
    loading,
    error,
    pagination,
    driveIdCache,

    fetchCostumes,
    getCostumeById,
    getCostumesByCategory,
    searchCostumes,
    addCostume,

    idDriveFile,
    getDriveImageUrl,
  }
})
