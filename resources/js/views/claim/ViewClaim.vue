<script setup>
import { ref, reactive, onMounted, watch, computed, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/services/axios'
import { useFileUpload } from '@/services/useFileUpload'
import { useBreadcrumb } from '@/services/useBreadcrumb'
import { CButton } from '@coreui/vue'

import { useToast } from 'vue-toastification'
const btnLoading = ref(false)
import { cilFile, cilCheckCircle,cilArrowCircleLeft } from '@coreui/icons'

const route = useRoute()
const router = useRouter()
const toast = useToast()
const { breadcrumbs } = useBreadcrumb()
import { loadingStore } from '@/services/loading'

const id = route.params.id || null
const claim = reactive({})
import { Fancybox } from "@fancyapps/ui";
import "@fancyapps/ui/dist/fancybox/fancybox.css";

const getClaim = async () => {
    try {
        const response = await api.post('/claim/show', { id });

        Object.assign(claim, response.data.data);
        breadcrumbs.value = ['Claim', 'View', response.data.data.claim_no]
    } catch (error) {
        console.error(error)
    }
}

onMounted(() => {
    breadcrumbs.value = ['Claim', 'View',]
    if (id) {
        getClaim()
    }
    Fancybox.bind('[data-fancybox="claim-gallery"]', {
        animated: true,
        showClass: "f-fadeIn",
        hideClass: "f-fadeOut",

        Toolbar: {
            display: {
                left: [],
                middle: ["zoomIn", "zoomOut", "toggle1to1"],
                right: ["slideshow", "fullscreen", "thumbs", "close"],
            },
        },

        Thumbs: {
            autoStart: false,
        },

        Carousel: {
            infinite: true,
        },
    });
})
const formatDateTime = (dateString) => {
    if (!dateString) return ''
    return new Date(dateString).toLocaleString('en-US', {
        dateStyle: 'short',
        timeStyle: 'short'
    })
}
onUnmounted(() => {
    Fancybox.destroy();
});

const goBack = () => {
    router.back()
}

</script>
<template>
    <div class="container-fluid">

        <!-- Registration Summary -->
        <CCard class="summary-card mb-4">
            <CCardHeader class="summary-header">
                <div class="d-flex justify-content-between align-items-center">

                    <button type="button" class="btn btn-warning me-2 warranty-btn" @click="goBack">
                        <CIcon :icon="cilArrowCircleLeft" />
                        Back

                    </button>



                </div>
            </CCardHeader>
            <CCardBody>
                <div class="row align-items-center">

                    <div class="col-lg-2 text-center">
                        <img :src="claim?.billing?.product?.product_image" class="product-image" />
                    </div>

                    <div class="col-lg-7">

                        <h3 class="product-title">
                            {{ claim?.billing?.product?.product_name }}
                        </h3>

                        <div class="summary-grid">

                            <div class="summary-item">
                                <span class="label">Claim No</span>
                                <span class="value">#{{ claim?.claim_no ?? 'N/A' }}</span>
                            </div>

                            <div class="summary-item">
                                <span class="label">Warranty</span>
                                <span class="value">{{ claim?.billing?.product?.warranty }} Years</span>
                            </div>

                            <div class="summary-item">
                                <span class="label">Acknowledgement No</span>
                                <span class="value">#{{ claim?.billing?.acknowledgement_no ?? 'N/A' }}</span>
                            </div>

                            <div class="summary-item">
                                <span class="label">Created On</span>
                                <span class="value">
                                    {{ formatDateTime(claim?.billing?.created_at) }}

                                </span>
                            </div>

                        </div>

                    </div>

                    <div class="col-lg-3 text-center">



                        <!-- Status -->
                        <div class="mt-2">
                            <CBadge :color="claim?.billing?.acknowledgement_no ? 'success' : 'warning'"
                                class="status-badge">
                                {{ claim?.billing?.acknowledgement_no ? 'Active' : 'Pending' }}
                            </CBadge>
                        </div>

                    </div>

                </div>
            </CCardBody>
        </CCard>


        <CCard class="shadow-sm border-0 mt-4">
            <CCardHeader class="bg-white py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="cil-image me-2 text-primary"></i>
                        Claim Images
                    </h5>

                    <CBadge color="primary" shape="rounded-pill">
                        {{ claim.images?.length || 0 }} Images
                    </CBadge>
                </div>
            </CCardHeader>

            <CCardBody>

                <div v-if="claim.images?.length" class="row">

                    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 mb-4" v-for="(image, index) in claim.images"
                        :key="index">
                        <div class="gallery-card">

                            <div class="gallery-image-wrapper">

                                <a :href="image" data-fancybox="claim-gallery"
                                    :data-caption="'Warranty Claim Image ' + (index + 1)">
                                    <img :src="image" class="gallery-image" />

                                    <div class="gallery-overlay">
                                        <CButton color="light" size="sm" type="button">
                                            <i class="cil-zoom me-1"></i>
                                            View
                                        </CButton>
                                    </div>
                                </a>

                            </div>

                            <div class="gallery-footer">
                                <span>Image {{ index + 1 }}</span>

                                <small class="text-muted">
                                    Warranty Claim
                                </small>
                            </div>

                        </div>
                    </div>

                </div>

                <div v-else class="empty-gallery">

                    <i class="cil-image"></i>

                    <h5>No Images Uploaded</h5>

                    <p class="text-muted mb-0">
                        Customer has not uploaded any claim images.
                    </p>

                </div>

            </CCardBody>
        </CCard>

        <!-- Preview Modal -->

        <CModal alignment="center" size="xl" :visible="showImageModal" @close="showImageModal = false">

            <CModalBody class="text-center p-2">

                <img :src="selectedImage" class="img-fluid rounded" style="max-height:80vh;" />

            </CModalBody>

        </CModal>


        <!-- Product Information -->

        <CCard class="custom-card mb-4">

            <CCardHeader>
                <h5>📦 Product Information</h5>
            </CCardHeader>

            <CCardBody>

                <div class="info-row">
                    <span class="info-label">Product Name</span>
                    <span class="info-value">
                        {{ claim?.billing?.product?.product_name }}
                    </span>
                </div>

                <div class="info-row">
                    <span class="info-label">Warranty</span>
                    <span class="info-value">
                        {{ claim?.billing?.product?.warranty }} Years
                    </span>
                </div>

                <div class="info-row">
                    <span class="info-label">Registration Type</span>
                    <span class="info-value">
                        {{ claim?.billing?.registration_type }}
                    </span>
                </div>

                <div class="info-row">
                    <span class="info-label">Status</span>

                    <span class="info-value">
                        <CBadge :color="claim?.billing?.status == 'A' ? 'success' : 'danger'">
                            {{ claim?.billing?.status == 'A' ? 'Active' : 'Inactive' }}
                        </CBadge>
                    </span>
                </div>

            </CCardBody>

        </CCard>
        <!-- Customer Information -->
        <CCard class="custom-card mb-4">

            <CCardHeader>
                <h5>👤 Customer Information</h5>
            </CCardHeader>

            <CCardBody>

                <div class="row">

                    <div class="col-md-6">

                        <div class="profile-item">
                            <label>Customer Name</label>
                            <div>{{ claim?.billing?.user?.name }}</div>
                        </div>

                        <div class="profile-item">
                            <label>Email Address</label>
                            <div>{{ claim?.billing?.user?.email }}</div>
                        </div>

                        <div class="profile-item">
                            <label>Mobile Number</label>
                            <div>{{ claim?.billing?.user?.mobile_number }}</div>
                        </div>

                    </div>

                    <div class="col-md-6">

                        <div class="profile-item">
                            <label>Postal Address</label>
                            <div>{{ claim?.billing?.user?.postal_address }}</div>
                        </div>

                        <div class="profile-item">
                            <label>Email Verified</label>

                            <CBadge :color="claim?.billing?.user?.email_authenticated ? 'success' : 'danger'">
                                {{ claim?.billing?.user?.email_authenticated ? 'Verified' : 'Not Verified' }}
                            </CBadge>

                        </div>

                        <div class="profile-item">
                            <label>Mobile Verified</label>

                            <CBadge :color="claim?.billing?.user?.mobile_authenticated ? 'success' : 'danger'">
                                {{ claim?.billing?.user?.mobile_authenticated ? 'Verified' : 'Not Verified' }}
                            </CBadge>

                        </div>

                    </div>

                </div>

            </CCardBody>

        </CCard>
        <!-- Dealer Information -->
        <CCard class="custom-card mb-4">

            <CCardHeader>
                <h5>🏪 Dealer Information</h5>
            </CCardHeader>

            <CCardBody>

                <div class="row">

                    <div class="col-md-6">

                        <div class="info-row">
                            <span class="info-label">Dealer Name</span>
                            <span class="info-value">
                                {{ claim?.billing?.authorized_dealer_name }}
                            </span>
                        </div>

                        <div class="info-row">
                            <span class="info-label">Dealer Phone</span>
                            <span class="info-value">
                                {{ claim?.billing?.authorized_dealer_phone }}
                            </span>
                        </div>

                    </div>

                    <div class="col-md-6">

                        <div class="profile-item">
                            <label>Dealer Address</label>

                            <div>
                                {{ claim?.billing?.authorized_dealer_address }}
                            </div>

                        </div>

                    </div>

                </div>

            </CCardBody>

        </CCard>
        <!-- Purchase Information -->
        <CCard class="custom-card mb-4">

            <CCardHeader>
                <h5>🧾 Purchase & Paint Details</h5>
            </CCardHeader>

            <CCardBody>

                <div class="row">

                    <!-- Purchase -->

                    <div class="col-lg-6">

                        <h6 class="section-title">
                            Purchase Information
                        </h6>

                        <div class="info-row">
                            <span class="info-label">Bill Number</span>
                            <span class="info-value">{{ claim?.billing?.bill_number }}</span>
                        </div>

                        <div class="info-row">
                            <span class="info-label">Bill Date</span>
                            <span class="info-value">{{ claim?.billing?.bill_date }}</span>
                        </div>

                        <div class="info-row">
                            <span class="info-label">Quantity</span>
                            <span class="info-value">
                                {{ claim?.billing?.quantity }}
                            </span>
                        </div>

                        <div class="info-row">
                            <span class="info-label">Application Method</span>
                            <span class="info-value">
                                {{ claim?.billing?.application_method }}
                            </span>
                        </div>

                        <div class="info-row">
                            <span class="info-label">No. of Coats</span>
                            <span class="info-value">
                                {{ claim?.billing?.no_of_coats }}
                            </span>
                        </div>

                    </div>

                    <!-- Paint -->

                    <div class="col-lg-6">

                        <h6 class="section-title">
                            Paint Details
                        </h6>

                        <div class="info-row">
                            <span class="info-label">Shade Name</span>
                            <span class="info-value">
                                {{ claim?.billing?.shade_name }}
                            </span>
                        </div>

                        <div class="info-row">
                            <span class="info-label">Shade Code</span>
                            <span class="info-value">
                                {{ claim?.billing?.shade_code }}
                            </span>
                        </div>

                        <div class="info-row">
                            <span class="info-label">Batch Number</span>
                            <span class="info-value">
                                {{ claim?.billing?.batch_number }}
                            </span>
                        </div>

                        <div class="info-row">
                            <span class="info-label">
                                Manufacturing Date
                            </span>
                            <span class="info-value">
                                {{ claim?.billing?.manufacturing_date }}
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">
                                Invoice
                            </span>
                            <span class="info-value">
                                <CButton color="primary" variant="outline">
                                    <a target="_blank" style="text-decoration: none;"
                                        :href="claim?.billing?.invoice_file">
                                        <CIcon :icon="cilFile" />View
                                    </a>
                                </CButton>

                            </span>
                        </div>

                    </div>

                </div>

            </CCardBody>

        </CCard>
        <!-- Site Information -->
        <CCard class="custom-card mb-4">

            <CCardHeader>
                <h5>🏠 Site Information</h5>
            </CCardHeader>

            <CCardBody>

                <div class="row">

                    <div class="col-lg-6">

                        <div class="profile-item">
                            <label>Property Type</label>

                            <div>
                                {{ claim?.billing?.property_type }}
                            </div>

                        </div>

                        <div class="profile-item">
                            <label>E-Approval Number</label>

                            <div>
                                {{ claim?.billing?.e_approval_number }}
                            </div>

                        </div>

                    </div>

                    <div class="col-lg-6">

                        <div class="profile-item">

                            <label>Site Address</label>

                            <div>
                                {{ claim?.billing?.site_postal_address }}
                            </div>

                        </div>

                    </div>

                </div>

            </CCardBody>

        </CCard>
        <CCard class="custom-card mb-4">

            <CCardHeader>
                <h5>🛡 Warranty Terms & Conditions</h5>
            </CCardHeader>

            <CCardBody>

                <div class="terms-wrapper">

                    <div class="terms-content" v-html="claim?.billing?.product?.term_text"></div>

                </div>

            </CCardBody>

        </CCard>

    </div>
</template>

<style scoped src="@/assets/billing.css"></style>
