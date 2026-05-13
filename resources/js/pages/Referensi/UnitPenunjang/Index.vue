<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Edit2, Plus, Search, Trash2 } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import ConfirmDeleteModal from '@/components/ConfirmDeleteModal.vue';
import Modal from '@/components/Modal.vue';
import AppLayout from '@/layouts/AppLayout.vue';

interface UnitPenunjang {
    id: number;
    kode: string;
    nama_unit: string;
    jenjang: string | null;
    alamat: string | null;
    keterangan: string | null;
    auditee_pusat: { id: number; nama: string };
}

interface PageProps {
    data: {
        data: UnitPenunjang[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: { url: string | null; label: string; active: boolean }[];
    };
    filters: { search?: string };
    auditeePusat: { id: number; nama: string }[];
}

defineOptions({ layout: AppLayout });
const props = defineProps<PageProps>();

const search = ref(props.filters.search ?? '');
let st: ReturnType<typeof setTimeout>;
watch(search, (val) => {
    clearTimeout(st);
    st = setTimeout(() => router.get('/referensi/unit-penunjang', { search: val }, { preserveState: true, replace: true }), 400);
});

const jenjangOptions = ['S1', 'S2', 'S3', 'D3', 'D4', 'Profesi', 'Non-Akademik'];

const showForm = ref(false);
const editTarget = ref<UnitPenunjang | null>(null);
const form = useForm({
    kode: '',
    nama_unit: '',
    auditee_pusat_id: '' as string | number,
    jenjang: '',
    alamat: '',
    keterangan: '',
});

const openCreate = () => {
    editTarget.value = null;
    form.reset();
    form.clearErrors();
    showForm.value = true;
};

const openEdit = (item: UnitPenunjang) => {
    editTarget.value = item;
    form.kode = item.kode;
    form.nama_unit = item.nama_unit;
    form.auditee_pusat_id = item.auditee_pusat.id;
    form.jenjang = item.jenjang ?? '';
    form.alamat = item.alamat ?? '';
    form.keterangan = item.keterangan ?? '';
    form.clearErrors();
    showForm.value = true;
};

const closeForm = () => {
    showForm.value = false;
    editTarget.value = null;
    form.reset();
    form.clearErrors();
};

const submit = () => {
    const opts = {
        preserveScroll: true,
        onSuccess: () => {
            closeForm();
        },
    };

    if (editTarget.value) {
        form.put(`/referensi/unit-penunjang/${editTarget.value.id}`, opts);
    } else {
        form.post('/referensi/unit-penunjang', opts);
    }
};

const showDelete = ref(false);
const deleteTarget = ref<UnitPenunjang | null>(null);
const deleting = ref(false);

const openDelete = (item: UnitPenunjang) => {
    deleteTarget.value = item;
    showDelete.value = true;
};

const closeDelete = () => {
    showDelete.value = false;
    deleteTarget.value = null;
};

const confirmDelete = () => {
    if (!deleteTarget.value) {
        return;
    }

    deleting.value = true;
    router.delete(`/referensi/unit-penunjang/${deleteTarget.value.id}`, {
        preserveScroll: true,
        onFinish: () => {
            deleting.value = false;
            closeDelete();
        },
    });
};
</script>

<template>
    <Head title="Unit Penunjang" />
    <div class="space-y-6 p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-semibold text-gray-900 ">Unit Penunjang</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Kelola data unit penunjang akademik</p>
            </div>
            <button id="btn-tambah-unit" type="button"
                class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700"
                @click="openCreate">
                <Plus class="size-4" /> Tambah
            </button>
        </div>

        <div class="relative w-full max-w-xs">
            <Search class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-gray-400" />
            <input v-model="search" type="text" placeholder="Cari kode / nama..."
                class="w-full rounded-lg border border-gray-300 py-2 pr-4 pl-9 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200   " />
        </div>

