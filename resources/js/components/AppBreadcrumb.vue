<script setup>
import { onMounted, ref, watch } from 'vue'
import router from '@/router'
import { useBreadcrumb } from '@/services/useBreadcrumb'

const { breadcrumbs } = useBreadcrumb()

// Local breadcrumb items for rendering
const items = ref([])

const getBreadcrumbs = () => {
  if (breadcrumbs.value.length > 0) {
    return breadcrumbs.value.map((text, index) => ({
      name: text,
      active: index === breadcrumbs.value.length - 1,
    }))
  }

  return router.currentRoute.value.matched.map((route, index, routes) => ({
    name: route.meta?.breadcrumb || route.name,
    active: index === routes.length - 1,
  }))
}

onMounted(() => {
  items.value = getBreadcrumbs()
})

router.afterEach(() => {
  items.value = getBreadcrumbs()
})

watch(
  breadcrumbs,
  () => {
    items.value = getBreadcrumbs()
  },
  { deep: true }
)
</script>

<template>
  <CBreadcrumb class="my-0">
    <CBreadcrumbItem
      v-for="item in items"
      :key="item.name"
      :active="item.active"
    >
      {{ item.name }}
    </CBreadcrumbItem>
  </CBreadcrumb>
</template>
