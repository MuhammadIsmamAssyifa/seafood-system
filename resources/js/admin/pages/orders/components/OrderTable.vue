<script setup>
import { useRouter } from 'vue-router';
import { Eye, Clock, Users, ChevronLeft, ChevronRight, CreditCard } from 'lucide-vue-next';
import { formatCurrency, formatTime, formatDate } from '../../../utils/formatter';
import OrderStatusBadge from './OrderStatusBadge.vue';
import BaseCard from '../../../components/common/BaseCard.vue';
import BaseEmpty from '../../../components/common/BaseEmpty.vue';

const props = defineProps({
    orders: {
        type: Array,
        default: () => [],
    },
    pagination: {
        type: Object,
        required: true,
    },
    loading: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['page-change']);

const router = useRouter();

function getPaymentBadge(order) {
    const payments = order.payments || [];
    const isPaid = payments.some((p) => p.status === 'paid' || p.status?.value === 'paid');
    if (isPaid) {
        return { label: 'Lunas', class: 'bg-emerald-50 text-emerald-700 border-emerald-200' };
    }
    return { label: 'Belum Lunas', class: 'bg-amber-50 text-amber-700 border-amber-200' };
}
</script>

<template>
    <BaseCard padding="p-0">
        <!-- Loading Skeleton -->
        <div v-if="loading" class="p-6 space-y-3">
            <div v-for="i in 6" :key="i" class="h-12 bg-slate-100 rounded-lg animate-pulse"></div>
        </div>

        <!-- Empty State -->
        <div v-else-if="!orders || orders.length === 0" class="p-8">
            <BaseEmpty
                title="Tidak Ada Pesanan Ditemukan"
                message="Tidak ada data pesanan yang sesuai dengan kriteria filter atau pencarian Anda."
            />
        </div>

        <!-- Table View -->
        <div v-else class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-left text-sm">
                <thead>
                    <tr class="bg-slate-50/70 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="px-6 py-3.5">No. Order</th>
                        <th class="px-4 py-3.5">Pelanggan & Tamu</th>
                        <th class="px-4 py-3.5">Meja</th>
                        <th class="px-4 py-3.5">Waktu Transaksi</th>
                        <th class="px-4 py-3.5">Status Pesanan</th>
                        <th class="px-4 py-3.5">Pembayaran</th>
                        <th class="px-4 py-3.5 text-right">Total Transaksi</th>
                        <th class="px-6 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    <tr
                        v-for="order in orders"
                        :key="order.id"
                        class="hover:bg-slate-50/70 transition-colors cursor-pointer"
                        @click="router.push(`/orders/${order.id}`)"
                    >
                        <!-- Order Number -->
                        <td class="px-6 py-3.5 whitespace-nowrap">
                            <span class="font-mono font-bold text-blue-600 hover:underline">
                                #{{ order.order_number }}
                            </span>
                        </td>

                        <!-- Customer & Guest -->
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <div class="text-xs font-semibold text-slate-800">
                                {{ order.customer_name || 'Pelanggan Umum' }}
                            </div>
                            <div v-if="order.guest_count" class="text-[10px] text-slate-400 flex items-center gap-1 mt-0.5">
                                <Users class="w-3 h-3" />
                                <span>{{ order.guest_count }} orang</span>
                            </div>
                        </td>

                        <!-- Table -->
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <span
                                class="px-2 py-0.5 rounded text-xs font-mono font-semibold"
                                :class="[order.table?.number ? 'bg-slate-100 text-slate-800' : 'bg-amber-50 text-amber-800']"
                            >
                                {{ order.table?.number ? `Meja ${order.table.number}` : 'Takeaway' }}
                            </span>
                        </td>

                        <!-- Date & Time -->
                        <td class="px-4 py-3.5 whitespace-nowrap text-xs text-slate-500">
                            <div class="flex items-center gap-1 font-medium">
                                <Clock class="w-3.5 h-3.5 text-slate-400" />
                                <span>{{ formatTime(order.created_at) }}</span>
                                <span class="text-[10px] text-slate-400">({{ formatDate(order.created_at, 'DD/MM') }})</span>
                            </div>
                        </td>

                        <!-- Status Badge -->
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <OrderStatusBadge :status="order.status?.value || order.status" />
                        </td>

                        <!-- Payment Status -->
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <span
                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold border"
                                :class="getPaymentBadge(order).class"
                            >
                                <CreditCard class="w-3 h-3" />
                                {{ getPaymentBadge(order).label }}
                            </span>
                        </td>

                        <!-- Grand Total -->
                        <td class="px-4 py-3.5 whitespace-nowrap text-right font-mono font-bold text-slate-900">
                            {{ formatCurrency(order.grand_total) }}
                        </td>

                        <!-- Action Button -->
                        <td class="px-6 py-3.5 whitespace-nowrap text-center">
                            <router-link
                                :to="`/orders/${order.id}`"
                                @click.stop
                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold text-blue-600 bg-blue-50 hover:bg-blue-100 border border-blue-200 transition-colors"
                            >
                                <Eye class="w-3.5 h-3.5" />
                                <span>Detail</span>
                            </router-link>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination Controls -->
        <template #footer v-if="pagination.last_page > 1">
            <div class="flex items-center justify-between text-xs text-slate-600">
                <span>
                    Menampilkan halaman <strong>{{ pagination.current_page }}</strong> dari <strong>{{ pagination.last_page }}</strong> (Total {{ pagination.total }} pesanan)
                </span>

                <div class="flex items-center gap-1.5">
                    <button
                        type="button"
                        :disabled="pagination.current_page <= 1 || loading"
                        @click="emit('page-change', pagination.current_page - 1)"
                        class="p-1.5 rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer transition-colors"
                    >
                        <ChevronLeft class="w-4 h-4" />
                    </button>

                    <button
                        type="button"
                        :disabled="pagination.current_page >= pagination.last_page || loading"
                        @click="emit('page-change', pagination.current_page + 1)"
                        class="p-1.5 rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer transition-colors"
                    >
                        <ChevronRight class="w-4 h-4" />
                    </button>
                </div>
            </div>
        </template>
    </BaseCard>
</template>

