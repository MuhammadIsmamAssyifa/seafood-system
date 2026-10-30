<template>
    <AppLayout>
        <div class="w-full h-full flex flex-col space-y-4">
            <!-- State 1: Loading -->
            <div v-if="kitchenStore.detailLoading" class="flex-1 flex items-center justify-center">
                <Loading
                    title="Loading order detail..."
                    subtitle="Mengambil rincian item, metode masak, dan station dari server"
                />
            </div>

            <!-- State 2: Error -->
            <div v-else-if="kitchenStore.detailError" class="flex-1 flex items-center justify-center">
                <ErrorState
                    title="Gagal Mengambil Detail Order"
                    :message="kitchenStore.detailError"
                    @retry="loadOrderDetail"
                >
                    <template #extra-actions>
                        <button
                            type="button"
                            class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-xl text-xs font-semibold border border-slate-700 transition-colors"
                            @click="goBack"
                        >
                            Kembali ke Queue
                        </button>
                    </template>
                </ErrorState>
            </div>

            <!-- State 3: Order Detail Content (Full Width & No Visible Scrollbar) -->
            <div v-else-if="order" class="space-y-4 pb-8 overflow-y-auto no-scrollbar">
                <!-- Order Header (Order #, Table, Customer, Guests, Time, Status) -->
                <OrderHeader
                    :order="order"
                    @back="goBack"
                    @accepted="loadOrderDetail"
                />

                <!-- Order Notes Component -->
                <OrderNote
                    :note="order.note || order.notes"
                />

                <!-- Order Items Section (Cards with cooking method, station, quantity) -->
                <OrderItemList
                    :items="order.items || []"
                />

                <!-- Bottom Back Navigation Button -->
                <div class="pt-4 flex items-center justify-between border-t border-slate-800/80">
                    <button
                        type="button"
                        class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white font-bold text-sm border border-slate-700 transition-all active:scale-95 shadow-md"
                        @click="goBack"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <span>Kembali ke Kitchen Queue</span>
                    </button>

                    <span class="text-xs text-slate-500 font-mono">
                        KDS Seafood System &bull; Operasional Dapur
                    </span>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed, onMounted, onUnmounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useKitchenStore } from '@/kitchen/stores/kitchen.store';
import { subscribeToKitchenChannel, unsubscribeFromKitchenChannel } from '@/kitchen/services/pusher';

import AppLayout from '@/kitchen/components/AppLayout.vue';
import OrderHeader from '@/kitchen/components/order/OrderHeader.vue';
import OrderItemList from '@/kitchen/components/order/OrderItemList.vue';
import OrderNote from '@/kitchen/components/order/OrderNote.vue';
import Loading from '@/kitchen/components/Loading.vue';
import ErrorState from '@/kitchen/components/ErrorState.vue';

const route = useRoute();
const router = useRouter();
const kitchenStore = useKitchenStore();

const order = computed(() => {
    return kitchenStore.selectedOrder;
});

const loadOrderDetail = async () => {
    const orderId = route.params.id;
    if (orderId) {
        await kitchenStore.fetchOrderDetail(orderId);
    }
};

const goBack = () => {
    router.push({ name: 'kitchen.dashboard' });
};

onMounted(() => {
    loadOrderDetail();

    subscribeToKitchenChannel({
        onItemStatusChanged: () => {
            loadOrderDetail();
        },
        onBatchUpdated: () => {
            loadOrderDetail();
        },
    });
});

onUnmounted(() => {
    unsubscribeFromKitchenChannel();
});
</script>

