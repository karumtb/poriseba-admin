<template>
  <CRow>
    <!-- ====================================================== -->
    <!-- LISTING -->
    <!-- ====================================================== -->

    <CCol :lg="12" :md="12" :sm="12">
      <CCard class="mb-4">
        <CCardHeader>
          <div class="d-flex justify-content-between align-items-center">
            <strong> Enrolment Lists </strong>

            <CButton color="primary" @click="addNew" v-if="authStore.user?.role != 'hospital'">
              <CIcon :icon="cilPlus" class="me-1" />

              Add New
            </CButton>
          </div>
        </CCardHeader>

        <CCardBody>
          <CCard class="filter-card mb-4">
            <CCardBody class="py-4">
              <div class="row g-3 align-items-end">
                <!-- Acknowledgement -->
                <div class="col-lg-3 col-md-6">
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
          <div class="card">
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
                    id="formSwitchCheckChecked{{ data.id }}"
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

    <!-- ====================================================== -->
    <!-- FORM -->
    <!-- ====================================================== -->

    <CCol :lg="12" :md="12" :sm="12" ref="formSection" v-if="authStore.user.role != 'hospital'">
      <CCard class="mb-4">
        <CCardHeader>
          <div class="d-flex justify-content-between align-items-center">
            <strong>
              {{ editMode ? 'Edit Enrolment' : 'Add Enrolment' }}
            </strong>

            <CButton v-if="editMode" color="secondary" variant="outline" size="sm" @click="addNew">
              <CIcon :icon="cilX" class="me-1" />

              New
            </CButton>
          </div>
        </CCardHeader>

        <CCardBody>
          <CForm class="enrolment-wizard-form needs-validation layout-fixed-form" novalidate :validated="validated" @submit.prevent="saveEnrolment">
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
                  :aria-disabled="!editMode && index + 1 > activeStep"
                  :style="{ cursor: editMode || index + 1 <= activeStep ? 'pointer' : 'not-allowed' }">
                  <button type="button" class="step-number">
                    <CIcon v-if="index + 1 < activeStep" :icon="cilCheckCircle" />
                    <span v-else>{{ index + 1 }}</span>
                  </button>
                  <span class="step-label">{{ step }}</span>
                </div>
              </div>

              <div class="enrolment-step-panel">
                <!-- STEP 1: BASIC INFORMATION -->
                <section v-if="activeStep === 1" class="enrolment-step-section">
                  <h5 class="enrolment-section-heading">Basic Information</h5>
                  <p class="enrolment-section-description">Enter caregiver personal details and contact information.</p>
                  <CRow>
                  <CCol md="6" class="mb-3">
                    <CFormLabel>Caregiver Code</CFormLabel>
                    <input v-model.trim="form.caregiver_code" class="form-control" readonly placeholder="Generated by system" />
                  </CCol>
                  <CCol md="6" class="mb-3">
                    <!-- First Name -->
                    <CFormLabel>First Name</CFormLabel>
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
                  <CCol md="6" class="mb-3">
                    <!-- Last Name -->
                    <CFormLabel>Last Name</CFormLabel>
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
                  <CCol md="6" class="mb-3">
                    <!-- Last Name -->
                    <CFormLabel>Gender</CFormLabel>
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
                  <CCol md="6" class="mb-3">
                    <!-- Email -->
                    <CFormLabel>Email</CFormLabel>
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
                  <CCol md="6" class="mb-3">
                    <!-- Phone -->
                    <CFormLabel>Phone No</CFormLabel>
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
  <CFormLabel>Middle Name</CFormLabel>
  <input v-model.trim="form.middle_name" class="form-control" />
</CCol>
<CCol md="6" class="mb-3">
  <CFormLabel>Date of Birth</CFormLabel>
  <CDatePicker v-model:date="form.date_of_birth" format="dd/MM/yyyy" />
</CCol>
<CCol md="6" class="mb-3">
  <CFormLabel>Alternate Phone</CFormLabel>
  <input v-model.trim="form.alternate_phone" class="form-control" maxlength="10" />
</CCol>
<CCol md="6" class="mb-3">
  <CFormLabel>Emergency Contact Name</CFormLabel>
  <input v-model.trim="form.emergency_contact_name" class="form-control" />
</CCol>
<CCol md="6" class="mb-3">
  <CFormLabel>Emergency Contact Number</CFormLabel>
  <input v-model.trim="form.emergency_contact_number" class="form-control" maxlength="10" />
