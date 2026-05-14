<script setup lang="ts">
withDefaults(defineProps<{
    required?: boolean;
    for?: string;
}>(), {
    required: false,
});
</script>

<template>
    <label
        :for="$props.for"
        class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300"
    >
        <slot />
        <span v-if="required" class="ml-0.5 text-red-500">*</span>
    </label>
</template>
