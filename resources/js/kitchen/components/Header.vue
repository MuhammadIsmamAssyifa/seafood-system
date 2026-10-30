<template>
    <header class="h-16 bg-slate-900 border-b border-slate-800 px-4 sm:px-6 flex items-center justify-between select-none shadow-md">
        <!-- Left: Logo & Station Title -->
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-2.5">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-500 flex items-center justify-center shadow-lg shadow-blue-500/20">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-base font-bold text-white tracking-wider flex items-center gap-2">
                        SEAFOOD KDS
                        <span class="text-[10px] uppercase font-semibold px-2 py-0.5 rounded-full bg-blue-500/10 text-blue-400 border border-blue-500/20">
                            v1.0
                        </span>
                    </h1>
                    <p class="text-xs text-slate-400 font-medium">Kitchen Display System</p>
                </div>
            </div>

            <!-- Station Selector -->
            <div class="hidden md:flex items-center ml-4 pl-4 border-l border-slate-800">
                <span class="text-xs text-slate-400 mr-2 font-medium">Station:</span>
                <select
                    v-model="selectedStation"
                    class="bg-slate-800 border border-slate-700 text-slate-200 text-xs rounded-lg px-2.5 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500 font-medium"
                    @change="onStationChange"
                >
                    <option value="all">Semua Station</option>
                    <option v-for="st in kitchenStore.stations" :key="st.id" :value="st.id">
                        {{ st.name }}
                    </option>
                </select>
            </div>
        </div>

        <!-- Center: Live Clock -->
        <div class="hidden lg:flex items-center gap-2 bg-slate-950/60 border border-slate-800/80 px-4 py-1.5 rounded-xl font-mono">
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="text-sm font-semibold text-slate-200 tracking-wider">
                {{ currentTime }}
            </span>
        </div>

        <!-- Right: Realtime status, Fullscreen, User, Logout -->
        <div class="flex items-center gap-3">
            <!-- Pusher Connection Status -->
            <div
                :class="[
                    'flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium border transition-colors',
                    connectionBadgeClass,
                ]"
                :title="`Status WebSocket: ${kitchenStore.connectionStatus}`"
            >
                <span :class="['w-2 h-2 rounded-full', connectionDotClass]"></span>
                <span class="hidden sm:inline">{{ connectionLabel }}</span>
            </div>

            <!-- Fullscreen Button -->
            <button
                type="button"
                class="p-2 text-slate-400 hover:text-white rounded-lg hover:bg-slate-800 transition-colors"
                title="Toggle Fullscreen"
                @click="toggleFullscreen"
            >
                <svg v-if="!isFullscreen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                </svg>
                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 9L4 4m0 0h4m-4 0v4m11-4l5 5m0-5h-4m4 0v4M9 15l-5 5m0 0h4m-4 0v-4m16 4l-5-5m5 5h-4m4 0v-4" />
                </svg>
            </button>

            <!-- User profile & Logout -->
            <div class="flex items-center gap-2 pl-2 border-l border-slate-800">
                <div class="w-8 h-8 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-xs font-bold text-slate-200">
                    {{ userInitial }}
                </div>
                <div class="hidden sm:flex flex-col text-left">
                    <span class="text-xs font-semibold text-slate-200 leading-tight">{{ authStore.userName }}</span>
                    <span class="text-[10px] text-slate-400 uppercase leading-tight">{{ authStore.userRole }}</span>
                </div>
                <button
                    type="button"
                    class="ml-1 p-2 text-rose-400 hover:text-rose-300 hover:bg-rose-950/40 rounded-lg transition-colors"
                    title="Logout"
                    @click="handleLogout"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </button>
            </div>
        </div>
    </header>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/kitchen/stores/auth.store';
import { useKitchenStore } from '@/kitchen/stores/kitchen.store';

const router = useRouter();
const authStore = useAuthStore();
const kitchenStore = useKitchenStore();

const currentTime = ref('');
const isFullscreen = ref(false);
const selectedStation = ref(authStore.selectedStation);

let clockInterval = null;

const updateClock = () => {
    const now = new Date();
    currentTime.value = now.toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false,
    });
};

const userInitial = computed(() => {
    const name = authStore.userName || 'K';
    return name.charAt(0).toUpperCase();
});

const connectionLabel = computed(() => {
    switch (kitchenStore.connectionStatus) {
        case 'connected': return 'Realtime';
        case 'connecting': return 'Menghubungkan';
        case 'unavailable': return 'Terputus';
        default: return 'Offline';
    }
});

const connectionBadgeClass = computed(() => {
    switch (kitchenStore.connectionStatus) {
        case 'connected':
            return 'bg-emerald-950/60 border-emerald-800/80 text-emerald-400';
        case 'connecting':
            return 'bg-amber-950/60 border-amber-800/80 text-amber-400';
        default:
            return 'bg-slate-800 border-slate-700 text-slate-400';
    }
});

const connectionDotClass = computed(() => {
    switch (kitchenStore.connectionStatus) {
        case 'connected': return 'bg-emerald-400 animate-pulse';
        case 'connecting': return 'bg-amber-400 animate-pulse';
        default: return 'bg-slate-500';
    }
});

const onStationChange = () => {
    authStore.setStation(selectedStation.value);
};

const toggleFullscreen = () => {
    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen().then(() => {
            isFullscreen.value = true;
        }).catch(() => {});
    } else {
        document.exitFullscreen().then(() => {
            isFullscreen.value = false;
        }).catch(() => {});
    }
};

const handleLogout = async () => {
    await authStore.logout();
    router.push({ name: 'kitchen.login' });
};

onMounted(() => {
    updateClock();
    clockInterval = setInterval(updateClock, 1000);
});

onUnmounted(() => {
    if (clockInterval) clearInterval(clockInterval);
});
</script>

