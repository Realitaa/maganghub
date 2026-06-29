<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Clock, Check, UserX, X } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { useIdTimeFormat } from '@/composables/useIdTimeFormat';
import {
    approve as approveRequest,
    reject as rejectRequest,
} from '@/routes/groups/join-requests';

import type { Group } from '@/types';

defineProps<{
    group: Group;
    isLeader: boolean;
}>();

const { formatTimeAgo } = useIdTimeFormat();

function approveJoinRequest(requestId: number) {
    router.post(approveRequest.url(requestId));
}

function rejectJoinRequest(requestId: number) {
    router.post(rejectRequest.url(requestId));
}
</script>

<template>
    <div class="space-y-4">
        <div
            class="flex items-center gap-2 text-sm font-semibold text-muted-foreground"
        >
            <Clock class="h-4 w-4" />
            Permintaan Bergabung
        </div>

        <div
            v-if="isLeader && group.join_requests.length > 0"
            class="divide-y divide-border/60"
        >
            <div
                v-for="req in group.join_requests"
                :key="req.id"
                class="flex flex-col gap-4 py-3 first:pt-0 last:pb-0 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-9 w-9 items-center justify-center rounded-full bg-secondary text-sm font-semibold"
                    >
                        {{ req.user.name.charAt(0).toUpperCase() }}
                    </div>
                    <div>
                        <p class="text-sm font-medium">
                            {{ req.user.name }} ({{ req.user.nim ?? req.user.email }})
                        </p>
                        <p class="text-xs text-muted-foreground">
                            {{ formatTimeAgo(req.created_at) }}
                        </p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <Button
                        size="sm"
                        variant="outline"
                        class="gap-1.5 border-green-600/30 text-green-600 hover:bg-green-600/10 hover:text-green-600"
                        @click="approveJoinRequest(req.id)"
                    >
                        <Check class="h-3.5 w-3.5" />
                        Terima
                    </Button>
                    <Button
                        size="sm"
                        variant="ghost"
                        class="gap-1.5 text-destructive hover:bg-destructive/10 hover:text-destructive"
                        @click="rejectJoinRequest(req.id)"
                    >
                        <X class="h-3.5 w-3.5" />
                        Tolak
                    </Button>
                </div>
            </div>
        </div>

        <div
            v-else-if="isLeader"
            class="rounded-xl border border-dashed border-border/80 bg-muted/20 px-4 py-10 text-center space-y-2"
        >
            <UserX class="size-6 mx-auto" />
            <p class="text-sm font-medium">Belum ada permintaan aktif</p>
            <p class="text-xs text-muted-foreground">
                Permintaan bergabung baru akan muncul di tab ini.
            </p>
        </div>

        <div
            v-else
            class="rounded-xl border border-dashed border-border/80 bg-muted/20 px-4 py-10 text-center"
        >
            <p class="text-sm font-medium">
                Hanya ketua yang dapat memproses permintaan
            </p>
            <p class="mt-1 text-xs text-muted-foreground">
                Kamu tetap bisa memantau anggota melalui tab Anggota.
            </p>
        </div>
    </div>
</template>
