<script setup lang="ts">
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { Edit2, Plus, Search, Trash2, FileBadge, Download, Calendar } from 'lucide-vue-next';
import { ref, watch, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import ConfirmDeleteModal from '@/components/ConfirmDeleteModal.vue';
import Modal from '@/components/Modal.vue';
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
    data: { data: LaporanAmi[]; total: number; links: any[]; };
    filters: { search?: string; periode_id?: string };
    periodeList: { id: number; label: string }[];
    auditeeList: { id: number; nama_auditee: string }[];
    currentAuditee?: { nama_auditee: string } | null;
}>();

defineOptions({ layout: AppLayout });
const page = usePage();
const isAdmin = computed(() => (page.props.auth as any)?.roles?.includes('Admin'));
const isAuditee = computed(() => {
    const roles = (page.props.auth as any)?.roles ?? [];
    return roles.includes('Auditee') || roles.includes('Unit Penunjang');
});

const search    = ref(props.filters.search ?? '');
const periode_id = ref(props.filters.periode_id ?? '');

let t: any;
watch([search, periode_id], () => {
    clearTimeout(t);
    t = setTimeout(() => router.get('/ami/laporan-ami', { search: search.value, periode_id: periode_id.value }, { preserveState: true, replace: true }), 400);
});

const showForm = ref(false);
const editTarget = ref<LaporanAmi | null>(null);
const form = useForm({
    pengaturan_periode_id: '',
    auditee_id: '',
    file_laporan: null as File | null,
    tanggal_laporan: new Date().toISOString().split('T')[0],
    status: 'Draft',
});

const openCreate = () => { editTarget.value = null; form.reset(); showForm.value = true; };
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
        form.post(`/ami/laporan-ami/${editTarget.value.id}`, { ...opts, forceFormData: true, onBefore: (r: any) => { r.data._method = 'PUT'; } });
    } else {
        form.post('/ami/laporan-ami', opts);
    }
};

const showDelete = ref(false);
const deleteTarget = ref<LaporanAmi | null>(null);
const confirmDelete = () => {
    if (!deleteTarget.value) return;
    router.delete(`/ami/laporan-ami/${deleteTarget.value.id}`, { preserveScroll: true, onSuccess: () => { showDelete.value = false; } });
};

const getDownloadUrl = (item: LaporanAmi) => `/ami/laporan-ami/${item.id}/download`;
const formatDate = (d: string) => new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
</script>

