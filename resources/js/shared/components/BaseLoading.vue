<template>
    <div :class="['flex flex-col items-center justify-center p-6 text-center select-none', fullPage ? 'fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm' : '']">
        <div class="relative flex items-center justify-center">
            <!-- Outer pulsing ring -->
            <div class="w-14 h-14 rounded-full border-4 border-blue-500/20 border-t-blue-500 animate-spin"></div>
            <!-- Inner accent -->
            <div class="absolute w-8 h-8 rounded-full border-2 border-emerald-400/30 border-b-emerald-400 animate-spin" style="animation-direction: reverse; animation-duration: 0.8s;"></div>
        </div>
        <p v-if="text" class="mt-4 text-sm font-medium text-slate-300 tracking-wide animate-pulse">
            {{ text }}
        </p>
    </div>
</template>

<script setup>
defineProps({
    text: {
        type: String,
        default: 'Memuat data...',
    },
    fullPage: {
        type: Boolean,
        default: false,
    },
});
</script>

