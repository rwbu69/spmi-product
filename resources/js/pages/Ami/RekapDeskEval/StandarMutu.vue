<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ChevronDown, ChevronRight, Layers, HelpCircle } from 'lucide-vue-next';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';

interface StandarNode {
    id: number;
    kode: string;
    nama_standar: string;
    level: number;
    total_children: number;
    total_indikator: number;
    children: StandarNode[];
}

const props = defineProps<{
    lembaga: { id: number; nama_lembaga: string };
    standarTree: StandarNode[];
    totalStandar: number;
}>();

defineOptions({ layout: AppLayout });

// Track expanded state per standar id
const expandedIds = ref<Set<number>>(new Set());

const toggle = (id: number) => {
    if (expandedIds.value.has(id)) {
        expandedIds.value.delete(id);
    } else {
        expandedIds.value.add(id);
    }
    // Force reactivity
    expandedIds.value = new Set(expandedIds.value);
};

const expandAll = (nodes: StandarNode[]) => {
    const addAll = (items: StandarNode[]) => {
        items.forEach(n => {
            expandedIds.value.add(n.id);
            if (n.children?.length) addAll(n.children);
        });
    };
    addAll(nodes);
    expandedIds.value = new Set(expandedIds.value);
};

const collapseAll = () => {
    expandedIds.value = new Set();
};

const isExpanded = (id: number) => expandedIds.value.has(id);

// Determine background color based on level
const rowBg = (level: number) => {
    if (level <= 1) return 'bg-gray-100';
    if (level === 2) return 'bg-gray-50';
    return 'bg-white';
};

const rowTextClass = (level: number) => {
    if (level <= 1) return 'font-semibold text-gray-800 text-sm';
    if (level === 2) return 'font-medium text-gray-700 text-sm';
    return 'text-gray-700 text-sm';
};

const indentClass = (level: number) => {
    const map: Record<number, string> = { 1: 'pl-2', 2: 'pl-6', 3: 'pl-12', 4: 'pl-16' };
    return map[level] ?? 'pl-2';
};
</script>

<template>
    <Head :title="`Standar Mutu - ${lembaga.nama_lembaga}`" />
    <div class="space-y-6 p-6">

        <!-- Header -->
        <div class="flex flex-col gap-1">
            <h1 class="text-xl font-semibold text-gray-900">Hasil Desk Evaluasi</h1>
            <!-- Breadcrumb -->
            <nav class="text-xs text-gray-400 flex items-center gap-1">
                <Link href="/beranda" class="hover:text-gray-600">Beranda</Link>
                <span>/</span>
                <Link href="/ami/rekap-desk-eval" class="hover:text-gray-600">Lembaga Akreditasi</Link>
                <span>/</span>
                <span class="text-gray-600 font-medium">Standar</span>
            </nav>
        </div>

        <!-- Section title + count badge -->
        <div class="flex items-center justify-between">
            <h2 class="text-base font-semibold text-gray-800">Daftar Standar Mutu</h2>
            <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-600 px-3 py-1 text-xs font-bold text-white">
                {{ totalStandar }} Standar Mutu
            </span>
        </div>

        <!-- Expand/Collapse controls -->
        <div class="flex items-center gap-2">
            <button
                type="button"
                class="inline-flex items-center gap-1.5 rounded px-3 py-1.5 text-xs font-semibold text-white bg-amber-500 hover:bg-amber-600 transition"
                @click="expandAll(standarTree)"
            >
                <ChevronDown class="size-3.5" /> Expand all
            </button>
            <button
                type="button"
                class="inline-flex items-center gap-1.5 rounded px-3 py-1.5 text-xs font-semibold text-white bg-amber-500 hover:bg-amber-600 transition"
                @click="collapseAll"
            >
                <ChevronRight class="size-3.5" /> Collapse all
            </button>
        </div>

        <!-- Tree container -->
        <div class="rounded border border-gray-200 overflow-hidden">
            <!-- Header row -->
            <div class="bg-gray-200 px-4 py-2 text-xs font-bold text-gray-600 uppercase tracking-wide">
                Standar Mutu
            </div>

            <!-- Tree nodes -->
            <template v-for="standar in standarTree" :key="standar.id">
                <StandarTreeNode
                    :node="standar"
                    :expanded-ids="expandedIds"
                    :lembaga-id="lembaga.id"
                    @toggle="toggle"
                />
            </template>

            <div v-if="standarTree.length === 0" class="px-4 py-12 text-center text-gray-400 italic">
                Belum ada standar mutu untuk lembaga ini.
            </div>
        </div>
    </div>
