<script setup>
import { ref, computed, onMounted, watch, reactive, nextTick } from 'vue';
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
import api from '@/services/adminAxios';
const toast = useToast();
import moment from 'moment';
import { loadingStore } from '@/services/loading';

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
const patients = ref([]);
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
const btnLoading = ref(false);
const serverErrors = ref({});
const editingPatientId = ref(null);
const currentServiceRequirementId = ref(null);
const existingFiles = reactive({ photo: null, id_proof: null, medical_reports: null, consent_form: null, privacy_document: null });
const onboardingPanel = ref(null);
const totalRecords = ref(0);

// Allowed file types and maximum upload size (MB)
const allowedPhotoTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
const allowedIdentityTypes = ['application/pdf', ...allowedPhotoTypes];
const allowedMedicalReportTypes = ['application/pdf', ...allowedPhotoTypes];
const allowedPdfTypes = ['application/pdf'];
const loading = computed(() => loadingStore.loading);

const maxDocumentSize = 1;
const lazyParams = ref({
  first: 0,
  rows: 10,
  page: 0,
});
const defaultFilters = {
  keyword: null,
};

const filters = reactive({ ...defaultFilters });
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
    .split(/\s+/)
    .filter(Boolean)
    .slice(0, 2)
    .map((part) => part.charAt(0).toUpperCase())
    .join('');
});

const isEditMode = computed(() => editingPatientId.value !== null);
const photoDisplayUrl = computed(() => photoPreviewUrl.value || existingFiles.photo);
const idProofDisplayUrl = computed(() => idProofPreviewUrl.value || existingFiles.id_proof);
const medicalReportsDisplayUrl = computed(() => medicalReportsPreviewUrl.value || existingFiles.medical_reports);
const consentFormDisplayUrl = computed(() => consentFormPreviewUrl.value || existingFiles.consent_form);
const privacyDocumentDisplayUrl = computed(() => privacyDocumentPreviewUrl.value || existingFiles.privacy_document);

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
  city: { default: '', required: true, message: 'City is required' },
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
      dobMoment && dobMoment.isValid() && !dobMoment.isAfter(moment(), 'day') ? moment().diff(dobMoment, 'years') : null;

    patient.value = {
      ...patient.value,
      name: basicForm.patientName || null,
      age: basicForm.age !== '' && basicForm.age !== null ? basicForm.age : calculatedAge,
      gender: basicForm.gender || null,
      location: [basicForm.locationArea, basicForm.city, basicForm.state].filter(Boolean).join(', ') || null,
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
const reviewStepTitle = computed(() => (isEditMode.value ? 'Review & Update' : 'Review & Onboard'));

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

const setRequestTab = (tab) => {
  activeRequestTab.value = tab;
  currentPage.value = 1;
};

const goToStep = (step) => {
  // In edit mode, allow the user to jump directly to any onboarding tab.
  // In create mode, retain the existing sequential navigation behaviour.
  if (isEditMode.value) {
    if (step >= 1 && step <= steps.length) activeStep.value = step;
    return;
  }

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
    photoDisplayUrl.value &&
    idProofDisplayUrl.value &&
    medicalReportsDisplayUrl.value &&
    consentFormDisplayUrl.value &&
    privacyDocumentDisplayUrl.value
  );
};

const nextStep = async () => {
  // Validate only the current section before moving forward.
  if (activeStep.value === 1 && !validateBasic()) return;
  if (activeStep.value === 2 && !validateMedical()) return;
  if (activeStep.value === 3 && !validateCare()) return;
  if (activeStep.value === 4 && !validateDocuments()) return;

  if (activeStep.value < steps.length) {
    activeStep.value += 1;
    return;
  }

  await submitPatient();
};

const triggerDocumentUpload = (inputElement) => {
  inputElement?.click();
};

// Convert Date objects and date-like values to Laravel's YYYY-MM-DD format.
const formatApiDate = (value) => {
  if (!value) return '';
  const parsed = moment(value);
  return parsed.isValid() ? parsed.format('YYYY-MM-DD') : String(value);
};

