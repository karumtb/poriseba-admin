<script setup>
import { allowDecimal, getFileName, openFile } from '@/utils/input';
import { useAuthStore } from '@/stores/auth';
const authStore = useAuthStore();

defineProps({
    data: {
        type: Object,
        default: () => ({}),
    },
})
/*
|--------------------------------------------------------------------------
| Date Transformation
|--------------------------------------------------------------------------
*/
const formatDate = (date) => {
    if (!date) {
        return '-';
    }

    const formattedDate = new Date(date.replace(' ', 'T'));

    return formattedDate.toLocaleString('en-IN', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        hour12: true,
    });
};
const formatnormalDate = (date) => {
    if (!date) {
        return '-';
    }

    const formattedDate = new Date(date.replace(' ', 'T'));

    const day = String(formattedDate.getDate()).padStart(2, '0');
    const month = String(formattedDate.getMonth() + 1).padStart(2, '0');
    const year = formattedDate.getFullYear();

    return `${day}/${month}/${year}`;
};
</script>
<template>
    <!-- ================= BASIC INFORMATION ================= -->

    <div class="view-section">
        <div class="view-section-title">
            <span>Personal Information</span>
        </div>

        <CRow>
            <!-- Vendor -->
            <CCol md="6" class="mb-3"  v-if="authStore.user.role != 'hospital'">
                <div class="view-field">
                    <div class="view-label">Vendor</div>
                    <div class="view-value">
                        {{ data?.vendor?.name }}
                    </div>
                </div>
            </CCol>

            <!-- First Name -->
            <CCol md="6" class="mb-3">
                <div class="view-field">
                    <div class="view-label">First Name</div>
                    <div class="view-value">
                        {{ data.first_name || '-' }}
                    </div>
                </div>
            </CCol>

            <!-- Last Name -->
            <CCol md="6" class="mb-3">
                <div class="view-field">
                    <div class="view-label">Last Name</div>
                    <div class="view-value">
                        {{ data.last_name || '-' }}
                    </div>
                </div>
            </CCol>

            <!-- Email -->
            <CCol md="6" class="mb-3" v-if="authStore.user.role != 'hospital'">
                <div class="view-field">
                    <div class="view-label">Email</div>
                    <div class="view-value">
                        {{ data.email || '-' }}
                    </div>
                </div>
            </CCol>

            <!-- Phone -->
            <CCol md="6" class="mb-3" v-if="authStore.user.role != 'hospital'">
                <div class="view-field">
                    <div class="view-label">Phone No</div>
                    <div class="view-value">
                        {{ data.phone || '-' }}
                    </div>
                </div>
            </CCol>

            <!-- Address -->
            <CCol md="6" class="mb-3">
                <div class="view-field">
                    <div class="view-label">Address</div>
                    <div class="view-value view-value-multiline">
                        {{ data.address || '-' }}
                    </div>
                </div>
            </CCol>

            <!-- City -->
            <CCol md="6" class="mb-3">
                <div class="view-field">
                    <div class="view-label">City</div>
                    <div class="view-value">
                        {{ data.city || '-' }}
                    </div>
                </div>
            </CCol>

            <!-- State -->
            <CCol md="6" class="mb-3">
                <div class="view-field">
                    <div class="view-label">State</div>
                    <div class="view-value">
                        {{ data.state || '-' }}
                    </div>
                </div>
            </CCol>

            <!-- Pin -->
            <CCol md="6" class="mb-3">
                <div class="view-field">
                    <div class="view-label">Pin</div>
                    <div class="view-value">
                        {{ data.pin_code || '-' }}
                    </div>
                </div>
            </CCol>
        </CRow>
    </div>


    <!-- ================= PROFESSIONAL INFORMATION ================= -->

    <div class="view-section">

        <div class="view-section-title">
            <span>Professional Information</span>
        </div>

        <CRow>

            <!-- Highest Qualification -->
            <CCol md="6" class="mb-3">
                <div class="view-field">
                    <div class="view-label">
                        Highest Qualification
                    </div>

                    <div class="view-value">
                        {{ data.highest_qualification || '-' }}
                    </div>
                </div>
            </CCol>


            <!-- Designation -->
            <CCol md="6" class="mb-3">
                <div class="view-field">
                    <div class="view-label">
                        Designation
                    </div>

                    <div class="view-value">
                        {{ data.designation?.name }}
                    </div>
                </div>
            </CCol>


            <!-- Experience -->
            <CCol md="6" class="mb-3">
                <div class="view-field">
                    <div class="view-label">
                        Experience
                    </div>

                    <div class="view-value">
                        {{ data.experience || '-' }}
                    </div>
                </div>
            </CCol>


            <!-- Specialization -->
            <CCol md="6" class="mb-3">
                <div class="view-field">
                    <div class="view-label">
                        Specialization
                    </div>

                    <div class="view-value view-value-multiline">
                        {{ data.specialization || '-' }}
                    </div>
                </div>
            </CCol>


            <!-- Location -->
            <CCol md="6" class="mb-3">
                <div class="view-field">
                    <div class="view-label">
                        Placement Location
                    </div>

                    <div class="view-value">
                        {{ data.placement_location || '-' }}
                    </div>
                </div>
            </CCol>


            <!-- Placement Start -->
            <CCol md="6" class="mb-3">
                <div class="view-field">
                    <div class="view-label">
                        Placement Start Date
                    </div>

                    <div class="view-value">
                        {{ formatDate(data.placement_start) }}
                    </div>
                </div>
            </CCol>


            <!-- Placement End -->
            <CCol md="6" class="mb-3">
                <div class="view-field">
                    <div class="view-label">
                        Placement End Date
                    </div>

                    <div class="view-value">
                        {{ formatDate(data.placement_end) }}
                    </div>
                </div>
            </CCol>


            <!-- Rating -->
            <CCol md="6" class="mb-3">
                <div class="view-field">

                    <div class="view-label">
                        Rating
                    </div>

                    <div class="view-value rating-value">
                        <CRating :value="Number(data.rating || 0)" readonly />

                        <span class="rating-number">
                            {{ data.rating || 0 }}/5
                        </span>
                    </div>

                </div>
            </CCol>

        </CRow>

    </div>


    <!-- ================= REVIEWS ================= -->

    <div class="view-section" v-if="authStore.user.role != 'hospital'">

        <div class="view-section-title">
            <span>Reviews & Remarks</span>
        </div>

        <CRow>

            <!-- Remarks -->
            <CCol md="6" class="mb-3">
                <div class="view-field">

                    <div class="view-label">
                        Remarks
                    </div>

                    <div v-if="data.remark" class="view-rich-text" v-html="data.remark"></div>

                    <div v-else class="view-value empty-value">
                        -
                    </div>

                </div>
            </CCol>


            <!-- Facebook -->
            <CCol md="6" class="mb-3">
                <div class="view-field">

                    <div class="view-label">
                        FB Reviews
                    </div>

                    <div v-if="data.fb_reviews" class="view-rich-text" v-html="data.fb_reviews"></div>

                    <div v-else class="view-value empty-value">
                        -
                    </div>

                </div>
            </CCol>


            <!-- Instagram -->
            <CCol md="6" class="mb-3">
                <div class="view-field">

                    <div class="view-label">
                        Instagram Reviews
                    </div>

                    <div v-if="data.insta_reviews" class="view-rich-text" v-html="data.insta_reviews"></div>

                    <div v-else class="view-value empty-value">
                        -
                    </div>

                </div>
            </CCol>


            <!-- Google -->
            <CCol md="6" class="mb-3">
                <div class="view-field">

                    <div class="view-label">
                        Google Reviews
                    </div>

                    <div v-if="data.google_reviews" class="view-rich-text" v-html="data.google_reviews"></div>

                    <div v-else class="view-value empty-value">
                        -
                    </div>

                </div>
            </CCol>

        </CRow>

    </div>
    <!-- ================= PAST EXPERIENCE ================= -->

