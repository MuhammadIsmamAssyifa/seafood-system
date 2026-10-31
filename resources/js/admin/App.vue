<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import DefaultLayout from './layouts/DefaultLayout.vue';
import AuthLayout from './layouts/AuthLayout.vue';

const route = useRoute();

const currentLayout = computed(() => {
    if (route.meta?.layout === 'auth') {
        return AuthLayout;
    }
    return DefaultLayout;
});
</script>

<template>
    <component :is="currentLayout">
        <router-view v-slot="{ Component }">
            <Transition
                enter-active-class="transition duration-150 ease-out"
                enter-from-class="opacity-0 translate-y-1"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition duration-100 ease-in"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 translate-y-1"
                mode="out-in"
            >
                <component :is="Component" />
            </Transition>
        </router-view>
    </component>
</template>

