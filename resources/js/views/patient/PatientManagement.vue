<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { PATIENT_DROPDOWNS } from '@/constants/patientDropdowns';
import { indianStates } from '@/constants/indianStates';
import { useFormBuilder } from '@/form/useFormBuilder';
import { useFileUpload } from '@/services/useFileUpload';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import { CRow, CCol, CCard, CCardHeader, CCardBody, CButton, CFormInput, CFormSelect, CFormFeedback } from '@coreui/vue';
import CIcon from '@coreui/icons-vue';
import { CDatePicker } from '@coreui/vue-pro';
import { useToast } from 'vue-toastification';
const toast = useToast();
import moment from 'moment';
import {
  cilPeople,
  cilHeart,
  cilClock,
  cilCalendar,
  cilLocationPin,
  cilSearch,
  cilBell,
  cilUser,
  cilMedicalCross,
  cilFile,
  cilCloudUpload,
  cilPlus,
  cilArrowLeft,
  cilArrowRight,
  cilPencil,
  cilLowVision,
  cilOptions,
  cilCheckCircle,
  cilLayers,
  cilInfo,
  cilCheck,
} from '@coreui/icons';
import { useBreadcrumb } from '@/services/useBreadcrumb';
const { breadcrumbs } = useBreadcrumb();
import '@coreui/coreui-pro/dist/css/coreui.css';

// Optional record supplied by the parent when opening this component in edit mode.
const props = defineProps({
  patientData: {
    type: Object,
    default: null,
  },
});

/*
|--------------------------------------------------------------------------
| Static dashboard data
|--------------------------------------------------------------------------
| This component currently uses local sample data only.
| Later, replace these refs with API/service calls.
|--------------------------------------------------------------------------
*/

const activeSidebar = ref('Patient Management');
const activeSubmenu = ref('Patient Onboarding');
const activeStep = ref(1);
const activeRequestTab = ref('All Requests');
const searchText = ref('');
const showAddRequest = ref(false);
const agreementFile = ref(null);
const agreementInput = ref(null);
const currentPage = ref(1);

// Document input refs
const photoInput = ref(null);
const idProofInput = ref(null);
const medicalReportsInput = ref(null);
const consentFormInput = ref(null);
const privacyDocumentInput = ref(null);
const documentValidationAttempted = ref(false);

// Allowed file types and maximum upload size (MB)
const allowedPhotoTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
const allowedIdentityTypes = ['application/pdf', ...allowedPhotoTypes];
const allowedMedicalReportTypes = ['application/pdf', ...allowedPhotoTypes];
const allowedPdfTypes = ['application/pdf'];
const maxDocumentSize = 1;

// Profile photo
const {
  file: photoFile,
  previewUrl: photoPreviewUrl,
  fileError: photoFileError,
  handleFileChange: handlePhotoFileChange,
  clearFile: clearPhotoFile,
} = useFileUpload();

// ID proof
const {
  file: idProofFile,
  previewUrl: idProofPreviewUrl,
  fileError: idProofFileError,
  handleFileChange: handleIdProofFileChange,
  clearFile: clearIdProofFile,
} = useFileUpload();

// Medical reports
const {
  file: medicalReportsFile,
  previewUrl: medicalReportsPreviewUrl,
  fileError: medicalReportsFileError,
  handleFileChange: handleMedicalReportsFileChange,
  clearFile: clearMedicalReportsFile,
} = useFileUpload();

// Consent form
const {
  file: consentFormFile,
  previewUrl: consentFormPreviewUrl,
  fileError: consentFormFileError,
  handleFileChange: handleConsentFormFileChange,
  clearFile: clearConsentFormFile,
} = useFileUpload();

// Privacy document
const {
  file: privacyDocumentFile,
  previewUrl: privacyDocumentPreviewUrl,
  fileError: privacyDocumentFileError,
  handleFileChange: handlePrivacyDocumentFileChange,
  clearFile: clearPrivacyDocumentFile,
} = useFileUpload();
const rowsPerPage = ref(10);

const patient = ref({
  code: null,
  name: null,
  age: null,
  gender: null,
  location: null,
  status: null,
  progress: 0,
});

// Patient initials are shown until a photo has been selected.
const patientInitials = computed(() => {
  const name = patient.value.name?.trim();

  if (!name) return 'SD';

  return name
    .split(/\\s+/)
    .filter(Boolean)
    .slice(0, 2)
    .map((part) => part.charAt(0).toUpperCase())
    .join('');
});

/*
|--------------------------------------------------------------------------
| Patient onboarding forms
|--------------------------------------------------------------------------
| Form state and validation follow the shared useFormBuilder pattern.
| The existing template bindings and CSS classes are intentionally retained.
|--------------------------------------------------------------------------
*/

const {
  form: basicForm,
  errors: basicErrors,
  validate: validateBasic,
  validateField: validateBasicField,
  isInvalid: isBasicInvalid,
  reset: resetBasic,
  patchValue: patchBasicValue,
} = useFormBuilder({
  patientName: {
    default: '',
    required: true,
    message: 'Patient name is required',
  },
  dateOfBirth: {
    default: '',
    required: true,
    message: 'Date of birth is required',
  },
  age: { default: '', required: false },
  gender: {
    default: '',
    required: true,
    message: 'Gender is required',
  },
  primaryContactName: {
    default: '',
    required: true,
    message: 'Primary contact name is required',
  },
  relationship: {
    default: '',
    required: true,
    message: 'Relationship is required',
  },
  phoneNumber: {
    default: '',
    required: true,
    pattern: /^[0-9]{10}$/,
    minLength: 10,
    maxLength: 10,
    messages: {
      required: 'Phone number is required',
      pattern: 'Phone number must contain only 10 digits',
      minLength: 'Phone number must be exactly 10 digits',
      maxLength: 'Phone number must be exactly 10 digits',
    },
  },
  alternateNumber: {
    default: '',
    required: false,
    pattern: /^[0-9]{10}$/,
    minLength: 10,
    maxLength: 10,
    messages: {
      pattern: 'Alternate number must contain only 10 digits',
      minLength: 'Alternate number must be exactly 10 digits',
      maxLength: 'Alternate number must be exactly 10 digits',
    },
  },
  address: {
    default: '',
    required: true,
    message: 'Address is required',
  },
  locationArea: {
    default: '',
    required: true,
    message: 'Location / Area is required',
  },
  city: { default: null, reqnulluired: true, message: 'City is required' },
  state: { default: null, required: true, message: 'State is required' },
  pinCode: {
    default: null,
    required: true,
    pattern: /^[0-9]{6}$/,
    messages: { pattern: 'PIN code must contain 6 digits' },
  },
  emergencyContactName: {
    default: '',
    required: true,
    messages: {
      required: 'Emergency Contact is required',
    },
  },
  emergencyRelationship: {
    default: '',
    required: true,
    messages: {
      required: 'Relationship is required',
    },
  },
  emergencyPhone: {
    default: null,
    required: true,
    pattern: /^[0-9]{10}$/,
    minLength: 10,
    maxLength: 10,
    messages: {
      required: 'Emergency phone is required',
      pattern: 'Emergency phone must contain only 10 digits',
      minLength: 'Emergency phone must be exactly 10 digits',
      maxLength: 'Emergency phone must be exactly 10 digits',
    },
  },
});

const {
  form: medicalForm,
  errors: medicalErrors,
  validate: validateMedical,
  validateField: validateMedicalField,
  isInvalid: isMedicalInvalid,
  reset: resetMedical,
  patchValue: patchMedicalValue,
} = useFormBuilder({
  primaryDiagnosis: {
    default: '',
    required: true,
    message: 'Primary diagnosis is required',
  },
  secondaryDiagnosis: { default: '', required: false },
  healthStatus: {
    default: '',
    required: true,
    message: 'Current health status is required',
  },
  allergies: { default: '', required: false },
  chronicConditions: { default: '', required: false },
  medicalHistory: { default: '', required: false },
  currentMedications: { default: '', required: false },
  specialCareNeeds: { default: '', required: false },
  dietaryPreferences: { default: '', required: false },
});

