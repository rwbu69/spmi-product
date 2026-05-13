<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { LogOut, User, Download } from 'lucide-vue-next';
import { computed } from 'vue';
import {
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu';
import UserInfo from '@/components/UserInfo.vue';
import { logout } from '@/routes';
import type { User as UserType } from '@/types';

type Props = {
    user: UserType;
};

defineProps<Props>();

const page = usePage();
const userRole = computed(() => (page.props.auth as any)?.role ?? '');

const handleLogout = () => {
    router.flushAll();
};

// Download user manual links per role
const userManuals: { label: string; href: string }[] = [
    { label: 'User Manual Admin', href: '/storage/manuals/admin.pdf' },
    { label: 'User Manual Auditor', href: '/storage/manuals/auditor.pdf' },
    { label: 'User Manual Fakultas', href: '/storage/manuals/fakultas.pdf' },
    { label: 'User Manual Prodi', href: '/storage/manuals/prodi.pdf' },
];
</script>

<template>
    <DropdownMenuLabel class="p-0 font-normal">
        <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
            <UserInfo :user="user" :show-email="true" />
        </div>
    </DropdownMenuLabel>
    <DropdownMenuSeparator />
    <DropdownMenuGroup>
        <DropdownMenuItem :as-child="true">
            <Link class="block w-full cursor-pointer" href="/settings/profile" prefetch>
                <User class="mr-2 h-4 w-4" />
                Profil
            </Link>
        </DropdownMenuItem>
    </DropdownMenuGroup>
    <DropdownMenuSeparator />
    <DropdownMenuGroup>
        <DropdownMenuItem v-for="manual in userManuals" :key="manual.label" :as-child="true">
            <a :href="manual.href" target="_blank" rel="noopener" class="flex w-full items-center cursor-pointer">
                <Download class="mr-2 h-4 w-4" />
                {{ manual.label }}
            </a>
        </DropdownMenuItem>
    </DropdownMenuGroup>
    <DropdownMenuSeparator />
    <DropdownMenuItem :as-child="true">
        <Link
            class="block w-full cursor-pointer"
            :href="logout()"
            @click="handleLogout"
            as="button"
            data-test="logout-button"
        >
            <LogOut class="mr-2 h-4 w-4" />
            Keluar
        </Link>
    </DropdownMenuItem>
</template>
