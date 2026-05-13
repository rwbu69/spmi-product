<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

const props = defineProps<{
    links: PaginationLink[];
    total?: number;
    from?: number;
    to?: number;
}>();

// Label pertama = Previous, label terakhir = Next (dari Laravel paginator)
const isPrev  = (link: PaginationLink) => link.label.includes('Previous') || link.label.includes('&laquo;') || link.label === 'pagination.previous';
const isNext  = (link: PaginationLink) => link.label.includes('Next')     || link.label.includes('&raquo;') || link.label === 'pagination.next';
const isPage  = (link: PaginationLink) => !isPrev(link) && !isNext(link);

const go = (url: string | null) => {
    if (url) router.get(url, {}, { preserveState: true, preserveScroll: true });
};
</script>

<template>
    <div class="flex items-center justify-between text-sm text-gray-500 dark:text-gray-400">
        <!-- Kiri: info total -->
        <span v-if="total !== undefined && from !== undefined && to !== undefined">
            Menampilkan {{ from }}–{{ to }} dari {{ total }} data
        </span>
        <span v-else-if="total !== undefined">
            Total: {{ total }} data
        </span>
        <span v-else />

        <!-- Kanan: tombol navigasi -->
        <div class="flex items-center gap-1">
            <!-- Previous -->
            <template v-for="link in links" :key="link.label">
                <button
                    v-if="isPrev(link)"
                    type="button"
                    :disabled="!link.url"
                    class="inline-flex items-center justify-center rounded-md p-1.5 transition"
                    :class="link.url
                        ? 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700'
                        : 'cursor-not-allowed text-gray-300 dark:text-gray-600'"
                    @click="go(link.url)"
                    aria-label="Halaman sebelumnya"
                >
                    <ChevronLeft class="size-4" />
                </button>
            </template>

            <!-- Page numbers -->
            <template v-for="link in links" :key="link.label">
                <button
                    v-if="isPage(link)"
                    type="button"
                    :disabled="!link.url"
                    class="min-w-[2rem] rounded-md px-2 py-1 text-center transition"
                    :class="[
                        link.active
                            ? 'bg-blue-600 font-semibold text-white'
                            : link.url
                                ? 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700'
                                : 'cursor-default text-gray-400 dark:text-gray-600',
                    ]"
                    @click="go(link.url)"
                >
                    {{ link.label }}
                </button>
            </template>

            <!-- Next -->
            <template v-for="link in links" :key="link.label">
                <button
                    v-if="isNext(link)"
                    type="button"
                    :disabled="!link.url"
                    class="inline-flex items-center justify-center rounded-md p-1.5 transition"
                    :class="link.url
                        ? 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700'
                        : 'cursor-not-allowed text-gray-300 dark:text-gray-600'"
                    @click="go(link.url)"
                    aria-label="Halaman berikutnya"
                >
                    <ChevronRight class="size-4" />
                </button>
            </template>
        </div>
    </div>
</template>
