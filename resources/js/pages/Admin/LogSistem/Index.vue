<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import Pagination from '@/components/Pagination.vue';
import { DataTable } from '@/components/index';

interface LogRow {
    id: number;
    event: string;
    subject_type: string;
    subject_class: string;
    causer_name: string;
    causer_role: string;
    time: string;
    time_human: string;
    old_data: any;
    new_data: any;
}

const props = defineProps<{
    data: { data: LogRow[]; total: number; links: any[]; };
    filters: { date_from?: string; date_to?: string; user_id?: string; model_type?: string; event?: string; per_page?: string };
    modelOptions: Array<{ value: string; label: string }>;
    userOptions: Array<{ id: number; name: string }>;
    actionOptions: string[];
}>();

defineOptions({ layout: AppLayout });

const dateFrom = ref(props.filters.date_from ?? '');
const dateTo = ref(props.filters.date_to ?? '');
const userId = ref(props.filters.user_id ?? '');
const modelType = ref(props.filters.model_type ?? '');
const actionEvent = ref(props.filters.event ?? '');
const perPage = ref(props.filters.per_page ?? '10');

let t: any;
watch([dateFrom, dateTo, userId, modelType, actionEvent, perPage], () => {
    clearTimeout(t);
    t = setTimeout(() => {
        router.get('/admin/log-sistem', {
            date_from: dateFrom.value || undefined,
            date_to: dateTo.value || undefined,
            user_id: userId.value || undefined,
            model_type: modelType.value || undefined,
            event: actionEvent.value || undefined,
            per_page: perPage.value || undefined,
        }, { preserveState: true, replace: true });
    }, 300);
});

// Modal state
const selectedLog = ref<LogRow | null>(null);
const showModal = ref(false);
const showUnchanged = ref(false);

const openDetail = (row: LogRow) => {
    selectedLog.value = row;
    showUnchanged.value = false;
    showModal.value = true;
};

const closeDetail = () => {
    showModal.value = false;
    selectedLog.value = null;
    showUnchanged.value = false;
};

const translateEvent = (event: string) => {
    switch (event) {
        case 'created': return 'Tambah';
        case 'updated': return 'Ubah';
        case 'deleted': return 'Hapus';
        default: return event;
    }
};

const formatValue = (val: any): string => {
    if (val === null || val === undefined) return '-';
    if (typeof val === 'boolean') return val ? 'YA' : 'TIDAK';
    if (typeof val === 'object') return JSON.stringify(val, null, 2);
    return String(val);
};

const getChangedFields = (log: LogRow) => {
    if (!log.old_data || !log.new_data || !Array.isArray(log.old_data) || !Array.isArray(log.new_data)) {
        return [];
    }
    const changes: Array<{ label: string; old_value: any; new_value: any }> = [];
    
    // We check all unique labels across both datasets
    const allLabels = Array.from(new Set([
        ...log.old_data.map((item: any) => item.label),
        ...log.new_data.map((item: any) => item.label)
    ]));

    allLabels.forEach((label) => {
        const oldItem = log.old_data.find((item: any) => item.label === label);
        const newItem = log.new_data.find((item: any) => item.label === label);
        
        const oldVal = oldItem ? oldItem.value : undefined;
        const newVal = newItem ? newItem.value : undefined;

        if (oldVal !== newVal) {
            changes.push({
                label,
                old_value: oldVal,
                new_value: newVal,
            });
        }
    });

    return changes;
};

const getUnchangedFields = (log: LogRow) => {
    if (!log.old_data || !log.new_data || !Array.isArray(log.old_data) || !Array.isArray(log.new_data)) {
        return [];
    }
    const unchanged: Array<{ label: string; value: any }> = [];
    
    log.new_data.forEach((newItem: any) => {
        const oldItem = log.old_data.find((item: any) => item.label === newItem.label);
        if (oldItem && oldItem.value === newItem.value) {
            unchanged.push({
                label: newItem.label,
                value: newItem.value,
            });
        }
    });

    return unchanged;
};
</script>

