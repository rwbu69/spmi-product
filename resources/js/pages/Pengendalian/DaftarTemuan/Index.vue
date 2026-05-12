<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Edit2, Plus, Search, Trash2, ShieldAlert, CheckCircle2, Timer } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import ConfirmDeleteModal from '@/components/ConfirmDeleteModal.vue';
import Modal from '@/components/Modal.vue';

interface TemuanPp {
    id: number;
    uraian_temuan: string;
    jenis: string;
    status: 'Open' | 'In Progress' | 'Closed';
    auditee: { id: number; nama_auditee: string };
    pengaturan_periode: { 
        id: number; 
        tahun_periode: { tahun: number };
        lembaga_akreditasi: { nama_lembaga: string };
    };
}

const props = defineProps<{
    data: {
        data: TemuanPp[];
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
        router.get('/pengendalian/daftar-temuan', { 
            search: search.value, 
            periode_id: periode_id.value 
        }, { preserveState: true, replace: true });
    }, 400);
});

// Form
const showForm = ref(false);
const editTarget = ref<TemuanPp | null>(null);
const form = useForm({
    auditee_id: '',
    pengaturan_periode_id: '',
    uraian_temuan: '',
    jenis: '',
    status: 'Open' as 'Open' | 'In Progress' | 'Closed',
});

const openCreate = () => {
    editTarget.value = null;
    form.reset();
    showForm.value = true;
};

const openEdit = (item: TemuanPp) => {
    editTarget.value = item;
    form.auditee_id = item.auditee.id.toString();
    form.pengaturan_periode_id = item.pengaturan_periode.id.toString();
    form.uraian_temuan = item.uraian_temuan;
    form.jenis = item.jenis;
    form.status = item.status;
    showForm.value = true;
};

const submit = () => {
    const opts = { preserveScroll: true, onSuccess: () => { showForm.value = false; } };
    if (editTarget.value) {
        form.put(`/pengendalian/daftar-temuan/${editTarget.value.id}`, opts);
    } else {
        form.post('/pengendalian/daftar-temuan', opts);
    }
};

const showDelete = ref(false);
const deleteTarget = ref<TemuanPp | null>(null);
const openDelete = (item: TemuanPp) => { deleteTarget.value = item; showDelete.value = true; };
const confirmDelete = () => {
    if (!deleteTarget.value) return;
    router.delete(`/pengendalian/daftar-temuan/${deleteTarget.value.id}`, {
        preserveScroll: true,
        onSuccess: () => { showDelete.value = false; }
    });
};

const getStatusBadge = (status: string) => {
    switch (status) {
        case 'Closed': return 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400';
        case 'In Progress': return 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400';
        default: return 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400';
    }
};
</script>

<template>
    <Head title="Daftar Temuan" />
    <div class="space-y-6 p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-semibold text-gray-900 ">Daftar Temuan (Tindak Lanjut)</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Monitoring penyelesaian temuan KTS dan Observasi</p>
            </div>
            <button type="button" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700" @click="openCreate">
                <Plus class="size-4" /> Tambah Temuan
            </button>
        </div>

        <div class="flex flex-wrap gap-4 items-center">
            <div class="relative w-full max-w-xs">
                <Search class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-gray-400" />
                <input v-model="search" type="text" placeholder="Cari uraian..." class="w-full rounded-lg border border-gray-300 py-2 pr-4 pl-9 text-sm outline-none transition focus:border-blue-500  " />
            </div>
            <select v-model="periode_id" class="rounded-lg border border-gray-300 py-2 px-3 text-sm  ">
                <option value="">Semua Periode</option>
                <option v-for="p in periodeList" :key="p.id" :value="p.id">{{ p.label }}</option>
            </select>
        </div>

        <div class="grid grid-cols-1 gap-4">
            <div v-if="data.data.length === 0" class="p-12 text-center border-2 border-dashed rounded-2xl text-gray-400">
                Belum ada temuan yang didaftarkan.
            </div>
            <div v-for="item in data.data" :key="item.id" class="p-4 rounded-2xl border bg-white dark:bg-gray-900 shadow-sm hover:shadow-md transition">
                <div class="flex items-start gap-4">
                    <div :class="item.status === 'Closed' ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600'" class="size-10 rounded-xl flex items-center justify-center shrink-0">
                        <CheckCircle2 v-if="item.status === 'Closed'" class="size-6" />
                        <ShieldAlert v-else class="size-6" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2 mb-1">
                            <span :class="getStatusBadge(item.status)" class="px-2 py-0.5 rounded text-[10px] font-bold uppercase">{{ item.status }}</span>
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ item.jenis }}</span>
                        </div>
                        <h3 class="font-medium text-gray-900 ">{{ item.uraian_temuan }}</h3>
                        <div class="mt-2 flex items-center gap-2 text-xs text-gray-500">
                            <span class="font-bold text-blue-600">{{ item.auditee.nama_auditee }}</span>
                            <span>•</span>
                            <span>{{ item.pengaturan_periode.lembaga_akreditasi.nama_lembaga }} ({{ item.pengaturan_periode.tahun_periode.tahun }})</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-1">
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
    </div>

    <!-- Modal Form -->
    <Modal :show="showForm" :title="editTarget ? 'Edit Temuan' : 'Tambah Temuan Baru'" max-width="lg" @close="showForm = false">
        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1">Auditee <span class="text-red-500">*</span></label>
                <select v-model="form.auditee_id" :disabled="!!editTarget" class="w-full rounded-lg border px-3 py-2 text-sm  ">
                    <option value="">Pilih Auditee</option>
                    <option v-for="a in auditeeList" :key="a.id" :value="a.id">{{ a.nama_auditee }}</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Periode AMI <span class="text-red-500">*</span></label>
                <select v-model="form.pengaturan_periode_id" :disabled="!!editTarget" class="w-full rounded-lg border px-3 py-2 text-sm  ">
                    <option value="">Pilih Periode</option>
                    <option v-for="p in periodeList" :key="p.id" :value="p.id">{{ p.label }}</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Uraian Temuan <span class="text-red-500">*</span></label>
                <textarea v-model="form.uraian_temuan" rows="3" class="w-full rounded-lg border px-3 py-2 text-sm  " placeholder="Deskripsikan temuan..."></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Kategori (Jenis) <span class="text-red-500">*</span></label>
                    <input v-model="form.jenis" type="text" placeholder="KTS Mayor / Minor" class="w-full rounded-lg border px-3 py-2 text-sm  " />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Status <span class="text-red-500">*</span></label>
                    <select v-model="form.status" class="w-full rounded-lg border px-3 py-2 text-sm  ">
                        <option value="Open">Open</option>
                        <option value="In Progress">In Progress</option>
                        <option value="Closed">Closed</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" class="px-4 py-2 text-sm font-medium border rounded-lg hover:bg-gray-50" @click="showForm = false">Batal</button>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700" :disabled="form.processing">Simpan Temuan</button>
            </div>
        </form>
    </Modal>

    <ConfirmDeleteModal :show="showDelete" message="Hapus data temuan ini?" @close="showDelete = false" @confirm="confirmDelete" />
</template>
