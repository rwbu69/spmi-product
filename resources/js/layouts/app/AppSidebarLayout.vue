<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import FlashMessage from '@/components/FlashMessage.vue';
import { Toaster } from '@/components/ui/sonner';
import type { BreadcrumbItem } from '@/types';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppShell variant="sidebar" class="pt-16">
        <AppSidebarHeader class="fixed top-0 left-0 right-0 w-full z-50" />
        <AppSidebar />
        <AppContent variant="sidebar" class="overflow-x-hidden min-h-[calc(100vh-4rem)] bg-gray-50/50">
            <div v-if="breadcrumbs && breadcrumbs.length > 0" class="px-6 pt-4">
                <Breadcrumbs :breadcrumbs="breadcrumbs" class="text-gray-500" />
            </div>
            <slot />
        </AppContent>
        <Toaster />
        <FlashMessage />
    </AppShell>
</template>
