<template>
    <div class="space-y-4">
        <!-- Section Header -->
        <div class="flex items-center justify-between">
            <h3 class="text-base font-bold text-white tracking-wide flex items-center gap-2">
                <span>Daftar Menu yang Harus Dibuat</span>
                <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-slate-800 text-slate-300 border border-slate-700">
                    {{ items.length }} Item
                </span>
            </h3>
        </div>

        <!-- Items Grid: Responsive up to 4 columns on desktop/laptop -->
        <div v-if="items.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            <OrderItemCard
                v-for="item in items"
                :key="item.id"
                :item="item"
                @report-issue="$emit('report-issue', $event)"
            />
        </div>

        <!-- Empty State for Items -->
        <div v-else class="text-center py-12 bg-slate-900 border border-slate-800 rounded-2xl p-6">
            <p class="text-sm text-slate-400">Tidak ada item dalam pesanan ini.</p>
        </div>
    </div>
</template>

<script setup>
import OrderItemCard from './OrderItemCard.vue';

defineEmits(['report-issue']);

defineProps({
    items: {
        type: Array,
        default: () => [],
    },
});
</script>

