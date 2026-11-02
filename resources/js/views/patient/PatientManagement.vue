<script setup>
import { ref, computed, onMounted } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import { CRow, CCol, CCard, CCardHeader, CCardBody, CButton, CFormInput, CFormSelect } from '@coreui/vue';
import CIcon from '@coreui/icons-vue';
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
const rowsPerPage = ref(10);

const patient = ref({
  code: 'PT-2026-0001',
  name: 'Mr. Subhash Dutta',
  age: 72,
  gender: 'Male',
  location: 'Salt Lake, Kolkata',
  status: 'In Onboarding',
  progress: 70,
});

const basicForm = ref({
  patientId: 'PT-2026-0001', patientName: '', dateOfBirth: '', age: '', gender: '',
  primaryContactName: '', relationship: '', phoneNumber: '', alternateNumber: '',
  address: '', locationArea: '', city: 'Kolkata', state: 'West Bengal', pinCode: '',
  emergencyContactName: '', emergencyRelationship: '', emergencyPhone: '',
});

const medicalForm = ref({
  primaryDiagnosis: '', secondaryDiagnosis: '', healthStatus: '', allergies: '',
  chronicConditions: '', medicalHistory: '', currentMedications: '',
  specialCareNeeds: '', dietaryPreferences: '',
});

const careForm = ref({
  careType: 'Elderly Care',
  serviceCategory: 'Post-Operative Care',
  startDate: '2026-10-01',
  duration: 3,
  durationUnit: 'Months',
  dutyHours: '12 Hours',
  serviceLocation: 'Home (Patient Residence)',
  locationArea: 'Salt Lake',
  locationCity: 'Kolkata',
  caregiverGender: 'Any',
  serviceDetails:
    'Need elderly caregiver with post-operative care experience, mobility assistance, daily vitals support and monitoring of vitals. Caregiver should be patient, reliable and able to assist with daily activities.',
  feeRange: '₹ 220 – 280 per hour',
  additionalFacilities: ['Physiotherapy Support', 'Nursing Support'],
});

const steps = [
  'Basic Info & Contact',
  'Medical/Care Information',
  'Service Requirement',
  'Consent & Documents',
  'Review & Onboard',
];

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
  activeStep.value = step;
};

const previousStep = () => {
  if (activeStep.value > 1) activeStep.value -= 1;
};

const nextStep = () => {
  if (activeStep.value < steps.length) activeStep.value += 1;
  else window.alert('Static demo: patient onboarding review is complete.');
};

const saveDraft = () => {
  window.alert('Draft saved locally for this static demo.');
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
    window.alert('No agreement uploaded yet. Choose a file to preview it.');
    return;
  }

  const fileUrl = URL.createObjectURL(agreementFile.value);
  window.open(fileUrl, '_blank', 'noopener');
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
  window.alert(`Request: ${request.requestId}\n${request.details}`);
};

