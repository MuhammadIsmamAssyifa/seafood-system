import { useAuthStore } from '@/kitchen/stores/auth.store';

export const createAuthGuard = (router) => {
    router.beforeEach(async (to, from, next) => {
        const authStore = useAuthStore();

        if (to.meta.title) {
            document.title = `${to.meta.title} - KDS`;
        }

        // If route requires authentication
        if (to.meta.requiresAuth) {
            // Check if token exists
            if (!authStore.token) {
                next({
                    name: 'kitchen.login',
                    query: { redirect: to.fullPath },
                });
                return;
            }

            // If user profile is not yet loaded, load it via GET /api/v1/me
            if (!authStore.user) {
                const user = await authStore.getProfile();
                if (!user) {
                    next({
                        name: 'kitchen.login',
                        query: { redirect: to.fullPath, error: 'unauthorized' },
                    });
                    return;
                }
            }

            // Verify kitchen role
            if (!authStore.hasKitchenAccess) {
                next({
                    name: 'kitchen.unauthorized',
                });
                return;
            }

            next();
            return;
        }

        // Guest only routes (e.g. Login)
        if (to.meta.guestOnly && authStore.token) {
            // Already logged in, redirect to dashboard
            next({ name: 'kitchen.dashboard' });
            return;
        }

        next();
    });
};

export default createAuthGuard;

