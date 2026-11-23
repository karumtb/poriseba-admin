<template>
  <div class="org-candidate-page">
    <div v-if="loading" class="profile-state">
      <span class="profile-spinner"></span>
      Loading candidate profile...
    </div>

    <div v-else-if="errorMessage" class="profile-state profile-error">
      {{ errorMessage }}
      <button type="button" class="profile-retry" @click="loadCandidate">Retry</button>
    </div>

    <template v-else-if="candidate">
      <header class="org-profile-header">
        <div class="org-heading">
          <div class="org-heading-icon">▣</div>
          <div>
            <h1>Candidate Profile</h1>
            <p>Organization View</p>
          </div>
        </div>
        <span class="verified-badge" v-if="isVerified">✓ Verified Profile</span>
        <span class="pending-badge" v-else>Verification Pending</span>
      </header>

      <main class="org-profile-card">
        <section class="candidate-intro">
          <img
            v-if="photoUrl"
            :src="photoUrl"
            class="candidate-photo"
            alt="Candidate profile"
            @error="photoFailed = true"
          />
          <div v-else class="candidate-photo candidate-placeholder">
            {{ initials }}
          </div>

          <div class="candidate-main">
            <div class="candidate-code">{{ value(candidate.caregiver_code, candidate.candidate_code) }}</div>
            <h2>{{ fullName }}</h2>
            <p class="candidate-designation">{{ designationName }}</p>
            <p class="candidate-meta">{{ ageLabel }} <span v-if="genderLabel">| {{ genderLabel }}</span></p>
            <p class="candidate-meta">⌖ {{ locationLabel }}</p>
          </div>
        </section>

        <div class="profile-tabs" role="tablist" aria-label="Candidate information">
          <button
            v-for="tab in tabs"
            :key="tab.key"
            type="button"
            :class="{ active: activeTab === tab.key }"
            @click="activeTab = tab.key"
          >{{ tab.label }}</button>
        </div>

        <section v-if="activeTab === 'overview'" class="profile-content">
          <div class="profile-grid">
            <article class="info-panel">
              <h3>Qualification</h3>
              <div class="info-row"><span>Highest Qualification</span><strong>{{ value(candidate.highest_qualification) }}</strong></div>
              <div class="info-row"><span>Experience</span><strong>{{ experienceLabel }}</strong></div>
              <div class="info-row"><span>Skills / Specialization</span><strong>{{ value(candidate.specialization) }}</strong></div>
              <div class="info-row"><span>Primary Role</span><strong>{{ value(candidate.primary_role, designationName) }}</strong></div>
            </article>

            <article class="info-panel">
              <h3>Professional Summary</h3>
              <p class="summary-text">{{ value(candidate.professional_summary, 'Professional summary has not been added.') }}</p>
              <h3 class="subheading">Preferred Duty Type</h3>
              <div class="chip-list">
                <span v-for="item in preferredDutyTypes" :key="item" class="soft-chip">{{ item }}</span>
                <span v-if="!preferredDutyTypes.length" class="muted">Not specified</span>
              </div>
              <h3 class="subheading">Preferred Location</h3>
              <div class="chip-list">
                <span v-for="item in preferredLocations" :key="item" class="soft-chip">{{ item }}</span>
                <span v-if="!preferredLocations.length" class="muted">{{ locationLabel }}</span>
              </div>
            </article>

            <article class="info-panel">
              <h3>Languages</h3>
              <div class="info-row"><span>Preferred Language</span><strong>{{ relationName(candidate.language, candidate.preferred_language, candidate.language_name) }}</strong></div>
              <div class="info-row"><span>Other Languages</span><strong>{{ value(candidate.languages) }}</strong></div>
            </article>

            <article class="info-panel">
              <h3>Availability</h3>
              <div class="status-line"><span class="status-dot"></span>{{ value(candidate.availability_status, 'Availability not updated') }}</div>
              <div class="info-row"><span>Career Status</span><strong>{{ value(candidate.career_status) }}</strong></div>
              <div class="info-row"><span>Placement Status</span><strong>{{ value(candidate.placement_status) }}</strong></div>
            </article>
          </div>
        </section>

        <section v-else-if="activeTab === 'personal'" class="profile-content">
          <div class="profile-grid">
            <article class="info-panel">
              <h3>Personal Information</h3>
              <div class="info-row"><span>First Name</span><strong>{{ value(candidate.first_name) }}</strong></div>
              <div class="info-row"><span>Middle Name</span><strong>{{ value(candidate.middle_name) }}</strong></div>
              <div class="info-row"><span>Last Name</span><strong>{{ value(candidate.last_name) }}</strong></div>
              <div class="info-row"><span>Date of Birth</span><strong>{{ dateLabel(candidate.date_of_birth) }}</strong></div>
              <div class="info-row"><span>Gender</span><strong>{{ genderLabel }}</strong></div>
              <div class="info-row"><span>Religion</span><strong>{{ relationName(candidate.religion, candidate.preferred_religion, candidate.religion_name) }}</strong></div>
            </article>
            <article class="info-panel">
              <h3>Contact Information</h3>
              <div class="info-row"><span>Email</span><strong>{{ value(candidate.email) }}</strong></div>
              <div class="info-row"><span>Phone</span><strong>{{ value(candidate.phone) }}</strong></div>
              <div class="info-row"><span>Alternate Phone</span><strong>{{ value(candidate.alternate_phone) }}</strong></div>
              <div class="info-row"><span>Address</span><strong>{{ addressLabel }}</strong></div>
              <div class="info-row"><span>Emergency Contact</span><strong>{{ value(candidate.emergency_contact_name) }}</strong></div>
              <div class="info-row"><span>Emergency Number</span><strong>{{ value(candidate.emergency_contact_number) }}</strong></div>
            </article>
          </div>
        </section>

        <section v-else-if="activeTab === 'experience'" class="profile-content">
          <div class="profile-grid">
            <article class="info-panel">
              <h3>Experience & Skills</h3>
              <div class="info-row"><span>Experience</span><strong>{{ experienceLabel }}</strong></div>
              <div class="info-row"><span>Designation</span><strong>{{ designationName }}</strong></div>
              <div class="info-row"><span>Specialization</span><strong>{{ value(candidate.specialization) }}</strong></div>
              <div class="info-row"><span>Primary Role</span><strong>{{ value(candidate.primary_role) }}</strong></div>
              <p class="summary-text">{{ value(candidate.professional_summary, 'No professional summary available.') }}</p>
            </article>
            <article class="info-panel">
              <h3>Placement</h3>
              <div class="info-row"><span>Placement Location</span><strong>{{ value(candidate.placement_location) }}</strong></div>
              <div class="info-row"><span>Placement Start</span><strong>{{ dateLabel(candidate.placement_start) }}</strong></div>
              <div class="info-row"><span>Placement End</span><strong>{{ dateLabel(candidate.placement_end) }}</strong></div>
            </article>
          </div>
        </section>

        <section v-else-if="activeTab === 'verification'" class="profile-content">
          <div class="profile-grid">
            <article class="info-panel">
              <h3>Verification Status</h3>
              <div class="status-line" :class="{ 'status-good': isVerified }">{{ value(candidate.verification_status, 'Pending') }}</div>
              <div class="info-row"><span>Verification Remark</span><strong>{{ value(candidate.verification_remark) }}</strong></div>
              <div class="info-row"><span>Verified On</span><strong>{{ dateLabel(candidate.verified_at) }}</strong></div>
            </article>
            <article class="info-panel">
              <h3>Documents</h3>
              <a v-if="documentUrl('adhar_pdf')" :href="documentUrl('adhar_pdf')" target="_blank" rel="noopener">View identity document ↗</a>
              <span v-else class="muted">Identity document not available</span>
              <a v-if="documentUrl('certificate_pdf')" :href="documentUrl('certificate_pdf')" target="_blank" rel="noopener">View qualification certificate ↗</a>
              <span v-else class="muted">Certificate not available</span>
            </article>
          </div>
        </section>

        <footer class="org-profile-footer">
          <span>Candidate ID: {{ value(candidate.caregiver_code, candidate.candidate_code, candidate.id) }}</span>
          <button type="button" class="shortlist-button" @click="emit('shortlist', candidate)">♡ Save to Shortlist</button>
        </footer>
      </main>
    </template>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import api from '@/services/adminAxios';

