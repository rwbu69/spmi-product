<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import ConfirmDeleteModal from '@/components/ConfirmDeleteModal.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import Pagination from '@/components/Pagination.vue';
import { PageHeader, DataTable, FormModal } from '@/components/index';
import { BaseButton, BaseInput, BaseTextarea, BaseSelect } from '@/components/index';
import { FormField, FormActions, SearchInput } from '@/components/index';

interface Role { id: number; name: string }
interface AuditeeItem { id: number; nama_auditee: string }
interface AuditorItem { id: number; nama: string }
interface FakultasItem { id: number; nama: string }
interface UnitPenunjangItem { id: number; nama_unit: string }

interface UserItem {
    id: number;
    name: string;
    username: string;
    roles: Role[];
    keterangan: string | null;
    is_active: boolean;
    auditee_id: number | null;
    auditor_id: number | null;
    auditee_pusat_id: number | null;
    unit_penunjang_id: number | null;
}

const props = defineProps<{
    data: {
        data: UserItem[];
        total: number;
        links: any[];
        current_page: number;
        per_page: number;
    };
    filters: { search?: string; group?: string };
    auditeeList: AuditeeItem[];
    auditorList: AuditorItem[];
    fakultasList: FakultasItem[];
    unitPenunjangList: UnitPenunjangItem[];
}>();

defineOptions({ layout: AppLayout });

const search = ref(props.filters.search ?? '');
const filterGroup = ref(props.filters.group ?? '');

const groupOptions = [
    { value: 'Prodi', label: 'Prodi' },
    { value: 'Auditor', label: 'Auditor' },
    { value: 'Fakultas', label: 'Fakultas' },
    { value: 'Unit Penunjang', label: 'Unit Penunjang' },
];

/**
 * Map role name from DB to display group name.
 * "Auditee" role → displayed as "Prodi"
 */
const roleToGroup = (roleName: string): string => {
    return roleName === 'Auditee' ? 'Prodi' : roleName;
};

const groupToRole = (group: string): string => {
    return group === 'Prodi' ? 'Auditee' : group;
};

let searchTimeout: any;
watch([search, filterGroup], () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get('/pengaturan/pengguna-portal', {
            search: search.value,
            group: filterGroup.value,
        }, { preserveState: true, replace: true });
    }, 400);
});

const showForm = ref(false);
const editTarget = ref<UserItem | null>(null);
const form = useForm({
    name: '',
    username: '',
    password: '',
    group: '',
    auditee_id: '' as string | number,
    auditor_id: '' as string | number,
    auditee_pusat_id: '' as string | number,
    unit_penunjang_id: '' as string | number,
    keterangan: '',
    is_active: true,
});

