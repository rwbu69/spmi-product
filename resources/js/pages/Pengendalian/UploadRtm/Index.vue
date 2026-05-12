<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Plus, Search, Trash2, FileCheck, Download, CheckCircle2 } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import ConfirmDeleteModal from '@/components/ConfirmDeleteModal.vue';
import Modal from '@/components/Modal.vue';

interface UploadRtm {
    id: number;
    nama_dokumen: string;
    file_path: string;
    tanggal_upload: string;
    status_download: string;
    auditee: { id: number; nama_auditee: string };
    tahun_periode: { id: number; tahun: number };
}

const props = defineProps<{
    data: {
        data: UploadRtm[];
        total: number;
        links: any[];
    };
    filters: { search?: string; tahun_id?: string };
    tahunList: { id: number; tahun: number }[];
    auditeeList: { id: number; nama_auditee: string }[];
}>();

defineOptions({ layout: AppLayout });

const search = ref(props.filters.search ?? '');
const tahun_id = ref(props.filters.tahun_id ?? '');

let searchTimeout: any;
watch([search, tahun_id], () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get('/pengendalian/upload-rtm', { 
            search: search.value, 
            tahun_id: tahun_id.value 
        }, { preserveState: true, replace: true });
    }, 400);
});

// Form
const showForm = ref(false);
const form = useForm({
    auditee_id: '',
    tahun_periode_id: '',
    nama_dokumen: '',
    file_path: null as File | null,
});

const openCreate = () => {
    form.reset();
    showForm.value = true;
};

const submit = () => {
    const opts = { preserveScroll: true, onSuccess: () => { showForm.value = false; } };
    form.post('/pengendalian/upload-rtm', opts);
};

const showDelete = ref(false);
const deleteTarget = ref<UploadRtm | null>(null);
const openDelete = (item: UploadRtm) => { deleteTarget.value = item; showDelete.value = true; };
const confirmDelete = () => {
    if (!deleteTarget.value) return;
    router.delete(`/pengendalian/upload-rtm/${deleteTarget.value.id}`, {
        preserveScroll: true,
        onSuccess: () => { showDelete.value = false; }
    });
};

const getDownloadUrl = (path: string) => `/storage/${path}`;
</script>

<template>
    <Head title="Upload Laporan RTM" />
    <div class="space-y-6 p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-semibold text-gray-900 ">Upload Laporan RTM Final</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Pengarsipan laporan RTM yang telah disahkan</p>
            </div>
            <button type="button" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700" @click="openCreate">
                <Plus class="size-4" /> Unggah Laporan
            </button>
        </div>

        <div class="flex flex-wrap gap-4 items-center">
            <div class="relative w-full max-w-xs">
                <Search class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-gray-400" />
                <input v-model="search" type="text" placeholder="Cari nama..." class="w-full rounded-lg border border-gray-300 py-2 pr-4 pl-9 text-sm outline-none transition focus:border-blue-500  " />
            </div>
            <select v-model="tahun_id" class="rounded-lg border border-gray-300 py-2 px-3 text-sm  ">
                <option value="">Semua Tahun</option>
                <option v-for="t in tahunList" :key="t.id" :value="t.id">{{ t.tahun }}</option>
            </select>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-if="data.data.length === 0" class="col-span-full p-12 text-center border-2 border-dashed rounded-2xl text-gray-400">
                Belum ada laporan RTM final.
            </div>
            <div v-for="item in data.data" :key="item.id" class="p-6 rounded-2xl border bg-white dark:bg-gray-900 shadow-sm hover:shadow-lg transition">
                <div class="flex items-start gap-4 mb-4">
                    <div class="size-12 rounded-xl bg-green-50 dark:bg-green-900/20 flex items-center justify-center text-green-600 shrink-0">
                        <FileCheck class="size-6" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="font-bold text-gray-900  truncate">{{ item.nama_dokumen }}</h3>
                        <p class="text-[10px] text-gray-500 font-bold uppercase tracking-widest">{{ item.auditee.nama_auditee }}</p>
                    </div>
                </div>

                <div class="mt-4 pt-4 border-t dark:border-gray-800 flex items-center justify-between">
                    <div class="flex items-center gap-1 text-[10px] font-bold text-gray-400 uppercase">
                        <CheckCircle2 class="size-3 text-green-500" /> {{ item.status_download }}
                    </div>
                    <div class="flex items-center gap-2">
                        <a :href="getDownloadUrl(item.file_path)" target="_blank" class="p-2 rounded-lg text-blue-600 hover:bg-blue-50" title="Unduh">
                            <Download class="size-4" />
                        </a>
                        <button type="button" class="p-2 rounded-lg text-gray-400 hover:bg-red-50 hover:text-red-500" @click="openDelete(item)">
                            <Trash2 class="size-4" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Form -->
    <Modal :show="showForm" title="Unggah Laporan RTM Final" max-width="md" @close="showForm = false">
        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1">Nama Laporan <span class="text-red-500">*</span></label>
                <input v-model="form.nama_dokumen" type="text" placeholder="Contoh: Laporan RTM 2024 Final" class="w-full rounded-lg border px-3 py-2 text-sm  " />
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Auditee <span class="text-red-500">*</span></label>
                    <select v-model="form.auditee_id" class="w-full rounded-lg border px-3 py-2 text-sm  ">
                        <option value="">Pilih Auditee</option>
                        <option v-for="a in auditeeList" :key="a.id" :value="a.id">{{ a.nama_auditee }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Tahun Periode <span class="text-red-500">*</span></label>
                    <select v-model="form.tahun_periode_id" class="w-full rounded-lg border px-3 py-2 text-sm  ">
                        <option value="">Pilih Tahun</option>
                        <option v-for="t in tahunList" :key="t.id" :value="t.id">{{ t.tahun }}</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">File Laporan (PDF) <span class="text-red-500">*</span></label>
                <input type="file" accept="application/pdf" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" @input="form.file_path = ($event.target as HTMLInputElement).files?.[0] || null" />
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" class="px-4 py-2 text-sm font-medium border rounded-lg hover:bg-gray-50" @click="showForm = false">Batal</button>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700" :disabled="form.processing">Unggah Laporan</button>
            </div>
        </form>
    </Modal>

    <ConfirmDeleteModal :show="showDelete" message="Hapus laporan RTM ini?" @close="showDelete = false" @confirm="confirmDelete" />
</template>
