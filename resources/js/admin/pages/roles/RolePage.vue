<script setup>
import { onMounted, ref } from 'vue';
import { useRoleStore } from '../../stores/role.store';
import RoleTable from './components/RoleTable.vue';
import PermissionMatrix from './components/PermissionMatrix.vue';
import RoleFormModal from './components/RoleFormModal.vue';
import { ShieldCheck, Plus, Shield, Grid, List } from 'lucide-vue-next';

const roleStore = useRoleStore();

const activeTab = ref('roles'); // 'roles' | 'matrix'
const showModal = ref(false);
const editingRole = ref(null);
const toastMessage = ref('');

onMounted(async () => {
    await Promise.all([
        roleStore.fetchRoles(),
        roleStore.fetchPermissions(),
        roleStore.fetchMatrix(),
    ]);
});

function handleOpenCreate() {
    editingRole.value = null;
    showModal.value = true;
}

function handleOpenEdit(role) {
    editingRole.value = role;
    showModal.value = true;
}

async function handleSave({ payload, id }) {
    try {
        if (id) {
            await roleStore.updateRole(id, payload);
            showToast('Hak akses role berhasil diperbarui.');
        } else {
            await roleStore.createRole(payload);
            showToast('Role baru berhasil dibuat.');
        }
        showModal.value = false;
    } catch (err) {
        console.error('Error saving role:', err);
    }
}

function showToast(msg) {
    toastMessage.value = msg;
    setTimeout(() => {
        toastMessage.value = '';
    }, 3500);
}
</script>

<template>
    <div class="space-y-6">
        <!-- Toast Feedback -->
        <div
            v-if="toastMessage"
            class="fixed top-5 right-5 z-50 bg-indigo-600 text-white px-4 py-3 rounded-xl shadow-lg flex items-center gap-3 text-xs font-medium animate-bounce"
        >
            <span>{{ toastMessage }}</span>
        </div>

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-bold text-slate-900 tracking-tight">Authorization & Permissions</h1>
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-indigo-100 text-indigo-700">
                        Security & RBAC
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-1">
                    Atur wewenang pengguna berdasarkan role jabatan dan batasi menu serta aksi operasional sistem.
                </p>
            </div>

            <div class="flex items-center gap-2.5">
                <button
                    @click="handleOpenCreate"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl shadow-xs transition-colors"
                >
                    <Plus class="w-4 h-4" />
                    <span>Buat Role Baru</span>
                </button>
            </div>
        </div>

        <!-- Tab Controls -->
        <div class="flex items-center gap-2 border-b border-slate-200 pb-3">
            <button
                @click="activeTab = 'roles'"
                :class="[
                    'flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold transition-colors',
                    activeTab === 'roles'
                        ? 'bg-indigo-600 text-white shadow-xs'
                        : 'text-slate-600 hover:bg-slate-100',
                ]"
            >
                <List class="w-4 h-4" />
                <span>Daftar Role Jabatan</span>
            </button>

            <button
                @click="activeTab = 'matrix'"
                :class="[
                    'flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold transition-colors',
                    activeTab === 'matrix'
                        ? 'bg-indigo-600 text-white shadow-xs'
                        : 'text-slate-600 hover:bg-slate-100',
                ]"
            >
                <Grid class="w-4 h-4" />
                <span>Matriks Hak Akses (Matrix)</span>
            </button>
        </div>

        <!-- Tab 1: Roles Table -->
        <div v-if="activeTab === 'roles'" class="space-y-4">
            <RoleTable
                :roles="roleStore.roles"
                :loading="roleStore.loading"
                @edit-role="handleOpenEdit"
            />
        </div>

        <!-- Tab 2: Permission Matrix -->
        <div v-else class="space-y-4">
            <PermissionMatrix
                :roles="roleStore.matrixRoles"
                :matrix="roleStore.matrix"
                :loading="roleStore.matrixLoading"
            />
        </div>

        <!-- Form Modal -->
        <RoleFormModal
            v-model="showModal"
            :role="editingRole"
            :grouped-permissions="roleStore.groupedPermissions"
            @saved="handleSave"
        />
    </div>
</template>

