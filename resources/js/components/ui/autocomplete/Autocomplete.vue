<script setup lang="ts">
import { AutocompleteRoot, useForwardPropsEmits } from "reka-ui"

interface AutocompleteRootProps {
  as?: any
  asChild?: boolean
  defaultOpen?: boolean
  defaultValue?: string
  dir?: "ltr" | "rtl"
  disabled?: boolean
  highlightOnHover?: boolean
  ignoreFilter?: boolean
  modelValue?: string
  name?: string
  open?: boolean
  openOnClick?: boolean
  openOnFocus?: boolean
  required?: boolean
  resetSearchTermOnBlur?: boolean
}

const props = defineProps<AutocompleteRootProps>()

const emits = defineEmits<{
  (e: "highlight", payload: { ref: HTMLElement; value: string }): void
  (e: "update:modelValue", value: string): void
  (e: "update:open", value: boolean): void
}>()

const forwarded = useForwardPropsEmits(props, emits)
</script>

<template>
  <AutocompleteRoot
    v-slot="slotProps"
    data-slot="autocomplete"
    v-bind="forwarded"
  >
    <slot v-bind="slotProps" />
  </AutocompleteRoot>
</template>
