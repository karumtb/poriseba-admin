<script setup>
import { onMounted, ref, computed } from 'vue'
import { CChart } from '@coreui/vue-chartjs'
import { getStyle } from '@coreui/utils'
import { loadingStore } from '@/services/loading'
import api from '@/services/adminAxios'
import { useToast } from 'vue-toastification'
import { useAuthStore } from '@/stores/auth'

const toast = useToast()
const summary_data = ref(null)
const loading = computed(() => loadingStore.loading)
const authStore = useAuthStore()

const getSummary = async () => {
  try {
    const response = await api.post('/dashboard', {})
    const result = response.data.data
    summary_data.value = result
  } catch (error) {
    console.error('Designation Error:', error)
  } finally {
  }
}

onMounted(() => {
  getSummary()
})
</script>

<template>
  <div class="position-relative">
    <!-- Loader -->
    <div v-if="loading" class="dashboard-loader">
      <CSpinner color="primary" />
    </div>

    <CRow :xs="{ gutter: 4 }">
      <CCol :sm="6" :xl="4" :xxl="4">
        <CWidgetStatsA color="primary">
          <template #value>
            {{ summary_data?.total_enrolment }}
          </template>

          <template #title>Total Enrolment</template>
        </CWidgetStatsA>
      </CCol>

      <CCol
        :sm="6"
        :xl="4"
        :xxl="4"
        v-if="authStore.user?.role == 'admin'"
      >
        <CWidgetStatsA color="info">
          <template #value>
            {{ summary_data?.total_institute }}
          </template>

          <template #title>Total Institution</template>
        </CWidgetStatsA>
      </CCol>

      <CCol
        :sm="6"
        :xl="4"
        :xxl="4"
        v-if="authStore.user?.role == 'admin'"
      >
        <CWidgetStatsA color="warning">
          <template #value>
            0
          </template>

          <template #title>Subscription Booking</template>
        </CWidgetStatsA>
      </CCol>


    </CRow>
  </div>
</template>


