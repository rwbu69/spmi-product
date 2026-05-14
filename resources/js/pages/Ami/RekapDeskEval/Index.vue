<script setup lang="ts">
import { Head, router, Link } from '@inertiajs/vue3';
import { Search, BarChart3, ChevronRight, Info, AlertCircle } from 'lucide-vue-next';
import { ref, watch, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { usePage } from '@inertiajs/vue3';

interface DeskEvalSummary {
    id: number;
    nilai_evaluasi: number;
    avg_desk_eval: string | null;
    status: string;
    auditee: { id: number; nama_auditee: string };
    pengaturan_periode: {
        id: number;
        tahun_periode: { tahun: number };
        lembaga_akreditasi: { nama_lembaga: string; id: number };
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
    lembagaList?: { id: number; nama_lembaga: string; total_standar?: number }[];
}>();

defineOptions({ layout: AppLayout });

const page = usePage();
const isAuditee = computed(() => {
    const roles = (page.props.auth as any)?.roles ?? [];
    return roles.includes('Auditee') || roles.includes('Unit Penunjang');
});

const search    = ref(props.filters.search ?? '');
const periode_id = ref(props.filters.periode_id ?? '');

let t: any;
watch([search, periode_id], () => {
    clearTimeout(t);
    t = setTimeout(() => {
        router.get('/ami/rekap-desk-eval', { search: search.value, periode_id: periode_id.value }, { preserveState: true, replace: true });
    }, 400);
});

const selectedLembaga = ref<number | null>(null);
const showStandar = (lembagaId: number) => {
    selectedLembaga.value = lembagaId;
    router.get('/ami/rekap-desk-eval', { lembaga_id: lembagaId }, { preserveState: true, replace: true });
};

const getScoreColor = (score: number) => {
    if (score >= 80) return 'text-green-600';
    if (score >= 60) return 'text-blue-600';
    if (score >= 40) return 'text-amber-600';
    return 'text-red-600';
};
</script>

<template>
    <Head title="Hasil Desk Evaluation" />
    <div class="space-y-6 p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-semibold text-gray-900">{{ isAuditee ? 'Hasil Desk Evaluasi' : 'Rekap Desk Evaluation' }}</h1>
                <p class="mt-1 text-sm text-gray-500">Ringkasan penilaian dokumen oleh tim auditor</p>
            </div>
        </div>

        <!-- Auditee View: Lembaga Akreditasi list with Pilih button -->
        <template v-if="isAuditee && lembagaList && lembagaList.length > 0">
            <!-- Info Banner -->
            <div class="flex items-center gap-3 rounded-xl bg-blue-500 p-4 text-white">
                <Info class="size-5 flex-shrink-0" />
                <p class="text-sm font-medium">Silakan pilih lembaga akreditasi untuk menampilkan standar mutu</p>
            </div>

            <!-- Count Badge -->
            <div class="flex items-center gap-2">
                <span class="text-sm font-medium text-gray-700">Total Lembaga Akreditasi</span>
                <span class="inline-flex size-6 items-center justify-center rounded-full bg-orange-500 text-xs font-bold text-white">{{ lembagaList.length }}</span>
            </div>

            <!-- Lembaga List -->
            <div class="rounded-xl border bg-white shadow-sm overflow-hidden divide-y divide-gray-100">
                <div v-for="lembaga in lembagaList" :key="lembaga.id" class="flex items-center gap-4 p-4 hover:bg-gray-50 transition">
                    <Link
                        :href="`/ami/rekap-desk-eval/standar/${lembaga.id}`"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-blue-700 transition"
                    >
                        Pilih <ChevronRight class="size-3.5" />
                    </Link>
                    <div class="flex-1">
                        <p class="font-semibold text-blue-600">{{ lembaga.nama_lembaga }}</p>
                        <p class="text-xs text-gray-500">
                            Standar Nasional :
                            <span class="font-medium text-gray-700">Memiliki {{ lembaga.total_standar ?? 0 }} Standar Mutu</span>
                        </p>
                    </div>
                </div>
            </div>
        </template>

        <!-- Standard Admin/Auditor View: card grid with search/filter -->
        <template v-else>
            <div class="flex flex-wrap gap-4 items-center">
                <div class="relative w-full max-w-xs">
                    <Search class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-gray-400" />
                    <input v-model="search" type="text" placeholder="Cari auditee..." class="w-full rounded-lg border border-gray-300 py-2 pr-4 pl-9 text-sm outline-none transition focus:border-blue-500" />
                </div>
                <select v-model="periode_id" class="rounded-lg border border-gray-300 py-2 px-3 text-sm">
                    <option value="">Semua Periode</option>
                    <option v-for="p in periodeList" :key="p.id" :value="p.id">{{ p.label }}</option>
                </select>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-if="data.data.length === 0" class="col-span-full p-12 text-center border-2 border-dashed rounded-2xl text-gray-400">
                    Belum ada data evaluasi.
                </div>
                <div v-for="item in data.data" :key="item.id" class="p-6 rounded-2xl border bg-white shadow-sm hover:shadow-md transition">
                    <div class="flex items-start justify-between mb-4">
                        <div class="space-y-1">
                            <h3 class="font-bold text-gray-900 line-clamp-1">{{ item.auditee.nama_auditee }}</h3>
                            <p class="text-[10px] text-gray-500 uppercase font-bold tracking-wider">{{ item.pengaturan_periode.lembaga_akreditasi.nama_lembaga }} ({{ item.pengaturan_periode.tahun_periode.tahun }})</p>
                        </div>
                        <div class="size-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600">
                            <BarChart3 class="size-5" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div class="p-3 rounded-xl bg-gray-50 border">
                            <span class="text-[10px] text-gray-400 block uppercase font-bold">Skor ED</span>
                            <span class="text-lg font-black">{{ item.nilai_evaluasi.toFixed(1) }}</span>
                        </div>
                        <div class="p-3 rounded-xl bg-blue-50/50 border border-blue-100">
                            <span class="text-[10px] text-blue-400 block uppercase font-bold">Desk Eval</span>
                            <span :class="getScoreColor(Number(item.avg_desk_eval || 0))" class="text-lg font-black">
                                {{ item.avg_desk_eval ? Number(item.avg_desk_eval).toFixed(1) : '0.0' }}
                            </span>
                        </div>
                    </div>

                    <div class="pt-4 border-t flex justify-end">
                        <Link :href="`/ami/rekap-desk-eval/${item.id}`" class="inline-flex items-center gap-2 text-xs font-bold text-blue-600 hover:underline">
                            Lihat Detail <ChevronRight class="size-3" />
                        </Link>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>