const showRequestActions = (request) => {
  window.alert(`Static demo actions for ${request.requestId}`);
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
              <div class="stat-card stat-blue">
                <div class="stat-icon"><CIcon :icon="cilPeople" /></div>
                <div><span>Total Patients</span><strong>248</strong></div>
              </div>
              <div class="stat-card stat-green">
                <div class="stat-icon"><CIcon :icon="cilHeart" /></div>
                <div><span>Active Care</span><strong>86</strong></div>
              </div>
              <div class="stat-card stat-pink">
                <div class="stat-icon"><CIcon :icon="cilUser" /></div>
                <div><span>Caregivers Assigned</span><strong>79</strong></div>
              </div>
              <div class="stat-card stat-orange">
                <div class="stat-icon"><CIcon :icon="cilClock" /></div>
                <div><span>Assignment Pending</span><strong>7</strong></div>
              </div>
            </section>

            <!-- Onboarding heading -->
            <section class="onboarding-panel">
              <div class="section-heading">
                <div class="heading-icon"><CIcon :icon="cilUser" /></div>
                <div>
                  <h2>Patient Onboarding</h2>
                  <p>Register new patient and create care requirement</p>
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
                  @click="goToStep(index + 1)">
                  <button type="button" class="step-number">
                    <CIcon v-if="index + 1 < activeStep" :icon="cilCheck" />
                    <span v-else>{{ index + 1 }}</span>
                  </button>
                  <span class="step-label">{{ step }}</span>
                </div>
              </div>

              <!-- Patient strip -->
              <div class="patient-summary-grid">
                <div class="patient-identity">
                  <div class="patient-photo">SD</div>
                  <div>
                    <a href="#" @click.prevent>{{ patient.code }}</a>
                    <strong>{{ patient.name }}</strong>
                    <div class="patient-meta">
                      <span><CIcon :icon="cilUser" /> {{ patient.age }} yrs</span>
                      <span><CIcon :icon="cilLocationPin" /> {{ patient.location }}</span>
                    </div>
                  </div>
                </div>
                <div class="patient-status">
                  <span class="mini-label">Patient Status</span>
                  <span class="status-pill onboarding">{{ patient.status }}</span>
                </div>
                <div class="progress-card">
                  <div class="progress-title">
                    <span>Onboarding Progress</span>
                    <strong>{{ patient.progress }}%</strong>
                  </div>
                  <div class="progress-track"><div :style="{ width: `${patient.progress}%` }"></div></div>
                </div>
              </div>

              <!-- Basic Information + Contact & Address -->
              <div v-if="activeStep === 1" class="onboarding-content onboarding-page-content">
                <CCard class="content-card onboarding-form-card">
                  <CCardHeader class="card-heading"><CIcon :icon="cilUser" /><strong>Basic Information</strong></CCardHeader>
                  <CCardBody>
                    <div class="field-grid basic-info-grid">
                      <div class="field-group"><label>Patient ID (Auto-generated)</label><CFormInput v-model="basicForm.patientId" size="sm" readonly /></div>
                      <div class="field-group"><label>Patient Name <span>*</span></label><CFormInput v-model="basicForm.patientName" size="sm" placeholder="Enter patient name" /></div>
                      <div class="field-group"><label>Date of Birth <span>*</span></label><CFormInput v-model="basicForm.dateOfBirth" type="date" size="sm" /></div>
                      <div class="field-group"><label>Age</label><CFormInput v-model="basicForm.age" size="sm" placeholder="-" readonly /></div>
                      <div class="field-group"><label>Gender <span>*</span></label><CFormSelect v-model="basicForm.gender" size="sm"><option value="">Select</option><option>Male</option><option>Female</option><option>Other</option></CFormSelect></div>
                    </div>
                  </CCardBody>
                </CCard>
                <CCard class="content-card onboarding-form-card">
                  <CCardHeader class="card-heading"><CIcon :icon="cilLocationPin" /><strong>Contact & Address</strong></CCardHeader>
                  <CCardBody>
                    <div class="field-grid contact-info-grid">
                      <div class="field-group"><label>Primary Contact Name <span>*</span></label><CFormInput v-model="basicForm.primaryContactName" size="sm" placeholder="Enter contact name" /></div>
                      <div class="field-group"><label>Relationship <span>*</span></label><CFormSelect v-model="basicForm.relationship" size="sm"><option value="">Select</option><option>Spouse</option><option>Son</option><option>Daughter</option><option>Parent</option><option>Other</option></CFormSelect></div>
                      <div class="field-group"><label>Phone Number <span>*</span></label><CFormInput v-model="basicForm.phoneNumber" size="sm" placeholder="Enter phone number" /></div>
                      <div class="field-group"><label>Alternate Number</label><CFormInput v-model="basicForm.alternateNumber" size="sm" placeholder="Enter alternate number" /></div>
                      <div class="field-group address-field"><label>Address <span>*</span></label><textarea v-model="basicForm.address" rows="2" class="form-control" placeholder="Enter full address"></textarea></div>
                      <div class="field-group"><label>Location / Area <span>*</span></label><CFormInput v-model="basicForm.locationArea" size="sm" placeholder="Select location" /></div>
                      <div class="field-group"><label>City</label><CFormInput v-model="basicForm.city" size="sm" /></div>
                      <div class="field-group"><label>State</label><CFormInput v-model="basicForm.state" size="sm" /></div>
                      <div class="field-group"><label>PIN Code</label><CFormInput v-model="basicForm.pinCode" size="sm" placeholder="Enter PIN" /></div>
                      <div class="field-group"><label>Emergency Contact Name</label><CFormInput v-model="basicForm.emergencyContactName" size="sm" placeholder="Enter name" /></div>
                      <div class="field-group"><label>Relationship</label><CFormSelect v-model="basicForm.emergencyRelationship" size="sm"><option value="">Select</option><option>Spouse</option><option>Son</option><option>Daughter</option><option>Parent</option><option>Other</option></CFormSelect></div>
                      <div class="field-group emergency-phone-field"><label>Emergency Phone</label><CFormInput v-model="basicForm.emergencyPhone" size="sm" placeholder="Enter phone number" /></div>
                    </div>
                  </CCardBody>
                </CCard>
              </div>

              <!-- Medical / Care Information -->
              <div v-else-if="activeStep === 2" class="onboarding-content onboarding-page-content">
                <CCard class="content-card">
                  <CCardHeader class="card-heading"><CIcon :icon="cilMedicalCross" /><strong>Medical / Care Information</strong></CCardHeader>
                  <CCardBody>
                    <div class="field-grid medical-info-grid">
                      <div class="field-group"><label>Primary Diagnosis <span>*</span></label><CFormSelect v-model="medicalForm.primaryDiagnosis" size="sm"><option value="">Select diagnosis</option><option>Post-Operative Care</option><option>General Care</option><option>Diabetes</option><option>Hypertension</option></CFormSelect></div>
                      <div class="field-group"><label>Secondary Diagnosis</label><CFormInput v-model="medicalForm.secondaryDiagnosis" size="sm" placeholder="Enter secondary diagnosis" /></div>
                      <div class="field-group"><label>Current Health Status <span>*</span></label><CFormSelect v-model="medicalForm.healthStatus" size="sm"><option value="">Select status</option><option>Stable</option><option>Needs Monitoring</option><option>Critical</option></CFormSelect></div>
                      <div class="field-group"><label>Allergies (if any)</label><CFormInput v-model="medicalForm.allergies" size="sm" placeholder="Enter allergies or None" /></div>
                      <div class="field-group"><label>Chronic Conditions</label><CFormInput v-model="medicalForm.chronicConditions" size="sm" placeholder="Enter chronic conditions" /></div>
                      <div class="field-group"><label>Medical History (Brief)</label><textarea v-model="medicalForm.medicalHistory" rows="3" class="form-control" placeholder="Enter medical history"></textarea></div>
                      <div class="field-group"><label>Current Medications</label><textarea v-model="medicalForm.currentMedications" rows="3" class="form-control" placeholder="Enter current medications"></textarea></div>
                      <div class="field-group"><label>Special Care Needs</label><textarea v-model="medicalForm.specialCareNeeds" rows="3" class="form-control" placeholder="Enter special care needs"></textarea></div>
                      <div class="field-group"><label>Dietary Preferences</label><textarea v-model="medicalForm.dietaryPreferences" rows="2" class="form-control" placeholder="Enter dietary preferences"></textarea></div>
                    </div>
                  </CCardBody>
                </CCard>
              </div>

              <!-- Service Requirement page -->
              <div v-if="activeStep === 3" class="onboarding-content">
                <CCard class="content-card requirement-card">
                  <CCardHeader class="card-heading">
                    <CIcon :icon="cilMedicalCross" />
                    <strong>Service Requirement</strong>
                  </CCardHeader>
                  <CCardBody>
                    <div class="field-grid">
                      <div class="field-group">
                        <label>Care Type <span>*</span></label>
                        <CFormSelect v-model="careForm.careType" size="sm">
                          <option>Elderly Care</option>
                          <option>Patient Care</option>
                          <option>Post-Surgical Care</option>
                        </CFormSelect>
                      </div>
                      <div class="field-group">
                        <label>Service Category</label>
                        <CFormSelect v-model="careForm.serviceCategory" size="sm">
                          <option>Post-Operative Care</option>
                          <option>General Nursing</option>
                          <option>Daily Living Assistance</option>
                        </CFormSelect>
                      </div>
                      <div class="field-group">
                        <label>Care Start Date <span>*</span></label>
                        <div class="input-with-icon">
                          <CFormInput v-model="careForm.startDate" type="date" size="sm" />
                          <CIcon :icon="cilCalendar" />
                        </div>
                      </div>
                      <div class="field-group">
                        <label>Expected Duration <span>*</span></label>
                        <div class="duration-field">
                          <CFormInput v-model.number="careForm.duration" type="number" min="1" size="sm" />
                          <CFormSelect v-model="careForm.durationUnit" size="sm">
                            <option>Months</option>
                            <option>Weeks</option>
                            <option>Days</option>
                          </CFormSelect>
                        </div>
                      </div>
                      <div class="field-group full-field">
                        <label>Duty Hours <span>*</span></label>
                        <div class="radio-pills">
                          <label
                            v-for="hour in ['8 Hours', '12 Hours', '24 Hours', 'Custom']"
                            :key="hour"
                            :class="{ chosen: careForm.dutyHours === hour }">
                            <input v-model="careForm.dutyHours" type="radio" :value="hour" />
                            {{ hour }}
                          </label>
                        </div>
                      </div>
                      <div class="field-group">
                        <label>Service Location <span>*</span></label>
                        <CFormSelect v-model="careForm.serviceLocation" size="sm">
                          <option>Home (Patient Residence)</option>
                          <option>Hospital</option>
                          <option>Care Facility</option>
                        </CFormSelect>
                      </div>
                      <div class="field-group">
                        <label>Location / Area</label>
                        <CFormInput v-model="careForm.locationArea" size="sm" />
                      </div>
                      <div class="field-group">
                        <label>City</label>
                        <CFormInput v-model="careForm.locationCity" size="sm" />
                      </div>
                      <div class="field-group full-field">
                        <label>Preferred Caregiver Gender</label>
                        <div class="radio-pills compact">
                          <label
                            v-for="gender in ['Any', 'Male', 'Female']"
                            :key="gender"
                            :class="{ chosen: careForm.caregiverGender === gender }">
                            <input v-model="careForm.caregiverGender" type="radio" :value="gender" />
                            {{ gender }}
                          </label>
                        </div>
                      </div>
                    </div>
                  </CCardBody>
                </CCard>

                <CCard class="content-card details-card">
                  <CCardHeader class="card-heading">
                    <CIcon :icon="cilFile" />
                    <strong>Service Details & Instructions</strong>
                  </CCardHeader>
                  <CCardBody>
                    <div class="field-group">
                      <textarea v-model="careForm.serviceDetails" rows="4" class="form-control details-textarea"></textarea>
                    </div>
                    <div class="fee-box">
                      <span>Expected Caregiver Fee Range (Optional)</span>
                      <strong>{{ careForm.feeRange }}</strong>
                      <small>Based on service type and market rate.</small>
                    </div>
                    <div class="facilities-title">Additional Facilities (if any)</div>
                    <label
                      v-for="facility in ['Physiotherapy Support', 'Nursing Support', 'Dialysis Support', 'Medical Visit Coordination']"
                      :key="facility"
                      class="facility-option">
                      <input v-model="careForm.additionalFacilities" type="checkbox" :value="facility" />
                      <span>{{ facility }}</span>
                    </label>
                  </CCardBody>
                </CCard>


              </div>



              <!-- Consent & Documents: blank placeholder as requested -->
              <div v-else-if="activeStep === 4" class="onboarding-content onboarding-page-content consent-documents-page">
                <CCard class="content-card consent-empty-card">
                  <CCardHeader class="card-heading"><CIcon :icon="cilFile" /><strong>Consent & Documents</strong></CCardHeader>
                  <CCardBody><!-- Consent and document fields will be added later. --></CCardBody>
                </CCard>
              </div>

              <!-- Review & Onboard -->
              <div v-else-if="activeStep === 5" class="onboarding-content onboarding-page-content review-page-content">
                <CCard class="content-card">
                  <CCardHeader class="card-heading"><CIcon :icon="cilCheckCircle" /><strong>Review & Onboard</strong></CCardHeader>
                  <CCardBody>
                    <div class="review-section"><h3>Basic Information & Contact</h3>
                      <div class="review-grid">
                        <p><span>Patient ID</span><strong>{{ basicForm.patientId }}</strong></p>
                        <p><span>Patient Name</span><strong>{{ basicForm.patientName || '-' }}</strong></p>
                        <p><span>Date of Birth</span><strong>{{ basicForm.dateOfBirth || '-' }}</strong></p>
                        <p><span>Gender</span><strong>{{ basicForm.gender || '-' }}</strong></p>
                        <p><span>Primary Contact</span><strong>{{ basicForm.primaryContactName || '-' }}</strong></p>
                        <p><span>Phone Number</span><strong>{{ basicForm.phoneNumber || '-' }}</strong></p>
                        <p><span>Address</span><strong>{{ basicForm.address || '-' }}</strong></p>
                        <p><span>Location</span><strong>{{ [basicForm.locationArea, basicForm.city, basicForm.state, basicForm.pinCode].filter(Boolean).join(', ') || '-' }}</strong></p>
                      </div>
                    </div>
                    <div class="review-section"><h3>Medical / Care Information</h3>
                      <div class="review-grid">
                        <p><span>Primary Diagnosis</span><strong>{{ medicalForm.primaryDiagnosis || '-' }}</strong></p>
                        <p><span>Health Status</span><strong>{{ medicalForm.healthStatus || '-' }}</strong></p>
                        <p><span>Allergies</span><strong>{{ medicalForm.allergies || '-' }}</strong></p>
                        <p><span>Current Medications</span><strong>{{ medicalForm.currentMedications || '-' }}</strong></p>
                      </div>
                    </div>
                    <div class="review-section"><h3>Service Requirement</h3>
                      <div class="review-grid">
                        <p><span>Care Type</span><strong>{{ careForm.careType }}</strong></p>
                        <p><span>Service Category</span><strong>{{ careForm.serviceCategory }}</strong></p>
                        <p><span>Start Date</span><strong>{{ careForm.startDate }}</strong></p>
                        <p><span>Duration</span><strong>{{ careForm.duration }} {{ careForm.durationUnit }}</strong></p>
                        <p><span>Duty Hours</span><strong>{{ careForm.dutyHours }}</strong></p>
                        <p><span>Service Location</span><strong>{{ careForm.serviceLocation }}</strong></p>
                      </div>
                    </div>
                  </CCardBody>
                </CCard>
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
            <section class="requests-panel">
              <div class="requests-heading">
                <div class="requests-title">
                  <CIcon :icon="cilLayers" />
                  <div>
                    <h2>Additional Services & Requests</h2>
                    <p>
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
                  <label>Service Type</label>
                  <CFormSelect v-model="newRequest.serviceType" size="sm">
                    <option>Caregiver Replacement</option>
                    <option>Ambulance</option>
                    <option>Consumables</option>
                    <option>Other Services</option>
                  </CFormSelect>
                </div>
                <div class="field-group request-details-input">
                  <label>Details</label>
                  <CFormInput v-model="newRequest.details" size="sm" placeholder="Enter request details" />
                </div>
                <div class="field-group">
                  <label>Expected Date</label>
                  <CFormInput v-model="newRequest.expectedDate" type="date" size="sm" />
                </div>
                <div class="add-form-actions">
                  <CButton color="light" variant="outline" size="sm" @click="showAddRequest = false">Cancel</CButton>
                  <CButton color="primary" size="sm" @click="addServiceRequest">Add Request</CButton>
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
                <span
                  >Showing {{ filteredRequests.length ? (currentPage - 1) * rowsPerPage + 1 : 0 }} to
                  {{ Math.min(currentPage * rowsPerPage, filteredRequests.length) }} of {{ filteredRequests.length }} records</span
                >
                <div class="table-toolbar-right">
                  <div class="request-search">
                    <CIcon :icon="cilSearch" />
                    <input
                      v-model="searchText"
                      type="search"
                      placeholder="Search service requests..."
                      aria-label="Search service requests" />
                  </div>
                  <label>Rows</label>
                  <CFormSelect v-model.number="rowsPerPage" size="sm" class="rows-select" @change="currentPage = 1">
                    <option :value="5">5</option>
                    <option :value="10">10</option>
                    <option :value="25">25</option>
                  </CFormSelect>
                </div>
              </div>

              <div class="requests-table-wrap">
                <DataTable :value="visibleRequests" dataKey="id" responsiveLayout="scroll" class="requests-table" :rowHover="true">
                  <template #empty>
                    <div class="empty-state">No service requests found.</div>
                  </template>
                  <Column header="#" headerStyle="width: 42px">
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
