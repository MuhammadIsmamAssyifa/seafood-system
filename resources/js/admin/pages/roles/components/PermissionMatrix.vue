<script setup>
import { ref, computed } from 'vue';
import { Check, Minus, Search, ShieldCheck } from 'lucide-vue-next';

const props = defineProps({
    roles: {
        type: Array,
        default: () => [],
    },
    matrix: {
        type: Array,
        default: () => [],
    },
    loading: {
        type: Boolean,
        default: false,
    },
});

const searchQuery = ref('');

const filteredMatrix = computed(() => {
    if (!searchQuery.value?.trim()) return props.matrix;
    const q = searchQuery.value.toLowerCase();
    return props.matrix.filter(
        (m) => m.permission.toLowerCase().includes(q) || m.label?.toLowerCase().includes(q)
    );
});
</script>

<template>
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-xs space-y-4 p-5">
        <!-- Header & Search -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
            <div>
                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <ShieldCheck class="w-4 h-4 text-indigo-600" />
                    <span>Matriks Hak Akses (Permission Matrix)</span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Pemetaan komprehensif seluruh izin akses sistem terhadap setiap role yang aktif
                </p>
            </div>

            <div class="relative w-64">
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Filter izin / nama hak akses..."
                    class="w-full text-xs pl-8 pr-3 py-1.5 border border-slate-200 rounded-xl focus:border-indigo-500 focus:ring-indigo-500"
                />
                <Search class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2 pointer-events-none" />
            </div>
        </div>

        <!-- Matrix Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-700 font-semibold">
                        <th class="py-3 px-4 min-w-[240px]">Hak Akses / Izin Fitur</th>
                        <th class="py-3 px-4 min-w-[180px]">Identifier Teknis</th>
                        <th
                            v-for="role in roles"
                            :key="role.id"
                            class="py-3 px-3 text-center whitespace-nowrap"
                        >
                            <span class="inline-block px-2.5 py-1 rounded-md text-[11px] font-bold bg-slate-100 text-slate-800">
                                {{ role.label }}
                            </span>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    <tr v-if="loading" v-for="i in 6" :key="i">
                        <td class="py-3 px-4"><div class="h-4 w-40 bg-slate-100 animate-pulse rounded"></div></td>
                        <td class="py-3 px-4"><div class="h-4 w-28 bg-slate-100 animate-pulse rounded"></div></td>
                        <td v-for="r in roles" :key="r.id" class="py-3 px-3 text-center">
                            <div class="h-4 w-4 bg-slate-100 animate-pulse rounded mx-auto"></div>
                        </td>
                    </tr>

                    <tr v-else-if="filteredMatrix.length === 0">
                        <td :colspan="2 + roles.length" class="py-8 text-center text-xs text-slate-400">
                            Tidak ada hak akses yang cocok dengan filter.
                        </td>
                    </tr>

                    <tr
                        v-else
                        v-for="row in filteredMatrix"
                        :key="row.permission"
                        class="hover:bg-slate-50/60 transition-colors"
                    >
                        <!-- Permission Label -->
                        <td class="py-2.5 px-4 font-medium text-slate-900">
                            {{ row.label }}
                        </td>

                        <!-- Identifier -->
                        <td class="py-2.5 px-4 font-mono text-[11px] text-slate-400">
                            {{ row.permission }}
                        </td>

                        <!-- Role Checks -->
                        <td
                            v-for="role in roles"
                            :key="role.id"
                            class="py-2.5 px-3 text-center whitespace-nowrap"
                        >
                            <div class="flex items-center justify-center">
                                <span
                                    v-if="row.roles[role.name]"
                                    class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center shadow-2xs"
                                    title="Diizinkan"
                                >
                                    <Check class="w-3.5 h-3.5 stroke-[2.5]" />
                                </span>
                                <span
                                    v-else
                                    class="w-5 h-5 rounded-full bg-slate-100 text-slate-300 flex items-center justify-center"
                                    title="Tidak Diizinkan"
                                >
                                    <Minus class="w-3.5 h-3.5" />
                                </span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

