<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import {
    Users,
    Clock,
    CheckCircle2,
    XCircle,
    Pencil,
    ChevronRight,
    Share2,
    FileCheck,
    Trash2,
    ArrowLeftToLine,
    FileSearchCorner,
} from '@lucide/vue';
import { TabsContent, TabsList, TabsRoot, TabsTrigger } from 'reka-ui';
import { ref, onMounted, computed, watch } from 'vue';
import GroupBannerCropDialog from '@/components/groups/GroupBannerCropDialog.vue';
import GroupDisbandDialog from '@/components/groups/GroupDisbandDialog.vue';
import GroupHistoryTab from '@/components/groups/GroupHistoryTab.vue';
import GroupJoinFromLinkDialog from '@/components/groups/GroupJoinFromLinkDialog.vue';
import GroupJoinRequestsTab from '@/components/groups/GroupJoinRequestsTab.vue';
import GroupLeaveDialog from '@/components/groups/GroupLeaveDialog.vue';
import GroupMembersTab from '@/components/groups/GroupMembersTab.vue';
import GroupShareDialog from '@/components/groups/GroupShareDialog.vue';
import GroupStatusDialog from '@/components/groups/GroupStatusDialog.vue';
import NoGroupState from '@/components/groups/NoGroupState.vue';
import InternshipLetterTab from '@/components/submissions/InternshipLetterTab.vue';
import InternshipSubmissionForm from '@/components/submissions/InternshipSubmissionForm.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { ScrollArea, ScrollBar } from '@/components/ui/scroll-area';
import { home } from '@/routes';

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

import type { Group, PendingJoinRequest } from '@/types';

// ─── Props ────────────────────────────────────────────────────────────────────

const props = defineProps<{
    group: Group | null;
    pendingJoinRequests: PendingJoinRequest[];
}>();

// ─── State ────────────────────────────────────────────────────────────────────

const showDisbandConfirm = ref(false);
const showLeaveConfirm = ref(false);
const showJoinConfirmDialog = ref(false);
const joinConfirmCode = ref('');
const activeGroupTab = ref<
    'members' | 'requests' | 'submissions' | 'history' | 'response'
>('members');

// Banner & dialogs
const showBannerDialog = ref(false);
const showStatusDialog = ref(false);
const showShareDialog = ref(false);
const isBannerHovered = ref(false);

// ─── Computed ─────────────────────────────────────────────────────────────────

const page = usePage();

const isLocked = computed(() => {
    const requirements = (page.props.auth as any)?.requirements;

    return !requirements?.password_changed || !requirements?.profile_completed;
});

const isLeader = computed(() => {
    return props.group?.leader_id === (page.props.auth as any)?.user?.id;
});

const groupStatusLabel = computed(() => {
    const labels: Record<string, string> = {
        forming: 'Pembentukan',
        submitted: 'Menunggu Verifikasi',
        letter_published: 'Surat Terbit',
        applying: 'Menunggu Balasan',
        loa_review: 'Menunggu Review LoA',
        accepted: 'Diterima',
        partially_accepted: 'Diterima Sebagian',
        rejected: 'Ditolak Perusahaan',
        internship_started: 'Magang Dimulai',
        completed: 'Selesai',
    };

    return labels[props.group?.status ?? ''] ?? props.group?.status ?? '-';
});

