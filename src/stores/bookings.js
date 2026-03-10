import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useBookingsStore = defineStore('bookings', () => {
  const bookings = ref([
    {
      id: 1,
      costumeId: 2,
      costumeName: 'Superhero Spider Suit',
      costumeImage:
        'https://images.unsplash.com/photo-1635805737707-575885ab0820?w=500&auto=format&fit=crop&q=60',
      customerName: 'John Doe',
      email: 'john@example.com',
      phone: '555-0123',
      startDate: '2026-03-15',
      endDate: '2026-03-18',
      size: 'M',
      totalPrice: 195,
      status: 'confirmed',
      bookingDate: '2026-03-10',
    },
  ])

  const addBooking = (booking) => {
    const newBooking = {
      id: Date.now(),
      ...booking,
      status: 'pending',
      bookingDate: new Date().toISOString().split('T')[0],
    }
    bookings.value.push(newBooking)
    return newBooking
  }

  const cancelBooking = (id) => {
    const index = bookings.value.findIndex((b) => b.id === id)
    if (index !== -1) {
      bookings.value[index].status = 'cancelled'
    }
  }

  const getUserBookings = () => {
    return bookings.value.sort((a, b) => new Date(b.bookingDate) - new Date(a.bookingDate))
  }

  return {
    bookings,
    addBooking,
    cancelBooking,
    getUserBookings,
  }
})