const {
  form: careForm,
  errors: careErrors,
  validate: validateCare,
  validateField: validateCareField,
  isInvalid: isCareInvalid,
  reset: resetCare,
  patchValue: patchCareValue,
} = useFormBuilder({
  careType: { default: '', required: true, message: 'Care type is required' },
  serviceCategory: {
    default: '',
    required: true,
    message: 'Service category is required',
  },
  startDate: {
    default: '',
    required: true,
    message: 'Start date is required',
  },
  duration: {
    default: '',
    required: true,
    message: 'Duration is required',
  },
  durationUnit: { default: '', required: true, message: 'Duration unit is required' },
  dutyHours: { default: '', required: true, message: 'Duty hours are required' },
  serviceLocation: {
    default: '',
    required: true,
    message: 'Service location is required',
  },
  locationArea: { default: '', required: false },
  locationCity: { default: '', required: false },
  caregiverGender: { default: '', required: false },
  serviceDetails: {
    default: '',
    required: true,
    message: 'Service details are required',
  },
  feeRange: { default: '', required: false },
  additionalFacilities: {
    default: [],
    required: false,
  },
});

// Progress is calculated from required onboarding fields and mandatory documents.
// Optional fields do not affect the percentage.
const onboardingProgress = computed(() => {
  const requiredChecks = [
    basicForm.patientName,
    basicForm.dateOfBirth,
    basicForm.gender,
    basicForm.primaryContactName,
    basicForm.relationship,
    basicForm.phoneNumber,
    basicForm.address,
    basicForm.locationArea,
    basicForm?.emergencyContactName,
    basicForm?.emergencyRelationship,
    basicForm?.emergencyPhone,
    medicalForm.primaryDiagnosis,
    medicalForm.healthStatus,
    careForm.careType,
    careForm.serviceCategory,
    careForm.startDate,
    Number(careForm.duration) > 0,
    careForm.durationUnit,
    careForm.dutyHours,
    careForm.serviceLocation,
    careForm.serviceDetails,
    photoPreviewUrl.value,
    idProofPreviewUrl.value,
    medicalReportsPreviewUrl.value,
    consentFormPreviewUrl.value,
    privacyDocumentPreviewUrl.value,
  ];

  const completed = requiredChecks.filter((value) => (typeof value === 'boolean' ? value : String(value ?? '').trim().length > 0)).length;

  return Math.round((completed / requiredChecks.length) * 100);
});

// Populate forms when the parent supplies an existing patient record.
// Expected shape: { code/patient_code, basicInfo, medicalInfo, serviceRequirement }.
// Flat records using the same form field names are also supported.
const populatePatientForEdit = (data) => {
  if (!data) return;

  const basicData = data.basicInfo || data.basic_info || data;
  const medicalData = data.medicalInfo || data.medical_info || data;
  const serviceData = data.serviceRequirement || data.service_requirement || data.care || data;

  Object.keys(basicForm).forEach((key) => {
    if (basicData[key] !== undefined) basicForm[key] = basicData[key];
  });
  Object.keys(medicalForm).forEach((key) => {
    if (medicalData[key] !== undefined) medicalForm[key] = medicalData[key];
  });
  Object.keys(careForm).forEach((key) => {
    if (serviceData[key] !== undefined) careForm[key] = serviceData[key];
  });

  patient.value = {
    ...patient.value,
    code: data.patient_code || data.patientCode || data.code || null,
    name: basicForm.patientName || data.name || null,
    age: basicForm.age || null,
    gender: basicForm.gender || null,
    location: [basicForm.locationArea, basicForm.city, basicForm.state].filter(Boolean).join(', ') || null,
    status: data.status || 'Draft',
  };
};

watch(
  () => props.patientData,
  (data) => populatePatientForEdit(data),
  { immediate: true, deep: true }
);

// Keep the patient summary reactive while the user fills in the form.
// The photo preview comes from the existing useFileUpload composable.
watch(
  () => [
    basicForm.patientName,
    basicForm.age,
    basicForm.dateOfBirth,
    basicForm.gender,
    basicForm.locationArea,
    basicForm.city,
    basicForm.state,
  ],
  () => {
    const dob = basicForm.dateOfBirth;
    const dobMoment = dob ? moment(dob) : null;
    const calculatedAge =
      dobMoment && dobMoment.isValid() && !dobMoment.isAfter(moment(), 'day')
        ? moment().diff(dobMoment, 'years')
        : null;

    patient.value = {
      ...patient.value,
      name: basicForm.patientName || null,
      age: basicForm.age !== '' && basicForm.age !== null
        ? basicForm.age
        : calculatedAge,
      gender: basicForm.gender || null,
      location: [
        basicForm.locationArea,
        basicForm.city,
        basicForm.state,
      ].filter(Boolean).join(', ') || null,
    };
  },
  { immediate: true }
);

// Call this with the API response after creating/updating a patient.
// The patient code must come from the backend, never from a frontend default.
const applyBackendPatientResponse = (response) => {
  const record = response?.data?.patient || response?.patient || response?.data || response;
  if (!record) return;

  patient.value = {
    ...patient.value,
    code: record.patient_code || record.patientCode || record.code || patient.value.code,
    name: record.patient_name || record.name || basicForm.patientName,
    age: record.age || basicForm.age || null,
    gender: record.gender || basicForm.gender || null,
    location: record.location || [basicForm.locationArea, basicForm.city, basicForm.state].filter(Boolean).join(', '),
    status: record.status || patient.value.status || 'Draft',
  };
};

const steps = ['Basic Info & Contact', 'Medical/Care Information', 'Service Requirement', 'Consent & Documents', 'Review & Onboard'];

const stepDescriptions = [
  'Enter basic information, contact and address details',
  'Add medical history and care information',
  'Configure the patient service requirement',
  'Consent and document section',
  'Review all details and onboard the patient',
];

const requestTabs = [
  { label: 'All Requests', count: 3 },
  { label: 'Caregiver Replacement', count: 1 },
  { label: 'Ambulance', count: 1 },
  { label: 'Consumables', count: 1 },
  { label: 'Other Services', count: 0 },
];

const serviceRequests = ref([
  {
    id: 1,
    requestId: 'SR-2026-001',
    serviceType: 'Caregiver Replacement',
    requestDate: '05 Oct 2026',
    details: 'Current caregiver not available. Need replacement from 06 Oct 2026.',
    assignedTo: 'Operations',
    status: 'Pending',
    expectedDate: '06 Oct 2026',
    category: 'Caregiver Replacement',
  },
  {
    id: 2,
    requestId: 'SR-2026-002',
    serviceType: 'Ambulance Requirement',
    requestDate: '08 Oct 2026',
    details: 'Need ambulance for hospital visit (Respiratory check-up at Apollo, Salt Lake).',
    assignedTo: 'Ambulance Team',
    status: 'Confirmed',
    expectedDate: '09 Oct 2026',
    category: 'Ambulance',
  },
  {
    id: 3,
    requestId: 'SR-2026-003',
    serviceType: 'Medical Consumables',
    requestDate: '10 Oct 2026',
    details: 'Diapers (M) – 2 packs, Gloves – 1 box, Adult Wipes – 1 pack.',
    assignedTo: 'Consumables Team',
    status: 'Delivered',
    expectedDate: '11 Oct 2026',
    category: 'Consumables',
  },
]);

const newRequest = ref({
  serviceType: 'Caregiver Replacement',
  details: '',
  expectedDate: '2026-10-31',
});

const filteredRequests = computed(() => {
  let rows = serviceRequests.value;

  if (activeRequestTab.value !== 'All Requests') {
    rows = rows.filter((item) => item.category === activeRequestTab.value);
  }

  const search = searchText.value.trim().toLowerCase();
  if (search) {
    rows = rows.filter((item) =>
      [item.requestId, item.serviceType, item.details, item.assignedTo, item.status].some((value) =>
        String(value).toLowerCase().includes(search)
      )
    );
  }

  return rows;
});

const visibleRequests = computed(() => {
  const start = (currentPage.value - 1) * rowsPerPage.value;
  return filteredRequests.value.slice(start, start + rowsPerPage.value);
});

const totalPages = computed(() => Math.max(1, Math.ceil(filteredRequests.value.length / rowsPerPage.value)));

const setRequestTab = (tab) => {
  activeRequestTab.value = tab;
  currentPage.value = 1;
};

const goToStep = (step) => {
  // Do not allow skipping ahead. Users can revisit completed steps,
  // but must use Continue to move forward one step at a time.
  if (step >= 1 && step <= activeStep.value) {
    activeStep.value = step;
  }
};

