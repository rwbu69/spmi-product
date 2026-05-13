<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Edit2, Plus, Search, Trash2, CheckCircle, Clock } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import ConfirmDeleteModal from '@/components/ConfirmDeleteModal.vue';
import Modal from '@/components/Modal.vue';

interface RekapTemuan {
    id: number;
    jumlah_temuan: number;
    status_approval: 'Pending' | 'Approved';
    tanggal_approval: string | null;
    approved_by: string | null;
    auditee: { id: number; nama_auditee: string };
    pengaturan_periode: { 
        id: number; 
        tahun_periode: { tahun: number };
        lembaga_akreditasi: { nama_lembaga: string };
    };
}

// Define props with explicit structure to avoid compiler issues
const props = defineProps<{
    data: {
        data: RekapTemuan[];
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
        router.get('/ami/temuan-kolektif', { 
            search: search.value, 
            periode_id: periode_id.value 
        }, { preserveState: true, replace: true });
    }, 400);
});

// Form
const showForm = ref(false);
const editTarget = ref<RekapTemuan | null>(null);
const form = useForm({
    auditee_id: '',
    pengaturan_periode_id: '',
    status_approval: 'Pending',
    jumlah_temuan: 0,
});

const openCreate = () => {
    editTarget.value = null;
    form.reset();
    showForm.value = true;
};

const openEdit = (item: RekapTemuan) => {
    editTarget.value = item;
    form.auditee_id = item.auditee.id.toString();
    form.pengaturan_periode_id = item.pengaturan_periode.id.toString();
    form.status_approval = item.status_approval;
    form.jumlah_temuan = item.jumlah_temuan;
    showForm.value = true;
};

const submit = () => {
    const opts = { preserveScroll: true, onSuccess: () => { showForm.value = false; } };
    form.post('/ami/temuan-kolektif', opts);
};

// Delete
const showDelete = ref(false);
const deleteTarget = ref<RekapTemuan | null>(null);
const openDelete = (item: RekapTemuan) => { deleteTarget.value = item; showDelete.value = true; };
const confirmDelete = () => {
    if (!deleteTarget.value) return;
    router.delete(`/ami/temuan-kolektif/${deleteTarget.value.id}`, {
        preserveScroll: true,
        onSuccess: () => { showDelete.value = false; }
    });
};
</script>

<template>
    <Head title="Temuan Kolektif" />
    <div class="space-y-6 p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-semibold text-gray-900 ">Rekap Temuan Kolektif</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Persetujuan rekapitulasi temuan per auditee</p>
            </div>
            <button type="button" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700" @click="openCreate">
                <Plus class="size-4" /> Tambah Rekap
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

        <div class="overflow-hidden rounded-xl border border-gray-200 shadow-sm dark:border-gray-700 bg-white dark:bg-gray-900">
            <table class="w-full text-sm text-left">
                <thead class="bg-blue-50 text-blue-700 border-b border-blue-100">
                    <tr>
                        <th class="px-4 py-3 font-medium text-gray-600 dark:text-gray-300">Auditee</th>
                        <th class="px-4 py-3 font-medium text-gray-600 dark:text-gray-300">Periode</th>
                        <th class="px-4 py-3 font-medium text-gray-600 dark:text-gray-300 text-center">Temuan</th>
                        <th class="px-4 py-3 font-medium text-gray-600 dark:text-gray-300">Status Approval</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600 dark:text-gray-300">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    <tr v-if="data.data.length === 0">
                        <td colspan="5" class="px-4 py-12 text-center text-gray-400 italic">Belum ada data temuan kolektif.</td>
                    </tr>
                    <tr v-for="item in data.data" :key="item.id" class="bg-white transition hover:bg-gray-50 dark:bg-gray-900 dark:hover:bg-gray-800/50">
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-900 ">{{ item.auditee.nama_auditee }}</div>
                            <div v-if="item.approved_by" class="text-[10px] text-gray-400 uppercase">Approved by: {{ item.approved_by }}</div>
                        </td>
                        <td class="px-4 py-3 text-gray-500">
                            {{ item.pengaturan_periode.lembaga_akreditasi.nama_lembaga }} ({{ item.pengaturan_periode.tahun_periode.tahun }})
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center justify-center size-8 rounded-lg bg-amber-50 text-amber-700 font-bold border border-amber-100 dark:bg-amber-900/20 dark:text-amber-400">
                                {{ item.jumlah_temuan }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <CheckCircle v-if="item.status_approval === 'Approved'" class="size-4 text-green-500" />
                                <Clock v-else class="size-4 text-amber-500" />
                                <span :class="item.status_approval === 'Approved' ? 'text-green-600' : 'text-amber-600'" class="text-xs font-bold uppercase">{{ item.status_approval }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <button type="button" class="p-1.5 text-gray-400 hover:text-blue-600" @click="openEdit(item)"><Edit2 class="size-4" /></button>
                                <button type="button" class="p-1.5 text-gray-400 hover:text-red-500" @click="openDelete(item)"><Trash2 class="size-4" /></button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Form -->
    <Modal :show="showForm" :title="editTarget ? 'Edit Rekap Temuan' : 'Tambah Rekap Temuan'" max-width="md" @close="showForm = false">
        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1">Periode AMI <span class="text-red-500">*</span></label>
                <select v-model="form.pengaturan_periode_id" :disabled="!!editTarget" class="w-full rounded-lg border px-3 py-2 text-sm  ">
                    <option value="">Pilih Periode</option>
                    <option v-for="p in periodeList" :key="p.id" :value="p.id">{{ p.label }}</option>
                </select>
                <p v-if="form.errors.pengaturan_periode_id" class="text-[11px] text-red-500 mt-1">{{ form.errors.pengaturan_periode_id }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Auditee <span class="text-red-500">*</span></label>
                <select v-model="form.auditee_id" :disabled="!!editTarget" class="w-full rounded-lg border px-3 py-2 text-sm  ">
                    <option value="">Pilih Auditee</option>
                    <option v-for="a in auditeeList" :key="a.id" :value="a.id">{{ a.nama_auditee }}</option>
                </select>
                <p v-if="form.errors.auditee_id" class="text-[11px] text-red-500 mt-1">{{ form.errors.auditee_id }}</p>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Jumlah Temuan <span class="text-red-500">*</span></label>
                    <input v-model="form.jumlah_temuan" type="number" min="0" class="w-full rounded-lg border px-3 py-2 text-sm  " />
                <p v-if="form.errors.jumlah_temuan" class="text-[11px] text-red-500 mt-1">{{ form.errors.jumlah_temuan }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Status Approval <span class="text-red-500">*</span></label>
                    <select v-model="form.status_approval" class="w-full rounded-lg border px-3 py-2 text-sm  ">
                        <option value="Pending">Pending</option>
                        <option value="Approved">Approved</option>
                    </select>
                <p v-if="form.errors.status_approval" class="text-[11px] text-red-500 mt-1">{{ form.errors.status_approval }}</p>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" class="px-4 py-2 text-sm font-medium border rounded-lg hover:bg-gray-50" @click="showForm = false">Batal</button>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700" :disabled="form.processing">Simpan Perubahan</button>
            </div>
        </form>
    </Modal>

    <ConfirmDeleteModal :show="showDelete" message="Hapus data rekap ini?" @close="showDelete = false" @confirm="confirmDelete" />
</template>