</template>

<script lang="ts">
// Recursive tree node component defined inline
import { defineComponent, h, computed, type PropType } from 'vue';
import { ChevronDown as CDIcon, ChevronRight as CRIcon } from 'lucide-vue-next';

interface StandarNodeType {
    id: number;
    kode: string;
    nama_standar: string;
    level: number;
    total_children: number;
    total_indikator: number;
    children: StandarNodeType[];
}

const StandarTreeNode = defineComponent({
    name: 'StandarTreeNode',
    props: {
        node: { type: Object as PropType<StandarNodeType>, required: true },
        expandedIds: { type: Object as PropType<Set<number>>, required: true },
        lembagaId: { type: Number, required: true },
        depth: { type: Number, default: 0 },
    },
    emits: ['toggle'],
    setup(props, { emit }) {
        const isOpen = computed(() => props.expandedIds.has(props.node.id));
        const hasChildren = computed(() => props.node.children && props.node.children.length > 0);
        const paddingLeft = computed(() => `${props.depth * 20 + 12}px`);
        const isLeaf = computed(() => !hasChildren.value);

        const bgClass = computed(() => {
            if (props.depth === 0) return '#f3f4f6';
            if (props.depth === 1) return '#f9fafb';
            return '#ffffff';
        });

        return () => {
            const node = props.node;

            const rowVNodes = [
                // Main row
                h('div', {
                    class: 'flex items-center border-b border-gray-100',
                    style: { backgroundColor: bgClass.value },
                }, [
                    // Toggle + label
                    h('div', {
                        class: 'flex flex-1 items-center gap-2 px-3 py-2 cursor-pointer select-none',
                        style: { paddingLeft: paddingLeft.value },
                        onClick: () => hasChildren.value && emit('toggle', node.id),
                    }, [
                        // Chevron
                        hasChildren.value
                            ? h(isOpen.value ? CDIcon : CRIcon, { class: 'size-3.5 text-gray-400 flex-shrink-0' })
                            : h('span', { class: 'size-3.5 flex-shrink-0' }),
                        // Kode + Nama
                        h('span', {
                            class: props.depth === 0
                                ? 'font-semibold text-gray-800 text-sm'
                                : props.depth === 1
                                    ? 'font-medium text-gray-700 text-sm'
                                    : 'text-gray-700 text-sm',
                        }, `${node.kode} ${node.nama_standar}`),
                        // Badges (for leaves)
                        ...(isLeaf.value ? [
                            h('span', { class: 'ml-2 inline-flex items-center gap-0.5 rounded px-1.5 py-0.5 text-[10px] font-bold bg-green-500 text-white' }, [
                                `${node.total_children} Sub Standar Mutu`
                            ]),
                            h('span', { class: 'inline-flex items-center gap-0.5 rounded px-1.5 py-0.5 text-[10px] font-bold bg-cyan-500 text-white' }, [
                                `${node.total_indikator} Daftar Titik Pertanyaan`
                            ]),
                        ] : []),
                    ]),
                    // Pilih button (only for leaf nodes)
                    isLeaf.value
                        ? h('a', {
                            href: `/ami/rekap-desk-eval/standar/${props.lembagaId}/detail/${node.id}`,
                            class: 'mr-3 inline-flex items-center gap-1 rounded bg-blue-500 px-3 py-1 text-xs font-semibold text-white hover:bg-blue-600 transition flex-shrink-0',
                        }, ['Pilih ', h(CRIcon, { class: 'size-3' })])
                        : null,
                ]),
            ];

            // Children
            if (hasChildren.value && isOpen.value) {
                rowVNodes.push(
                    ...node.children.map((child: StandarNodeType) =>
                        h(StandarTreeNode, {
                            key: child.id,
                            node: child,
                            expandedIds: props.expandedIds,
                            lembagaId: props.lembagaId,
                            depth: props.depth + 1,
                            onToggle: (id: number) => emit('toggle', id),
                        })
                    )
                );
            }

            return h('div', {}, rowVNodes);
        };
    },
});

export default { components: { StandarTreeNode } };
</script>
