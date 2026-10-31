<script setup>
import { computed } from 'vue';

const props = defineProps({
    status: {
        type: String,
        default: 'active',
    },
});

const config = computed(() => {
    if (props.status === 'active') {
        return {
            label: 'Aktif',
            classes: 'bg-emerald-50 text-emerald-700 border-emerald-200',
            dot: 'bg-emerald-500',
        };
    }
    return {
        label: 'Nonaktif',
        classes: 'bg-slate-100 text-slate-600 border-slate-200',
        dot: 'bg-slate-400',
    };
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

