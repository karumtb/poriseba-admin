<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useToast } from 'vue-toastification';

import DataTable from 'primevue/datatable';
import Column from 'primevue/column';

import { CRow, CCol, CCard, CCardHeader, CCardBody, CFormInput, CFormSelect, CButton, CSpinner } from '@coreui/vue';

import CIcon from '@coreui/icons-vue';
import { cilSearch, cilArrowLeft } from '@coreui/icons';

import api from '@/services/adminAxios';

// Separate assignment history component
import AssignmentHistory from './AssignmentHistory.vue';

// --------------------------------------------------
// Router and services
// --------------------------------------------------

const route = useRoute();
const router = useRouter();
const toast = useToast();

const emit = defineEmits(['select-caregiver']);

// --------------------------------------------------
// Active tab
// --------------------------------------------------

const activeTab = ref('shortlisted');

// --------------------------------------------------
// Patient code
// --------------------------------------------------

const patientCode = computed(() => route.params.patient_code || '');

// --------------------------------------------------
// Listing data
// --------------------------------------------------

const caregivers = ref([]);
const designations = ref([]);
const totalRecords = ref(0);
const searchLoading = ref(false);

const filters = ref({
  keyword: '',
  location: '',
  designation: '',
  status: '',
});

const lazyParams = ref({
  first: 0,
  rows: 10,
  page: 0,
});

// --------------------------------------------------
// Dropdown options
// --------------------------------------------------

const designationOptions = computed(() => [
  {
    label: 'All',
    value: '',
  },
  ...designations.value.map((item) => ({
    label: item.name,
    value: item.id,
  })),
]);

const statusOptions = [
  {
    label: 'All',
    value: '',
  },
  {
    label: 'Active',
    value: 'A',
  },
  {
    label: 'Inactive',
    value: 'I',
  },
];

// --------------------------------------------------
// Get master data
// --------------------------------------------------

const getMasters = async () => {
  try {
    const response = await api.post('/masters');

    const masterData = response?.data?.data || {};

    designations.value = Array.isArray(masterData.designations) ? masterData.designations : [];
  } catch (error) {
    console.error('Master API error:', error);

    toast.error(error?.response?.data?.message || 'Unable to load caregiver designations.');
  }
};

// --------------------------------------------------
// Get available caregivers
// --------------------------------------------------

const getCaregivers = async () => {
  if (!patientCode.value) {
    caregivers.value = [];
    totalRecords.value = 0;

    toast.error('Patient code is missing.');
    return;
  }

  searchLoading.value = true;

  try {
    const payload = {
      page: lazyParams.value.page + 1,
      per_page: lazyParams.value.rows,

      keyword: filters.value.keyword.trim() || null,
      location: filters.value.location.trim() || null,
      status: filters.value.status || null,
      designation: filters.value.designation || null,

      patient_code: patientCode.value,
    };

    const response = await api.post('/patients/available-caregiver', payload);

    const result = response?.data?.data || {};

    caregivers.value = Array.isArray(result.data) ? result.data : [];

    totalRecords.value = Number(result.total || 0);
  } catch (error) {
    console.error('Available caregiver API error:', error);

    caregivers.value = [];
    totalRecords.value = 0;

    toast.error(error?.response?.data?.message || 'Unable to fetch available caregivers.');
  } finally {
    searchLoading.value = false;
  }
};

// --------------------------------------------------
// Search
// --------------------------------------------------

const searchCaregivers = () => {
  lazyParams.value.first = 0;
  lazyParams.value.page = 0;

  getCaregivers();
};

// --------------------------------------------------
// Reset
// --------------------------------------------------

const resetFilters = () => {
  filters.value = {
    keyword: '',
    location: '',
    designation: '',
    status: '',
  };

  lazyParams.value = {
    first: 0,
    rows: 10,
    page: 0,
  };

  getCaregivers();
};

