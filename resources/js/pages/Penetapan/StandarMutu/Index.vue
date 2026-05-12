<script setup lang="ts">
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { Edit2, Plus, Search, Trash2, ChevronRight, ListTree } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import ConfirmDeleteModal from '@/components/ConfirmDeleteModal.vue';
import Modal from '@/components/Modal.vue';

interface StandarMutu {
    id: number;
    kode: string;
    nama_standar: string;
    level: number;
    lembaga_akreditasi: { id: number; nama_lembaga: string };
    tahun_periode: { id: number; tahun: number };
}

interface PageProps {
    data: {
        data: StandarMutu[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: { url: string | null; label: string; active: boolean }[];
    };
    filters: { search?: string; lembaga_id?: number; tahun_id?: number };
    lembagaList: { id: number; nama_lembaga: string }[];
    tahunList: { id: number; tahun: number; status: string }[];
}

defineOptions({ layout: AppLayout });
const props = defineProps<PageProps>();

const search = ref(props.filters.search ?? '');
const lembaga_id = ref(props.filters.lembaga_id ?? '');
const tahun_id = ref(props.filters.tahun_id ?? '');

let searchTimeout: any;
watch([search, lembaga_id, tahun_id], () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get('/penetapan/standar-mutu', { 
            search: search.value, 
            lembaga_id: lembaga_id.value, 
            tahun_id: tahun_id.value 
        }, { preserveState: true, replace: true });
    }, 400);
});

// Form
const showForm = ref(false);
const editTarget = ref<StandarMutu | null>(null);
const form = useForm({
    kode: '',
    nama_standar: '',
    lembaga_akreditasi_id: '',
    tahun_periode_id: '',
    level: 1,
    parent_id: null as number | null,
    deskripsi: '',
});

const openCreate = () => {
    editTarget.value = null;
    form.reset();
    form.clearErrors();
    showForm.value = true;
};

const openEdit = (item: StandarMutu) => {
    editTarget.value = item;
    form.kode = item.kode;
    form.nama_standar = item.nama_standar;
    form.lembaga_akreditasi_id = item.lembaga_akreditasi.id;
    form.tahun_periode_id = item.tahun_periode.id;
    form.level = item.level;
    form.clearErrors();
    showForm.value = true;
};

const submit = () => {
    const opts = { preserveScroll: true, onSuccess: () => { showForm.value = false; } };
    if (editTarget.value) {
        form.put(`/penetapan/standar-mutu/${editTarget.value.id}`, opts);
    } else {
        form.post('/penetapan/standar-mutu', opts);
    }
};

// Delete
const showDelete = ref(false);
const deleteTarget = ref<StandarMutu | null>(null);
const openDelete = (item: StandarMutu) => { deleteTarget.value = item; showDelete.value = true; };
const confirmDelete = () => {
    if (!deleteTarget.value) return;
    router.delete(`/penetapan/standar-mutu/${deleteTarget.value.id}`, {
        preserveScroll: true,
        onSuccess: () => { showDelete.value = false; }
    });
};
</script>

