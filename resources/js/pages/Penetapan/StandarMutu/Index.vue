<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ChevronDown, ChevronRight, Plus, Trash2, Edit2, ChevronsDownUp, ChevronsUpDown } from 'lucide-vue-next';
import { ref, defineComponent, h, type PropType } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import ConfirmDeleteModal from '@/components/ConfirmDeleteModal.vue';
import { PageHeader, FormModal } from '@/components/index';
import { BaseButton, BaseInput, BaseTextarea, BaseSelect } from '@/components/index';
import { FormField, FormActions } from '@/components/index';

interface Indikator { id: number; deskripsi: string; bobot: number; }
interface StandarNode {
    id: number; kode: string; nama_standar: string; level: number;
    deskripsi: string | null; data_dukung: string | null;
    lembaga_akreditasi: { id: number; nama_lembaga: string };
    tahun_periode: { id: number; tahun: number };
    indikator: Indikator[];
    children_recursive: StandarNode[];
}

const props = defineProps<{
    standarTree: StandarNode[];
    totalStandar: number;
    filters: { search?: string; lembaga_id?: string; tahun_id?: string };
    lembagaList: { id: number; nama_lembaga: string }[];
    tahunList: { id: number; tahun: number }[];
    lembagaAll: { id: number; nama_lembaga: string }[];
    tahunAll: { id: number; tahun: number }[];
}>();

defineOptions({ layout: AppLayout });

// ── Filters ──────────────────────────────────────────────────────────────────
const lembaga_id = ref(props.filters.lembaga_id ?? '');
const tahun_id   = ref(props.filters.tahun_id   ?? '');
const applyFilter = () => router.get('/penetapan/standar-mutu', {
    lembaga_id: lembaga_id.value || undefined,
    tahun_id:   tahun_id.value   || undefined,
}, { preserveState: true, replace: true });

// ── Expand / Collapse ─────────────────────────────────────────────────────────
const expandedIds = ref<Set<number>>(new Set());

const collectAll = (nodes: StandarNode[]): number[] => {
    const ids: number[] = [];
    nodes.forEach(n => { ids.push(n.id); ids.push(...collectAll(n.children_recursive)); });
    return ids;
};
const expandAll  = () => { expandedIds.value = new Set(collectAll(props.standarTree)); };
const collapseAll = () => { expandedIds.value = new Set(); };
const toggle = (id: number) => {
    const s = new Set(expandedIds.value);
    s.has(id) ? s.delete(id) : s.add(id);
    expandedIds.value = s;
};

// ── Add / Edit forms ──────────────────────────────────────────────────────────
const showForm   = ref(false);
const editTarget = ref<any>(null);

const form = useForm({
    kode: '', nama_standar: '', deskripsi: '', data_dukung: '',
    parent_id: null as number | null,
    lembaga_akreditasi_id: '' as string | number,
    tahun_periode_id: '' as string | number,
    level: 1,
});

const openCreate = (parent?: StandarNode) => {
    editTarget.value = null;
    form.reset();
    if (parent) {
        form.parent_id = parent.id;
        form.level = parent.level + 1;
        form.lembaga_akreditasi_id = parent.lembaga_akreditasi.id;
        form.tahun_periode_id = parent.tahun_periode.id;
    }
    showForm.value = true;
};
const openEdit = (node: StandarNode) => {
    editTarget.value = node;
    form.kode = node.kode;
    form.nama_standar = node.nama_standar;
    form.deskripsi = node.deskripsi ?? '';
    form.data_dukung = node.data_dukung ?? '';
    form.lembaga_akreditasi_id = node.lembaga_akreditasi.id;
    form.tahun_periode_id = node.tahun_periode.id;
    form.level = node.level;
    form.parent_id = null;
    showForm.value = true;
};
const submitForm = () => {
    const opts = { preserveScroll: true, onSuccess: () => { showForm.value = false; } };
    if (editTarget.value) {
        form.put(`/penetapan/standar-mutu/${editTarget.value.id}`, opts);
    } else {
        form.post('/penetapan/standar-mutu', opts);
    }
};

// ── Delete ────────────────────────────────────────────────────────────────────
const showDelete = ref(false);
const deleteId   = ref<number | null>(null);
const openDelete = (id: number) => { deleteId.value = id; showDelete.value = true; };
const confirmDelete = () => {
    if (!deleteId.value) return;
    router.delete(`/penetapan/standar-mutu/${deleteId.value}`, {
        preserveScroll: true, onSuccess: () => { showDelete.value = false; }
    });
};

