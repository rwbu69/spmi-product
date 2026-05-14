<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Edit2, Plus, Search, Trash2, UserCog, Mail, Key, User, Shield } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import ConfirmDeleteModal from '@/components/ConfirmDeleteModal.vue';
import Modal from '@/components/Modal.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import Pagination from '@/components/Pagination.vue';

interface Role { id: number; name: string }
interface UserItem {
    id: number;
    name: string;
    username: string;
    email: string;
    roles: Role[];
}

const props = defineProps<{
    data: {
        data: UserItem[];
        total: number;
        links: any[];
        current_page: number;
        per_page: number;
    };
    filters: { search?: string; role?: string };
    roleList: Role[];
}>();

defineOptions({ layout: AppLayout });

const search = ref(props.filters.search ?? '');
const filterRole = ref(props.filters.role ?? '');

const ROLE_COLORS: Record<string, string> = {
    Admin:          'bg-purple-100 text-purple-700',
    Auditor:        'bg-blue-100 text-blue-700',
    Fakultas:       'bg-teal-100 text-teal-700',
    Auditee:        'bg-orange-100 text-orange-700',
    'Unit Penunjang': 'bg-green-100 text-green-700',
};

const roleColor = (name: string) => ROLE_COLORS[name] ?? 'bg-gray-100 text-gray-700';

let searchTimeout: any;
watch([search, filterRole], () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get('/pengaturan/pengguna-backoffice', { search: search.value, role: filterRole.value }, { preserveState: true, replace: true });
    }, 400);
});

// Form
const showForm = ref(false);
const editTarget = ref<UserItem | null>(null);
const form = useForm({
    name: '',
    username: '',
    email: '',
    password: '',
    role: '',
});

const openCreate = () => {
    editTarget.value = null;
    form.reset();
    form.clearErrors();
    showForm.value = true;
};

const openEdit = (item: UserItem) => {
    editTarget.value = item;
    form.name = item.name;
    form.username = item.username;
    form.email = item.email;
    form.password = '';
    form.role = item.roles[0]?.name ?? '';
    form.clearErrors();
    showForm.value = true;
};

const closeForm = () => { showForm.value = false; editTarget.value = null; form.reset(); form.clearErrors(); };

const submit = () => {
    const opts = { preserveScroll: true, onSuccess: () => closeForm() };
    if (editTarget.value) {
        form.put(`/pengaturan/pengguna-backoffice/${editTarget.value.id}`, opts);
    } else {
        form.post('/pengaturan/pengguna-backoffice', opts);
    }
};

const showDelete = ref(false);
const deleteTarget = ref<UserItem | null>(null);
const deleting = ref(false);
const openDelete = (item: UserItem) => { deleteTarget.value = item; showDelete.value = true; };
const closeDelete = () => { showDelete.value = false; deleteTarget.value = null; };
const confirmDelete = () => {
    if (!deleteTarget.value) return;
    deleting.value = true;
    router.delete(`/pengaturan/pengguna-backoffice/${deleteTarget.value.id}`, {
        preserveScroll: true,
        onFinish: () => { deleting.value = false; closeDelete(); },
    });
};
</script>

