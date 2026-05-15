<script setup lang="ts">
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { Download, Calendar } from 'lucide-vue-next';
import { ref, watch, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import ConfirmDeleteModal from '@/components/ConfirmDeleteModal.vue';
import Pagination from '@/components/Pagination.vue';
import { PageHeader, DataTable, FormModal } from '@/components/index';
import { BaseButton, BaseInput, BaseSelect, BaseBadge } from '@/components/index';
import { FormField, FormActions, SearchInput } from '@/components/index';

interface LaporanAmi {
    id: number;
    file_laporan: string;
    tanggal_laporan: string;
    status: 'Draft' | 'Final';
    auditee: { id: number; nama_auditee: string };
    pengaturan_periode: {
        id: number;
        tahun_periode: { tahun: number };
        lembaga_akreditasi: { nama_lembaga: string };
    };
}

const props = defineProps<{
    data: { data: LaporanAmi[]; total: number; links: any[]; };
    filters: { search?: string; periode_id?: string };
    periodeList: { id: number; label: string }[];
    auditeeList: { id: number; nama_auditee: string }[];
    currentAuditee?: { nama_auditee: string } | null;
}>();

defineOptions({ layout: AppLayout });
const page = usePage();
const isAdmin = computed(() => (page.props.auth as any)?.roles?.includes('Admin'));
const isAuditee = computed(() => {
    const roles = (page.props.auth as any)?.roles ?? [];
    return roles.includes('Auditee') || roles.includes('Unit Penunjang');
});

const search    = ref(props.filters.search ?? '');
const periode_id = ref(props.filters.periode_id ?? '');

let t: any;
watch([search, periode_id], () => {
    clearTimeout(t);
    t = setTimeout(() => router.get('/ami/laporan-ami', { search: search.value, periode_id: periode_id.value }, { preserveState: true, replace: true }), 400);
});

const showForm = ref(false);
const editTarget = ref<LaporanAmi | null>(null);
const form = useForm({
    pengaturan_periode_id: '',
    auditee_id: '',
    file_laporan: null as File | null,
    tanggal_laporan: new Date().toISOString().split('T')[0],
    status: 'Draft',
});

const openCreate = () => { editTarget.value = null; form.reset(); showForm.value = true; };
const openEdit = (item: LaporanAmi) => {
    editTarget.value = item;
    form.pengaturan_periode_id = item.pengaturan_periode.id.toString();
    form.auditee_id = item.auditee.id.toString();
    form.tanggal_laporan = item.tanggal_laporan ? item.tanggal_laporan.substring(0, 10) : '';
    form.status = item.status;
    form.file_laporan = null;
    showForm.value = true;
};

const submit = () => {
    const opts = { preserveScroll: true, onSuccess: () => { showForm.value = false; } };
    if (editTarget.value) {
        form.post(`/ami/laporan-ami/${editTarget.value.id}`, { ...opts, forceFormData: true, onBefore: (r: any) => { r.data._method = 'PUT'; } });
    } else {
        form.post('/ami/laporan-ami', opts);
    }
};

const showDelete = ref(false);
const deleteTarget = ref<LaporanAmi | null>(null);
const confirmDelete = () => {
    if (!deleteTarget.value) return;
    router.delete(`/ami/laporan-ami/${deleteTarget.value.id}`, { preserveScroll: true, onSuccess: () => { showDelete.value = false; } });
};

const getDownloadUrl = (item: LaporanAmi) => `/ami/laporan-ami/${item.id}/download`;
const formatDate = (d: string) => new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
</script>

<template>
    <Head title="Laporan AMI" />
    <div class="space-y-6 p-6">

        <PageHeader
            :title="isAuditee ? 'Dokumen Laporan AMI' : 'Laporan Hasil AMI'"
            subtitle="Arsip laporan audit final per unit kerja"
        >
            <template v-if="isAdmin" #actions>
                <BaseButton variant="primary" @click="openCreate">Unggah Laporan</BaseButton>
            </template>
        </PageHeader>

        <!-- Auditee view: current auditee header -->
        <div v-if="isAuditee && currentAuditee" class="rounded-xl border bg-white shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b bg-gray-50/40">
                <div class="flex items-center gap-4 text-sm">
                    <span class="font-semibold text-gray-600 w-24">Auditee</span>
                    <span class="text-gray-900 font-medium">{{ currentAuditee.nama_auditee }}</span>
                </div>
            </div>
        </div>

        <!-- Filters (Admin/Auditor only) -->
        <div v-if="!isAuditee" class="flex flex-wrap items-center gap-3">
            <SearchInput v-model="search" placeholder="Cari auditee..." />
            <BaseSelect v-model="periode_id">
                <option value="">Semua Periode</option>
                <option v-for="p in periodeList" :key="p.id" :value="p.id">{{ p.label }}</option>
            </BaseSelect>
        </div>

        <!-- Table -->
        <DataTable :is-empty="data.data.length === 0" empty-message="Belum ada laporan AMI."
            :col-span="isAdmin ? (isAuditee ? 6 : 7) : (isAuditee ? 5 : 6)">
            <template #head>
                <th class="px-4 py-3 font-medium text-gray-600">No</th>
                <th v-if="!isAuditee" class="px-4 py-3 font-medium text-gray-600">Auditee</th>
                <th class="px-4 py-3 font-medium text-gray-600">Jenis Laporan AMI</th>
                <th class="px-4 py-3 font-medium text-gray-600">Tahun Laporan AMI</th>
                <th class="px-4 py-3 font-medium text-gray-600">Status</th>
                <th class="px-4 py-3 font-medium text-gray-600">Download</th>
                <th v-if="isAdmin" class="px-4 py-3 text-center font-medium text-gray-600">Aksi</th>
            </template>

            <tr v-for="(item, idx) in data.data" :key="item.id" class="hover:bg-gray-50 transition">
                <td class="px-4 py-3 text-gray-500">{{ idx + 1 }}</td>
                <td v-if="!isAuditee" class="px-4 py-3 font-medium text-gray-900 text-xs">{{ item.auditee.nama_auditee }}</td>
                <td class="px-4 py-3 text-xs text-gray-700">
                    {{ item.pengaturan_periode.lembaga_akreditasi.nama_lembaga }}
                    <div class="flex items-center gap-1 text-[10px] text-gray-400 mt-0.5">
                        <Calendar class="size-3" /> {{ formatDate(item.tanggal_laporan) }}
                    </div>
                </td>
                <td class="px-4 py-3 font-mono text-blue-600 font-bold">{{ item.pengaturan_periode.tahun_periode.tahun }}</td>
                <td class="px-4 py-3">
                    <BaseBadge :variant="item.status === 'Final' ? 'success' : 'warning'" rounded="sm">
                        {{ item.status }}
                    </BaseBadge>
                </td>
                <td class="px-4 py-3">
                    <a :href="getDownloadUrl(item)" target="_blank"
                        class="inline-flex items-center gap-1.5 px-2 py-1 rounded-md bg-gray-50 text-gray-600 text-xs font-medium hover:bg-gray-100 transition">
                        <Download class="size-3.5" /> Unduh PDF
                    </a>
                </td>
                <td v-if="isAdmin" class="px-4 py-3">
                    <div class="flex items-center justify-center gap-1">
                        <BaseButton variant="ghost" size="sm" icon-only title="Edit" @click="openEdit(item)">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </BaseButton>
                        <BaseButton variant="ghost" size="sm" icon-only title="Hapus" @click="() => { deleteTarget = item; showDelete = true; }">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4 text-red-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                        </BaseButton>
                    </div>
                </td>
            </tr>
        </DataTable>

        <Pagination :links="data.links" :total="data.total" />
    </div>

    <FormModal :show="showForm" :title="editTarget ? 'Edit Laporan AMI' : 'Unggah Laporan AMI'" max-width="md" @close="showForm = false">
        <form @submit.prevent="submit" class="space-y-4">
            <FormField label="Periode AMI" :required="true">
                <BaseSelect v-model="form.pengaturan_periode_id" :disabled="!!editTarget">
                    <option value="">Pilih Periode</option>
                    <option v-for="p in periodeList" :key="p.id" :value="p.id">{{ p.label }}</option>
                </BaseSelect>
            </FormField>
            <FormField label="Auditee" :required="true">
                <BaseSelect v-model="form.auditee_id" :disabled="!!editTarget">
                    <option value="">Pilih Auditee</option>
                    <option v-for="a in auditeeList" :key="a.id" :value="a.id">{{ a.nama_auditee }}</option>
                </BaseSelect>
            </FormField>
            <div class="grid grid-cols-2 gap-4">
                <FormField label="Tanggal Laporan" :required="true">
                    <BaseInput v-model="form.tanggal_laporan" type="date" />
                </FormField>
                <FormField label="Status" :required="true">
                    <BaseSelect v-model="form.status">
                        <option value="Draft">Draft</option>
                        <option value="Final">Final</option>
                    </BaseSelect>
                </FormField>
            </div>
            <FormField label="File Laporan (PDF)"
                :hint="editTarget ? 'Kosongkan jika tidak ingin mengganti file.' : undefined">
                <input type="file" accept="application/pdf"
                    class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                    @input="form.file_laporan = ($event.target as HTMLInputElement).files?.[0] || null" />
            </FormField>
            <FormActions :processing="form.processing" submit-label="Simpan Laporan" @cancel="showForm = false" />
        </form>
    </FormModal>

    <ConfirmDeleteModal :show="showDelete" message="Hapus laporan AMI ini?" @close="showDelete = false" @confirm="confirmDelete" />
</template>
