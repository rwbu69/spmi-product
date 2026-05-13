<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Edit2, Plus, Search, Trash2, FileText, Download, Filter } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import ConfirmDeleteModal from '@/components/ConfirmDeleteModal.vue';
import Modal from '@/components/Modal.vue';

interface ManajemenDokumen {
    id: number;
    nama_dokumen: string;
    tahun: number | null;
    file_path: string;
    jenis_dokumen: { id: number; nama_jenis: string; kategori_dokumen: { nama_kategori: string } };
    auditee: { id: number; nama_auditee: string };
    user: { id: number; name: string };
}

interface PageProps {
    data: {
        data: ManajemenDokumen[];
        total: number;
        links: any[];
    };
    filters: { search?: string; kategori_id?: string; auditee_id?: string };
    kategoriList: { id: number; nama_kategori: string }[];
    jenisList: { id: number; nama_jenis: string; kategori_dokumen_id: number }[];
    auditeeList: { id: number; nama_auditee: string }[];
}

defineOptions({ layout: AppLayout });
const props = defineProps<PageProps>();

const search = ref(props.filters.search ?? '');
const kategori_id = ref(props.filters.kategori_id ?? '');
const auditee_id = ref(props.filters.auditee_id ?? '');

let searchTimeout: any;
watch([search, kategori_id, auditee_id], () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get('/dokumen/manajemen', { 
            search: search.value, 
            kategori_id: kategori_id.value,
            auditee_id: auditee_id.value
        }, { preserveState: true, replace: true });
    }, 400);
});

// Form
const showForm = ref(false);
const editTarget = ref<ManajemenDokumen | null>(null);
const form = useForm({
    jenis_dokumen_id: '',
    auditee_id: '',
    nama_dokumen: '',
    tahun: new Date().getFullYear(),
    file: null as File | null,
});

const openCreate = () => {
    editTarget.value = null;
    form.reset();
    showForm.value = true;
};

const openEdit = (item: ManajemenDokumen) => {
    editTarget.value = item;
    form.jenis_dokumen_id = item.jenis_dokumen.id.toString();
    form.auditee_id = item.auditee.id.toString();
    form.nama_dokumen = item.nama_dokumen;
    form.tahun = item.tahun ?? new Date().getFullYear();
    form.file = null;
    showForm.value = true;
};

const submit = () => {
    const opts = { preserveScroll: true, onSuccess: () => { showForm.value = false; } };
    if (editTarget.value) {
        // Laravel PUT with files needs _method field if using POST or just use router.post with _method
        form.post(`/dokumen/manajemen/${editTarget.value.id}`, {
            ...opts,
            forceFormData: true,
            onBefore: (request: any) => { request.data._method = 'PUT'; }
        });
    } else {
        form.post('/dokumen/manajemen', opts);
    }
};

// Delete
const showDelete = ref(false);
const deleteTarget = ref<ManajemenDokumen | null>(null);
const openDelete = (item: ManajemenDokumen) => { deleteTarget.value = item; showDelete.value = true; };
const confirmDelete = () => {
    if (!deleteTarget.value) return;
    router.delete(`/dokumen/manajemen/${deleteTarget.value.id}`, {
        preserveScroll: true,
        onSuccess: () => { showDelete.value = false; }
    });
};

const getDownloadUrl = (item: ManajemenDokumen) => `/dokumen/manajemen/${item.id}/download`;
</script>

