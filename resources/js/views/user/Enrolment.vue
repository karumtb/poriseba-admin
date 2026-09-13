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
const viewData=ref(null);

watch(
  selectedEnrolments,
  (value) => {
    //console.log('SELECTED:', value)
  },
  { deep: true }
);

// Profile photo
const {
  file: profileFile,
  previewUrl: profilePreviewUrl,
  fileError: profileFileError,
  handleFileChange: handleProfileFileChange,
  clearFile: clearProfileFile,
} = useFileUpload();

// Aadhaar PDF
const {
  file: adharFile,
  previewUrl: aadhaarPreviewUrl,
  fileError: adharFileError,
  handleFileChange: handleAdharFileChange,
  clearFile: clearAdharFile,
} = useFileUpload();

// Certificate PDF
const {
  file: certificateFile,
  previewUrl: certificatePreviewUrl,
  fileError: certificateFileError,
  handleFileChange: handleCertificateFileChange,
  clearFile: clearCertificateFile,
} = useFileUpload();

const allowedTypes = ['application/pdf'];
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
  ...(authStore.user?.role === 'admin' && {
    vendor_id: {
      default: null,
      required: true,
      message: 'Vendor is required',
    },
  }),

  first_name: {
    default: null,
    required: true,
    message: 'First Name is required',
  },
  last_name: {
    default: null,
    required: true,
    message: 'Last Name is required',
  },

  email: {
    default: null,
    required: true,
    email: true,
    messages: {
      required: 'Email is required',
      pattern: 'Invalid Email Address',
    },
  },

  phone: {
    default: null,
    required: true,
    pattern: /^[0-9]{10}$/,
    minLength: 10,
    maxLength: 10,
    messages: {
      required: 'Phone number is required',
      pattern: 'Phone must contain only digits',
      minLength: 'Phone must be exactly 10 digits',
      maxLength: 'Phone must be exactly 10 digits',
    },
  },

  address: {
    default: null,
    required: true,
    message: 'Address is required',
  },

  city: {
    default: null,
    required: true,
    message: 'City is required',
  },

  state: {
    default: null,
    required: true,
    message: 'State is required',
  },

  pin_code: {
    default: null,
    required: true,
    pattern: /^[0-9]{6}$/,
    messages: {
      required: 'Pin is required',
      pattern: 'Pin must be 6 digits',
    },
  },

  highest_qualification: {
    default: null,
    required: true,
    message: 'Highest Qualification is required',
  },

  designation: {
    default: null,
    required: true,
    message: 'Designation is required',
  },
  gender: {
    default: null,
    required: true,
    message: 'gender is required',
  },

  experience: {
    default: null,
    required: false,
    message: 'Experience is required',
  },

  specialization: {
    default: null,
    required: false,
    message: 'Specialization is required',
  },

  profile_photo: {
    default: null,
    required: () => editMode.value,
    message: 'Profile Photo is required',
  },

  adhar_pdf: {
    default: null,
    required: () => editMode.value,
    message: 'Aadhaar PDF is required',
  },

  certificate_pdf: {
    default: null,
    required: () => editMode.value,
    message: 'Certificate PDF is required',
  },
  profile_photo_file: {
    default: null,
    required: () => !editMode.value,
    message: 'Profile Photo is required',
  },

  adhar_pdf_file: {
    default: null,
    required: () => !editMode.value,
    message: 'Aadhaar PDF is required',
  },

  certificate_pdf_file: {
    default: null,
    required: () => !editMode.value,
    message: 'Certificate PDF is required',
  },
  rating: {
    default: null,
    required: false,
    message: 'Rating is required',
  },
  remark: {
    default: null,
    required: false,
    message: 'Customer Remarks is required',
  },
  placement_location: {
    default: null,
    required: false,
    message: 'Customer Remarks is required',
  },
  placement_start: {
    default: new Date(),
    required: false,
    message: 'Customer Remarks is required',
  },
  placement_end: {
    default: null,
    required: false,
    message: 'Customer Remarks is required',
  },
  fb_reviews: {
    default: null,
    required: false,
    message: 'Customer Remarks is required',
  },
  insta_reviews: {
    default: null,
    required: false,
    message: 'Customer Remarks is required',
  },
  google_reviews: {
    default: null,
    required: false,
    message: 'Customer Remarks is required',
  },
});

