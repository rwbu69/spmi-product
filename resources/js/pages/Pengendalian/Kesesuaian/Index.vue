<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Edit2, Plus, Search, Trash2, CheckCircle, Sparkles } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import ConfirmDeleteModal from '@/components/ConfirmDeleteModal.vue';
import Modal from '@/components/Modal.vue';

interface Kesesuaian {
    id: number;
    deskripsi: string | null;
    peningkatan: string | null;
    nilai_mutu: number | null;
    temuan_positif: string | null;
    auditee: { id: number; nama_auditee: string };
    standar_mutu: { id: number; kode: string; nama_standar: string };
    pengaturan_periode: { 
        id: number; 
        tahun_periode: { tahun: number };
        lembaga_akreditasi: { nama_lembaga: string };
    };
}

const props = defineProps<{
    data: {
        data: Kesesuaian[];
        total: number;
        links: any[];
    };
    filters: { search?: string; periode_id?: string };
    periodeList: { id: number; label: string }[];
    auditeeList: { id: number; nama_auditee: string }[];
    standarList: { id: number; kode: string; nama_standar: string }[];
}>();

defineOptions({ layout: AppLayout });

const search = ref(props.filters.search ?? '');
const periode_id = ref(props.filters.periode_id ?? '');

let searchTimeout: any;
watch([search, periode_id], () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get('/pengendalian/kesesuaian', { 
            search: search.value, 
            periode_id: periode_id.value 
        }, { preserveState: true, replace: true });
    }, 400);
});

// Form
const showForm = ref(false);
const editTarget = ref<Kesesuaian | null>(null);
const form = useForm({
    auditee_id: '',
    standar_mutu_id: '',
    pengaturan_periode_id: '',
    deskripsi: '',
    peningkatan: '',
    nilai_mutu: 0,
    temuan_positif: '',
});

const openCreate = () => {
    editTarget.value = null;
    form.reset();
    showForm.value = true;
};

const openEdit = (item: Kesesuaian) => {
    editTarget.value = item;
    form.auditee_id = item.auditee.id.toString();
    form.standar_mutu_id = item.standar_mutu.id.toString();
    form.pengaturan_periode_id = item.pengaturan_periode.id.toString();
    form.deskripsi = item.deskripsi ?? '';
    form.peningkatan = item.peningkatan ?? '';
    form.nilai_mutu = item.nilai_mutu ?? 0;
    form.temuan_positif = item.temuan_positif ?? '';
    showForm.value = true;
};

const submit = () => {
    const opts = { preserveScroll: true, onSuccess: () => { showForm.value = false; } };
    if (editTarget.value) {
        form.put(`/pengendalian/kesesuaian/${editTarget.value.id}`, opts);
    } else {
        form.post('/pengendalian/kesesuaian', opts);
    }
};

const showDelete = ref(false);
const deleteTarget = ref<Kesesuaian | null>(null);
const openDelete = (item: Kesesuaian) => { deleteTarget.value = item; showDelete.value = true; };
const confirmDelete = () => {
    if (!deleteTarget.value) return;
    router.delete(`/pengendalian/kesesuaian/${deleteTarget.value.id}`, {
        preserveScroll: true,
        onSuccess: () => { showDelete.value = false; }
    });
};
</script>

