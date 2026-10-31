<script setup>
import { ref } from 'vue';
import { Calendar, Filter } from 'lucide-vue-next';
import { useReportStore } from '../../../stores/report.store';
import BaseButton from '../../../components/common/BaseButton.vue';

const reportStore = useReportStore();

const presets = [
    { key: 'today', label: 'Hari Ini' },
    { key: 'yesterday', label: 'Kemarin' },
    { key: 'this_week', label: 'Minggu Ini' },
    { key: 'this_month', label: 'Bulan Ini' },
    { key: 'last_month', label: 'Bulan Lalu' },
    { key: 'custom', label: 'Kustom' },
];

const customStart = ref(reportStore.startDate);
const customEnd = ref(reportStore.endDate);

function handlePresetClick(key) {
    if (key === 'custom') {
        reportStore.selectedPreset = 'custom';
    } else {
        reportStore.setPreset(key);
    }
}

function applyCustom() {
    reportStore.setCustomRange(customStart.value, customEnd.value);
}
</script>

<template>
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <!-- Preset Pills -->
            <div class="flex items-center gap-1.5 flex-wrap">
                <span class="text-xs font-semibold text-slate-500 mr-1 flex items-center gap-1">
                    <Filter class="w-3.5 h-3.5" />
                    Periode:
                </span>

                <button
                    v-for="p in presets"
                    :key="p.key"
                    type="button"
                    @click="handlePresetClick(p.key)"
                    class="px-3 py-1.5 rounded-lg text-xs font-medium transition-all cursor-pointer"
                    :class="[
                        reportStore.selectedPreset === p.key
                            ? 'bg-blue-600 text-white shadow-xs'
                            : 'bg-slate-100 text-slate-600 hover:bg-slate-200 hover:text-slate-900',
                    ]"
                >
                    {{ p.label }}
                </button>
            </div>

            <!-- Date Range Inputs -->
            <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap">
                <div class="flex items-center gap-1.5 bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5">
                    <Calendar class="w-3.5 h-3.5 text-slate-400 shrink-0" />
                    <input
                        type="date"
                        v-model="customStart"
                        class="bg-transparent text-xs text-slate-700 font-medium focus:outline-none cursor-pointer"
                    />
                    <span class="text-xs text-slate-400 font-medium">s/d</span>
                    <input
                        type="date"
                        v-model="customEnd"
                        class="bg-transparent text-xs text-slate-700 font-medium focus:outline-none cursor-pointer"
                    />
                </div>

                <BaseButton
                    v-if="reportStore.selectedPreset === 'custom' || customStart !== reportStore.startDate || customEnd !== reportStore.endDate"
                    variant="primary"
                    size="sm"
                    @click="applyCustom"
                    :loading="reportStore.loading"
                >
                    Terapkan
                </BaseButton>
            </div>
        </div>
    </div>
</template>

