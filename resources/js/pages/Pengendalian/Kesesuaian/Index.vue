<script setup lang="ts">
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { Edit2, Plus, Search, Trash2, Filter, FileSpreadsheet } from 'lucide-vue-next';
import { ref, watch, computed } from 'vue';
import ConfirmDeleteModal from '@/components/ConfirmDeleteModal.vue';
import Modal from '@/components/Modal.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import Pagination from '@/components/Pagination.vue';

interface Kesesuaian {
    id: number;
    deskripsi: string | null;
    peningkatan: string | null;
    nilai_mutu: number | null;
    temuan_positif: string | null;
    auditee: { id: number; nama_auditee: string };
    standar_mutu: { id: number; kode: string; nama_standar: string };
    pengaturan_periode: { id: number; tahun_periode: { tahun: number }; lembaga_akreditasi: { nama_lembaga: string }; };
}

const props = defineProps<{
    data: { data: Kesesuaian[]; total: number; links: any[]; };
    filters: { search?: string; tahun_id?: string; lembaga_id?: string };
    periodeList: { id: number; label: string }[];
    tahunList: { id: number; tahun: number }[];
    lembagaList: { id: number; nama_lembaga: string }[];
    auditeeList: { id: number; nama_auditee: string }[];
    standarList: { id: number; kode: string; nama_standar: string }[];
}>();

defineOptions({ layout: AppLayout });
const page = usePage();
const isAdmin = computed(() => (page.props.auth as any)?.roles?.includes('Admin'));

const search    = ref(props.filters.search ?? '');
const tahun_id  = ref(props.filters.tahun_id ?? '');
const lembaga_id = ref(props.filters.lembaga_id ?? '');

let t: any;
watch([search, tahun_id, lembaga_id], () => {
    clearTimeout(t);
    t = setTimeout(() => router.get('/pengendalian/kesesuaian', {
        search: search.value || undefined,
        tahun_id: tahun_id.value || undefined,
        lembaga_id: lembaga_id.value || undefined,
    }, { preserveState: true, replace: true }), 400);
});

const exportUrl = computed(() => {
    const p = new URLSearchParams();
    if (tahun_id.value) p.set('tahun_id', tahun_id.value);
    if (lembaga_id.value) p.set('lembaga_id', lembaga_id.value);
    if (search.value) p.set('search', search.value);
    return '/pengendalian/kesesuaian/export' + (p.toString() ? '?' + p.toString() : '');
});

const showForm = ref(false);
const editTarget = ref<Kesesuaian | null>(null);
const form = useForm({ auditee_id: '', standar_mutu_id: '', pengaturan_periode_id: '', deskripsi: '', peningkatan: '', nilai_mutu: 0, temuan_positif: '' });

const openCreate = () => { editTarget.value = null; form.reset(); showForm.value = true; };
const openEdit = (item: Kesesuaian) => {
    editTarget.value = item;
    form.auditee_id = item.auditee.id.toString();
    form.standar_mutu_id = item.standar_mutu.id.toString();
    form.pengaturan_periode_id = item.pengaturan_periode.id.toString();
    form.deskripsi = item.deskripsi ?? '';
    form.peningkatan = item.peningkatan ?? '';
    form.nilai_mutu = item.nilai_mutu ?? 0;
    form.temuan_positif = item.temuan_positif ?? '';
    showForm.value = true;
};
const submit = () => {
    const opts = { preserveScroll: true, onSuccess: () => { showForm.value = false; } };
    editTarget.value ? form.put(`/pengendalian/kesesuaian/${editTarget.value.id}`, opts) : form.post('/pengendalian/kesesuaian', opts);
};

const showDelete = ref(false);
const deleteTarget = ref<Kesesuaian | null>(null);
const confirmDelete = () => {
    if (!deleteTarget.value) return;
    router.delete(`/pengendalian/kesesuaian/${deleteTarget.value.id}`, { preserveScroll: true, onSuccess: () => { showDelete.value = false; } });
};
</script>

