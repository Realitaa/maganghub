<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    Users,
    Clock,
    CheckCircle2,
    XCircle,
    ChevronRight,
    FileCheck,
    FileSearch,
    ArrowLeft,
    Download,
    Upload,
    MoreVertical,
    User as UserIcon,
    UserMinus,
    Crown,
    ExternalLink,
    FileText,
    AlertCircle,
} from '@lucide/vue';
import { TabsContent, TabsList, TabsRoot, TabsTrigger } from 'reka-ui';
import { ref, computed } from 'vue';
import GroupHistoryTab from '@/components/groups/GroupHistoryTab.vue';
import GroupStatusDialog from '@/components/groups/GroupStatusDialog.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Label } from '@/components/ui/label';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Spinner } from '@/components/ui/spinner';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Textarea } from '@/components/ui/textarea';
import { useIdTimeFormat } from '@/composables/useIdTimeFormat';
import {
    downloadLetter,
    downloadResponse,
} from '@/routes/groups/submissions';
import {
    index as groupsIndex,
    kick as kickMemberRoute,
    changeLeader as changeLeaderRoute,
    replaceLetter as replaceLetterRoute,
    replaceResponse as replaceResponseRoute,
} from '@/routes/internships/groups';
import { index as usersIndex } from '@/routes/users';
import type { Group, User } from '@/types';

// Define layout breadcrumbs
defineOptions({
    layout: (props: any) => ({
        breadcrumbs: [
            {
                title: 'Manajemen Magang',
                href: groupsIndex.url(),
            },
            {
                title: `Kelompok Magang ${props.group?.active_submission?.company_name ?? props.group?.leader?.name}`,
            },
        ],
    }),
});

const props = defineProps<{
    group: Group;
}>();

const { formatDate, formatDateTime } = useIdTimeFormat();

// ─── Navigation State ─────────────────────────────────────────────────────────

const activeTab = ref<'members' | 'documents' | 'history'>('members');
const showStatusModal = ref(false);

function goBack() {
    const search = window.location.search;
    router.visit(groupsIndex.url() + (search || ''));
}

// ─── Status Computation (Reused from GroupDashboard) ──────────────────────────

const statusLabel = computed(() => {
    const labels: Record<string, string> = {
        forming: 'Membentuk Kelompok',
        submitted: 'Pengajuan Dikirim',
        letter_published: 'Surat Terbit',
        applying: 'Menunggu Balasan Perusahaan',
        loa_review: 'Review Balasan Perusahaan',
        accepted: 'Diterima',
        partially_accepted: 'Diterima Sebagian',
        rejected: 'Ditolak Perusahaan',
        internship_started: 'Sedang Magang',
        completed: 'Selesai Magang',
    };

    return labels[props.group?.status ?? ''] ?? props.group?.status ?? '-';
});

const statusIcon = computed(() => {
    const map: Record<string, any> = {
        forming: Users,
        submitted: Clock,
        letter_published: FileCheck,
        applying: Clock,
        loa_review: FileSearch,
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
        submitted: 'text-amber-500',
        letter_published: 'text-emerald-500',
        applying: 'text-amber-500',
        loa_review: 'text-amber-500',
        accepted: 'text-emerald-500',
        partially_accepted: 'text-amber-500',
        rejected: 'text-rose-500',
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
        completed: 'Program magang telah selesai.',
    };

    return map[props.group?.status ?? ''] ?? 'Status kelompok magang.';
});

// ─── Kick Member Modal & Action ───────────────────────────────────────────────

const showKickDialog = ref(false);
const memberToKick = ref<User | null>(null);
const kickReason = ref('');
const kickProcessing = ref(false);
const kickError = ref<string | null>(null);

function openKickModal(user: User) {
    memberToKick.value = user;
    kickReason.value = '';
    kickError.value = null;
    showKickDialog.value = true;
}

