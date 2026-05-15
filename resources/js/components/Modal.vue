<script setup lang="ts">
import { X } from 'lucide-vue-next';
import { onMounted, onUnmounted } from 'vue';

const props = defineProps<{
    show: boolean;
    title: string;
    maxWidth?: 'sm' | 'md' | 'lg' | 'xl' | '2xl';
}>();

const emit = defineEmits<{
    close: [];
}>();

const maxWidthClass = {
    sm: 'max-w-sm',
    md: 'max-w-md',
    lg: 'max-w-lg',
    xl: 'max-w-xl',
    '2xl': 'max-w-2xl',
};

const handleKeydown = (e: KeyboardEvent) => {
    if (e.key === 'Escape' && props.show) emit('close');
};

onMounted(() => document.addEventListener('keydown', handleKeydown));
onUnmounted(() => document.removeEventListener('keydown', handleKeydown));
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="show"
                class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto p-4 sm:py-8"
            >
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="emit('close')" />

                <!-- Modal Panel -->
                <Transition
                    enter-active-class="transition duration-200 ease-out"
                    enter-from-class="scale-95 opacity-0"
                    enter-to-class="scale-100 opacity-100"
                    leave-active-class="transition duration-150 ease-in"
                    leave-from-class="scale-100 opacity-100"
                    leave-to-class="scale-95 opacity-0"
                >
                    <div
                        v-if="show"
                        class="relative z-10 w-full rounded-xl bg-white shadow-2xl dark:bg-gray-900"
                        :class="maxWidthClass[maxWidth ?? 'lg']"
                    >
                        <!-- Header -->
                        <div class="flex items-center justify-between border-b px-6 py-4 dark:border-gray-700">
                            <h2 class="text-base font-semibold text-gray-900 dark:text-white">
                                {{ title }}
                            </h2>
                            <button
                                type="button"
                                class="rounded-md p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300"
                                @click="emit('close')"
                            >
                                <X class="size-4" />
                            </button>
                        </div>

                        <!-- Body -->
                        <div class="max-h-[calc(100vh-10rem)] overflow-y-auto px-6 py-5">
                            <slot />
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>
