<template>
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 sm:p-6 shadow-xl">
        <!-- Top row: Back button & Status badge -->
        <div class="flex items-center justify-between gap-3 mb-4">
            <button
                type="button"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white text-xs font-bold border border-slate-700 transition-all active:scale-95 shadow-sm"
                @click="$emit('back')"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Kembali ke Queue</span>
            </button>

            <div class="flex items-center gap-3">
                <WorkflowAction
                    v-if="isWaiting"
                    action-type="accept"
                    :target-id="order.id"
                    label="Terima Order (Accept)"
                    size="md"
                    :require-confirm="true"
                    @success="$emit('accepted')"
                />
                <OrderBadge :status="order.status" size="lg" />
            </div>
        </div>

        <!-- Main Details Banner -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-4 bg-slate-950/70 border border-slate-800/80 rounded-xl">
            <!-- Order Number & Table -->
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Nomor Order</p>
                <div class="flex items-baseline gap-2 mt-0.5">
                    <span class="text-xl sm:text-2xl font-black text-white font-mono tracking-tight">
                        #{{ orderNumber }}
                    </span>
                    <span v-if="order.batch_number" class="text-xs font-bold px-2 py-0.5 rounded bg-blue-950 text-blue-400 border border-blue-800">
                        Batch {{ order.batch_number }}
                    </span>
                </div>
            </div>

            <!-- Table Number -->
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Meja</p>
                <p class="text-xl sm:text-2xl font-black text-blue-400 mt-0.5">
                    Meja {{ tableNumber }}
                </p>
            </div>

            <!-- Customer & Guests -->
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Pelanggan</p>
                <p class="text-base sm:text-lg font-bold text-slate-100 mt-0.5 truncate">
                    {{ customerName }}
                </p>
                <p class="text-xs text-slate-400 mt-0.5 font-medium">
                    {{ guestCount }} Tamu
                </p>
            </div>

            <!-- Time & Elapsed -->
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Waktu Masuk</p>
                <p class="text-base sm:text-lg font-bold text-slate-100 font-mono mt-0.5">
                    {{ orderTime }}
                </p>
                <p class="text-xs text-emerald-400 font-mono font-bold mt-0.5">
                    Durasi: {{ elapsedTime }}
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import OrderBadge from '@/kitchen/components/dashboard/OrderBadge.vue';
import WorkflowAction from '@/kitchen/components/workflow/WorkflowAction.vue';
import { formatTime, formatElapsedTime } from '@/shared/utils/formatter';

const props = defineProps({
    order: {
        type: Object,
        required: true,
    },
});

defineEmits(['back', 'accepted']);

const orderNumber = computed(() => {
    return props.order.order_number || props.order.id || '-';
});

const tableNumber = computed(() => {
    return props.order.table_number || props.order.table?.number || '-';
});

const customerName = computed(() => {
    return props.order.customer_name || 'Pelanggan';
});

const guestCount = computed(() => {
    return props.order.guest_count || 1;
});

const isWaiting = computed(() => {
    const s = (props.order.status || '').toLowerCase();
    return ['waiting', 'sent', 'submitted'].includes(s);
});

const orderTimestamp = computed(() => {
    return props.order.sent_to_kitchen_at || props.order.created_at;
});

const orderTime = computed(() => {
    return formatTime(orderTimestamp.value, true);
});

const elapsedTime = computed(() => {
    return formatElapsedTime(orderTimestamp.value);
});
</script>

