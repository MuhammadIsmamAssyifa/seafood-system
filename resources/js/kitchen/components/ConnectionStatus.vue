<template>
    <div>
        <!-- Disconnected / Reconnecting Floating Alert Banner (Sprint 07) -->
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0 -translate-y-4"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 -translate-y-4"
        >
            <div
                v-if="isDisconnected || isConnecting"
                class="mb-3 px-4 py-3 rounded-2xl border shadow-lg flex items-center justify-between gap-3 text-xs font-semibold select-none"
                :class="bannerClasses"
            >
                <div class="flex items-center gap-2.5">
                    <!-- Spinning / Warning Icon -->
                    <svg
                        v-if="isConnecting"
                        class="w-4 h-4 animate-spin text-amber-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <svg
                        v-else
                        class="w-4 h-4 text-rose-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>

                    <span>{{ bannerMessage }}</span>
                </div>

                <button
                    type="button"
                    class="px-3 py-1 rounded-xl text-xs font-bold transition-all border active:scale-95"
                    :class="buttonClasses"
                    @click="handleManualReconnect"
                >
                    Hubungkan Ulang
                </button>
            </div>
        </Transition>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { useKitchenStore } from '@/kitchen/stores/kitchen.store';
import { getEcho } from '@/kitchen/services/pusher';

const kitchenStore = useKitchenStore();

const isDisconnected = computed(() => {
    return ['disconnected', 'unavailable', 'failed'].includes(kitchenStore.connectionStatus);
});

const isConnecting = computed(() => {
    return kitchenStore.connectionStatus === 'connecting';
});

const bannerClasses = computed(() => {
    if (isConnecting.value) {
        return 'bg-amber-950/80 border-amber-600/70 text-amber-200';
    }
    return 'bg-rose-950/80 border-rose-600/70 text-rose-200';
});

const buttonClasses = computed(() => {
    if (isConnecting.value) {
        return 'bg-amber-900/60 hover:bg-amber-800 text-amber-100 border-amber-600';
    }
    return 'bg-rose-900/60 hover:bg-rose-800 text-rose-100 border-rose-600';
});

const bannerMessage = computed(() => {
    if (isConnecting.value) {
        return 'Koneksi realtime terputus. Mencoba menghubungkan kembali...';
    }
    return 'Koneksi realtime ke server terputus. Notifikasi otomatis mungkin terhambat.';
});

const handleManualReconnect = () => {
    const echo = getEcho();
    if (echo?.connector?.pusher) {
        kitchenStore.setConnectionStatus('connecting');
        echo.connector.pusher.connect();
    }
    kitchenStore.fetchOrders(true);
};
</script>

