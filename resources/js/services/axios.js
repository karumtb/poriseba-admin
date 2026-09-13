import axios from 'axios'
import { useToast } from 'vue-toastification'
import { loadingStore } from '@/services/loading'

const toast = useToast()

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL,
  headers: {
    Accept: 'application/json',
  },
})

// REQUEST INTERCEPTOR
api.interceptors.request.use(
  (config) => {

    // Skip global loader if requested
    if (!config.skipLoading) {
      loadingStore.start()
    }

    const token = localStorage.getItem('token')

    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }

    // No need to handle onUploadProgress here.
    // Axios automatically uses config.onUploadProgress.

    return config
  },
  (error) => {
    loadingStore.stop()
    return Promise.reject(error)
  }
)

// RESPONSE INTERCEPTOR
api.interceptors.response.use(
  (response) => {
    if (!response.config.skipLoading) {
      loadingStore.stop()
    }
    if (response.data?.status == false) {
      if(response.data.errors &&   Object.keys(response.data.errors).length > 0)
      {
        triggerToasterErrors(response.data.errors)
      }
      else{
         toast.error(response.data.message)
      }

    }

    return response
  },
  (error) => {
    if (!error.config?.skipLoading) {
      loadingStore.stop()
    }

    if (error.response?.status === 401 || error.response?.status === 403) {
      localStorage.removeItem('token')
      window.location.href = '/#/pages/login'
    }

    if (error.response?.data?.errors) {
      triggerToasterErrors(error.response.data.errors)
    }

    return Promise.reject(error)
  }
)

// Helper function
function triggerToasterErrors(errorsObj) {
  if (!errorsObj) return

  Object.values(errorsObj).forEach((messages) => {
    if (Array.isArray(messages)) {
      messages.forEach((message) => {
        toast.error(message)
      })
    }
  })
}

export default api
