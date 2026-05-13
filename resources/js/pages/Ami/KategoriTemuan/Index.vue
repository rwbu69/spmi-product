<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Edit2, Plus, Trash2, Tag, Bookmark } from 'lucide-vue-next';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import ConfirmDeleteModal from '@/components/ConfirmDeleteModal.vue';
import Modal from '@/components/Modal.vue';

interface KategoriTemuan {
    id: number;
    nama_kategori: string;
    jenis_temuan_id: number;
    jenis_temuan: { id: number; nama: string; status: string };
}

interface PageProps {
    data: KategoriTemuan[];
    jenisList: { id: number; nama: string }[];
}

defineOptions({ layout: AppLayout });
defineProps<PageProps>();

// Form
const showForm = ref(false);
const editTarget = ref<KategoriTemuan | null>(null);
const form = useForm({
    nama_kategori: '',
    jenis_temuan_id: '',
});

const openCreate = () => {
    editTarget.value = null;
    form.reset();
    showForm.value = true;
};

const openEdit = (item: KategoriTemuan) => {
    editTarget.value = item;
    form.nama_kategori = item.nama_kategori;
    form.jenis_temuan_id = item.jenis_temuan_id.toString();
    showForm.value = true;
};

const submit = () => {
    const opts = { preserveScroll: true, onSuccess: () => { showForm.value = false; } };
    if (editTarget.value) {
        form.put(`/ami/kategori-temuan/${editTarget.value.id}`, opts);
    } else {
        form.post('/ami/kategori-temuan', opts);
    }
};

// Delete
const showDelete = ref(false);
const deleteTarget = ref<KategoriTemuan | null>(null);
const openDelete = (item: KategoriTemuan) => { deleteTarget.value = item; showDelete.value = true; };
const confirmDelete = () => {
    if (!deleteTarget.value) return;
    router.delete(`/ami/kategori-temuan/${deleteTarget.value.id}`, {
        preserveScroll: true,
        onSuccess: () => { showDelete.value = false; }
    });
};
</script>

<template>
    <Head title="Kategori Temuan" />
    <div class="space-y-6 p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-semibold text-gray-900 ">Kategori Temuan AMI</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Tingkatan temuan (Contoh: Mayor, Minor, Melampaui)</p>
            </div>
            <button type="button" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700" @click="openCreate">
                <Plus class="size-4" /> Tambah Kategori
            </button>
        </div>

        <div class="overflow-hidden rounded-xl border border-gray-200 shadow-sm dark:border-gray-700 bg-white dark:bg-gray-900">
            <table class="w-full text-sm text-left">
                <thead class="bg-blue-50 text-blue-700 border-b border-blue-100">
                    <tr>
                        <th class="px-4 py-3 font-medium text-gray-600 dark:text-gray-300">Nama Kategori</th>
                        <th class="px-4 py-3 font-medium text-gray-600 dark:text-gray-300">Jenis Induk</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600 dark:text-gray-300">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    <tr v-if="data.length === 0">
                        <td colspan="3" class="px-4 py-12 text-center text-gray-400 italic">Belum ada kategori temuan.</td>
                    </tr>
                    <tr v-for="item in data" :key="item.id" class="bg-white transition hover:bg-gray-50 dark:bg-gray-900 dark:hover:bg-gray-800/50">
                        <td class="px-4 py-3 font-medium text-gray-900 ">
                            <div class="flex items-center gap-2">
                                <Bookmark class="size-4 text-blue-500" />
                                {{ item.nama_kategori }}
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span :class="item.jenis_temuan.status === 'Positif' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'" class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase">
                                {{ item.jenis_temuan.nama }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <button type="button" class="p-1.5 text-gray-400 hover:text-blue-600" @click="openEdit(item)"><Edit2 class="size-4" /></button>
                                <button type="button" class="p-1.5 text-gray-400 hover:text-red-500" @click="openDelete(item)"><Trash2 class="size-4" /></button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Form -->
    <Modal :show="showForm" :title="editTarget ? 'Edit Kategori Temuan' : 'Tambah Kategori Temuan'" max-width="md" @close="showForm = false">
        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1">Nama Kategori <span class="text-red-500">*</span></label>
                <input v-model="form.nama_kategori" type="text" placeholder="Contoh: Mayor / Minor" class="w-full rounded-lg border px-3 py-2 text-sm  " />
                <p v-if="form.errors.nama_kategori" class="text-[11px] text-red-500 mt-1">{{ form.errors.nama_kategori }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Jenis Temuan Induk <span class="text-red-500">*</span></label>
                <select v-model="form.jenis_temuan_id" class="w-full rounded-lg border px-3 py-2 text-sm  ">
                    <option value="">Pilih Jenis</option>
                    <option v-for="j in jenisList" :key="j.id" :value="j.id">{{ j.nama }}</option>
                </select>
                <p v-if="form.errors.jenis_temuan_id" class="text-[11px] text-red-500 mt-1">{{ form.errors.jenis_temuan_id }}</p>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" class="px-4 py-2 text-sm font-medium border rounded-lg hover:bg-gray-50" @click="showForm = false">Batal</button>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700" :disabled="form.processing">Simpan Kategori</button>
            </div>
        </form>
    </Modal>

    <ConfirmDeleteModal :show="showDelete" message="Hapus kategori temuan ini?" @close="showDelete = false" @confirm="confirmDelete" />
</template>

