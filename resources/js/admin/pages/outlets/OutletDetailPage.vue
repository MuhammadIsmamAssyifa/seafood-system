<script setup>
import { onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import {
    ArrowLeft,
    Building2,
    Edit,
    Trash2,
    Users,
    UtensilsCrossed,
    LayoutGrid,
    ShoppingBag,
    Phone,
    MapPin,
    Tag
} from 'lucide-vue-next';
import { useOutletStore } from '../../stores/outlet.store';
import OutletStatusBadge from './components/OutletStatusBadge.vue';
import OutletFormModal from './components/OutletFormModal.vue';
import OutletEmployeeList from './components/OutletEmployeeList.vue';

const route = useRoute();
const router = useRouter();
const outletStore = useOutletStore();

const isEditModalOpen = ref(false);

onMounted(async () => {
    const id = route.params.id;
    await outletStore.fetchDetail(id);
});

function goBack() {
    router.push('/outlets');
}

function openEdit() {
    isEditModalOpen.value = true;
}

function closeEdit() {
    isEditModalOpen.value = false;
}

async function handleUpdate(formData) {
    try {
        await outletStore.updateOutlet(outletStore.selectedOutlet.id, formData);
        await outletStore.fetchDetail(outletStore.selectedOutlet.id);
        closeEdit();
    } catch (err) {
        // handled in store
    }
}

async function handleAssignment(userIds) {
    try {
        await outletStore.assignUsers(outletStore.selectedOutlet.id, userIds);
        await outletStore.fetchDetail(outletStore.selectedOutlet.id);
    } catch (err) {
        // handled in store
    }
}
</script>

<template>
    <div class="space-y-6 max-w-5xl mx-auto pb-16">
        <!-- Top Navigation & Actions -->
        <div class="flex items-center justify-between">
            <button
                @click="goBack"
                class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-slate-800 transition"
            >
                <ArrowLeft class="w-4 h-4" />
                Kembali ke Daftar Cabang
            </button>

            <div v-if="outletStore.selectedOutlet" class="flex items-center gap-2">
                <button
                    @click="openEdit"
                    class="px-3.5 py-1.5 text-xs font-bold rounded-xl bg-amber-50 text-amber-700 hover:bg-amber-100 border border-amber-200 transition inline-flex items-center gap-1.5"
                >
                    <Edit class="w-3.5 h-3.5" />
                    Edit Cabang
                </button>
            </div>
        </div>

        <!-- Loading -->
        <div v-if="outletStore.detailLoading" class="p-12 text-center text-slate-400 bg-white rounded-2xl border border-slate-200">
            <span class="w-5 h-5 border-2 border-sky-500 border-t-transparent rounded-full animate-spin inline-block mb-2"></span>
            <p class="text-xs">Memuat informasi cabang...</p>
        </div>

        <div v-else-if="outletStore.selectedOutlet" class="space-y-6">
            <!-- Header Card -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-sky-100 text-sky-700 flex items-center justify-center font-bold">
                        <Building2 class="w-8 h-8" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2.5">
                            <h2 class="text-xl font-bold text-slate-800">
                                {{ outletStore.selectedOutlet.name }}
                            </h2>
                            <OutletStatusBadge :status="outletStore.selectedOutlet.status" />
                        </div>
                        <div class="flex flex-wrap items-center gap-3 mt-1.5 text-xs text-slate-500">
                            <span class="font-mono bg-slate-100 text-slate-700 px-2 py-0.5 rounded font-bold">
                                {{ outletStore.selectedOutlet.code }}
                            </span>
                            <span>•</span>
                            <span>Restoran: {{ outletStore.selectedOutlet.restaurant_name || 'Seafood Bahari' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
                    <div class="flex items-center gap-2 text-slate-400 mb-1">
                        <Users class="w-4 h-4 text-purple-600" />
                        <span class="text-xs font-semibold">Total Karyawan</span>
                    </div>
                    <span class="text-2xl font-extrabold text-slate-800">
                        {{ outletStore.selectedOutlet.users_count || 0 }}
                    </span>
                </div>

                <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
                    <div class="flex items-center gap-2 text-slate-400 mb-1">
                        <UtensilsCrossed class="w-4 h-4 text-sky-600" />
                        <span class="text-xs font-semibold">Menu Aktif</span>
                    </div>
                    <span class="text-2xl font-extrabold text-slate-800">
                        {{ outletStore.selectedOutlet.menus_count || 0 }}
                    </span>
                </div>

                <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
                    <div class="flex items-center gap-2 text-slate-400 mb-1">
                        <LayoutGrid class="w-4 h-4 text-amber-600" />
                        <span class="text-xs font-semibold">Meja Pelanggan</span>
                    </div>
                    <span class="text-2xl font-extrabold text-slate-800">
                        {{ outletStore.selectedOutlet.tables_count || 0 }}
                    </span>
                </div>

                <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
                    <div class="flex items-center gap-2 text-slate-400 mb-1">
                        <ShoppingBag class="w-4 h-4 text-emerald-600" />
                        <span class="text-xs font-semibold">Pesanan Hari Ini</span>
                    </div>
                    <span class="text-2xl font-extrabold text-emerald-600">
                        {{ outletStore.selectedOutlet.orders_today_count || 0 }}
                    </span>
                </div>
            </div>

            <!-- Address & Contact Info -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-start gap-3">
                    <MapPin class="w-5 h-5 text-slate-400 flex-shrink-0 mt-0.5" />
                    <div>
                        <span class="text-xs font-semibold text-slate-400 block mb-0.5">Alamat Lokasi Cabang</span>
                        <p class="text-sm text-slate-700 font-medium">
                            {{ outletStore.selectedOutlet.address || 'Alamat cabang belum diatur.' }}
                        </p>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-start gap-3">
                    <Phone class="w-5 h-5 text-slate-400 flex-shrink-0 mt-0.5" />
                    <div>
                        <span class="text-xs font-semibold text-slate-400 block mb-0.5">Kontak / Telepon</span>
                        <p class="text-sm text-slate-700 font-medium">
                            {{ outletStore.selectedOutlet.phone || 'Nomor telepon belum diatur.' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Employee Assignment Component -->
            <OutletEmployeeList
                :outlet="outletStore.selectedOutlet"
                :saving="outletStore.saving"
                @save-assignment="handleAssignment"
            />
        </div>

        <!-- Edit Modal -->
        <OutletFormModal
            :is-open="isEditModalOpen"
            :outlet="outletStore.selectedOutlet"
            :saving="outletStore.saving"
            @close="closeEdit"
            @submit="handleUpdate"
        />
    </div>
</template>

