<script setup>
import { ref, reactive, onMounted, computed, nextTick, onUnmounted, watch } from 'vue';
import { CModal, CModalHeader, CModalTitle, CModalBody, CModalFooter } from '@coreui/vue';

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

import {
  CRow,
  CCol,
  CCard,
  CCardHeader,
  CCardBody,
  CForm,
  CFormLabel,
  CFormInput,
  CFormFeedback,
  CButton,
  CSpinner,
  CFormSelect,
  CFormSwitch,
} from '@coreui/vue';

import CIcon from '@coreui/icons-vue';

import {
  cilPencil,
  cilTrash,
  cilPlus,
  cilSave,
  cilX,
  cilSearch,
  cilReload,
  cilCheckCircle,
  cilEyedropper,
  cilLowVision,
  cilLockUnlocked,
} from '@coreui/icons';

import Swal from 'sweetalert2';

import { useToast } from 'vue-toastification';

import { CDatePicker } from '@coreui/vue-pro';

import OrganisationCandidateProfile from '@/views/user/OrganisationCandidateProfile.vue';
import AdminCandidateProfile from '@/views/user/AdminCandidateProfile.vue';

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

import { CRating } from '@coreui/vue-pro';

import { CFormCheck } from '@coreui/vue';

import '@coreui/coreui-pro/dist/css/coreui.css';

/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

const enrolments = ref([]);
const vendors = ref([]);
const designations = ref([]);
const religions = ref([]);
const languages = ref([]);
const areas = ref([]);
const vendorSearch = ref(null);

const loading = computed(() => loadingStore.loading);
const vendorLoading = computed(() => loadingStore.loading);

const btnLoading = ref(false);

const submitted = ref(false);

const validated = ref(false);

const editMode = ref(false);

// Initialize the auth store before step rules reference the current user role.
const authStore = useAuthStore();

// Enrolment wizard: uses the same horizontal stepper classes as PatientManagement.vue.
const activeStep = ref(1);
const steps = [
  'Basic Information',
  'Address Information',
  'Education & Professional',
  'Documents & Verification',
  'Placement & Reviews',
  'Confirmation',
];

// Fields are validated step-by-step. Optional fields are intentionally excluded.
const requiredFieldsByStep = {
  1: ['first_name', 'last_name', 'gender', 'email', 'phone'],
  2: ['address', 'city', 'state', 'pin_code'],
  3: [...(authStore.user?.role === 'admin' ? ['vendor_id'] : []), 'highest_qualification', 'designation'],
  4: [], // Document uploads are checked separately below.
  5: [],
  6: [],
};

const scrollToStepError = async () => {
  await nextTick();
  const formElement = document.querySelector('.enrolment-wizard-form');
  const firstError = formElement?.querySelector('.is-invalid, .upload-required');
  firstError?.scrollIntoView({ behavior: 'smooth', block: 'center' });
};

const validateCurrentStep = () => {
  submitted.value = true;
  const fields = requiredFieldsByStep[activeStep.value] || [];

  fields.forEach((field) => validateField(field));
  let valid = fields.every((field) => {
    try {
      return !isInvalid(field);
    } catch {
      return false;
    }
  });

  // On a new enrolment, all three documents are mandatory.
  // In edit mode, the existing document preview also satisfies the requirement.
  if (activeStep.value === 4) {
    const documentsValid = Boolean(profilePreviewUrl.value) && Boolean(aadhaarPreviewUrl.value) && Boolean(certificatePreviewUrl.value);
    valid = documentsValid;
  }

  if (!valid) {
    scrollToStepError();
    return false;
  }

  submitted.value = false;
  return true;
};

const goToStep = (step) => {
  if (step < 1 || step > steps.length || step === activeStep.value) return;

  // Existing records retain free tab navigation while editing.
  if (editMode.value) {
    activeStep.value = step;
    submitted.value = false;
    return;
  }

  // In Add mode, users can return to previous steps. A future step is
  // accessible only through Continue after the current step validates.
  if (step < activeStep.value) {
    activeStep.value = step;
    submitted.value = false;
    return;
  }

  if (step === activeStep.value + 1) nextStep();
};

const nextStep = () => {
  if (activeStep.value >= steps.length) return;
  if (!validateCurrentStep()) return;
  activeStep.value += 1;
  submitted.value = false;
};

