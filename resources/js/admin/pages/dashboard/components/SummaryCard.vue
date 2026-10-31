<script setup>
import { computed } from 'vue';
import {
    DollarSign,
    ShoppingCart,
    TrendingUp,
    Receipt,
    ArrowUpRight,
} from 'lucide-vue-next';
import { formatCurrency, formatNumber } from '../../../utils/formatter';

const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    value: {
        type: [Number, String],
        required: true,
    },
    subtitle: {
        type: String,
        default: '',
    },
    type: {
        type: String,
        default: 'sales', // sales, orders, profit, avg
    },
    badge: {
        type: String,
        default: '',
    },
    loading: {
        type: Boolean,
        default: false,
    },
});

const config = computed(() => {
    switch (props.type) {
        case 'orders':
            return {
                icon: ShoppingCart,
                iconBg: 'bg-indigo-50 text-indigo-600 border-indigo-100',
                displayValue: `${formatNumber(props.value)} Order`,
            };
        case 'profit':
            return {
                icon: TrendingUp,
                iconBg: 'bg-emerald-50 text-emerald-600 border-emerald-100',
                displayValue: formatCurrency(props.value),
            };
        case 'avg':
            return {
                icon: Receipt,
                iconBg: 'bg-amber-50 text-amber-600 border-amber-100',
                displayValue: formatCurrency(props.value),
            };
        case 'sales':
        default:
            return {
                icon: DollarSign,
                iconBg: 'bg-blue-50 text-blue-600 border-blue-100',
                displayValue: formatCurrency(props.value),
            };
    }
});
</script>

<template>
    <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs hover:shadow-md transition-all duration-200">
        <div v-if="loading" class="animate-pulse space-y-3">
            <div class="flex justify-between items-center">
                <div class="h-4 bg-slate-200 rounded w-20"></div>
                <div class="w-10 h-10 bg-slate-200 rounded-xl"></div>
            </div>
            <div class="h-7 bg-slate-200 rounded w-32"></div>
            <div class="h-3 bg-slate-100 rounded w-24"></div>
        </div>

        <div v-else>
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">
                    {{ title }}
                </span>
                <div class="w-10 h-10 rounded-xl flex items-center justify-center border" :class="config.iconBg">
                    <component :is="config.icon" class="w-5 h-5" />
                </div>
            </div>

            <div class="text-2xl font-bold text-slate-800 tracking-tight">
                {{ config.displayValue }}
            </div>

            <div class="mt-2 flex items-center justify-between text-xs">
                <span class="text-slate-500 truncate">{{ subtitle }}</span>
                <span
                    v-if="badge"
                    class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full font-semibold font-mono text-[10px] bg-emerald-50 text-emerald-700 border border-emerald-200 shrink-0"
                >
                    <ArrowUpRight class="w-3 h-3" />
                    {{ badge }}
                </span>
            </div>
        </div>
    </div>
</template>

