<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { FileText, Users, Activity, NotebookPen } from '@lucide/vue';
import AppLogo from '@/components/AppLogo.vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { store } from '@/routes/login';

defineOptions({
    layout: {
        title: 'Masuk ke Akun Anda',
        description:
            'Masukkan email atau NIM dan kata sandi Anda di bawah ini untuk masuk',
    },
});

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();
</script>

<template>
    <Head title="Masuk ke Sistem" />

    <div
        class="grid min-h-screen grid-cols-1 bg-background text-foreground antialiased selection:bg-primary/20 selection:text-primary lg:grid-cols-2"
    >
        <!-- Left Side: Dark green / Emerald branding and illustration (Hidden on Mobile) -->
        <div
            class="relative hidden flex-col justify-between overflow-hidden bg-primary p-12 text-primary-foreground lg:flex dark:border-r dark:border-border/30 dark:bg-emerald-950/40"
        >
            <!-- Subtle background pattern overlay -->
            <div class="pointer-events-none absolute inset-0 opacity-10">
                <svg viewBox="0 0 100 100" class="h-full w-full fill-current">
                    <circle cx="10" cy="10" r="30" />
                    <circle cx="90" cy="90" r="40" />
                    <line
                        x1="0"
                        y1="0"
                        x2="100"
                        y2="100"
                        stroke="currentColor"
                        stroke-width="2"
                    />
                    <line
                        x1="100"
                        y1="0"
                        x2="0"
                        y2="100"
                        stroke="currentColor"
                        stroke-width="2"
                    />
                </svg>
            </div>

            <!-- Middle Illustration -->
            <div class="relative z-10 my-auto max-w-md">
                <!-- High-fidelity academic/internship themed vector illustration -->
                <svg
                    viewBox="0 0 400 300"
                    class="mb-8 h-auto w-full drop-shadow-lg select-none"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <!-- Base glow -->
                    <circle
                        cx="200"
                        cy="150"
                        r="100"
                        fill="white"
                        fill-opacity="0.05"
                        filter="blur(20px)"
                    />

                    <!-- Ground/Desk line -->
                    <line
                        x1="50"
                        y1="230"
                        x2="350"
                        y2="230"
                        stroke="white"
                        stroke-opacity="0.2"
                        stroke-width="2"
                        stroke-linecap="round"
                    />

                    <!-- Campus/University shape in background -->
                    <path
                        d="M70 230 V160 L100 130 L130 160 V230 Z"
                        fill="white"
                        fill-opacity="0.08"
                        stroke="white"
                        stroke-opacity="0.15"
                        stroke-width="1.5"
                    />
                    <path
                        d="M130 230 V140 L160 110 L190 140 V230 Z"
                        fill="white"
                        fill-opacity="0.12"
                        stroke="white"
                        stroke-opacity="0.25"
                        stroke-width="1.5"
                    />
                    <rect
                        x="152"
                        y="170"
                        width="16"
                        height="25"
                        rx="2"
                        fill="white"
                        fill-opacity="0.2"
                    />
                    <circle
                        cx="160"
                        cy="135"
                        r="5"
                        fill="white"
                        fill-opacity="0.3"
                    />

                    <!-- Floating Document representing proposal -->
                    <rect
                        x="230"
                        y="80"
                        width="70"
                        height="90"
                        rx="6"
                        fill="white"
                        fill-opacity="0.1"
                        stroke="white"
                        stroke-opacity="0.3"
                        stroke-width="2"
                        transform="rotate(5 265 125)"
                    />
                    <line
                        x1="245"
                        y1="105"
                        x2="285"
                        y2="105"
                        stroke="white"
                        stroke-opacity="0.4"
                        stroke-width="2"
                        stroke-linecap="round"
                        transform="rotate(5 265 125)"
                    />
                    <line
                        x1="245"
                        y1="120"
                        x2="275"
                        y2="120"
                        stroke="white"
                        stroke-opacity="0.4"
                        stroke-width="2"
                        stroke-linecap="round"
                        transform="rotate(5 265 125)"
                    />
                    <line
                        x1="245"
                        y1="135"
                        x2="280"
                        y2="135"
                        stroke="white"
                        stroke-opacity="0.4"
                        stroke-width="2"
                        stroke-linecap="round"
                        transform="rotate(5 265 125)"
                    />
                    <circle
                        cx="280"
                        cy="150"
                        r="8"
                        fill="var(--color-emerald-400, #34d399)"
                        fill-opacity="0.9"
                        transform="rotate(5 265 125)"
                    />
                    <path
                        d="M277 150 L279 152 L283 148"
                        stroke="white"
                        stroke-width="1.5"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        transform="rotate(5 265 125)"
                    />

                    <!-- Floating Cog/Gears representing system -->
                    <circle
                        cx="290"
                        cy="200"
                        r="24"
                        stroke="white"
                        stroke-opacity="0.2"
                        stroke-width="2"
                        stroke-dasharray="6 4"
                    />
                    <circle
                        cx="290"
                        cy="200"
                        r="14"
                        fill="white"
                        fill-opacity="0.05"
                        stroke="white"
                        stroke-opacity="0.15"
                        stroke-width="2"
                    />

                    <!-- Connected nodes representing group/collaboration -->
                    <circle
                        cx="90"
                        cy="90"
                        r="6"
                        fill="white"
                        fill-opacity="0.4"
                    />
                    <circle
                        cx="120"
                        cy="70"
                        r="8"
                        fill="white"
                        fill-opacity="0.6"
                    />
                    <circle
                        cx="160"
                        cy="80"
                        r="5"
                        fill="white"
                        fill-opacity="0.4"
                    />
                    <line
                        x1="96"
                        y1="88"
                        x2="114"
                        y2="74"
                        stroke="white"
                        stroke-opacity="0.3"
                        stroke-width="1.5"
                    />
                    <line
                        x1="128"
                        y1="72"
                        x2="155"
                        y2="78"
                        stroke="white"
                        stroke-opacity="0.3"
                        stroke-width="1.5"
                    />
                </svg>

                <h2 class="text-2xl font-bold tracking-tight text-white">
                    Kelola Proses Magang Anda Secara Digital
                </h2>
                <p
                    class="mt-3 text-sm leading-relaxed text-primary-foreground/85"
                >
                    Dari pembentukan kelompok hingga verifikasi proposal dan
                    pengisian logbook harian, semua terintegrasi secara cepat,
                    teratur, dan transparan dalam satu sistem akademik kampus.
                </p>
            </div>

            <!-- Bottom Features Highlight Grid -->
            <div
                class="relative z-10 grid grid-cols-2 gap-4 border-t border-white/10 pt-8"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/10 text-white"
                    >
                        <FileText class="h-4.5 w-4.5" />
                    </div>
                    <span class="text-xs font-semibold text-white"
                        >Pengajuan Magang</span
                    >
                </div>
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/10 text-white"
                    >
                        <Users class="h-4.5 w-4.5" />
                    </div>
                    <span class="text-xs font-semibold text-white"
                        >Kelola Kelompok</span
                    >
                </div>
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/10 text-white"
                    >
                        <Activity class="h-4.5 w-4.5" />
                    </div>
                    <span class="text-xs font-semibold text-white"
                        >Monitoring Status</span
                    >
                </div>
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/10 text-white"
                    >
                        <NotebookPen class="h-4.5 w-4.5" />
                    </div>
                    <span class="text-xs font-semibold text-white"
                        >Logbook Digital</span
                    >
                </div>
            </div>
        </div>

        <!-- Right Side: Login Form Card -->
        <div
            class="flex min-h-screen flex-col items-center justify-between bg-background p-6 sm:p-10 lg:p-12"
        >
            <!-- Spacer for vertical center alignment -->
            <div class="hidden h-6 lg:block"></div>

            <!-- Login Container Card -->
            <div class="my-auto w-full max-w-sm space-y-6">
                <div
                    class="flex flex-col items-center justify-center space-y-2"
                >
                    <!-- Desktop Brand Indicator -->
                    <AppLogo />
                    <h1
                        class="mt-4 text-2xl font-bold tracking-tight text-foreground"
                    >
                        Selamat Datang Kembali
                    </h1>
                    <p
                        class="text-center text-sm leading-normal text-muted-foreground"
                    >
                        Silakan masuk dengan email atau NIM Anda.
                    </p>
                </div>

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
            </div>

            <!-- Footer Text -->
            <div class="mt-8 w-full border-t border-border/40 py-6 text-center">
                <span
                    class="text-[11px] font-medium tracking-wide text-muted-foreground uppercase"
                >
                    MagangHub &mdash; Platform Pengelolaan Magang Mahasiswa
                </span>
            </div>
        </div>
    </div>
</template>
