<template>
    <div
        class="min-h-screen w-full bg-slate-950 flex flex-col justify-center items-center p-4 sm:p-6 select-none relative overflow-hidden"
    >
        <!-- Background Ambient Glow -->
        <div
            class="absolute -top-32 -left-32 w-96 h-96 bg-blue-600/10 rounded-full blur-3xl pointer-events-none"
        ></div>
        <div
            class="absolute -bottom-32 -right-32 w-96 h-96 bg-indigo-600/10 rounded-full blur-3xl pointer-events-none"
        ></div>

        <!-- Login Container Card -->
        <div
            class="w-full max-w-md bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-2xl relative z-10"
        >
            <!-- Brand & Heading -->
            <div class="text-center mb-6">
                <div
                    class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-500 shadow-xl shadow-blue-500/25 mb-3"
                >
                    <svg
                        class="w-8 h-8 text-white"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"
                        />
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-white tracking-wide">
                    Kitchen Display System
                </h2>
                <p class="text-xs text-slate-400 mt-1">
                    Masuk dengan akun Staff Kitchen
                </p>
            </div>

            <!-- Login Mode Switcher -->
            <div
                class="flex bg-slate-950/80 p-1 rounded-xl mb-6 border border-slate-800"
            >
                <button
                    type="button"
                    :class="[
                        'flex-1 py-2 text-xs font-semibold rounded-lg transition-all',
                        mode === 'password'
                            ? 'bg-blue-600 text-white shadow'
                            : 'text-slate-400 hover:text-white',
                    ]"
                    @click="setMode('password')"
                >
                    Email / Password
                </button>
                <button
                    type="button"
                    :class="[
                        'flex-1 py-2 text-xs font-semibold rounded-lg transition-all',
                        mode === 'pin'
                            ? 'bg-blue-600 text-white shadow'
                            : 'text-slate-400 hover:text-white',
                    ]"
                    @click="setMode('pin')"
                >
                    PIN Staff Dapur
                </button>
            </div>

            <!-- Error Banner -->
            <div
                v-if="errorMessage"
                class="mb-5 p-3.5 bg-rose-950/70 border border-rose-800 rounded-xl text-rose-300 text-xs flex items-start gap-2.5 animate-shake"
            >
                <svg
                    class="w-4 h-4 text-rose-400 shrink-0 mt-0.5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                </svg>
                <span>{{ errorMessage }}</span>
            </div>

            <!-- Mode 1: Email / Password Login -->
            <form
                v-if="mode === 'password'"
                @submit.prevent="handlePasswordSubmit"
                class="space-y-4"
            >
                <div>
                    <label
                        class="block text-xs font-semibold text-slate-300 mb-1.5"
                    >
                        Email atau Username <span class="text-rose-400">*</span>
                    </label>
                    <input
                        v-model="form.loginId"
                        type="text"
                        class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50"
                        :disabled="authStore.loading"
                        required
                        autocomplete="username"
                    />
                </div>

                <div>
                    <label
                        class="block text-xs font-semibold text-slate-300 mb-1.5"
                    >
                        Password <span class="text-rose-400">*</span>
                    </label>
                    <input
                        v-model="form.password"
                        type="password"
                        class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50"
                        :disabled="authStore.loading"
                        required
                        autocomplete="current-password"
                    />
                </div>

                <div class="pt-2">
                    <BaseButton
                        type="submit"
                        variant="primary"
                        size="lg"
                        custom-class="w-full text-base font-bold shadow-lg shadow-blue-600/30"
                        :loading="authStore.loading"
                        :disabled="
                            authStore.loading || !form.loginId || !form.password
                        "
                    >
                        <template v-if="authStore.loading">Loading...</template>
                        <template v-else>Masuk</template>
                    </BaseButton>
                </div>
            </form>

            <!-- Mode 2: Staff PIN Login -->
            <form v-else @submit.prevent="handlePinSubmit" class="space-y-4">
                <div>
                    <label
                        class="block text-xs font-semibold text-slate-300 mb-1.5"
                    >
                        Username Staff <span class="text-rose-400">*</span>
                    </label>
                    <input
                        v-model="form.username"
                        type="text"
                        class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50"
                        :disabled="authStore.loading"
                        required
                        autocomplete="username"
                    />
                </div>

                <div>
                    <label
                        class="block text-xs font-semibold text-slate-300 mb-1.5"
                    >
                        PIN Dapur (6 Digit) <span class="text-rose-400">*</span>
                    </label>
                    <div class="flex justify-center gap-2 mb-3">
                        <div
                            v-for="i in 6"
                            :key="i"
                            :class="[
                                'w-4 h-4 rounded-full border transition-all duration-150',
                                form.pin.length >= i
                                    ? 'bg-blue-500 border-blue-400 scale-110 shadow-md shadow-blue-500/50'
                                    : 'bg-slate-800 border-slate-700',
                            ]"
                        ></div>
                    </div>
                </div>

                <!-- On-Screen Numpad for Touch Display -->
                <div class="grid grid-cols-3 gap-2 pt-1">
                    <button
                        v-for="num in [
                            '1',
                            '2',
                            '3',
                            '4',
                            '5',
                            '6',
                            '7',
                            '8',
                            '9',
                        ]"
                        :key="num"
                        type="button"
                        :disabled="authStore.loading"
                        class="h-12 rounded-xl bg-slate-800 hover:bg-slate-700 active:scale-95 text-lg font-bold text-white border border-slate-700/60 shadow-sm transition-all disabled:opacity-50"
                        @click="appendPin(num)"
                    >
                        {{ num }}
                    </button>
                    <button
                        type="button"
                        :disabled="authStore.loading"
                        class="h-12 rounded-xl bg-slate-800/60 hover:bg-slate-800 active:scale-95 text-xs font-semibold text-slate-400 border border-slate-700/40 transition-all disabled:opacity-50"
                        @click="clearPin"
                    >
                        CLEAR
                    </button>
                    <button
                        type="button"
                        :disabled="authStore.loading"
                        class="h-12 rounded-xl bg-slate-800 hover:bg-slate-700 active:scale-95 text-lg font-bold text-white border border-slate-700/60 shadow-sm transition-all disabled:opacity-50"
                        @click="appendPin('0')"
                    >
                        0
                    </button>
                    <button
                        type="button"
                        :disabled="authStore.loading"
                        class="h-12 rounded-xl bg-slate-800/60 hover:bg-slate-800 active:scale-95 flex items-center justify-center text-slate-400 border border-slate-700/40 transition-all disabled:opacity-50"
                        @click="backspacePin"
                    >
                        <svg
                            class="w-5 h-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M3 12l6.414-6.414A2 2 0 0110.828 5H20a2 2 0 012 2v10a2 2 0 01-2 2h-9.172a2 2 0 01-1.414-.586L3 12z"
                            />
                        </svg>
                    </button>
                </div>

                <div class="pt-2">
                    <BaseButton
                        type="submit"
                        variant="primary"
                        size="lg"
                        custom-class="w-full text-base font-bold shadow-lg shadow-blue-600/30"
                        :loading="authStore.loading"
                        :disabled="
                            authStore.loading ||
                            !form.username ||
                            form.pin.length < 4
                        "
                    >
                        <template v-if="authStore.loading">Loading...</template>
                        <template v-else>Masuk</template>
                    </BaseButton>
                </div>
            </form>

            <!-- Card Footer -->
            <div class="mt-6 text-center text-xs text-slate-500">
                Seafood System &bull; Kitchen Display System
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, onMounted } from "vue";
import { useRouter, useRoute } from "vue-router";
import { useAuthStore } from "@/kitchen/stores/auth.store";
import BaseButton from "@/shared/components/BaseButton.vue";

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();

