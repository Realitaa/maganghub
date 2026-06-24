<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { useHttp } from '@inertiajs/vue3';
import {
    Building2,
    Calendar,
    MapPin,
    Phone,
    User,
    Users,
    FileText,
    CheckCircle2,
    XCircle,
    Clock,
    AlertCircle,
    ArrowRight,
} from '@lucide/vue';
import { ref, computed } from 'vue';
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
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { Textarea } from '@/components/ui/textarea';
import {
    index as submissionsIndex,
    show as showSubmission,
    approve as approveSubmission,
    reject as rejectSubmission,
} from '@/routes/review/submissions';

// Define layout breadcrumbs
defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Review Pengajuan',
                href: submissionsIndex.url(),
            },
        ],
    },
});

// ─── Types ────────────────────────────────────────────────────────────────────

interface SubmissionListItem {
    id: number;
    company_name: string;
    leader_name: string;
    members_count: number;
    submitted_at: string;
    status: string;
}

interface MemberDetail {
    id: number;
    name: string;
    email: string;
    nim?: string;
}

interface SubmissionMembership {
    id: number;
    user: MemberDetail;
    status: string;
}

interface SubmissionDetail {
    id: number;
    company_name: string;
    company_address: string;
    company_contact: string;
    division: string;
    field_of_interest: string;
    start_date: string;
    end_date: string;
    status: string;
    rejection_note?: string;
    group: {
        id: number;
        leader: MemberDetail;
    };
    submission_memberships: SubmissionMembership[];
}

// ─── Props ────────────────────────────────────────────────────────────────────

const props = defineProps<{
    submissions: SubmissionListItem[];
}>();

// ─── State ────────────────────────────────────────────────────────────────────

const showDetailModal = ref(false);
const showRejectModal = ref(false);
const showApproveModal = ref(false);

const selectedSubmissionId = ref<number | null>(null);
const submissionDetail = ref<SubmissionDetail | null>(null);
const rejectNotes = ref('');
const localProcessing = ref(false);

// HTTP Client for Lazy Load (Inertia v3 useHttp)
const http = useHttp();

// ─── Handlers ─────────────────────────────────────────────────────────────────

function openDetail(id: number) {
    selectedSubmissionId.value = id;
    showDetailModal.value = true;
    submissionDetail.value = null; // Clear previous
    localProcessing.value = true;

    // Fetch detail via useHttp without page navigation
    http.get(showSubmission.url(id), {
        onSuccess: (response: any) => {
            submissionDetail.value = response.data;
            localProcessing.value = false;
        },
        onError: () => {
            localProcessing.value = false;
            showDetailModal.value = false;
        },
    });
}

function confirmApprove() {
    showApproveModal.value = true;
}

function submitApprove() {
    if (!selectedSubmissionId.value) {
        return;
    }

    localProcessing.value = true;
    router.post(
        approveSubmission.url(selectedSubmissionId.value),
        {},
        {
            onFinish: () => {
                localProcessing.value = false;
                showApproveModal.value = false;
                showDetailModal.value = false;
            },
        },
    );
}

function confirmReject() {
    rejectNotes.value = '';
    showRejectModal.value = true;
}

function submitReject() {
    if (!selectedSubmissionId.value) {
        return;
    }

    if (!rejectNotes.value.trim()) {
        return;
    }

    localProcessing.value = true;
    router.post(
        rejectSubmission.url(selectedSubmissionId.value),
        {
            notes: rejectNotes.value,
        },
        {
            onFinish: () => {
                localProcessing.value = false;
                showRejectModal.value = false;
                showDetailModal.value = false;
            },
        },
    );
}

// Formatting dates helper
function formatDate(dateStr?: string) {
    if (!dateStr) {
        return '-';
    }

    return new Date(dateStr).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
}
</script>

