<script setup lang="ts">
import { Head, router, usePage, Link } from '@inertiajs/vue3';
import {
    Users,
    Plus,
    LogIn,
    Copy,
    Check,
    Crown,
    UserMinus,
    Trash2,
    UserCheck,
    UserX,
    Clock,
    Link2,
    FileText,
    CheckCircle2,
    Lock,
    XCircle,
} from '@lucide/vue';
import { edit as editSecurity } from '@/routes/security';
import { edit as editProfile } from '@/routes/profile';
import { ref, onMounted, computed } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
    CardDescription,
} from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
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
import { Spinner } from '@/components/ui/spinner';
import {
    store as groupStore,
    join as groupJoin,
    leave as groupLeave,
    destroy as groupDestroy,
} from '@/routes/groups';
import {
    store as submissionStore,
    submit as submissionSubmit,
    downloadLetter,
    uploadResponse,
} from '@/routes/groups/submissions';
import {
    cancel as cancelRequest,
    approve as approveRequest,
    reject as rejectRequest,
} from '@/routes/groups/join-requests';
import { dashboard } from '@/routes';
import { useForm } from '@inertiajs/vue3';
import { watch } from 'vue';
import {
    Save,
    Send,
    Building2,
    MapPin,
    Phone,
    Calendar,
    AlertCircle,
} from '@lucide/vue';

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
    start_date: string;
    end_date: string;
    supporting_document: string | null;
    company_response_path?: string | null;
    status: string;
}

interface Group {
    id: number;
    code: string;
    status: string;
    leader_id: number;
    leader: Member;
    memberships: Array<{ user: Member }>;
    join_requests: JoinRequest[];
    active_submission?: Submission | null;
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
const codeCopied = ref(false);
const linkCopied = ref(false);
const showCreateConfirm = ref(false);
const showDisbandConfirm = ref(false);
const showLeaveConfirm = ref(false);
const showJoinConfirmDialog = ref(false);
const joinConfirmCode = ref('');
const isProcessing = ref(false);
const activeGroupTab = ref<'members' | 'requests' | 'submissions'>('members');

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
        submitted: 'Diajukan',
        under_review: 'Direview',
        letter_published: 'Surat Diterbitkan',
        applying: 'Sedang Mengajukan',
        accepted: 'Diterima',
        partially_accepted: 'Diterima Sebagian',
        rejected: 'Ditolak Perusahaan',
        internship_started: 'Magang Dimulai',
        completed: 'Selesai',
    };
    return labels[props.group?.status ?? ''] ?? props.group?.status ?? '-';
});

const groupStatusVariant = computed(
    (): 'default' | 'secondary' | 'destructive' | 'outline' => {
        const variants: Record<
            string,
            'default' | 'secondary' | 'destructive' | 'outline'
        > = {
            forming: 'secondary',
            submitted: 'default',
            under_review: 'default',
            letter_published: 'default',
            applying: 'default',
            accepted: 'default',
            partially_accepted: 'outline',
            rejected: 'destructive',
            internship_started: 'default',
            completed: 'default',
        };
        return variants[props.group?.status ?? ''] ?? 'outline';
    },
);

const shareableLink = computed(() => {
    if (!props.group) {
        return '';
    }
    return `${window.location.origin}/dashboard?code=${props.group.code}`;
});

const groupTabItems = computed(() => [
    {
        value: 'members' as const,
        label: 'Anggota',
        count: props.group?.memberships.length ?? 0,
    },
    {
        value: 'requests' as const,
        label: 'Permintaan',
        count: props.group?.join_requests.length ?? 0,
    },
    {
        value: 'submissions' as const,
        label: 'Pengajuan',
        count: null,
    },
]);

// ─── Handlers ─────────────────────────────────────────────────────────────────

function copyCode() {
    if (!props.group) {
        return;
    }
    navigator.clipboard.writeText(props.group.code);
    codeCopied.value = true;
    setTimeout(() => (codeCopied.value = false), 2000);
}

function copyLink() {
    navigator.clipboard.writeText(shareableLink.value);
    linkCopied.value = true;
    setTimeout(() => (linkCopied.value = false), 2000);
}

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

