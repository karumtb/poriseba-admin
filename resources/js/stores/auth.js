import { defineStore } from 'pinia'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: JSON.parse(localStorage.getItem('user') || 'null'),
  }),

  getters: {
    profileImage: (state) => state.user?.profile_image || '',
  },

  actions: {
    setUser(user) {
      this.user = user

      localStorage.setItem(
        'user',
        JSON.stringify(user)
      )
    },

    updateUser(data) {
      this.user = {
        ...this.user,
        ...data,
      }

      localStorage.setItem(
        'user',
        JSON.stringify(this.user)
      )
    },

    logout() {
      this.user = null
      localStorage.removeItem('user')
    },
  },
})
