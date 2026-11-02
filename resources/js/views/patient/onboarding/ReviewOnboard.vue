<script setup>
import { CCard, CCardHeader, CCardBody } from '@coreui/vue'
defineProps({ form: { type: Object, required: true } })
</script>
<template>
  <div class="review-onboard-grid">
    <CCard class="content-card"><CCardHeader class="card-heading"><strong>Basic Information &amp; Contact</strong></CCardHeader><CCardBody>
      <dl class="review-list"><dt>Patient ID</dt><dd>{{ form.patientId || '—' }}</dd><dt>Patient Name</dt><dd>{{ form.patientName || '—' }}</dd><dt>Date of Birth</dt><dd>{{ form.dateOfBirth || '—' }}</dd><dt>Age / Gender</dt><dd>{{ form.age || '—' }} / {{ form.gender || '—' }}</dd><dt>Primary Contact</dt><dd>{{ form.primaryContactName || '—' }} ({{ form.relationship || '—' }})</dd><dt>Phone</dt><dd>{{ form.phoneNumber || '—' }}</dd><dt>Address</dt><dd>{{ [form.address, form.locationArea, form.city, form.state, form.pinCode].filter(Boolean).join(', ') || '—' }}</dd><dt>Emergency Contact</dt><dd>{{ form.emergencyContactName || '—' }} / {{ form.emergencyPhone || '—' }}</dd></dl>
    </CCardBody></CCard>
    <CCard class="content-card"><CCardHeader class="card-heading"><strong>Medical / Care Information</strong></CCardHeader><CCardBody>
      <dl class="review-list"><dt>Primary Diagnosis</dt><dd>{{ form.primaryDiagnosis || '—' }}</dd><dt>Secondary Diagnosis</dt><dd>{{ form.secondaryDiagnosis || '—' }}</dd><dt>Health Status</dt><dd>{{ form.healthStatus || '—' }}</dd><dt>Allergies</dt><dd>{{ form.allergies || '—' }}</dd><dt>Chronic Conditions</dt><dd>{{ form.chronicConditions.join(', ') || '—' }}</dd><dt>Medical History</dt><dd>{{ form.medicalHistory || '—' }}</dd><dt>Current Medications</dt><dd>{{ form.currentMedications || '—' }}</dd><dt>Special Care Needs</dt><dd>{{ form.specialCareNeeds || '—' }}</dd><dt>Dietary Preferences</dt><dd>{{ form.dietaryPreferences || '—' }}</dd></dl>
    </CCardBody></CCard>
    <CCard class="content-card"><CCardHeader class="card-heading"><strong>Service Requirement</strong></CCardHeader><CCardBody>
      <dl class="review-list"><dt>Care Type</dt><dd>{{ form.careType }}</dd><dt>Service Category</dt><dd>{{ form.serviceCategory }}</dd><dt>Start Date</dt><dd>{{ form.startDate || '—' }}</dd><dt>Duration</dt><dd>{{ form.duration }} {{ form.durationUnit }}</dd><dt>Duty Hours</dt><dd>{{ form.dutyHours }}</dd><dt>Service Location</dt><dd>{{ form.serviceLocation }}</dd><dt>Area / City</dt><dd>{{ [form.serviceArea, form.serviceCity].filter(Boolean).join(', ') || '—' }}</dd><dt>Preferred Gender</dt><dd>{{ form.caregiverGender }}</dd><dt>Expected Fee</dt><dd>{{ form.feeRange }}</dd><dt>Additional Facilities</dt><dd>{{ form.additionalFacilities.join(', ') || '—' }}</dd><dt>Instructions</dt><dd>{{ form.serviceDetails || '—' }}</dd></dl>
    </CCardBody></CCard>
    <div class="approval-panel"><strong>Approval &amp; Activation</strong><p>Review the information above, then click Finish to complete this demo onboarding flow.</p></div>
  </div>
</template>
