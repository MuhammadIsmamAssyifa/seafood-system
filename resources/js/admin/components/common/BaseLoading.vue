<script setup>
defineProps({
    type: {
        type: String,
        default: 'spinner',
        validator: (v) => ['spinner', 'skeleton', 'card'].includes(v),
    },
    text: {
        type: String,
        default: 'Memuat data...',
    },
    height: {
        type: String,
        default: 'h-48',
    },
});
</script>

<template>
    <div v-if="type === 'spinner'" class="flex flex-col items-center justify-center p-8 text-slate-500" :class="height">
        <svg class="animate-spin h-8 w-8 text-blue-600 mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span class="text-sm font-medium text-slate-600">{{ text }}</span>
    </div>

    <div v-else-if="type === 'skeleton'" class="animate-pulse space-y-4 p-4">
        <div class="h-4 bg-slate-200 rounded w-1/3"></div>
        <div class="h-8 bg-slate-200 rounded w-1/2"></div>
        <div class="h-4 bg-slate-100 rounded w-full"></div>
    </div>

    <div v-else-if="type === 'card'" class="animate-pulse bg-white p-6 rounded-xl border border-slate-200/80">
        <div class="flex items-center justify-between mb-4">
            <div class="h-4 bg-slate-200 rounded w-24"></div>
            <div class="h-8 w-8 bg-slate-200 rounded-lg"></div>
        </div>
        <div class="h-7 bg-slate-200 rounded w-36 mb-2"></div>
        <div class="h-3.5 bg-slate-100 rounded w-48"></div>
    </div>
</template>

