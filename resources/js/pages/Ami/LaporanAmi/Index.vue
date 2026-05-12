<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Edit2, Plus, Search, Trash2, FileBadge, Download, Calendar } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
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
    data: {
        data: LaporanAmi[];
        total: number;
        links: any[];
    };
    filters: { search?: string; periode_id?: string };
    periodeList: { id: number; label: string }[];
    auditeeList: { id: number; nama_auditee: string }[];
}>();

defineOptions({ layout: AppLayout });

const search = ref(props.filters.search ?? '');
const periode_id = ref(props.filters.periode_id ?? '');

let searchTimeout: any;
watch([search, periode_id], () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get('/ami/laporan-ami', { 
            search: search.value, 
            periode_id: periode_id.value 
        }, { preserveState: true, replace: true });
    }, 400);
});

// Form
const showForm = ref(false);
const editTarget = ref<LaporanAmi | null>(null);
const form = useForm({
    pengaturan_periode_id: '',
    auditee_id: '',
    file_laporan: null as File | null,
    tanggal_laporan: new Date().toISOString().split('T')[0],
    status: 'Draft',
});

const openCreate = () => {
    editTarget.value = null;
    form.reset();
    showForm.value = true;
};

const openEdit = (item: LaporanAmi) => {
    editTarget.value = item;
    form.pengaturan_periode_id = item.pengaturan_periode.id.toString();
    form.auditee_id = item.auditee.id.toString();
    form.tanggal_laporan = item.tanggal_laporan;
    form.status = item.status;
    form.file_laporan = null;
    showForm.value = true;
};

const submit = () => {
    const opts = { preserveScroll: true, onSuccess: () => { showForm.value = false; } };
    if (editTarget.value) {
        form.post(`/ami/laporan-ami/${editTarget.value.id}`, {
            ...opts,
            forceFormData: true,
            onBefore: (request: any) => { request.data._method = 'PUT'; }
        });
    } else {
        form.post('/ami/laporan-ami', opts);
    }
};

const showDelete = ref(false);
const deleteTarget = ref<LaporanAmi | null>(null);
const openDelete = (item: LaporanAmi) => { deleteTarget.value = item; showDelete.value = true; };
const confirmDelete = () => {
    if (!deleteTarget.value) return;
    router.delete(`/ami/laporan-ami/${deleteTarget.value.id}`, {
        preserveScroll: true,
        onSuccess: () => { showDelete.value = false; }
    });
};

const getDownloadUrl = (path: string) => `/storage/${path}`;
const formatDate = (d: string) => new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
</script>

<template>
    <Head title="Laporan AMI" />
    <div class="space-y-6 p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-semibold text-gray-900 ">Laporan Hasil AMI</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Arsip laporan audit final per unit kerja</p>
            </div>
            <button type="button" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700" @click="openCreate">
                <Plus class="size-4" /> Unggah Laporan
            </button>
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

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div v-if="data.data.length === 0" class="col-span-full p-12 text-center border-2 border-dashed rounded-2xl text-gray-400">
                Belum ada laporan AMI yang diunggah.
            </div>
            <div v-for="item in data.data" :key="item.id" class="p-6 rounded-2xl border bg-white dark:bg-gray-900 shadow-sm hover:shadow-md transition">
                <div class="flex items-start gap-4">
                    <div class="size-14 rounded-2xl bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center text-blue-600 shrink-0">
                        <FileBadge class="size-8" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <h3 class="font-bold text-gray-900  truncate">{{ item.auditee.nama_auditee }}</h3>
                            <span :class="item.status === 'Final' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'" class="px-2 py-0.5 rounded text-[10px] font-bold uppercase">
                                {{ item.status }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 line-clamp-1">{{ item.pengaturan_periode.lembaga_akreditasi.nama_lembaga }} ({{ item.pengaturan_periode.tahun_periode.tahun }})</p>
                        <div class="mt-3 flex items-center gap-4 text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                            <span class="flex items-center gap-1"><Calendar class="size-3" /> {{ formatDate(item.tanggal_laporan) }}</span>
                        </div>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t dark:border-gray-800 flex justify-end gap-2">
                    <a :href="getDownloadUrl(item.file_laporan)" target="_blank" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-gray-50 text-gray-600 text-xs font-bold hover:bg-gray-100  dark:text-gray-400 dark:hover:bg-gray-700 transition">
                        <Download class="size-3.5" /> Unduh PDF
                    </a>
                    <button type="button" class="p-2 rounded-lg text-gray-400 hover:bg-blue-50 hover:text-blue-600" @click="openEdit(item)">
                        <Edit2 class="size-4" />
                    </button>
                    <button type="button" class="p-2 rounded-lg text-gray-400 hover:bg-red-50 hover:text-red-500" @click="openDelete(item)">
                        <Trash2 class="size-4" />
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Form -->
    <Modal :show="showForm" :title="editTarget ? 'Edit Laporan AMI' : 'Unggah Laporan AMI'" max-width="md" @close="showForm = false">
        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1">Periode AMI <span class="text-red-500">*</span></label>
                <select v-model="form.pengaturan_periode_id" :disabled="!!editTarget" class="w-full rounded-lg border px-3 py-2 text-sm  ">
                    <option value="">Pilih Periode</option>
                    <option v-for="p in periodeList" :key="p.id" :value="p.id">{{ p.label }}</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Auditee <span class="text-red-500">*</span></label>
                <select v-model="form.auditee_id" :disabled="!!editTarget" class="w-full rounded-lg border px-3 py-2 text-sm  ">
                    <option value="">Pilih Auditee</option>
                    <option v-for="a in auditeeList" :key="a.id" :value="a.id">{{ a.nama_auditee }}</option>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Tanggal Laporan <span class="text-red-500">*</span></label>
                    <input v-model="form.tanggal_laporan" type="date" class="w-full rounded-lg border px-3 py-2 text-sm  " />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Status <span class="text-red-500">*</span></label>
                    <select v-model="form.status" class="w-full rounded-lg border px-3 py-2 text-sm  ">
                        <option value="Draft">Draft</option>
                        <option value="Final">Final</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">File Laporan (PDF) <span v-if="!editTarget" class="text-red-500">*</span></label>
                <input type="file" accept="application/pdf" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" @input="form.file_laporan = ($event.target as HTMLInputElement).files?.[0] || null" />
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" class="px-4 py-2 text-sm font-medium border rounded-lg hover:bg-gray-50" @click="showForm = false">Batal</button>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700" :disabled="form.processing">Simpan Laporan</button>
            </div>
        </form>
    </Modal>

    <ConfirmDeleteModal :show="showDelete" message="Hapus laporan AMI ini?" @close="showDelete = false" @confirm="confirmDelete" />
</template>
