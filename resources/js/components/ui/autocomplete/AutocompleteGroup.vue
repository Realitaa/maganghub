<script setup lang="ts">
import { AutocompleteGroup } from "reka-ui"
import { cn } from "@/lib/utils"

interface AutocompleteGroupProps {
  as?: any
  asChild?: boolean
}

const props = defineProps<AutocompleteGroupProps>()
</script>

<template>
  <AutocompleteGroup
    v-bind="props"
    data-slot="autocomplete-group"
    :class="cn('overflow-hidden p-1', $attrs.class as string)"
  >
    <slot />
  </AutocompleteGroup>
</template>
