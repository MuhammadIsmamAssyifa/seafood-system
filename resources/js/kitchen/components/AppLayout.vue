<template>
    <div class="h-screen w-screen flex flex-col bg-slate-950 text-slate-100 overflow-hidden font-sans">
        <!-- Top Navigation / Header -->
        <Header />

        <!-- Main Body: Full width responsive container without visible scrollbar -->
        <main class="flex-1 overflow-y-auto no-scrollbar bg-slate-950 p-3 sm:p-5 lg:p-6">
            <slot />
        </main>

        <!-- Global Toast Alerts -->
        <BaseToast />
    </div>
</template>

<script setup>
import Header from './Header.vue';
import BaseToast from '@/shared/components/BaseToast.vue';
</script>
