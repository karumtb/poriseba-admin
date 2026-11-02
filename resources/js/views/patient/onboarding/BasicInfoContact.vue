<script setup>
import { CCard, CCardHeader, CCardBody, CFormInput, CFormSelect } from '@coreui/vue'
defineProps({ form: { type: Object, required: true } })
</script>

<template>
  <div class="basic-contact-layout">
    <CCard class="content-card basic-info-card">
      <CCardHeader class="card-heading"><strong>Basic Information</strong></CCardHeader>
      <CCardBody>
        <div class="field-grid">
          <div class="field-group"><label>Patient ID (Auto-generated)</label><CFormInput v-model="form.patientId" size="sm" readonly /></div>
          <div class="field-group"><label>Patient Name <span>*</span></label><CFormInput v-model="form.patientName" size="sm" placeholder="Enter patient name" /></div>
          <div class="field-group"><label>Date of Birth <span>*</span></label><CFormInput v-model="form.dateOfBirth" type="date" size="sm" /></div>
          <div class="field-group"><label>Age</label><CFormInput v-model="form.age" type="number" size="sm" placeholder="Age" /></div>
          <div class="field-group"><label>Gender <span>*</span></label><CFormSelect v-model="form.gender" size="sm"><option value="">Select</option><option>Male</option><option>Female</option><option>Other</option></CFormSelect></div>
        </div>
      </CCardBody>
    </CCard>

    <CCard class="content-card contact-address-card">
      <CCardHeader class="card-heading"><strong>Contact &amp; Address</strong></CCardHeader>
      <CCardBody>
        <div class="field-grid">
          <div class="field-group"><label>Primary Contact Name <span>*</span></label><CFormInput v-model="form.primaryContactName" size="sm" placeholder="Enter contact name" /></div>
          <div class="field-group"><label>Relationship <span>*</span></label><CFormSelect v-model="form.relationship" size="sm"><option value="">Select</option><option>Self</option><option>Son</option><option>Daughter</option><option>Spouse</option><option>Relative</option><option>Other</option></CFormSelect></div>
          <div class="field-group"><label>Phone Number <span>*</span></label><CFormInput v-model="form.phoneNumber" size="sm" placeholder="Enter phone number" /></div>
          <div class="field-group"><label>Alternate Number</label><CFormInput v-model="form.alternateNumber" size="sm" placeholder="Enter alternate number" /></div>
          <div class="field-group full-field"><label>Address <span>*</span></label><textarea v-model="form.address" rows="2" class="form-control" placeholder="Enter full address"></textarea></div>
          <div class="field-group"><label>Location / Area <span>*</span></label><CFormInput v-model="form.locationArea" size="sm" placeholder="Enter area" /></div>
          <div class="field-group"><label>City</label><CFormInput v-model="form.city" size="sm" /></div>
          <div class="field-group"><label>State</label><CFormInput v-model="form.state" size="sm" /></div>
          <div class="field-group"><label>PIN Code</label><CFormInput v-model="form.pinCode" size="sm" placeholder="Enter PIN" /></div>
          <div class="field-group"><label>Emergency Contact Name</label><CFormInput v-model="form.emergencyContactName" size="sm" placeholder="Enter name" /></div>
          <div class="field-group"><label>Relationship</label><CFormSelect v-model="form.emergencyRelationship" size="sm"><option value="">Select</option><option>Spouse</option><option>Son</option><option>Daughter</option><option>Relative</option><option>Other</option></CFormSelect></div>
          <div class="field-group"><label>Emergency Phone</label><CFormInput v-model="form.emergencyPhone" size="sm" placeholder="Enter phone number" /></div>
        </div>
      </CCardBody>
    </CCard>
  </div>
</template>
