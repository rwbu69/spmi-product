<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { CheckCircle2, XCircle } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';

interface Stats {
    belum_desk_eval: number;
    sudah_desk_eval: number;
    belum_visitasi: number;
    sudah_visitasi: number;
}

defineProps<{ stats: Stats }>();
defineOptions({ layout: AppLayout });

const cards = [
    {
        key: 'belum_desk_eval' as const,
        label: 'Belum Desk Evaluation',
        href: '/auditor/desk-evaluation',
        color: 'from-red-500 to-red-600',
        icon: XCircle,
    },
    {
        key: 'sudah_desk_eval' as const,
        label: 'Sudah Desk Evaluation',
        href: '/auditor/desk-evaluation',
        color: 'from-blue-500 to-blue-600',
        icon: CheckCircle2,
    },
    {
        key: 'belum_visitasi' as const,
        label: 'Belum Visitasi',
        href: '/auditor/visitasi',
        color: 'from-amber-400 to-amber-500',
        icon: XCircle,
    },
    {
        key: 'sudah_visitasi' as const,
        label: 'Sudah Visitasi',
        href: '/auditor/visitasi',
        color: 'from-green-500 to-green-600',
        icon: CheckCircle2,
    },
];
</script>

<template>
    <Head title="Beranda" />

    <div class="p-6">
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
            <a
                v-for="card in cards"
                :key="card.key"
                :href="card.href"
                class="group relative flex flex-col justify-between overflow-hidden rounded-2xl bg-gradient-to-br p-6 shadow-md transition-all duration-300 hover:scale-[1.02] hover:shadow-xl"
                :class="card.color"
            >
                <!-- Background icon (decorative) -->
                <component
                    :is="card.icon"
                    class="absolute -bottom-3 -right-3 size-28 opacity-10 text-white transition-opacity duration-300 group-hover:opacity-20"
                />

                <!-- Content -->
                <div class="relative z-10">
                    <p class="text-sm font-semibold text-white/90 leading-snug">
                        {{ card.label }}
                    </p>
                </div>

                <div class="relative z-10 mt-6 flex items-end justify-between">
                    <p class="text-5xl font-extrabold text-white leading-none">
                        {{ stats[card.key] }}
                        <span class="ml-1 text-xl font-medium text-white/80">Data</span>
                    </p>
                    <component
                        :is="card.icon"
                        class="size-8 text-white/70 transition-transform duration-300 group-hover:scale-110 group-hover:text-white"
                    />
                </div>
            </a>
        </div>
    </div>
</template>