<template>
    <Head title="Laporan AMI" />
    <div class="space-y-6 p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-semibold text-gray-900">{{ isAuditee ? 'Dokumen Laporan AMI' : 'Laporan Hasil AMI' }}</h1>
                <p class="mt-1 text-sm text-gray-500">Arsip laporan audit final per unit kerja</p>
            </div>
            <button v-if="isAdmin" type="button" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700" @click="openCreate">
                <Plus class="size-4" /> Unggah Laporan
            </button>
        </div>

        <!-- Auditee View: show current auditee header -->
        <div v-if="isAuditee && currentAuditee" class="rounded-xl border bg-white shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b bg-gray-50/40">
                <div class="flex items-center gap-4 text-sm">
                    <span class="font-semibold text-gray-600 w-24">Auditee</span>
                    <span class="text-gray-900 font-medium">{{ currentAuditee.nama_auditee }}</span>
                </div>
            </div>
        </div>

        <!-- Filters (Admin/Auditor only) -->
        <div v-if="!isAuditee" class="flex flex-wrap gap-4 items-center">
            <div class="relative w-full max-w-xs">
                <Search class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-gray-400" />
                <input v-model="search" type="text" placeholder="Cari auditee..." class="w-full rounded-lg border border-gray-300 py-2 pr-4 pl-9 text-sm outline-none focus:border-blue-500" />
            </div>
            <select v-model="periode_id" class="rounded-lg border border-gray-300 py-2 px-3 text-sm">
                <option value="">Semua Periode</option>
                <option v-for="p in periodeList" :key="p.id" :value="p.id">{{ p.label }}</option>
            </select>
        </div>

        <!-- Table -->
        <div class="overflow-hidden rounded-xl border bg-white shadow-sm">
            <table class="w-full text-sm text-left">
                <thead class="bg-blue-50 border-b border-blue-100">
                    <tr>
                        <th class="px-4 py-3 font-medium text-gray-600">No</th>
                        <th v-if="!isAuditee" class="px-4 py-3 font-medium text-gray-600">Auditee</th>
                        <th class="px-4 py-3 font-medium text-gray-600">Jenis Laporan AMI</th>
                        <th class="px-4 py-3 font-medium text-gray-600">Tahun Laporan AMI</th>
                        <th class="px-4 py-3 font-medium text-gray-600">Status</th>
                        <th class="px-4 py-3 font-medium text-gray-600">Download</th>
                        <th v-if="isAdmin" class="px-4 py-3 text-center font-medium text-gray-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-if="data.data.length === 0">
                        <td :colspan="isAdmin ? (isAuditee ? 6 : 7) : (isAuditee ? 5 : 6)" class="px-4 py-12 text-center text-gray-400 italic">Belum ada laporan AMI.</td>
                    </tr>
                    <tr v-for="(item, idx) in data.data" :key="item.id" class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 text-gray-500">{{ idx + 1 }}</td>
                        <td v-if="!isAuditee" class="px-4 py-3 font-medium text-gray-900 text-xs">{{ item.auditee.nama_auditee }}</td>
                        <td class="px-4 py-3 text-xs text-gray-700">
                            {{ item.pengaturan_periode.lembaga_akreditasi.nama_lembaga }}
                            <div class="flex items-center gap-1 text-[10px] text-gray-400 mt-0.5">
                                <Calendar class="size-3" /> {{ formatDate(item.tanggal_laporan) }}
                            </div>
                        </td>
                        <td class="px-4 py-3 font-mono text-blue-600 font-bold">{{ item.pengaturan_periode.tahun_periode.tahun }}</td>
                        <td class="px-4 py-3">
                            <span :class="item.status === 'Final' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'" class="px-2 py-0.5 rounded text-[10px] font-bold uppercase">{{ item.status }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <a :href="getDownloadUrl(item)" target="_blank" class="inline-flex items-center gap-1.5 px-2 py-1 rounded-md bg-gray-50 text-gray-600 text-xs font-medium hover:bg-gray-100 transition">
                                <Download class="size-3.5" /> Unduh PDF
                            </a>
                        </td>
                        <td v-if="isAdmin" class="px-4 py-3">
                            <div class="flex items-center justify-center gap-1">
                                <button type="button" class="p-1.5 rounded-lg text-gray-400 hover:bg-blue-50 hover:text-blue-600" @click="openEdit(item)"><Edit2 class="size-4" /></button>
                                <button type="button" class="p-1.5 rounded-lg text-gray-400 hover:bg-red-50 hover:text-red-500" @click="() => { deleteTarget = item; showDelete = true; }"><Trash2 class="size-4" /></button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Pagination :links="data.links" :total="data.total" />
    </div>

    <Modal :show="showForm" :title="editTarget ? 'Edit Laporan AMI' : 'Unggah Laporan AMI'" max-width="md" @close="showForm = false">
        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1">Periode AMI <span class="text-red-500">*</span></label>
                <select v-model="form.pengaturan_periode_id" :disabled="!!editTarget" class="w-full rounded-lg border px-3 py-2 text-sm">
                    <option value="">Pilih Periode</option>
                    <option v-for="p in periodeList" :key="p.id" :value="p.id">{{ p.label }}</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Auditee <span class="text-red-500">*</span></label>
                <select v-model="form.auditee_id" :disabled="!!editTarget" class="w-full rounded-lg border px-3 py-2 text-sm">
                    <option value="">Pilih Auditee</option>
                    <option v-for="a in auditeeList" :key="a.id" :value="a.id">{{ a.nama_auditee }}</option>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Tanggal Laporan <span class="text-red-500">*</span></label>
                    <input v-model="form.tanggal_laporan" type="date" class="w-full rounded-lg border px-3 py-2 text-sm" />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Status <span class="text-red-500">*</span></label>
                    <select v-model="form.status" class="w-full rounded-lg border px-3 py-2 text-sm">
                        <option value="Draft">Draft</option>
                        <option value="Final">Final</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">File Laporan (PDF) <span v-if="!editTarget" class="text-red-500">*</span></label>
                <input type="file" accept="application/pdf" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" @input="form.file_laporan = ($event.target as HTMLInputElement).files?.[0] || null" />
                <p v-if="editTarget" class="text-[10px] text-gray-400 mt-1">Kosongkan jika tidak ingin mengganti file.</p>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" class="px-4 py-2 text-sm border rounded-lg hover:bg-gray-50" @click="showForm = false">Batal</button>
                <button type="submit" class="px-4 py-2 text-sm text-white bg-blue-600 rounded-lg hover:bg-blue-700" :disabled="form.processing">Simpan Laporan</button>
            </div>
        </form>
    </Modal>
    <ConfirmDeleteModal :show="showDelete" message="Hapus laporan AMI ini?" @close="showDelete = false" @confirm="confirmDelete" />
</template>
