<script setup lang="ts">
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import Pagination from '@/components/Pagination.vue';
import ConfirmDeleteModal from '@/components/ConfirmDeleteModal.vue';

// Atomic imports
import { PageHeader, DataTable, FormModal }    from '@/components/index';
import { BaseButton, BaseBadge }              from '@/components/index';
import { FormField, FormActions, SearchInput } from '@/components/index';
import { BaseInput, BaseSelect }              from '@/components/index';

interface TahunPeriode {
    id: number;
    tahun: number;
    status: 'Aktif' | 'Tidak Aktif';
    created_at: string;
}

interface PageProps {
    data: {
        data: TahunPeriode[];
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

// ── Search ─────────────────────────────────────────────────
const search = ref(props.filters.search ?? '');
let searchTimeout: ReturnType<typeof setTimeout>;
watch(search, (val) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get('/referensi/tahun-periode', { search: val }, { preserveState: true, replace: true });
    }, 400);
});

// ── Form Modal ─────────────────────────────────────────────
const showFormModal = ref(false);
const editTarget = ref<TahunPeriode | null>(null);

const form = useForm({
    tahun: '',
    status: 'Tidak Aktif' as 'Aktif' | 'Tidak Aktif',
});

const openCreate = () => {
    editTarget.value = null;
    form.reset();
    form.clearErrors();
    showFormModal.value = true;
};

const openEdit = (item: TahunPeriode) => {
    editTarget.value = item;
    form.tahun = String(item.tahun);
    form.status = item.status;
    form.clearErrors();
    showFormModal.value = true;
};

const closeForm = () => {
    showFormModal.value = false;
    editTarget.value = null;
    form.reset();
    form.clearErrors();
};

const submitForm = () => {
    if (editTarget.value) {
        form.put(`/referensi/tahun-periode/${editTarget.value.id}`, {
            preserveScroll: true,
            onSuccess: () => closeForm(),
        });
    } else {
        form.post('/referensi/tahun-periode', {
            preserveScroll: true,
            onSuccess: () => closeForm(),
        });
    }
};

// ── Delete Modal ───────────────────────────────────────────
const showDeleteModal = ref(false);
const deleteTarget = ref<TahunPeriode | null>(null);
const deleteProcessing = ref(false);

const openDelete = (item: TahunPeriode) => {
    deleteTarget.value = item;
    showDeleteModal.value = true;
};

const closeDelete = () => {
    showDeleteModal.value = false;
    deleteTarget.value = null;
};

const confirmDelete = () => {
    if (!deleteTarget.value) return;
    deleteProcessing.value = true;
    router.delete(`/referensi/tahun-periode/${deleteTarget.value.id}`, {
        preserveScroll: true,
        onFinish: () => {
            deleteProcessing.value = false;
            closeDelete();
        },
    });
};
</script>

<template>
    <Head title="Tahun Periode" />

    <div class="space-y-6 p-6">

        <!-- Page Header -->
        <PageHeader title="Tahun Periode" subtitle="Kelola data tahun periode akademik">
            <template #actions>
                <BaseButton id="btn-tambah-tahun-periode" variant="primary" @click="openCreate">
                    Tambah
                </BaseButton>
            </template>
        </PageHeader>

        <!-- Flash Messages -->
        <div v-if="flash.success" class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            {{ flash.success }}
        </div>
        <div v-if="flash.error" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ flash.error }}
        </div>

        <!-- Search -->
        <SearchInput v-model="search" placeholder="Cari tahun..." />

        <!-- Table -->
        <DataTable :is-empty="data.data.length === 0" empty-message="Tidak ada data." :col-span="4">
            <template #head>
                <th class="px-4 py-3 text-left font-medium text-gray-600">No</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Tahun</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Status</th>
                <th class="px-4 py-3 text-center font-medium text-gray-600">Aksi</th>
            </template>

            <tr
                v-for="(item, i) in data.data"
                :key="item.id"
                class="bg-white hover:bg-gray-50 transition"
            >
                <td class="px-4 py-3 text-gray-500">
                    {{ (data.current_page - 1) * data.per_page + i + 1 }}
                </td>
                <td class="px-4 py-3 font-medium text-gray-900">{{ item.tahun }}</td>
                <td class="px-4 py-3">
                    <BaseBadge :variant="item.status === 'Aktif' ? 'success' : 'default'" size="sm" rounded="full">
                        {{ item.status }}
                    </BaseBadge>
                </td>
                <td class="px-4 py-3">
                    <div class="flex items-center justify-center gap-2">
                        <BaseButton
                            :id="`btn-edit-tahun-${item.id}`"
                            variant="ghost"
                            size="sm"
                            icon-only
                            title="Edit"
                            @click="openEdit(item)"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </BaseButton>
                        <BaseButton
                            :id="`btn-delete-tahun-${item.id}`"
                            variant="ghost"
                            size="sm"
                            icon-only
                            title="Hapus"
                            @click="openDelete(item)"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4 text-red-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                        </BaseButton>
                    </div>
                </td>
            </tr>
        </DataTable>

        <!-- Pagination -->
        <Pagination :links="data.links" :total="data.total" />
    </div>

    <!-- Form Modal -->
    <FormModal
        :show="showFormModal"
        :title="editTarget ? 'Edit Tahun Periode' : 'Tambah Tahun Periode'"
        max-width="sm"
        @close="closeForm"
    >
        <form @submit.prevent="submitForm" class="space-y-4">
            <FormField label="Tahun" :required="true" :error="form.errors.tahun">
                <BaseInput
                    v-model="form.tahun"
                    type="number"
                    :min="2000"
                    :max="2100"
                    placeholder="Contoh: 2025"
                    :error="form.errors.tahun"
                />
            </FormField>

            <FormField label="Status" :required="true" :error="form.errors.status">
                <BaseSelect v-model="form.status" :error="form.errors.status">
                    <option value="Aktif">Aktif</option>
                    <option value="Tidak Aktif">Tidak Aktif</option>
                </BaseSelect>
            </FormField>

            <FormActions
                :processing="form.processing"
                @cancel="closeForm"
            />
        </form>
    </FormModal>

    <!-- Delete Confirm -->
    <ConfirmDeleteModal
        :show="showDeleteModal"
        :message="`Apakah Anda yakin ingin menghapus tahun ${deleteTarget?.tahun}?`"
        :processing="deleteProcessing"
        @close="closeDelete"
        @confirm="confirmDelete"
    />
</template>
