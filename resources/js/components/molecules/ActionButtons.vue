<script setup lang="ts">
import { Edit2, Trash2, Plus } from 'lucide-vue-next';

withDefaults(defineProps<{
    showEdit?: boolean;
    showDelete?: boolean;
    showAdd?: boolean;
    addTitle?: string;
    editTitle?: string;
    deleteTitle?: string;
    justify?: 'start' | 'center' | 'end';
}>(), {
    showEdit: true,
    showDelete: true,
    showAdd: false,
    addTitle: 'Tambah Sub',
    editTitle: 'Edit',
    deleteTitle: 'Hapus',
    justify: 'center',
});

const emit = defineEmits<{
    edit: [];
    delete: [];
    add: [];
}>();

const justifyClasses: Record<string, string> = {
    start:  'justify-start',
    center: 'justify-center',
    end:    'justify-end',
};
</script>

<template>
    <div class="flex items-center gap-0.5" :class="justifyClasses[justify]">
        <button
            v-if="showAdd"
            type="button"
            :title="addTitle"
            class="p-1.5 rounded-lg text-gray-400 hover:bg-blue-50 hover:text-blue-600 transition"
            @click.stop="emit('add')"
        >
            <Plus class="size-4" />
        </button>
        <button
            v-if="showEdit"
            type="button"
            :title="editTitle"
            class="p-1.5 rounded-lg text-gray-400 hover:bg-amber-50 hover:text-amber-500 transition"
            @click.stop="emit('edit')"
        >
            <Edit2 class="size-4" />
        </button>
        <button
            v-if="showDelete"
            type="button"
            :title="deleteTitle"
            class="p-1.5 rounded-lg text-gray-400 hover:bg-red-50 hover:text-red-500 transition"
            @click.stop="emit('delete')"
        >
            <Trash2 class="size-4" />
        </button>
    </div>
</template>
