<script setup>
import { useRouter } from 'vue-router';
import { Eye, PlusCircle, CheckCircle2, AlertTriangle, XCircle, Search } from 'lucide-vue-next';
import { formatNumber } from '../../../utils/formatter';
import BaseCard from '../../../components/common/BaseCard.vue';
import BaseEmpty from '../../../components/common/BaseEmpty.vue';

defineProps({
    ingredients: {
        type: Array,
        default: () => [],
    },
    loading: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['adjust']);

const router = useRouter();

function getStockStatusBadge(status) {
    switch (status) {
        case 'available':
            return {
                label: 'Tersedia',
                class: 'bg-emerald-50 text-emerald-700 border-emerald-200',
                icon: CheckCircle2,
            };
        case 'low_stock':
            return {
                label: 'Menipis (≤ 5)',
                class: 'bg-amber-50 text-amber-700 border-amber-200',
                icon: AlertTriangle,
            };
        case 'out_of_stock':
            return {
                label: 'Habis (0)',
                class: 'bg-rose-50 text-rose-700 border-rose-200',
                icon: XCircle,
            };
        default:
            return {
                label: status,
                class: 'bg-slate-100 text-slate-600 border-slate-200',
                icon: CheckCircle2,
            };
    }
}
</script>

<template>
    <BaseCard padding="p-0">
        <!-- Skeleton -->
        <div v-if="loading" class="p-6 space-y-3">
            <div v-for="i in 5" :key="i" class="h-12 bg-slate-100 rounded-lg animate-pulse"></div>
        </div>

        <!-- Empty -->
        <div v-else-if="!ingredients || ingredients.length === 0" class="p-8">
            <BaseEmpty
                title="Bahan Baku Tidak Ditemukan"
                message="Tidak ada bahan baku yang sesuai dengan kriteria filter atau pencarian Anda."
            />
        </div>

        <!-- Table -->
        <div v-else class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-left text-sm">
                <thead>
                    <tr class="bg-slate-50/70 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="px-6 py-3.5">Nama Bahan Baku</th>
                        <th class="px-4 py-3.5">Satuan</th>
                        <th class="px-4 py-3.5 text-right">Stok Fisik Saat Ini</th>
                        <th class="px-4 py-3.5">Status Persediaan</th>
                        <th class="px-6 py-3.5 text-right">Aksi Manajemen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    <tr
                        v-for="item in ingredients"
                        :key="item.id"
                        class="hover:bg-slate-50/70 transition-colors cursor-pointer"
                        @click="router.push(`/inventory/${item.id}`)"
                    >
                        <!-- Name -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="font-bold text-slate-800 text-sm hover:text-blue-600 transition-colors">
                                {{ item.name }}
                            </span>
                        </td>

                        <!-- Unit -->
                        <td class="px-4 py-4 whitespace-nowrap text-xs font-mono text-slate-500 uppercase">
                            {{ item.unit }}
                        </td>

                        <!-- Current Stock (supports decimal) -->
                        <td class="px-4 py-4 whitespace-nowrap text-right font-mono font-bold text-base" :class="[item.current_stock <= 0 ? 'text-rose-600' : 'text-slate-800']">
                            {{ formatNumber(item.current_stock, 2) }} <span class="text-xs font-normal text-slate-400">{{ item.unit }}</span>
                        </td>

                        <!-- Status Badge -->
                        <td class="px-4 py-4 whitespace-nowrap">
                            <span
                                class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold border"
                                :class="getStockStatusBadge(item.status).class"
                            >
                                <component :is="getStockStatusBadge(item.status).icon" class="w-3.5 h-3.5" />
                                <span>{{ getStockStatusBadge(item.status).label }}</span>
                            </span>
                        </td>

                        <!-- Action -->
                        <td class="px-6 py-4 whitespace-nowrap text-right" @click.stop>
                            <div class="flex items-center justify-end gap-2">
                                <button
                                    type="button"
                                    @click="emit('adjust', item)"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 transition-colors cursor-pointer"
                                    title="Sesuaikan Stok"
                                >
                                    <PlusCircle class="w-3.5 h-3.5" />
                                    <span>Sesuaikan</span>
                                </button>

                                <router-link
                                    :to="`/inventory/${item.id}`"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold text-blue-600 bg-blue-50 hover:bg-blue-100 border border-blue-200 transition-colors"
                                >
                                    <Eye class="w-3.5 h-3.5" />
                                    <span>Detail</span>
                                </router-link>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </BaseCard>
</template>