<template>
    <Head title="Log Sistem" />
    <div class="space-y-6 p-6">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Log Sistem</h1>
            <p class="mt-1 text-sm text-gray-500">Riwayat rekam jejak aktivitas dan perubahan data pada database secara presisi.</p>
        </div>

        <!-- Filter Panel -->
        <div class="rounded-xl border bg-white p-4 shadow-sm">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-6">
                <div>
                    <label class="text-xs font-semibold text-gray-600">Tanggal Mulai</label>
                    <input v-model="dateFrom" type="date" class="mt-1 w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-600">Tanggal Akhir</label>
                    <input v-model="dateTo" type="date" class="mt-1 w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-600">Model / Menu</label>
                    <select v-model="modelType" class="mt-1 w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                        <option value="">Semua Model</option>
                        <option v-for="m in modelOptions" :key="m.value" :value="m.value">{{ m.label }}</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-600">Pengguna</label>
                    <select v-model="userId" class="mt-1 w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                        <option value="">Semua Pengguna</option>
                        <option v-for="u in userOptions" :key="u.id" :value="String(u.id)">{{ u.name }}</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-600">Jenis Aksi</label>
                    <select v-model="actionEvent" class="mt-1 w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                        <option value="">Semua Aksi</option>
                        <option v-for="a in actionOptions" :key="a" :value="a">{{ translateEvent(a) }}</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-600">Per Halaman</label>
                    <select v-model="perPage" class="mt-1 w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <DataTable :is-empty="data.data.length === 0" empty-message="Belum ada rekam jejak aktivitas." :col-span="4">
            <template #head>
                <th class="px-4 py-3 font-medium text-gray-600">Model / Menu</th>
                <th class="px-4 py-3 font-medium text-gray-600">Aksi</th>
                <th class="px-4 py-3 font-medium text-gray-600">Pengguna</th>
                <th class="px-4 py-3 font-medium text-gray-600">Waktu</th>
                <th class="px-4 py-3 font-medium text-gray-600 text-center">Aksi</th>
            </template>

            <tr v-for="row in data.data" :key="row.id" class="hover:bg-gray-50 transition">
                <td class="px-4 py-3 font-semibold text-gray-800">
                    {{ row.subject_type }}
                </td>
                <td class="px-4 py-3">
                    <span
                        class="inline-flex items-center rounded px-2 py-0.5 text-[10px] font-bold uppercase"
                        :class="{
                            'bg-green-100 text-green-800': row.event === 'created',
                            'bg-blue-100 text-blue-800': row.event === 'updated',
                            'bg-red-100 text-red-800': row.event === 'deleted',
                        }"
                    >
                        {{ translateEvent(row.event) }}
                    </span>
                </td>
                <td class="px-4 py-3 text-xs text-gray-600">
                    <div class="font-medium text-gray-800">{{ row.causer_name }}</div>
                    <div class="text-[10px] text-gray-400">{{ row.causer_role }}</div>
                </td>
                <td class="px-4 py-3 text-xs text-gray-500">
                    <div class="font-medium">{{ row.time }}</div>
                    <div class="text-[10px] text-gray-400">{{ row.time_human }}</div>
                </td>
                <td class="px-4 py-3 text-center">
                    <button
                        type="button"
                        class="rounded bg-blue-50 px-2 py-1 text-xs font-semibold text-blue-700 hover:bg-blue-100 transition"
                        @click="openDetail(row)"
                    >
                        Lihat Detail
                    </button>
                </td>
            </tr>
        </DataTable>

        <Pagination :links="data.links" :total="data.total" />

        <!-- Detail Modal (Centering vertically & horizontally perfectly) -->
        <div v-if="showModal && selectedLog" class="fixed inset-0 z-[60] flex items-center justify-center p-4 transition-all">
            <!-- Backdrop -->
            <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm transition-opacity" @click="closeDetail"></div>
            
            <!-- Modal Body -->
            <div class="relative w-full max-w-4xl rounded-2xl bg-white p-6 shadow-xl border border-gray-100 max-h-[90vh] overflow-y-auto transition-all transform scale-100">
                <div class="flex items-center justify-between border-b pb-4 mb-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Detail Perubahan Log</h3>
                        <p class="text-xs text-gray-500">ID Aktivitas #{{ selectedLog.id }} • {{ selectedLog.subject_type }} • {{ translateEvent(selectedLog.event) }}</p>
                    </div>
                    <button @click="closeDetail" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition focus:outline-none">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Meta Info Grid -->
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4 bg-gray-50 p-4 rounded-xl mb-6 text-xs text-gray-600">
                    <div>
                        <span class="block text-gray-400 font-medium">Model Class</span>
                        <span class="font-bold text-gray-800 truncate block" :title="selectedLog.subject_class">{{ selectedLog.subject_class }}</span>
                    </div>
                    <div>
                        <span class="block text-gray-400 font-medium">Pelaku</span>
                        <span class="font-bold text-gray-800">{{ selectedLog.causer_name }} ({{ selectedLog.causer_role }})</span>
                    </div>
                    <div>
                        <span class="block text-gray-400 font-medium">Waktu</span>
                        <span class="font-bold text-gray-800">{{ selectedLog.time }}</span>
                    </div>
                </div>

                <!-- Diff Grid: Dynamic scenarios for Created, Deleted, and Updated -->
                <div v-if="selectedLog.event === 'created'" class="border rounded-xl p-5 bg-green-50/20 border-green-200 shadow-sm">
                    <div class="flex items-center gap-3 border-b border-green-100 pb-3 mb-4">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-green-100 text-green-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-green-900">Data Ditambahkan</h4>
                            <p class="text-xs text-green-600">Daftar field dan nilai baru yang berhasil dimasukkan ke sistem saat create/insert.</p>
                        </div>
                    </div>
                    
                    <div v-if="Array.isArray(selectedLog.new_data)" class="overflow-x-auto rounded-lg border border-green-100 bg-white">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-green-50 text-green-800">
                                <tr>
                                    <th class="px-4 py-3 font-semibold w-1/3">Kolom / Field</th>
                                    <th class="px-4 py-3 font-semibold">Nilai Baru</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-green-100/50">
                                <tr v-for="item in selectedLog.new_data" :key="item.label" class="hover:bg-green-50/30 transition">
                                    <td class="px-4 py-2.5 font-bold text-gray-700">{{ item.label }}</td>
                                    <td class="px-4 py-2.5 text-gray-800 font-medium break-all whitespace-pre-wrap">{{ formatValue(item.value) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="rounded-lg bg-white p-4 text-center border text-xs text-gray-500 italic">
                        {{ selectedLog.new_data }}
                    </div>
                </div>

                <div v-else-if="selectedLog.event === 'deleted'" class="border rounded-xl p-5 bg-red-50/20 border-red-200 shadow-sm">
                    <div class="flex items-center gap-3 border-b border-red-100 pb-3 mb-4">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-red-100 text-red-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-red-900">Data Dihapus</h4>
                            <p class="text-xs text-red-600">Daftar field dan nilai lama yang tersimpan sebelum data dihapus secara permanen.</p>
                        </div>
                    </div>
                    
                    <div v-if="Array.isArray(selectedLog.old_data)" class="overflow-x-auto rounded-lg border border-red-100 bg-white">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-red-50 text-red-800">
                                <tr>
                                    <th class="px-4 py-3 font-semibold w-1/3">Kolom / Field</th>
                                    <th class="px-4 py-3 font-semibold">Nilai Lama</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-red-100/50">
                                <tr v-for="item in selectedLog.old_data" :key="item.label" class="hover:bg-red-50/30 transition">
                                    <td class="px-4 py-2.5 font-bold text-gray-700">{{ item.label }}</td>
                                    <td class="px-4 py-2.5 text-gray-800 font-medium break-all whitespace-pre-wrap">{{ formatValue(item.value) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="rounded-lg bg-white p-4 text-center border text-xs text-gray-500 italic">
                        {{ selectedLog.old_data }}
                    </div>
                </div>

                <div v-else class="space-y-6">
                    <!-- Changed Fields Card -->
                    <div class="border rounded-xl p-5 bg-blue-50/20 border-blue-200 shadow-sm">
                        <div class="flex items-center gap-3 border-b border-blue-100 pb-3 mb-4">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 text-blue-600">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-blue-900">Perbandingan Nilai yang Berubah</h4>
                                <p class="text-xs text-blue-600">Hanya menampilkan kolom yang mengalami perubahan nilai sebelum (before) dan sesudah (after).</p>
                            </div>
                        </div>

                        <div v-if="getChangedFields(selectedLog).length > 0" class="overflow-x-auto rounded-lg border border-blue-100 bg-white">
                            <table class="w-full text-xs text-left">
                                <thead class="bg-blue-50 text-blue-800">
                                    <tr>
                                        <th class="px-4 py-3 font-semibold w-1/4">Kolom / Field</th>
                                        <th class="px-4 py-3 font-semibold bg-red-50/50 text-red-800">Nilai Lama (Before)</th>
                                        <th class="px-4 py-3 font-semibold bg-green-50/50 text-green-800">Nilai Baru (After)</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-blue-100/50">
                                    <tr v-for="item in getChangedFields(selectedLog)" :key="item.label" class="hover:bg-blue-50/10 transition">
                                        <td class="px-4 py-3 font-bold text-gray-700">{{ item.label }}</td>
                                        <td class="px-4 py-3 bg-red-50/10 text-red-900 font-medium break-all whitespace-pre-wrap">{{ formatValue(item.old_value) }}</td>
                                        <td class="px-4 py-3 bg-green-50/10 text-green-950 font-bold break-all whitespace-pre-wrap">{{ formatValue(item.new_value) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-else class="rounded-lg bg-white p-6 text-center border border-blue-100 text-xs text-gray-500 italic">
                            Tidak ada kolom data yang berubah secara spesifik.
                        </div>
                    </div>

                    <!-- Unchanged Fields Section (Collapsible Accordion) -->
                    <div v-if="getUnchangedFields(selectedLog).length > 0" class="border rounded-xl bg-gray-50/50 border-gray-200">
                        <button 
                            @click="showUnchanged = !showUnchanged"
                            type="button" 
                            class="w-full flex items-center justify-between px-5 py-3 text-xs font-semibold text-gray-700 hover:bg-gray-100/70 rounded-xl transition focus:outline-none"
                        >
                            <span class="flex items-center gap-2">
                                <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Lihat {{ getUnchangedFields(selectedLog).length }} Field Lain yang Tidak Berubah
                            </span>
                            <svg 
                                class="h-4 w-4 text-gray-500 transition-transform duration-200" 
                                :class="{ 'rotate-180': showUnchanged }" 
                                fill="none" 
                                viewBox="0 0 24 24" 
                                stroke="currentColor"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        
                        <div v-if="showUnchanged" class="p-5 border-t border-gray-200 bg-white rounded-b-xl overflow-x-auto">
                            <table class="w-full text-xs text-left">
                                <thead class="bg-gray-50 text-gray-600">
                                    <tr>
                                        <th class="px-4 py-2 font-semibold w-1/3">Kolom / Field</th>
                                        <th class="px-4 py-2 font-semibold">Nilai</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <tr v-for="item in getUnchangedFields(selectedLog)" :key="item.label" class="hover:bg-gray-50/50 transition">
                                        <td class="px-4 py-2 font-medium text-gray-500">{{ item.label }}</td>
                                        <td class="px-4 py-2 text-gray-600 break-all whitespace-pre-wrap">{{ formatValue(item.value) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex justify-end gap-3 border-t pt-4 mt-6">
                    <button
                        type="button"
                        class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition"
                        @click="closeDetail"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
