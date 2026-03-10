import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(JSON.parse(localStorage.getItem('auth_user') || 'null'))
  const token = ref(localStorage.getItem('auth_token') || null)

  const isLoggedIn = computed(() => !!token.value)
  const userName = computed(() => user.value?.name || '')

  function login(userData, authToken) {
    user.value = userData
    token.value = authToken
    localStorage.setItem('auth_user', JSON.stringify(userData))
    localStorage.setItem('auth_token', authToken)
  }

  function logout() {
    user.value = null
    token.value = null
    localStorage.removeItem('auth_user')
    localStorage.removeItem('auth_token')
  }

  return { user, token, isLoggedIn, userName, login, logout }
})