const statusIcon = computed(() => {
    const map: Record<string, object> = {
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

    return map[props.group?.status ?? ''] ?? Clock;
});

const statusIconColor = computed(() => {
    const map: Record<string, string> = {
        forming: 'text-blue-500',
        submitted: 'text-yellow-500',
        letter_published: 'text-green-500',
        applying: 'text-yellow-500',
        loa_review: 'text-yellow-500',
        accepted: 'text-green-500',
        partially_accepted: 'text-yellow-500',
        rejected: 'text-destructive',
        internship_started: 'text-primary',
        completed: 'text-primary',
    };

    return map[props.group?.status ?? ''] ?? 'text-muted-foreground';
});

const statusDescription = computed(() => {
    const company =
        props.group?.active_submission?.company_name ?? 'perusahaan tujuan';
    const map: Record<string, string> = {
        forming: 'Kelompok masih dalam tahap pembentukan.',
        submitted: 'Pengajuan menunggu verifikasi admin.',
        letter_published: `Surat terbit, antar ke ${company}.`,
        applying: `${company} sedang meninjau permohonan.`,
        loa_review: 'LoA sedang dalam tahap review.',
        accepted: `Diterima di ${company}.`,
        partially_accepted: 'Diterima sebagian oleh perusahaan.',
        rejected: 'Permohonan ditolak perusahaan.',
        internship_started: 'Magang sedang berlangsung.',
        completed: 'Masa magang selesai.',
    };

    return map[props.group?.status ?? ''] ?? '';
});

const showResponseTab = computed(() => {
    return (
        isLeader.value &&
        (props.group?.status === 'letter_published' ||
            props.group?.status === 'applying' ||
            props.group?.status === 'loa_review')
    );
});

const isSubmissionEditable = computed(() => {
    return (
        isLeader.value &&
        (props.group?.status === 'forming' ||
            props.group?.status === 'company_rejected')
    );
});

// ─── Deep link: ?code=XXXXXXXXXX ─────────────────────────────────────────────

onMounted(() => {
    const urlParams = new URLSearchParams(window.location.search);
    const code = urlParams.get('code');

    if (
        code &&
        !props.group &&
        props.pendingJoinRequests.length === 0 &&
        !isLocked.value
    ) {
        joinConfirmCode.value = code;
        showJoinConfirmDialog.value = true;
    }
});

watch(showJoinConfirmDialog, (isOpen) => {
    if (!isOpen) {
        const url = new URL(window.location.href);

        if (url.searchParams.has('code')) {
            url.searchParams.delete('code');
            window.history.replaceState({}, '', url.pathname + url.search);
        }
    }
});
</script>

<template>
    <Head title="Beranda" />

    <div class="flex-1">
        <!-- ───── NO GROUP STATE ───── -->
        <NoGroupState
            v-if="!group"
            :is-locked="isLocked"
            :pending-join-requests="pendingJoinRequests"
        />

        <!-- ───── IN GROUP STATE ───── -->
        <div v-else-if="group" class="flex flex-col">
            <!-- ── Hero: Banner ── -->
            <div
                class="group/banner relative h-48 w-full overflow-hidden md:h-64"
                @mouseenter="isBannerHovered = true"
                @mouseleave="isBannerHovered = false"
            >
                <img
                    :src="
                        group.banner_url ??
                        '/assets/images/default-company-background.png'
                    "
                    :alt="`Banner kelompok ${group.leader.name}`"
                    class="h-full w-full object-cover transition-all duration-300"
                    :class="{
                        'blur-sm brightness-75': isBannerHovered && isLeader,
                    }"
                />
                <!-- Gradient overlay always present for text readability -->
                <div
                    class="absolute inset-0 bg-linear-to-t from-black/60 via-black/10 to-transparent"
                />

                <!-- Edit button — visible on hover for leader only -->
                <button
                    v-if="isLeader"
                    class="absolute top-4 right-4 flex items-center gap-2 rounded-xl bg-black/40 px-3 py-2 text-sm font-medium text-white backdrop-blur-sm transition-all duration-200"
                    :class="
                        isBannerHovered
                            ? 'translate-y-0 opacity-100'
                            : '-translate-y-1 opacity-0'
                    "
                    @click="showBannerDialog = true"
                    id="btn-edit-banner"
                >
                    <Pencil class="h-4 w-4" />
                    Ubah Banner
                </button>
            </div>

            <!-- ── Hero: Title + Right Column ── -->
            <div class="border-b border-border/60 bg-background">
                <div
                    class="flex flex-col gap-6 px-4 py-5 md:flex-row md:items-start md:justify-between md:px-8"
                >
                    <!-- Left: Title + description + action buttons -->
                    <div class="flex-1 space-y-2">
                        <div class="flex flex-wrap items-center gap-2">
                            <h1
                                class="text-2xl font-bold tracking-tight md:text-3xl"
                            >
                                Kelompok Magang
                                {{
                                    group?.active_submission?.company_name ||
                                    group.leader.name
                                }}
                            </h1>
                        </div>
                        <p class="text-sm text-muted-foreground">
                            {{ 
                                isLeader ? 'Kelola anggota, pengajuan, dan perkembangan kelompok magang.'
                                : 'Lihat perkembangan kelompok magangmu.'
                            }}
                        </p>

                        <!-- Leave / Disband actions -->
                        <div class="flex flex-wrap gap-2 pt-1">
                            <Button
                                v-if="
                                    isLeader &&
                                    (group.status === 'forming' ||
                                        group.status === 'company_rejected')
                                "
                                variant="ghost"
                                size="sm"
                                class="h-8 gap-1.5 text-destructive hover:bg-destructive/10 hover:text-destructive"
                                @click="showDisbandConfirm = true"
                            >
                                <Trash2 class="h-3.5 w-3.5" />
                                Bubarkan Kelompok
                            </Button>
                            <Button
                                v-else-if="
                                    !isLeader &&
                                    (group.status === 'forming' ||
                                        group.status === 'company_rejected')
                                "
                                variant="ghost"
                                size="sm"
                                class="h-8 gap-1.5 text-destructive hover:bg-destructive/10 hover:text-destructive"
                                @click="showLeaveConfirm = true"
                            >
                                <ArrowLeftToLine class="h-3.5 w-3.5" />
                                Keluar dari Kelompok
                            </Button>
                        </div>
                    </div>

                    <!-- Right: Status Card + Share Button -->
                    <div class="flex flex-col gap-2 md:w-72 lg:w-80">
                        <!-- Status Card (clickable) -->
                        <button
                            type="button"
                            class="group/status flex w-full items-start gap-3 rounded-xl border border-border/70 bg-muted/30 p-4 text-left transition-all hover:border-border hover:bg-muted/50 hover:shadow-sm"
                            @click="showStatusDialog = true"
                            id="btn-open-status-dialog"
                        >
                            <div class="mt-0.5 shrink-0">
                                <component
                                    :is="statusIcon"
                                    class="h-5 w-5"
                                    :class="statusIconColor"
                                />
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold">
                                    {{ groupStatusLabel }}
                                </p>
                                <p
                                    class="mt-0.5 line-clamp-2 text-xs text-muted-foreground"
                                >
                                    {{ statusDescription }}
                                </p>
                            </div>
                            <ChevronRight
                                class="mt-0.5 h-4 w-4 shrink-0 text-muted-foreground/50 transition-transform group-hover/status:translate-x-0.5"
                            />
                        </button>

                        <!-- Share Button -->
                        <Button
                            variant="outline"
                            size="sm"
                            class="w-full gap-2"
                            @click="showShareDialog = true"
                            id="btn-open-share-dialog"
                        >
                            <Share2 class="h-4 w-4" />
                            Bagikan Kelompok
                        </Button>
                    </div>
                </div>
            </div>

            <!-- ── Sticky Tabs ── -->
            <TabsRoot
                v-model="activeGroupTab"
            >
                <div
                    class="sticky top-0 z-20 border-b border-border/60 bg-background/95 max-w-[99.5%] backdrop-blur-sm"
                >
                    <div class="px-4 md:px-8">
                        <ScrollArea class="w-full">
                            <TabsList
                                class="flex h-auto w-max min-w-full gap-0 rounded-none bg-transparent p-0 pb-2.5 md:pb-0"
                            >
                                <TabsTrigger
                                    value="members"
                                    class="relative flex items-center gap-1 shrink-0 rounded-none border-b-2 border-transparent px-4 py-3 text-sm font-medium text-muted-foreground transition-all hover:text-foreground data-[state=active]:border-primary data-[state=active]:text-foreground data-[state=active]:shadow-none"
                                >
                                    Anggota
                                    <Badge class="h-5 min-w-5 rounded-full px-1 font-mono tabular-nums text-xs">
                                        {{ group.memberships.length }}
                                    </Badge>
                                </TabsTrigger>
                                <TabsTrigger
                                    v-if="isLeader"
                                    value="requests"
                                    class="relative items-center gap-1 shrink-0 rounded-none border-b-2 border-transparent px-4 py-3 text-sm font-medium text-muted-foreground transition-all hover:text-foreground data-[state=active]:border-primary data-[state=active]:text-foreground data-[state=active]:shadow-none"
                                >
                                    Permintaan
                                    <Badge
                                        v-if="group.join_requests.length > 0"
                                        class="h-5 min-w-5 rounded-full px-1 font-mono tabular-nums text-xs"
                                    >
                                        {{ group.join_requests.length }}
                                    </Badge>
                                </TabsTrigger>
                                <TabsTrigger
                                    value="submissions"
                                    class="relative shrink-0 rounded-none border-b-2 border-transparent px-4 py-3 text-sm font-medium text-muted-foreground transition-all hover:text-foreground data-[state=active]:border-primary data-[state=active]:text-foreground data-[state=active]:shadow-none"
                                >
                                    Pengajuan
                                </TabsTrigger>
                                <TabsTrigger
                                    value="history"
                                    class="relative flex items-center gap-1 shrink-0 rounded-none border-b-2 border-transparent px-4 py-3 text-sm font-medium text-muted-foreground transition-all hover:text-foreground data-[state=active]:border-primary data-[state=active]:text-foreground data-[state=active]:shadow-none"
                                >
                                    Riwayat
                                    <Badge
                                        v-if="group.timelines?.length"
                                        class="h-5 min-w-5 rounded-full px-1 font-mono tabular-nums text-xs"
                                    >
                                        {{ group.timelines?.length }}
                                    </Badge>
                                </TabsTrigger>
                                <TabsTrigger
                                    v-if="showResponseTab"
                                    value="response"
                                    class="relative shrink-0 rounded-none border-b-2 border-transparent px-4 py-3 text-sm font-medium text-muted-foreground transition-all hover:text-foreground data-[state=active]:border-primary data-[state=active]:text-foreground data-[state=active]:shadow-none"
                                >
                                    Surat Magang
                                </TabsTrigger>
                            </TabsList>
                            <ScrollBar orientation="horizontal" />
                        </ScrollArea>
                    </div>
                </div>

                <!-- ── Tab Content ── -->
                <div class="px-4 py-6 md:px-8">
                    <!-- ─ Members Tab ─ -->
                    <TabsContent value="members" class="mt-0 space-y-4">
                        <GroupMembersTab :group="group" />
                    </TabsContent>

                    <!-- ─ Requests Tab ─ -->
                    <TabsContent v-if="isLeader" value="requests" class="mt-0 space-y-4">
                        <GroupJoinRequestsTab
                            :group="group"
                            :is-leader="isLeader"
                        />
                    </TabsContent>

                    <!-- ─ Submissions Tab ─ -->
                    <TabsContent value="submissions" class="mt-0 space-y-6">
                        <InternshipSubmissionForm
                            :group="group"
                            :is-leader="isLeader"
                            :is-submission-editable="isSubmissionEditable"
                            :group-status-label="groupStatusLabel"
                            :status-description="statusDescription"
                        />
                    </TabsContent>

                    <!-- ─ History Tab ─ -->
                    <TabsContent value="history" class="mt-0 space-y-6">
                        <GroupHistoryTab :group="group" />
                    </TabsContent>

                    <!-- ─ Internship Letter Tab ─ -->
                    <TabsContent
                        v-if="showResponseTab"
                        value="response"
                        class="mt-0 space-y-4"
                    >
                        <InternshipLetterTab
                            :group="group"
                        />
                    </TabsContent>
                </div>
            </TabsRoot>
        </div>

        <!-- ───── DIALOGS ───── -->

        <!-- Banner Crop Dialog -->
        <GroupBannerCropDialog
            v-if="group"
            v-model:open="showBannerDialog"
            :group-id="group.id"
        />

        <!-- Status Dialog -->
        <GroupStatusDialog
            v-if="group"
            v-model:open="showStatusDialog"
            :group="group"
        />

        <!-- Share Dialog -->
        <GroupShareDialog
            v-if="group"
            v-model:open="showShareDialog"
            :group="group"
        />

        <!-- Join from Link Confirm -->
        <GroupJoinFromLinkDialog
            v-model:open="showJoinConfirmDialog"
            :code="joinConfirmCode"
            @success="router.reload()"
        />

        <!-- Disband Confirm -->
        <GroupDisbandDialog
            v-if="group"
            v-model:open="showDisbandConfirm"
            :group-id="group.id"
            @success="router.reload()"
        />

        <!-- Leave Confirm -->
        <GroupLeaveDialog
            v-model:open="showLeaveConfirm"
            @success="router.reload()"
        />
    </div>
</template>