// Clear entity fields when group changes
watch(() => form.group, () => {
    form.auditee_id = '';
    form.auditor_id = '';
    form.auditee_pusat_id = '';
    form.unit_penunjang_id = '';
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
    form.password = '';
    form.group = roleToGroup(item.roles[0]?.name ?? '');
    form.auditee_id = item.auditee_id ?? '';
    form.auditor_id = item.auditor_id ?? '';
    form.auditee_pusat_id = item.auditee_pusat_id ?? '';
    form.unit_penunjang_id = item.unit_penunjang_id ?? '';
    form.keterangan = item.keterangan ?? '';
    form.is_active = item.is_active ?? true;
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
    const opts = { preserveScroll: true, onSuccess: () => closeForm() };
    editTarget.value
        ? form.put(`/pengaturan/pengguna-portal/${editTarget.value.id}`, opts)
        : form.post('/pengaturan/pengguna-portal', opts);
};

const showDelete = ref(false);
const deleteTarget = ref<UserItem | null>(null);
const deleting = ref(false);
const openDelete = (item: UserItem) => { deleteTarget.value = item; showDelete.value = true; };
const closeDelete = () => { showDelete.value = false; deleteTarget.value = null; };
const confirmDelete = () => {
    if (!deleteTarget.value) return;
    deleting.value = true;
    router.delete(`/pengaturan/pengguna-portal/${deleteTarget.value.id}`, {
        preserveScroll: true,
        onFinish: () => { deleting.value = false; closeDelete(); },
    });
};

/**
 * Get display name for the entity linked to a user based on their role.
 */
const getEntityName = (item: UserItem): string => {
    const role = item.roles[0]?.name;
    if (role === 'Auditee' && item.auditee_id) {
        const a = props.auditeeList.find(x => x.id === item.auditee_id);
        return a?.nama_auditee ?? '-';
    }
    if (role === 'Auditor' && item.auditor_id) {
        const a = props.auditorList.find(x => x.id === item.auditor_id);
        return a?.nama ?? '-';
    }
    if (role === 'Fakultas' && item.auditee_pusat_id) {
        const a = props.fakultasList.find(x => x.id === item.auditee_pusat_id);
        return a?.nama ?? '-';
    }
    if (role === 'Unit Penunjang' && item.unit_penunjang_id) {
        const a = props.unitPenunjangList.find(x => x.id === item.unit_penunjang_id);
        return a?.nama_unit ?? '-';
    }
    return '-';
};
</script>

<template>
    <Head title="Pengguna Portal" />
    <div class="space-y-6 p-6">

        <PageHeader title="Data Pengguna Portal" subtitle="Kelola akun pengguna portal (Prodi, Auditor, Fakultas, Unit Penunjang)">
            <template #actions>
                <BaseButton variant="primary" @click="openCreate">
                    Tambah Data
                </BaseButton>
            </template>
        </PageHeader>

        <!-- Filter Bar -->
        <div class="flex flex-wrap items-center gap-3">
            <SearchInput v-model="search" placeholder="Cari nama atau username..." />
            <BaseSelect v-model="filterGroup">
                <option value="">Semua Group</option>
                <option v-for="g in groupOptions" :key="g.value" :value="g.value">{{ g.label }}</option>
            </BaseSelect>
        </div>

        <!-- Table matching Foto 3: No, Aksi, Nama Pengguna, Nama Asli, Group, Status -->
        <DataTable :is-empty="data.data.length === 0" empty-message="Tidak ada pengguna terdaftar." :col-span="6">
            <template #head>
                <th class="px-4 py-3 font-medium text-gray-600">No</th>
                <th class="px-4 py-3 font-medium text-gray-600">Aksi</th>
                <th class="px-4 py-3 font-medium text-gray-600">Nama Pengguna</th>
                <th class="px-4 py-3 font-medium text-gray-600">Nama Asli</th>
                <th class="px-4 py-3 font-medium text-gray-600">Group</th>
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
                <td class="px-4 py-3 text-gray-600">{{ roleToGroup(item.roles[0]?.name ?? '-') }}</td>
                <td class="px-4 py-3">
                    <span :class="item.is_active !== false ? 'text-green-600' : 'text-red-500'" class="text-sm font-medium">
                        {{ item.is_active !== false ? 'Aktif' : 'Tidak Aktif' }}
                    </span>
                </td>
            </tr>
        </DataTable>

        <Pagination :links="data.links" :total="data.total" />

        <!-- Petunjuk section matching Foto 3 -->
        <div class="rounded-xl border border-gray-200 bg-white p-6">
            <h3 class="text-base font-semibold text-blue-700 mb-3">Petunjuk:</h3>
            <ul class="list-disc list-inside space-y-1 text-sm text-gray-600">
                <li>Gunakan tombol <span class="inline-block size-4 bg-yellow-500 rounded align-middle"></span> untuk mengubah data.</li>
                <li>Gunakan tombol <span class="inline-block size-4 bg-red-500 rounded align-middle"></span> untuk menghapus data.</li>
            </ul>
        </div>
    </div>

    <!-- Form Modal matching Foto 2 -->
    <FormModal :show="showForm" :title="editTarget ? 'Edit Pengguna' : 'Manajemen Pengguna'" max-width="2xl" @close="closeForm">
        <form @submit.prevent="submit" class="space-y-4">
            <FormField label="Nama Lengkap" :required="true" :error="form.errors.name">
                <BaseInput v-model="form.name" type="text" placeholder="" :error="form.errors.name" />
            </FormField>

            <FormField label="Nama Pengguna" :required="true" :error="form.errors.username">
                <BaseInput v-model="form.username" type="text" placeholder="" :error="form.errors.username" />
            </FormField>

            <FormField label="Password" :required="!editTarget" :error="form.errors.password"
                :hint="editTarget ? 'Kosongkan jika tidak ingin mengubah kata sandi.' : undefined">
                <BaseInput v-model="form.password" type="password" placeholder="" :error="form.errors.password" />
            </FormField>

            <FormField label="Group" :required="true" :error="form.errors.group">
                <BaseSelect v-model="form.group" :error="form.errors.group">
                    <option value="">-- PILIH --</option>
                    <option v-for="g in groupOptions" :key="g.value" :value="g.value">{{ g.label }}</option>
                </BaseSelect>
            </FormField>

            <!-- Conditional dropdown: Prodi → Auditee -->
            <FormField v-if="form.group === 'Prodi'" label="Auditee" :required="true" :error="form.errors.auditee_id">
                <BaseSelect v-model="form.auditee_id" :error="form.errors.auditee_id">
                    <option value="">-- PILIH --</option>
                    <option v-for="a in auditeeList" :key="a.id" :value="a.id">{{ a.nama_auditee }}</option>
                </BaseSelect>
            </FormField>

            <!-- Conditional dropdown: Auditor → Nama Auditor -->
            <FormField v-if="form.group === 'Auditor'" label="Nama Auditor" :required="true" :error="form.errors.auditor_id">
                <BaseSelect v-model="form.auditor_id" :error="form.errors.auditor_id">
                    <option value="">-- PILIH --</option>
                    <option v-for="a in auditorList" :key="a.id" :value="a.id">{{ a.nama }}</option>
                </BaseSelect>
            </FormField>

            <!-- Conditional dropdown: Fakultas → Fakultas -->
            <FormField v-if="form.group === 'Fakultas'" label="Fakultas" :required="true" :error="form.errors.auditee_pusat_id">
                <BaseSelect v-model="form.auditee_pusat_id" :error="form.errors.auditee_pusat_id">
                    <option value="">-- PILIH --</option>
                    <option v-for="f in fakultasList" :key="f.id" :value="f.id">{{ f.nama }}</option>
                </BaseSelect>
            </FormField>

            <!-- Conditional dropdown: Unit Penunjang → Unit Penunjang -->
            <FormField v-if="form.group === 'Unit Penunjang'" label="Unit Penunjang" :required="true" :error="form.errors.unit_penunjang_id">
                <BaseSelect v-model="form.unit_penunjang_id" :error="form.errors.unit_penunjang_id">
                    <option value="">-- PILIH --</option>
                    <option v-for="u in unitPenunjangList" :key="u.id" :value="u.id">{{ u.nama_unit }}</option>
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

            <!-- Petunjuk in form -->
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