const previousStep = () => {
  if (activeStep.value > 1) activeStep.value -= 1;
};

const validateDocuments = () => {
  documentValidationAttempted.value = true;

  return Boolean(
    photoPreviewUrl.value &&
    idProofPreviewUrl.value &&
    medicalReportsPreviewUrl.value &&
    consentFormPreviewUrl.value &&
    privacyDocumentPreviewUrl.value
  );
};

const nextStep = () => {
  // Validate only the current form section before allowing the user to continue.
  if (activeStep.value === 1 && !validateBasic()) return;
  if (activeStep.value === 2 && !validateMedical()) return;
  if (activeStep.value === 3 && !validateCare()) return;
  if (activeStep.value === 4 && !validateDocuments()) return;

  if (activeStep.value < steps.length) {
    activeStep.value += 1;
  } else {
    toast.success('Static demo: patient onboarding review is complete.');
  }
};

const triggerDocumentUpload = (inputElement) => {
  inputElement?.click();
};

const saveDraft = () => {
  toast.success('Draft saved locally for this static demo.');
};

const triggerAgreementUpload = () => {
  agreementInput.value?.click();
};

const onAgreementChange = (event) => {
  const file = event.target.files?.[0];
  if (file) agreementFile.value = file;
};

const viewAgreement = () => {
  if (!agreementFile.value) {
    toast.error('No agreement uploaded yet. Choose a file to preview it.');
    return;
  }

  const fileUrl = URL.createObjectURL(agreementFile.value);
  window.open(fileUrl, '_blank', 'noopener');
};
const calculateAge = () => {
  if (!basicForm.dateOfBirth) {
    basicForm.age = '';
    return;
  }

  const dob = moment(basicForm.dateOfBirth);

  // Validate date
  if (!dob.isValid() || dob.isAfter(moment(), 'day')) {
    basicForm.age = '';
    return;
  }

  // Calculate completed years
  basicForm.age = moment().diff(dob, 'years');
};
const handleDateOfBirthChange = () => {
  validateBasicField('dateOfBirth');
  calculateAge();
};

const addServiceRequest = () => {
  const nextNumber = serviceRequests.value.length + 1;
  const category = newRequest.value.serviceType;
  const categoryNames = {
    'Caregiver Replacement': 'Caregiver Replacement',
    Ambulance: 'Ambulance',
    Consumables: 'Consumables',
    'Other Services': 'Other Services',
  };

  serviceRequests.value.unshift({
    id: Date.now(),
    requestId: `SR-2026-${String(nextNumber).padStart(3, '0')}`,
    serviceType: category === 'Ambulance' ? 'Ambulance Requirement' : category === 'Consumables' ? 'Medical Consumables' : category,
    requestDate: new Date().toLocaleDateString('en-GB', {
      day: '2-digit',
      month: 'short',
      year: 'numeric',
    }),
    details: newRequest.value.details || 'New service request added from the static demo.',
    assignedTo: 'Operations',
    status: 'Pending',
    expectedDate: newRequest.value.expectedDate
      ? new Date(`${newRequest.value.expectedDate}T00:00:00`).toLocaleDateString('en-GB', {
          day: '2-digit',
          month: 'short',
          year: 'numeric',
        })
      : 'To be confirmed',
    category: categoryNames[category] || category,
  });

  showAddRequest.value = false;
  newRequest.value = {
    serviceType: 'Caregiver Replacement',
    details: '',
    expectedDate: '2026-10-31',
  };
  activeRequestTab.value = 'All Requests';
  currentPage.value = 1;
};

const viewRequest = (request) => {
  //toast.success(`Request: ${request.requestId}\n${request.details}`);
};

const showRequestActions = (request) => {
  //window.alert(`Static demo actions for ${request.requestId}`);
};

const statusSeverity = (status) => {
  if (status === 'Confirmed' || status === 'Delivered') return 'success';
  if (status === 'Pending') return 'warn';
  return 'info';
};

const requestTabCount = (tab) => {
  if (tab.label === 'All Requests') return serviceRequests.value.length;
  return serviceRequests.value.filter((item) => item.category === tab.label).length;
};
onMounted(() => {
  breadcrumbs.value = ['Patient', 'Enrolment'];
});
</script>

