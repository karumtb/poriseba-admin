import { createRouter, createWebHashHistory,createWebHistory } from 'vue-router'

import DefaultLayout from '@/layouts/DefaultLayout.vue'
import authGuard from './authGuard'

const routes = [

    // =========================
    // ADMIN LOGIN
    // =========================
    {
        path: '/',
        redirect: '/pages/login',
    },

    {
        path: '/pages/login',
        name: 'Login',
        component: () => import('@/views/pages/Login.vue'),
    },

    // =========================
    // ADMIN PANEL
    // =========================
    {
        path: '/',
        component: DefaultLayout,
        meta: {
            requiresAuth: true,
        },
        children: [
            {
                path: 'dashboard',
                name: 'Dashboard',
                component: () => import('@/views/dashboard/Dashboard.vue'),
            },

            {
                path: 'user/institute',
                name: 'Vendor',
                component: () => import('@/views/user/Vendor.vue'),
            },
            {
                path: 'user/hospital',
                name: 'Hospital',
                component: () => import('@/views/user/Vendor.vue'),
            },
            {
                path: 'enrolment',
                name: 'Enrolment',
                component: () => import('@/views/user/Enrolment.vue'),
            },
            {
                path: 'placement-request',
                name: 'Placement Request',
                component: () => import('@/views/user/Placement.vue'),
            },
             {
                path: 'subscription-plan',
                name: 'SubscriptionPlan',
                component: () => import('@/views/subscription-plan/Plan.vue'),
            },
            {
                path: 'setting',
                name: 'Setting',
                component: () => import('@/views/setting/Setting.vue'),
            },


        ],
    },

    // Unknown Vue route
    {
        path: '/:pathMatch(.*)*',
        redirect: '/pages/login',
    },
]

const router = createRouter({
    history: createWebHistory(import.meta.env.VITE_APP_BASE_URL),
    routes,

    scrollBehavior() {
        return {
            top: 0,
            left: 0,
        }
    },
})
// Auth Guard
router.beforeEach(authGuard)
export default router
