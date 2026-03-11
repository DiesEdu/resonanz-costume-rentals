<template>
  <div class="modal fade" id="bookingModal" tabindex="-1" ref="modalRef">
    <div class="modal-dialog modal-lg">
      <div class="modal-content border-0 shadow">
        <div class="modal-header bg-primary text-white border-0">
          <h5 class="modal-title fw-bold"><i class="bi bi-calendar-check me-2"></i>Book Costume</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4">
          <div v-if="costume" class="row">
            <div class="col-md-5 mb-3 mb-md-0">
              <img :src="costume.image" class="img-fluid rounded-3 shadow-sm" :alt="costume.name" />
              <h5 class="mt-3 fw-bold">{{ costume.name }}</h5>
            </div>
            <div class="col-md-7">
              <form @submit.prevent="submitBooking">
                <div class="mb-3">
                  <label class="form-label fw-bold">Select Size</label>
                  <div class="d-flex gap-2 flex-wrap">
                    <button
                      v-for="size in costume.size"
                      :key="size"
                      type="button"
                      class="btn"
                      :class="selectedSize === size ? 'btn-primary' : 'btn-outline-secondary'"
                      @click="selectedSize = size"
                    >
                      {{ size }}
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
                </div>

                <div
                  v-if="totalDays > 0"
                  class="alert alert-info d-flex justify-content-between align-items-center"
                >
                  <span>Total for {{ totalDays }} days:</span>
                  <span class="fw-bold fs-5">${{ totalPrice }}</span>
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
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { Modal } from 'bootstrap'
import { useBookingsStore } from '@/stores/bookings'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'

const props = defineProps({
  costume: {
    type: Object,
    default: null,
  },
})

const emit = defineEmits(['booked'])

const bookingsStore = useBookingsStore()
const authStore = useAuthStore()
const router = useRouter()
const modalRef = ref(null)
let modalInstance = null

const selectedSize = ref('')
const startDate = ref('')
const endDate = ref('')

const minDate = new Date().toISOString().split('T')[0]

const totalDays = computed(() => {
  if (!startDate.value || !endDate.value) return 0
  const start = new Date(startDate.value)
  const end = new Date(endDate.value)
  const diff = Math.ceil((end - start) / (1000 * 60 * 60 * 24))
  return diff > 0 ? diff : 0
})

const totalPrice = computed(() => {
  return totalDays.value * (props.costume?.price || 0)
})

const isValid = computed(() => {
  return (
    selectedSize.value &&
    startDate.value &&
    endDate.value &&
    totalDays.value > 0
  )
})

watch(
  () => props.costume,
  (newCostume) => {
    if (newCostume) {
      selectedSize.value = newCostume.size[0]
    }
  },
)

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
    alert('Please sign in to place a booking.')
    router.push('/login')
    return
  }

  const customerName = authStore.user?.name || 'Unknown'
  const email = authStore.user?.email || ''
  const phone = authStore.user?.phone || ''

  const booking = {
    costumeId: props.costume.id,
    costumeName: props.costume.name,
    costumeImage: props.costume.image,
    customerName,
    email,
    phone,
    userId: authStore.user?.id || null,
    startDate: startDate.value,
    endDate: endDate.value,
    size: selectedSize.value,
    totalPrice: totalPrice.value,
  }

  bookingsStore.addBooking(booking)
  hide()
  emit('booked')

  // Reset form
  startDate.value = ''
  endDate.value = ''
}

defineExpose({ show, hide })
</script>
