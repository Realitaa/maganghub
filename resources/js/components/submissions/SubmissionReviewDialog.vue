<script setup lang="ts">
import { router, useHttp } from '@inertiajs/vue3';
import {
    Building2,
    Calendar,
    MapPin,
    Phone,
    Users,
    CheckCircle2,
    XCircle,
} from '@lucide/vue';
import { ref, watch } from 'vue';
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
import { Spinner } from '@/components/ui/spinner';
import { Textarea } from '@/components/ui/textarea';
import {
    show as showSubmission,
    approve as approveSubmission,
    reject as rejectSubmission,
} from '@/routes/review/submissions';
import type { SubmissionDetail } from '@/types';

const props = defineProps<{
    open: boolean;
    submissionId: number | null;
}>();

const emit = defineEmits<{
    (e: 'update:open', val: boolean): void;
    (e: 'success'): void;
}>();

const submissionDetail = ref<SubmissionDetail | null>(null);
const processing = ref(false);
const showRejectModal = ref(false);
const showApproveModal = ref(false);
const rejectNotes = ref('');

const http = useHttp();

watch([() => props.open, () => props.submissionId], ([open, id]) => {
    if (open && id) {
        submissionDetail.value = null;
        processing.value = true;
        http.get(showSubmission.url(id), {
            onSuccess: (response: any) => {
                submissionDetail.value = response.data;
                processing.value = false;
            },
            onError: () => {
                processing.value = false;
                emit('update:open', false);
            },
        });
    }
});

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

function confirmApprove() {
    showApproveModal.value = true;
}

function submitApprove() {
    if (!props.submissionId) {
        return;
    }

    processing.value = true;
    router.post(
        approveSubmission.url(props.submissionId),
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
    if (!props.submissionId || !rejectNotes.value.trim()) {
        return;
    }

    processing.value = true;
    router.post(
        rejectSubmission.url(props.submissionId),
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
        <!-- ───── DETAIL DIALOG ───── -->
        <Dialog :open="open" @update:open="(val) => emit('update:open', val)">
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
                    v-if="processing && !submissionDetail"
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
                                        class="mt-0.5 h-3.5 w-3.5 shrink-0 text-muted-foreground"
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
