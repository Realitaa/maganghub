<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { ShieldAlert } from '@lucide/vue';
import AppLogo from '@/components/AppLogo.vue';
</script>

<template>
    <Head title="Akun Dinonaktifkan" />

    <div class="flex min-h-screen flex-col items-center justify-between p-6 sm:p-10 bg-background text-foreground antialiased">
        <div class="hidden lg:block h-10"></div>

        <div class="w-full max-w-md space-y-6 my-auto text-center flex flex-col items-center justify-center">
            <AppLogo />
            
            <div class="mt-8 flex h-16 w-16 items-center justify-center rounded-full bg-destructive/10 text-destructive">
                <ShieldAlert class="h-10 w-10" />
            </div>

            <div class="space-y-2">
                <h1 class="text-2xl font-bold tracking-tight mt-4">
                    Akun Anda Dinonaktifkan
                </h1>
                <p class="text-sm text-muted-foreground leading-normal max-w-sm mx-auto">
                    Silakan hubungi operator atau administrator untuk memulihkan akses Anda.
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

        <div class="w-full text-center py-6 border-t border-border/40 mt-8">
            <span class="text-[11px] text-muted-foreground tracking-wide font-medium uppercase">
                MagangHub &mdash; Platform Pengelolaan Magang Mahasiswa
            </span>
        </div>
    </div>
</template>
