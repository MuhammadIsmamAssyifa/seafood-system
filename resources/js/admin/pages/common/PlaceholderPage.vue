<script setup>
import { useRoute, useRouter } from 'vue-router';
import { Construction, ArrowLeft } from 'lucide-vue-next';
import BaseCard from '../../components/common/BaseCard.vue';
import BaseButton from '../../components/common/BaseButton.vue';

const route = useRoute();
const router = useRouter();

const sprintTitle = route.meta?.title || 'Fitur';
const sprintNumber = route.meta?.sprint || 'Sprint Berikutnya';
</script>

<template>
    <div class="max-w-xl mx-auto py-12">
        <BaseCard class="text-center p-8">
            <div class="w-16 h-16 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mx-auto mb-4 border border-blue-100">
                <Construction class="w-8 h-8" />
            </div>

            <span class="inline-block px-3 py-1 rounded-full text-xs font-mono font-bold bg-blue-100 text-blue-800 mb-2">
                {{ sprintNumber }}
            </span>

            <h2 class="text-xl font-bold text-slate-800">
                Modul {{ sprintTitle }}
            </h2>

            <p class="text-sm text-slate-500 mt-2 leading-relaxed">
                Modul ini dijadwalkan dan akan diimplementasikan pada {{ sprintNumber }} sesuai dokumen rencana sprint. Fondasi arsitektur dan dashboard overview telah aktif dan siap digunakan.
            </p>

            <div class="mt-6 flex justify-center">
                <BaseButton variant="primary" size="md" @click="router.push('/dashboard')">
                    <ArrowLeft class="w-4 h-4" />
                    <span>Kembali ke Dashboard</span>
                </BaseButton>
            </div>
        </BaseCard>
    </div>
</template>

