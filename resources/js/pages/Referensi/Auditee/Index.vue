<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { FileText } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import Pagination from '@/components/Pagination.vue';
import ConfirmDeleteModal from '@/components/ConfirmDeleteModal.vue';
import { PageHeader, DataTable, FormModal } from '@/components/index';
import { BaseButton, BaseInput, BaseTextarea, BaseSelect } from '@/components/index';
import { FormField, FormActions, SearchInput } from '@/components/index';

interface Auditee {
    id: number;
    kode: string;
    nama_auditee: string;
    jenjang: string;
    akreditasi: string | null;
    alamat: string | null;
    sk_no: string | null;
    sk_tanggal: string | null;
    sk_file_path: string | null;
    auditee_pusat: { id: number; nama: string };
}

interface PageProps {
    data: {
        data: Auditee[];
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
    st = setTimeout(() => router.get('/referensi/auditee', { search: val }, { preserveState: true, replace: true }), 400);
});

const jenjangOptions = ['S1', 'S2', 'S3', 'D3', 'D4', 'Profesi', 'PRODI'];
const akreditasiOptions = ['A', 'B', 'C', 'Baik', 'Baik Sekali', 'Unggul'];

const showForm = ref(false);
const editTarget = ref<Auditee | null>(null);
const form = useForm({
    kode: '', nama_auditee: '', jenjang: '',
    auditee_pusat_id: '' as string | number,
    alamat: '', akreditasi: '', sk_no: '', sk_tanggal: '',
    sk_file: null as File | null,
});

const openCreate = () => { editTarget.value = null; form.reset(); form.clearErrors(); showForm.value = true; };
const openEdit = (item: Auditee) => {
    editTarget.value = item;
    form.kode = item.kode; form.nama_auditee = item.nama_auditee;
    form.jenjang = item.jenjang; form.auditee_pusat_id = item.auditee_pusat.id;
    form.alamat = item.alamat ?? ''; form.akreditasi = item.akreditasi ?? '';
    form.sk_no = item.sk_no ?? ''; form.sk_tanggal = item.sk_tanggal ?? '';
    form.sk_file = null; form.clearErrors(); showForm.value = true;
};
const closeForm = () => { showForm.value = false; editTarget.value = null; form.reset(); form.clearErrors(); };
const onFileChange = (e: Event) => { form.sk_file = (e.target as HTMLInputElement).files?.[0] ?? null; };
const submit = () => {
    const opts = { preserveScroll: true, onSuccess: () => closeForm(), forceFormData: true };
    editTarget.value
        ? form.post(`/referensi/auditee/${editTarget.value.id}?_method=PUT`, opts)
        : form.post('/referensi/auditee', opts);
};

const showDelete = ref(false);
const deleteTarget = ref<Auditee | null>(null);
const deleting = ref(false);
const openDelete = (item: Auditee) => { deleteTarget.value = item; showDelete.value = true; };
const closeDelete = () => { showDelete.value = false; deleteTarget.value = null; };
const confirmDelete = () => {
    if (!deleteTarget.value) return;
    deleting.value = true;
    router.delete(`/referensi/auditee/${deleteTarget.value.id}`, {
        preserveScroll: true,
        onFinish: () => { deleting.value = false; closeDelete(); },
    });
};

const akreditasiBadge = (ak: string | null) => {
    if (!ak) return 'bg-gray-100 text-gray-500';
    const map: Record<string, string> = {
        'Unggul': 'bg-blue-100 text-blue-700', 'Baik Sekali': 'bg-green-100 text-green-700',
        'Baik': 'bg-teal-100 text-teal-700', 'A': 'bg-purple-100 text-purple-700',
        'B': 'bg-indigo-100 text-indigo-700', 'C': 'bg-yellow-100 text-yellow-700',
    };
    return map[ak] ?? 'bg-gray-100 text-gray-500';
};
</script>

