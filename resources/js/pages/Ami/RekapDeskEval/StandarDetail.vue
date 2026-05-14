<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Layers } from 'lucide-vue-next';
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
    standar: {
        id: number;
        kode: string;
        nama_standar: string;
        level: number;
        total_children: number;
        total_indikator: number;
    };
    children: StandarNode[];
    periode: string;
}>();

defineOptions({ layout: AppLayout });
</script>

<template>
    <Head :title="`Hasil Desk Evaluasi – ${standar.kode}`" />
    <div class="space-y-6 p-6">

        <!-- Header -->
        <div class="flex flex-col gap-1">
            <h1 class="text-xl font-semibold text-gray-900">Hasil Desk Evaluation</h1>
            <!-- Breadcrumb -->
            <nav class="text-xs text-gray-400 flex items-center gap-1 flex-wrap">
                <Link href="/beranda" class="hover:text-gray-600">Beranda</Link>
                <span>/</span>
                <Link href="/ami/rekap-desk-eval" class="hover:text-gray-600">Lembaga Akreditasi</Link>
                <span>/</span>
                <Link :href="`/ami/rekap-desk-eval/standar/${lembaga.id}`" class="hover:text-gray-600">Standar</Link>
                <span>/</span>
                <span class="text-gray-700 font-medium">Sub Standar</span>
            </nav>
        </div>

        <!-- Info Table -->
        <div class="rounded-lg border border-gray-200 bg-white overflow-hidden shadow-sm">
            <table class="w-full text-sm">
                <tbody>
                    <tr class="border-b border-gray-100">
                        <td class="px-5 py-3 text-gray-500 bg-gray-50 w-48 font-medium">Periode</td>
                        <td class="px-5 py-3 text-gray-800">: {{ periode }}</td>
                    </tr>
                    <tr class="border-b border-gray-100">
                        <td class="px-5 py-3 text-gray-500 bg-gray-50 font-medium">Lembaga Akreditasi</td>
                        <td class="px-5 py-3 text-gray-800">: {{ lembaga.nama_lembaga }}</td>
                    </tr>
                    <tr>
                        <td class="px-5 py-3 text-gray-500 bg-gray-50 font-medium">Jumlah Standar Mutu</td>
                        <td class="px-5 py-3">
                            <span class="text-gray-700 mr-2">:</span>
                            <span class="inline-flex items-center rounded px-2 py-0.5 text-[11px] font-bold bg-green-500 text-white mr-1">
                                {{ standar.total_children }} Sub Standar Mutu
                            </span>
                            <span class="inline-flex items-center rounded px-2 py-0.5 text-[11px] font-bold bg-cyan-500 text-white">
                                {{ standar.total_indikator }} Daftar Titik Pertanyaan
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Standar Mutu section -->
        <div class="rounded-lg border border-gray-200 bg-white overflow-hidden shadow-sm">
            <!-- Section header -->
            <div class="flex items-center gap-2 px-5 py-3 border-b border-gray-100 bg-gray-50">
                <Layers class="size-4 text-gray-500" />
                <span class="text-sm font-semibold text-gray-700">Standar Mutu</span>
            </div>

            <!-- Children list -->
            <div v-if="children.length > 0">
                <div
                    v-for="child in children"
                    :key="child.id"
                    class="px-5 py-3 border-b border-gray-50 last:border-0 hover:bg-gray-50 transition"
                >
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-sm font-medium text-gray-800">{{ child.kode }} {{ child.nama_standar }}</span>
                        <span class="inline-flex items-center rounded px-1.5 py-0.5 text-[10px] font-bold bg-green-500 text-white">
                            {{ child.total_children }} Sub Standar Mutu
                        </span>
                        <span class="inline-flex items-center rounded px-1.5 py-0.5 text-[10px] font-bold bg-cyan-500 text-white">
                            {{ child.total_indikator }} Daftar Titik Pertanyaan
                        </span>
                    </div>
                </div>
            </div>

            <!-- Empty state -->
            <div v-else class="px-5 py-8 text-center text-gray-400 text-sm">
                Tidak ada daftar standar mutu
            </div>
        </div>
    </div>
</template>
