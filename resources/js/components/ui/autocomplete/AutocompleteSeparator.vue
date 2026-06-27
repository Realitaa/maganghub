<script setup lang="ts">
import { AutocompleteSeparator } from "reka-ui"
import { cn } from "@/lib/utils"

interface AutocompleteSeparatorProps {
  as?: any
  asChild?: boolean
}

const props = defineProps<AutocompleteSeparatorProps>()
</script>

<template>
  <AutocompleteSeparator
    v-bind="props"
    data-slot="autocomplete-separator"
    :class="cn('-mx-1 my-1 h-px bg-border', $attrs.class as string)"
  >
    <slot />
  </AutocompleteSeparator>
</template>
