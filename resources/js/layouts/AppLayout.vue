<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { AlertCircle, AlertTriangle } from '@lucide/vue';
import { computed } from 'vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { ScrollArea } from '@/components/ui/scroll-area';
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import { edit as editProfile } from '@/routes/profile';
import { edit as editSecurity } from '@/routes/security';
import type { BreadcrumbItem } from '@/types';

const { breadcrumbs = [] } = defineProps<{
    breadcrumbs?: BreadcrumbItem[];
}>();

const page = usePage();
const user = computed(() => page.props.auth?.user);
const requirements = computed(() => (page.props.auth as any)?.requirements);
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <ScrollArea class="h-[calc(100vh-5rem)]">
            <div
                v-if="
                    user &&
                    user.role === 'student' &&
                    requirements &&
                    (!requirements.password_changed ||
                        !requirements.profile_completed)
                "
                class="space-y-3 px-6 pt-2"
            >
                <Alert
                    v-if="!requirements.password_changed"
                    variant="destructive"
                    id="alert-password"
                >
                    <AlertTriangle class="h-4 w-4" />
                    <div>
                        <AlertTitle>Ganti Password Default</AlertTitle>
                        <AlertDescription>
                            Anda masih menggunakan password default. Silakan
                            <Link
                                :href="editSecurity()"
                                class="font-semibold underline"
                                >ganti password Anda</Link
                            >
                            terlebih dahulu.
                        </AlertDescription>
                    </div>
                </Alert>
                <Alert
                    v-if="!requirements.profile_completed"
                    class="border-yellow-200 bg-yellow-50/50 dark:border-yellow-900 dark:bg-yellow-950/20"
                    id="alert-profile"
                >
                    <AlertCircle
                        class="h-4 w-4 text-yellow-600 dark:text-yellow-400"
                    />
                    <div>
                        <AlertTitle class="text-yellow-800 dark:text-yellow-200"
                            >Biodata Mahasiswa Belum Lengkap</AlertTitle
                        >
                        <AlertDescription
                            class="text-yellow-700 dark:text-yellow-300"
                        >
                            Biodata Anda belum lengkap. Silakan
                            <Link
                                :href="editProfile()"
                                class="font-semibold underline"
                                >lengkapi biodata Anda</Link
                            >
                            terlebih dahulu.
                        </AlertDescription>
                    </div>
                </Alert>
            </div>
            <slot />
        </ScrollArea>
    </AppLayout>
</template>