const mode = ref("password");
const errorMessage = ref("");

const form = reactive({
    loginId: "",
    password: "",
    username: "",
    pin: "",
});

const setMode = (newMode) => {
    mode.value = newMode;
    errorMessage.value = "";
};

const appendPin = (digit) => {
    if (form.pin.length < 6) {
        form.pin += digit;
    }
    if (form.pin.length === 6 && form.username) {
        handlePinSubmit();
    }
};

const clearPin = () => {
    form.pin = "";
};

const backspacePin = () => {
    form.pin = form.pin.slice(0, -1);
};

const redirectSuccess = () => {
    const redirect = route.query.redirect || "/kitchen/dashboard";
    router.push(redirect);
};

const handlePasswordSubmit = async () => {
    errorMessage.value = "";

    // Validation
    if (!form.loginId.trim()) {
        errorMessage.value = "Email atau username tidak boleh kosong.";
        return;
    }
    if (!form.password) {
        errorMessage.value = "Password tidak boleh kosong.";
        return;
    }

    const isEmail = form.loginId.includes("@");
    const credentials = {
        password: form.password,
        ...(isEmail
            ? { email: form.loginId.trim() }
            : { username: form.loginId.trim() }),
    };

    const res = await authStore.login(credentials);
    if (res.success) {
        redirectSuccess();
    } else {
        errorMessage.value = res.message;
    }
};

const handlePinSubmit = async () => {
    errorMessage.value = "";

    // Validation
    if (!form.username.trim()) {
        errorMessage.value = "Username staff tidak boleh kosong.";
        return;
    }
    if (!form.pin || form.pin.length < 4) {
        errorMessage.value = "PIN minimal 4 digit.";
        return;
    }

    const res = await authStore.loginWithPin({
        username: form.username.trim(),
        pin: form.pin,
    });

    if (res.success) {
        redirectSuccess();
    } else {
        errorMessage.value = res.message;
        form.pin = "";
    }
};

onMounted(() => {
    if (route.query.error === "unauthorized") {
        errorMessage.value =
            "Sesi Anda telah berakhir atau Anda tidak memiliki akses ke KDS.";
    }
});
</script>
