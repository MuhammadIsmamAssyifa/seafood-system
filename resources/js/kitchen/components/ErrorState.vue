<template>
    <div class="flex flex-col items-center justify-center p-12 text-center h-full min-h-[350px]">
        <div class="w-20 h-20 rounded-3xl bg-rose-950/40 border border-rose-800/60 flex items-center justify-center shadow-inner mb-4">
            <svg class="w-10 h-10 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
        </div>
        <h3 class="text-lg font-bold text-slate-100">{{ title }}</h3>
        <p class="text-sm text-slate-400 mt-1 max-w-sm">{{ message }}</p>

        <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
            <button
                type="button"
                class="px-5 py-2.5 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-sm font-semibold shadow-md shadow-blue-900/30 transition-all flex items-center gap-2 active:scale-95"
                @click="$emit('retry')"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Coba Lagi
            </button>
            <slot name="extra-actions" />
        </div>
    </div>
</template>

<script setup>
defineProps({
    title: {
        type: String,
        default: 'Terjadi Kesalahan',
    },
    message: {
        type: String,
        default: 'Gagal memuat antrean dapur dari server.',
    },
});

defineEmits(['retry']);
</script>

