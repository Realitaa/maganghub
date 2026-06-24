<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ShieldAlert } from '@lucide/vue';
import AppLogo from '@/components/AppLogo.vue';
import { Button } from '@/components/ui/button';
</script>

<template>
    <Head title="Akun Dinonaktifkan" />

    <div
        class="flex min-h-screen flex-col items-center justify-between bg-background p-6 text-foreground antialiased sm:p-10"
    >
        <div class="hidden h-10 lg:block"></div>

        <div
            class="my-auto flex w-full max-w-md flex-col items-center justify-center space-y-6 text-center"
        >
            <AppLogo />

            <div
                class="mt-8 flex h-16 w-16 items-center justify-center rounded-full bg-destructive/10 text-destructive"
            >
                <ShieldAlert class="h-10 w-10" />
            </div>

            <div class="space-y-2">
                <h1 class="mt-4 text-2xl font-bold tracking-tight">
                    Akun Anda Dinonaktifkan
                </h1>
                <p
                    class="mx-auto max-w-sm text-sm leading-normal text-muted-foreground"
                >
                    Silakan hubungi operator atau administrator untuk memulihkan
                    akses Anda.
                </p>
            </div>

            <div class="w-full pt-4">
                <Link href="/logout" method="post" as="button" class="w-full">
                    <Button variant="outline" class="w-full">
                        Kembali ke Halaman Utama
                    </Button>
                </Link>
            </div>
        </div>

        <div class="mt-8 w-full border-t border-border/40 py-6 text-center">
            <span
                class="text-[11px] font-medium tracking-wide text-muted-foreground uppercase"
            >
                MagangHub &mdash; Platform Pengelolaan Magang Mahasiswa
            </span>
        </div>
    </div>
</template>
