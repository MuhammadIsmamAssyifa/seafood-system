<script setup>
import { computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import {
    LayoutDashboard,
    BarChart3,
    ShoppingCart,
    Package,
    Users,
    Settings,
    ClipboardList,
    ChefHat,
    Building2,
    Shield,
    ShieldCheck,
    Trash2,
    LogOut,
    UtensilsCrossed,
    X,
} from 'lucide-vue-next';
import { useAuthStore } from '../../stores/auth.store';

const props = defineProps({
    isOpen: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close']);

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

const navigation = [
    {
        name: 'Dashboard',
        href: '/dashboard',
        icon: LayoutDashboard,
        exact: true,
        permission: 'dashboard.view',
    },
    {
        name: 'Reporting',
        href: '/reporting',
        icon: BarChart3,
        permission: 'report.view',
    },
    {
        name: 'Orders',
        href: '/orders',
        icon: ShoppingCart,
        permission: 'order.view',
    },
    {
        name: 'Inventory',
        href: '/inventory',
        icon: Package,
        permission: 'inventory.view',
    },
    {
        name: 'Batch Cost',
        href: '/inventory/cost',
        icon: Shield,
        permission: 'inventory.view',
    },
    {
        name: 'Recipes',
        href: '/recipes',
        icon: ChefHat,
        permission: 'recipe.view',
    },
    {
        name: 'Waste Management',
        href: '/waste',
        icon: Trash2,
        permission: 'inventory.manage',
    },
    {
        name: 'Outlets',
        href: '/outlets',
        icon: Building2,
        permission: 'outlet.view',
    },
    {
        name: 'Employees',
        href: '/employees',
        icon: Users,
        permission: 'employee.view|user.view',
    },
    {
        name: 'Roles & Access',
        href: '/roles',
        icon: ShieldCheck,
        permission: 'role.view|user.manage',
    },
    {
        name: 'Audit Trail',
        href: '/audit-logs',
        icon: ClipboardList,
        permission: 'audit.view|report.view|user.manage',
    },
    {
        name: 'Settings',
        href: '/settings',
        icon: Settings,
        permission: 'settings.view|user.manage',
    },
];

const filteredNavigation = computed(() => {
    return navigation.filter((item) => {
        if (!item.permission) return true;
        return authStore.hasPermission(item.permission);
    });
});

function isActive(item) {
    if (item.exact) {
        return route.path === item.href;
    }
    return route.path.startsWith(item.href);
}

async function handleLogout() {
    await authStore.logout();
    router.push('/login');
}
</script>

<template>
    <!-- Mobile Backdrop -->
    <div
        v-if="isOpen"
        class="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-xs lg:hidden"
        @click="emit('close')"
    ></div>

    <!-- Sidebar Container -->
    <aside
        class="fixed top-0 bottom-0 left-0 z-40 w-64 bg-slate-900 text-slate-300 flex flex-col transition-transform duration-300 ease-in-out lg:translate-x-0 border-r border-slate-800"
        :class="[isOpen ? 'translate-x-0' : '-translate-x-full']"
    >
        <!-- Brand Header -->
        <div class="h-16 px-6 flex items-center justify-between border-b border-slate-800/80 bg-slate-950/40">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-500 flex items-center justify-center text-white shadow-md shadow-blue-500/20">
                    <UtensilsCrossed class="w-5 h-5" />
                </div>
                <div>
                    <h1 class="text-sm font-bold text-white tracking-wide">SEAFOOD POS</h1>
                    <span class="text-[10px] uppercase font-semibold tracking-wider text-blue-400">Admin Portal</span>
                </div>
            </div>

            <!-- Mobile Close Button -->
            <button
                type="button"
                @click="emit('close')"
                class="lg:hidden text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-800"
            >
                <X class="w-5 h-5" />
            </button>
        </div>

        <!-- Navigation Menu -->
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto no-scrollbar">
            <div class="px-3 pb-2 text-[10px] font-bold uppercase tracking-wider text-slate-500">
                Menu Utama
            </div>

            <router-link
                v-for="item in filteredNavigation"
                :key="item.name"
                :to="item.href"
                @click="emit('close')"
                class="flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-medium transition-all group"
                :class="[
                    isActive(item)
                        ? 'bg-blue-600 text-white shadow-sm shadow-blue-600/30'
                        : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/70',
                ]"
            >
                <div class="flex items-center gap-3">
                    <component
                        :is="item.icon"
                        class="w-4.5 h-4.5 transition-colors"
                        :class="[isActive(item) ? 'text-white' : 'text-slate-400 group-hover:text-slate-200']"
                    />
                    <span>{{ item.name }}</span>
                </div>

                <span
                    v-if="item.badge"
                    class="text-[10px] px-1.5 py-0.5 rounded font-mono font-medium"
                    :class="[
                        isActive(item)
                            ? 'bg-blue-700 text-blue-100'
                            : 'bg-slate-800 text-slate-400 group-hover:bg-slate-700 group-hover:text-slate-300',
                    ]"
                >
                    {{ item.badge }}
                </span>
            </router-link>
        </nav>

        <!-- User / Role Card in Footer -->
        <div class="p-3 border-t border-slate-800/80 bg-slate-950/30">
            <div class="flex items-center justify-between p-2 rounded-xl bg-slate-800/50">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-8 h-8 rounded-lg bg-blue-500/20 text-blue-400 border border-blue-500/30 flex items-center justify-center font-bold text-xs uppercase">
                        {{ authStore.userName.charAt(0) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-semibold text-white truncate">{{ authStore.userName }}</p>
                        <p class="text-[10px] text-slate-400 uppercase tracking-wider font-mono">{{ authStore.userRole }}</p>
                    </div>
                </div>

                <button
                    type="button"
                    @click="handleLogout"
                    title="Logout"
                    class="p-1.5 text-slate-400 hover:text-rose-400 hover:bg-slate-800 rounded-lg transition-colors cursor-pointer"
                >
                    <LogOut class="w-4 h-4" />
                </button>
            </div>
        </div>
    </aside>
</template>