const props = defineProps({
  candidateId: { type: [String, Number], default: null },
  candidate: { type: Object, default: null },
});
const emit = defineEmits(['shortlist', 'loaded', 'error']);
const route = useRoute();

const loading = ref(false);
const errorMessage = ref('');
const candidateData = ref(null);
const activeTab = ref('overview');
const photoFailed = ref(false);

const tabs = [
  { key: 'overview', label: 'Overview' },
  { key: 'personal', label: 'Personal Info' },
  { key: 'experience', label: 'Experience' },
  { key: 'verification', label: 'Verification' },
];

const candidate = computed(() => props.candidate || candidateData.value);
const value = (...values) => {
  const found = values.find(v => v !== null && v !== undefined && v !== '');
  return found === undefined ? '—' : found;
};
const fullName = computed(() => [candidate.value?.first_name, candidate.value?.middle_name, candidate.value?.last_name].filter(Boolean).join(' ') || 'Candidate Name');
const initials = computed(() => fullName.value.split(/\s+/).slice(0, 2).map(x => x[0]).join('').toUpperCase());
const designationName = computed(() => candidate.value?.designation?.name || candidate.value?.designation_name || candidate.value?.designation || 'Caregiver');
const genderLabel = computed(() => ({ M: 'Male', F: 'Female', O: 'Other' }[candidate.value?.gender] || candidate.value?.gender || '—'));
const ageLabel = computed(() => candidate.value?.age ? `${candidate.value.age} Years` : (candidate.value?.date_of_birth ? `${calculateAge(candidate.value.date_of_birth)} Years` : 'Age not provided'));
const locationLabel = computed(() => [candidate.value?.city, candidate.value?.state].filter(Boolean).join(', ') || 'Location not provided');
const addressLabel = computed(() => [candidate.value?.address, candidate.value?.city, candidate.value?.district, candidate.value?.state, candidate.value?.pin_code].filter(Boolean).join(', ') || '—');
const experienceLabel = computed(() => candidate.value?.experience ? `${candidate.value.experience} Years` : 'Not specified');
const isVerified = computed(() => ['verified', 'approved', 'complete', 'completed', 'V', 'A'].includes(String(candidate.value?.verification_status || '').toLowerCase()) || Boolean(candidate.value?.verified_at));
const photoUrl = computed(() => {
  if (photoFailed.value) return '';
  return candidate.value?.profilePreviewUrl || candidate.value?.profile_photo_url || candidate.value?.profile_photo || candidate.value?.photo_url || '';
});
const preferredDutyTypes = computed(() => toList(candidate.value?.preferred_duty_types));
const preferredLocations = computed(() => toList(candidate.value?.preferred_locations));

