<script setup lang="ts">
import AppLogo from '@/components/AppLogo.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { Bell, ChevronDown } from 'lucide-vue-next';
import { usePage, Link } from '@inertiajs/vue3';
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
            <span class="text-sm font-medium">Periode Aktif : 2025</span>
            <DropdownMenu>
                <DropdownMenuTrigger as-child>
                    <button class="relative p-2 hover:bg-blue-700 rounded-full transition outline-none">
                        <Bell class="size-5 text-white" />
                        <span
                            v-if="notifications.length"
                            class="absolute -right-0.5 -top-0.5 flex size-5 items-center justify-center rounded-full bg-green-400 text-[10px] font-bold text-white border-2 border-blue-600"
                        >{{ notifications.length }}</span>
                    </button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end" class="w-80 mt-2 p-0 border-gray-100 shadow-xl overflow-hidden rounded-xl">
                    <div class="bg-gray-50 border-b border-gray-100 px-4 py-3 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-900">Notifikasi</h3>
                        <span v-if="notifications.length" class="text-xs font-medium text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full">{{ notifications.length }} Baru</span>
                    </div>
                    <div class="max-h-80 overflow-y-auto">
                        <div v-if="notifications.length === 0" class="p-6 text-center text-gray-500 text-sm">
                            Tidak ada notifikasi baru
                        </div>
                        <Link v-else v-for="(notif, idx) in notifications" :key="idx" :href="notif.link || '#'" class="p-4 border-b border-gray-50 hover:bg-gray-50 transition cursor-pointer flex gap-3 relative w-full text-left">
                            <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-8 rounded-r-full" :class="notif.type === 'blue' ? 'bg-blue-600' : (notif.type === 'amber' ? 'bg-amber-500' : (notif.type === 'red' ? 'bg-red-500' : 'bg-green-500'))"></span>
                            <div class="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-full" :class="notif.type === 'blue' ? 'bg-blue-100 text-blue-600' : (notif.type === 'amber' ? 'bg-amber-100 text-amber-600' : (notif.type === 'red' ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-600'))">
                                <Bell class="size-4" />
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">{{ notif.title }}</p>
                                <p class="text-xs text-gray-500 mt-1">{{ notif.message }}</p>
                                <p class="text-[10px] text-gray-400 mt-2 font-medium">{{ notif.time }}</p>
                            </div>
                        </Link>
                    </div>
                    <div class="bg-gray-50 border-t border-gray-100 p-2 text-center" v-if="notifications.length">
                        <button class="text-xs font-medium text-blue-600 hover:text-blue-700 transition">Tandai semua dibaca</button>
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
