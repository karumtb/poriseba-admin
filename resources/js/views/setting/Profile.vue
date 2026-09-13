<script setup>
import {ref,reactive,onMounted,computed,nextTick,onUnmounted,watch,} from "vue";

import { useFormBuilder } from "@/form/useFormBuilder";


import api from "@/services/adminAxios";

import { loadingStore } from "@/services/loading";

import { useBreadcrumb } from "@/services/useBreadcrumb";

import { allowDecimal,getFileName,openFile } from "@/utils/input";

const { breadcrumbs } = useBreadcrumb();

import { useFileUpload } from "@/services/useFileUpload";

import {CRow,CCol,CCard,CCardHeader,CCardBody,CForm,CFormLabel,CFormInput,CFormFeedback,CButton,CSpinner} from "@coreui/vue";

import CIcon from "@coreui/icons-vue";

import {cilPencil,cilTrash,cilPlus,cilSave,cilX,cilSearch,cilReload,cilCheckCircle} from "@coreui/icons";

import { useAuthStore } from '@/stores/auth'

import { useToast } from "vue-toastification";

import Swal from "sweetalert2";


const toast = useToast();
const authStore = useAuthStore()


/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

const enrolments = ref([]);
const vendors = ref([]);

const vendorSearch = ref(null);

const loading = computed(() => loadingStore.loading);
const vendorLoading = computed(() => loadingStore.loading);

const btnLoading = ref(false);

const submitted = ref(false);

const validated = ref(false);

const editMode = ref(false);

const editingId = ref(null);


const totalRecords = ref(0);

const PhotoFile = ref(null);

const formSection = ref(null);


// Profile photo
const {file: profileFile,previewUrl: profilePreviewUrl,fileError: profileFileError,handleFileChange: handleProfileFileChange,clearFile: clearProfileFile,
} = useFileUpload();


/*
|--------------------------------------------------------------------------
| Form
|--------------------------------------------------------------------------
*/

const { form, errors, validate, validateField, isInvalid, reset, patchValue } =
    useFormBuilder({


        profile_photo: {
            default: null,
            required:false,
            message: "Profile Photo is required",
        },

        profile_photo_file: {
            default: null,
            required:false,
            message: "Profile Photo is required",
        },


    });

watch(profileFile, (newFile) => {
    if (newFile) {
        form.profile_photo_file = newFile;
    } else {
        console.log("File was removed.");
    }
});



/*
|--------------------------------------------------------------------------
| Save
|--------------------------------------------------------------------------
*/