function toList(input) {
  if (Array.isArray(input)) return input.map(x => typeof x === 'object' ? (x.label || x.name || '') : String(x)).filter(Boolean);
  if (typeof input === 'string' && input.trim()) {
    try {
      const parsed = JSON.parse(input);
      if (Array.isArray(parsed)) return toList(parsed);
    } catch { return input.split(',').map(x => x.trim()).filter(Boolean); }
  }
  return [];
}
function relationName(...items) {
  for (const item of items) {
    if (item && typeof item === 'object') return item.name || item.label || '—';
    if (item) return item;
  }
  return '—';
}
function calculateAge(date) {
  const birth = new Date(date);
  if (Number.isNaN(birth.getTime())) return '—';
  const today = new Date();
  let age = today.getFullYear() - birth.getFullYear();
  if (today.getMonth() < birth.getMonth() || (today.getMonth() === birth.getMonth() && today.getDate() < birth.getDate())) age--;
  return age;
}
function dateLabel(date) {
  if (!date) return '—';
  const parsed = new Date(date);
  return Number.isNaN(parsed.getTime()) ? date : parsed.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
}
function documentUrl(field) {
  const c = candidate.value || {};
  const key = field === 'adhar_pdf' ? (c.aadhaarPreviewUrl || c.adhar_pdf_url || c.adhar_pdf) : (c.certificatePreviewUrl || c.certificate_pdf_url || c.certificate_pdf);
  if (!key || typeof key !== 'string') return '';
  return key.startsWith('http') || key.startsWith('/') ? key : '';
}
async function loadCandidate() {
  if (props.candidate) {
    candidateData.value = props.candidate;
    return;
  }
  const id = props.candidateId || route.params.id || route.params.candidateId || route.params.caregiver_code;
  if (!id) {
    errorMessage.value = 'Candidate ID was not provided.';
    return;
  }
  loading.value = true;
  errorMessage.value = '';
  photoFailed.value = false;
  try {
    const response = await api.post('/enrolment/view', { id });
    if (response.data?.status && response.data?.data) {
      candidateData.value = response.data.data;
      emit('loaded', candidateData.value);
    } else {
      throw new Error(response.data?.message || 'Candidate profile could not be loaded.');
    }
  } catch (error) {
    errorMessage.value = error.response?.data?.message || error.message || 'Unable to load candidate profile.';
    emit('error', error);
  } finally {
    loading.value = false;
  }
}
watch(() => [props.candidateId, props.candidate], loadCandidate);
onMounted(loadCandidate);
</script>

