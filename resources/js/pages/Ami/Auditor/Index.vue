<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Edit2, Mail, Plus, Search, Trash2, User } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import Pagination from '@/components/Pagination.vue';
import ConfirmDeleteModal from '@/components/ConfirmDeleteModal.vue';
import Modal from '@/components/Modal.vue';

interface Auditor { id: number; nama: string; email: string | null; keahlian: string | null; no_hp: string | null; keterangan: string | null }
interface PageProps {
    data: { data: Auditor[]; current_page: number; last_page: number; per_page: number; total: number; links: { url: string | null; label: string; active: boolean }[] };
    filters: { search?: string };
}

defineOptions({ layout: AppLayout });
const props = defineProps<PageProps>();

const search = ref(props.filters.search ?? '');
let st: ReturnType<typeof setTimeout>;
watch(search, (val) => { clearTimeout(st); st = setTimeout(() => router.get('/ami/auditor', { search: val }, { preserveState: true, replace: true }), 400); });

const showForm = ref(false);
const editTarget = ref<Auditor | null>(null);
const form = useForm({ nama: '', email: '', keahlian: '', no_hp: '', keterangan: '' });

const openCreate = () => { editTarget.value = null; form.reset(); form.clearErrors(); showForm.value = true; };
const openEdit = (item: Auditor) => {
    editTarget.value = item;
    form.nama = item.nama; form.email = item.email ?? ''; form.keahlian = item.keahlian ?? ''; form.no_hp = item.no_hp ?? ''; form.keterangan = item.keterangan ?? '';
    form.clearErrors(); showForm.value = true;
};
const closeForm = () => { showForm.value = false; editTarget.value = null; form.reset(); form.clearErrors(); };
const submit = () => {
    const opts = { preserveScroll: true, onSuccess: () => closeForm() };
    editTarget.value ? form.put(`/ami/auditor/${editTarget.value.id}`, opts) : form.post('/ami/auditor', opts);
};

const showDelete = ref(false);
const deleteTarget = ref<Auditor | null>(null);
const deleting = ref(false);
const openDelete = (item: Auditor) => { deleteTarget.value = item; showDelete.value = true; };
const closeDelete = () => { showDelete.value = false; deleteTarget.value = null; };
const confirmDelete = () => {
    if (!deleteTarget.value) return;
    deleting.value = true;
    router.delete(`/ami/auditor/${deleteTarget.value.id}`, { preserveScroll: true, onFinish: () => { deleting.value = false; closeDelete(); } });
};
</script>

<template>
    <Head title="Manajemen Auditor" />
    <div class="space-y-6 p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-semibold text-gray-900 ">Manajemen Auditor</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Kelola data tim auditor internal</p>
            </div>
            <button id="btn-tambah-auditor" type="button" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700" @click="openCreate">
                <Plus class="size-4" /> Tambah Auditor
            </button>
        </div>

        <div class="relative w-full max-w-xs">
            <Search class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-gray-400" />
            <input v-model="search" type="text" placeholder="Cari nama / email..." class="w-full rounded-lg border border-gray-300 py-2 pr-4 pl-9 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200   " />
        </div>

        <div class="overflow-hidden rounded-xl border border-gray-200 shadow-sm dark:border-gray-700">
            <table class="w-full text-sm">
                <thead class="bg-blue-50 text-blue-700">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">No</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Nama</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Email</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Keahlian</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">No. HP</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600 dark:text-gray-300">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    <tr v-if="data.data.length === 0"><td colspan="6" class="px-4 py-12 text-center text-gray-400">Tidak ada data.</td></tr>
                    <tr v-for="(item, i) in data.data" :key="item.id" class="bg-white transition hover:bg-gray-50 dark:bg-gray-900 dark:hover:bg-gray-800/50">
                        <td class="px-4 py-3 text-gray-500">{{ (data.current_page - 1) * data.per_page + i + 1 }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="flex size-8 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/30">
                                    <User class="size-4 text-blue-600 dark:text-blue-400" />
                                </div>
                                <span class="font-medium text-gray-900 ">{{ item.nama }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-gray-500">
                            <a v-if="item.email" :href="`mailto:${item.email}`" class="flex items-center gap-1 hover:text-blue-600">
                                <Mail class="size-3.5" /> {{ item.email }}
                            </a>
                            <span v-else>-</span>
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ item.keahlian ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ item.no_hp ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <button :id="`btn-edit-auditor-${item.id}`" type="button" class="rounded-md p-1.5 text-blue-600 transition hover:bg-blue-50 dark:hover:bg-blue-900/20" @click="openEdit(item)"><Edit2 class="size-4" /></button>
                                <button :id="`btn-delete-auditor-${item.id}`" type="button" class="rounded-md p-1.5 text-red-500 transition hover:bg-red-50 dark:hover:bg-red-900/20" @click="openDelete(item)"><Trash2 class="size-4" /></button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Pagination :links="data.links" :total="data.total" />
    </div>

    <Modal :show="showForm" :title="editTarget ? 'Edit Auditor' : 'Tambah Auditor'" max-width="lg" @close="closeForm">
        <form @submit.prevent="submit" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Lengkap <span class="text-red-500">*</span></label>
                <input v-model="form.nama" type="text" placeholder="Nama auditor..." class="w-full rounded-lg border px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200   " :class="form.errors.nama ? 'border-red-400' : 'border-gray-300'" />
                <p v-if="form.errors.nama" class="text-[11px] text-red-500 mt-1">{{ form.errors.nama }}</p>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                <input v-model="form.email" type="email" placeholder="email@contoh.com" class="w-full rounded-lg border px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200   " :class="form.errors.email ? 'border-red-400' : 'border-gray-300'" />
                <p v-if="form.errors.email" class="text-[11px] text-red-500 mt-1">{{ form.errors.email }}</p>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">No. HP</label>
                <input v-model="form.no_hp" type="text" placeholder="08xxxxxxxxxx" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200   " />
                <p v-if="form.errors.no_hp" class="text-[11px] text-red-500 mt-1">{{ form.errors.no_hp }}</p>
            </div>
            <div class="sm:col-span-2">
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Keahlian</label>
                <input v-model="form.keahlian" type="text" placeholder="Bidang keahlian..." class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200   " />
                <p v-if="form.errors.keahlian" class="text-[11px] text-red-500 mt-1">{{ form.errors.keahlian }}</p>
            </div>
            <div class="sm:col-span-2">
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Keterangan</label>
                <textarea v-model="form.keterangan" rows="2" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200   " />
            </div>
            <div class="sm:col-span-2 flex justify-end gap-3 pt-2">
                <button type="button" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50  dark:text-gray-300 dark:hover:bg-gray-800" @click="closeForm">Batal</button>
                <button type="submit" :disabled="form.processing" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700 disabled:opacity-60">{{ form.processing ? 'Menyimpan...' : 'Simpan' }}</button>
            </div>
        </form>
    </Modal>

    <ConfirmDeleteModal :show="showDelete" :message="`Hapus auditor '${deleteTarget?.nama}'?`" :processing="deleting" @close="closeDelete" @confirm="confirmDelete" />
</template>

