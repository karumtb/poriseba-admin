<script setup>
import { onMounted, ref, computed } from 'vue'
import chart from '@/assets/images/chart.png'
import { CChart } from '@coreui/vue-chartjs'
import { getStyle } from '@coreui/utils'
import { loadingStore } from '@/services/loading'
import api from '@/services/adminAxios'
import { useToast } from 'vue-toastification'
import { useAuthStore } from '@/stores/auth'

import { CIcon } from '@coreui/icons-vue'
import { cilPeople,cilHospital,cilMedicalCross,cilOptions,cilCalendarCheck,cilUserPlus,cilPlus,cilFile,cilCheckCircle,cilChevronRight } from '@coreui/icons'



const toast = useToast()
const summary_data = ref(null)
const loading = computed(() => loadingStore.loading)
const authStore = useAuthStore()

const getSummary = async () => {
  try {
    const response = await api.post('/dashboard', {})
    const result = response.data.data
    summary_data.value = result
  } catch (error) {
    console.error('Designation Error:', error)
  } finally {
  }
}

onMounted(() => {
  getSummary()
})
</script>



<template>
<!-- Dashboard Content -->
<section class="pori-content">

    <!-- Welcome -->
    <div class="pori-welcome">
        <div>
            <h2>Welcome back, Admin! 👋</h2>
            <p>Here's what's happening with your healthcare platform today.</p>
        </div> 
    </div>

    <!-- Statistics -->
    <div class="row pori-stat-row">

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="pori-stat-card">
                <div class="pori-stat-top">
                    <div class="pori-stat-icon pori-icon-pink">
                        <CIcon :icon="cilPeople" />
                    </div>
                    <CIcon :icon="cilOptions" class="pori-stat-menu" />
                </div>
               
                <h3>12,845</h3>
                <div class="pori-stat-label">Total Patients</div>

                <div class="pori-stat-footer">
                    <span class="pori-growth">
                        <CIcon :icon="cilCheckCircle" /> 12.8%
                    </span>
                    <span class="pori-stat-period">vs last month</span>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="pori-stat-card">
                <div class="pori-stat-top">
                    <div class="pori-stat-icon pori-icon-blue">
                        <CIcon :icon="cilHospital" />
                    </div>
                    <CIcon :icon="cilOptions" class="pori-stat-menu" />
                </div>

                <h3>248</h3>
                <div class="pori-stat-label">Total Institutions</div>

                <div class="pori-stat-footer">
                    <span class="pori-growth">
                        <CIcon :icon="cilCheckCircle" /> 8.2%
                    </span>
                    <span class="pori-stat-period">vs last month</span>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="pori-stat-card">
                <div class="pori-stat-top">
                    <div class="pori-stat-icon pori-icon-green">
                        <CIcon :icon="cilMedicalCross" />
                    </div>
                    <CIcon :icon="cilOptions" class="pori-stat-menu" />
                </div>

                <h3>1,486</h3>
                <div class="pori-stat-label">Active Caregivers</div>

                <div class="pori-stat-footer">
                    <span class="pori-growth">
                        <CIcon :icon="cilCheckCircle" /> 6.5%
                    </span>
                    <span class="pori-stat-period">vs last month</span>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="pori-stat-card">
                <div class="pori-stat-top">
                    <div class="pori-stat-icon pori-icon-orange">
                        <CIcon :icon="cilCalendarCheck" />
                    </div>
                    <CIcon :icon="cilOptions" class="pori-stat-menu" />
                </div>

                <h3>3,642</h3>
                <div class="pori-stat-label">Total Appointments</div>

                <div class="pori-stat-footer">
                    <span class="pori-growth">
                        <CIcon :icon="cilCheckCircle" /> 15.3%
                    </span>
                    <span class="pori-stat-period">vs last month</span>
                </div>
            </div>
        </div>

    </div>

    <!-- Charts and Activity -->
    <div class="row mt-2">

        <div class="col-xl-8 col-lg-7 mb-3">
            <div class="pori-panel pori-chart-panel">

                <div class="pori-panel-header">
                    <div>
                        <h4 class="pori-panel-title">Patient Enrollment Overview</h4>
                        <div class="pori-panel-subtitle">Monthly patient registration statistics</div>
                    </div>

                    <select class="pori-select" id="poriChartPeriod">
                        <option value="six">Last 6 Months</option>
                        <option value="twelve">Last 12 Months</option>
                        <option value="year">This Year</option>
                    </select>
                </div>

                <div class="pori-chart-wrap">
                    <!-- <canvas id="poriPatientChart"></canvas>-->
                    <img :src="chart" alt="Poriseba Chart" class="chart-image">
                </div>

            </div>
        </div>

        <div class="col-xl-4 col-lg-5 mb-3">
            <div class="pori-panel pori-activity-panel">

                <div class="pori-panel-header">
                    <div>
                        <h4 class="pori-panel-title">Recent Activity</h4>
                        <div class="pori-panel-subtitle">Latest platform updates</div>
                    </div>

                    <a href="#" style="color:#E50060;font-size:10px;font-weight:600;">View All</a>
                </div>

                <div class="pori-activity-item">
                    <div class="pori-activity-icon pori-icon-pink">
                        <CIcon :icon="cilUserPlus" />
                    </div>
                    <div class="pori-activity-content">
                        <h6>New patient registered</h6>
                        <p>Rahul Sharma joined the platform.</p>
                    </div>
                    <span class="pori-activity-time">10m ago</span>
                </div>

                <div class="pori-activity-item">
                    <div class="pori-activity-icon pori-icon-blue">
                        <CIcon :icon="cilHospital" />
                    </div>
                    <div class="pori-activity-content">
                        <h6>New institution added</h6>
                        <p>City Care Hospital was added.</p>
                    </div>
                    <span class="pori-activity-time">35m ago</span>
                </div>

                <div class="pori-activity-item">
                    <div class="pori-activity-icon pori-icon-green">
                        <CIcon :icon="cilCheckCircle" />
                    </div>
                    <div class="pori-activity-content">
                        <h6>Appointment completed</h6>
                        <p>Patient appointment completed.</p>
                    </div>
                    <span class="pori-activity-time">1h ago</span>
                </div>

                <div class="pori-activity-item">
                    <div class="pori-activity-icon pori-icon-orange">
                        <CIcon :icon="cilMedicalCross" />
                    </div>
                    <div class="pori-activity-content">
                        <h6>Caregiver application</h6>
                        <p>New caregiver application received.</p>
                    </div>
                    <span class="pori-activity-time">2h ago</span>
                </div>

            </div>
        </div>

    </div>

    <!-- Dashboard Widgets -->
    <div class="row mt-2 pori-registration-layout">

        <!-- Right Side -->
        <div class="col-xl-4 col-lg-5 pori-right-widgets">

            <div class="pori-right-widget-grid">

            <!-- Quick Actions -->
            <div class="pori-panel mb-3">
                <div class="pori-panel-header">
                    <div>
                        <h4 class="pori-panel-title">Quick Actions</h4>
                        <div class="pori-panel-subtitle">Frequently used shortcuts</div>
                    </div>
                </div>

                <div class="pori-action-grid">
                    <a href="#" class="pori-action-card">
                        <CIcon :icon="cilUserPlus" />
                        <span>Add Patient</span>
                        <CIcon :icon="cilChevronRight" class="angle-right-icon" />
                    </a>

                    <a href="#" class="pori-action-card">
                        <CIcon :icon="cilHospital" />
                        <span>Add Institution</span>
                        <CIcon :icon="cilChevronRight" class="angle-right-icon" />
                    </a>

                    <a href="#" class="pori-action-card">
                        <CIcon :icon="cilMedicalCross" />
                        <span>Add Caregiver</span>
                        <CIcon :icon="cilChevronRight" class="angle-right-icon" />
                    </a>

                    <a href="#" class="pori-action-card">
                        <CIcon :icon="cilFile" />
                        <span>Generate Report</span>
                        <CIcon :icon="cilChevronRight" class="angle-right-icon" />
                    </a>
                </div>
            </div>

            <!-- Institution Overview -->
            <div class="pori-panel mb-3">
                <div class="pori-panel-header">
                    <div>
                        <h4 class="pori-panel-title">Institution Overview</h4>
                        <div class="pori-panel-subtitle">Leading institutions by patient count</div>
                    </div>
                    <a href="#" style="color:#E50060;font-size:10px;font-weight:600;">View All</a>
                </div>

                <div class="pori-institution-summary">
                    <strong>248</strong>
                    <span>Total Institutions</span>
                </div>

                <div class="pori-institution">
                    <div class="pori-institution-icon">
                        <CIcon :icon="cilHospital" />
                    </div>
                    <div class="pori-institution-info">
                        <h6>City Care Hospital</h6>
                        <p>Kolkata, West Bengal</p>
                    </div>
                    <div class="pori-institution-count">845</div>
                </div>

                <div class="pori-institution">
                    <div class="pori-institution-icon" style="background:#FFF0F6;color:#E50060;">
                        <CIcon :icon="cilMedicalCross" />
                    </div>
                    <div class="pori-institution-info">
                        <h6>Life Care Medical</h6>
                        <p>Howrah, West Bengal</p>
                    </div>
                    <div class="pori-institution-count">628</div>
                </div>

                <div class="pori-institution">
                    <div class="pori-institution-icon" style="background:#E9F8F1;color:#19A974;">
                        <CIcon :icon="cilPlus" />
                    </div>
                    <div class="pori-institution-info">
                        <h6>Wellness Healthcare</h6>
                        <p>Salt Lake, Kolkata</p>
                    </div>
                    <div class="pori-institution-count">512</div>
                </div>
            </div>

            <!-- Caregiver Distribution -->
            <div class="pori-panel mb-3">
                <div class="pori-panel-header">
                    <div>
                        <h4 class="pori-panel-title">Caregiver Distribution</h4>
                        <div class="pori-panel-subtitle">Workforce overview</div>
                    </div>
                </div>

                <div class="pori-manpower-row">
                    <div class="pori-manpower-heading">
                        <span>Caregivers</span>
                        <span>620</span>
                    </div>
                    <div class="pori-progress">
                        <div class="pori-progress-bar" style="width:85%;"></div>
                    </div>
                </div>

                <div class="pori-manpower-row">
                    <div class="pori-manpower-heading">
                        <span>Nursing Staff</span>
                        <span>420</span>
                    </div>
                    <div class="pori-progress">
                        <div class="pori-progress-bar pori-progress-blue" style="width:65%;"></div>
                    </div>
                </div>

                <div class="pori-manpower-row">
                    <div class="pori-manpower-heading">
                        <span>Physiotherapists</span>
                        <span>280</span>
                    </div>
                    <div class="pori-progress">
                        <div class="pori-progress-bar pori-progress-green" style="width:45%;"></div>
                    </div>
                </div>

                <div class="pori-manpower-row mb-0">
                    <div class="pori-manpower-heading">
                        <span>Elder Care Specialists</span>
                        <span>166</span>
                    </div>
                    <div class="pori-progress">
                        <div class="pori-progress-bar pori-progress-orange" style="width:30%;"></div>
                    </div>
                </div>
            </div>
            </div>

        </div>
    </div>
 

