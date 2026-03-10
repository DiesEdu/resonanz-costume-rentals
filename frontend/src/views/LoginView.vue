<template>
  <div class="auth-page">
    <div class="auth-bg"></div>
    <div
      class="container d-flex justify-content-center align-items-center"
      style="min-height: 100vh; padding-top: 80px"
    >
      <div class="auth-card reveal" :class="{ visible: mounted }">
        <!-- Logo / Brand -->
        <div class="auth-brand text-center mb-4">
          <i class="bi bi-gem auth-gem"></i>
          <h2 class="auth-title mt-2">Welcome Back</h2>
          <p class="auth-subtitle">Sign in to your CostumeRent account</p>
        </div>

        <!-- Alert -->
        <div v-if="error" class="auth-alert mb-3">
          <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ error }}
        </div>
        <div v-if="success" class="auth-alert auth-alert-success mb-3">
          <i class="bi bi-check-circle-fill me-2"></i>{{ success }}
        </div>

        <!-- Form -->
        <form @submit.prevent="handleLogin" novalidate>
          <div class="auth-field mb-3">
            <label class="auth-label">Email Address</label>
            <div class="input-icon-wrap">
              <i class="bi bi-envelope input-icon"></i>
              <input
                v-model="form.email"
                type="email"
                class="auth-input"
                :class="{ 'is-invalid': v$.email.$error }"
                placeholder="you@example.com"
                autocomplete="email"
              />
            </div>
            <span v-if="v$.email.$error" class="field-error">{{
              v$.email.$errors[0].$message
            }}</span>
          </div>

          <div class="auth-field mb-4">
            <label class="auth-label">Password</label>
            <div class="input-icon-wrap">
              <i class="bi bi-lock input-icon"></i>
              <input
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                class="auth-input"
                :class="{ 'is-invalid': v$.password.$error }"
                placeholder="Enter your password"
                autocomplete="current-password"
              />
              <button
                type="button"
                class="toggle-password"
                @click="showPassword = !showPassword"
                tabindex="-1"
              >
                <i :class="showPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
              </button>
            </div>
            <span v-if="v$.password.$error" class="field-error">{{
              v$.password.$errors[0].$message
            }}</span>
          </div>

          <div class="d-flex justify-content-between align-items-center mb-4">
            <label class="auth-check-label">
              <input v-model="form.remember" type="checkbox" class="auth-check" />
              <span>Remember me</span>
            </label>
            <a href="#" class="auth-link">Forgot password?</a>
          </div>

          <button type="submit" class="btn-auth w-100" :disabled="loading">
            <span v-if="loading" class="spinner-border spinner-border-sm me-2" role="status"></span>
            <i v-else class="bi bi-box-arrow-in-right me-2"></i>
            {{ loading ? 'Signing in…' : 'Sign In' }}
          </button>
        </form>

        <div class="auth-divider my-4"><span>or</span></div>

        <p class="text-center auth-footer-text mb-0">
          Don't have an account?
          <router-link to="/register" class="auth-link fw-semibold">Create one</router-link>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useVuelidate } from '@vuelidate/core'
import { required, email, minLength } from '@vuelidate/validators'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()
const mounted = ref(false)
const loading = ref(false)
const error = ref('')
const success = ref('')
const showPassword = ref(false)

const form = reactive({ email: '', password: '', remember: false })

const rules = {
  email: { required, email },
  password: { required, minLength: minLength(6) },
}

const v$ = useVuelidate(rules, form)

onMounted(() => setTimeout(() => (mounted.value = true), 50))

const API_BASE = import.meta.env.VITE_API_BASE || ''