// Build the request using the exact snake_case names expected by Laravel.
const buildPatientFormData = () => {
  const payload = new FormData();

  if (editingPatientId.value) payload.append('id', String(editingPatientId.value));
  if (currentServiceRequirementId.value) payload.append('current_service_requirement_id', String(currentServiceRequirementId.value));

  const fields = {
    patient_name: basicForm.patientName,
    date_of_birth: formatApiDate(basicForm.dateOfBirth),
    gender: basicForm.gender,

    primary_contact_name: basicForm.primaryContactName,
    relationship: basicForm.relationship,
    phone_number: basicForm.phoneNumber,
    alternate_number: basicForm.alternateNumber,
    location_area: basicForm.locationArea,
    address: basicForm.address,
    city: basicForm.city,
    state: basicForm.state,
    pin_code: basicForm.pinCode,

    emergency_contact_name: basicForm.emergencyContactName,
    emergency_relationship: basicForm.emergencyRelationship,
    emergency_phone: basicForm.emergencyPhone,

    primary_diagnosis: medicalForm.primaryDiagnosis,
    secondary_diagnosis: medicalForm.secondaryDiagnosis,
    health_status: medicalForm.healthStatus,
    allergies: medicalForm.allergies,
    chronic_conditions: medicalForm.chronicConditions,
    medical_history: medicalForm.medicalHistory,
    current_medications: medicalForm.currentMedications,
    special_care_needs: medicalForm.specialCareNeeds,
    dietary_preferences: medicalForm.dietaryPreferences,

    care_type: careForm.careType,
    care_start_date: formatApiDate(careForm.startDate),
    duration: careForm.duration,
    duration_unit: careForm.durationUnit,
    duty_hours: careForm.dutyHours,
    service_category: careForm.serviceCategory,
    service_location: careForm.serviceLocation,
    service_location_area: careForm.locationArea,
    service_city: careForm.locationCity,
    caregiver_gender: careForm.caregiverGender,
    service_details: careForm.serviceDetails,
    expected_fee_range: careForm.feeRange,
  };

  Object.entries(fields).forEach(([key, value]) => {
    if (value !== null && value !== undefined && value !== '') {
      payload.append(key, String(value));
    }
  });

  // Laravel receives this as an array: additional_facilities.
  (careForm.additionalFacilities || []).forEach((facility) => {
    payload.append('additional_facilities[]', facility);
  });

  // Attach the actual File objects, not their preview URLs.
  const files = {
    photo: photoFile.value,
    id_proof: idProofFile.value,
    medical_reports: medicalReportsFile.value,
    consent_form: consentFormFile.value,
    privacy_document: privacyDocumentFile.value,
  };

  Object.entries(files).forEach(([key, file]) => {
    if (file instanceof File) {
      payload.append(key, file, file.name);
    }
  });

  return payload;
};

