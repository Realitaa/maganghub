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
    FileSearch,
} from '@lucide/vue';
import { TabsContent, TabsList, TabsRoot, TabsTrigger } from 'reka-ui';
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
import { ScrollArea } from '@/components/ui/scroll-area';
import { useIdTimeFormat } from '@/composables/useIdTimeFormat';
import { downloadResponse } from '@/routes/groups/submissions';
import { index as preparationsIndex } from '@/routes/internships/preparations';
import type { Submission } from '@/types';

// Define layout breadcrumbs
defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Persiapan Magang',
                href: preparationsIndex.url(),
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

const activeTab = ref<'print' | 'waiting' | 'received'>('print');
const searchQuery = ref('');

const showDetailModal = ref(false);
const showDecisionModal = ref(false);
const selectedSubmission = ref<Submission | null>(null);

// ─── Computed Filters ─────────────────────────────────────────────────────────

function matchesSearch(sub: Submission, query: string): boolean {
    if (!query.trim()) {
        return true;
    }

    const q = query.toLowerCase();
    const company = sub.company_name?.toLowerCase() || '';
    const leaderName = sub.group?.leader?.name?.toLowerCase() || '';
    const leaderNim = sub.group?.leader?.nim?.toLowerCase() || '';

    return (
        company.includes(q) || leaderName.includes(q) || leaderNim.includes(q)
    );
}

const filteredPrint = computed(() => {
    return props.readyToPrint.filter((sub) =>
        matchesSearch(sub, searchQuery.value),
    );
});

const filteredWaiting = computed(() => {
    return props.waitingResponse.filter((sub) =>
        matchesSearch(sub, searchQuery.value),
    );
});

