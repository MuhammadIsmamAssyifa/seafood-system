<script setup>
import { AlertTriangle, ArrowRight } from 'lucide-vue-next';
import { formatNumber } from '../../../utils/formatter';

defineProps({
    items: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['adjust']);
</script>

<template>
    <div
        v-if="items && items.length > 0"
        class="p-4.5 rounded-2xl bg-amber-50/80 border border-amber-200/90 text-amber-900 shadow-2xs space-y-3"
    >
        <div class="flex items-start justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                    <AlertTriangle class="w-4.5 h-4.5" />
                </div>
                <div>
                    <h4 class="text-sm font-bold text-amber-950">
                        Peringatan: {{ items.length }} Bahan Baku Membutuhkan Restock!
                    </h4>
                    <p class="text-xs text-amber-800/80 mt-0.5">
                        Beberapa bahan berada di bawah batas minimum aman atau kosong. Lakukan penyesuaian atau pembelian dari pasar ikan.
                    </p>
                </div>
            </div>
        </div>

        <!-- Chips -->
        <div class="flex flex-wrap gap-2 pt-1">
            <div
                v-for="item in items"
                :key="item.id"
                class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-semibold bg-white border border-amber-200 shadow-2xs"
            >
                <span class="text-slate-800">{{ item.name }}</span>
                <span
                    class="font-mono px-1.5 py-0.2 rounded text-[11px]"
                    :class="[item.current_stock <= 0 ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-800']"
                >
                    {{ formatNumber(item.current_stock, 2) }} {{ item.unit }}
                </span>
                <button
                    type="button"
                    @click="emit('adjust', item)"
                    class="text-blue-600 hover:text-blue-800 hover:underline cursor-pointer text-[11px] font-bold"
                >
                    + Atur Stok
                </button>
            </div>
        </div>
    </div>
</template>