<style scoped>
.org-candidate-page{max-width:1050px;margin:0 auto;padding:18px;color:#25364b;font-family:inherit}
.org-profile-header{display:flex;align-items:center;justify-content:space-between;gap:12px;background:#123f6d;color:#fff;padding:15px 20px;border-radius:9px 9px 0 0}
.org-heading{display:flex;align-items:center;gap:12px}.org-heading-icon{width:34px;height:34px;border:1px solid #ffffff70;border-radius:7px;display:grid;place-items:center;font-size:20px}
.org-heading h1{font-size:17px;margin:0;font-weight:700}.org-heading p{font-size:11px;margin:2px 0 0;color:#d8e7f7}
.verified-badge,.pending-badge{font-size:11px;font-weight:700;padding:6px 10px;border-radius:20px;background:#e3f7ef;color:#087c57;white-space:nowrap}.pending-badge{background:#fff4dc;color:#986000}
.org-profile-card{background:#fff;border:1px solid #dce5ef;border-top:0;border-radius:0 0 9px 9px;box-shadow:0 5px 20px #173b5b0d}
.candidate-intro{display:flex;align-items:center;gap:16px;padding:20px 22px 16px}.candidate-photo{width:92px;height:92px;object-fit:cover;border-radius:8px;border:1px solid #e1e8f0}.candidate-placeholder{display:grid;place-items:center;background:#eaf1f8;color:#164c7b;font-size:25px;font-weight:700}
.candidate-main{min-width:0}.candidate-code{font-size:12px;font-weight:800;color:#164b7a}.candidate-main h2{font-size:19px;margin:3px 0 5px;color:#1e2f45}.candidate-designation,.candidate-meta{font-size:12px;margin:3px 0;color:#536477}.candidate-designation{font-weight:600;color:#30445b}
.profile-tabs{display:flex;gap:3px;overflow-x:auto;padding:0 16px;border-bottom:1px solid #dce5ef;background:#f8fafc}.profile-tabs button{border:0;background:transparent;padding:12px 15px;color:#536477;font-size:12px;font-weight:600;white-space:nowrap;border-bottom:3px solid transparent;cursor:pointer}.profile-tabs button.active{color:#1265ad;border-bottom-color:#1680d3;background:#fff}
.profile-content{padding:18px}.profile-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:13px}.info-panel{border:1px solid #e1e8f0;border-radius:7px;padding:14px;min-width:0}.info-panel h3{font-size:13px;font-weight:800;margin:0 0 12px;color:#1d3d5c}.info-panel h3.subheading{margin-top:15px;margin-bottom:8px}
.info-row{display:flex;justify-content:space-between;gap:12px;padding:7px 0;border-bottom:1px solid #eff2f6;font-size:11px}.info-row:last-child{border-bottom:0}.info-row span{color:#6b7888;flex:0 0 44%}.info-row strong{text-align:right;color:#273b51;font-weight:600;overflow-wrap:anywhere}
.summary-text{font-size:11px;line-height:1.65;color:#566779;margin:0}.chip-list{display:flex;gap:6px;flex-wrap:wrap}.soft-chip{font-size:10px;padding:5px 8px;border-radius:5px;background:#eaf3fc;color:#23639a}.muted{font-size:11px;color:#8793a1}.status-line{font-size:11px;color:#25835d;display:flex;align-items:center;gap:7px}.status-dot{width:8px;height:8px;border-radius:50%;background:#26ad78}.status-good{font-weight:700;color:#15805a}
.info-panel a{display:block;font-size:11px;color:#1768a9;margin:9px 0;text-decoration:none}.org-profile-footer{border-top:1px solid #e6ecf2;padding:13px 18px;display:flex;align-items:center;justify-content:space-between;gap:12px;font-size:11px;color:#7b8794}.shortlist-button{border:1px solid #d84d68;background:#fff;color:#c83d5b;padding:8px 13px;border-radius:6px;font-size:11px;font-weight:700;cursor:pointer}.shortlist-button:hover{background:#fff4f6}
.profile-state{padding:35px;text-align:center;color:#5e6e80;background:#fff;border:1px solid #e1e8f0;border-radius:8px}.profile-error{color:#b42318}.profile-retry{margin-left:10px;border:0;background:#155d91;color:#fff;border-radius:5px;padding:7px 12px}.profile-spinner{display:inline-block;width:17px;height:17px;border:2px solid #c9d7e5;border-top-color:#1768a9;border-radius:50%;vertical-align:middle;margin-right:8px;animation:spin .8s linear infinite}@keyframes spin{to{transform:rotate(360deg)}}
@media(max-width:640px){.org-candidate-page{padding:8px}.org-profile-header{padding:12px}.candidate-intro{padding:15px;align-items:flex-start}.candidate-photo{width:72px;height:72px}.candidate-main h2{font-size:16px}.profile-grid{grid-template-columns:1fr}.profile-content{padding:12px}.info-row{font-size:10px}.profile-tabs button{padding:11px 10px}.org-profile-footer{align-items:flex-start;flex-direction:column}}
</style>
