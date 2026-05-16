<script setup lang="ts">
import AppLogo from '@/components/AppLogo.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { Bell, ChevronDown } from 'lucide-vue-next';
import { router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import UserInfo from '@/components/UserInfo.vue';
import UserMenuContent from '@/components/UserMenuContent.vue';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

const page = usePage();
const user = computed(() => page.props.auth.user);
const notifications = computed(() => page.props.notifications || []);
const unreadCount = computed(() => (page.props as any).notificationsUnreadCount ?? 0);
const periodeAktif = computed(() => (page.props as any).periodeAktif ?? '-');

const markAllRead = () => {
    router.post('/notifications/read-all', {}, { preserveScroll: true, preserveState: true });
};

const openNotification = (notif: any) => {
    router.post('/notifications/read', { id: notif.id }, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            if (notif.link) {
                router.visit(notif.link);
            }
        },
    });
};
</script>

<template>
    <header class="flex h-16 shrink-0 items-center justify-between border-b border-blue-700 bg-blue-600 text-white px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4 shadow-sm">
        <div class="flex items-center gap-4">
            <SidebarTrigger class="-ml-1 text-white hover:bg-blue-700 hover:text-white" />
            <div class="flex items-center gap-3">
                <div class="pointer-events-none">
                    <AppLogo class="hidden md:block" />
                </div>
                <div class="hidden sm:block pointer-events-none">
                    <h1 class="text-sm font-bold leading-tight">Sistem Informasi Penjaminan Mutu Internal</h1>
                    <p class="text-xs text-blue-200">Nama Universitas</p>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-6">
            <span class="text-sm font-medium">Periode Aktif : {{ periodeAktif }}</span>
            <DropdownMenu>
                <DropdownMenuTrigger as-child>
                    <button class="relative p-2 hover:bg-blue-700 rounded-full transition outline-none">
                        <Bell class="size-5 text-white" />
                        <span
                            v-if="unreadCount"
                            class="absolute -right-2 -top-1 rounded-full bg-amber-500 px-1.5 py-0.5 text-[9px] font-semibold text-white"
                        >{{ unreadCount }} Baru</span>
                    </button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end" class="w-80 mt-2 p-0 border-gray-100 shadow-xl overflow-hidden rounded-xl">
                    <div class="bg-gray-50 border-b border-gray-100 px-4 py-3 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-900">Notifikasi</h3>
                        <span v-if="unreadCount" class="text-xs font-medium text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full">{{ unreadCount }} Baru</span>
                    </div>
                    <div class="max-h-80 overflow-y-auto">
                        <div v-if="notifications.length === 0" class="p-6 text-center text-gray-500 text-sm">
                            Tidak ada notifikasi baru
                        </div>
                        <button
                            v-else
                            v-for="(notif, idx) in notifications"
                            :key="idx"
                            type="button"
                            class="p-4 border-b border-gray-50 hover:bg-gray-50 transition cursor-pointer flex gap-3 relative w-full text-left"
                            @click="openNotification(notif)"
                        >
                            <span
                                class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-8 rounded-r-full"
                                :class="notif.is_read ? 'bg-gray-300' : (notif.type === 'blue' ? 'bg-blue-600' : (notif.type === 'amber' ? 'bg-amber-500' : (notif.type === 'red' ? 'bg-red-500' : 'bg-green-500')))"
                            ></span>
                            <div
                                class="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-full"
                                :class="notif.is_read ? 'bg-gray-100 text-gray-400' : (notif.type === 'blue' ? 'bg-blue-100 text-blue-600' : (notif.type === 'amber' ? 'bg-amber-100 text-amber-600' : (notif.type === 'red' ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-600')))"
                            >
                                <Bell class="size-4" />
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium" :class="notif.is_read ? 'text-gray-600' : 'text-gray-900'">{{ notif.title }}</p>
                                <p class="text-xs text-gray-500 mt-1">{{ notif.message }}</p>
                                <p class="text-[10px] text-gray-400 mt-2 font-medium">{{ notif.time }}</p>
                            </div>
                        </button>
                    </div>
                    <div class="bg-gray-50 border-t border-gray-100 p-2 text-center" v-if="notifications.length">
                        <button type="button" class="text-xs font-medium text-blue-600 hover:text-blue-700 transition" @click="markAllRead">Tandai semua dibaca</button>
                    </div>
                </DropdownMenuContent>
            </DropdownMenu>

            <DropdownMenu>
                <DropdownMenuTrigger class="flex items-center gap-2 hover:bg-blue-700 px-2 py-1.5 rounded-full transition outline-none">
                    <UserInfo :user="user" />
                    <ChevronDown class="size-4 opacity-70" />
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end" class="w-56 mt-2 border-gray-100 shadow-lg">
                    <UserMenuContent :user="user" />
                </DropdownMenuContent>
            </DropdownMenu>
        </div>
    </header>
</template>
