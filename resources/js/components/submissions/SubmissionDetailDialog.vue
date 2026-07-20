<script setup lang="ts">
import { router, useHttp } from '@inertiajs/vue3';
import {
    Building2,
    Calendar,
    MapPin,
    Phone,
    Users,
    Printer,
    Mail,
    CheckCircle2,
    XCircle,
} from '@lucide/vue';
import { ref, watch, computed } from 'vue';
import GoogleMapLink from '@/components/GoogleMapLink.vue';
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
import { Label } from '@/components/ui/label';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Spinner } from '@/components/ui/spinner';
import { Textarea } from '@/components/ui/textarea';
import { useIdTimeFormat } from '@/composables/useIdTimeFormat';
import { downloadLetter } from '@/routes/groups/submissions';
import { markApplying } from '@/routes/review/submissions';
import {
    show as showSubmission,
    approve as approveSubmission,
    reject as rejectSubmission,
} from '@/routes/review/submissions';
import type { Submission } from '@/types';

interface Props {
    open: boolean;
    submission?: Submission | null;
    submissionId?: number | null;
    mode?: 'view' | 'review';
}

const props = withDefaults(defineProps<Props>(), {
    submission: null,
    submissionId: null,
    mode: 'view',
});

const emit = defineEmits<{
    (e: 'update:open', val: boolean): void;
    (e: 'success'): void;
}>();

const { formatDate } = useIdTimeFormat();

const submissionDetail = ref<any | null>(null);
const loading = ref(false);
const processing = ref(false);

const showRejectModal = ref(false);
const showApproveModal = ref(false);
const rejectNotes = ref('');

const http = useHttp();

// Watch dialog visibility and reload details if needed
watch(
    [() => props.open, () => props.submission, () => props.submissionId],
    ([open, subProp, subId]) => {
        if (open) {
            if (subProp) {
                submissionDetail.value = subProp;
                loading.value = false;
            } else if (subId) {
                submissionDetail.value = null;
                loading.value = true;
                http.get(showSubmission.url(subId), {
                    onSuccess: (response: any) => {
                        submissionDetail.value = response.data;
                        loading.value = false;
                    },
                    onError: () => {
                        loading.value = false;
                        emit('update:open', false);
                    },
                });
            }
        }
    },
    { immediate: true },
);

const leaderId = computed(() => {
    if (!submissionDetail.value) {
        return null;
    }

    if (submissionDetail.value.group?.leader_id) {
        return submissionDetail.value.group.leader_id;
    }

    if (submissionDetail.value.group?.leader?.id) {
        return submissionDetail.value.group.leader.id;
    }

    return null;
});

function handleDownloadLetter(subId: number) {
    window.open(downloadLetter.url({ submission: subId }), '_blank');
}

function handleMarkApplying(subId: number) {
    processing.value = true;
    router.post(
        markApplying.url({ submission: subId }),
        {},
        {
            onFinish: () => {
                processing.value = false;
                emit('update:open', false);
                emit('success');
            },
        },
    );
}

function confirmApprove() {
    showApproveModal.value = true;
}

function submitApprove() {
    const subId = props.submissionId || submissionDetail.value?.id;

    if (!subId) {
        return;
    }

    processing.value = true;
    router.post(
        approveSubmission.url(subId),
        {},
        {
            onFinish: () => {
                processing.value = false;
                showApproveModal.value = false;
                emit('update:open', false);
                emit('success');
            },
        },
    );
}

function confirmReject() {
    rejectNotes.value = '';
    showRejectModal.value = true;
}

function submitReject() {
    const subId = props.submissionId || submissionDetail.value?.id;

    if (!subId || !rejectNotes.value.trim()) {
        return;
    }

    processing.value = true;
    router.post(
        rejectSubmission.url(subId),
        {
            notes: rejectNotes.value,
        },
        {
            onFinish: () => {
                processing.value = false;
                showRejectModal.value = false;
                emit('update:open', false);
                emit('success');
            },
        },
    );
}
</script>