// ── Indikator forms ───────────────────────────────────────────────────────────
const showIndForm  = ref(false);
const editIndTarget = ref<any>(null);
const indForm = useForm({ standar_mutu_id: 0, deskripsi: '', bobot: 0 });

const openCreateInd = (standarId: number) => {
    editIndTarget.value = null; indForm.reset();
    indForm.standar_mutu_id = standarId;
    showIndForm.value = true;
};
const openEditInd = (ind: Indikator, standarId: number) => {
    editIndTarget.value = ind;
    indForm.standar_mutu_id = standarId;
    indForm.deskripsi = ind.deskripsi;
    indForm.bobot = ind.bobot;
    showIndForm.value = true;
};
const submitInd = () => {
    const opts = { preserveScroll: true, onSuccess: () => { showIndForm.value = false; } };
    if (editIndTarget.value) {
        indForm.put(`/penetapan/indikator/${editIndTarget.value.id}`, opts);
    } else {
        indForm.post('/penetapan/indikator', opts);
    }
};
const showDeleteInd = ref(false);
const deleteIndId = ref<number | null>(null);
const openDeleteInd = (id: number) => { deleteIndId.value = id; showDeleteInd.value = true; };
const confirmDeleteInd = () => {
    if (!deleteIndId.value) return;
    router.delete(`/penetapan/indikator/${deleteIndId.value}`, {
        preserveScroll: true, onSuccess: () => { showDeleteInd.value = false; }
    });
};

