<script setup lang="ts">
const props = withDefaults(defineProps<{
    variant?: 'success' | 'warning' | 'danger' | 'info' | 'default';
    size?: 'sm' | 'xs';
    rounded?: 'sm' | 'full';
}>(), {
    variant: 'default',
    size: 'xs',
    rounded: 'full',
});

const variantClasses: Record<string, string> = {
    success: 'bg-green-100 text-green-700',
    warning: 'bg-amber-100 text-amber-700',
    danger:  'bg-red-100 text-red-600',
    info:    'bg-blue-100 text-blue-700',
    default: 'bg-gray-100 text-gray-600',
};

const sizeClasses: Record<string, string> = {
    xs: 'px-2 py-0.5 text-[10px]',
    sm: 'px-2.5 py-0.5 text-xs',
};

const roundedClasses: Record<string, string> = {
    sm:   'rounded',
    full: 'rounded-full',
};
</script>

<template>
    <span
        class="inline-flex items-center font-semibold uppercase tracking-wide"
        :class="[variantClasses[variant], sizeClasses[size], roundedClasses[rounded]]"
    >
        <slot />
    </span>
</template>
