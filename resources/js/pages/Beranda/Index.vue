<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Building2, GraduationCap, Shield, Filter } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';

interface SummaryItem {
    total_auditee: number;
    total_lembaga: number;
    total_standar: number;
}

interface ChartItem {
    auditee: string;
    rata_nilai: number;
}

interface FilterOption {
    id: number;
    tahun?: number;
    nama_lembaga?: string;
    nama_auditee?: string;
}

interface PageProps {
    summary: SummaryItem;
    chartData: ChartItem[];
    tahunList: { id: number; tahun: number; status: string }[];
    lembagaList: { id: number; nama_lembaga: string }[];
    auditeeList: { id: number; nama_auditee: string }[];
    filters: {
        tahun_id: number | null;
        lembaga_id: number | null;
        auditee_id: number | null;
        jenis: string | null;
    };
}

defineOptions({ layout: AppLayout });

const props = defineProps<PageProps>();

const filters = ref({ ...props.filters });

const applyFilters = () => {
    router.get('/dashboard', {
        tahun_id: filters.value.tahun_id || undefined,
        lembaga_id: filters.value.lembaga_id || undefined,
        auditee_id: filters.value.auditee_id || undefined,
        jenis: filters.value.jenis || undefined,
    }, { preserveState: true, replace: true });
};

watch(filters, applyFilters, { deep: true });

const statCards = [
    {
        label: 'Total Auditee',
        key: 'total_auditee',
        icon: Building2,
        color: 'bg-blue-50 text-blue-600',
    },
    {
        label: 'Lembaga Akreditasi',
        key: 'total_lembaga',
        icon: GraduationCap,
        color: 'bg-purple-50 text-purple-600',
    },
    {
        label: 'Standar Mutu',
        key: 'total_standar',
        icon: Shield,
        color: 'text-gray-400',
    },
] as const;

const maxNilai = Math.max(...props.chartData.map((d) => d.rata_nilai), 100);
</script>

<template>
    <Head title="Beranda" />

    <div class="space-y-6 p-6">
        <!-- Header Removed per user request -->
        <!-- Filter Bar -->
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-gray-700">Tahun Periode</label>
                    <select v-model="filters.tahun_id" class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm transition-colors focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                        <option :value="null">Semua Tahun</option>
                        <option v-for="t in tahunList" :key="t.id" :value="t.id">{{ t.tahun }}</option>
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-gray-700">Lembaga Akreditasi</label>
                    <select v-model="filters.lembaga_id" class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm transition-colors focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                        <option :value="null">Semua Lembaga</option>
                        <option v-for="l in lembagaList" :key="l.id" :value="l.id">{{ l.nama_lembaga }}</option>
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-gray-700">Auditee</label>
                    <select v-model="filters.auditee_id" class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm transition-colors focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                        <option :value="null">Semua Auditee</option>
                        <option v-for="a in auditeeList" :key="a.id" :value="a.id">{{ a.nama_auditee }}</option>
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-gray-700">Jenis Evaluasi</label>
                    <select v-model="filters.jenis" class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm transition-colors focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                        <option :value="null">Semua Jenis</option>
                        <option value="Evaluasi Diri">Evaluasi Diri</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Stat Cards (Bento-inspired) -->
        <div class="grid gap-6 md:grid-cols-3">
            <div
                v-for="(card, index) in statCards"
                :key="card.key"
                class="group relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-6 shadow-sm transition-all hover:shadow-md"
            >
                <!-- Subtle background gradient on hover -->
                <div class="absolute inset-0 bg-gradient-to-br opacity-0 transition-opacity duration-300 group-hover:opacity-100" 
                     :class="[
                         index === 0 ? 'from-blue-50/50 to-transparent' : 
                         index === 1 ? 'from-purple-50/50 to-transparent' : 
                         'from-green-50/50 to-transparent'
                     ]">
                </div>
                
                <div class="relative z-10">
                    <div class="flex items-center justify-between">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gray-50 ring-1 ring-gray-100/50 transition-colors group-hover:bg-white group-hover:shadow-sm" :class="card.color">
                            <component :is="card.icon" class="size-6" />
                        </div>
                        <span class="text-xs font-semibold uppercase tracking-wider text-gray-400">Metrik</span>
                    </div>
                    <div class="mt-6">
                        <h3 class="text-sm font-medium text-gray-500">{{ card.label }}</h3>
                        <p class="mt-2 text-4xl font-extrabold tracking-tight text-gray-900">{{ summary[card.key] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart -->
        <div class="rounded-2xl border border-gray-100 bg-white shadow-sm overflow-hidden">
            <div class="border-b border-gray-50 bg-gray-50/30 p-6">
                <h2 class="text-lg font-semibold text-gray-900">Persentase Rata Nilai</h2>
                <p class="text-sm text-gray-500 mt-1">Berdasarkan Standar Mutu per Auditee</p>
            </div>

            <div class="p-6">
                <div v-if="chartData.length === 0" class="py-12 text-center">
                    <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-gray-50 mb-3">
                        <Filter class="size-6 text-gray-400" />
                    </div>
                    <p class="text-sm font-medium text-gray-900">Tidak ada data</p>
                    <p class="text-sm text-gray-500">Silakan sesuaikan filter Anda</p>
                </div>

                <div v-else class="space-y-6">
                    <div v-for="(item, index) in chartData" :key="item.auditee" class="flex items-center gap-4">
                        <div class="w-8 flex-shrink-0 text-sm font-medium text-gray-500">
                            #{{ index + 1 }}
                        </div>
                        <div class="flex-1 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-900">{{ item.auditee }}</span>
                                <span class="text-sm font-medium text-gray-600">{{ item.rata_nilai.toFixed(1) }}</span>
                            </div>
                            <div class="h-2 w-full overflow-hidden rounded-full bg-gray-100">
                                <div
                                    class="h-full rounded-full bg-blue-600 transition-all duration-1000 ease-out"
                                    :style="{ width: `${Math.min((item.rata_nilai / maxNilai) * 100, 100)}%` }"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