function submitKick() {
    if (!memberToKick.value) return;
    if (!kickReason.value.trim()) {
        kickError.value = 'Alasan mengeluarkan anggota wajib diisi.';
        return;
    }

    kickProcessing.value = true;
    router.post(
        kickMemberRoute.url({ group: props.group.code }),
        {
            user_id: memberToKick.value.id,
            reason: kickReason.value.trim(),
        },
        {
            onSuccess: () => {
                showKickDialog.value = false;
                memberToKick.value = null;
                kickReason.value = '';
            },
            onError: (errors) => {
                kickError.value =
                    errors.reason || errors.user_id || 'Gagal mengeluarkan anggota kelompok.';
            },
            onFinish: () => {
                kickProcessing.value = false;
            },
        },
    );
}

// ─── Change Leader Modal & Action ─────────────────────────────────────────────

const showChangeLeaderDialog = ref(false);
const memberToPromote = ref<User | null>(null);
const changeLeaderProcessing = ref(false);
const changeLeaderError = ref<string | null>(null);

function openChangeLeaderModal(user: User) {
    memberToPromote.value = user;
    changeLeaderError.value = null;
    showChangeLeaderDialog.value = true;
}

function submitChangeLeader() {
    if (!memberToPromote.value) return;

    changeLeaderProcessing.value = true;
    router.post(
        changeLeaderRoute.url({ group: props.group.code }),
        {
            new_leader_id: memberToPromote.value.id,
        },
        {
            onSuccess: () => {
                showChangeLeaderDialog.value = false;
                memberToPromote.value = null;
            },
            onError: (errors) => {
                changeLeaderError.value =
                    errors.new_leader_id || 'Gagal mengubah ketua kelompok.';
            },
            onFinish: () => {
                changeLeaderProcessing.value = false;
            },
        },
    );
}

// ─── Replace Documents (Letter & Response) ────────────────────────────────────

const letterInput = ref<HTMLInputElement | null>(null);
const responseInput = ref<HTMLInputElement | null>(null);
const isUploadingLetter = ref(false);
const isUploadingResponse = ref(false);

const letterForm = useForm({
    file: null as File | null,
});

const responseForm = useForm({
    file: null as File | null,
});

function triggerLetterUpload() {
    letterInput.value?.click();
}

function triggerResponseUpload() {
    responseInput.value?.click();
}

function handleLetterChange(event: Event) {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        letterForm.file = target.files[0];
        isUploadingLetter.value = true;

        letterForm.post(
            replaceLetterRoute.url({ group: props.group.code }),
            {
                forceFormData: true,
                onSuccess: () => {
                    letterForm.reset();
                    if (letterInput.value) letterInput.value.value = '';
                },
                onFinish: () => {
                    isUploadingLetter.value = false;
                },
            },
        );
    }
}

function handleResponseChange(event: Event) {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        responseForm.file = target.files[0];
        isUploadingResponse.value = true;

        responseForm.post(
            replaceResponseRoute.url({ group: props.group.code }),
            {
                forceFormData: true,
                onSuccess: () => {
                    responseForm.reset();
                    if (responseInput.value) responseInput.value.value = '';
                },
                onFinish: () => {
                    isUploadingResponse.value = false;
                },
            },
        );
    }
}

// Redirect to user management search
function navigateToUserManagement(nim?: string | null) {
    if (!nim) return;
    router.visit(usersIndex.url({ query: { search: nim } }));
}
</script>