<div class="view-section" v-if="data?.past_experience?.length">

    <div class="view-section-title">
        <span>Past Experience</span>
    </div>

    <div class="table-responsive">
        <table class="table past-experience-table">
            <thead>
                <tr>
                    <th>Sl. No.</th>
                    <th>Hiring Agency</th>
                    <th>Start Date</th>
                    <th>Release Date</th>
                </tr>
            </thead>

            <tbody>
                <tr
                    v-for="(past_data, index) in data.past_experience"
                    :key="past_data.id || index"
                >
                    <td>
                        {{ index + 1 }}
                    </td>

                    <td>
                        {{ past_data?.hospital?.name || '-' }}
                    </td>

                    <td>
                        {{ formatnormalDate(past_data?.created_at) }}
                    </td>

                    <td>
                        {{ formatnormalDate(past_data?.released_at) }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

</div>

    <!-- ================= PROFILE PHOTO ================= -->

    <div class="view-section" v-if="authStore.user.role != 'hospital'">

        <div class="view-section-title">
            <span>Documents</span>
        </div>

        <CRow>

            <!-- Profile -->



            <!-- Aadhaar -->
            <CCol md="4" class="mb-3">
                <div class="document-upload-group">
                    <div class="document-label">
                        <div>
                            <h6>Profile Photo</h6>

                        </div>

                    </div>

                     <div class="profile-image-wrapper">
                            <img v-if="data?.profilePreviewUrl" :src="data?.profilePreviewUrl" alt="Profile Photo"
                                class="profile-image" />

                            <div v-else class="profile-image-placeholder">
                                <div class="placeholder-icon">👤</div>

                                <span>No photo</span>
                            </div>
                    </div>

                </div>
            </CCol>

            <CCol md="4" class="mb-3">
                <div class="document-upload-group">
                    <div class="document-label">
                        <div>
                            <h6>Aadhaar Card</h6>

                        </div>


                    </div>

                    <div class="pdf-upload-box">
                        <!-- PDF Icon -->
                        <div class="pdf-file-icon">
                            <div class="pdf-fold"></div>

                            <span>PDF</span>
                        </div>

                        <!-- File Information -->
                        <div class="pdf-file-content">

                            <div class="pdf-actions">


                                <button v-if="data.aadhaarPreviewUrl" type="button" class="action-secondary"
                                    @click="openFile(data.aadhaarPreviewUrl)">
                                    View
                                </button>


                            </div>
                        </div>
                    </div>



                </div>

            </CCol>
            <!-- Certificate -->
            <CCol md="4" class="mb-3">
                <div class="document-upload-group">
                    <div class="document-label">
                        <div>
                            <h6>Certificate</h6>
                        </div>

                    </div>

                    <div class="pdf-upload-box">
                        <!-- PDF Icon -->
                        <div class="pdf-file-icon certificate-pdf">
                            <div class="pdf-fold"></div>

                            <span>PDF</span>
                        </div>

                        <!-- File Information -->
                        <div class="pdf-file-content">



                            <div class="pdf-file-type">PDF Document</div>

                            <div class="pdf-actions">


                                <button v-if="data?.certificatePreviewUrl" type="button" class="action-secondary"
                                    @click="openFile(data?.certificatePreviewUrl)">
                                    View
                                </button>


                            </div>
                        </div>
                    </div>


                </div>


            </CCol>

        </CRow>

    </div>
</template>
