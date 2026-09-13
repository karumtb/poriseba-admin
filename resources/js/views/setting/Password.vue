<script setup>
import { ref, reactive, onMounted, computed, nextTick } from 'vue'

import api from '@/services/adminAxios'
import { loadingStore } from '@/services/loading'
import { useBreadcrumb } from '@/services/useBreadcrumb'
import { allowDecimal } from '@/utils/input'
const { breadcrumbs } = useBreadcrumb()

import {CRow,CCol,CCard,CCardHeader,CCardBody,CForm,CFormLabel,CFormInput,CFormFeedback,CButton,CSpinner} from '@coreui/vue'

import CIcon from '@coreui/icons-vue'

import {cilPencil,cilTrash,cilPlus,cilSave,cilX,cilSearch, cilReload} from '@coreui/icons'

import Swal from 'sweetalert2'
import { useToast } from 'vue-toastification'

const toast = useToast()

/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/


const loading = computed(() => loadingStore.loading)

const btnLoading = ref(false)

const submitted = ref(false)


const validated = ref(false)




/*
|--------------------------------------------------------------------------
| Form
|--------------------------------------------------------------------------
*/

const form = reactive({
    old_password: '',
    new_password: '',
    new_password_confirmation: '',
})

/*
|--------------------------------------------------------------------------
| Reset Form
|--------------------------------------------------------------------------
*/

const resetForm = () => {
    Object.assign(form, {
        old_password: null,
        new_password: null,
        new_password_confirmation: null,


    })

    validated.value = false

}


/*
|--------------------------------------------------------------------------
| Save
|--------------------------------------------------------------------------
*/

const savePassword = async (event) => {
    submitted.value = true
    submitted.value = true;
    const formElement = event.currentTarget
    if (!formElement.checkValidity()  || !isValidPassword(form.new_password) ||form.new_password === form.old_password || !isPasswordConfirmed()) {
        event.preventDefault()
        event.stopPropagation()
        // Wait for Vue to update the DOM with the '.is-invalid' class
        nextTick(() => {
            const firstErrorEl = formElement.querySelector('.is-invalid, :invalid');
            if (firstErrorEl) {
                firstErrorEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });

        return
    }

    validated.value = true
    btnLoading.value = true

    try {
        // Your API call here
        try {

        const response = await api.post(
            '/change-password',
            {
                ...form,
            }
        )
        submitted.value = false;

        if (response.data.status) {

            toast.success(
                response.data.message ||
                'Saved successfully.'
            )

            resetForm()

        }

    } catch (error) {

        console.error(error)

        toast.error(
            error.response?.data?.message ||
            'Something went wrong.'
        )

    } finally {

        btnLoading.value = false;

    }



    } catch (error) {
        console.error(error)
    } finally {
        loading.value = false
    }
}
/*
|--------------------------------------------------------------------------
| Mounted
|--------------------------------------------------------------------------
*/

onMounted(() => {
    breadcrumbs.value = ['Password']

})

/*
|--------------------------------------------------------------------------
| Validation
|--------------------------------------------------------------------------
*/

const isValidPassword = (password) => {
    if (!password) {
        return false
    }

    // Minimum 8 characters
    if (password.length < 8) {
        return false
    }

    // At least one uppercase
    if (!/[A-Z]/.test(password)) {
        return false
    }

    // At least one lowercase
    if (!/[a-z]/.test(password)) {
        return false
    }

    // At least one number
    if (!/[0-9]/.test(password)) {
        return false
    }

    // At least one special character
    if (!/[^A-Za-z0-9]/.test(password)) {
        return false
    }

    return true
}

const isPasswordConfirmed = () => {
    return (
        form.new_password &&
        form.new_password_confirmation &&
        form.new_password === form.new_password_confirmation
    )
}

</script>
<template>

    <CRow>

        <!-- ====================================================== -->
        <!-- FORM -->
        <!-- ====================================================== -->

        <CCol :lg="12" :md="12" :sm="12">

            <CCard class="mb-4">


                <CCardBody>

                    <CForm class="row g-3 needs-validation" novalidate :validated="validated"
                        @submit.prevent="savePassword">

                        <!-- Old Password -->
                        <CCol :md="12">
                            <CFormLabel>Old Password</CFormLabel>

                            <input v-model.trim="form.old_password" type="password" required class="form-control"
                                :class="{
                                    'is-invalid':
                                        submitted && !form.old_password
                                }" />

                            <CFormFeedback invalid>
                                Old password is required.
                            </CFormFeedback>
                        </CCol>


                        <!-- New Password -->
                        <CCol :md="12">
                            <CFormLabel>New Password</CFormLabel>

                            <input v-model="form.new_password" type="password" required class="form-control" :class="{
                                'is-invalid':
                                    submitted &&
                                    (
                                        !isValidPassword(form.new_password) ||
                                        form.new_password === form.old_password
                                    )
                            }" />

                            <CFormFeedback v-if="
                                submitted &&
                                !form.new_password
                            " invalid>
                                New password is required.
                            </CFormFeedback>

                            <CFormFeedback v-else-if="
                                submitted &&
                                form.new_password.length < 8
                            " invalid>
                                Password must be at least 8 characters.
                            </CFormFeedback>

                            <CFormFeedback v-else-if="
                                submitted &&
                                !/[A-Z]/.test(form.new_password)
                            " invalid>
                                Password must contain at least one uppercase letter.
                            </CFormFeedback>

                            <CFormFeedback v-else-if="
                                submitted &&
                                !/[a-z]/.test(form.new_password)
                            " invalid>
                                Password must contain at least one lowercase letter.
                            </CFormFeedback>

                            <CFormFeedback v-else-if="
                                submitted &&
                                !/[0-9]/.test(form.new_password)
                            " invalid>
                                Password must contain at least one number.
                            </CFormFeedback>

                            <CFormFeedback v-else-if="
                                submitted &&
                                !/[^A-Za-z0-9]/.test(form.new_password)
                            " invalid>
                                Password must contain at least one special character.
                            </CFormFeedback>

                            <CFormFeedback v-else-if="
                                submitted &&
                                form.new_password === form.old_password
                            " invalid>
                                New password must be different from old password.
                            </CFormFeedback>
                        </CCol>


                        <!-- Confirm Password -->
                        <CCol :md="12">
                            <CFormLabel>Confirm New Password</CFormLabel>

                            <input v-model="form.new_password_confirmation" type="password" required
                                class="form-control" :class="{
                                    'is-invalid':
                                        submitted &&
                                        !isPasswordConfirmed()
                                }" />

                            <CFormFeedback v-if="
                                submitted &&
                                !form.new_password_confirmation
                            " invalid>
                                Please confirm your new password.
                            </CFormFeedback>

                            <CFormFeedback v-else-if="
                                submitted &&
                                form.new_password !==
                                form.new_password_confirmation
                            " invalid>
                                Password confirmation does not match.
                            </CFormFeedback>
                        </CCol>


                        <!-- Submit -->
                        <CCol :md="12">
                            <CButton type="submit" color="primary" :disabled="loading">
                                <CSpinner v-if="btnLoading" size="sm" class="me-1" />

                                    <CIcon v-else :icon="cilSave" class="me-1" />

                                {{ loading ? 'Changing...' : 'Change Password' }}

                            </CButton>
                        </CCol>

                    </CForm>

                </CCardBody>

            </CCard>

        </CCol>



    </CRow>

</template>
