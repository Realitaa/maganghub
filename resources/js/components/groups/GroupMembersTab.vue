<script setup lang="ts">
import { Users, Crown } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';

import type { Group } from '@/types';

defineProps<{
    group: Group;
}>();
</script>

<template>
    <div class="space-y-4">
        <div
            class="flex items-center gap-2 text-sm font-semibold text-muted-foreground"
        >
            <Users class="h-4 w-4" />
            Anggota Saat Ini ({{ group.memberships.length }})
        </div>
        <div class="divide-y divide-border/60">
            <div
                v-for="membership in group.memberships"
                :key="membership.user.id"
                class="flex items-center justify-between py-3 first:pt-0 last:pb-0"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-9 w-9 items-center justify-center rounded-full bg-primary/10 text-sm font-semibold text-primary"
                    >
                        {{ membership.user.name.charAt(0).toUpperCase() }}
                    </div>
                    <div>
                        <p class="text-sm font-medium">
                            {{ membership.user.name }}
                        </p>
                        <p class="text-xs text-muted-foreground">
                            {{ membership.user.nim ?? membership.user.email }}
                        </p>
                    </div>
                </div>
                <Badge
                    v-if="membership.user.id === group.leader_id"
                    variant="secondary"
                    class="gap-1"
                >
                    <Crown class="h-3 w-3" />
                    Ketua
                </Badge>
            </div>
        </div>
    </div>
</template>
