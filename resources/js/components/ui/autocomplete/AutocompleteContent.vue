<script setup lang="ts">
import {
  AutocompleteContent,
  AutocompletePortal,
  useForwardPropsEmits,
} from "reka-ui"
import { cn } from "@/lib/utils"

defineOptions({
  inheritAttrs: false,
})

interface AutocompleteContentProps {
  align?: "start" | "center" | "end"
  alignFlip?: boolean
  alignOffset?: number
  arrowPadding?: number
  as?: any
  asChild?: boolean
  avoidCollisions?: boolean
  bodyLock?: boolean
  collisionBoundary?: any
  collisionPadding?: any
  dir?: "ltr" | "rtl"
  disableOutsidePointerEvents?: boolean
  disableUpdateOnLayoutShift?: boolean
  forceMount?: boolean
  hideShiftedArrow?: boolean
  hideWhenDetached?: boolean
  hideWhenEmpty?: boolean
  memoDependencies?: any[]
  position?: "inline" | "popper"
  positionStrategy?: "fixed" | "absolute"
  prioritizePosition?: boolean
  reference?: any
  side?: "top" | "right" | "bottom" | "left"
  sideFlip?: boolean
  sideOffset?: number
  sticky?: "partial" | "always"
  updatePositionStrategy?: "always" | "optimized"
}

const props = withDefaults(
  defineProps<AutocompleteContentProps>(),
  {
    position: "popper",
    sideOffset: 4,
  },
)

const emits = defineEmits<{
  (e: "escapeKeyDown", event: KeyboardEvent): void
  (e: "focusOutside", event: any): void
  (e: "interactOutside", event: any): void
  (e: "pointerDownOutside", event: any): void
}>()

const forwarded = useForwardPropsEmits(props, emits)
</script>

<template>
  <AutocompletePortal>
    <AutocompleteContent
      data-slot="autocomplete-content"
      v-bind="{ ...$attrs, ...forwarded }"
      :class="cn(
        'bg-popover text-popover-foreground data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95 data-[side=bottom]:slide-in-from-top-2 data-[side=left]:slide-in-from-right-2 data-[side=right]:slide-in-from-left-2 data-[side=top]:slide-in-from-bottom-2 relative z-50 overflow-hidden rounded-md border shadow-md',
        position === 'popper'
          && 'w-[var(--reka-autocomplete-trigger-width)] min-w-[var(--reka-autocomplete-trigger-width)] data-[side=bottom]:translate-y-1 data-[side=left]:-translate-x-1 data-[side=right]:translate-x-1 data-[side=top]:-translate-y-1',
        $attrs.class as string,
      )
      "
    >
      <slot />
    </AutocompleteContent>
  </AutocompletePortal>
</template>
