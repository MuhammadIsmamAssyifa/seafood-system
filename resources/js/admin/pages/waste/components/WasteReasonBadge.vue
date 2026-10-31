<script setup>
import { computed } from 'vue';

const props = defineProps({
    reason: {
        type: String,
        default: 'other',
    },
    label: {
        type: String,
        default: '',
    },
});

const config = computed(() => {
    switch (props.reason) {
        case 'expired':
            return {
                label: props.label || 'Kadaluarsa / Expired',
                classes: 'bg-red-50 text-red-700 border-red-200',
                dot: 'bg-red-500',
            };
        case 'damaged':
            return {
                label: props.label || 'Rusak / Busuk',
                classes: 'bg-orange-50 text-orange-700 border-orange-200',
                dot: 'bg-orange-500',
            };
        case 'cooking_loss':
            return {
                label: props.label || 'Susut Masak / Handling',
                classes: 'bg-amber-50 text-amber-700 border-amber-200',
                dot: 'bg-amber-500',
            };
        case 'wrong_preparation':
            return {
                label: props.label || 'Salah Olah / Masak',
                classes: 'bg-purple-50 text-purple-700 border-purple-200',
                dot: 'bg-purple-500',
            };
        case 'other':
        default:
            return {
                label: props.label || 'Lainnya',
                classes: 'bg-slate-50 text-slate-700 border-slate-200',
                dot: 'bg-slate-500',
            };
    }
});
</script>

<template>
    <span
        :class="[
            'inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium border',
            config.classes,
        ]"
    >
        <span :class="['w-1.5 h-1.5 rounded-full', config.dot]"></span>
        {{ config.label }}
    </span>
</template>

