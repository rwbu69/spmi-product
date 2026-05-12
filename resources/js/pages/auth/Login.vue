<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

defineOptions({
    layout: {
        title: 'Masuk ke Akun Anda',
        description: 'Masukkan email dan kata sandi untuk masuk ke dalam sistem',
    },
});

defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean; // Kept prop but we won't show it per user request
}>();

import { ref, onMounted } from 'vue';
const num1 = ref(0);
const num2 = ref(0);
const captchaInput = ref('');
const captchaError = ref(false);

const generateCaptcha = () => {
    num1.value = Math.floor(Math.random() * 10) + 1;
    num2.value = Math.floor(Math.random() * 10) + 1;
    captchaInput.value = '';
    captchaError.value = false;
};

onMounted(() => {
    generateCaptcha();
});

const validateCaptcha = (e: Event) => {
    if (parseInt(captchaInput.value) !== num1.value + num2.value) {
        e.preventDefault();
        captchaError.value = true;
        generateCaptcha();
    } else {
        captchaError.value = false;
    }
};
</script>

<template>
    <Head title="Log in" />

    <div
        v-if="status"
        class="mb-4 text-center text-sm font-medium text-green-600"
    >
        {{ status }}
    </div>

    <Form
        v-bind="store.form()"
        :reset-on-success="['password']"
        v-slot="{ errors, processing }"
        class="flex flex-col gap-6"
    >
        <div class="grid gap-6">
            <div class="grid gap-2">
                <Label for="username">Username</Label>
                <Input
                    id="username"
                    type="text"
                    name="username"
                    required
                    autofocus
                    :tabindex="1"
                    autocomplete="username"
                    placeholder="Masukkan username"
                />
                <InputError :message="errors.username" />
            </div>

            <div class="grid gap-2">
                <div class="flex items-center justify-between">
                    <Label for="password">Password</Label>
                    <!-- <TextLink
                        v-if="canResetPassword"
                        :href="request()"
                        class="text-sm"
                        :tabindex="6"
                    >
                        Lupa Password?
                    </TextLink> -->
                </div>
                <PasswordInput
                    id="password"
                    name="password"
                    required
                    :tabindex="2"
                    autocomplete="current-password"
                    placeholder="Password Anda"
                />
                <InputError :message="errors.password" />
            </div>

            <!-- CAPTCHA LOKAL -->
            <div class="grid gap-2">
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2 bg-blue-50 border border-blue-200 text-blue-700 px-4 py-2 rounded-md font-bold tracking-widest text-lg w-32 justify-center">
                        {{ num1 }} + {{ num2 }}
                    </div>
                    <button type="button" @click="generateCaptcha" class="p-2.5 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-md transition outline-none" title="Refresh Captcha">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-refresh-cw"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                    </button>
                </div>
                <Label for="captcha" class="mt-2 text-xs text-muted-foreground">Isikan Captcha</Label>
                <Input
                    id="captcha"
                    type="number"
                    v-model="captchaInput"
                    required
                    :tabindex="3"
                    placeholder="Hasil perhitungan..."
                    :class="captchaError ? 'border-red-500 focus-visible:ring-red-500' : ''"
                />
                <InputError v-if="captchaError" message="Jawaban Captcha salah. Silakan coba lagi." />
            </div>

            <div class="flex items-center justify-between">
                <Label for="remember" class="flex items-center space-x-3">
                    <Checkbox id="remember" name="remember" :tabindex="4" />
                    <span>Ingat Saya</span>
                </Label>
            </div>

            <Button
                type="submit"
                class="mt-4 w-full bg-blue-600 hover:bg-blue-700 text-white"
                :tabindex="5"
                :disabled="processing"
                @click="validateCaptcha"
            >
                <Spinner v-if="processing" />
                Masuk Sistem
            </Button>
        </div>
        <!-- Register form removed per user request: "Hilangkan tampilan opsi register untuk sementara" -->
    </Form>
</template>
