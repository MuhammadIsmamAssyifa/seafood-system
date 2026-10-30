<template>
    <button
        :type="type"
        :disabled="disabled || loading"
        :class="[
            'inline-flex items-center justify-center font-semibold transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 select-none active:scale-[0.98]',
            sizeClasses,
            variantClasses,
            (disabled || loading) ? 'opacity-60 cursor-not-allowed shadow-none' : 'cursor-pointer',
            customClass,
        ]"
        @click="$emit('click', $event)"
    >
        <!-- Loading Spinner -->
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

        <slot name="icon-left" />
        <slot />
        <slot name="icon-right" />
    </button>
</template>

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
        validator: (v) => ['primary', 'secondary', 'success', 'danger', 'warning', 'ghost'].includes(v),
    },
    size: {
        type: String,
        default: 'md',
        validator: (v) => ['sm', 'md', 'lg', 'xl'].includes(v),
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    loading: {
        type: Boolean,
        default: false,
    },
    customClass: {
        type: String,
        default: '',
    },
});

defineEmits(['click']);

const sizeClasses = computed(() => {
    switch (props.size) {
        case 'sm':
            return 'px-3 py-1.5 text-xs rounded-lg gap-1.5';
        case 'lg':
            return 'px-5 py-3 text-base rounded-xl gap-2.5';
        case 'xl':
            return 'px-7 py-4 text-lg rounded-2xl gap-3 shadow-lg'; // Ideal for kitchen touch
        case 'md':
        default:
            return 'px-4 py-2 text-sm rounded-lg gap-2';
    }
});

const variantClasses = computed(() => {
    switch (props.variant) {
        case 'secondary':
            return 'bg-slate-800 hover:bg-slate-700 text-slate-100 border border-slate-700 focus:ring-slate-500 shadow-sm';
        case 'success':
            return 'bg-emerald-600 hover:bg-emerald-500 text-white focus:ring-emerald-400 shadow-md shadow-emerald-950/30';
        case 'danger':
            return 'bg-rose-600 hover:bg-rose-500 text-white focus:ring-rose-400 shadow-md shadow-rose-950/30';
        case 'warning':
            return 'bg-amber-600 hover:bg-amber-500 text-white focus:ring-amber-400 shadow-md shadow-amber-950/30';
        case 'ghost':
            return 'bg-transparent hover:bg-slate-800 text-slate-300 hover:text-white focus:ring-slate-600';
        case 'primary':
        default:
            return 'bg-blue-600 hover:bg-blue-500 text-white focus:ring-blue-400 shadow-md shadow-blue-950/30';
    }
});
</script>

