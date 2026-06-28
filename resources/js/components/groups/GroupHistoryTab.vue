<script setup lang="ts">
import {
    FilePlus,
    XCircle,
    CheckCircle2,
    Printer,
    FileUp,
    Sparkles,
    AlertCircle,
    Clock
} from '@lucide/vue';
import {
    Stepper,
    StepperItem,
    StepperTrigger,
    StepperIndicator
} from '@/components/ui/stepper';

import type { Group } from '@/types';

defineProps<{
    group: Group;
}>();

// Helper to format date-time
const formatDateTime = (dateStr: string) => {
    return new Date(dateStr).toLocaleString('id-ID', {
        dateStyle: 'medium',
        timeStyle: 'short',
    });
};

// Helper to parse title and reason
const parseTimelineTitle = (title: string, metadata: any) => {
    if (!title) {
return { message: '', reason: null };
}
    
    // Check if the title contains "\n\nAlasan:\n"
    const splitKey = '\n\nAlasan:\n';

    if (title.includes(splitKey)) {
        const parts = title.split(splitKey);

        return {
            message: parts[0],
            reason: parts[1] || null
        };
    }
    
    // Fallback: check if the title has "Alasan:\n"
    const altSplitKey = 'Alasan:\n';

    if (title.includes(altSplitKey)) {
        const parts = title.split(altSplitKey);

        return {
            message: parts[0].trim(),
            reason: parts[1] || null
        };
    }

    // Fallback to metadata if available
    const reason = metadata?.reason || null;

    return {
        message: title,
        reason: reason
    };
};

// Helper to map timeline types to appropriate icons and color classes
const getTimelineMeta = (type: string) => {
    switch (type) {
        case 'SUBMISSION_CREATED':
            return {
                icon: FilePlus,
                colorClass: 'bg-blue-500 text-white dark:bg-blue-600',
                cardClass: 'border-l-4 border-l-blue-500 border-y-border border-r-border bg-card hover:bg-accent/5',
            };
        case 'SUBMISSION_REJECTED':
            return {
                icon: XCircle,
                colorClass: 'bg-red-500 text-white dark:bg-red-600',
                cardClass: 'border-l-4 border-l-red-500 border-y-border border-r-border bg-card hover:bg-accent/5',
            };
        case 'SUBMISSION_APPROVED':
            return {
                icon: CheckCircle2,
                colorClass: 'bg-emerald-500 text-white dark:bg-emerald-600',
                cardClass: 'border-l-4 border-l-emerald-500 border-y-border border-r-border bg-card hover:bg-accent/5',
            };
        case 'APPLICATION_LETTER_PRINTED':
            return {
                icon: Printer,
                colorClass: 'bg-indigo-500 text-white dark:bg-indigo-600',
                cardClass: 'border-l-4 border-l-indigo-500 border-y-border border-r-border bg-card hover:bg-accent/5',
            };
        case 'COMPANY_REPLY_UPLOADED':
            return {
                icon: FileUp,
                colorClass: 'bg-amber-500 text-white dark:bg-amber-600',
                cardClass: 'border-l-4 border-l-amber-500 border-y-border border-r-border bg-card hover:bg-accent/5',
            };
        case 'ADMINISTRATION_COMPLETED':
            return {
                icon: Sparkles,
                colorClass: 'bg-purple-600 text-white dark:bg-purple-700',
                cardClass: 'border-l-4 border-l-purple-500 border-y-border border-r-border bg-card hover:bg-accent/5',
            };
        default:
            return {
                icon: Clock,
                colorClass: 'bg-slate-500 text-white dark:bg-slate-600',
                cardClass: 'border-l-4 border-l-slate-500 border-y-border border-r-border bg-card hover:bg-accent/5',
            };
    }
};
</script>

<template>
    <div class="space-y-6">
        <div
            class="flex items-center gap-2 text-sm font-semibold text-muted-foreground"
        >
            <Clock class="h-4 w-4" />
            Riwayat Progres Kelompok
        </div>

        <Stepper
            v-if="group?.timelines && group.timelines.length > 0"
            orientation="vertical"
            class="relative flex flex-col space-y-6 before:absolute before:top-2 before:bottom-2 before:left-[17px] before:w-[2px] before:bg-border/60"
        >
            <StepperItem
                v-for="(timeline, index) in group.timelines"
                :key="timeline.id"
                :step="index + 1"
                class="group relative z-10 flex w-full items-start gap-4 transition-all duration-300"
            >
                <StepperTrigger
                    class="flex cursor-default pointer-events-none items-center justify-center p-0 hover:bg-transparent bg-transparent border-none"
                >
                    <StepperIndicator
                        :class="[
                            'z-10 flex h-9 w-9 shrink-0 items-center justify-center rounded-full transition-all duration-300 border-none shadow-sm',
                            getTimelineMeta(timeline.type).colorClass,
                        ]"
                    >
                        <component
                            :is="getTimelineMeta(timeline.type).icon"
                            class="h-4.5 w-4.5"
                        />
                    </StepperIndicator>
                </StepperTrigger>

                <div
                    class="flex-1 rounded-xl border p-4 transition-all duration-300 shadow-sm"
                    :class="getTimelineMeta(timeline.type).cardClass"
                >
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-xs text-muted-foreground font-medium">
                            {{ formatDateTime(timeline.created_at) }}
                        </span>
                    </div>

                    <p class="mt-2 text-sm font-medium text-foreground whitespace-pre-line leading-relaxed">
                        {{ parseTimelineTitle(timeline.title, timeline.metadata).message }}
                    </p>

                    <div
                        v-if="parseTimelineTitle(timeline.title, timeline.metadata).reason"
                        class="mt-3 rounded-lg border border-red-500/20 bg-red-500/5 p-3 text-xs text-red-700 dark:bg-red-500/10 dark:text-red-400"
                    >
                        <div class="flex gap-2 items-start">
                            <AlertCircle class="h-4 w-4 shrink-0 mt-0.5" />
                            <div>
                                <span class="font-semibold block mb-0.5">Alasan Penolakan:</span>
                                <p class="whitespace-pre-line font-normal leading-normal">
                                    {{ parseTimelineTitle(timeline.title, timeline.metadata).reason }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </StepperItem>
        </Stepper>

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
