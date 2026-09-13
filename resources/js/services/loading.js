import { reactive } from 'vue'

export const loadingStore = reactive({
  loading: false,
  requests: 0,

  start() {
    this.requests++
    this.loading = true
  },

  stop() {
    if (this.requests > 0) {
      this.requests--
    }

    this.loading = this.requests > 0
  }
})
