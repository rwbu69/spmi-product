<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { home } from '@/routes';

const page = usePage();
const name = page.props.name;

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex flex-col items-center justify-center p-4 sm:p-8 relative">
        <!-- Subtle background pattern or blobs -->
        <div class="absolute inset-0 z-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-1/4 -right-1/4 w-1/2 h-1/2 bg-blue-100/50 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-1/4 -left-1/4 w-1/2 h-1/2 bg-indigo-100/50 rounded-full blur-3xl"></div>
        </div>

        <div class="w-full max-w-4xl bg-white rounded-2xl shadow-xl overflow-hidden flex flex-col md:flex-row relative z-10 border border-gray-100">
            
            <!-- Left Side -->
            <div class="w-full md:w-[45%] bg-gradient-to-b from-gray-50 to-white p-10 flex flex-col items-center justify-center border-b md:border-b-0 md:border-r border-gray-100 relative overflow-hidden">
                <AppLogoIcon class="size-32 text-blue-700 mb-6 drop-shadow-sm" />
                
                <h2 class="text-xl font-bold tracking-tight text-gray-900 text-center uppercase">STTII YOGYAKARTA</h2>
                <p class="text-sm font-medium text-gray-500 mt-2 text-center">Sistem Penjaminan Mutu Internal</p>
            </div>
            
            <!-- Right Side -->
            <div class="w-full md:w-[55%] p-8 md:p-12 bg-white flex flex-col justify-center relative">
                
                <div class="flex flex-col space-y-1.5 mb-8 text-center">
                    <h1 class="text-2xl font-bold tracking-tight text-gray-900">Selamat Datang</h1>
                    <p class="text-sm text-gray-500">Silakan masuk ke akun Anda</p>
                </div>

                <div class="w-full">
                    <slot />
                </div>
            </div>
        </div>
        
        <div class="mt-8 text-center text-xs font-medium text-gray-400 relative z-10">
            STTII Yogyakarta &copy; {{ new Date().getFullYear() }}
        </div>
    </div>
</template>
