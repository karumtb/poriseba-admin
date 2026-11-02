<script setup>
import { CCard, CCardHeader, CCardBody, CFormInput, CFormSelect } from '@coreui/vue'
defineProps({ form: { type: Object, required: true } })
const conditions = ['Diabetes (Type 2)', 'Hypertension', 'Asthma', 'Heart Disease', 'Arthritis', 'Other']
</script>
<template>
  <CCard class="content-card medical-care-card">
    <CCardHeader class="card-heading"><strong>Medical / Care Information</strong></CCardHeader>
    <CCardBody>
      <div class="field-grid medical-field-grid">
        <div class="field-group"><label>Primary Diagnosis <span>*</span></label><CFormSelect v-model="form.primaryDiagnosis" size="sm"><option value="">Select diagnosis</option><option>Post-Operative Care</option><option>General Nursing</option><option>Mobility Assistance</option><option>Chronic Care</option><option>Other</option></CFormSelect></div>
        <div class="field-group"><label>Medical History (Brief)</label><textarea v-model="form.medicalHistory" rows="3" class="form-control" placeholder="Enter medical history"></textarea></div>
        <div class="field-group"><label>Secondary Diagnosis</label><CFormInput v-model="form.secondaryDiagnosis" size="sm" placeholder="Enter secondary diagnosis" /></div>
        <div class="field-group"><label>Current Medications</label><textarea v-model="form.currentMedications" rows="2" class="form-control" placeholder="Enter current medications"></textarea></div>
        <div class="field-group"><label>Current Health Status <span>*</span></label><CFormSelect v-model="form.healthStatus" size="sm"><option>Stable</option><option>Under Observation</option><option>Critical</option><option>Recovering</option></CFormSelect></div>
        <div class="field-group"><label>Special Care Needs</label><textarea v-model="form.specialCareNeeds" rows="2" class="form-control" placeholder="Enter special care needs"></textarea></div>
        <div class="field-group"><label>Allergies (if any)</label><CFormInput v-model="form.allergies" size="sm" placeholder="Enter allergies or None" /></div>
        <div class="field-group"><label>Dietary Preferences</label><textarea v-model="form.dietaryPreferences" rows="2" class="form-control" placeholder="Enter dietary preferences"></textarea></div>
        <div class="field-group full-field"><label>Chronic Conditions</label>
          <div class="checkbox-pills"><label v-for="condition in conditions" :key="condition" class="checkbox-pill"><input v-model="form.chronicConditions" type="checkbox" :value="condition" /> {{ condition }}</label></div>
        </div>
      </div>
    </CCardBody>
  </CCard>
</template>
