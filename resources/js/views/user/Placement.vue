<script setup>
import { ref, reactive, onMounted, computed, nextTick, onUnmounted, watch } from 'vue';
import {CModal, CModalHeader, CModalTitle, CModalBody, CModalFooter } from '@coreui/vue'

import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import { FilterMatchMode } from '@primevue/core/api';
import { useFormBuilder } from '@/form/useFormBuilder';

import Select from 'primevue/select';

import api from '@/services/adminAxios';

import { loadingStore } from '@/services/loading';

import { useBreadcrumb } from '@/services/useBreadcrumb';

import { allowDecimal, getFileName, openFile } from '@/utils/input';

const { breadcrumbs } = useBreadcrumb();

import { useFileUpload } from '@/services/useFileUpload';

import { CRow, CCol, CCard, CCardHeader, CCardBody, CForm, CFormLabel, CFormInput, CFormFeedback, CButton, CSpinner } from '@coreui/vue';

import CIcon from '@coreui/icons-vue';

import { cilPencil, cilTrash, cilPlus, cilSave, cilX, cilSearch, cilReload, cilCheckCircle,cilEyedropper,cilLowVision } from '@coreui/icons';

import Swal from 'sweetalert2';

import { useToast } from 'vue-toastification';

import { CDatePicker } from '@coreui/vue-pro'

import ViewEnrolment from '@/views/user/ViewEnrolment.vue'

const toast = useToast();

import { indianStates } from '@/constants/indianStates';

import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.css';
import { Ckeditor } from '@ckeditor/ckeditor5-vue';
// Import the desired predefined editor setup
import { ClassicEditor, Essentials, Paragraph, Heading, Bold, Italic, Link, List } from 'ckeditor5';
import 'ckeditor5/ckeditor5.css'; // Don't forget to import styles!
import AutoComplete from 'primevue/autocomplete';

import { Subject, of, from, map } from 'rxjs';

import { debounceTime, distinctUntilChanged, switchMap, catchError } from 'rxjs/operators';
import { useAuthStore } from '@/stores/auth';

import { CRating } from '@coreui/vue-pro'

import { CFormCheck } from '@coreui/vue'

import '@coreui/coreui-pro/dist/css/coreui.css'

/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

const enrolments = ref([]);
const vendors = ref([]);
const designations=ref([]);
const vendorSearch = ref(null);

const loading = computed(() => loadingStore.loading);
const vendorLoading = computed(() => loadingStore.loading);

const btnLoading = ref(false);

const submitted = ref(false);

const validated = ref(false);

const editMode = ref(false);

const editingId = ref(null);

const totalRecords = ref(0);

const PhotoFile = ref(null);
const AdharFile = ref(null);
const CertificateFile = ref(null);

const formSection = ref(null);

const selectedEnrolments = ref([]);
const visibleLiveDemo =ref(false);
const approving = ref(false);
const statusTypes = ref([
  { label: 'Active', value: 'A' },
  { label: 'Pending', value: 'P' },
]);
const authStore = useAuthStore();

/*
|--------------------------------------------------------------------------
| Get Enrolment Data
|--------------------------------------------------------------------------
*/

const defaultFilters = {
  keyword: null,
  status: null,
  designation:null
};

const filters = reactive({ ...defaultFilters });

const lazyParams = ref({
  first: 0,
  rows: 10,
  page: 0,
});

const getPlacements = async () => {
  try {
    const page = lazyParams.value.page + 1;

    const response = await api.post('/placement-request', {
      page: page,
      per_page: lazyParams.value.rows,
      ...filters,
    });

    const result = response.data.data;

    enrolments.value = result.data;

    totalRecords.value = result.total;
  } catch (error) {
    console.error('Enrolment Error:', error);
  } finally {
  }
};
const getDesignations = async () => {
  try {

    const response = await api.post('/designation/listing', {});
    const result = response.data.data;
    designations.value = result;

  } catch (error) {
    console.error('Designation Error:', error);
  } finally {
  }
};

const onPage = (event) => {
  first.value = event.first;
  lazyParams.value = {
    first: event.first,
    rows: event.rows,
    page: event.page,
  };

  getPlacements();
};
const searchFilters = () => {
  lazyParams.value.page = 0;
  getPlacements();
};

const resetFilters = () => {
  lazyParams.value.page = 0;
  Object.assign(filters, defaultFilters);
  getPlacements();
};

/*
|--------------------------------------------------------------------------
| Mounted
|--------------------------------------------------------------------------
*/

onMounted(() => {
  breadcrumbs.value = ['Enrolment'];
  getPlacements();
  getDesignations();
});

/*
|--------------------------------------------------------------------------
| Date Transformation
|--------------------------------------------------------------------------
*/
const formatDate = (date) => {
  if (!date) {
    return '-';
  }

  const formattedDate = new Date(date.replace(' ', 'T'));

  return formattedDate.toLocaleString('en-IN', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    hour12: true,
  });
};



onUnmounted(() => {
});

