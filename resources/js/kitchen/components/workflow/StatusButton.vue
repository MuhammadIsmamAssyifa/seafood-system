<template>
    <button
        type="button"
        :disabled="disabled || loading"
        :class="[
            'inline-flex items-center justify-center font-bold tracking-wide rounded-xl transition-all duration-150 select-none shadow-md active:scale-95 focus:outline-none focus:ring-2 focus:ring-offset-2',
            sizeClasses,
            actionClasses,
            (disabled || loading) ? 'opacity-60 cursor-not-allowed shadow-none' : 'cursor-pointer',
        ]"
        @click="$emit('click', $event)"
    >
        <!-- Loading spinner -->
        <svg
            v-if="loading"
            class="animate-spin -ml-1 mr-2 h-4 w-4 text-current"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
        >
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
        </svg>

        <!-- Icon slot or default action icons -->
        <span v-if="!loading" class="mr-1.5 shrink-0">
            <!-- Accept Icon -->
            <svg v-if="actionType === 'accept'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <!-- Prepare / Cook Icon -->
            <svg v-else-if="actionType === 'prepare'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" />
            </svg>
            <!-- Ready Icon -->
            <svg v-else-if="actionType === 'ready'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <!-- Serve Icon -->
            <svg v-else-if="actionType === 'serve'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
            </svg>
        </span>

        <!-- Label -->
        <span>{{ buttonLabel }}</span>
    </button>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    actionType: {
        type: String,
        required: true, // 'accept' | 'prepare' | 'ready' | 'serve'
    },
    label: {
        type: String,
        default: null,
    },
    size: {
        type: String,
        default: 'md', // 'sm' | 'md' | 'lg'
    },
    loading: {
        type: Boolean,
        default: false,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

defineEmits(['click']);

const buttonLabel = computed(() => {
    if (props.label) return props.label;
    if (props.loading) return 'Memproses...';

    switch (props.actionType) {
        case 'accept': return 'Terima Order';
        case 'prepare': return 'Mulai Masak';
        case 'ready': return 'Selesai Masak';
        case 'serve': return 'Sajikan (Serve)';
        default: return 'Lanjutkan';
    }
});

const sizeClasses = computed(() => {
    switch (props.size) {
        case 'sm': return 'px-3 py-1.5 text-xs';
        case 'lg': return 'px-5 py-3 text-base';
        case 'md':
        default:
            return 'px-4 py-2 text-sm';
    }
});

const actionClasses = computed(() => {
    switch (props.actionType) {
        case 'accept':
            return 'bg-blue-600 hover:bg-blue-500 text-white focus:ring-blue-400 shadow-blue-900/30';
        case 'prepare':
            return 'bg-indigo-600 hover:bg-indigo-500 text-white focus:ring-indigo-400 shadow-indigo-900/30';
        case 'ready':
            return 'bg-emerald-600 hover:bg-emerald-500 text-white focus:ring-emerald-400 shadow-emerald-950/30';
        case 'serve':
            return 'bg-teal-600 hover:bg-teal-500 text-white focus:ring-teal-400 shadow-teal-950/30';
        default:
            return 'bg-slate-700 hover:bg-slate-600 text-white focus:ring-slate-400';
    }
});
</script>

