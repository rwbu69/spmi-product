<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Edit2, Plus, Trash2, Calendar, Clock } from 'lucide-vue-next';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import ConfirmDeleteModal from '@/components/ConfirmDeleteModal.vue';
import Modal from '@/components/Modal.vue';

interface PengaturanPeriode {
    id: number;
    tahun_periode: { id: number; tahun: number };
    lembaga_akreditasi: { id: number; nama_lembaga: string };
    mulai_evaluasi_diri: string | null;
    akhir_evaluasi_diri: string | null;
    mulai_desk_eval: string | null;
    akhir_desk_eval: string | null;
    mulai_visitasi: string | null;
    akhir_visitasi: string | null;
    status: 'Aktif' | 'Tidak Aktif';
}

interface PageProps {
    data: {
        data: PengaturanPeriode[];
        total: number;
        links: any[];
    };
    tahunList: { id: number; tahun: number }[];
    lembagaList: { id: number; nama_lembaga: string }[];
}

defineOptions({ layout: AppLayout });
const props = defineProps<PageProps>();

// Form
const showForm = ref(false);
const editTarget = ref<PengaturanPeriode | null>(null);
const form = useForm({
    tahun_periode_id: '',
    lembaga_akreditasi_id: '',
    mulai_evaluasi_diri: '',
    akhir_evaluasi_diri: '',
    mulai_desk_eval: '',
    akhir_desk_eval: '',
    mulai_visitasi: '',
    akhir_visitasi: '',
    status: 'Tidak Aktif',
});

const openCreate = () => {
    editTarget.value = null;
    form.reset();
    showForm.value = true;
};

const openEdit = (item: PengaturanPeriode) => {
    editTarget.value = item;
    form.tahun_periode_id = item.tahun_periode.id.toString();
    form.lembaga_akreditasi_id = item.lembaga_akreditasi.id.toString();
    form.mulai_evaluasi_diri = item.mulai_evaluasi_diri ?? '';
    form.akhir_evaluasi_diri = item.akhir_evaluasi_diri ?? '';
    form.mulai_desk_eval = item.mulai_desk_eval ?? '';
    form.akhir_desk_eval = item.akhir_desk_eval ?? '';
    form.mulai_visitasi = item.mulai_visitasi ?? '';
    form.akhir_visitasi = item.akhir_visitasi ?? '';
    form.status = item.status;
    showForm.value = true;
};

const submit = () => {
    const opts = { preserveScroll: true, onSuccess: () => { showForm.value = false; } };
    if (editTarget.value) {
        form.put(`/pelaksanaan/pengaturan-periode/${editTarget.value.id}`, opts);
    } else {
        form.post('/pelaksanaan/pengaturan-periode', opts);
    }
};

// Delete
const showDelete = ref(false);
const deleteTarget = ref<PengaturanPeriode | null>(null);
const openDelete = (item: PengaturanPeriode) => { deleteTarget.value = item; showDelete.value = true; };
const confirmDelete = () => {
    if (!deleteTarget.value) return;
    router.delete(`/pelaksanaan/pengaturan-periode/${deleteTarget.value.id}`, {
        preserveScroll: true,
        onSuccess: () => { showDelete.value = false; }
    });
};

const formatDate = (date: string | null) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
};
</script>

