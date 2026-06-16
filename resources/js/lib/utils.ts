import type { ClassValue } from "clsx"
import { clsx } from "clsx"
import { twMerge } from "tailwind-merge"

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs))
}

export function toUrl(value: any): string {
    if (!value) {
        return '';
    }

    if (typeof value === 'string') {
        return value;
    }

    if (typeof value === 'function') {
        return toUrl(value());
    }

    if (typeof value === 'object') {
        if ('url' in value) {
            return toUrl(value.url);
        }

        if ('action' in value) {
            return toUrl(value.action);
        }
    }

    return String(value);
}
