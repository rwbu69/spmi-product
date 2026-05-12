<script setup lang="ts">
import { AlertTriangle } from 'lucide-vue-next';
import Modal from '@/components/Modal.vue';

defineProps<{
    show: boolean;
    title?: string;
    message?: string;
    confirmLabel?: string;
    processing?: boolean;
}>();

const emit = defineEmits<{
    close: [];
    confirm: [];
}>();
</script>

<template>
    <Modal :show="show" :title="title ?? 'Konfirmasi Hapus'" max-width="sm" @close="emit('close')">
        <div class="flex flex-col items-center gap-4 text-center">
            <div class="flex size-14 items-center justify-center rounded-full bg-red-50 dark:bg-red-900/20">
                <AlertTriangle class="size-7 text-red-500" />
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                {{ message ?? 'Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.' }}
            </p>
        </div>
        <div class="mt-6 flex justify-end gap-3">
            <button
                type="button"
                class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800"
                @click="emit('close')"
            >
                Batal
            </button>
            <button
                type="button"
                :disabled="processing"
                class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-red-700 disabled:opacity-60"
                @click="emit('confirm')"
            >
                {{ processing ? 'Menghapus...' : (confirmLabel ?? 'Ya, Hapus') }}
            </button>
        </div>
    </Modal>
</template>
