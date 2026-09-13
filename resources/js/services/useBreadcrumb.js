import { ref } from 'vue'

const breadcrumbs = ref([])

export function useBreadcrumb() {
  return {
    breadcrumbs,
  }
}
