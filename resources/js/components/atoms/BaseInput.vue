<script setup lang="ts">
defineOptions({ inheritAttrs: false });

const model = defineModel<string | number | null>();

const props = withDefaults(defineProps<{
    error?: string;
    disabled?: boolean;
}>(), {
    disabled: false,
});
</script>

<template>
    <div class="w-full">
        <input
            v-bind="$attrs"
            v-model="model"
            :disabled="props.disabled"
            class="w-full rounded-lg border px-3 py-2 text-sm outline-none transition focus:ring-2 focus:ring-blue-200"
            :class="[
                error
                    ? 'border-red-400 focus:border-red-400 focus:ring-red-100'
                    : 'border-gray-300 focus:border-blue-500',
                props.disabled ? 'bg-gray-50 cursor-not-allowed' : 'bg-white',
            ]"
        />
        <p v-if="error" class="mt-1 text-[11px] text-red-500">{{ error }}</p>
    </div>
</template>
