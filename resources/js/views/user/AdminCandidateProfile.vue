<template>
  <div class="admin-candidate-profile">
    <div class="admin-profile-banner">
      <div class="banner-title">
        <div class="banner-avatar-icon">♙</div>
        <div>
          <h2>Candidate Profile (Admin View)</h2>
          <p>{{ breadcrumb }}</p>
        </div>
      </div>
      <button class="edit-profile-btn" type="button" @click="$emit('edit', candidate)">✎ Edit Profile</button>
    </div>

    <div v-if="!candidate" class="profile-empty">No candidate information available.</div>

    <template v-else>
      <section class="admin-profile-summary">
        <img v-if="photoUrl" :src="photoUrl" class="admin-profile-photo" alt="Candidate photo" @error="photoFailed = true" />
        <div v-else class="admin-profile-photo photo-placeholder">{{ initials }}</div>

        <div class="summary-identity">
          <strong class="candidate-code">{{ get('caregiver_code', 'candidate_code') }}</strong>
          <h1>{{ fullName }}</h1>
          <p>
            ♟ {{ designationName }} <span v-if="candidate.profile_type"> · {{ candidate.profile_type }}</span>
          </p>
          <p>
            ♙ Age: {{ ageLabel }} <span v-if="genderLabel"> | {{ genderLabel }}</span>
          </p>
          <p>⌖ {{ locationLabel }}</p>
        </div>

        <div class="summary-status">
          <span class="status-pill" :class="isActive ? 'active' : 'inactive'">{{ isActive ? 'Active' : 'Pending' }}</span>
        </div>

        <div class="summary-progress">
          <div class="summary-progress-row">
            <strong>Career Status</strong><span class="status-chip green">{{ label(candidate.career_status, 'Available') }}</span>
          </div>
          <div class="summary-progress-row">
            <strong>Placement Status</strong
            ><span class="status-chip blue">{{ label(candidate.placement_status, 'Looking for Placement') }}</span>
          </div>
          <strong class="completion-title">Profile Completion</strong>
          <div class="progress-track"><span :style="{ width: completion + '%' }"></span></div>
          <small>{{ completion }}%</small>
        </div>
      </section>

      <nav class="admin-profile-tabs" aria-label="Candidate profile sections">
        <button v-for="tab in tabs" :key="tab.key" type="button" :class="{ selected: activeTab === tab.key }" @click="activeTab = tab.key">
          {{ tab.label }}
        </button>
      </nav>

      <section v-if="activeTab === 'personal'" class="admin-profile-content three-columns">
        <article class="profile-info-card">
          <h3>Basic Information</h3>
          <InfoRow label="First Name" :value="candidate.first_name" />
          <InfoRow label="Last Name" :value="candidate.last_name" />
          <InfoRow label="Gender" :value="genderLabel" />
          <InfoRow label="Date of Birth" :value="dateLabel(candidate.date_of_birth)" />
          <InfoRow label="Age" :value="ageLabel" />
          <InfoRow label="Mobile (Primary)" :value="candidate.phone" />
          <InfoRow label="Mobile (Secondary)" :value="candidate.alternate_phone" />
          <InfoRow label="Email" :value="candidate.email" />
          <InfoRow label="Emergency Contact" :value="candidate.emergency_contact_name" />
          <InfoRow label="Emergency Number" :value="candidate.emergency_contact_number" />
        </article>

        <article class="profile-info-card">
          <h3>Address</h3>
          <InfoRow label="Address Line 1" :value="candidate.address" />
          <InfoRow label="Locality" :value="candidate.area?.name || candidate.locality || candidate.city" />
          <InfoRow label="City" :value="candidate.city" />
          <InfoRow label="District" :value="candidate.district" />
          <InfoRow label="State" :value="candidate.state" />
          <InfoRow label="PIN" :value="candidate.pin_code" />
          <InfoRow label="Preferred Service City" :value="candidate.preferred_service_city || candidate.city" />
          <InfoRow label="Preferred Areas" :value="listText(candidate.preferred_locations)" />
        </article>

        <article class="profile-info-card">
          <h3>Professional Summary</h3>
          <p class="professional-summary">{{ label(candidate.professional_summary, 'No professional summary has been added.') }}</p>
          <h3 class="inner-heading">Languages</h3>
          <div v-for="language in languageItems" :key="language.name" class="language-row">
            <span>{{ language.name }}</span
            ><b :class="['language-level', language.level.toLowerCase()]">{{ language.level }}</b>
          </div>
          <p v-if="!languageItems.length" class="muted">No languages added.</p>
        </article>
      </section>

      <section v-else-if="activeTab === 'education'" class="admin-profile-content two-columns">
        <article class="profile-info-card">
          <h3>Education</h3>
          <InfoRow label="Highest Qualification" :value="candidate.highest_qualification" />
          <InfoRow label="Qualification Details" :value="candidate.qualification_details" />
          <InfoRow label="Institution" :value="candidate.institution_name" />
        </article>
        <article class="profile-info-card">
          <h3>Additional Information</h3>
          <InfoRow label="Specialization" :value="candidate.specialization" />
          <InfoRow label="Religion" :value="candidate.religion?.name || candidate.religion_name" />
          <InfoRow label="Preferred Language" :value="candidate.language?.name || candidate.language_name" />
        </article>
      </section>

      <section v-else-if="activeTab === 'skills'" class="admin-profile-content two-columns">
        <article class="profile-info-card">
          <h3>Skills & Specialization</h3>
          <InfoRow label="Designation" :value="designationName" />
          <InfoRow label="Primary Role" :value="candidate.primary_role" />
          <InfoRow label="Specialization" :value="candidate.specialization" />
          <InfoRow label="Experience" :value="experienceLabel" />
        </article>
        <article class="profile-info-card">
          <h3>Professional Summary</h3>
          <p class="professional-summary">{{ label(candidate.professional_summary) }}</p>
        </article>
      </section>

      <section v-else-if="activeTab === 'experience'" class="admin-profile-content two-columns">
        <article class="profile-info-card">
          <h3>Experience</h3>
          <InfoRow label="Total Experience" :value="experienceLabel" />
          <InfoRow label="Previous Employer" :value="candidate.previous_employer" />
          <InfoRow label="Career Status" :value="candidate.career_status" />
        </article>
        <article class="profile-info-card">
          <h3>Placement History</h3>
          <InfoRow label="Placement Location" :value="candidate.placement_location" />
          <InfoRow label="Placement Start" :value="dateLabel(candidate.placement_start)" />
          <InfoRow label="Placement End" :value="dateLabel(candidate.placement_end)" />
        </article>
      </section>

      <section v-else-if="activeTab === 'verification'" class="admin-profile-content two-columns">
        <article class="profile-info-card">
          <h3>Verification</h3>
          <InfoRow label="Verification Status" :value="candidate.verification_status" />
          <InfoRow label="Verified At" :value="dateLabel(candidate.verified_at)" />
          <InfoRow label="Verification Remark" :value="candidate.verification_remark" />
        </article>
        <article class="profile-info-card">
          <h3>Review & Approval</h3>
          <InfoRow label="Record Status" :value="isActive ? 'Active' : 'Pending'" />
          <InfoRow label="Vendor" :value="candidate.vendor?.name || candidate.vendor_name" />
        </article>
      </section>

      <section v-else-if="activeTab === 'documents'" class="admin-profile-content document-grid">
        <article class="profile-info-card document-card">
          <h3>Profile Photo</h3>
          <div class="document-card-body">
            <img
              v-if="documentUrl('profile_photo')"
              :src="documentUrl('profile_photo')"
              class="document-photo-preview"
              alt="Caregiver profile photo"
              @error="photoFailed = true"
            />
            <p v-else class="muted">Profile photo not uploaded.</p>
            <a
              v-if="documentUrl('profile_photo')"
              :href="documentUrl('profile_photo')"
              target="_blank"
              rel="noopener"
              class="document-view-link"
            >View profile photo ↗</a>
          </div>
        </article>

        <article class="profile-info-card document-card">
          <h3>Aadhaar Card</h3>
          <div class="document-card-body">
            <div v-if="documentUrl('adhar_pdf')" class="document-file-icon">PDF</div>
            <div class="document-details">
              <a
                v-if="documentUrl('adhar_pdf')"
                :href="documentUrl('adhar_pdf')"
                target="_blank"
                rel="noopener"
                class="document-view-link"
              >View Aadhaar document ↗</a>
              <p v-else class="muted">Aadhaar document not uploaded.</p>
            </div>
          </div>
        </article>

        <article class="profile-info-card document-card">
          <h3>Qualification Certificate</h3>
          <div class="document-card-body">
            <div v-if="documentUrl('certificate_pdf')" class="document-file-icon certificate">PDF</div>
            <div class="document-details">
              <a
                v-if="documentUrl('certificate_pdf')"
                :href="documentUrl('certificate_pdf')"
                target="_blank"
                rel="noopener"
                class="document-view-link"
              >View certificate ↗</a>
              <p v-else class="muted">Certificate not uploaded.</p>
            </div>
          </div>
        </article>
      </section>

      <section v-else-if="activeTab === 'availability'" class="admin-profile-content two-columns">
        <article class="profile-info-card">
          <h3>Availability</h3>
          <InfoRow label="Availability Status" :value="candidate.availability_status" />
          <InfoRow label="Currently Engaged" :value="candidate.is_engaged ? 'Yes' : 'No'" />
          <InfoRow label="Preferred Duty Types" :value="listText(candidate.preferred_duty_types)" />
        </article>
        <article class="profile-info-card">
          <h3>Preferred Locations</h3>
          <p class="professional-summary">{{ listText(candidate.preferred_locations) }}</p>
        </article>
      </section>

      <section v-else-if="activeTab === 'placement'" class="admin-profile-content two-columns">
        <article class="profile-info-card">
          <h3>Placement Details</h3>
          <InfoRow label="Placement Status" :value="candidate.placement_status" />
          <InfoRow label="Placement Location" :value="candidate.placement_location" />
          <InfoRow label="Start Date" :value="dateLabel(candidate.placement_start)" />
          <InfoRow label="End Date" :value="dateLabel(candidate.placement_end)" />
          <InfoRow label="Hospital ID" :value="candidate.hospital_id" />
        </article>
        <article class="profile-info-card">
          <h3>Ratings & Reviews</h3>
          <InfoRow label="Rating" :value="candidate.rating" />
          <InfoRow label="Facebook Review" :value="stripHtml(candidate.fb_reviews)" />
          <InfoRow label="Instagram Review" :value="stripHtml(candidate.insta_reviews)" />
          <InfoRow label="Google Review" :value="stripHtml(candidate.google_reviews)" />
        </article>
      </section>

      <section v-else class="admin-profile-content">
        <article class="profile-info-card">
          <h3>Notes</h3>
          <p class="professional-summary">{{ label(candidate.remark, 'No notes available.') }}</p>
        </article>
      </section>
    </template>
  </div>
