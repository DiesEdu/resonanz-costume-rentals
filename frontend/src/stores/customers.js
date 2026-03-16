import { defineStore } from 'pinia'
import { ref } from 'vue'

const API_BASE = import.meta.env.VITE_API_BASE ?? 'http://localhost:8000/costume-rental/backend'

export const useCustomersStore = defineStore('customers', () => {
  const customers = ref([])

  const fetchCustomers = async () => {
    try {
      const response = await fetch(`${API_BASE}/api/customers`, {
        headers: {
          Authorization: `Bearer ${localStorage.getItem('auth_token')}`,
        },
      })

      if (!response.ok) throw new Error(`HTTP ${response.status}`)

      const data = await response.json()
      console.log('customers data: ', data)
      customers.value = data
    } catch (err) {
      console.error('Failed to fetch customers:', err)
    }
  }

  return {
    customers,
    fetchCustomers,
  }
})
