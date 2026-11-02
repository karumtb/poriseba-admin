<script setup>
import { CCard, CCardHeader, CCardBody, CFormInput, CFormSelect } from '@coreui/vue'
defineProps({ form: { type: Object, required: true } })
const facilities = ['Physiotherapy Support', 'Nursing Support', 'Dialysis Support', 'Medical Visit Coordination']
</script>
<template>
  <div class="onboarding-content service-requirement-layout">
    <CCard class="content-card requirement-card">
      <CCardHeader class="card-heading"><strong>Service Requirement</strong></CCardHeader>
      <CCardBody><div class="field-grid">
        <div class="field-group"><label>Care Type <span>*</span></label><CFormSelect v-model="form.careType" size="sm"><option>Elderly Care</option><option>Patient Care</option><option>Post-Surgical Care</option></CFormSelect></div>
        <div class="field-group"><label>Service Category</label><CFormSelect v-model="form.serviceCategory" size="sm"><option>Post-Operative Care</option><option>General Nursing</option><option>Daily Living Assistance</option></CFormSelect></div>
        <div class="field-group"><label>Care Start Date <span>*</span></label><CFormInput v-model="form.startDate" type="date" size="sm" /></div>
        <div class="field-group"><label>Expected Duration <span>*</span></label><div class="duration-field"><CFormInput v-model.number="form.duration" type="number" min="1" size="sm" /><CFormSelect v-model="form.durationUnit" size="sm"><option>Months</option><option>Weeks</option><option>Days</option></CFormSelect></div></div>
        <div class="field-group full-field"><label>Duty Hours <span>*</span></label><div class="radio-pills"><label v-for="hour in ['8 Hours','12 Hours','24 Hours','Custom']" :key="hour" :class="{ chosen: form.dutyHours === hour }"><input v-model="form.dutyHours" type="radio" :value="hour" /> {{ hour }}</label></div></div>
        <div class="field-group"><label>Service Location <span>*</span></label><CFormSelect v-model="form.serviceLocation" size="sm"><option>Home (Patient Residence)</option><option>Hospital</option><option>Care Facility</option></CFormSelect></div>
        <div class="field-group"><label>Location / Area</label><CFormInput v-model="form.serviceArea" size="sm" /></div>
        <div class="field-group"><label>City</label><CFormInput v-model="form.serviceCity" size="sm" /></div>
        <div class="field-group full-field"><label>Preferred Caregiver Gender</label><div class="radio-pills compact"><label v-for="gender in ['Any','Male','Female']" :key="gender" :class="{ chosen: form.caregiverGender === gender }"><input v-model="form.caregiverGender" type="radio" :value="gender" /> {{ gender }}</label></div></div>
      </div></CCardBody>
    </CCard>
    <CCard class="content-card details-card">
      <CCardHeader class="card-heading"><strong>Service Details &amp; Instructions</strong></CCardHeader>
      <CCardBody><div class="field-group"><label>Instructions</label><textarea v-model="form.serviceDetails" rows="5" class="form-control details-textarea" placeholder="Enter service details and instructions"></textarea></div>
        <div class="fee-box"><span>Expected Caregiver Fee Range (Optional)</span><strong>{{ form.feeRange }}</strong><small>Based on service type and market rate.</small></div>
        <div class="facilities-title">Additional Facilities (if any)</div>
        <label v-for="facility in facilities" :key="facility" class="facility-option"><input v-model="form.additionalFacilities" type="checkbox" :value="facility" /><span>{{ facility }}</span></label>
      </CCardBody>
    </CCard>
  </div>
</template>
