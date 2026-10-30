<template>
    <div class="flex flex-col items-center justify-center p-12 text-center h-full min-h-[300px]">
        <div class="relative flex items-center justify-center">
            <div class="w-16 h-16 rounded-full border-4 border-blue-500/20 border-t-blue-500 animate-spin"></div>
            <div class="absolute w-10 h-10 rounded-full border-2 border-emerald-400/20 border-b-emerald-400 animate-spin" style="animation-direction: reverse; animation-duration: 0.8s;"></div>
        </div>
        <h4 class="mt-4 text-base font-semibold text-slate-200 tracking-wide">{{ title }}</h4>
        <p class="mt-1 text-xs text-slate-400">{{ subtitle }}</p>
    </div>
</template>

<script setup>
defineProps({
    title: {
        type: String,
        default: 'Memuat Data Dapur...',
    },
    subtitle: {
        type: String,
        default: 'Mengambil antrean pesanan dari server',
    },
});
</script>

