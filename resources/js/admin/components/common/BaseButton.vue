<script setup>
import { computed } from 'vue';

const props = defineProps({
    type: {
        type: String,
        default: 'button',
    },
    variant: {
        type: String,
        default: 'primary',
        validator: (v) => ['primary', 'secondary', 'danger', 'success', 'outline', 'ghost'].includes(v),
    },
    size: {
        type: String,
        default: 'md',
        validator: (v) => ['sm', 'md', 'lg'].includes(v),
    },
    loading: {
        type: Boolean,
        default: false,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    block: {
        type: Boolean,
        default: false,
    },
});

const variantClasses = computed(() => {
    switch (props.variant) {
        case 'secondary':
            return 'bg-slate-100 text-slate-700 hover:bg-slate-200 border-slate-200 active:bg-slate-300';
        case 'danger':
            return 'bg-rose-600 text-white hover:bg-rose-700 border-rose-600 active:bg-rose-800 shadow-sm';
        case 'success':
            return 'bg-emerald-600 text-white hover:bg-emerald-700 border-emerald-600 active:bg-emerald-800 shadow-sm';
        case 'outline':
            return 'bg-transparent text-slate-700 hover:bg-slate-100 border-slate-300 active:bg-slate-200';
        case 'ghost':
            return 'bg-transparent text-slate-600 hover:bg-slate-100 border-transparent active:bg-slate-200';
        case 'primary':
        default:
            return 'bg-blue-600 text-white hover:bg-blue-700 border-blue-600 active:bg-blue-800 shadow-sm';
    }
});

const sizeClasses = computed(() => {
    switch (props.size) {
        case 'sm':
            return 'px-3 py-1.5 text-xs font-medium rounded-lg gap-1.5';
        case 'lg':
            return 'px-6 py-3 text-base font-medium rounded-xl gap-2.5';
        case 'md':
        default:
            return 'px-4 py-2 text-sm font-medium rounded-lg gap-2';
    }
});
</script>

<template>
    <button
        :type="type"
        :disabled="disabled || loading"
        class="inline-flex items-center justify-center border font-medium transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-blue-500/20 disabled:cursor-not-allowed disabled:opacity-60 cursor-pointer"
        :class="[variantClasses, sizeClasses, block ? 'w-full' : '']"
    >
        <svg
            v-if="loading"
            class="animate-spin -ml-0.5 h-4 w-4 text-current"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
        >
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>

        <slot />
    </button>
</template>

