<template>
    <div id="kds-root" class="h-full w-full bg-slate-950 text-slate-100 antialiased font-sans">
        <RouterView />
    </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useAuthStore } from '@/kitchen/stores/auth.store';

const authStore = useAuthStore();

onMounted(async () => {
    // If token exists, sync current user profile in background
    if (authStore.isAuthenticated) {
        await authStore.getProfile();
    }
});
</script>

