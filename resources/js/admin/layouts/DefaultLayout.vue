<script setup>
import { ref } from 'vue';
import Sidebar from '../components/layout/Sidebar.vue';
import Topbar from '../components/layout/Topbar.vue';
import BaseToast from '../components/feedback/BaseToast.vue';

const isSidebarOpen = ref(false);

function toggleSidebar() {
    isSidebarOpen.value = !isSidebarOpen.value;
}

function closeSidebar() {
    isSidebarOpen.value = false;
}
</script>

<template>
    <div class="min-h-screen bg-slate-50 flex">
        <!-- Toast Stack -->
        <BaseToast />

        <!-- Sidebar -->
        <Sidebar :is-open="isSidebarOpen" @close="closeSidebar" />

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 lg:pl-64">
            <Topbar @toggle-sidebar="toggleSidebar" />

            <main class="flex-1 p-4 sm:p-6 lg:p-8 overflow-y-auto">
                <slot>
                    <router-view />
                </slot>
            </main>
        </div>
    </div>
</template>