<template>
    <Head title="Pengaturan Periode AMI" />
    <div class="space-y-6 p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-semibold text-gray-900 ">Pengaturan Periode AMI</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Jadwal siklus SPMI dan Audit Mutu Internal</p>
            </div>
            <button type="button" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700" @click="openCreate">
                <Plus class="size-4" /> Tambah Jadwal
            </button>
        </div>

        <div class="grid grid-cols-1 gap-4">
            <div v-if="data.data.length === 0" class="p-12 text-center border-2 border-dashed rounded-2xl text-gray-400">
                Belum ada jadwal periode yang diatur.
            </div>
            <div v-for="item in data.data" :key="item.id" class="relative group p-6 rounded-2xl border bg-white dark:bg-gray-900 shadow-sm hover:shadow-md transition">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="text-lg font-bold">Periode {{ item.tahun_periode.tahun }}</span>
                            <span :class="item.status === 'Aktif' ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300' : 'bg-gray-100 text-gray-600  dark:text-gray-400'" class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider">
                                {{ item.status }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-500">{{ item.lembaga_akreditasi.nama_lembaga }}</p>
                    </div>

                    <div class="flex flex-wrap gap-4 md:gap-8">
                        <div class="space-y-1">
                            <span class="text-[10px] uppercase font-bold text-gray-400 flex items-center gap-1"><Calendar class="size-3" /> Evaluasi Diri</span>
                            <p class="text-xs font-medium">{{ formatDate(item.mulai_evaluasi_diri) }} - {{ formatDate(item.akhir_evaluasi_diri) }}</p>
                        </div>
                        <div class="space-y-1">
                            <span class="text-[10px] uppercase font-bold text-gray-400 flex items-center gap-1"><Clock class="size-3" /> Desk Evaluation</span>
                            <p class="text-xs font-medium">{{ formatDate(item.mulai_desk_eval) }} - {{ formatDate(item.akhir_desk_eval) }}</p>
                        </div>
                        <div class="space-y-1">
                            <span class="text-[10px] uppercase font-bold text-gray-400 flex items-center gap-1"><Calendar class="size-3" /> Visitasi</span>
                            <p class="text-xs font-medium">{{ formatDate(item.mulai_visitasi) }} - {{ formatDate(item.akhir_visitasi) }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" class="p-2 rounded-lg text-gray-400 hover:bg-blue-50 hover:text-blue-600 dark:hover:bg-blue-900/20 transition" @click="openEdit(item)">
                            <Edit2 class="size-4" />
                        </button>
                        <button type="button" class="p-2 rounded-lg text-gray-400 hover:bg-red-50 hover:text-red-500 dark:hover:bg-red-900/20 transition" @click="openDelete(item)">
                            <Trash2 class="size-4" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Form -->
    <Modal :show="showForm" :title="editTarget ? 'Edit Jadwal Periode' : 'Tambah Jadwal Periode'" max-width="2xl" @close="showForm = false">
        <form @submit.prevent="submit" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Tahun Periode <span class="text-red-500">*</span></label>
                    <select v-model="form.tahun_periode_id" class="w-full rounded-lg border px-3 py-2 text-sm  ">
                        <option value="">Pilih Tahun</option>
                        <option v-for="t in tahunList" :key="t.id" :value="t.id">{{ t.tahun }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Lembaga Akreditasi <span class="text-red-500">*</span></label>
                    <select v-model="form.lembaga_akreditasi_id" class="w-full rounded-lg border px-3 py-2 text-sm  ">
                        <option value="">Pilih Lembaga</option>
                        <option v-for="l in lembagaList" :key="l.id" :value="l.id">{{ l.nama_lembaga }}</option>
                    </select>
                </div>
            </div>

            <div class="space-y-4 border-t pt-4 dark:border-gray-700">
                <h4 class="text-xs font-bold uppercase text-gray-400 tracking-wider">Jadwal Pelaksanaan</h4>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold uppercase text-gray-500 mb-1">Mulai Evaluasi Diri</label>
                        <input v-model="form.mulai_evaluasi_diri" type="date" class="w-full rounded-lg border px-3 py-2 text-sm  " />
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase text-gray-500 mb-1">Akhir Evaluasi Diri</label>
                        <input v-model="form.akhir_evaluasi_diri" type="date" class="w-full rounded-lg border px-3 py-2 text-sm  " />
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase text-gray-500 mb-1">Mulai Desk Eval</label>
                        <input v-model="form.mulai_desk_eval" type="date" class="w-full rounded-lg border px-3 py-2 text-sm  " />
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase text-gray-500 mb-1">Akhir Desk Eval</label>
                        <input v-model="form.akhir_desk_eval" type="date" class="w-full rounded-lg border px-3 py-2 text-sm  " />
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase text-gray-500 mb-1">Mulai Visitasi</label>
                        <input v-model="form.mulai_visitasi" type="date" class="w-full rounded-lg border px-3 py-2 text-sm  " />
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase text-gray-500 mb-1">Akhir Visitasi</label>
                        <input v-model="form.akhir_visitasi" type="date" class="w-full rounded-lg border px-3 py-2 text-sm  " />
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Status Keaktifan <span class="text-red-500">*</span></label>
                <div class="flex gap-4">
                    <label class="flex items-center gap-2">
                        <input v-model="form.status" type="radio" value="Aktif" />
                        <span class="text-sm">Aktif</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input v-model="form.status" type="radio" value="Tidak Aktif" />
                        <span class="text-sm">Tidak Aktif</span>
                    </label>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" class="px-4 py-2 text-sm font-medium border rounded-lg hover:bg-gray-50" @click="showForm = false">Batal</button>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700" :disabled="form.processing">Simpan Jadwal</button>
            </div>
        </form>
    </Modal>

    <ConfirmDeleteModal :show="showDelete" message="Hapus pengaturan periode ini?" @close="showDelete = false" @confirm="confirmDelete" />
</template>

