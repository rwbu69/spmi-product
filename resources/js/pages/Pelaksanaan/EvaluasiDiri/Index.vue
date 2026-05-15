<script setup lang="ts">
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { Edit2, Plus, Search, Trash2, AlertTriangle, X, CheckCircle, Clock } from 'lucide-vue-next';
import { ref, watch, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import ConfirmDeleteModal from '@/components/ConfirmDeleteModal.vue';
import Modal from '@/components/Modal.vue';

interface PeriodeAlert {
    label: string;
    mulai: string;
    selesai: string;
    hari_lewat: number;
    is_active: boolean;
    is_expired: boolean;
}

interface EvaluasiDiri {
    id: number;
    nilai_evaluasi: number;
    status: 'Draft' | 'Submitted' | 'Approved';
    auditee: { id: number; nama_auditee: string };
    pengaturan_periode: { id: number; tahun_periode: { tahun: number }; lembaga_akreditasi: { nama_lembaga: string }; };
}

const props = defineProps<{
    data: { data: EvaluasiDiri[]; total: number; links: any[]; };
    filters: { search?: string; periode_id?: string };
    periodeList: { id: number; label: string }[];
    auditeeList: { id: number; nama_auditee: string }[];
    periodeAlerts?: PeriodeAlert[];
    lembagaCount?: number;
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
const dismissedAlerts = ref<number[]>([]);

let t: any;
watch([search, periode_id], () => {
    clearTimeout(t);
    t = setTimeout(() => router.get('/pelaksanaan/evaluasi-diri', { search: search.value, periode_id: periode_id.value }, { preserveState: true, replace: true }), 400);
});

const activeAlerts = computed(() => (props.periodeAlerts ?? []).filter((_, i) => !dismissedAlerts.value.includes(i)));
const dismissAlert = (idx: number) => dismissedAlerts.value.push(idx);

const showForm = ref(false);
const editTarget = ref<EvaluasiDiri | null>(null);
const form = useForm({ pengaturan_periode_id: '', auditee_id: '', status: 'Draft' });

const openCreate = () => { editTarget.value = null; form.reset(); showForm.value = true; };
const openEdit = (item: EvaluasiDiri) => {
    editTarget.value = item;
    form.pengaturan_periode_id = item.pengaturan_periode.id.toString();
    form.auditee_id = item.auditee.id.toString();
    form.status = item.status;
    showForm.value = true;
};
const submit = () => {
    form.post('/pelaksanaan/evaluasi-diri', { preserveScroll: true, onSuccess: () => { showForm.value = false; } });
};

const showDelete = ref(false);
const deleteTarget = ref<EvaluasiDiri | null>(null);
const confirmDelete = () => {
    if (!deleteTarget.value) return;
    router.delete(`/pelaksanaan/evaluasi-diri/${deleteTarget.value.id}`, { preserveScroll: true, onSuccess: () => { showDelete.value = false; } });
};

const getStatusBadge = (s: string) => ({
    'Approved':  'bg-green-100 text-green-700',
    'Submitted': 'bg-blue-100 text-blue-700',
}[s] ?? 'bg-amber-100 text-amber-700');
</script>

<template>
    <Head title="Evaluasi Diri" />
    <div class="space-y-6 p-6">

        <!-- Page Header -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-semibold text-gray-900">Evaluasi Diri{{ isAuditee ? '' : ' Auditee' }}</h1>
                <p class="mt-1 text-sm text-gray-500">{{ isAuditee ? 'Status evaluasi diri Anda berdasarkan periode berjalan' : 'Monitoring pengisian evaluasi diri oleh unit/auditee' }}</p>
            </div>
            <button v-if="isAdmin" type="button" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700" @click="openCreate">
                <Plus class="size-4" /> Inisialisasi Evaluasi
            </button>
        </div>

        <!-- ════════════════════════════════════════════════════════ -->
        <!-- AUDITEE / UNIT PENUNJANG VIEW                         -->
        <!-- ════════════════════════════════════════════════════════ -->
        <template v-if="isAuditee">
            <!-- Period count badge: Total Lembaga Akreditasi in this active period -->
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-700 font-medium">Total Lembaga Akreditasi Periode Ini :</span>
                <span class="inline-flex size-6 items-center justify-center rounded-full bg-orange-500 text-xs font-bold text-white">{{ lembagaCount ?? 0 }}</span>
            </div>

            <!-- Alert banners for each Lembaga Akreditasi entry in the active period -->
            <div
                v-for="(alert, idx) in activeAlerts"
                :key="idx"
                class="relative flex items-start gap-3 rounded-lg px-5 py-4 text-white"
                :class="alert.is_expired ? 'bg-red-500' : (alert.is_active ? 'bg-green-500' : 'bg-gray-400')"
            >
                <AlertTriangle v-if="alert.is_expired" class="size-5 flex-shrink-0 mt-0.5" />
                <Clock v-else-if="alert.is_active" class="size-5 flex-shrink-0 mt-0.5" />
                <CheckCircle v-else class="size-5 flex-shrink-0 mt-0.5" />
                <div class="flex-1">
                    <p class="font-bold text-sm">⚠ {{ alert.label }}</p>
                    <p class="text-sm mt-0.5">
                        <template v-if="alert.is_expired">
                            Jadwal Evaluasi Diri sudah berlangsung pada tanggal
                            <strong>{{ alert.mulai }}</strong> sampai dengan
                            <strong>{{ alert.selesai }}</strong> dan sudah lewat selama
                            <strong>{{ alert.hari_lewat }}</strong> hari.
                        </template>
                        <template v-else-if="alert.is_active">
                            Jadwal Evaluasi Diri sedang berlangsung dari tanggal
                            <strong>{{ alert.mulai }}</strong> sampai dengan
                            <strong>{{ alert.selesai }}</strong>.
                        </template>
                        <template v-else>
                            Jadwal Evaluasi Diri: <strong>{{ alert.mulai }}</strong> - <strong>{{ alert.selesai }}</strong>
                        </template>
                    </p>
                </div>
                <button @click="dismissAlert(idx)" class="flex-shrink-0 text-white/70 hover:text-white transition">
                    <X class="size-4" />
                </button>
            </div>

            <!-- Empty state when no periods -->
            <div v-if="(lembagaCount ?? 0) === 0" class="rounded-lg border border-dashed border-gray-200 py-12 text-center text-gray-400">
                Belum ada periode evaluasi aktif.
            </div>
        </template>

        <!-- ════════════════════════════════════════════════════════ -->
        <!-- ADMIN / AUDITOR VIEW: Search filter + full data table   -->
        <!-- ════════════════════════════════════════════════════════ -->
        <template v-else>
            <!-- Search + Filter row -->
            <div class="flex flex-wrap gap-4 items-center">
                <div class="relative w-full max-w-xs">
                    <Search class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-gray-400" />
                    <input v-model="search" type="text" placeholder="Cari auditee..." class="w-full rounded-lg border border-gray-300 py-2 pr-4 pl-9 text-sm outline-none focus:border-blue-500" />
                </div>
                <select v-model="periode_id" class="rounded-lg border border-gray-300 py-2 px-3 text-sm">
                    <option value="">Semua Periode</option>
                    <option v-for="p in periodeList" :key="p.id" :value="p.id">{{ p.label }}</option>
                </select>
            </div>

            <!-- Data table -->
            <div class="overflow-hidden rounded-xl border bg-white shadow-sm">
                <table class="w-full text-sm text-left">
                    <thead class="bg-blue-50 border-b border-blue-100">
                        <tr>
                            <th class="px-4 py-3 font-medium text-gray-600">Auditee</th>
                            <th class="px-4 py-3 font-medium text-gray-600">Periode</th>
                            <th class="px-4 py-3 font-medium text-gray-600">Skor ED</th>
                            <th class="px-4 py-3 font-medium text-gray-600">Status</th>
                            <th v-if="isAdmin" class="px-4 py-3 text-center font-medium text-gray-600">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-if="data.data.length === 0">
                            <td :colspan="isAdmin ? 5 : 4" class="px-4 py-12 text-center text-gray-400 italic">Belum ada data evaluasi diri.</td>
                        </tr>
                        <tr v-for="item in data.data" :key="item.id" class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ item.auditee.nama_auditee }}</td>
                            <td class="px-4 py-3 text-gray-500 text-xs">{{ item.pengaturan_periode.lembaga_akreditasi.nama_lembaga }} ({{ item.pengaturan_periode.tahun_periode.tahun }})</td>
                            <td class="px-4 py-3 font-bold text-blue-600">{{ item.nilai_evaluasi.toFixed(2) }}</td>
                            <td class="px-4 py-3">
                                <span :class="getStatusBadge(item.status)" class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase">{{ item.status }}</span>
                            </td>
                            <td v-if="isAdmin" class="px-4 py-3">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button" class="inline-flex items-center gap-1.5 rounded-md px-2 py-1 text-xs font-medium bg-gray-50 text-gray-600 hover:bg-gray-100" @click="openEdit(item)">
                                        <Edit2 class="size-3.5" /> Edit Status
                                    </button>
                                    <button type="button" class="p-1.5 text-gray-400 hover:text-red-500" @click="() => { deleteTarget = item; showDelete = true; }"><Trash2 class="size-4" /></button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </template>
    </div>


    <Modal :show="showForm" title="Inisialisasi Evaluasi Baru" max-width="md" @close="showForm = false">
        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1">Periode AMI <span class="text-red-500">*</span></label>
                <select v-model="form.pengaturan_periode_id" class="w-full rounded-lg border px-3 py-2 text-sm">
                    <option value="">Pilih Periode</option>
                    <option v-for="p in periodeList" :key="p.id" :value="p.id">{{ p.label }}</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Auditee <span class="text-red-500">*</span></label>
                <select v-model="form.auditee_id" class="w-full rounded-lg border px-3 py-2 text-sm">
                    <option value="">Pilih Auditee</option>
                    <option v-for="a in auditeeList" :key="a.id" :value="a.id">{{ a.nama_auditee }}</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Status</label>
                <select v-model="form.status" class="w-full rounded-lg border px-3 py-2 text-sm">
                    <option value="Draft">Draft</option>
                    <option value="Submitted">Submitted</option>
                    <option value="Approved">Approved</option>
                </select>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" class="px-4 py-2 text-sm border rounded-lg hover:bg-gray-50" @click="showForm = false">Batal</button>
                <button type="submit" class="px-4 py-2 text-sm text-white bg-blue-600 rounded-lg hover:bg-blue-700" :disabled="form.processing">Simpan</button>
            </div>
        </form>
    </Modal>
    <ConfirmDeleteModal :show="showDelete" message="Hapus data evaluasi ini?" @close="showDelete = false" @confirm="confirmDelete" />
</template>
