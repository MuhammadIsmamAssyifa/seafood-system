import { createRouter, createWebHistory } from 'vue-router';
import { createAuthGuard } from '@/kitchen/middleware/auth.guard';

const routes = [
    {
        path: '/kitchen',
        redirect: '/kitchen/dashboard',
    },
    {
        path: '/kitchen/login',
        name: 'kitchen.login',
        component: () => import('@/kitchen/pages/Login.vue'),
        meta: {
            title: 'Login Staff Dapur',
            guestOnly: true,
        },
    },
    {
        path: '/kitchen/dashboard',
        name: 'kitchen.dashboard',
        component: () => import('@/kitchen/pages/Dashboard.vue'),
        meta: {
            title: 'Kitchen Display System',
            requiresAuth: true,
        },
    },
    {
        path: '/kitchen/orders/:id',
        name: 'kitchen.orders.detail',
        component: () => import('@/kitchen/pages/OrderDetail.vue'),
        meta: {
            title: 'Detail Order',
            requiresAuth: true,
        },
    },
    {
        path: '/kitchen/unauthorized',
        name: 'kitchen.unauthorized',
        component: () => import('@/kitchen/pages/Unauthorized.vue'),
        meta: {
            title: 'Akses Ditolak',
        },
    },
    {
        path: '/kitchen/:pathMatch(.*)*',
        redirect: '/kitchen/dashboard',
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

createAuthGuard(router);

export default router;
