<script setup lang="ts">
import { AutocompleteItem, useForwardPropsEmits } from "reka-ui"
import { cn } from "@/lib/utils"

interface AutocompleteItemProps {
  as?: any
  asChild?: boolean
  disabled?: boolean
  textValue?: string
  value: any
}

const props = defineProps<AutocompleteItemProps>()
const emits = defineEmits<{
  (e: "select", event: any): void
}>()

const forwarded = useForwardPropsEmits(props, emits)
</script>

<template>
  <AutocompleteItem
    v-bind="forwarded"
    data-slot="autocomplete-item"
    :class="cn(
      'data-highlighted:bg-accent data-highlighted:text-accent-foreground data-[highlighted]:bg-accent data-[highlighted]:text-accent-foreground data-[state=checked]:bg-accent data-[state=checked]:text-accent-foreground flex w-full cursor-default items-center gap-2 rounded-sm px-2 py-1.5 text-sm outline-hidden select-none data-[disabled]:pointer-events-none data-[disabled]:opacity-50 [&_svg]:pointer-events-none [&_svg]:shrink-0 [&_svg:not([class*=\'size-\'])]:size-4',
      $attrs.class as string
    )"
  >
    <slot />
  </AutocompleteItem>
</template>
