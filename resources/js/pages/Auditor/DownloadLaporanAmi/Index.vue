<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Search, Download, FileBadge, Calendar } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import Pagination from '@/components/Pagination.vue';

interface LaporanAmi {
    id: number;
    file_laporan: string;
    tanggal_laporan: string;
    status: 'Draft' | 'Final';
    auditee: { id: number; nama_auditee: string };
    pengaturan_periode: {
        id: number;
        tahun_periode: { tahun: number };
        lembaga_akreditasi: { nama_lembaga: string };
    };
}

const props = defineProps<{
    data: { data: LaporanAmi[]; total: number; links: any[] };
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
        router.get('/auditor/download-laporan-ami', {
            search: search.value,
            periode_id: periode_id.value,
        }, { preserveState: true, replace: true });
    }, 400);
});

const getDownloadUrl = (path: string) => `/storage/${path}`;
const formatDate = (d: string) => new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
</script>

<template>
    <Head title="Download Laporan AMI" />
    <div class="space-y-6 p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-semibold text-gray-900">Download Laporan AMI</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Unduh laporan hasil audit per unit kerja</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="flex flex-wrap gap-4 items-center">
            <div class="relative w-full max-w-xs">
                <Search class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-gray-400" />
                <input v-model="search" type="text" placeholder="Cari nama auditee..." class="w-full rounded-lg border border-gray-300 py-2 pr-4 pl-9 text-sm outline-none transition focus:border-blue-500" />
            </div>
            <select v-model="periode_id" class="rounded-lg border border-gray-300 py-2 px-3 text-sm">
                <option value="">Semua Periode</option>
                <option v-for="p in periodeList" :key="p.id" :value="p.id">{{ p.label }}</option>
            </select>
        </div>

        <!-- Table -->
        <div class="overflow-hidden rounded-xl border border-gray-200 shadow-sm dark:border-gray-700">
            <table class="w-full text-sm">
                <thead class="bg-blue-50 text-blue-700">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">No</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Nama Dokumen</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600 dark:text-gray-300">Download</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    <tr v-if="data.data.length === 0">
                        <td colspan="3" class="px-4 py-12 text-center text-gray-400">Belum ada laporan AMI.</td>
                    </tr>
                    <tr
                        v-for="(item, i) in data.data"
                        :key="item.id"
                        class="bg-white transition hover:bg-gray-50 dark:bg-gray-900 dark:hover:bg-gray-800/50"
                    >
                        <td class="px-4 py-3 text-gray-500">{{ i + 1 }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="flex size-8 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/30">
                                    <FileBadge class="size-4 text-blue-600 dark:text-blue-400" />
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ item.auditee.nama_auditee }}</p>
                                    <p class="text-xs text-gray-400">{{ item.pengaturan_periode.lembaga_akreditasi.nama_lembaga }} ({{ item.pengaturan_periode.tahun_periode.tahun }})</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="inline-flex rounded-lg overflow-hidden border border-blue-600">
                                <a
                                    :href="getDownloadUrl(item.file_laporan)"
                                    target="_blank"
                                    class="inline-flex items-center gap-1.5 bg-blue-600 text-white px-3 py-1.5 text-xs font-medium hover:bg-blue-700 transition"
                                >
                                    {{ item.auditee.nama_auditee }}
                                </a>
                                <a
                                    :href="getDownloadUrl(item.file_laporan)"
                                    target="_blank"
                                    class="inline-flex items-center px-2 py-1.5 bg-blue-500 text-white hover:bg-blue-600 transition border-l border-blue-700"
                                >
                                    <Download class="size-3.5" />
                                </a>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <Pagination :links="data.links" :total="data.total" />
    </div>
</template>