/*
|--------------------------------------------------------------------------
| BULK APPROVE
|--------------------------------------------------------------------------
*/
const bulkApprove = async (id = null) => {
  // Single approve
  if (id) {
    selectedEnrolments.value = [{ id }];
  }
  if (!selectedEnrolments.value.length) {
    return;
  }

  try {
    approving.value = true;

    const ids = selectedEnrolments.value.map((item) => item.id);
    const response = await api.post('/hiring-request/approve', {
      ids,
    });

    toast.success(response.data.message || 'Selected enrolment records approved successfully.');

    // Clear selection
    selectedEnrolments.value = [];

    // Reload table
    getPlacements();
  } catch (error) {
    console.error(error);

    toast.error(error.response?.data?.message || 'Unable to approve selected records.');
  } finally {
    approving.value = false;
  }
};
</script>
<template>
  <CRow>
    <!-- ====================================================== -->
    <!-- LISTING -->
    <!-- ====================================================== -->

    <CCol :lg="12" :md="12" :sm="12" >
      <CCard class="mb-4">
        <CCardHeader>
          <div class="d-flex justify-content-between align-items-center">
            <strong> Enrolment Hiring requests </strong>
          </div>
        </CCardHeader>

        <CCardBody>
          <CCard class="filter-card mb-4">
            <CCardBody class="py-4">
              <div class="row g-3 align-items-end">
                <!-- Acknowledgement -->
                <div class="col-lg-3 col-md-6">
                  <input type="text" class="form-control" v-model.trim="filters.keyword" placeholder="Filter By Name,Vendor,Phone No" size="lg" />
                </div>

                <!-- Registration Type -->
                <div class="col-lg-2 col-md-6">
                  <Select
                    v-model="filters.status"
                    :options="statusTypes"
                    optionLabel="label"
                    optionValue="value"
                    placeholder="All"
                    class="w-100" />
                </div>
                 <div class="col-lg-2 col-md-6">
                  <select  class="form-control" v-model="filters.designation">
                      <option :value="null">Designation</option>
                      <!-- Loop starts here -->
                        <option
                            v-for="item in designations"
                            :key="item.id"
                            :value="item.id">
                            {{ item.name }}
                        </option>
                </select>
                 </div>


                <!-- Search -->
                <div class="col-lg-2 col-md-6">
                  <CButton color="primary" class="w-100 py-2" @click="searchFilters">
                    <CIcon :icon="cilSearch" class="me-2" />
                    Search
                  </CButton>
                </div>

                <!-- Reset -->
                <div class="col-lg-2 col-md-6">
                  <CButton color="light" class="w-100 py-2 border" @click="resetFilters">
                    <CIcon :icon="cilReload" class="me-2" />
                    Reset
                  </CButton>
                </div>
              </div>
            </CCardBody>
          </CCard>
          <div class="card">
            <div class="bulk-approve-bar mb-3">
              <div class="selected-info">
                <span class="selected-count">
                  {{ selectedEnrolments.length }}
                </span>

                <span class="selected-text"> records selected </span>
              </div>
              <CButton color="success" :disabled="selectedEnrolments.length === 0 || approving" @click="bulkApprove()" style="color: white;">
                <CSpinner v-if="approving" size="sm" class="me-2" />
                <CIcon :icon="cilCheckCircle" class="me-2" v-else />
                Approve
              </CButton>

            </div>
            <DataTable
              v-model:selection="selectedEnrolments"
              :value="enrolments"
              dataKey="id"
              lazy
              paginator
              :first="lazyParams.first"
              :rows="lazyParams.rows"
              :totalRecords="totalRecords"
              :loading="loading"
              :rowsPerPageOptions="[10, 25, 50, 100]"
              paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown CurrentPageReport"
              currentPageReportTemplate="Showing {first} to {last} of {totalRecords} records"
              responsiveLayout="scroll"
              @page="onPage">
              <template #empty>
                <div class="text-center py-4">No records found.</div>
              </template>

              <template #loading>
                <div class="text-center py-4">Loading records...</div>
              </template>
              <!-- SELECT ALL / ROW CHECKBOX -->
              <Column selectionMode="multiple" headerStyle="width: 3rem" v-if="authStore.user.role == 'admin' || authStore.user.role == 'hospital'" />
              <Column header="#">
                <template #body="{ index }">
                  {{ lazyParams.first + index + 1 }}
                </template>
              </Column>
              <Column header="Photo">
                <template #body="{ data }">
                  <img :src="data.profilePreviewUrl" class="profile-img" />
                </template>
              </Column>

              <Column header="Name" field="name" />
              <Column header="Vendor">
                <template #body="{ data }">
                  {{ data?.vendor }}
                </template>
              </Column>
              <Column header="Hospital" field="hospital" />
              <Column header="Phone" field="phone" />
              <Column header="Status">
                <template #body="{ data }">
                  <Tag  :severity="data.status == 'A' ? 'success' : 'danger'" :value="data.status == 'A' ? 'Approved' : 'Pending'" />
                </template>
              </Column>
              <Column header="Designation" #body="{ data }" >{{ data?.designation_name }}</Column>

            </DataTable>
          </div>
        </CCardBody>
      </CCard>
    </CCol>

    <!-- ====================================================== -->
    <!-- FORM -->
    <!-- ====================================================== -->


  </CRow>

</template>