// ── Recursive tree row component ──────────────────────────────────────────────
const TreeRow = defineComponent({
    name: 'TreeRow',
    props: {
        node: { type: Object as PropType<StandarNode>, required: true },
        depth: { type: Number, default: 0 },
        expandedIds: { type: Object as PropType<Set<number>>, required: true },
    },
    emits: ['toggle', 'add-sub', 'edit', 'delete', 'add-ind', 'edit-ind', 'delete-ind'],
    setup(props, { emit }) {
        return () => {
            const { node, depth } = props;
            const isOpen = props.expandedIds.has(node.id);
            const hasChildren = node.children_recursive?.length > 0;
            const hasIndikator = node.indikator?.length > 0;
            const paddingLeft = `${depth * 20 + 12}px`;

            // Depth-based styling
            const rowBg = depth === 0 ? '#f3f4f6' : depth === 1 ? '#f9fafb' : '#ffffff';
            const fontClass = depth === 0 ? 'font-bold text-sm text-gray-800'
                : depth === 1 ? 'font-semibold text-sm text-gray-700'
                : 'font-medium text-sm text-gray-700';

            const rows: any[] = [];

            // Main standar row
            rows.push(
                h('tr', {
                    key: `row-${node.id}`,
                    style: { backgroundColor: rowBg },
                    class: ['border-b border-gray-100', hasChildren ? 'cursor-pointer select-none' : ''].join(' '),
                    onClick: hasChildren ? () => emit('toggle', node.id) : undefined,
                }, [
                    // Standar column
                    h('td', { class: 'py-2 pr-4', style: { paddingLeft } }, [
                        h('div', { class: 'flex items-center gap-2' }, [
                            hasChildren
                                ? h('button', {
                                    type: 'button',
                                    class: 'flex-shrink-0 text-gray-400 hover:text-gray-700',
                                    onClick: () => emit('toggle', node.id),
                                }, h(isOpen ? ChevronDown : ChevronRight, { class: 'size-3.5' }))
                                : h('span', { class: 'size-3.5 flex-shrink-0' }),
                            h('span', { class: fontClass }, `${node.kode} ${node.nama_standar}`),
                        ]),
                    ]),
                    // Indikator count
                    h('td', { class: 'py-2 px-4 text-right text-xs text-blue-600 font-medium w-28' },
                        hasIndikator ? `${node.indikator.length} indikator` : ''
                    ),
                    // Actions
                    h('td', { class: 'py-2 px-3 w-36', onClick: (e: Event) => e.stopPropagation() }, [
                        h('div', { class: 'flex items-center gap-0.5 justify-end' }, [
                            // Add Sub — blue tint hover
                            h('button', {
                                type: 'button', title: 'Tambah Sub-Standar',
                                class: 'p-1.5 rounded-lg text-gray-400 hover:bg-blue-50 hover:text-blue-600 transition',
                                onClick: (e: Event) => { e.stopPropagation(); emit('add-sub', node); },
                            }, h(Plus, { class: 'size-4' })),
                            // Edit — amber tint hover
                            h('button', {
                                type: 'button', title: 'Edit Standar',
                                class: 'p-1.5 rounded-lg text-gray-400 hover:bg-amber-50 hover:text-amber-500 transition',
                                onClick: (e: Event) => { e.stopPropagation(); emit('edit', node); },
                            }, h(Edit2, { class: 'size-4' })),
                            // Delete — red tint hover
                            h('button', {
                                type: 'button', title: 'Hapus Standar',
                                class: 'p-1.5 rounded-lg text-gray-400 hover:bg-red-50 hover:text-red-500 transition',
                                onClick: (e: Event) => { e.stopPropagation(); emit('delete', node.id); },
                            }, h(Trash2, { class: 'size-4' })),
                        ]),
                    ]),
                ])
            );

            // Indikator rows (shown when node is expanded or is leaf)
            if (hasIndikator && (isOpen || !hasChildren)) {
                node.indikator.forEach((ind) => {
                    rows.push(
                        h('tr', {
                            key: `ind-${ind.id}`,
                            class: 'border-b border-gray-50 hover:bg-gray-50 transition',
                        }, [
                            h('td', { class: 'py-2 pr-4 text-xs text-gray-600 italic', style: { paddingLeft: `${depth * 20 + 36}px` } },
                                ind.deskripsi
                            ),
                            h('td', { class: 'py-2 px-4 text-right text-xs text-gray-400' }, `${ind.bobot}%`),
                            h('td', { class: 'py-2 px-3' }, [
                                h('div', { class: 'flex items-center gap-0.5 justify-end' }, [
                                    h('button', {
                                        type: 'button', title: 'Edit Indikator',
                                        class: 'p-1.5 rounded-lg text-gray-400 hover:bg-amber-50 hover:text-amber-500 transition',
                                        onClick: () => emit('edit-ind', ind, node.id),
                                    }, h(Edit2, { class: 'size-4' })),
                                    h('button', {
                                        type: 'button', title: 'Hapus Indikator',
                                        class: 'p-1.5 rounded-lg text-gray-400 hover:bg-red-50 hover:text-red-500 transition',
                                        onClick: () => emit('delete-ind', ind.id),
                                    }, h(Trash2, { class: 'size-4' })),
                                ]),
                            ]),
                        ])
                    );
                });
                // Add indikator button row
                rows.push(
                    h('tr', { key: `add-ind-${node.id}`, class: 'border-b border-gray-50' }, [
                        h('td', { class: 'py-1.5 text-xs', colspan: 3, style: { paddingLeft: `${depth * 20 + 36}px` } }, [
                            h('button', {
                                type: 'button',
                                class: 'text-blue-600 hover:underline text-xs',
                                onClick: () => emit('add-ind', node.id),
                            }, '+ Tambah Indikator'),
                        ]),
                    ])
                );
            }

            // Recurse children
            if (hasChildren && isOpen) {
                node.children_recursive.forEach(child => {
                    rows.push(h(TreeRow, {
                        key: `tree-${child.id}`,
                        node: child,
                        depth: depth + 1,
                        expandedIds: props.expandedIds,
                        onToggle: (id: number) => emit('toggle', id),
                        onAddSub: (n: StandarNode) => emit('add-sub', n),
                        onEdit: (n: StandarNode) => emit('edit', n),
                        onDelete: (id: number) => emit('delete', id),
                        onAddInd: (id: number) => emit('add-ind', id),
                        onEditInd: (ind: Indikator, sid: number) => emit('edit-ind', ind, sid),
                        onDeleteInd: (id: number) => emit('delete-ind', id),
                    }));
                });
            }

            return rows;
        };
    },
});
</script>

