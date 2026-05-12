<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Search, Users, ShieldCheck } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';

interface User {
    id: number;
    name: string;
    email: string;
}

const props = defineProps<{
    data: {
        data: User[];
        total: number;
        links: any[];
    };
    filters: { search?: string };
}>();

defineOptions({ layout: AppLayout });

const search = ref(props.filters.search ?? '');

let searchTimeout: any;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get('/pengaturan/pengguna-portal', { search: search.value }, { preserveState: true, replace: true });
    }, 400);
});
</script>

<template>
    <Head title="Pengguna Portal" />
    <div class="space-y-6 p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-semibold text-gray-900 ">Pengguna Portal Auditee</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Daftar pengguna yang memiliki akses ke portal pengisian SPMI</p>
            </div>
        </div>

        <div class="flex flex-wrap gap-4 items-center">
            <div class="relative w-full max-w-xs">
                <Search class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-gray-400" />
                <input v-model="search" type="text" placeholder="Cari nama atau email..." class="w-full rounded-lg border border-gray-300 py-2 pr-4 pl-9 text-sm outline-none transition focus:border-blue-500  " />
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="item in data.data" :key="item.id" class="p-6 rounded-2xl border bg-white dark:bg-gray-900 shadow-sm hover:shadow-md transition flex items-center gap-4">
                <div class="size-12 rounded-full bg-green-50 dark:bg-green-900/20 flex items-center justify-center text-green-600 shrink-0">
                    <Users class="size-6" />
                </div>
                <div class="min-w-0">
                    <h3 class="font-bold text-gray-900  truncate">{{ item.name }}</h3>
                    <p class="text-xs text-gray-500 truncate">{{ item.email }}</p>
                    <div class="mt-2 flex items-center gap-1 text-[10px] font-bold text-green-600 uppercase tracking-tighter">
                        <ShieldCheck class="size-3" /> Akun Terverifikasi
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
