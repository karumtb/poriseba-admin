<script setup>
import avatar from '@/assets/images/avatars/8.jpg'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

import api from '@/services/axios'
import {
  cilLockLocked
} from '@coreui/icons'
const itemsCount = 42
const router = useRouter()
const authStore = useAuthStore()
const logout = async () => {
  try {
    authStore.logout();
    router.push('/pages/login')
  } catch (error) {
    console.error('Logout error:', error)
  } finally {
    // Always clear local login data
    localStorage.removeItem('token')


  }
}



</script>

<template>
  <CDropdown placement="bottom-end" variant="nav-item">
    <CDropdownToggle class="py-0 pe-0" :caret="false">
      <CAvatar :src="authStore?.user?.profileImageUrl" size="md"   v-if="authStore?.user?.profileImageUrl" />
      <div class="placeholder-icon"  v-else>👤</div>
    </CDropdownToggle>
    <CDropdownMenu class="pt-0">


      <CDropdownHeader
        component="h6"
        class="bg-body-secondary text-body-secondary fw-semibold my-2"
      >
        Admin
      </CDropdownHeader>

      <CDropdownItem @click="logout" style="cursor: pointer"><CIcon :icon="cilLockLocked" /> Logout </CDropdownItem>
    </CDropdownMenu>
  </CDropdown>
</template>
