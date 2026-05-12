<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Edit2, Plus, Search, Trash2, ClipboardCheck, Send, CheckCircle } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import ConfirmDeleteModal from '@/components/ConfirmDeleteModal.vue';
import Modal from '@/components/Modal.vue';

interface EvaluasiDiri {
    id: number;
    nilai_evaluasi: number;
    status: 'Draft' | 'Submitted' | 'Approved';
    auditee: { id: number; nama_auditee: string };
    pengaturan_periode: { 
        id: number; 
        tahun_periode: { tahun: number };
        lembaga_akreditasi: { nama_lembaga: string };
    };
}

interface PageProps {
    data: {
        data: EvaluasiDiri[];
        total: number;
        links: any[];
    };
    filters: { search?: string; periode_id?: string };
    periodeList: { id: number; label: string }[];
    auditeeList: { id: number; nama_auditee: string }[];
}

defineOptions({ layout: AppLayout });
const props = defineProps<PageProps>();

const search = ref(props.filters.search ?? '');
const periode_id = ref(props.filters.periode_id ?? '');

let searchTimeout: any;
watch([search, periode_id], () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get('/pelaksanaan/evaluasi-diri', { 
            search: search.value, 
            periode_id: periode_id.value 
        }, { preserveState: true, replace: true });
    }, 400);
});

// Form
const showForm = ref(false);
const editTarget = ref<EvaluasiDiri | null>(null);
const form = useForm({
    pengaturan_periode_id: '',
    auditee_id: '',
    status: 'Draft',
});

const openCreate = () => {
    editTarget.value = null;
    form.reset();
    showForm.value = true;
};

const openEdit = (item: EvaluasiDiri) => {
    editTarget.value = item;
    form.pengaturan_periode_id = item.pengaturan_periode.id.toString();
    form.auditee_id = item.auditee.id.toString();
    form.status = item.status;
    showForm.value = true;
};

const submit = () => {
    const opts = { preserveScroll: true, onSuccess: () => { showForm.value = false; } };
    form.post('/pelaksanaan/evaluasi-diri', opts);
};

// Delete
const showDelete = ref(false);
const deleteTarget = ref<EvaluasiDiri | null>(null);
const openDelete = (item: EvaluasiDiri) => { deleteTarget.value = item; showDelete.value = true; };
const confirmDelete = () => {
    if (!deleteTarget.value) return;
    router.delete(`/pelaksanaan/evaluasi-diri/${deleteTarget.value.id}`, {
        preserveScroll: true,
        onSuccess: () => { showDelete.value = false; }
    });
};

const getStatusBadge = (status: string) => {
    switch (status) {
        case 'Approved': return 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400';
        case 'Submitted': return 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400';
        default: return 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400';
    }
};
</script>

<template>
    <Head title="Evaluasi Diri" />
    <div class="space-y-6 p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-semibold text-gray-900 ">Evaluasi Diri Auditee</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Monitoring pengisian evaluasi diri oleh unit/auditee</p>
            </div>
            <button type="button" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700" @click="openCreate">
                <Plus class="size-4" /> Inisialisasi Evaluasi
            </button>
        </div>

        <!-- Filters -->
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
                        <th class="px-4 py-3 font-medium text-gray-600 dark:text-gray-300">Skor ED</th>
                        <th class="px-4 py-3 font-medium text-gray-600 dark:text-gray-300">Status</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600 dark:text-gray-300">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    <tr v-if="data.data.length === 0">
                        <td colspan="5" class="px-4 py-12 text-center text-gray-400 italic">Belum ada data evaluasi diri.</td>
                    </tr>
                    <tr v-for="item in data.data" :key="item.id" class="bg-white transition hover:bg-gray-50 dark:bg-gray-900 dark:hover:bg-gray-800/50">
                        <td class="px-4 py-3 font-medium text-gray-900 ">{{ item.auditee.nama_auditee }}</td>
                        <td class="px-4 py-3 text-gray-500">
                            {{ item.pengaturan_periode.lembaga_akreditasi.nama_lembaga }} ({{ item.pengaturan_periode.tahun_periode.tahun }})
                        </td>
                        <td class="px-4 py-3 font-bold text-blue-600 dark:text-blue-400">{{ item.nilai_evaluasi.toFixed(2) }}</td>
                        <td class="px-4 py-3">
                            <span :class="getStatusBadge(item.status)" class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase">
                                {{ item.status }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <button type="button" class="inline-flex items-center gap-1.5 rounded-md px-2 py-1 text-xs font-medium bg-gray-50 text-gray-600 hover:bg-gray-100  dark:text-gray-400" @click="openEdit(item)">
                                    <Edit2 class="size-3.5" /> Edit Status
                                </button>
                                <button type="button" class="p-1.5 text-gray-400 hover:text-red-500" @click="openDelete(item)"><Trash2 class="size-4" /></button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Form -->
    <Modal :show="showForm" :title="editTarget ? 'Update Status Evaluasi' : 'Inisialisasi Evaluasi Baru'" max-width="md" @close="showForm = false">
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
            <div>
                <label class="block text-sm font-medium mb-1">Status <span class="text-red-500">*</span></label>
                <select v-model="form.status" class="w-full rounded-lg border px-3 py-2 text-sm  ">
                    <option value="Draft">Draft</option>
                    <option value="Submitted">Submitted (Selesai Isi)</option>
                    <option value="Approved">Approved (Terkunci)</option>
                </select>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" class="px-4 py-2 text-sm font-medium border rounded-lg hover:bg-gray-50" @click="showForm = false">Batal</button>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700" :disabled="form.processing">Simpan Perubahan</button>
            </div>
        </form>
    </Modal>

    <ConfirmDeleteModal :show="showDelete" message="Hapus data evaluasi ini? Semua pengisian indikator akan hilang." @close="showDelete = false" @confirm="confirmDelete" />
</template>

