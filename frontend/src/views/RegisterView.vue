<template>
  <div class="auth-page">
    <div class="auth-bg"></div>
    <div
      class="container d-flex justify-content-center align-items-center"
      style="min-height: 100vh; padding-top: 80px; padding-bottom: 2rem"
    >
      <div class="auth-card reveal" :class="{ visible: mounted }">
        <!-- Brand -->
        <div class="auth-brand text-center mb-4">
          <i class="bi bi-gem auth-gem"></i>
          <h2 class="auth-title mt-2">Create Account</h2>
          <p class="auth-subtitle">Join CostumeRent and discover premium costumes</p>
        </div>

        <!-- Alert -->
        <div v-if="error" class="auth-alert mb-3">
          <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ error }}
        </div>
        <div v-if="success" class="auth-alert auth-alert-success mb-3">
          <i class="bi bi-check-circle-fill me-2"></i>{{ success }}
        </div>

        <!-- Form -->
        <form @submit.prevent="handleRegister" novalidate>
          <!-- Full Name -->
          <div class="auth-field mb-3">
            <label class="auth-label">Full Name</label>
            <div class="input-icon-wrap">
              <i class="bi bi-person input-icon"></i>
              <input
                v-model="form.name"
                type="text"
                class="auth-input"
                :class="{ 'is-invalid': v$.name.$error }"
                placeholder="Your full name"
                autocomplete="name"
              />
            </div>
            <span v-if="v$.name.$error" class="field-error">{{ v$.name.$errors[0].$message }}</span>
          </div>

          <!-- Email -->
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

          <!-- Phone -->
          <div class="auth-field mb-3">
            <label class="auth-label">Phone Number</label>
            <div class="input-icon-wrap">
              <i class="bi bi-telephone input-icon"></i>
              <input
                v-model="form.phone"
                type="tel"
                class="auth-input"
                :class="{ 'is-invalid': v$.phone.$error }"
                placeholder="+62 8xx xxxx xxxx"
                autocomplete="tel"
              />
            </div>
            <span v-if="v$.phone.$error" class="field-error">{{
              v$.phone.$errors[0].$message
            }}</span>
          </div>

          <!-- Password -->
          <div class="auth-field mb-3">
            <label class="auth-label">Password</label>
            <div class="input-icon-wrap">
              <i class="bi bi-lock input-icon"></i>
              <input
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                class="auth-input"
                :class="{ 'is-invalid': v$.password.$error }"
                placeholder="Min. 8 characters"
                autocomplete="new-password"
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
            <!-- Strength bar -->
            <div class="strength-bar mt-2" v-if="form.password">
              <div
                class="strength-fill"
                :style="{ width: strengthPercent + '%' }"
                :class="strengthClass"
              ></div>
            </div>
            <span class="strength-label" v-if="form.password">{{ strengthLabel }}</span>
          </div>

          <!-- Confirm Password -->
          <div class="auth-field mb-4">
            <label class="auth-label">Confirm Password</label>
            <div class="input-icon-wrap">
              <i class="bi bi-lock-fill input-icon"></i>
              <input
                v-model="form.confirmPassword"
                :type="showConfirm ? 'text' : 'password'"
                class="auth-input"
                :class="{ 'is-invalid': v$.confirmPassword.$error }"
                placeholder="Repeat your password"
                autocomplete="new-password"
              />
              <button
                type="button"
                class="toggle-password"
                @click="showConfirm = !showConfirm"
                tabindex="-1"
              >
                <i :class="showConfirm ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
              </button>
            </div>
            <span v-if="v$.confirmPassword.$error" class="field-error">{{
              v$.confirmPassword.$errors[0].$message
            }}</span>
          </div>

          <!-- Terms -->
          <div class="mb-4">
            <label class="auth-check-label">
              <input
                v-model="form.terms"
                type="checkbox"
                class="auth-check"
                :class="{ 'is-invalid': v$.terms.$error }"
              />
              <span
                >I agree to the <a href="#" class="auth-link">Terms of Service</a> &
                <a href="#" class="auth-link">Privacy Policy</a></span
              >
            </label>
            <span v-if="v$.terms.$error" class="field-error d-block mt-1">{{
              v$.terms.$errors[0].$message
            }}</span>
          </div>

          <button type="submit" class="btn-auth w-100" :disabled="loading">
            <span v-if="loading" class="spinner-border spinner-border-sm me-2" role="status"></span>
            <i v-else class="bi bi-person-plus-fill me-2"></i>
            {{ loading ? 'Creating account…' : 'Create Account' }}
          </button>
        </form>

        <div class="auth-divider my-4"><span>or</span></div>

        <p class="text-center auth-footer-text mb-0">
          Already have an account?
          <router-link to="/login" class="auth-link fw-semibold">Sign in</router-link>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useVuelidate } from '@vuelidate/core'
