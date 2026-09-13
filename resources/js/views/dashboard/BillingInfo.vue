<script setup>
import { ref, reactive, onMounted, watch, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/services/axios'
import { useFileUpload } from '@/services/useFileUpload'
import { useBreadcrumb } from '@/services/useBreadcrumb'
import { CButton } from '@coreui/vue'

import { useToast } from 'vue-toastification'
const btnLoading = ref(false)
import { cilFile, cilCheckCircle, cilArrowCircleLeft } from '@coreui/icons'

const route = useRoute()
const router = useRouter()
const toast = useToast()
const { breadcrumbs } = useBreadcrumb()
import { loadingStore } from '@/services/loading'

const id = route.params.id || null
const billing = reactive({})

const getBilling = async () => {
    try {
        const response = await api.post('/billing/view', { id });

        Object.assign(billing, response.data.data);
        breadcrumbs.value = ['Billing', 'View', response.data.data.product?.product_name]
    } catch (error) {
        console.error(error)
    }
}

onMounted(() => {
    breadcrumbs.value = ['Billing', 'View',]
    if (id) {
        getBilling()
    }
})
const formatDateTime = (dateString) => {
    if (!dateString) return ''
    return new Date(dateString).toLocaleString('en-US', {
        dateStyle: 'short',
        timeStyle: 'short'
    })
}
const generateAcknowledgement = async (id) => {
    btnLoading.value = true
    try {

        const response = await api.post('/warranty/acknowledgement', {
            id
        });

        if (response.data.status) {
            toast.success(response.data?.message);
            billing.acknowledgement_no = response.data.data.acknowledgement_no
            router.push('/dashboard')

        }
    } catch (error) {
    } finally {
        btnLoading.value = false;
    }
};
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

                    <!-- Product Information -->
                    <div :class="billing?.product ? 'col-lg-7' : 'col-lg-10'">

                        <!-- Product Name -->
                        <h3 class="product-title">
                            {{ billing?.product?.product_name ?? 'N/A' }}
                        </h3>

                        <div class="summary-grid">

                            <!-- Acknowledgement -->
                            <div class="summary-item">
                                <span class="label">Acknowledgement No</span>
                                <span class="value">
                                    #{{ billing?.acknowledgement_no ?? 'N/A' }}
                                </span>
                            </div>

                            <!-- Warranty - Only when product exists -->
                            <div v-if="billing?.product" class="summary-item">
                                <span class="label">Warranty</span>
                                <span class="value">
                                    {{ billing.product.warranty }} Years
                                </span>
                            </div>

                            <!-- Registration Type -->
                            <div class="summary-item">
                                <span class="label">Registration Type</span>
                                <span class="value text-capitalize">
                                    {{ billing?.registration_type ?? 'N/A' }}
                                </span>
                            </div>

                            <!-- Created On -->
                            <div class="summary-item">
                                <span class="label">Created On</span>
                                <span class="value">
                                    {{ formatDateTime(billing?.created_at) }}
                                </span>
                            </div>

                        </div>

                    </div>


                    <!-- Generate / Acknowledgement -->
                    <div :class="billing?.product ? 'col-lg-3' : 'col-lg-2'" class="text-center">

                        <!-- Generate Button -->
                        <CButton v-if="!billing?.acknowledgement_no" color="primary" size="sm"
                            @click="generateAcknowledgement(billing.id)">

                            <CSpinner v-if="btnLoading" size="sm" class="me-2" />

                            {{
                                btnLoading
                                    ? 'Generating ...'
                                    : 'Generate Acknowledgement'
                            }}

                        </CButton>

                        <!-- Acknowledgement Number -->
                        <CBadge v-else color="success" class="status-badge px-3 py-2">
                            <CIcon :icon="cilCheckCircle" class="me-1" />

                            {{ billing.acknowledgement_no }}
                        </CBadge>


                        <!-- Status -->
                        <div class="mt-2">

                            <CBadge :color="billing?.acknowledgement_no
                                    ? 'success'
                                    : 'warning'
                                " class="status-badge">
                                {{
                                    billing?.acknowledgement_no
                                        ? 'Active'
                                        : 'Pending'
                                }}
                            </CBadge>

                        </div>

                    </div>

                </div>
            </CCardBody>
        </CCard>





        <!-- Product Information -->

        <CCard class="custom-card mb-4">

            <CCardHeader>
                <h5>📦 Product Information</h5>
            </CCardHeader>

            <CCardBody>
                <template v-if="billing?.product">

                <div class="info-row">
                    <span class="info-label">Product Name</span>
                    <span class="info-value">
                        {{ billing?.product?.product_name }}
                    </span>
                </div>

                <div class="info-row">
                    <span class="info-label">Warranty</span>
                    <span class="info-value">
                        {{ billing?.product?.warranty }} Years
                    </span>
                </div>
                </template>
                <div class="info-row">
                    <span class="info-label">Registration Type</span>
                    <span class="info-value">
                        {{ billing.registration_type }}
                    </span>
                </div>

                <div class="info-row">
                    <span class="info-label">Status</span>

                    <span class="info-value">
                        <CBadge :color="billing.status == 'A' ? 'success' : 'danger'">
                            {{ billing.status == 'A' ? 'Active' : 'Inactive' }}
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
                            <div>{{ billing?.user?.name }}</div>
                        </div>

                        <div class="profile-item">
                            <label>Email Address</label>
                            <div>{{ billing?.user?.email }}</div>
                        </div>

                        <div class="profile-item">
                            <label>Mobile Number</label>
                            <div>{{ billing?.user?.mobile_number }}</div>
                        </div>

                    </div>

                    <div class="col-md-6">

                        <div class="profile-item">
                            <label>Postal Address</label>
                            <div>{{ billing?.user?.postal_address }}</div>
                        </div>

                        <div class="profile-item">
                            <label>Email Verified</label>

                            <CBadge :color="billing?.user?.email_authenticated ? 'success' : 'danger'">
                                {{ billing?.user?.email_authenticated ? 'Verified' : 'Not Verified' }}
                            </CBadge>

                        </div>

                        <div class="profile-item">
                            <label>Mobile Verified</label>

                            <CBadge :color="billing?.user?.mobile_authenticated ? 'success' : 'danger'">
                                {{ billing?.user?.mobile_authenticated ? 'Verified' : 'Not Verified' }}
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
                                {{ billing.authorized_dealer_name }}
                            </span>
                        </div>

                        <div class="info-row">
                            <span class="info-label">Dealer Phone</span>
                            <span class="info-value">
                                {{ billing.authorized_dealer_phone }}
                            </span>
                        </div>

                    </div>

                    <div class="col-md-6">

                        <div class="profile-item">
                            <label>Dealer Address</label>

                            <div>
                                {{ billing.authorized_dealer_address }}
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
                            <span class="info-value">{{ billing.bill_number }}</span>
                        </div>

                        <div class="info-row">
                            <span class="info-label">Bill Date</span>
                            <span class="info-value">{{ billing.bill_date }}</span>
                        </div>

                        <div class="info-row">
                            <span class="info-label">Quantity</span>
                            <span class="info-value">
                                {{ billing.quantity }}
                            </span>
                        </div>

                        <div class="info-row">
                            <span class="info-label">Application Method</span>
                            <span class="info-value">
                                {{ billing.application_method }}
                            </span>
                        </div>

                        <div class="info-row">
                            <span class="info-label">No. of Coats</span>
                            <span class="info-value">
                                {{ billing.no_of_coats }}
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
                                {{ billing.shade_name }}
                            </span>
                        </div>

                        <div class="info-row">
                            <span class="info-label">Shade Code</span>
                            <span class="info-value">
                                {{ billing.shade_code }}
                            </span>
                        </div>

                        <div class="info-row">
                            <span class="info-label">Batch Number</span>
                            <span class="info-value">
                                {{ billing.batch_number }}
                            </span>
                        </div>

                        <div class="info-row">
                            <span class="info-label">
                                Manufacturing Date
                            </span>
                            <span class="info-value">
                                {{ billing.manufacturing_date }}
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">
                                Invoice
                            </span>
                            <span class="info-value">
                                <CButton color="primary" variant="outline">
                                    <a target="_blank" style="text-decoration: none;" :href="billing.invoice_file">
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
                                {{ billing.property_type }}
                            </div>

                        </div>

                        <div class="profile-item">
                            <label>E-Approval Number</label>

                            <div>
                                {{ billing.e_approval_number }}
                            </div>

                        </div>

                    </div>

                    <div class="col-lg-6">

                        <div class="profile-item">

                            <label>Site Address</label>

                            <div>
                                {{ billing.site_postal_address }}
                            </div>

                        </div>

                    </div>

                </div>

            </CCardBody>

        </CCard>
        <CCard class="custom-card mb-4" v-if="billing?.product">

            <CCardHeader>
                <h5>🛡 Warranty Terms & Conditions</h5>
            </CCardHeader>

            <CCardBody>

                <div class="terms-wrapper">

                    <div class="terms-content" v-html="billing?.product?.term_text"></div>

                </div>

            </CCardBody>

        </CCard>

    </div>
</template>

<style scoped src="@/assets/billing.css"></style>
