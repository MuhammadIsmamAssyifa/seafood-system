<template>
    <AppLayout>
        <div class="h-full flex flex-col space-y-4">
            <!-- Reconnection / Disconnected Banner (Sprint 07) -->
            <ConnectionStatus />

            <!-- Section 1: Active Kitchen Status Summary Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 shrink-0">
                <StatusCard
                    status-key="all"
                    title="Semua Antrean"
                    :count="kitchenStore.summary.total"
                    :is-active="kitchenStore.activeTab === 'all'"
                    @select="setStatusFilter"
                />

                <StatusCard
                    status-key="waiting"
                    title="Menunggu"
                    :count="kitchenStore.summary.waiting"
                    :is-active="kitchenStore.activeTab === 'waiting'"
                    @select="setStatusFilter"
                />

                <StatusCard
                    status-key="preparing"
                    title="Sedang Dimasak"
                    :count="kitchenStore.summary.preparing"
                    :is-active="kitchenStore.activeTab === 'preparing'"
                    @select="setStatusFilter"
                />

                <StatusCard
                    status-key="ready"
                    title="Siap Saji"
                    :count="kitchenStore.summary.ready"
                    :is-active="kitchenStore.activeTab === 'ready'"
                    @select="setStatusFilter"
                />
            </div>

            <!-- Section 2: Controls & Queue Toolbar -->
            <div
                class="flex flex-wrap items-center justify-between gap-3 bg-slate-900/80 border border-slate-800 px-4 py-2.5 rounded-2xl shrink-0"
            >
                <!-- Left: Filter pills -->
                <div class="flex items-center gap-1.5 overflow-x-auto py-0.5">
                    <span
                        class="text-xs text-slate-400 font-medium mr-1 hidden sm:inline"
                        >Filter:</span
                    >

                    <button
                        type="button"
                        :class="[
                            'px-3 py-1.5 rounded-xl text-xs font-bold transition-all shrink-0',
                            kitchenStore.activeTab === 'all'
                                ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30'
                                : 'bg-slate-800 text-slate-300 hover:text-white',
                        ]"
                        @click="setStatusFilter('all')"
                    >
                        Semua ({{ kitchenStore.summary.total }})
                    </button>

                    <button
                        type="button"
                        :class="[
                            'px-3 py-1.5 rounded-xl text-xs font-bold transition-all shrink-0',
                            kitchenStore.activeTab === 'waiting'
                                ? 'bg-amber-600 text-white shadow-md shadow-amber-600/30'
                                : 'bg-slate-800 text-slate-300 hover:text-white',
                        ]"
                        @click="setStatusFilter('waiting')"
                    >
                        Menunggu ({{ kitchenStore.summary.waiting }})
                    </button>

                    <button
                        type="button"
                        :class="[
                            'px-3 py-1.5 rounded-xl text-xs font-bold transition-all shrink-0',
                            kitchenStore.activeTab === 'preparing'
                                ? 'bg-sky-600 text-white shadow-md shadow-sky-600/30'
                                : 'bg-slate-800 text-slate-300 hover:text-white',
                        ]"
                        @click="setStatusFilter('preparing')"
                    >
                        Dimasak ({{ kitchenStore.summary.preparing }})
                    </button>

                    <button
                        type="button"
                        :class="[
                            'px-3 py-1.5 rounded-xl text-xs font-bold transition-all shrink-0',
                            kitchenStore.activeTab === 'ready'
                                ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30'
                                : 'bg-slate-800 text-slate-300 hover:text-white',
                        ]"
                        @click="setStatusFilter('ready')"
                    >
                        Siap Saji ({{ kitchenStore.summary.ready }})
                    </button>
                </div>

                <!-- Right: Average Waiting Time, Last Sync, Sound test, Manual refresh (Sprint 07) -->
                <div class="flex items-center gap-2.5 flex-wrap">
                    <!-- Average Waiting Time (Sprint 07) -->
                    <div
                        class="hidden md:flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-950/70 border border-slate-800 text-xs font-mono text-slate-300"
                        title="Rata-rata Waktu Tunggu Dapur"
                    >
                        <span class="text-slate-400 font-sans text-[11px]"
                            >Rata-rata:</span
                        >
                        <span class="font-bold text-amber-400">{{
                            kitchenStore.averageWaitingTime
                        }}</span>
                    </div>

                    <!-- Last Sync (Sprint 07) -->
                    <div
                        class="hidden xl:flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-950/70 border border-slate-800 text-[11px] font-mono text-slate-400"
                        title="Waktu Sinkronisasi Terakhir"
                    >
                        <span>Sync:</span>
                        <span class="text-slate-300 font-semibold">{{
                            kitchenStore.lastSyncFormatted
                        }}</span>
                    </div>

                    <!-- Sound test -->
                    <button
                        type="button"
                        class="p-2 text-slate-400 hover:text-white bg-slate-800 hover:bg-slate-700 rounded-xl transition-all border border-slate-700/60"
                        title="Tes Suara Notifikasi Dapur"
                        @click="handleTestAudio"
                    >
                        <svg
                            class="w-4 h-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"
                            />
                        </svg>
                    </button>

                    <!-- Manual Refresh -->
                    <button
                        type="button"
                        class="flex items-center gap-2 px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white text-xs font-semibold rounded-xl border border-slate-700 transition-all active:scale-95 disabled:opacity-50"
                        :disabled="kitchenStore.loading"
                        @click="handleManualRefresh"
                    >
                        <svg
                            :class="[
                                'w-4 h-4 text-blue-400',
                                kitchenStore.loading ? 'animate-spin' : '',
                            ]"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                            />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Section 3: Kitchen Queue List -->
            <div
                class="flex-1 bg-slate-900/40 border border-slate-800/80 rounded-2xl p-4 overflow-hidden relative"
            >
                <QueueList
                    :orders="kitchenStore.filteredQueue"
                    :active-tab="kitchenStore.activeTab"
                    :loading="kitchenStore.loading"
                    :error="kitchenStore.error"
                    @select-order="handleSelectOrder"
                    @retry="handleManualRefresh"
                />
            </div>
        </div>

        <!-- Order Quick Preview Modal -->
        <BaseModal
            v-model="showDetailModal"
            :title="`Rincian Pesanan #${selectedOrderNumber}`"
            max-width="lg"
            @close="showDetailModal = false"
        >
            <div v-if="activeOrder" class="space-y-4">
                <!-- Info Grid -->
                <div
                    class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 p-3.5 bg-slate-950/70 border border-slate-800 rounded-xl text-xs"
                >
                    <div>
                        <span class="text-slate-400 block font-medium"
                            >Meja:</span
                        >
                        <span class="font-bold text-white text-sm"
                            >Meja {{ selectedTableNumber }}</span
                        >
                    </div>
                    <div>
                        <span class="text-slate-400 block font-medium"
                            >Pelanggan:</span
                        >
                        <span
                            class="font-semibold text-slate-200 truncate block"
                            >{{ selectedCustomer }}</span
                        >
                    </div>
                    <div>
                        <span class="text-slate-400 block font-medium"
                            >Jumlah Tamu:</span
                        >
                        <span class="font-semibold text-slate-200"
                            >{{ selectedGuestCount }} Orang</span
                        >
                    </div>
                    <div>
                        <span class="text-slate-400 block font-medium"
                            >Status:</span
                        >
                        <OrderBadge :status="activeOrder.status" size="sm" />
                    </div>
                </div>

                <!-- Timing info -->
                <div
                    class="flex items-center justify-between text-xs text-slate-400 px-1 font-mono"
                >
                    <span
                        >Masuk:
                        {{
                            formatTime(
                                activeOrder.sent_to_kitchen_at ||
                                    activeOrder.created_at,
                                true,
                            )
                        }}</span
                    >
                    <span class="text-emerald-400 font-bold"
                        >Durasi:
                        {{
                            formatElapsedTime(
                                activeOrder.sent_to_kitchen_at ||
                                    activeOrder.created_at,
                            )
                        }}</span
                    >
                </div>

                <!-- Items list -->
                <div>
                    <h4
                        class="text-xs font-bold text-slate-300 uppercase tracking-wider mb-2"
                    >
                        Daftar Menu
                    </h4>
                    <div class="space-y-2 max-h-64 overflow-y-auto pr-1">
                        <div
                            v-for="item in activeOrder.items || []"
                            :key="item.id"
                            class="p-3 bg-slate-950/50 border border-slate-800/80 rounded-xl flex items-center justify-between"
                        >
                            <div class="flex items-start gap-3">
                                <span
                                    class="w-7 h-7 rounded-lg bg-blue-600/20 text-blue-400 border border-blue-500/30 font-bold flex items-center justify-center text-xs shrink-0"
                                >
                                    {{ item.quantity }}x
                                </span>
                                <div>
                                    <p
                                        class="font-bold text-white text-sm leading-tight"
                                    >
                                        {{ item.menu_name || item.name }}
                                        <span v-if="item.variant_name && item.variant_name !== (item.menu_name || item.name)" class="text-slate-400 font-normal text-xs"> ({{ item.variant_name }})</span>
                                    </p>
                                    <p
                                        v-if="item.notes"
                                        class="text-xs text-amber-400 italic mt-0.5"
                                    >
                                        "{{ item.notes }}"
                                    </p>
                                </div>
                            </div>
                            <span
                                class="text-xs font-mono uppercase px-2 py-0.5 bg-slate-800 text-slate-300 rounded border border-slate-700"
                            >
                                {{ item.unit || "porsi" }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <template #footer>
                <button
                    type="button"
                    class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-xl text-xs font-semibold transition-colors"
                    @click="showDetailModal = false"
                >
                    Tutup
                </button>
            </template>
        </BaseModal>
    </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { useRouter } from "vue-router";
import { useKitchenStore } from "@/kitchen/stores/kitchen.store";
import { useToast } from "@/kitchen/composables/useToast";
import { formatTime, formatElapsedTime } from "@/shared/utils/formatter";
import { playNotificationTone } from "@/shared/utils/helper";
import {
    subscribeToKitchenChannel,
    unsubscribeFromKitchenChannel,
} from "@/kitchen/services/pusher";

import AppLayout from "@/kitchen/components/AppLayout.vue";
import ConnectionStatus from "@/kitchen/components/ConnectionStatus.vue";
import StatusCard from "@/kitchen/components/dashboard/StatusCard.vue";
import QueueList from "@/kitchen/components/dashboard/QueueList.vue";
import OrderBadge from "@/kitchen/components/dashboard/OrderBadge.vue";
import BaseModal from "@/shared/components/BaseModal.vue";

const router = useRouter();
const kitchenStore = useKitchenStore();
const toast = useToast();

const showDetailModal = ref(false);
const activeOrder = ref(null);

const setStatusFilter = (status) => {
    if (kitchenStore.activeTab === status) {
        kitchenStore.setActiveTab("all");
    } else {
        kitchenStore.setActiveTab(status);
    }
};

const handleSelectOrder = (order) => {
    kitchenStore.selectOrder(order);
    router.push({ name: "kitchen.orders.detail", params: { id: order.id } });
};

const selectedOrderNumber = computed(() => {
    if (!activeOrder.value) return "";
    return (
        activeOrder.value.order_number ||
        activeOrder.value.order?.order_number ||
        activeOrder.value.id
    );
});

const selectedTableNumber = computed(() => {
    if (!activeOrder.value) return "-";
    return (
        activeOrder.value.table_number ||
        activeOrder.value.table?.number ||
        activeOrder.value.order?.table_number ||
        "-"
    );
});

const selectedCustomer = computed(() => {
    if (!activeOrder.value) return "Pelanggan";
    return (
        activeOrder.value.customer_name ||
        activeOrder.value.order?.customer_name ||
        "Pelanggan"
    );
});

const selectedGuestCount = computed(() => {
    if (!activeOrder.value) return 1;
    return (
        activeOrder.value.guest_count ||
        activeOrder.value.order?.guest_count ||
        1
    );
});

const handleManualRefresh = async () => {
    await kitchenStore.refreshOrders();
    toast.info("Antrean dapur diperbarui");
};

const handleTestAudio = () => {
    playNotificationTone("new-order");
    toast.success("Tes audio notifikasi dapur berbunyi");
};

onMounted(() => {
    kitchenStore.initConnectionWatcher();
    kitchenStore.fetchStations();
    kitchenStore.fetchOrders();

    // Subscribe to realtime KDS events via Pusher
    subscribeToKitchenChannel({
        onOrderCreated: (event) => {
            console.log("[KDS Pusher] onOrderCreated event:", event);
            playNotificationTone("new-order");
            toast.info(
                `Pesanan baru #${event?.order?.order_number || event?.order_number || ""} masuk!`,
            );
            kitchenStore.fetchOrders(true);
        },
        onOrderUpdated: (event) => {
            console.log(
                "[KDS Pusher] onOrderUpdated / onBatchUpdated event:",
                event,
            );
            if (event && event.id) {
                kitchenStore.upsertOrder(event);
            }
            kitchenStore.fetchOrders(true);
        },
        onBatchSubmitted: (event) => {
            console.log("[KDS Pusher] onBatchSubmitted event:", event);
            const batchId = event?.id;
            if (kitchenStore.canPlayAlertFor(batchId)) {
                playNotificationTone("new-order");
            }

            const orderNum =
                event?.order_number || (event?.id ? `ORD-${event.id}` : "-");
            const tableNum = event?.table_number || event?.table?.number || "-";
            const count = event?.items?.length || 1;

            toast.notifyOrder({
                orderNumber: orderNum,
                tableNumber: tableNum,
                itemsCount: count,
                duration: 5000,
            });

            if (event && event.id) {
                kitchenStore.upsertOrder(event);
            }
            kitchenStore.fetchOrders(true);
        },
        onItemStatusChanged: (event) => {
            console.log("[KDS Pusher] onItemStatusChanged event:", event);
            kitchenStore.fetchOrders(true);
        },
    });
});

onUnmounted(() => {
    unsubscribeFromKitchenChannel();
});
</script>
