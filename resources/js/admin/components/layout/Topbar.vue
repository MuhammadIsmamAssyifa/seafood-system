<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import {
    Menu,
    ChevronDown,
    Building2,
    LogOut,
    User,
    CookingPot,
    ShieldCheck,
    Store,
    Calendar,
    Search,
} from 'lucide-vue-next';
import { useAuthStore } from '../../stores/auth.store';
import { useUserStore } from '../../stores/user.store';
import { formatDate } from '../../utils/formatter';
import GlobalSearchModal from '../search/GlobalSearchModal.vue';

const emit = defineEmits(['toggle-sidebar']);

const router = useRouter();
const authStore = useAuthStore();
const userStore = useUserStore();

const isProfileOpen = ref(false);
const isSearchOpen = ref(false);
const profileRef = ref(null);
const currentDate = ref(formatDate(new Date(), 'dddd, DD MMMM YYYY'));

function handleClickOutside(e) {
    if (profileRef.value && !profileRef.value.contains(e.target)) {
        isProfileOpen.value = false;
    }
}

function handleGlobalKeydown(e) {
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
        e.preventDefault();
        isSearchOpen.value = !isSearchOpen.value;
    }
}

onMounted(() => {
    window.addEventListener('click', handleClickOutside);
    window.addEventListener('keydown', handleGlobalKeydown);
});

onUnmounted(() => {
    window.removeEventListener('click', handleClickOutside);
    window.removeEventListener('keydown', handleGlobalKeydown);
});

async function handleLogout() {
    isProfileOpen.value = false;
    await authStore.logout();
    router.push('/login');
}
</script>

<template>
    <header class="h-16 bg-white border-b border-slate-200/80 sticky top-0 z-30 flex items-center justify-between px-4 sm:px-6">
        <!-- Left: Mobile Toggle & Outlet Indicator -->
        <div class="flex items-center gap-3">
            <button
                type="button"
                @click="emit('toggle-sidebar')"
                class="lg:hidden p-2 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-colors cursor-pointer"
            >
                <Menu class="w-5 h-5" />
            </button>

            <!-- Active Outlet Badge -->
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-50 border border-slate-200 text-xs font-medium text-slate-700">
                <Store class="w-4 h-4 text-blue-600" />
                <span class="hidden sm:inline text-slate-500 font-normal">Outlet:</span>
                <span class="font-semibold text-slate-800">{{ userStore.activeOutlet.name }}</span>
            </div>

            <div class="hidden md:flex items-center gap-1.5 text-xs text-slate-500">
                <Calendar class="w-3.5 h-3.5 text-slate-400" />
                <span>{{ currentDate }}</span>
            </div>
        </div>

        <!-- Center / Right: Global Search, Kitchen Switcher & User Profile Menu -->
        <div class="flex items-center gap-2.5 sm:gap-3">
            <!-- Global Search Button -->
            <button
                type="button"
                @click="isSearchOpen = true"
                class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-100/80 hover:bg-slate-200/80 text-slate-500 hover:text-slate-800 transition-colors text-xs font-medium cursor-pointer"
                title="Pencarian Global (Ctrl + K)"
            >
                <Search class="w-4 h-4 text-slate-400" />
                <span class="hidden md:inline">Cari apa saja...</span>
                <kbd class="hidden sm:inline-flex items-center px-1.5 py-0.5 text-[10px] bg-white border border-slate-200 rounded text-slate-500 font-mono shadow-2xs">Ctrl K</kbd>
            </button>

            <!-- Kitchen App Shortcut -->
            <a
                href="/kitchen"
                target="_blank"
                class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-amber-50 text-amber-800 border border-amber-200 text-xs font-semibold hover:bg-amber-100 transition-colors"
                title="Buka Kitchen Display System di Tab Baru"
            >
                <CookingPot class="w-4 h-4 text-amber-600" />
                <span>Kitchen KDS</span>
            </a>

            <!-- User Dropdown -->
            <div class="relative" ref="profileRef">
                <button
                    type="button"
                    @click.stop="isProfileOpen = !isProfileOpen"
                    class="flex items-center gap-2.5 p-1.5 rounded-xl hover:bg-slate-100 transition-colors cursor-pointer"
                >
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-blue-600 to-indigo-600 text-white flex items-center justify-center font-bold text-xs uppercase shadow-xs">
                        {{ authStore.userName.charAt(0) }}
                    </div>

                    <div class="hidden sm:block text-left">
                        <div class="text-xs font-semibold text-slate-800 leading-tight">
                            {{ authStore.userName }}
                        </div>
                        <div class="text-[10px] uppercase font-mono tracking-wider text-slate-500">
                            {{ authStore.userRole }}
                        </div>
                    </div>

                    <ChevronDown class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="[isProfileOpen ? 'rotate-180' : '']" />
                </button>

                <!-- Profile Dropdown Content -->
                <Transition
                    enter-active-class="transition duration-150 ease-out"
                    enter-from-class="transform scale-95 opacity-0"
                    enter-to-class="transform scale-100 opacity-100"
                    leave-active-class="transition duration-100 ease-in"
                    leave-from-class="transform scale-100 opacity-100"
                    leave-to-class="transform scale-95 opacity-0"
                >
                    <div
                        v-if="isProfileOpen"
                        class="absolute right-0 mt-2 w-64 bg-white rounded-xl shadow-xl border border-slate-200/80 py-1.5 z-50 overflow-hidden"
                    >
                        <div class="px-4 py-3 border-b border-slate-100 bg-slate-50/70">
                            <p class="text-xs text-slate-500">Masuk sebagai</p>
                            <p class="text-sm font-bold text-slate-800 truncate">{{ authStore.userName }}</p>
                            <div class="mt-1 flex items-center gap-1.5">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold uppercase font-mono bg-blue-100 text-blue-800">
                                    <ShieldCheck class="w-3 h-3" />
                                    {{ authStore.userRole }}
                                </span>
                            </div>
                        </div>

                        <div class="py-1">
                            <div v-if="authStore.userRestaurant" class="px-4 py-2 text-xs text-slate-600 flex items-center gap-2 border-b border-slate-100">
                                <Building2 class="w-4 h-4 text-slate-400" />
                                <span class="truncate">{{ authStore.userRestaurant.name }}</span>
                            </div>

                            <a
                                href="/kitchen"
                                class="sm:hidden flex items-center gap-2.5 px-4 py-2 text-xs text-amber-700 hover:bg-amber-50"
                            >
                                <CookingPot class="w-4 h-4 text-amber-600" />
                                <span>Kitchen Display (KDS)</span>
                            </a>
                        </div>

                        <div class="pt-1 border-t border-slate-100">
                            <button
                                type="button"
                                @click="handleLogout"
                                class="w-full flex items-center gap-2.5 px-4 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer text-left"
                            >
                                <LogOut class="w-4 h-4" />
                                <span>Keluar / Logout</span>
                            </button>
                        </div>
                    </div>
                </Transition>
            </div>
        </div>

        <!-- Global Search Modal -->
        <GlobalSearchModal v-model="isSearchOpen" />
    </header>
</template>
