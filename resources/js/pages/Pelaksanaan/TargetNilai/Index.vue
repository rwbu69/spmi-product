<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Edit2, Plus, Search, Trash2, Target, TrendingUp } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import ConfirmDeleteModal from '@/components/ConfirmDeleteModal.vue';
import Modal from '@/components/Modal.vue';

interface TargetNilai {
    id: number;
    target_nilai: number;
    auditee: { id: number; nama_auditee: string };
    pengaturan_periode: { 
        id: number; 
        tahun_periode: { tahun: number };
        lembaga_akreditasi: { nama_lembaga: string };
    };
}

interface PageProps {
    data: {
        data: TargetNilai[];
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
        router.get('/pelaksanaan/target-nilai', { 
            search: search.value, 
            periode_id: periode_id.value 
        }, { preserveState: true, replace: true });
    }, 400);
});

// Form
const showForm = ref(false);
const editTarget = ref<TargetNilai | null>(null);
const form = useForm({
    pengaturan_periode_id: '',
    auditee_id: '',
    target_nilai: 0,
});

const openCreate = () => {
    editTarget.value = null;
    form.reset();
    showForm.value = true;
};

const openEdit = (item: TargetNilai) => {
    editTarget.value = item;
    form.pengaturan_periode_id = item.pengaturan_periode.id.toString();
    form.auditee_id = item.auditee.id.toString();
    form.target_nilai = item.target_nilai;
    showForm.value = true;
};

const submit = () => {
    const opts = { preserveScroll: true, onSuccess: () => { showForm.value = false; } };
    if (editTarget.value) {
        form.put(`/pelaksanaan/target-nilai/${editTarget.value.id}`, opts);
    } else {
        form.post('/pelaksanaan/target-nilai', opts);
    }
};

// Delete
const showDelete = ref(false);
const deleteTarget = ref<TargetNilai | null>(null);
const openDelete = (item: TargetNilai) => { deleteTarget.value = item; showDelete.value = true; };
const confirmDelete = () => {
    if (!deleteTarget.value) return;
    router.delete(`/pelaksanaan/target-nilai/${deleteTarget.value.id}`, {
        preserveScroll: true,
        onSuccess: () => { showDelete.value = false; }
    });
};
</script>

<template>
    <Head title="Target Nilai Mutu" />
    <div class="space-y-6 p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-semibold text-gray-900 ">Target Nilai Mutu</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Tentukan target pencapaian mutu per Auditee</p>
            </div>
            <button type="button" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700" @click="openCreate">
                <Plus class="size-4" /> Tambah Target
            </button>
        </div>

        <div class="flex flex-wrap gap-4 items-center">
            <div class="relative w-full max-w-xs">
                <Search class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-gray-400" />
                <input v-model="search" type="text" placeholder="Cari auditee..." class="w-full rounded-lg border border-gray-300 py-2 pr-4 pl-9 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200   " />
            </div>
            <select v-model="periode_id" class="rounded-lg border border-gray-300 py-2 px-3 text-sm   ">
                <option value="">Semua Periode</option>
                <option v-for="p in periodeList" :key="p.id" :value="p.id">{{ p.label }}</option>
            </select>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-if="data.data.length === 0" class="col-span-full p-12 text-center border-2 border-dashed rounded-2xl text-gray-400">
                Belum ada target nilai yang ditentukan.
            </div>
            <div v-for="item in data.data" :key="item.id" class="p-6 rounded-2xl border bg-white dark:bg-gray-900 shadow-sm hover:shadow-lg transition group relative">
                <div class="flex items-start justify-between">
                    <div class="space-y-3">
                        <div class="inline-flex items-center justify-center size-10 rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                            <Target class="size-6" />
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900  line-clamp-1">{{ item.auditee.nama_auditee }}</h3>
                            <p class="text-xs text-gray-500">{{ item.pengaturan_periode.lembaga_akreditasi.nama_lembaga }} ({{ item.pengaturan_periode.tahun_periode.tahun }})</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-2xl font-black text-blue-600 dark:text-blue-400">{{ item.target_nilai }}</div>
                        <div class="text-[10px] font-bold text-gray-400 uppercase">Target Skor</div>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t dark:border-gray-800 flex justify-end gap-2">
                    <button type="button" class="p-2 rounded-lg text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800" @click="openEdit(item)">
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
    <Modal :show="showForm" :title="editTarget ? 'Edit Target Nilai' : 'Tambah Target Nilai'" max-width="md" @close="showForm = false">
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
            <div>
                <label class="block text-sm font-medium mb-1">Target Nilai (Skor) <span class="text-red-500">*</span></label>
                <div class="relative">
                    <TrendingUp class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-gray-400" />
                    <input v-model="form.target_nilai" type="number" step="0.01" min="0" max="100" class="w-full rounded-lg border pl-10 pr-3 py-2 text-sm  " />
                </div>
                <p v-if="form.errors.target_nilai" class="text-[11px] text-red-500 mt-1">{{ form.errors.target_nilai }}</p>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" class="px-4 py-2 text-sm font-medium border rounded-lg hover:bg-gray-50" @click="showForm = false">Batal</button>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700" :disabled="form.processing">Simpan Target</button>
            </div>
        </form>
    </Modal>

    <ConfirmDeleteModal :show="showDelete" message="Hapus target nilai ini?" @close="showDelete = false" @confirm="confirmDelete" />
</template>

