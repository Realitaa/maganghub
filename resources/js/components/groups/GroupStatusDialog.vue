<script setup lang="ts">
import {
    CheckCircle2,
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
import WorkflowStepper from '@/components/WorkflowStepper.vue';

import type { Group } from '@/types';

// ─── Props ────────────────────────────────────────────────────────────────────

const props = defineProps<{
    open: boolean;
    group: Group;
}>();

defineEmits<{
    (e: 'update:open', value: boolean): void;
}>();

// ─── Human-readable status info ───────────────────────────────────────────────

const statusInfo = computed(() => {
    const company =
        props.group.active_submission?.company_name ?? 'perusahaan tujuan';

    const map: Record<
        string,
        { icon: object; label: string; color: string; description: string }
    > = {
        forming: {
            icon: Users,
            label: 'Membentuk Kelompok',
            color: 'text-blue-500',
            description:
                'Kelompok sedang dalam tahap pembentukan. Ketua kelompok dapat mengisi data pengajuan magang dan mengundang anggota.',
        },
        submitted: {
            icon: FileText,
            label: 'Pengajuan Dikirim',
            color: 'text-yellow-500',
            description:
                'Pengajuan telah dikirim ke program studi dan sedang menunggu verifikasi oleh admin/operator.',
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
        loa_review: {
            icon: Clock,
            label: 'Review Surat Balasan',
            color: 'text-yellow-500',
            description:
                'Surat balasan dari perusahaan sedang diperiksa oleh operator/administrator.',
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
            description:
                'Masa magang kelompok ini telah selesai. Terima kasih telah menggunakan MagangHub.',
        },
    };

    return map[props.group.status] ?? map['forming'];
});
</script>

<template>
    <Dialog :open="open" @update:open="$emit('update:open', $event)">
        <DialogContent
            class="max-h-[90vh] overflow-y-auto sm:max-w-2xl xl:max-w-7xl"
        >
            <DialogHeader>
                <DialogTitle>Status Kelompok</DialogTitle>
                <DialogDescription>
                    Perkembangan pengajuan magang kelompok Anda.
                </DialogDescription>
            </DialogHeader>

            <!-- Current Status Badge -->
            <div
                class="flex items-start gap-3 rounded-xl border border-border/60 bg-muted/30 p-4"
            >
                <div
                    class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-full"
                    :class="`bg-${statusInfo.color.replace('text-', '')}/10`"
                >
                    <component
                        :is="statusInfo.icon"
                        class="h-5 w-5"
                        :class="statusInfo.color"
                    />
                </div>
                <div>
                    <p class="font-semibold" :class="statusInfo.color">
                        {{ statusInfo.label }}
                    </p>
                    <p
                        class="mt-0.5 text-sm leading-relaxed text-muted-foreground"
                    >
                        {{ statusInfo.description }}
                    </p>
                </div>
            </div>

            <!-- Progress Timeline -->
            <div class="space-y-1">
                <p
                    class="mb-3 text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                >
                    Progres Kelompok
                </p>
                <WorkflowStepper mode="dialog" :status="group.status" />
            </div>
        </DialogContent>
    </Dialog>
</template>