function setActiveGroupTab(tab: 'members' | 'requests' | 'submissions') {
    activeGroupTab.value = tab;
}

// ─── Submission Form Setup ───────────────────────────────────────────────────

const showSubmitConfirm = ref(false);

const submissionForm = useForm({
    company_name: props.group?.active_submission?.company_name ?? '',
    company_address: props.group?.active_submission?.company_address ?? '',
    company_contact: props.group?.active_submission?.company_contact ?? '',
    division: props.group?.active_submission?.division ?? '',
    field_of_interest: props.group?.active_submission?.field_of_interest ?? '',
    start_date: props.group?.active_submission?.start_date ? props.group.active_submission.start_date.substring(0, 10) : '',
    end_date: props.group?.active_submission?.end_date ? props.group.active_submission.end_date.substring(0, 10) : '',
});

const isSubmissionEditable = computed(() => {
    return isLeader.value && (props.group?.status === 'forming' || props.group?.status === 'company_rejected');
});

watch(
    () => props.group?.active_submission,
    (newSub) => {
        submissionForm.company_name = newSub?.company_name ?? '';
        submissionForm.company_address = newSub?.company_address ?? '';
        submissionForm.company_contact = newSub?.company_contact ?? '';
        submissionForm.division = newSub?.division ?? '';
        submissionForm.field_of_interest = newSub?.field_of_interest ?? '';
        submissionForm.start_date = newSub?.start_date ? newSub.start_date.substring(0, 10) : '';
        submissionForm.end_date = newSub?.end_date ? newSub.end_date.substring(0, 10) : '';
    },
    { deep: true }
);

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
        responseUploadForm.post(uploadResponse.url(props.group.active_submission.id), {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                responseUploadForm.reset();
                if (fileInput.value) {
                    fileInput.value.value = '';
                }
            },
        });
    }
}

// ─── Deep link: ?code=XXXXXXXXXX ─────────────────────────────────────────────

