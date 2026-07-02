<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { CheckCircle2, XCircle, Users } from '@lucide/vue';
import { ref, computed, watch } from 'vue';
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
import { companyDecision } from '@/routes/review/submissions';
import type { Submission } from '@/types';

const props = defineProps<{
    open: boolean;
    submission: Submission | null;
}>();

const emit = defineEmits<{
    (e: 'update:open', val: boolean): void;
    (e: 'success'): void;
}>();

const currentStep = ref<'select' | 'partial'>('select');
const processing = ref(false);
const submissionError = ref<string | null>(null);

const partialDecisions = ref<
    Record<number, { status: 'accepted' | 'rejected'; rejection_note: string }>
>({});
const newLeaderId = ref<number | null>(null);

function isInterningElsewhere(userId: number) {
    if (!props.submission?.group?.memberships) {
        return false;
    }

    const mem = props.submission.group.memberships.find(
        (m) => m.user_id === userId,
    );

    return mem && mem.status === 'interning_elsewhere';
}

const hasInterningElsewhere = computed(() => {
    if (!props.submission?.group?.memberships) {
        return false;
    }

    return props.submission.group.memberships.some(
        (m) => m.status === 'interning_elsewhere',
    );
});

// Watch when submission changes to reset step and build partialDecisions state
watch(
    () => props.submission,
    (sub) => {
        currentStep.value = 'select';
        submissionError.value = null;
        newLeaderId.value = null;

        if (sub) {
            const decisions: Record<
                number,
                { status: 'accepted' | 'rejected'; rejection_note: string }
            > = {};
            sub.submission_memberships.forEach((m) => {
                const elsewhere = isInterningElsewhere(m.user.id);
                decisions[m.user.id] = {
                    status: elsewhere ? 'rejected' : 'accepted',
                    rejection_note: elsewhere
                        ? 'Mahasiswa ini sudah diterima magang di kelompok lain.'
                        : '',
                };
            });
            partialDecisions.value = decisions;
        }
    },
    { immediate: true },
);

const acceptedMembersForDropdown = computed(() => {
    if (!props.submission) {
        return [];
    }

    return props.submission.submission_memberships
        .filter((m) => {
            const dec = partialDecisions.value[m.user.id];

            return dec && dec.status === 'accepted';
        })
        .map((m) => m.user);
});

function selectDecision(
    type: 'all_accepted' | 'all_rejected' | 'partially_accepted',
) {
    if (type === 'partially_accepted') {
        currentStep.value = 'partial';
    } else {
        submitDecision(type);
    }
}

function submitDecision(type: 'all_accepted' | 'all_rejected') {
    if (!props.submission) {
        return;
    }

    processing.value = true;
    submissionError.value = null;

    router.post(
        companyDecision.url({ submission: props.submission.id }),
        {
            decision: type,
        },
        {
            onSuccess: () => {
                emit('update:open', false);
                emit('success');
            },
            onError: (errors) => {
                submissionError.value =
                    errors.error ||
                    'Terjadi kesalahan saat memproses keputusan.';
            },
            onFinish: () => {
                processing.value = false;
            },
        },
    );
}

