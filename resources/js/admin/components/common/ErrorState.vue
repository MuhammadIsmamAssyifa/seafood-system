<script setup>
import { AlertCircle, RefreshCw } from 'lucide-vue-next';
import BaseButton from './BaseButton.vue';

defineProps({
    title: {
        type: String,
        default: 'Terjadi Kesalahan',
    },
    message: {
        type: String,
        default: 'Gagal memuat data dari server. Silakan coba beberapa saat lagi.',
    },
    showRetry: {
        type: Boolean,
        default: true,
    },
    loading: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['retry']);
</script>

<template>
    <div class="p-6 rounded-2xl bg-rose-50/80 border border-rose-200/80 text-rose-800 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-2xs">
        <div class="flex items-start sm:items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-rose-100 flex items-center justify-center shrink-0 text-rose-600">
                <AlertCircle class="w-5 h-5" />
            </div>
            <div>
                <h4 class="text-sm font-bold text-rose-900">{{ title }}</h4>
                <p class="text-xs text-rose-700 mt-0.5 leading-relaxed">{{ message }}</p>
            </div>
        </div>
        <BaseButton
            v-if="showRetry"
            variant="danger"
            size="sm"
            @click="emit('retry')"
            :loading="loading"
            class="shrink-0"
        >
            <RefreshCw class="w-3.5 h-3.5 mr-1" :class="[loading ? 'animate-spin' : '']" />
            Coba Lagi
        </BaseButton>
    </div>
</template>