<template>
    <Head title="Daftar Kesesuaian" />
    <div class="space-y-6 p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-semibold text-gray-900 ">Daftar Kesesuaian & Peningkatan</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Arsip praktik baik dan rencana peningkatan mutu</p>
            </div>
            <button type="button" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700" @click="openCreate">
                <Plus class="size-4" /> Tambah Data
            </button>
        </div>

        <div class="flex flex-wrap gap-4 items-center">
            <div class="relative w-full max-w-xs">
                <Search class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-gray-400" />
                <input v-model="search" type="text" placeholder="Cari deskripsi..." class="w-full rounded-lg border border-gray-300 py-2 pr-4 pl-9 text-sm outline-none transition focus:border-blue-500  " />
            </div>
            <select v-model="periode_id" class="rounded-lg border border-gray-300 py-2 px-3 text-sm  ">
                <option value="">Semua Periode</option>
                <option v-for="p in periodeList" :key="p.id" :value="p.id">{{ p.label }}</option>
            </select>
        </div>

        <div class="grid grid-cols-1 gap-6">
            <div v-if="data.data.length === 0" class="p-12 text-center border-2 border-dashed rounded-2xl text-gray-400">
                Belum ada data kesesuaian.
            </div>
            <div v-for="item in data.data" :key="item.id" class="group p-6 rounded-2xl border bg-white dark:bg-gray-900 shadow-sm hover:shadow-md transition">
                <div class="flex flex-col md:flex-row gap-6">
                    <div class="flex-1 space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded bg-green-100 text-green-700 text-[10px] font-bold uppercase">{{ item.standar_mutu.kode }}</span>
                                <h3 class="font-bold text-gray-900 ">{{ item.standar_mutu.nama_standar }}</h3>
                            </div>
                            <div class="text-right">
                                <span class="text-[10px] text-gray-400 block uppercase font-bold">Skor Mutu</span>
                                <span class="text-lg font-black text-blue-600">{{ item.nilai_mutu?.toFixed(1) || '0.0' }}</span>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="p-3 rounded-xl bg-gray-50 /50 border dark:border-gray-700">
                                <span class="text-[10px] text-gray-400 block uppercase font-bold flex items-center gap-1 mb-1"><CheckCircle class="size-3 text-green-500" /> Temuan Positif</span>
                                <p class="text-xs text-gray-700 dark:text-gray-300">{{ item.temuan_positif || '-' }}</p>
                            </div>
                            <div class="p-3 rounded-xl bg-blue-50/30 dark:bg-blue-900/10 border border-blue-50 dark:border-blue-900/30">
                                <span class="text-[10px] text-blue-400 block uppercase font-bold flex items-center gap-1 mb-1"><Sparkles class="size-3" /> Rencana Peningkatan</span>
                                <p class="text-xs text-gray-700 dark:text-gray-300">{{ item.peningkatan || '-' }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                            <span>{{ item.auditee.nama_auditee }}</span>
                            <span>•</span>
                            <span>{{ item.pengaturan_periode.lembaga_akreditasi.nama_lembaga }} ({{ item.pengaturan_periode.tahun_periode.tahun }})</span>
                        </div>
                    </div>
                    <div class="flex md:flex-col justify-end gap-2 shrink-0">
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
    <Modal :show="showForm" :title="editTarget ? 'Edit Data Kesesuaian' : 'Tambah Data Kesesuaian'" max-width="lg" @close="showForm = false">
        <form @submit.prevent="submit" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
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
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Standar Mutu <span class="text-red-500">*</span></label>
                <select v-model="form.standar_mutu_id" :disabled="!!editTarget" class="w-full rounded-lg border px-3 py-2 text-sm  ">
                    <option value="">Pilih Standar</option>
                    <option v-for="s in standarList" :key="s.id" :value="s.id">[{{ s.kode }}] {{ s.nama_standar }}</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Temuan Positif</label>
                <textarea v-model="form.temuan_positif" rows="2" class="w-full rounded-lg border px-3 py-2 text-sm  " placeholder="Apa yang sudah baik..."></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Rencana Peningkatan</label>
                <textarea v-model="form.peningkatan" rows="2" class="w-full rounded-lg border px-3 py-2 text-sm  " placeholder="Apa yang akan ditingkatkan..."></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Nilai Mutu (Skor)</label>
                <input v-model="form.nilai_mutu" type="number" step="0.1" class="w-full rounded-lg border px-3 py-2 text-sm  " />
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" class="px-4 py-2 text-sm font-medium border rounded-lg hover:bg-gray-50" @click="showForm = false">Batal</button>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700" :disabled="form.processing">Simpan Data</button>
            </div>
        </form>
    </Modal>

    <ConfirmDeleteModal :show="showDelete" message="Hapus data kesesuaian ini?" @close="showDelete = false" @confirm="confirmDelete" />
</template>
