<script setup lang="ts">
import { CheckCircle2, XCircle, X } from 'lucide-vue-next';
import { usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const page = usePage<{ flash: { success?: string; error?: string } }>();
const flash = computed(() => page.props.flash ?? {});

const visible = ref(false);

watch(
    () => flash.value,
    (val) => {
        if (val.success || val.error) {
            visible.value = true;
            setTimeout(() => (visible.value = false), 4000);
        }
    },
    { immediate: true, deep: true },
);
</script>

<template>
    <Transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="translate-y-4 opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="translate-y-0 opacity-100"
        leave-to-class="translate-y-4 opacity-0"
    >
        <div
            v-if="visible && (flash.success || flash.error)"
            class="fixed bottom-6 right-6 z-[100] flex items-start gap-3 rounded-xl border px-5 py-4 shadow-xl"
            :class="flash.success
                ? 'border-green-200 bg-green-50 text-green-800 dark:border-green-800 dark:bg-green-900/30 dark:text-green-300'
                : 'border-red-200 bg-red-50 text-red-800 dark:border-red-800 dark:bg-red-900/30 dark:text-red-300'"
        >
            <CheckCircle2 v-if="flash.success" class="mt-0.5 size-5 flex-shrink-0 text-green-500" />
            <XCircle v-else class="mt-0.5 size-5 flex-shrink-0 text-red-500" />
            <p class="max-w-xs text-sm font-medium">{{ flash.success ?? flash.error }}</p>
            <button type="button" class="ml-2 rounded p-0.5 opacity-60 transition hover:opacity-100" @click="visible = false">
                <X class="size-4" />
            </button>
        </div>
    </Transition>
</template>
