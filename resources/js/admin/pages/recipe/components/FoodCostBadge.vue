<script setup>
import { computed } from 'vue';

const props = defineProps({
    percentage: {
        type: [Number, String],
        default: 0,
    },
});

const numericPercent = computed(() => Number(props.percentage) || 0);

const config = computed(() => {
    const val = numericPercent.value;
    if (val <= 0) {
        return { label: '0%', bg: 'bg-slate-100 text-slate-600 border-slate-200' };
    }
    if (val <= 35) {
        return { label: `${val}% (Ideal)`, bg: 'bg-emerald-50 text-emerald-700 border-emerald-200' };
    }
    if (val <= 50) {
        return { label: `${val}% (Sedang)`, bg: 'bg-amber-50 text-amber-700 border-amber-200' };
    }
    return { label: `${val}% (Tinggi)`, bg: 'bg-rose-50 text-rose-700 border-rose-200' };
});
</script>

<template>
    <span
        :class="[
            'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold border tracking-wide',
            config.bg
        ]"
    >
        {{ config.label }}
    </span>
</template>

