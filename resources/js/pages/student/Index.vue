<script setup lang="ts">
import { Head, router, usePage, Link, useHttp } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import {
    parseDate,
    DateFormatter,
    getLocalTimeZone,
} from '@internationalized/date';
import {
    Users,
    Plus,
    LogIn,
    Crown,
    UserMinus,
    Trash2,
    UserCheck,
    UserX,
    Clock,
    FileText,
    CheckCircle2,
    Lock,
    XCircle,
    Pencil,
    ChevronRight,
    Share2,
    AlertCircle,
    FileCheck,
    Upload,
} from '@lucide/vue';
import { Save, Send, Building2, MapPin, Phone, Calendar } from '@lucide/vue';
import { TabsContent, TabsList, TabsRoot, TabsTrigger } from 'reka-ui';
import { ref, onMounted, computed } from 'vue';
import { watch } from 'vue';
import GroupBannerCropDialog from '@/components/GroupBannerCropDialog.vue';
import GroupShareDialog from '@/components/GroupShareDialog.vue';
import GroupStatusDialog from '@/components/GroupStatusDialog.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Calendar as CalendarComponent } from '@/components/ui/calendar';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
    CardDescription,
} from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import { Spinner } from '@/components/ui/spinner';
import { cn } from '@/lib/utils';
import { dashboard } from '@/routes';
import {
    store as groupStore,
    join as groupJoin,
    leave as groupLeave,
    destroy as groupDestroy,
    byCode as groupByCode,
} from '@/routes/groups';
import {
    cancel as cancelRequest,
    approve as approveRequest,
    reject as rejectRequest,
} from '@/routes/groups/join-requests';
import {
    store as submissionStore,
    submit as submissionSubmit,
    uploadResponse,
} from '@/routes/groups/submissions';
import { edit as editProfile } from '@/routes/profile';
import { edit as editSecurity } from '@/routes/security';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});

// ─── Types ────────────────────────────────────────────────────────────────────

interface Member {
    id: number;
    name: string;
    email: string;
    nim?: string;
    major?: string;
}

interface JoinRequest {
    id: number;
    user: Member;
    status: string;
    created_at: string;
}

interface Submission {
    id: number;
    group_id: number;
    company_name: string;
    company_address: string;
    company_contact: string;
    division: string;
    field_of_interest: string;
    start_date: string;
    end_date: string;
    supporting_document: string | null;
    company_response_path?: string | null;
    status: string;
}

interface Timeline {
    id: number;
    title: string;
    created_at: string;
}

interface Group {
    id: number;
    code: string;
    status: string;
    leader_id: number;
    leader: Member;
    banner_url?: string | null;
    memberships: Array<{ user: Member }>;
    join_requests: JoinRequest[];
    active_submission?: Submission | null;
    timelines?: Timeline[];
}

interface PendingJoinRequest {
    id: number;
    status: string;
    group: {
        id: number;
        code: string;
        status: string;
        leader: Member;
    };
}

// ─── Props ────────────────────────────────────────────────────────────────────

const props = defineProps<{
    group: Group | null;
    pendingJoinRequests: PendingJoinRequest[];
}>();

// ─── State ────────────────────────────────────────────────────────────────────

const joinCode = ref('');
const showCreateConfirm = ref(false);
const showDisbandConfirm = ref(false);
const showLeaveConfirm = ref(false);
const showJoinConfirmDialog = ref(false);
const joinConfirmCode = ref('');
const isProcessing = ref(false);
const inviteGroup = ref<any>(null);
const isFetchingInviteGroup = ref(false);
const inviteGroupError = ref('');
const http = useHttp();
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
        under_review: 'Sedang Ditinjau',
        letter_published: 'Surat Terbit',
        applying: 'Menunggu Balasan',
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
        under_review: Clock,
        letter_published: FileCheck,
        applying: Clock,
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
        under_review: 'text-orange-500',
        letter_published: 'text-green-500',
        applying: 'text-yellow-500',
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
        under_review: 'Pengajuan sedang ditinjau tim prodi.',
        letter_published: `Surat terbit, antar ke ${company}.`,
        applying: `${company} sedang meninjau permohonan.`,
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
            props.group?.status === 'applying')
    );
});

const isSubmissionEditable = computed(() => {
    return (
        isLeader.value &&
        (props.group?.status === 'forming' ||
            props.group?.status === 'company_rejected')
    );
});

// ─── Handlers ─────────────────────────────────────────────────────────────────

function createGroup() {
    if (isLocked.value) {
        return;
    }

    isProcessing.value = true;
    router.post(
        groupStore.url(),
        {},
        {
            onFinish: () => {
                isProcessing.value = false;
                showCreateConfirm.value = false;
            },
        },
    );
}

function sendJoinRequest() {
    if (!joinCode.value.trim() || isLocked.value) {
        return;
    }

    isProcessing.value = true;
    router.post(
        groupJoin.url(),
        { code: joinCode.value.trim().toUpperCase() },
        {
            onFinish: () => {
                isProcessing.value = false;
                joinCode.value = '';
            },
        },
    );
}

function confirmJoinFromLink() {
    isProcessing.value = true;
    router.post(
        groupJoin.url(),
        { code: joinConfirmCode.value },
        {
            onFinish: () => {
                isProcessing.value = false;
                showJoinConfirmDialog.value = false;
            },
        },
    );
}

function leaveGroup() {
    isProcessing.value = true;
    router.post(
        groupLeave.url(),
        {},
        {
            onFinish: () => {
                isProcessing.value = false;
                showLeaveConfirm.value = false;
            },
        },
    );
}

function disbandGroup() {
    if (!props.group) {
        return;
    }

    isProcessing.value = true;
    router.delete(groupDestroy.url(props.group.id), {
        onFinish: () => {
            isProcessing.value = false;
            showDisbandConfirm.value = false;
        },
    });
}

function cancelJoinRequest(requestId: number) {
    router.delete(cancelRequest.url(requestId));
}

