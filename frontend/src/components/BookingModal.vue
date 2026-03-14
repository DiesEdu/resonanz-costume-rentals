<template>
  <div>
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1100">
      <div
        ref="toastRef"
        class="toast align-items-center text-white border-0 shadow-lg"
        :class="`bg-${toastVariant}`"
        role="alert"
        aria-live="assertive"
        aria-atomic="true"
      >
        <div class="d-flex">
          <div class="toast-body d-flex align-items-center gap-2">
            <i :class="`bi ${toastIcon} fs-5`"></i>
            <span>{{ toastMessage }}</span>
          </div>
          <button
            type="button"
            class="btn-close btn-close-white me-2 m-auto"
            data-bs-dismiss="toast"
          ></button>
        </div>
      </div>
    </div>

    <div class="modal fade" id="bookingModal" tabindex="-1" ref="modalRef">
      <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
          <div
            class="modal-header bg-primary text-white border-0"
            style="background: var(--charcoal-2) !important"
          >
            <h5 class="modal-title fw-bold" style="color: var(--gold)">
              <i class="bi bi-calendar-check me-2" style="color: var(--gold)"></i>Book Costume
            </h5>
            <button
              type="button"
              class="btn-close btn-close-white"
              data-bs-dismiss="modal"
            ></button>
          </div>
          <div class="modal-body p-4">
            <div v-if="costume" class="row">
              <div class="col-md-5 mb-3 mb-md-0">
                <LazyDriveImage v-if="imageUrl" :fileId="imageUrl" :alt="costume.name" />
                <h5 class="mt-3 fw-bold">{{ costume.name }}</h5>
              </div>
              <div class="col-md-7">
                <form @submit.prevent="submitBooking">
                  <div class="mb-3">
                    <label class="form-label fw-bold">Select Size</label>
                    <div class="d-flex gap-2 flex-wrap">
                      <button
                        type="button"
                        class="btn"
                        :class="'btn-primary'"
                        @click="selectedSize = costume.size"
                      >
                        {{ costume.size }}
                      </button>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-6 mb-3">
                      <label class="form-label fw-bold">Start Date</label>
                      <input
                        type="date"
                        class="form-control"
                        v-model="startDate"
                        :min="minDate"
                        required
                      />
                    </div>
                    <div class="col-md-6 mb-3">
                      <label class="form-label fw-bold">End Date</label>
                      <input
                        type="date"
                        class="form-control"
                        v-model="endDate"
                        :min="startDate || minDate"
                        required
                      />
                    </div>
                    <div class="row">
                      <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Amount</label>
                        <div class="input-group">
                          <input
                            type="number"
                            class="form-control"
                            v-model.number="amount"
                            :min="1"
                            :max="availableAmount"
                            :disabled="availableAmount === 0"
                            required
                          />
                          <span class="input-group-text bg-light">
                            Remainder: {{ remainingAmount }}
                          </span>
                        </div>
                        <small class="text-muted"> Max {{ availableAmount }} available </small>
                      </div>
                    </div>
                  </div>

                  <div
                    v-if="totalDays > 0"
                    class="alert alert-info d-flex justify-content-between align-items-center"
                  >
                    <span>For {{ totalDays }} days</span>
                  </div>

                  <button
                    type="submit"
                    class="btn btn-primary w-100 btn-lg rounded-pill"
                    :disabled="!isValid"
                  >
                    <i class="bi bi-check-circle me-2"></i>Confirm Booking
                  </button>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted } from 'vue'
import { Modal, Toast } from 'bootstrap'
import { useBookingsStore } from '@/stores/bookings'
import { useCostumesStore } from '@/stores/costumes'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'
import LazyDriveImage from '@/components/LazyDriveImage.vue'

const props = defineProps({
  costume: {
    type: Object,
    default: null,
  },
})

const emit = defineEmits(['booked'])

const bookingsStore = useBookingsStore()
const costumesStore = useCostumesStore()
const authStore = useAuthStore()
const router = useRouter()
const modalRef = ref(null)
let modalInstance = null

const toastRef = ref(null)
let toastInstance = null
const toastMessage = ref('')
const toastVariant = ref('primary')
const toastIcon = ref('bi-info-circle')

const selectedSize = ref('')
const startDate = ref('')
const endDate = ref('')
const amount = ref(1)

const imageUrl = ref(null)

const minDate = new Date().toISOString().split('T')[0]

const totalDays = computed(() => {
  if (!startDate.value || !endDate.value) return 0
  const start = new Date(startDate.value)
  const end = new Date(endDate.value)
  const diff = Math.ceil((end - start) / (1000 * 60 * 60 * 24))
  return diff > 0 ? diff : 0
})

const availableAmount = computed(() => Number(props.costume?.quantity ?? 0))
const remainingAmount = computed(() =>
  Math.max(availableAmount.value - Number(amount.value || 0), 0),
)

const isValid = computed(() => {
  return (
    selectedSize.value &&
    startDate.value &&
    endDate.value &&
    totalDays.value > 0 &&
    amount.value >= 1 &&
    amount.value <= availableAmount.value &&
    availableAmount.value >= 0
  )
})

onMounted(async () => {
  imageUrl.value = await costumesStore.getDriveImageUrl(props.costume.image)
  selectedSize.value = props.costume.size
})

watch(
  () => props.costume,
  (newCostume) => {
    if (newCostume) {
      selectedSize.value = newCostume.size[0]
      amount.value = 1
    }
  },
)

watch(amount, (val) => {
  const numeric = Number(val || 0)
  if (numeric < 1) amount.value = 1
  if (numeric > availableAmount.value) amount.value = availableAmount.value
})

const show = () => {
  if (!modalInstance) {
    modalInstance = new Modal(modalRef.value)
  }
  modalInstance.show()
}

const hide = () => {
  modalInstance?.hide()
}

const submitBooking = () => {
  if (!authStore.isLoggedIn) {
    showToast('Please sign in to continue your booking.', 'warning', 'bi-person-exclamation')
    setTimeout(() => router.push('/login'), 800)
    return
  }

  const booking = {
    costumeId: props.costume.id,
    customerId: authStore.user?.id || null,
    startDate: startDate.value,
    endDate: endDate.value,
    size: selectedSize.value,
    amount: amount.value,
  }

  bookingsStore.addBooking(booking)
  showToast('Booking confirmed! We reserved this costume for you.', 'success', 'bi-check2-circle')

  hide()
  emit('booked')

  // Reset form
  startDate.value = ''
  endDate.value = ''
}

const showToast = (message, variant = 'primary', icon = 'bi-info-circle') => {
  toastMessage.value = message
  toastVariant.value = variant
  toastIcon.value = icon

  nextTick(() => {
    if (!toastInstance && toastRef.value) {
      toastInstance = new Toast(toastRef.value, { delay: 2400 })
    }
    toastInstance?.show()
  })
}

defineExpose({ show, hide })
</script>
