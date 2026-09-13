<script setup>
import { ref, reactive, onMounted, computed, nextTick, onUnmounted, watch } from 'vue';

import { useFormBuilder } from '@/form/useFormBuilder';

import Select from 'primevue/select';

import api from '@/services/adminAxios';

import { loadingStore } from '@/services/loading';

import { useBreadcrumb } from '@/services/useBreadcrumb';

import { allowDecimal, getFileName, openFile } from '@/utils/input';

const { breadcrumbs } = useBreadcrumb();


import { CRow, CCol, CCard, CCardHeader, CCardBody, CForm, CFormLabel, CFormInput, CFormFeedback, CButton, CSpinner } from '@coreui/vue';

import CIcon from '@coreui/icons-vue';

import { cilPencil, cilTrash, cilPlus, cilSave, cilX, cilSearch, cilReload, cilCheckCircle } from '@coreui/icons';

import Swal from 'sweetalert2';

import { useToast } from 'vue-toastification';


const toast = useToast();

import { Ckeditor } from '@ckeditor/ckeditor5-vue';
// Import the desired predefined editor setup
import { ClassicEditor, Essentials, Paragraph, Heading, Bold, Italic, Link, List } from 'ckeditor5';
import 'ckeditor5/ckeditor5.css'; // Don't forget to import styles!

import { useAuthStore } from '@/stores/auth';

import { CFormCheck } from '@coreui/vue';

import '@coreui/coreui-pro/dist/css/coreui.css';

/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

const subscription_plans = ref([]);
const vendors = ref([]);

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

const selectedPlans = ref([]);
const approving = ref(false);
const statusTypes = ref([
  { label: 'Active', value: 'A' },
  { label: 'Pending', value: 'P' },
]);
const authStore = useAuthStore();

/*
|--------------------------------------------------------------------------
| Form
|--------------------------------------------------------------------------
*/

const { form, errors, validate, validateField, isInvalid, reset, patchValue } = useFormBuilder({
  id: {
    default: null,
    required: false,
  },
  name: {
    default: null,
    required: true,
    message: 'Plan Name is required',
  },
  description: {
    default: null,
    required: true,
    message: 'Description is required',
  },
  duration: {
    default: null,
    required: true,
    message: 'Duration is required',
  },
  price: {
    default: null,
    required: true,
    message: 'Price is required',
  },
  sort_order: {
    default: null,
    required: true,
    message: 'Sort is required',
  },
});

/*
|--------------------------------------------------------------------------
| CKEDITOR
|--------------------------------------------------------------------------
*/
const editor = ClassicEditor;

const editorConfig = {
  licenseKey: 'GPL',

  plugins: [Essentials, Paragraph, Heading, Bold, Italic, Link, List],

  toolbar: ['heading', '|', 'bold', 'italic', 'link', '|', 'bulletedList', 'numberedList', '|', 'undo', 'redo'],
};

/*
|--------------------------------------------------------------------------
| Reset Form
|--------------------------------------------------------------------------
*/

const resetForm = () => {
  reset();

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
  // Scroll to the form smoothly
  formSection.value?.$el?.scrollIntoView({ behavior: 'smooth' });
};

/*
|--------------------------------------------------------------------------
| Edit
|--------------------------------------------------------------------------
*/

const editPlan = async (item) => {
  try {
    patchValue(item);
    formSection.value?.$el?.scrollIntoView({ behavior: 'smooth' });

  } catch (error) {
    console.error('Plan Error:', error);
  } finally {
  }
};

/*
|--------------------------------------------------------------------------
| Save
|--------------------------------------------------------------------------
*/