<template>
  <CRow>
    <CCol :lg="12" :md="12" :sm="12">
      <div class="patient-management-page">
        <div class="dashboard-shell">
          <!-- Main content -->
          <main class="dashboard-main">
            <header class="topbar">
              <div class="page-heading">
                <div>
                  <h1>Patient Management</h1>
                  <p>Manage patients, care requirements and caregiver assignments</p>
                </div>
              </div>
            </header>

            <!-- Summary cards -->
            <section class="stats-grid">
              <div class="pb-stat-card pb-stat-blue">
                <div class="pb-stat-icon-wrap"><CIcon :icon="cilPeople" /></div>
                <div class="pb-stat-content">
                  <span class="pb-stat-label">Total Patients</span>
                  <span class="pb-stat-value">248</span>
                </div>
              </div>
              <div class="pb-stat-card pb-stat-green">
                <div class="pb-stat-icon-wrap"><CIcon :icon="cilHeart" /></div>
                <div class="pb-stat-content">
                  <span class="pb-stat-label">Active Care</span>
                  <span class="pb-stat-value">86</span>
                </div>
              </div>
              <div class="pb-stat-card pb-stat-pink">
                <div class="pb-stat-icon-wrap"><CIcon :icon="cilUser" /></div>
                <div class="pb-stat-content">
                  <span class="pb-stat-label">Caregivers Assigned</span>
                  <span class="pb-stat-value">79</span>
                </div>
              </div>
              <div class="pb-stat-card pb-stat-orange">
                <div class="pb-stat-icon-wrap"><CIcon :icon="cilClock" /></div>
                <div class="pb-stat-content">
                  <span class="pb-stat-label">Assignment Pending</span>
                  <span class="pb-stat-value">7</span>
                </div>
              </div>
            </section>

            <!-- Onboarding heading -->
            <section class="onboarding-panel">
              <div class="pb-card-header">
                <div class="pb-card-header-left">
                  <div class="pb-card-icon"><CIcon :icon="cilUser" /></div>
                  <div>
                    <h2 class="pb-card-title">Patient Onboarding</h2>
                    <p class="pb-card-desc">Register new patient and create care requirement</p>
                  </div>
                </div>
              </div>

              <!-- Stepper -->
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
                  :aria-disabled="index + 1 > activeStep"
                  :style="{ cursor: index + 1 > activeStep ? 'not-allowed' : 'pointer' }">
                  <button type="button" class="step-number">
                    <CIcon v-if="index + 1 < activeStep" :icon="cilCheck" />
                    <span v-else>{{ index + 1 }}</span>
                  </button>
                  <span class="step-label">{{ step }}</span>
                </div>
              </div>

              <!-- Patient strip -->
              <div class="patient-summary-grid">
                <!-- Patient Identity: uses the existing uploaded-photo preview -->
                <div class="patient-identity">
                  <div class="patient-photo">
                    <img
                      v-if="photoPreviewUrl"
                      :src="photoPreviewUrl"
                      alt="Patient profile"
                      class="patient-summary-photo" />
                    <span v-else>{{ patientInitials }}</span>
                  </div>

                  <div class="patient-summary-details">
                    <a href="#" @click.prevent>
                      {{ patient.code || 'Generated after saving' }}
                    </a>
                    <strong>{{ patient.name || 'Patient Name' }}</strong>
                    <div class="patient-meta">
                      <span>
                        <CIcon :icon="cilUser" />
                        {{ patient.age ?? '--' }} yrs
                      </span>
                      <span>
                        <CIcon :icon="cilLocationPin" />
                        {{ patient.location || 'Location' }}
                      </span>
                    </div>
                  </div>
                </div>

                <!-- Patient Status -->
                <div class="patient-status">
                  <span class="mini-label">Patient Status</span>
                  <span class="status-pill onboarding">
                    {{ patient.status || 'Onboarding' }}
                  </span>
                </div>

                <!-- Onboarding Progress -->
                <div class="progress-card">
                  <div class="progress-title">
                    <span>Onboarding Progress</span>
                    <strong>{{ onboardingProgress }}%</strong>
                  </div>
                  <div class="progress-track">
                    <div :style="{ width: `${onboardingProgress}%` }"></div>
                  </div>
                </div>
              </div>

              <!-- Basic Information + Contact & Address -->
              <div v-if="activeStep === 1" class="pb-patient-step-content">
                <div class="pb-patient-step-content-inr">
                  <CCardHeader class="pb-section-title">
                    <!-- <CIcon :icon="cilUser" /> -->
                    <strong>Basic Information</strong></CCardHeader
                  >
                  <CCardBody>
                    <div class="row g-3">
                      <div class="col-lg-4">
                        <div class="field-group">
                          <label class="pb-label">Patient Name <span>*</span></label
                          ><CFormInput
                            v-model="basicForm.patientName"
                            placeholder="Enter patient name"
                            :class="{ 'is-invalid': isBasicInvalid('patientName') }"
                            @input="validateBasicField('patientName')" />

                          <CFormFeedback invalid> {{ basicErrors?.patientName?.message }}. </CFormFeedback>
                        </div>
                      </div>
                      <div class="col-lg-4">
                        <div class="field-group">
                          <label class="pb-label">Date of Birth <span>*</span></label>
                          <CDatePicker
                            v-model:date="basicForm.dateOfBirth"
                            format="dd/MM/yyyy"
                            class="form-control"
                            :invalid="isBasicInvalid('dateOfBirth')"
                            :feedback-invalid="basicErrors?.dateOfBirth?.message"
                            @update:date="handleDateOfBirthChange" />
                        </div>
                      </div>
                      <div class="col-lg-4">
                        <div class="field-group">
                          <label class="pb-label">Age</label><CFormInput v-model="basicForm.age" placeholder="-" readonly />
                        </div>
                      </div>
                      <div class="col-lg-4">
                        <div class="field-group">
                          <label class="pb-label">Gender <span>*</span></label
                          ><CFormSelect
                            v-model="basicForm.gender"
                            :options="[{ label: 'Select', value: '' }, ...PATIENT_DROPDOWNS.gender]"
                            :class="{ 'is-invalid': isBasicInvalid('gender') }"
                            @update:modelValue="validateBasicField('gender')" />
                          <CFormFeedback invalid>{{ basicErrors?.gender?.message }}</CFormFeedback>
                        </div>
                      </div>
                    </div>
                  </CCardBody>
                </div>
                <div class="pb-patient-step-content-inr">
                  <CCardHeader class="pb-section-title">
                    <!--<CIcon :icon="cilLocationPin" /> -->
                    <strong>Contact & Address</strong></CCardHeader
                  >
                  <CCardBody>
                    <div class="row g-3">
                      <div class="col-lg-4">
                        <div class="field-group">
                          <label class="pb-label">Primary Contact Name <span>*</span></label
                          ><CFormInput
                            v-model="basicForm.primaryContactName"
                            placeholder="Enter contact name"
                            :class="{ 'is-invalid': isBasicInvalid('primaryContactName') }"
                            @input="validateBasicField('primaryContactName')" />
                          <CFormFeedback invalid>{{ basicErrors?.primaryContactName?.message }}</CFormFeedback>
                        </div>
                      </div>
                      <div class="col-lg-4">
                        <div class="field-group">
                          <label class="pb-label">Relationship <span>*</span></label
                          ><CFormSelect
                            v-model="basicForm.relationship"
                            :options="[{ label: 'Select', value: '' }, ...PATIENT_DROPDOWNS.relationship]"
                            :class="{ 'is-invalid': isBasicInvalid('relationship') }"
                            @update:modelValue="validateBasicField('relationship')" />
                          <CFormFeedback invalid>{{ basicErrors?.relationship?.message }}</CFormFeedback>
                        </div>
                      </div>
                      <div class="col-lg-4">
                        <div class="field-group">
                          <label class="pb-label">Phone Number <span>*</span></label>
                          <CFormInput
                            v-model="basicForm.phoneNumber"
                            placeholder="Enter phone number"
                            :class="{ 'is-invalid': isBasicInvalid('phoneNumber') }"
                            maxlength="10"
                            @input="
                              $event.target.value = $event.target.value.replace(/\D/g, '');
                              validateBasicField('phoneNumber');
                            " />
                          <CFormFeedback invalid>{{ basicErrors?.phoneNumber?.message }}</CFormFeedback>
                        </div>
                      </div>

                      <div class="col-lg-4">
                        <div class="field-group">
                          <label class="pb-label">Alternate Number</label
                          ><CFormInput
                            v-model="basicForm.alternateNumber"
                            placeholder="Enter alternate number"
                            maxlength="10"
                            :class="{ 'is-invalid': isBasicInvalid('alternateNumber') }"
                            @input="
                              $event.target.value = $event.target.value.replace(/\D/g, '');
                              validateBasicField('alternateNumber');
                            " />
                        </div>
                      </div>

                      <div class="col-lg-8">
                        <div class="field-group">
                          <label class="pb-label">Location / Area <span>*</span></label
                          ><CFormInput
                            v-model="basicForm.locationArea"
                            placeholder="Select location"
                            :class="{ 'is-invalid': isBasicInvalid('locationArea') }"
                            @input="validateBasicField('locationArea')" />
                          <CFormFeedback invalid>{{ basicErrors?.locationArea?.message }}</CFormFeedback>
                        </div>
                      </div>
                      <div class="col-lg-12">
                        <div class="field-group address-field">
                          <label class="pb-label">Address <span>*</span></label
                          ><textarea
                            v-model="basicForm.address"
                            rows="2"
                            class="form-control"
                            :class="{ 'is-invalid': isBasicInvalid('address') }"
                            placeholder="Enter full address"
                            @input="validateBasicField('address')"></textarea>
                          <CFormFeedback invalid>{{ basicErrors?.address?.message }}</CFormFeedback>
                        </div>
                      </div>

                      <div class="col-lg-4">
                        <div class="field-group">
                          <label class="pb-label">City<span>*</span></label>
                          <CFormInput
                            v-model="basicForm.city"
                            :class="{ 'is-invalid': isBasicInvalid('city') }"
                            @input="validateBasicField('city')" />
                          <CFormFeedback invalid>{{ basicErrors?.city?.message }}</CFormFeedback>
                        </div>
                      </div>
                      <div class="col-lg-4">
                        <div class="field-group">
                          <label class="pb-label">State<span class="text-danger">*</span></label>
                          <CFormSelect
                            v-model="basicForm.state"
                            :class="{ 'is-invalid': isBasicInvalid('state') }"
                            @update:modelValue="validateBasicField('state')">
                            <option value="">Select state</option>
                            <option v-for="stateName in indianStates" :key="stateName" :value="stateName">
                              {{ stateName }}
                            </option>
                          </CFormSelect>
                          <CFormFeedback invalid>{{ basicErrors?.state?.message }}</CFormFeedback>
                        </div>
                      </div>
                      <div class="col-lg-4">
                        <div class="field-group">
                          <label class="pb-label">PIN Code <span class="text-danger">*</span></label>

                          <CFormInput
                            v-model="basicForm.pinCode"
                            placeholder="Enter PIN"
                            :class="{ 'is-invalid': isBasicInvalid('pinCode') }"
                            @input="
                              $event.target.value = $event.target.value.replace(/\D/g, '');
                              validateBasicField('pinCode');
                            " />
                        </div>
                      </div>

                      <div class="col-lg-4">
                        <div class="field-group">
                          <label class="pb-label">Emergency Contact Name <span class="text-danger">*</span></label
                          ><CFormInput
                            v-model="basicForm.emergencyContactName"
                            placeholder="Enter name"
                            :class="{ 'is-invalid': isBasicInvalid('emergencyContactName') }"
                            @input="validateBasicField('emergencyContactName')" />
                          <CFormFeedback invalid>{{ basicErrors?.emergencyContactName?.message }}</CFormFeedback>
                        </div>
                      </div>
                      <div class="col-lg-4">
                        <div class="field-group">
                          <label class="pb-label">Relationship <span class="text-danger">*</span></label>

                          <CFormSelect
                            v-model="basicForm.emergencyRelationship"
                            :options="[{ label: 'Select', value: '' }, ...PATIENT_DROPDOWNS.relationship]"
                            :class="{ 'is-invalid': isBasicInvalid('emergencyRelationship') }"
                            @update:modelValue="validateBasicField('emergencyRelationship')" />
                          <CFormFeedback invalid>{{ basicErrors?.emergencyRelationship?.message }}</CFormFeedback>
                        </div>
                      </div>
                      <div class="col-lg-4">
                        <div class="field-group emergency-phone-field">
                          <label class="pb-label">Emergency Phone <span class="text-danger">*</span></label
                          ><CFormInput
                            v-model="basicForm.emergencyPhone"
                            placeholder="Enter phone number"
                            :class="{ 'is-invalid': isBasicInvalid('emergencyPhone') }"
                            @input="
                              $event.target.value = $event.target.value.replace(/\D/g, '');
                              validateBasicField('emergencyPhone');
                            " />
                          <CFormFeedback invalid>{{ basicErrors?.emergencyPhone?.message }}</CFormFeedback>
                        </div>
                      </div>
                    </div>
                  </CCardBody>
                </div>
              </div>

              <!-- Medical / Care Information -->
              <div v-else-if="activeStep === 2" class="pb-patient-step-content">
                <div class="pb-patient-step-content-inr">
                  <CCardHeader class="pb-section-title">
                    <!-- <CIcon :icon="cilMedicalCross" /> -->
                    <strong>Medical / Care Information</strong></CCardHeader
                  >
                  <CCardBody>
                    <div class="row g-3 medical-info-grid">
                      <div class="col-lg-4">
                        <div class="field-group">
                          <label class="pb-label">Primary Diagnosis <span>*</span></label
                          ><CFormSelect
                            v-model="medicalForm.primaryDiagnosis"
                            :options="[{ label: 'Select diagnosis', value: '' }, ...PATIENT_DROPDOWNS.primaryDiagnosis]"
                            :class="{ 'is-invalid': isMedicalInvalid('primaryDiagnosis') }"
                            @update:modelValue="validateMedicalField('primaryDiagnosis')" />
                          <CFormFeedback invalid>{{ medicalErrors?.primaryDiagnosis?.message }}</CFormFeedback>
                        </div>
                      </div>
                      <div class="col-lg-4">
                        <div class="field-group">
                          <label class="pb-label">Secondary Diagnosis</label
                          ><CFormInput v-model="medicalForm.secondaryDiagnosis" placeholder="Enter secondary diagnosis" />
                        </div>
                      </div>
                      <div class="col-lg-4">
                        <div class="field-group">
                          <label class="pb-label">Current Health Status <span>*</span></label
                          ><CFormSelect
                            v-model="medicalForm.healthStatus"
                            :options="[{ label: 'Select status', value: '' }, ...PATIENT_DROPDOWNS.healthStatus]"
                            :class="{ 'is-invalid': isMedicalInvalid('healthStatus') }"
                            @update:modelValue="validateMedicalField('healthStatus')" />
                          <CFormFeedback invalid>{{ medicalErrors?.healthStatus?.message }}</CFormFeedback>
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="field-group">
                          <label class="pb-label">Allergies (if any)</label
                          ><CFormInput v-model="medicalForm.allergies" placeholder="Enter allergies or None" />
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="field-group">
                          <label class="pb-label">Chronic Conditions</label
                          ><CFormInput v-model="medicalForm.chronicConditions" placeholder="Enter chronic conditions" />
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="field-group">
                          <label class="pb-label">Medical History (Brief)</label
                          ><textarea
                            v-model="medicalForm.medicalHistory"
                            rows="3"
                            class="form-control"
                            placeholder="Enter medical history"></textarea>
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="field-group">
                          <label class="pb-label">Current Medications</label
                          ><textarea
                            v-model="medicalForm.currentMedications"
                            rows="3"
                            class="form-control"
                            placeholder="Enter current medications"></textarea>
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="field-group">
                          <label class="pb-label">Special Care Needs</label
                          ><textarea
                            v-model="medicalForm.specialCareNeeds"
                            rows="3"
                            class="form-control"
                            placeholder="Enter special care needs"></textarea>
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="field-group">
                          <label class="pb-label">Dietary Preferences</label
                          ><textarea
                            v-model="medicalForm.dietaryPreferences"
                            rows="2"
                            class="form-control"
                            placeholder="Enter dietary preferences"></textarea>
                        </div>
                      </div>
                    </div>
                  </CCardBody>
                </div>
              </div>

              <!-- Service Requirement page -->
              <div v-else-if="activeStep === 3" class="pb-patient-step-content">
                <div class="pb-patient-step-content-inr">
                  <CCardHeader class="pb-section-title">
                    <!-- <CIcon :icon="cilMedicalCross" /> -->
                    <strong>Service Requirement</strong>
                  </CCardHeader>
                  <CCardBody>
                    <div class="row g-3">
                      <div class="col-lg-4 col-md-6">
                        <div class="field-group">
                          <label class="pb-label">Care Type <span>*</span></label>
                          <CFormSelect
                            v-model="careForm.careType"
                            :options="[{ label: 'Select care type', value: '' }, ...PATIENT_DROPDOWNS.careType]"
                            :class="{ 'is-invalid': isCareInvalid('careType') }"
                            @update:modelValue="validateCareField('careType')" />
                          <CFormFeedback invalid>{{ careErrors?.careType?.message }}</CFormFeedback>
                        </div>
                      </div>

                      <div class="col-lg-3 col-md-6">
                        <div class="field-group">
                          <label class="pb-label">Care Start Date <span>*</span></label>
                          <CDatePicker
                            v-model:date="careForm.startDate"
                            format="dd/MM/yyyy"
                            class="form-control"
                            :invalid="isCareInvalid('startDate')"
                            :feedback-invalid="careErrors?.startDate?.message"
                            @update:date="validateCareField('startDate')" />
                        </div>
                      </div>
                      <div class="col-lg-5 col-md-6">
                        <div class="field-group">
                          <label class="pb-label">Expected Duration <span>*</span></label>
                          <div class="duration-field">
                            <CFormInput
                              v-model.number="careForm.duration"
                              type="number"
                              min="1"
                              :class="{ 'is-invalid': isCareInvalid('duration') }"
                              @input="validateCareField('duration')" />
                            <CFormSelect
                              v-model="careForm.durationUnit"
                              :options="[{ label: 'Select unit', value: '' }, ...PATIENT_DROPDOWNS.durationUnit]"
                              :class="{ 'is-invalid': isCareInvalid('durationUnit') }"
                              @update:modelValue="validateCareField('durationUnit')" />
                          </div>
                          <CFormFeedback invalid>{{ careErrors?.duration?.message }}</CFormFeedback>
                          <CFormFeedback invalid>{{ careErrors?.durationUnit?.message }}</CFormFeedback>
                        </div>
                      </div>
                      <div class="col-lg-12">
                        <div class="field-group full-field">
                          <label class="pb-label">Duty Hours <span>*</span></label>
                          <div class="radio-pills">
                            <label
                              v-for="hour in PATIENT_DROPDOWNS.dutyHours.map((option) => option.value)"
                              :key="hour"
                              :class="{ chosen: careForm.dutyHours === hour }">
                              <input v-model="careForm.dutyHours" type="radio" :value="hour" />
                              <span class="custom-radio-span">{{ hour }}</span>
                            </label>
                          </div>
                          <CFormFeedback invalid>{{ careErrors?.dutyHours?.message }}</CFormFeedback>
                        </div>
                      </div>
                      <div class="col-lg-3 col-md-6">
                        <div class="field-group">
                          <label class="pb-label">Service Category</label>
                          <CFormSelect
                            v-model="careForm.serviceCategory"
                            :options="[{ label: 'Select service category', value: '' }, ...PATIENT_DROPDOWNS.serviceCategory]"
                            :class="{ 'is-invalid': isCareInvalid('serviceCategory') }"
                            @update:modelValue="validateCareField('serviceCategory')" />
                          <CFormFeedback invalid>{{ careErrors?.serviceCategory?.message }}</CFormFeedback>
                        </div>
                      </div>
                      <div class="col-lg-3 col-md-6">
                        <div class="field-group">
                          <label class="pb-label">Service Location <span>*</span></label>
                          <CFormSelect
                            v-model="careForm.serviceLocation"
                            :options="[{ label: 'Select service location', value: '' }, ...PATIENT_DROPDOWNS.serviceLocation]"
                            :class="{ 'is-invalid': isCareInvalid('serviceLocation') }"
                            @update:modelValue="validateCareField('serviceLocation')" />
                          <CFormFeedback invalid>{{ careErrors?.serviceLocation?.message }}</CFormFeedback>
                        </div>
                      </div>
                      <div class="col-lg-3 col-md-6">
                        <div class="field-group">
                          <label class="pb-label">Location / Area</label>
                          <CFormInput v-model="careForm.locationArea" />
                        </div>
                      </div>
                      <div class="col-lg-3 col-md-6">
                        <div class="field-group">
                          <label class="pb-label">City</label>
                          <CFormInput v-model="careForm.locationCity" />
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="field-group full-field">
                          <label class="pb-label">Preferred Caregiver Gender</label>
                          <div class="radio-pills compact">
                            <label
                              v-for="gender in PATIENT_DROPDOWNS.caregiverGender.map((option) => option.value)"
                              :key="gender"
                              :class="{ chosen: careForm.caregiverGender === gender }">
                              <input v-model="careForm.caregiverGender" type="radio" :value="gender" />
                              <span>{{ gender }}</span>
                            </label>
                          </div>
                        </div>
                      </div>
                    </div>
                  </CCardBody>
                </div>

                <div class="pb-patient-step-content-inr">
                  <CCardHeader class="pb-section-title">
                    <!-- <CIcon :icon="cilFile" /> -->
                    <strong>Service Details & Instructions</strong>
                  </CCardHeader>
                  <CCardBody>
                    <div class="row g-4">
                      <div class="col-lg-6">
                        <div class="field-group">
                          <textarea
                            v-model="careForm.serviceDetails"
                            rows="4"
                            class="form-control details-textarea"
                            :class="{ 'is-invalid': isCareInvalid('serviceDetails') }"
                            @input="validateCareField('serviceDetails')"></textarea>
                          <CFormFeedback invalid>{{ careErrors?.serviceDetails?.message }}</CFormFeedback>
                        </div>
                      </div>
                      <div class="col-lg-6">
                        <div class="fee-box">
                          <div class="pb-fee-config-header">
                            <div class="pb-fee-config-title">Expected Caregiver Fee Range (Optional)</div>
                          </div>
                          <div class="pb-fee-config-body">
                            <div class="pb-fee-amount-badge fs-4 mb-1">
                              {{ careForm.feeRange }}
                              <span class="fs-6 fw-normal text-muted ms-1">per hour</span>
                            </div>
                            <div class="pb-fee-calc-text">Based on service type and market rate.</div>
                          </div>
                        </div>
                      </div>
                      <div class="col-lg-12">
                        <div class="facilities-title">Additional Facilities (if any)</div>
                        <div class="facility-option-row">
                          <label
                            v-for="facility in PATIENT_DROPDOWNS.additionalFacilities.map((option) => option.value)"
                            :key="facility"
                            class="facility-option">
                            <input v-model="careForm.additionalFacilities" type="checkbox" :value="facility" />
                            <span>{{ facility }}</span>
                          </label>
                        </div>
                      </div>
                    </div>
                  </CCardBody>
                </div>
              </div>

              <!-- Consent & Documents -->
              <div v-else-if="activeStep === 4" class="pb-patient-step-content consent-documents-page">
                <div class="pb-patient-step-content-inr">
                  <CCardHeader class="pb-section-title">
                    <strong>Consent & Documents</strong>
                  </CCardHeader>

                  <CCardBody>
                    <p class="text-body-secondary mb-3">
                      Upload the patient's photograph and the required supporting documents. Accepted file size: maximum 1 MB per document.
                    </p>

                    <div class="row g-3">
                      <!-- Patient photo -->
                      <div class="col-lg-6">
                        <div class="border rounded p-3 h-100">
                          <div class="d-flex justify-content-between align-items-center mb-2">
                            <strong>Patient Photo <span class="text-danger">*</span></strong>
                            <span v-if="photoPreviewUrl" class="text-success small">Uploaded</span>
                          </div>
                          <div class="d-flex align-items-center gap-3 flex-wrap">
                            <img
                              v-if="photoPreviewUrl"
                              :src="photoPreviewUrl"
                              alt="Patient photo preview"
                              class="rounded"
                              style="width: 76px; height: 76px; object-fit: cover" />
                            <div
                              v-else
                              class="rounded bg-body-secondary d-flex align-items-center justify-content-center"
                              style="width: 76px; height: 76px">
                              <CIcon :icon="cilUser" size="xl" />
                            </div>
                            <div class="flex-grow-1">
                              <div class="small text-body-secondary mb-2">
                                {{ photoFile?.name || 'JPG, JPEG, PNG or WEBP' }}
                              </div>
                              <input
                                ref="photoInput"
                                type="file"
                                accept="image/jpeg,image/png,image/jpg,image/webp"
                                class="d-none"
                                @change="handlePhotoFileChange($event, allowedPhotoTypes, maxDocumentSize)" />
                              <CButton color="primary" variant="outline" @click="triggerDocumentUpload(photoInput)">
                                {{ photoPreviewUrl ? 'Change Photo' : 'Upload Photo' }}
                              </CButton>
                              <CButton
                                v-if="photoPreviewUrl"
                                color="danger"
                                variant="ghost"
                                class="ms-2"
                                @click="clearPhotoFile(photoInput)">
                                Remove
                              </CButton>
                            </div>
                          </div>
                          <div v-if="photoFileError" class="text-danger small mt-2">{{ photoFileError }}</div>
                          <div v-if="documentValidationAttempted && !photoPreviewUrl" class="text-danger small mt-2">
                            Patient photo is required.
                          </div>
                        </div>
                      </div>

                      <!-- ID proof -->
                      <div class="col-lg-6">
                        <div class="border rounded p-3 h-100">
                          <div class="d-flex justify-content-between align-items-center mb-2">
                            <strong>ID Proof <span class="text-danger">*</span></strong>
                            <span v-if="idProofPreviewUrl" class="text-success small">Uploaded</span>
                          </div>
                          <div class="d-flex align-items-center gap-3">
                            <CIcon :icon="cilFile" size="xl" class="text-body-secondary" />
                            <div class="flex-grow-1">
                              <div class="small text-body-secondary mb-2">
                                {{ idProofFile?.name || 'PDF, JPG, JPEG, PNG or WEBP' }}
                              </div>
                              <input
                                ref="idProofInput"
                                type="file"
                                accept="application/pdf,image/jpeg,image/png,image/jpg,image/webp"
                                class="d-none"
                                @change="handleIdProofFileChange($event, allowedIdentityTypes, maxDocumentSize)" />
                              <CButton color="primary" variant="outline" @click="triggerDocumentUpload(idProofInput)">
                                {{ idProofPreviewUrl ? 'Replace File' : 'Upload File' }}
                              </CButton>
                              <a v-if="idProofPreviewUrl" :href="idProofPreviewUrl" target="_blank" rel="noopener" class="small ms-2"
                                >View</a
                              >
                              <CButton
                                v-if="idProofPreviewUrl"
                                color="danger"
                                variant="ghost"
                                class="ms-2"
                                @click="clearIdProofFile(idProofInput)"
                                >Remove</CButton
                              >
                            </div>
                          </div>
                          <div v-if="idProofFileError" class="text-danger small mt-2">{{ idProofFileError }}</div>
                          <div v-if="documentValidationAttempted && !idProofPreviewUrl" class="text-danger small mt-2">
                            ID proof is required.
                          </div>
                        </div>
                      </div>

                      <!-- Medical reports -->
                      <div class="col-lg-6">
                        <div class="border rounded p-3 h-100">
                          <div class="d-flex justify-content-between align-items-center mb-2">
                            <strong>Medical Reports <span class="text-danger">*</span></strong>
                            <span v-if="medicalReportsPreviewUrl" class="text-success small">Uploaded</span>
                          </div>
                          <div class="d-flex align-items-center gap-3">
                            <CIcon :icon="cilFile" size="xl" class="text-body-secondary" />
                            <div class="flex-grow-1">
                              <div class="small text-body-secondary mb-2">
                                {{ medicalReportsFile?.name || 'PDF, JPG, JPEG, PNG or WEBP' }}
                              </div>
                              <input
                                ref="medicalReportsInput"
                                type="file"
                                accept="application/pdf,image/jpeg,image/png,image/jpg,image/webp"
                                class="d-none"
                                @change="handleMedicalReportsFileChange($event, allowedMedicalReportTypes, maxDocumentSize)" />
                              <CButton color="primary" variant="outline" @click="triggerDocumentUpload(medicalReportsInput)">
                                {{ medicalReportsPreviewUrl ? 'Replace File' : 'Upload File' }}
                              </CButton>
                              <a
                                v-if="medicalReportsPreviewUrl"
                                :href="medicalReportsPreviewUrl"
                                target="_blank"
                                rel="noopener"
                                class="small ms-2"
                                >View</a
                              >
                              <CButton
                                v-if="medicalReportsPreviewUrl"
                                color="danger"
                                variant="ghost"
                                class="ms-2"
                                @click="clearMedicalReportsFile(medicalReportsInput)"
                                >Remove</CButton
                              >
                            </div>
                          </div>
                          <div v-if="medicalReportsFileError" class="text-danger small mt-2">{{ medicalReportsFileError }}</div>
                          <div v-if="documentValidationAttempted && !medicalReportsPreviewUrl" class="text-danger small mt-2">
                            Medical reports are required.
                          </div>
                        </div>
                      </div>

                      <!-- Consent form -->
                      <div class="col-lg-6">
                        <div class="border rounded p-3 h-100">
                          <div class="d-flex justify-content-between align-items-center mb-2">
                            <strong>Consent Form <span class="text-danger">*</span></strong>
                            <span v-if="consentFormPreviewUrl" class="text-success small">Uploaded</span>
                          </div>
                          <div class="d-flex align-items-center gap-3">
                            <CIcon :icon="cilFile" size="xl" class="text-body-secondary" />
                            <div class="flex-grow-1">
                              <div class="small text-body-secondary mb-2">
                                {{ consentFormFile?.name || 'PDF document' }}
                              </div>
                              <input
                                ref="consentFormInput"
                                type="file"
                                accept="application/pdf"
                                class="d-none"
                                @change="handleConsentFormFileChange($event, allowedPdfTypes, maxDocumentSize)" />
                              <CButton color="primary" variant="outline" @click="triggerDocumentUpload(consentFormInput)">
                                {{ consentFormPreviewUrl ? 'Replace File' : 'Upload PDF' }}
                              </CButton>
                              <a
                                v-if="consentFormPreviewUrl"
                                :href="consentFormPreviewUrl"
                                target="_blank"
                                rel="noopener"
                                class="small ms-2"
                                >View</a
                              >
                              <CButton
                                v-if="consentFormPreviewUrl"
                                color="danger"
                                variant="ghost"
                                class="ms-2"
                                @click="clearConsentFormFile(consentFormInput)"
                                >Remove</CButton
                              >
                            </div>
                          </div>
                          <div v-if="consentFormFileError" class="text-danger small mt-2">{{ consentFormFileError }}</div>
                          <div v-if="documentValidationAttempted && !consentFormPreviewUrl" class="text-danger small mt-2">
                            Consent form is required.
                          </div>
                        </div>
                      </div>

                      <!-- Privacy document -->
                      <div class="col-lg-6">
                        <div class="border rounded p-3 h-100">
                          <div class="d-flex justify-content-between align-items-center mb-2">
                            <strong>Privacy Document <span class="text-danger">*</span></strong>
                            <span v-if="privacyDocumentPreviewUrl" class="text-success small">Uploaded</span>
                          </div>
                          <div class="d-flex align-items-center gap-3">
                            <CIcon :icon="cilFile" size="xl" class="text-body-secondary" />
                            <div class="flex-grow-1">
                              <div class="small text-body-secondary mb-2">
                                {{ privacyDocumentFile?.name || 'PDF document' }}
                              </div>
                              <input
                                ref="privacyDocumentInput"
                                type="file"
                                accept="application/pdf"
                                class="d-none"
                                @change="handlePrivacyDocumentFileChange($event, allowedPdfTypes, maxDocumentSize)" />
                              <CButton color="primary" variant="outline" @click="triggerDocumentUpload(privacyDocumentInput)">
                                {{ privacyDocumentPreviewUrl ? 'Replace File' : 'Upload PDF' }}
                              </CButton>
                              <a
                                v-if="privacyDocumentPreviewUrl"
                                :href="privacyDocumentPreviewUrl"
                                target="_blank"
                                rel="noopener"
                                class="small ms-2"
                                >View</a
                              >
                              <CButton
                                v-if="privacyDocumentPreviewUrl"
                                color="danger"
                                variant="ghost"
                                class="ms-2"
                                @click="clearPrivacyDocumentFile(privacyDocumentInput)"
                                >Remove</CButton
                              >
                            </div>
                          </div>
                          <div v-if="privacyDocumentFileError" class="text-danger small mt-2">{{ privacyDocumentFileError }}</div>
                          <div v-if="documentValidationAttempted && !privacyDocumentPreviewUrl" class="text-danger small mt-2">
                            Privacy document is required.
                          </div>
                        </div>
                      </div>
                    </div>
                  </CCardBody>
                </div>
              </div>

              <!-- Review & Onboard -->
              <div v-else-if="activeStep === 5" class="pb-patient-step-content review-page-content">
                <div class="pb-patient-step-content-inr">
                  <CCardHeader class="pb-section-title">
                    <!-- <CIcon :icon="cilCheckCircle" /> -->
                    <strong>Review & Onboard</strong></CCardHeader
                  >
                  <CCardBody>
                    <div class="row">
                      <div class="col-lg-4">
                        <div class="pb-shadow-box h-100">
                          <div class="pb-shadow-box-header pb-box-header-blue">
                            <span>Basic Information & Contact</span>
                          </div>
                          <div class="pb-shadow-box-body">
                            <div class="pb-kv-row">
                              <span class="pb-kv-label">Patient ID</span>
                              <span class="pb-kv-value">{{ patient.code || 'Generated after saving' }}</span>
                            </div>
                            <div class="pb-kv-row">
                              <span class="pb-kv-label">Patient Name</span>
                              <span class="pb-kv-value">{{ basicForm.patientName || '-' }}</span>
                            </div>
                            <div class="pb-kv-row">
                              <span class="pb-kv-label">Date of Birth</span>
                              <span class="pb-kv-value">{{ basicForm.dateOfBirth || '-' }}</span>
                            </div>
                            <div class="pb-kv-row">
                              <span class="pb-kv-label">Gender</span>
                              <span class="pb-kv-value">{{ basicForm.gender || '-' }}</span>
                            </div>
                            <div class="pb-kv-row">
                              <span class="pb-kv-label">Primary Contact</span>
                              <span class="pb-kv-value">{{ basicForm.primaryContactName || '-' }}</span>
                            </div>
                            <div class="pb-kv-row">
                              <span class="pb-kv-label">Phone Number</span>
                              <span class="pb-kv-value">{{ basicForm.phoneNumber || '-' }}</span>
                            </div>
                            <div class="pb-kv-row">
                              <span class="pb-kv-label">Address</span>
                              <span class="pb-kv-value">{{ basicForm.address || '-' }}</span>
                            </div>
                            <div class="pb-kv-row">
                              <span class="pb-kv-label">Location</span>
                              <span class="pb-kv-value">{{
                                [basicForm.locationArea, basicForm.city, basicForm.state, basicForm.pinCode].filter(Boolean).join(', ') ||
                                '-'
                              }}</span>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="col-lg-4">
                        <div class="pb-shadow-box h-100">
                          <div class="pb-shadow-box-header pb-box-header-red">
                            <span>Medical / Care Information</span>
                          </div>
                          <div class="pb-shadow-box-body">
                            <div class="pb-kv-row">
                              <span>Primary Diagnosis</span><strong>{{ medicalForm.primaryDiagnosis || '-' }}</strong>
                            </div>
                            <div class="pb-kv-row">
                              <span>Health Status</span><strong>{{ medicalForm.healthStatus || '-' }}</strong>
                            </div>
                            <div class="pb-kv-row">
                              <span>Allergies</span><strong>{{ medicalForm.allergies || '-' }}</strong>
                            </div>
                            <div class="pb-kv-row">
                              <span>Current Medications</span><strong>{{ medicalForm.currentMedications || '-' }}</strong>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="col-lg-4">
                        <div class="pb-shadow-box h-100">
                          <div class="pb-shadow-box-header pb-box-header-green">
                            <span>Service Requirement</span>
                          </div>
                          <div class="pb-shadow-box-body">
                            <div class="pb-kv-row">
                              <span>Care Type</span><strong>{{ careForm.careType }}</strong>
                            </div>
                            <div class="pb-kv-row">
                              <span>Service Category</span><strong>{{ careForm.serviceCategory }}</strong>
                            </div>
                            <div class="pb-kv-row">
                              <span>Start Date</span><strong>{{ careForm.startDate }}</strong>
                            </div>
                            <div class="pb-kv-row">
                              <span>Duration</span><strong>{{ careForm.duration }} {{ careForm.durationUnit }}</strong>
                            </div>
                            <div class="pb-kv-row">
                              <span>Duty Hours</span><strong>{{ careForm.dutyHours }}</strong>
                            </div>
                            <div class="pb-kv-row">
                              <span>Service Location</span><strong>{{ careForm.serviceLocation }}</strong>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>

                    <div class="pb-shadow-box mt-3">
                      <div class="pb-shadow-box-header">
                        <span>Consent & Documents</span>
                      </div>
                      <div class="pb-shadow-box-body">
                        <div class="pb-kv-row">
                          <span>Patient Photo</span><strong>{{ photoPreviewUrl ? 'Uploaded' : 'Not uploaded' }}</strong>
                        </div>
                        <div class="pb-kv-row">
                          <span>ID Proof</span><strong>{{ idProofPreviewUrl ? 'Uploaded' : 'Not uploaded' }}</strong>
                        </div>
                        <div class="pb-kv-row">
                          <span>Medical Reports</span><strong>{{ medicalReportsPreviewUrl ? 'Uploaded' : 'Not uploaded' }}</strong>
                        </div>
                        <div class="pb-kv-row">
                          <span>Consent Form</span><strong>{{ consentFormPreviewUrl ? 'Uploaded' : 'Not uploaded' }}</strong>
                        </div>
                        <div class="pb-kv-row">
                          <span>Privacy Document</span><strong>{{ privacyDocumentPreviewUrl ? 'Uploaded' : 'Not uploaded' }}</strong>
                        </div>
                      </div>
                    </div>
                  </CCardBody>
                </div>
              </div>
              <div class="form-footer">
                <CButton color="light" variant="outline" class="back-button" @click="previousStep">
                  <CIcon :icon="cilArrowLeft" /> Back
                </CButton>
                <div class="footer-actions">
                  <CButton color="light" variant="outline" class="draft-button" @click="saveDraft">
                    <CIcon :icon="cilFile" /> Save Draft
                  </CButton>
                  <CButton color="primary" class="continue-button" @click="nextStep">
                    {{ activeStep === steps.length ? 'Finish & Onboard' : 'Continue' }}
                    <CIcon :icon="cilArrowRight" />
                  </CButton>
                </div>
              </div>
            </section>

            <!-- Requests table -->
            <section class="onboarding-panel">
              <div class="pb-card-header">
                <div class="pb-card-header-left">
                  <div class="pb-card-icon">
                    <CIcon :icon="cilLayers" />
                  </div>
                  <div>
                    <h2 class="pb-card-title">Additional Services & Requests</h2>
                    <p class="pb-card-desc">
                      Manage multiple service requests for this patient (e.g. caregiver replacement, ambulance, consumables, and other
                      services)
                    </p>
                  </div>
                </div>
                <CButton color="primary" class="add-request-button" @click="showAddRequest = !showAddRequest">
                  <CIcon :icon="cilPlus" /> Add Service Request
                </CButton>
              </div>

              <div v-if="showAddRequest" class="add-request-form">
                <div class="field-group">
                  <label class="pb-label">Service Type</label>
                  <CFormSelect v-model="newRequest.serviceType" :options="PATIENT_DROPDOWNS.serviceRequestType" />
                </div>
                <div class="field-group request-details-input">
                  <label class="pb-label">Details</label>
                  <CFormInput v-model="newRequest.details" placeholder="Enter request details" />
                </div>
                <div class="field-group">
                  <label class="pb-label">Expected Date</label>
                  <CDatePicker v-model:date="newRequest.expectedDate" format="dd/MM/yyyy" class="form-control" />
                </div>
                <div class="add-form-actions">
                  <CButton color="light" variant="outline" @click="showAddRequest = false">Cancel</CButton>
                  <CButton color="primary" @click="addServiceRequest">Add Request</CButton>
                </div>
              </div>

              <div class="request-tabs">
                <button
                  v-for="tab in requestTabs"
                  :key="tab.label"
                  class="request-tab"
                  :class="{ active: activeRequestTab === tab.label }"
                  @click="setRequestTab(tab.label)">
                  {{ tab.label }}
                  <span class="tab-count">{{ requestTabCount(tab) }}</span>
                </button>
              </div>

              <div class="table-toolbar">
                <div class="request-search">
                  <CIcon :icon="cilSearch" />
                  <input v-model="searchText" type="search" placeholder="Search service requests..." aria-label="Search service requests" />
                </div>
                <div class="table-toolbar-right">
                  <span
                    >Showing {{ filteredRequests.length ? (currentPage - 1) * rowsPerPage + 1 : 0 }} to
                    {{ Math.min(currentPage * rowsPerPage, filteredRequests.length) }} of {{ filteredRequests.length }} records</span
                  >
                  <label class="pb-label">Rows</label>
                  <CFormSelect
                    v-model.number="rowsPerPage"
                    :options="PATIENT_DROPDOWNS.rowsPerPage"
                    class="rows-select"
                    @update:modelValue="currentPage = 1" />
                </div>
              </div>

              <div class="requests-table-wrap">
                <DataTable :value="visibleRequests" dataKey="id" responsiveLayout="scroll" class="requests-table" :rowHover="true">
                  <template #empty>
                    <div class="empty-state">No service requests found.</div>
                  </template>
                  <Column header="#" headerStyle="width: 34px">
                    <template #body="{ index }">
                      {{ (currentPage - 1) * rowsPerPage + index + 1 }}
                    </template>
                  </Column>
                  <Column field="requestId" header="Request ID" headerStyle="min-width: 112px">
                    <template #body="{ data }">
                      <a href="#" class="request-id" @click.prevent>{{ data.requestId }}</a>
                    </template>
                  </Column>
                  <Column field="serviceType" header="Service Type" headerStyle="min-width: 145px">
                    <template #body="{ data }">
                      <div class="service-type-cell">
                        <span class="service-type-icon">
                          <CIcon
                            :icon="
                              data.category === 'Ambulance' ? cilClock : data.category === 'Consumables' ? cilMedicalCross : cilUser
                            " />
                        </span>
                        {{ data.serviceType }}
                      </div>
                    </template>
                  </Column>
                  <Column field="requestDate" header="Request Date" headerStyle="min-width: 105px" />
                  <Column field="details" header="Details" headerStyle="min-width: 220px">
                    <template #body="{ data }">
                      <div class="request-details">{{ data.details }}</div>
                    </template>
                  </Column>
                  <Column field="assignedTo" header="Assigned To" headerStyle="min-width: 120px" />
                  <Column field="status" header="Status" headerStyle="min-width: 95px">
                    <template #body="{ data }">
                      <Tag :severity="statusSeverity(data.status)" :value="data.status" class="status-tag" />
                    </template>
                  </Column>
                  <Column field="expectedDate" header="Expected Date" headerStyle="min-width: 110px" />
                  <Column header="Action" headerStyle="width: 94px">
                    <template #body="{ data }">
                      <div class="table-actions">
                        <button type="button" title="View request" @click="viewRequest(data)">
                          <CIcon :icon="cilLowVision" />
                        </button>
                        <button
                          type="button"
                          title="Edit request"
                          @click="
                            newRequest = { serviceType: data.category, details: data.details, expectedDate: '' };
                            showAddRequest = true;
                          ">
                          <CIcon :icon="cilPencil" />
                        </button>
                        <button type="button" title="More actions" @click="showRequestActions(data)">
                          <CIcon :icon="cilOptions" />
                        </button>
                      </div>
                    </template>
                  </Column>
                </DataTable>
              </div>
            </section>
          </main>
        </div>
      </div>
    </CCol>
  </CRow>
</template>


<style scoped>
.patient-photo {
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
}

.patient-summary-photo {
  width: 100%;
  height: 100%;
  display: block;
  object-fit: cover;
}

.patient-summary-details {
  min-width: 0;
}
</style>
