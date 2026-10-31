<script setup>
import { Clock, User, Hash, Utensils } from 'lucide-vue-next';
import { formatCurrency, formatTime, formatRelativeTime } from '../../../utils/formatter';
import { getStatusBadge } from '../../../utils/helper';
import BaseCard from '../../../components/common/BaseCard.vue';
import BaseEmpty from '../../../components/common/BaseEmpty.vue';

defineProps({
    orders: {
        type: Array,
        default: () => [],
    },
    loading: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <BaseCard title="Transaksi & Pesanan Terbaru" subtitle="5 transaksi pesanan terakhir yang tercatat di sistem">
        <!-- Loading Skeleton -->
        <div v-if="loading" class="animate-pulse space-y-3 py-2">
            <div v-for="i in 5" :key="i" class="h-12 bg-slate-100 rounded-lg"></div>
        </div>

        <!-- Empty State -->
        <div v-else-if="!orders || orders.length === 0" class="py-4">
            <BaseEmpty
                title="Belum Ada Pesanan"
                message="Daftar pesanan terbaru akan muncul di sini secara otomatis."
            />
        </div>

        <!-- Order Table -->
        <div v-else class="overflow-x-auto -mx-6">
            <table class="min-w-full divide-y divide-slate-100 text-left text-sm">
                <thead>
                    <tr class="bg-slate-50/70 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="px-6 py-3">No. Order</th>
                        <th class="px-4 py-3">Meja / Pelanggan</th>
                        <th class="px-4 py-3">Waktu</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-6 py-3 text-right">Total Transaksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    <tr
                        v-for="order in orders"
                        :key="order.id"
                        class="hover:bg-slate-50/60 transition-colors"
                    >
                        <!-- Order Number -->
                        <td class="px-6 py-3.5 whitespace-nowrap">
                            <span class="font-mono font-bold text-blue-600">
                                #{{ order.order_number }}
                            </span>
                        </td>

                        <!-- Table & Customer -->
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded text-xs font-mono font-semibold bg-slate-100 text-slate-700">
                                    {{ order.table_number ? `Meja ${order.table_number}` : 'Takeaway' }}
                                </span>
                                <span class="text-slate-800 text-xs">
                                    {{ order.customer_name || 'Pelanggan' }}
                                </span>
                            </div>
                        </td>

                        <!-- Time -->
                        <td class="px-4 py-3.5 whitespace-nowrap text-xs text-slate-500">
                            <div class="flex items-center gap-1">
                                <Clock class="w-3.5 h-3.5 text-slate-400" />
                                <span>{{ formatTime(order.created_at) }}</span>
                                <span class="text-[10px] text-slate-400">({{ formatRelativeTime(order.created_at) }})</span>
                            </div>
                        </td>

                        <!-- Status Badge -->
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                :class="getStatusBadge(order.status).class"
                            >
                                {{ getStatusBadge(order.status).label }}
                            </span>
                        </td>

                        <!-- Total -->
                        <td class="px-6 py-3.5 whitespace-nowrap text-right font-mono font-bold text-slate-900">
                            {{ formatCurrency(order.grand_total) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </BaseCard>
</template>

