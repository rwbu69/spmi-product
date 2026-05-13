<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Search, ClipboardList, ChevronDown, ChevronRight } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';

interface Auditee { id: number; nama_auditee: string; pengaturan_periode_id: number | null; }
interface PengaturanPeriode {
    id: number;
    lembaga_akreditasi: { id: number; nama_lembaga: string };
    tahun_periode: { tahun: number };
    auditee_count: number;
}

const props = defineProps<{
    periodeList: PengaturanPeriode[];
    auditeeList: Auditee[];
    filters: { periode_id?: string };
}>();

defineOptions({ layout: AppLayout });

const selectedPeriodeId = ref(props.filters.periode_id ?? '');

watch(selectedPeriodeId, () => {
    router.get('/auditor/desk-evaluation', { periode_id: selectedPeriodeId.value }, { preserveState: true, replace: true });
});

const getAuditeeByPeriode = (periodeId: number) =>
    props.auditeeList.filter(a => a.pengaturan_periode_id === periodeId);
</script>

<template>
    <Head title="Desk Evaluation" />
    <div class="space-y-6 p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-semibold text-gray-900">Desk Evaluation</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Evaluasi dokumen per lembaga akreditasi</p>
            </div>
        </div>

        <!-- Info Banner -->
        <div class="flex items-center gap-3 rounded-xl bg-blue-50 border border-blue-100 px-4 py-3 text-sm text-blue-700 dark:bg-blue-900/20 dark:border-blue-800 dark:text-blue-300">
            <ClipboardList class="size-4 shrink-0" />
            <span>Silakan pilih lembaga akreditasi untuk menampilkan standar mutu</span>
        </div>

        <!-- Periode Count -->
        <div class="flex items-center gap-2 text-sm font-medium text-gray-700">
            Total Lembaga Akreditasi
            <span class="inline-flex items-center justify-center rounded-full bg-blue-600 text-white text-xs font-bold w-6 h-6">
                {{ periodeList.length }}
            </span>
        </div>

        <div class="space-y-4">
            <div v-if="periodeList.length === 0" class="p-12 text-center border-2 border-dashed rounded-2xl text-gray-400">
                Tidak ada lembaga akreditasi tersedia.
            </div>
            <div
                v-for="periode in periodeList"
                :key="periode.id"
                class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm hover:shadow-md transition dark:bg-gray-900 dark:border-gray-700"
            >
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-800">
                    <div class="flex-1">
                        <h2 class="font-bold text-gray-900 dark:text-white">{{ periode.lembaga_akreditasi.nama_lembaga }}</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Memiliki {{ periode.auditee_count }} Standar Mutu</p>
                        <span class="inline-flex mt-2 items-center rounded-full bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400 px-2 py-0.5 text-[10px] font-bold uppercase">
                            Periode {{ periode.tahun_periode.tahun }}
                        </span>
                    </div>
                    <div class="shrink-0 ml-4">
                        <button
                            type="button"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-3 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700"
                            @click="selectedPeriodeId = String(periode.id)"
                        >
                            Pilih Auditee / Unit Penunjang
                            <ChevronDown class="size-4" />
                        </button>
                    </div>
                </div>

                <!-- Dropdown auditee list -->
                <div v-if="selectedPeriodeId === String(periode.id)" class="px-6 py-3 bg-gray-50 dark:bg-gray-800/50">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Daftar Auditee</p>
                    <div v-if="getAuditeeByPeriode(periode.id).length === 0" class="text-sm text-gray-400 py-2">
                        Belum ada auditee terdaftar.
                    </div>
                    <div v-else class="space-y-1">
                        <a
                            v-for="auditee in getAuditeeByPeriode(periode.id)"
                            :key="auditee.id"
                            :href="`/ami/rekap-desk-eval?auditee_id=${auditee.id}&periode_id=${periode.id}`"
                            class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition dark:text-gray-300 dark:hover:bg-blue-900/20"
                        >
                            <ChevronRight class="size-3.5 text-blue-400" />
                            {{ auditee.nama_auditee }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