import { required, email, minLength, sameAs, helpers } from '@vuelidate/validators'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()
const mounted = ref(false)
const loading = ref(false)
const error = ref('')
const success = ref('')
const showPassword = ref(false)
const showConfirm = ref(false)

const form = reactive({
  name: '',
  email: '',
  phone: '',
  password: '',
  confirmPassword: '',
  terms: false,
})

const mustBeTrue = helpers.withMessage('You must accept the terms', (val) => val === true)

const rules = computed(() => ({
  name: { required, minLength: minLength(2) },
  email: { required, email },
  phone: { required },
  password: { required, minLength: minLength(8) },
  confirmPassword: {
    required,
    sameAs: helpers.withMessage('Passwords do not match', sameAs(computed(() => form.password))),
  },
  terms: { mustBeTrue },
}))

const v$ = useVuelidate(rules, form)

// Password strength
const strengthPercent = computed(() => {
  const p = form.password
  if (!p) return 0
  let score = 0
  if (p.length >= 8) score += 25
  if (p.length >= 12) score += 15
  if (/[A-Z]/.test(p)) score += 20
  if (/[0-9]/.test(p)) score += 20
  if (/[^A-Za-z0-9]/.test(p)) score += 20
  return Math.min(score, 100)
})

const strengthLabel = computed(() => {
  const s = strengthPercent.value
  if (s < 40) return 'Weak'
  if (s < 70) return 'Fair'
  if (s < 90) return 'Strong'
  return 'Very Strong'
})

const strengthClass = computed(() => {
  const s = strengthPercent.value
  if (s < 40) return 'weak'
  if (s < 70) return 'fair'
  if (s < 90) return 'strong'
  return 'very-strong'
})

onMounted(() => setTimeout(() => (mounted.value = true), 50))

const API_BASE = import.meta.env.VITE_API_BASE || ''

async function handleRegister() {
  error.value = ''
  const isValid = await v$.value.$validate()
  if (!isValid) return

  loading.value = true
  try {
    const res = await fetch(`${API_BASE}/api/auth/register`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        name: form.name,
        email: form.email,
        phone: form.phone,
        password: form.password,
      }),
    })
    const data = await res.json()
    if (!res.ok) throw new Error(data.error || 'Registration failed.')

    authStore.login(data.user, data.token)
    success.value = 'Account created! Redirecting…'
    setTimeout(() => router.push('/'), 900)
  } catch (err) {
    error.value = err?.message || 'Registration failed. Please try again.'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
/* ── Page ──────────────────────────────────────── */
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
    radial-gradient(ellipse 70% 60% at 80% 20%, rgba(201, 168, 76, 0.12) 0%, transparent 60%),
    radial-gradient(ellipse 60% 50% at 20% 80%, rgba(114, 47, 55, 0.18) 0%, transparent 60%),
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
  max-width: 460px;
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

/* ── Strength bar ──────────────────────────────── */
.strength-bar {
  height: 4px;
  background: rgba(255, 255, 255, 0.08);
  border-radius: 2px;
  overflow: hidden;
}
.strength-fill {
  height: 100%;
  border-radius: 2px;
  transition:
    width 0.3s ease,
    background 0.3s;
}
.strength-fill.weak {
  background: #c0392b;
}
.strength-fill.fair {
  background: #e67e22;
}
.strength-fill.strong {
  background: #27ae60;
}
.strength-fill.very-strong {
  background: var(--gold);
}
.strength-label {
  font-size: 0.75rem;
  color: var(--text-muted);
}

/* ── Check / link ──────────────────────────────── */
.auth-check-label {
  display: flex;
  align-items: flex-start;
  gap: 0.5rem;
  color: var(--text-muted);
  font-size: 0.85rem;
  cursor: pointer;
}
.auth-check {
  accent-color: var(--gold);
  width: 15px;
  height: 15px;
  flex-shrink: 0;
  margin-top: 2px;
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

/* ── CTA ───────────────────────────────────────── */
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
