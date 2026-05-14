<script setup lang="ts">
import BaseButton from '@/components/atoms/BaseButton.vue';

const props = withDefaults(defineProps<{
    cancelLabel?: string;
    submitLabel?: string;
    processing?: boolean;
    loadingLabel?: string;
}>(), {
    cancelLabel: 'Batal',
    submitLabel: 'Simpan',
    loadingLabel: 'Menyimpan...',
    processing: false,
});

const emit = defineEmits<{ cancel: [] }>();
</script>

<template>
    <div class="flex justify-end gap-3 pt-2">
        <BaseButton variant="secondary" type="button" @click="emit('cancel')">
            {{ props.cancelLabel }}
        </BaseButton>
        <BaseButton variant="primary" type="submit" :disabled="props.processing" :loading="props.processing">
            {{ props.processing ? props.loadingLabel : props.submitLabel }}
        </BaseButton>
    </div>
</template>
