<script setup lang="ts">
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { Edit2, Plus, Search, Trash2 } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import Pagination from '@/components/Pagination.vue';
import ConfirmDeleteModal from '@/components/ConfirmDeleteModal.vue';
import Modal from '@/components/Modal.vue';

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

// ── Flash messages ─────────────────────────────────────────
const flash = computed(() => page.props.flash ?? {});

// ── Search ─────────────────────────────────────────────────
const search = ref(props.filters.search ?? '');
let searchTimeout: ReturnType<typeof setTimeout>;
watch(search, (val) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get('/referensi/tahun-periode', { search: val }, {
            preserveState: true, replace: true,
        });
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
        <!-- Header -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-semibold text-gray-900 ">Tahun Periode</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Kelola data tahun periode akademik
                </p>
            </div>
            <button
                id="btn-tambah-tahun-periode"
                type="button"
                class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                @click="openCreate"
            >
                <Plus class="size-4" />
                Tambah
            </button>
        </div>

        <!-- Flash messages -->
        <div v-if="flash.success" class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-800 dark:bg-green-900/20 dark:text-green-400">
            {{ flash.success }}
        </div>
        <div v-if="flash.error" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400">
            {{ flash.error }}
        </div>

        <!-- Search -->
        <div class="relative w-full max-w-xs">
            <Search class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-gray-400" />
            <input
                v-model="search"
                type="text"
                placeholder="Cari tahun..."
                class="w-full rounded-lg border border-gray-300 py-2 pr-4 pl-9 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200   "
            />
        </div>

        <!-- Table -->
        <div class="overflow-hidden rounded-xl border border-gray-200 shadow-sm dark:border-gray-700">
            <table class="w-full text-sm">
                <thead class="bg-blue-50 text-blue-700">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">No</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Tahun</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Status</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600 dark:text-gray-300">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    <tr v-if="data.data.length === 0">
                        <td colspan="4" class="px-4 py-10 text-center text-gray-400">Tidak ada data.</td>
                    </tr>
                    <tr
                        v-for="(item, i) in data.data"
                        :key="item.id"
                        class="bg-white transition hover:bg-gray-50 dark:bg-gray-900 dark:hover:bg-gray-800/50"
                    >
                        <td class="px-4 py-3 text-gray-500">
                            {{ (data.current_page - 1) * data.per_page + i + 1 }}
                        </td>
                        <td class="px-4 py-3 font-medium text-gray-900 ">
                            {{ item.tahun }}
                        </td>
                        <td class="px-4 py-3">
                            <span
                                class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                :class="item.status === 'Aktif'
                                    ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                                    : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400'"
                            >
                                {{ item.status }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <button
                                    :id="`btn-edit-tahun-${item.id}`"
                                    type="button"
                                    class="rounded-md p-1.5 text-blue-600 transition hover:bg-blue-50 dark:hover:bg-blue-900/20"
                                    title="Edit"
                                    @click="openEdit(item)"
                                >
                                    <Edit2 class="size-4" />
                                </button>
                                <button
                                    :id="`btn-delete-tahun-${item.id}`"
                                    type="button"
                                    class="rounded-md p-1.5 text-red-500 transition hover:bg-red-50 dark:hover:bg-red-900/20"
                                    title="Hapus"
                                    @click="openDelete(item)"
                                >
                                    <Trash2 class="size-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <Pagination :links="data.links" :total="data.total" />
    </div>

    <!-- Form Modal -->
    <Modal
        :show="showFormModal"
        :title="editTarget ? 'Edit Tahun Periode' : 'Tambah Tahun Periode'"
        max-width="sm"
        @close="closeForm"
    >
        <form @submit.prevent="submitForm" class="space-y-4">
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Tahun <span class="text-red-500">*</span>
                </label>
                <input
                    v-model="form.tahun"
                    type="number"
                    min="2000"
                    max="2100"
                    placeholder="Contoh: 2025"
                    class="w-full rounded-lg border px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200   "
                    :class="form.errors.tahun ? 'border-red-400' : 'border-gray-300'"
                />
                <p v-if="form.errors.tahun" class="text-[11px] text-red-500 mt-1">{{ form.errors.tahun }}</p>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Status <span class="text-red-500">*</span>
                </label>
                <select
                    v-model="form.status"
                    class="w-full rounded-lg border px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200   "
                    :class="form.errors.status ? 'border-red-400' : 'border-gray-300'"
                >
                    <option value="Aktif">Aktif</option>
                    <option value="Tidak Aktif">Tidak Aktif</option>
                </select>
                <p v-if="form.errors.status" class="text-[11px] text-red-500 mt-1">{{ form.errors.status }}</p>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button
                    type="button"
                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50  dark:text-gray-300 dark:hover:bg-gray-800"
                    @click="closeForm"
                >
                    Batal
                </button>
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700 disabled:opacity-60"
                >
                    {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                </button>
            </div>
        </form>
    </Modal>

    <!-- Delete Confirm Modal -->
    <ConfirmDeleteModal
        :show="showDeleteModal"
        :message="`Apakah Anda yakin ingin menghapus tahun ${deleteTarget?.tahun}?`"
        :processing="deleteProcessing"
        @close="closeDelete"
        @confirm="confirmDelete"
    />
</template>

