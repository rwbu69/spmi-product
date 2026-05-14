<script setup lang="ts">
import BaseLabel from '@/components/atoms/BaseLabel.vue';

const props = withDefaults(defineProps<{
    label: string;
    for?: string;
    required?: boolean;
    error?: string;
    hint?: string;
}>(), {
    required: false,
});
</script>

<template>
    <div class="w-full">
        <BaseLabel :for="props.for" :required="props.required">
            {{ label }}
        </BaseLabel>
        <div class="w-full [&>input]:w-full [&>select]:w-full [&>textarea]:w-full">
            <slot />
        </div>
        <p v-if="error" class="mt-1 text-[11px] text-red-500">{{ error }}</p>
        <p v-else-if="hint" class="mt-1 text-[11px] text-gray-400">{{ hint }}</p>
    </div>
</template>
