<script setup lang="ts">
import { Check, Copy, Link2 } from '@lucide/vue';
import { useClipboard } from '@vueuse/core';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
} from '@/components/ui/dialog';

import type { Group } from '@/types';

// ─── Props ────────────────────────────────────────────────────────────────────

const props = defineProps<{
    open: boolean;
    group: Group;
}>();

defineEmits<{
    (e: 'update:open', value: boolean): void;
}>();

// ─── Invite URL ───────────────────────────────────────────────────────────────

/**
 * Use the server-generated invite_url (/join/{code}) which has proper OG tags.
 * Falls back to constructing it from the current origin if not available.
 */
const inviteUrl = computed(
    () =>
        props.group.invite_url ??
        `${window.location.origin}/join/${props.group.code}`,
);

// ─── OG preview thumbnail ─────────────────────────────────────────────────────

/**
 * Show the OG image in the mockup preview so the user sees exactly what
 * WhatsApp will render. Falls back to the banner, then a placeholder.
 */
const previewImageUrl = computed(
    () => props.group.og_image_url ?? props.group.banner_url ?? null,
);

// ─── Clipboard ───────────────────────────────────────────────────────────────

const { copy: copyCode, copied: codeCopied } = useClipboard({
    source: computed(() => props.group.code),
});

const { copy: copyLink, copied: linkCopied } = useClipboard({
    source: inviteUrl,
});
</script>

<template>
    <Dialog :open="open" @update:open="$emit('update:open', $event)">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Bagikan Kelompok</DialogTitle>
                <DialogDescription>
                    Bagikan kode atau link undangan kepada calon anggota
                    kelompok. Saat dibagikan di WhatsApp, preview gambar akan
                    tampil otomatis.
                </DialogDescription>
            </DialogHeader>

            <!-- WhatsApp OG Preview Mockup -->
            <div
                class="overflow-hidden rounded-xl border border-border/60 shadow-sm"
            >
                <!-- OG image — 1200:630 ≈ 1.9:1 ratio, shown as a thumbnail -->
                <div
                    class="relative w-full overflow-hidden bg-muted"
                    style="aspect-ratio: 1200/630"
                >
                    <img
                        v-if="previewImageUrl"
                        :src="previewImageUrl"
                        alt="Preview OG"
                        class="h-full w-full object-cover"
                        loading="lazy"
                    />
                    <div
                        v-else
                        class="flex h-full w-full items-center justify-center"
                    >
                        <div
                            class="flex flex-col items-center gap-2 text-muted-foreground/30"
                        >
                            <svg
                                class="h-10 w-10"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.5"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 18h16.5M12 3v2.25m0 0V3m0 2.25H9.75m2.25 0H14.25"
                                />
                            </svg>
                            <span class="text-xs">Belum ada banner</span>
                        </div>
                    </div>
                    <!-- Gradient overlay like WhatsApp does -->
                    <div
                        class="absolute inset-0 bg-linear-to-t from-black/60 to-transparent"
                    />
                </div>

                <!-- OG text block (mimics WhatsApp link preview) -->
                <div class="bg-muted/60 p-3">
                    <p
                        class="truncate text-[10px] tracking-wider text-muted-foreground uppercase"
                    >
                        {{ inviteUrl }}
                    </p>
                    <p
                        class="mt-0.5 line-clamp-2 text-sm leading-snug font-semibold"
                    >
                        Bergabung ke Kelompok Magang {{ group.leader.name }}
                    </p>
                    <p class="mt-0.5 truncate text-xs text-muted-foreground">
                        Gunakan kode
                        <span class="font-mono font-semibold">{{
                            group.code
                        }}</span>
                        untuk bergabung.
                    </p>
                </div>
            </div>

            <!-- Group Code Display -->
            <div class="flex flex-col items-center gap-2 py-2">
                <p
                    class="text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                >
                    Kode Kelompok
                </p>
                <p
                    class="font-mono text-4xl font-bold tracking-[0.25em] text-primary"
                >
                    {{ group.code }}
                </p>
            </div>

            <!-- Copy Buttons -->
            <div class="flex flex-col gap-2">
                <Button
                    id="btn-share-copy-code"
                    class="w-full gap-2"
                    @click="copyCode()"
                >
                    <component
                        :is="codeCopied ? Check : Copy"
                        class="h-4 w-4"
                    />
                    {{ codeCopied ? 'Kode Tersalin!' : 'Salin Kode' }}
                </Button>
                <Button
                    id="btn-share-copy-link"
                    variant="outline"
                    class="w-full gap-2"
                    @click="copyLink()"
                >
                    <component
                        :is="linkCopied ? Check : Link2"
                        class="h-4 w-4"
                    />
                    {{ linkCopied ? 'Link Tersalin!' : 'Salin Link Undangan' }}
                </Button>
            </div>

            <!-- Info note -->
            <p class="text-center text-[11px] text-muted-foreground/60">
                Link undangan: <span class="font-mono">{{ inviteUrl }}</span>
            </p>
        </DialogContent>
    </Dialog>
</template>
