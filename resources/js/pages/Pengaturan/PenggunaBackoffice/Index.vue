<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Mail, Key, User, Shield } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import ConfirmDeleteModal from '@/components/ConfirmDeleteModal.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import Pagination from '@/components/Pagination.vue';
import { PageHeader, DataTable, FormModal } from '@/components/index';
import { BaseButton, BaseInput, BaseSelect } from '@/components/index';
import { FormField, FormActions, SearchInput } from '@/components/index';

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
    Admin:           'bg-purple-100 text-purple-700',
    Auditor:         'bg-blue-100 text-blue-700',
    Fakultas:        'bg-teal-100 text-teal-700',
    Auditee:         'bg-orange-100 text-orange-700',
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

const showForm = ref(false);
const editTarget = ref<UserItem | null>(null);
const form = useForm({ name: '', username: '', email: '', password: '', role: '' });

const openCreate = () => { editTarget.value = null; form.reset(); form.clearErrors(); showForm.value = true; };
const openEdit = (item: UserItem) => {
    editTarget.value = item;
    form.name = item.name; form.username = item.username;
    form.email = item.email; form.password = '';
    form.role = item.roles[0]?.name ?? '';
    form.clearErrors(); showForm.value = true;
};
const closeForm = () => { showForm.value = false; editTarget.value = null; form.reset(); form.clearErrors(); };
const submit = () => {
    const opts = { preserveScroll: true, onSuccess: () => closeForm() };
    editTarget.value
        ? form.put(`/pengaturan/pengguna-backoffice/${editTarget.value.id}`, opts)
        : form.post('/pengaturan/pengguna-backoffice', opts);
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

        <PageHeader title="Manajemen Pengguna" subtitle="Kelola akun pengguna dan penugasan role">
            <template #actions>
                <BaseButton variant="primary" @click="openCreate">
                    Tambah Pengguna
                </BaseButton>
            </template>
        </PageHeader>

        <!-- Filter Bar -->
        <div class="flex flex-wrap items-center gap-3">
            <SearchInput v-model="search" placeholder="Cari nama, username, atau email..." />
            <BaseSelect v-model="filterRole">
                <option value="">Semua Role</option>
                <option v-for="r in roleList" :key="r.id" :value="r.name">{{ r.name }}</option>
            </BaseSelect>
        </div>

        <DataTable :is-empty="data.data.length === 0" empty-message="Tidak ada pengguna terdaftar." :col-span="6">
            <template #head>
                <th class="px-4 py-3 font-medium text-gray-600">No</th>
                <th class="px-4 py-3 font-medium text-gray-600">Nama Lengkap</th>
                <th class="px-4 py-3 font-medium text-gray-600">Username</th>
                <th class="px-4 py-3 font-medium text-gray-600">Email</th>
                <th class="px-4 py-3 font-medium text-gray-600">Role</th>
                <th class="px-4 py-3 text-center font-medium text-gray-600">Aksi</th>
            </template>

            <tr v-for="(item, i) in data.data" :key="item.id" class="bg-white hover:bg-gray-50 transition">
                <td class="px-4 py-3 text-gray-500">{{ (data.current_page - 1) * data.per_page + i + 1 }}</td>
                <td class="px-4 py-3 font-medium text-gray-900">
                    <div class="flex items-center gap-2">
                        <div class="size-8 rounded-full bg-blue-50 flex items-center justify-center text-blue-600 font-bold text-xs">
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
                        :class="roleColor(role.name)">
                        <Shield class="size-2.5" /> {{ role.name }}
                    </span>
                    <span v-if="item.roles.length === 0" class="text-gray-400 text-xs">Belum ada role</span>
                </td>
                <td class="px-4 py-3">
                    <div class="flex items-center justify-center gap-1">
                        <BaseButton variant="ghost" size="sm" icon-only title="Edit" @click="openEdit(item)">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </BaseButton>
                        <BaseButton variant="ghost" size="sm" icon-only title="Hapus" @click="openDelete(item)">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4 text-red-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                        </BaseButton>
                    </div>
                </td>
            </tr>
        </DataTable>

        <Pagination :links="data.links" :total="data.total" />
    </div>

    <FormModal :show="showForm" :title="editTarget ? 'Edit Pengguna' : 'Tambah Pengguna Baru'" max-width="md" @close="closeForm">
        <form @submit.prevent="submit" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <FormField label="Nama Lengkap" :required="true" :error="form.errors.name">
                    <BaseInput v-model="form.name" type="text" placeholder="Masukkan nama..." :error="form.errors.name" />
                </FormField>
            </div>
            <div>
                <FormField label="Username" :required="true" :error="form.errors.username">
                    <BaseInput v-model="form.username" type="text" placeholder="nama_pengguna" :error="form.errors.username" />
                </FormField>
            </div>
            <div>
                <FormField label="Alamat Email" :required="true" :error="form.errors.email">
                    <BaseInput v-model="form.email" type="email" placeholder="email@contoh.com" :error="form.errors.email" />
                </FormField>
            </div>
            <div>
                <FormField label="Kata Sandi" :required="!editTarget" :error="form.errors.password"
                    :hint="editTarget ? 'Kosongkan jika tidak ingin mengubah kata sandi.' : undefined">
                    <BaseInput v-model="form.password" type="password" placeholder="••••••••" :error="form.errors.password" />
                </FormField>
            </div>
            <div>
                <FormField label="Role" :required="true" :error="form.errors.role">
                    <BaseSelect v-model="form.role" :error="form.errors.role">
                        <option value="">-- Pilih Role --</option>
                        <option v-for="r in roleList" :key="r.id" :value="r.name">{{ r.name }}</option>
                    </BaseSelect>
                </FormField>
            </div>
            <div class="sm:col-span-2">
                <FormActions :processing="form.processing" submit-label="Simpan Pengguna" @cancel="closeForm" />
            </div>
        </form>
    </FormModal>

    <ConfirmDeleteModal :show="showDelete"
        :message="`Hapus pengguna '${deleteTarget?.name}'? Tindakan ini tidak dapat dibatalkan.`"
        :processing="deleting" @close="closeDelete" @confirm="confirmDelete" />
</template>
