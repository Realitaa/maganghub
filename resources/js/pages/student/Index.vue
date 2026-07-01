<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Users, Clock, CheckCircle2, XCircle, FileCheck, FileSearchCorner } from '@lucide/vue';
import { computed } from 'vue';
import NoGroupState from '@/components/groups/NoGroupState.vue';
import { Badge } from '@/components/ui/badge';
import { ScrollArea } from '@/components/ui/scroll-area';
import { home } from '@/routes';
import { show } from '@/routes/student/groups';
import type { Group, PendingJoinRequest } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Beranda',
                href: home(),
            },
        ],
    },
});

const props = defineProps<{
    groups: (Group & { membership_status: string })[];
    pendingJoinRequests: PendingJoinRequest[];
}>();

const page = usePage();

const isLocked = computed(() => {
    const requirements = (page.props.auth as any)?.requirements;

    return !requirements?.password_changed || !requirements?.profile_completed;
});

const isLeaderInAnyGroup = computed(() => {
    const userId = (page.props.auth as any)?.user?.id;

    return props.groups.some((g) => g.leader_id === userId);
});

function getStatusIcon(status: string) {
    const map: Record<string, any> = {
        forming: Users,
        submitted: Clock,
        letter_published: FileCheck,
        applying: Clock,
        loa_review: FileSearchCorner,
        accepted: CheckCircle2,
        partially_accepted: CheckCircle2,
        rejected: XCircle,
        internship_started: CheckCircle2,
        completed: CheckCircle2,
    };

    return map[status] ?? Clock;
}

function getStatusColor(status: string) {
    const map: Record<string, string> = {
        forming: 'bg-blue-500/10 text-blue-500',
        submitted: 'bg-yellow-500/10 text-yellow-500',
        letter_published: 'bg-green-500/10 text-green-500',
        applying: 'bg-yellow-500/10 text-yellow-500',
        loa_review: 'bg-yellow-500/10 text-yellow-500',
        accepted: 'bg-green-500/10 text-green-500',
        partially_accepted: 'bg-yellow-500/10 text-yellow-500',
        rejected: 'bg-destructive/10 text-destructive',
        internship_started: 'bg-primary/10 text-primary',
        completed: 'bg-primary/10 text-primary',
    };

    return map[status] ?? 'bg-muted text-muted-foreground';
}

function getStatusLabel(status: string) {
    const labels: Record<string, string> = {
        forming: 'Pembentukan',
        submitted: 'Menunggu Verifikasi',
        letter_published: 'Surat Terbit',
        applying: 'Menunggu Balasan',
        loa_review: 'Review LoA',
        accepted: 'Diterima',
        partially_accepted: 'Diterima Sebagian',
        rejected: 'Ditolak',
        internship_started: 'Magang Dimulai',
        completed: 'Selesai',
    };

    return labels[status] ?? status;
}

</script>

<template>
    <Head title="Beranda" />

    <div class="flex-1 flex flex-col">
        <NoGroupState
            :is-locked="isLocked"
            :pending-join-requests="pendingJoinRequests"
            :is-leader-in-any-group="isLeaderInAnyGroup"
        >
            <!-- Show groups in the right column if there are any -->
            <template #right-column v-if="groups.length > 0 && !isLocked">
                <div class="flex w-full flex-col lg:max-w-md xl:max-w-lg lg:pl-6 pt-12 lg:pt-0">
                    <h2 class="mb-6 text-xl font-semibold tracking-tight text-foreground">Kelompok Magang Saya</h2>
                    
                    <ScrollArea class="w-full pr-4 pb-12 lg:pb-0 max-h-[60vh]">
                        <div class="flex flex-col space-y-5 pb-6">
                            <Link
                                v-for="group in groups"
                                :key="group.id"
                                :href="show(group.id)"
                                class="flex flex-col overflow-hidden rounded-2xl border border-border bg-card transition-all hover:border-primary/50 hover:shadow-md"
                            >
                                <div class="relative h-28 w-full bg-muted">
                                    <img
                                        :src="group.banner_url ?? '/assets/images/default-company-background.png'"
                                        alt="Banner"
                                        class="h-full w-full object-cover"
                                    />
                                    <div class="absolute inset-0 bg-linear-to-t from-black/70 to-transparent" />
                                    
                                    <div class="absolute bottom-3 left-4 right-4 flex items-center justify-between">
                                        <span class="truncate font-medium text-white">
                                            {{ group.active_submission?.company_name || 'Menunggu Perusahaan' }}
                                        </span>
                                        <Badge variant="secondary" class="border-none bg-black/40 text-white backdrop-blur-sm">
                                            {{ group.memberships?.length || 0 }} Anggota
                                        </Badge>
                                    </div>
                                </div>
                                
                                <div class="flex flex-1 flex-col justify-between space-y-3 p-4">
                                    <div>
                                        <div class="mb-2 flex items-center justify-between">
                                            <span class="font-mono text-xs text-muted-foreground">Kode: {{ group.code }}</span>
                                            <span v-if="group.leader_id === page.props.auth?.user?.id" class="text-xs font-semibold text-primary">Ketua</span>
                                            <span v-else class="text-xs font-medium text-muted-foreground">Anggota</span>
                                        </div>
                                        
                                        <div class="mt-3 flex items-center gap-2">
                                            <div class="rounded-md p-1.5" :class="getStatusColor(group.status)">
                                                <component :is="getStatusIcon(group.status)" class="h-4 w-4" />
                                            </div>
                                            <span class="text-sm font-medium">{{ getStatusLabel(group.status) }}</span>
                                        </div>

                                        <div v-if="group.membership_status === 'interning_elsewhere'" class="mt-3 rounded-md border border-amber-500/20 bg-amber-500/10 p-2">
                                            <p class="text-center font-medium text-xs text-amber-600 dark:text-amber-400">
                                                Status keanggotaan: Magang di tempat lain
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </Link>
                        </div>
                    </ScrollArea>
                </div>
            </template>
        </NoGroupState>
    </div>
</template>
