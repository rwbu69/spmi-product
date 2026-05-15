<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import ConfirmDeleteModal from '@/components/ConfirmDeleteModal.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import Pagination from '@/components/Pagination.vue';
import { PageHeader, DataTable, FormModal } from '@/components/index';
import { BaseButton, BaseInput, BaseTextarea, BaseSelect } from '@/components/index';
import { FormField, FormActions, SearchInput } from '@/components/index';

interface Role { id: number; name: string }
interface AuditeePusat { id: number; nama: string }
interface UserItem {
    id: number;
    name: string;
    username: string;
    roles: Role[];
    keterangan: string | null;
    is_active: boolean;
    auditee_pusat_id: number | null;
    unit_display: string;
}

const props = defineProps<{
    data: {
        data: UserItem[];
        total: number;
        links: any[];
        current_page: number;
        per_page: number;
    };
    filters: { search?: string };
    auditeePusatList: AuditeePusat[];
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

const showForm = ref(false);
const editTarget = ref<UserItem | null>(null);
const form = useForm({
    name: '',
    username: '',
    password: '',
    auditee_pusat_id: '' as string | number,
    keterangan: '',
    is_active: true,
});

const openCreate = () => { editTarget.value = null; form.reset(); form.clearErrors(); showForm.value = true; };
const openEdit = (item: UserItem) => {
    editTarget.value = item;
    form.name = item.name;
    form.username = item.username;
    form.password = '';
    form.auditee_pusat_id = item.auditee_pusat_id ?? '';
    form.keterangan = item.keterangan ?? '';
    form.is_active = item.is_active ?? true;
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

        <PageHeader title="Manajemen Pengguna" subtitle="Kelola akun pengguna backoffice (Admin)">
            <template #actions>
                <BaseButton variant="primary" @click="openCreate">
                    Tambah
                </BaseButton>
            </template>
        </PageHeader>

        <SearchInput v-model="search" placeholder="Cari nama atau username..." />

        <!-- Table matching Foto 4: No, Aksi, Nama Pengguna, Nama Lengkap, Group, Unit, Status -->
        <DataTable :is-empty="data.data.length === 0" empty-message="Tidak ada pengguna terdaftar." :col-span="7">
            <template #head>
                <th class="px-4 py-3 font-medium text-gray-600">No</th>
                <th class="px-4 py-3 font-medium text-gray-600">Aksi</th>
                <th class="px-4 py-3 font-medium text-gray-600">Nama Pengguna</th>
                <th class="px-4 py-3 font-medium text-gray-600">Nama Lengkap</th>
                <th class="px-4 py-3 font-medium text-gray-600">Group</th>
                <th class="px-4 py-3 font-medium text-gray-600">Unit</th>
                <th class="px-4 py-3 font-medium text-gray-600">Status</th>
            </template>

            <tr v-for="(item, i) in data.data" :key="item.id" class="bg-white hover:bg-gray-50 transition">
                <td class="px-4 py-3 text-gray-500">{{ (data.current_page - 1) * data.per_page + i + 1 }}</td>
                <td class="px-4 py-3">
                    <div class="flex items-center gap-1">
                        <BaseButton variant="ghost" size="sm" icon-only title="Edit" @click="openEdit(item)">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </BaseButton>
                        <BaseButton variant="ghost" size="sm" icon-only title="Hapus" @click="openDelete(item)">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4 text-red-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                        </BaseButton>
                    </div>
                </td>
                <td class="px-4 py-3 text-gray-700">{{ item.username }}</td>
                <td class="px-4 py-3 font-medium text-gray-900">{{ item.name }}</td>
                <td class="px-4 py-3 text-gray-600">{{ item.roles[0]?.name ?? '-' }}</td>
                <td class="px-4 py-3 text-gray-500">{{ item.unit_display || '-' }}</td>
                <td class="px-4 py-3">
                    <span :class="item.is_active ? 'text-green-600' : 'text-red-500'" class="text-sm font-medium">
                        {{ item.is_active ? 'aktif' : 'tidak aktif' }}
                    </span>
                </td>
            </tr>
        </DataTable>

        <Pagination :links="data.links" :total="data.total" />

        <!-- Petunjuk section matching Foto 4 -->
        <div class="rounded-xl border border-gray-200 bg-white p-6">
            <h3 class="text-base font-semibold text-blue-700 mb-3">Petunjuk:</h3>
            <ul class="list-disc list-inside space-y-1 text-sm text-gray-600">
                <li>Pengguna dapat dicari dengan menggunakan nama pengguna ataupun nama asli.</li>
                <li><span class="text-blue-600 font-medium">Nama pengguna</span> adalah nama yang dipakai untuk login, sedangkan <span class="text-blue-600 font-medium">nama asli</span> adalah nama asli dari pengguna tersebut.</li>
            </ul>
        </div>
    </div>

    <!-- Form Modal matching Foto 5 -->
    <FormModal :show="showForm" :title="editTarget ? 'Edit Pengguna' : 'Manajemen Pengguna'" max-width="2xl" @close="closeForm">
        <form @submit.prevent="submit" class="space-y-4">
            <FormField label="Nama Lengkap" :required="true" :error="form.errors.name">
                <BaseInput v-model="form.name" type="text" placeholder="" :error="form.errors.name" />
            </FormField>

            <FormField label="Username" :required="true" :error="form.errors.username">
                <BaseInput v-model="form.username" type="text" placeholder="" :error="form.errors.username" />
            </FormField>

            <FormField label="Password" :required="!editTarget" :error="form.errors.password"
                :hint="editTarget ? 'Kosongkan jika tidak ingin mengubah kata sandi.' : undefined">
                <BaseInput v-model="form.password" type="password" placeholder="" :error="form.errors.password" />
            </FormField>

            <FormField label="Unit Kerja" :error="form.errors.auditee_pusat_id">
                <BaseSelect v-model="form.auditee_pusat_id" :error="form.errors.auditee_pusat_id">
                    <option value="">-- PILIH --</option>
                    <option v-for="ap in auditeePusatList" :key="ap.id" :value="ap.id">{{ ap.nama }}</option>
                </BaseSelect>
            </FormField>

            <FormField label="Satuan Kerja">
                <BaseSelect disabled>
                    <option value="">-- PILIH --</option>
                </BaseSelect>
            </FormField>

            <FormField label="Group">
                <BaseSelect disabled>
                    <option value="">Admin</option>
                </BaseSelect>
            </FormField>

            <FormField label="Keterangan">
                <BaseTextarea v-model="form.keterangan" :rows="3" placeholder="" />
            </FormField>

            <FormField label="Status">
                <div class="flex items-center gap-6 pt-1">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" :value="true" v-model="form.is_active" class="text-blue-600" />
                        <span class="text-sm">Aktif</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" :value="false" v-model="form.is_active" class="text-blue-600" />
                        <span class="text-sm">Tidak Aktif</span>
                    </label>
                </div>
            </FormField>

            <FormActions :processing="form.processing" submit-label="Simpan" @cancel="closeForm" />

            <!-- Petunjuk in form matching Foto 5 -->
            <div class="rounded-lg border border-blue-200 bg-blue-50/50 p-4 mt-4">
                <h4 class="text-sm font-semibold text-blue-700 mb-2">Petunjuk</h4>
                <ul class="list-disc list-inside text-xs text-blue-600 space-y-1">
                    <li>Nama pengguna adalah nama yang dipakai untuk login, sedangkan nama asli adalah nama asli dari pengguna tersebut.</li>
                </ul>
            </div>
        </form>
    </FormModal>

    <ConfirmDeleteModal :show="showDelete"
        :message="`Hapus pengguna '${deleteTarget?.name}'? Tindakan ini tidak dapat dibatalkan.`"
        :processing="deleting" @close="closeDelete" @confirm="confirmDelete" />
</template>