</CCol>
<CCol md="6" class="mb-3">
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
                    <CFormLabel>Address</CFormLabel>
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
                    <CFormLabel>City</CFormLabel>
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
                    <CFormLabel>State</CFormLabel>
                    <Multiselect
                      v-model="form.state"
                      :options="indianStates"
                      :searchable="true"
                      :close-on-select="true"
                      :show-labels="false"
                      placeholder="Choose State"
                      :allow-empty="true"
                      @clear="form.state = null"
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
                    <CFormLabel>Pin</CFormLabel>
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
                    <CFormLabel>Vendor</CFormLabel>
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
                      {{ errors.vendor_id.message }}
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
                    <CFormLabel>Highest Qualification</CFormLabel>
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
                    <CFormLabel>Designation</CFormLabel>
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
                      @input="validateField('designation')" />
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
  <input v-model="form.preferred_locations" class="form-control" placeholder="Enter preferred locations separated by commas" />
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
                          <h6>Profile Photo</h6>
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
                          <h6>Aadhaar Card</h6>
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
                          <h6>Certificate</h6>
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
                  <div class="enrolment-review-grid">
                    <div class="enrolment-review-card">
                      <h6>Personal Information</h6>
                      <div><span>Name</span><strong>{{ [form.first_name, form.middle_name, form.last_name].filter(Boolean).join(' ') || '-' }}</strong></div>
                      <div><span>Gender</span><strong>{{ form.gender || '-' }}</strong></div>
                      <div><span>Date of Birth</span><strong>{{ form.date_of_birth || '-' }}</strong></div>
                      <div><span>Email</span><strong>{{ form.email || '-' }}</strong></div>
                      <div><span>Phone</span><strong>{{ form.phone || '-' }}</strong></div>
                      <div><span>Alternate Phone</span><strong>{{ form.alternate_phone || '-' }}</strong></div>
                    </div>
                    <div class="enrolment-review-card">
                      <h6>Address</h6>
                      <div><span>Address</span><strong>{{ form.address || '-' }}</strong></div>
                      <div><span>City</span><strong>{{ form.city || '-' }}</strong></div>
                      <div><span>District</span><strong>{{ form.district || '-' }}</strong></div>
                      <div><span>State</span><strong>{{ form.state || '-' }}</strong></div>
                      <div><span>PIN Code</span><strong>{{ form.pin_code || '-' }}</strong></div>
                    </div>
                    <div class="enrolment-review-card">
                      <h6>Professional Information</h6>
                      <div><span>Qualification</span><strong>{{ form.highest_qualification || '-' }}</strong></div>
                      <div><span>Designation</span><strong>{{ designations.find(item => item.id === form.designation)?.name || '-' }}</strong></div>
                      <div><span>Experience</span><strong>{{ form.experience || '-' }}</strong></div>
                      <div><span>Specialization</span><strong>{{ form.specialization || '-' }}</strong></div>
                      <div><span>Profile Type</span><strong>{{ form.profile_type || '-' }}</strong></div>
                    </div>
                    <div class="enrolment-review-card">
                      <h6>Documents</h6>
                      <div><span>Profile Photo</span><strong>{{ profilePreviewUrl ? 'Uploaded' : 'Not uploaded' }}</strong></div>
                      <div><span>Aadhaar PDF</span><strong>{{ aadhaarPreviewUrl ? 'Uploaded' : 'Not uploaded' }}</strong></div>
                      <div><span>Certificate PDF</span><strong>{{ certificatePreviewUrl ? 'Uploaded' : 'Not uploaded' }}</strong></div>
                    </div>
                    <div class="enrolment-review-card">
                      <h6>Placement</h6>
                      <div><span>Location</span><strong>{{ form.placement_location || '-' }}</strong></div>
                      <div><span>Start Date</span><strong>{{ form.placement_start || '-' }}</strong></div>
                      <div><span>End Date</span><strong>{{ form.placement_end || '-' }}</strong></div>
                      <div><span>Status</span><strong>{{ form.placement_status || '-' }}</strong></div>
                    </div>
                  </div>
                </section>
              </div>
            </div>

            <div class="form-sticky-actions enrolment-wizard-actions">
              <div class="d-flex gap-2 justify-content-between align-items-center w-100">
                <div>
                  <CButton type="button" color="secondary" variant="outline" :disabled="activeStep === 1 || btnLoading" @click="previousStep">
                    Previous
                  </CButton>
                </div>
                <div class="d-flex gap-2">
                  <CButton type="button" color="light" variant="outline" :disabled="btnLoading" @click="resetForm">
                    <CIcon :icon="cilX" class="me-1" /> Clear
                  </CButton>
                  <CButton v-if="activeStep < steps.length" type="button" color="primary" @click="nextStep">
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
  </CRow>

  <CModal
    :visible="visibleLiveDemo"
    aria-labelledby="LiveDemoExampleLabel"
    size="lg"
    alignment="center"
    @close="
      () => {
        visibleLiveDemo = false;
      }
    ">
    <CModalHeader>
      <CModalTitle id="LiveDemoExampleLabel">View Enrolment</CModalTitle>
    </CModalHeader>
    <CModalBody> <ViewEnrolment v-if="viewData" :data="viewData" /> </CModalBody>
    <CModalFooter>
      <CButton
        color="secondary"
        @click="
          () => {
            visibleLiveDemo = false;
          }
        ">
        Close
      </CButton>
    </CModalFooter>
  </CModal>
</template>