function submitPartialDecision() {
    if (!props.submission) {
        return;
    }

    submissionError.value = null;

    const decisionsArray = Object.entries(partialDecisions.value).map(
        ([userId, dec]) => ({
            user_id: parseInt(userId),
            status: dec.status,
            rejection_note:
                dec.status === 'rejected' ? dec.rejection_note : null,
        }),
    );

    const acceptedUsers = decisionsArray.filter((d) => d.status === 'accepted');
    const rejectedUsers = decisionsArray.filter((d) => d.status === 'rejected');

    if (acceptedUsers.length === 0) {
        submissionError.value =
            'Minimal harus ada satu anggota yang diterima. Jika semua ditolak, silakan gunakan opsi Semua Ditolak.';

        return;
    }

    // Check if leader is rejected
    const leaderId = props.submission.group.leader_id;
    const isLeaderRejected = rejectedUsers.some((u) => u.user_id === leaderId);

    if (isLeaderRejected && !newLeaderId.value) {
        submissionError.value =
            'Ketua kelompok ditolak, Anda wajib menunjuk Ketua baru dari anggota yang diterima.';

        return;
    }

    // Check if rejection notes are filled for rejected members
    for (const dec of decisionsArray) {
        if (
            dec.status === 'rejected' &&
            (!dec.rejection_note || !dec.rejection_note.trim())
        ) {
            submissionError.value =
                'Catatan penolakan wajib diisi untuk semua anggota yang ditolak.';

            return;
        }
    }

    processing.value = true;

    router.post(
        companyDecision.url({ submission: props.submission.id }),
        {
            decision: 'partially_accepted',
            member_decisions: decisionsArray,
            new_leader_id: isLeaderRejected ? newLeaderId.value : null,
        },
        {
            onSuccess: () => {
                emit('update:open', false);
                emit('success');
            },
            onError: (errors) => {
                submissionError.value =
                    errors.error ||
                    'Terjadi kesalahan saat memproses keputusan.';
            },
            onFinish: () => {
                processing.value = false;
            },
        },
    );
}

function handleBackToDecision() {
    currentStep.value = 'select';
}
</script>