<template>
    <Head title="Auditee" />
    <div class="space-y-6 p-6">

        <PageHeader title="Auditee" subtitle="Kelola data program studi dan unit auditee">
            <template #actions>
                <BaseButton id="btn-tambah-auditee" variant="primary" @click="openCreate">
                    Tambah
                </BaseButton>
            </template>
        </PageHeader>

        <SearchInput v-model="search" placeholder="Cari kode / nama..." />

        <DataTable :is-empty="data.data.length === 0" empty-message="Tidak ada data." :col-span="8">
            <template #head>
                <th class="px-4 py-3 text-left font-medium text-gray-600">No</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Kode</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Nama Auditee</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Jenjang</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Auditee Pusat</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600">Akreditasi</th>
                <th class="px-4 py-3 text-left font-medium text-gray-600">SK</th>
                <th class="px-4 py-3 text-center font-medium text-gray-600">Aksi</th>
            </template>

            <tr v-for="(item, i) in data.data" :key="item.id" class="bg-white hover:bg-gray-50 transition">
                <td class="px-4 py-3 text-gray-500">{{ (data.current_page - 1) * data.per_page + i + 1 }}</td>
                <td class="px-4 py-3 font-mono text-xs font-medium text-gray-700">{{ item.kode }}</td>
                <td class="px-4 py-3 font-medium text-gray-900">{{ item.nama_auditee }}</td>
                <td class="px-4 py-3 text-gray-500">{{ item.jenjang }}</td>
                <td class="px-4 py-3 text-gray-500">{{ item.auditee_pusat?.nama ?? '-' }}</td>
                <td class="px-4 py-3">
                    <span v-if="item.akreditasi"
                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold"
                        :class="akreditasiBadge(item.akreditasi)">
                        {{ item.akreditasi }}
                    </span>
                    <span v-else class="text-gray-400">-</span>
                </td>
                <td class="px-4 py-3">
                    <a v-if="item.sk_file_path" :href="`/storage/${item.sk_file_path}`" target="_blank"
                        class="inline-flex items-center gap-1 text-xs text-blue-600 hover:underline">
                        <FileText class="size-3.5" /> Lihat SK
                    </a>
                    <span v-else class="text-gray-400">-</span>
                </td>
                <td class="px-4 py-3">
                    <div class="flex items-center justify-center gap-1">
                        <BaseButton :id="`btn-edit-auditee-${item.id}`" variant="ghost" size="sm" icon-only title="Edit" @click="openEdit(item)">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </BaseButton>
                        <BaseButton :id="`btn-delete-auditee-${item.id}`" variant="ghost" size="sm" icon-only title="Hapus" @click="openDelete(item)">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4 text-red-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                        </BaseButton>
                    </div>
                </td>
            </tr>
        </DataTable>

        <Pagination :links="data.links" :total="data.total" />
    </div>

    <FormModal :show="showForm" :title="editTarget ? 'Edit Auditee' : 'Tambah Auditee'" max-width="2xl" @close="closeForm">
        <form @submit.prevent="submit" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <FormField label="Kode" :required="true" :error="form.errors.kode">
                    <BaseInput v-model="form.kode" type="text" placeholder="Contoh: S1-TEO" :error="form.errors.kode" />
                </FormField>
            </div>
            <div>
                <FormField label="Jenjang" :required="true" :error="form.errors.jenjang">
                    <BaseSelect v-model="form.jenjang" :error="form.errors.jenjang">
                        <option value="">-- Pilih Jenjang --</option>
                        <option v-for="j in jenjangOptions" :key="j" :value="j">{{ j }}</option>
                    </BaseSelect>
                </FormField>
            </div>
            <div class="sm:col-span-2">
                <FormField label="Nama Auditee" :required="true" :error="form.errors.nama_auditee">
                    <BaseInput v-model="form.nama_auditee" type="text" placeholder="Contoh: Sarjana Teologi" :error="form.errors.nama_auditee" />
                </FormField>
            </div>
            <div>
                <FormField label="Auditee Pusat" :required="true" :error="form.errors.auditee_pusat_id">
                    <BaseSelect v-model="form.auditee_pusat_id" :error="form.errors.auditee_pusat_id">
                        <option value="">-- Pilih Auditee Pusat --</option>
                        <option v-for="ap in auditeePusat" :key="ap.id" :value="ap.id">{{ ap.nama }}</option>
                    </BaseSelect>
                </FormField>
            </div>
            <div>
                <FormField label="Akreditasi" :error="form.errors.akreditasi">
                    <BaseSelect v-model="form.akreditasi">
                        <option value="">-- Pilih Akreditasi --</option>
                        <option v-for="ak in akreditasiOptions" :key="ak" :value="ak">{{ ak }}</option>
                    </BaseSelect>
                </FormField>
            </div>
            <div class="sm:col-span-2">
                <FormField label="Alamat">
                    <BaseTextarea v-model="form.alamat" :rows="2" placeholder="Alamat lengkap..." />
                </FormField>
            </div>
            <div>
                <FormField label="Nomor SK" :error="form.errors.sk_no">
                    <BaseInput v-model="form.sk_no" type="text" placeholder="No. SK..." :error="form.errors.sk_no" />
                </FormField>
            </div>
            <div>
                <FormField label="Tanggal SK" :error="form.errors.sk_tanggal">
                    <BaseInput v-model="form.sk_tanggal" type="date" :error="form.errors.sk_tanggal" />
                </FormField>
            </div>
            <div class="sm:col-span-2">
                <FormField label="File SK (PDF, maks 2MB)"
                    :hint="editTarget?.sk_file_path ? 'Kosongkan jika tidak ingin mengganti file.' : undefined">
                    <input type="file" accept="application/pdf" @change="onFileChange"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm file:mr-3 file:rounded file:border-0 file:bg-blue-50 file:px-3 file:py-1 file:text-xs file:font-medium file:text-blue-700" />
                </FormField>
            </div>
            <div class="sm:col-span-2">
                <FormActions :processing="form.processing" submit-label="Simpan" @cancel="closeForm" />
            </div>
        </form>
    </FormModal>

    <ConfirmDeleteModal :show="showDelete" :message="`Hapus auditee '${deleteTarget?.nama_auditee}'?`"
        :processing="deleting" @close="closeDelete" @confirm="confirmDelete" />
</template>
