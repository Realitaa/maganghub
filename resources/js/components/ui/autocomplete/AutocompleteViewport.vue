<script setup lang="ts">
import { AutocompleteViewport } from "reka-ui"
import { cn } from "@/lib/utils"

interface AutocompleteViewportProps {
  as?: any
  asChild?: boolean
  nonce?: string
}

const props = defineProps<AutocompleteViewportProps>()
</script>

<template>
  <AutocompleteViewport
    v-bind="props"
    data-slot="autocomplete-viewport"
    :class="cn('p-1 max-h-[300px] overflow-y-auto overflow-x-hidden', $attrs.class as string)"
  >
    <slot />
  </AutocompleteViewport>
</template>
