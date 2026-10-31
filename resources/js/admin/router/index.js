import { createRouter, createWebHistory } from 'vue-router';
import routes from './routes';
import { storageService } from '../services/storage.service';
import { useAuthStore } from '../stores/auth.store';

const router = createRouter({
    history: createWebHistory('/admin'),
    routes,
    scrollBehavior() {
        return { top: 0 };
    },
});

router.beforeEach(async (to, from, next) => {
    // Dynamic document title
    const appName = window.__APP_CONFIG__?.appName || 'Seafood Resto';
    const pageTitle = to.meta?.title ? `${to.meta.title} - ${appName}` : `${appName} Admin`;
    if (typeof document !== 'undefined') {
        document.title = pageTitle;
    }

    const token = storageService.getToken();
    const authStore = useAuthStore();

    // Route requires guest (e.g. /login)
    if (to.meta?.requiresGuest) {
        if (token) {
            return next('/dashboard');
        }
        return next();
    }

    // Route requires authentication
    if (to.meta?.requiresAuth) {
        if (!token) {
            return next('/login');
        }

        // If user profile is not yet in Pinia, fetch it
        if (!authStore.user) {
            try {
                await authStore.fetchUser();
            } catch (error) {
                // Token might be invalid or expired
                storageService.clear();
                return next('/login');
            }
        }

        // Authorization check: Permission Guard
        if (to.meta?.permission && !authStore.hasPermission(to.meta.permission)) {
            return next('/403');
        }

        // Authorization check: Role Guard
        if (to.meta?.role && !authStore.hasRole(to.meta.role)) {
            return next('/403');
        }
    }

    next();
});

export default router;

