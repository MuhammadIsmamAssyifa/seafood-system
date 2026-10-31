<script setup>
import BaseToast from '../components/feedback/BaseToast.vue';
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-slate-800 to-slate-950 flex flex-col justify-center py-12 sm:px-6 lg:px-8 selection:bg-blue-600 selection:text-white">
        <BaseToast />

        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <slot />
        </div>
    </div>
</template>

