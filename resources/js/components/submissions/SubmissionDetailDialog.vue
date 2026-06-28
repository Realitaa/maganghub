<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import {
    Building2,
    Calendar,
    MapPin,
    Phone,
    Users,
    Printer,
} from '@lucide/vue';
import { ref } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { useIdTimeFormat } from '@/composables/useIdTimeFormat';
import { downloadLetter } from '@/routes/groups/submissions';
import { markApplying } from '@/routes/review/submissions';
import type { Submission } from '@/types';

defineProps<{
    open: boolean;
    submission: Submission | null;
}>();

const emit = defineEmits<{
    (e: 'update:open', val: boolean): void;
    (e: 'success'): void;
}>();

const processing = ref(false);

const { formatDate } = useIdTimeFormat();

function handleDownloadLetter(subId: number) {
    window.open(downloadLetter.url({ submission: subId }), '_blank');
}

function handleDownloadIndividualLetter(subId: number, userId: number) {
    window.open(
        downloadLetter.url(
            { submission: subId },
            { query: { user_id: userId } },
        ),
        '_blank',
    );
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
</script>

<template>
    <Dialog :open="open" @update:open="(val) => emit('update:open', val)">
        <DialogContent class="max-h-[85vh] overflow-y-auto sm:max-w-[650px]">
            <DialogHeader class="border-b border-border/60 pb-4">
                <DialogTitle class="text-base font-bold text-foreground"
                    >Detail Pengajuan & Cetak Surat</DialogTitle
                >
                <DialogDescription class="text-xs">
                    Informasi data pengajuan dan anggota untuk dokumen cetak
                    surat permohonan magang kelompok.
                </DialogDescription>
            </DialogHeader>

            <div v-if="submission" class="space-y-6 pt-4">
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
                            <Label class="text-[10px] text-muted-foreground"
                                >Nama Perusahaan</Label
                            >
                            <p class="mt-0.5 font-medium text-foreground">
                                {{ submission.company_name }}
                            </p>
                        </div>
                        <div>
                            <Label class="text-[10px] text-muted-foreground"
                                >Bidang yang Diminati</Label
                            >
                            <p class="mt-0.5 font-medium text-foreground">
                                {{ submission.field_of_interest }}
                            </p>
                        </div>
                        <div>
                            <Label class="text-[10px] text-muted-foreground"
                                >Divisi Pekerjaan (Opsional)</Label
                            >
                            <p class="mt-0.5 font-medium text-foreground">
                                {{ submission.division || '-' }}
                            </p>
                        </div>
                        <div>
                            <Label class="text-[10px] text-muted-foreground"
                                >Kontak Hubungan</Label
                            >
                            <p
                                class="mt-0.5 flex items-center gap-1 font-medium text-foreground"
                            >
                                <Phone class="h-3 w-3 text-muted-foreground" />
                                {{ submission.company_contact }}
                            </p>
                        </div>
                        <div>
                            <Label class="text-[10px] text-muted-foreground"
                                >Tanggal Pelaksanaan</Label
                            >
                            <p
                                class="mt-0.5 flex items-center gap-1 font-medium text-foreground"
                            >
                                <Calendar
                                    class="h-3 w-3 text-muted-foreground"
                                />
                                {{ formatDate(submission.start_date) }} -
                                {{ formatDate(submission.end_date) }}
                            </p>
                        </div>
                        <div
                            class="border-t border-border/60 pt-2 sm:col-span-2"
                        >
                            <Label class="text-[10px] text-muted-foreground"
                                >Alamat Lengkap</Label
                            >
                            <p
                                class="mt-0.5 flex items-start gap-1 text-foreground"
                            >
                                <MapPin
                                    class="mt-0.5 h-3.5 w-3.5 shrink-0 text-muted-foreground"
                                />
                                {{ submission.company_address }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Anggota List -->
                <div class="space-y-3">
                    <h3
                        class="flex items-center gap-1.5 text-xs font-semibold tracking-wider text-primary uppercase"
                    >
                        <Users class="h-4 w-4" />
                        Daftar Anggota Kelompok
                    </h3>
                    <div
                        class="divide-y divide-border/60 overflow-hidden rounded-xl border border-border/80 bg-background text-xs"
                    >
                        <div
                            v-for="membership in submission.submission_memberships"
                            :key="membership.id"
                            class="p-3"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/10 text-xs font-semibold text-primary"
                                    >
                                        {{
                                            membership.user.name
                                                .charAt(0)
                                                .toUpperCase()
                                        }}
                                    </div>
                                    <div>
                                        <p
                                            class="font-semibold text-foreground"
                                        >
                                            {{ membership.user.name }}
                                        </p>
                                        <p
                                            class="text-[10px] text-muted-foreground"
                                        >
                                            {{ membership.user.nim }} | Semester
                                            {{
                                                membership.user.semester ?? '-'
                                            }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <Badge
                                        v-if="
                                            membership.user.id ===
                                            submission.group.leader_id
                                        "
                                        variant="secondary"
                                        class="px-2 py-0.5 text-[9px]"
                                    >
                                        Ketua Kelompok
                                    </Badge>
                                    <Button
                                        size="sm"
                                        variant="outline"
                                        class="h-7 cursor-pointer gap-1 border-primary/20 font-medium text-primary hover:bg-primary/5"
                                        @click="
                                            handleDownloadIndividualLetter(
                                                submission.id,
                                                membership.user.id,
                                            )
                                        "
                                    >
                                        <Printer class="h-3 w-3" />
                                        Cetak Surat
                                    </Button>
                                </div>
                            </div>
                            <div
                                class="mt-2 grid grid-cols-2 gap-2 border-t border-border/20 pt-2 pl-11 text-[10px] text-muted-foreground"
                            >
                                <div>
                                    Email:
                                    <span class="font-medium text-foreground">{{
                                        membership.user.email
                                    }}</span>
                                </div>
                                <div>
                                    Telepon:
                                    <span class="font-medium text-foreground">{{
                                        membership.user.phone || '-'
                                    }}</span>
                                </div>
                                <div class="col-span-2">
                                    Alamat:
                                    <span class="font-medium text-foreground">{{
                                        membership.user.address || '-'
                                    }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div
                    class="flex items-center justify-between border-t border-border/60 pt-4"
                >
                    <Button
                        variant="outline"
                        class="cursor-pointer border-primary/30 text-primary hover:bg-primary/5"
                        @click="handleDownloadLetter(submission.id)"
                        id="btn-print-letter"
                    >
                        <Printer class="mr-2 h-4 w-4" />
                        Unduh Semua Surat sekaligus
                    </Button>

                    <div class="flex gap-2">
                        <Button
                            variant="outline"
                            @click="emit('update:open', false)"
                            >Tutup</Button
                        >
                        <Button
                            v-if="submission.status === 'letter_published'"
                            variant="default"
                            class="cursor-pointer bg-primary hover:bg-primary/95"
                            @click="handleMarkApplying(submission.id)"
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
</template>