</template>

<script setup>
import { computed, ref, defineComponent, h } from 'vue';

const props = defineProps({
  candidate: { type: Object, default: null },
  completion: { type: Number, default: 90 },
});
defineEmits(['edit']);

const activeTab = ref('personal');
const photoFailed = ref(false);
const tabs = [
  { key: 'personal', label: 'Personal Info' },
  { key: 'education', label: 'Education' },
  { key: 'skills', label: 'Skills' },
  { key: 'experience', label: 'Experience' },
  { key: 'verification', label: 'Verification' },
  { key: 'documents', label: 'Documents' },
  { key: 'availability', label: 'Availability' },
  { key: 'placement', label: 'Placement' },
  { key: 'notes', label: 'Notes' },
];

const candidate = computed(() => props.candidate || {});
const get = (...keys) => {
  for (const key of keys) {
    const value = candidate.value?.[key];
    if (value !== undefined && value !== null && value !== '') return value;
  }
  return '—';
};
const label = (value, fallback = '—') => (value === null || value === undefined || value === '' ? fallback : value);
const fullName = computed(
  () => [candidate.value.first_name, candidate.value.middle_name, candidate.value.last_name].filter(Boolean).join(' ') || 'Candidate Name'
);
const initials = computed(() =>
  fullName.value
    .split(/\s+/)
    .slice(0, 2)
    .map((part) => part[0])
    .join('')
    .toUpperCase()
);
const designationName = computed(
  () =>
    candidate.value.designation?.name ||
    candidate.value.designation_name ||
    (typeof candidate.value.designation === 'string' ? candidate.value.designation : '') ||
    'Caregiver'
);
const genderLabel = computed(() => ({ M: 'Male', F: 'Female', O: 'Other' })[candidate.value.gender] || candidate.value.gender || '—');
const ageLabel = computed(() =>
  candidate.value.age
    ? `${candidate.value.age} yrs`
    : candidate.value.date_of_birth
      ? `${calculateAge(candidate.value.date_of_birth)} yrs`
      : '—'
);
const experienceLabel = computed(() => (candidate.value.experience ? `${candidate.value.experience} Years` : '—'));
const locationLabel = computed(() => [candidate.value.city, candidate.value.state].filter(Boolean).join(', ') || 'Location not provided');
const breadcrumb = computed(() => `Caregiver  >  ${get('caregiver_code', 'candidate_code')}`);
const isActive = computed(() => ['a', 'active', 'approved'].includes(String(candidate.value.status || '').toLowerCase()));
const photoUrl = computed(() =>
  photoFailed.value
    ? ''
    : candidate.value.profilePreviewUrl ||
      candidate.value.profile_photo_url ||
      candidate.value.profile_photo ||
      candidate.value.photo_url ||
      ''
);
const languageItems = computed(() => {
  const raw = candidate.value.languages || candidate.value.language_names || candidate.value.language;
  if (Array.isArray(raw))
    return raw
      .map((item) => ({
        name: typeof item === 'string' ? item : item.name || item.label,
        level: item.level || item.proficiency || 'Basic',
      }))
      .filter((item) => item.name);
  if (raw && typeof raw === 'object')
    return [{ name: raw.name || raw.label, level: raw.level || raw.proficiency || 'Basic' }].filter((item) => item.name);
  if (typeof raw === 'string' && raw.trim()) return raw.split(',').map((name) => ({ name: name.trim(), level: 'Basic' }));
  return [];
});
function calculateAge(date) {
  const birth = new Date(date);
  if (Number.isNaN(birth.getTime())) return '—';
  const now = new Date();
  let age = now.getFullYear() - birth.getFullYear();
  if (now.getMonth() < birth.getMonth() || (now.getMonth() === birth.getMonth() && now.getDate() < birth.getDate())) age--;
  return age;
}
function dateLabel(value) {
  if (!value) return '—';
  const date = new Date(value);
  return Number.isNaN(date.getTime()) ? value : date.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
}
function listText(value) {
  if (Array.isArray(value))
    return (
      value
        .map((item) => (typeof item === 'object' ? item.name || item.label : item))
        .filter(Boolean)
        .join(', ') || '—'
    );
  if (typeof value === 'string') {
    try {
      const parsed = JSON.parse(value);
      if (Array.isArray(parsed)) return listText(parsed);
    } catch {}
    return value || '—';
  }
  return '—';
}
function documentUrl(field) {
  const c = candidate.value || {};

  // Match the document preview fields used by the enrolment form,
  // while also supporting the persisted Laravel file/path fields.
  const fields = {
    profile_photo: [
      c.profilePreviewUrl,
      c.profilePhotoPreviewUrl,
      c.profile_photo_url,
      c.photo_url,
      c.profile_photo,
      c.profile_photo_file,
    ],
    adhar_pdf: [
      c.aadhaarPreviewUrl,
      c.aadhaar_preview_url,
      c.adhar_pdf_url,
      c.aadhaar_url,
      c.aadhaar_file_url,
      c.adhar_pdf,
      c.adhar_pdf_file,
      c.aadhaar_file,
    ],
    certificate_pdf: [
      c.certificatePreviewUrl,
      c.certificate_preview_url,
      c.certificate_pdf_url,
      c.certificate_url,
      c.certificate_file_url,
      c.certificate_pdf,
      c.certificate_pdf_file,
      c.certificate_file,
    ],
  };

  const value = (fields[field] || []).find(
    (item) => item !== null && item !== undefined && item !== ''
  );

  if (!value) return '';

  const rawUrl =
    typeof value === 'object'
      ? value.url || value.path || value.full_url || value.original_url
      : value;

  if (typeof rawUrl !== 'string' || !rawUrl.trim()) return '';

  const url = rawUrl.trim();

  // Keep absolute and root-relative URLs unchanged.
  if (/^(https?:)?\/\//i.test(url) || url.startsWith('/')) return url;

  // Laravel public storage paths are often returned without the leading slash.
  if (url.startsWith('storage/')) return `/${url}`;

  return url;
}
function stripHtml(value) {
  return typeof value === 'string'
    ? value
        .replace(/<[^>]*>/g, ' ')
        .replace(/\s+/g, ' ')
        .trim() || '—'
    : label(value);
}

const InfoRow = defineComponent({
  name: 'InfoRow',
  props: { label: String, value: [String, Number, Boolean] },
  setup(props) {
    return () =>
      h('div', { class: 'admin-info-row' }, [
        h('span', props.label),
        h('strong', props.value === null || props.value === undefined || props.value === '' ? '—' : String(props.value)),
      ]);
  },
});
</script>

<style scoped>
.admin-candidate-profile {
  background: #fff;
  color: #26384c;
  font-family: inherit;
  font-size: 12px;
  min-height: 300px;
}
.admin-profile-banner {
  background: linear-gradient(105deg, #0e416f, #15558a);
  color: #fff;
  padding: 12px 18px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  border-radius: 5px 5px 0 0;
}
.banner-title {
  display: flex;
  align-items: center;
  gap: 10px;
}
.banner-avatar-icon {
  width: 32px;
  height: 32px;
  border: 1px solid #ffffff70;
  border-radius: 6px;
  display: grid;
  place-items: center;
  font-size: 21px;
}
.banner-title h2 {
  font-size: 15px;
  font-weight: 700;
  margin: 0;
}
.banner-title p {
  font-size: 10px;
  margin: 3px 0 0;
  color: #d9e9f7;
}
.edit-profile-btn {
  background: transparent;
  color: #fff;
  border: 1px solid #ffffff9c;
  border-radius: 5px;
  padding: 6px 10px;
  font-size: 10px;
  white-space: nowrap;
  cursor: pointer;
}
.admin-profile-summary {
  display: grid;
  grid-template-columns: 94px minmax(180px, 1fr) auto minmax(190px, 270px);
  gap: 14px;
  align-items: center;
  padding: 15px 18px;
  border-bottom: 1px solid #e8edf3;
}
.admin-profile-photo {
  width: 92px;
  height: 96px;
  object-fit: cover;
  border-radius: 6px;
  border: 1px solid #e0e8ef;
}
.photo-placeholder {
  display: grid;
  place-items: center;
  background: #edf3f8;
  color: #164d7a;
  font-size: 25px;
  font-weight: 700;
}
.summary-identity .candidate-code {
  font-size: 12px;
  color: #154d82;
}
.summary-identity h1 {
  font-size: 17px;
  margin: 2px 0 5px;
  color: #1d2f43;
}
.summary-identity p {
  font-size: 10px;
  margin: 4px 0;
  color: #536477;
}
.status-pill {
  font-size: 10px;
  font-weight: 700;
  padding: 5px 11px;
  border-radius: 15px;
  align-self: start;
}
.status-pill.active {
  background: #d9f5e8;
  color: #07804e;
}
.status-pill.inactive {
  background: #fff0d5;
  color: #9a6500;
}
.summary-progress {
  background: #f0f6fc;
  border-radius: 7px;
  padding: 10px 12px;
  align-self: stretch;
  display: flex;
  flex-direction: column;
  justify-content: center;
  gap: 6px;
}
.summary-progress-row {
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.summary-progress strong {
  font-size: 10px;
}
.status-chip {
  width: max-content;
  border-radius: 4px;
  padding: 3px 6px;
  font-size: 9px;
}
.status-chip.green {
  background: #d9f5e8;
  color: #087a4c;
}
.status-chip.blue {
  background: #dceeff;
  color: #1767a5;
}
.completion-title {
  margin-top: 2px;
}
.progress-track {
  height: 7px;
  background: #dce5ed;
  border-radius: 10px;
  overflow: hidden;
}
.progress-track span {
  display: block;
  height: 100%;
  background: #239b64;
  border-radius: 10px;
}
.summary-progress small {
  text-align: right;
  font-size: 9px;
  color: #526274;
  margin-top: -5px;
}
.admin-profile-tabs {
  display: flex;
  overflow-x: auto;
  gap: 2px;
  padding: 0 15px;
  border-bottom: 1px solid #e2e9f0;
  background: #f8fafc;
}
.admin-profile-tabs button {
  flex: 0 0 auto;
  border: 0;
  border-bottom: 3px solid transparent;
  background: transparent;
  padding: 11px 12px;
  font-size: 10px;
  color: #526477;
  cursor: pointer;
}
.admin-profile-tabs button.selected {
  background: #fff;
  color: #1464a3;
  border-bottom-color: #1879c3;
  font-weight: 700;
}
.admin-profile-content {
  padding: 13px 15px;
  display: grid;
  gap: 10px;
}
.three-columns {
  grid-template-columns: 1fr 1fr 1fr;
}
.two-columns {
  grid-template-columns: 1fr 1fr;
}
.profile-info-card {
  border: 1px solid #e0e8f0;
  border-radius: 5px;
  overflow: hidden;
  min-width: 0;
}
.profile-info-card h3 {
  font-size: 10px;
  font-weight: 800;
  color: #263d55;
  background: #edf4fb;
  padding: 7px 9px;
  margin: 0;
}
.profile-info-card h3.inner-heading {
  margin-top: 10px;
}
.admin-info-row {
  display: grid;
  grid-template-columns: 43% 57%;
  gap: 5px;
  padding: 5px 8px;
  border-bottom: 1px solid #edf1f5;
  font-size: 9px;
  line-height: 1.35;
}
.admin-info-row:last-child {
  border-bottom: 0;
}
.admin-info-row span {
  color: #667587;
}
.admin-info-row strong {
  color: #2d3d50;
  font-weight: 600;
  overflow-wrap: anywhere;
}
.professional-summary {
  font-size: 9px;
  line-height: 1.55;
  color: #536477;
  padding: 9px;
  margin: 0;
  white-space: pre-wrap;
}
.language-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 5px 9px;
  border-bottom: 1px solid #edf1f5;
  font-size: 9px;
}
.language-level {
  font-size: 8px;
  padding: 3px 6px;
  border-radius: 4px;
  background: #e2f5e9;
  color: #17814f;
}
.language-level.conversational {
  background: #e3f1ff;
  color: #1a6da8;
}
.language-level.basic {
  background: #fff0e9;
  color: #bf6043;
}
.muted {
  font-size: 10px;
  color: #8793a1;
  padding: 8px;
  margin: 0;
}
.profile-info-card a {
  display: inline-block;
  padding: 10px;
  color: #176ba8;
  text-decoration: none;
  font-size: 10px;
}
.document-grid {
  grid-template-columns: repeat(3, minmax(0, 1fr));
  align-items: stretch;
}
.document-card {
  min-height: 145px;
}
.document-card-body {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px;
  min-height: 95px;
}
.document-photo-preview {
  width: 76px;
  height: 82px;
  object-fit: cover;
  border: 1px solid #e0e8f0;
  border-radius: 6px;
  background: #f7f9fc;
}
.document-file-icon {
  width: 48px;
  height: 58px;
  flex: 0 0 48px;
  display: grid;
  place-items: center;
  border: 1px solid #e0e8f0;
  border-top: 4px solid #dc3545;
  border-radius: 5px;
  background: #fff;
  color: #dc3545;
  font-size: 11px;
  font-weight: 800;
}
.document-file-icon.certificate {
  border-top-color: #5145cd;
  color: #5145cd;
}
.document-details {
  min-width: 0;
}
.document-view-link {
  display: inline-block;
  padding: 5px 0;
  color: #176ba8;
  text-decoration: none;
  font-size: 11px;
  font-weight: 600;
}
.document-view-link:hover {
  text-decoration: underline;
}
.profile-empty {
  padding: 25px;
  text-align: center;
  color: #7c8795;
}
@media (max-width: 760px) {
  .admin-profile-summary {
    grid-template-columns: 75px 1fr;
    gap: 10px;
  }
  .admin-profile-photo {
    width: 74px;
    height: 78px;
  }
  .summary-status {
    grid-column: 2;
  }
  .summary-progress {
    grid-column: 1/-1;
  }
  .three-columns,
  .two-columns,
  .document-grid {
    grid-template-columns: 1fr;
  }
  .admin-profile-tabs button {
    padding: 10px 9px;
  }
  .admin-profile-content {
    padding: 10px;
  }
  .admin-profile-banner {
    padding: 10px;
  }
}
</style>
