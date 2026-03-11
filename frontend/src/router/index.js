import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView,
    },
    {
      path: '/costumes',
      name: 'costumes',
      component: () => import('../views/CostumesView.vue'),
    },
    {
      path: '/costume/:id',
      name: 'costume-detail',
      component: () => import('../views/CostumeDetail.vue'),
    },
    {
      path: '/my-bookings',
      name: 'my-bookings',
      component: () => import('../views/MyBookings.vue'),
    },
    {
      path: '/about',
      name: 'about',
      component: () => import('../views/AboutView.vue'),
    },
    {
      path: '/login',
      name: 'login',
      component: () => import('../views/LoginView.vue'),
    },
    {
      path: '/register',
      name: 'register',
      component: () => import('../views/RegisterView.vue'),
    },
    {
      path: '/costumes/add',
      name: 'add-costume',
      component: () => import('../views/AddCostumeView.vue'),
    },
    {
      path: '/manage/bookings',
      name: 'manage-bookings',
      component: () => import('../views/ManageBookingsView.vue'),
    },
  ],
})

export default router
