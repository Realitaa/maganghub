<script setup lang="ts">
import { MapPinned } from '@lucide/vue';

const props = defineProps<{
    query: string;
    text: string;
}>();

const getRedirectUrlWithParams = (searchQuery: string) => {
    const baseUrl = 'https://www.google.com/maps/search/?api=1&query=';
    const transformedQuery = encodeURIComponent(searchQuery);

    return `${baseUrl}${transformedQuery}`;
};
</script>

<template>
    <a
        target="_blank"
        :href="getRedirectUrlWithParams(props.query)"
        class="flex items-center gap-1 text-xs text-blue-500 hover:text-blue-600 hover:underline"
    >
        <MapPinned class="size-4" />
        {{ text }}
    </a>
</template>