<template>
    <Head title="Pengguna Backoffice" />
    <div class="space-y-6 p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-semibold text-gray-900">Manajemen Pengguna</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Kelola akun pengguna dan penugasan role</p>
            </div>
            <button type="button" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700" @click="openCreate">
                <Plus class="size-4" /> Tambah Pengguna
            </button>
        </div>

        <div class="flex flex-wrap gap-4 items-center">
            <div class="relative w-full max-w-xs">
                <Search class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-gray-400" />
                <input v-model="search" type="text" placeholder="Cari nama, username, atau email..." class="w-full rounded-lg border border-gray-300 py-2 pr-4 pl-9 text-sm outline-none transition focus:border-blue-500" />
            </div>
            <select v-model="filterRole" class="rounded-lg border border-gray-300 py-2 px-3 text-sm">
                <option value="">Semua Role</option>
                <option v-for="r in roleList" :key="r.id" :value="r.name">{{ r.name }}</option>
            </select>
        </div>

        <div class="overflow-hidden rounded-xl border border-gray-200 shadow-sm dark:border-gray-700 bg-white dark:bg-gray-900">
            <table class="w-full text-sm text-left">
                <thead class="bg-blue-50 text-blue-700 border-b border-blue-100">
                    <tr>
                        <th class="px-4 py-3 font-medium text-gray-600 dark:text-gray-300">No</th>
                        <th class="px-4 py-3 font-medium text-gray-600 dark:text-gray-300">Nama Lengkap</th>
                        <th class="px-4 py-3 font-medium text-gray-600 dark:text-gray-300">Username</th>
                        <th class="px-4 py-3 font-medium text-gray-600 dark:text-gray-300">Email</th>
                        <th class="px-4 py-3 font-medium text-gray-600 dark:text-gray-300">Role</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600 dark:text-gray-300">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    <tr v-if="data.data.length === 0">
                        <td colspan="6" class="px-4 py-12 text-center text-gray-400">Tidak ada pengguna terdaftar.</td>
                    </tr>
                    <tr v-for="(item, i) in data.data" :key="item.id" class="bg-white transition hover:bg-gray-50 dark:bg-gray-900 dark:hover:bg-gray-800/50">
                        <td class="px-4 py-3 text-gray-500">{{ (data.current_page - 1) * data.per_page + i + 1 }}</td>
                        <td class="px-4 py-3 font-medium text-gray-900">
                            <div class="flex items-center gap-2">
                                <div class="size-8 rounded-full bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center text-blue-600 font-bold text-xs">
                                    {{ item.name.charAt(0).toUpperCase() }}
                                </div>
                                {{ item.name }}
                            </div>
                        </td>
                        <td class="px-4 py-3 text-gray-500">
                            <div class="flex items-center gap-1.5">
                                <User class="size-3.5 text-gray-400" /> {{ item.username }}
                            </div>
                        </td>
                        <td class="px-4 py-3 text-gray-500">
                            <div class="flex items-center gap-1.5">
                                <Mail class="size-3.5" /> {{ item.email }}
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span v-for="role in item.roles" :key="role.id"
                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase"
                                :class="roleColor(role.name)"
                            >
                                <Shield class="size-2.5" /> {{ role.name }}
                            </span>
                            <span v-if="item.roles.length === 0" class="text-gray-400 text-xs">Belum ada role</span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <button type="button" class="rounded-md p-1.5 text-blue-600 transition hover:bg-blue-50 dark:hover:bg-blue-900/20" @click="openEdit(item)"><Edit2 class="size-4" /></button>
                                <button type="button" class="rounded-md p-1.5 text-red-500 transition hover:bg-red-50 dark:hover:bg-red-900/20" @click="openDelete(item)"><Trash2 class="size-4" /></button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Pagination :links="data.links" :total="data.total" />
    </div>

    <!-- Modal Form -->
    <Modal :show="showForm" :title="editTarget ? 'Edit Pengguna' : 'Tambah Pengguna Baru'" max-width="md" @close="closeForm">
        <form @submit.prevent="submit" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Lengkap <span class="text-red-500">*</span></label>
                <div class="relative">
                    <UserCog class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-gray-400" />
                    <input v-model="form.name" type="text" placeholder="Masukkan nama..." class="w-full rounded-lg border pl-10 pr-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200" :class="form.errors.name ? 'border-red-400' : 'border-gray-300'" />
                </div>
                <p v-if="form.errors.name" class="text-[11px] text-red-500 mt-1">{{ form.errors.name }}</p>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Username <span class="text-red-500">*</span></label>
                <div class="relative">
                    <User class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-gray-400" />
                    <input v-model="form.username" type="text" placeholder="nama_pengguna" class="w-full rounded-lg border pl-10 pr-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200" :class="form.errors.username ? 'border-red-400' : 'border-gray-300'" />
                </div>
                <p v-if="form.errors.username" class="text-[11px] text-red-500 mt-1">{{ form.errors.username }}</p>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Alamat Email <span class="text-red-500">*</span></label>
                <div class="relative">
                    <Mail class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-gray-400" />
                    <input v-model="form.email" type="email" placeholder="email@contoh.com" class="w-full rounded-lg border pl-10 pr-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200" :class="form.errors.email ? 'border-red-400' : 'border-gray-300'" />
                </div>
                <p v-if="form.errors.email" class="text-[11px] text-red-500 mt-1">{{ form.errors.email }}</p>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Kata Sandi <span v-if="!editTarget" class="text-red-500">*</span></label>
                <div class="relative">
                    <Key class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-gray-400" />
                    <input v-model="form.password" type="password" placeholder="••••••••" class="w-full rounded-lg border pl-10 pr-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200" :class="form.errors.password ? 'border-red-400' : 'border-gray-300'" />
                </div>
                <p v-if="form.errors.password" class="text-[11px] text-red-500 mt-1">{{ form.errors.password }}</p>
                <p v-if="editTarget" class="text-[10px] text-gray-400 mt-1">Kosongkan jika tidak ingin mengubah kata sandi.</p>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Role <span class="text-red-500">*</span></label>
                <div class="relative">
                    <Shield class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-gray-400" />
                    <select v-model="form.role" class="w-full rounded-lg border pl-10 pr-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200" :class="form.errors.role ? 'border-red-400' : 'border-gray-300'">
                        <option value="">-- Pilih Role --</option>
                        <option v-for="r in roleList" :key="r.id" :value="r.name">{{ r.name }}</option>
                    </select>
                </div>
                <p v-if="form.errors.role" class="text-[11px] text-red-500 mt-1">{{ form.errors.role }}</p>
            </div>

            <div class="sm:col-span-2 flex justify-end gap-3 pt-2">
                <button type="button" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-800" @click="closeForm">Batal</button>
                <button type="submit" :disabled="form.processing" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700 disabled:opacity-60">
                    {{ form.processing ? 'Menyimpan...' : 'Simpan Pengguna' }}
                </button>
            </div>
        </form>
    </Modal>

    <ConfirmDeleteModal :show="showDelete" :message="`Hapus pengguna '${deleteTarget?.name}'? Tindakan ini tidak dapat dibatalkan.`" :processing="deleting" @close="closeDelete" @confirm="confirmDelete" />
</template>

