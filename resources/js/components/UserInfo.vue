<script setup lang="ts">
import { User as UserIcon } from 'lucide-vue-next';
import type { User } from '@/types';

type Props = {
    user: User;
    showEmail?: boolean;
};

const props = withDefaults(defineProps<Props>(), {
    showEmail: false,
});
</script>

<template>
    <div class="flex items-center justify-center h-8 w-8 rounded-full bg-black/10">
        <UserIcon class="size-4 opacity-80" />
    </div>

    <div class="grid flex-1 text-left text-sm leading-tight">
        <span class="truncate font-medium">{{ user.name }}</span>
        <span v-if="showEmail" class="truncate text-xs opacity-70">{{
            user.email
        }}</span>
    </div>
</template>
