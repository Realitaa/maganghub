<script setup lang="ts">
import { cn } from '@/lib/utils';

defineProps<{
    class?: any;
}>();
</script>

<template>
    <div
        :class="
            cn(
                'flex h-9 w-9 items-center justify-center rounded-lg transition-transform duration-200 group-hover:scale-105',
                $props.class,
            )
        "
    >
        <img
            src="/assets/images/Lambang_Universitas_Negeri_Medan.png"
            alt="Logo Universitas Negeri Medan"
        />
    </div>
</template>
