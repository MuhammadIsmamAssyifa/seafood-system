<script setup>
import { useNotificationStore } from '../../stores/notification.store';

const notificationStore = useNotificationStore();

function getToastIcon(type) {
    switch (type) {
        case 'success':
            return 'CheckCircle';
        case 'error':
            return 'AlertCircle';
        case 'warning':
            return 'AlertTriangle';
        case 'info':
        default:
            return 'Info';
    }
}

function getToastClasses(type) {
    switch (type) {
        case 'success':
            return 'bg-emerald-50 border-emerald-200 text-emerald-900';
        case 'error':
            return 'bg-rose-50 border-rose-200 text-rose-900';
        case 'warning':
            return 'bg-amber-50 border-amber-200 text-amber-900';
        case 'info':
        default:
            return 'bg-blue-50 border-blue-200 text-blue-900';
    }
}

function getIconColor(type) {
    switch (type) {
        case 'success':
            return 'text-emerald-500';
        case 'error':
            return 'text-rose-500';
        case 'warning':
            return 'text-amber-500';
        case 'info':
        default:
            return 'text-blue-500';
    }
}
</script>

<template>
    <div class="fixed top-5 right-5 z-50 flex flex-col gap-2.5 max-w-sm w-full pointer-events-none">
        <TransitionGroup
            enter-active-class="transform transition ease-out duration-300"
            enter-from-class="translate-y-[-10px] opacity-0 scale-95"
            enter-to-class="translate-y-0 opacity-100 scale-100"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-90"
        >
            <div
                v-for="toast in notificationStore.toasts"
                :key="toast.id"
                class="pointer-events-auto p-4 rounded-xl border shadow-lg flex items-start gap-3 backdrop-blur-xs"
                :class="getToastClasses(toast.type)"
            >
                <div class="shrink-0 mt-0.5" :class="getIconColor(toast.type)">
                    <svg v-if="toast.type === 'success'" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <svg v-else-if="toast.type === 'error'" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <svg v-else-if="toast.type === 'warning'" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <svg v-else class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>

                <div class="flex-1 min-w-0">
                    <p v-if="toast.title" class="text-xs font-bold leading-tight">{{ toast.title }}</p>
                    <p class="text-xs font-medium leading-relaxed opacity-90 mt-0.5">{{ toast.message }}</p>
                </div>

                <button
                    type="button"
                    @click="notificationStore.removeToast(toast.id)"
                    class="shrink-0 text-slate-400 hover:text-slate-600 rounded p-0.5 cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </TransitionGroup>
    </div>
</template>

