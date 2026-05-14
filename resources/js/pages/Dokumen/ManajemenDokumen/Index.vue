<script setup lang="ts">
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { Edit2, Plus, Trash2, FileText, Download, Filter } from 'lucide-vue-next';
import { ref, watch, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import Pagination from '@/components/Pagination.vue';
import ConfirmDeleteModal from '@/components/ConfirmDeleteModal.vue';
import Modal from '@/components/Modal.vue';

interface ManajemenDokumen {
    id: number;
    nama_dokumen: string;
    tahun: number | null;
    file_path: string;
    jenis_dokumen: { id: number; nama_jenis: string; kategori_dokumen: { nama_kategori: string } };
    auditee: { id: number; nama_auditee: string } | null;
    unit_penunjang: { id: number; nama_unit: string } | null;
    user: { id: number; name: string };
}

interface AuditeeOption {
    value: string;
    label: string;
    type: 'auditee' | 'unit_penunjang';
    id: number;
    auditee_id: number | null;
    unit_penunjang_id: number | null;
}

const props = defineProps<{
    data: { data: ManajemenDokumen[]; total: number; links: any[]; };
    filters: { search?: string; kategori_id?: string; auditee_id?: string };
    kategoriList: { id: number; nama_kategori: string }[];
    jenisList: { id: number; nama_jenis: string; kategori_dokumen_id: number; kategori_dokumen: { nama_kategori: string } }[];
    auditeeList: { id: number; nama_auditee: string }[];
    auditeeOptions: AuditeeOption[];
    unitOptions: AuditeeOption[];
    userList?: { id: number; name: string }[];
}>();

defineOptions({ layout: AppLayout });
const page = usePage();
const isAdmin = computed(() => (page.props.auth as any)?.roles?.includes('Admin'));
const isAuditee = computed(() => {
    const roles = (page.props.auth as any)?.roles ?? [];
    return roles.includes('Auditee') || roles.includes('Unit Penunjang');
});

const filterKategori = ref(props.filters.kategori_id ?? '');
const filterUnit     = ref('');
const filterUser     = ref('');
const filterSearch   = ref(props.filters.search ?? '');

let t: any;
watch([filterKategori, filterSearch], () => {
    clearTimeout(t);
    t = setTimeout(() => router.get('/dokumen/manajemen', {
        search: filterSearch.value || undefined,
        kategori_id: filterKategori.value || undefined,
    }, { preserveState: true, replace: true }), 400);
});

const penerimaValue = ref('');
const selectedKategori = ref('');
const form = useForm({
    jenis_dokumen_id:  '',
    penerima_type:     'auditee' as 'auditee' | 'unit_penunjang',
    auditee_id:        '' as string | number,
    unit_penunjang_id: '' as string | number,
    nama_dokumen:      '',
    tahun:             new Date().getFullYear(),
    file:              null as File | null,
});

// When kategori changes in form, reset jenis selection
watch(selectedKategori, () => { form.jenis_dokumen_id = ''; });

// Filtered jenis list based on selected kategori in form
const filteredJenisList = computed(() => {
    if (!selectedKategori.value) return props.jenisList;
    return props.jenisList.filter(j => j.kategori_dokumen_id.toString() === selectedKategori.value.toString());
});

watch(penerimaValue, (val) => {
    if (!val) { form.penerima_type = 'auditee'; form.auditee_id = ''; form.unit_penunjang_id = ''; return; }
    if (val.startsWith('auditee_')) { form.penerima_type = 'auditee'; form.auditee_id = val.replace('auditee_', ''); form.unit_penunjang_id = ''; }
    else if (val.startsWith('unit_')) { form.penerima_type = 'unit_penunjang'; form.unit_penunjang_id = val.replace('unit_', ''); form.auditee_id = ''; }
});

const showForm   = ref(false);
const editTarget = ref<ManajemenDokumen | null>(null);

const openCreate = () => { editTarget.value = null; form.reset(); penerimaValue.value = ''; selectedKategori.value = ''; showForm.value = true; };
const openEdit   = (item: ManajemenDokumen) => {
    editTarget.value = item;
    form.jenis_dokumen_id = item.jenis_dokumen.id.toString();
    form.nama_dokumen = item.nama_dokumen;
    form.tahun = item.tahun ?? new Date().getFullYear();
    form.file = null;
    selectedKategori.value = item.jenis_dokumen.kategori_dokumen_id?.toString() ?? '';
    penerimaValue.value = item.auditee ? 'auditee_' + item.auditee.id : item.unit_penunjang ? 'unit_' + item.unit_penunjang.id : '';
    showForm.value = true;
};

const submit = () => {
    const opts = { preserveScroll: true, onSuccess: () => { showForm.value = false; } };
    editTarget.value
        ? form.post(`/dokumen/manajemen/${editTarget.value.id}`, { ...opts, forceFormData: true, onBefore: (r: any) => { r.data._method = 'PUT'; } })
        : form.post('/dokumen/manajemen', opts);
};

const showDelete   = ref(false);
const deleteTarget = ref<ManajemenDokumen | null>(null);
const confirmDelete = () => {
    if (!deleteTarget.value) return;
    router.delete(`/dokumen/manajemen/${deleteTarget.value.id}`, { preserveScroll: true, onSuccess: () => { showDelete.value = false; } });
};

const getDownloadUrl = (item: ManajemenDokumen) => `/dokumen/manajemen/${item.id}/download`;
const getOwnerName   = (item: ManajemenDokumen) => item.auditee?.nama_auditee ?? item.unit_penunjang?.nama_unit ?? '-';

// Client-side filter by unit/user (since not server-filtered)
const filteredData = computed(() => {
    let rows = props.data.data;
    if (filterUnit.value) rows = rows.filter(r => getOwnerName(r) === filterUnit.value);
    if (filterUser.value) rows = rows.filter(r => r.user.name === filterUser.value);
    return rows;
});

const allOwners = computed(() => {
    const names = new Set(props.data.data.map(getOwnerName));
    return [...names];
});
const allUsers = computed(() => {
    const names = new Set(props.data.data.map(r => r.user.name));
    return [...names];
});
</script>

<template>
    <Head title="Manajemen Dokumen" />
    <div class="space-y-6 p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-semibold text-gray-900">{{ isAuditee ? 'Daftar Dokumen' : 'Manajemen Dokumen' }}</h1>
                <p class="mt-1 text-sm text-gray-500">Arsip dokumen bukti fisik dan pendukung SPMI</p>
            </div>
            <button type="button" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700" @click="openCreate">
                <Plus class="size-4" /> {{ isAuditee ? 'Tambah' : 'Unggah Dokumen' }}
            </button>
        </div>

        <!-- Filters -->
        <div class="rounded-xl border bg-white p-4 shadow-sm space-y-3">
            <p class="flex items-center gap-1.5 text-sm font-semibold text-gray-700"><Filter class="size-4 text-gray-400" /> Filter Data</p>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500 uppercase">Filter Kategori</label>
                    <select v-model="filterKategori" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                        <option value="">-- SEMUA --</option>
                        <option v-for="k in kategoriList" :key="k.id" :value="k.id">{{ k.nama_kategori }}</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500 uppercase">Filter Unit Pengguna</label>
                    <select v-model="filterUnit" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                        <option value="">-- SEMUA --</option>
                        <option v-for="n in allOwners" :key="n" :value="n">{{ n }}</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500 uppercase">Filter User Pengunggah</label>
                    <select v-model="filterUser" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                        <option value="">-- SEMUA --</option>
                        <option v-for="n in allUsers" :key="n" :value="n">{{ n }}</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-hidden rounded-xl border bg-white shadow-sm">
            <table class="w-full text-sm text-left">
                <thead class="bg-blue-50 border-b border-blue-100">
                    <tr>
                        <th class="px-4 py-3 font-medium text-gray-600">No</th>
                        <th class="px-4 py-3 font-medium text-gray-600">Aksi</th>
                        <th class="px-4 py-3 font-medium text-gray-600">Nama Dokumen</th>
                        <th class="px-4 py-3 font-medium text-gray-600">Auditee</th>
                        <th class="px-4 py-3 font-medium text-gray-600">Tahun</th>
                        <th class="px-4 py-3 font-medium text-gray-600">Kategori Dokumen</th>
                        <th class="px-4 py-3 font-medium text-gray-600">Jenis Dokumen</th>
                        <th class="px-4 py-3 font-medium text-gray-600">Unit Pengunggah</th>
                        <th class="px-4 py-3 font-medium text-gray-600">User Pengunggah</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-if="filteredData.length === 0">
                        <td colspan="9" class="px-4 py-12 text-center text-gray-400 italic">Belum ada dokumen.</td>
                    </tr>
                    <tr v-for="(item, idx) in filteredData" :key="item.id" class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 text-gray-500">{{ idx + 1 }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-1">
                                <a :href="getDownloadUrl(item)" target="_blank" class="p-1.5 rounded-lg text-blue-600 hover:bg-blue-50" title="Unduh"><Download class="size-4" /></a>
                                <button v-if="isAdmin" type="button" class="p-1.5 rounded-lg text-gray-400 hover:bg-blue-50 hover:text-blue-600" @click="openEdit(item)"><Edit2 class="size-4" /></button>
                                <button v-if="isAdmin" type="button" class="p-1.5 rounded-lg text-gray-400 hover:bg-red-50 hover:text-red-500" @click="() => { deleteTarget = item; showDelete = true; }"><Trash2 class="size-4" /></button>
                            </div>
                        </td>
                        <td class="px-4 py-3 font-medium text-gray-900 text-xs max-w-[180px]">{{ item.nama_dokumen }}</td>
                        <td class="px-4 py-3 text-xs text-gray-600">{{ getOwnerName(item) }}</td>
                        <td class="px-4 py-3 font-mono text-blue-600 font-bold">{{ item.tahun ?? '-' }}</td>
                        <td class="px-4 py-3 text-xs text-gray-600">{{ item.jenis_dokumen.kategori_dokumen?.nama_kategori ?? '-' }}</td>
                        <td class="px-4 py-3 text-xs text-gray-600">{{ item.jenis_dokumen.nama_jenis }}</td>
                        <td class="px-4 py-3 text-xs text-gray-600">{{ getOwnerName(item) }}</td>
                        <td class="px-4 py-3 text-xs text-gray-600">{{ item.user?.name ?? '-' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Pagination :links="data.links" :total="data.total" />
    </div>

    <Modal :show="showForm" :title="editTarget ? 'Edit Dokumen' : 'Tambah Data Dokumen'" max-width="md" @close="showForm = false">
        <form @submit.prevent="submit" class="space-y-0">
            <!-- Kategori Dokumen -->
            <div class="grid grid-cols-[160px_1fr] items-center gap-x-4 gap-y-3 py-2 border-b">
                <label class="text-sm text-gray-600 text-right">Kategori Dokumen</label>
                <select v-model="selectedKategori" class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm focus:border-blue-500 focus:outline-none">
                    <option value="">-- PILIH --</option>
                    <option v-for="k in kategoriList" :key="k.id" :value="k.id">{{ k.nama_kategori }}</option>
                </select>
            </div>
            <!-- Jenis Dokumen -->
            <div class="grid grid-cols-[160px_1fr] items-center gap-x-4 gap-y-3 py-2 border-b">
                <label class="text-sm text-gray-600 text-right">Jenis Dokumen</label>
                <select v-model="form.jenis_dokumen_id" class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm focus:border-blue-500 focus:outline-none">
                    <option value="">PILIH</option>
                    <option v-for="j in filteredJenisList" :key="j.id" :value="j.id">{{ j.nama_jenis }}</option>
                </select>
            </div>
            <!-- Auditee / Unit — Admin only -->
            <div v-if="!isAuditee" class="grid grid-cols-[160px_1fr] items-center gap-x-4 gap-y-3 py-2 border-b">
                <label class="text-sm text-gray-600 text-right">Pemilik Dokumen</label>
                <select v-model="penerimaValue" class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm focus:border-blue-500 focus:outline-none">
                    <option value="">-- Pilih --</option>
                    <optgroup label="Auditee Pusat">
                        <option v-for="a in auditeeOptions" :key="a.value" :value="a.value">{{ a.label }}</option>
                    </optgroup>
                    <optgroup label="Unit Penunjang">
                        <option v-for="u in unitOptions" :key="u.value" :value="u.value">{{ u.label }}</option>
                    </optgroup>
                </select>
            </div>
            <!-- Nama Dokumen -->
            <div class="grid grid-cols-[160px_1fr] items-center gap-x-4 gap-y-3 py-2 border-b">
                <label class="text-sm text-gray-600 text-right">Nama Dokumen <span class="text-red-500">*</span></label>
                <div class="relative">
                    <input v-model="form.nama_dokumen" type="text" class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm focus:border-blue-500 focus:outline-none pr-5" placeholder="" />
                    <span class="absolute right-1.5 top-1/2 -translate-y-1/2 text-red-500 text-xs">*</span>
                </div>
            </div>
            <!-- Tahun -->
            <div class="grid grid-cols-[160px_1fr] items-center gap-x-4 gap-y-3 py-2 border-b">
                <label class="text-sm text-gray-600 text-right">Tahun</label>
                <div class="relative">
                    <input v-model="form.tahun" type="number" class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm focus:border-blue-500 focus:outline-none pr-5" />
                    <span class="absolute right-1.5 top-1/2 -translate-y-1/2 text-red-500 text-xs">*</span>
                </div>
            </div>
            <!-- File -->
            <div class="grid grid-cols-[160px_1fr] items-start gap-x-4 gap-y-3 py-2">
                <label class="text-sm text-gray-600 text-right pt-1.5">File</label>
                <div>
                    <input
                        type="file"
                        accept="application/pdf"
                        class="block w-full text-sm text-gray-500
                            file:mr-3 file:py-1 file:px-3
                            file:rounded file:border file:border-gray-300
                            file:text-sm file:font-medium file:bg-white file:text-gray-700
                            hover:file:bg-gray-50 cursor-pointer"
                        @input="form.file = ($event.target as HTMLInputElement).files?.[0] || null"
                    />
                    <p class="mt-1 text-[11px] text-gray-500">* file berupa .PDF, ukuran maksimal 7 MB</p>
                    <p v-if="editTarget" class="text-[10px] text-gray-400 mt-0.5 italic">Kosongkan jika tidak ingin mengganti file.</p>
                </div>
            </div>
            <!-- Footer -->
            <div class="flex justify-end gap-2 pt-4 border-t mt-2">
                <button type="button" class="px-4 py-1.5 text-sm text-white bg-red-500 rounded hover:bg-red-600" @click="showForm = false">Batal</button>
                <button type="submit" class="px-4 py-1.5 text-sm text-white bg-blue-600 rounded hover:bg-blue-700" :disabled="form.processing">Simpan</button>
            </div>
        </form>
    </Modal>

    <ConfirmDeleteModal :show="showDelete" message="Hapus dokumen ini secara permanen?" @close="showDelete = false" @confirm="confirmDelete" />
</template>
