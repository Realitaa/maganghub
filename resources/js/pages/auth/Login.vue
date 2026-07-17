<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { store } from '@/routes/login';
import { request as passwordRequest } from '@/routes/password';

defineOptions({
    layout: {
        title: 'Selamat Datang Kembali',
        description: 'Silakan masuk dengan email atau NIM Anda.',
    },
});

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const currentIdentity = ref('');
</script>

<template>
    <Head title="Masuk ke Sistem" />

    <!-- Session Status / Alert Message -->
    <div
        v-if="status"
        class="rounded-lg border border-emerald-500/20 bg-emerald-500/10 p-3 text-center text-sm font-medium text-emerald-600 dark:text-emerald-400"
    >
        {{ status }}
    </div>

    <!-- Form component -->
    <Form
        v-bind="store.form()"
        :reset-on-success="['password']"
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
                :tabindex="1"
                autocomplete="username"
                placeholder="Email atau NIM"
                class="h-10 rounded-lg border-border/80 focus-visible:border-primary focus-visible:ring-primary/40"
            />
            <InputError :message="errors.email" />
        </div>

        <!-- Password field -->
        <div class="space-y-1.5">
            <div class="flex items-center justify-between">
                <Label
                    for="password"
                    class="text-xs font-semibold tracking-wide text-foreground"
                    >Kata Sandi</Label
                >
                <Link
                    v-if="canResetPassword"
                    :href="passwordRequest({ query: { identity: currentIdentity } }).url"
                    class="text-xs font-medium text-primary hover:underline"
                >
                    Lupa kata sandi?
                </Link>
            </div>
            <PasswordInput
                id="password"
                name="password"
                required
                :tabindex="2"
                autocomplete="current-password"
                placeholder="Kata Sandi"
                class="h-10 rounded-lg border-border/80 focus-visible:border-primary focus-visible:ring-primary/40"
            />
            <InputError :message="errors.password" />
        </div>

        <!-- Remember me checkbox -->
        <div class="flex items-center justify-between">
            <Label
                for="remember"
                class="flex cursor-pointer items-center space-x-2.5 text-xs select-none"
            >
                <Checkbox
                    id="remember"
                    name="remember"
                    :tabindex="3"
                    class="rounded-md border-border/80 focus:ring-primary"
                />
                <span class="font-medium text-muted-foreground"
                    >Ingat saya di perangkat ini</span
                >
            </Label>
        </div>

        <!-- Submit Button -->
        <Button
            type="submit"
            class="h-10.5 w-full rounded-lg bg-primary text-sm font-semibold text-primary-foreground shadow-sm transition-all duration-150 hover:bg-primary/95"
            :tabindex="4"
            :disabled="processing"
            data-test="login-button"
        >
            <Spinner
                v-if="processing"
                class="mr-2 h-4 w-4 animate-spin"
            />
            Masuk
        </Button>
    </Form>
</template>
