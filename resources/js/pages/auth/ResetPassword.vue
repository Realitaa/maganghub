<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';
import { update } from '@/routes/password';

defineOptions({
    layout: {
        title: 'Atur Ulang Kata Sandi',
        description: 'Buat kata sandi baru untuk akun Anda.',
    },
});

const props = defineProps<{
    token: string;
    email: string;
    passwordRules: string;
}>();

const inputEmail = ref(props.email);
</script>

<template>
    <Head title="Atur Ulang Kata Sandi" />

    <!-- Form component -->
    <Form
        v-bind="update.form()"
        :transform="(data) => ({ ...data, token, email })"
        :reset-on-success="['password', 'password_confirmation']"
        v-slot="{ errors, processing }"
        class="space-y-5"
    >
        <!-- Email field (read-only) -->
        <div class="space-y-1.5">
            <Label
                for="email"
                class="text-xs font-semibold tracking-wide text-foreground"
                >Alamat Email</Label
            >
            <Input
                id="email"
                type="email"
                name="email"
                autocomplete="email"
                v-model="inputEmail"
                readonly
                class="h-10 cursor-not-allowed rounded-lg border-border/80 bg-muted/50 select-none focus-visible:ring-0"
            />
            <InputError :message="errors.email" />
        </div>

        <!-- Password field -->
        <div class="space-y-1.5">
            <Label
                for="password"
                class="text-xs font-semibold tracking-wide text-foreground"
                >Kata Sandi Baru</Label
            >
            <PasswordInput
                id="password"
                name="password"
                required
                autofocus
                autocomplete="new-password"
                placeholder="Kata Sandi Baru"
                :passwordrules="passwordRules"
                class="h-10 rounded-lg border-border/80 focus-visible:border-primary focus-visible:ring-primary/40"
            />
            <InputError :message="errors.password" />
        </div>

        <!-- Confirm Password field -->
        <div class="space-y-1.5">
            <Label
                for="password_confirmation"
                class="text-xs font-semibold tracking-wide text-foreground"
                >Konfirmasi Kata Sandi</Label
            >
            <PasswordInput
                id="password_confirmation"
                name="password_confirmation"
                required
                autocomplete="new-password"
                placeholder="Konfirmasi Kata Sandi"
                :passwordrules="passwordRules"
                class="h-10 rounded-lg border-border/80 focus-visible:border-primary focus-visible:ring-primary/40"
            />
            <InputError :message="errors.password_confirmation" />
        </div>

        <!-- Submit Button -->
        <Button
            type="submit"
            class="h-10.5 w-full rounded-lg bg-primary text-sm font-semibold text-primary-foreground shadow-sm transition-all duration-150 hover:bg-primary/95"
            :disabled="processing"
            data-test="reset-password-button"
        >
            <Spinner v-if="processing" class="mr-2 h-4 w-4 animate-spin" />
            Atur Ulang Kata Sandi
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
