<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import {
    FileText,
    CheckCircle2,
    Clock,
    ArrowRight,
    Search,
    Printer,
    Download,
} from '@lucide/vue';
import { ref, computed } from 'vue';
import CompanyDecisionDialog from '@/components/submissions/CompanyDecisionDialog.vue';
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
import { Input } from '@/components/ui/input';
import { useIdTimeFormat } from '@/composables/useIdTimeFormat';
import { index as readyIndex } from '@/routes/review/ready';
import type { Submission } from '@/types';

// Define layout breadcrumbs
defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Siap Magang',
                href: readyIndex.url(),
            },
        ],
    },
});

const { formatDate } = useIdTimeFormat();

// ─── Props ────────────────────────────────────────────────────────────────────

const props = defineProps<{
    readyToPrint: Submission[];
    waitingResponse: Submission[];
    receivedResponse: Submission[];
}>();

// ─── State ────────────────────────────────────────────────────────────────────

const searchPrint = ref('');
const searchWaiting = ref('');
const searchReceived = ref('');

const showDetailModal = ref(false);
const showDecisionModal = ref(false);

const selectedSubmission = ref<Submission | null>(null);

// ─── Computed Filters ─────────────────────────────────────────────────────────

const filteredPrint = computed(() => {
    return props.readyToPrint.filter(
        (sub) =>
            sub.company_name
                .toLowerCase()
                .includes(searchPrint.value.toLowerCase()) ||
            sub.group.leader.name
                .toLowerCase()
                .includes(searchPrint.value.toLowerCase()) ||
            sub.group.code
                .toLowerCase()
                .includes(searchPrint.value.toLowerCase()),
    );
});

const filteredWaiting = computed(() => {
    // Exclude the ones that have uploaded a company response (receivedResponse)
    return props.waitingResponse
        .filter((sub) => !sub.company_response_path)
        .filter(
            (sub) =>
                sub.company_name
                    .toLowerCase()
                    .includes(searchWaiting.value.toLowerCase()) ||
                sub.group.leader.name
                    .toLowerCase()
                    .includes(searchWaiting.value.toLowerCase()) ||
                sub.group.code
                    .toLowerCase()
                    .includes(searchWaiting.value.toLowerCase()),
        );
});

const filteredReceived = computed(() => {
    return props.receivedResponse.filter(
        (sub) =>
            sub.company_name
                .toLowerCase()
                .includes(searchReceived.value.toLowerCase()) ||
            sub.group.leader.name
                .toLowerCase()
                .includes(searchReceived.value.toLowerCase()) ||
            sub.group.code
                .toLowerCase()
                .includes(searchReceived.value.toLowerCase()),
    );
});

// ─── Handlers ─────────────────────────────────────────────────────────────────

function openDetail(sub: Submission) {
    selectedSubmission.value = sub;
    showDetailModal.value = true;
}

function openDecision(sub: Submission) {
    selectedSubmission.value = sub;
    showDecisionModal.value = true;
}
</script>

