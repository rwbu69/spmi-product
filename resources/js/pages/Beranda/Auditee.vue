<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Building2, TrendingUp, Award, Filter } from 'lucide-vue-next';
import { ref, watch, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';

interface ChartItem { standar: string; nilai: number; }

interface PageProps {
    stats: { target_nilai: number; nilai_evaluasi_diri: number; nilai_hasil_audit: number; };
    chartData: ChartItem[];
    lembagaList: { id: number; nama_lembaga: string }[];
    tahunList: { id: number; tahun: number; status: string }[];
    standarList: { id: number; kode: string; nama_standar: string }[];
    filters: { lembaga_id: number | null; tahun_id: number | null; };
}

defineOptions({ layout: AppLayout });
const props = defineProps<PageProps>();

const filters = ref({ ...props.filters });

const applyFilters = () => {
    router.get('/dashboard', {
        lembaga_id: filters.value.lembaga_id || undefined,
        tahun_id: filters.value.tahun_id || undefined,
    }, { preserveState: true, replace: true });
};

watch(filters, applyFilters, { deep: true });

const maxNilai = computed(() => Math.max(...props.chartData.map(d => d.nilai), 100));

const statCards = [
    {
        label: 'Target Nilai Mutu',
        key: 'target_nilai' as const,
        icon: Award,
        bg: 'bg-blue-600',
        iconBg: 'bg-blue-700/50',
    },
    {
        label: 'Nilai Evaluasi Diri',
        key: 'nilai_evaluasi_diri' as const,
        icon: TrendingUp,
        bg: 'bg-amber-500',
        iconBg: 'bg-amber-600/50',
    },
    {
        label: 'Nilai Hasil Audit',
        key: 'nilai_hasil_audit' as const,
        icon: Building2,
        bg: 'bg-green-600',
        iconBg: 'bg-green-700/50',
    },
];
</script>

<template>
    <Head title="Beranda" />
    <div class="space-y-6 p-6">

        <!-- Stat Cards -->
        <div class="grid gap-6 md:grid-cols-3">
            <div
                v-for="card in statCards"
                :key="card.key"
                :class="card.bg"
                class="relative overflow-hidden rounded-2xl p-6 text-white shadow-lg"
            >
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-white/80">{{ card.label }}</p>
                        <p class="mt-3 text-4xl font-black tracking-tight">
                            {{ stats[card.key].toFixed(2) }}
                        </p>
                    </div>
                    <div :class="card.iconBg" class="flex size-12 items-center justify-center rounded-xl">
                        <component :is="card.icon" class="size-6 text-white/80" />
                    </div>
                </div>
                <!-- Decorative circle -->
                <div class="absolute -bottom-4 -right-4 size-24 rounded-full bg-white/10"></div>
            </div>
        </div>

        <!-- Filters -->
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="flex items-center gap-1.5 text-sm font-medium text-gray-700">
                        <Filter class="size-3.5 text-gray-400" /> Filter Lembaga Akreditasi
                    </label>
                    <select v-model="filters.lembaga_id" class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                        <option :value="null">Semua Lembaga</option>
                        <option v-for="l in lembagaList" :key="l.id" :value="l.id">{{ l.nama_lembaga }}</option>
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label class="flex items-center gap-1.5 text-sm font-medium text-gray-700">
                        <Filter class="size-3.5 text-gray-400" /> Filter Tahun
                    </label>
                    <select v-model="filters.tahun_id" class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                        <option :value="null">Semua Tahun</option>
                        <option v-for="t in tahunList" :key="t.id" :value="t.id">{{ t.tahun }}</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Chart: Perkembangan Nilai Mutu per Standar -->
        <div class="rounded-2xl border border-gray-100 bg-white shadow-sm overflow-hidden">
            <div class="border-b border-gray-50 bg-gray-50/30 p-6">
                <h2 class="text-lg font-semibold text-gray-900">Persentase Rata Nilai</h2>
                <p class="text-sm text-gray-500 mt-1">Berdasarkan Standar Mutu</p>
            </div>

            <div class="p-6">
                <div v-if="chartData.length === 0" class="py-12 text-center">
                    <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-gray-50 mb-3">
                        <Filter class="size-6 text-gray-400" />
                    </div>
                    <p class="text-sm font-medium text-gray-900">Tidak ada data</p>
                    <p class="text-sm text-gray-500">Silakan sesuaikan filter Anda</p>
                </div>

                <div v-else class="space-y-4">
                    <div v-for="(item, index) in chartData" :key="index" class="flex items-center gap-4">
                        <div class="w-8 flex-shrink-0 text-sm font-medium text-gray-500">#{{ index + 1 }}</div>
                        <div class="flex-1 space-y-1">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-900 truncate max-w-xs">{{ item.standar }}</span>
                                <span class="text-sm font-medium text-gray-600 ml-4">{{ item.nilai.toFixed(1) }}</span>
                            </div>
                            <div class="h-2 w-full overflow-hidden rounded-full bg-gray-100">
                                <div
                                    class="h-full rounded-full bg-blue-600 transition-all duration-1000 ease-out"
                                    :style="{ width: `${Math.min((item.nilai / maxNilai) * 100, 100)}%` }"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Standar Mutu Table -->
        <div class="rounded-2xl border border-gray-100 bg-white shadow-sm overflow-hidden">
            <div class="border-b border-gray-50 bg-gray-50/30 p-6">
                <h2 class="text-lg font-semibold text-gray-900">Daftar Standar Mutu</h2>
                <p class="text-sm text-gray-500 mt-1">Standar mutu yang berlaku pada periode ini</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-blue-50 text-blue-700 border-b border-blue-100">
                        <tr>
                            <th class="px-4 py-3 font-medium">No</th>
                            <th class="px-4 py-3 font-medium">Kode</th>
                            <th class="px-4 py-3 font-medium">Nama Standar Mutu</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-if="standarList.length === 0">
                            <td colspan="3" class="px-4 py-10 text-center text-gray-400 italic">Belum ada standar mutu.</td>
                        </tr>
                        <tr
                            v-for="(standar, idx) in standarList"
                            :key="standar.id"
                            class="hover:bg-gray-50 transition"
                        >
                            <td class="px-4 py-3 text-gray-500">{{ idx + 1 }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-0.5 text-xs font-bold text-blue-700">
                                    {{ standar.kode }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-medium text-gray-900">{{ standar.nama_standar }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</template>
