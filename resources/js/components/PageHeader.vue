<script setup lang="ts">
defineProps<{
  title: string;
  description: string;
}>();
</script>

<template>
  <div class="flex flex-col md:flex-row items-start md:items-center justify-between space-y-2 md:space-y-0">
    <div>
      <h2 class="text-3xl font-bold tracking-tight text-foreground">{{ title }}</h2>
      <p class="text-muted-foreground text-sm">
        {{ description }}
      </p>
    </div>
    <div class="flex items-center space-x-2 w-full md:w-auto">
      <slot />
    </div>
  </div>
</template>