<template>
    <div>
        <!-- ───── DETAIL / REVIEW DIALOG ───── -->
        <Dialog :open="open" @update:open="(val) => emit('update:open', val)">
            <DialogContent
                class="max-h-[85vh] overflow-y-auto sm:max-w-[650px] lg:max-w-[1000px]"
            >
                <DialogHeader class="border-b border-border/60 pb-4">
                    <DialogTitle class="text-base font-bold text-foreground">
                        {{
                            mode === 'review'
                                ? 'Review Pengajuan Magang'
                                : 'Detail Pengajuan & Cetak Surat'
                        }}
                    </DialogTitle>
                    <DialogDescription class="text-xs">
                        {{
                            mode === 'review'
                                ? 'Informasi lengkap draf dokumen permohonan magang kelompok.'
                                : 'Informasi data pengajuan dan anggota untuk dokumen cetak surat permohonan magang kelompok.'
                        }}
                    </DialogDescription>
                </DialogHeader>

                <!-- Loading State -->
                <div
                    v-if="loading && !submissionDetail"
                    class="flex flex-col items-center justify-center py-12"
                >
                    <Spinner class="h-8 w-8 animate-spin text-primary" />
                    <p class="mt-2 text-xs text-muted-foreground">
                        Memuat dokumen pengajuan...
                    </p>
                </div>

                <!-- Detail Content -->
                <div v-else-if="submissionDetail" class="space-y-6 pt-4">
                    <div
                        class="grid grid-cols-1 items-start gap-6 lg:grid-cols-2"
                    >
                        <!-- Perusahaan Info -->
                        <div class="space-y-3">
                            <h3
                                class="flex items-center gap-1.5 text-xs font-semibold tracking-wider text-primary uppercase"
                            >
                                <Building2 class="h-4 w-4" />
                                Instansi / Perusahaan Tujuan
                            </h3>
                            <div
                                class="grid gap-3 rounded-xl border border-border/80 bg-muted/10 p-4 text-xs sm:grid-cols-2"
                            >
                                <div>
                                    <Label
                                        class="text-[10px] text-muted-foreground"
                                        >Nama Perusahaan</Label
                                    >
                                    <p
                                        class="mt-0.5 font-medium text-foreground"
                                    >
                                        {{ submissionDetail.company_name }}
                                    </p>
                                </div>
                                <div>
                                    <Label
                                        class="text-[10px] text-muted-foreground"
                                        >Bidang yang Diminati</Label
                                    >
                                    <p
                                        class="mt-0.5 font-medium text-foreground"
                                    >
                                        {{ submissionDetail.field_of_interest }}
                                    </p>
                                </div>
                                <div>
                                    <Label
                                        class="text-[10px] text-muted-foreground"
                                        >Tipe Perusahaan</Label
                                    >
                                    <p
                                        class="mt-0.5 font-medium text-foreground"
                                    >
                                        {{ submissionDetail.company_type }}
                                    </p>
                                </div>
                                <div>
                                    <Label
                                        class="text-[10px] text-muted-foreground"
                                        >Model Kerja</Label
                                    >
                                    <p
                                        class="mt-0.5 font-medium text-foreground"
                                    >
                                        {{ submissionDetail.working_model }}
                                    </p>
                                </div>
                                <div>
                                    <Label
                                        class="text-[10px] text-muted-foreground"
                                        >Divisi Pekerjaan (Opsional)</Label
                                    >
                                    <p
                                        class="mt-0.5 font-medium text-foreground"
                                    >
                                        {{ submissionDetail.division || '-' }}
                                    </p>
                                </div>
                                <div>
                                    <Label
                                        class="text-[10px] text-muted-foreground"
                                        >Kontak Hubungan</Label
                                    >
                                    <p
                                        class="mt-0.5 flex items-center gap-1 font-medium text-foreground"
                                    >
                                        <Phone
                                            class="h-3 w-3 text-muted-foreground"
                                        />
                                        {{ submissionDetail.company_contact }}
                                    </p>
                                </div>
                                <div>
                                    <Label
                                        class="text-[10px] text-muted-foreground"
                                        >Tanggal Pelaksanaan</Label
                                    >
                                    <p
                                        class="mt-0.5 flex items-center gap-1 font-medium text-foreground"
                                    >
                                        <Calendar
                                            class="h-3 w-3 text-muted-foreground"
                                        />
                                        {{
                                            formatDate(
                                                submissionDetail.start_date,
                                            )
                                        }}
                                        -
                                        {{
                                            formatDate(
                                                submissionDetail.end_date,
                                            )
                                        }}
                                    </p>
                                </div>
                                <div
                                    class="border-t border-border/60 pt-2 sm:col-span-2"
                                >
                                    <Label
                                        class="text-[10px] text-muted-foreground"
                                        >Alamat Lengkap</Label
                                    >
                                    <p
                                        class="mt-0.5 flex items-start gap-1 text-foreground"
                                    >
                                        <MapPin
                                            class="mt-0.5 h-3.5 w-3.5 shrink-0 text-muted-foreground"
                                        />
                                        {{ submissionDetail.company_address }}
                                    </p>
                                </div>
                            </div>
                            <GoogleMapLink
                                :query="submissionDetail.company_name"
                                :text="`Lihat ${submissionDetail.company_name} di Google Maps`"
                            />
                            <GoogleMapLink
                                :query="submissionDetail.company_address"
                                :text="`Lihat ${submissionDetail.company_address} di Google Maps`"
                            />
                        </div>

                        <!-- Anggota List -->
                        <div class="space-y-3">
                            <h3
                                class="flex items-center gap-1.5 text-xs font-semibold tracking-wider text-primary uppercase"
                            >
                                <Users class="h-4 w-4" />
                                Daftar Anggota Kelompok
                            </h3>
                            <ScrollArea class="h-120">
                                <div class="grid gap-3">
                                    <div
                                        v-for="membership in submissionDetail.submission_memberships"
                                        :key="membership.id"
                                        class="relative flex flex-col justify-between gap-4 rounded-xl border border-border/70 bg-card p-4 shadow-sm transition-all duration-300 hover:border-border hover:shadow-md md:flex-row"
                                    >
                                        <div class="flex items-start gap-3">
                                            <div
                                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-primary/10 bg-primary/10 text-sm font-semibold text-primary shadow-xs"
                                            >
                                                {{
                                                    membership.user.name
                                                        .charAt(0)
                                                        .toUpperCase()
                                                }}
                                            </div>
                                            <div class="min-w-0 space-y-1">
                                                <div
                                                    class="flex flex-wrap items-center gap-2"
                                                >
                                                    <p
                                                        class="text-sm leading-none font-bold tracking-tight text-foreground"
                                                    >
                                                        {{
                                                            membership.user.name
                                                        }}
                                                    </p>
                                                    <Badge
                                                        v-if="
                                                            membership.user
                                                                .id === leaderId
                                                        "
                                                        variant="default"
                                                        class="border-none bg-primary/10 px-2 py-0 text-[10px] font-medium text-primary"
                                                    >
                                                        Ketua Kelompok
                                                    </Badge>
                                                </div>
                                                <p
                                                    class="font-mono text-xs text-muted-foreground"
                                                >
                                                    {{ membership.user.nim }}
                                                    <span
                                                        class="mx-1 font-sans text-muted-foreground/40"
                                                        >|</span
                                                    >
                                                    Semester
                                                    {{
                                                        membership.user
                                                            .semester ?? '-'
                                                    }}
                                                </p>

                                                <!-- Contact details inside a clean grid -->
                                                <div
                                                    class="grid grid-cols-1 gap-x-4 gap-y-1.5 pt-2 text-xs text-muted-foreground sm:grid-cols-2"
                                                >
                                                    <div
                                                        class="flex min-w-0 items-center gap-1.5"
                                                    >
                                                        <Mail
                                                            class="h-3.5 w-3.5 shrink-0 text-muted-foreground/60"
                                                        />
                                                        <span
                                                            class="truncate font-normal"
                                                            >{{
                                                                membership.user
                                                                    .email
                                                            }}</span
                                                        >
                                                    </div>
                                                    <div
                                                        class="flex min-w-0 items-center gap-1.5"
                                                    >
                                                        <Phone
                                                            class="h-3.5 w-3.5 shrink-0 text-muted-foreground/60"
                                                        />
                                                        <span
                                                            class="truncate font-normal"
                                                            >{{
                                                                membership.user
                                                                    .phone ||
                                                                '-'
                                                            }}</span
                                                        >
                                                    </div>
                                                    <div
                                                        class="mt-0.5 flex items-start gap-1.5 sm:col-span-2"
                                                    >
                                                        <MapPin
                                                            class="mt-0.5 h-3.5 w-3.5 shrink-0 text-muted-foreground/60"
                                                        />
                                                        <span
                                                            class="line-clamp-2 leading-relaxed font-normal"
                                                            >{{
                                                                membership.user
                                                                    .address ||
                                                                '-'
                                                            }}</span
                                                        >
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </ScrollArea>
                        </div>
                    </div>

                    <!-- Footer Actions -->
                    <!-- REVIEW Mode Actions -->
                    <div
                        v-if="mode === 'review'"
                        class="flex justify-end gap-3 border-t border-border/60 pt-4"
                    >
                        <Button
                            variant="outline"
                            class="cursor-pointer border-destructive/30 text-destructive hover:bg-destructive/10"
                            @click="confirmReject"
                            id="btn-reject"
                        >
                            <XCircle class="mr-2 h-4 w-4" />
                            Tolak Pengajuan
                        </Button>
                        <Button
                            variant="default"
                            class="cursor-pointer bg-green-600 text-white hover:bg-green-700"
                            @click="confirmApprove"
                            id="btn-approve"
                        >
                            <CheckCircle2 class="mr-2 h-4 w-4" />
                            Setujui Pengajuan
                        </Button>
                    </div>

                    <!-- VIEW Mode Actions -->
                    <div
                        v-else
                        class="flex flex-col items-stretch gap-3 border-t border-border/60 pt-4 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <Button
                            v-if="submissionDetail.status !== 'submitted'"
                            variant="outline"
                            class="w-full cursor-pointer border-primary/30 text-primary hover:bg-primary/5 sm:w-auto"
                            @click="handleDownloadLetter(submissionDetail.id)"
                            id="btn-print-letter"
                        >
                            <Printer class="mr-2 h-4 w-4" />
                            Unduh Surat Permohonan
                        </Button>

                        <div class="flex justify-end gap-2">
                            <Button
                                variant="outline"
                                @click="emit('update:open', false)"
                                >Tutup</Button
                            >
                            <Button
                                v-if="
                                    submissionDetail.status ===
                                    'letter_published'
                                "
                                variant="default"
                                class="cursor-pointer bg-primary hover:bg-primary/95"
                                @click="handleMarkApplying(submissionDetail.id)"
                                :disabled="processing"
                                id="btn-mark-applying"
                            >
                                <Spinner
                                    v-if="processing"
                                    class="mr-2 h-4 w-4 animate-spin"
                                />
                                Tandai Mengajukan
                            </Button>
                        </div>
                    </div>
                </div>
            </DialogContent>
        </Dialog>

        <!-- ───── REJECT CONFIRM DIALOG ───── -->
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
                    >
                        Catatan Penolakan (Wajib)
                    </Label>
                    <Textarea
                        id="reject_notes"
                        v-model="rejectNotes"
                        placeholder="Contoh: Tanggal pelaksanaan tidak sesuai ketentuan akademik."
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
                        :disabled="processing || !rejectNotes.trim()"
                    >
                        <Spinner
                            v-if="processing"
                            class="mr-2 h-4 w-4 animate-spin"
                        />
                        Tolak Sekarang
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- ───── APPROVE CONFIRM DIALOG ───── -->
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
                            magang akan ditandai siap dikirim.
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
                        :disabled="processing"
                    >
                        <Spinner
                            v-if="processing"
                            class="mr-2 h-4 w-4 animate-spin"
                        />
                        Ya, Setujui
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
