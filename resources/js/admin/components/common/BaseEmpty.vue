<script setup>
defineProps({
    title: {
        type: String,
        default: 'Tidak Ada Data',
    },
    message: {
        type: String,
        default: 'Belum ada data untuk ditampilkan saat ini.',
    },
});
</script>

<template>
    <div class="flex flex-col items-center justify-center p-8 text-center bg-white rounded-xl border border-dashed border-slate-300">
        <div class="w-12 h-12 mb-3.5 rounded-full bg-slate-100 flex items-center justify-center text-slate-400">
            <slot name="icon">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                </svg>
            </slot>
        </div>
        <h4 class="text-sm font-semibold text-slate-700">{{ title }}</h4>
        <p class="text-xs text-slate-500 max-w-sm mt-1">{{ message }}</p>
        <div v-if="$slots.action" class="mt-4">
            <slot name="action" />
        </div>
    </div>
</template>

