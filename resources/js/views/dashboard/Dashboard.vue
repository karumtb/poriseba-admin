<script setup>
import { ref, reactive, onMounted, computed, nextTick, onUnmounted, watch } from 'vue';
import { useBreadcrumb } from '@/services/useBreadcrumb';
const { breadcrumbs } = useBreadcrumb();

import Summary from './Summary.vue'
/*
|--------------------------------------------------------------------------
| Mounted
|--------------------------------------------------------------------------
*/

onMounted(() => {
  breadcrumbs.value = ['Dashboard'];
});

</script>

<template>
  <div>
    <Summary class="mb-4" />

  </div>
</template>
