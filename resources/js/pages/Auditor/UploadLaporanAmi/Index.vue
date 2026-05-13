<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Plus, Trash2, Search, FileBadge, Download } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import Pagination from '@/components/Pagination.vue';
import ConfirmDeleteModal from '@/components/ConfirmDeleteModal.vue';
import Modal from '@/components/Modal.vue';

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
    filters: { auditee_id?: string; periode_id?: string };
    auditeeList: { id: number; nama_auditee: string }[];
    periodeList: { id: number; label: string }[];
}>();

defineOptions({ layout: AppLayout });

const auditee_id = ref(props.filters.auditee_id ?? '');
const periode_id = ref(props.filters.periode_id ?? '');

let searchTimeout: any;
watch([auditee_id, periode_id], () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get('/auditor/upload-laporan-ami', {
            auditee_id: auditee_id.value,
            periode_id: periode_id.value,
        }, { preserveState: true, replace: true });
    }, 400);
});

// Form Upload
const showForm = ref(false);
const form = useForm({
    pengaturan_periode_id: '',
    auditee_id: '',
    file_laporan: null as File | null,
    tanggal_laporan: new Date().toISOString().split('T')[0],
    status: 'Draft',
});

const openCreate = () => { form.reset(); showForm.value = true; };

const submit = () => {
    form.post('/auditor/upload-laporan-ami', {
        preserveScroll: true,
        onSuccess: () => { showForm.value = false; },
    });
};

// Delete
const showDelete = ref(false);
const deleteTarget = ref<LaporanAmi | null>(null);
const deleting = ref(false);
const openDelete = (item: LaporanAmi) => { deleteTarget.value = item; showDelete.value = true; };
const closeDelete = () => { showDelete.value = false; deleteTarget.value = null; };
const confirmDelete = () => {
    if (!deleteTarget.value) return;
    deleting.value = true;
    router.delete(`/auditor/upload-laporan-ami/${deleteTarget.value.id}`, {
        preserveScroll: true,
        onFinish: () => { deleting.value = false; closeDelete(); },
    });
};

const getDownloadUrl = (path: string) => `/storage/${path}`;
</script>

<template>
    <Head title="Upload Laporan AMI" />
    <div class="space-y-6 p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-semibold text-gray-900">Upload Laporan AMI</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Kelola dokumen laporan hasil AMI per unit kerja</p>
            </div>
            <button type="button" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700" @click="openCreate">
                <Plus class="size-4" /> Tambah
            </button>
        </div>

        <!-- Filters -->
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:bg-gray-900 dark:border-gray-700">
            <p class="flex items-center gap-1.5 text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                <Search class="size-4 text-gray-400" /> Filter Data
            </p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Auditee</label>
                    <select v-model="auditee_id" class="w-full rounded-lg border border-gray-300 py-2 px-3 text-sm">
                        <option value="">-- PILIH --</option>
                        <option v-for="a in auditeeList" :key="a.id" :value="a.id">{{ a.nama_auditee }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Periode</label>
                    <select v-model="periode_id" class="w-full rounded-lg border border-gray-300 py-2 px-3 text-sm">
                        <option value="">-- PILIH --</option>
                        <option v-for="p in periodeList" :key="p.id" :value="p.id">{{ p.label }}</option>
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
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Jenis Laporan AMI</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Tahun Laporan AMI</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600 dark:text-gray-300">Download</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    <tr v-if="data.data.length === 0">
                        <td colspan="5" class="px-4 py-12 text-center text-gray-400">Tidak ada data tersedia dalam tabel</td>
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
                                <span class="font-medium text-gray-900 dark:text-white">{{ item.auditee.nama_auditee }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-gray-500">
                            <span :class="item.status === 'Final' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'" class="px-2 py-0.5 rounded text-[10px] font-bold uppercase">
                                {{ item.status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ item.pengaturan_periode.tahun_periode.tahun }}</td>
                        <td class="px-4 py-3 text-center">
                            <div class="inline-flex items-center gap-1">
                                <a :href="getDownloadUrl(item.file_laporan)" target="_blank" class="p-1.5 rounded-md text-blue-600 hover:bg-blue-50 transition">
                                    <Download class="size-4" />
                                </a>
                                <button type="button" class="p-1.5 rounded-md text-red-500 hover:bg-red-50 transition" @click="openDelete(item)">
                                    <Trash2 class="size-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <Pagination :links="data.links" :total="data.total" />
    </div>

    <!-- Modal Upload -->
    <Modal :show="showForm" title="Upload Laporan AMI" max-width="md" @close="showForm = false">
        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1">Periode AMI <span class="text-red-500">*</span></label>
                <select v-model="form.pengaturan_periode_id" class="w-full rounded-lg border px-3 py-2 text-sm">
                    <option value="">Pilih Periode</option>
                    <option v-for="p in periodeList" :key="p.id" :value="p.id">{{ p.label }}</option>
                </select>
                <p v-if="form.errors.pengaturan_periode_id" class="text-[11px] text-red-500 mt-1">{{ form.errors.pengaturan_periode_id }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Auditee <span class="text-red-500">*</span></label>
                <select v-model="form.auditee_id" class="w-full rounded-lg border px-3 py-2 text-sm">
                    <option value="">Pilih Auditee</option>
                    <option v-for="a in auditeeList" :key="a.id" :value="a.id">{{ a.nama_auditee }}</option>
                </select>
                <p v-if="form.errors.auditee_id" class="text-[11px] text-red-500 mt-1">{{ form.errors.auditee_id }}</p>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Tanggal Laporan <span class="text-red-500">*</span></label>
                    <input v-model="form.tanggal_laporan" type="date" class="w-full rounded-lg border px-3 py-2 text-sm" />
                    <p v-if="form.errors.tanggal_laporan" class="text-[11px] text-red-500 mt-1">{{ form.errors.tanggal_laporan }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Status <span class="text-red-500">*</span></label>
                    <select v-model="form.status" class="w-full rounded-lg border px-3 py-2 text-sm">
                        <option value="Draft">Draft</option>
                        <option value="Final">Final</option>
                    </select>
                    <p v-if="form.errors.status" class="text-[11px] text-red-500 mt-1">{{ form.errors.status }}</p>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">File Laporan (PDF) <span class="text-red-500">*</span></label>
                <input type="file" accept="application/pdf" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" @input="form.file_laporan = ($event.target as HTMLInputElement).files?.[0] || null" />
                <p v-if="form.errors.file_laporan" class="text-[11px] text-red-500 mt-1">{{ form.errors.file_laporan }}</p>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50" @click="showForm = false">Batal</button>
                <button type="submit" :disabled="form.processing" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700 disabled:opacity-60">
                    {{ form.processing ? 'Mengunggah...' : 'Upload Laporan' }}
                </button>
            </div>
        </form>
    </Modal>

    <ConfirmDeleteModal :show="showDelete" :message="`Hapus laporan AMI '${deleteTarget?.auditee?.nama_auditee}'?`" :processing="deleting" @close="closeDelete" @confirm="confirmDelete" />
</template>

