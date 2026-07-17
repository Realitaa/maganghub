<script setup lang="ts">
import { Form, Head, usePage, Link } from '@inertiajs/vue3';
import { AlertTriangle } from '@lucide/vue';
import { computed } from 'vue';
import SecurityController from '@/actions/App/Http/Controllers/Settings/SecurityController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Alert, AlertTitle, AlertDescription } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { edit as editProfile } from '@/routes/profile';
import { edit } from '@/routes/security';

type Props = {
    passwordRules: string;
};

const props = defineProps<Props>();

const page = usePage();
const user = computed(() => page.props.auth.user);
const isEmailEmpty = computed(() => !user.value?.email);

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Keamanan',
                href: edit(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Keamanan" />

    <h1 class="sr-only">Keamanan</h1>

    <div class="space-y-6">
        <Heading
            variant="small"
            title="Perbarui Kata Sandi"
            description="Pastikan akun Anda menggunakan kata sandi yang panjang dan acak agar tetap aman"
        />

        <Alert v-if="isEmailEmpty" variant="warning" class="mb-6">
            <AlertTriangle class="h-4 w-4" />
            <div>
                <AlertTitle>Email Masih Kosong</AlertTitle>
                <AlertDescription>
                    Anda harus mengisi alamat email terlebih dahulu di halaman
                    <Link :href="editProfile().url" class="font-medium underline hover:text-primary">Pengaturan Profil</Link>
                    sebelum dapat memperbarui kata sandi Anda.
                </AlertDescription>
            </div>
        </Alert>

        <Form
            v-bind="SecurityController.update.form()"
            :options="{
                preserveScroll: true,
            }"
            reset-on-success
            :reset-on-error="[
                'password',
                'password_confirmation',
                'current_password',
            ]"
            class="space-y-6"
            v-slot="{ errors, processing }"
        >
            <div class="grid gap-2">
                <Label for="current_password">Kata Sandi Saat Ini</Label>
                <PasswordInput
                    id="current_password"
                    name="current_password"
                    class="mt-1 block w-full"
                    autocomplete="current-password"
                    placeholder="Kata Sandi Saat Ini"
                    :disabled="isEmailEmpty"
                />
                <InputError :message="errors.current_password" />
            </div>

            <div class="grid gap-2">
                <Label for="password">Kata Sandi Baru</Label>
                <PasswordInput
                    id="password"
                    name="password"
                    class="mt-1 block w-full"
                    autocomplete="new-password"
                    placeholder="Kata Sandi Baru"
                    :passwordrules="props.passwordRules"
                    :disabled="isEmailEmpty"
                />
                <InputError :message="errors.password" />
            </div>

            <div class="grid gap-2">
                <Label for="password_confirmation"
                    >Konfirmasi Kata Sandi Baru</Label
                >
                <PasswordInput
                    id="password_confirmation"
                    name="password_confirmation"
                    class="mt-1 block w-full"
                    autocomplete="new-password"
                    placeholder="Konfirmasi Kata Sandi Baru"
                    :passwordrules="props.passwordRules"
                    :disabled="isEmailEmpty"
                />
                <InputError :message="errors.password_confirmation" />
            </div>

            <div class="flex items-center gap-4">
                <Button
                    :disabled="isEmailEmpty || processing"
                    data-test="update-password-button"
                >
                    Simpan
                </Button>
            </div>
        </Form>
    </div>
</template>