// --------------------------------------------------
// Pagination
// --------------------------------------------------

const onPage = (event) => {
  lazyParams.value = {
    first: event.first,
    rows: event.rows,
    page: event.page,
  };

  getCaregivers();
};

// --------------------------------------------------
// Caregiver name
// --------------------------------------------------

const caregiverName = (caregiver) => {
  return caregiver.name || [caregiver.first_name, caregiver.last_name].filter(Boolean).join(' ') || 'N/A';
};

// --------------------------------------------------
// Caregiver initials
// --------------------------------------------------

const caregiverInitials = (caregiver) => {
  const name = caregiverName(caregiver);

  if (name === 'N/A') {
    return 'CG';
  }

  return name
    .split(/\s+/)
    .filter(Boolean)
    .slice(0, 2)
    .map((item) => item.charAt(0).toUpperCase())
    .join('');
};

// --------------------------------------------------
// Availability
// --------------------------------------------------

const caregiverAvailability = (caregiver) => {
  if (caregiver.status !== 'A') {
    return 'Inactive';
  }

  return Number(caregiver.is_engaged) === 1 ? 'Engaged' : 'Available';
};

const availabilityClass = (caregiver) => {
  const status = caregiverAvailability(caregiver);

  if (status === 'Available') {
    return 'bg-success-subtle text-success';
  }

  if (status === 'Engaged') {
    return 'bg-warning-subtle text-warning';
  }

  return 'bg-danger-subtle text-danger';
};

// --------------------------------------------------
// Profile photo
// --------------------------------------------------

const profilePhoto = (caregiver) => {
  return caregiver.profilePreviewUrl || caregiver.profile_preview_url || null;
};

// --------------------------------------------------
// Select caregiver
// --------------------------------------------------

const selectCaregiver = (caregiver) => {
  if (caregiverAvailability(caregiver) !== 'Available') {
    toast.warning('This caregiver is not available.');
    return;
  }

  emit('select-caregiver', {
    patient_code: patientCode.value,
    caregiver_id: caregiver.id,
    caregiver_code: caregiver.caregiver_code,
    caregiver: caregiver,
  });
};

// --------------------------------------------------
// Hire caregiver
// --------------------------------------------------

const hireCaregiver = async (caregiver) => {
  try {
    const response = await api.post('/patients/hire-caregiver', {
      patient_code: patientCode.value,
      caregiver_id: caregiver.id,
    });

    toast.success(response?.data?.message || 'Caregiver hired successfully.');

    await getCaregivers();
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Unable to hire caregiver.');
  }
};

// --------------------------------------------------
// Back
// --------------------------------------------------

const goBack = () => {
  router.back();
};

// --------------------------------------------------
// Initial load
// --------------------------------------------------

onMounted(async () => {
  await getMasters();
  await getCaregivers();
});
</script>

