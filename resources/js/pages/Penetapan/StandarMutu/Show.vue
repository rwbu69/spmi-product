<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ChevronLeft, Plus, Trash2, Edit2, FileCheck, Layers, MoreVertical } from 'lucide-vue-next';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import ConfirmDeleteModal from '@/components/ConfirmDeleteModal.vue';
import Modal from '@/components/Modal.vue';

interface Indikator {
    id: number;
    deskripsi: string;
    bobot: number;
}

interface StandarMutu {
    id: number;
    kode: string;
    nama_standar: string;
    level: number;
    deskripsi: string | null;
    data_dukung: string | null;
    children_recursive: StandarMutu[];
    indikator: Indikator[];
    lembaga_akreditasi: { id: number; nama_lembaga: string };
    tahun_periode: { id: number; tahun: number };
}

defineOptions({ layout: AppLayout });
const props = defineProps<{ standar: StandarMutu }>();

// Modals State
const showSubForm = ref(false);
const showIndikatorForm = ref(false);
const editTarget = ref<any>(null);
const type = ref<'sub' | 'indikator'>('sub');

// Forms
const subForm = useForm({
    kode: '',
    nama_standar: '',
    parent_id: props.standar.id,
    lembaga_akreditasi_id: props.standar.lembaga_akreditasi.id,
    tahun_periode_id: props.standar.tahun_periode.id,
    level: props.standar.level + 1,
    deskripsi: '',
});

const indForm = useForm({
    standar_mutu_id: props.standar.id,
    deskripsi: '',
    bobot: 0,
});

const openCreateSub = (parent?: StandarMutu) => {
    type.value = 'sub';
    editTarget.value = null;
    subForm.reset();
    subForm.parent_id = parent ? parent.id : props.standar.id;
    subForm.level = parent ? parent.level + 1 : props.standar.level + 1;
    showSubForm.value = true;
};

const openEditSub = (item: StandarMutu) => {
    type.value = 'sub';
    editTarget.value = item;
    subForm.kode = item.kode;
    subForm.nama_standar = item.nama_standar;
    subForm.deskripsi = item.deskripsi ?? '';
    showSubForm.value = true;
};

const openCreateIndikator = (standardId: number) => {
    type.value = 'indikator';
    editTarget.value = null;
    indForm.reset();
    indForm.standar_mutu_id = standardId;
    showIndikatorForm.value = true;
};

const openEditIndikator = (item: Indikator) => {
    type.value = 'indikator';
    editTarget.value = item;
    indForm.deskripsi = item.deskripsi;
    indForm.bobot = item.bobot;
    showIndikatorForm.value = true;
};

const submitSub = () => {
    const opts = { preserveScroll: true, onSuccess: () => { showSubForm.value = false; } };
    if (editTarget.value) {
        subForm.put(`/penetapan/standar-mutu/${editTarget.value.id}`, opts);
    } else {
        subForm.post('/penetapan/standar-mutu', opts);
    }
};

const submitInd = () => {
    const opts = { preserveScroll: true, onSuccess: () => { showIndikatorForm.value = false; } };
    if (editTarget.value) {
        indForm.put(`/penetapan/indikator/${editTarget.value.id}`, opts);
    } else {
        indForm.post('/penetapan/indikator', opts);
    }
};

// Delete
const showDelete = ref(false);
const deleteId = ref<number | null>(null);
const deleteType = ref<'sub' | 'indikator'>('sub');

const openDelete = (id: number, t: 'sub' | 'indikator') => {
    deleteId.value = id;
    deleteType.value = t;
    showDelete.value = true;
};

const confirmDelete = () => {
    if (!deleteId.value) return;
    const url = deleteType.value === 'sub' ? `/penetapan/standar-mutu/${deleteId.value}` : `/penetapan/indikator/${deleteId.value}`;
    router.delete(url, {
        preserveScroll: true,
        onSuccess: () => { showDelete.value = false; }
    });
};
</script>