function approveJoinRequest(requestId: number) {
    router.post(approveRequest.url(requestId));
}

function rejectJoinRequest(requestId: number) {
    router.post(rejectRequest.url(requestId));
}

// ─── Submission Form Setup ───────────────────────────────────────────────────

const showSubmitConfirm = ref(false);

const submissionForm = useForm({
    company_name: props.group?.active_submission?.company_name ?? '',
    company_address: props.group?.active_submission?.company_address ?? '',
    company_contact: props.group?.active_submission?.company_contact ?? '',
    division: props.group?.active_submission?.division ?? '',
    field_of_interest: props.group?.active_submission?.field_of_interest ?? '',
    start_date: props.group?.active_submission?.start_date
        ? props.group.active_submission.start_date.substring(0, 10)
        : '',
    end_date: props.group?.active_submission?.end_date
        ? props.group.active_submission.end_date.substring(0, 10)
        : '',
});
watch(
    () => props.group?.active_submission,
    (newSub) => {
        submissionForm.company_name = newSub?.company_name ?? '';
        submissionForm.company_address = newSub?.company_address ?? '';
        submissionForm.company_contact = newSub?.company_contact ?? '';
        submissionForm.division = newSub?.division ?? '';
        submissionForm.field_of_interest = newSub?.field_of_interest ?? '';
        submissionForm.start_date = newSub?.start_date
            ? newSub.start_date.substring(0, 10)
            : '';
        submissionForm.end_date = newSub?.end_date
            ? newSub.end_date.substring(0, 10)
            : '';
    },
    { deep: true },
);

const dateFormatter = new DateFormatter('id-ID', {
    dateStyle: 'medium',
});

const startDateValue = computed({
    get: () => {
        return submissionForm.start_date
            ? parseDate(submissionForm.start_date)
            : undefined;
    },
    set: (val) => {
        submissionForm.start_date = val ? val.toString() : '';
    },
});

const endDateValue = computed({
    get: () => {
        return submissionForm.end_date
            ? parseDate(submissionForm.end_date)
            : undefined;
    },
    set: (val) => {
        submissionForm.end_date = val ? val.toString() : '';
    },
});

function saveSubmissionDraft() {
    isProcessing.value = true;
    submissionForm.post(submissionStore.url(), {
        preserveScroll: true,
        onFinish: () => {
            isProcessing.value = false;
        },
    });
}

function submitSubmissionProposal() {
    isProcessing.value = true;
    submissionForm.post(submissionSubmit.url(), {
        preserveScroll: true,
        onSuccess: () => {
            showSubmitConfirm.value = false;
        },
    });
}

// ─── Company Response Upload Setup ─────────────────────────────────────────────

const responseUploadForm = useForm({
    file: null as File | null,
});

const fileInput = ref<HTMLInputElement | null>(null);

function triggerUpload() {
    fileInput.value?.click();
}

