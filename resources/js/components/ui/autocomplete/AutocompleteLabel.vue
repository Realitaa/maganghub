<script setup lang="ts">
import { AutocompleteLabel } from "reka-ui"
import { cn } from "@/lib/utils"

interface AutocompleteLabelProps {
  as?: any
  asChild?: boolean
  for?: string
}

const props = defineProps<AutocompleteLabelProps>()
</script>

<template>
  <AutocompleteLabel
    v-bind="props"
    data-slot="autocomplete-label"
    :class="cn('px-2 py-1.5 text-xs font-semibold text-muted-foreground', $attrs.class as string)"
  >
    <slot />
  </AutocompleteLabel>
</template>
