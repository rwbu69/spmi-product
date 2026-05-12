<script setup lang="ts">
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { Edit2, Plus, Search, Trash2 } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import ConfirmDeleteModal from '@/components/ConfirmDeleteModal.vue';
import Modal from '@/components/Modal.vue';

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
        router.get('/referensi/lembaga-akreditasi', { search: val }, {
            preserveState: true, replace: true,
        });
    }, 400);
});

// Form Modal
const showFormModal = ref(false);
const editTarget = ref<LembagaAkreditasi | null>(null);
const form = useForm({ nama_lembaga: '', keterangan: '' });

const openCreate = () => {
    editTarget.value = null;
    form.reset();
    form.clearErrors();
    showFormModal.value = true;
};

const openEdit = (item: LembagaAkreditasi) => {
    editTarget.value = item;
    form.nama_lembaga = item.nama_lembaga;
    form.keterangan = item.keterangan ?? '';
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
        form.put(`/referensi/lembaga-akreditasi/${editTarget.value.id}`, {
            preserveScroll: true,
            onSuccess: () => closeForm(),
        });
    } else {
        form.post('/referensi/lembaga-akreditasi', {
            preserveScroll: true,
            onSuccess: () => closeForm(),
        });
    }
};

// Delete Modal
const showDeleteModal = ref(false);
const deleteTarget = ref<LembagaAkreditasi | null>(null);
const deleteProcessing = ref(false);

const openDelete = (item: LembagaAkreditasi) => {
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
    router.delete(`/referensi/lembaga-akreditasi/${deleteTarget.value.id}`, {
        preserveScroll: true,
        onFinish: () => {
            deleteProcessing.value = false;
            closeDelete();
        },
    });
};
</script>

<template>
    <Head title="Lembaga Akreditasi" />

    <div class="space-y-6 p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-semibold text-gray-900 ">Lembaga Akreditasi</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Kelola data lembaga akreditasi</p>
            </div>
            <button
                id="btn-tambah-lembaga"
                type="button"
                class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700"
                @click="openCreate"
            >
                <Plus class="size-4" /> Tambah
            </button>
        </div>

        <div v-if="flash.success" class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-800 dark:bg-green-900/20 dark:text-green-400">
            {{ flash.success }}
        </div>
        <div v-if="flash.error" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ flash.error }}
        </div>

        <div class="relative w-full max-w-xs">
            <Search class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-gray-400" />
            <input v-model="search" type="text" placeholder="Cari lembaga..."
                class="w-full rounded-lg border border-gray-300 py-2 pr-4 pl-9 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200   " />
        </div>

        <div class="overflow-hidden rounded-xl border border-gray-200 shadow-sm dark:border-gray-700">
            <table class="w-full text-sm">
                <thead class="bg-blue-50 text-blue-700">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">No</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Nama Lembaga</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Keterangan</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600 dark:text-gray-300">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    <tr v-if="data.data.length === 0">
                        <td colspan="4" class="px-4 py-10 text-center text-gray-400">Tidak ada data.</td>
                    </tr>
                    <tr v-for="(item, i) in data.data" :key="item.id"
                        class="bg-white transition hover:bg-gray-50 dark:bg-gray-900 dark:hover:bg-gray-800/50">
                        <td class="px-4 py-3 text-gray-500">{{ (data.current_page - 1) * data.per_page + i + 1 }}</td>
                        <td class="px-4 py-3 font-medium text-gray-900 ">{{ item.nama_lembaga }}</td>
                        <td class="px-4 py-3 text-gray-500">
                            <div class="max-w-[300px] whitespace-normal break-words text-xs">
                                {{ item.keterangan || '-' }}
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <button :id="`btn-edit-lembaga-${item.id}`" type="button"
                                    class="rounded-md p-1.5 text-blue-600 transition hover:bg-blue-50 dark:hover:bg-blue-900/20"
                                    @click="openEdit(item)"><Edit2 class="size-4" /></button>
                                <button :id="`btn-delete-lembaga-${item.id}`" type="button"
                                    class="rounded-md p-1.5 text-red-500 transition hover:bg-red-50 dark:hover:bg-red-900/20"
                                    @click="openDelete(item)"><Trash2 class="size-4" /></button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="flex items-center justify-between text-sm text-gray-500">
            <span>Total: {{ data.total }} data</span>
            <div class="flex gap-1">
                <template v-for="link in data.links" :key="link.label">
                    <button v-if="link.url" type="button"
                        class="rounded px-3 py-1 transition"
                        :class="link.active ? 'bg-blue-600 text-white' : 'hover:bg-gray-100 dark:hover:bg-gray-700'"
                        @click="router.get(link.url, {}, { preserveState: true })" v-html="link.label" />
                    <span v-else class="cursor-default rounded px-3 py-1 text-gray-300" v-html="link.label" />
                </template>
            </div>
        </div>
    </div>

    <!-- Form Modal -->
    <Modal :show="showFormModal" :title="editTarget ? 'Edit Lembaga Akreditasi' : 'Tambah Lembaga Akreditasi'" max-width="md" @close="closeForm">
        <form @submit.prevent="submitForm" class="space-y-4">
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Nama Lembaga <span class="text-red-500">*</span>
                </label>
                <input v-model="form.nama_lembaga" type="text" placeholder="Contoh: BAN-PT S1"
                    class="w-full rounded-lg border px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200   "
                    :class="form.errors.nama_lembaga ? 'border-red-400' : 'border-gray-300'" />
                <p v-if="form.errors.nama_lembaga" class="mt-1 text-xs text-red-500">{{ form.errors.nama_lembaga }}</p>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Keterangan</label>
                <textarea v-model="form.keterangan" rows="3" placeholder="Opsional..."
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200   " />
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50" @click="closeForm">Batal</button>
                <button type="submit" :disabled="form.processing" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700 disabled:opacity-60">
                    {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                </button>
            </div>
        </form>
    </Modal>

    <!-- Delete Modal -->
    <ConfirmDeleteModal :show="showDeleteModal" :message="`Hapus lembaga '${deleteTarget?.nama_lembaga}'?`"
        :processing="deleteProcessing" @close="closeDelete" @confirm="confirmDelete" />
</template>
