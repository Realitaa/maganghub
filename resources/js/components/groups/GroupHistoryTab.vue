<script setup lang="ts">
import { Clock } from '@lucide/vue';

import type { Group } from '@/types';

defineProps<{
    group: Group;
}>();
</script>

<template>
    <div class="space-y-6">
        <div
            class="flex items-center gap-2 text-sm font-semibold text-muted-foreground"
        >
            <Clock class="h-4 w-4" />
            Riwayat Progres Kelompok
        </div>

        <div
            v-if="group?.timelines && group.timelines.length > 0"
            class="relative pl-6 after:absolute after:inset-y-0 after:left-2.5 after:w-0.5 after:bg-border/60"
        >
            <div
                v-for="timeline in group.timelines"
                :key="timeline.id"
                class="relative pb-6 last:pb-0"
            >
                <!-- Timeline icon/dot -->
                <div
                    class="absolute top-1 left-[-22px] flex h-5 w-5 items-center justify-center rounded-full border border-primary bg-background text-primary"
                >
                    <div class="h-2 w-2 rounded-full bg-primary" />
                </div>
                <div class="space-y-1">
                    <p
                        class="text-sm font-medium whitespace-pre-line text-foreground"
                    >
                        {{ timeline.title }}
                    </p>
                    <p class="text-xs text-muted-foreground">
                        {{
                            new Date(timeline.created_at).toLocaleString(
                                'id-ID',
                                {
                                    dateStyle: 'medium',
                                    timeStyle: 'short',
                                },
                            )
                        }}
                    </p>
                </div>
            </div>
        </div>

        <div
            v-else
            class="rounded-xl border border-dashed border-border/80 bg-muted/20 px-4 py-12 text-center"
        >
            <p class="text-sm font-medium">Belum ada riwayat aktivitas</p>
            <p class="mt-1 text-xs text-muted-foreground">
                Aktivitas kelompok magangmu akan tercatat di sini.
            </p>
        </div>
    </div>
</template>
