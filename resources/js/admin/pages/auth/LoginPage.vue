<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { UtensilsCrossed, Lock, User, Eye, EyeOff, AlertCircle } from 'lucide-vue-next';
import { useAuthStore } from '../../stores/auth.store';
import { useNotificationStore } from '../../stores/notification.store';
import BaseInput from '../../components/common/BaseInput.vue';
import BaseButton from '../../components/common/BaseButton.vue';

const router = useRouter();
const authStore = useAuthStore();
const notificationStore = useNotificationStore();

const form = reactive({
    username: '',
    password: '',
    remember: true,
});

const errors = reactive({
    username: '',
    password: '',
    general: '',
});

const showPassword = ref(false);

onMounted(() => {
    // Check if session expired flash message exists
    const flashError = sessionStorage.getItem('flash_error');
    if (flashError) {
        errors.general = flashError;
        sessionStorage.removeItem('flash_error');
    }
});

function validate() {
    let isValid = true;
    errors.username = '';
    errors.password = '';
    errors.general = '';

    if (!form.username.trim()) {
        errors.username = 'Username atau Email wajib diisi.';
        isValid = false;
    }

    if (!form.password) {
        errors.password = 'Password wajib diisi.';
        isValid = false;
    }

    return isValid;
}

async function handleLogin() {
    if (!validate()) return;

    try {
        await authStore.login(
            {
                username: form.username.trim(),
                email: form.username.trim(),
                password: form.password,
            },
            form.remember
        );

        notificationStore.success(`Selamat datang, ${authStore.userName}!`);
        router.push('/dashboard');
    } catch (err) {
        if (err.response?.status === 401) {
            errors.general = 'Username / Email atau Password salah.';
        } else if (err.response?.status === 422) {
            const valErrors = err.response.data?.errors;
            if (valErrors?.username || valErrors?.email) {
                errors.username = (valErrors.username || valErrors.email)[0];
            }
            if (valErrors?.password) {
                errors.password = valErrors.password[0];
            }
            errors.general = err.response.data?.message || 'Validasi gagal.';
        } else {
            errors.general = err.customMessage || 'Terjadi kesalahan pada server. Coba beberapa saat lagi.';
        }
    }
}

function fillDemo(type = 'owner') {
    if (type === 'owner') {
        form.username = 'owner@seafood.com';
        form.password = 'password';
    }
}
</script>

<template>
    <div class="px-4">
        <!-- Brand Header -->
        <div class="text-center mb-8">
            <div class="inline-flex w-14 h-14 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-500 items-center justify-center text-white shadow-xl shadow-blue-500/20 mb-4">
                <UtensilsCrossed class="w-8 h-8" />
            </div>
            <h2 class="text-2xl font-bold tracking-tight text-white">
                Admin & Owner Portal
            </h2>
            <p class="text-xs text-slate-400 mt-1">
                Sistem Manajemen & Point of Sale Restoran Seafood
            </p>
        </div>

        <!-- Login Card -->
        <div class="bg-white/95 backdrop-blur-md py-8 px-6 sm:px-10 shadow-2xl rounded-2xl border border-slate-100">
            <!-- General Error Alert -->
            <div
                v-if="errors.general"
                class="mb-6 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-start gap-2.5"
            >
                <AlertCircle class="w-4 h-4 text-rose-600 shrink-0 mt-0.5" />
                <span class="font-medium leading-relaxed">{{ errors.general }}</span>
            </div>

            <form @submit.prevent="handleLogin" class="space-y-5">
                <!-- Username / Email -->
                <BaseInput
                    v-model="form.username"
                    label="Username atau Email"
                    placeholder="nama@email.com atau username"
                    :error="errors.username"
                    required
                    autocomplete="username"
                >
                    <template #prefix>
                        <User class="w-4 h-4" />
                    </template>
                </BaseInput>

                <!-- Password -->
                <div>
                    <BaseInput
                        v-model="form.password"
                        :type="showPassword ? 'text' : 'password'"
                        label="Password"
                        placeholder="••••••••"
                        :error="errors.password"
                        required
                        autocomplete="current-password"
                    >
                        <template #prefix>
                            <Lock class="w-4 h-4" />
                        </template>
                        <template #suffix>
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="cursor-pointer hover:text-slate-600 transition-colors"
                            >
                                <EyeOff v-if="showPassword" class="w-4 h-4" />
                                <Eye v-else class="w-4 h-4" />
                            </button>
                        </template>
                    </BaseInput>
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between text-xs pt-1">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input
                            type="checkbox"
                            v-model="form.remember"
                            class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-slate-300"
                        />
                        <span class="text-slate-600 font-medium">Ingat saya</span>
                    </label>

                    <a
                        href="/kitchen"
                        class="text-blue-600 hover:text-blue-700 font-medium hover:underline"
                    >
                        Buka Kitchen KDS &rarr;
                    </a>
                </div>

                <!-- Submit Button -->
                <BaseButton
                    type="submit"
                    variant="primary"
                    size="lg"
                    block
                    :loading="authStore.loading"
                    class="mt-2 font-semibold shadow-lg shadow-blue-500/25"
                >
                    Masuk ke Dashboard
                </BaseButton>
            </form>

            <!-- Quick Demo Credentials Hint -->
            <div class="mt-6 pt-5 border-t border-slate-100">
                <div class="flex items-center justify-between text-xs text-slate-500 mb-2">
                    <span>Akun Demo Default:</span>
                    <button
                        type="button"
                        @click="fillDemo('owner')"
                        class="text-blue-600 hover:text-blue-700 font-medium cursor-pointer"
                    >
                        Isi Otomatis
                    </button>
                </div>
                <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-200/80 font-mono text-[11px] text-slate-700 flex justify-between">
                    <span>owner@seafood.com</span>
                    <span class="text-slate-400">password</span>
                </div>
            </div>
        </div>
    </div>
</template>

