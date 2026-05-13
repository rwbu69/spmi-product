<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Edit2, Plus, Search, Trash2, UserCog, Mail, Key } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import ConfirmDeleteModal from '@/components/ConfirmDeleteModal.vue';
import Modal from '@/components/Modal.vue';

interface User {
    id: number;
    name: string;
    email: string;
}

const props = defineProps<{
    data: {
        data: User[];
        total: number;
        links: any[];
    };
    filters: { search?: string };
}>();

defineOptions({ layout: AppLayout });

const search = ref(props.filters.search ?? '');

let searchTimeout: any;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get('/pengaturan/pengguna-backoffice', { search: search.value }, { preserveState: true, replace: true });
    }, 400);
});

// Form
const showForm = ref(false);
const editTarget = ref<User | null>(null);
const form = useForm({
    name: '',
    email: '',
    password: '',
});

const openCreate = () => {
    editTarget.value = null;
    form.reset();
    showForm.value = true;
};

const openEdit = (item: User) => {
    editTarget.value = item;
    form.name = item.name;
    form.email = item.email;
    form.password = '';
    showForm.value = true;
};

const submit = () => {
    const opts = { preserveScroll: true, onSuccess: () => { showForm.value = false; } };
    if (editTarget.value) {
        form.put(`/pengaturan/pengguna-backoffice/${editTarget.value.id}`, opts);
    } else {
        form.post('/pengaturan/pengguna-backoffice', opts);
    }
};

const showDelete = ref(false);
const deleteTarget = ref<User | null>(null);
const openDelete = (item: User) => { deleteTarget.value = item; showDelete.value = true; };
const confirmDelete = () => {
    if (!deleteTarget.value) return;
    router.delete(`/pengaturan/pengguna-backoffice/${deleteTarget.value.id}`, {
        preserveScroll: true,
        onSuccess: () => { showDelete.value = false; }
    });
};
</script>

<template>
    <Head title="Pengguna Backoffice" />
    <div class="space-y-6 p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-semibold text-gray-900 ">Manajemen Pengguna Backoffice</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Kelola akun administrator dan staf SPMI</p>
            </div>
            <button type="button" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700" @click="openCreate">
                <Plus class="size-4" /> Tambah Pengguna
            </button>
        </div>

        <div class="flex flex-wrap gap-4 items-center">
            <div class="relative w-full max-w-xs">
                <Search class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-gray-400" />
                <input v-model="search" type="text" placeholder="Cari nama atau email..." class="w-full rounded-lg border border-gray-300 py-2 pr-4 pl-9 text-sm outline-none transition focus:border-blue-500  " />
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-gray-200 shadow-sm dark:border-gray-700 bg-white dark:bg-gray-900">
            <table class="w-full text-sm text-left">
                <thead class="bg-blue-50 text-blue-700 border-b border-blue-100">
                    <tr>
                        <th class="px-4 py-3 font-medium text-gray-600 dark:text-gray-300">Nama Lengkap</th>
                        <th class="px-4 py-3 font-medium text-gray-600 dark:text-gray-300">Email</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600 dark:text-gray-300">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    <tr v-for="item in data.data" :key="item.id" class="bg-white transition hover:bg-gray-50 dark:bg-gray-900 dark:hover:bg-gray-800/50">
                        <td class="px-4 py-3 font-medium text-gray-900 ">
                            <div class="flex items-center gap-2">
                                <div class="size-8 rounded-full bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center text-blue-600 font-bold text-xs">
                                    {{ item.name.charAt(0) }}
                                </div>
                                {{ item.name }}
                            </div>
                        </td>
                        <td class="px-4 py-3 text-gray-500">
                            <div class="flex items-center gap-2">
                                <Mail class="size-3.5" /> {{ item.email }}
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <button type="button" class="p-1.5 text-gray-400 hover:text-blue-600" @click="openEdit(item)"><Edit2 class="size-4" /></button>
                                <button type="button" class="p-1.5 text-gray-400 hover:text-red-500" @click="openDelete(item)"><Trash2 class="size-4" /></button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Form -->
    <Modal :show="showForm" :title="editTarget ? 'Edit Pengguna' : 'Tambah Pengguna Baru'" max-width="md" @close="showForm = false">
        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                <div class="relative">
                    <UserCog class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-gray-400" />
                    <input v-model="form.name" type="text" placeholder="Masukkan nama..." class="w-full rounded-lg border pl-10 pr-3 py-2 text-sm  " />
                </div>
                <p v-if="form.errors.name" class="text-[11px] text-red-500 mt-1">{{ form.errors.name }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Alamat Email <span class="text-red-500">*</span></label>
                <div class="relative">
                    <Mail class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-gray-400" />
                    <input v-model="form.email" type="email" placeholder="email@contoh.com" class="w-full rounded-lg border pl-10 pr-3 py-2 text-sm  " />
                </div>
                <p v-if="form.errors.email" class="text-[11px] text-red-500 mt-1">{{ form.errors.email }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Kata Sandi <span v-if="!editTarget" class="text-red-500">*</span></label>
                <div class="relative">
                    <Key class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-gray-400" />
                    <input v-model="form.password" type="password" placeholder="••••••••" class="w-full rounded-lg border pl-10 pr-3 py-2 text-sm  " />
                </div>
                <p v-if="form.errors.password" class="text-[11px] text-red-500 mt-1">{{ form.errors.password }}</p>
                <p v-if="editTarget" class="text-[10px] text-gray-400 mt-1">Kosongkan jika tidak ingin mengubah kata sandi.</p>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" class="px-4 py-2 text-sm font-medium border rounded-lg hover:bg-gray-50" @click="showForm = false">Batal</button>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700" :disabled="form.processing">Simpan Pengguna</button>
            </div>
        </form>
    </Modal>

    <ConfirmDeleteModal :show="showDelete" message="Hapus pengguna ini? Tindakan ini tidak dapat dibatalkan." @close="showDelete = false" @confirm="confirmDelete" />
</template>