<template>
  <CRow>
    <CCol :lg="12" :md="12" :sm="12">
      <CCard class="mb-4">
        <!-- Header -->

        <CCardHeader class="d-flex justify-content-between align-items-center flex-wrap gap-2">
          <div>
            <h5 class="mb-1">Caregiver Management</h5>

            <small class="text-body-secondary"> Patient: {{ patientCode }} </small>
          </div>

          <CButton color="secondary" variant="outline" size="sm" @click="goBack">
            <CIcon :icon="cilArrowLeft" class="me-1" />
            Back to Patient
          </CButton>
        </CCardHeader>

        <!-- Tabs -->

        <div class="caregiver-tabs px-3 pt-3">
          <button type="button" class="caregiver-tab" :class="{ active: activeTab === 'shortlisted' }" @click="activeTab = 'shortlisted'">
            Shortlisted Caregivers
          </button>

          <button type="button" class="caregiver-tab" :class="{ active: activeTab === 'history' }" @click="activeTab = 'history'">
            Assignment History
          </button>
        </div>

        <!-- ===================================== -->
        <!-- TAB 1: SHORTLISTED CAREGIVERS -->
        <!-- ===================================== -->

        <CCardBody v-if="activeTab === 'shortlisted'">
          <!-- Search filters -->

          <CRow class="g-2 align-items-end mb-3">
            <!-- Name / ID -->

            <CCol :lg="4" :md="6">
              <div class="input-group">
                <span class="input-group-text">
                  <CIcon :icon="cilSearch" />
                </span>

                <CFormInput v-model="filters.keyword" placeholder="Search by caregiver name, ID..." @keyup.enter="searchCaregivers" />
              </div>
            </CCol>

            <!-- Designation -->

            <CCol :lg="2" :md="6">
              <CFormSelect v-model="filters.designation" :options="designationOptions" />
            </CCol>

            <!-- Location -->

            <CCol :lg="2" :md="6">
              <CFormInput v-model="filters.location" placeholder="Search location..." @keyup.enter="searchCaregivers" />
            </CCol>

            <!-- Status -->

            <CCol :lg="2" :md="6">
              <CFormSelect v-model="filters.status" :options="statusOptions" />
            </CCol>

            <!-- Search -->

            <CCol :lg="1" :md="3">
              <CButton color="primary" class="w-100" :disabled="searchLoading" @click="searchCaregivers">
                <CSpinner v-if="searchLoading" size="sm" class="me-1" />

                <CIcon v-else :icon="cilSearch" />

                Search
              </CButton>
            </CCol>

            <!-- Reset -->

            <CCol :lg="1" :md="3">
              <CButton color="secondary" variant="outline" class="w-100" :disabled="searchLoading" @click="resetFilters"> Reset </CButton>
            </CCol>
          </CRow>

          <!-- Caregiver table -->

          <DataTable
            :value="caregivers"
            dataKey="id"
            lazy
            paginator
            :first="lazyParams.first"
            :rows="lazyParams.rows"
            :totalRecords="totalRecords"
            :loading="searchLoading"
            :rowsPerPageOptions="[10, 25, 50, 100]"
            paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown CurrentPageReport"
            currentPageReportTemplate="Showing {first} to {last} of {totalRecords} records"
            responsiveLayout="scroll"
            :tableStyle="{ minWidth: '1100px' }"
            @page="onPage">
            <template #empty>
              <div class="text-center py-4">No available caregivers found.</div>
            </template>

            <template #loading>
              <div class="text-center py-4">Loading caregivers...</div>
            </template>

            <!-- Serial number -->

            <Column header="#" :headerStyle="{ width: '55px' }" :bodyStyle="{ whiteSpace: 'nowrap' }">
              <template #body="{ index }">
                {{ lazyParams.first + index + 1 }}
              </template>
            </Column>

            <!-- Photo -->

            <Column header="Photo" :headerStyle="{ width: '75px' }" :bodyStyle="{ whiteSpace: 'nowrap' }">
              <template #body="{ data }">
                <img
                  v-if="profilePhoto(data)"
                  :src="profilePhoto(data)"
                  :alt="caregiverName(data)"
                  style="width: 40px; height: 40px; object-fit: cover; border-radius: 6px" />

                <div
                  v-else
                  class="rounded bg-body-secondary d-flex align-items-center justify-content-center"
                  style="width: 40px; height: 40px">
                  <strong>
                    {{ caregiverInitials(data) }}
                  </strong>
                </div>
              </template>
            </Column>

            <!-- Caregiver ID -->

            <Column field="caregiver_code" header="Caregiver ID" :headerStyle="{ minWidth: '120px' }" :bodyStyle="{ whiteSpace: 'nowrap' }">
              <template #body="{ data }">
                <strong class="text-primary">
                  {{ data.caregiver_code || '-' }}
                </strong>
              </template>
            </Column>

            <!-- Name -->

            <Column header="Name" :headerStyle="{ minWidth: '150px' }" :bodyStyle="{ whiteSpace: 'nowrap' }">
              <template #body="{ data }">
                <div class="d-flex flex-column">
                  <strong>
                    {{ caregiverName(data) }}
                  </strong>

                  <small class="text-body-secondary">
                    {{ data.phone || '-' }}
                  </small>
                </div>
              </template>
            </Column>

            <!-- Designation -->

            <Column header="Type" :headerStyle="{ minWidth: '110px' }" :bodyStyle="{ whiteSpace: 'nowrap' }">
              <template #body="{ data }">
                {{ data.designation?.name || '-' }}
              </template>
            </Column>

            <!-- Experience -->

            <Column header="Experience" :headerStyle="{ minWidth: '100px' }" :bodyStyle="{ whiteSpace: 'nowrap' }">
              <template #body="{ data }">
                {{ data.experience || data.experience_years || '-' }}
              </template>
            </Column>

            <!-- Location -->

            <Column header="Location" :headerStyle="{ minWidth: '120px' }" :bodyStyle="{ whiteSpace: 'nowrap' }">
              <template #body="{ data }">
                {{ data.address || '-' }}
              </template>
            </Column>

            <!-- Match -->

            <Column header="Match %" :headerStyle="{ minWidth: '90px' }" :bodyStyle="{ whiteSpace: 'nowrap' }">
              <template #body="{ data }">
                {{ data.match_percentage ?? data.match_percent ?? '-' }}
              </template>
            </Column>

            <!-- Expected Fee -->

            <Column header="Expected Fee (₹)" :headerStyle="{ minWidth: '125px' }" :bodyStyle="{ whiteSpace: 'nowrap' }">
              <template #body="{ data }">
                {{ data.expected_fee ?? data.expected_fee_range ?? '-' }}
              </template>
            </Column>

            <!-- Availability -->

            <Column header="Availability" :headerStyle="{ minWidth: '115px' }" :bodyStyle="{ whiteSpace: 'nowrap' }">
              <template #body="{ data }">
                <span class="badge" :class="availabilityClass(data)">
                  {{ caregiverAvailability(data) }}
                </span>
              </template>
            </Column>

            <!-- Action -->

            <Column
              header="Action"
              :headerStyle="{ width: '110px', minWidth: '110px' }"
              :bodyStyle="{ width: '110px', minWidth: '110px', whiteSpace: 'nowrap' }">
              <template #body="{ data }">
                <CButton
                  color="primary"
                  variant="outline"
                  size="sm"
                  :disabled="caregiverAvailability(data) !== 'Available'"
                  style="min-width: 75px; white-space: nowrap; display: inline-flex; align-items: center; justify-content: center"
                  @click="hireCaregiver(data)">
                  Select
                </CButton>
              </template>
            </Column>
          </DataTable>
        </CCardBody>

        <!-- ===================================== -->
        <!-- TAB 2: ASSIGNMENT HISTORY -->
        <!-- ===================================== -->

        <AssignmentHistory v-else-if="activeTab === 'history'" />
      </CCard>
    </CCol>
  </CRow>
</template>

<style scoped>
.caregiver-tabs {
  display: flex;
  align-items: center;
  gap: 8px;
  border-bottom: 1px solid var(--cui-border-color);
}

.caregiver-tab {
  appearance: none;
  border: 0;
  border-bottom: 2px solid transparent;
  background: transparent;
  color: var(--cui-secondary-color);
  padding: 10px 16px;
  margin-bottom: -1px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.caregiver-tab:hover {
  color: var(--cui-primary);
}

.caregiver-tab.active {
  color: var(--cui-primary);
  border-bottom-color: var(--cui-primary);
  font-weight: 600;
}

@media (max-width: 576px) {
  .caregiver-tabs {
    overflow-x: auto;
  }

  .caregiver-tab {
    white-space: nowrap;
    padding: 10px 12px;
  }
}
</style>
