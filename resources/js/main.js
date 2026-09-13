/**
 * main.js - Application Entry Point
 *
 * This file initializes the Vue 3 application and configures:
 * - Pinia for state management
 * - Vue Router for client-side routing
 * - CoreUI Vue component library
 * - Global icon system
 * - Documentation helper components
 *
 * The application uses:
 * - Vue 3 Composition API
 * - Vite for building and development
 * - CoreUI Vue components
 * - Hash-based routing for static hosting compatibility
 */

import { createApp } from 'vue'
import { createPinia } from 'pinia'

import App from './App.vue'
import router from './router'
import PrimeVue from 'primevue/config'
import Aura from '@primeuix/themes/aura'

// CoreUI Vue components and icons
import CoreuiVue from '@coreui/vue'
import CIcon from '@coreui/icons-vue'
import { iconsSet as icons } from '@/assets/icons'

// Documentation components (remove in production if not needed)
import DocsComponents from '@/components/DocsComponents'
import DocsExample from '@/components/DocsExample'
import DocsIcons from '@/components/DocsIcons'
import { QuillEditor } from '@vueup/vue-quill'
import '@vueup/vue-quill/dist/vue-quill.snow.css'
import './assets/common.css'
import Toast from 'vue-toastification'
import 'vue-toastification/dist/index.css'
const app = createApp(App)

app.use(createPinia())
app.use(router)
app.use(CoreuiVue)
app.use(PrimeVue, {
    theme: {
        preset: Aura,
    },
})
app.use(Toast)

app.component('CIcon', CIcon)
app.component('QuillEditor', QuillEditor)

router.isReady().then(() => {
    app.mount('#app')
})
