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
                <!-- Order Header (Order #, Table, Customer, Guests, Time, Status, Batch Actions) -->
                <OrderHeader
                    :order="order"
                    @back="goBack"
                    @accepted="loadOrderDetail"
                    @prepare-all="handlePrepareAll"
                    @ready-all="handleReadyAll"
                    @serve-all="handleServeAll"
                />

                <!-- Order Notes Component -->
                <OrderNote
                    :note="order.note || order.notes"
                />

                <!-- Order Items Section (Cards with cooking method, station, quantity) -->
                <OrderItemList
                    :items="order.items || []"
                    @report-issue="openReportModal"
                />
            </div>
        </div>

        <!-- Modal: Lapor Masalah / Stok Habis ke Kasir -->
        <BaseModal
            v-model="showReportModal"
            title="Lapor Kendala Item ke Kasir"
            max-width="md"
            @close="showReportModal = false"
        >
            <div v-if="reportingItem" class="space-y-4 text-xs">
                <!-- Info Item Yang Dilaporkan -->
                <div class="p-3 bg-slate-950/70 border border-slate-800 rounded-xl flex items-center justify-between">
                    <div>
                        <p class="font-bold text-white text-sm">
                            {{ reportingItem.name || reportingItem.menu_name }}
                            <span v-if="reportingItem.variant_name" class="text-slate-400 font-normal">({{ reportingItem.variant_name }})</span>
                        </p>
                        <p class="text-slate-400 mt-0.5">Jumlah: {{ reportingItem.quantity }} {{ reportingItem.sales_unit || 'porsi' }}</p>
                    </div>
                    <span class="px-2 py-0.5 rounded bg-blue-950 text-blue-400 border border-blue-800 font-bold uppercase text-[10px]">
                        Meja {{ order.table_number || order.table?.number || '-' }}
                    </span>
                </div>

                <!-- Tipe Kendala -->
                <div>
                    <label class="block font-bold text-slate-300 mb-1.5 uppercase tracking-wider text-[11px]">Tipe Kendala / Alasan</label>
                    <div class="space-y-2">
                        <label class="flex items-center gap-2.5 p-2.5 rounded-xl border border-slate-800 bg-slate-950/40 hover:bg-slate-800/40 cursor-pointer">
                            <input
                                v-model="reportType"
                                type="radio"
                                value="cancel"
                                class="text-rose-500 focus:ring-rose-500 bg-slate-900 border-slate-700"
                            />
                            <div>
                                <span class="font-bold text-rose-300">Stok / Ukuran Kosong (Minta Batal)</span>
                                <p class="text-slate-400 text-[11px]">Stok habis di dapur dan tidak tersedia di pasar ikan.</p>
                            </div>
                        </label>

                        <label class="flex items-center gap-2.5 p-2.5 rounded-xl border border-slate-800 bg-slate-950/40 hover:bg-slate-800/40 cursor-pointer">
                            <input
                                v-model="reportType"
                                type="radio"
                                value="quantity_change"
                                class="text-amber-500 focus:ring-amber-500 bg-slate-900 border-slate-700"
                            />
                            <div>
                                <span class="font-bold text-amber-300">Bobot Berbeda / Sisa Ukuran Tertentu</span>
                                <p class="text-slate-400 text-[11px]">Ikan/kepiting yang didapat memiliki berat berbeda dari estimasi.</p>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Input Berat Riil (Jika quantity_change) -->
                <div v-if="reportType === 'quantity_change'">
                    <label class="block font-bold text-slate-300 mb-1 text-[11px]">Bobot Riil yang Tersedia ({{ reportingItem.sales_unit || 'kg' }})</label>
                    <input
                        v-model="reportRealWeight"
                        type="number"
                        step="0.001"
                        min="0.001"
                        placeholder="Contoh: 0.85"
                        class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-blue-500 focus:outline-none"
                    />
                </div>

                <!-- Input Catatan / Alasan ke Kasir -->
                <div>
                    <label class="block font-bold text-slate-300 mb-1 text-[11px]">Keterangan untuk Kasir / Tamu</label>
                    <textarea
                        v-model="reportReason"
                        rows="3"
                        placeholder="Contoh: Ikan gurame ukuran 1 kg habis di kolam dan pasar ikan. Mohon konfirmasi tamu untuk ganti menu atau batalkan."
                        class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white text-xs focus:border-blue-500 focus:outline-none placeholder:text-slate-600 resize-none"
                    ></textarea>
                </div>
            </div>

            <template #footer>
                <div class="flex items-center justify-end gap-2.5 w-full">
                    <button
                        type="button"
                        class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-xs font-semibold transition-colors"
                        :disabled="isSubmittingReport"
                        @click="showReportModal = false"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        class="px-5 py-2 bg-rose-600 hover:bg-rose-500 text-white rounded-xl text-xs font-bold shadow-md shadow-rose-950/40 flex items-center gap-1.5 transition-all disabled:opacity-50"
                        :disabled="isSubmittingReport || !reportReason.trim()"
                        @click="submitReportIssue"
                    >
                        <span>{{ isSubmittingReport ? 'Mengirim...' : 'Kirim ke Kasir' }}</span>
                    </button>
                </div>
            </template>
        </BaseModal>
    </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useKitchenStore } from '@/kitchen/stores/kitchen.store';
