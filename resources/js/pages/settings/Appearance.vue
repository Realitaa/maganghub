<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppearanceTabs from '@/components/AppearanceTabs.vue';
import Heading from '@/components/Heading.vue';
import { edit } from '@/routes/appearance';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Pengaturan Tampilan',
                href: edit(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Pengaturan Tampilan" />

    <h1 class="sr-only">Pengaturan Tampilan</h1>

    <div class="space-y-6">
        <Heading
            variant="small"
            title="Pengaturan Tampilan"
            description="Ubah pengaturan tampilan akun Anda"
        />
        <AppearanceTabs />
    </div>
</template>