const filteredReceived = computed(() => {
    return props.receivedResponse.filter((sub) =>
        matchesSearch(sub, searchQuery.value),
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
    <Head title="Persiapan Magang" />

    <div class="flex-1 space-y-6 p-4 pt-6 md:p-8">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-foreground">
                Persiapan Magang
            </h1>
            <p class="mt-1 text-sm text-muted-foreground">
                Kelola surat permohonan yang telah terbit, pantau proses
                pengajuan ke perusahaan, dan tentukan keputusan penempatan
                magang mahasiswa.
            </p>
        </div>

        <!-- ─── Tab Workflow ─── -->
        <TabsRoot v-model="activeTab" class="w-full space-y-6">
            <div
                class="flex flex-col gap-4 border-b border-border/60 sm:flex-row sm:items-center sm:justify-between"
            >
                <ScrollArea class="w-full sm:w-auto">
                    <TabsList
                        class="flex h-auto w-max gap-0 rounded-none bg-transparent p-0"
                    >
                        <TabsTrigger
                            value="print"
                            class="relative flex shrink-0 cursor-pointer items-center gap-2 rounded-none border-b-2 border-transparent px-4 py-3 text-sm font-medium text-muted-foreground transition-all hover:text-foreground data-[state=active]:border-primary data-[state=active]:font-semibold data-[state=active]:text-foreground data-[state=active]:shadow-none"
                        >
                            <Printer class="h-4 w-4" />
                            <span>Siap Cetak Surat</span>
                            <Badge
                                class="h-5 min-w-5 rounded-full px-1.5 font-mono text-xs"
                            >
                                {{ readyToPrint.length }}
                            </Badge>
                        </TabsTrigger>

                        <TabsTrigger
                            value="waiting"
                            class="relative flex shrink-0 cursor-pointer items-center gap-2 rounded-none border-b-2 border-transparent px-4 py-3 text-sm font-medium text-muted-foreground transition-all hover:text-foreground data-[state=active]:border-primary data-[state=active]:font-semibold data-[state=active]:text-foreground data-[state=active]:shadow-none"
                        >
                            <Clock class="h-4 w-4" />
                            <span>Menunggu Balasan</span>
                            <Badge
                                class="h-5 min-w-5 rounded-full px-1.5 font-mono text-xs"
                            >
                                {{ waitingResponse.length }}
                            </Badge>
                        </TabsTrigger>

                        <TabsTrigger
                            value="received"
                            class="relative flex shrink-0 cursor-pointer items-center gap-2 rounded-none border-b-2 border-transparent px-4 py-3 text-sm font-medium text-muted-foreground transition-all hover:text-foreground data-[state=active]:border-primary data-[state=active]:font-semibold data-[state=active]:text-foreground data-[state=active]:shadow-none"
                        >
                            <FileSearch class="h-4 w-4" />
                            <span>Review Balasan / LoA</span>
                            <Badge
                                class="h-5 min-w-5 rounded-full px-1.5 font-mono text-xs"
                            >
                                {{ receivedResponse.length }}
                            </Badge>
                        </TabsTrigger>
                    </TabsList>
                </ScrollArea>

                <!-- Unified Search Input -->
                <div class="relative w-full pb-3 sm:w-72 sm:pb-0">
                    <Search
                        class="absolute top-2.5 left-2.5 h-4 w-4 text-muted-foreground"
                    />
                    <Input
                        v-model="searchQuery"
                        placeholder="Cari perusahaan, ketua, NIM..."
                        class="h-9 pl-9 text-xs"
                    />
                </div>
            </div>

            <!-- ─── TAB 1: SIAP CETAK SURAT ─── -->
            <TabsContent value="print">
                <Card class="border-border/80 shadow-xs">
                    <CardHeader class="pb-3">
                        <CardTitle
                            class="flex items-center gap-2 text-base font-semibold text-foreground"
                        >
                            <Printer class="h-4 w-4 text-primary" />
                            Kelompok Siap Cetak Surat
                        </CardTitle>
                        <CardDescription class="text-xs">
                            Kelompok yang telah disetujui pengajuannya. Siap
                            cetak surat pengantar resmi dan ditandai sedang
                            mengajukan.
                        </CardDescription>
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
                            <table
                                class="w-full border-collapse text-left text-xs"
                            >
                                <thead>
                                    <tr
                                        class="border-b border-border/60 bg-muted/40 text-[10px] font-semibold text-muted-foreground uppercase"
                                    >
                                        <th class="p-4">Ketua Kelompok</th>
                                        <th class="p-4">Perusahaan Tujuan</th>
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
                                            <div
                                                class="text-xs font-semibold text-foreground"
                                            >
                                                {{ sub.group.leader.name }}
                                            </div>
                                            <div
                                                class="text-[10px] text-muted-foreground"
                                            >
                                                {{
                                                    sub.group.leader.nim || '-'
                                                }}
                                            </div>
                                        </td>
                                        <td
                                            class="p-4 font-medium text-foreground"
                                        >
                                            {{ sub.company_name }}
                                        </td>
                                        <td class="p-4 text-center">
                                            <Badge
                                                variant="secondary"
                                                class="px-2 py-0.5 font-mono text-[10px]"
                                            >
                                                {{
                                                    sub.group.memberships_count
                                                }}
                                                Orang
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
            </TabsContent>

            <!-- ─── TAB 2: MENUNGGU BALASAN ─── -->
            <TabsContent value="waiting">
                <Card class="border-border/80 shadow-xs">
                    <CardHeader class="pb-3">
                        <CardTitle
                            class="flex items-center gap-2 text-base font-semibold text-foreground"
                        >
                            <Clock class="h-4 w-4 text-yellow-500" />
                            Kelompok Menunggu Balasan Perusahaan
                        </CardTitle>
                        <CardDescription class="text-xs">
                            Kelompok yang sedang memproses berkas ke perusahaan
                            tujuan. Menunggu mahasiswa mengunggah surat balasan.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="p-0">
                        <div
                            v-if="filteredWaiting.length === 0"
                            class="flex flex-col items-center justify-center p-8 text-center text-muted-foreground"
                        >
                            <Clock
                                class="mb-2 h-8 w-8 text-yellow-500 opacity-50"
                            />
                            <p class="text-xs font-medium">
                                Tidak ada kelompok yang sedang menunggu balasan.
                            </p>
                        </div>
                        <div v-else class="overflow-x-auto">
                            <table
                                class="w-full border-collapse text-left text-xs"
                            >
                                <thead>
                                    <tr
                                        class="border-b border-border/60 bg-muted/40 text-[10px] font-semibold text-muted-foreground uppercase"
                                    >
                                        <th class="p-4">Ketua Kelompok</th>
                                        <th class="p-4">Perusahaan Tujuan</th>
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
                                            <div
                                                class="text-xs font-semibold text-foreground"
                                            >
                                                {{ sub.group.leader.name }}
                                            </div>
                                            <div
                                                class="text-[10px] text-muted-foreground"
                                            >
                                                {{
                                                    sub.group.leader.nim || '-'
                                                }}
                                            </div>
                                        </td>
                                        <td
                                            class="p-4 font-medium text-foreground"
                                        >
                                            {{ sub.company_name }}
                                        </td>
                                        <td class="p-4 text-center">
                                            <Badge
                                                variant="secondary"
                                                class="px-2 py-0.5 font-mono text-[10px]"
                                            >
                                                {{
                                                    sub.group.memberships_count
                                                }}
                                                Orang
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
            </TabsContent>

            <!-- ─── TAB 3: REVIEW BALASAN / LoA ─── -->
            <TabsContent value="received">
                <Card class="border-border/80 shadow-xs">
                    <CardHeader class="pb-3">
                        <CardTitle
                            class="flex items-center gap-2 text-base font-semibold text-foreground"
                        >
                            <CheckCircle2 class="h-4 w-4 text-green-500" />
                            Kelompok Menerima Balasan Perusahaan
                        </CardTitle>
                        <CardDescription class="text-xs">
                            Kelompok yang telah mengunggah bukti surat balasan
                            dari perusahaan. Siap untuk proses keputusan
                            penempatan.
                        </CardDescription>
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
                            <table
                                class="w-full border-collapse text-left text-xs"
                            >
                                <thead>
                                    <tr
                                        class="border-b border-border/60 bg-muted/40 text-[10px] font-semibold text-muted-foreground uppercase"
                                    >
                                        <th class="p-4">Ketua Kelompok</th>
                                        <th class="p-4">Perusahaan Tujuan</th>
                                        <th class="p-4">Berkas Balasan</th>
                                        <th class="p-4 text-center">Anggota</th>
                                        <th class="p-4">Tanggal Upload</th>
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
                                            <div
                                                class="text-xs font-semibold text-foreground"
                                            >
                                                {{ sub.group.leader.name }}
                                            </div>
                                            <div
                                                class="text-[10px] text-muted-foreground"
                                            >
                                                {{
                                                    sub.group.leader.nim || '-'
                                                }}
                                            </div>
                                        </td>
                                        <td
                                            class="p-4 font-medium text-foreground"
                                        >
                                            {{ sub.company_name }}
                                        </td>
                                        <td class="p-4">
                                            <a
                                                :href="
                                                    downloadResponse.url({
                                                        submission: sub.id,
                                                    })
                                                "
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
                                                {{
                                                    sub.group.memberships_count
                                                }}
                                                Orang
                                            </Badge>
                                        </td>
                                        <td class="p-4">
                                            {{ formatDate(sub.updated_at) }}
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
            </TabsContent>
        </TabsRoot>

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
