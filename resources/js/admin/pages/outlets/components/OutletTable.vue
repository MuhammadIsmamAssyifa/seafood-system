<script setup>
import { Eye, Edit, Trash2, Building2, Users, UtensilsCrossed, LayoutGrid } from 'lucide-vue-next';
import OutletStatusBadge from './OutletStatusBadge.vue';

const props = defineProps({
    outlets: {
        type: Array,
        default: () => [],
    },
    loading: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['view', 'edit', 'delete']);
</script>

<template>
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50/80 text-xs font-semibold uppercase tracking-wider text-slate-500 border-b border-slate-200/80">
                    <tr>
                        <th class="py-3 px-4">Nama Cabang / Outlet</th>
                        <th class="py-3 px-4">Kode Cabang</th>
                        <th class="py-3 px-4">Alamat & Kontak</th>
                        <th class="py-3 px-4 text-center">Statistik</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <!-- Loading state -->
                    <tr v-if="loading">
                        <td colspan="6" class="py-12 text-center text-slate-400">
                            <div class="inline-flex items-center gap-2">
                                <span class="w-4 h-4 border-2 border-sky-500 border-t-transparent rounded-full animate-spin"></span>
                                Memuat data cabang restoran...
                            </div>
                        </td>
                    </tr>

                    <!-- Empty state -->
                    <tr v-else-if="!outlets.length">
                        <td colspan="6" class="py-12 text-center text-slate-400">
                            Belum ada cabang outlet yang terdaftar.
                        </td>
                    </tr>

                    <!-- Data rows -->
                    <tr
                        v-for="outlet in outlets"
                        :key="outlet.id"
                        class="hover:bg-slate-50/80 transition"
                    >
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <div class="flex items-center gap-2.5">
                                <div class="w-9 h-9 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center font-bold">
                                    <Building2 class="w-5 h-5" />
                                </div>
                                <div>
                                    <span class="font-bold text-slate-800 text-sm block">{{ outlet.name }}</span>
                                    <span class="text-xs text-slate-400">Restoran: {{ outlet.restaurant_name || 'Seafood Bahari' }}</span>
                                </div>
                            </div>
                        </td>

                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <span class="font-mono text-xs font-bold px-2 py-0.5 rounded-md bg-slate-100 text-slate-700">
                                {{ outlet.code }}
                            </span>
                        </td>

                        <td class="py-3.5 px-4 text-xs text-slate-600 max-w-xs truncate">
                            <span class="block truncate">{{ outlet.address || 'Alamat belum diatur' }}</span>
                            <span class="text-[11px] text-slate-400">{{ outlet.phone || '-' }}</span>
                        </td>

                        <td class="py-3.5 px-4 whitespace-nowrap text-center">
                            <div class="inline-flex items-center gap-3 text-xs text-slate-500">
                                <span class="inline-flex items-center gap-1" title="Karyawan">
                                    <Users class="w-3.5 h-3.5 text-slate-400" />
                                    {{ outlet.users_count || 0 }}
                                </span>
                                <span class="inline-flex items-center gap-1" title="Menu Aktif">
                                    <UtensilsCrossed class="w-3.5 h-3.5 text-slate-400" />
                                    {{ outlet.menus_count || 0 }}
                                </span>
                                <span class="inline-flex items-center gap-1" title="Meja">
                                    <LayoutGrid class="w-3.5 h-3.5 text-slate-400" />
                                    {{ outlet.tables_count || 0 }}
                                </span>
                            </div>
                        </td>

                        <td class="py-3.5 px-4 whitespace-nowrap text-center">
                            <OutletStatusBadge :status="outlet.status" />
                        </td>

                        <td class="py-3.5 px-4 whitespace-nowrap text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <button
                                    @click="emit('view', outlet)"
                                    class="p-1.5 rounded-lg bg-sky-50 text-sky-700 hover:bg-sky-100 border border-sky-200 transition"
                                    title="Rincian Cabang"
                                >
                                    <Eye class="w-4 h-4" />
                                </button>
                                <button
                                    @click="emit('edit', outlet)"
                                    class="p-1.5 rounded-lg bg-amber-50 text-amber-700 hover:bg-amber-100 border border-amber-200 transition"
                                    title="Edit Cabang"
                                >
                                    <Edit class="w-4 h-4" />
                                </button>
                                <button
                                    @click="emit('delete', outlet)"
                                    class="p-1.5 rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 transition"
                                    title="Hapus Cabang"
                                >
                                    <Trash2 class="w-4 h-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

