<script setup lang="ts">
import { AutocompleteEmpty } from "reka-ui"
import { cn } from "@/lib/utils"

interface AutocompleteEmptyProps {
  as?: any
  asChild?: boolean
}

const props = defineProps<AutocompleteEmptyProps>()
</script>

<template>
  <AutocompleteEmpty
    v-bind="props"
    data-slot="autocomplete-empty"
    :class="cn('py-6 text-center text-sm text-muted-foreground', $attrs.class as string)"
  >
    <slot />
  </AutocompleteEmpty>
</template>
