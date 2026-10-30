<template>
    <div
        :class="[
            'bg-slate-900 border rounded-2xl overflow-hidden shadow-lg transition-all flex flex-col cursor-pointer hover:border-slate-700 hover:shadow-2xl active:scale-[0.99] select-none',
            urgencyBorderClass,
        ]"
        @click="$emit('select', order)"
    >
        <!-- Card Header: Order #, Table #, Priority Badge, Status Badge -->
        <div class="p-3.5 bg-slate-850/90 border-b border-slate-800/80 flex items-center justify-between gap-2">
            <div>
                <div class="flex items-center gap-1.5 flex-wrap">
                    <span class="text-xs font-mono font-bold text-blue-400">
                        #{{ orderNumber }}
                    </span>
                    <span v-if="order.batch_number" class="text-[10px] font-semibold px-1.5 py-0.2 rounded bg-slate-800 text-slate-300 border border-slate-700">
                        B#{{ order.batch_number }}
                    </span>

                    <!-- Priority Badge (Sprint 07) -->
                    <span
                        :class="[
                            'text-[10px] font-bold px-2 py-0.5 rounded-full border flex items-center gap-1',
                            priorityBadgeClass,
                        ]"
                    >
                        <span>{{ priorityDot }}</span>
                        <span>{{ priorityLabel }}</span>
                    </span>
                </div>
                <h3 class="text-base font-black text-white mt-1 tracking-wide flex items-center gap-1.5">
                    <span>Meja {{ tableNumber }}</span>
                </h3>
            </div>

            <OrderBadge :status="order.status" size="sm" />
        </div>

        <!-- Customer & Guest info bar -->
        <div class="px-3.5 py-1.5 bg-slate-900/60 border-b border-slate-800/50 flex items-center justify-between text-xs text-slate-400">
            <div class="flex items-center gap-1.5 truncate max-w-[140px]">
                <svg class="w-3.5 h-3.5 text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span class="font-medium text-slate-200 truncate">{{ customerName }}</span>
            </div>

            <div class="flex items-center gap-1 shrink-0 font-medium text-slate-300">
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <span>{{ guestCount }} Tamu</span>
            </div>
        </div>

        <!-- Items to Cook (Max 4 Lines for clean laptop display) -->
        <div class="p-3.5 flex-1 flex flex-col justify-start text-xs min-h-[170px]">
            <div class="space-y-2">
                <div
                    v-for="item in displayedItems"
                    :key="item.id"
                    class="flex items-start justify-between gap-2 pb-1.5 border-b border-slate-800/40 last:border-0"
                >
                    <div class="flex items-start gap-2 min-w-0 flex-1">
                        <span class="font-black text-blue-400 text-xs shrink-0 w-6">
                            {{ item.quantity }}x
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="font-semibold text-slate-200 leading-snug truncate" :title="itemTitle(item)">
                                <span>{{ item.menu_name || item.name }}</span>
                                <span v-if="item.variant_name && item.variant_name !== (item.menu_name || item.name)" class="text-slate-400 font-normal text-[11px]"> ({{ item.variant_name }})</span>
                            </p>
                            <p v-if="item.notes || item.note" class="text-[11px] text-amber-400 italic truncate" :title="item.notes || item.note">
                                "{{ item.notes || item.note }}"
                            </p>
                        </div>
                    </div>

                    <!-- Item Status Pill -->
                    <div class="shrink-0 flex items-center">
                        <span
                            :class="[
                                'text-[10px] font-bold px-1.5 py-0.5 rounded uppercase tracking-wider',
                                itemStatusBadgeClass(item.status),
                            ]"
                        >
                            {{ itemStatusLabel(item.status) }}
                        </span>
                    </div>
                </div>

                <div v-if="displayedItems.length === 0" class="text-center py-6 text-slate-500 italic">
                    Semua item pesanan sudah disajikan
                </div>
            </div>

            <!-- Indicator if more than 4 items to cook -->
            <div v-if="remainingItemsCount > 0" class="mt-auto pt-2 flex items-center justify-between text-[11px] text-blue-400 font-semibold border-t border-slate-800/60">
                <span class="flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    +{{ remainingItemsCount }} menu lainnya...
                </span>
                <span class="text-slate-500">Klik detail</span>
            </div>
        </div>

        <!-- Card Footer: Items Count, Accept Button (if waiting), & Live Digital Timer (Sprint 07) -->
        <div class="p-3 bg-slate-900/90 border-t border-slate-800 flex items-center justify-between text-xs text-slate-400 gap-2">
            <!-- Items count -->
            <div class="flex items-center gap-1 font-semibold text-slate-300 shrink-0">
                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                <span>{{ itemsToCook.length }} Item</span>
            </div>

            <!-- Quick Accept Button (Sprint 05) -->
            <div v-if="isWaiting" class="shrink-0" @click.stop>
                <WorkflowAction
                    action-type="accept"
                    :target-id="order.id"
                    label="Terima"
                    size="sm"
                    :require-confirm="true"
                />
            </div>

            <!-- Digital Running Timer (Sprint 07) -->
            <div class="flex items-center gap-1.5 font-mono font-black shrink-0 ml-auto" :class="urgencyTextClass">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-sm tracking-wider">{{ digitalTimer }}</span>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue';
