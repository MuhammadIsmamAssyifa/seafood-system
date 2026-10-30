<template>
    <div class="h-full flex flex-col">
        <!-- State 1: Loading -->
        <Loading
            v-if="loading && orders.length === 0"
            title="Loading Kitchen Queue..."
            subtitle="Menyiapkan antrean operasional dapur dari server"
        />

        <!-- State 2: Error -->
        <ErrorState
            v-else-if="error && orders.length === 0"
            title="Gagal Mengambil Data Kitchen"
            :message="error"
            @retry="$emit('retry')"
        />

        <!-- State 3: Empty -->
        <EmptyState
            v-else-if="orders.length === 0"
            :title="emptyTitle"
            :description="emptyDescription"
        >
            <template #action>
                <button
                    type="button"
                    class="px-5 py-2.5 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-bold transition-all shadow-md shadow-blue-600/30 flex items-center gap-2"
                    @click="$emit('retry')"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Segarkan Antrean
                </button>
            </template>
        </EmptyState>

        <!-- State 4: Queue Grid (Optimized for Laptop & Touch displays) -->
        <div
            v-else
            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-4 overflow-y-auto no-scrollbar pr-1 pb-6"
        >
            <QueueCard
                v-for="order in orders"
                :key="order.id"
                :order="order"
                @select="$emit('select-order', $event)"
            />
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import QueueCard from './QueueCard.vue';
import Loading from '@/kitchen/components/Loading.vue';
import EmptyState from '@/kitchen/components/EmptyState.vue';
import ErrorState from '@/kitchen/components/ErrorState.vue';

const props = defineProps({
    orders: {
        type: Array,
        default: () => [],
    },
    activeTab: {
        type: String,
        default: 'all',
    },
    loading: {
        type: Boolean,
        default: false,
    },
    error: {
        type: String,
        default: null,
    },
});

defineEmits(['select-order', 'retry']);

const emptyTitle = computed(() => {
    switch (props.activeTab) {
        case 'waiting': return 'Tidak Ada Pesanan Menunggu';
        case 'preparing': return 'Tidak Ada Pesanan Sedang Dimasak';
        case 'ready': return 'Tidak Ada Pesanan Siap Saji';
        default: return 'Tidak Ada Antrean Dapur Aktif';
    }
});

const emptyDescription = computed(() => {
    switch (props.activeTab) {
        case 'waiting': return 'Semua pesanan yang masuk telah diterima oleh koki dapur.';
        case 'preparing': return 'Tidak ada menu yang sedang dalam proses penggorengan atau pembakaran.';
        case 'ready': return 'Belum ada menu yang menunggu diantar ke meja tamu.';
        default: return 'Dapur bersih dan siap menerima order. Pesanan baru dari kasir POS akan muncul otomatis.';
    }
});
</script>