<template>
    <Head title="Review Pengajuan Magang" />

    <div class="flex-1 space-y-6 p-4 pt-6 md:p-8">
        <div
            class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h1 class="text-2xl font-bold tracking-tight">
                    Review Pengajuan Magang
                </h1>
                <p class="text-sm text-muted-foreground">
                    Tinjau dan proses permohonan izin magang dari kelompok
                    mahasiswa.
                </p>
            </div>
            <Badge
                variant="outline"
                class="w-fit gap-1.5 self-start px-3 py-1 text-xs"
            >
                <Clock class="h-3.5 w-3.5 text-primary" />
                {{ submissions.length }} Pengajuan Aktif
            </Badge>
        </div>

        <!-- ───── Submissions Table ───── -->
        <Card class="border-border/80">
            <CardHeader class="pb-3">
                <CardTitle class="text-base font-semibold"
                    >Daftar Pengajuan Masuk</CardTitle
                >
                <CardDescription>
                    Pengajuan dengan status "Diajukan" (submitted) yang
                    memerlukan persetujuan dokumen.
                </CardDescription>
            </CardHeader>
            <CardContent class="p-0">
                <div
                    v-if="submissions.length === 0"
                    class="flex flex-col items-center justify-center p-12 text-center"
                >
                    <div
                        class="mb-3 rounded-full bg-primary/10 p-3 text-primary"
                    >
                        <CheckCircle2 class="h-6 w-6" />
                    </div>
                    <h3 class="text-sm font-medium">Semua Bersih!</h3>
                    <p class="mt-1 max-w-[280px] text-xs text-muted-foreground">
                        Tidak ada pengajuan magang aktif yang menunggu tinjauan
                        saat ini.
                    </p>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full border-collapse text-left text-sm">
                        <thead>
                            <tr
                                class="border-b border-border/60 bg-muted/30 text-xs font-semibold text-muted-foreground uppercase"
                            >
                                <th class="p-4">Ketua Kelompok</th>
                                <th class="p-4">Perusahaan Tujuan</th>
                                <th class="p-4 text-center">Anggota</th>
                                <th class="p-4">Tanggal Diajukan</th>
                                <th class="p-4">Status</th>
                                <th class="p-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/40">
                            <tr
                                v-for="sub in submissions"
                                :key="sub.id"
                                class="transition-colors hover:bg-muted/10"
                            >
                                <td class="p-4 font-medium text-foreground">
                                    {{ sub.leader_name }}
                                </td>
                                <td class="p-4">
                                    {{ sub.company_name }}
                                </td>
                                <td class="p-4 text-center">
                                    <Badge
                                        variant="secondary"
                                        class="font-mono text-xs"
                                    >
                                        {{ sub.members_count }} Orang
                                    </Badge>
                                </td>
                                <td class="p-4 text-muted-foreground">
                                    {{ formatDate(sub.submitted_at) }}
                                </td>
                                <td class="p-4">
                                    <Badge
                                        variant="default"
                                        class="bg-primary hover:bg-primary/95"
                                    >
                                        Diajukan
                                    </Badge>
                                </td>
                                <td class="p-4 text-right">
                                    <Button
                                        size="sm"
                                        variant="outline"
                                        class="h-8 gap-1.5"
                                        @click="openDetail(sub.id)"
                                        :id="`btn-detail-${sub.id}`"
                                    >
                                        Detail
                                        <ArrowRight class="h-3 w-3" />
                                    </Button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </CardContent>
        </Card>

        <!-- ───── DETAIL MODAL (LAZY LOAD) ───── -->
        <Dialog v-model:open="showDetailModal">
            <DialogContent
                class="max-h-[85vh] overflow-y-auto sm:max-w-[650px]"
            >
                <DialogHeader class="border-b border-border/60 pb-4">
                    <DialogTitle class="text-lg font-bold"
                        >Detail Pengajuan Magang</DialogTitle
                    >
                    <DialogDescription>
                        Informasi lengkap draf dokumen permohonan magang
                        kelompok.
                    </DialogDescription>
                </DialogHeader>

                <!-- Loading State -->
                <div
                    v-if="localProcessing && !submissionDetail"
                    class="flex flex-col items-center justify-center py-12"
                >
                    <Spinner class="h-8 w-8 animate-spin text-primary" />
                    <p class="mt-2 text-xs text-muted-foreground">
                        Memuat dokumen pengajuan...
                    </p>
                </div>

                <!-- Detail Content -->
                <div v-else-if="submissionDetail" class="space-y-6 pt-4">
                    <!-- Perusahaan Info -->
                    <div class="space-y-3">
                        <h3
                            class="flex items-center gap-1.5 text-sm font-semibold tracking-wider text-primary uppercase"
                        >
                            <Building2 class="h-4 w-4" />
                            Instansi / Perusahaan Tujuan
                        </h3>
                        <div
                            class="grid gap-3 rounded-xl border border-border/80 bg-muted/10 p-4 text-sm sm:grid-cols-2"
                        >
                            <div>
                                <Label class="text-xs text-muted-foreground"
                                    >Nama Perusahaan</Label
                                >
                                <p class="mt-0.5 font-medium">
                                    {{ submissionDetail.company_name }}
                                </p>
                            </div>
                            <div>
                                <Label class="text-xs text-muted-foreground"
                                    >Bidang yang Diminati</Label
                                >
                                <p class="mt-0.5 font-medium">
                                    {{ submissionDetail.field_of_interest }}
                                </p>
                            </div>
                            <div>
                                <Label class="text-xs text-muted-foreground"
                                    >Divisi Pekerjaan (Opsional)</Label
                                >
                                <p class="mt-0.5 font-medium">
                                    {{ submissionDetail.division || '-' }}
                                </p>
                            </div>
                            <div>
                                <Label class="text-xs text-muted-foreground"
                                    >Kontak Hubungan</Label
                                >
                                <p
                                    class="mt-0.5 flex items-center gap-1 font-medium"
                                >
                                    <Phone
                                        class="h-3 w-3 text-muted-foreground"
                                    />
                                    {{ submissionDetail.company_contact }}
                                </p>
                            </div>
                            <div>
                                <Label class="text-xs text-muted-foreground"
                                    >Tanggal Pelaksanaan</Label
                                >
                                <p
                                    class="mt-0.5 flex items-center gap-1 font-medium"
                                >
                                    <Calendar
                                        class="h-3 w-3 text-muted-foreground"
                                    />
                                    {{
                                        formatDate(submissionDetail.start_date)
                                    }}
                                    -
                                    {{ formatDate(submissionDetail.end_date) }}
                                </p>
                            </div>
                            <div
                                class="border-t border-border/60 pt-2.5 sm:col-span-2"
                            >
                                <Label class="text-xs text-muted-foreground"
                                    >Alamat Lengkap</Label
                                >
                                <p class="mt-0.5 flex items-start gap-1">
                                    <MapPin
                                        class="mt-0.5 h-3.5 w-3.5 flex-shrink-0 text-muted-foreground"
                                    />
                                    {{ submissionDetail.company_address }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Snapshot Anggota -->
                    <div class="space-y-3">
                        <h3
                            class="flex items-center gap-1.5 text-sm font-semibold tracking-wider text-primary uppercase"
                        >
                            <Users class="h-4 w-4" />
                            Snapshot Anggota Pengajuan Magang
                        </h3>
                        <div
                            class="divide-y divide-border/60 overflow-hidden rounded-xl border border-border/80 bg-background"
                        >
                            <div
                                v-for="membership in submissionDetail.submission_memberships"
                                :key="membership.id"
                                class="flex items-center justify-between p-3"
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
                                        <p
                                            class="text-sm font-medium text-foreground"
                                        >
                                            {{ membership.user.name }}
                                        </p>
                                        <p
                                            class="text-xs text-muted-foreground"
                                        >
                                            {{ membership.user.nim }}
                                        </p>
                                    </div>
                                </div>
                                <Badge
                                    v-if="
                                        membership.user.id ===
                                        submissionDetail.group.leader.id
                                    "
                                    variant="secondary"
                                    class="px-2 py-0.5 text-[10px]"
                                >
                                    Ketua Kelompok
                                </Badge>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div
                        class="flex justify-end gap-3 border-t border-border/60 pt-4"
                    >
                        <Button
                            variant="outline"
                            class="border-destructive/30 text-destructive hover:bg-destructive/10"
                            @click="confirmReject"
                            id="btn-reject"
                        >
                            <XCircle class="mr-2 h-4 w-4" />
                            Tolak Pengajuan
                        </Button>
                        <Button
                            variant="default"
                            class="bg-green-600 text-white hover:bg-green-700"
                            @click="confirmApprove"
                            id="btn-approve"
                        >
                            <CheckCircle2 class="mr-2 h-4 w-4" />
                            Setujui Pengajuan
                        </Button>
                    </div>
                </div>
            </DialogContent>
        </Dialog>

        <!-- ───── REJECT CONFIRM MODAL (CATATAN PENOLAKAN) ───── -->
        <Dialog v-model:open="showRejectModal">
            <DialogContent class="sm:max-w-[400px]">
                <DialogHeader>
                    <DialogTitle
                        class="flex items-center gap-1.5 text-destructive"
                    >
                        <XCircle class="h-5 w-5" />
                        Tolak Pengajuan Magang
                    </DialogTitle>
                    <DialogDescription>
                        Masukkan alasan atau catatan penolakan pengajuan magang
                        ini. Alasan ini akan dikirimkan kepada ketua kelompok
                        agar mereka dapat memperbaiki data.
                    </DialogDescription>
                </DialogHeader>

                <div class="space-y-3 py-2">
                    <Label
                        for="reject_notes"
                        class="text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                        >Catatan Penolakan (Wajib)</Label
                    >
                    <Textarea
                        id="reject_notes"
                        v-model="rejectNotes"
                        placeholder="Contoh: Tanggal pelaksanaan tidak sesuai ketentuan akademik / Alamat perusahaan kurang lengkap."
                        rows="4"
                    />
                </div>

                <DialogFooter>
                    <Button variant="outline" @click="showRejectModal = false"
                        >Batal</Button
                    >
                    <Button
                        id="btn-confirm-reject"
                        variant="destructive"
                        @click="submitReject"
                        :disabled="localProcessing || !rejectNotes.trim()"
                    >
                        <Spinner
                            v-if="localProcessing"
                            class="mr-2 h-4 w-4 animate-spin"
                        />
                        Tolak Sekarang
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- ───── APPROVE CONFIRM MODAL ───── -->
        <Dialog v-model:open="showApproveModal">
            <DialogContent class="sm:max-w-[400px]">
                <DialogHeader>
                    <DialogTitle
                        class="flex items-center gap-1.5 text-green-600"
                    >
                        <CheckCircle2 class="h-5 w-5" />
                        Setujui Pengajuan Magang
                    </DialogTitle>
                    <DialogDescription>
                        Apakah Anda yakin ingin menyetujui pengajuan permohonan
                        magang kelompok ini?
                        <strong
                            class="mt-2 block font-semibold text-foreground"
                        >
                            Status kelompok akan dikunci dan berkas pengantar
                            magang akan ditandai siap dikirim
                            (LETTER_PUBLISHED).
                        </strong>
                    </DialogDescription>
                </DialogHeader>

                <DialogFooter class="mt-4">
                    <Button variant="outline" @click="showApproveModal = false"
                        >Batal</Button
                    >
                    <Button
                        id="btn-confirm-approve"
                        class="bg-green-600 text-white hover:bg-green-700"
                        @click="submitApprove"
                        :disabled="localProcessing"
                    >
                        <Spinner
                            v-if="localProcessing"
                            class="mr-2 h-4 w-4 animate-spin"
                        />
                        Ya, Setujui
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
