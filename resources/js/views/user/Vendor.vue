<script setup>
import { ref, reactive, onMounted, computed, nextTick,watch } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import { FilterMatchMode } from '@primevue/core/api';
import Select from 'primevue/select';
import { CPasswordInput } from '@coreui/vue-pro';
import '@coreui/coreui-pro/dist/css/coreui.css';
import api from '@/services/adminAxios';
import { loadingStore } from '@/services/loading';
import { useBreadcrumb } from '@/services/useBreadcrumb';
import { allowDecimal } from '@/utils/input';
const { breadcrumbs } = useBreadcrumb();

import { CRow, CCol, CCard, CCardHeader, CCardBody, CForm, CFormLabel, CFormInput, CFormFeedback, CButton, CSpinner } from '@coreui/vue';

import CIcon from '@coreui/icons-vue';

import { cilPencil, cilTrash, cilPlus, cilSave, cilX, cilSearch, cilReload } from '@coreui/icons';

import Swal from 'sweetalert2';
import { useToast } from 'vue-toastification';
import { useRoute } from 'vue-router'
const toast = useToast();

/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

const vendors = ref([]);

const loading = computed(() => loadingStore.loading);

const btnLoading = ref(false);

const submitted = ref(false);

const validated = ref(false);

const editMode = ref(false);

const editingId = ref(null);

const billings = ref([]);

const totalRecords = ref(0);

const formSection = ref(null);

const role = ref(null);

const route = useRoute();


/*
|--------------------------------------------------------------------------
| Form
|--------------------------------------------------------------------------
*/

const form = reactive({
  id: null,
  name: null,
  phone: null,
  email: null,
  password: null,
  role:role
});

/*
|--------------------------------------------------------------------------
| Get Vendor Data
|--------------------------------------------------------------------------
*/

const defaultFilters = {
  keyword: null,
};

const filters = reactive({ ...defaultFilters });

const lazyParams = ref({
  first: 0,
  rows: 10,
  page: 0,
});

