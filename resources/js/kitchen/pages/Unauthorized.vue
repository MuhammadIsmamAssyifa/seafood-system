<template>
    <div class="min-h-screen w-full bg-slate-950 flex flex-col justify-center items-center p-6 text-center select-none">
        <div class="w-full max-w-md bg-slate-900 border border-slate-800 rounded-3xl p-8 shadow-2xl flex flex-col items-center">
            <div class="w-16 h-16 rounded-2xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400 mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>

            <h2 class="text-xl font-bold text-white mb-2">Akses Ditolak (403)</h2>
            <p class="text-sm text-slate-400 mb-6 leading-relaxed">
                Akun Anda (<span class="text-slate-200 font-semibold">{{ authStore.userName }}</span>) tidak memiliki izin akses untuk Kitchen Display System (KDS).
            </p>

            <button
                type="button"
                class="w-full py-3 px-4 bg-blue-600 hover:bg-blue-500 text-white rounded-xl font-bold text-sm shadow-lg shadow-blue-600/30 transition-all active:scale-95"
                @click="handleSwitchAccount"
            >
                Ganti Akun Staff Dapur
            </button>
        </div>
    </div>
</template>

<script setup>
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/kitchen/stores/auth.store';

const router = useRouter();
const authStore = useAuthStore();

const handleSwitchAccount = async () => {
    await authStore.logout();
    router.push({ name: 'kitchen.login' });
};
</script>

