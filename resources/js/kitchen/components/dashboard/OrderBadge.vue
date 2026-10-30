<template>
    <span
        :class="[
            'inline-flex items-center font-bold tracking-wide rounded-full border transition-colors select-none',
            sizeClasses,
            badgeVariant.classes,
        ]"
    >
        <span :class="['w-1.5 h-1.5 rounded-full mr-1.5 shrink-0', badgeVariant.dot]"></span>
        {{ badgeVariant.label }}
    </span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    status: {
        type: String,
        default: 'waiting',
    },
    size: {
        type: String,
        default: 'md', // 'sm' | 'md' | 'lg'
    },
});

const sizeClasses = computed(() => {
    switch (props.size) {
        case 'sm': return 'px-2 py-0.5 text-[10px]';
        case 'lg': return 'px-3.5 py-1.5 text-sm';
        case 'md':
        default:
            return 'px-2.5 py-1 text-xs';
    }
});

const badgeVariant = computed(() => {
    const s = (props.status || '').toLowerCase();

    if (['cooking', 'preparing'].includes(s)) {
        return {
            label: 'Dimasak',
            classes: 'bg-blue-950/80 text-blue-300 border-blue-700/80',
            dot: 'bg-blue-400 animate-pulse',
        };
    }

    if (['ready'].includes(s)) {
        return {
            label: 'Siap Saji',
            classes: 'bg-emerald-950/80 text-emerald-300 border-emerald-700/80',
            dot: 'bg-emerald-400 animate-ping',
        };
    }

    if (['completed', 'served'].includes(s)) {
        return {
            label: 'Selesai',
            classes: 'bg-slate-800 text-slate-300 border-slate-700',
            dot: 'bg-slate-500',
        };
    }

    // Default waiting / sent / submitted
    return {
        label: 'Menunggu',
        classes: 'bg-amber-950/80 text-amber-300 border-amber-700/80',
        dot: 'bg-amber-400 animate-pulse',
    };
});
</script>