<template>
    <Head title="Daftar Standar Mutu" />
    <div class="space-y-5 p-6">

        <!-- Page Header -->
        <PageHeader title="Daftar Standar Mutu" subtitle="Kelola pernyataan standar mutu internal">
            <template #actions>
                <BaseButton variant="primary" @click="openCreate()">
                    <Plus class="size-4" /> Tambah Standar
                </BaseButton>
            </template>
        </PageHeader>

        <!-- Filters + controls row -->
        <div class="flex flex-wrap items-center gap-3">
            <!-- Expand / Collapse -->
            <BaseButton variant="secondary" size="sm" @click="expandAll">
                <ChevronsUpDown class="size-3.5" /> Expand all
            </BaseButton>
            <BaseButton variant="secondary" size="sm" @click="collapseAll">
                <ChevronsDownUp class="size-3.5" /> Collapse all
            </BaseButton>

            <!-- Tahun -->
            <BaseSelect v-model="tahun_id" @change="applyFilter">
                <option value="">Semua Tahun</option>
                <option v-for="t in tahunList" :key="t.id" :value="t.id">{{ t.tahun }}</option>
            </BaseSelect>

            <!-- Lembaga -->
            <BaseSelect v-model="lembaga_id" @change="applyFilter">
                <option value="">Semua Lembaga</option>
                <option v-for="l in lembagaList" :key="l.id" :value="l.id">{{ l.nama_lembaga }}</option>
            </BaseSelect>

            <!-- Count badge -->
            <span class="ml-auto inline-flex items-center gap-1.5 rounded-full bg-blue-600 px-3 py-1 text-xs font-bold text-white">
                {{ totalStandar }} Standar Mutu
            </span>
        </div>

        <!-- Tree Table -->
        <div class="rounded-lg border border-gray-200 bg-white overflow-hidden shadow-sm">
            <table class="w-full">
                <thead>
                    <tr class="bg-blue-50 border-b border-blue-100">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide">Standar Mutu</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wide w-28">Indikator</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wide w-36">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <template v-if="standarTree.length === 0">
                        <tr>
                            <td colspan="3" class="py-12 text-center text-gray-400 italic text-sm">
                                Belum ada standar mutu. Klik "Tambah Standar" untuk memulai.
                            </td>
                        </tr>
                    </template>
                    <template v-for="node in standarTree" :key="node.id">
                        <TreeRow
                            :node="node"
                            :depth="0"
                            :expanded-ids="expandedIds"
                            @toggle="toggle"
                            @add-sub="openCreate"
                            @edit="openEdit"
                            @delete="openDelete"
                            @add-ind="openCreateInd"
                            @edit-ind="openEditInd"
                            @delete-ind="openDeleteInd"
                        />
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add / Edit Standar Modal -->
    <FormModal :show="showForm" :title="editTarget ? 'Edit Standar Mutu' : 'Tambah Standar Mutu'" max-width="md" @close="showForm = false">
        <form @submit.prevent="submitForm" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <FormField label="Kode" :required="true">
                    <BaseInput v-model="form.kode" type="text" placeholder="Contoh: A.1.1" />
                </FormField>
                <FormField label="Level" :required="true">
                    <BaseInput v-model="form.level" type="number" :min="1" />
                </FormField>
            </div>
            <FormField label="Nama Standar" :required="true">
                <BaseInput v-model="form.nama_standar" type="text" />
            </FormField>
            <FormField label="Lembaga Akreditasi" :required="true">
                <BaseSelect v-model="form.lembaga_akreditasi_id">
                    <option value="">Pilih Lembaga</option>
                    <option v-for="l in lembagaAll" :key="l.id" :value="l.id">{{ l.nama_lembaga }}</option>
                </BaseSelect>
            </FormField>
            <FormField label="Tahun Periode" :required="true">
                <BaseSelect v-model="form.tahun_periode_id">
                    <option value="">Pilih Tahun</option>
                    <option v-for="t in tahunAll" :key="t.id" :value="t.id">{{ t.tahun }}</option>
                </BaseSelect>
            </FormField>
            <FormField label="Deskripsi">
                <BaseTextarea v-model="form.deskripsi" :rows="3" />
            </FormField>
            <FormActions :processing="form.processing" @cancel="showForm = false" />
        </form>
    </FormModal>

    <!-- Add / Edit Indikator Modal -->
    <FormModal :show="showIndForm" :title="editIndTarget ? 'Edit Indikator' : 'Tambah Indikator'" max-width="md" @close="showIndForm = false">
        <form @submit.prevent="submitInd" class="space-y-4">
            <FormField label="Deskripsi Indikator" :required="true">
                <BaseTextarea v-model="indForm.deskripsi" :rows="4" />
            </FormField>
            <FormField label="Bobot (%)" :required="true">
                <BaseInput v-model="indForm.bobot" type="number" :min="0" :max="100" />
            </FormField>
            <FormActions :processing="indForm.processing" @cancel="showIndForm = false" />
        </form>
    </FormModal>

    <ConfirmDeleteModal :show="showDelete" message="Hapus standar mutu ini?" @close="showDelete = false" @confirm="confirmDelete" />
    <ConfirmDeleteModal :show="showDeleteInd" message="Hapus indikator ini?" @close="showDeleteInd = false" @confirm="confirmDeleteInd" />
</template>
