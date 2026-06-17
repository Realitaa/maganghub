<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { welcome } from '@/routes';

defineProps<{
    keepLight?: boolean;
}>() 
</script>

<template>
    <Link :href="welcome()" :class="['flex items-center gap-2.5 group', keepLight ? 'text-white' : '']">
        <AppLogoIcon />
        <div class="flex flex-col">
            <span class="text-xl font-bold tracking-tight" :class="[keepLight ? 'text-white' : 'text-foreground']">
                Magang<span class="text-primary font-extrabold">Hub</span>
            </span>
            <span class="text-xs font-medium" :class="[keepLight ? 'text-white/60' : 'text-muted-foreground']">Ilmu Komputer</span>
        </div>
    </Link>
</template>
