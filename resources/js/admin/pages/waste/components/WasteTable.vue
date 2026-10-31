<script setup>
import { useRouter } from 'vue-router';
import { formatDate, formatDateTime, formatCurrency, formatNumber } from '../../../utils/formatter';
import WasteReasonBadge from './WasteReasonBadge.vue';
import { Eye, ChevronLeft, ChevronRight, Inbox } from 'lucide-vue-next';

const props = defineProps({
    records: {
        type: Array,
        default: () => [],
    },
    pagination: {
        type: Object,
        default: () => ({
            current_page: 1,
            last_page: 1,
            total: 0,
            per_page: 15,
        }),
    },
    loading: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['page-change']);
const router = useRouter();

function goToDetail(id) {
    router.push(`/waste/${id}`);
}
</script>

<template>
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-500 font-semibold uppercase tracking-wider">
                        <th class="py-3 px-4">Waktu</th>
                        <th class="py-3 px-4">Bahan Baku</th>
                        <th class="py-3 px-4 text-right">Kuantitas</th>
                        <th class="py-3 px-4 text-right">Biaya/Unit</th>
                        <th class="py-3 px-4 text-right">Total Kerugian</th>
                        <th class="py-3 px-4">Penyebab (Alasan)</th>
                        <th class="py-3 px-4">Dicatat Oleh</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    <!-- Loading Skeleton -->
                    <tr v-if="loading" v-for="i in 5" :key="i">
                        <td class="py-4 px-4"><div class="h-4 w-24 bg-slate-100 animate-pulse rounded"></div></td>
                        <td class="py-4 px-4"><div class="h-4 w-32 bg-slate-100 animate-pulse rounded"></div></td>
                        <td class="py-4 px-4 text-right"><div class="h-4 w-16 bg-slate-100 animate-pulse rounded ml-auto"></div></td>
                        <td class="py-4 px-4 text-right"><div class="h-4 w-20 bg-slate-100 animate-pulse rounded ml-auto"></div></td>
                        <td class="py-4 px-4 text-right"><div class="h-4 w-24 bg-slate-100 animate-pulse rounded ml-auto"></div></td>
                        <td class="py-4 px-4"><div class="h-4 w-24 bg-slate-100 animate-pulse rounded"></div></td>
                        <td class="py-4 px-4"><div class="h-4 w-20 bg-slate-100 animate-pulse rounded"></div></td>
                        <td class="py-4 px-4 text-center"><div class="h-4 w-8 bg-slate-100 animate-pulse rounded mx-auto"></div></td>
                    </tr>

                    <!-- Empty State -->
                    <tr v-else-if="records.length === 0">
                        <td colspan="8" class="py-12 text-center">
                            <div class="flex flex-col items-center justify-center text-slate-400">
                                <Inbox class="w-10 h-10 stroke-[1.5] mb-2" />
                                <p class="text-sm font-medium text-slate-600">Belum ada riwayat waste</p>
                                <p class="text-xs text-slate-400 mt-0.5">Catatan bahan terbuang akan tercatat rapi di sini.</p>
                            </div>
                        </td>
                    </tr>

                    <!-- Data Rows -->
                    <tr
                        v-else
                        v-for="record in records"
                        :key="record.id"
                        class="hover:bg-slate-50/60 transition-colors"
                    >
                        <!-- Date -->
                        <td class="py-3 px-4 whitespace-nowrap">
                            <div class="font-medium text-slate-900">{{ formatDate(record.created_at, 'DD MMM YYYY') }}</div>
                            <div class="text-[11px] text-slate-400">{{ formatDate(record.created_at, 'HH:mm') }} WIB</div>
                        </td>

                        <!-- Ingredient -->
                        <td class="py-3 px-4">
                            <div class="font-semibold text-slate-900">{{ record.ingredient?.name || '-' }}</div>
                            <div v-if="record.note" class="text-[11px] text-slate-500 italic truncate max-w-xs">
                                "{{ record.note }}"
                            </div>
                        </td>

                        <!-- Quantity -->
                        <td class="py-3 px-4 text-right whitespace-nowrap font-medium text-slate-900">
                            {{ formatNumber(record.quantity, 3) }}
                            <span class="text-slate-400 text-[11px]">{{ record.ingredient?.unit || 'kg' }}</span>
                        </td>

                        <!-- Unit Cost -->
                        <td class="py-3 px-4 text-right whitespace-nowrap text-slate-600">
                            {{ formatCurrency(record.unit_cost) }}
                        </td>

                        <!-- Total Loss -->
                        <td class="py-3 px-4 text-right whitespace-nowrap">
                            <span class="font-bold text-red-600">{{ formatCurrency(record.total_cost) }}</span>
                        </td>

                        <!-- Reason -->
                        <td class="py-3 px-4 whitespace-nowrap">
                            <WasteReasonBadge :reason="record.reason" />
                        </td>

                        <!-- Creator -->
                        <td class="py-3 px-4 whitespace-nowrap text-slate-600">
                            {{ record.creator?.name || 'Admin' }}
                        </td>

                        <!-- Actions -->
                        <td class="py-3 px-4 text-center whitespace-nowrap">
                            <button
                                @click="goToDetail(record.id)"
                                class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-medium text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors"
                            >
                                <Eye class="w-3.5 h-3.5" />
                                <span>Detail</span>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div
            v-if="pagination.last_page > 1"
            class="px-4 py-3 bg-white border-t border-slate-100 flex items-center justify-between text-xs text-slate-500"
        >
            <div>
                Menampilkan halaman <strong class="text-slate-800">{{ pagination.current_page }}</strong> dari
                <strong class="text-slate-800">{{ pagination.last_page }}</strong> (Total {{ pagination.total }} catatan)
            </div>
            <div class="flex items-center gap-1.5">
                <button
                    @click="emit('page-change', pagination.current_page - 1)"
                    :disabled="pagination.current_page <= 1"
                    class="p-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 disabled:opacity-40 disabled:pointer-events-none transition-colors"
                >
                    <ChevronLeft class="w-4 h-4" />
                </button>
                <button
                    @click="emit('page-change', pagination.current_page + 1)"
                    :disabled="pagination.current_page >= pagination.last_page"
                    class="p-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 disabled:opacity-40 disabled:pointer-events-none transition-colors"
                >
                    <ChevronRight class="w-4 h-4" />
                </button>
            </div>
        </div>
    </div>
</template>

