<script setup>
import { reactive, ref, nextTick, onMounted } from 'vue';
import { CPasswordInput } from '@coreui/vue-pro';
import '@coreui/coreui-pro/dist/css/coreui.css';

import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

import api from '@/services/adminAxios';
import logo from '@/assets/images/logo.png';
import imgg2 from '@/assets/images/imgg2.png';
import usericon from '@/assets/images/name.png';
import lockicon from '@/assets/images/lock.png';
import homeicon from '@/assets/images/home.png';
import { cilLockLocked, cilEnvelopeClosed } from '@coreui/icons';
const router = useRouter();
const authStore = useAuthStore();
const btnLoading = ref(false);
const submitted = ref(false);

const form = reactive({
  email: '',
  password: '',
});

// ==========================================
// LOGIN
// ==========================================

const login = async (event) => {
  submitted.value = true;
  const formElement = event.currentTarget;
  if (!formElement.checkValidity()) {
    console.log(formElement.checkValidity());
    event.preventDefault();
    event.stopPropagation();
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
    const response = await api.post('/login', {
      email: form.email.trim(),
      password: form.password,
    });
    if (response.data.status) {
      // Save JWT token
      if (response.data.data.token) {
        localStorage.setItem('token', response.data.data.token);
      }

      // Save user
      if (response.data.data.user) {
        authStore.setUser(response.data.data.user);
      }
      // Redirect dashboard
      router.push('/dashboard');
    }
  } catch (error) {
    console.error(error);
  } finally {
    btnLoading.value = false;
  }
};
onMounted(() => {
  if (localStorage.getItem('token')) {
    router.push('/dashboard');
  }
});
</script>

<template>
  <section class="loginpage">
    <div class="row g-0 heightvh">
      <div class="d-none d-md-block col-md-6 rightimg">
        <img :src="imgg2" alt="" />
      </div>
      <div class="col-md-6 p-4 text-center d-flex align-items-center justify-content-center">
        <a class="home-link" href="/"><img :src="homeicon" alt="" /></a>
        <div class="loginmain text-center">
          <div class="loginlogo">
            <img :src="logo" alt="" />
          </div>
          <CForm @submit.prevent="login" novalidate>
            <h2 class="und">Login</h2> 
            <hr>

            <div class="mb-3 text-start">
              <label for="exampleFormControlInput1" class="form-label text-left">Email Address</label>
              <div class="input-group">
              <span class="input-group-text">
                <img :src="usericon" alt="" />
              </span>
                <CFormInput
                v-model.trim.trim="form.email" type="email" placeholder="Email address" class="form-control"
                :class="{
                  'is-invalid': submitted && (!form.email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)),
                }" required />
              </div>
              <CFormFeedback invalid v-if="submitted && !form.email"> Please enter an email address. </CFormFeedback>

              <CFormFeedback invalid v-else-if="submitted && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)">
                Please enter a valid email address.
              </CFormFeedback>
            </div>
            <div class="mb-3 text-start passmain">
              <label for="exampleFormControlInput1" class="form-label text-left">Password</label>
              <div class="input-group">
                  <span class="input-group-text">
                    <img :src="lockicon" alt="" />
                  </span>
                  <CPasswordInput
                    v-model.trim="form.password"
                    placeholder="Password"
                    autocomplete="current-password"
                    class="form-control"
                    :class="{ 'is-invalid': submitted && !form.password }"
                    required />
              </div>
              <CFormFeedback invalid v-if="submitted && !form.password">Password is required. </CFormFeedback>
            </div>
            <div class="d-flex justify-content-between"> 
                <div class="mb-3 form-check text-start"> 
                  <input type="checkbox" class="form-check-input" id="exampleCheck1" />
                  <label class="form-check-label" for="exampleCheck1">Remember Me</label>
                </div>
                <div class="mb-3 form-check text-start forgotpass">
                  <a href="#0">Forgot Password?</a>
                </div>
            </div>
            <div class="col-auto">
              <button type="submit" color="primary" class="btn btn-color form-control" :disabled="btnLoading">
                <CIcon :icon="cilLockLocked" />
                <CSpinner v-if="btnLoading" size="sm" class="me-2" />

                {{ btnLoading ? 'Logging...' : 'Login' }}
              </button>
            </div>
          </CForm>
        </div>
      </div>
    </div>
  </section>
</template>
<style scoped>
.login-logo {
  width: 180px;
  max-width: 100%;
  height: auto;
}
</style>
