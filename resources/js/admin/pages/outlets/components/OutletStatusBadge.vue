<script setup>
import { computed } from 'vue';

const props = defineProps({
    status: {
        type: String,
        default: 'active',
    },
});

const isAct = computed(() => props.status === 'active');
</script>

<template>
    <span
        :class="[
            'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold border tracking-wide uppercase',
            isAct
                ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                : 'bg-slate-100 text-slate-600 border-slate-200'
        ]"
    >
        <span
            :class="[
                'w-1.5 h-1.5 rounded-full mr-1.5',
                isAct ? 'bg-emerald-500' : 'bg-slate-400'
            ]"
        ></span>
        {{ isAct ? 'Aktif' : 'Nonaktif' }}
    </span>
</template>