const getVendors = async () => {
  try {
    const page = lazyParams.value.page + 1;

    const response = await api.post('/vendor/listing', {
    page: page,
    per_page: lazyParams.value.rows,
    ...filters, // Spreads all properties of filters directly into this payload object
    role: role.value.toLowerCase(), // Adds or overrides the role property
    });


    const result = response.data.data;
    vendors.value = result.data;

    totalRecords.value = result.total;
  } catch (error) {
    console.error('Vendor Error:', error);
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

  getVendors();
};
const searchFilters = () => {
  lazyParams.value.page = 0;
  getVendors();
};

const resetFilters = () => {
  lazyParams.value.page = 0;
  Object.assign(filters, defaultFilters);
  getVendors();
};

/*
|--------------------------------------------------------------------------
| Reset Form
|--------------------------------------------------------------------------
*/

const resetForm = () => {
  Object.assign(form, {
    id: null,
    name: null,
    phone: null,
    email: null,
    password: null,
  });

  editingId.value = null;

  editMode.value = false;

  validated.value = false;
};

/*
|--------------------------------------------------------------------------
| Add New
|--------------------------------------------------------------------------
*/

const addNew = () => {
  resetForm();
  formSection.value?.$el?.scrollIntoView({ behavior: 'smooth' });

};

/*
|--------------------------------------------------------------------------
| Edit
|--------------------------------------------------------------------------
*/

const editVendor = (item) => {
  Object.assign(form, item);

  editingId.value = item.id;

  editMode.value = true;

  validated.value = false;

  formSection.value?.$el?.scrollIntoView({ behavior: 'smooth' });

};

/*
|--------------------------------------------------------------------------
| Save
|--------------------------------------------------------------------------
*/

const saveMaster = async (event) => {
  submitted.value = true;

  const formElement = event.currentTarget;

  if (!formElement.checkValidity()) {
    event.preventDefault();

    event.stopPropagation();

    // Wait for Vue to update the DOM with the '.is-invalid' class
    nextTick(() => {
      const firstErrorEl = formElement.querySelector('.is-invalid, :invalid');
      if (firstErrorEl) {
        firstErrorEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
    });

    return;
  }

  validated.value = true;

  btnLoading.value = true;

  try {
    const response = await api.post('/vendor/save', {
      ...form, role:role.value.toLowerCase(),
    });
    submitted.value = false;

    if (response.data.status) {
      toast.success(response.data.message || 'Saved successfully.');

      resetForm();

      await getVendors();
    } else {
      toast.error(response.data.message || 'Unable to save data.');
    }
  } catch (error) {
    console.error(error);

    toast.error(error.response?.data?.message || 'Something went wrong.');
  } finally {
    btnLoading.value = false;
  }
};

/*
|--------------------------------------------------------------------------
| Delete
|--------------------------------------------------------------------------
*/

const deleteVendor = (id) => {
  Swal.fire({
    title: 'Are you sure?',

    text: 'You will not be able to recover this record!',

    icon: 'warning',

    showCancelButton: true,

    confirmButtonText: 'Yes, delete it!',

    cancelButtonText: 'Cancel',
  }).then(async (result) => {
    if (!result.isConfirmed) {
      return;
    }

    try {
      const response = await api.post('/vendor/remove', {
        id: id,
      });

      if (response.data.status) {
        toast.success(response.data.message || 'Deleted successfully.');

        if (editingId.value === id) {
          resetForm();
        }

        await getVendors();
      } else {
        toast.error(response.data.message || 'Unable to delete data.');
      }
    } catch (error) {
      console.error(error);

      toast.error(error.response?.data?.message || 'Unable to delete data.');
    }
  });
};

/*
|--------------------------------------------------------------------------
| Mounted
|--------------------------------------------------------------------------
*/

const validatePassword = () => {
  // Access the underlying native input element
  const nativeInput = passwordInput.value?.$el?.querySelector('input') || passwordInput.value?.$el;

  if (!nativeInput) return;

  // Check your custom strong password logic
  if (form.value.id === null && !isStrongPassword(form.value.password)) {
    // Setting a string triggers native invalid state and prevents form submission
    nativeInput.setCustomValidity("Invalid password structure.");
  } else {
    // Empty string clears the native validation error immediately
    nativeInput.setCustomValidity("");
  }
};

onMounted(() => {
//   breadcrumbs.value = ['Vendor'];
//   getVendors();
});
// 1. Automatically updates based on the current route name or path


// 2. Watch the route path to re-fetch the API data whenever switching routes
watch(
    () => route.name,
    (newRoute) => {
        role.value = newRoute;

        breadcrumbs.value = ['User', newRoute=='Hospital'?'Hiring Organization':'Instititute']

        getVendors()
    },
    {
        immediate: true,
    }
)
/*
|--------------------------------------------------------------------------
| Validation
|--------------------------------------------------------------------------
*/
const isValidEmail = (email) => {
  if (!email) return false;

  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
};

const isValidPhone = (phone) => {
  return /^\d{10}$/.test(phone);
};

const isStrongPassword = (password) => {
  if (!password || password.length < 8) {
    return false;
  }

  const hasUppercase = /[A-Z]/.test(password);
  const hasLowercase = /[a-z]/.test(password);
  const hasNumber = /[0-9]/.test(password);
  const hasSpecial = /[^A-Za-z0-9]/.test(password);

  return hasUppercase && hasLowercase && hasNumber && hasSpecial;
};
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
/*
|--------------------------------------------------------------------------
| Toogle Status
|--------------------------------------------------------------------------
*/
const toogleStatus=async (id=null)=>{
try {
      const response = await api.post('/vendor/status/toggle', {id: id});
      if (response.data.status) {
        toast.success(response.data.message);

      }
    }
    catch (error) {
      console.error(error);
      toast.error(error.response?.data?.message || 'Unable to delete data.');
    }
}
</script>

<template>
  <CRow>
   <!-- ====================================================== -->
    <!-- LISTING -->
    <!-- ====================================================== -->

    <CCol :lg="12" :md="12" :sm="12">
      <CCard class="mb-4">
        <CCardHeader>
          <div class="d-flex justify-content-between align-items-center">
            <strong> {{  role=='Hospital'?'Hiring Organization':'Instititute' }} Lists </strong>

            <CButton color="primary" @click="addNew">
              <CIcon :icon="cilPlus" class="me-1" />

              Add New
            </CButton>
          </div>
        </CCardHeader>

        <CCardBody>
          <div class="card">
            <CCard class="border-0 shadow-sm mb-4">
              <CCardBody class="py-4">
                <div class="row g-3 align-items-end">
                  <!-- Acknowledgement -->
                  <div class="col-lg-8 col-md-6">
                    <label class="form-label fw-semibold text-secondary mb-2"> Keyword </label>

                    <input type="text" class="form-control" v-model.trim="filters.keyword" placeholder="Filter By Name or Email Or Phone No" size="lg" />
                  </div>

                  <!-- Registration Type -->

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
            <DataTable
              :value="vendors"
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

              <Column header="#">
                <template #body="{ index }">
                  {{ lazyParams.first + index + 1 }}
                </template>
              </Column>

              <Column header="Name" field="name" />
              <Column header="Email" field="email" />
              <Column header="Phone" field="phone" />
              <Column header="Status">
              <template #body="{ data }">
                  <CFormSwitch style="cursor: pointer;" id="formSwitchCheckChecked{{ data.id }}" label=""  :checked="data.status === 'A'" @click="toogleStatus(data.id)"  />
              </template>
              </Column>
              <Column header="Updated At">
                <template #body="{ data }">
                  {{ formatDate(data.updated_at) || 'N/A' }}
                </template>
              </Column>

              <Column header="Action">
                <template #body="{ data }">
                  <CButton color="warning" variant="outline" size="sm" class="me-2 mb-2" @click="editVendor(data)">
                    <CIcon :icon="cilPencil" />
                  </CButton>

                  <CButton color="danger" variant="outline" size="sm" class="me-2 mb-2" @click="deleteVendor(data.id)">
                    <CIcon :icon="cilTrash" />
                  </CButton>
                </template>
              </Column>
            </DataTable>
          </div>
        </CCardBody>
      </CCard>
    </CCol>
    <!-- ====================================================== -->
    <!-- FORM -->
    <!-- ====================================================== -->

    <CCol :lg="12" :md="12" :sm="12" ref="formSection">
      <CCard class="mb-4">
        <CCardHeader>
          <div class="d-flex justify-content-between align-items-center">
            <strong>
              {{ editMode ? `Edit ${ role=='Hospital'?'Hiring Organization':'Instititute' }` : `Add ${ role=='Hospital'?'Hiring Organization':'Instititute' }` }}
            </strong>

            <CButton v-if="editMode" color="secondary" variant="outline" size="sm" @click="addNew">
              <CIcon :icon="cilX" class="me-1" />

              New
            </CButton>
          </div>
        </CCardHeader>

        <CCardBody>
          <CForm class="row g-3 needs-validation" novalidate :validated="validated" @submit.prevent="saveMaster">
            <!-- Name -->

            <CCol :md="12">
              <!-- Name -->
              <CFormLabel>Name</CFormLabel>
              <input
                v-model.trim="form.name"
                type="text"
                required
                class="form-control"
                :class="{ 'is-invalid': submitted && !form.name }" />
              <CFormFeedback invalid> Name is required. </CFormFeedback>

              <!-- Email -->
              <CFormLabel>Email</CFormLabel>
              <input
                v-model.trim="form.email"
                type="email"
                required
                class="form-control"
                :class="{
                  'is-invalid': submitted && !isValidEmail(form.email),
                }" />
              <CFormFeedback invalid> Please enter a valid email address. </CFormFeedback>

              <!-- Phone -->
              <CFormLabel>Phone No</CFormLabel>
              <input
                v-model.trim="form.phone"
                type="text"
                required
                minlength="10"
                maxlength="10"
                pattern="[0-9]{10}"
                class="form-control"
                :class="{
                  'is-invalid': submitted && !isValidPhone(form.phone),
                }"
                @input="form.phone = form.phone.replace(/\D/g, '')" />
              <CFormFeedback invalid> Phone No must be exactly 10 digits. </CFormFeedback>

              <!-- Password -->
              <CFormLabel>Password</CFormLabel>
              <CPasswordInput
                v-model.trim="form.password"
                type="password"
                :required="form.id === null"
                minlength="8"
                class="form-control"
                @input="validatePassword"
                :class="{
                  'is-invalid': submitted && form.id === null && !isStrongPassword(form.password),
                }" />
               <CFormFeedback
                v-if="submitted && form.id === null && !isStrongPassword(form.password)"
                invalid
                >
                Password must be at least 8 characters and contain uppercase, lowercase, number and special character.
                </CFormFeedback>

              <!-- Password Requirements -->
              <small class="text-muted d-block mt-1">
                Password must contain:
                <br />
                • Minimum 8 characters
                <br />
                • At least one uppercase letter
                <br />
                • At least one lowercase letter
                <br />
                • At least one number
                <br />
                • At least one special character
              </small>
            </CCol>

            <!-- Buttons -->

            <CCol :xs="12">
              <div class="d-flex gap-2">
                <CButton type="submit" color="primary" :disabled="btnLoading">
                  <CSpinner v-if="btnLoading" size="sm" class="me-1" />

                  <CIcon v-else :icon="cilSave" class="me-1" />

                  {{ editMode ? 'Update' : 'Save' }}
                </CButton>

                <CButton type="button" color="secondary" variant="outline" :disabled="btnLoading" @click="resetForm">
                  <CIcon :icon="cilX" class="me-1" />

                  Clear
                </CButton>
              </div>
            </CCol>
          </CForm>
        </CCardBody>
      </CCard>
    </CCol>

  </CRow>
</template>
