<script setup lang="ts">
withDefaults(defineProps<{
    emptyMessage?: string;
    isEmpty?: boolean;
    colSpan?: number;
}>(), {
    emptyMessage: 'Tidak ada data.',
    isEmpty: false,
    colSpan: 99,
});
</script>

<template>
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="w-full text-sm text-left">
            <!-- Header slot -->
            <thead class="bg-blue-50 border-b border-blue-100">
                <tr>
                    <slot name="head" />
                </tr>
            </thead>

            <!-- Body -->
            <tbody class="divide-y divide-gray-100">
                <!-- Empty state -->
                <tr v-if="isEmpty">
                    <td
                        :colspan="colSpan"
                        class="px-4 py-12 text-center text-gray-400 italic"
                    >
                        {{ emptyMessage }}
                    </td>
                </tr>
                <!-- Row slot -->
                <slot v-else />
            </tbody>
        </table>
    </div>
</template>
