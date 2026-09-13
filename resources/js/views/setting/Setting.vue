<script setup>
import { ref, reactive, onMounted, computed, nextTick } from 'vue'
import { useBreadcrumb } from '@/services/useBreadcrumb'
const { breadcrumbs } = useBreadcrumb()
import Profile from './Profile.vue'
import Password from './Password.vue'
import {CRow,CCol,CCard,CCardHeader,CCardBody,CForm,CFormLabel,CFormInput,CFormFeedback,CButton,CSpinner} from '@coreui/vue'

onMounted(() => {
    breadcrumbs.value = ['Setting']

})

</script>


<template>

    <CRow>

        <!-- ====================================================== -->
        <!-- FORM -->
        <!-- ====================================================== -->

        <CCol :lg="8" :md="8" :sm="8">

             <CCard class="mb-4">

            <CCardBody>

            <DocsExample href="components/tabs.html#example">
                <CTabs activeItemKey="profile">
                <CTabList variant="tabs">
                    <CTab itemKey="profile">Profile</CTab>
                    <CTab itemKey="password">Password</CTab>
                </CTabList>
                <CTabContent>
                    <CTabPanel class="p-3" itemKey="profile"> <Profile/> </CTabPanel>
                    <CTabPanel class="p-3" itemKey="password"><Password/></CTabPanel>
                </CTabContent>
                </CTabs>
            </DocsExample>
            </CCardBody>
        </CCard>

        </CCol>




    </CRow>

</template>