onMounted(() => {
    const urlParams = new URLSearchParams(window.location.search);
    const code = urlParams.get('code');
    if (code && !props.group && props.pendingJoinRequests.length === 0 && !isLocked.value) {
        joinConfirmCode.value = code;
        showJoinConfirmDialog.value = true;
    }
});
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex-1 space-y-6 p-4 pt-6 md:p-8">
        <!-- ───── NO GROUP STATE (Google Meet style) ───── -->
        <div v-if="!group">
            <div v-if="isLocked" class="flex justify-center py-8">
                <Card class="border-destructive/30 bg-destructive/5 max-w-2xl w-full">
                    <CardHeader class="pb-3 text-center">
                        <div class="mx-auto rounded-full bg-destructive/10 p-3 text-destructive w-12 h-12 flex items-center justify-center mb-2">
                            <Lock class="h-6 w-6" />
                        </div>
                        <CardTitle class="text-xl font-bold">Akses Terkunci</CardTitle>
                        <CardDescription>
                            Anda belum memenuhi persyaratan untuk dapat mengakses fitur kelompok magang.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-6 text-center">
                        <p class="text-sm text-muted-foreground leading-relaxed">
                            Untuk dapat bergabung atau membuat kelompok magang baru, Anda wajib:
                        </p>
                        <div class="flex flex-col items-center gap-3 text-sm font-medium">
                            <div class="flex items-center gap-2">
                                <component :is="page.props.auth.requirements?.password_changed ? CheckCircle2 : XCircle" class="h-5 w-5" :class="page.props.auth.requirements?.password_changed ? 'text-green-500' : 'text-destructive'" />
                                <span>Mengubah password default</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <component :is="page.props.auth.requirements?.profile_completed ? CheckCircle2 : XCircle" class="h-5 w-5" :class="page.props.auth.requirements?.profile_completed ? 'text-green-500' : 'text-destructive'" />
                                <span>Melengkapi data biodata mahasiswa</span>
                            </div>
                        </div>
                        <div class="flex flex-col sm:flex-row gap-3 justify-center pt-4">
                            <Link v-if="!page.props.auth.requirements?.password_changed" :href="editSecurity()">
                                <Button variant="destructive" id="btn-lock-password">Ubah Password</Button>
                            </Link>
                            <Link v-if="!page.props.auth.requirements?.profile_completed" :href="editProfile()">
                                <Button variant="outline" class="border-primary/30 text-primary" id="btn-lock-profile">Lengkapi Biodata</Button>
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
                    class="border-primary/20 bg-gradient-to-br from-primary/5 to-primary/10"
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
        <div v-else-if="group" class="space-y-6">
            <!-- Group Header -->
            <div
                class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start"
            >
                <div class="space-y-1">
                    <div class="flex items-center gap-2.5">
                        <h1 class="text-2xl font-bold tracking-tight">
                            Kelompok Magang
                        </h1>
                        <Badge :variant="groupStatusVariant">{{
                            groupStatusLabel
                        }}</Badge>
                    </div>
                    <p class="text-sm text-muted-foreground">
                        Kode kelompok kamu dan daftar anggota ditampilkan di
                        sini.
                    </p>
                </div>
                <!-- Action Buttons -->
                <div class="flex gap-2">
                    <Button
                        v-if="isLeader && (group.status === 'forming' || group.status === 'company_rejected')"
                        variant="destructive"
                        size="sm"
                        @click="showDisbandConfirm = true"
                    >
                        <Trash2 class="mr-1.5 h-4 w-4" />
                        Bubarkan Kelompok
                    </Button>
                    <Button
                        v-else-if="!isLeader && (group.status === 'forming' || group.status === 'company_rejected')"
                        variant="outline"
                        size="sm"
                        class="border-destructive/30 text-destructive hover:bg-destructive/10 hover:text-destructive"
                        @click="showLeaveConfirm = true"
                    >
                        <UserMinus class="mr-1.5 h-4 w-4" />
                        Keluar
                    </Button>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Left: Group Code & Share -->
                <div class="space-y-4 lg:col-span-1">
                    <Card>
                        <CardHeader class="pb-3">
                            <CardTitle
                                class="text-sm font-semibold tracking-wider text-muted-foreground uppercase"
                                >Kode Kelompok</CardTitle
                            >
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <div class="flex items-center gap-2">
                                <span
                                    class="font-mono text-2xl font-bold tracking-widest text-primary"
                                    >{{ group.code }}</span
                                >
                            </div>
                            <div class="flex flex-col gap-2">
                                <Button
                                    variant="outline"
                                    size="sm"
                                    class="w-full justify-start gap-2"
                                    @click="copyCode"
                                >
                                    <component
                                        :is="codeCopied ? Check : Copy"
                                        class="h-4 w-4"
                                    />
                                    {{
                                        codeCopied
                                            ? 'Kode Tersalin!'
                                            : 'Salin Kode'
                                    }}
                                </Button>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    class="w-full justify-start gap-2 text-muted-foreground"
                                    @click="copyLink"
                                >
                                    <component
                                        :is="linkCopied ? Check : Link2"
                                        class="h-4 w-4"
                                    />
                                    {{
                                        linkCopied
                                            ? 'Link Tersalin!'
                                            : 'Salin Link Undangan'
                                    }}
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Right: Members -->
                <div class="space-y-4 lg:col-span-2">
                    <!-- Card: Surat Permohonan & Balasan Perusahaan -->
                    <Card v-if="group.active_submission?.status === 'letter_published' || group.active_submission?.status === 'applying'" class="border-green-200 dark:border-green-900 bg-green-50/10 dark:bg-green-950/10">
                        <CardHeader class="pb-3">
                            <div class="flex items-center gap-3">
                                <div class="rounded-full bg-green-100 dark:bg-green-900 p-2 text-green-700 dark:text-green-300">
                                    <FileText class="h-5 w-5" />
                                </div>
                                <div>
                                    <CardTitle class="text-base font-semibold text-green-900 dark:text-green-100">
                                        Surat Permohonan Magang Diterbitkan
                                    </CardTitle>
                                    <CardDescription class="text-xs text-green-700/80 dark:text-green-300/80">
                                        Unggah surat balasan resmi setelah disetujui perusahaan.
                                    </CardDescription>
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <p class="text-sm text-muted-foreground leading-relaxed">
                                Surat permohonan magang untuk kelompok Anda telah berhasil diterbitkan. Silakan berkoordinasi dengan administrator/operator untuk pengiriman ke instansi/perusahaan tujuan <strong>{{ group.active_submission.company_name }}</strong>.
                            </p>

                            <!-- Success banner if response is uploaded -->
                            <div v-if="group.active_submission.company_response_path" class="flex items-center gap-2 rounded-lg bg-green-100/50 dark:bg-green-900/30 p-3 text-sm text-green-800 dark:text-green-200 border border-green-200/50 dark:border-green-800/50">
                                <CheckCircle2 class="h-4 w-4 text-green-600 dark:text-green-400 flex-shrink-0" />
                                <span>Surat balasan perusahaan telah berhasil diunggah.</span>
                            </div>

                            <div class="flex flex-wrap gap-3 pt-2">
                                <template v-if="isLeader">
                                    <input 
                                        type="file" 
                                        ref="fileInput" 
                                        class="hidden" 
                                        accept=".pdf,.docx,.png,.jpg,.jpeg" 
                                        @change="handleResponseUpload" 
                                    />
                                    <Button 
                                        variant="outline"
                                        class="border-green-600 text-green-700 hover:bg-green-50 dark:text-green-400 dark:border-green-700 dark:hover:bg-green-950"
                                        @click="triggerUpload"
                                        :disabled="responseUploadForm.processing"
                                        id="btn-upload-response"
                                    >
                                        <Spinner v-if="responseUploadForm.processing" class="mr-2 h-4 w-4 animate-spin" />
                                        <Plus v-else class="mr-2 h-4 w-4" />
                                        {{ group.active_submission.company_response_path ? 'Unggah Ulang Surat Balasan' : 'Unggah Surat Balasan' }}
                                    </Button>
                                </template>
                            </div>
                            
                            <p v-if="responseUploadForm.errors.file" class="text-xs text-destructive mt-1 font-medium">
                                {{ responseUploadForm.errors.file }}
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader class="gap-4 pb-3">
                            <div
                                class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
                            >
                                <div class="space-y-1">
                                    <CardTitle
                                        class="text-sm font-semibold tracking-wider text-muted-foreground uppercase"
                                    >
                                        Panel Kelompok
                                    </CardTitle>
                                    <CardDescription>
                                        Kelola anggota, permintaan bergabung,
                                        dan pengajuan kelompok.
                                    </CardDescription>
                                </div>
                                <div
                                    class="flex flex-wrap justify-start gap-2 sm:justify-end"
                                >
                                    <Button
                                        v-for="tab in groupTabItems"
                                        :key="tab.value"
                                        size="sm"
                                        :variant="
                                            activeGroupTab === tab.value
                                                ? 'default'
                                                : 'outline'
                                        "
                                        class="rounded-full px-4"
                                        @click="setActiveGroupTab(tab.value)"
                                    >
                                        {{ tab.label }}
                                        <span
                                            v-if="tab.count !== null"
                                            class="ml-1.5 rounded-full bg-black/10 px-1.5 py-0.5 text-[10px] leading-none dark:bg-white/10"
                                        >
                                            {{ tab.count }}
                                        </span>
                                    </Button>
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div
                                v-if="activeGroupTab === 'members'"
                                class="space-y-4"
                            >
                                <div
                                    class="flex items-center gap-2 text-sm font-semibold text-muted-foreground"
                                >
                                    <Users class="h-4 w-4" />
                                    Anggota Saat Ini
                                </div>
                                <div class="divide-y divide-border/60">
                                    <div
                                        v-for="membership in group.memberships"
                                        :key="membership.user.id"
                                        class="flex items-center justify-between py-3 first:pt-0 last:pb-0"
                                    >
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/10 text-sm font-semibold text-primary"
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
                                                membership.user.id ===
                                                group.leader_id
                                            "
                                            variant="secondary"
                                            class="gap-1"
                                        >
                                            <Crown class="h-3 w-3" />
                                            Ketua
                                        </Badge>
                                    </div>
                                </div>
                            </div>

                            <div
                                v-else-if="activeGroupTab === 'requests'"
                                class="space-y-4"
                            >
                                <div
                                    class="flex items-center gap-2 text-sm font-semibold text-muted-foreground"
                                >
                                    <Clock class="h-4 w-4" />
                                    Permintaan Bergabung
                                </div>

                                <div
                                    v-if="
                                        isLeader &&
                                        group.join_requests.length > 0
                                    "
                                    class="divide-y divide-border/60"
                                >
                                    <div
                                        v-for="req in group.join_requests"
                                        :key="req.id"
                                        class="flex flex-col gap-4 py-3 first:pt-0 last:pb-0 sm:flex-row sm:items-center sm:justify-between"
                                    >
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="flex h-8 w-8 items-center justify-center rounded-full bg-secondary text-sm font-semibold"
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
                                                    {{
                                                        req.user.nim ??
                                                        req.user.email
                                                    }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="flex gap-2">
                                            <Button
                                                size="sm"
                                                variant="outline"
                                                class="gap-1.5 border-green-600/30 text-green-600 hover:bg-green-600/10 hover:text-green-600"
                                                @click="
                                                    approveJoinRequest(req.id)
                                                "
                                            >
                                                <UserCheck
                                                    class="h-3.5 w-3.5"
                                                />
                                                Terima
                                            </Button>
                                            <Button
                                                size="sm"
                                                variant="ghost"
                                                class="gap-1.5 text-destructive hover:bg-destructive/10 hover:text-destructive"
                                                @click="
                                                    rejectJoinRequest(req.id)
                                                "
                                            >
                                                <UserX class="h-3.5 w-3.5" />
                                                Tolak
                                            </Button>
                                        </div>
                                    </div>
                                </div>

                                <div
                                    v-else-if="isLeader"
                                    class="rounded-xl border border-dashed border-border/80 bg-muted/20 px-4 py-8 text-center"
                                >
                                    <p class="text-sm font-medium">
                                        Belum ada permintaan aktif
                                    </p>
                                    <p
                                        class="mt-1 text-xs text-muted-foreground"
                                    >
                                        Permintaan bergabung baru akan muncul di
                                        tab ini.
                                    </p>
                                </div>

                                <div
                                    v-else
                                    class="rounded-xl border border-dashed border-border/80 bg-muted/20 px-4 py-8 text-center"
                                >
                                    <p class="text-sm font-medium">
                                        Hanya ketua yang dapat memproses
                                        permintaan
                                    </p>
                                    <p
                                        class="mt-1 text-xs text-muted-foreground"
                                    >
                                        Kamu tetap bisa memantau anggota
                                        kelompok melalui tab Anggota.
                                    </p>
                                </div>
                            </div>

                            <!-- ───── SUBMISSIONS TAB (Form Pengajuan Magang) ───── -->
                            <div v-else class="space-y-6">
                                <!-- Status Banner / Alert -->
                                <div v-if="group.status !== 'forming' && group.status !== 'company_rejected'" class="rounded-xl border border-primary/20 bg-primary/5 p-4 flex gap-3 text-sm text-foreground">
                                    <AlertCircle class="h-5 w-5 text-primary flex-shrink-0" />
                                    <div>
                                        <h4 class="font-semibold mb-1">Status Pengajuan Magang: {{ groupStatusLabel }}</h4>
                                        <p class="text-xs text-muted-foreground" v-if="group.status === 'submitted'">
                                            Pengajuan kelompok Anda telah dikirim dan sedang menunggu verifikasi oleh admin/operator. Komposisi anggota kelompok dan data perusahaan telah dikunci.
                                        </p>
                                        <p class="text-xs text-muted-foreground" v-else-if="group.status === 'under_review'">
                                            Pengajuan Anda sedang ditinjau oleh tim program studi / admin.
                                        </p>
                                        <p class="text-xs text-muted-foreground" v-else-if="group.status === 'letter_published'">
                                            Surat izin magang resmi telah diterbitkan oleh program studi.
                                        </p>
                                        <p class="text-xs text-muted-foreground" v-else-if="group.status === 'accepted'">
                                            Kelompok Anda telah diterima magang oleh instansi tujuan.
                                        </p>
                                        <p class="text-xs text-muted-foreground" v-else-if="group.status === 'partially_accepted'">
                                            Penerimaan magang disetujui sebagian oleh instansi tujuan.
                                        </p>
                                        <p class="text-xs text-muted-foreground" v-else-if="group.status === 'rejected'">
                                            Pengajuan kelompok Anda ditolak oleh instansi tujuan.
                                        </p>
                                        <p class="text-xs text-muted-foreground" v-else-if="group.status === 'internship_started'">
                                            Masa magang resmi dimulai. Selamat melaksanakan magang!
                                        </p>
                                        <p class="text-xs text-muted-foreground" v-else-if="group.status === 'completed'">
                                            Masa magang kelompok ini telah selesai.
                                        </p>
                                    </div>
                                </div>
                                <div v-else class="rounded-xl border border-yellow-500/20 bg-yellow-500/5 p-4 flex gap-3 text-sm text-foreground">
                                    <AlertCircle class="h-5 w-5 text-yellow-600 dark:text-yellow-500 flex-shrink-0" />
                                    <div>
                                        <h4 class="font-semibold mb-1" v-if="isLeader">Persiapan Pengajuan Magang</h4>
                                        <h4 class="font-semibold mb-1" v-else>Menunggu Pengajuan Ketua Kelompok</h4>
                                        <p class="text-xs text-muted-foreground" v-if="isLeader">
                                            Sebagai ketua kelompok, silakan isi data instansi/perusahaan tujuan magang di bawah ini. Anda dapat menyimpannya sebagai draf terlebih dahulu sebelum resmi mengajukan ke admin. Setelah diajukan, data perusahaan dan keanggotaan kelompok akan <strong>dikunci</strong>.
                                        </p>
                                        <p class="text-xs text-muted-foreground" v-else>
                                            Draf data pengajuan sedang diisi oleh ketua kelompok Anda ({{ group.leader.name }}). Data akan dikunci setelah diajukan ke admin.
                                        </p>
                                    </div>
                                </div>

                                <!-- Form -->
                                <form @submit.prevent class="space-y-6">
                                    <!-- Grid 3 Kolom -->
                                    <div class="grid gap-4 sm:grid-cols-3">
                                        <!-- Nama Instansi/Perusahaan -->
                                        <div class="space-y-1.5">
                                            <Label for="company_name" class="flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                                <Building2 class="h-3.5 w-3.5" />
                                                Nama Perusahaan / Instansi
                                            </Label>
                                            <Input
                                                id="company_name"
                                                v-model="submissionForm.company_name"
                                                placeholder="Contoh: PT Teknologi Nusantara"
                                                :disabled="!isSubmissionEditable"
                                            />
                                            <span v-if="submissionForm.errors.company_name" class="text-xs text-destructive">
                                                {{ submissionForm.errors.company_name }}
                                            </span>
                                        </div>

                                        <!-- Bidang yang Diminati -->
                                        <div class="space-y-1.5">
                                            <Label for="field_of_interest" class="flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                                <FileText class="h-3.5 w-3.5" />
                                                Bidang yang Diminati
                                            </Label>
                                            <Input
                                                id="field_of_interest"
                                                v-model="submissionForm.field_of_interest"
                                                placeholder="Contoh: Web Developer, UI/UX"
                                                :disabled="!isSubmissionEditable"
                                            />
                                            <span v-if="submissionForm.errors.field_of_interest" class="text-xs text-destructive">
                                                {{ submissionForm.errors.field_of_interest }}
                                            </span>
                                        </div>

                                        <!-- Bidang/Divisi Magang -->
                                        <div class="space-y-1.5">
                                            <Label for="division" class="flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                                <Building2 class="h-3.5 w-3.5" />
                                                Divisi Pekerjaan (Opsional)
                                            </Label>
                                            <Input
                                                id="division"
                                                v-model="submissionForm.division"
                                                placeholder="Contoh: Frontend, Backend"
                                                :disabled="!isSubmissionEditable"
                                            />
                                            <span v-if="submissionForm.errors.division" class="text-xs text-destructive">
                                                {{ submissionForm.errors.division }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Grid 3 Kolom -->
                                    <div class="grid gap-4 sm:grid-cols-3">
                                        <!-- Kontak Perusahaan -->
                                        <div class="space-y-1.5">
                                            <Label for="company_contact" class="flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                                <Phone class="h-3.5 w-3.5" />
                                                Kontak Instansi (No. Telp / Email)
                                            </Label>
                                            <Input
                                                id="company_contact"
                                                v-model="submissionForm.company_contact"
                                                placeholder="Contoh: hr@company.com / 021-xxxxxx"
                                                :disabled="!isSubmissionEditable"
                                            />
                                            <span v-if="submissionForm.errors.company_contact" class="text-xs text-destructive">
                                                {{ submissionForm.errors.company_contact }}
                                            </span>
                                        </div>

                                        <!-- Tanggal Mulai -->
                                        <div class="space-y-1.5">
                                            <Label for="start_date" class="flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                                <Calendar class="h-3.5 w-3.5" />
                                                Tanggal Mulai
                                            </Label>
                                            <Input
                                                id="start_date"
                                                type="date"
                                                v-model="submissionForm.start_date"
                                                :disabled="!isSubmissionEditable"
                                            />
                                            <span v-if="submissionForm.errors.start_date" class="text-xs text-destructive">
                                                {{ submissionForm.errors.start_date }}
                                            </span>
                                        </div>

                                        <!-- Tanggal Selesai -->
                                        <div class="space-y-1.5">
                                            <Label for="end_date" class="flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                                <Calendar class="h-3.5 w-3.5" />
                                                Tanggal Selesai
                                            </Label>
                                            <Input
                                                id="end_date"
                                                type="date"
                                                v-model="submissionForm.end_date"
                                                :disabled="!isSubmissionEditable"
                                            />
                                            <span v-if="submissionForm.errors.end_date" class="text-xs text-destructive">
                                                {{ submissionForm.errors.end_date }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Alamat Perusahaan -->
                                    <div class="space-y-1.5">
                                        <Label for="company_address" class="flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                            <MapPin class="h-3.5 w-3.5" />
                                            Alamat Lengkap Perusahaan
                                        </Label>
                                        <textarea
                                            id="company_address"
                                            v-model="submissionForm.company_address"
                                            placeholder="Contoh: Jl. Jenderal Sudirman No. 12, Jakarta Selatan"
                                            rows="3"
                                            class="flex min-h-[80px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                                            :disabled="!isSubmissionEditable"
                                        ></textarea>
                                        <span v-if="submissionForm.errors.company_address" class="text-xs text-destructive">
                                            {{ submissionForm.errors.company_address }}
                                        </span>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div v-if="isSubmissionEditable" class="flex justify-end gap-3 border-t border-border pt-4">
                                        <Button
                                            type="button"
                                            variant="outline"
                                            @click="saveSubmissionDraft"
                                            :disabled="isProcessing"
                                            id="btn-save-draft"
                                        >
                                            <Spinner v-if="isProcessing" class="mr-2 h-4 w-4 animate-spin" />
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
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>

        <!-- ───── DIALOGS ───── -->

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
                    <DialogDescription>
                        Kamu diundang untuk bergabung ke kelompok dengan kode
                        <strong class="font-mono">{{ joinConfirmCode }}</strong
                        >. Kirim permintaan bergabung?
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button
                        variant="outline"
                        @click="showJoinConfirmDialog = false"
                        >Batal</Button
                    >
                    <Button
                        id="btn-confirm-join-from-link"
                        @click="confirmJoinFromLink"
                        :disabled="isProcessing"
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
                        <span class="block mt-2 font-semibold text-destructive">
                            Tindakan ini akan mengirimkan data pengajuan ke program studi dan mengunci komposisi anggota kelompok magang serta data perusahaan.
                        </span>
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button variant="outline" @click="showSubmitConfirm = false">Batal</Button>
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
