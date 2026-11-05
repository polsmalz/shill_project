import { defineStore } from 'pinia'
import axios from 'axios'

axios.defaults.baseURL = 'http://127.0.0.1:8000/api'

export const useAuthStore = defineStore('auth', {
  state: () => ({
   user: JSON.parse(localStorage.getItem('user')) || null, 
    token: localStorage.getItem('token') || null,
    error: null
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
    isAdmin: (state) => state.user?.role === 'admin'
  },

  actions: {
    async login(credentials) {
      try {
        const res = await axios.post('/login', credentials)

        // Laravel Sanctum returns access_token
        this.token = res.data.access_token
        this.user = res.data.user

        localStorage.setItem('token', this.token)
        localStorage.setItem('user', JSON.stringify(this.user))

        // Set Authorization header for future requests
        axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`

        this.error = null

      } catch (err) {
        this.error = err.response?.data?.message || 'Login failed'
        throw err
      }
    },

    async register(data) {
      try {
        const res = await axios.post('/register', data)

        this.token = res.data.access_token
        this.user = res.data.user

        localStorage.setItem('token', this.token)
        localStorage.setItem('user', JSON.stringify(this.user))
        axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`

        this.error = null

      } catch (err) {
        this.error = Object.values(err.response?.data?.errors || {})[0]?.[0] || 'Registration failed'
        throw err
      }
    },

    logout() {
      this.user = null
      this.token = null
      this.error = null
      localStorage.removeItem('token')
      localStorage.removeItem('user')
      delete axios.defaults.headers.common['Authorization']
    }
  }
})