<template>
    <div
        :class="[
            'p-4 sm:p-5 rounded-2xl border transition-all flex flex-col justify-between bg-slate-900 shadow-md',
            statusCardBorder,
        ]"
    >
        <!-- Item Top Row: Quantity & Menu Name -->
        <div>
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-start gap-3">
                    <!-- Big Quantity Box -->
                    <div class="min-w-14 h-14 rounded-2xl bg-blue-600/20 border border-blue-500/30 flex flex-col items-center justify-center px-2 py-1 shrink-0">
                        <span class="text-xl font-black text-blue-400 font-mono leading-none">
                            {{ formattedQuantity }}
                        </span>
                        <span class="text-[10px] font-bold text-blue-300 uppercase mt-0.5 tracking-wider">
                            {{ unitDisplay }}
                        </span>
                    </div>

                    <!-- Menu Info -->
                    <div>
                        <h4 class="text-base sm:text-lg font-bold text-white leading-snug">
                            <span>{{ item.name || item.menu_name }}</span>
                            <span v-if="item.variant_name && item.variant_name !== (item.name || item.menu_name)" class="text-slate-400 font-normal text-sm ml-1">
                                ({{ item.variant_name }})
                            </span>
                        </h4>
                    </div>
                </div>

                <!-- Status Badge -->
                <OrderBadge :status="item.status" size="sm" />
            </div>

            <!-- Cooking Method & Station Tags -->
            <div class="flex flex-wrap items-center gap-2 mt-3.5 pt-3 border-t border-slate-800/60">
                <!-- Cooking Method -->
                <div
                    v-if="cookingMethod"
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-orange-950/50 border border-orange-800/60 text-orange-300 text-xs font-semibold"
                >
                    <!-- Fire Icon -->
                    <svg class="w-3.5 h-3.5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" />
                    </svg>
                    <span>Metode: {{ cookingMethod }}</span>
                </div>

                <!-- Kitchen Station -->
                <div
                    v-if="station"
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-indigo-950/50 border border-indigo-800/60 text-indigo-300 text-xs font-semibold"
                >
                    <!-- Station Icon -->
                    <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <span>Station: {{ station }}</span>
                </div>
            </div>

            <!-- Notes preview if any -->
            <div
                v-if="item.notes || item.note"
                class="mt-3 p-2.5 rounded-xl bg-amber-950/40 border border-amber-800/50 flex items-start gap-2 text-xs text-amber-300"
            >
                <svg class="w-4 h-4 text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                </svg>
                <span class="italic leading-relaxed font-medium">"{{ item.notes || item.note }}"</span>
            </div>
        </div>

        <!-- If Pending Change / Adjustment Request to Cashier -->
        <div v-if="hasPendingChange" class="mt-4 p-3 rounded-xl bg-amber-950/40 border border-amber-800/60 flex items-start gap-2.5">
            <span class="text-amber-400 animate-pulse text-base">⏳</span>
            <div class="text-xs">
                <p class="font-bold text-amber-300">Menunggu Konfirmasi Kasir</p>
                <p class="text-slate-300 mt-0.5">{{ item.pending_change.reason || 'Laporan ketidaksesuaian stok/bobot' }}</p>
            </div>
        </div>

        <!-- Workflow Action Row (Sprint 05) -->
        <div class="mt-4 pt-3 border-t border-slate-800 flex items-center justify-between flex-wrap gap-2">
            <span class="text-xs text-slate-400 font-medium">Aksi Kitchen:</span>

            <!-- If Waiting & Has Pending Change: Waiting confirmation -->
            <span v-if="itemStatus === 'waiting' && hasPendingChange" class="text-xs text-amber-400 font-semibold italic">
                Terkunci (menunggu kasir)
            </span>

            <!-- If Waiting & No Pending Change: Mulai Masak (prepare) or Lapor Kasir -->
            <div v-else-if="itemStatus === 'waiting'" class="flex items-center gap-2">
                <button
                    type="button"
                    class="px-2.5 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-amber-400 hover:text-amber-300 border border-slate-700 text-xs font-semibold flex items-center gap-1.5 transition-all active:scale-95"
                    title="Lapor jika stok/ukuran habis di dapur atau pasar ikan"
                    @click="$emit('report-issue', item)"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>Lapor Kasir</span>
                </button>
                <WorkflowAction
                    action-type="prepare"
                    :target-id="item.id"
                    :target-name="item.name || item.menu_name"
                    label="Mulai Masak"
                    size="sm"
                />
            </div>

            <!-- If Preparing/Cooking: Selesai Masak (ready) -->
            <WorkflowAction
                v-else-if="itemStatus === 'preparing'"
                action-type="ready"
                :target-id="item.id"
                :target-name="item.name || item.menu_name"
                label="Selesai Masak"
                size="sm"
                :require-confirm="true"
            />

            <!-- If Ready: Sajikan (serve) -->
            <WorkflowAction
                v-else-if="itemStatus === 'ready'"
                action-type="serve"
                :target-id="item.id"
                :target-name="item.name || item.menu_name"
                label="Sajikan"
                size="sm"
                :require-confirm="true"
            />

            <!-- If Served: Completed indicator -->
            <span
                v-else-if="itemStatus === 'served'"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-800 text-slate-300 text-xs font-semibold border border-slate-700"
            >
                <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>Sudah Disajikan</span>
            </span>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import OrderBadge from '@/kitchen/components/dashboard/OrderBadge.vue';
import WorkflowAction from '@/kitchen/components/workflow/WorkflowAction.vue';

const props = defineProps({
    item: {
        type: Object,
        required: true,
    },
});

defineEmits(['report-issue']);

const hasPendingChange = computed(() => {
    return Boolean(props.item.pending_change);
});

const unit = computed(() => {
    return (props.item.sales_unit || props.item.unit || 'porsi').toLowerCase();
});

const isWeightBased = computed(() => {
    return ['kg', 'gram', 'gr', 'ons'].includes(unit.value);
});

const formattedQuantity = computed(() => {
    const raw = Number(props.item.quantity) || 0;
    if (isWeightBased.value) {
        // Format weight to up to 3 decimals or stripped trailing zeros
        return Number.isInteger(raw) ? raw.toString() : raw.toFixed(3).replace(/\.?0+$/, '');
    }
    return Number.isInteger(raw) ? raw.toString() : raw.toFixed(1);
});

const unitDisplay = computed(() => {
    return props.item.sales_unit || props.item.unit || 'porsi';
});

const cookingMethod = computed(() => {
    return props.item.cooking_method || props.item.cookingMethod || null;
});

const station = computed(() => {
    return props.item.station || props.item.kitchen_station || null;
});

const itemStatus = computed(() => {
    const s = (props.item.status || '').toLowerCase();
    if (['cooking', 'preparing'].includes(s)) return 'preparing';
    if (['ready'].includes(s)) return 'ready';
    if (['served', 'completed'].includes(s)) return 'served';
    return 'waiting';
});

const statusCardBorder = computed(() => {
    const s = (props.item.status || '').toLowerCase();
    if (['cooking', 'preparing'].includes(s)) {
        return 'border-blue-700/80 hover:border-blue-500';
    }
    if (['ready'].includes(s)) {
        return 'border-emerald-700/80 hover:border-emerald-500';
    }
    if (['completed', 'served'].includes(s)) {
        return 'border-slate-800 opacity-75';
    }
    return 'border-slate-800 hover:border-slate-700';
});
</script>

