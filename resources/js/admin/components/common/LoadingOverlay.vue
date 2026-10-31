<script setup>
import { Loader2 } from 'lucide-vue-next';

defineProps({
    message: {
        type: String,
        default: 'Memuat data...',
    },
    fullscreen: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <div
        :class="[
            fullscreen
                ? 'fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-xs'
                : 'absolute inset-0 z-20 bg-white/70 backdrop-blur-xs rounded-2xl',
            'flex flex-col items-center justify-center transition-all duration-200'
        ]"
    >
        <div class="flex flex-col items-center justify-center p-6 bg-white rounded-2xl shadow-lg border border-slate-100 max-w-xs text-center">
            <Loader2 class="w-8 h-8 text-blue-600 animate-spin mb-3" />
            <p class="text-xs font-semibold text-slate-700">{{ message }}</p>
        </div>
    </div>
</template>

