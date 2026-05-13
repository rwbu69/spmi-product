<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Search } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';

interface RekapTemuan {
    id: number;
    nama_auditee: string;
    jumlah_temuan: number;
}

const props = defineProps<{
    data: { data: RekapTemuan[]; total: number; links: any[] };
    filters: { tahun?: string; lembaga_id?: string };
    tahunList: { id: number; tahun: number }[];
    lembagaList: { id: number; nama_lembaga: string }[];
    currentTahun: string | number;
}>();

defineOptions({ layout: AppLayout });

const tahun = ref(String(props.filters.tahun ?? props.currentTahun));
const lembaga_id = ref(props.filters.lembaga_id ?? '');

let searchTimeout: any;
watch([tahun, lembaga_id], () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get('/auditor/rekap-temuan', {
            tahun: tahun.value,
            lembaga_id: lembaga_id.value,
        }, { preserveState: true, replace: true });
    }, 400);
});
</script>

<template>
    <Head title="Rekapitulasi Daftar Temuan" />
    <div class="space-y-6 p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-semibold text-gray-900">Rekapitulasi Daftar Temuan</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Rekap jumlah temuan per auditee berdasarkan periode</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:bg-gray-900 dark:border-gray-700">
            <p class="flex items-center gap-1.5 text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                <Search class="size-4 text-gray-400" /> Filter Data
            </p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Tahun</label>
                    <select v-model="tahun" class="w-full rounded-lg border border-gray-300 py-2 px-3 text-sm">
                        <option v-for="t in tahunList" :key="t.id" :value="String(t.tahun)">{{ t.tahun }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Lembaga Akreditasi</label>
                    <select v-model="lembaga_id" class="w-full rounded-lg border border-gray-300 py-2 px-3 text-sm">
                        <option value="">Semua Lembaga</option>
                        <option v-for="l in lembagaList" :key="l.id" :value="l.id">{{ l.nama_lembaga }}</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-hidden rounded-xl border border-gray-200 shadow-sm dark:border-gray-700">
            <table class="w-full text-sm">
                <thead class="bg-blue-50 text-blue-700">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">No</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Auditee</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Jumlah Temuan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    <tr v-if="data.data.length === 0">
                        <td colspan="3" class="px-4 py-12 text-center text-gray-400">Tidak ada data yang sesuai.</td>
                    </tr>
                    <tr
                        v-for="(item, i) in data.data"
                        :key="item.id"
                        class="bg-white transition hover:bg-gray-50 dark:bg-gray-900 dark:hover:bg-gray-800/50"
                    >
                        <td class="px-4 py-3 text-gray-500">{{ i + 1 }}</td>
                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ item.nama_auditee }}</td>
                        <td class="px-4 py-3">
                            <span
                                class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                :class="item.jumlah_temuan > 0 ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' : 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'"
                            >
                                {{ item.jumlah_temuan > 0 ? item.jumlah_temuan + ' Temuan' : 'Tidak ada temuan' }}
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="flex items-center justify-between text-sm text-gray-500">
            <span>Total: {{ data.total }} data</span>
            <div class="flex gap-1">
                <template v-for="link in data.links" :key="link.label">
                    <button v-if="link.url" type="button" class="rounded px-3 py-1 transition" :class="link.active ? 'bg-blue-600 text-white' : 'hover:bg-gray-100 dark:hover:bg-gray-700'" @click="router.get(link.url, {}, { preserveState: true })" v-html="link.label" />
                    <span v-else class="cursor-default rounded px-3 py-1 text-gray-300" v-html="link.label" />
                </template>
            </div>
        </div>
    </div>
</template>
