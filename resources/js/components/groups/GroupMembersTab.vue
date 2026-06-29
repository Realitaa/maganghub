<script setup lang="ts">
import { usePage, router } from '@inertiajs/vue3';
import { Users, Crown, UserMinus } from '@lucide/vue';
import { ref, computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Spinner } from '@/components/ui/spinner';
import { kick as kickRoute } from '@/routes/groups';
import type { Group, Member } from '@/types';

const props = defineProps<{
    group: Group;
}>();

const page = usePage();
const memberToKick = ref<Member | null>(null);
const isProcessing = ref(false);

const isLeader = computed(() => {
    return props.group.leader_id === (page.props.auth as any)?.user?.id;
});

const isEditable = computed(() => {
    return props.group.status === 'forming' || props.group.status === 'company_rejected';
});

function kickMember() {
    if (!memberToKick.value) {
        return;
    }

    isProcessing.value = true;
    router.post(
        kickRoute(props.group.id).url,
        { user_id: memberToKick.value.id },
        {
            preserveScroll: true,
            onSuccess: () => {
                memberToKick.value = null;
            },
            onFinish: () => {
                isProcessing.value = false;
            },
        }
    );
}
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

                <div class="flex items-center gap-2">
                    <Badge
                        v-if="membership.user.id === group.leader_id"
                        variant="secondary"
                        class="gap-1"
                    >
                        <Crown class="h-3 w-3" />
                        Ketua
                    </Badge>
                    <Button
                        v-else-if="isLeader && isEditable"
                        variant="ghost"
                        size="icon"
                        class="h-8 w-8 text-destructive hover:bg-destructive/10 hover:text-destructive"
                        @click="memberToKick = membership.user"
                        title="Keluarkan dari kelompok"
                        id="btn-kick-member"
                    >
                        <UserMinus class="h-4 w-4" />
                    </Button>
                </div>
            </div>
        </div>

        <!-- Kick Confirmation Dialog -->
        <Dialog :open="!!memberToKick" @update:open="val => { if (!val) memberToKick = null }">
            <DialogContent class="sm:max-w-[400px]">
                <DialogHeader>
                    <DialogTitle>Keluarkan Anggota Kelompok</DialogTitle>
                    <DialogDescription>
                        Apakah Anda yakin ingin mengeluarkan <span class="font-semibold text-foreground">{{ memberToKick?.name }}</span> dari kelompok magang Anda?
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button variant="outline" @click="memberToKick = null" :disabled="isProcessing">
                        Batal
                    </Button>
                    <Button variant="destructive" @click="kickMember" :disabled="isProcessing" id="btn-confirm-kick-member">
                        <Spinner v-if="isProcessing" class="mr-2 h-4 w-4 animate-spin" />
                        Ya, Keluarkan
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
