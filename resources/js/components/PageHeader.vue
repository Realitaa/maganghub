<script setup lang="ts">
defineProps<{
    title: string;
    description: string;
}>();
</script>

<template>
    <div
        class="flex flex-col items-start justify-between space-y-2 md:flex-row md:items-center md:space-y-0"
    >
        <div>
            <h2 class="text-3xl font-bold tracking-tight text-foreground">
                {{ title }}
            </h2>
            <p class="text-sm text-muted-foreground">
                {{ description }}
            </p>
        </div>
        <div class="flex w-full items-center space-x-2 md:w-auto">
            <slot />
        </div>
    </div>
</template>