<template>
    <Head title="Manajemen Dokumen" />
    <div class="space-y-6 p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-semibold text-gray-900 ">Manajemen Dokumen</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Arsip dokumen bukti fisik dan pendukung SPMI</p>
            </div>
            <button type="button" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700" @click="openCreate">
                <Plus class="size-4" /> Unggah Dokumen
            </button>
        </div>

        <!-- Filters -->
        <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-4 gap-4 items-center bg-gray-50 /40 p-4 rounded-xl border dark:border-gray-700">
            <div class="relative">
                <Search class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-gray-400" />
                <input v-model="search" type="text" placeholder="Nama dokumen..." class="w-full rounded-lg border border-gray-300 py-2 pr-4 pl-9 text-sm outline-none transition focus:border-blue-500  " />
            </div>
            <select v-model="kategori_id" class="rounded-lg border border-gray-300 py-2 px-3 text-sm  ">
                <option value="">Semua Kategori</option>
                <option v-for="k in kategoriList" :key="k.id" :value="k.id">{{ k.nama_kategori }}</option>
            </select>
            <select v-model="auditee_id" class="rounded-lg border border-gray-300 py-2 px-3 text-sm  ">
                <option value="">Semua Auditee</option>
                <option v-for="a in auditeeList" :key="a.id" :value="a.id">{{ a.nama_auditee }}</option>
            </select>
            <div class="hidden lg:block text-right">
                <span class="text-xs font-bold text-gray-400 uppercase">Total: {{ data.total }}</span>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4">
            <div v-if="data.data.length === 0" class="p-12 text-center border-2 border-dashed rounded-2xl text-gray-400">
                Belum ada dokumen yang diunggah.
            </div>
            <div v-for="item in data.data" :key="item.id" class="flex items-center gap-4 p-4 rounded-2xl border bg-white dark:bg-gray-900 shadow-sm hover:shadow-md transition">
                <div class="flex-shrink-0 size-12 rounded-xl bg-red-50 dark:bg-red-900/20 flex items-center justify-center text-red-600 dark:text-red-400">
                    <FileText class="size-6" />
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="font-bold text-sm text-gray-900  truncate">{{ item.nama_dokumen }}</h3>
                    <div class="flex items-center gap-2 text-xs text-gray-500">
                        <span>{{ item.jenis_dokumen.nama_jenis }}</span>
                        <span>â€¢</span>
                        <span>{{ item.auditee.nama_auditee }}</span>
                        <span>â€¢</span>
                        <span class="font-mono text-blue-600 dark:text-blue-400">{{ item.tahun }}</span>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a :href="getDownloadUrl(item)" target="_blank" class="p-2 rounded-lg text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800" title="Unduh">
                        <Download class="size-4" />
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

        <!-- Pagination -->
        <div class="flex items-center justify-between text-sm text-gray-500">
             <span>Menampilkan {{ data.data.length }} dari {{ data.total }} data</span>
             <div class="flex gap-1">
                 <template v-for="link in data.links" :key="link.label">
                     <button v-if="link.url" type="button" class="rounded px-3 py-1 transition" :class="link.active ? 'bg-blue-600 text-white' : 'hover:bg-gray-100 dark:hover:bg-gray-700'" @click="router.get(link.url, {}, { preserveState: true })" v-html="link.label" />
                 </template>
             </div>
        </div>
    </div>

    <!-- Modal Form -->
    <Modal :show="showForm" :title="editTarget ? 'Edit Dokumen' : 'Unggah Dokumen Baru'" max-width="lg" @close="showForm = false">
        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1">Nama Dokumen <span class="text-red-500">*</span></label>
                <input v-model="form.nama_dokumen" type="text" placeholder="Contoh: Laporan Kinerja 2024" class="w-full rounded-lg border px-3 py-2 text-sm  " />
                <p v-if="form.errors.nama_dokumen" class="text-xs text-red-500 mt-1">{{ form.errors.nama_dokumen }}</p>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Tahun <span class="text-red-500">*</span></label>
                    <input v-model="form.tahun" type="number" class="w-full rounded-lg border px-3 py-2 text-sm  " />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Auditee <span class="text-red-500">*</span></label>
                    <select v-model="form.auditee_id" class="w-full rounded-lg border px-3 py-2 text-sm  ">
                        <option value="">Pilih Auditee</option>
                        <option v-for="a in auditeeList" :key="a.id" :value="a.id">{{ a.nama_auditee }}</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Jenis Dokumen <span class="text-red-500">*</span></label>
                <select v-model="form.jenis_dokumen_id" class="w-full rounded-lg border px-3 py-2 text-sm  ">
                    <option value="">Pilih Jenis</option>
                    <option v-for="j in jenisList" :key="j.id" :value="j.id">[{{ j.kategori_dokumen.nama_kategori }}] {{ j.nama_jenis }}</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">File PDF <span v-if="!editTarget" class="text-red-500">*</span></label>
                <input type="file" accept="application/pdf" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" @input="form.file = ($event.target as HTMLInputElement).files?.[0] || null" />
                <p v-if="editTarget" class="text-[10px] text-gray-400 mt-1 italic">Kosongkan jika tidak ingin mengganti file.</p>
                <p v-if="form.errors.file" class="text-xs text-red-500 mt-1">{{ form.errors.file }}</p>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" class="px-4 py-2 text-sm font-medium border rounded-lg hover:bg-gray-50" @click="showForm = false">Batal</button>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700" :disabled="form.processing">
                    {{ form.processing ? 'Sedang Proses...' : 'Simpan Dokumen' }}
                </button>
            </div>
        </form>
    </Modal>

    <ConfirmDeleteModal :show="showDelete" message="Hapus dokumen ini secara permanen?" @close="showDelete = false" @confirm="confirmDelete" />
</template>

