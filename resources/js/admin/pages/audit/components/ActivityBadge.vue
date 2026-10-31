<script setup>
import { computed } from 'vue';

const props = defineProps({
    action: {
        type: String,
        default: '',
    },
});

const config = computed(() => {
    const act = (props.action || '').toLowerCase();

    if (act.includes('login')) {
        return { label: 'Login', bg: 'bg-emerald-50 text-emerald-700 border-emerald-200' };
    }
    if (act.includes('logout')) {
        return { label: 'Logout', bg: 'bg-slate-50 text-slate-700 border-slate-200' };
    }
    if (act.includes('create_order') || act.includes('order_created')) {
        return { label: 'Order Baru', bg: 'bg-blue-50 text-blue-700 border-blue-200' };
    }
    if (act.includes('cancel')) {
        return { label: 'Batal', bg: 'bg-rose-50 text-rose-700 border-rose-200' };
    }
    if (act.includes('payment')) {
        return { label: 'Pembayaran', bg: 'bg-teal-50 text-teal-700 border-teal-200' };
    }
    if (act.includes('stock') || act.includes('inventory')) {
        return { label: 'Penyesuaian Stok', bg: 'bg-amber-50 text-amber-700 border-amber-200' };
    }
    if (act.includes('waste')) {
        return { label: 'Waste Item', bg: 'bg-red-50 text-red-700 border-red-200' };
    }
    if (act.includes('menu')) {
        return { label: 'Katalog Menu', bg: 'bg-indigo-50 text-indigo-700 border-indigo-200' };
    }
    if (act.includes('user') || act.includes('employee') || act.includes('role')) {
        return { label: 'Karyawan & Akses', bg: 'bg-purple-50 text-purple-700 border-purple-200' };
    }
    if (act.includes('setting')) {
        return { label: 'Pengaturan Sistem', bg: 'bg-cyan-50 text-cyan-700 border-cyan-200' };
    }

    return { label: props.action || 'Aktivitas', bg: 'bg-gray-50 text-gray-700 border-gray-200' };
});
</script>

<template>
    <span
        :class="[
            'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border uppercase tracking-wider',
            config.bg
        ]"
    >
        {{ config.label }}
    </span>
</template>

