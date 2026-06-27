<script setup lang="ts">
import { AutocompleteCancel } from "reka-ui"
import { cn } from "@/lib/utils"

interface AutocompleteCancelProps {
  as?: any
  asChild?: boolean
}

const props = defineProps<AutocompleteCancelProps>()
</script>

<template>
  <AutocompleteCancel
    v-bind="props"
    data-slot="autocomplete-cancel"
    :class="cn(
      'text-muted-foreground/80 hover:text-foreground absolute right-8 flex h-4 w-4 items-center justify-center cursor-pointer disabled:pointer-events-none disabled:opacity-50',
      $attrs.class as string
    )"
  >
    <slot />
  </AutocompleteCancel>
</template>
