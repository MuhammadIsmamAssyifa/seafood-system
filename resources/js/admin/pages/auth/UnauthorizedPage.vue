<script setup>
import { useRouter } from 'vue-router';
import { ShieldAlert, ArrowLeft, LogOut } from 'lucide-vue-next';
import { useAuthStore } from '../../stores/auth.store';
import BaseButton from '../../components/common/BaseButton.vue';

const router = useRouter();
const authStore = useAuthStore();

async function handleLogout() {
    await authStore.logout();
    router.push('/login');
}
</script>

<template>
    <div class="min-h-[70vh] flex items-center justify-center p-4">
        <div class="max-w-md w-full bg-white rounded-2xl border border-slate-200/80 shadow-lg p-8 text-center">
            <div class="w-16 h-16 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center mx-auto mb-4 border border-rose-100">
                <ShieldAlert class="w-8 h-8" />
            </div>

            <span class="inline-block px-2.5 py-1 rounded-full text-xs font-mono font-bold bg-rose-100 text-rose-800 mb-2">
                HTTP 403 FORBIDDEN
            </span>

            <h2 class="text-xl font-bold text-slate-800">
                Akses Ditolak
            </h2>

            <p class="text-sm text-slate-600 mt-2 leading-relaxed">
                Anda tidak memiliki hak akses untuk membuka halaman ini. Silakan hubungi Owner atau Administrator sistem untuk otorisasi lebih lanjut.
            </p>

            <div class="mt-6 flex flex-col sm:flex-row items-center justify-center gap-3">
                <BaseButton
                    variant="primary"
                    size="md"
                    @click="router.push('/dashboard')"
                >
                    <ArrowLeft class="w-4 h-4" />
                    <span>Ke Dashboard</span>
                </BaseButton>

                <BaseButton
                    variant="outline"
                    size="md"
                    @click="handleLogout"
                >
                    <LogOut class="w-4 h-4 text-slate-500" />
                    <span>Ganti Akun</span>
                </BaseButton>
            </div>
        </div>
    </div>
</template>

