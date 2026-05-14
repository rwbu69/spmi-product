<script setup lang="ts">
defineOptions({ inheritAttrs: false });

const model = defineModel<string | number>();

const props = withDefaults(defineProps<{
    error?: string;
    disabled?: boolean;
    placeholder?: string;
}>(), {
    disabled: false,
    placeholder: 'Pilih...',
});
</script>

<template>
    <div class="w-full">
        <select
            v-bind="$attrs"
            v-model="model"
            :disabled="props.disabled"
            class="rounded-lg border px-3 py-2 text-sm outline-none transition focus:ring-2 focus:ring-blue-200 bg-white"
            :class="[
                error
                    ? 'border-red-400 focus:border-red-400 focus:ring-red-100'
                    : 'border-gray-300 focus:border-blue-500',
                props.disabled ? 'bg-gray-50 cursor-not-allowed' : '',
            ]"
        >
            <slot />
        </select>
        <p v-if="error" class="mt-1 text-[11px] text-red-500">{{ error }}</p>
    </div>
</template>