import { useToast } from '@/kitchen/composables/useToast';
import { subscribeToKitchenChannel, unsubscribeFromKitchenChannel } from '@/kitchen/services/pusher';

import AppLayout from '@/kitchen/components/AppLayout.vue';
import OrderHeader from '@/kitchen/components/order/OrderHeader.vue';
import OrderItemList from '@/kitchen/components/order/OrderItemList.vue';
import OrderNote from '@/kitchen/components/order/OrderNote.vue';
import Loading from '@/kitchen/components/Loading.vue';
import ErrorState from '@/kitchen/components/ErrorState.vue';
import BaseModal from '@/shared/components/BaseModal.vue';

const route = useRoute();
const router = useRouter();
const kitchenStore = useKitchenStore();
const toast = useToast();

const showReportModal = ref(false);
const reportingItem = ref(null);
const reportType = ref('cancel');
const reportRealWeight = ref('');
const reportReason = ref('');
const isSubmittingReport = ref(false);

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

const handlePrepareAll = async () => {
    if (!order.value?.id) return;
    const res = await kitchenStore.prepareAll(order.value.id);
    if (res.success) {
        toast.success('Semua item telah masuk proses memasak');
        await loadOrderDetail();
    } else {
        toast.error(res.message);
    }
};

const handleReadyAll = async () => {
    if (!order.value?.id) return;
    const res = await kitchenStore.readyAll(order.value.id);
    if (res.success) {
        toast.success('Semua item telah ditandai siap disajikan');
        await loadOrderDetail();
    } else {
        toast.error(res.message);
    }
};

const handleServeAll = async () => {
    if (!order.value?.id) return;
    const res = await kitchenStore.serveAll(order.value.id);
    if (res.success) {
        toast.success('Semua item telah selesai disajikan');
        goBack();
    } else {
        toast.error(res.message);
    }
};

const openReportModal = (item) => {
    reportingItem.value = item;
    reportType.value = 'cancel';
    reportRealWeight.value = '';
    reportReason.value = '';
    showReportModal.value = true;
};

const submitReportIssue = async () => {
    if (!reportingItem.value) return;
    isSubmittingReport.value = true;
    try {
        const payload = {
            type: reportType.value,
            reason: reportReason.value.trim(),
        };
        if (reportType.value === 'quantity_change' && reportRealWeight.value) {
            payload.quantity = Number(reportRealWeight.value);
        }
        const res = await kitchenStore.reportItemIssue(reportingItem.value.id, payload);
        if (res.success) {
            toast.success('Laporan kendala item berhasil dikirim ke Kasir.');
            showReportModal.value = false;
            await loadOrderDetail();
        } else {
            toast.error(res.message);
        }
    } finally {
        isSubmittingReport.value = false;
    }
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

