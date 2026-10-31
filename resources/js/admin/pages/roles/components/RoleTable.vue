<script setup>
import { Shield, Users, CheckCircle, Edit3, Lock } from 'lucide-vue-next';

const props = defineProps({
    roles: {
        type: Array,
        default: () => [],
    },
    loading: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['edit-role']);
</script>

<template>
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-500 font-semibold uppercase tracking-wider">
                        <th class="py-3 px-4">Nama Role</th>
                        <th class="py-3 px-4">Identifikasi Sistem</th>
                        <th class="py-3 px-4 text-center">Jumlah Hak Akses</th>
                        <th class="py-3 px-4 text-center">Pengguna Terkait</th>
                        <th class="py-3 px-4">Tipe Role</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    <tr v-if="loading" v-for="i in 4" :key="i">
                        <td class="py-3.5 px-4"><div class="h-4 w-28 bg-slate-100 animate-pulse rounded"></div></td>
                        <td class="py-3.5 px-4"><div class="h-4 w-20 bg-slate-100 animate-pulse rounded"></div></td>
                        <td class="py-3.5 px-4 text-center"><div class="h-4 w-12 bg-slate-100 animate-pulse rounded mx-auto"></div></td>
                        <td class="py-3.5 px-4 text-center"><div class="h-4 w-12 bg-slate-100 animate-pulse rounded mx-auto"></div></td>
                        <td class="py-3.5 px-4"><div class="h-4 w-16 bg-slate-100 animate-pulse rounded"></div></td>
                        <td class="py-3.5 px-4 text-center"><div class="h-4 w-16 bg-slate-100 animate-pulse rounded mx-auto"></div></td>
                    </tr>

                    <tr
                        v-else
                        v-for="role in roles"
                        :key="role.id"
                        class="hover:bg-slate-50/60 transition-colors"
                    >
                        <!-- Role Name -->
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-slate-900 flex items-center gap-2">
                                <Shield class="w-4 h-4 text-indigo-600" />
                                <span>{{ role.label }}</span>
                            </div>
                        </td>

                        <!-- Slug / System Name -->
                        <td class="py-3.5 px-4 font-mono text-[11px] text-slate-500">
                            {{ role.name }}
                        </td>

                        <!-- Permission Count -->
                        <td class="py-3.5 px-4 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                {{ role.name === 'owner' ? 'Semua (Full)' : `${role.permission_count} hak akses` }}
                            </span>
                        </td>

                        <!-- User Count -->
                        <td class="py-3.5 px-4 text-center font-semibold text-slate-900">
                            {{ role.user_count }} staf
                        </td>

                        <!-- System / Custom Type -->
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <span
                                v-if="role.is_system"
                                class="inline-flex items-center gap-1 text-[11px] font-medium text-slate-600 bg-slate-100 px-2 py-0.5 rounded"
                            >
                                <Lock class="w-3 h-3 text-slate-400" />
                                <span>Bawaan Sistem</span>
                            </span>
                            <span
                                v-else
                                class="inline-flex items-center text-[11px] font-medium text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded"
                            >
                                Custom
                            </span>
                        </td>

                        <!-- Actions -->
                        <td class="py-3.5 px-4 text-center whitespace-nowrap">
                            <button
                                v-if="role.name !== 'owner'"
                                @click="emit('edit-role', role)"
                                class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium text-indigo-700 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 rounded-lg transition-colors"
                            >
                                <Edit3 class="w-3.5 h-3.5" />
                                <span>Atur Akses</span>
                            </button>
                            <span v-else class="text-xs text-slate-400 italic">Akses Permanen</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