const previousStep = () => {
  if (activeStep.value > 1) activeStep.value -= 1;
};

const editingId = ref(null);

const totalRecords = ref(0);

const PhotoFile = ref(null);
const AdharFile = ref(null);
const CertificateFile = ref(null);

const formSection = ref(null);

const selectedEnrolments = ref([]);
const visibleLiveDemo = ref(false);
const approving = ref(false);
const statusTypes = ref([
  { label: 'Active', value: 'A' },
  { label: 'Pending', value: 'P' },
]);
const viewData = ref(null);

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
const allowedPdfTypes = allowedTypes;
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

  religion_id: {
    default: null,
    required: false,
  },

  language_id: {
    default: null,
    required: false,
  },

  area_id: {
    default: null,
    required: false,
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
  middle_name: { default: null, required: false },
  date_of_birth: { default: null, required: false },
  alternate_phone: { default: null, required: false },
  district: { default: null, required: false },
  professional_summary: { default: null, required: false },
  primary_role: { default: null, required: false },
  career_status: { default: null, required: false },
  availability_status: { default: null, required: false },
  placement_status: { default: null, required: false },
  preferred_duty_types: { default: null, required: false },
  preferred_locations: { default: null, required: false },
  verification_status: { default: null, required: false },
  verification_remark: { default: null, required: false },
  profile_type: { default: null, required: false },
  status: { default: 'P', required: false },
  hospital_id: { default: null, required: false },
  is_engaged: { default: false, required: false },
  emergency_contact_name: { default: null, required: false },
  emergency_contact_number: { default: null, required: false },
  emergency_contact_relationship: { default: null, required: false },
  verified_at: { default: null, required: false },
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
  designation: null,
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

// Fetch Religion, Language and Area master data
const getMasters = async () => {
  try {
    const response = await api.post('/masters');

    if (response.data?.status) {
      const data = response.data.data || {};

      religions.value = data.religions || [];
      languages.value = data.languages || [];
      areas.value = data.areas || [];
    }
  } catch (error) {
    console.error('Master Data Error:', error);
    toast.error(error.response?.data?.message || 'Unable to load master data.');
  }
};

const onPage = (event) => {
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
  activeStep.value = 1;

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
| Release Enrolment
|--------------------------------------------------------------------------
*/
const releaseEnrolement = (id) => {
  Swal.fire({
    title: 'Are you sure?',

    text: 'You are about to release this employee. This action will release the employee from the current hospital.',

    icon: 'warning',

    showCancelButton: true,

    confirmButtonText: 'Yes, release employee!',

    cancelButtonText: 'Cancel',
  }).then(async (result) => {
    if (!result.isConfirmed) {
      return;
    }
    try {
      const response = await api.post('/enrolment/release', {
        id: id,
      });
      if (response.data.status) {
        toast.success(response.data.message);
        getEnrolments();
      } else {
        toast.error(response.data.message);
      }
    } catch (error) {
      console.error(error);

      toast.error(error.response?.data?.message);
    }
  });
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
    visibleLiveDemo.value = false;
    const response = await api.post('/enrolment/view', { id: id });
    const result = response.data.data;
    if (response.data.status) {
      patchValue({
        ...result,
        designation: result?.designation?.id ?? result?.designation ?? null,
        religion_id: result?.religion_id ?? result?.preferred_religion?.id ?? null,
        language_id: result?.language_id ?? result?.language?.id ?? null,
        area_id: result?.area_id ?? result?.area?.id ?? null,
      });

      editingId.value = result.id;

      editMode.value = true;
      activeStep.value = 1;

      validated.value = false;

      profilePreviewUrl.value = result?.profilePreviewUrl;

      aadhaarPreviewUrl.value = result?.aadhaarPreviewUrl;

      certificatePreviewUrl.value = result?.certificatePreviewUrl;

      vendorSearch.value = result?.vendor ? { id: result.vendor.id, name: result.vendor.name } : null;

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
    const response = await api.post('/enrolment/view', { id: id });
    const result = response.data.data;
    if (response.data.status) {
      viewData.value = result;
      visibleLiveDemo.value = true;
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

    const stepFields = [
      ['first_name', 'last_name', 'middle_name', 'gender', 'date_of_birth', 'email', 'phone', 'alternate_phone'],
      ['address', 'city', 'district', 'state', 'pin_code', 'area_id'],
      [
        'vendor_id',
        'religion_id',
        'language_id',
        'highest_qualification',
        'designation',
        'experience',
        'specialization',
        'professional_summary',
        'primary_role',
      ],
      ['profile_photo_file', 'adhar_pdf_file', 'certificate_pdf_file'],
      ['placement_location', 'placement_start', 'placement_end', 'rating', 'remark', 'fb_reviews', 'insta_reviews', 'google_reviews'],
    ];
    const invalidStep = stepFields.findIndex((fields) =>
      fields.some((key) => {
        try {
          return isInvalid(key);
        } catch {
          return false;
        }
      })
    );
    if (invalidStep !== -1) activeStep.value = invalidStep + 1;

    nextTick(() => {
      const firstErrorEl = formElement.querySelector('.is-invalid, :invalid');
      if (firstErrorEl) firstErrorEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
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
  breadcrumbs.value = ['Manpower'];
  getEnrolments();
  getDesignations();
  getMasters();
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
  vendorSearch.value = null;
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
    if (response.data.status) {
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
    if (response.data.status) {
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
const toogleStatus = async (id = null) => {
  try {
    const response = await api.post('/enrolment/status/toggle', { id: id });
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
  <CRow class="patient-management-page"> 
    <!-- ====================================================== -->
    <!-- FORM -->
    <!-- ====================================================== -->

    <CCol :lg="12" :md="12" :sm="12" ref="formSection" v-if="authStore.user.role != 'hospital'">
      <CCard class="mb-4 onboarding-panel">
        <CCardHeader class="pb-card-header">
          <div class="pb-card-header-left">
            <div class="pb-card-icon">
              <CIcon :icon="cilPlus" />
            </div>
            <div class="pb-card-title">{{ editMode ? 'Edit Manpower' : 'Add Manpower' }}</div>
          </div>

          <CButton v-if="editMode" color="primary" variant="outline" size="sm" @click="addNew">
            <CIcon :icon="cilX" class="me-1" />
            New
          </CButton>
        </CCardHeader>

        <CCardBody class="px-0">
          <CForm
            class="enrolment-wizard-form needs-validation layout-fixed-form"
            novalidate
            :validated="validated"
            @submit.prevent="saveEnrolment">
            <div class="form-scroll-body">
              <!-- Same horizontal stepper structure/classes used by PatientManagement.vue -->
              <div class="stepper">
                <div
                  v-for="(step, index) in steps"
                  :key="step"
                  class="stepper-item"
                  :class="{
                    completed: index + 1 < activeStep,
                    current: index + 1 === activeStep,
                  }"
                  @click="goToStep(index + 1)"
                  :aria-disabled="!editMode && index + 1 > activeStep + 1"
                  :style="{ cursor: editMode || index + 1 <= activeStep + 1 ? 'pointer' : 'not-allowed' }">
                  <button type="button" class="step-number">
                    <CIcon v-if="index + 1 < activeStep" :icon="cilCheckCircle" />
                    <span v-else>{{ index + 1 }}</span>
                  </button>
                  <span class="step-label">{{ step }}</span>
                </div>
              </div>

              <div class="enrolment-step-panel">
                <!-- STEP 1: BASIC INFORMATION -->
                <section v-if="activeStep === 1" class="enrolment-step-section review-content-wrapper">
                  <h5 class="enrolment-section-heading pb-section-title review-section-title mt-0">Basic Information</h5>
                  <p class="enrolment-section-description">Enter caregiver personal details and contact information.</p>
                  <CRow>
                    <CCol md="4" class="mb-3">
                      <!-- First Name -->
                      <CFormLabel>First Name <span class="required-mark">*</span></CFormLabel>
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
                    <CCol md="4" class="mb-3">
                      <CFormLabel>Middle Name</CFormLabel>
                      <input v-model.trim="form.middle_name" class="form-control" />
                    </CCol>
                    <CCol md="4" class="mb-3">
                      <!-- Last Name -->
                      <CFormLabel>Last Name <span class="required-mark">*</span></CFormLabel>
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
                    <CCol md="4" class="mb-3">
                      <!-- Last Name -->
                      <CFormLabel>Gender <span class="required-mark">*</span></CFormLabel>
                      <CFormSelect
                        size="md"
                        class="form-control"
                        aria-label="Large select example"
                        v-model.trim="form.gender"
                        :class="{
                          'is-invalid': submitted && isInvalid('gender'),
                        }"
                        @change="validateField('gender')">
                        <option value="">Gender</option>
                        <option value="M">Male</option>
                        <option value="F">Female</option>
                      </CFormSelect>
                      <CFormFeedback invalid> {{ errors?.gender?.message }}. </CFormFeedback>
                    </CCol>
                    <CCol md="4" class="mb-3">
                      <!-- Email -->
                      <CFormLabel>Email <span class="required-mark">*</span></CFormLabel>
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
                    <CCol md="4" class="mb-3">
                      <CFormLabel>Date of Birth</CFormLabel>
                      <CDatePicker v-model:date="form.date_of_birth" format="dd/MM/yyyy" />
                    </CCol>

                    <CCol md="6" class="mb-3">
                      <!-- Phone -->
                      <CFormLabel>Phone No <span class="required-mark">*</span></CFormLabel>
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
                      <CFormLabel>Alternate Phone</CFormLabel>
                      <input v-model.trim="form.alternate_phone" class="form-control" maxlength="10" />
                    </CCol>

                    <CCol md="4" class="mb-3">
                      <CFormLabel>Emergency Contact Name</CFormLabel>
                      <input v-model.trim="form.emergency_contact_name" class="form-control" />
                    </CCol>
                    <CCol md="4" class="mb-3">
                      <CFormLabel>Emergency Contact Number</CFormLabel>
                      <input v-model.trim="form.emergency_contact_number" class="form-control" maxlength="10" />
                    </CCol>
                    <CCol md="4" class="mb-3">
                      <CFormLabel>Emergency Contact Relationship</CFormLabel>
                      <input v-model.trim="form.emergency_contact_relationship" class="form-control" />
                    </CCol>
                  </CRow>
                </section>

                <!-- STEP 2: ADDRESS INFORMATION -->
                <section v-if="activeStep === 2" class="enrolment-step-section">
                  <h5 class="enrolment-section-heading">Address Information</h5>
                  <p class="enrolment-section-description">Enter caregiver address and location details.</p>
                  <CRow>
                    <CCol md="6" class="mb-3">
                      <!-- Address -->
                      <CFormLabel>Address <span class="required-mark">*</span></CFormLabel>
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
                      <CFormLabel>City <span class="required-mark">*</span></CFormLabel>
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
                      <CFormLabel>State <span class="required-mark">*</span></CFormLabel>
                      <Multiselect
                        v-model="form.state"
                        :options="indianStates"
                        :searchable="true"
                        :close-on-select="true"
                        :show-labels="false"
                        placeholder="Choose State"
                        :allow-empty="true"
                        @clear="((form.state = null), validateField('state'))"
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
                      <CFormLabel>Pin <span class="required-mark">*</span></CFormLabel>
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
                    <!-- Area -->
                    <CCol md="6" class="mb-3">
                      <CFormLabel>Area</CFormLabel>
                      <select
                        v-model="form.area_id"
                        class="form-control"
                        :class="{ 'is-invalid': submitted && isInvalid('area_id') }"
                        @change="validateField('area_id')">
                        <option :value="null">Select Area</option>
                        <option v-for="item in areas" :key="item.id" :value="item.id">
                          {{ item.name }}
                        </option>
                      </select>
                      <CFormFeedback invalid>
                        {{ errors?.area_id?.message }}
                      </CFormFeedback>
                    </CCol>

                    <CCol md="6" class="mb-3">
                      <CFormLabel>District</CFormLabel>
                      <input v-model.trim="form.district" class="form-control" />
                    </CCol>
                  </CRow>
                </section>

                <!-- STEP 3: EDUCATION & PROFESSIONAL -->
                <section v-if="activeStep === 3" class="enrolment-step-section">
                  <h5 class="enrolment-section-heading">Education & Professional</h5>
                  <p class="enrolment-section-description">Enter qualification, experience and professional preferences.</p>
                  <CRow>
                    <!-- Vendor -->
                    <CCol md="6" class="mb-3" v-if="authStore.user?.role == 'admin'">
                      <CFormLabel>Vendor <span class="required-mark">*</span></CFormLabel>
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
                        {{ errors?.vendor_id?.message }}
                      </CFormFeedback>
                    </CCol>
                    <!-- Religion -->
                    <CCol md="6" class="mb-3">
                      <CFormLabel>Religion</CFormLabel>
                      <select
                        v-model="form.religion_id"
                        class="form-control"
                        :class="{ 'is-invalid': submitted && isInvalid('religion_id') }"
                        @change="validateField('religion_id')">
                        <option :value="null">Select Religion</option>
                        <option v-for="item in religions" :key="item.id" :value="item.id">
                          {{ item.name }}
                        </option>
                      </select>
                      <CFormFeedback invalid>
                        {{ errors?.religion_id?.message }}
                      </CFormFeedback>
                    </CCol>

                    <!-- Preferred Language -->
                    <CCol md="6" class="mb-3">
                      <CFormLabel>Preferred Language</CFormLabel>
                      <select
                        v-model="form.language_id"
                        class="form-control"
                        :class="{ 'is-invalid': submitted && isInvalid('language_id') }"
                        @change="validateField('language_id')">
                        <option :value="null">Select Language</option>
                        <option v-for="item in languages" :key="item.id" :value="item.id">
                          {{ item.name }}
                        </option>
                      </select>
                      <CFormFeedback invalid>
                        {{ errors?.language_id?.message }}
                      </CFormFeedback>
                    </CCol>
                    <CCol md="6" class="mb-3">
                      <!-- Highest Qualification -->
                      <CFormLabel>Highest Qualification <span class="required-mark">*</span></CFormLabel>
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
                      <CFormLabel>Designation <span class="required-mark">*</span></CFormLabel>
                      <select
                        v-model="form.designation"
                        class="form-control"
                        :class="{
                          'is-invalid': submitted && isInvalid('designation'),
                        }"
                        @change="validateField('designation')">
                        <option :value="null">Designation</option>
                        <!-- Loop starts here -->
                        <option v-for="item in designations" :key="item.id" :value="item.id">
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
                        @input="validateField('experience')" />
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
                      <CFormLabel>Profile Type</CFormLabel>
                      <input v-model.trim="form.profile_type" class="form-control" />
                    </CCol>
                    <CCol md="6" class="mb-3">
                      <CFormLabel>Primary Role</CFormLabel>
                      <input v-model.trim="form.primary_role" class="form-control" />
                    </CCol>
                    <CCol md="6" class="mb-3">
                      <CFormLabel>Career Status</CFormLabel>
                      <input v-model.trim="form.career_status" class="form-control" />
                    </CCol>
                    <CCol md="6" class="mb-3">
                      <CFormLabel>Availability Status</CFormLabel>
                      <input v-model.trim="form.availability_status" class="form-control" />
                    </CCol>
                    <CCol md="6" class="mb-3">
                      <CFormLabel>Preferred Duty Types</CFormLabel>
                      <input v-model="form.preferred_duty_types" class="form-control" placeholder="Enter duty types separated by commas" />
                    </CCol>
                    <CCol md="6" class="mb-3">
                      <CFormLabel>Preferred Locations</CFormLabel>
                      <input
                        v-model="form.preferred_locations"
                        class="form-control"
                        placeholder="Enter preferred locations separated by commas" />
                    </CCol>
                    <CCol md="12" class="mb-3">
                      <CFormLabel>Professional Summary</CFormLabel>
                      <textarea v-model.trim="form.professional_summary" class="form-control" rows="3"></textarea>
                    </CCol>
                  </CRow>
                </section>

                <!-- STEP 4: DOCUMENTS -->
                <section v-if="activeStep === 4" class="enrolment-step-section">
                  <h5 class="enrolment-section-heading">Documents & Verification</h5>
                  <p class="enrolment-section-description">Upload profile photo, Aadhaar and qualification certificate.</p>
                  <CRow>
                    <CCol md="6" class="mb-3">
                      <!-- ========================================================= PROFILE PHOTO===================================== -->

                      <div class="document-upload-group">
                        <div class="document-label">
                          <div>
                            <h6>Profile Photo <span class="required-mark">*</span></h6>
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
                            <h6>Aadhaar Card <span class="required-mark">*</span></h6>
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
                            <h6>Certificate <span class="required-mark">*</span></h6>
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
                </section>

                <!-- STEP 5: PLACEMENT & REVIEWS -->
                <section v-if="activeStep === 5" class="enrolment-step-section">
                  <h5 class="enrolment-section-heading">Placement & Reviews</h5>
                  <p class="enrolment-section-description">Enter placement details, ratings and review information.</p>
                  <CRow>
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
                      <CFormLabel>Placement Status</CFormLabel>
                      <input v-model.trim="form.placement_status" class="form-control" />
                    </CCol>
                    <CCol md="6" class="mb-3">
                      <CFormLabel>Verification Status</CFormLabel>
                      <input v-model.trim="form.verification_status" class="form-control" />
                    </CCol>
                    <CCol md="6" class="mb-3">
                      <CFormLabel>Hospital ID</CFormLabel>
                      <input v-model.number="form.hospital_id" type="number" class="form-control" />
                    </CCol>
                    <CCol md="6" class="mb-3">
                      <CFormLabel>Verified At</CFormLabel>
                      <CDatePicker v-model:date="form.verified_at" format="dd/MM/yyyy" />
                    </CCol>
                    <CCol md="6" class="mb-3">
                      <CFormLabel>Record Status</CFormLabel>
                      <select v-model="form.status" class="form-control">
                        <option value="A">Active</option>
                        <option value="P">Pending</option>
                      </select>
                    </CCol>
                    <CCol md="6" class="mb-3">
                      <CFormCheck v-model="form.is_engaged" label="Currently Engaged" />
                    </CCol>
                    <CCol md="12" class="mb-3">
                      <CFormLabel>Verification Remark</CFormLabel>
                      <textarea v-model.trim="form.verification_remark" class="form-control" rows="2"></textarea>
                    </CCol>
                  </CRow>
                </section>

                <!-- STEP 6: CONFIRMATION -->
                <section v-if="activeStep === 6" class="enrolment-step-section">
                  <h5 class="enrolment-section-heading">Confirmation</h5>
                  <p class="enrolment-section-description">Review the details before saving the enrolment.</p>
                  <div class="review-summary-grid enrolment-review-grid">
                    <div class="review-summary-card enrolment-review-card">
                      <div class="review-summary-card-header review-header-blue">Personal Information</div>
                      <div class="review-summary-card-body">
                        <div class="review-value-row">
                          <span>Name</span
                          ><strong>{{ [form.first_name, form.middle_name, form.last_name].filter(Boolean).join(' ') || '-' }}</strong>
                        </div>
                        <div class="review-value-row">
                          <span>Gender</span><strong>{{ form.gender || '-' }}</strong>
                        </div>
                        <div class="review-value-row">
                          <span>Date of Birth</span><strong>{{ form.date_of_birth || '-' }}</strong>
                        </div>
                        <div class="review-value-row">
                          <span>Email</span><strong>{{ form.email || '-' }}</strong>
                        </div>
                        <div class="review-value-row">
                          <span>Phone</span><strong>{{ form.phone || '-' }}</strong>
                        </div>
                        <div class="review-value-row">
                          <span>Alternate Phone</span><strong>{{ form.alternate_phone || '-' }}</strong>
                        </div>
                      </div>
                    </div>
                    <div class="enrolment-review-card review-summary-card">
                      <div class="review-summary-card-header review-header-red">Address</div>
                      <div class="review-summary-card-body">
                        <div class="review-value-row">
                          <span>Address</span><strong>{{ form.address || '-' }}</strong>
                        </div>
                        <div class="review-value-row">
                          <span>City</span><strong>{{ form.city || '-' }}</strong>
                        </div>
                        <div class="review-value-row">
                          <span>District</span><strong>{{ form.district || '-' }}</strong>
                        </div>
                        <div class="review-value-row">
                          <span>State</span><strong>{{ form.state || '-' }}</strong>
                        </div>
                        <div class="review-value-row">
                          <span>PIN Code</span><strong>{{ form.pin_code || '-' }}</strong>
                        </div>
                      </div>
                    </div>
                    <div class="enrolment-review-card review-summary-card">
                      <div class="review-summary-card-header review-header-green">Professional Information</div>
                      <div class="review-summary-card-body">
                        <div class="review-value-row">
                          <span>Qualification</span><strong>{{ form.highest_qualification || '-' }}</strong>
                        </div>
                        <div class="review-value-row">
                          <span>Designation</span
                          ><strong>{{ designations.find((item) => item.id === form.designation)?.name || '-' }}</strong>
                        </div>
                        <div class="review-value-row">
                          <span>Experience</span><strong>{{ form.experience || '-' }}</strong>
                        </div>
                        <div class="review-value-row">
                          <span>Specialization</span><strong>{{ form.specialization || '-' }}</strong>
                        </div>
                        <div class="review-value-row">
                          <span>Profile Type</span><strong>{{ form.profile_type || '-' }}</strong>
                        </div>
                      </div>
                    </div>
                    <div class="enrolment-review-card review-summary-card">
                      <div class="review-summary-card-header review-header-red">Documents</div>
                      <div class="review-summary-card-body">
                        <div class="review-value-row">
                          <span>Profile Photo</span><strong>{{ profilePreviewUrl ? 'Uploaded' : 'Not uploaded' }}</strong>
                        </div>
                        <div class="review-value-row">
                          <span>Aadhaar PDF</span><strong>{{ aadhaarPreviewUrl ? 'Uploaded' : 'Not uploaded' }}</strong>
                        </div>
                        <div class="review-value-row">
                          <span>Certificate PDF</span><strong>{{ certificatePreviewUrl ? 'Uploaded' : 'Not uploaded' }}</strong>
                        </div>
                      </div>
                    </div>
                    <div class="enrolment-review-card review-summary-card">
                      <div class="review-summary-card-header review-header-blue">Placement</div>
                      <div class="review-summary-card-body">
                        <div class="review-value-row">
                          <span>Location</span><strong>{{ form.placement_location || '-' }}</strong>
                        </div>
                        <div class="review-value-row">
                          <span>Start Date</span><strong>{{ form.placement_start || '-' }}</strong>
                        </div>
                        <div class="review-value-row">
                          <span>End Date</span><strong>{{ form.placement_end || '-' }}</strong>
                        </div>
                        <div class="review-value-row">
                          <span>Status</span><strong>{{ form.placement_status || '-' }}</strong>
                        </div>
                      </div>
                    </div>
                  </div>
                </section>
              </div>
            </div>
            <!-- /form-scroll-body -->

            <div class="form-sticky-actions enrolment-wizard-actions mt-3">
              <div class="d-flex gap-2 justify-content-between align-items-center w-100">
                <div>
                  <CButton type="button" color="light" variant="outline" :disabled="activeStep === 1 || btnLoading" @click="previousStep">
                    Previous
                  </CButton>
                </div>
                <div class="d-flex gap-2">
                  <CButton type="button" color="light" variant="outline" :disabled="btnLoading" @click="resetForm">
                    <CIcon :icon="cilX" class="me-1" /> Clear
                  </CButton>
                  <CButton v-if="activeStep < steps.length" type="button" color="primary" :disabled="btnLoading" @click="nextStep">
                    Continue <CIcon :icon="cilPlus" class="ms-1" />
                  </CButton>
                  <CButton v-else type="submit" color="primary" :disabled="btnLoading">
                    <CSpinner v-if="btnLoading" size="sm" class="me-1" />
                    <CIcon v-else :icon="cilSave" class="me-1" />
                    {{ editMode ? 'Update' : 'Save Enrolment' }}
                  </CButton>
                </div>
              </div>
            </div>
          </CForm>
        </CCardBody>
      </CCard>
    </CCol>
    <!-- ====================================================== -->
    <!-- LISTING -->
    <!-- ====================================================== -->

    <CCol :lg="12" :md="12" :sm="12">
      <CCard class="mb-4 onboarding-panel">
        <CCardHeader class="pb-card-header">
          <div class="pb-card-header-left">
            <div class="pb-card-icon">
              <CIcon :icon="cilPencil" />
            </div>
            <div class="pb-card-title">Manpower Lists</div>
          </div>
          <CButton color="primary" @click="addNew" v-if="authStore.user?.role != 'hospital'">
            <CIcon :icon="cilPlus" class="me-1" />

            Add New
          </CButton>
        </CCardHeader>

        <CCardBody class="px-0 pb-0">
          <CCard class="filter-card mb-4">
            <CCardBody class="py-4">
              <div class="row g-3 align-items-end">
                <!-- Acknowledgement -->
                <div class="col-lg-4 col-md-6">
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
                  <select class="form-control" v-model="filters.designation">
                    <option :value="null">Designation</option>
                    <!-- Loop starts here -->
                    <option v-for="item in designations" :key="item.id" :value="item.id">
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
          <div class="card onboarding-panel p-3 mb-0">
            <div class="bulk-approve-bar mb-3" v-if="authStore.user.role == 'admin' || authStore.user.role == 'hospital'">
              <div class="selected-info">
                <span class="selected-count">
                  {{ selectedEnrolments.length }}
                </span>

                <span class="selected-text"> records selected </span>
              </div>

              <CButton
                color="success"
                :disabled="selectedEnrolments.length === 0 || approving"
                @click="bulkApprove()"
                v-if="authStore.user.role == 'admin'">
                <CSpinner v-if="approving" size="sm" class="me-2" />
                <CIcon :icon="cilCheckCircle" class="me-2" v-else />
                Bulk Approve
              </CButton>
              <CButton
                color="success"
                :disabled="selectedEnrolments.length === 0"
                @click="bulkHire()"
                v-if="authStore.user.role == 'hospital'"
                style="color: white">
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
              :rowClass="(data) => (data.hospital_id ? 'has-placement' : '')"
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
              <Column
                selectionMode="multiple"
                headerStyle="width: 3rem"
                v-if="authStore.user.role == 'admin' || authStore.user.role == 'hospital'" />
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
              <Column header="Vendor" v-if="authStore.user.role != 'hospital'">
                <template #body="{ data }">
                  {{ data?.vendor?.name }}
                </template>
              </Column>
              <Column header="Address" field="address" v-if="authStore.user.role == 'hospital'" />

              <Column header="Phone" field="phone" v-if="authStore.user.role != 'hospital'" />
              <Column header="Qualification" field="highest_qualification" v-if="authStore.user.role == 'hospital'" />
              <Column header="Status">
                <template #body="{ data }">
                  <CFormSwitch
                    style="cursor: pointer"
                    :id="`formSwitchCheckChecked${data.id}`"
                    label=""
                    :checked="data.status === 'A'"
                    @click="toogleStatus(data.id)"
                    v-if="authStore.user.role == 'admin'" />
                  <Tag v-else :severity="data.status == 'A' ? 'success' : 'danger'" :value="data.status == 'A' ? 'Active' : 'Pending'" />
                </template>
              </Column>
              <Column header="Designation" #body="{ data }">{{ data?.designation?.name }}</Column>
              <Column header="Action">
                <template #body="{ data }">
                  <CButton
                    color="warning"
                    variant="outline"
                    size="sm"
                    class="me-2 mb-2"
                    @click="releaseEnrolement(data.id)"
                    v-if="data.hospital_id && authStore.user.role == 'admin'">
                    <CIcon :icon="cilLockUnlocked" />
                  </CButton>
                  <CButton color="warning" variant="outline" size="sm" class="me-2 mb-2" @click="viewEnrolment(data.id)">
                    <CIcon :icon="cilLowVision" />
                  </CButton>
                  <template v-if="authStore.user.role != 'hospital'">
                    <CButton color="warning" variant="outline" size="sm" class="me-2 mb-2" @click="editEnrolment(data.id)">
                      <CIcon :icon="cilPencil" />
                    </CButton>

                    <CButton color="danger" variant="outline" size="sm" class="me-2 mb-2" @click="deleteEnrolment(data.id)">
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
  </CRow>

  <CModal
    :visible="visibleLiveDemo"
    aria-labelledby="CandidateProfileModalTitle"
    size="xxl"
    alignment="center"
    scrollable
    @close="visibleLiveDemo = false">
    <CModalHeader>
      <CModalTitle id="CandidateProfileModalTitle">Candidate Profile</CModalTitle>
    </CModalHeader>

    <CModalBody class="p-0">
      <OrganisationCandidateProfile v-if="viewData && authStore.user?.role != 'admin'" :candidate="viewData" />
      <AdminCandidateProfile v-if="viewData && authStore.user?.role == 'admin'" :candidate="viewData" @edit="editEnrolment" />
    </CModalBody>

    <CModalFooter>
      <CButton color="secondary" @click="visibleLiveDemo = false"> Close </CButton>
    </CModalFooter>
  </CModal>
</template>
