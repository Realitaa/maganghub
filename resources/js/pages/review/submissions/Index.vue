<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { CheckCircle2, Clock, ArrowRight } from '@lucide/vue';
import { ref } from 'vue';
import SubmissionDetailDialog from '@/components/submissions/SubmissionDetailDialog.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { useIdTimeFormat } from '@/composables/useIdTimeFormat';
import { index as submissionsIndex } from '@/routes/review/submissions';

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

import type { SubmissionListItem } from '@/types';

// ─── Props ────────────────────────────────────────────────────────────────────

defineProps<{
    submissions: SubmissionListItem[];
}>();

// ─── State ────────────────────────────────────────────────────────────────────

const showDetailModal = ref(false);
const selectedSubmissionId = ref<number | null>(null);
const { formatFAT } = useIdTimeFormat();

// ─── Handlers ─────────────────────────────────────────────────────────────────

function openDetail(id: number) {
    selectedSubmissionId.value = id;
    showDetailModal.value = true;
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
                    Pengajuan dengan status "Diajukan" yang memerlukan
                    persetujuan.
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
                                <th class="p-4">Waktu Pengajuan</th>
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
                                    {{ formatFAT(sub.submitted_at) }}
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

        <!-- Submission Detail/Review Dialog -->
        <SubmissionDetailDialog
            v-model:open="showDetailModal"
            :submission-id="selectedSubmissionId"
            mode="review"
            @success="router.reload({ only: ['submissions'] })"
        />
    </div>
</template>
