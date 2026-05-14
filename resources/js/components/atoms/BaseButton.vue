<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(defineProps<{
    variant?: 'primary' | 'secondary' | 'danger' | 'ghost' | 'success';
    size?: 'sm' | 'md' | 'lg';
    type?: 'button' | 'submit' | 'reset';
    disabled?: boolean;
    loading?: boolean;
    iconOnly?: boolean;
}>(), {
    variant: 'primary',
    size: 'md',
    type: 'button',
    disabled: false,
    loading: false,
    iconOnly: false,
});

const variantClasses: Record<string, string> = {
    primary:   'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500 border border-transparent',
    secondary: 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 focus:ring-blue-500',
    danger:    'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500 border border-transparent',
    ghost:     'bg-transparent text-gray-600 hover:bg-gray-100 border border-transparent focus:ring-gray-400',
    success:   'bg-green-600 text-white hover:bg-green-700 focus:ring-green-500 border border-transparent',
};

const sizeClasses: Record<string, string> = {
    sm: 'px-3 py-1.5 text-xs gap-1.5',
    md: 'px-4 py-2 text-sm gap-2',
    lg: 'px-5 py-2.5 text-base gap-2',
};

const iconOnlySizeClasses: Record<string, string> = {
    sm: 'p-1.5',
    md: 'p-2',
    lg: 'p-2.5',
};

const classes = computed(() => [
    'inline-flex items-center justify-center font-medium rounded-lg transition focus:outline-none focus:ring-2 focus:ring-offset-1',
    variantClasses[props.variant],
    props.iconOnly ? iconOnlySizeClasses[props.size] : sizeClasses[props.size],
    (props.disabled || props.loading) ? 'opacity-60 cursor-not-allowed' : '',
]);
</script>

<template>
    <button
        :type="type"
        :disabled="disabled || loading"
        :class="classes"
    >
        <slot v-if="!loading" />
        <span v-else class="flex items-center gap-2">
            <svg class="size-4 animate-spin" viewBox="0 0 24 24" fill="none">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
            </svg>
            <slot />
        </span>
    </button>
</template>
