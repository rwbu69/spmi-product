<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ClipboardCheck, ClipboardX, Search, CheckSquare } from 'lucide-vue-next';
import { computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';

interface Stats {
    belum_desk_eval: number;
    sudah_desk_eval: number;
    belum_visitasi: number;
    sudah_visitasi: number;
}

const props = defineProps<{ stats: Stats }>();
defineOptions({ layout: AppLayout });

const totalDeskEval = computed(() => props.stats.belum_desk_eval + props.stats.sudah_desk_eval);
const totalVisitasi = computed(() => props.stats.belum_visitasi + props.stats.sudah_visitasi);

const pctDeskEval = computed(() =>
    totalDeskEval.value > 0
        ? Math.round((props.stats.sudah_desk_eval / totalDeskEval.value) * 100)
        : 0
);
const pctVisitasi = computed(() =>
    totalVisitasi.value > 0
        ? Math.round((props.stats.sudah_visitasi / totalVisitasi.value) * 100)
        : 0
);

const cards = [
    {
        key: 'belum_desk_eval' as const,
        label: 'Belum Desk Evaluation',
        desc: 'Auditee yang belum dievaluasi',
        href: '/auditor/desk-evaluation',
        iconBg: 'bg-red-50',
        iconColor: 'text-red-500',
        valueColor: 'text-red-600',
        icon: ClipboardX,
        badge: 'Perlu Tindakan',
        badgeColor: 'bg-red-100 text-red-600',
    },
    {
        key: 'sudah_desk_eval' as const,
        label: 'Sudah Desk Evaluation',
        desc: 'Auditee yang telah dievaluasi',
        href: '/auditor/desk-evaluation',
        iconBg: 'bg-blue-50',
        iconColor: 'text-blue-500',
        valueColor: 'text-blue-600',
        icon: ClipboardCheck,
        badge: 'Selesai',
        badgeColor: 'bg-blue-100 text-blue-600',
    },
    {
        key: 'belum_visitasi' as const,
        label: 'Belum Visitasi',
        desc: 'Auditee yang belum dikunjungi',
        href: '/auditor/visitasi',
        iconBg: 'bg-amber-50',
        iconColor: 'text-amber-500',
        valueColor: 'text-amber-600',
        icon: Search,
        badge: 'Perlu Tindakan',
        badgeColor: 'bg-amber-100 text-amber-600',
    },
    {
        key: 'sudah_visitasi' as const,
        label: 'Sudah Visitasi',
        desc: 'Auditee yang telah dikunjungi',
        href: '/auditor/visitasi',
        iconBg: 'bg-green-50',
        iconColor: 'text-green-500',
        valueColor: 'text-green-600',
        icon: CheckSquare,
        badge: 'Selesai',
        badgeColor: 'bg-green-100 text-green-600',
    },
] as const;
</script>

<template>
    <Head title="Beranda" />

    <div class="space-y-6 p-6">
        <!-- Page Heading -->
        <div>
            <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Beranda Auditor</h1>
            <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                Ringkasan progres audit pada periode aktif
            </p>
        </div>

        <!-- 4 Stat Cards -->
        <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
            <a
                v-for="card in cards"
                :key="card.key"
                :href="card.href"
                class="group relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-6 shadow-sm transition-all duration-200 hover:shadow-md hover:-translate-y-0.5 dark:bg-gray-900 dark:border-gray-800"
            >
                <!-- Subtle gradient on hover -->
                <div
                    class="pointer-events-none absolute inset-0 bg-gradient-to-br opacity-0 transition-opacity duration-300 group-hover:opacity-100"
                    :class="{
                        'from-red-50/60 to-transparent':   card.key === 'belum_desk_eval',
                        'from-blue-50/60 to-transparent':  card.key === 'sudah_desk_eval',
                        'from-amber-50/60 to-transparent': card.key === 'belum_visitasi',
                        'from-green-50/60 to-transparent': card.key === 'sudah_visitasi',
                    }"
                />

                <div class="relative z-10 flex flex-col gap-4">
                    <!-- Top row: icon + badge -->
                    <div class="flex items-start justify-between">
                        <div
                            class="flex size-11 items-center justify-center rounded-xl transition-colors group-hover:shadow-sm"
                            :class="[card.iconBg]"
                        >
                            <component :is="card.icon" class="size-5" :class="card.iconColor" />
                        </div>
                        <span
                            class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wider"
                            :class="card.badgeColor"
                        >
                            {{ card.badge }}
                        </span>
                    </div>

                    <!-- Value + label -->
                    <div>
                        <p class="text-4xl font-extrabold tracking-tight" :class="card.valueColor">
                            {{ stats[card.key] }}
                        </p>
                        <h3 class="mt-1.5 text-sm font-semibold text-gray-800 dark:text-gray-200">
                            {{ card.label }}
                        </h3>
                        <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">
                            {{ card.desc }}
                        </p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Progress Section -->
        <div class="grid gap-5 md:grid-cols-2">
            <!-- Desk Evaluation Progress -->
            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:bg-gray-900 dark:border-gray-800">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200">
                            Progres Desk Evaluation
                        </h3>
                        <p class="mt-0.5 text-xs text-gray-400">
                            {{ stats.sudah_desk_eval }} dari {{ totalDeskEval }} auditee
                        </p>
                    </div>
                    <span class="text-2xl font-extrabold text-blue-600">{{ pctDeskEval }}%</span>
                </div>
                <div class="mt-4 h-2.5 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                    <div
                        class="h-full rounded-full bg-blue-500 transition-all duration-700 ease-out"
                        :style="{ width: pctDeskEval + '%' }"
                    />
                </div>
                <div class="mt-3 flex justify-between text-xs text-gray-400">
                    <span>{{ stats.belum_desk_eval }} belum</span>
                    <span>{{ stats.sudah_desk_eval }} selesai</span>
                </div>
            </div>

            <!-- Visitasi Progress -->
            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:bg-gray-900 dark:border-gray-800">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200">
                            Progres Visitasi
                        </h3>
                        <p class="mt-0.5 text-xs text-gray-400">
                            {{ stats.sudah_visitasi }} dari {{ totalVisitasi }} auditee
                        </p>
                    </div>
                    <span class="text-2xl font-extrabold text-green-600">{{ pctVisitasi }}%</span>
                </div>
                <div class="mt-4 h-2.5 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                    <div
                        class="h-full rounded-full bg-green-500 transition-all duration-700 ease-out"
                        :style="{ width: pctVisitasi + '%' }"
                    />
                </div>
                <div class="mt-3 flex justify-between text-xs text-gray-400">
                    <span>{{ stats.belum_visitasi }} belum</span>
                    <span>{{ stats.sudah_visitasi }} selesai</span>
                </div>
            </div>
        </div>
    </div>
</template>
