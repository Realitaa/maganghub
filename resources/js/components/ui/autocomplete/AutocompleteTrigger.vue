<script setup lang="ts">
import { AutocompleteTrigger } from "reka-ui"
import { cn } from "@/lib/utils"

interface AutocompleteTriggerProps {
  as?: any
  asChild?: boolean
  disabled?: boolean
}

const props = defineProps<AutocompleteTriggerProps>()
</script>

<template>
  <AutocompleteTrigger
    v-bind="props"
    data-slot="autocomplete-trigger"
    :class="cn(
      'text-muted-foreground/80 hover:text-foreground absolute right-3 flex h-4 w-4 items-center justify-center cursor-pointer disabled:pointer-events-none disabled:opacity-50',
      $attrs.class as string
    )"
  >
    <slot />
  </AutocompleteTrigger>
</template>
