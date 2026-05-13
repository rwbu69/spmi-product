<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Edit2, Plus, Trash2, CheckCircle2, AlertCircle } from 'lucide-vue-next';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import ConfirmDeleteModal from '@/components/ConfirmDeleteModal.vue';
import Modal from '@/components/Modal.vue';

interface JenisTemuan {
    id: number;
    nama: string;
    status: 'Positif' | 'Negatif';
}

defineOptions({ layout: AppLayout });
defineProps<{ data: JenisTemuan[] }>();

// Form
const showForm = ref(false);
const editTarget = ref<JenisTemuan | null>(null);
const form = useForm({
    nama: '',
    status: 'Negatif' as 'Positif' | 'Negatif',
});

const openCreate = () => {
    editTarget.value = null;
    form.reset();
    showForm.value = true;
};

const openEdit = (item: JenisTemuan) => {
    editTarget.value = item;
    form.nama = item.nama;
    form.status = item.status;
    showForm.value = true;
};

const submit = () => {
    const opts = { preserveScroll: true, onSuccess: () => { showForm.value = false; } };
    if (editTarget.value) {
        form.put(`/ami/jenis-temuan/${editTarget.value.id}`, opts);
    } else {
        form.post('/ami/jenis-temuan', opts);
    }
};

// Delete
const showDelete = ref(false);
const deleteTarget = ref<JenisTemuan | null>(null);
const openDelete = (item: JenisTemuan) => { deleteTarget.value = item; showDelete.value = true; };
const confirmDelete = () => {
    if (!deleteTarget.value) return;
    router.delete(`/ami/jenis-temuan/${deleteTarget.value.id}`, {
        preserveScroll: true,
        onSuccess: () => { showDelete.value = false; }
    });
};
</script>

<template>
    <Head title="Jenis Temuan" />
    <div class="space-y-6 p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-semibold text-gray-900 ">Jenis Temuan AMI</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Kategorisasi temuan audit (Kesesuaian / KTS / Observasi)</p>
            </div>
            <button type="button" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700" @click="openCreate">
                <Plus class="size-4" /> Tambah Jenis
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="item in data" :key="item.id" class="p-6 rounded-2xl border bg-white dark:bg-gray-900 shadow-sm hover:shadow-md transition">
                <div class="flex items-start justify-between">
                    <div class="space-y-3">
                        <div :class="item.status === 'Positif' ? 'bg-green-50 text-green-600 dark:bg-green-900/20 dark:text-green-400' : 'bg-red-50 text-red-600 dark:bg-red-900/20 dark:text-red-400'" class="inline-flex items-center justify-center size-10 rounded-xl">
                            <CheckCircle2 v-if="item.status === 'Positif'" class="size-6" />
                            <AlertCircle v-else class="size-6" />
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 ">{{ item.nama }}</h3>
                            <span :class="item.status === 'Positif' ? 'text-green-600' : 'text-red-600'" class="text-[10px] font-bold uppercase tracking-widest">{{ item.status }}</span>
                        </div>
                    </div>
                    <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition">
                         <!-- Buttons always visible in this design for better accessibility -->
                    </div>
                </div>
                <div class="mt-6 pt-4 border-t dark:border-gray-800 flex justify-end gap-2">
                    <button type="button" class="p-2 rounded-lg text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800" @click="openEdit(item)">
                        <Edit2 class="size-4" />
                    </button>
                    <button type="button" class="p-2 rounded-lg text-gray-400 hover:bg-red-50 hover:text-red-500" @click="openDelete(item)">
                        <Trash2 class="size-4" />
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Form -->
    <Modal :show="showForm" :title="editTarget ? 'Edit Jenis Temuan' : 'Tambah Jenis Temuan'" max-width="md" @close="showForm = false">
        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1">Nama Jenis Temuan <span class="text-red-500">*</span></label>
                <input v-model="form.nama" type="text" placeholder="Contoh: Kesesuaian / Observasi" class="w-full rounded-lg border px-3 py-2 text-sm  " />
                <p v-if="form.errors.nama" class="text-[11px] text-red-500 mt-1">{{ form.errors.nama }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Status Findings <span class="text-red-500">*</span></label>
                <div class="flex gap-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input v-model="form.status" type="radio" value="Positif" class="text-green-600 focus:ring-green-500" />
                <p v-if="form.errors.status" class="text-[11px] text-red-500 mt-1">{{ form.errors.status }}</p>
                        <span class="text-sm font-medium text-green-700">Positif (Kesesuaian)</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input v-model="form.status" type="radio" value="Negatif" class="text-red-600 focus:ring-red-500" />
                <p v-if="form.errors.status" class="text-[11px] text-red-500 mt-1">{{ form.errors.status }}</p>
                        <span class="text-sm font-medium text-red-700">Negatif (KTS/Penyimpangan)</span>
                    </label>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" class="px-4 py-2 text-sm font-medium border rounded-lg hover:bg-gray-50" @click="showForm = false">Batal</button>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700" :disabled="form.processing">Simpan Jenis</button>
            </div>
        </form>
    </Modal>

    <ConfirmDeleteModal :show="showDelete" message="Hapus jenis temuan ini?" @close="showDelete = false" @confirm="confirmDelete" />
</template>

