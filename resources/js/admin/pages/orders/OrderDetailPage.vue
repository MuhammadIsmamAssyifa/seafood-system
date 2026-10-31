<script setup>
import { onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { ArrowLeft, User, Armchair, Clock, Hash, AlertCircle, RefreshCw } from 'lucide-vue-next';
import { useOrderStore } from '../../stores/order.store';
import { formatDate, formatTime, formatCurrency } from '../../utils/formatter';

import OrderStatusBadge from './components/OrderStatusBadge.vue';
import OrderItems from './components/OrderItems.vue';
import PaymentInfo from './components/PaymentInfo.vue';
import StatusTimeline from './components/StatusTimeline.vue';
import BaseButton from '../../components/common/BaseButton.vue';
import BaseLoading from '../../components/common/BaseLoading.vue';

const route = useRoute();
const router = useRouter();
const orderStore = useOrderStore();

const orderId = computed(() => route.params.id);
const order = computed(() => orderStore.currentOrder);

onMounted(() => {
    loadDetail();
});

async function loadDetail() {
    if (orderId.value) {
        await orderStore.fetchOrderDetail(orderId.value).catch(() => {});
    }
}
</script>

<template>
    <div class="space-y-6">
        <!-- Top Navigation & Actions -->
        <div class="flex items-center justify-between gap-4">
            <BaseButton
                variant="outline"
                size="sm"
                @click="router.push('/orders')"
            >
                <ArrowLeft class="w-4 h-4" />
                <span>Kembali ke Daftar Pesanan</span>
            </BaseButton>

            <BaseButton
                v-if="order"
                variant="ghost"
                size="sm"
                @click="loadDetail"
                :loading="orderStore.detailLoading"
            >
                <RefreshCw class="w-3.5 h-3.5" :class="[orderStore.detailLoading ? 'animate-spin' : '']" />
                <span>Segarkan</span>
            </BaseButton>
        </div>

        <!-- Loading State -->
        <div v-if="orderStore.detailLoading" class="py-12">
            <BaseLoading text="Memuat rincian pesanan..." />
        </div>

        <!-- Error State -->
        <div
            v-else-if="orderStore.error || !order"
            class="p-6 rounded-2xl bg-rose-50 border border-rose-200 text-center max-w-lg mx-auto space-y-4"
        >
            <AlertCircle class="w-12 h-12 text-rose-500 mx-auto" />
            <h3 class="text-base font-bold text-slate-800">Pesanan Tidak Ditemukan</h3>
            <p class="text-xs text-slate-500 leading-relaxed">
                {{ orderStore.error || 'Data pesanan dengan ID tersebut tidak tersedia atau sudah dihapus.' }}
            </p>
            <BaseButton variant="primary" size="sm" @click="router.push('/orders')">
                Kembali ke Daftar
            </BaseButton>
        </div>

        <!-- Order Content -->
        <div v-else class="space-y-6">
            <!-- Header Banner Card -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-lg font-mono">
                            #
                        </div>
                        <div>
                            <div class="flex items-center gap-2.5">
                                <h2 class="text-xl font-bold font-mono text-slate-900">
                                    {{ order.order_number }}
                                </h2>
                                <OrderStatusBadge :status="order.status?.value || order.status" size="lg" />
                            </div>
                            <p class="text-xs text-slate-400 mt-0.5">
                                Dibuat pada {{ formatDate(order.created_at, 'dddd, DD MMMM YYYY') }} pukul {{ formatTime(order.created_at) }}
                            </p>
                        </div>
                    </div>

                    <!-- Grand Total Highlight -->
                    <div class="text-left md:text-right bg-slate-50 md:bg-transparent p-3 md:p-0 rounded-xl">
                        <span class="text-xs text-slate-500 block">Total Nilai Transaksi</span>
                        <span class="text-2xl font-black font-mono text-blue-700">
                            {{ formatCurrency(order.grand_total) }}
                        </span>
                    </div>
                </div>

                <!-- Info Badges Row -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-4 text-xs">
                    <div>
                        <span class="text-slate-400 block mb-0.5">Pelanggan</span>
                        <div class="font-bold text-slate-800 flex items-center gap-1.5">
                            <User class="w-3.5 h-3.5 text-slate-400" />
                            <span>{{ order.customer_name || 'Pelanggan Umum' }}</span>
                        </div>
                    </div>

                    <div>
                        <span class="text-slate-400 block mb-0.5">Meja Restoran</span>
                        <div class="font-bold text-slate-800 flex items-center gap-1.5">
                            <Armchair class="w-3.5 h-3.5 text-slate-400" />
                            <span>{{ order.table?.number ? `Meja ${order.table.number}` : 'Takeaway / Luar' }}</span>
                        </div>
                    </div>

                    <div>
                        <span class="text-slate-400 block mb-0.5">Jumlah Tamu</span>
                        <span class="font-bold text-slate-800 font-mono">
                            {{ order.guest_count || 1 }} Orang
                        </span>
                    </div>

                    <div>
                        <span class="text-slate-400 block mb-0.5">Staf Pembuat Order</span>
                        <span class="font-semibold text-slate-800">
                            {{ order.created_by?.name || 'Kasir POS' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Detail Grid Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left 2 Cols: Ordered Items Batches -->
                <div class="lg:col-span-2 space-y-6">
                    <OrderItems :batches="order.batches || []" />
                </div>

                <!-- Right 1 Col: Payment Info & Status History Timeline -->
                <div class="space-y-6">
                    <PaymentInfo
                        :payments="order.payments || []"
                        :subtotal="order.subtotal"
                        :discount="order.discount"
                        :grand-total="order.grand_total"
                    />

                    <StatusTimeline
                        :histories="order.status_histories || []"
                        :created-at="order.created_at"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

