<script setup lang="ts">
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import { useStorage } from '@vueuse/core';
import { ref, onMounted, onBeforeUnmount } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';
import { email } from '@/routes/password';

defineOptions({
    layout: {
        title: 'Lupa Kata Sandi?',
        description: 'Masukkan Email atau NIM Anda untuk menerima tautan atur ulang kata sandi. Cek Spam folder jika tidak ada email yang masuk.',
    },
});

defineProps<{
    status?: string;
}>();

const page = usePage();
const urlParams = new URLSearchParams(page.url.split('?')[1] || '');
const currentIdentity = ref(urlParams.get('identity') || '');

const resetCooldownUntil = useStorage('password_reset_cooldown', 0);
const countdown = ref(0);
let timer: ReturnType<typeof setInterval> | null = null;

const updateCountdown = () => {
    const remaining = Math.ceil((resetCooldownUntil.value - Date.now()) / 1000);

    if (remaining > 0) {
        countdown.value = remaining;
    } else {
        countdown.value = 0;
        
        if (timer) {
            clearInterval(timer);
            timer = null;
        }
    }
};

const startCountdown = () => {
    resetCooldownUntil.value = Date.now() + 60000;
    updateCountdown();

    if (timer) {
        clearInterval(timer);
    }

    timer = setInterval(updateCountdown, 1000);
};

onMounted(() => {
    if (resetCooldownUntil.value > Date.now()) {
        updateCountdown();
        timer = setInterval(updateCountdown, 1000);
    }
});

onBeforeUnmount(() => {
    if (timer) {
        clearInterval(timer);
    }
});
</script>

<template>
    <Head title="Lupa Kata Sandi" />

    <!-- Session Status / Alert Message -->
    <div
        v-if="status"
        class="rounded-lg border border-emerald-500/20 bg-emerald-500/10 p-3 text-center text-sm font-medium text-emerald-600 dark:text-emerald-400"
    >
        {{ status }}
    </div>

    <!-- Form component -->
    <Form
        v-bind="email.form()"
        @success="startCountdown"
        v-slot="{ errors, processing }"
        class="space-y-5"
    >
        <!-- Email or NIM field -->
        <div class="space-y-1.5">
            <Label
                for="email"
                class="text-xs font-semibold tracking-wide text-foreground"
                >Email atau NIM</Label
            >
            <Input
                id="email"
                type="text"
                name="email"
                v-model="currentIdentity"
                required
                autofocus
                placeholder="Masukkan Email atau NIM"
                class="h-10 rounded-lg border-border/80 focus-visible:border-primary focus-visible:ring-primary/40"
            />
            <InputError :message="errors.email" />
        </div>

        <!-- Submit Button -->
        <Button
            type="submit"
            class="h-10.5 w-full rounded-lg bg-primary text-sm font-semibold text-primary-foreground shadow-sm transition-all duration-150 hover:bg-primary/95"
            :disabled="processing || countdown > 0"
            data-test="email-password-reset-link-button"
        >
            <Spinner
                v-if="processing"
                class="mr-2 h-4 w-4 animate-spin"
            />
            <span v-if="countdown > 0">Tunggu {{ countdown }} detik</span>
            <span v-else>Kirim Tautan Atur Ulang</span>
        </Button>
    </Form>

    <div class="flex items-center justify-center pt-2">
        <Link
            :href="login().url"
            class="inline-flex items-center gap-1.5 text-sm font-medium text-primary hover:underline"
        >
            <ArrowLeft class="h-4 w-4" />
            Kembali ke Halaman Masuk
        </Link>
    </div>
</template>
