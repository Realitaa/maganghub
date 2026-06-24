import { h } from 'vue';
import type { VNode } from 'vue';
import { cn } from '@/lib/utils';

export function useLogoWrapper() {
    function svgLogoWrapper(node: any, className?: string): () => VNode {
        return () =>
            h(
                'div',
                {
                    class: cn(
                        'flex h-7 items-center gap-3 transition-all duration-200 select-none [&>svg]:h-7 [&>svg]:w-auto',
                        'opacity-60 grayscale dark:opacity-50 dark:brightness-0 dark:invert',
                        'hover:opacity-100 hover:grayscale-0 hover:dark:opacity-100 hover:dark:brightness-100 hover:dark:invert-0',
                        className,
                    ),
                },
                [h(node)],
            );
    }

    function idnfinlogosWrapper(node: any): string {
        return `<div class="flex items-center gap-3 h-7 select-none transition-all duration-200 grayscale opacity-60 dark:opacity-50 dark:brightness-0 dark:invert hover:grayscale-0 hover:opacity-100 hover:dark:opacity-100 hover:dark:brightness-100 hover:dark:invert-0">
                    <div class="h-7 w-auto flex items-center justify-center [&>svg]:h-7 [&>svg]:w-auto">${node}</div>
                </div>`;
    }

    return {
        svgLogoWrapper,
        idnfinlogosWrapper,
    };
}
