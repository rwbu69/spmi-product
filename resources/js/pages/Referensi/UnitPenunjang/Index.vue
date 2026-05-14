<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import ConfirmDeleteModal from '@/components/ConfirmDeleteModal.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import Pagination from '@/components/Pagination.vue';
import { PageHeader, DataTable, FormModal } from '@/components/index';
import { BaseButton, BaseInput, BaseTextarea, BaseSelect } from '@/components/index';
import { FormField, FormActions, SearchInput } from '@/components/index';

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
const form = useForm({ kode: '', nama_unit: '', auditee_pusat_id: '' as string | number, jenjang: '', alamat: '', keterangan: '' });

const openCreate = () => { editTarget.value = null; form.reset(); form.clearErrors(); showForm.value = true; };
const openEdit = (item: UnitPenunjang) => {
    editTarget.value = item;
    form.kode = item.kode; form.nama_unit = item.nama_unit;
    form.auditee_pusat_id = item.auditee_pusat.id;
    form.jenjang = item.jenjang ?? ''; form.alamat = item.alamat ?? ''; form.keterangan = item.keterangan ?? '';
    form.clearErrors(); showForm.value = true;
};
const closeForm = () => { showForm.value = false; editTarget.value = null; form.reset(); form.clearErrors(); };
const submit = () => {
    const opts = { preserveScroll: true, onSuccess: () => closeForm() };
    editTarget.value ? form.put(`/referensi/unit-penunjang/${editTarget.value.id}`, opts) : form.post('/referensi/unit-penunjang', opts);
};

const showDelete = ref(false);
const deleteTarget = ref<UnitPenunjang | null>(null);
const deleting = ref(false);
const openDelete = (item: UnitPenunjang) => { deleteTarget.value = item; showDelete.value = true; };
const closeDelete = () => { showDelete.value = false; deleteTarget.value = null; };
const confirmDelete = () => {
    if (!deleteTarget.value) return;
    deleting.value = true;
    router.delete(`/referensi/unit-penunjang/${deleteTarget.value.id}`, {
        preserveScroll: true,
        onFinish: () => { deleting.value = false; closeDelete(); },
    });
};
</script>

<template>
    <Head title="Unit Penunjang" />
    <div class="space-y-6 p-6">

        <PageHeader title="Unit Penunjang" subtitle="Kelola data unit penunjang akademik">
            <template #actions>
                <BaseButton id="btn-tambah-unit" variant="primary" @click="openCreate">Tambah</BaseButton>
            </template>
        </PageHeader>

        <SearchInput v-model="search" placeholder="Cari kode / nama..." />

        <DataTable :is-empty="data.data.length === 0" empty-message="Tidak ada data." :col-span="7">
            <template #head>
                <th class="px-4 py-3 text-left font-medium text-gray-600">No</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Kode</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Nama Unit</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Jenjang</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Pusat</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Keterangan</th>
                <th class="px-4 py-3 text-center font-medium text-gray-600">Aksi</th>
            </template>

            <tr v-for="(item, i) in data.data" :key="item.id" class="bg-white hover:bg-gray-50 transition">
                <td class="px-4 py-3 text-gray-500">{{ (data.current_page - 1) * data.per_page + i + 1 }}</td>
                <td class="px-4 py-3 font-mono text-xs font-medium text-gray-700">{{ item.kode }}</td>
                <td class="px-4 py-3 font-medium text-gray-900">{{ item.nama_unit }}</td>
                <td class="px-4 py-3 text-gray-500">{{ item.jenjang ?? '-' }}</td>
                <td class="px-4 py-3 text-gray-500">{{ item.auditee_pusat?.nama ?? '-' }}</td>
                <td class="px-4 py-3 text-gray-500 text-xs max-w-xs whitespace-normal break-words">{{ item.keterangan || '-' }}</td>
                <td class="px-4 py-3">
                    <div class="flex items-center justify-center gap-1">
                        <BaseButton :id="`btn-edit-unit-${item.id}`" variant="ghost" size="sm" icon-only title="Edit" @click="openEdit(item)">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </BaseButton>
                        <BaseButton :id="`btn-delete-unit-${item.id}`" variant="ghost" size="sm" icon-only title="Hapus" @click="openDelete(item)">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4 text-red-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                        </BaseButton>
                    </div>
                </td>
            </tr>
        </DataTable>

        <Pagination :links="data.links" :total="data.total" />
    </div>

    <FormModal :show="showForm" :title="editTarget ? 'Edit Unit Penunjang' : 'Tambah Unit Penunjang'" max-width="lg" @close="closeForm">
        <form @submit.prevent="submit" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <FormField label="Kode" :required="true" :error="form.errors.kode">
                    <BaseInput v-model="form.kode" type="text" placeholder="Contoh: UPT-01" :error="form.errors.kode" />
                </FormField>
            </div>
            <div>
                <FormField label="Jenjang">
                    <BaseSelect v-model="form.jenjang">
                        <option value="">-- Pilih Jenjang --</option>
                        <option v-for="j in jenjangOptions" :key="j" :value="j">{{ j }}</option>
                    </BaseSelect>
                </FormField>
            </div>
            <div class="sm:col-span-2">
                <FormField label="Nama Unit" :required="true" :error="form.errors.nama_unit">
                    <BaseInput v-model="form.nama_unit" type="text" placeholder="Contoh: Perpustakaan Pusat" :error="form.errors.nama_unit" />
                </FormField>
            </div>
            <div class="sm:col-span-2">
                <FormField label="Auditee Pusat" :required="true" :error="form.errors.auditee_pusat_id">
                    <BaseSelect v-model="form.auditee_pusat_id" :error="form.errors.auditee_pusat_id">
                        <option value="">-- Pilih Auditee Pusat --</option>
                        <option v-for="ap in auditeePusat" :key="ap.id" :value="ap.id">{{ ap.nama }}</option>
                    </BaseSelect>
                </FormField>
            </div>
            <div class="sm:col-span-2">
                <FormField label="Alamat">
                    <BaseTextarea v-model="form.alamat" :rows="2" />
                </FormField>
            </div>
            <div class="sm:col-span-2">
                <FormField label="Keterangan">
                    <BaseTextarea v-model="form.keterangan" :rows="2" />
                </FormField>
            </div>
            <div class="sm:col-span-2">
                <FormActions :processing="form.processing" @cancel="closeForm" />
            </div>
        </form>
    </FormModal>

    <ConfirmDeleteModal :show="showDelete" :message="`Hapus unit penunjang '${deleteTarget?.nama_unit}'?`"
        :processing="deleting" @close="closeDelete" @confirm="confirmDelete" />
</template>
