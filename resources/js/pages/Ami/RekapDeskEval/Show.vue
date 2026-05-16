<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, CheckCircle2, AlertCircle } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';

interface DeskEvaluation {
    id: number;
    nilai: number;
    catatan: string | null;
    indikator: {
        id: number;
        deskripsi: string;
        standar_mutu: {
            nama_standar: string;
        };
    };
    auditor: {
        nama: string;
    } | null;
}

const props = defineProps<{
    evaluasiDiri: {
        id: number;
        nilai_evaluasi: number;
        auditee: { nama_auditee: string };
        pengaturan_periode: { 
            tahun_periode: { tahun: number };
            lembaga_akreditasi: { nama_lembaga: string };
        };
    };
    deskEvaluations: DeskEvaluation[];
}>();

defineOptions({ layout: AppLayout });

const getScoreColor = (score: number) => {
    if (score >= 80) return 'text-green-600 bg-green-50 border-green-200';
    if (score >= 60) return 'text-blue-600 bg-blue-50 border-blue-200';
    if (score >= 40) return 'text-amber-600 bg-amber-50 border-amber-200';
    return 'text-red-600 bg-red-50 border-red-200';
};
</script>

<template>
    <Head title="Detail Rekap Desk Evaluation" />
    <div class="space-y-6 p-6">
        <!-- Header -->
        <div class="flex items-center gap-4">
            <Link href="/ami/rekap-desk-eval" class="p-2 rounded-lg hover:bg-gray-100 transition">
                <ArrowLeft class="size-5 text-gray-500" />
            </Link>
            <div>
                <h1 class="text-xl font-semibold text-gray-900 ">Detail Desk Evaluation</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ evaluasiDiri.auditee.nama_auditee }} - {{ evaluasiDiri.pengaturan_periode.lembaga_akreditasi.nama_lembaga }} ({{ evaluasiDiri.pengaturan_periode.tahun_periode.tahun }})
                </p>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-gray-200 shadow-sm dark:border-gray-700 bg-white dark:bg-gray-900">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-4 py-3 font-medium text-gray-600 dark:text-gray-300">Standar & Indikator</th>
                        <th class="px-4 py-3 font-medium text-gray-600 dark:text-gray-300 w-32 text-center">Nilai</th>
                        <th class="px-4 py-3 font-medium text-gray-600 dark:text-gray-300">Catatan Auditor</th>
                        <th class="px-4 py-3 font-medium text-gray-600 dark:text-gray-300 w-48">Auditor</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    <tr v-if="deskEvaluations.length === 0">
                        <td colspan="4" class="px-4 py-8 text-center text-gray-400">Belum ada evaluasi dari auditor.</td>
                    </tr>
                    <tr v-for="item in deskEvaluations" :key="item.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                        <td class="px-4 py-4">
                            <div class="text-xs font-bold text-gray-400 mb-1">{{ item.indikator.standar_mutu.nama_standar }}</div>
                            <div class="font-medium text-gray-900 dark:text-gray-100">{{ item.indikator.deskripsi }}</div>
                        </td>
                        <td class="px-4 py-4 text-center">
                            <span :class="getScoreColor(item.nilai)" class="inline-flex items-center justify-center w-12 h-8 rounded-lg font-bold border">
                                {{ item.nilai }}
                            </span>
                        </td>
                        <td class="px-4 py-4">
                            <div v-if="item.catatan" class="text-sm text-gray-600 dark:text-gray-300">{{ item.catatan }}</div>
                            <div v-else class="text-sm text-gray-400 italic">Tidak ada catatan</div>
                        </td>
                        <td class="px-4 py-4 text-gray-500 text-xs">
                            <div class="flex items-center gap-2">
                                <CheckCircle2 v-if="item.auditor" class="size-4 text-green-500" />
                                <AlertCircle v-else class="size-4 text-amber-500" />
                                {{ item.auditor?.nama || 'Belum ditugaskan' }}
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