</section>

  <div class="position-relative"> 
    <!-- Loader -->
    <div v-if="loading" class="dashboard-loader">
      <CSpinner color="primary" />
    </div>

    <CRow :xs="{ gutter: 4 }">
      <CCol :sm="6" :xl="3" :xxl="3">
        <CWidgetStatsA color="primary">
          <template #value>
            {{ summary_data?.total_enrolment }}
          </template>

          <template #title>Total Enrolment</template>
        </CWidgetStatsA>
      </CCol>

      <CCol
        :sm="6"
        :xl="3"
        :xxl="3"
        v-if="authStore.user?.role == 'admin'"
      >
        <CWidgetStatsA color="info">
          <template #value>
            {{ summary_data?.total_institute }}
          </template>

          <template #title>Total Institution</template>
        </CWidgetStatsA>
      </CCol>

      <CCol
        :sm="6"
        :xl="3"
        :xxl="3"
        v-if="authStore.user?.role == 'admin'"
      >
        <CWidgetStatsA color="warning">
          <template #value>
            0
          </template>

          <template #title>Hiring Organisation</template>
        </CWidgetStatsA>
      </CCol>


    </CRow>
  </div>
</template>


<style scoped> 
 
        /* Content */ 

        .pori-welcome {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 23px;
        }

        .pori-welcome h2 {
            color: var(--pori-dark);
            font-size: 21px;
            font-weight: 700;
            margin: 0 0 6px;
        } 

        .pori-welcome p {
            color: var(--pori-muted);
            margin: 0;
            font-size: 11px;
        } 

        /* Cards */
        .pori-stat-card {
            background: #fff;
            border: 1px solid var(--pori-border);
            border-radius: 15px;
            padding: 21px;
            height: 100%;
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(16,25,45,0.09);
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }

        .pori-stat-card::before {
            content: "";
            position: absolute;
            width: 165px;
            height: 165px;
            top: -86px;
            right: -48px;
            border: 1px solid rgba(255,255,255,0.16);
            border-radius: 50%;
            background: rgba(255,255,255,0.08);
            pointer-events: none;
        }

        .pori-stat-card::after {
            content: "";
            position: absolute;
            width: 105px;
            height: 105px;
            top: -47px;
            right: -18px;
            border: 1px solid rgba(255,255,255,0.14);
            border-radius: 50%;
            pointer-events: none;
        }

        .pori-stat-row .pori-stat-card > * {
            position: relative;
            z-index: 1;
        }

        .pori-stat-row > div:nth-child(1) .pori-stat-card {
            background: linear-gradient(135deg, #D91563 0%, #A80D4B 100%);
            border-color: #C21056;
        }
        .pori-stat-row > div:nth-child(2) .pori-stat-card {
            background: linear-gradient(135deg, #3987D5 0%, #2362AA 100%);
            border-color: #2D73BC;
        }
        .pori-stat-row > div:nth-child(3) .pori-stat-card {
            background: linear-gradient(135deg, #20A477 0%, #11714F 100%);
            border-color: #16845D;
        }
        .pori-stat-row > div:nth-child(4) .pori-stat-card {
            background: linear-gradient(135deg, #E99A24 0%, #B96712 100%);
            border-color: #CF7C18;
        }

        .pori-stat-row .pori-stat-card h3,
        .pori-stat-row .pori-stat-label,
        .pori-stat-row .pori-stat-period {
            color: #fff;
        }

        .pori-stat-row .pori-stat-label { color: rgba(255,255,255,0.88); }
        .pori-stat-row .pori-stat-menu { color: rgba(255,255,255,0.72); }
        .pori-stat-row .pori-stat-footer { border-top-color: rgba(255,255,255,0.24); }
        .pori-stat-row .pori-growth { 
          color: #fff;
          display: flex;
          align-items: center;
          gap: 4px;
         }
        .pori-stat-row .pori-stat-icon {
            width: 47px;
            height: 47px;
            border-radius: 14px;
            background: rgba(255,255,255,0.2);
            color: #fff;
            border: 1px solid rgba(255,255,255,0.28);
            backdrop-filter: blur(4px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        .pori-stat-row .pori-stat-icon svg{
          width:1.5rem;
          height:1.5rem;
        }

        .pori-stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 15px 28px rgba(16,25,45,0.18);
        }

        .pori-stat-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
        }

        .pori-stat-icon {
            width: 43px;
            height: 43px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        .pori-icon-pink {
            background: #FFF0F6;
            color: var(--pori-primary);
        }

        .pori-icon-blue {
            background: #EDF5FF;
            color: #4786C9;
        }

        .pori-icon-green {
            background: #E9F8F1;
            color: var(--pori-green);
        }

        .pori-icon-orange {
            background: #FFF5E5;
            color: var(--pori-orange);
        }

        .pori-stat-menu {
            color: #AAB2C0;
            font-size: 16px;
        }

        .pori-stat-card h3 {
            color: var(--pori-dark);
            font-size: 28px;
            font-weight: 800;
            letter-spacing: -0.6px;
            margin: 0 0 7px;
        }

        .pori-stat-label {
            color: var(--pori-muted);
            font-size: 12px;
            font-weight: 600;
        }

        .pori-stat-footer {
            border-top: 1px solid #F0F2F5;
            margin-top: 16px;
            padding-top: 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 10px;
        }

        .pori-growth {
            color: var(--pori-green);
            font-weight: 700;
        }

        .pori-growth i {
            margin-right: 3px;
        }

        .pori-stat-period {
            color: var(--pori-muted);
        }

        /* Panels */
        .pori-panel {
            background: #fff;
            border: 1px solid var(--pori-border);
            border-radius: 11px;
            padding: 20px;
            height: 100%;
        }

        .pori-panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .pori-panel-title {
            color: var(--pori-dark);
            font-size: 14px;
            font-weight: 700;
            margin: 0;
        }

        .pori-panel-subtitle {
            color: var(--pori-muted);
            font-size: 10px;
            margin-top: 5px;
        }

        .pori-select {
            border: 1px solid var(--pori-border);
            border-radius: 6px;
            color: var(--pori-text);
            padding: 7px 9px;
            font-size: 10px;
            outline: none;
            background: #fff;
        }

        .pori-chart-wrap {
            height: 290px;
            position: relative;
        }

        .pori-chart-panel {
            position: relative;
            overflow: hidden;
            padding: 22px;
            border: 1px solid #E7EAF0;
            border-radius: 15px;
            background: linear-gradient(145deg, #FFFFFF 0%, #FFFBFD 100%);
            box-shadow: 0 8px 24px rgba(25, 39, 65, 0.055);
        }

        .pori-chart-panel::before {
            content: "";
            position: absolute;
            inset: 0 0 auto;
            height: 3px;
            background: linear-gradient(90deg, #F25599, #E50060 60%, #FDB5D2);
        }

        .pori-chart-panel .pori-panel-header {
            position: relative;
            margin-bottom: 16px;
        }

        .pori-chart-panel .pori-panel-title {
            font-size: 15px;
        }

        .pori-chart-panel .pori-panel-subtitle {
            font-size: 11px;
        }

        .pori-chart-panel .pori-select {
            border-color: #E6EAF0;
            border-radius: 8px;
            padding: 8px 11px;
            box-shadow: 0 2px 6px rgba(16, 25, 45, 0.035);
        }

        .pori-chart-panel .pori-chart-wrap {
            padding: 8px 10px 0;
            border: 1px solid #F1EDF1;
            border-radius: 12px;
            background: linear-gradient(180deg, #FFFFFF 0%, #FFFAFC 100%);
        }

        .pori-activity-panel {
            position: relative;
            overflow: hidden;
            padding: 22px;
            border: 1px solid #E7EAF0;
            border-radius: 15px;
            background: linear-gradient(145deg, #FFFFFF 0%, #F6FAFF 100%);
            box-shadow: 0 9px 26px rgba(25, 39, 65, 0.07);
        }

        .pori-activity-panel::before {
            content: "";
            position: absolute;
            inset: 0 0 auto;
            height: 3px;
            background: linear-gradient(90deg, #78AEE8, #4786C9 60%, #B8D8FA);
        }

        .pori-activity-panel .pori-panel-header {
            position: relative;
            margin-bottom: 13px;
        }

        .pori-activity-panel .pori-panel-title {
            font-size: 15px;
        }

        .pori-activity-panel .pori-panel-subtitle {
            font-size: 11px;
        }

        .pori-activity-panel .pori-panel-header a {
            padding: 7px 10px;
            border-radius: 7px;
            background: #F2F7FD;
            text-decoration: none;
        }

        .pori-activity-panel .pori-activity-item {
            position: relative;
            gap: 10px;
            padding: 13px 10px;
            border: 0;
            border-bottom: 1px solid #EEF1F5;
            border-radius: 9px;
            transition: background 0.2s ease;
        }

        .pori-activity-panel .pori-activity-item:hover {
            background: #F8FAFD;
        }

        .pori-activity-panel .pori-activity-item:not(:last-child)::before {
            content: "";
            position: absolute;
            left: 26px;
            top: 45px;
            bottom: -13px;
            width: 1px;
            background: #E4EAF2;
        }

        .pori-activity-panel .pori-activity-item:last-child {
            border-bottom: 0;
        }

        .pori-activity-panel .pori-activity-icon {
            position: relative;
            z-index: 1;
            width: 34px;
            height: 34px;
            border-radius: 10px;
            box-shadow: 0 2px 7px rgba(16,25,45,0.08);
        }

        .pori-activity-panel .pori-activity-content h6 {
            font-size: 13px;
        }

        .pori-activity-panel .pori-activity-content p {
            font-size: 10px;
        }

        .pori-activity-panel .pori-activity-time {
            padding: 4px 6px;
            border-radius: 6px;
            background: #F1F4F8;
            color: #7E8999;
            font-size: 10px;
        }

        /* Activity */
        .pori-activity-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 13px 0;
            border-bottom: 1px solid #F0F2F5;
        }

        .pori-activity-item:last-child {
            border-bottom: 0;
        }

        .pori-activity-icon {
            width: 35px;
            height: 35px;
            flex-shrink: 0;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }

        .pori-activity-content {
            flex: 1;
        }

        .pori-activity-content h6 {
            color: var(--pori-dark);
            font-size: 11px;
            font-weight: 600;
            margin: 2px 0 5px;
        }

        .pori-activity-content p {
            color: var(--pori-muted);
            font-size: 10px;
            margin: 0;
            line-height: 1.5;
        }

        .pori-activity-time {
            color: #AAB2C0;
            font-size: 9px;
            white-space: nowrap;
        }

        /* Table */
        .pori-table {
            margin-bottom: 0;
        }

        .pori-table thead th {
            border-top: 0;
            border-bottom: 1px solid var(--pori-border);
            color: #8993A4;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 12px 10px;
            white-space: nowrap;
        }

        .pori-table tbody td {
            border-top: 1px solid #F1F3F6;
            color: var(--pori-text);
            font-size: 10px;
            padding: 13px 10px;
            vertical-align: middle;
            white-space: nowrap;
        }

        .pori-patient {
            display: flex;
            align-items: center;
            gap: 9px;
        }

        .pori-patient-avatar {
            width: 31px;
            height: 31px;
            border-radius: 50%;
            background: #EDF5FF;
            color: #4786C9;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: 700;
        }

        .pori-patient-name {
            color: var(--pori-dark);
            font-weight: 600;
            font-size: 10px;
        }

        .pori-patient-id {
            color: var(--pori-muted);
            font-size: 9px;
            margin-top: 3px;
        }

        .pori-status {
            display: inline-block;
            padding: 5px 9px;
            border-radius: 20px;
            font-size: 9px;
            font-weight: 600;
        }

        .pori-status-active {
            color: #138A5A;
            background: #E8F8F0;
        }

        .pori-status-pending {
            color: #C27A0B;
            background: #FFF5E5;
        }

        .pori-status-inactive {
            color: #D04466;
            background: #FFF0F3;
        }

        .pori-table-action {
            color: #929BAC;
            font-size: 15px;
        }

        

        /* Responsive */
        @media (max-width: 1199px) {
            .pori-sidebar {
                width: 220px;
            }

            .pori-main {
                margin-left: 220px;
            }

            .pori-content {
                padding: 22px 20px;
            }

            .pori-topbar {
                padding: 0 20px;
            }
        }

        @media (max-width: 991px) {
            .pori-sidebar {
                transform: translateX(-100%);
            }

            .pori-sidebar.open {
                transform: translateX(0);
            }

            .pori-main {
                margin-left: 0;
            }

            .pori-mobile-toggle {
                display: block;
            }

            .pori-search {
                width: 180px;
            }
        }

        @media (max-width: 575px) {
            .pori-topbar {
                height: 65px;
                padding: 0 14px;
            }

            .pori-content {
                padding: 20px 13px;
            }

            .pori-search {
                display: none;
            }

            .pori-profile-details {
                display: none;
            }

            .pori-topbar-right {
                gap: 15px;
            }

            .pori-welcome {
                align-items: flex-start;
                flex-direction: column;
                gap: 14px;
            }

            .pori-welcome h2 {
                font-size: 18px;
            }

            

            .pori-panel {
                padding: 15px;
            }

            .pori-chart-wrap {
                height: 245px;
            }
        }







.pori-registration-layout {
    flex-direction: column;
}

.pori-registration-layout > .pori-right-widgets {
    flex: 0 0 100%;
    max-width: 100%;
    order: 1;
    width:100%;
}

.pori-right-widget-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 14px;
    align-items: start;
}

.pori-right-widget-grid > .pori-panel {
    margin-bottom: 0 !important;
}

.pori-right-widgets .pori-panel {
    position: relative;
    overflow: hidden;
    height: 100%;
    padding: 18px;
    border: 1px solid #E6EAF0;
    border-radius: 15px;
    background: linear-gradient(145deg, #FFFFFF 0%, #FCFCFE 100%);
    box-shadow: 0 8px 24px rgba(25, 39, 65, 0.055);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.pori-right-widgets .pori-panel::before {
    content: "";
    position: absolute;
    inset: 0 0 auto;
    height: 3px;
    background: linear-gradient(90deg, #F25599, #E50060 60%, #FDB5D2);
    pointer-events: none;
}

.pori-right-widget-grid > .pori-panel:nth-child(2)::before {
    background: linear-gradient(90deg, #78AEE8, #4786C9 60%, #B8D8FA);
}

.pori-right-widget-grid > .pori-panel:nth-child(3)::before {
    background: linear-gradient(90deg, #49C99B, #19A974 60%, #A5E8CB);
}

.pori-right-widget-grid > .pori-panel:nth-child(1) {
    background: linear-gradient(145deg, #FFFFFF 0%, #FFFAFC 100%);
}

.pori-right-widget-grid > .pori-panel:nth-child(2) {
    background: linear-gradient(145deg, #FFFFFF 0%, #F8FBFF 100%);
}

.pori-right-widget-grid > .pori-panel:nth-child(3) {
    background: linear-gradient(145deg, #FFFFFF 0%, #F7FCF9 100%);
}

.pori-right-widgets .pori-panel:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 28px rgba(25, 39, 65, 0.09);
}

.pori-right-widgets .pori-panel-header {
    position: relative;
    margin-bottom: 11px;
}

.pori-right-widgets .pori-panel-title {
    color: var(--pori-dark);
    font-size: 14px;
    font-weight: 700;
    margin: 0;
}

.pori-right-widgets .pori-panel-subtitle {
    color: var(--pori-muted);
    font-size: 11px;
    line-height: 1.5;
    margin-top: 5px;
}

.pori-right-widgets .pori-panel-header > a {
    display: inline-flex;
    align-items: center;
    color: var(--pori-primary) !important;
    font-size: 10px;
    font-weight: 600;
    white-space: nowrap;
    text-decoration: none;
}

.pori-right-widgets .pori-panel-header > a:hover {
    color: #B8004D !important;
}

/* Quick actions */
.pori-right-widgets .pori-action-grid {
    display: flex;
    flex-direction: column;
}

.pori-right-widgets .pori-action-card {
    display: flex;
    align-items: center;
    gap: 11px;
    min-height: 48px;
    margin: 5px 0;
    padding: 7px 9px;
    border: 1px solid #EDF0F5;
    border-radius: 10px;
    background: rgba(255, 255, 255, 0.82);
    color: var(--pori-text);
    text-align: left;
    text-decoration: none !important;
    transition: 0.2s ease;
}

.pori-right-widgets .pori-action-card:last-child {
    margin-bottom: 0;
}

.pori-right-widgets .pori-action-card > i, .pori-right-widgets .pori-action-card > svg {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 33px;
    height: 33px;
    flex: 0 0 33px;
    margin: 0;
    border-radius: 9px;
    background: #fff0f6;
    color: #e50060;
    font-size: 14px;
    padding:7px;
}
.pori-right-widgets .pori-action-card > svg.angle-right-icon{
    width: 24px;
    height: 24px;
    border-radius: 4px;
    padding: 6px;
    flex-shrink: 0;
    flex: initial;
}
.pori-right-widgets .pori-action-card:nth-child(2) > i,
.pori-right-widgets .pori-action-card:nth-child(2) > svg {
    color: #4786C9;
    background: #EDF5FF;
}
.pori-right-widgets .pori-action-card:nth-child(3) > i,
.pori-right-widgets .pori-action-card:nth-child(3) > svg {
    color: var(--pori-green);
    background: #E9F8F1;
}
.pori-right-widgets .pori-action-card:nth-child(4) > i,
.pori-right-widgets .pori-action-card:nth-child(4) > svg {
    color: var(--pori-purple);
    background: #F1EEFF;
}

.pori-right-widgets .pori-action-card > span {
    flex: 1;
    font-size: 12px;
    line-height: 1.35;
    font-weight:500;
}

/*.pori-right-widgets .pori-action-card::after {
    content: "\f105";
    color: #A2ADBB;
    font: normal normal normal 16px/1 FontAwesome;
}*/

.pori-right-widgets .pori-action-card:hover {
    transform: translateX(2px);
    border-color: #F2C6D8;
    background: #FFFAFC;
    color: var(--pori-primary);
    box-shadow: 0 4px 10px rgba(229, 0, 96, 0.07);
}

/* Institution overview */
.pori-right-widgets .pori-institution-summary {
    position: relative;
    overflow: hidden;
    padding: 15px 16px;
    margin: 0 0 11px;
    border: 0;
    border-radius: 12px;
    background: linear-gradient(135deg, #6F62D8 0%, #5145B5 100%);
    box-shadow: 0 6px 14px rgba(81, 69, 181, 0.18);
}

.pori-right-widgets .pori-institution-summary::after {
    content: "";
    position: absolute;
    width: 100px;
    height: 100px;
    top: -46px;
    right: -20px;
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: 50%;
    background: rgba(255,255,255,0.08);
    pointer-events: none;
}

.pori-right-widgets .pori-institution-summary strong {
    display: block;
    margin-bottom: 4px;
    color: #FFFFFF;
    font-size: 25px;
    line-height: 1.1;
}

.pori-right-widgets .pori-institution-summary span {
    color: rgba(255,255,255,0.82);
    font-size: 11px;
}

.pori-right-widgets .pori-institution {
    display: flex;
    align-items: center;
    gap: 9px;
    padding: 8px;
    margin: 5px 0;
    border: 1px solid #EEF1F5;
    border-radius: 10px;
    background: rgba(255,255,255,0.8);
}

.pori-right-widgets .pori-institution:last-child {
    padding-bottom: 2px;
    border-bottom: 0;
}

.pori-right-widgets .pori-institution-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    flex: 0 0 34px;
    border-radius: 10px;
    background: #EDF5FF;
    color: #4786C9;
    font-size: 15px;
}

.pori-right-widgets .pori-institution-info {
    flex: 1;
    min-width: 0;
}

.pori-right-widgets .pori-institution-info h6 {
    margin: 0 0 4px;
    color: var(--pori-dark);
    font-size: 11px;
    font-weight: 700;
}

.pori-right-widgets .pori-institution-info p {
    margin: 0;
    color: var(--pori-muted);
    font-size: 10px;
}

.pori-right-widgets .pori-institution-count {
    min-width: 43px;
    padding: 6px 8px;
    border-radius: 8px;
    background: #F1F4F8;
    color: var(--pori-dark);
    text-align: center;
    font-size: 12px;
    font-weight: 700;
}

.pori-right-widgets .pori-institution-count::after {
    display: block;
    margin-top: 2px;
    color: var(--pori-muted);
    content: "patients";
    font-size: 7px;
    font-weight: 500;
}

/* Caregiver distribution */
.pori-right-widgets .pori-manpower-row {
    padding: 9px 10px 10px;
    margin-bottom: 8px;
    border: 1px solid #EDF0F5;
    border-radius: 10px;
    background: rgba(255,255,255,0.82);
}

.pori-right-widgets .pori-manpower-heading {
    display: flex;
    justify-content: space-between;
    margin-bottom: 6px;
    font-size: 12px;
}

.pori-right-widgets .pori-manpower-heading span:first-child {
    color: var(--pori-text);
    font-weight: 600;
}

.pori-right-widgets .pori-manpower-heading span:last-child {
    color: var(--pori-dark);
    font-weight: 700;
}

.pori-right-widgets .pori-progress {
    height: 8px;
    overflow: hidden;
    border-radius: 20px;
    background: #F1F3F7;
}

.pori-right-widgets .pori-progress-bar {
    height: 100%;
    border-radius: 20px;
    background: linear-gradient(90deg, #F25599, #E50060);
}

.pori-right-widgets .pori-progress-blue { background: linear-gradient(90deg, #75AFE9, #3980C8); }
.pori-right-widgets .pori-progress-green { background: linear-gradient(90deg, #53C99A, #159869); }
.pori-right-widgets .pori-progress-orange { background: linear-gradient(90deg, #F9BE62, #E68A18); }

@media (min-width: 576px) and (max-width: 991px) {
    .pori-right-widget-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 575px) {
    .pori-right-widget-grid {
        grid-template-columns: 1fr;
    }

    .pori-right-widgets .pori-panel {
        padding: 16px;
    }
}

</style>