const savePlan = async (event) => {
  submitted.value = true;
  const formElement = event.currentTarget;
  if (!validate()) {
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

  btnLoading.value = true;

  try {
    const response = await api.post('/subscription-plan/save', form);
    if (response.data.status) {
      toast.success(response.data.message || 'Saved successfully.');
      resetForm();

      await getPlans();
    } else {
      toast.error(response.data.message || 'Unable to save data.');
    }
  } catch (error) {
    console.error(error);
  } finally {
    btnLoading.value = false;
    submitted.value = false;
  }
};

/*
|--------------------------------------------------------------------------
| Delete
|--------------------------------------------------------------------------
*/

const deletePlan = (id) => {
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
      const response = await api.post('/subscription-plan/remove', {
        id: id,
      });
      if (response.data.status) {
        toast.success(response.data.message || 'Deleted successfully.');
        getPlans();

        if (editingId.value === id) {
          resetForm();
        }
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
| Get Subscription Data
|--------------------------------------------------------------------------
*/
const getPlans = async () => {
  try {
    const response = await api.post('/subscription-plan/index', {});
    if(response.data.status)
    {
         const result = response.data.data;
         subscription_plans.value = result;
    }

  } catch (error) {
    console.error('Vendor Error:', error);
  } finally {
  }
};
const formatPrice = (value) => {
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(value)
}
/*
|--------------------------------------------------------------------------
| Mounted
|--------------------------------------------------------------------------
*/

onMounted(() => {
  breadcrumbs.value = ['Plan'];
  getPlans();
});

onUnmounted(() => {});

/*
|--------------------------------------------------------------------------
| Toogle Status
|--------------------------------------------------------------------------
*/
const toogleStatus = async (id = null) => {
  try {
    const response = await api.post('/subscription-plan/status/toggle', { id: id });
    if (response.data.status) {
      toast.success(response.data.message);
    }
  } catch (error) {
    console.error(error);
    toast.error(error.response?.data?.message || 'Unable to delete data.');
  }
};
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
            <strong> Plan Lists </strong>

            <CButton color="primary" @click="addNew">
              <CIcon :icon="cilPlus" class="me-1" />

              Add New
            </CButton>
          </div>
        </CCardHeader>

        <CCardBody>
          <div class="table-responsive position-relative">
            <!-- Loader Overlay -->

            <table class="table table-bordered table-hover align-middle mb-0">
              <thead>
                <tr>
                  <th style="width: 70px" class="text-center">#</th>
                  <th>Name</th>
                  <th>Duration(Days)</th>
                  <th>Price</th>
                  <th>Sort Order</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>

              <tbody>
                <tr v-if="!loading && subscription_plans.length === 0">
                  <td colspan="7" class="text-center py-4">No records found.</td>
                </tr>

                <tr v-for="(item, index) in subscription_plans" :key="item.id" v-show="!loading">
                  <td class="text-center">{{ index + 1 }}</td>

                  <td>{{ item.name }}</td>

                  <td>{{ item.duration }}</td>

                  <td>{{ formatPrice(item.price) }}</td>

                  <td>{{ item.sort_order }}</td>
                  <td>
                    <CFormSwitch
                      style="cursor: pointer"
                      id="formSwitchCheckChecked{{ item.id }}"
                      label=""
                      :checked="item.status === 'A'"
                      @click="toogleStatus(item.id)" />
                  </td>

                  <td class="text-center">
                    <CButton color="warning" variant="outline" size="sm" class="me-2" @click="editPlan(item)">
                      <CIcon :icon="cilPencil" />
                    </CButton>

                    <CButton color="danger" variant="outline" size="sm" @click="deletePlan(item.id)">
                      <CIcon :icon="cilTrash" />
                    </CButton>
                  </td>
                </tr>
              </tbody>
            </table>
            <div v-if="loading" class="table-loader">
              <CSpinner />
            </div>
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
              {{ editMode ? 'Edit Plan' : 'Add Plan' }}
            </strong>

            <CButton v-if="editMode" color="secondary" variant="outline" size="sm" @click="addNew">
              <CIcon :icon="cilX" class="me-1" />

              New
            </CButton>
          </div>
        </CCardHeader>

        <CCardBody>
          <CForm class="row g-3 needs-validation layout-fixed-form" novalidate :validated="validated" @submit.prevent="savePlan">
            <!-- 1. Scrollable Fields Container -->
            <div class="form-scroll-body">
              <CCol :md="12">
                <CRow>
                  <!-- Vendor -->

                  <CCol md="12" class="mb-3">
                    <!-- First Name -->
                    <CFormLabel>Plan Name</CFormLabel>
                    <input
                      v-model.trim="form.name"
                      type="text"
                      class="form-control"
                      :class="{
                        'is-invalid': submitted && isInvalid('name'),
                      }"
                      @input="validateField('name')" />
                    <CFormFeedback invalid> {{ errors?.name?.message }}. </CFormFeedback>
                  </CCol>

                  <CCol md="12" class="mb-3">
                    <!-- Phone -->
                    <CFormLabel>Durations(Days)</CFormLabel>
                    <input
                      v-model.trim="form.duration"
                      type="number"
                      class="form-control"
                      :class="{
                        'is-invalid': submitted && isInvalid('duration'),
                      }"
                      @input="validateField('duration')" />
                    <CFormFeedback invalid>
                      {{ errors?.duration?.message }}
                    </CFormFeedback>
                  </CCol>

                  <CCol md="12" class="mb-3">
                    <!-- City -->
                    <CFormLabel>Price</CFormLabel>
                    <input
                      v-model.trim="form.price"
                      required
                      class="form-control"
                      :class="{
                        'is-invalid': submitted && isInvalid('price'),
                      }"
                      @input="validateField('price')" />
                    <CFormFeedback invalid>
                      {{ errors?.price?.message }}
                    </CFormFeedback>
                  </CCol>

                  <CCol md="12" class="mb-3">
                    <!-- Last Name -->
                    <CFormLabel>Description</CFormLabel>
                    <ckeditor
                      :editor="ClassicEditor"
                      :config="editorConfig"
                      v-model="form.description"
                      @input="validateField('description')"
                      :class="{ 'is-invalid': submitted && isInvalid('description') }" />
                    <CFormFeedback invalid>
                      {{ errors?.description?.message }}
                    </CFormFeedback>
                  </CCol>
                    <CCol md="12" class="mb-3">
                    <!-- First Name -->
                    <CFormLabel>Sort order</CFormLabel>
                    <input
                      v-model.trim="form.sort_order"
                      type="number"
                      class="form-control"
                      :class="{
                        'is-invalid': submitted && isInvalid('sort_order'),
                      }"
                      @input="validateField('sort_order')" />
                    <CFormFeedback invalid> {{ errors?.sort_order?.message }}. </CFormFeedback>
                  </CCol>
                </CRow>
              </CCol>
            </div>

            <!-- 2. Sticky Action Buttons Container -->
            <div class="form-sticky-actions">
              <CCol :xs="12">
                <div class="d-flex gap-2 justify-content-end">
                  <CButton type="button" color="secondary" variant="outline" :disabled="btnLoading" @click="resetForm">
                    <CIcon :icon="cilX" class="me-1" />
                    Clear
                  </CButton>
                  <CButton type="submit" color="primary" :disabled="btnLoading">
                    <CSpinner v-if="btnLoading" size="sm" class="me-1" />
                    <CIcon v-else :icon="cilSave" class="me-1" />
                    {{ editMode ? 'Update' : 'Save' }}
                  </CButton>
                </div>
              </CCol>
            </div>
          </CForm>
        </CCardBody>
      </CCard>
    </CCol>
  </CRow>
</template>
