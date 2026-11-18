<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute } from 'vue-router';
import { useToast } from 'vue-toastification';

import DataTable from 'primevue/datatable';
import Column from 'primevue/column';

import { CCardBody, CSpinner } from '@coreui/vue';

import api from '@/services/adminAxios';

// --------------------------------------------------
// Router and services
// --------------------------------------------------

const route = useRoute();
const toast = useToast();

// --------------------------------------------------
// Patient code
// --------------------------------------------------

const patientCode = computed(() => route.params.patient_code || '');

// --------------------------------------------------
// State
// --------------------------------------------------

const loading = ref(false);
const assignments = ref([]);
const patient = ref(null);

const totalRecords = ref(0);
const currentPage = ref(1);
const perPage = ref(10);

// --------------------------------------------------
// Fetch assignment history (POST API)
// --------------------------------------------------

const getAssignmentHistory = async () => {
  if (!patientCode.value) {
    assignments.value = [];
    totalRecords.value = 0;

    toast.error('Patient code is missing.');
    return;
  }

  loading.value = true;

  try {
    const payload = {
      patient_code: patientCode.value,
      page: currentPage.value,
      per_page: perPage.value,
    };

    console.log('Assignment History Payload:', payload);

    // POST API
    const response = await api.post('/patients/caregiver/assignment-history', payload);

    console.log('Assignment History Response:', response.data);

    const result = response?.data?.data || {};
    const pagination = result.assignments || {};

    patient.value = result.patient || null;

    assignments.value = Array.isArray(pagination.data) ? pagination.data : [];

    totalRecords.value = Number(pagination.total || 0);

    currentPage.value = Number(pagination.current_page || 1);
    perPage.value = Number(pagination.per_page || 10);
  } catch (error) {
    console.error('Assignment History API Error:', error);

    assignments.value = [];
    totalRecords.value = 0;
    patient.value = null;

    toast.error(error?.response?.data?.message || 'Unable to fetch assignment history.');
  } finally {
    loading.value = false;
  }
};

// --------------------------------------------------
// Pagination
// --------------------------------------------------

const onPage = (event) => {
  currentPage.value = event.page + 1;
  perPage.value = event.rows;

  getAssignmentHistory();
};

// --------------------------------------------------
// Status
// --------------------------------------------------

const statusClass = (status) => {
  const classes = {
    Active: 'bg-success-subtle text-success',
    Completed: 'bg-primary-subtle text-primary',
    Reassigned: 'bg-warning-subtle text-warning',
    Inactive: 'bg-secondary-subtle text-secondary',
  };

  return classes[status] || 'bg-secondary-subtle text-secondary';
};

// --------------------------------------------------
// Patient code change
// --------------------------------------------------

watch(patientCode, () => {
  currentPage.value = 1;
  getAssignmentHistory();
});

// --------------------------------------------------
// Initial load
// --------------------------------------------------

onMounted(() => {
  getAssignmentHistory();
});
</script>

<template>
  <CCardBody>
    <!-- Patient information -->

    <div v-if="patient" class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
      <div>
        <h6 class="mb-1">Assignment History</h6>

        <small class="text-body-secondary">
          {{ patient.patient_name }}
          ({{ patient.patient_code }})
        </small>
      </div>

      <span class="badge bg-primary-subtle text-primary"> Total Assignments: {{ totalRecords }} </span>
    </div>

    <!-- Assignment table -->

    <DataTable
      :value="assignments"
      dataKey="id"
      lazy
      paginator
      :first="(currentPage - 1) * perPage"
      :rows="perPage"
      :totalRecords="totalRecords"
      :loading="loading"
      :rowsPerPageOptions="[10, 25, 50, 100]"
      paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown CurrentPageReport"
      currentPageReportTemplate="Showing {first} to {last} of {totalRecords} records"
      responsiveLayout="scroll"
      :tableStyle="{ minWidth: '1100px' }"
      @page="onPage">
      <template #empty>
        <div class="text-center py-4">No assignment history found.</div>
      </template>

      <template #loading>
        <div class="text-center py-4">
          <CSpinner size="sm" class="me-2" />
          Loading assignment history...
        </div>
      </template>

      <!-- Serial number -->

      <Column header="#" :headerStyle="{ width: '55px' }">
        <template #body="{ index }">
          {{ (currentPage - 1) * perPage + index + 1 }}
        </template>
      </Column>

      <!-- Assignment ID -->

      <Column field="assignment_id" header="Assignment ID" :headerStyle="{ minWidth: '130px' }">
        <template #body="{ data }">
          <strong class="text-primary">
            {{ data.assignment_id || '-' }}
          </strong>
        </template>
      </Column>

      <!-- Caregiver -->

      <Column field="caregiver_name" header="Caregiver" :headerStyle="{ minWidth: '160px' }">
        <template #body="{ data }">
          <div class="d-flex flex-column">
            <strong>
              {{ data.caregiver_name || '-' }}
            </strong>

            <small class="text-body-secondary">
              {{ data.caregiver_code || '-' }}
            </small>
          </div>
        </template>
      </Column>

      <!-- Role -->

      <Column field="role" header="Role" :headerStyle="{ minWidth: '120px' }">
        <template #body="{ data }">
          {{ data.role || '-' }}
        </template>
      </Column>

      <!-- From -->

      <Column field="from" header="From" :headerStyle="{ minWidth: '110px' }">
        <template #body="{ data }">
          {{ data.from || '-' }}
        </template>
      </Column>

      <!-- To -->

      <Column field="to" header="To" :headerStyle="{ minWidth: '110px' }">
        <template #body="{ data }">
          {{ data.to || '-' }}
        </template>
      </Column>

      <!-- Duty Hours -->

      <Column field="duty_hours" header="Duty Hours" :headerStyle="{ minWidth: '110px' }">
        <template #body="{ data }">
          {{ data.duty_hours || '-' }}
        </template>
      </Column>

      <!-- Fee -->

      <Column field="fee" header="Fee (₹)" :headerStyle="{ minWidth: '100px' }">
        <template #body="{ data }">
          {{ data.fee ?? '-' }}
        </template>
      </Column>

      <!-- Total Amount -->

      <Column field="total_amount" header="Total Amount (₹)" :headerStyle="{ minWidth: '130px' }">
        <template #body="{ data }">
          {{ data.total_amount ?? '-' }}
        </template>
      </Column>

      <!-- Status -->

      <Column field="status" header="Status" :headerStyle="{ minWidth: '110px' }">
        <template #body="{ data }">
          <span class="badge" :class="statusClass(data.status)">
            {{ data.status || '-' }}
          </span>
        </template>
      </Column>

      <!-- Remarks -->

      <Column field="remarks" header="Remarks" :headerStyle="{ minWidth: '150px' }">
        <template #body="{ data }">
          {{ data.remarks || '-' }}
        </template>
      </Column>

      <!-- Assigned Date -->

      <Column field="assigned_date" header="Assigned Date" :headerStyle="{ minWidth: '120px' }">
        <template #body="{ data }">
          {{ data.assigned_date || '-' }}
        </template>
      </Column>
    </DataTable>
  </CCardBody>
</template>
