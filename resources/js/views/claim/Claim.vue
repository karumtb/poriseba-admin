<script setup>
import { ref, onMounted,reactive} from 'vue'
import api from '@/services/axios'
import { useRouter } from 'vue-router'

import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Tag from 'primevue/tag'
import Select from 'primevue/select'

import { useBreadcrumb } from '@/services/useBreadcrumb'
import { FilterMatchMode } from '@primevue/core/api'
import { cilLowVision,cilSearch,cilReload,cilTrash } from '@coreui/icons'
import Swal from 'sweetalert2' //
import { useToast } from 'vue-toastification'
const claims = ref([])
const loading = ref(false)
const totalRecords = ref(0)
const router = useRouter()
const toast = useToast()

const { breadcrumbs } = useBreadcrumb()
const first = ref(0);  // Index of the first record on the current page
const viewClaim = (id) => {
  router.push({
    name: 'ViewClaim',
    params: { id }
  })
}
const defaultFilters = {
    claim_no: null,
}

const filters = reactive({ ...defaultFilters })

const lazyParams = ref({
  first: 0,
  rows: 10,
  page: 0,
})

const fetchClaims = async () => {
  loading.value = true

  try {
    const page = lazyParams.value.page + 1

    const response = await api.post('/claim/index', {
        page: page,
        per_page: lazyParams.value.rows,
        ...filters

    })

    const result = response.data.data

    claims.value = result.data
    totalRecords.value = result.total
  } catch (error) {
    console.error('Claim Error:', error)
  } finally {
    loading.value = false
  }
}

const onPage = (event) => {
 first.value = event.first;
  lazyParams.value = {
    first: event.first,
    rows: event.rows,
    page: event.page,
  }

  fetchClaims()
}
const searchFilters=()=>{
lazyParams.value.page=0;
fetchClaims();
}

const resetFilters=()=>{
lazyParams.value.page=0;
Object.assign(filters, defaultFilters)
fetchClaims();
}

const formatDate = (date) => {
  if (!date) {
    return '-'
  }

  return new Date(`${date}T00:00:00`).toLocaleDateString('en-IN', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  })
}

onMounted(() => {
 breadcrumbs.value = ['Claim']
  fetchClaims()
})
const deleteClaim= (id) => {
Swal.fire({
    title: 'Are you sure?',
    text: 'You will not be able to recover this data!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Yes, delete it!'
  }).then(async (result) => {
    if (result.isConfirmed) {
    const response = await api.post('/claim/remove', {
        id: id,
    })

    const result = response.data;
    if(result.status)
    {
         toast.error(result?.message);
         fetchClaims();
    }


      //Swal.fire('Deleted!', 'Your file has been deleted.', 'success')
    }
  })
}
</script>

<template>
  <div class="card">
    <CCard class="border-0 shadow-sm mb-4">
    <CCardBody class="py-4">

        <div class="row g-3 align-items-end">

            <!-- Acknowledgement -->
            <div class="col-lg-4 col-md-6">
                <label class="form-label fw-semibold text-secondary mb-2">
                    Claim No
                </label>

                <CFormInput
                    v-model.trim="filters.claim_no"
                    placeholder="Enter Claim No"
                    size="lg"
                />
            </div>



            <!-- Search -->
            <div class="col-lg-2 col-md-6">
                <CButton
                    color="primary"
                    class="w-100 py-2"
                    @click="searchFilters"
                >
                    <CIcon :icon="cilSearch" class="me-2"/>
                    Search
                </CButton>
            </div>

            <!-- Reset -->
            <div class="col-lg-2 col-md-6">
                <CButton
                    color="light"
                    class="w-100 py-2 border"
                    @click="resetFilters"
                >
                    <CIcon :icon="cilReload" class="me-2"/>
                    Reset
                </CButton>
            </div>

        </div>

    </CCardBody>
</CCard>
    <DataTable
      :value="claims"
      lazy
      paginator
      :first="lazyParams.first"
      :rows="lazyParams.rows"
      :totalRecords="totalRecords"
      :loading="loading"
      :rowsPerPageOptions="[10, 25, 50, 100]"
      paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown CurrentPageReport"
      currentPageReportTemplate="Showing {first} to {last} of {totalRecords} records"
      responsiveLayout="scroll"
      @page="onPage"
    >
      <!-- USER -->
      <Column header="#">
    <template #body="slotProps">
      {{ first + slotProps.index + 1 }}
    </template>
  </Column>
      <Column header="User">
        <template #body="{ data }">
          <div class="fw-semibold">
            {{ data.billing.user?.name || '-' }}
          </div>

          <div class="small text-body-secondary">
            {{ data.billing.user?.email || '-' }}
          </div>

          <div class="small text-body-secondary">
            {{ data.billing.user?.mobile_number || '-' }}
          </div>
        </template>
      </Column>

       <Column header="Acknowledgement" >
     <template #body="{ data }">
        {{ data?.billing?.acknowledgement_no }}
       </template>

       </Column>
       <Column header="Claim No" field="claim_no"></Column>

      <!-- PRODUCT -->

      <Column
        field="product_name"
        header="Product"
      >
       <template #body="{ data }">
        {{ data?.billing?.product?.product_name }}
       </template>
      </Column>



      <!-- BILL -->

      <Column header="Bill Details">
        <template #body="{ data }">
          <div class="fw-semibold">
            {{ data.billing?.bill_number ?? 'N/A' }}
          </div>

          <div class="small text-body-secondary">
            {{ formatDate(data?.billing?.bill_date) ?? 'N/A' }}
          </div>
        </template>
      </Column>

      <!-- SHADE -->

      <Column header="Shade">
        <template #body="{ data }">
          <div class="fw-semibold">
            {{ data?.billing?.shade_name ?? 'N/A' }}
          </div>

          <div class="small text-body-secondary">
            {{ data?.billing?.shade_code ?? 'N/A' }}
          </div>
        </template>
      </Column>

      <!-- QUANTITY -->

      <Column header="Quantity">
       <template #body="{ data }">
         {{ data?.billing?.quantity ?? 'N/A' }}
       </template>

    </Column>
       <!-- Registration Type -->

      <!-- STATUS -->


       <Column header="Action" style="width: 140px">
        <template #body="{ data }">
          <CButton
            color="warning"
            variant="outline"
            size="sm"
            class="me-2"
            @click="viewClaim(data.id)"
          >
            <CIcon :icon="cilLowVision" />
          </CButton>
          <CButton
            color="danger"
            variant="outline"
            size="sm"
            @click="deleteClaim(data.id)"
          >
            <CIcon :icon="cilTrash" />
          </CButton>

        </template>
      </Column>
    </DataTable>
  </div>
</template>
