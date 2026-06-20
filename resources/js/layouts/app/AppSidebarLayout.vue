<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { AlertCircle, AlertTriangle } from '@lucide/vue';
import { computed } from 'vue';
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Toaster } from '@/components/ui/sonner';
import { edit as editProfile } from '@/routes/profile';
import { edit as editSecurity } from '@/routes/security';
import type { BreadcrumbItem } from '@/types';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage();
const user = computed(() => page.props.auth?.user);
const requirements = computed(() => (page.props.auth as any)?.requirements);
</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar />
        <AppContent variant="sidebar" class="overflow-x-hidden">
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
            
            <div v-if="user && user.role === 'student' && requirements && (!requirements.password_changed || !requirements.profile_completed)" class="px-6 pt-2 space-y-3">
                <Alert v-if="!requirements.password_changed" variant="destructive" id="alert-password">
                    <AlertTriangle class="h-4 w-4" />
                    <div>
                        <AlertTitle>Ganti Password Default</AlertTitle>
                        <AlertDescription>
                            Anda masih menggunakan password default. Silakan 
                            <Link :href="editSecurity()" class="font-semibold underline">ganti password Anda</Link> 
                            terlebih dahulu.
                        </AlertDescription>
                    </div>
                </Alert>
                <Alert v-if="!requirements.profile_completed" class="border-yellow-200 dark:border-yellow-900 bg-yellow-50/50 dark:bg-yellow-950/20" id="alert-profile">
                    <AlertCircle class="h-4 w-4 text-yellow-600 dark:text-yellow-400" />
                    <div>
                        <AlertTitle class="text-yellow-800 dark:text-yellow-200">Biodata Mahasiswa Belum Lengkap</AlertTitle>
                        <AlertDescription class="text-yellow-700 dark:text-yellow-300">
                            Biodata Anda belum lengkap. Silakan 
                            <Link :href="editProfile()" class="font-semibold underline">lengkapi biodata Anda</Link> 
                            terlebih dahulu.
                        </AlertDescription>
                    </div>
                </Alert>
            </div>

            <slot />
        </AppContent>
        <Toaster />
    </AppShell>
</template>
