<script setup lang="ts">
import Modal from '@/components/Modal.vue';

const props = defineProps<{
    show: boolean;
    title: string;
    maxWidth?: 'sm' | 'md' | 'lg' | 'xl' | '2xl';
}>();

const emit = defineEmits<{ close: [] }>();
</script>

<template>
    <Modal
        :show="props.show"
        :title="props.title"
        :max-width="props.maxWidth ?? 'md'"
        @close="emit('close')"
    >
        <slot />
    </Modal>
</template>