<template>
    <Head :title="`Detail Standar: ${standar.kode}`" />
    <div class="space-y-6 p-6">
        <!-- Back Button & Header -->
        <div class="flex items-center gap-4">
            <button type="button" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition" @click="router.get('/penetapan/standar-mutu')">
                <ChevronLeft class="size-5" />
            </button>
            <div>
                <h1 class="text-xl font-semibold text-gray-900 ">
                    <span class="text-blue-600 dark:text-blue-400">[{{ standar.kode }}]</span> {{ standar.nama_standar }}
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ standar.lembaga_akreditasi.nama_lembaga }} • Tahun {{ standar.tahun_periode.tahun }}
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left: Tree View / Nested Standards -->
            <div class="lg:col-span-2 space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-bold flex items-center gap-2">
                        <Layers class="size-4 text-blue-500" /> Struktur Sub-Standar
                    </h2>
                    <button type="button" class="inline-flex items-center gap-1.5 text-xs font-medium text-blue-600 hover:underline" @click="openCreateSub()">
                        <Plus class="size-3.5" /> Tambah Sub
                    </button>
                </div>

                <!-- Recursively render children -->
                <div class="space-y-4">
                    <div v-if="standar.children_recursive.length === 0" class="p-8 text-center border-2 border-dashed rounded-xl text-gray-400">
                        Belum ada sub-standar.
                    </div>
                    <div v-for="child in standar.children_recursive" :key="child.id" class="border rounded-xl bg-white dark:bg-gray-900 overflow-hidden shadow-sm">
                        <div class="p-4 flex items-center justify-between bg-gray-50 /50 border-b dark:border-gray-700">
                            <div class="flex items-center gap-3">
                                <span class="text-xs font-mono font-bold px-2 py-0.5 rounded bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">
                                    {{ child.kode }}
                                </span>
                                <h3 class="font-semibold text-sm">{{ child.nama_standar }}</h3>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" class="p-1.5 text-gray-400 hover:text-blue-600" @click="openEditSub(child)"><Edit2 class="size-4" /></button>
                                <button type="button" class="p-1.5 text-gray-400 hover:text-red-500" @click="openDelete(child.id, 'sub')"><Trash2 class="size-4" /></button>
                            </div>
                        </div>
                        
                        <!-- Indicators for this sub-standard -->
                        <div class="p-4 space-y-3">
                            <div class="flex items-center justify-between text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <span>Indikator Penilaian</span>
                                <button type="button" class="text-blue-600 hover:underline" @click="openCreateIndikator(child.id)">+ Tambah Indikator</button>
                            </div>
                            
                            <div v-if="child.indikator.length === 0" class="text-xs text-gray-400 italic">Belum ada indikator.</div>
                            <div v-for="ind in child.indikator" :key="ind.id" class="flex items-start gap-3 p-3 rounded-lg border border-gray-100 dark:border-gray-800 bg-gray-50/50 /30 group">
                                <div class="mt-0.5">
                                    <div class="flex items-center justify-center size-5 rounded-full bg-blue-500 text-[10px] text-white font-bold">
                                        {{ ind.bobot }}%
                                    </div>
                                </div>
                                <div class="flex-1 text-sm text-gray-700 dark:text-gray-300">
                                    {{ ind.deskripsi }}
                                </div>
                                <div class="flex items-center opacity-0 group-hover:opacity-100 transition">
                                    <button type="button" class="p-1 text-gray-400 hover:text-blue-600" @click="openEditIndikator(ind)"><Edit2 class="size-3.5" /></button>
                                    <button type="button" class="p-1 text-gray-400 hover:text-red-500" @click="openDelete(ind.id, 'indikator')"><Trash2 class="size-3.5" /></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Info Panel -->
            <div class="space-y-6">
                <div class="p-6 rounded-xl border bg-white dark:bg-gray-900 shadow-sm space-y-4">
                    <h3 class="font-bold flex items-center gap-2 border-b pb-2 dark:border-gray-700">
                        <FileCheck class="size-4 text-green-500" /> Informasi Standar
                    </h3>
                    <div class="space-y-3">
                        <div>
                            <span class="text-xs text-gray-500 block">Kode</span>
                            <span class="text-sm font-mono font-bold">{{ standar.kode }}</span>
                        </div>
                        <div>
                            <span class="text-xs text-gray-500 block">Status</span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">AKTIF</span>
                        </div>
                        <div v-if="standar.deskripsi">
                            <span class="text-xs text-gray-500 block">Deskripsi Utama</span>
                            <p class="text-sm text-gray-600 dark:text-gray-400 italic">"{{ standar.deskripsi }}"</p>
                        </div>
                    </div>
                </div>

                <div v-if="standar.indikator.length > 0" class="p-6 rounded-xl border bg-white dark:bg-gray-900 shadow-sm space-y-4">
                    <h3 class="font-bold flex items-center justify-between border-b pb-2 dark:border-gray-700">
                        <span class="flex items-center gap-2">Indikator Root</span>
                        <button type="button" class="text-xs text-blue-600 hover:underline" @click="openCreateIndikator(standar.id)">+ Tambah</button>
                    </h3>
                    <div class="space-y-2">
                         <div v-for="ind in standar.indikator" :key="ind.id" class="flex items-start gap-2 text-xs p-2 rounded bg-gray-50 ">
                            <span class="font-bold text-blue-600">{{ ind.bobot }}%</span>
                            <span class="flex-1">{{ ind.deskripsi }}</span>
                         </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <Modal :show="showSubForm" :title="editTarget ? 'Edit Sub-Standar' : 'Tambah Sub-Standar'" max-width="md" @close="showSubForm = false">
        <form @submit.prevent="submitSub" class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1">Kode <span class="text-red-500">*</span></label>
                <input v-model="subForm.kode" type="text" placeholder="Contoh: STD.01.01" class="w-full rounded-lg border px-3 py-2 text-sm   " />
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Nama Sub-Standar <span class="text-red-500">*</span></label>
                <input v-model="subForm.nama_standar" type="text" placeholder="Masukkan nama..." class="w-full rounded-lg border px-3 py-2 text-sm   " />
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Keterangan</label>
                <textarea v-model="subForm.deskripsi" rows="2" class="w-full rounded-lg border px-3 py-2 text-sm   "></textarea>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" class="px-4 py-2 text-sm font-medium border rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800" @click="showSubForm = false">Batal</button>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700" :disabled="subForm.processing">Simpan</button>
            </div>
        </form>
    </Modal>

    <Modal :show="showIndikatorForm" :title="editTarget ? 'Edit Indikator' : 'Tambah Indikator'" max-width="md" @close="showIndikatorForm = false">
        <form @submit.prevent="submitInd" class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1">Deskripsi Indikator <span class="text-red-500">*</span></label>
                <textarea v-model="indForm.deskripsi" rows="4" placeholder="Deskripsi apa yang dinilai..." class="w-full rounded-lg border px-3 py-2 text-sm   "></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Bobot (%) <span class="text-red-500">*</span></label>
                <input v-model="indForm.bobot" type="number" min="0" max="100" class="w-full rounded-lg border px-3 py-2 text-sm   " />
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" class="px-4 py-2 text-sm font-medium border rounded-lg hover:bg-gray-50" @click="showIndikatorForm = false">Batal</button>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700" :disabled="indForm.processing">Simpan</button>
            </div>
        </form>
    </Modal>

    <ConfirmDeleteModal :show="showDelete" message="Hapus data ini? Tindakan ini tidak dapat dibatalkan." @close="showDelete = false" @confirm="confirmDelete" />
</template>
