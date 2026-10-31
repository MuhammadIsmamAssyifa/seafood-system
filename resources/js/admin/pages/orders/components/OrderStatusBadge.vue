<script setup>
import { computed } from 'vue';
import { getStatusBadge } from '../../../utils/helper';

const props = defineProps({
    status: {
        type: String,
        required: true,
    },
    size: {
        type: String,
        default: 'md',
        validator: (v) => ['sm', 'md', 'lg'].includes(v),
    },
});

const info = computed(() => getStatusBadge(props.status));

const sizeClass = computed(() => {
    switch (props.size) {
        case 'sm':
            return 'px-2 py-0.5 text-[10px]';
        case 'lg':
            return 'px-3.5 py-1 text-xs font-bold';
        case 'md':
        default:
            return 'px-2.5 py-0.5 text-xs';
    }
});
</script>

<template>
    <span
        class="inline-flex items-center gap-1 font-medium rounded-full"
        :class="[info.class, sizeClass]"
    >
        <span class="w-1.5 h-1.5 rounded-full bg-current opacity-70"></span>
        <span>{{ info.label }}</span>
    </span>
</template>

