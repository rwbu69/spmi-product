<script setup lang="ts">
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import Pagination from '@/components/Pagination.vue';
import ConfirmDeleteModal from '@/components/ConfirmDeleteModal.vue';
import { PageHeader, DataTable, FormModal } from '@/components/index';
import { BaseButton, BaseInput, BaseTextarea } from '@/components/index';
import { FormField, FormActions, SearchInput } from '@/components/index';

interface LembagaAkreditasi {
    id: number;
    nama_lembaga: string;
    keterangan: string | null;
}

interface PageProps {
    data: {
        data: LembagaAkreditasi[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: { url: string | null; label: string; active: boolean }[];
    };
    filters: { search?: string };
    flash: { success?: string; error?: string };
}

defineOptions({ layout: AppLayout });

const page = usePage<PageProps>();
const props = defineProps<PageProps>();
const flash = computed(() => page.props.flash ?? {});

const search = ref(props.filters.search ?? '');
let searchTimeout: ReturnType<typeof setTimeout>;
watch(search, (val) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get('/referensi/lembaga-akreditasi', { search: val }, { preserveState: true, replace: true });
    }, 400);
});

const showFormModal = ref(false);
const editTarget = ref<LembagaAkreditasi | null>(null);
const form = useForm({ nama_lembaga: '', keterangan: '' });

const openCreate = () => { editTarget.value = null; form.reset(); form.clearErrors(); showFormModal.value = true; };
const openEdit = (item: LembagaAkreditasi) => {
    editTarget.value = item;
    form.nama_lembaga = item.nama_lembaga;
    form.keterangan = item.keterangan ?? '';
    form.clearErrors();
    showFormModal.value = true;
};
const closeForm = () => { showFormModal.value = false; editTarget.value = null; form.reset(); form.clearErrors(); };
const submitForm = () => {
    if (editTarget.value) {
        form.put(`/referensi/lembaga-akreditasi/${editTarget.value.id}`, { preserveScroll: true, onSuccess: () => closeForm() });
    } else {
        form.post('/referensi/lembaga-akreditasi', { preserveScroll: true, onSuccess: () => closeForm() });
    }
};

const showDeleteModal = ref(false);
const deleteTarget = ref<LembagaAkreditasi | null>(null);
const deleteProcessing = ref(false);
const openDelete = (item: LembagaAkreditasi) => { deleteTarget.value = item; showDeleteModal.value = true; };
const closeDelete = () => { showDeleteModal.value = false; deleteTarget.value = null; };
const confirmDelete = () => {
    if (!deleteTarget.value) return;
    deleteProcessing.value = true;
    router.delete(`/referensi/lembaga-akreditasi/${deleteTarget.value.id}`, {
        preserveScroll: true,
        onFinish: () => { deleteProcessing.value = false; closeDelete(); },
    });
};
</script>

<template>
    <Head title="Lembaga Akreditasi" />
    <div class="space-y-6 p-6">

        <PageHeader title="Lembaga Akreditasi" subtitle="Kelola data lembaga akreditasi">
            <template #actions>
                <BaseButton id="btn-tambah-lembaga" variant="primary" @click="openCreate">
                    Tambah
                </BaseButton>
            </template>
        </PageHeader>

        <div v-if="flash.success" class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            {{ flash.success }}
        </div>
        <div v-if="flash.error" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ flash.error }}
        </div>

        <SearchInput v-model="search" placeholder="Cari lembaga..." />

        <DataTable :is-empty="data.data.length === 0" empty-message="Tidak ada data." :col-span="4">
            <template #head>
                <th class="px-4 py-3 text-left font-medium text-gray-600">No</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Nama Lembaga</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Keterangan</th>
                <th class="px-4 py-3 text-center font-medium text-gray-600">Aksi</th>
            </template>

            <tr v-for="(item, i) in data.data" :key="item.id" class="bg-white hover:bg-gray-50 transition">
                <td class="px-4 py-3 text-gray-500">{{ (data.current_page - 1) * data.per_page + i + 1 }}</td>
                <td class="px-4 py-3 font-medium text-gray-900">{{ item.nama_lembaga }}</td>
                <td class="px-4 py-3 text-gray-500 text-xs max-w-xs whitespace-normal break-words">
                    {{ item.keterangan || '-' }}
                </td>
                <td class="px-4 py-3">
                    <div class="flex items-center justify-center gap-1">
                        <BaseButton :id="`btn-edit-lembaga-${item.id}`" variant="ghost" size="sm" icon-only title="Edit" @click="openEdit(item)">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </BaseButton>
                        <BaseButton :id="`btn-delete-lembaga-${item.id}`" variant="ghost" size="sm" icon-only title="Hapus" @click="openDelete(item)">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4 text-red-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                        </BaseButton>
                    </div>
                </td>
            </tr>
        </DataTable>

        <Pagination :links="data.links" :total="data.total" />
    </div>

    <FormModal :show="showFormModal" :title="editTarget ? 'Edit Lembaga Akreditasi' : 'Tambah Lembaga Akreditasi'" max-width="md" @close="closeForm">
        <form @submit.prevent="submitForm" class="space-y-4">
            <FormField label="Nama Lembaga" :required="true">
                <BaseInput v-model="form.nama_lembaga" type="text" placeholder="Contoh: BAN-PT S1" :error="form.errors.nama_lembaga" />
            </FormField>
            <FormField label="Keterangan">
                <BaseTextarea v-model="form.keterangan" placeholder="Opsional..." />
            </FormField>
            <FormActions :processing="form.processing" @cancel="closeForm" />
        </form>
    </FormModal>

    <ConfirmDeleteModal :show="showDeleteModal" :message="`Hapus lembaga '${deleteTarget?.nama_lembaga}'?`"
        :processing="deleteProcessing" @close="closeDelete" @confirm="confirmDelete" />
</template>