async function handleLogin() {
  error.value = ''
  const isValid = await v$.value.$validate()
  if (!isValid) return

  loading.value = true
  try {
    const res = await fetch(`${API_BASE}/api/auth/login`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email: form.email, password: form.password }),
    })
    const data = await res.json()
    if (!res.ok) throw new Error(data.error || 'Login failed.')

    authStore.login(data.user, data.token)
    success.value = 'Login successful! Redirecting…'
    setTimeout(() => router.push('/'), 700)
  } catch (err) {
    error.value = err.message || 'Invalid email or password. Please try again.'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
/* ── Page layout ───────────────────────────────── */
.auth-page {
  position: relative;
  background: var(--charcoal);
  min-height: 100vh;
  overflow: hidden;
}

.auth-bg {
  position: fixed;
  inset: 0;
  background:
    radial-gradient(ellipse 70% 60% at 20% 30%, rgba(201, 168, 76, 0.12) 0%, transparent 60%),
    radial-gradient(ellipse 60% 50% at 80% 70%, rgba(114, 47, 55, 0.18) 0%, transparent 60%),
    var(--charcoal);
  pointer-events: none;
}

/* ── Card ──────────────────────────────────────── */
.auth-card {
  position: relative;
  z-index: 2;
  background: rgba(26, 26, 46, 0.85);
  border: 1px solid rgba(201, 168, 76, 0.2);
  border-radius: 2px;
  padding: 2.5rem 2.5rem 2rem;
  width: 100%;
  max-width: 440px;
  backdrop-filter: blur(12px);
  box-shadow: 0 25px 60px rgba(0, 0, 0, 0.5);
  opacity: 0;
  transform: translateY(30px);
  transition:
    opacity 0.7s ease,
    transform 0.7s ease;
}
.auth-card.visible {
  opacity: 1;
  transform: translateY(0);
}

/* ── Brand ─────────────────────────────────────── */
.auth-gem {
  font-size: 2.4rem;
  color: var(--gold);
  filter: drop-shadow(0 0 10px rgba(201, 168, 76, 0.5));
}
.auth-title {
  color: var(--cream);
  font-size: 1.7rem;
  margin-bottom: 0.25rem;
}
.auth-subtitle {
  color: var(--text-muted);
  font-size: 0.88rem;
  margin-bottom: 0;
}

/* ── Alert ─────────────────────────────────────── */
.auth-alert {
  background: rgba(114, 47, 55, 0.25);
  border: 1px solid rgba(114, 47, 55, 0.5);
  color: #f8adb3;
  border-radius: 2px;
  padding: 0.65rem 1rem;
  font-size: 0.875rem;
}
.auth-alert-success {
  background: rgba(40, 120, 80, 0.2);
  border-color: rgba(60, 180, 100, 0.4);
  color: #7ddfab;
}

/* ── Labels / fields ───────────────────────────── */
.auth-label {
  display: block;
  color: var(--text-light);
  font-size: 0.82rem;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  margin-bottom: 0.4rem;
  font-family: 'Lato', sans-serif;
}

.input-icon-wrap {
  position: relative;
}
.input-icon {
  position: absolute;
  left: 0.9rem;
  top: 50%;
  transform: translateY(-50%);
  color: var(--gold);
  font-size: 0.9rem;
  pointer-events: none;
}
.auth-input {
  width: 100%;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(201, 168, 76, 0.2);
  border-radius: 2px;
  color: var(--cream);
  padding: 0.7rem 2.5rem 0.7rem 2.4rem;
  font-size: 0.95rem;
  transition:
    border-color 0.2s,
    background 0.2s;
  outline: none;
}
.auth-input::placeholder {
  color: var(--text-muted);
}
.auth-input:focus {
  background: rgba(255, 255, 255, 0.07);
  border-color: var(--gold);
  box-shadow: 0 0 0 3px rgba(201, 168, 76, 0.12);
}
.auth-input.is-invalid {
  border-color: #c0392b;
}
.toggle-password {
  position: absolute;
  right: 0.8rem;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: var(--text-muted);
  cursor: pointer;
  padding: 0;
  line-height: 1;
  transition: color 0.2s;
}
.toggle-password:hover {
  color: var(--gold);
}

.field-error {
  display: block;
  color: #f8adb3;
  font-size: 0.78rem;
  margin-top: 0.3rem;
}

/* ── Remember / link ───────────────────────────── */
.auth-check-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: var(--text-muted);
  font-size: 0.85rem;
  cursor: pointer;
}
.auth-check {
  accent-color: var(--gold);
  width: 15px;
  height: 15px;
}
.auth-link {
  color: var(--gold);
  font-size: 0.85rem;
  text-decoration: none;
  transition: color 0.2s;
}
.auth-link:hover {
  color: var(--gold-light);
}

/* ── CTA button ────────────────────────────────── */
.btn-auth {
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, var(--gold) 0%, var(--gold-light) 100%);
  color: var(--charcoal);
  border: none;
  border-radius: 2px;
  padding: 0.78rem 1.5rem;
  font-weight: 700;
  font-size: 0.95rem;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  cursor: pointer;
  transition:
    opacity 0.2s,
    transform 0.2s,
    box-shadow 0.2s;
  box-shadow: 0 4px 20px rgba(201, 168, 76, 0.35);
}
.btn-auth:hover:not(:disabled) {
  opacity: 0.9;
  transform: translateY(-1px);
  box-shadow: 0 6px 25px rgba(201, 168, 76, 0.45);
}
.btn-auth:disabled {
  opacity: 0.65;
  cursor: not-allowed;
}

/* ── Divider ───────────────────────────────────── */
.auth-divider {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  color: var(--text-muted);
  font-size: 0.8rem;
}
.auth-divider::before,
.auth-divider::after {
  content: '';
  flex: 1;
  height: 1px;
  background: rgba(201, 168, 76, 0.15);
}

.auth-footer-text {
  color: var(--text-muted);
  font-size: 0.88rem;
}
</style>