<template>
    <Dialog :open="open" @update:open="(val) => emit('update:open', val)">
        <!-- STEP 1: SELECT DECISION TYPE -->
        <DialogContent v-if="currentStep === 'select'" class="sm:max-w-[450px]">
            <DialogHeader class="border-b border-border/60 pb-3">
                <DialogTitle class="text-base font-bold text-foreground"
                    >Proses Hasil Balasan Perusahaan</DialogTitle
                >
                <DialogDescription class="text-xs">
                    Pilih keputusan akhir penempatan magang berdasarkan surat
                    balasan dari perusahaan tujuan.
                </DialogDescription>
            </DialogHeader>

            <div
                v-if="submissionError"
                class="mt-2 rounded-lg border border-destructive/20 bg-destructive/15 p-3 text-xs text-destructive"
            >
                {{ submissionError }}
            </div>

            <div class="space-y-4 py-4">
                <div class="grid gap-3">
                    <Button
                        variant="outline"
                        class="h-14 cursor-pointer justify-start border-border/80 px-4 text-left transition-all hover:border-green-500 hover:bg-green-50/20 hover:text-green-600 dark:hover:border-green-600 dark:hover:bg-green-950/20"
                        @click="selectDecision('all_accepted')"
                        :disabled="processing || hasInterningElsewhere"
                        id="btn-decision-all-accepted"
                    >
                        <CheckCircle2 class="mr-3 h-5 w-5 text-green-500" />
                        <div>
                            <div class="text-xs font-semibold text-foreground">
                                Semua Diterima
                            </div>
                            <div
                                class="text-[10px] font-normal"
                                :class="
                                    hasInterningElsewhere
                                        ? 'font-medium text-destructive'
                                        : 'text-muted-foreground'
                                "
                            >
                                {{
                                    hasInterningElsewhere
                                        ? 'Terkunci: Ada anggota yang sudah diterima di kelompok lain.'
                                        : 'Seluruh anggota kelompok resmi diterima magang.'
                                }}
                            </div>
                        </div>
                    </Button>

                    <Button
                        variant="outline"
                        class="h-14 cursor-pointer justify-start border-border/80 px-4 text-left transition-all hover:border-red-500 hover:bg-red-50/20 hover:text-red-600 dark:hover:border-red-600 dark:hover:bg-red-950/20"
                        @click="selectDecision('all_rejected')"
                        :disabled="processing"
                        id="btn-decision-all-rejected"
                    >
                        <XCircle class="mr-3 h-5 w-5 text-red-500" />
                        <div>
                            <div class="text-xs font-semibold text-foreground">
                                Semua Ditolak
                            </div>
                            <div
                                class="text-[10px] font-normal text-muted-foreground"
                            >
                                Seluruh anggota kelompok ditolak, kelompok
                                dibebaskan.
                            </div>
                        </div>
                    </Button>

                    <Button
                        variant="outline"
                        class="h-14 cursor-pointer justify-start border-border/80 px-4 text-left transition-all hover:border-blue-500 hover:bg-blue-50/20 hover:text-blue-600 dark:hover:border-blue-600 dark:hover:bg-blue-950/20"
                        @click="selectDecision('partially_accepted')"
                        :disabled="processing"
                        id="btn-decision-partial"
                    >
                        <Users class="mr-3 h-5 w-5 text-blue-500" />
                        <div>
                            <div class="text-xs font-semibold text-foreground">
                                Sebagian Diterima
                            </div>
                            <div
                                class="text-[10px] font-normal text-muted-foreground"
                            >
                                Pilih secara spesifik siapa saja anggota yang
                                diterima/ditolak.
                            </div>
                        </div>
                    </Button>
                </div>
            </div>

            <DialogFooter class="border-t border-border/60 pt-3">
                <Button
                    variant="outline"
                    @click="emit('update:open', false)"
                    :disabled="processing"
                    >Batal</Button
                >
            </DialogFooter>
        </DialogContent>

        <!-- STEP 2: PARTIAL ACCEPTANCE DETAIL -->
        <DialogContent
            v-else-if="currentStep === 'partial'"
            class="max-h-[85vh] overflow-y-auto sm:max-w-[600px]"
        >
            <DialogHeader class="border-b border-border/60 pb-3">
                <DialogTitle class="text-base font-bold text-foreground"
                    >Atur Penempatan Sebagian Anggota</DialogTitle
                >
                <DialogDescription class="text-xs">
                    Tentukan hasil untuk masing-masing anggota kelompok magang
                    secara spesifik.
                </DialogDescription>
            </DialogHeader>

            <div
                v-if="submissionError"
                class="mt-1 rounded-lg border border-destructive/20 bg-destructive/15 p-3 text-xs text-destructive"
            >
                {{ submissionError }}
            </div>

            <div v-if="submission" class="space-y-6 py-4">
                <!-- Members status selector -->
                <div class="space-y-4">
                    <Label
                        class="text-xs font-bold tracking-wider text-muted-foreground uppercase"
                    >
                        Konfirmasi Anggota
                    </Label>

                    <div
                        class="divide-y divide-border/60 overflow-hidden rounded-xl border border-border/80 bg-background text-xs"
                    >
                        <div
                            v-for="membership in submission.submission_memberships"
                            :key="membership.id"
                            class="space-y-3 p-4"
                        >
                            <div
                                class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                            >
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
                                            class="flex items-center gap-2 font-semibold text-foreground"
                                        >
                                            {{ membership.user.name }}
                                            <Badge
                                                v-if="
                                                    membership.user.id ===
                                                    submission.group.leader_id
                                                "
                                                variant="secondary"
                                                class="px-1 py-0 text-[8px] font-normal"
                                            >
                                                Ketua
                                            </Badge>
                                        </p>
                                        <p
                                            class="text-[10px] text-muted-foreground"
                                        >
                                            {{ membership.user.nim }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Radio Toggle -->
                                <div class="flex gap-2">
                                    <label
                                        class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg border px-3 py-1.5 text-[11px] font-medium transition-all"
                                        :class="[
                                            partialDecisions[membership.user.id]
                                                ?.status === 'accepted'
                                                ? 'border-green-500/30 bg-green-500/10 text-green-600'
                                                : 'border-border/80 bg-muted/10 text-muted-foreground',
                                            isInterningElsewhere(
                                                membership.user.id,
                                            )
                                                ? 'cursor-not-allowed opacity-50'
                                                : '',
                                        ]"
                                    >
                                        <input
                                            type="radio"
                                            v-model="
                                                partialDecisions[
                                                    membership.user.id
                                                ].status
                                            "
                                            value="accepted"
                                            class="sr-only"
                                            :disabled="
                                                isInterningElsewhere(
                                                    membership.user.id,
                                                )
                                            "
                                            @change="
                                                () => {
                                                    if (
                                                        membership.user.id ===
                                                        submission?.group
                                                            ?.leader_id
                                                    ) {
                                                        newLeaderId = null;
                                                    }
                                                }
                                            "
                                        />
                                        Diterima
                                    </label>
                                    <label
                                        class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg border px-3 py-1.5 text-[11px] font-medium transition-all"
                                        :class="
                                            partialDecisions[membership.user.id]
                                                ?.status === 'rejected'
                                                ? 'border-red-500/30 bg-red-500/10 font-semibold text-red-600'
                                                : 'border-border/80 bg-muted/10 text-muted-foreground'
                                        "
                                    >
                                        <input
                                            type="radio"
                                            v-model="
                                                partialDecisions[
                                                    membership.user.id
                                                ].status
                                            "
                                            value="rejected"
                                            class="sr-only"
                                        />
                                        Ditolak
                                    </label>
                                </div>
                            </div>

                            <!-- Rejection Note Input -->
                            <div
                                v-if="
                                    partialDecisions[membership.user.id]
                                        ?.status === 'rejected'
                                "
                                class="space-y-1.5 pl-11"
                            >
                                <Label
                                    class="text-[10px] font-semibold text-muted-foreground"
                                >
                                    Catatan Penolakan Anggota (Wajib)
                                </Label>
                                <Textarea
                                    v-model="
                                        partialDecisions[membership.user.id]
                                            .rejection_note
                                    "
                                    placeholder="Contoh: Kuota posisi magang sudah penuh."
                                    rows="2"
                                    class="text-xs"
                                    :id="`reject-note-${membership.user.id}`"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Change Leader Dropdown -->
                <div
                    v-if="
                        partialDecisions[submission.group.leader_id]?.status ===
                        'rejected'
                    "
                    class="space-y-2 border-t border-border/40 pt-4"
                >
                    <Label class="text-xs font-bold text-destructive"
                        >Ketua Kelompok Ditolak, Pilih Ketua Baru!</Label
                    >
                    <p
                        class="text-[10px] leading-relaxed text-muted-foreground"
                    >
                        Karena ketua kelompok yang mengajukan ditolak, Anda
                        wajib menunjuk salah satu anggota kelompok yang
                        <strong>diterima</strong> untuk menjadi Ketua baru demi
                        melanjutkan proses kelompok aktif.
                    </p>

                    <div
                        v-if="acceptedMembersForDropdown.length === 0"
                        class="mt-1 text-xs font-semibold text-destructive"
                    >
                        Peringatan: Tidak ada anggota yang diterima. Silakan
                        ganti keputusan menjadi Semua Ditolak.
                    </div>

                    <div v-else class="mt-2">
                        <select
                            v-model="newLeaderId"
                            class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-hidden"
                            id="select-new-leader"
                        >
                            <option :value="null" disabled>
                                -- Pilih Ketua Baru --
                            </option>
                            <option
                                v-for="member in acceptedMembersForDropdown"
                                :key="member.id"
                                :value="member.id"
                            >
                                {{ member.name }} ({{ member.nim }})
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <DialogFooter
                class="flex flex-col-reverse gap-3 border-t border-border/60 pt-4 sm:flex-row sm:justify-between"
            >
                <Button
                    variant="outline"
                    @click="handleBackToDecision"
                    :disabled="processing"
                    >Kembali</Button
                >
                <div class="flex w-full gap-2 sm:w-auto">
                    <Button
                        variant="outline"
                        @click="emit('update:open', false)"
                        :disabled="processing"
                        >Batal</Button
                    >
                    <Button
                        variant="default"
                        class="cursor-pointer bg-green-600 font-medium text-white hover:bg-green-700"
                        @click="submitPartialDecision"
                        :disabled="processing"
                        id="btn-submit-partial"
                    >
                        <Spinner
                            v-if="processing"
                            class="mr-2 h-4 w-4 animate-spin"
                        />
                        Simpan Keputusan
                    </Button>
                </div>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
