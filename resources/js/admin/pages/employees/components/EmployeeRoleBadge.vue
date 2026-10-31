<script setup>
import { computed } from 'vue';

const props = defineProps({
    role: {
        type: String,
        default: 'cashier',
    },
});

const config = computed(() => {
    switch (props.role?.toLowerCase()) {
        case 'owner':
            return {
                label: 'Owner',
                classes: 'bg-purple-50 text-purple-700 border-purple-200',
            };
        case 'admin':
            return {
                label: 'Admin',
                classes: 'bg-blue-50 text-blue-700 border-blue-200',
            };
        case 'cashier':
        case 'kasir':
            return {
                label: 'Kasir',
                classes: 'bg-emerald-50 text-emerald-700 border-emerald-200',
            };
        case 'kitchen':
        case 'kitchen_head':
        case 'kitchen_staff':
            return {
                label: 'Dapur (Kitchen)',
                classes: 'bg-amber-50 text-amber-700 border-amber-200',
            };
        default:
            return {
                label: props.role ? ucfirst(props.role) : '-',
                classes: 'bg-slate-100 text-slate-700 border-slate-200',
            };
    }
});

function ucfirst(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
}
</script>

<template>
    <span
        :class="[
            'inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold border',
            config.classes,
        ]"
    >
        {{ config.label }}
    </span>
</template>

