<script setup lang="ts">
import { AutocompleteAnchor } from "reka-ui"
import { cn } from "@/lib/utils"

interface AutocompleteAnchorProps {
  as?: any
  asChild?: boolean
  reference?: any
}

const props = defineProps<AutocompleteAnchorProps>()
</script>

<template>
  <AutocompleteAnchor
    v-bind="props"
    data-slot="autocomplete-anchor"
    :class="cn('relative flex items-center', $attrs.class as string)"
  >
    <slot />
  </AutocompleteAnchor>
</template>
