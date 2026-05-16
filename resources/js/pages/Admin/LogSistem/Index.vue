<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import Pagination from '@/components/Pagination.vue';
import { DataTable } from '@/components/index';

interface NotificationRow {
    id: number;
    title: string;
    description: string;
    causer_name: string;
    causer_role: string;
    time: string;
    is_read: boolean;
}

const props = defineProps<{
    data: { data: NotificationRow[]; total: number; links: any[]; };
    filters: { date_from?: string; date_to?: string; role?: string; status?: string; per_page?: string };
    roleOptions: string[];
}>();

defineOptions({ layout: AppLayout });

const dateFrom = ref(props.filters.date_from ?? '');
const dateTo = ref(props.filters.date_to ?? '');
const role = ref(props.filters.role ?? '');
const status = ref(props.filters.status ?? '');
const perPage = ref(props.filters.per_page ?? '10');

let t: any;
watch([dateFrom, dateTo, role, status, perPage], () => {
    clearTimeout(t);
    t = setTimeout(() => {
        router.get('/admin/log-sistem', {
            date_from: dateFrom.value || undefined,
            date_to: dateTo.value || undefined,
            role: role.value || undefined,
            status: status.value || undefined,
            per_page: perPage.value || undefined,
        }, { preserveState: true, replace: true });
    }, 300);
});

const markRead = (row: NotificationRow) => {
    router.post('/notifications/read', { id: row.id }, { preserveScroll: true, preserveState: true });
};
</script>

<template>
    <Head title="Pemberitahuan Update" />
    <div class="space-y-6 p-6">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Pemberitahuan Update</h1>
            <p class="mt-1 text-sm text-gray-500">Riwayat perubahan data pada sistem</p>
        </div>

        <div class="rounded-xl border bg-white p-4 shadow-sm">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
                <div>
                    <label class="text-xs font-semibold text-gray-600">Tanggal Mulai</label>
                    <input v-model="dateFrom" type="date" class="mt-1 w-full rounded border border-gray-300 px-3 py-2 text-sm" />
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-600">Tanggal Akhir</label>
                    <input v-model="dateTo" type="date" class="mt-1 w-full rounded border border-gray-300 px-3 py-2 text-sm" />
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-600">Role</label>
                    <select v-model="role" class="mt-1 w-full rounded border border-gray-300 px-3 py-2 text-sm">
                        <option value="">Semua Role</option>
                        <option v-for="r in roleOptions" :key="r" :value="r">{{ r }}</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-600">Status</label>
                    <select v-model="status" class="mt-1 w-full rounded border border-gray-300 px-3 py-2 text-sm">
                        <option value="">Semua</option>
                        <option value="unread">Belum Dibaca</option>
                        <option value="read">Dibaca</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-600">Per Page</label>
                    <select v-model="perPage" class="mt-1 w-full rounded border border-gray-300 px-3 py-2 text-sm">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </div>
            </div>
        </div>

        <DataTable :is-empty="data.data.length === 0" empty-message="Belum ada riwayat notifikasi." :col-span="6">
            <template #head>
                <th class="px-4 py-3 font-medium text-gray-600">Judul</th>
                <th class="px-4 py-3 font-medium text-gray-600">Deskripsi</th>
                <th class="px-4 py-3 font-medium text-gray-600">Role / User</th>
                <th class="px-4 py-3 font-medium text-gray-600">Waktu</th>
                <th class="px-4 py-3 font-medium text-gray-600">Status</th>
                <th class="px-4 py-3 font-medium text-gray-600">Aksi</th>
            </template>

            <tr v-for="row in data.data" :key="row.id" class="hover:bg-gray-50 transition">
                <td class="px-4 py-3 font-medium text-gray-900">{{ row.title }}</td>
                <td class="px-4 py-3 text-xs text-gray-600">{{ row.description }}</td>
                <td class="px-4 py-3 text-xs text-gray-600">
                    <div class="font-medium text-gray-800">{{ row.causer_name }}</div>
                    <div class="text-[10px] text-gray-400">{{ row.causer_role }}</div>
                </td>
                <td class="px-4 py-3 text-xs text-gray-500">{{ row.time }}</td>
                <td class="px-4 py-3">
                    <span
                        class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold"
                        :class="row.is_read ? 'bg-gray-100 text-gray-500' : 'bg-amber-100 text-amber-700'"
                    >
                        {{ row.is_read ? 'Dibaca' : 'Belum Dibaca' }}
                    </span>
                </td>
                <td class="px-4 py-3">
                    <button
                        v-if="!row.is_read"
                        type="button"
                        class="rounded bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 hover:bg-blue-100"
                        @click="markRead(row)"
                    >
                        Tandai dibaca
                    </button>
                </td>
            </tr>
        </DataTable>

        <Pagination :links="data.links" :total="data.total" />
    </div>
</template>
