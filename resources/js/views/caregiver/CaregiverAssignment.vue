<script setup>
import { onMounted } from 'vue';
import { CRow, CCol, CCard, CCardHeader, CCardBody, CButton, CFormInput, CFormSelect } from '@coreui/vue';
import CIcon from '@coreui/icons-vue';
import {
  cilPeople,
  cilHeart,
  cilClock,
  cilCalendar,
  cilLocationPin,
  cilUser,
  cilMedicalCross,
  cilFile,
  cilArrowRight,
  cilInfo,
  cilCheck,
} from '@coreui/icons';
import { useBreadcrumb } from '@/services/useBreadcrumb';

const { breadcrumbs } = useBreadcrumb();

onMounted(() => {
  breadcrumbs.value = ['Patient', 'Caregiver Assignment'];
});
</script>

<template>
  <CRow>
    <CCol :lg="12" :md="12" :sm="12">
      <div class="patient-management-page">
        <div class="dashboard-shell">
          <!-- Main Content -->
          <main class="dashboard-main">
            <!-- Page Header -->
            <header class="topbar">
              <div class="page-heading">
                <div>
                  <h1>Caregiver Assignment</h1>
                  <p>Manage caregiver assignments and patient care services</p>
                </div>
              </div>
            </header>

            <!-- Summary Cards -->
            <section class="stats-grid">
              <div class="stat-card stat-blue">
                <div class="stat-icon">
                  <CIcon :icon="cilPeople" />
                </div>
                <div>
                  <span>Total Assignments</span>
                  <strong>124</strong>
                </div>
              </div>

              <div class="stat-card stat-green">
                <div class="stat-icon">
                  <CIcon :icon="cilHeart" />
                </div>
                <div>
                  <span>Active Assignments</span>
                  <strong>86</strong>
                </div>
              </div>

              <div class="stat-card stat-orange">
                <div class="stat-icon">
                  <CIcon :icon="cilClock" />
                </div>
                <div>
                  <span>Pending Assignments</span>
                  <strong>7</strong>
                </div>
              </div>

              <div class="stat-card stat-pink">
                <div class="stat-icon">
                  <CIcon :icon="cilCalendar" />
                </div>
                <div>
                  <span>Reassignment</span>
                  <strong>5</strong>
                </div>
              </div>
            </section>

            <!-- Caregiver Assignment Panel -->
            <section class="onboarding-panel">
              <!-- Section Heading -->
              <div class="section-heading">
                <div class="heading-icon">
                  <CIcon :icon="cilUser" />
                </div>
                <div>
                  <h2>New Caregiver Assignment</h2>
                  <p>Assign caregiver(s) to a patient for care service</p>
                </div>
              </div>

              <!-- Stepper -->
              <div class="stepper">
                <div class="stepper-item current">
                  <button type="button" class="step-number">
                    <span>1</span>
                  </button>
                  <span class="step-label">Select Patient</span>
                </div>

                <div class="stepper-item">
                  <button type="button" class="step-number">
                    <span>2</span>
                  </button>
                  <span class="step-label">Select & Shortlist Caregiver</span>
                </div>

                <div class="stepper-item">
                  <button type="button" class="step-number">
                    <span>3</span>
                  </button>
                  <span class="step-label">Interview & Verify</span>
                </div>

                <div class="stepper-item">
                  <button type="button" class="step-number">
                    <span>4</span>
                  </button>
                  <span class="step-label">Assign & Confirm</span>
                </div>

                <div class="stepper-item">
                  <button type="button" class="step-number">
                    <span>5</span>
                  </button>
                  <span class="step-label">Review & Notify</span>
                </div>
              </div>

              <!-- Assignment Information -->
              <div class="onboarding-content assignment-content">
                <CCard class="content-card assignment-form-card">
                  <CCardBody>
                    <!-- Patient & Care Information -->
                    <div class="field-grid assignment-basic-grid">
                      <div class="field-group">
                        <label>Patient <span>*</span></label>
                        <CFormSelect size="sm">
                          <option>Select patient (Search by name or ID)</option>
                          <option>Mr. Subhash Dutta</option>
                          <option>Mr. Rajesh Kumar</option>
                          <option>Mrs. Anita Sharma</option>
                        </CFormSelect>
                      </div>

                      <div class="field-group">
                        <label>Care Requirement</label>
                        <CFormInput size="sm" value="Elderly Care" readonly />
                      </div>

                      <div class="field-group">
                        <label>Care Plan (if any)</label>
                        <CFormSelect size="sm">
                          <option>Select care plan</option>
                          <option>Basic Care Plan</option>
                          <option>Premium Care Plan</option>
                        </CFormSelect>
                      </div>

                      <div class="field-group">
                        <label>Caregiver Type <span>*</span></label>
                        <CFormSelect size="sm">
                          <option>All Types</option>
                          <option>General Caregiver</option>
                          <option>Nurse</option>
                          <option>Patient Attendant</option>
                        </CFormSelect>
                      </div>

                      <div class="field-group">
                        <label>No. of Caregivers</label>
                        <CFormSelect size="sm">
                          <option>1</option>
                          <option>2</option>
                          <option>3</option>
                        </CFormSelect>
                      </div>

                      <div class="field-group">
                        <label>Duty Hours <span>*</span></label>
                        <CFormSelect size="sm">
                          <option>Select duty hours</option>
                          <option>8 Hours</option>
                          <option>12 Hours</option>
                          <option>24 Hours</option>
                        </CFormSelect>
                      </div>

                      <div class="field-group">
                        <label>Duty Start Date <span>*</span></label>
                        <div class="input-with-icon">
                          <CFormInput type="date" size="sm" />
                          <CIcon :icon="cilCalendar" />
                        </div>
                      </div>

                      <div class="field-group">
                        <label>Expected End Date</label>
                        <div class="input-with-icon">
                          <CFormInput type="date" size="sm" />
                          <CIcon :icon="cilCalendar" />
                        </div>
                      </div>
                    </div>
                  </CCardBody>
                </CCard>

                <!-- Fee Configuration -->
                <CCard class="content-card fee-configuration-card">
                  <CCardHeader class="card-heading fee-card-heading">
                    <CIcon :icon="cilFile" />
                    <strong>Caregiver Fee Configuration</strong>
                  </CCardHeader>

                  <CCardBody>
                    <div class="fee-grid">
                      <div class="field-group">
                        <label>Fee Type <span>*</span></label>
                        <CFormSelect size="sm">
                          <option>Hourly</option>
                          <option>Daily</option>
                          <option>Weekly</option>
                          <option>Monthly</option>
                        </CFormSelect>
                      </div>

                      <div class="field-group">
                        <label>Fee Amount (₹) <span>*</span></label>
                        <div class="fee-input-group">
                          <CFormInput size="sm" value="250" />
                          <span>per hour</span>
                        </div>
                      </div>

                      <div class="field-group">
                        <label>Estimated Duration</label>
                        <div class="duration-field">
                          <CFormInput size="sm" value="1" />
                          <CFormSelect size="sm">
                            <option>Month</option>
                            <option>Week</option>
                            <option>Day</option>
                          </CFormSelect>
                        </div>
                      </div>

                      <div class="field-group">
                        <label>Estimated Amount (₹)</label>
                        <div class="estimated-amount">₹ 45,000</div>
                      </div>

                      <div class="fee-calculation">
                        <div class="calculation-info">
                          <CIcon :icon="cilInfo" />
                          Calculated as: 250 × 8 hrs × 30 days
                        </div>

                        <label class="checkbox-field">
                          <input type="checkbox" checked />
                          <span>Include in contract/assignment terms</span>
                        </label>
                      </div>
                    </div>
                  </CCardBody>
                </CCard>

                <!-- Location & Preferences -->
                <CCard class="content-card location-preference-card">
                  <CCardBody>
                    <div class="field-grid location-preference-grid">
                      <div class="field-group">
                        <label>Location / Area</label>
                        <CFormInput size="sm" value="Salt Lake" />
                      </div>

                      <div class="field-group">
                        <label>Preferred Caregiver</label>

                        <div class="radio-options">
                          <label class="radio-field">
                            <input type="radio" name="caregiverGender" checked />
                            <span>Any</span>
                          </label>

                          <label class="radio-field">
                            <input type="radio" name="caregiverGender" />
                            <span>Male</span>
                          </label>

                          <label class="radio-field">
                            <input type="radio" name="caregiverGender" />
                            <span>Female</span>
                          </label>
                        </div>
                      </div>

                      <div class="field-group">
                        <label>Special Instructions (optional)</label>
                        <textarea
                          class="form-control"
                          rows="2"
                          placeholder="e.g., patient condition, language preference, dietary instruction etc."></textarea>
                      </div>
                    </div>
                  </CCardBody>
                </CCard>
              </div>

              <!-- Form Footer -->
              <div class="form-footer">
                <CButton color="light" variant="outline" class="back-button"> Cancel </CButton>

                <div class="footer-actions">
                  <CButton color="light" variant="outline" class="draft-button">
                    <CIcon :icon="cilFile" />
                    Save as Draft
                  </CButton>

                  <CButton color="primary" class="continue-button">
                    Continue
                    <CIcon :icon="cilArrowRight" />
                  </CButton>
                </div>
              </div>
            </section>
          </main>
        </div>
      </div>
    </CCol>
  </CRow>
</template>

<style scoped></style>