const submitPatient = async () => {
  // Final safety check before sending the request.
  const basicValid = validateBasic();
  const medicalValid = validateMedical();
  const careValid = validateCare();
  const documentsValid = validateDocuments();

  if (!basicValid || !medicalValid || !careValid || !documentsValid) {
    if (!basicValid) activeStep.value = 1;
    else if (!medicalValid) activeStep.value = 2;
    else if (!careValid) activeStep.value = 3;
    else activeStep.value = 4;

    toast.error('Please complete all required fields and documents.');
    return;
  }

  btnLoading.value = true;
  serverErrors.value = {};

  try {
    const formData = buildPatientFormData();

    const response = await api.post('/patients/save', formData);

    const responseData = response?.data || {};

    if (responseData.status === false || responseData.success === false) {
      toast.error(responseData.message || 'Unable to save patient.');
      return;
    }

    applyBackendPatientResponse(responseData);
    toast.success(responseData.message || (isEditMode.value ? 'Patient updated successfully.' : 'Patient enrolled successfully.'));

    resetPatientForm();
    await getPatients();
  } catch (error) {
    const responseData = error?.response?.data;

    if (error?.response?.status === 422 && responseData?.errors) {
      serverErrors.value = responseData.errors;

      const firstError = Object.values(responseData.errors).flat()[0];
      toast.error(firstError || responseData.message || 'Please check the form fields.');
    } else {
      toast.error(responseData?.message || error?.message || 'Something went wrong while saving the patient.');
    }

    console.error('Patient enrolment error:', error);
  } finally {
    btnLoading.value = false;
  }
};
const getPatients = async () => {
  try {
    const page = lazyParams.value.page + 1;

    const response = await api.post('/patients/index', {
      page: page,
      per_page: lazyParams.value.rows,
      ...filters,
    });

    const result = response.data.data;

    patients.value = result.data;

    totalRecords.value = result.total;
  } catch (error) {
    console.error('Enrolment Error:', error);
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

  getPatients();
};
const searchFilters = () => {
  lazyParams.value.page = 0;
  getPatients();
};

const resetFilters = () => {
  lazyParams.value.page = 0;
  Object.assign(filters, defaultFilters);
  getPatients();
};

// Clear all form fields and uploaded files after a successful save.
const resetPatientForm = () => {
  resetBasic();
  resetMedical();
  resetCare();

  clearPhotoFile(photoInput.value);
  clearIdProofFile(idProofInput.value);
  clearMedicalReportsFile(medicalReportsInput.value);
  clearConsentFormFile(consentFormInput.value);
  clearPrivacyDocumentFile(privacyDocumentInput.value);

  documentValidationAttempted.value = false;
  serverErrors.value = {};
  editingPatientId.value = null;
  currentServiceRequirementId.value = null;
  Object.keys(existingFiles).forEach((key) => {
    existingFiles[key] = null;
  });
  activeStep.value = 1;

  patient.value = {
    code: null,
    name: null,
    age: null,
    gender: null,
    location: null,
    status: null,
    progress: 0,
  };

  // Clear native file input values as well.
  [photoInput.value, idProofInput.value, medicalReportsInput.value, consentFormInput.value, privacyDocumentInput.value].forEach((input) => {
    if (input) input.value = '';
  });
};
const viewPatient = async (id) => {
  try {
    const response = await api.post('/patients/view', { id });
    const patientRecord = response?.data?.data?.patient;
    if (!patientRecord) return toast.error('Patient details not found.');
    // Keep the existing listing unchanged; show the fetched details in the form's review state.
    await editPatient(id);
    activeStep.value = 5;
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Unable to fetch patient details.');
  }
};

const editPatient = async (id) => {
  try {
    const response = await api.post('/patients/view', { id }, { headers: { Accept: 'application/json' } });
    const result = response?.data?.data || {};
    const record = result.patient || {};
    const service = result.current_service_requirement || record.current_service_requirement || null;
    const files = result.files || {};
    if (!record.id) {
      toast.error('Patient details not found.');
      return;
    }

    editingPatientId.value = record.id;
    currentServiceRequirementId.value = service?.id || record.current_service_requirement_id || null;

    patchBasicValue({
      patientName: record.patient_name || '',
      dateOfBirth: record.date_of_birth ? new Date(record.date_of_birth) : '',
      age: record.age ?? '',
      gender: record.gender || '',
      primaryContactName: record.primary_contact_name || '',
      relationship: record.relationship || '',
      phoneNumber: record.phone_number || '',
      alternateNumber: record.alternate_number || '',
      locationArea: record.location_area || '',
      address: record.address || '',
      city: record.city || '',
      state: record.state || '',
      pinCode: record.pin_code || '',
      emergencyContactName: record.emergency_contact_name || '',
      emergencyRelationship: record.emergency_relationship || '',
      emergencyPhone: record.emergency_phone || '',
    });

    patchMedicalValue({
      primaryDiagnosis: record.primary_diagnosis || '',
      secondaryDiagnosis: record.secondary_diagnosis || '',
      healthStatus: record.health_status || '',
      allergies: record.allergies || '',
      chronicConditions: record.chronic_conditions || '',
      medicalHistory: record.medical_history || '',
      currentMedications: record.current_medications || '',
      specialCareNeeds: record.special_care_needs || '',
      dietaryPreferences: record.dietary_preferences || '',
    });

    if (service) {
      patchCareValue({
        careType: service.care_type || '',
        serviceCategory: service.service_category || '',
        startDate: service.care_start_date ? new Date(service.care_start_date) : '',
        duration: service.duration ?? '',
        durationUnit: service.duration_unit || '',
        dutyHours: service.duty_hours || '',
        serviceLocation: service.service_location || '',
        locationArea: service.service_location_area || '',
        locationCity: service.service_city || '',
        caregiverGender: service.caregiver_gender || '',
        serviceDetails: service.service_details || '',
        feeRange: service.expected_fee_range || '',
        additionalFacilities: Array.isArray(service.additional_facilities) ? [...service.additional_facilities] : [],
      });
    } else {
      resetCare();
    }

    // Existing server files are displayed separately from useFileUpload's local File previews.
    Object.assign(existingFiles, {
      photo: files.photo || (record.photo ? record.photo : null),
      id_proof: files.id_proof || (record.id_proof ? record.id_proof : null),
      medical_reports: files.medical_reports || (record.medical_reports ? record.medical_reports : null),
      consent_form: files.consent_form || (record.consent_form ? record.consent_form : null),
      privacy_document: files.privacy_document || (record.privacy_document ? record.privacy_document : null),
    });

    // Clear only newly selected files from any previous form session.
    clearPhotoFile(photoInput.value);
    clearIdProofFile(idProofInput.value);
    clearMedicalReportsFile(medicalReportsInput.value);
    clearConsentFormFile(consentFormInput.value);
    clearPrivacyDocumentFile(privacyDocumentInput.value);
    [photoInput.value, idProofInput.value, medicalReportsInput.value, consentFormInput.value, privacyDocumentInput.value].forEach(
      (input) => {
        if (input) input.value = '';
      }
    );

    patient.value = {
      ...patient.value,
      code: record.patient_code || null,
      name: record.patient_name || null,
      age: record.age ?? null,
      gender: record.gender || null,
      location: [record.location_area, record.city, record.state].filter(Boolean).join(', '),
      status: record.status || 'draft',
    };
    documentValidationAttempted.value = false;
    serverErrors.value = {};
    activeStep.value = 1;
    await nextTick();
    onboardingPanel.value?.scrollIntoView({ behavior: 'smooth', block: 'start' });
  } catch (error) {
    console.error('Patient edit error:', error);
    toast.error(error?.response?.data?.errors?.id?.[0] || error?.response?.data?.message || 'Unable to load patient details.');
  } finally {
    btnLoading.value = false;
  }
};

// Draft persistence needs a separate backend endpoint/status contract.
// Until that endpoint exists, do not display a misleading "saved" message.
const saveDraft = () => {
  toast.info(isEditMode.value ? 'Use Update to save your patient changes.' : 'Draft saving is not connected yet. Use Finish & Onboard to submit the patient.');
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
const patientStatusConfig = {
  draft: {
    label: 'Draft',
    severity: 'warning',
  },
  active: {
    label: 'Active',
    severity: 'success',
  },
  inactive: {
    label: 'Inactive',
    severity: 'danger',
  },
  pending: {
    label: 'Pending',
    severity: 'info',
  },
  completed: {
    label: 'Completed',
    severity: 'success',
  },
  cancelled: {
    label: 'Cancelled',
    severity: 'danger',
  },
};

const getPatientStatusSeverity = (status) => {
  return patientStatusConfig[status?.toLowerCase()]?.severity || 'secondary';
};

const formatPatientStatus = (status) => {
  return patientStatusConfig[status?.toLowerCase()]?.label || (status ? status.charAt(0).toUpperCase() + status.slice(1) : 'Unknown');
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
/*
|--------------------------------------------------------------------------
| Date Transformation
|--------------------------------------------------------------------------
*/
const formatDate = (date) => {
  if (!date) {
    return '-';
  }

  // Date pickers may provide a Date object, while API/list values are strings.
  const formattedDate = date instanceof Date
    ? date
    : new Date(String(date).replace(' ', 'T'));

  if (Number.isNaN(formattedDate.getTime())) {
    return '-';
  }

  return formattedDate.toLocaleString('en-IN', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    hour12: true,
  });
};
onMounted(() => {
  breadcrumbs.value = ['Patient', 'Enrolment'];
  getPatients();
});
</script>

<template>
  <CRow>
    <CCol :lg="12" :md="12" :sm="12">
      <div class="patient-management-page">
        <div class="dashboard-shell">
          <!-- Main content -->
          <main ref="onboardingPanel" class="dashboard-main">
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
                  :aria-disabled="!isEditMode && index + 1 > activeStep"
                  :style="{ cursor: isEditMode || index + 1 <= activeStep ? 'pointer' : 'not-allowed' }">
                  <button type="button" class="step-number">
                    <CIcon v-if="index + 1 < activeStep" :icon="cilCheck" />
                    <span v-else>{{ index + 1 }}</span>
                  </button>
                  <span class="step-label">{{ index === steps.length - 1 ? reviewStepTitle : step }}</span>
                </div>
              </div>

              <!-- Patient strip -->
              <div class="patient-summary-grid">
                <!-- Patient Identity: uses the existing uploaded-photo preview -->
                <div class="patient-identity">
                  <div class="patient-photo">
                    <img v-if="photoDisplayUrl" :src="photoDisplayUrl" alt="Patient profile" class="patient-summary-photo" />
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
                              v-model="careForm.duration"
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
                            <span v-if="photoDisplayUrl" class="text-success small">Uploaded</span>
                          </div>
                          <div class="d-flex align-items-center gap-3 flex-wrap">
                            <img
                              v-if="photoDisplayUrl"
                              :src="photoDisplayUrl"
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
                                {{ photoDisplayUrl ? 'Change Photo' : 'Upload Photo' }}
                              </CButton>
                              <CButton
                                v-if="photoDisplayUrl"
                                color="danger"
                                variant="ghost"
                                class="ms-2"
                                @click="clearPhotoFile(photoInput)">
                                Remove
                              </CButton>
                            </div>
                          </div>
                          <div v-if="photoFileError" class="text-danger small mt-2">{{ photoFileError }}</div>
                          <div v-if="documentValidationAttempted && !photoDisplayUrl" class="text-danger small mt-2">
                            Patient photo is required.
                          </div>
                        </div>
                      </div>

                      <!-- ID proof -->
                      <div class="col-lg-6">
                        <div class="border rounded p-3 h-100">
                          <div class="d-flex justify-content-between align-items-center mb-2">
                            <strong>ID Proof <span class="text-danger">*</span></strong>
                            <span v-if="idProofDisplayUrl" class="text-success small">Uploaded</span>
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
                                {{ idProofDisplayUrl ? 'Replace File' : 'Upload File' }}
                              </CButton>
                              <a v-if="idProofDisplayUrl" :href="idProofDisplayUrl" target="_blank" rel="noopener" class="small ms-2"
                                >View</a
                              >
                              <CButton
                                v-if="idProofDisplayUrl"
                                color="danger"
                                variant="ghost"
                                class="ms-2"
                                @click="clearIdProofFile(idProofInput)"
                                >Remove</CButton
                              >
                            </div>
                          </div>
                          <div v-if="idProofFileError" class="text-danger small mt-2">{{ idProofFileError }}</div>
                          <div v-if="documentValidationAttempted && !idProofDisplayUrl" class="text-danger small mt-2">
                            ID proof is required.
                          </div>
                        </div>
                      </div>

                      <!-- Medical reports -->
                      <div class="col-lg-6">
                        <div class="border rounded p-3 h-100">
                          <div class="d-flex justify-content-between align-items-center mb-2">
                            <strong>Medical Reports <span class="text-danger">*</span></strong>
                            <span v-if="medicalReportsDisplayUrl" class="text-success small">Uploaded</span>
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
                                {{ medicalReportsDisplayUrl ? 'Replace File' : 'Upload File' }}
                              </CButton>
                              <a
                                v-if="medicalReportsDisplayUrl"
                                :href="medicalReportsDisplayUrl"
                                target="_blank"
                                rel="noopener"
                                class="small ms-2"
                                >View</a
                              >
                              <CButton
                                v-if="medicalReportsDisplayUrl"
                                color="danger"
                                variant="ghost"
                                class="ms-2"
                                @click="clearMedicalReportsFile(medicalReportsInput)"
                                >Remove</CButton
                              >
                            </div>
                          </div>
                          <div v-if="medicalReportsFileError" class="text-danger small mt-2">{{ medicalReportsFileError }}</div>
                          <div v-if="documentValidationAttempted && !medicalReportsDisplayUrl" class="text-danger small mt-2">
                            Medical reports are required.
                          </div>
                        </div>
                      </div>

                      <!-- Consent form -->
                      <div class="col-lg-6">
                        <div class="border rounded p-3 h-100">
                          <div class="d-flex justify-content-between align-items-center mb-2">
                            <strong>Consent Form <span class="text-danger">*</span></strong>
                            <span v-if="consentFormDisplayUrl" class="text-success small">Uploaded</span>
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
                                {{ consentFormDisplayUrl ? 'Replace File' : 'Upload PDF' }}
                              </CButton>
                              <a
                                v-if="consentFormDisplayUrl"
                                :href="consentFormDisplayUrl"
                                target="_blank"
                                rel="noopener"
                                class="small ms-2"
                                >View</a
                              >
                              <CButton
                                v-if="consentFormDisplayUrl"
                                color="danger"
                                variant="ghost"
                                class="ms-2"
                                @click="clearConsentFormFile(consentFormInput)"
                                >Remove</CButton
                              >
                            </div>
                          </div>
                          <div v-if="consentFormFileError" class="text-danger small mt-2">{{ consentFormFileError }}</div>
                          <div v-if="documentValidationAttempted && !consentFormDisplayUrl" class="text-danger small mt-2">
                            Consent form is required.
                          </div>
                        </div>
                      </div>

                      <!-- Privacy document -->
                      <div class="col-lg-6">
                        <div class="border rounded p-3 h-100">
                          <div class="d-flex justify-content-between align-items-center mb-2">
                            <strong>Privacy Document <span class="text-danger">*</span></strong>
                            <span v-if="privacyDocumentDisplayUrl" class="text-success small">Uploaded</span>
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
                                {{ privacyDocumentDisplayUrl ? 'Replace File' : 'Upload PDF' }}
                              </CButton>
                              <a
                                v-if="privacyDocumentDisplayUrl"
                                :href="privacyDocumentDisplayUrl"
                                target="_blank"
                                rel="noopener"
                                class="small ms-2"
                                >View</a
                              >
                              <CButton
                                v-if="privacyDocumentDisplayUrl"
                                color="danger"
                                variant="ghost"
                                class="ms-2"
                                @click="clearPrivacyDocumentFile(privacyDocumentInput)"
                                >Remove</CButton
                              >
                            </div>
                          </div>
                          <div v-if="privacyDocumentFileError" class="text-danger small mt-2">{{ privacyDocumentFileError }}</div>
                          <div v-if="documentValidationAttempted && !privacyDocumentDisplayUrl" class="text-danger small mt-2">
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
                <div class="pb-patient-step-content-inr review-content-wrapper">
                  <div class="pb-section-title review-section-title">
                    <strong>{{ reviewStepTitle }}</strong>
                  </div>

                  <div class="review-summary-grid">
                    <div class="review-summary-card">
                      <div class="review-summary-card-header review-header-blue">Basic Information &amp; Contact</div>
                      <div class="review-summary-card-body">
                        <div class="review-value-row"><span>Patient ID</span><strong>{{ patient.code || 'Generated after saving' }}</strong></div>
                        <div class="review-value-row"><span>Patient Name</span><strong>{{ basicForm.patientName || '-' }}</strong></div>
                        <div class="review-value-row"><span>Date of Birth</span><strong>{{ formatDate(basicForm.dateOfBirth) || '-' }}</strong></div>
                        <div class="review-value-row"><span>Age</span><strong>{{ basicForm.age || patient.age || '-' }}</strong></div>
                        <div class="review-value-row"><span>Gender</span><strong>{{ basicForm.gender || '-' }}</strong></div>
                        <div class="review-value-row"><span>Primary Contact</span><strong>{{ basicForm.primaryContactName || '-' }}</strong></div>
                        <div class="review-value-row"><span>Relationship</span><strong>{{ basicForm.relationship || '-' }}</strong></div>
                        <div class="review-value-row"><span>Phone Number</span><strong>{{ basicForm.phoneNumber || '-' }}</strong></div>
                        <div class="review-value-row"><span>Alternate Number</span><strong>{{ basicForm.alternateNumber || '-' }}</strong></div>
                        <div class="review-value-row"><span>Emergency Contact</span><strong>{{ basicForm.emergencyContactName || '-' }}</strong></div>
                        <div class="review-value-row"><span>Emergency Phone</span><strong>{{ basicForm.emergencyPhone || '-' }}</strong></div>
                        <div class="review-value-row"><span>Address</span><strong>{{ basicForm.address || '-' }}</strong></div>
                        <div class="review-value-row"><span>Location</span><strong>{{ [basicForm.locationArea, basicForm.city, basicForm.state, basicForm.pinCode].filter(Boolean).join(', ') || '-' }}</strong></div>
                      </div>
                    </div>

                    <div class="review-summary-card">
                      <div class="review-summary-card-header review-header-red">Medical / Care Information</div>
                      <div class="review-summary-card-body">
                        <div class="review-value-row"><span>Primary Diagnosis</span><strong>{{ medicalForm.primaryDiagnosis || '-' }}</strong></div>
                        <div class="review-value-row"><span>Secondary Diagnosis</span><strong>{{ medicalForm.secondaryDiagnosis || '-' }}</strong></div>
                        <div class="review-value-row"><span>Health Status</span><strong>{{ medicalForm.healthStatus || '-' }}</strong></div>
                        <div class="review-value-row"><span>Allergies</span><strong>{{ medicalForm.allergies || '-' }}</strong></div>
                        <div class="review-value-row"><span>Chronic Conditions</span><strong>{{ medicalForm.chronicConditions || '-' }}</strong></div>
                        <div class="review-value-row"><span>Medical History</span><strong>{{ medicalForm.medicalHistory || '-' }}</strong></div>
                        <div class="review-value-row"><span>Current Medications</span><strong>{{ medicalForm.currentMedications || '-' }}</strong></div>
                        <div class="review-value-row"><span>Special Care Needs</span><strong>{{ medicalForm.specialCareNeeds || '-' }}</strong></div>
                        <div class="review-value-row"><span>Dietary Preferences</span><strong>{{ medicalForm.dietaryPreferences || '-' }}</strong></div>
                      </div>
                    </div>

                    <div class="review-summary-card">
                      <div class="review-summary-card-header review-header-green">Service Requirement</div>
                      <div class="review-summary-card-body">
                        <div class="review-value-row"><span>Care Type</span><strong>{{ careForm.careType || '-' }}</strong></div>
                        <div class="review-value-row"><span>Service Category</span><strong>{{ careForm.serviceCategory || '-' }}</strong></div>
                        <div class="review-value-row"><span>Start Date</span><strong>{{ formatDate(careForm.startDate) || '-' }}</strong></div>
                        <div class="review-value-row"><span>Duration</span><strong>{{ [careForm.duration, careForm.durationUnit].filter(Boolean).join(' ') || '-' }}</strong></div>
                        <div class="review-value-row"><span>Duty Hours</span><strong>{{ careForm.dutyHours || '-' }}</strong></div>
                        <div class="review-value-row"><span>Service Location</span><strong>{{ careForm.serviceLocation || '-' }}</strong></div>
                        <div class="review-value-row"><span>Location Area</span><strong>{{ careForm.locationArea || '-' }}</strong></div>
                        <div class="review-value-row"><span>City</span><strong>{{ careForm.locationCity || '-' }}</strong></div>
                        <div class="review-value-row"><span>Caregiver Gender</span><strong>{{ careForm.caregiverGender || '-' }}</strong></div>
                        <div class="review-value-row"><span>Expected Fee Range</span><strong>{{ careForm.feeRange || '-' }}</strong></div>
                        <div class="review-value-row"><span>Service Details</span><strong>{{ careForm.serviceDetails || '-' }}</strong></div>
                        <div class="review-value-row"><span>Additional Facilities</span><strong>{{ (careForm.additionalFacilities || []).join(', ') || '-' }}</strong></div>
                      </div>
                    </div>
                  </div>

                  <div class="review-summary-card review-documents-card">
                    <div class="review-summary-card-header">Consent &amp; Documents</div>
                    <div class="review-summary-card-body review-documents-grid">
                      <div class="review-value-row"><span>Patient Photo</span><strong>{{ photoDisplayUrl ? 'Uploaded' : 'Not uploaded' }}</strong></div>
                      <div class="review-value-row"><span>ID Proof</span><strong>{{ idProofDisplayUrl ? 'Uploaded' : 'Not uploaded' }}</strong></div>
                      <div class="review-value-row"><span>Medical Reports</span><strong>{{ medicalReportsDisplayUrl ? 'Uploaded' : 'Not uploaded' }}</strong></div>
                      <div class="review-value-row"><span>Consent Form</span><strong>{{ consentFormDisplayUrl ? 'Uploaded' : 'Not uploaded' }}</strong></div>
                      <div class="review-value-row"><span>Privacy Document</span><strong>{{ privacyDocumentDisplayUrl ? 'Uploaded' : 'Not uploaded' }}</strong></div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="form-footer">
                <CButton color="light" variant="outline" class="back-button" :disabled="btnLoading" @click="previousStep">
                  <CIcon :icon="cilArrowLeft" /> Back
                </CButton>
                <div class="footer-actions">
                  <CButton color="light" variant="outline" class="draft-button" @click="saveDraft">
                    <CIcon :icon="cilFile" /> Save Draft
                  </CButton>
                  <CButton color="primary" class="continue-button" :disabled="btnLoading" @click="nextStep">
                    <CSpinner v-if="btnLoading" size="sm" class="me-1" />

                    <template v-else>
                      {{ activeStep === steps.length ? (isEditMode ? 'Update' : 'Finish & Onboard') : 'Continue' }}
                      <CIcon :icon="cilArrowRight" />
                    </template>
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
                    <h2 class="pb-card-title">All Patients</h2>
                  </div>
                </div>
              </div>

              <div class="requests-table-wrap">
                <DataTable
                  :value="patients"
                  class="patient-table"
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
                    <div class="text-center py-4">No patients found.</div>
                  </template>

                  <template #loading>
                    <div class="text-center py-4">Loading patients...</div>
                  </template>

                  <!-- Serial Number -->
                  <Column header="#" style="width: 60px">
                    <template #body="{ index }">
                      {{ lazyParams.first + index + 1 }}
                    </template>
                  </Column>

                  <!-- Patient Information -->
                  <Column header="Patient Information" style="min-width: 220px">
                    <template #body="{ data }">
                      <div class="d-flex align-items-center gap-2">
                        <img
                          :src="data.photo_url || defaultPatientImage"
                          class="patient-list-img"
                          alt="Patient"
                          @error="handlePatientImageError" />

                        <div class="d-flex flex-column">
                          <strong class="patient-name">
                            {{ data.patient_name || 'N/A' }}
                          </strong>

                          <small class="text-muted">
                            {{ data.patient_code || 'N/A' }}
                          </small>

                          <small class="text-muted">
                            {{ data.age ?? 'N/A' }} years /
                            {{ data.gender || 'N/A' }}
                          </small>
                        </div>
                      </div>
                    </template>
                  </Column>

                  <!-- Contact -->
                  <Column header="Contact" style="min-width: 150px">
                    <template #body="{ data }">
                      <div class="d-flex flex-column">
                        <span>
                          <CIcon :icon="cilPhone" class="me-1 text-muted" />
                          {{ data.phone_number || 'N/A' }}
                        </span>

                        <small v-if="data.alternate_number" class="text-muted"> Alt: {{ data.alternate_number }} </small>
                      </div>
                    </template>
                  </Column>

                  <!-- Location -->
                  <Column header="Location" style="min-width: 160px">
                    <template #body="{ data }">
                      <div class="d-flex flex-column">
                        <span>{{ data.city || 'N/A' }}</span>
                        <small class="text-muted">
                          {{ data.state || '' }}
                          {{ data.pin_code ? `- ${data.pin_code}` : '' }}
                        </small>
                      </div>
                    </template>
                  </Column>

                  <!-- Care Requirement -->
                  <Column header="Care Requirement" style="min-width: 190px">
                    <template #body="{ data }">
                      <div v-if="data.current_service_requirement">
                        <strong>
                          {{ data.current_service_requirement.care_type || 'N/A' }}
                        </strong>

                        <small class="d-block text-muted">
                          {{ data.current_service_requirement.service_category || 'N/A' }}
                        </small>

                        <small class="d-block text-muted">
                          {{ data.current_service_requirement.duty_hours || '' }}
                        </small>

                        <small v-if="data.service_requirements?.length > 1" class="text-primary">
                          +{{ data.service_requirements.length - 1 }} more requirements
                        </small>
                      </div>

                      <span v-else class="text-muted"> Not assigned </span>
                    </template>
                  </Column>

                  <!-- Caregiver -->
                  <Column header="Caregiver" style="min-width: 150px">
                    <template #body="{ data }">
                      <template v-if="data.caregiver_assignments?.length">
                        <div v-for="assignment in data.caregiver_assignments.slice(0, 2)" :key="assignment.id" class="mb-1">
                          <strong>
                            {{ assignment.caregiver?.name || 'Caregiver assigned' }}
                          </strong>
                          <small class="d-block text-muted">
                            {{ assignment.status || 'Assigned' }}
                          </small>
                        </div>

                        <small v-if="data.caregiver_assignments.length > 2" class="text-primary">
                          +{{ data.caregiver_assignments.length - 2 }} more
                        </small>
                      </template>

                      <Tag v-else severity="warning" value="Not Assigned" />
                    </template>
                  </Column>

                  <!-- Patient Status -->
                  <Column header="Status" style="width: 120px">
                    <template #body="{ data }">
                      <Tag :severity="getPatientStatusSeverity(data.status)" :value="formatPatientStatus(data.status)" />
                    </template>
                  </Column>

                  <!-- Registration Date -->
                  <Column header="Registered On" style="min-width: 130px">
                    <template #body="{ data }">
                      {{ formatDate(data.created_at) }}
                    </template>
                  </Column>

                  <!-- Actions -->
                  <Column header="Action" style="min-width: 150px">
                    <template #body="{ data }">
                      <CButton
                        color="info"
                        variant="outline"
                        size="sm"
                        class="me-2 mb-1"
                        title="View Patient"
                        @click="viewPatient(data.id)">
                        <CIcon :icon="cilLowVision" />
                      </CButton>

                      <CButton
                        color="warning"
                        variant="outline"
                        size="sm"
                        class="me-2 mb-1"
                        title="Edit Patient"
                        @click="editPatient(data.id)">
                        <CIcon :icon="cilPencil" />
                      </CButton>
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


/* Review tab: use explicit layout so the summary remains visible in both create and edit modes. */
.review-content-wrapper { display: block !important; width: 100%; }
.review-section-title { display: block; margin-bottom: 18px; color: #24324b; }
.review-summary-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; align-items: stretch; }
.review-summary-card { min-width: 0; height: 100%; overflow: hidden; border: 1px solid #dce5f1; border-radius: 8px; background: #fff; }
.review-summary-card-header { padding: 12px 15px; font-weight: 700; color: #24324b; background: #f4f7fb; border-bottom: 1px solid #dce5f1; }
.review-header-blue { background: #edf4ff; }
.review-header-red { background: #fff0f0; }
.review-header-green { background: #effaf3; }
.review-summary-card-body { display: block !important; padding: 12px 15px; }
.review-value-row { display: flex; justify-content: space-between; align-items: flex-start; gap: 14px; padding: 8px 0; border-bottom: 1px solid #edf0f5; font-size: 13px; }
.review-value-row:last-child { border-bottom: 0; }
.review-value-row > span { flex: 0 0 42%; color: #718096; }
.review-value-row > strong { flex: 1; min-width: 0; color: #263449; font-weight: 600; text-align: right; overflow-wrap: anywhere; white-space: pre-wrap; }
.review-documents-card { margin-top: 16px; height: auto; }
.review-documents-grid { display: grid !important; grid-template-columns: repeat(2, minmax(0, 1fr)); column-gap: 24px; }
@media (max-width: 991px) { .review-summary-grid { grid-template-columns: 1fr; } }
@media (max-width: 575px) { .review-documents-grid { grid-template-columns: 1fr; } .review-value-row { gap: 8px; } }

</style>
