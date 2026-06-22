<script setup lang="ts">
import {
    CheckCircle2,
    Circle,
    Clock,
    FileCheck,
    FileText,
    Trophy,
    Users,
    XCircle,
} from '@lucide/vue';
import { computed } from 'vue';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
} from '@/components/ui/dialog';

// ─── Types ────────────────────────────────────────────────────────────────────

interface Submission {
    company_name: string;
    status: string;
}

interface Group {
    id: number;
    code: string;
    status: string;
    leader: { name: string };
    active_submission?: Submission | null;
}

// ─── Props ────────────────────────────────────────────────────────────────────

const props = defineProps<{
    open: boolean;
    group: Group;
}>();

defineEmits<{
    (e: 'update:open', value: boolean): void;
}>();

// ─── Progress Steps ───────────────────────────────────────────────────────────

/**
 * Maps internal status values to ordered progress steps with human labels.
 * Internal states (submitted, applying, accepted) are hidden from the user.
 */
const progressSteps = [
    { key: 'forming', label: 'Membentuk Kelompok', icon: Users },
    { key: 'pengajuan', label: 'Pengajuan Dikirim', icon: FileText },
    { key: 'letter_published', label: 'Surat Terbit', icon: FileCheck },
    { key: 'applying', label: 'Menunggu Balasan', icon: Clock },
    { key: 'internship_started', label: 'Magang Dimulai', icon: Trophy },
    { key: 'completed', label: 'Selesai', icon: CheckCircle2 },
];

const statusToStepIndex: Record<string, number> = {
    forming: 0,
    submitted: 1,
    under_review: 1,
    letter_published: 2,
    applying: 3,
    accepted: 3,
    partially_accepted: 3,
    rejected: 3,
    internship_started: 4,
    completed: 5,
};

const currentStepIndex = computed(() => {
    return statusToStepIndex[props.group.status] ?? 0;
});

const isRejected = computed(() => props.group.status === 'rejected');

// ─── Human-readable status info ───────────────────────────────────────────────

const statusInfo = computed(() => {
    const company = props.group.active_submission?.company_name ?? 'perusahaan tujuan';

    const map: Record<string, { icon: object; label: string; color: string; description: string }> = {
        forming: {
            icon: Users,
            label: 'Membentuk Kelompok',
            color: 'text-blue-500',
            description: 'Kelompok sedang dalam tahap pembentukan. Ketua kelompok dapat mengisi data pengajuan magang dan mengundang anggota.',
        },
        submitted: {
            icon: FileText,
            label: 'Pengajuan Dikirim',
            color: 'text-yellow-500',
            description: 'Pengajuan telah dikirim ke program studi dan sedang menunggu verifikasi oleh admin/operator.',
        },
        under_review: {
            icon: Clock,
            label: 'Sedang Ditinjau',
            color: 'text-orange-500',
            description: 'Tim program studi sedang meninjau pengajuan kelompok Anda.',
        },
        letter_published: {
            icon: FileCheck,
            label: 'Surat Terbit',
            color: 'text-green-500',
            description: `Surat permohonan magang telah diterbitkan. Antar surat ke ${company} dan tunggu surat balasan.`,
        },
        applying: {
            icon: Clock,
            label: 'Menunggu Balasan Perusahaan',
            color: 'text-yellow-500',
            description: `${company} sedang meninjau permohonan magang. Setelah menerima surat balasan, upload di tab Surat Balasan.`,
        },
        accepted: {
            icon: CheckCircle2,
            label: 'Diterima Perusahaan',
            color: 'text-green-500',
            description: `Selamat! Kelompok Anda diterima magang di ${company}.`,
        },
        partially_accepted: {
            icon: CheckCircle2,
            label: 'Diterima Sebagian',
            color: 'text-yellow-500',
            description: `${company} hanya menerima sebagian anggota kelompok.`,
        },
        rejected: {
            icon: XCircle,
            label: 'Ditolak Perusahaan',
            color: 'text-destructive',
            description: `Permohonan magang di ${company} tidak dapat diterima. Silakan diskusikan dengan pembimbing untuk langkah selanjutnya.`,
        },
        internship_started: {
            icon: Trophy,
            label: 'Magang Sedang Berlangsung',
            color: 'text-primary',
            description: `Selamat melaksanakan magang di ${company}!`,
        },
        completed: {
            icon: Trophy,
            label: 'Magang Selesai',
            color: 'text-primary',
            description: 'Masa magang kelompok ini telah selesai. Terima kasih telah menggunakan MagangHub.',
        },
    };

    return map[props.group.status] ?? map['forming'];
});
</script>

<template>
    <Dialog :open="open" @update:open="$emit('update:open', $event)">
        <DialogContent class="sm:max-w-lg">
            <DialogHeader>
                <DialogTitle>Status Kelompok</DialogTitle>
                <DialogDescription>
                    Perkembangan pengajuan magang kelompok Anda.
                </DialogDescription>
            </DialogHeader>

            <!-- Current Status Badge -->
            <div class="flex items-start gap-3 rounded-xl border border-border/60 bg-muted/30 p-4">
                <div
                    class="mt-0.5 flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full"
                    :class="`bg-${statusInfo.color.replace('text-', '')}/10`"
                >
                    <component :is="statusInfo.icon" class="h-5 w-5" :class="statusInfo.color" />
                </div>
                <div>
                    <p class="font-semibold" :class="statusInfo.color">{{ statusInfo.label }}</p>
                    <p class="mt-0.5 text-sm text-muted-foreground leading-relaxed">
                        {{ statusInfo.description }}
                    </p>
                </div>
            </div>

            <!-- Progress Timeline -->
            <div class="space-y-1">
                <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                    Progres Kelompok
                </p>
                <div class="relative space-y-0">
                    <div
                        v-for="(step, index) in progressSteps"
                        :key="step.key"
                        class="flex items-start gap-3"
                    >
                        <!-- Connector line -->
                        <div class="flex flex-col items-center">
                            <div
                                class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full border-2 transition-all"
                                :class="[
                                    index < currentStepIndex
                                        ? 'border-primary bg-primary text-primary-foreground'
                                        : index === currentStepIndex
                                          ? isRejected
                                            ? 'border-destructive bg-destructive text-destructive-foreground'
                                            : 'border-primary bg-primary/10 text-primary'
                                          : 'border-border bg-muted/40 text-muted-foreground/50',
                                ]"
                            >
                                <CheckCircle2 v-if="index < currentStepIndex" class="h-3.5 w-3.5" />
                                <XCircle v-else-if="index === currentStepIndex && isRejected" class="h-3.5 w-3.5" />
                                <Circle v-else-if="index === currentStepIndex" class="h-3 w-3 fill-current" />
                                <Circle v-else class="h-3 w-3" />
                            </div>
                            <!-- Line between steps -->
                            <div
                                v-if="index < progressSteps.length - 1"
                                class="mt-0.5 h-6 w-0.5 rounded-full"
                                :class="index < currentStepIndex ? 'bg-primary' : 'bg-border'"
                            />
                        </div>

                        <!-- Step label -->
                        <div class="pb-1 pt-0.5">
                            <p
                                class="text-sm leading-7"
                                :class="[
                                    index < currentStepIndex
                                        ? 'font-medium text-foreground'
                                        : index === currentStepIndex
                                          ? isRejected
                                            ? 'font-semibold text-destructive'
                                            : 'font-semibold text-primary'
                                          : 'text-muted-foreground/60',
                                ]"
                            >
                                {{ step.label }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>