<template>
    <Head title="Manajemen Siap Magang" />

    <div class="flex-1 space-y-8 p-4 pt-6 md:p-8">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-foreground">
                Manajemen Siap Magang
            </h1>
            <p class="mt-1 text-sm text-muted-foreground">
                Kelola surat permohonan yang telah terbit, pantau proses
                pengajuan ke perusahaan, dan tentukan keputusan penempatan
                magang mahasiswa.
            </p>
        </div>

        <!-- ─── TABEL 1: SIAP CETAK SURAT ─── -->
        <Card class="border-border/80 shadow-xs">
            <CardHeader class="pb-3">
                <div
                    class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
                >
                    <div>
                        <CardTitle
                            class="flex items-center gap-2 text-base font-semibold text-foreground"
                        >
                            <Printer class="h-4 w-4 text-primary" />
                            1. Kelompok Siap Cetak Surat
                        </CardTitle>
                        <CardDescription class="text-xs">
                            Kelompok yang telah disetujui pengajuannya. Siap
                            cetak surat pengantar resmi dan ditandai sedang
                            mengajukan.
                        </CardDescription>
                    </div>
                    <div class="relative w-full md:w-72">
                        <Search
                            class="absolute top-2.5 left-2.5 h-4 w-4 text-muted-foreground"
                        />
                        <Input
                            v-model="searchPrint"
                            placeholder="Cari perusahaan, ketua..."
                            class="h-9 pl-9"
                        />
                    </div>
                </div>
            </CardHeader>
            <CardContent class="p-0">
                <div
                    v-if="filteredPrint.length === 0"
                    class="flex flex-col items-center justify-center p-8 text-center text-muted-foreground"
                >
                    <FileText class="mb-2 h-8 w-8 opacity-50" />
                    <p class="text-xs font-medium">
                        Tidak ada kelompok siap cetak surat.
                    </p>
                </div>
                <div v-else class="overflow-x-auto">
                    <table class="w-full border-collapse text-left text-xs">
                        <thead>
                            <tr
                                class="border-b border-border/60 bg-muted/40 text-[10px] font-semibold text-muted-foreground uppercase"
                            >
                                <th class="p-4">Kode / Ketua</th>
                                <th class="p-4">Perusahaan</th>
                                <th class="p-4 text-center">Anggota</th>
                                <th class="p-4">Disetujui Sejak</th>
                                <th class="p-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/40">
                            <tr
                                v-for="sub in filteredPrint"
                                :key="sub.id"
                                class="transition-colors hover:bg-muted/10"
                            >
                                <td class="p-4">
                                    <div class="font-semibold text-foreground">
                                        {{ sub.group.code }}
                                    </div>
                                    <div
                                        class="text-[10px] text-muted-foreground"
                                    >
                                        {{ sub.group.leader.name }}
                                    </div>
                                </td>
                                <td class="p-4 font-medium text-foreground">
                                    {{ sub.company_name }}
                                </td>
                                <td class="p-4 text-center">
                                    <Badge
                                        variant="secondary"
                                        class="px-2 py-0.5 font-mono text-[10px]"
                                    >
                                        {{ sub.group.memberships_count }} Orang
                                    </Badge>
                                </td>
                                <td class="p-4 text-muted-foreground">
                                    {{ formatDate(sub.updated_at) }}
                                </td>
                                <td class="p-4 text-right">
                                    <Button
                                        size="sm"
                                        variant="outline"
                                        class="h-8 cursor-pointer gap-1.5 font-medium"
                                        @click="openDetail(sub)"
                                    >
                                        Detail & Cetak
                                        <ArrowRight class="h-3 w-3" />
                                    </Button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </CardContent>
        </Card>

        <!-- ─── TABEL 2: MENUNGGU BALASAN ─── -->
        <Card class="border-border/80 shadow-xs">
            <CardHeader class="pb-3">
                <div
                    class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
                >
                    <div>
                        <CardTitle
                            class="flex items-center gap-2 text-base font-semibold text-foreground"
                        >
                            <Clock class="h-4 w-4 text-yellow-500" />
                            2. Kelompok Menunggu Balasan Perusahaan
                        </CardTitle>
                        <CardDescription class="text-xs">
                            Kelompok yang sedang memproses berkas ke perusahaan
                            tujuan. Menunggu mahasiswa mengunggah surat balasan.
                        </CardDescription>
                    </div>
                    <div class="relative w-full md:w-72">
                        <Search
                            class="absolute top-2.5 left-2.5 h-4 w-4 text-muted-foreground"
                        />
                        <Input
                            v-model="searchWaiting"
                            placeholder="Cari perusahaan, ketua..."
                            class="h-9 pl-9"
                        />
                    </div>
                </div>
            </CardHeader>
            <CardContent class="p-0">
                <div
                    v-if="filteredWaiting.length === 0"
                    class="flex flex-col items-center justify-center p-8 text-center text-muted-foreground"
                >
                    <Clock class="mb-2 h-8 w-8 text-yellow-500 opacity-50" />
                    <p class="text-xs font-medium">
                        Tidak ada kelompok yang sedang menunggu balasan.
                    </p>
                </div>
                <div v-else class="overflow-x-auto">
                    <table class="w-full border-collapse text-left text-xs">
                        <thead>
                            <tr
                                class="border-b border-border/60 bg-muted/40 text-[10px] font-semibold text-muted-foreground uppercase"
                            >
                                <th class="p-4">Kode / Ketua</th>
                                <th class="p-4">Perusahaan</th>
                                <th class="p-4 text-center">Anggota</th>
                                <th class="p-4">Mulai Mengajukan</th>
                                <th class="p-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/40">
                            <tr
                                v-for="sub in filteredWaiting"
                                :key="sub.id"
                                class="transition-colors hover:bg-muted/10"
                            >
                                <td class="p-4">
                                    <div class="font-semibold text-foreground">
                                        {{ sub.group.code }}
                                    </div>
                                    <div
                                        class="text-[10px] text-muted-foreground"
                                    >
                                        {{ sub.group.leader.name }}
                                    </div>
                                </td>
                                <td class="p-4 font-medium text-foreground">
                                    {{ sub.company_name }}
                                </td>
                                <td class="p-4 text-center">
                                    <Badge
                                        variant="secondary"
                                        class="px-2 py-0.5 font-mono text-[10px]"
                                    >
                                        {{ sub.group.memberships_count }} Orang
                                    </Badge>
                                </td>
                                <td class="p-4 text-muted-foreground">
                                    {{ formatDate(sub.updated_at) }}
                                </td>
                                <td class="p-4 text-right">
                                    <Button
                                        size="sm"
                                        variant="outline"
                                        class="h-8 cursor-pointer gap-1.5 font-medium"
                                        @click="openDetail(sub)"
                                    >
                                        Detail & Cetak
                                        <ArrowRight class="h-3 w-3" />
                                    </Button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </CardContent>
        </Card>

        <!-- ─── TABEL 3: MENERIMA BALASAN ─── -->
        <Card class="border-border/80 shadow-xs">
            <CardHeader class="pb-3">
                <div
                    class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
                >
                    <div>
                        <CardTitle
                            class="flex items-center gap-2 text-base font-semibold text-foreground"
                        >
                            <CheckCircle2 class="h-4 w-4 text-green-500" />
                            3. Kelompok Menerima Balasan Perusahaan
                        </CardTitle>
                        <CardDescription class="text-xs">
                            Kelompok yang telah mengunggah bukti surat balasan
                            dari perusahaan. Siap untuk proses keputusan
                            penempatan.
                        </CardDescription>
                    </div>
                    <div class="relative w-full md:w-72">
                        <Search
                            class="absolute top-2.5 left-2.5 h-4 w-4 text-muted-foreground"
                        />
                        <Input
                            v-model="searchReceived"
                            placeholder="Cari perusahaan, ketua..."
                            class="h-9 pl-9"
                        />
                    </div>
                </div>
            </CardHeader>
            <CardContent class="p-0">
                <div
                    v-if="filteredReceived.length === 0"
                    class="flex flex-col items-center justify-center p-8 text-center text-muted-foreground"
                >
                    <CheckCircle2
                        class="mb-2 h-8 w-8 text-green-500 opacity-50"
                    />
                    <p class="text-xs font-medium">
                        Tidak ada kelompok yang mengunggah surat balasan
                        perusahaan.
                    </p>
                </div>
                <div v-else class="overflow-x-auto">
                    <table class="w-full border-collapse text-left text-xs">
                        <thead>
                            <tr
                                class="border-b border-border/60 bg-muted/40 text-[10px] font-semibold text-muted-foreground uppercase"
                            >
                                <th class="p-4">Kode / Ketua</th>
                                <th class="p-4">Perusahaan</th>
                                <th class="p-4">Berkas Balasan</th>
                                <th class="p-4 text-center">Anggota</th>
                                <th class="p-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/40">
                            <tr
                                v-for="sub in filteredReceived"
                                :key="sub.id"
                                class="transition-colors hover:bg-muted/10"
                            >
                                <td class="p-4">
                                    <div class="font-semibold text-foreground">
                                        {{ sub.group.code }}
                                    </div>
                                    <div
                                        class="text-[10px] text-muted-foreground"
                                    >
                                        {{ sub.group.leader.name }}
                                    </div>
                                </td>
                                <td class="p-4 font-medium text-foreground">
                                    {{ sub.company_name }}
                                </td>
                                <td class="p-4">
                                    <a
                                        :href="`/storage/${sub.company_response_path}`"
                                        target="_blank"
                                        class="inline-flex items-center gap-1 font-medium text-primary hover:underline"
                                    >
                                        <Download class="h-3 w-3" />
                                        Unduh Surat Balasan
                                    </a>
                                </td>
                                <td class="p-4 text-center">
                                    <Badge
                                        variant="secondary"
                                        class="px-2 py-0.5 font-mono text-[10px]"
                                    >
                                        {{ sub.group.memberships_count }} Orang
                                    </Badge>
                                </td>
                                <td class="p-4 text-right">
                                    <Button
                                        size="sm"
                                        variant="default"
                                        class="h-8 cursor-pointer bg-green-600 font-medium text-white hover:bg-green-700"
                                        @click="openDecision(sub)"
                                    >
                                        Proses Hasil
                                    </Button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </CardContent>
        </Card>

        <!-- EXTRACTED DIALOGS -->
        <SubmissionDetailDialog
            v-model:open="showDetailModal"
            :submission="selectedSubmission"
            @success="selectedSubmission = null"
        />

        <CompanyDecisionDialog
            v-model:open="showDecisionModal"
            :submission="selectedSubmission"
            @success="selectedSubmission = null"
        />
    </div>
</template>