watch(profileFile, (newFile) => {
  if (newFile) {
    form.profile_photo_file = newFile;
  } else {
    console.log('File was removed.');
  }
});
watch(adharFile, (newFile) => {
  if (newFile) {
    form.adhar_pdf_file = newFile;
  } else {
    console.log('File was removed.');
  }
});
watch(certificateFile, (newFile) => {
  if (newFile) {
    form.certificate_pdf_file = newFile;
  } else {
    console.log('File was removed.');
  }
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

const getEnrolments = async () => {
  try {
    const page = lazyParams.value.page + 1;

    const response = await api.post('/enrolment/listing', {
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

  getEnrolments();
};
const searchFilters = () => {
  lazyParams.value.page = 0;
  getEnrolments();
};

const resetFilters = () => {
  lazyParams.value.page = 0;
  Object.assign(filters, defaultFilters);
  getEnrolments();
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

  PhotoFile.value = null;

  AdharFile.value = null;

  CertificateFile.value = null;

  profilePreviewUrl.value = null;

  aadhaarPreviewUrl.value = null;

  certificatePreviewUrl.value = null;
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

const editEnrolment = async (id) => {

  try {

    const response = await api.post('/enrolment/view', {id:id});
    const result = response.data.data;
    if(response.data.status)
    {

        patchValue({...result,designation:result?.designation?.id});

        editingId.value = result.id;

        editMode.value = true;

        validated.value = false;

        profilePreviewUrl.value = result?.profilePreviewUrl;

        aadhaarPreviewUrl.value = result?.aadhaarPreviewUrl;

        certificatePreviewUrl.value = result?.certificatePreviewUrl;

        vendorSearch.value = { id: result.vendor.id, name: result.vendor.name };

        formSection.value?.$el?.scrollIntoView({ behavior: 'smooth' });
    }

  } catch (error) {
    console.error('Enrolment Error:', error);
  } finally {
  }





};

/*
|--------------------------------------------------------------------------
| View
|--------------------------------------------------------------------------
*/

const viewEnrolment = async (id) => {

  try {

    const response = await api.post('/enrolment/view', {id:id});
    const result = response.data.data;
    if(response.data.status)
    {
        viewData.value=result;
        visibleLiveDemo.value=true;

    }

  } catch (error) {
    console.error('Enrolment Error:', error);
  } finally {
  }





};

/*
|--------------------------------------------------------------------------
| Save
|--------------------------------------------------------------------------
*/

const saveEnrolment = async (event) => {
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
   const formData = new FormData();

    Object.entries(form).forEach(([key, value]) => {
    if (value !== null && value !== undefined) {

        // Check if the value is an instance of a Date object
        if (value instanceof Date) {
        // Formats safely to YYYY-MM-DD using local time
        const year = value.getFullYear();
        const month = String(value.getMonth() + 1).padStart(2, '0');
        const day = String(value.getDate()).padStart(2, '0');

        value = `${year}-${month}-${day}`;
        }
        formData.append(key, value);
    }
    });


    const response = await api.post('/enrolment/save', formData);
    if (response.data.status) {
      toast.success(response.data.message || 'Saved successfully.');
      resetForm();

      await getEnrolments();
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

const deleteEnrolment = (id) => {
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
      const response = await api.post('/enrolment/remove', {
        id: id,
      });
      if (response.data.status) {
        toast.success(response.data.message || 'Deleted successfully.');
        getEnrolments();

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
| Mounted
|--------------------------------------------------------------------------
*/

onMounted(() => {
  breadcrumbs.value = ['Enrolment'];
  getEnrolments();
  getDesignations();
});

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
| Upload Trigger
|--------------------------------------------------------------------------
*/
const triggerPhotoUpload = () => {
  PhotoFile.value.click();
};
const triggerAdharUpload = () => {
  AdharFile.value.click();
};
const triggerCertificateUpload = () => {
  CertificateFile.value.click();
};
/*
|--------------------------------------------------------------------------
| Auto Complete
|--------------------------------------------------------------------------
*/
const searchSubject = new Subject();

const subscription = searchSubject
  .pipe(
    debounceTime(400),
    distinctUntilChanged(),

    switchMap((search) => {
      if (!search.trim()) {
        return of([]);
      }

      return from(
        api.post('/vendor/search', {
          search: search.trim(),
        })
      ).pipe(
        map((response) => response.data.data || []),

        catchError((error) => {
          console.error(error);
          return of([]);
        })
      );
    })
  )
  .subscribe((results) => {
    vendors.value = results;
  });

const searchVendors = (event) => {
  vendorSearch.value = event.query;
  searchSubject.next(event.query);
};

const selectVendor = (event) => {
  const vendor = event.value;
  form.vendor_id = vendor.id;

  validateField('vendor_id');
};

onUnmounted(() => {
  subscription.unsubscribe();
});
/*
|--------------------------------------------------------------------------
| Clear Vendor
|--------------------------------------------------------------------------
*/
const clearVendor = () => {
  vendorSearch = '';
  form.vendor_id = null;
  validateField('vendor_id');
};

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
    const response = await api.post('/enrolment/bulk-approve', {
      ids,
    });
    if(response.data.status)
    {
         toast.success(response.data.message || 'Selected enrolment records approved successfully.');

        // Clear selection
        selectedEnrolments.value = [];

        // Reload table
        getEnrolments();
    }

  } catch (error) {
    console.error(error);

    toast.error(error.response?.data?.message || 'Unable to approve selected records.');
  } finally {
    approving.value = false;
  }
};

/*
|--------------------------------------------------------------------------
| BULK HIRE
|--------------------------------------------------------------------------
*/
const bulkHire = async (id = null) => {
  // Single approve
  if (id) {
    selectedEnrolments.value = [{ id }];
  }
  if (!selectedEnrolments.value.length) {
    return;
  }

  try {

    const ids = selectedEnrolments.value.map((item) => item.id);
    const response = await api.post('/enrolment/hiring', {
      ids,
    });
    if(response.data.status)
    {
         toast.success(response.data.message || 'Selected enrolment records approved successfully.');
        // Clear selection
        selectedEnrolments.value = [];

        // Reload table
        getEnrolments();
    }

  } catch (error) {
    console.error(error);

    toast.error(error.response?.data?.message || 'Unable to approve selected records.');
  } finally {
    approving.value = false;
  }
};


/*
|--------------------------------------------------------------------------
| Toogle Status
|--------------------------------------------------------------------------
*/
const toogleStatus=async (id=null)=>{
try {
      const response = await api.post('/enrolment/status/toggle', {id: id});
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

    <CCol :lg="12" :md="12" :sm="12" >
      <CCard class="mb-4">
        <CCardHeader>
          <div class="d-flex justify-content-between align-items-center">
            <strong> Enrolment Lists </strong>

            <CButton color="primary" @click="addNew" v-if="authStore.user?.role != 'hospital'">
              <CIcon :icon="cilPlus" class="me-1" />

              Add New
            </CButton>
          </div>
        </CCardHeader>

        <CCardBody>
          <CCard class="filter-card mb-4">
            <CCardBody class="py-4">
              <div class="row g-3 align-items-end">
                <!-- Acknowledgement -->
                <div class="col-lg-3 col-md-6">
                  <input type="text" class="form-control" v-model.trim="filters.keyword" placeholder="Filter By Name,Phone No" size="lg" />
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
            <div class="bulk-approve-bar mb-3" v-if="authStore.user.role == 'admin' || authStore.user.role == 'hospital'">
              <div class="selected-info">
                <span class="selected-count">
                  {{ selectedEnrolments.length }}
                </span>

                <span class="selected-text"> records selected </span>
              </div>
              <CButton color="success" :disabled="selectedEnrolments.length === 0 || approving" @click="bulkApprove()" v-if="authStore.user.role == 'admin'">
                <CSpinner v-if="approving" size="sm" class="me-2" />
                <CIcon :icon="cilCheckCircle" class="me-2" v-else />
                Bulk Approve
              </CButton>
              <CButton color="success" :disabled="selectedEnrolments.length === 0" @click="bulkHire()" v-if="authStore.user.role == 'hospital'" style="color: white;">
                <CSpinner v-if="approving" size="sm" class="me-2" />
                <CIcon :icon="cilCheckCircle" class="me-2" v-else />
                Hiring Request
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
              :rowClass="(data) => data.hospital_id ? 'has-placement' : ''"
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
              <Column header="Vendor"  v-if="authStore.user.role != 'hospital'">
                <template #body="{ data }">
                  {{ data?.vendor?.name }}
                </template>
              </Column>
              <Column  header="Address" field="address"  v-if="authStore.user.role == 'hospital'"/>

              <Column header="Phone" field="phone"  v-if="authStore.user.role != 'hospital'" />
              <Column header="Qualification" field="highest_qualification"  v-if="authStore.user.role == 'hospital'" />
              <Column header="Status">
                <template #body="{ data }">
                  <CFormSwitch style="cursor: pointer;" id="formSwitchCheckChecked{{ data.id }}" label=""  :checked="data.status === 'A'" @click="toogleStatus(data.id)" v-if="authStore.user.role == 'admin'" />
                  <Tag v-else :severity="data.status == 'A' ? 'success' : 'danger'" :value="data.status == 'A' ? 'Active' : 'Pending'" />
                </template>
              </Column>
              <Column header="Designation" #body="{ data }" >{{ data?.designation?.name }}</Column>
              <Column header="Action">
                <template #body="{ data }">
                 <CButton color="warning" variant="outline" size="sm" class="me-2 mb-2" @click="viewEnrolment(data.id)">
                    <CIcon :icon="cilLowVision" />
                  </CButton>
                  <template v-if="authStore.user.role != 'hospital'">
                     <CButton color="warning" variant="outline" size="sm" class="me-2  mb-2" @click="editEnrolment(data.id)">
                    <CIcon :icon="cilPencil" />
                  </CButton>

                  <CButton color="danger" variant="outline" size="sm" class="me-2  mb-2" @click="deleteEnrolment(data.id)">
                    <CIcon :icon="cilTrash" />
                  </CButton>
                  </template>

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

    <CCol :lg="12" :md="12" :sm="12" ref="formSection" v-if="authStore.user.role != 'hospital'">
      <CCard class="mb-4">
        <CCardHeader>
          <div class="d-flex justify-content-between align-items-center">
            <strong>
              {{ editMode ? 'Edit Enrolment' : 'Add Enrolment' }}
            </strong>

            <CButton v-if="editMode" color="secondary" variant="outline" size="sm" @click="addNew">
              <CIcon :icon="cilX" class="me-1" />

              New
            </CButton>
          </div>
        </CCardHeader>

        <CCardBody>
          <CForm class="row g-3 needs-validation layout-fixed-form" novalidate :validated="validated" @submit.prevent="saveEnrolment">
            <!-- 1. Scrollable Fields Container -->
            <div class="form-scroll-body">
              <CCol :md="12">
                <CRow>
                  <!-- Vendor -->
                  <CCol md="6" class="mb-3" v-if="authStore.user?.role == 'admin'">
                    <CFormLabel>Vendor</CFormLabel>
                    <AutoComplete
                      v-model="vendorSearch"
                      :suggestions="vendors"
                      optionLabel="name"
                      placeholder="Search Vendor"
                      :loading="vendorLoading"
                      :minLength="1"
                      @complete="searchVendors"
                      @option-select="selectVendor"
                      class="w-100"
                      inputClass="w-100"
                      :class="{
                        'is-invalid': submitted && isInvalid('vendor_id'),
                      }" />

                    <CFormFeedback invalid>
                      {{ errors.vendor_id.message }}
                    </CFormFeedback>
                  </CCol>
                  <CCol md="6" class="mb-3">
                    <!-- First Name -->
                    <CFormLabel>First Name</CFormLabel>
                    <input
                      v-model.trim="form.first_name"
                      type="text"
                      class="form-control"
                      :class="{
                        'is-invalid': submitted && isInvalid('first_name'),
                      }"
                      @input="validateField('first_name')" />
                    <CFormFeedback invalid> {{ errors?.first_name?.message }}. </CFormFeedback>
                  </CCol>
                  <CCol md="6" class="mb-3">
                    <!-- Last Name -->
                    <CFormLabel>Last Name</CFormLabel>
                    <input
                      v-model.trim="form.last_name"
                      type="text"
                      class="form-control"
                      :class="{
                        'is-invalid': submitted && isInvalid('last_name'),
                      }"
                      @input="validateField('last_name')" />
                    <CFormFeedback invalid> {{ errors?.last_name?.message }}. </CFormFeedback>
                  </CCol>
                   <CCol md="6" class="mb-3">
                    <!-- Last Name -->
                    <CFormLabel>Gender</CFormLabel>
                    <CFormSelect size="lg" class="form-control " aria-label="Large select example"  v-model.trim="form.gender"
                      :class="{
                        'is-invalid': submitted && isInvalid('gender'),
                      }"
                      @change="validateField('gender')"
                    >
                    <option value="">Gender</option>
                    <option value="M">Male</option>
                    <option value="F">Female</option>
                   </CFormSelect>
                    <CFormFeedback invalid> {{ errors?.gender?.message }}. </CFormFeedback>
                  </CCol>
                  <CCol md="6" class="mb-3">
                    <!-- Email -->
                    <CFormLabel>Email</CFormLabel>
                    <input
                      v-model.trim="form.email"
                      type="text"
                      class="form-control"
                      :class="{
                        'is-invalid': submitted && isInvalid('email'),
                      }"
                      @input="validateField('email')" />
                    <CFormFeedback invalid> {{ errors?.email?.message }}. </CFormFeedback>
                  </CCol>
                  <CCol md="6" class="mb-3">
                    <!-- Phone -->
                    <CFormLabel>Phone No</CFormLabel>
                    <input
                      v-model.trim="form.phone"
                      type="text"
                      class="form-control"
                      :class="{
                        'is-invalid': submitted && isInvalid('phone'),
                      }"
                      @input="
                        form.phone = form.phone.replace(/\D/g, '');
                        validateField('phone');
                      "
                      maxlength="10"
                      minlength="10" />
                    <CFormFeedback invalid>
                      {{ errors?.phone?.message }}
                    </CFormFeedback>
                  </CCol>
                  <CCol md="6" class="mb-3">
                    <!-- Address -->
                    <CFormLabel>Address</CFormLabel>
                    <textarea
                      v-model.trim="form.address"
                      class="form-control"
                      :class="{
                        'is-invalid': submitted && isInvalid('address'),
                      }"
                      @input="validateField('address')"></textarea>
                    <CFormFeedback invalid>
                      {{ errors?.address?.message }}
                    </CFormFeedback>
                  </CCol>
                  <CCol md="6" class="mb-3">
                    <!-- City -->
                    <CFormLabel>City</CFormLabel>
                    <input
                      v-model.trim="form.city"
                      required
                      class="form-control"
                      :class="{
                        'is-invalid': submitted && isInvalid('city'),
                      }"
                      @input="validateField('city')" />
                    <CFormFeedback invalid>
                      {{ errors?.city?.message }}
                    </CFormFeedback>
                  </CCol>
                  <CCol md="6" class="mb-3">
                    <!-- State -->
                    <CFormLabel>State</CFormLabel>
                    <Multiselect
                      v-model="form.state"
                      :options="indianStates"
                      :searchable="true"
                      :close-on-select="true"
                      :show-labels="false"
                      placeholder="Choose State"
                      :allow-empty="true"
                      @clear="form.state = null"
                      :class="{
                        'is-invalid': submitted && isInvalid('state'),
                      }"
                      @select="validateField('state')">
                      <template #singleLabel="{ option }">
                        <div class="product-option">
                          <span>{{ option }}</span>
                        </div>
                      </template>
                    </Multiselect>

                    <CFormFeedback invalid> State is required </CFormFeedback>
                  </CCol>
                  <CCol md="6" class="mb-3">
                    <!-- Pin -->
                    <CFormLabel>Pin</CFormLabel>
                    <input
                      v-model.trim="form.pin_code"
                      class="form-control"
                      :class="{
                        'is-invalid': submitted && isInvalid('pin_code'),
                      }"
                      @input="
                        form.pin_code = form.pin_code.replace(/\D/g, '');
                        validateField('pin_code');
                      " />
                    <CFormFeedback invalid>
                      {{ errors?.pin_code?.message }}
                    </CFormFeedback>
                  </CCol>
                  <CCol md="6" class="mb-3">
                    <!-- Highest Qualification -->
                    <CFormLabel>Highest Qualification</CFormLabel>
                    <input
                      v-model.trim="form.highest_qualification"
                      class="form-control"
                      :class="{
                        'is-invalid': submitted && isInvalid('highest_qualification'),
                      }"
                      @input="validateField('highest_qualification')" />
                    <CFormFeedback invalid>
                      {{ errors?.highest_qualification?.message }}
                    </CFormFeedback>
                  </CCol>
                  <CCol md="6" class="mb-3">
                    <!-- Designation -->
                    <CFormLabel>Designation</CFormLabel>
                    <select  v-model="form.designation"

                      class="form-control"
                      :class="{
                        'is-invalid': submitted && isInvalid('designation'),
                      }"
                      @change="validateField('designation')">
                      <option :value="null">Designation</option>
                      <!-- Loop starts here -->
                        <option
                            v-for="item in designations"
                            :key="item.id"
                            :value="item.id">
                            {{ item.name }}
                        </option>
                    </select>

                    <CFormFeedback invalid>
                      {{ errors?.designation?.message }}
                    </CFormFeedback>
                  </CCol>
                  <CCol md="6" class="mb-3">
                    <!-- Experience -->
                    <CFormLabel>Experience</CFormLabel>
                    <input
                      v-model.trim="form.experience"
                      class="form-control"
                      :class="{
                        'is-invalid': submitted && isInvalid('experience'),
                      }"
                      @input="validateField('designation')" />
                    <CFormFeedback invalid>
                      {{ errors?.experience?.message }}
                    </CFormFeedback>
                  </CCol>
                  <CCol md="6" class="mb-3">
                    <!-- Specialization -->
                    <CFormLabel>Specialization</CFormLabel>
                    <textarea
                      v-model.trim="form.specialization"
                      class="form-control"
                      :class="{
                        'is-invalid': submitted && isInvalid('specialization'),
                      }"
                      @input="validateField('specialization')"></textarea>
                    <CFormFeedback invalid>
                      {{ errors?.specialization?.message }}
                    </CFormFeedback>
                  </CCol>

                  <CCol md="6" class="mb-3">
                    <!-- Location -->
                    <CFormLabel>Location</CFormLabel>
                    <input
                      type="text"
                      class="form-control"
                      v-model.trim="form.placement_location"
                      :class="{
                        'is-invalid': submitted && isInvalid('placement_location'),
                      }"
                      @input="validateField('placement_location')" />
                    <CFormFeedback invalid>
                      {{ errors?.placement_location?.message }}
                    </CFormFeedback>
                  </CCol>

                  <CCol md="6" class="mb-3">
                    <!-- Location -->
                    <CFormLabel>Placement Start Date</CFormLabel>
                    <CDatePicker v-model:date="form.placement_start" format="dd/MM/yyyy" />
                  </CCol>
                  <CCol md="6" class="mb-3">
                    <!-- Location -->
                    <CFormLabel>Placement End Date</CFormLabel>
                    <CDatePicker v-model:date="form.placement_end" format="dd/MM/yyyy" />
                  </CCol>

                  <CCol md="6" class="mb-3">
                    <!-- Location -->
                    <CFormLabel>Rating</CFormLabel>
                    <CRating v-model="form.rating" />
                  </CCol>

                  <CCol md="6" class="mb-3">
                    <!-- Remarks -->
                    <CFormLabel>Remarks</CFormLabel>
                    <ckeditor
                      :editor="ClassicEditor"
                      :config="editorConfig"
                      v-model="form.remark"
                      @input="validateField('remark')"
                      :class="{ 'is-invalid': submitted && isInvalid('remark') }" />
                    <CFormFeedback invalid>
                      {{ errors?.remark?.message }}
                    </CFormFeedback>
                  </CCol>

                  <CCol md="6" class="mb-3">
                    <!-- Remarks -->
                    <CFormLabel>FB Reviews</CFormLabel>
                    <ckeditor
                      :editor="ClassicEditor"
                      :config="editorConfig"
                      v-model="form.fb_reviews"
                      @input="validateField('fb_reviews')"
                      :class="{ 'is-invalid': submitted && isInvalid('fb_reviews') }" />
                    <CFormFeedback invalid>
                      {{ errors?.fb_reviews?.message }}
                    </CFormFeedback>
                  </CCol>
                  <CCol md="6" class="mb-3">
                    <!-- Remarks -->
                    <CFormLabel>Instagram Reviews</CFormLabel>
                    <ckeditor
                      :editor="ClassicEditor"
                      :config="editorConfig"
                      v-model="form.insta_reviews"
                      @input="validateField('insta_reviews')"
                      :class="{ 'is-invalid': submitted && isInvalid('insta_reviews') }" />
                    <CFormFeedback invalid>
                      {{ errors?.insta_reviews?.message }}
                    </CFormFeedback>
                  </CCol>
                  <CCol md="6" class="mb-3">
                    <!-- Remarks -->
                    <CFormLabel>Google Reviews</CFormLabel>
                    <ckeditor
                      :editor="ClassicEditor"
                      :config="editorConfig"
                      v-model="form.google_reviews"
                      @input="validateField('google_reviews')"
                      :class="{ 'is-invalid': submitted && isInvalid('google_reviews') }" />
                    <CFormFeedback invalid>
                      {{ errors?.google_reviews?.message }}
                    </CFormFeedback>
                  </CCol>

                  <CCol md="6" class="mb-3">
                    <!-- ========================================================= PROFILE PHOTO===================================== -->

                    <div class="document-upload-group">
                      <div class="document-label">
                        <div>
                          <h6>Profile Photo</h6>
                          <span>JPG, JPEG WEBP or PNG · Max 5 MB</span>
                        </div>

                        <span v-if="profilePreviewUrl" class="uploaded-badge"> ✓ Uploaded </span>
                      </div>

                      <div class="profile-upload-box">
                        <!-- Photo Preview -->
                        <div class="profile-image-wrapper">
                          <img v-if="profilePreviewUrl" :src="profilePreviewUrl" alt="Profile Photo" class="profile-image" />

                          <div v-else class="profile-image-placeholder">
                            <div class="placeholder-icon">👤</div>

                            <span>No photo</span>
                          </div>
                        </div>

                        <!-- Details -->
                        <div class="profile-upload-details">
                          <div v-if="profileFile" class="selected-filename">
                            {{ profileFile.name }}
                          </div>

                          <div v-else-if="profilePreviewUrl" class="selected-filename">Profile photo</div>

                          <div v-else class="selected-filename">No profile photo selected</div>

                          <div class="selected-file-info">
                            {{
                              profileFile
                                ? 'Photo selected successfully'
                                : profilePreviewUrl
                                  ? 'Saved profile photo'
                                  : 'Upload a clear profile photograph'
                            }}
                          </div>

                          <div class="file-actions">
                            <input
                              ref="PhotoFile"
                              type="file"
                              accept="image/jpeg,image/png,image/jpg,image/webp"
                              style="display: none"
                              @change="handleProfileFileChange($event, ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'], 5)" />

                            <button type="button" class="action-primary" @click="triggerPhotoUpload">
                              <span>📤</span>

                              {{ profilePreviewUrl ? 'Change Photo' : 'Upload Photo' }}
                            </button>

                            <button
                              v-if="profilePreviewUrl && !form.id"
                              type="button"
                              class="action-danger"
                              @click="clearProfileFile(PhotoFile)">
                              Remove
                            </button>
                          </div>
                        </div>
                      </div>

                      <div v-if="profileFileError" class="upload-error">
                        {{ profileFileError }}
                      </div>

                      <div v-if="submitted && !profilePreviewUrl" class="upload-required">Profile Photo is required</div>
                    </div>
                  </CCol>
                </CRow>

                <!-- =========================================================AADHAAR + CERTIFICATE========================================================= -->

                <CRow class="document-row">
                  <!-- ================= AADHAAR ================= -->

                  <CCol md="6">
                    <div class="document-upload-group">
                      <div class="document-label">
                        <div>
                          <h6>Aadhaar Card</h6>
                          <span>PDF · Max 5 MB</span>
                        </div>

                        <span v-if="aadhaarPreviewUrl" class="uploaded-badge"> ✓ Uploaded </span>
                      </div>

                      <div class="pdf-upload-box">
                        <!-- PDF Icon -->
                        <div class="pdf-file-icon">
                          <div class="pdf-fold"></div>

                          <span>PDF</span>
                        </div>

                        <!-- File Information -->
                        <div class="pdf-file-content">
                          <div v-if="adharFile" class="pdf-file-name">
                            {{ adharFile.name }}
                          </div>

                          <div v-else-if="aadhaarPreviewUrl" class="pdf-file-name">
                            {{ getFileName(aadhaarPreviewUrl) }}
                          </div>

                          <div v-else class="pdf-file-name empty">No Aadhaar document</div>

                          <div class="pdf-file-type">PDF Document</div>

                          <div class="pdf-actions">
                            <input
                              ref="AdharFile"
                              type="file"
                              accept="application/pdf"
                              style="display: none"
                              @change="handleAdharFileChange($event, allowedPdfTypes, 5)" />

                            <button type="button" class="action-primary small" @click="triggerAdharUpload">
                              {{ aadhaarPreviewUrl ? 'Replace' : 'Upload PDF' }}
                            </button>

                            <button v-if="aadhaarPreviewUrl" type="button" class="action-secondary" @click="openFile(aadhaarPreviewUrl)">
                              View
                            </button>

                            <button
                              v-if="aadhaarPreviewUrl && !form.id"
                              type="button"
                              class="action-danger"
                              @click="clearAdharFile(AdharFile)">
                              Remove
                            </button>
                          </div>
                        </div>
                      </div>

                      <div v-if="adharFileError" class="upload-error">
                        {{ adharFileError }}
                      </div>

                      <div v-if="submitted && !aadhaarPreviewUrl" class="upload-required">Aadhaar is required</div>
                    </div>
                  </CCol>

                  <!-- ================= CERTIFICATE ================= -->

                  <CCol md="6">
                    <div class="document-upload-group">
                      <div class="document-label">
                        <div>
                          <h6>Certificate</h6>
                          <span>PDF · Max 5 MB</span>
                        </div>

                        <span v-if="certificatePreviewUrl" class="uploaded-badge"> ✓ Uploaded </span>
                      </div>

                      <div class="pdf-upload-box">
                        <!-- PDF Icon -->
                        <div class="pdf-file-icon certificate-pdf">
                          <div class="pdf-fold"></div>

                          <span>PDF</span>
                        </div>

                        <!-- File Information -->
                        <div class="pdf-file-content">
                          <div v-if="certificateFile" class="pdf-file-name">
                            {{ certificateFile.name }}
                          </div>

                          <div v-else-if="certificatePreviewUrl" class="pdf-file-name">
                            {{ getFileName(certificatePreviewUrl) }}
                          </div>

                          <div v-else class="pdf-file-name empty">No certificate document</div>

                          <div class="pdf-file-type">PDF Document</div>

                          <div class="pdf-actions">
                            <input
                              ref="CertificateFile"
                              type="file"
                              accept="application/pdf"
                              style="display: none"
                              @change="handleCertificateFileChange($event, allowedPdfTypes, 5)" />

                            <button type="button" class="action-primary small" @click="triggerCertificateUpload">
                              {{ certificatePreviewUrl ? 'Replace' : 'Upload PDF' }}
                            </button>

                            <button
                              v-if="certificatePreviewUrl"
                              type="button"
                              class="action-secondary"
                              @click="openFile(certificatePreviewUrl)">
                              View
                            </button>

                            <button
                              v-if="certificatePreviewUrl && !form.id"
                              type="button"
                              class="action-danger"
                              @click="clearCertificateFile(CertificateFile)">
                              Remove
                            </button>
                          </div>
                        </div>
                      </div>

                      <div v-if="certificateFileError" class="upload-error">
                        {{ certificateFileError }}
                      </div>

                      <div v-if="submitted && !certificatePreviewUrl" class="upload-required">Certificate is required</div>
                    </div>
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

  <CModal
    :visible="visibleLiveDemo"
    aria-labelledby="LiveDemoExampleLabel"
    size="lg"
    alignment="center"
    @close="
      () => {
        visibleLiveDemo = false
      }
    "
  >
    <CModalHeader>
      <CModalTitle id="LiveDemoExampleLabel">View Enrolment</CModalTitle>
    </CModalHeader>
    <CModalBody> <ViewEnrolment v-if="viewData":data="viewData"/>
    </CModalBody>
    <CModalFooter>
      <CButton
        color="secondary"
        @click="
          () => {
            visibleLiveDemo = false
          }
        "
      >
        Close
      </CButton>
    </CModalFooter>
  </CModal>
</template>



