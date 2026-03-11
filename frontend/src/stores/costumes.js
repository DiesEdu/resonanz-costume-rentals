import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

const API_BASE = import.meta.env.VITE_API_BASE ?? 'http://localhost:8000/costume-rental/backend'

export const useCostumesStore = defineStore('costumes', () => {
  const costumes = ref([])
  const loading = ref(false)
  const error = ref(null)

  // ── Fetch all costumes (optional filters) ──────────────────────────────────
  const fetchCostumes = async ({ category = '', search = '' } = {}) => {
    loading.value = true
    error.value = null
    try {
      const params = new URLSearchParams()
      if (category && category !== 'All') params.set('category', category)
      if (search) params.set('search', search)

      const qs = params.toString()
      const res = await fetch(`${API_BASE}/api/costumes${qs ? '?' + qs : ''}`)
      if (!res.ok) throw new Error(`HTTP ${res.status}`)
      const json = await res.json()
      costumes.value = json.data
    } catch (err) {
      error.value = err.message
    } finally {
      loading.value = false
    }
  }

  // ── Fetch categories ───────────────────────────────────────────────────────
  const categories = ref(['All', 'BMS', 'JCO', 'TRCC', 'ARMONIA', 'TRMS'])

  // ── Get single costume by id (from cache or API) ──────────────────────────
  const getCostumeById = async (id) => {
    const cached = costumes.value.find((c) => c.id === Number(id))
    if (cached) return cached

    try {
      const res = await fetch(`${API_BASE}/api/costumes/${id}`)
      if (!res.ok) throw new Error(`HTTP ${res.status}`)
      const json = await res.json()
      return json.data
    } catch (err) {
      console.error('Failed to fetch costume:', err)
      return null
    }
  }

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
        headers: isFormData ? undefined : { 'Content-Type': 'application/json' },
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

  return {
    costumes,
    categories,
    loading,
    error,
    fetchCostumes,
    getCostumeById,
    getCostumesByCategory,
    searchCostumes,
    addCostume,
  }
})
