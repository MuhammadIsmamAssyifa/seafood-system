<template>
    <div class="fixed top-5 right-5 z-50 flex flex-col gap-2.5 pointer-events-none max-w-sm w-full">
        <TransitionGroup
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0 translate-y-[-10px] scale-95"
            enter-to-class="opacity-100 translate-y-0 scale-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-90"
        >
            <div
                v-for="toast in toasts"
                :key="toast.id"
                class="w-full pointer-events-auto"
            >
                <!-- Rich Order Notification Toast (Sprint 07) -->
                <NotificationToast
                    v-if="toast.isOrderNotification"
                    :order-number="toast.orderNumber"
                    :table-number="toast.tableNumber"
                    :items-count="toast.itemsCount"
                    @close="remove(toast.id)"
                />

                <!-- Standard Toast -->
                <div
                    v-else
                    :class="[
                        'flex items-start gap-3 p-4 rounded-xl border shadow-xl backdrop-blur-md transition-all',
                        typeClasses(toast.type),
                    ]"
                >
                    <!-- Icon -->
                    <div class="shrink-0 mt-0.5">
                        <svg v-if="toast.type === 'success'" class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <svg v-else-if="toast.type === 'error'" class="w-5 h-5 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <svg v-else-if="toast.type === 'warning'" class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <svg v-else class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>

                    <!-- Text -->
                    <div class="flex-1 text-sm font-medium leading-relaxed">
                        {{ toast.message }}
                    </div>

                    <!-- Close button -->
                    <button
                        type="button"
                        class="shrink-0 text-slate-400 hover:text-white p-0.5 rounded transition-colors"
                        @click="remove(toast.id)"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </TransitionGroup>
    </div>
</template>

<script setup>
import NotificationToast from '@/kitchen/components/NotificationToast.vue';
import { useToast } from '@/kitchen/composables/useToast';

const { toasts, remove } = useToast();

const typeClasses = (type) => {
    switch (type) {
        case 'success':
            return 'bg-emerald-950/90 border-emerald-700/60 text-emerald-100';
        case 'error':
            return 'bg-rose-950/90 border-rose-700/60 text-rose-100';
        case 'warning':
            return 'bg-amber-950/90 border-amber-700/60 text-amber-100';
        case 'info':
        default:
            return 'bg-slate-900/90 border-slate-700/60 text-slate-100';
    }
};
</script>
