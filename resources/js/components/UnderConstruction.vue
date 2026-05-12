<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Construction } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });

const props = defineProps<{
    title: string;
    description?: string;
}>();
</script>

<template>
    <Head :title="title" />
    <div class="flex min-h-[60vh] flex-col items-center justify-center gap-4 p-6 text-center">
        <div class="flex size-16 items-center justify-center rounded-2xl bg-blue-50 dark:bg-blue-900/20">
            <Construction class="size-8 text-blue-500" />
        </div>
        <div>
            <h2 class="text-lg font-semibold text-gray-800 dark:text-white">{{ title }}</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ description ?? 'Halaman ini sedang dalam pengembangan.' }}
            </p>
        </div>
    </div>
</template>