        <div class="overflow-hidden rounded-xl border border-gray-200 shadow-sm dark:border-gray-700">
            <table class="w-full text-sm">
                <thead class="bg-blue-50 text-blue-700">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">No</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Kode</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Nama Unit</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Jenjang</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Pusat</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Keterangan</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600 dark:text-gray-300">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    <tr v-if="data.data.length === 0">
                        <td colspan="7" class="px-4 py-12 text-center text-gray-400">Tidak ada data.</td>
                    </tr>
                    <tr v-for="(item, i) in data.data" :key="item.id"
                        class="bg-white transition hover:bg-gray-50 dark:bg-gray-900 dark:hover:bg-gray-800/50">
                        <td class="px-4 py-3 text-gray-500">{{ (data.current_page - 1) * data.per_page + i + 1 }}</td>
                        <td class="px-4 py-3 font-mono text-xs font-medium text-gray-700 dark:text-gray-300">{{ item.kode }}</td>
                        <td class="px-4 py-3 font-medium text-gray-900 ">{{ item.nama_unit }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ item.jenjang ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ item.auditee_pusat?.nama ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-500">
                            <div class="max-w-75 whitespace-normal wrap-break-word text-xs">
                                {{ item.keterangan || '-' }}
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <button :id="`btn-edit-unit-${item.id}`" type="button"
                                    class="rounded-md p-1.5 text-blue-600 transition hover:bg-blue-50 dark:hover:bg-blue-900/20"
                                    @click="openEdit(item)"><Edit2 class="size-4" /></button>
                                <button :id="`btn-delete-unit-${item.id}`" type="button"
                                    class="rounded-md p-1.5 text-red-500 transition hover:bg-red-50 dark:hover:bg-red-900/20"
                                    @click="openDelete(item)"><Trash2 class="size-4" /></button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="flex items-center justify-between text-sm text-gray-500">
            <span>Total: {{ data.total }} data</span>
            <div class="flex gap-1">
                <template v-for="link in data.links" :key="link.label">
                    <button v-if="link.url" type="button" class="rounded px-3 py-1 transition"
                        :class="link.active ? 'bg-blue-600 text-white' : 'hover:bg-gray-100 dark:hover:bg-gray-700'"
                        @click="router.get(link.url, {}, { preserveState: true })" v-html="link.label" />
                    <span v-else class="cursor-default rounded px-3 py-1 text-gray-300" v-html="link.label" />
                </template>
            </div>
        </div>
    </div>

    <Modal :show="showForm" :title="editTarget ? 'Edit Unit Penunjang' : 'Tambah Unit Penunjang'" max-width="lg" @close="closeForm">
        <form @submit.prevent="submit" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Kode <span class="text-red-500">*</span></label>
                <input v-model="form.kode" type="text" placeholder="Contoh: UPT-01"
                    class="w-full rounded-lg border px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200   "
                    :class="form.errors.kode ? 'border-red-400' : 'border-gray-300'" />
                <p v-if="form.errors.kode" class="text-[11px] text-red-500 mt-1">{{ form.errors.kode }}</p>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Jenjang</label>
                <select v-model="form.jenjang"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200   ">
                    <option value="">-- Pilih Jenjang --</option>
                    <option v-for="j in jenjangOptions" :key="j" :value="j">{{ j }}</option>
                </select>
                <p v-if="form.errors.jenjang" class="text-[11px] text-red-500 mt-1">{{ form.errors.jenjang }}</p>
            </div>
            <div class="sm:col-span-2">
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Unit <span class="text-red-500">*</span></label>
                <input v-model="form.nama_unit" type="text" placeholder="Contoh: Perpustakaan Pusat"
                    class="w-full rounded-lg border px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200   "
                    :class="form.errors.nama_unit ? 'border-red-400' : 'border-gray-300'" />
                <p v-if="form.errors.nama_unit" class="text-[11px] text-red-500 mt-1">{{ form.errors.nama_unit }}</p>
            </div>
            <div class="sm:col-span-2">
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Auditee Pusat <span class="text-red-500">*</span></label>
                <select v-model="form.auditee_pusat_id"
                    class="w-full rounded-lg border px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200   "
                    :class="form.errors.auditee_pusat_id ? 'border-red-400' : 'border-gray-300'">
                    <option value="">-- Pilih Auditee Pusat --</option>
                    <option v-for="ap in auditeePusat" :key="ap.id" :value="ap.id">{{ ap.nama }}</option>
                </select>
                <p v-if="form.errors.auditee_pusat_id" class="text-[11px] text-red-500 mt-1">{{ form.errors.auditee_pusat_id }}</p>
            </div>
            <div class="sm:col-span-2">
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Alamat</label>
                <textarea v-model="form.alamat" rows="2"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200   " />
            </div>
            <div class="sm:col-span-2">
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Keterangan</label>
                <textarea v-model="form.keterangan" rows="2"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200   " />
            </div>
            <div class="sm:col-span-2 flex justify-end gap-3 pt-2">
                <button type="button" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50  dark:text-gray-300 dark:hover:bg-gray-800" @click="closeForm">Batal</button>
                <button type="submit" :disabled="form.processing" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700 disabled:opacity-60">
                    {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                </button>
            </div>
        </form>
    </Modal>

    <ConfirmDeleteModal :show="showDelete" :message="`Hapus unit penunjang '${deleteTarget?.nama_unit}'?`"
        :processing="deleting" @close="closeDelete" @confirm="confirmDelete" />
</template>
