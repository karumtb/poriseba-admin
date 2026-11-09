export const PATIENT_DROPDOWNS = {
  // Basic Information
  gender: [
    { label: 'Male', value: 'Male' },
    { label: 'Female', value: 'Female' },
    { label: 'Other', value: 'Other' },
  ],

  relationship: [
    { label: 'Spouse', value: 'Spouse' },
    { label: 'Son', value: 'Son' },
    { label: 'Daughter', value: 'Daughter' },
    { label: 'Parent', value: 'Parent' },
    { label: 'Other', value: 'Other' },
  ],

  // Medical Information
  primaryDiagnosis: [
    { label: 'Post-Operative Care', value: 'Post-Operative Care' },
    { label: 'General Care', value: 'General Care' },
    { label: 'Diabetes', value: 'Diabetes' },
    { label: 'Hypertension', value: 'Hypertension' },
  ],

  healthStatus: [
    { label: 'Stable', value: 'Stable' },
    { label: 'Needs Monitoring', value: 'Needs Monitoring' },
    { label: 'Critical', value: 'Critical' },
  ],

  // Service Requirement
  careType: [
    { label: 'Elderly Care', value: 'Elderly Care' },
    { label: 'Patient Care', value: 'Patient Care' },
    { label: 'Post-Surgical Care', value: 'Post-Surgical Care' },
  ],

  durationUnit: [
    { label: 'Months', value: 'Months' },
    { label: 'Weeks', value: 'Weeks' },
    { label: 'Days', value: 'Days' },
  ],

  dutyHours: [
    { label: '8 Hours', value: '8 Hours' },
    { label: '12 Hours', value: '12 Hours' },
    { label: '24 Hours', value: '24 Hours' },
    { label: 'Custom', value: 'Custom' },
  ],

  serviceCategory: [
    { label: 'Post-Operative Care', value: 'Post-Operative Care' },
    { label: 'General Nursing', value: 'General Nursing' },
    { label: 'Daily Living Assistance', value: 'Daily Living Assistance' },
  ],

  serviceLocation: [
    { label: 'Home (Patient Residence)', value: 'Home (Patient Residence)' },
    { label: 'Hospital', value: 'Hospital' },
    { label: 'Care Facility', value: 'Care Facility' },
  ],

  caregiverGender: [
    { label: 'Any', value: 'Any' },
    { label: 'Male', value: 'Male' },
    { label: 'Female', value: 'Female' },
  ],

  // Additional Facilities
  additionalFacilities: [
    { label: 'Physiotherapy Support', value: 'Physiotherapy Support' },
    { label: 'Nursing Support', value: 'Nursing Support' },
    { label: 'Dialysis Support', value: 'Dialysis Support' },
    { label: 'Medical Visit Coordination', value: 'Medical Visit Coordination' },
  ],

  // Additional Service Requests
  serviceRequestType: [
    { label: 'Caregiver Replacement', value: 'Caregiver Replacement' },
    { label: 'Ambulance', value: 'Ambulance' },
    { label: 'Consumables', value: 'Consumables' },
    { label: 'Other Services', value: 'Other Services' },
  ],

  // Table pagination
  rowsPerPage: [
    { label: '5', value: 5 },
    { label: '10', value: 10 },
    { label: '25', value: 25 },
  ],
};