import OrderBadge from './OrderBadge.vue';
import WorkflowAction from '@/kitchen/components/workflow/WorkflowAction.vue';
import { formatTimerDigital } from '@/shared/utils/formatter';
import { getOrderAgeStatus } from '@/shared/utils/helper';

const props = defineProps({
    order: {
        type: Object,
        required: true,
    },
});

defineEmits(['select']);

// 1-second live stopwatch tick (Sprint 07)
const nowTick = ref(Date.now());
let timer = null;

onMounted(() => {
    timer = setInterval(() => {
        nowTick.value = Date.now();
    }, 1000);
});

onUnmounted(() => {
    if (timer) clearInterval(timer);
});

const itemTitle = (item) => {
    const base = item?.menu_name || item?.name || '';
    const variant = item?.variant_name && item.variant_name !== base ? ` (${item.variant_name})` : '';
    return `${base}${variant}`;
};

const orderNumber = computed(() => {
    return props.order.order_number || props.order.order?.order_number || `ORD-${props.order.id}`;
});

const tableNumber = computed(() => {
    return props.order.table_number || props.order.table?.number || props.order.order?.table_number || '-';
});

const customerName = computed(() => {
    return props.order.customer_name || props.order.order?.customer_name || 'Pelanggan';
});

const guestCount = computed(() => {
    return props.order.guest_count || props.order.order?.guest_count || 1;
});

/**
 * Filter items that need to be cooked (exclude already served or cancelled items)
 */
const itemsToCook = computed(() => {
    const all = props.order.items || [];
    return all.filter((item) => {
        const s = (item.status || '').toLowerCase();
        return s !== 'served' && s !== 'cancelled';
    });
});

/**
 * Limit to max 4 lines for laptop screen uniformity
 */
const displayedItems = computed(() => {
    return itemsToCook.value.slice(0, 4);
});

const remainingItemsCount = computed(() => {
    return Math.max(0, itemsToCook.value.length - 4);
});

const isWaiting = computed(() => {
    const s = (props.order.status || '').toLowerCase();
    return ['waiting', 'sent', 'submitted'].includes(s);
});

/**
 * Calculated strictly from sent_to_kitchen_at (with fallback to created_at)
 */
const orderTimestamp = computed(() => {
    return props.order.sent_to_kitchen_at || props.order.created_at;
});

/**
 * Digital running stopwatch format MM:SS or HH:MM:SS (Sprint 07)
 */
const digitalTimer = computed(() => {
    if (nowTick.value) {
        return formatTimerDigital(orderTimestamp.value);
    }
    return formatTimerDigital(orderTimestamp.value);
});

/**
 * Priority thresholds (Sprint 07):
 * 0-5m: normal
 * 5-10m: warning (Perhatian)
 * >10m: danger (Prioritas Tinggi)
 */
const ageStatus = computed(() => {
    if (['completed', 'ready'].includes((props.order.status || '').toLowerCase())) {
        return 'normal';
    }
    return getOrderAgeStatus(orderTimestamp.value, 5, 10);
});

const priorityLabel = computed(() => {
    switch (ageStatus.value) {
        case 'danger':
            return 'Prioritas';
        case 'warning':
            return 'Perhatian';
        default:
            return 'Normal';
    }
});

const priorityDot = computed(() => {
    switch (ageStatus.value) {
        case 'danger':
            return '🔴';
        case 'warning':
            return '🟡';
        default:
            return '🟢';
    }
});

const priorityBadgeClass = computed(() => {
    switch (ageStatus.value) {
        case 'danger':
            return 'bg-rose-500/20 text-rose-300 border-rose-500/40 animate-pulse font-black';
        case 'warning':
            return 'bg-amber-500/20 text-amber-300 border-amber-500/40 font-bold';
        default:
            return 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20 font-semibold';
    }
});

const urgencyBorderClass = computed(() => {
    switch (ageStatus.value) {
        case 'danger':
            return 'border-rose-500 shadow-xl shadow-rose-950/60 ring-1 ring-rose-500/40';
        case 'warning':
            return 'border-amber-600/80 shadow-lg shadow-amber-950/40';
        default:
            return 'border-slate-800';
    }
});

const urgencyTextClass = computed(() => {
    switch (ageStatus.value) {
        case 'danger':
            return 'text-rose-400 animate-pulse';
        case 'warning':
            return 'text-amber-400';
        default:
            return 'text-emerald-400';
    }
});

function itemStatusLabel(status) {
    const s = (status || '').toLowerCase();
    switch (s) {
        case 'preparing':
        case 'cooking':
            return 'Masak';
        case 'ready':
            return 'Siap';
        case 'waiting':
        default:
            return 'Antre';
    }
}

function itemStatusBadgeClass(status) {
    const s = (status || '').toLowerCase();
    switch (s) {
        case 'preparing':
        case 'cooking':
            return 'bg-sky-500/10 text-sky-400 border border-sky-500/20';
        case 'ready':
            return 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20';
        case 'waiting':
        default:
            return 'bg-slate-800 text-slate-400 border border-slate-700';
    }
}
</script>