<template>
    <Head title="Standar Mutu" />
    <div class="space-y-6 p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-semibold text-gray-900 ">Standar Mutu (Pernyataan)</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Kelola pernyataan standar mutu internal</p>
            </div>
            <button type="button" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700" @click="openCreate">
                <Plus class="size-4" /> Tambah Standar
            </button>
        </div>

        <!-- Filters -->
        <div class="flex flex-wrap gap-4 items-center">
            <div class="relative w-full max-w-xs">
                <Search class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-gray-400" />
                <input v-model="search" type="text" placeholder="Cari standar..." class="w-full rounded-lg border border-gray-300 py-2 pr-4 pl-9 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200   " />
            </div>
            <select v-model="lembaga_id" class="rounded-lg border border-gray-300 py-2 px-3 text-sm   ">
                <option value="">Semua Lembaga</option>
                <option v-for="l in lembagaList" :key="l.id" :value="l.id">{{ l.nama_lembaga }}</option>
            </select>
            <select v-model="tahun_id" class="rounded-lg border border-gray-300 py-2 px-3 text-sm   ">
                <option value="">Semua Tahun</option>
                <option v-for="t in tahunList" :key="t.id" :value="t.id">{{ t.tahun }} ({{ t.status }})</option>
            </select>
        </div>

        <div class="overflow-hidden rounded-xl border border-gray-200 shadow-sm dark:border-gray-700">
            <table class="w-full text-sm text-left">
                <thead class="bg-blue-50 text-blue-700 border-b border-blue-100">
                    <tr>
                        <th class="px-4 py-3 font-medium text-gray-600 dark:text-gray-300">Kode</th>
                        <th class="px-4 py-3 font-medium text-gray-600 dark:text-gray-300">Nama Standar</th>
                        <th class="px-4 py-3 font-medium text-gray-600 dark:text-gray-300">Lembaga</th>
                        <th class="px-4 py-3 font-medium text-gray-600 dark:text-gray-300">Tahun</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600 dark:text-gray-300">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    <tr v-if="data.data.length === 0">
                        <td colspan="5" class="px-4 py-12 text-center text-gray-400 italic">Belum ada data standar mutu.</td>
                    </tr>
                    <tr v-for="item in data.data" :key="item.id" class="bg-white transition hover:bg-gray-50 dark:bg-gray-900 dark:hover:bg-gray-800/50">
                        <td class="px-4 py-3 font-mono text-xs font-semibold text-blue-600 dark:text-blue-400">{{ item.kode }}</td>
                        <td class="px-4 py-3 font-medium text-gray-900 ">{{ item.nama_standar }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ item.lembaga_akreditasi.nama_lembaga }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ item.tahun_periode.tahun }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <button type="button" class="inline-flex items-center gap-1.5 rounded-md px-2 py-1 text-xs font-medium bg-blue-50 text-blue-600 hover:bg-blue-100 dark:bg-blue-900/20 dark:text-blue-400" @click="router.get(`/penetapan/standar-mutu/${item.id}`)">
                                    <ListTree class="size-3.5" /> Struktur
                                </button>
                                <button type="button" class="rounded-md p-1.5 text-gray-400 hover:text-blue-600 transition" @click="openEdit(item)"><Edit2 class="size-4" /></button>
                                <button type="button" class="rounded-md p-1.5 text-gray-400 hover:text-red-500 transition" @click="openDelete(item)"><Trash2 class="size-4" /></button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination (Basic) -->
        <div class="flex items-center justify-between text-sm text-gray-500">
            <span>Total: {{ data.total }} data</span>
            <div class="flex gap-1">
                <template v-for="link in data.links" :key="link.label">
                    <button v-if="link.url" type="button" class="rounded px-3 py-1 transition" :class="link.active ? 'bg-blue-600 text-white' : 'hover:bg-gray-100 dark:hover:bg-gray-700'" @click="router.get(link.url, {}, { preserveState: true })" v-html="link.label" />
                </template>
            </div>
        </div>
    </div>

    <!-- Modal Form -->
    <Modal :show="showForm" :title="editTarget ? 'Edit Standar' : 'Tambah Standar'" max-width="lg" @close="showForm = false">
        <form @submit.prevent="submit" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-1">
                    <label class="block text-sm font-medium mb-1 dark:text-gray-300">Lembaga <span class="text-red-500">*</span></label>
                    <select v-model="form.lembaga_akreditasi_id" class="w-full rounded-lg border px-3 py-2 text-sm   ">
                        <option value="">Pilih Lembaga</option>
                        <option v-for="l in lembagaList" :key="l.id" :value="l.id">{{ l.nama_lembaga }}</option>
                    </select>
                </div>
                <div class="col-span-1">
                    <label class="block text-sm font-medium mb-1 dark:text-gray-300">Tahun Periode <span class="text-red-500">*</span></label>
                    <select v-model="form.tahun_periode_id" class="w-full rounded-lg border px-3 py-2 text-sm   ">
                        <option value="">Pilih Tahun</option>
                        <option v-for="t in tahunList" :key="t.id" :value="t.id">{{ t.tahun }}</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1 dark:text-gray-300">Kode <span class="text-red-500">*</span></label>
                <input v-model="form.kode" type="text" placeholder="Contoh: STD.01" class="w-full rounded-lg border px-3 py-2 text-sm   " />
            </div>
            <div>
                <label class="block text-sm font-medium mb-1 dark:text-gray-300">Nama Standar <span class="text-red-500">*</span></label>
                <input v-model="form.nama_standar" type="text" placeholder="Masukkan nama standar..." class="w-full rounded-lg border px-3 py-2 text-sm   " />
            </div>
            <div>
                <label class="block text-sm font-medium mb-1 dark:text-gray-300">Deskripsi</label>
                <textarea v-model="form.deskripsi" rows="3" class="w-full rounded-lg border px-3 py-2 text-sm   " placeholder="Keterangan opsional..."></textarea>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border rounded-lg hover:bg-gray-50" @click="showForm = false">Batal</button>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700" :disabled="form.processing">
                    {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                </button>
            </div>
        </form>
    </Modal>

    <ConfirmDeleteModal :show="showDelete" :message="`Hapus standar ${deleteTarget?.kode}?`" @close="showDelete = false" @confirm="confirmDelete" />
</template>