<template>
    <Head title="Daftar Kesesuaian" />
    <div class="space-y-6 p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-semibold text-gray-900">Daftar Kesesuaian &amp; Peningkatan</h1>
                <p class="mt-1 text-sm text-gray-500">Arsip praktik baik dan rencana peningkatan mutu</p>
            </div>
            <button v-if="isAdmin" type="button" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700" @click="openCreate">
                <Plus class="size-4" /> Tambah Data
            </button>
        </div>

        <!-- Filter Panel -->
        <div class="rounded-xl border bg-white p-4 shadow-sm space-y-3">
            <p class="flex items-center gap-1.5 text-sm font-semibold text-gray-700"><Filter class="size-4 text-gray-400" /> Filter Data</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500 uppercase">Tahun</label>
                    <select v-model="tahun_id" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                        <option value="">Semua Tahun</option>
                        <option v-for="t in tahunList" :key="t.id" :value="t.id">{{ t.tahun }}</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500 uppercase">Lembaga Akreditasi</label>
                    <select v-model="lembaga_id" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                        <option value="">Semua Lembaga</option>
                        <option v-for="l in lembagaList" :key="l.id" :value="l.id">{{ l.nama_lembaga }}</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500 uppercase">Cari</label>
                    <div class="relative">
                        <Search class="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-gray-400" />
                        <input v-model="search" type="text" placeholder="Cari deskripsi..." class="w-full rounded-md border border-gray-300 pl-9 pr-3 py-2 text-sm focus:border-blue-500 focus:outline-none" />
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <span class="text-sm text-gray-500">Total: <strong>{{ data.total }}</strong> data</span>
            <a :href="exportUrl" class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-3 py-2 text-sm font-medium text-white hover:bg-green-700">
                <FileSpreadsheet class="size-4" /> Export Excel
            </a>
        </div>

        <div class="overflow-hidden rounded-xl border bg-white shadow-sm">
            <table class="w-full text-sm text-left">
                <thead class="bg-blue-50 border-b border-blue-100">
                    <tr>
                        <th class="px-4 py-3 font-medium text-gray-600">No</th>
                        <th class="px-4 py-3 font-medium text-gray-600">Standar Mutu</th>
                        <th class="px-4 py-3 font-medium text-gray-600">Deskripsi</th>
                        <th class="px-4 py-3 font-medium text-gray-600">Nilai Mutu</th>
                        <th class="px-4 py-3 font-medium text-gray-600">Daftar Temuan</th>
                        <th class="px-4 py-3 font-medium text-gray-600">Peningkatan</th>
                        <th v-if="isAdmin" class="px-4 py-3 text-center font-medium text-gray-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-if="data.data.length === 0">
                        <td :colspan="isAdmin ? 7 : 6" class="px-4 py-12 text-center text-gray-400 italic">Belum ada data kesesuaian.</td>
                    </tr>
                    <tr v-for="(item, idx) in data.data" :key="item.id" class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 text-gray-500">{{ idx + 1 }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-block rounded bg-green-50 px-1.5 py-0.5 text-[10px] font-bold uppercase text-green-700">{{ item.standar_mutu.kode }}</span>
                            <div class="text-xs font-medium text-gray-800 mt-0.5">{{ item.standar_mutu.nama_standar }}</div>
                            <div class="text-[10px] text-gray-400 mt-0.5">{{ item.auditee.nama_auditee }} · {{ item.pengaturan_periode.lembaga_akreditasi.nama_lembaga }} ({{ item.pengaturan_periode.tahun_periode.tahun }})</div>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-700 max-w-[180px]">{{ item.deskripsi || '-' }}</td>
                        <td class="px-4 py-3 font-bold text-blue-600">{{ item.nilai_mutu?.toFixed(1) ?? '0.0' }}</td>
                        <td class="px-4 py-3 text-xs text-gray-700 max-w-[150px]">{{ item.temuan_positif || '-' }}</td>
                        <td class="px-4 py-3 text-xs text-gray-700 max-w-[150px]">{{ item.peningkatan || '-' }}</td>
                        <td v-if="isAdmin" class="px-4 py-3">
                            <div class="flex items-center justify-center gap-1">
                                <button type="button" class="p-1.5 rounded-lg text-gray-400 hover:bg-blue-50 hover:text-blue-600" @click="openEdit(item)"><Edit2 class="size-4" /></button>
                                <button type="button" class="p-1.5 rounded-lg text-gray-400 hover:bg-red-50 hover:text-red-500" @click="() => { deleteTarget = item; showDelete = true; }"><Trash2 class="size-4" /></button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <Pagination :links="data.links" :total="data.total" />
    </div>

    <Modal :show="showForm" :title="editTarget ? 'Edit Data Kesesuaian' : 'Tambah Data Kesesuaian'" max-width="lg" @close="showForm = false">
        <form @submit.prevent="submit" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Auditee <span class="text-red-500">*</span></label>
                    <select v-model="form.auditee_id" :disabled="!!editTarget" class="w-full rounded-lg border px-3 py-2 text-sm">
                        <option value="">Pilih Auditee</option>
                        <option v-for="a in auditeeList" :key="a.id" :value="a.id">{{ a.nama_auditee }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Periode AMI <span class="text-red-500">*</span></label>
                    <select v-model="form.pengaturan_periode_id" :disabled="!!editTarget" class="w-full rounded-lg border px-3 py-2 text-sm">
                        <option value="">Pilih Periode</option>
                        <option v-for="p in periodeList" :key="p.id" :value="p.id">{{ p.label }}</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Standar Mutu <span class="text-red-500">*</span></label>
                <select v-model="form.standar_mutu_id" :disabled="!!editTarget" class="w-full rounded-lg border px-3 py-2 text-sm">
                    <option value="">Pilih Standar</option>
                    <option v-for="s in standarList" :key="s.id" :value="s.id">[{{ s.kode }}] {{ s.nama_standar }}</option>
                </select>
            </div>
            <div><label class="block text-sm font-medium mb-1">Temuan Positif</label><textarea v-model="form.temuan_positif" rows="2" class="w-full rounded-lg border px-3 py-2 text-sm"></textarea></div>
            <div><label class="block text-sm font-medium mb-1">Deskripsi</label><textarea v-model="form.deskripsi" rows="2" class="w-full rounded-lg border px-3 py-2 text-sm"></textarea></div>
            <div><label class="block text-sm font-medium mb-1">Peningkatan</label><textarea v-model="form.peningkatan" rows="2" class="w-full rounded-lg border px-3 py-2 text-sm"></textarea></div>
            <div><label class="block text-sm font-medium mb-1">Nilai Mutu</label><input v-model="form.nilai_mutu" type="number" step="0.1" class="w-full rounded-lg border px-3 py-2 text-sm" /></div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" class="px-4 py-2 text-sm border rounded-lg hover:bg-gray-50" @click="showForm = false">Batal</button>
                <button type="submit" class="px-4 py-2 text-sm text-white bg-blue-600 rounded-lg hover:bg-blue-700" :disabled="form.processing">Simpan</button>
            </div>
        </form>
    </Modal>
    <ConfirmDeleteModal :show="showDelete" message="Hapus data kesesuaian ini?" @close="showDelete = false" @confirm="confirmDelete" />
</template>
