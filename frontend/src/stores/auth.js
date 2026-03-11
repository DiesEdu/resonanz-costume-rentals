import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(JSON.parse(localStorage.getItem('auth_user') || 'null'))
  const token = ref(localStorage.getItem('auth_token') || null)
  const role = ref(localStorage.getItem('auth_role') || 'user')

  const isLoggedIn = computed(() => !!token.value)
  const userName = computed(() => user.value?.name || '')
  const userRole = computed(() => role.value || 'user')

  function login(userData, authToken) {
    user.value = userData
    token.value = authToken
    role.value = userData.role || 'user'
    localStorage.setItem('auth_user', JSON.stringify(userData))
    localStorage.setItem('auth_token', authToken)
    localStorage.setItem('auth_role', role.value)
  }

  function logout() {
    user.value = null
    token.value = null
    role.value = 'user'
    localStorage.removeItem('auth_user')
    localStorage.removeItem('auth_token')
    localStorage.removeItem('auth_role')
  }

  return { user, token, role, isLoggedIn, userName, userRole, login, logout }
})
