<script setup lang="ts">
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { Edit2, Plus, Search, Trash2, Users } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import ConfirmDeleteModal from '@/components/ConfirmDeleteModal.vue';
import Modal from '@/components/Modal.vue';

interface AuditeePusat {
    id: number;
    nama: string;
    auditee_count: number;
}

interface PageProps {
    data: {
        data: AuditeePusat[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: { url: string | null; label: string; active: boolean }[];
    };
    filters: { search?: string };
}

defineOptions({ layout: AppLayout });
const props = defineProps<PageProps>();

const search = ref(props.filters.search ?? '');
let st: ReturnType<typeof setTimeout>;
watch(search, (val) => {
    clearTimeout(st);
    st = setTimeout(() => router.get('/referensi/auditee-pusat', { search: val }, { preserveState: true, replace: true }), 400);
});

// Form
const showForm = ref(false);
const editTarget = ref<AuditeePusat | null>(null);
const form = useForm({ nama: '' });

const openCreate = () => { editTarget.value = null; form.reset(); form.clearErrors(); showForm.value = true; };
const openEdit = (item: AuditeePusat) => { editTarget.value = item; form.nama = item.nama; form.clearErrors(); showForm.value = true; };
const closeForm = () => { showForm.value = false; editTarget.value = null; form.reset(); form.clearErrors(); };
const submit = () => {
    const opts = { preserveScroll: true, onSuccess: () => closeForm() };
    editTarget.value
        ? form.put(`/referensi/auditee-pusat/${editTarget.value.id}`, opts)
        : form.post('/referensi/auditee-pusat', opts);
};

// Delete
const showDelete = ref(false);
const deleteTarget = ref<AuditeePusat | null>(null);
const deleting = ref(false);

const openDelete = (item: AuditeePusat) => { deleteTarget.value = item; showDelete.value = true; };
const closeDelete = () => { showDelete.value = false; deleteTarget.value = null; };
const confirmDelete = () => {
    if (!deleteTarget.value) return;
    deleting.value = true;
    router.delete(`/referensi/auditee-pusat/${deleteTarget.value.id}`, {
        preserveScroll: true,
        onFinish: () => { deleting.value = false; closeDelete(); },
    });
};
</script>

<template>
    <Head title="Auditee Pusat" />
    <div class="space-y-6 p-6">
        <!-- Header -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-semibold text-gray-900 ">Auditee Pusat</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Kelola data institusi/lembaga pusat</p>
            </div>
            <button id="btn-tambah-auditee-pusat" type="button"
                class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700"
                @click="openCreate">
                <Plus class="size-4" /> Tambah
            </button>
        </div>

        <!-- Search -->
        <div class="relative w-full max-w-xs">
            <Search class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-gray-400" />
            <input v-model="search" type="text" placeholder="Cari nama..."
                class="w-full rounded-lg border border-gray-300 py-2 pr-4 pl-9 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200   " />
        </div>

        <!-- Table -->
        <div class="overflow-hidden rounded-xl border border-gray-200 shadow-sm dark:border-gray-700">
            <table class="w-full text-sm">
                <thead class="bg-blue-50 text-blue-700">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">No</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Nama</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Jumlah Auditee</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600 dark:text-gray-300">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    <tr v-if="data.data.length === 0">
                        <td colspan="4" class="px-4 py-12 text-center text-gray-400">Tidak ada data.</td>
                    </tr>
                    <tr v-for="(item, i) in data.data" :key="item.id"
                        class="bg-white transition hover:bg-gray-50 dark:bg-gray-900 dark:hover:bg-gray-800/50">
                        <td class="px-4 py-3 text-gray-500">{{ (data.current_page - 1) * data.per_page + i + 1 }}</td>
                        <td class="px-4 py-3 font-medium text-gray-900 ">{{ item.nama }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center gap-1.5 text-gray-500 dark:text-gray-400">
                                <Users class="size-3.5" />
                                {{ item.auditee_count }} auditee
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <button :id="`btn-edit-ap-${item.id}`" type="button"
                                    class="rounded-md p-1.5 text-blue-600 transition hover:bg-blue-50 dark:hover:bg-blue-900/20"
                                    @click="openEdit(item)"><Edit2 class="size-4" /></button>
                                <button :id="`btn-delete-ap-${item.id}`" type="button"
                                    class="rounded-md p-1.5 text-red-500 transition hover:bg-red-50 dark:hover:bg-red-900/20"
                                    @click="openDelete(item)"><Trash2 class="size-4" /></button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
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

    <!-- Form Modal -->
    <Modal :show="showForm" :title="editTarget ? 'Edit Auditee Pusat' : 'Tambah Auditee Pusat'" max-width="sm" @close="closeForm">
        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Nama <span class="text-red-500">*</span>
                </label>
                <input v-model="form.nama" type="text" placeholder="Contoh: Nama Universitas"
                    class="w-full rounded-lg border px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200   "
                    :class="form.errors.nama ? 'border-red-400' : 'border-gray-300'" />
                <p v-if="form.errors.nama" class="text-[11px] text-red-500 mt-1">{{ form.errors.nama }}</p>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50  dark:text-gray-300 dark:hover:bg-gray-800" @click="closeForm">Batal</button>
                <button type="submit" :disabled="form.processing" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700 disabled:opacity-60">
                    {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                </button>
            </div>
        </form>
    </Modal>

    <ConfirmDeleteModal :show="showDelete" :message="`Hapus auditee pusat '${deleteTarget?.nama}'?`"
        :processing="deleting" @close="closeDelete" @confirm="confirmDelete" />
</template>
