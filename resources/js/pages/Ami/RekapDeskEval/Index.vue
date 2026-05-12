<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Search, BarChart3, ChevronRight, LayoutDashboard } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';

interface DeskEvalSummary {
    id: number;
    nilai_evaluasi: number;
    avg_desk_eval: string | null;
    status: string;
    auditee: { id: number; nama_auditee: string };
    pengaturan_periode: { 
        id: number; 
        tahun_periode: { tahun: number };
        lembaga_akreditasi: { nama_lembaga: string };
    };
}

const props = defineProps<{
    data: {
        data: DeskEvalSummary[];
        total: number;
        links: any[];
    };
    filters: { search?: string; periode_id?: string };
    periodeList: { id: number; label: string }[];
}>();

defineOptions({ layout: AppLayout });

const search = ref(props.filters.search ?? '');
const periode_id = ref(props.filters.periode_id ?? '');

let searchTimeout: any;
watch([search, periode_id], () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get('/ami/rekap-desk-eval', { 
            search: search.value, 
            periode_id: periode_id.value 
        }, { preserveState: true, replace: true });
    }, 400);
});

const getScoreColor = (score: number) => {
    if (score >= 80) return 'text-green-600';
    if (score >= 60) return 'text-blue-600';
    if (score >= 40) return 'text-amber-600';
    return 'text-red-600';
};
</script>

<template>
    <Head title="Rekap Desk Evaluation" />
    <div class="space-y-6 p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-semibold text-gray-900 ">Rekap Desk Evaluation</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Ringkasan penilaian dokumen oleh tim auditor</p>
            </div>
        </div>

        <div class="flex flex-wrap gap-4 items-center">
            <div class="relative w-full max-w-xs">
                <Search class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-gray-400" />
                <input v-model="search" type="text" placeholder="Cari auditee..." class="w-full rounded-lg border border-gray-300 py-2 pr-4 pl-9 text-sm outline-none transition focus:border-blue-500  " />
            </div>
            <select v-model="periode_id" class="rounded-lg border border-gray-300 py-2 px-3 text-sm  ">
                <option value="">Semua Periode</option>
                <option v-for="p in periodeList" :key="p.id" :value="p.id">{{ p.label }}</option>
            </select>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-if="data.data.length === 0" class="col-span-full p-12 text-center border-2 border-dashed rounded-2xl text-gray-400">
                Belum ada data evaluasi.
            </div>
            <div v-for="item in data.data" :key="item.id" class="p-6 rounded-2xl border bg-white dark:bg-gray-900 shadow-sm hover:shadow-md transition">
                <div class="flex items-start justify-between mb-4">
                    <div class="space-y-1">
                        <h3 class="font-bold text-gray-900  line-clamp-1">{{ item.auditee.nama_auditee }}</h3>
                        <p class="text-[10px] text-gray-500 uppercase font-bold tracking-wider">{{ item.pengaturan_periode.lembaga_akreditasi.nama_lembaga }} ({{ item.pengaturan_periode.tahun_periode.tahun }})</p>
                    </div>
                    <div class="size-10 rounded-xl bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center text-blue-600">
                        <BarChart3 class="size-5" />
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-3 rounded-xl bg-gray-50 /50 border dark:border-gray-700">
                            <span class="text-[10px] text-gray-400 block uppercase font-bold">Skor ED</span>
                            <span class="text-lg font-black">{{ item.nilai_evaluasi.toFixed(1) }}</span>
                        </div>
                        <div class="p-3 rounded-xl bg-blue-50/50 dark:bg-blue-900/10 border border-blue-100 dark:border-blue-900/40">
                            <span class="text-[10px] text-blue-400 block uppercase font-bold">Desk Eval</span>
                            <span :class="getScoreColor(Number(item.avg_desk_eval || 0))" class="text-lg font-black">
                                {{ item.avg_desk_eval ? Number(item.avg_desk_eval).toFixed(1) : '0.0' }}
                            </span>
                        </div>
                    </div>

                    <div class="relative pt-1">
                        <div class="flex mb-2 items-center justify-between">
                            <div><span class="text-[10px] font-semibold inline-block py-1 px-2 uppercase rounded-full text-blue-600 bg-blue-100 dark:bg-blue-900/40">Progres Penilaian</span></div>
                            <div class="text-right"><span class="text-xs font-semibold inline-block text-blue-600">{{ item.avg_desk_eval ? '100%' : '0%' }}</span></div>
                        </div>
                        <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-gray-100 ">
                            <div :style="`width: ${item.avg_desk_eval ? 100 : 0}%`" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-blue-500"></div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-4 border-t dark:border-gray-800 flex justify-end">
                    <button type="button" class="inline-flex items-center gap-2 text-xs font-bold text-blue-600 hover:underline">
                        Lihat Detail <ChevronRight class="size-3" />
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