function handleResponseUpload(event: Event) {
    const target = event.target as HTMLInputElement;

    if (target.files && target.files[0] && props.group?.active_submission) {
        const file = target.files[0];
        responseUploadForm.file = file;
        responseUploadForm.post(
            uploadResponse.url(props.group.active_submission.id),
            {
                forceFormData: true,
                preserveScroll: true,
                onSuccess: () => {
                    responseUploadForm.reset();

                    if (fileInput.value) {
                        fileInput.value.value = '';
                    }
                },
            },
        );
    }
}

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
        isFetchingInviteGroup.value = true;
        inviteGroupError.value = '';
        inviteGroup.value = null;

        http.get(groupByCode.url(code), {
            onSuccess: (response: any) => {
                inviteGroup.value = response;
                isFetchingInviteGroup.value = false;
            },
            onError: () => {
                inviteGroupError.value =
                    'Gagal memuat detail kelompok. Silakan periksa kembali kode undangan Anda.';
                isFetchingInviteGroup.value = false;
            },
            onHttpException: () => {
                inviteGroupError.value =
                    'Gagal memuat detail kelompok. Silakan periksa kembali kode undangan Anda.';
                isFetchingInviteGroup.value = false;

                return true;
            },
            onNetworkError: () => {
                inviteGroupError.value =
                    'Koneksi jaringan terputus. Silakan coba lagi.';
                isFetchingInviteGroup.value = false;

                return true;
            },
        });
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
        <div v-if="!group" class="space-y-6 p-4 pt-6 md:p-8">
            <div v-if="isLocked" class="flex justify-center py-8">
                <Card
                    class="w-full max-w-2xl border-destructive/30 bg-destructive/5"
                >
                    <CardHeader class="pb-3 text-center">
                        <div
                            class="mx-auto mb-2 flex h-12 w-12 items-center justify-center rounded-full bg-destructive/10 p-3 text-destructive"
                        >
                            <Lock class="h-6 w-6" />
                        </div>
                        <CardTitle class="text-xl font-bold"
                            >Akses Terkunci</CardTitle
                        >
                        <CardDescription>
                            Anda belum memenuhi persyaratan untuk dapat
                            mengakses fitur kelompok magang.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-6 text-center">
                        <p
                            class="text-sm leading-relaxed text-muted-foreground"
                        >
                            Untuk dapat bergabung atau membuat kelompok magang
                            baru, Anda wajib:
                        </p>
                        <div
                            class="flex flex-col items-center gap-3 text-sm font-medium"
                        >
                            <div class="flex items-center gap-2">
                                <component
                                    :is="
                                        page.props.auth.requirements
                                            ?.password_changed
                                            ? CheckCircle2
                                            : XCircle
                                    "
                                    class="h-5 w-5"
                                    :class="
                                        page.props.auth.requirements
                                            ?.password_changed
                                            ? 'text-green-500'
                                            : 'text-destructive'
                                    "
                                />
                                <span>Mengubah password default</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <component
                                    :is="
                                        page.props.auth.requirements
                                            ?.profile_completed
                                            ? CheckCircle2
                                            : XCircle
                                    "
                                    class="h-5 w-5"
                                    :class="
                                        page.props.auth.requirements
                                            ?.profile_completed
                                            ? 'text-green-500'
                                            : 'text-destructive'
                                    "
                                />
                                <span>Melengkapi data biodata mahasiswa</span>
                            </div>
                        </div>
                        <div
                            class="flex flex-col justify-center gap-3 pt-4 sm:flex-row"
                        >
                            <Link
                                v-if="
                                    !page.props.auth.requirements
                                        ?.password_changed
                                "
                                :href="editSecurity()"
                            >
                                <Button
                                    variant="destructive"
                                    id="btn-lock-password"
                                    >Ubah Password</Button
                                >
                            </Link>
                            <Link
                                v-if="
                                    !page.props.auth.requirements
                                        ?.profile_completed
                                "
                                :href="editProfile()"
                            >
                                <Button
                                    variant="outline"
                                    class="border-primary/30 text-primary"
                                    id="btn-lock-profile"
                                    >Lengkapi Biodata</Button
                                >
                            </Link>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div v-else class="grid items-start gap-8 lg:grid-cols-2">
                <!-- Left Column: Actions -->
                <div class="space-y-8">
                    <div class="space-y-2">
                        <h1 class="text-3xl font-bold tracking-tight">
                            Selamat Datang!
                        </h1>
                        <p class="text-base text-muted-foreground">
                            Buat kelompok magang baru atau bergabung ke kelompok
                            yang sudah ada.
                        </p>
                    </div>

                    <!-- Create Group Card -->
                    <Card
                        class="border-2 border-primary/20 transition-colors hover:border-primary/40"
                    >
                        <CardContent class="space-y-4 p-6">
                            <div class="flex items-center gap-3">
                                <div
                                    class="rounded-xl bg-primary/10 p-2.5 text-primary"
                                >
                                    <Plus class="h-5 w-5" />
                                </div>
                                <div>
                                    <h2 class="text-base font-semibold">
                                        Buat Kelompok Baru
                                    </h2>
                                    <p class="text-xs text-muted-foreground">
                                        Kamu akan menjadi ketua kelompok
                                    </p>
                                </div>
                            </div>
                            <Button
                                id="btn-create-group"
                                class="w-full"
                                @click="createGroup"
                            >
                                <Plus class="mr-2 h-4 w-4" />
                                Buat Kelompok Magang
                            </Button>
                        </CardContent>
                    </Card>

                    <!-- Join Group Card -->
                    <Card
                        class="border-2 border-border transition-colors hover:border-primary/20"
                    >
                        <CardContent class="space-y-4 p-6">
                            <div class="flex items-center gap-3">
                                <div
                                    class="rounded-xl bg-secondary p-2.5 text-secondary-foreground"
                                >
                                    <LogIn class="h-5 w-5" />
                                </div>
                                <div>
                                    <h2 class="text-base font-semibold">
                                        Gabung ke Kelompok
                                    </h2>
                                    <p class="text-xs text-muted-foreground">
                                        Masukkan kode kelompok dari teman kamu
                                    </p>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <div class="flex-1 space-y-1.5">
                                    <Label for="join-code" class="sr-only"
                                        >Kode Kelompok</Label
                                    >
                                    <Input
                                        id="join-code"
                                        v-model="joinCode"
                                        placeholder="Contoh: ABCDE12345"
                                        class="font-mono tracking-widest uppercase"
                                        maxlength="10"
                                        @keydown.enter="sendJoinRequest"
                                    />
                                </div>
                                <Button
                                    id="btn-join-group"
                                    variant="outline"
                                    @click="sendJoinRequest"
                                    :disabled="!joinCode.trim() || isProcessing"
                                >
                                    <Spinner
                                        v-if="isProcessing"
                                        class="h-4 w-4 animate-spin"
                                    />
                                    <span v-else>Kirim</span>
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Right Column: Info -->
                <Card
                    v-if="pendingJoinRequests.length === 0"
                    class="border-primary/20 bg-linear-to-br from-primary/5 to-primary/10"
                >
                    <CardContent class="space-y-6 p-8">
                        <div class="space-y-1">
                            <h3 class="text-lg font-semibold">
                                Cara Bergabung ke Kelompok
                            </h3>
                            <p class="text-sm text-muted-foreground">
                                Ikuti langkah-langkah berikut:
                            </p>
                        </div>
                        <ol class="space-y-4 text-sm">
                            <li class="flex gap-3">
                                <span
                                    class="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-primary/15 text-xs font-bold text-primary"
                                    >1</span
                                >
                                <span class="pt-0.5 text-muted-foreground"
                                    >Minta ketua kelompok untuk berbagi
                                    <strong class="text-foreground"
                                        >kode kelompok</strong
                                    >
                                    atau link undangan.</span
                                >
                            </li>
                            <li class="flex gap-3">
                                <span
                                    class="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-primary/15 text-xs font-bold text-primary"
                                    >2</span
                                >
                                <span class="pt-0.5 text-muted-foreground"
                                    >Masukkan kode di kolom "Gabung ke Kelompok"
                                    dan klik
                                    <strong class="text-foreground"
                                        >Kirim</strong
                                    >.</span
                                >
                            </li>
                            <li class="flex gap-3">
                                <span
                                    class="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-primary/15 text-xs font-bold text-primary"
                                    >3</span
                                >
                                <span class="pt-0.5 text-muted-foreground"
                                    >Tunggu ketua kelompok
                                    <strong class="text-foreground"
                                        >menyetujui permintaanmu</strong
                                    >.</span
                                >
                            </li>
                        </ol>
                        <div class="border-t border-primary/10 pt-2">
                            <p class="text-xs text-muted-foreground">
                                Setiap mahasiswa hanya dapat bergabung ke
                                <strong>satu kelompok aktif</strong>.
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <Card v-else class="border-border">
                    <CardHeader class="space-y-1">
                        <CardTitle class="text-lg"
                            >Permintaan Bergabung Terkirim</CardTitle
                        >
                        <CardDescription>
                            Permintaanmu sedang menunggu persetujuan dari ketua
                            kelompok.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div
                            v-for="req in pendingJoinRequests"
                            :key="req.id"
                            class="flex flex-col gap-4 rounded-xl border border-border/70 bg-muted/20 p-4 sm:flex-row sm:items-center sm:justify-between"
                        >
                            <div class="flex items-center gap-3">
                                <div class="rounded-lg bg-yellow-500/10 p-2">
                                    <Clock
                                        class="h-5 w-5 text-yellow-600 dark:text-yellow-400"
                                    />
                                </div>
                                <div>
                                    <p class="text-sm font-semibold">
                                        Kelompok
                                        <span class="font-mono">{{
                                            req.group.code
                                        }}</span>
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        Ketua: {{ req.group.leader.name }}
                                    </p>
                                </div>
                            </div>
                            <Button
                                variant="ghost"
                                size="sm"
                                class="justify-start text-destructive hover:bg-destructive/10 hover:text-destructive sm:justify-center"
                                @click="cancelJoinRequest(req.id)"
                            >
                                <UserX class="mr-1.5 h-4 w-4" />
                                Batalkan
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>

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
                    class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/10 to-transparent"
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
                            Kelola anggota, pengajuan, dan perkembangan kelompok
                            magang.
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
                                <UserMinus class="h-3.5 w-3.5" />
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
                            <div class="mt-0.5 flex-shrink-0">
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
                                class="mt-0.5 h-4 w-4 flex-shrink-0 text-muted-foreground/50 transition-transform group-hover/status:translate-x-0.5"
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
                :default-value="activeGroupTab"
                @update:model-value="
                    (val) => (activeGroupTab = val as typeof activeGroupTab)
                "
            >
                <div
                    class="sticky top-16 z-20 border-b border-border/60 bg-background/95 backdrop-blur-sm"
                >
                    <div class="px-4 md:px-8">
                        <TabsList
                            class="flex h-auto scrollbar-none gap-0 overflow-x-auto rounded-none bg-transparent p-0"
                            style="
                                -webkit-overflow-scrolling: touch;
                                white-space: nowrap;
                            "
                        >
                            <TabsTrigger
                                value="members"
                                class="relative shrink-0 rounded-none border-b-2 border-transparent px-4 py-3 text-sm font-medium text-muted-foreground transition-all hover:text-foreground data-[state=active]:border-primary data-[state=active]:text-foreground data-[state=active]:shadow-none"
                            >
                                Anggota
                                <span
                                    class="ml-1.5 inline-flex items-center justify-center rounded-full bg-muted px-1.5 py-0.5 text-[10px] leading-none font-semibold"
                                >
                                    {{ group.memberships.length }}
                                </span>
                            </TabsTrigger>
                            <TabsTrigger
                                value="requests"
                                class="relative shrink-0 rounded-none border-b-2 border-transparent px-4 py-3 text-sm font-medium text-muted-foreground transition-all hover:text-foreground data-[state=active]:border-primary data-[state=active]:text-foreground data-[state=active]:shadow-none"
                            >
                                Permintaan
                                <span
                                    v-if="group.join_requests.length > 0"
                                    class="ml-1.5 inline-flex items-center justify-center rounded-full bg-primary px-1.5 py-0.5 text-[10px] leading-none font-semibold text-primary-foreground"
                                >
                                    {{ group.join_requests.length }}
                                </span>
                            </TabsTrigger>
                            <TabsTrigger
                                value="submissions"
                                class="relative shrink-0 rounded-none border-b-2 border-transparent px-4 py-3 text-sm font-medium text-muted-foreground transition-all hover:text-foreground data-[state=active]:border-primary data-[state=active]:text-foreground data-[state=active]:shadow-none"
                            >
                                Pengajuan
                            </TabsTrigger>
                            <TabsTrigger
                                value="history"
                                class="relative shrink-0 rounded-none border-b-2 border-transparent px-4 py-3 text-sm font-medium text-muted-foreground transition-all hover:text-foreground data-[state=active]:border-primary data-[state=active]:text-foreground data-[state=active]:shadow-none"
                            >
                                Riwayat
                            </TabsTrigger>
                            <TabsTrigger
                                v-if="showResponseTab"
                                value="response"
                                class="relative shrink-0 rounded-none border-b-2 border-transparent px-4 py-3 text-sm font-medium text-muted-foreground transition-all hover:text-foreground data-[state=active]:border-primary data-[state=active]:text-foreground data-[state=active]:shadow-none"
                            >
                                Surat Balasan
                            </TabsTrigger>
                        </TabsList>
                    </div>
                </div>

                <!-- ── Tab Content ── -->
                <div class="px-4 py-6 md:px-8">
                    <!-- ─ Members Tab ─ -->
                    <TabsContent value="members" class="mt-0 space-y-4">
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
                                        {{
                                            membership.user.name
                                                .charAt(0)
                                                .toUpperCase()
                                        }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium">
                                            {{ membership.user.name }}
                                        </p>
                                        <p
                                            class="text-xs text-muted-foreground"
                                        >
                                            {{
                                                membership.user.nim ??
                                                membership.user.email
                                            }}
                                        </p>
                                    </div>
                                </div>
                                <Badge
                                    v-if="
                                        membership.user.id === group.leader_id
                                    "
                                    variant="secondary"
                                    class="gap-1"
                                >
                                    <Crown class="h-3 w-3" />
                                    Ketua
                                </Badge>
                            </div>
                        </div>
                    </TabsContent>

                    <!-- ─ Requests Tab ─ -->
                    <TabsContent value="requests" class="mt-0 space-y-4">
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
                                        {{
                                            req.user.name
                                                .charAt(0)
                                                .toUpperCase()
                                        }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium">
                                            {{ req.user.name }}
                                        </p>
                                        <p
                                            class="text-xs text-muted-foreground"
                                        >
                                            {{ req.user.nim ?? req.user.email }}
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
                                        <UserCheck class="h-3.5 w-3.5" />
                                        Terima
                                    </Button>
                                    <Button
                                        size="sm"
                                        variant="ghost"
                                        class="gap-1.5 text-destructive hover:bg-destructive/10 hover:text-destructive"
                                        @click="rejectJoinRequest(req.id)"
                                    >
                                        <UserX class="h-3.5 w-3.5" />
                                        Tolak
                                    </Button>
                                </div>
                            </div>
                        </div>

                        <div
                            v-else-if="isLeader"
                            class="rounded-xl border border-dashed border-border/80 bg-muted/20 px-4 py-10 text-center"
                        >
                            <p class="text-sm font-medium">
                                Belum ada permintaan aktif
                            </p>
                            <p class="mt-1 text-xs text-muted-foreground">
                                Permintaan bergabung baru akan muncul di tab
                                ini.
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
                                Kamu tetap bisa memantau anggota melalui tab
                                Anggota.
                            </p>
                        </div>
                    </TabsContent>

                    <!-- ─ Submissions Tab ─ -->
                    <TabsContent value="submissions" class="mt-0 space-y-6">
                        <!-- Status Banner -->
                        <div
                            v-if="
                                group.status !== 'forming' &&
                                group.status !== 'company_rejected'
                            "
                            class="flex gap-3 rounded-xl border border-primary/20 bg-primary/5 p-4 text-sm text-foreground"
                        >
                            <AlertCircle
                                class="h-5 w-5 flex-shrink-0 text-primary"
                            />
                            <div>
                                <h4 class="mb-1 font-semibold">
                                    Status Pengajuan Magang:
                                    {{ groupStatusLabel }}
                                </h4>
                                <p class="text-xs text-muted-foreground">
                                    {{ statusDescription }}
                                </p>
                            </div>
                        </div>
                        <div
                            v-else
                            class="flex gap-3 rounded-xl border border-yellow-500/20 bg-yellow-500/5 p-4 text-sm text-foreground"
                        >
                            <AlertCircle
                                class="h-5 w-5 flex-shrink-0 text-yellow-600 dark:text-yellow-500"
                            />
                            <div>
                                <h4 class="mb-1 font-semibold" v-if="isLeader">
                                    Persiapan Pengajuan Magang
                                </h4>
                                <h4 class="mb-1 font-semibold" v-else>
                                    Menunggu Pengajuan Ketua Kelompok
                                </h4>
                                <p
                                    class="text-xs text-muted-foreground"
                                    v-if="isLeader"
                                >
                                    Isi data instansi/perusahaan tujuan magang
                                    di bawah ini. Setelah diajukan, data dan
                                    keanggotaan akan <strong>dikunci</strong>.
                                </p>
                                <p class="text-xs text-muted-foreground" v-else>
                                    Draf data pengajuan sedang diisi oleh ketua
                                    kelompok ({{ group.leader.name }}).
                                </p>
                            </div>
                        </div>

                        <!-- Form -->
                        <form @submit.prevent class="space-y-6">
                            <div class="grid gap-4 sm:grid-cols-3">
                                <div class="space-y-1.5">
                                    <Label
                                        for="company_name"
                                        class="flex items-center gap-1.5 text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                                    >
                                        <Building2 class="h-3.5 w-3.5" />
                                        Nama Perusahaan / Instansi
                                    </Label>
                                    <Input
                                        id="company_name"
                                        v-model="submissionForm.company_name"
                                        placeholder="Contoh: PT Teknologi Nusantara"
                                        :disabled="!isSubmissionEditable"
                                    />
                                    <span
                                        v-if="
                                            submissionForm.errors.company_name
                                        "
                                        class="text-xs text-destructive"
                                    >
                                        {{ submissionForm.errors.company_name }}
                                    </span>
                                </div>

                                <div class="space-y-1.5">
                                    <Label
                                        for="field_of_interest"
                                        class="flex items-center gap-1.5 text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                                    >
                                        <FileText class="h-3.5 w-3.5" />
                                        Bidang yang Diminati
                                    </Label>
                                    <Input
                                        id="field_of_interest"
                                        v-model="
                                            submissionForm.field_of_interest
                                        "
                                        placeholder="Contoh: Web Developer, UI/UX"
                                        :disabled="!isSubmissionEditable"
                                    />
                                    <span
                                        v-if="
                                            submissionForm.errors
                                                .field_of_interest
                                        "
                                        class="text-xs text-destructive"
                                    >
                                        {{
                                            submissionForm.errors
                                                .field_of_interest
                                        }}
                                    </span>
                                </div>

                                <div class="space-y-1.5">
                                    <Label
                                        for="division"
                                        class="flex items-center gap-1.5 text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                                    >
                                        <Building2 class="h-3.5 w-3.5" />
                                        Divisi Pekerjaan (Opsional)
                                    </Label>
                                    <Input
                                        id="division"
                                        v-model="submissionForm.division"
                                        placeholder="Contoh: Frontend, Backend"
                                        :disabled="!isSubmissionEditable"
                                    />
                                    <span
                                        v-if="submissionForm.errors.division"
                                        class="text-xs text-destructive"
                                    >
                                        {{ submissionForm.errors.division }}
                                    </span>
                                </div>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-3">
                                <div class="space-y-1.5">
                                    <Label
                                        for="company_contact"
                                        class="flex items-center gap-1.5 text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                                    >
                                        <Phone class="h-3.5 w-3.5" />
                                        Kontak Instansi (No. Telp / Email)
                                    </Label>
                                    <Input
                                        id="company_contact"
                                        v-model="submissionForm.company_contact"
                                        placeholder="Contoh: hr@company.com / 021-xxxxxx"
                                        :disabled="!isSubmissionEditable"
                                    />
                                    <span
                                        v-if="
                                            submissionForm.errors
                                                .company_contact
                                        "
                                        class="text-xs text-destructive"
                                    >
                                        {{
                                            submissionForm.errors
                                                .company_contact
                                        }}
                                    </span>
                                </div>

                                <div class="space-y-1.5">
                                    <Label
                                        for="start_date"
                                        class="flex items-center gap-1.5 text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                                    >
                                        <Calendar class="h-3.5 w-3.5" />
                                        Tanggal Mulai
                                    </Label>
                                    <Popover>
                                        <PopoverTrigger as-child>
                                            <Button
                                                id="start_date"
                                                variant="outline"
                                                role="combobox"
                                                :class="
                                                    cn(
                                                        'h-10 w-full justify-start text-left font-normal',
                                                        !submissionForm.start_date &&
                                                            'text-muted-foreground',
                                                    )
                                                "
                                                :disabled="
                                                    !isSubmissionEditable
                                                "
                                            >
                                                <Calendar
                                                    class="mr-2 h-4 w-4 text-muted-foreground"
                                                />
                                                <span>
                                                    {{
                                                        submissionForm.start_date
                                                            ? dateFormatter.format(
                                                                  startDateValue!.toDate(
                                                                      getLocalTimeZone(),
                                                                  ),
                                                              )
                                                            : 'Pilih tanggal...'
                                                    }}
                                                </span>
                                            </Button>
                                        </PopoverTrigger>
                                        <PopoverContent
                                            class="w-auto p-0"
                                            align="start"
                                        >
                                            <CalendarComponent
                                                v-model="startDateValue"
                                                initial-focus
                                            />
                                        </PopoverContent>
                                    </Popover>
                                    <span
                                        v-if="submissionForm.errors.start_date"
                                        class="text-xs text-destructive"
                                    >
                                        {{ submissionForm.errors.start_date }}
                                    </span>
                                </div>

                                <div class="space-y-1.5">
                                    <Label
                                        for="end_date"
                                        class="flex items-center gap-1.5 text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                                    >
                                        <Calendar class="h-3.5 w-3.5" />
                                        Tanggal Selesai
                                    </Label>
                                    <Popover>
                                        <PopoverTrigger as-child>
                                            <Button
                                                id="end_date"
                                                variant="outline"
                                                role="combobox"
                                                :class="
                                                    cn(
                                                        'h-10 w-full justify-start text-left font-normal',
                                                        !submissionForm.end_date &&
                                                            'text-muted-foreground',
                                                    )
                                                "
                                                :disabled="
                                                    !isSubmissionEditable
                                                "
                                            >
                                                <Calendar
                                                    class="mr-2 h-4 w-4 text-muted-foreground"
                                                />
                                                <span>
                                                    {{
                                                        submissionForm.end_date
                                                            ? dateFormatter.format(
                                                                  endDateValue!.toDate(
                                                                      getLocalTimeZone(),
                                                                  ),
                                                              )
                                                            : 'Pilih tanggal...'
                                                    }}
                                                </span>
                                            </Button>
                                        </PopoverTrigger>
                                        <PopoverContent
                                            class="w-auto p-0"
                                            align="start"
                                        >
                                            <CalendarComponent
                                                v-model="endDateValue"
                                                initial-focus
                                            />
                                        </PopoverContent>
                                    </Popover>
                                    <span
                                        v-if="submissionForm.errors.end_date"
                                        class="text-xs text-destructive"
                                    >
                                        {{ submissionForm.errors.end_date }}
                                    </span>
                                </div>
                            </div>

                            <div class="space-y-1.5">
                                <Label
                                    for="company_address"
                                    class="flex items-center gap-1.5 text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                                >
                                    <MapPin class="h-3.5 w-3.5" />
                                    Alamat Lengkap Perusahaan
                                </Label>
                                <textarea
                                    id="company_address"
                                    v-model="submissionForm.company_address"
                                    placeholder="Contoh: Jl. Jenderal Sudirman No. 12, Jakarta Selatan"
                                    rows="3"
                                    class="flex min-h-[80px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                                    :disabled="!isSubmissionEditable"
                                ></textarea>
                                <span
                                    v-if="submissionForm.errors.company_address"
                                    class="text-xs text-destructive"
                                >
                                    {{ submissionForm.errors.company_address }}
                                </span>
                            </div>

                            <div
                                v-if="isSubmissionEditable"
                                class="flex justify-end gap-3 border-t border-border pt-4"
                            >
                                <Button
                                    type="button"
                                    variant="outline"
                                    @click="saveSubmissionDraft"
                                    :disabled="isProcessing"
                                    id="btn-save-draft"
                                >
                                    <Spinner
                                        v-if="isProcessing"
                                        class="mr-2 h-4 w-4 animate-spin"
                                    />
                                    <Save v-else class="mr-2 h-4 w-4" />
                                    Simpan Draf
                                </Button>
                                <Button
                                    type="button"
                                    @click="showSubmitConfirm = true"
                                    :disabled="isProcessing"
                                    id="btn-submit-proposal"
                                >
                                    <Send class="mr-2 h-4 w-4" />
                                    Ajukan Magang
                                </Button>
                            </div>
                        </form>
                    </TabsContent>

                    <!-- ─ History Tab ─ -->
                    <TabsContent value="history" class="mt-0 space-y-6">
                        <div
                            class="flex items-center gap-2 text-sm font-semibold text-muted-foreground"
                        >
                            <Clock class="h-4 w-4" />
                            Riwayat Progres Kelompok
                        </div>

                        <div
                            v-if="
                                group?.timelines && group.timelines.length > 0
                            "
                            class="relative pl-6 after:absolute after:inset-y-0 after:left-2.5 after:w-0.5 after:bg-border/60"
                        >
                            <div
                                v-for="timeline in group.timelines"
                                :key="timeline.id"
                                class="relative pb-6 last:pb-0"
                            >
                                <!-- Timeline icon/dot -->
                                <div
                                    class="absolute top-1 -left-[22px] flex h-5 w-5 items-center justify-center rounded-full border border-primary bg-background text-primary"
                                >
                                    <div
                                        class="h-2 w-2 rounded-full bg-primary"
                                    />
                                </div>
                                <div class="space-y-1">
                                    <p
                                        class="text-sm font-medium whitespace-pre-line text-foreground"
                                    >
                                        {{ timeline.title }}
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        {{
                                            new Date(
                                                timeline.created_at,
                                            ).toLocaleString('id-ID', {
                                                dateStyle: 'medium',
                                                timeStyle: 'short',
                                            })
                                        }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div
                            v-else
                            class="rounded-xl border border-dashed border-border/80 bg-muted/20 px-4 py-12 text-center"
                        >
                            <p class="text-sm font-medium">
                                Belum ada riwayat aktivitas
                            </p>
                            <p class="mt-1 text-xs text-muted-foreground">
                                Aktivitas kelompok magangmu akan tercatat di
                                sini.
                            </p>
                        </div>
                    </TabsContent>

                    <!-- ─ Company Response Tab ─ -->
                    <TabsContent
                        v-if="showResponseTab"
                        value="response"
                        class="mt-0 space-y-4"
                    >
                        <div
                            class="flex items-start gap-3 rounded-xl border border-green-200 bg-green-50/30 p-4 dark:border-green-900 dark:bg-green-950/20"
                        >
                            <div
                                class="rounded-full bg-green-100 p-2 text-green-700 dark:bg-green-900 dark:text-green-300"
                            >
                                <FileText class="h-5 w-5" />
                            </div>
                            <div>
                                <p
                                    class="text-sm font-semibold text-green-900 dark:text-green-100"
                                >
                                    Surat Permohonan Magang Diterbitkan
                                </p>
                                <p
                                    class="mt-0.5 text-xs text-green-700/80 dark:text-green-300/80"
                                >
                                    Unggah surat balasan resmi setelah disetujui
                                    perusahaan.
                                </p>
                            </div>
                        </div>

                        <p
                            class="text-sm leading-relaxed text-muted-foreground"
                        >
                            Surat permohonan untuk kelompok Anda telah
                            diterbitkan. Antar ke
                            <strong>{{
                                group.active_submission?.company_name ??
                                'perusahaan tujuan'
                            }}</strong>
                            dan upload surat balasannya di sini.
                        </p>

                        <!-- Success if already uploaded -->
                        <div
                            v-if="
                                group.active_submission?.company_response_path
                            "
                            class="flex items-center gap-2 rounded-lg border border-green-200/50 bg-green-100/50 p-3 text-sm text-green-800 dark:border-green-800/50 dark:bg-green-900/30 dark:text-green-200"
                        >
                            <CheckCircle2
                                class="h-4 w-4 flex-shrink-0 text-green-600 dark:text-green-400"
                            />
                            <span
                                >Surat balasan perusahaan telah berhasil
                                diunggah.</span
                            >
                        </div>

                        <div class="flex flex-wrap gap-3">
                            <input
                                type="file"
                                ref="fileInput"
                                class="hidden"
                                accept=".pdf,.docx,.png,.jpg,.jpeg"
                                @change="handleResponseUpload"
                            />
                            <Button
                                variant="outline"
                                class="border-green-600 text-green-700 hover:bg-green-50 dark:border-green-700 dark:text-green-400 dark:hover:bg-green-950"
                                @click="triggerUpload"
                                :disabled="responseUploadForm.processing"
                                id="btn-upload-response"
                            >
                                <Spinner
                                    v-if="responseUploadForm.processing"
                                    class="mr-2 h-4 w-4 animate-spin"
                                />
                                <Upload v-else class="mr-2 h-4 w-4" />
                                {{
                                    group.active_submission
                                        ?.company_response_path
                                        ? 'Unggah Ulang Surat Balasan'
                                        : 'Unggah Surat Balasan'
                                }}
                            </Button>
                        </div>

                        <p
                            v-if="responseUploadForm.errors.file"
                            class="text-xs font-medium text-destructive"
                        >
                            {{ responseUploadForm.errors.file }}
                        </p>
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

        <!-- Create Group Confirm -->
        <Dialog v-model:open="showCreateConfirm">
            <DialogContent class="sm:max-w-[400px]">
                <DialogHeader>
                    <DialogTitle>Buat Kelompok Magang</DialogTitle>
                    <DialogDescription>
                        Kamu akan membuat kelompok magang baru dan menjadi
                        ketuanya. Kode unik akan dibuat secara otomatis untuk
                        anggota lain bergabung.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button variant="outline" @click="showCreateConfirm = false"
                        >Batal</Button
                    >
                    <Button
                        id="btn-confirm-create"
                        @click="createGroup"
                        :disabled="isProcessing"
                    >
                        <Spinner
                            v-if="isProcessing"
                            class="mr-2 h-4 w-4 animate-spin"
                        />
                        Buat Kelompok
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Join from Link Confirm -->
        <Dialog v-model:open="showJoinConfirmDialog">
            <DialogContent class="sm:max-w-[400px]">
                <DialogHeader>
                    <DialogTitle>Bergabung ke Kelompok</DialogTitle>
                    <DialogDescription v-if="!inviteGroupError">
                        Kamu diundang untuk bergabung ke kelompok magang
                        berikut.
                    </DialogDescription>
                </DialogHeader>

                <!-- Loading State -->
                <div
                    v-if="isFetchingInviteGroup"
                    class="flex flex-col items-center justify-center py-8"
                >
                    <Spinner class="h-8 w-8 animate-spin text-primary" />
                    <p class="mt-2 text-xs text-muted-foreground">
                        Memuat informasi kelompok...
                    </p>
                </div>

                <!-- Error State -->
                <div v-else-if="inviteGroupError" class="space-y-4 py-2">
                    <div
                        class="flex items-center gap-3 rounded-lg border border-destructive/25 bg-destructive/5 p-4 text-sm text-destructive"
                    >
                        <XCircle class="h-5 w-5 flex-shrink-0" />
                        <p>{{ inviteGroupError }}</p>
                    </div>
                </div>

                <!-- Loaded State -->
                <div v-else-if="inviteGroup" class="space-y-4">
                    <!-- Banner -->
                    <div
                        class="relative h-32 w-full overflow-hidden rounded-xl border border-border/80"
                    >
                        <img
                            :src="
                                inviteGroup.banner_url ??
                                '/assets/images/default-company-background.png'
                            "
                            alt="Banner Kelompok"
                            class="h-full w-full object-cover"
                        />
                        <div
                            class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"
                        />
                        <div class="absolute bottom-3 left-3 text-white">
                            <span
                                class="rounded-md bg-black/45 px-2 py-0.5 font-mono text-xs backdrop-blur-xs"
                            >
                                Kode: {{ inviteGroup.code }}
                            </span>
                        </div>
                    </div>

                    <!-- Info List -->
                    <div
                        class="space-y-3 rounded-xl border border-border/80 bg-muted/20 p-4 text-sm"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary/10 text-primary"
                            >
                                <Crown class="h-4 w-4" />
                            </div>
                            <div>
                                <p
                                    class="text-[10px] font-bold tracking-wider text-muted-foreground uppercase"
                                >
                                    Ketua Kelompok
                                </p>
                                <p class="mt-0.5 font-medium text-foreground">
                                    {{ inviteGroup.leader.name }}
                                </p>
                            </div>
                        </div>

                        <div
                            class="flex items-center gap-3 border-t border-border/40 pt-2.5"
                        >
                            <div
                                class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary/10 text-primary"
                            >
                                <Users class="h-4 w-4" />
                            </div>
                            <div>
                                <p
                                    class="text-[10px] font-bold tracking-wider text-muted-foreground uppercase"
                                >
                                    Anggota
                                </p>
                                <p class="mt-0.5 font-medium text-foreground">
                                    {{ inviteGroup.members_count }} Orang
                                </p>
                            </div>
                        </div>

                        <div
                            v-if="inviteGroup.company_name"
                            class="flex items-center gap-3 border-t border-border/40 pt-2.5"
                        >
                            <div
                                class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary/10 text-primary"
                            >
                                <Building2 class="h-4 w-4" />
                            </div>
                            <div>
                                <p
                                    class="text-[10px] font-bold tracking-wider text-muted-foreground uppercase"
                                >
                                    Perusahaan Tujuan
                                </p>
                                <p class="mt-0.5 font-medium text-foreground">
                                    {{ inviteGroup.company_name }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <p class="px-2 text-center text-xs text-muted-foreground">
                        Kirim permintaan bergabung ke kelompok ini?
                    </p>
                </div>

                <DialogFooter>
                    <Button
                        variant="outline"
                        @click="showJoinConfirmDialog = false"
                        >Batal</Button
                    >
                    <Button
                        v-if="!inviteGroupError"
                        id="btn-confirm-join-from-link"
                        @click="confirmJoinFromLink"
                        :disabled="isProcessing || isFetchingInviteGroup"
                    >
                        <Spinner
                            v-if="isProcessing"
                            class="mr-2 h-4 w-4 animate-spin"
                        />
                        Kirim Permintaan
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Disband Confirm -->
        <Dialog v-model:open="showDisbandConfirm">
            <DialogContent class="sm:max-w-[400px]">
                <DialogHeader>
                    <DialogTitle class="text-destructive"
                        >Bubarkan Kelompok</DialogTitle
                    >
                    <DialogDescription>
                        Tindakan ini akan membubarkan kelompok dan menghapus
                        seluruh data anggota. Ini tidak dapat dibatalkan.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button
                        variant="outline"
                        @click="showDisbandConfirm = false"
                        >Batal</Button
                    >
                    <Button
                        id="btn-confirm-disband"
                        variant="destructive"
                        @click="disbandGroup"
                        :disabled="isProcessing"
                    >
                        <Spinner
                            v-if="isProcessing"
                            class="mr-2 h-4 w-4 animate-spin"
                        />
                        Ya, Bubarkan
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Leave Confirm -->
        <Dialog v-model:open="showLeaveConfirm">
            <DialogContent class="sm:max-w-[400px]">
                <DialogHeader>
                    <DialogTitle>Keluar dari Kelompok</DialogTitle>
                    <DialogDescription>
                        Apakah kamu yakin ingin keluar dari kelompok ini? Kamu
                        harus mengirim permintaan baru jika ingin bergabung
                        lagi.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button variant="outline" @click="showLeaveConfirm = false"
                        >Batal</Button
                    >
                    <Button
                        id="btn-confirm-leave"
                        variant="destructive"
                        @click="leaveGroup"
                        :disabled="isProcessing"
                    >
                        <Spinner
                            v-if="isProcessing"
                            class="mr-2 h-4 w-4 animate-spin"
                        />
                        Ya, Keluar
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Submit Proposal Confirm -->
        <Dialog v-model:open="showSubmitConfirm">
            <DialogContent class="sm:max-w-[400px]">
                <DialogHeader>
                    <DialogTitle>Ajukan Permohonan Magang</DialogTitle>
                    <DialogDescription>
                        Apakah Anda yakin ingin mengajukan permohonan magang?
                        <span class="mt-2 block font-semibold text-destructive">
                            Tindakan ini akan mengirimkan data pengajuan ke
                            program studi dan mengunci komposisi anggota
                            kelompok serta data perusahaan.
                        </span>
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button variant="outline" @click="showSubmitConfirm = false"
                        >Batal</Button
                    >
                    <Button
                        id="btn-confirm-submit-proposal"
                        @click="submitSubmissionProposal"
                        :disabled="isProcessing"
                    >
                        <Spinner
                            v-if="isProcessing"
                            class="mr-2 h-4 w-4 animate-spin"
                        />
                        Ya, Ajukan
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
