<script setup lang="ts">
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { Edit2, Plus, Trash2, Download, Filter, ChevronDown, Check } from 'lucide-vue-next';
import { ref, watch, computed, onMounted, onUnmounted } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import Pagination from '@/components/Pagination.vue';
import ConfirmDeleteModal from '@/components/ConfirmDeleteModal.vue';
import Modal from '@/components/Modal.vue';

interface ManajemenDokumen {
    id: number;
    nama_dokumen: string;
    tahun: number | null;
    file_path: string;
    jenis_dokumen: { id: number; nama_jenis: string; kategori_dokumen_id?: number; kategori_dokumen: { nama_kategori: string } };
    auditee: { id: number; nama_auditee: string } | null;
    unit_penunjang: { id: number; nama_unit: string } | null;
    user: { id: number; name: string };
}

interface AuditeeOption {
    value: string;
    label: string;
    type: 'auditee' | 'unit_penunjang' | 'semua';
    id: number | null;
    auditee_id: number | null;
    unit_penunjang_id: number | null;
}

const props = defineProps<{
    data: { data: ManajemenDokumen[]; total: number; links: any[]; current_page: number; per_page: number; };
    filters: { search?: string; kategori_id?: string; auditee_id?: string; per_page?: string };
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
const filterUser     = ref('');
const perPage        = ref(props.filters.per_page ?? '10');

let t: any;
watch([filterKategori, perPage], () => {
    clearTimeout(t);
    t = setTimeout(() => router.get('/dokumen/manajemen', {
        kategori_id: filterKategori.value || undefined,
        per_page: perPage.value || undefined,
    }, { preserveState: true, replace: true }), 400);
});

const selectedKategori = ref('');
const form = useForm({
    jenis_dokumen_id:  '',
    penerima_type:     'semua' as 'auditee' | 'unit_penunjang' | 'semua',
    auditee_id:        '' as string | number,
    unit_penunjang_id: '' as string | number,
    nama_dokumen:      '',
    tahun:             new Date().getFullYear(),
    file:              null as File | null,
});

watch(selectedKategori, () => { form.jenis_dokumen_id = ''; });

const filteredJenisList = computed(() => {
    if (!selectedKategori.value) return props.jenisList;
    return props.jenisList.filter(j => j.kategori_dokumen_id.toString() === selectedKategori.value.toString());
});

// Searchable Select Logic for Auditee
const showAuditeeDropdown = ref(false);
const auditeeSearch = ref('');
const selectedAuditeeLabel = ref('-- PILIH SEMUA --');
const auditeeDropdownRef = ref<HTMLElement | null>(null);

const allAuditeeOptions = computed<AuditeeOption[]>(() => {
    return [
        { value: 'semua', label: '-- PILIH SEMUA --', type: 'semua', id: null, auditee_id: null, unit_penunjang_id: null },
        ...props.auditeeOptions,
        ...props.unitOptions
    ];
});

const filteredAuditeeOptions = computed(() => {
    if (!auditeeSearch.value) return allAuditeeOptions.value;
    const lowerSearch = auditeeSearch.value.toLowerCase();
    return allAuditeeOptions.value.filter(opt => opt.label.toLowerCase().includes(lowerSearch));
});

const selectAuditee = (opt: AuditeeOption) => {
    selectedAuditeeLabel.value = opt.label;
    form.penerima_type = opt.type;
    form.auditee_id = opt.auditee_id ?? '';
    form.unit_penunjang_id = opt.unit_penunjang_id ?? '';
    showAuditeeDropdown.value = false;
    auditeeSearch.value = '';
};

const closeDropdownOnOutsideClick = (e: MouseEvent) => {
    if (auditeeDropdownRef.value && !auditeeDropdownRef.value.contains(e.target as Node)) {
        showAuditeeDropdown.value = false;
    }
};

onMounted(() => document.addEventListener('click', closeDropdownOnOutsideClick));
onUnmounted(() => document.removeEventListener('click', closeDropdownOnOutsideClick));

const showForm   = ref(false);
const editTarget = ref<ManajemenDokumen | null>(null);

const openCreate = () => { 
    editTarget.value = null; 
    form.reset(); 
    selectedKategori.value = ''; 
    selectedAuditeeLabel.value = '-- PILIH SEMUA --';
    form.penerima_type = 'semua';
    form.auditee_id = '';
    form.unit_penunjang_id = '';
    showForm.value = true; 
};

const openEdit   = (item: ManajemenDokumen) => {
    editTarget.value = item;
    form.jenis_dokumen_id = item.jenis_dokumen.id.toString();
    form.nama_dokumen = item.nama_dokumen;
    form.tahun = item.tahun ?? new Date().getFullYear();
    form.file = null;
    selectedKategori.value = item.jenis_dokumen.kategori_dokumen_id?.toString() ?? '';
    
    if (item.auditee) {
        form.penerima_type = 'auditee';
        form.auditee_id = item.auditee.id;
        selectedAuditeeLabel.value = item.auditee.nama_auditee;
    } else if (item.unit_penunjang) {
        form.penerima_type = 'unit_penunjang';
        form.unit_penunjang_id = item.unit_penunjang.id;
        selectedAuditeeLabel.value = item.unit_penunjang.nama_unit;
    } else {
        form.penerima_type = 'semua';
        selectedAuditeeLabel.value = '-- PILIH SEMUA --';
    }
    
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

const filteredData = computed(() => {
    let rows = props.data.data;
    if (filterUser.value) rows = rows.filter(r => r.user.name === filterUser.value);
    return rows;
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
        </div>

        <div class="rounded-xl border bg-white p-4 shadow-sm space-y-3">
            <p class="flex items-center gap-1.5 text-sm font-semibold text-gray-700"><Filter class="size-4 text-gray-400" /> Filter Kategori</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <select v-model="filterKategori" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                        <option value="">-- SEMUA --</option>
                        <option v-for="k in kategoriList" :key="k.id" :value="k.id">{{ k.nama_kategori }}</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <button type="button" class="inline-flex items-center gap-2 rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700" @click="openCreate">
                <Plus class="size-4" /> Tambah
            </button>
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2 text-sm text-gray-600">
                    <span class="text-xs font-semibold text-gray-700 uppercase">Filter User Pengunggah</span>
                    <select v-model="filterUser" class="rounded border border-gray-300 px-2 py-1.5 text-sm w-32">
                        <option value="">-- SEMUA --</option>
                        <option v-for="n in allUsers" :key="n" :value="n">{{ n }}</option>
                    </select>
                </div>
                <div class="flex items-center gap-2 text-sm text-gray-600">
                    <span>Per page:</span>
                    <select v-model="perPage" class="rounded border border-gray-300 px-2 py-1.5 text-sm">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border bg-white shadow-sm">
            <table class="w-full text-sm text-left">
                <thead class="bg-blue-50 border-b border-blue-100">
                    <tr>
                        <th class="px-4 py-3 font-medium text-gray-600 w-12">No</th>
                        <th class="px-4 py-3 font-medium text-gray-600 w-24">Aksi</th>
                        <th class="px-4 py-3 font-medium text-gray-600">Nama Dokumen</th>
                        <th class="px-4 py-3 font-medium text-gray-600">Tahun</th>
                        <th class="px-4 py-3 font-medium text-gray-600">Kategori Dokumen</th>
                        <th class="px-4 py-3 font-medium text-gray-600">Jenis Dokumen</th>
                        <th class="px-4 py-3 font-medium text-gray-600">Unit Pengunggah</th>
                        <th class="px-4 py-3 font-medium text-gray-600">User Pengunggah</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-if="filteredData.length === 0">
                        <td colspan="8" class="px-4 py-12 text-center text-gray-400 italic">No data available in table</td>
                    </tr>
                    <tr v-for="(item, idx) in filteredData" :key="item.id" class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 text-gray-500">{{ (data.current_page - 1) * data.per_page + idx + 1 }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-1">
                                <a :href="getDownloadUrl(item)" target="_blank" class="p-1.5 rounded-lg text-blue-600 hover:bg-blue-50" title="Unduh"><Download class="size-4" /></a>
                                <button v-if="isAdmin" type="button" class="p-1.5 rounded-lg text-gray-400 hover:bg-blue-50 hover:text-blue-600" @click="openEdit(item)"><Edit2 class="size-4" /></button>
                                <button v-if="isAdmin" type="button" class="p-1.5 rounded-lg text-gray-400 hover:bg-red-50 hover:text-red-500" @click="() => { deleteTarget = item; showDelete = true; }"><Trash2 class="size-4" /></button>
                            </div>
                        </td>
                        <td class="px-4 py-3 font-medium text-gray-900 text-xs">{{ item.nama_dokumen }}</td>
                        <td class="px-4 py-3 font-mono text-blue-600 font-bold">{{ item.tahun ?? '-' }}</td>
                        <td class="px-4 py-3 text-xs text-gray-600">{{ item.jenis_dokumen.kategori_dokumen?.nama_kategori ?? '-' }}</td>
                        <td class="px-4 py-3 text-xs text-gray-600">{{ item.jenis_dokumen.nama_jenis }}</td>
                        <td class="px-4 py-3 text-xs text-gray-600">{{ getOwnerName(item) }}</td>
                        <td class="px-4 py-3 text-xs text-gray-600">{{ item.user?.name ?? '-' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-2 text-sm text-gray-500">
            <span>Showing {{ data.data.length === 0 ? 0 : (data.current_page - 1) * data.per_page + 1 }} to {{ Math.min(data.current_page * data.per_page, data.total) }} of {{ data.total }} entries</span>
        </div>
        <Pagination :links="data.links" :total="data.total" />
    </div>

    <Modal :show="showForm" :title="editTarget ? 'Edit Dokumen' : 'Tambah Data Dokumen'" max-width="lg" @close="showForm = false">
        <form @submit.prevent="submit" class="space-y-0 pb-2">
            <div class="grid grid-cols-[140px_1fr] items-center gap-x-4 gap-y-3 py-3 border-b">
                <label class="text-sm font-medium text-gray-600 text-right">Kategori Dokumen</label>
                <select v-model="selectedKategori" class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                    <option value="">-- PILIH --</option>
                    <option v-for="k in kategoriList" :key="k.id" :value="k.id">{{ k.nama_kategori }}</option>
                </select>
            </div>
            
            <div class="grid grid-cols-[140px_1fr] items-center gap-x-4 gap-y-3 py-3 border-b">
                <label class="text-sm font-medium text-gray-600 text-right">Jenis Dokumen</label>
                <select v-model="form.jenis_dokumen_id" class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                    <option value="">-- PILIH --</option>
                    <option v-for="j in filteredJenisList" :key="j.id" :value="j.id">{{ j.nama_jenis }}</option>
                </select>
            </div>
            
            <div class="grid grid-cols-[140px_1fr] items-start gap-x-4 gap-y-1 py-3 border-b">
                <label class="text-sm font-medium text-gray-600 text-right mt-2">Auditee</label>
                <div class="relative w-full" ref="auditeeDropdownRef">
                    <div 
                        class="flex items-center justify-between w-full rounded border border-gray-300 px-3 py-2 text-sm cursor-pointer bg-white"
                        @click="showAuditeeDropdown = !showAuditeeDropdown"
                    >
                        <span class="truncate">{{ selectedAuditeeLabel }}</span>
                        <ChevronDown class="size-4 text-gray-400" />
                    </div>
                    
                    <div v-if="showAuditeeDropdown" class="absolute z-50 mt-1 w-full rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5">
                        <div class="p-2 border-b">
                            <input 
                                v-model="auditeeSearch" 
                                type="text" 
                                class="w-full rounded-md border border-gray-300 px-3 py-1.5 text-sm focus:border-blue-500 focus:outline-none" 
                                placeholder="Cari auditee..." 
                                @click.stop 
                            />
                        </div>
                        <ul class="max-h-60 overflow-auto py-1 text-sm">
                            <li 
                                v-for="opt in filteredAuditeeOptions" 
                                :key="opt.value"
                                class="px-3 py-2 hover:bg-blue-50 cursor-pointer flex items-center justify-between"
                                :class="selectedAuditeeLabel === opt.label ? 'bg-blue-50 text-blue-600' : 'text-gray-700'"
                                @click="selectAuditee(opt)"
                            >
                                <span class="truncate" :class="opt.type === 'semua' ? 'font-bold' : ''">{{ opt.label }}</span>
                                <Check v-if="selectedAuditeeLabel === opt.label" class="size-4" />
                            </li>
                            <li v-if="filteredAuditeeOptions.length === 0" class="px-3 py-4 text-center text-gray-500">
                                Tidak ditemukan
                            </li>
                        </ul>
                    </div>
                    <p class="mt-1 text-[11px] text-gray-500">*Pilih SEMUA untuk dokumen yang diploting ke semua Auditee</p>
                </div>
            </div>
            
            <div class="grid grid-cols-[140px_1fr] items-center gap-x-4 gap-y-3 py-3 border-b">
                <label class="text-sm font-medium text-gray-600 text-right">Nama Dokumen</label>
                <div class="relative">
                    <input v-model="form.nama_dokumen" type="text" class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none pr-6" />
                    <span class="absolute right-2.5 top-1/2 -translate-y-1/2 text-red-500 text-sm">*</span>
                </div>
            </div>
            
            <div class="grid grid-cols-[140px_1fr] items-center gap-x-4 gap-y-3 py-3 border-b">
                <label class="text-sm font-medium text-gray-600 text-right">Tahun</label>
                <div class="relative w-32">
                    <input v-model="form.tahun" type="number" class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none pr-6" />
                    <span class="absolute right-2.5 top-1/2 -translate-y-1/2 text-red-500 text-sm">*</span>
                </div>
            </div>
            
            <div class="grid grid-cols-[140px_1fr] items-start gap-x-4 gap-y-1 py-3">
                <label class="text-sm font-medium text-gray-600 text-right mt-1.5">File</label>
                <div class="relative">
                    <input
                        type="file"
                        accept="application/pdf"
                        class="block w-full text-sm text-gray-500
                            file:mr-3 file:py-1.5 file:px-3
                            file:rounded file:border file:border-gray-300
                            file:text-sm file:font-medium file:bg-white file:text-gray-700
                            hover:file:bg-gray-50 cursor-pointer"
                        @input="form.file = ($event.target as HTMLInputElement).files?.[0] || null"
                    />
                    <span class="absolute -right-4 top-1.5 text-red-500 text-sm">*</span>
                    <p class="mt-1.5 text-[11px] text-gray-500">* file berupa .PDF, ukuran maksimal 7 MB</p>
                    <p v-if="editTarget" class="text-[10px] text-gray-400 mt-0.5 italic">Kosongkan jika tidak ingin mengganti file.</p>
                </div>
            </div>
            
            <div class="flex justify-end gap-2 pt-6 pb-2 border-t mt-2">
                <button type="submit" class="px-5 py-2 text-sm font-medium text-white bg-blue-600 rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1" :disabled="form.processing">Simpan</button>
                <button type="button" class="px-5 py-2 text-sm font-medium text-white bg-red-500 rounded hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1" @click="showForm = false">Batal</button>
            </div>
        </form>
    </Modal>

    <ConfirmDeleteModal :show="showDelete" message="Hapus dokumen ini secara permanen?" @close="showDelete = false" @confirm="confirmDelete" />
</template>