<template>
    <Head :title="`Detail Kelompok - ${group.active_submission?.company_name || group.leader?.name}`" />

    <div class="flex-1">
        <!-- ─── 1. HERO: BANNER KELOMPOK (AT THE VERY TOP) ─── -->
        <div class="relative h-48 w-full overflow-hidden md:h-64">
            <img
                :src="group.banner_url ?? '/assets/images/default-company-background.png'"
                :alt="`Banner kelompok ${group.leader?.name}`"
                class="h-full w-full object-cover"
                fetchpriority="high"
            />
            <!-- Gradient overlay for readability -->
            <div
                class="absolute inset-0 bg-linear-to-t from-black/60 via-black/10 to-transparent"
            />
        </div>

        <!-- ─── 2. HERO: CONTENT (BACK BUTTON ABOVE TITLE, TITLE, STATUS CARD) ─── -->
        <div class="border-b border-border/60 bg-background">
            <div class="space-y-4 px-4 py-5 md:px-8">
                <!-- Back Button Directly Above Title -->
                <div>
                    <Button
                        variant="ghost"
                        size="sm"
                        class="-ml-2 h-8 cursor-pointer gap-1.5 text-xs text-muted-foreground hover:text-foreground"
                        @click="goBack"
                        id="btn-back-to-groups"
                    >
                        <ArrowLeft class="h-3.5 w-3.5" />
                        Kembali ke Manajemen Magang
                    </Button>
                </div>

                <!-- Title & Status Section -->
                <div
                    class="flex flex-col gap-6 md:flex-row md:items-start md:justify-between"
                >
                    <div class="flex-1 space-y-1.5">
                        <div class="flex flex-wrap items-center gap-2">
                            <h1
                                class="text-2xl font-bold tracking-tight text-foreground md:text-3xl"
                            >
                                Kelompok Magang
                                {{
                                    group.active_submission?.company_name ||
                                    group.leader?.name
                                }}
                            </h1>
                        </div>
                        <p class="text-sm text-muted-foreground">
                            Kelola anggota kelompok, periksa atau timpa dokumen
                            resmi magang, serta pantau seluruh riwayat
                            aktivitas.
                        </p>
                    </div>

                    <!-- Status Card (Reused from GroupDashboard + GroupStatusDialog) -->
                    <div
                        class="flex w-full flex-col gap-3 sm:w-auto sm:min-w-[280px]"
                    >
                        <button
                            type="button"
                            class="group/status flex cursor-pointer items-start justify-between gap-3 rounded-xl border border-border/80 bg-muted/30 p-3 text-left transition-colors hover:bg-muted/50"
                            @click="showStatusModal = true"
                            id="btn-open-status-dialog"
                        >
                            <div class="flex items-start gap-2.5">
                                <component
                                    :is="statusIcon"
                                    class="mt-0.5 h-4 w-4 shrink-0"
                                    :class="statusIconColor"
                                />
                                <div>
                                    <p
                                        class="text-xs font-semibold text-foreground"
                                    >
                                        {{ statusLabel }}
                                    </p>
                                    <p class="text-[11px] text-muted-foreground">
                                        {{ statusDescription }}
                                    </p>
                                </div>
                            </div>
                            <ChevronRight
                                class="mt-0.5 h-4 w-4 shrink-0 text-muted-foreground/50 transition-transform group-hover/status:translate-x-0.5"
                            />
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ─── 3. STICKY TABS SECTION ─── -->
        <TabsRoot v-model="activeTab" class="w-full">
            <div
                class="sticky top-0 z-20 border-b border-border/60 bg-background/95 backdrop-blur-sm"
            >
                <div class="px-4 md:px-8">
                    <ScrollArea class="w-full">
                        <TabsList
                            class="flex h-auto w-max min-w-full gap-0 rounded-none bg-transparent p-0 pb-2.5 md:pb-0"
                        >
                            <!-- Tab 1: Anggota Kelompok -->
                            <TabsTrigger
                                value="members"
                                class="relative flex shrink-0 cursor-pointer items-center gap-2 rounded-none border-b-2 border-transparent px-4 py-3 text-sm font-medium text-muted-foreground transition-all hover:text-foreground data-[state=active]:border-primary data-[state=active]:text-foreground data-[state=active]:shadow-none"
                            >
                                <Users class="h-4 w-4" />
                                <span>Anggota Kelompok</span>
                                <Badge
                                    class="h-5 min-w-5 rounded-full px-1.5 font-mono text-xs"
                                >
                                    {{ group.memberships?.length || 0 }}
                                </Badge>
                            </TabsTrigger>

                            <!-- Tab 2: Dokumen Magang -->
                            <TabsTrigger
                                value="documents"
                                class="relative flex shrink-0 cursor-pointer items-center gap-2 rounded-none border-b-2 border-transparent px-4 py-3 text-sm font-medium text-muted-foreground transition-all hover:text-foreground data-[state=active]:border-primary data-[state=active]:text-foreground data-[state=active]:shadow-none"
                            >
                                <FileText class="h-4 w-4" />
                                <span>Dokumen Magang</span>
                            </TabsTrigger>

                            <!-- Tab 3: Riwayat Aktivitas -->
                            <TabsTrigger
                                value="history"
                                class="relative flex shrink-0 cursor-pointer items-center gap-2 rounded-none border-b-2 border-transparent px-4 py-3 text-sm font-medium text-muted-foreground transition-all hover:text-foreground data-[state=active]:border-primary data-[state=active]:text-foreground data-[state=active]:shadow-none"
                            >
                                <Clock class="h-4 w-4" />
                                <span>Riwayat Aktivitas</span>
                            </TabsTrigger>
                        </TabsList>
                    </ScrollArea>
                </div>
            </div>

            <div class="p-4 pt-6 md:p-8">
                <!-- ─── TAB CONTENT 1: ANGGOTA KELOMPOK ─── -->
                <TabsContent value="members" class="space-y-4">
                    <Card class="border border-border/80 shadow-xs">
                        <CardHeader class="pb-3">
                            <CardTitle class="text-base font-semibold text-foreground">
                                Daftar Anggota Kelompok
                            </CardTitle>
                            <CardDescription class="text-xs">
                                Kelola mahasiswa yang tergabung dalam kelompok magang ini.
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="p-0">
                            <Table>
                                <TableHeader
                                    class="border-b border-border/80 bg-muted/40 text-muted-foreground"
                                >
                                    <TableRow>
                                        <TableHead
                                            class="h-10 px-4 text-left text-xs font-semibold uppercase"
                                            >Nama</TableHead
                                        >
                                        <TableHead
                                            class="h-10 px-4 text-left text-xs font-semibold uppercase"
                                            >Email</TableHead
                                        >
                                        <TableHead
                                            class="h-10 px-4 text-left text-xs font-semibold uppercase"
                                            >NIM</TableHead
                                        >
                                        <TableHead
                                            class="h-10 px-4 text-left text-xs font-semibold uppercase"
                                            >Kelas</TableHead
                                        >
                                        <TableHead
                                            class="h-10 px-4 text-center text-xs font-semibold uppercase"
                                            >Status Kelompok</TableHead
                                        >
                                        <TableHead
                                            class="h-10 px-4 text-right text-xs font-semibold uppercase"
                                            >Aksi</TableHead
                                        >
                                    </TableRow>
                                </TableHeader>
                                <TableBody class="divide-y divide-border/60">
                                    <TableRow
                                        v-if="
                                            !group.memberships ||
                                            group.memberships.length === 0
                                        "
                                    >
                                        <TableCell
                                            colspan="6"
                                            class="p-8 text-center text-muted-foreground"
                                        >
                                            Tidak ada anggota kelompok.
                                        </TableCell>
                                    </TableRow>
                                    <TableRow
                                        v-for="membership in group.memberships"
                                        :key="membership.id"
                                        class="transition-colors hover:bg-muted/20"
                                    >
                                        <!-- Nama -->
                                        <TableCell
                                            class="p-4 align-middle font-medium text-foreground text-xs"
                                        >
                                            {{ membership.user?.name }}
                                        </TableCell>

                                        <!-- Email -->
                                        <TableCell
                                            class="p-4 align-middle text-xs text-muted-foreground"
                                        >
                                            {{ membership.user?.email }}
                                        </TableCell>

                                        <!-- NIM -->
                                        <TableCell
                                            class="p-4 align-middle text-xs font-mono text-muted-foreground"
                                        >
                                            {{ membership.user?.nim || '-' }}
                                        </TableCell>

                                        <!-- Kelas -->
                                        <TableCell
                                            class="p-4 align-middle text-xs text-muted-foreground"
                                        >
                                            {{
                                                membership.user?.student_class
                                                    ?.name ||
                                                membership.user?.studentClass
                                                    ?.name ||
                                                '-'
                                            }}
                                        </TableCell>

                                        <!-- Status Kelompok: Ketua / Anggota -->
                                        <TableCell
                                            class="p-4 text-center align-middle"
                                        >
                                            <Badge
                                                v-if="
                                                    membership.user?.id ===
                                                    group.leader_id
                                                "
                                                variant="default"
                                                class="gap-1 font-semibold text-[10px]"
                                            >
                                                <Crown class="h-3 w-3 text-amber-400" />
                                                Ketua
                                            </Badge>
                                            <Badge
                                                v-else
                                                variant="secondary"
                                                class="text-[10px]"
                                            >
                                                Anggota
                                            </Badge>
                                        </TableCell>

                                        <!-- Dropdown Menu Aksi -->
                                        <TableCell
                                            class="p-4 text-right align-middle"
                                        >
                                            <DropdownMenu>
                                                <DropdownMenuTrigger as-child>
                                                    <Button
                                                        variant="ghost"
                                                        class="h-8 w-8 cursor-pointer p-0"
                                                        :id="`btn-action-member-${membership.user?.id}`"
                                                    >
                                                        <span class="sr-only"
                                                            >Buka menu</span
                                                        >
                                                        <MoreVertical
                                                            class="h-4 w-4"
                                                        />
                                                    </Button>
                                                </DropdownMenuTrigger>
                                                <DropdownMenuContent
                                                    align="end"
                                                    class="w-[200px]"
                                                >
                                                    <!-- 1. Ke Manajemen Pengguna -->
                                                    <DropdownMenuItem
                                                        class="cursor-pointer"
                                                        @click="
                                                            navigateToUserManagement(
                                                                membership.user
                                                                    ?.nim,
                                                            )
                                                        "
                                                    >
                                                        <UserIcon
                                                            class="mr-2 h-4 w-4 text-primary"
                                                        />
                                                        <span>Ke Manajemen Pengguna</span>
                                                        <ExternalLink
                                                            class="ml-auto h-3 w-3 opacity-60"
                                                        />
                                                    </DropdownMenuItem>

                                                    <DropdownMenuSeparator />

                                                    <!-- 2. Angkat Menjadi Ketua -->
                                                    <DropdownMenuItem
                                                        v-if="
                                                            membership.user
                                                                ?.id !==
                                                            group.leader_id
                                                        "
                                                        class="cursor-pointer text-amber-600 focus:text-amber-700 dark:text-amber-400"
                                                        @click="
                                                            openChangeLeaderModal(
                                                                membership.user,
                                                            )
                                                        "
                                                    >
                                                        <Crown
                                                            class="mr-2 h-4 w-4"
                                                        />
                                                        <span>Angkat Jadi Ketua</span>
                                                    </DropdownMenuItem>

                                                    <!-- 3. Tendang Anggota -->
                                                    <DropdownMenuItem
                                                        class="cursor-pointer text-destructive focus:text-destructive"
                                                        @click="
                                                            openKickModal(
                                                                membership.user,
                                                            )
                                                        "
                                                    >
                                                        <UserMinus
                                                            class="mr-2 h-4 w-4"
                                                        />
                                                        <span>Tendang</span>
                                                    </DropdownMenuItem>
                                                </DropdownMenuContent>
                                            </DropdownMenu>
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </CardContent>
                    </Card>
                </TabsContent>

                <!-- ─── TAB CONTENT 2: DOKUMEN MAGANG ─── -->
                <TabsContent value="documents" class="space-y-6">
                    <!-- Hidden File Inputs -->
                    <input
                        type="file"
                        ref="letterInput"
                        class="hidden"
                        accept=".pdf"
                        @change="handleLetterChange"
                    />
                    <input
                        type="file"
                        ref="responseInput"
                        class="hidden"
                        accept=".pdf"
                        @change="handleResponseChange"
                    />

                    <div class="grid gap-6 md:grid-cols-2">
                        <!-- Card 1: Surat Permohonan Magang -->
                        <Card class="border border-border/80 shadow-xs">
                            <CardHeader class="pb-3">
                                <CardTitle class="flex items-center gap-2 text-base font-semibold text-foreground">
                                    <FileText class="h-4 w-4 text-primary" />
                                    Surat Permohonan Magang
                                </CardTitle>
                                <CardDescription class="text-xs">
                                    Surat izin / pengantar resmi dari kampus untuk diajukan ke instansi tujuan.
                                </CardDescription>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div
                                    class="rounded-xl border border-border/60 bg-muted/20 p-4 space-y-2 text-xs"
                                >
                                    <div class="flex items-center justify-between">
                                        <span class="text-muted-foreground">Status Berkas:</span>
                                        <Badge
                                            v-if="group.active_submission?.letter_path"
                                            class="bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400"
                                        >
                                            Tersedia
                                        </Badge>
                                        <Badge v-else variant="secondary">
                                            Belum Terbit
                                        </Badge>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-muted-foreground">Instansi Tujuan:</span>
                                        <span class="font-medium text-foreground">
                                            {{ group.active_submission?.company_name || '-' }}
                                        </span>
                                    </div>
                                </div>

                                <div class="flex flex-wrap items-center gap-2 pt-2">
                                    <!-- Download Letter -->
                                    <Button
                                        v-if="group.active_submission?.letter_path"
                                        as="a"
                                        :href="
                                            downloadLetter.url({
                                                submission: group.active_submission.id,
                                            })
                                        "
                                        target="_blank"
                                        variant="outline"
                                        size="sm"
                                        class="gap-1.5 text-xs font-medium"
                                    >
                                        <Download class="h-3.5 w-3.5" />
                                        Unduh Surat
                                    </Button>

                                    <!-- Replace / Overwrite Letter -->
                                    <Button
                                        size="sm"
                                        variant="default"
                                        class="cursor-pointer gap-1.5 text-xs font-medium"
                                        @click="triggerLetterUpload"
                                        :disabled="isUploadingLetter || !group.active_submission"
                                        id="btn-replace-letter"
                                    >
                                        <Spinner
                                            v-if="isUploadingLetter"
                                            class="h-3.5 w-3.5 animate-spin"
                                        />
                                        <Upload v-else class="h-3.5 w-3.5" />
                                        <span>
                                            {{
                                                group.active_submission?.letter_path
                                                    ? 'Timpa Surat Permohonan'
                                                    : 'Unggah Surat Permohonan'
                                            }}
                                        </span>
                                    </Button>
                                </div>
                                <p
                                    v-if="letterForm.errors.file"
                                    class="text-xs text-destructive font-medium"
                                >
                                    {{ letterForm.errors.file }}
                                </p>
                            </CardContent>
                        </Card>

                        <!-- Card 2: Surat Balasan Perusahaan (LoA) -->
                        <Card class="border border-border/80 shadow-xs">
                            <CardHeader class="pb-3">
                                <CardTitle class="flex items-center gap-2 text-base font-semibold text-foreground">
                                    <FileSearch class="h-4 w-4 text-primary" />
                                    Surat Balasan / LoA Perusahaan
                                </CardTitle>
                                <CardDescription class="text-xs">
                                    Surat keputusan resmi penerimaan atau penolakan magang dari pihak perusahaan.
                                </CardDescription>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div
                                    class="rounded-xl border border-border/60 bg-muted/20 p-4 space-y-2 text-xs"
                                >
                                    <div class="flex items-center justify-between">
                                        <span class="text-muted-foreground">Status Balasan:</span>
                                        <Badge
                                            v-if="group.active_submission?.company_response_path"
                                            class="bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400"
                                        >
                                            Sudah Diunggah
                                        </Badge>
                                        <Badge v-else variant="secondary">
                                            Belum Ada
                                        </Badge>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-muted-foreground">Status Magang:</span>
                                        <span class="font-medium text-foreground">
                                            {{ statusLabel }}
                                        </span>
                                    </div>
                                </div>

                                <div class="flex flex-wrap items-center gap-2 pt-2">
                                    <!-- Download Response -->
                                    <Button
                                        v-if="group.active_submission?.company_response_path"
                                        as="a"
                                        :href="
                                            downloadResponse.url({
                                                submission: group.active_submission.id,
                                            })
                                        "
                                        target="_blank"
                                        variant="outline"
                                        size="sm"
                                        class="gap-1.5 text-xs font-medium"
                                    >
                                        <Download class="h-3.5 w-3.5" />
                                        Unduh Surat Balasan
                                    </Button>

                                    <!-- Replace / Overwrite Response -->
                                    <Button
                                        size="sm"
                                        variant="default"
                                        class="cursor-pointer gap-1.5 text-xs font-medium"
                                        @click="triggerResponseUpload"
                                        :disabled="isUploadingResponse || !group.active_submission"
                                        id="btn-replace-response"
                                    >
                                        <Spinner
                                            v-if="isUploadingResponse"
                                            class="h-3.5 w-3.5 animate-spin"
                                        />
                                        <Upload v-else class="h-3.5 w-3.5" />
                                        <span>
                                            {{
                                                group.active_submission?.company_response_path
                                                    ? 'Timpa Surat Balasan'
                                                    : 'Unggah Surat Balasan'
                                            }}
                                        </span>
                                    </Button>
                                </div>
                                <p
                                    v-if="responseForm.errors.file"
                                    class="text-xs text-destructive font-medium"
                                >
                                    {{ responseForm.errors.file }}
                                </p>
                            </CardContent>
                        </Card>
                    </div>
                </TabsContent>

                <!-- ─── TAB CONTENT 3: RIWAYAT AKTIVITAS (REUSING GROUP HISTORY) ─── -->
                <TabsContent value="history">
                    <GroupHistoryTab :group="group" />
                </TabsContent>
            </div>
        </TabsRoot>

        <!-- ─── MODAL 1: STATUS PROGRESSION DIALOG ─── -->
        <GroupStatusDialog
            :open="showStatusModal"
            :group="group"
            @update:open="showStatusModal = $event"
        />

        <!-- ─── MODAL 2: TENDANG ANGGOTA KELOMPOK ─── -->
        <Dialog v-model:open="showKickDialog">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2 text-destructive">
                        <UserMinus class="h-5 w-5" />
                        Keluarkan Mahasiswa dari Kelompok
                    </DialogTitle>
                    <DialogDescription class="text-xs">
                        Tindakan ini akan mencabut keanggotaan mahasiswa dari kelompok magang ini.
                        Mahasiswa akan menerima notifikasi beserta alasan yang Anda cantumkan di bawah ini.
                    </DialogDescription>
                </DialogHeader>

                <div class="space-y-4 py-2">
                    <div
                        v-if="memberToKick"
                        class="rounded-lg border border-border/60 bg-muted/30 p-3 text-xs"
                    >
                        <p class="font-semibold text-foreground">
                            {{ memberToKick.name }}
                        </p>
                        <p class="text-muted-foreground">
                            NIM: {{ memberToKick.nim || '-' }} &bull; {{ memberToKick.email }}
                        </p>
                    </div>

                    <div class="space-y-1.5">
                        <Label for="kick-reason" class="text-xs font-medium">
                            Alasan Mengeluarkan Anggota <span class="text-destructive">*</span>
                        </Label>
                        <Textarea
                            id="kick-reason"
                            v-model="kickReason"
                            placeholder="Contoh: Mengundurkan diri karena telah diterima magang mandiri..."
                            class="min-h-[100px] text-xs resize-none"
                        />
                        <p v-if="kickError" class="text-xs text-destructive font-medium">
                            {{ kickError }}
                        </p>
                    </div>
                </div>

                <DialogFooter class="gap-2 sm:justify-end">
                    <Button
                        variant="outline"
                        size="sm"
                        @click="showKickDialog = false"
                        :disabled="kickProcessing"
                    >
                        Batal
                    </Button>
                    <Button
                        variant="destructive"
                        size="sm"
                        class="gap-1.5"
                        @click="submitKick"
                        :disabled="kickProcessing"
                        id="btn-confirm-kick"
                    >
                        <Spinner v-if="kickProcessing" class="h-3.5 w-3.5 animate-spin" />
                        <UserMinus v-else class="h-3.5 w-3.5" />
                        Keluarkan Anggota
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- ─── MODAL 3: ANGKAT MENJADI KETUA KELOMPOK ─── -->
        <Dialog v-model:open="showChangeLeaderDialog">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2 text-foreground">
                        <Crown class="h-5 w-5 text-amber-500" />
                        Angkat Sebagai Ketua Kelompok
                    </DialogTitle>
                    <DialogDescription class="text-xs">
                        Apakah Anda yakin ingin mengalihkan kepemimpinan kelompok magang ini?
                        Perubahan ini akan dicatat ke dalam linimasa riwayat kelompok.
                    </DialogDescription>
                </DialogHeader>

                <div v-if="memberToPromote" class="space-y-3 py-2">
                    <div
                        class="rounded-lg border border-border/60 bg-muted/30 p-3 text-xs"
                    >
                        <p class="font-semibold text-foreground">
                            Calon Ketua Baru: {{ memberToPromote.name }}
                        </p>
                        <p class="text-muted-foreground">
                            NIM: {{ memberToPromote.nim || '-' }} &bull; {{ memberToPromote.email }}
                        </p>
                    </div>

                    <div
                        class="flex items-start gap-2 rounded-lg border border-amber-200/50 bg-amber-50/10 p-3 text-xs text-amber-900 dark:border-amber-900/50 dark:text-amber-200"
                    >
                        <AlertCircle class="h-4 w-4 shrink-0 text-amber-500 mt-0.5" />
                        <p>
                            Ketua sebelumnya akan tetap berada di dalam kelompok sebagai anggota biasa.
                        </p>
                    </div>

                    <p v-if="changeLeaderError" class="text-xs text-destructive font-medium">
                        {{ changeLeaderError }}
                    </p>
                </div>

                <DialogFooter class="gap-2 sm:justify-end">
                    <Button
                        variant="outline"
                        size="sm"
                        @click="showChangeLeaderDialog = false"
                        :disabled="changeLeaderProcessing"
                    >
                        Batal
                    </Button>
                    <Button
                        variant="default"
                        size="sm"
                        class="gap-1.5 bg-amber-600 hover:bg-amber-700 text-white"
                        @click="submitChangeLeader"
                        :disabled="changeLeaderProcessing"
                        id="btn-confirm-change-leader"
                    >
                        <Spinner v-if="changeLeaderProcessing" class="h-3.5 w-3.5 animate-spin" />
                        <Crown v-else class="h-3.5 w-3.5" />
                        Ya, Angkat Menjadi Ketua
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
