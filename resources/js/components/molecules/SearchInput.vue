<script setup lang="ts">
import { Search } from 'lucide-vue-next';

const model = defineModel<string>();

withDefaults(defineProps<{
    placeholder?: string;
}>(), {
    placeholder: 'Cari...',
});
</script>

<template>
    <div class="relative w-full max-w-xs">
        <Search class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-gray-400 pointer-events-none" />
        <input
            v-model="model"
            type="text"
            :placeholder="placeholder"
            class="w-full rounded-lg border border-gray-300 py-2 pr-4 pl-9 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
        />
    </div>
</template>
