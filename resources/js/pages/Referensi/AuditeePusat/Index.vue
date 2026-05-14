<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Users } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import Pagination from '@/components/Pagination.vue';
import ConfirmDeleteModal from '@/components/ConfirmDeleteModal.vue';
import { PageHeader, DataTable, FormModal } from '@/components/index';
import { BaseButton, BaseInput } from '@/components/index';
import { FormField, FormActions, SearchInput } from '@/components/index';

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

const showForm = ref(false);
const editTarget = ref<AuditeePusat | null>(null);
const form = useForm({ nama: '' });

const openCreate = () => { editTarget.value = null; form.reset(); form.clearErrors(); showForm.value = true; };
const openEdit = (item: AuditeePusat) => { editTarget.value = item; form.nama = item.nama; form.clearErrors(); showForm.value = true; };
const closeForm = () => { showForm.value = false; editTarget.value = null; form.reset(); form.clearErrors(); };
const submit = () => {
    const opts = { preserveScroll: true, onSuccess: () => closeForm() };
    editTarget.value ? form.put(`/referensi/auditee-pusat/${editTarget.value.id}`, opts) : form.post('/referensi/auditee-pusat', opts);
};

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

        <PageHeader title="Auditee Pusat" subtitle="Kelola data institusi/lembaga pusat">
            <template #actions>
                <BaseButton id="btn-tambah-auditee-pusat" variant="primary" @click="openCreate">Tambah</BaseButton>
            </template>
        </PageHeader>

        <SearchInput v-model="search" placeholder="Cari nama..." />

        <DataTable :is-empty="data.data.length === 0" empty-message="Tidak ada data." :col-span="4">
            <template #head>
                <th class="px-4 py-3 text-left font-medium text-gray-600">No</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Nama</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Jumlah Auditee</th>
                <th class="px-4 py-3 text-center font-medium text-gray-600">Aksi</th>
            </template>

            <tr v-for="(item, i) in data.data" :key="item.id" class="bg-white hover:bg-gray-50 transition">
                <td class="px-4 py-3 text-gray-500">{{ (data.current_page - 1) * data.per_page + i + 1 }}</td>
                <td class="px-4 py-3 font-medium text-gray-900">{{ item.nama }}</td>
                <td class="px-4 py-3">
                    <span class="inline-flex items-center gap-1.5 text-gray-500">
                        <Users class="size-3.5" /> {{ item.auditee_count }} auditee
                    </span>
                </td>
                <td class="px-4 py-3">
                    <div class="flex items-center justify-center gap-1">
                        <BaseButton :id="`btn-edit-ap-${item.id}`" variant="ghost" size="sm" icon-only title="Edit" @click="openEdit(item)">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </BaseButton>
                        <BaseButton :id="`btn-delete-ap-${item.id}`" variant="ghost" size="sm" icon-only title="Hapus" @click="openDelete(item)">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4 text-red-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                        </BaseButton>
                    </div>
                </td>
            </tr>
        </DataTable>

        <Pagination :links="data.links" :total="data.total" />
    </div>

    <FormModal :show="showForm" :title="editTarget ? 'Edit Auditee Pusat' : 'Tambah Auditee Pusat'" max-width="sm" @close="closeForm">
        <form @submit.prevent="submit" class="space-y-4">
            <FormField label="Nama" :required="true" :error="form.errors.nama">
                <BaseInput v-model="form.nama" type="text" placeholder="Contoh: Nama Universitas" :error="form.errors.nama" />
            </FormField>
            <FormActions :processing="form.processing" @cancel="closeForm" />
        </form>
    </FormModal>

    <ConfirmDeleteModal :show="showDelete" :message="`Hapus auditee pusat '${deleteTarget?.nama}'?`"
        :processing="deleting" @close="closeDelete" @confirm="confirmDelete" />
</template>