const saveProfile = async (event) => {
    submitted.value = true;

    if (!validate()) {
        event.preventDefault()
        event.stopPropagation()
        // Wait for Vue to update the DOM with the '.is-invalid' class
        nextTick(() => {
            const firstErrorEl = formElement.querySelector('.is-invalid, :invalid');
            if (firstErrorEl) {
                firstErrorEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
        return;
    }

    btnLoading.value = true;

    try {
        const formData = new FormData();
        Object.entries(form).forEach(([key, value]) => {
            if (value !== null && value !== undefined) {
                formData.append(key, value);
            }
        });

    const response = await api.post("/update-profile", formData);
    if (response.data.status) {
            toast.success(
                response.data.message ||
                'Saved successfully.'
            )
            reset();
            authStore.updateUser(response.data.data);


        } else {

            toast.error(
                response.data.message ||
                'Unable to save data.'
            )

        }

    } catch (error) {
        console.error(error);
    } finally {
        console.log("Final..");
        btnLoading.value = false;
        submitted.value = false;
    }
};



/*
|--------------------------------------------------------------------------
| Mounted
|--------------------------------------------------------------------------
*/

onMounted(() => {
    breadcrumbs.value = ["Proile"];
    profilePreviewUrl.value=authStore?.user?.profileImageUrl;

});


/*
|--------------------------------------------------------------------------
| Upload Trigger
|--------------------------------------------------------------------------
*/
const triggerPhotoUpload = () => {
    PhotoFile.value.click();
};

onUnmounted(() => {

});
/*
|--------------------------------------------------------------------------
| Clear Vendor
|--------------------------------------------------------------------------
*/
const clearVendor = () => {
    vendorSearch = "";
    form.vendor_id = null;
    validateField("vendor_id");
};


</script>
<template>
    <CRow>

        <!-- ====================================================== -->
        <!-- FORM -->
        <!-- ====================================================== -->

        <CCol :lg="12" :md="12" :sm="12" ref="formSection">
            <CCard class="mb-4">


                <CCardBody>
                    <CForm
                        class="row g-3 needs-validation layout-fixed-form"
                        novalidate
                        :validated="validated"
                        @submit.prevent="saveProfile"
                    >
                        <!-- 1. Scrollable Fields Container -->
                        <div class="form-scroll-body">
                            <CCol :md="12">
                                <CRow>

                                    <CCol md="12" class="mb-3">
                                        <!-- =========================================================
     PROFILE PHOTO
========================================================= -->

                                        <div class="document-upload-group">
                                            <div class="document-label">
                                                <div>
                                                    <h6>Profile Photo</h6>
                                                    <span
                                                        >JPG, JPEG WEBP or PNG ·
                                                        Max 5 MB</span
                                                    >
                                                </div>

                                                <span
                                                    v-if="profilePreviewUrl"
                                                    class="uploaded-badge"
                                                >
                                                    ✓ Uploaded
                                                </span>
                                            </div>

                                            <div class="profile-upload-box">
                                                <!-- Photo Preview -->
                                                <div
                                                    class="profile-image-wrapper"
                                                >
                                                    <img
                                                        v-if="profilePreviewUrl"
                                                        :src="profilePreviewUrl"
                                                        alt="Profile Photo"
                                                        class="profile-image"
                                                    />

                                                    <div
                                                        v-else
                                                        class="profile-image-placeholder"
                                                    >
                                                        <div
                                                            class="placeholder-icon"
                                                        >
                                                            👤
                                                        </div>

                                                        <span>No photo</span>
                                                    </div>
                                                </div>

                                                <!-- Details -->
                                                <div
                                                    class="profile-upload-details"
                                                >
                                                    <div
                                                        v-if="profileFile"
                                                        class="selected-filename"
                                                    >
                                                        {{ profileFile.name }}
                                                    </div>

                                                    <div
                                                        v-else-if="
                                                            profilePreviewUrl
                                                        "
                                                        class="selected-filename"
                                                    >
                                                        Profile photo
                                                    </div>

                                                    <div
                                                        v-else
                                                        class="selected-filename"
                                                    >
                                                        No profile photo
                                                        selected
                                                    </div>

                                                    <div
                                                        class="selected-file-info"
                                                    >
                                                        {{
                                                            profileFile
                                                                ? "Photo selected successfully"
                                                                : profilePreviewUrl
                                                                  ? "Saved profile photo"
                                                                  : "Upload a clear profile photograph"
                                                        }}
                                                    </div>

                                                    <div class="file-actions">
                                                        <input
                                                            ref="PhotoFile"
                                                            type="file"
                                                            accept="image/jpeg,image/png,image/jpg,image/webp"
                                                            style="
                                                                display: none;
                                                            "
                                                            @change="
                                                                handleProfileFileChange(
                                                                    $event,
                                                                    [
                                                                        'image/jpeg',
                                                                        'image/png',
                                                                        'image/jpg',
                                                                        'image/webp',
                                                                    ],
                                                                    5,
                                                                )
                                                            "
                                                        />

                                                        <button
                                                            type="button"
                                                            class="action-primary"
                                                            @click="
                                                                triggerPhotoUpload
                                                            "
                                                        >
                                                            <span>📤</span>

                                                            {{
                                                                profilePreviewUrl
                                                                    ? "Change Photo"
                                                                    : "Upload Photo"
                                                            }}
                                                        </button>

                                                        <button
                                                            v-if="
                                                                profilePreviewUrl &&
                                                                !form.id
                                                            "
                                                            type="button"
                                                            class="action-danger"
                                                            @click="
                                                                clearProfileFile(
                                                                    PhotoFile,
                                                                )
                                                            "
                                                        >
                                                            Remove
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                            <div
                                                v-if="profileFileError"
                                                class="upload-error"
                                            >
                                                {{ profileFileError }}
                                            </div>


                                        </div>
                                    </CCol>
                                </CRow>

                                <!-- =========================================================AADHAAR + CERTIFICATE========================================================= -->


                            </CCol>
                        </div>

                        <!-- 2. Sticky Action Buttons Container -->
                        <div class="form-sticky-actions">
                            <CCol :xs="12">
                                <div class="d-flex gap-2 justify-content-end">

                                    <CButton
                                        type="submit"
                                        color="primary"
                                        :disabled="btnLoading"
                                    >
                                        <CSpinner
                                            v-if="btnLoading"
                                            size="sm"
                                            class="me-1"
                                        />
                                        <CIcon
                                            v-else
                                            :icon="cilSave"
                                            class="me-1"
                                        />
                                       Submit
                                    </CButton>
                                </div>
                            </CCol>
                        </div>
                    </CForm>
                </CCardBody>
            </CCard>
        </CCol>
    </CRow>
</template>
