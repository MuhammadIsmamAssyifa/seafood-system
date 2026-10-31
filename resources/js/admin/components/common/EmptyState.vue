<script setup>
import { AlertCircle, Inbox } from 'lucide-vue-next';

defineProps({
    title: {
        type: String,
        default: 'Tidak Ada Data',
    },
    message: {
        type: String,
        default: 'Belum ada data untuk ditampilkan pada kategori ini.',
    },
});
</script>

<template>
    <div class="flex flex-col items-center justify-center p-8 sm:p-12 text-center bg-white rounded-2xl border border-dashed border-slate-200">
        <div class="w-14 h-14 mb-3.5 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-400">
            <slot name="icon">
                <Inbox class="w-7 h-7 text-slate-400" />
            </slot>
        </div>
        <h4 class="text-sm sm:text-base font-bold text-slate-800">{{ title }}</h4>
        <p class="text-xs sm:text-sm text-slate-500 max-w-sm mt-1 leading-relaxed">{{ message }}</p>
        <div v-if="$slots.action" class="mt-4 flex items-center gap-2">
            <slot name="action" />
        </div>
    </div>
</template>

