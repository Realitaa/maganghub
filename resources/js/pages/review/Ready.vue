<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
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
    Search,
    Printer,
    Download,
} from '@lucide/vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
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
import { Input } from '@/components/ui/input';
import { index as readyIndex } from '@/routes/review/ready';
import { markApplying, companyDecision } from '@/routes/review/submissions';
import { downloadLetter } from '@/routes/groups/submissions';

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

// ─── Types ────────────────────────────────────────────────────────────────────

interface MemberDetail {
    id: number;
    name: string;
    email: string;
    nim?: string;
    phone?: string;
    address?: string;
    gender?: string;
    semester?: number;
}

interface SubmissionMembership {
    id: number;
    user: MemberDetail;
    status: string;
    rejection_note?: string | null;
}

interface Submission {
    id: number;
    company_name: string;
    company_address: string;
    company_contact: string;
    division: string;
    field_of_interest: string;
    start_date: string;
    end_date: string;
    status: string;
    company_response_path: string | null;
    updated_at: string;
    group: {
        id: number;
        code: string;
        leader_id: number;
        memberships_count: number;
        leader: MemberDetail;
    };
    submission_memberships: SubmissionMembership[];
}

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
const showPartialModal = ref(false);

const selectedSubmission = ref<Submission | null>(null);
const localProcessing = ref(false);

// Company decision form state
const decisionType = ref<'all_accepted' | 'all_rejected' | 'partially_accepted' | null>(null);
const partialDecisions = ref<Record<number, { status: 'accepted' | 'rejected'; rejection_note: string }>>({});
const newLeaderId = ref<number | null>(null);
const submissionError = ref<string | null>(null);

// ─── Computed Filters ─────────────────────────────────────────────────────────

const filteredPrint = computed(() => {
    return props.readyToPrint.filter(sub => 
        sub.company_name.toLowerCase().includes(searchPrint.value.toLowerCase()) ||
        sub.group.leader.name.toLowerCase().includes(searchPrint.value.toLowerCase()) ||
        sub.group.code.toLowerCase().includes(searchPrint.value.toLowerCase())
    );
});

const filteredWaiting = computed(() => {
    // Exclude the ones that have uploaded a company response (receivedResponse)
    return props.waitingResponse
        .filter(sub => !sub.company_response_path)
        .filter(sub => 
            sub.company_name.toLowerCase().includes(searchWaiting.value.toLowerCase()) ||
            sub.group.leader.name.toLowerCase().includes(searchWaiting.value.toLowerCase()) ||
            sub.group.code.toLowerCase().includes(searchWaiting.value.toLowerCase())
        );
});

const filteredReceived = computed(() => {
    return props.receivedResponse.filter(sub => 
        sub.company_name.toLowerCase().includes(searchReceived.value.toLowerCase()) ||
        sub.group.leader.name.toLowerCase().includes(searchReceived.value.toLowerCase()) ||
        sub.group.code.toLowerCase().includes(searchReceived.value.toLowerCase())
    );
});

const acceptedMembersForDropdown = computed(() => {
    if (!selectedSubmission.value) return [];
    return selectedSubmission.value.submission_memberships.filter(m => {
        const dec = partialDecisions.value[m.user.id];
        return dec && dec.status === 'accepted';
    }).map(m => m.user);
});

// ─── Handlers ─────────────────────────────────────────────────────────────────

function openDetail(sub: Submission) {
    selectedSubmission.value = sub;
    showDetailModal.value = true;
}

function handleDownloadLetter(subId: number) {
    window.open(downloadLetter.url({ submission: subId }), '_blank');
}

function handleMarkApplying(subId: number) {
    localProcessing.value = true;
    router.post(markApplying.url({ submission: subId }), {}, {
        onFinish: () => {
            localProcessing.value = false;
            showDetailModal.value = false;
            selectedSubmission.value = null;
        }
    });
}

function openDecision(sub: Submission) {
    selectedSubmission.value = sub;
    decisionType.value = null;
    submissionError.value = null;
    
    // Initialize partial decisions dictionary
    const decisions: Record<number, { status: 'accepted' | 'rejected'; rejection_note: string }> = {};
    sub.submission_memberships.forEach(m => {
        decisions[m.user.id] = {
            status: 'accepted',
            rejection_note: ''
        };
    });
    partialDecisions.value = decisions;
    newLeaderId.value = null;
    
    showDecisionModal.value = true;
}

function selectDecision(type: 'all_accepted' | 'all_rejected' | 'partially_accepted') {
    decisionType.value = type;
    if (type === 'partially_accepted') {
        showDecisionModal.value = false;
        showPartialModal.value = true;
    } else {
        submitDecision(type);
    }
}

function submitDecision(type: 'all_accepted' | 'all_rejected') {
    if (!selectedSubmission.value) return;
    
    localProcessing.value = true;
    submissionError.value = null;
    
    router.post(companyDecision.url({ submission: selectedSubmission.value.id }), {
        decision: type
    }, {
        onSuccess: () => {
            showDecisionModal.value = false;
            selectedSubmission.value = null;
        },
        onError: (errors) => {
            submissionError.value = errors.error || 'Terjadi kesalahan saat memproses keputusan.';
        },
        onFinish: () => {
            localProcessing.value = false;
        }
    });
}

function submitPartialDecision() {
    if (!selectedSubmission.value) return;
    
    submissionError.value = null;
    
    const decisionsArray = Object.entries(partialDecisions.value).map(([userId, dec]) => ({
        user_id: parseInt(userId),
        status: dec.status,
        rejection_note: dec.status === 'rejected' ? dec.rejection_note : null
    }));
    
    const acceptedUsers = decisionsArray.filter(d => d.status === 'accepted');
    const rejectedUsers = decisionsArray.filter(d => d.status === 'rejected');
    
    if (acceptedUsers.length === 0) {
        submissionError.value = 'Minimal harus ada satu anggota yang diterima. Jika semua ditolak, silakan gunakan opsi Semua Ditolak.';
        return;
    }
    
    // Check if leader is rejected
    const leaderId = selectedSubmission.value.group.leader_id;
    const isLeaderRejected = rejectedUsers.some(u => u.user_id === leaderId);
    
    if (isLeaderRejected && !newLeaderId.value) {
        submissionError.value = 'Ketua kelompok ditolak, Anda wajib menunjuk Ketua baru dari anggota yang diterima.';
        return;
    }
    
    // Check if rejection notes are filled for rejected members
    for (const dec of decisionsArray) {
        if (dec.status === 'rejected' && (!dec.rejection_note || !dec.rejection_note.trim())) {
            submissionError.value = 'Catatan penolakan wajib diisi untuk semua anggota yang ditolak.';
            return;
        }
    }
    
    localProcessing.value = true;
    
    router.post(companyDecision.url({ submission: selectedSubmission.value.id }), {
        decision: 'partially_accepted',
        member_decisions: decisionsArray,
        new_leader_id: isLeaderRejected ? newLeaderId.value : null
    }, {
        onSuccess: () => {
            showPartialModal.value = false;
            selectedSubmission.value = null;
        },
        onError: (errors) => {
            submissionError.value = errors.error || 'Terjadi kesalahan saat memproses keputusan.';
        },
        onFinish: () => {
            localProcessing.value = false;
        }
    });
}

function handleBackToDecision() {
    showPartialModal.value = false;
    showDecisionModal.value = true;
}

// Formatting dates helper
function formatDate(dateStr?: string) {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
}
</script>

<template>
    <Head title="Manajemen Siap Magang" />

    <div class="flex-1 space-y-8 p-4 pt-6 md:p-8">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-foreground">Manajemen Siap Magang</h1>
            <p class="text-sm text-muted-foreground mt-1">
                Kelola surat permohonan yang telah terbit, pantau proses pengajuan ke perusahaan, dan tentukan keputusan penempatan magang mahasiswa.
            </p>
        </div>

        <!-- ─── TABEL 1: SIAP CETAK SURAT ─── -->
        <Card class="border-border/80 shadow-xs">
            <CardHeader class="pb-3">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <CardTitle class="text-base font-semibold flex items-center gap-2 text-foreground">
                            <Printer class="h-4 w-4 text-primary" />
                            1. Kelompok Siap Cetak Surat
                        </CardTitle>
                        <CardDescription class="text-xs">
                            Kelompok yang telah disetujui pengajuannya. Siap cetak surat pengantar resmi dan ditandai sedang mengajukan.
                        </CardDescription>
                    </div>
                    <div class="relative w-full md:w-72">
                        <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                        <Input
                            v-model="searchPrint"
                            placeholder="Cari perusahaan, ketua..."
                            class="pl-9 h-9"
                        />
                    </div>
                </div>
            </CardHeader>
            <CardContent class="p-0">
                <div v-if="filteredPrint.length === 0" class="flex flex-col items-center justify-center p-8 text-center text-muted-foreground">
                    <FileText class="h-8 w-8 mb-2 opacity-50" />
                    <p class="text-xs font-medium">Tidak ada kelompok siap cetak surat.</p>
                </div>
                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-border/60 bg-muted/40 text-[10px] font-semibold text-muted-foreground uppercase">
                                <th class="p-4">Kode / Ketua</th>
                                <th class="p-4">Perusahaan</th>
                                <th class="p-4 text-center">Anggota</th>
                                <th class="p-4">Disetujui Sejak</th>
                                <th class="p-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/40">
                            <tr v-for="sub in filteredPrint" :key="sub.id" class="hover:bg-muted/10 transition-colors">
                                <td class="p-4">
                                    <div class="font-semibold text-foreground">{{ sub.group.code }}</div>
                                    <div class="text-[10px] text-muted-foreground">{{ sub.group.leader.name }}</div>
                                </td>
                                <td class="p-4 font-medium text-foreground">{{ sub.company_name }}</td>
                                <td class="p-4 text-center">
                                    <Badge variant="secondary" class="font-mono text-[10px] px-2 py-0.5">
                                        {{ sub.group.memberships_count }} Orang
                                    </Badge>
                                </td>
                                <td class="p-4 text-muted-foreground">{{ formatDate(sub.updated_at) }}</td>
                                <td class="p-4 text-right">
                                    <Button
                                        size="xs"
                                        variant="outline"
                                        class="h-8 gap-1.5 font-medium cursor-pointer"
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
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <CardTitle class="text-base font-semibold flex items-center gap-2 text-foreground">
                            <Clock class="h-4 w-4 text-yellow-500" />
                            2. Kelompok Menunggu Balasan Perusahaan
                        </CardTitle>
                        <CardDescription class="text-xs">
                            Kelompok yang sedang memproses berkas ke perusahaan tujuan. Menunggu mahasiswa mengunggah surat balasan.
                        </CardDescription>
                    </div>
                    <div class="relative w-full md:w-72">
                        <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                        <Input
                            v-model="searchWaiting"
                            placeholder="Cari perusahaan, ketua..."
                            class="pl-9 h-9"
                        />
                    </div>
                </div>
            </CardHeader>
            <CardContent class="p-0">
                <div v-if="filteredWaiting.length === 0" class="flex flex-col items-center justify-center p-8 text-center text-muted-foreground">
                    <Clock class="h-8 w-8 mb-2 opacity-50 text-yellow-500" />
                    <p class="text-xs font-medium">Tidak ada kelompok yang sedang menunggu balasan.</p>
                </div>
                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-border/60 bg-muted/40 text-[10px] font-semibold text-muted-foreground uppercase">
                                <th class="p-4">Kode / Ketua</th>
                                <th class="p-4">Perusahaan</th>
                                <th class="p-4 text-center">Anggota</th>
                                <th class="p-4">Mulai Mengajukan</th>
                                <th class="p-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/40">
                            <tr v-for="sub in filteredWaiting" :key="sub.id" class="hover:bg-muted/10 transition-colors">
                                <td class="p-4">
                                    <div class="font-semibold text-foreground">{{ sub.group.code }}</div>
                                    <div class="text-[10px] text-muted-foreground">{{ sub.group.leader.name }}</div>
                                </td>
                                <td class="p-4 font-medium text-foreground">{{ sub.company_name }}</td>
                                <td class="p-4 text-center">
                                    <Badge variant="secondary" class="font-mono text-[10px] px-2 py-0.5">
                                        {{ sub.group.memberships_count }} Orang
                                    </Badge>
                                </td>
                                <td class="p-4 text-muted-foreground">{{ formatDate(sub.updated_at) }}</td>
                                <td class="p-4 text-right">
                                    <Button
                                        size="xs"
                                        variant="outline"
                                        class="h-8 gap-1.5 font-medium cursor-pointer"
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
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <CardTitle class="text-base font-semibold flex items-center gap-2 text-foreground">
                            <CheckCircle2 class="h-4 w-4 text-green-500" />
                            3. Kelompok Menerima Balasan Perusahaan
                        </CardTitle>
                        <CardDescription class="text-xs">
                            Kelompok yang telah mengunggah bukti surat balasan dari perusahaan. Siap untuk proses keputusan penempatan.
                        </CardDescription>
                    </div>
                    <div class="relative w-full md:w-72">
                        <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                        <Input
                            v-model="searchReceived"
                            placeholder="Cari perusahaan, ketua..."
                            class="pl-9 h-9"
                        />
                    </div>
                </div>
            </CardHeader>
            <CardContent class="p-0">
                <div v-if="filteredReceived.length === 0" class="flex flex-col items-center justify-center p-8 text-center text-muted-foreground">
                    <CheckCircle2 class="h-8 w-8 mb-2 opacity-50 text-green-500" />
                    <p class="text-xs font-medium">Tidak ada kelompok yang mengunggah surat balasan perusahaan.</p>
                </div>
                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-border/60 bg-muted/40 text-[10px] font-semibold text-muted-foreground uppercase">
                                <th class="p-4">Kode / Ketua</th>
                                <th class="p-4">Perusahaan</th>
                                <th class="p-4">Berkas Balasan</th>
                                <th class="p-4 text-center">Anggota</th>
                                <th class="p-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/40">
                            <tr v-for="sub in filteredReceived" :key="sub.id" class="hover:bg-muted/10 transition-colors">
                                <td class="p-4">
                                    <div class="font-semibold text-foreground">{{ sub.group.code }}</div>
                                    <div class="text-[10px] text-muted-foreground">{{ sub.group.leader.name }}</div>
                                </td>
                                <td class="p-4 font-medium text-foreground">{{ sub.company_name }}</td>
                                <td class="p-4">
                                    <a
                                        :href="`/storage/${sub.company_response_path}`"
                                        target="_blank"
                                        class="inline-flex items-center gap-1 text-primary hover:underline font-medium"
                                    >
                                        <Download class="h-3 w-3" />
                                        Unduh Surat Balasan
                                    </a>
                                </td>
                                <td class="p-4 text-center">
                                    <Badge variant="secondary" class="font-mono text-[10px] px-2 py-0.5">
                                        {{ sub.group.memberships_count }} Orang
                                    </Badge>
                                </td>
                                <td class="p-4 text-right">
                                    <Button
                                        size="xs"
                                        variant="default"
                                        class="h-8 bg-green-600 hover:bg-green-700 text-white font-medium cursor-pointer"
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

        <!-- ─── MODAL DETAIL & PRINT ─── -->
        <Dialog v-model:open="showDetailModal">
            <DialogContent class="sm:max-w-[650px] max-h-[85vh] overflow-y-auto">
                <DialogHeader class="border-b border-border/60 pb-4">
                    <DialogTitle class="text-base font-bold text-foreground">Detail Pengajuan & Cetak Surat</DialogTitle>
                    <DialogDescription class="text-xs">
                        Informasi data pengajuan dan anggota untuk dokumen cetak surat permohonan magang kelompok.
                    </DialogDescription>
                </DialogHeader>

                <div v-if="selectedSubmission" class="space-y-6 pt-4">
                    <!-- Perusahaan Info -->
                    <div class="space-y-3">
                        <h3 class="text-xs font-semibold flex items-center gap-1.5 text-primary uppercase tracking-wider">
                            <Building2 class="h-4 w-4" />
                            Instansi / Perusahaan Tujuan
                        </h3>
                        <div class="grid gap-3 rounded-xl border border-border/80 bg-muted/10 p-4 text-xs sm:grid-cols-2">
                            <div>
                                <Label class="text-[10px] text-muted-foreground">Nama Perusahaan</Label>
                                <p class="font-medium mt-0.5 text-foreground">{{ selectedSubmission.company_name }}</p>
                            </div>
                            <div>
                                <Label class="text-[10px] text-muted-foreground">Bidang yang Diminati</Label>
                                <p class="font-medium mt-0.5 text-foreground">{{ selectedSubmission.field_of_interest }}</p>
                            </div>
                            <div>
                                <Label class="text-[10px] text-muted-foreground">Divisi Pekerjaan (Opsional)</Label>
                                <p class="font-medium mt-0.5 text-foreground">{{ selectedSubmission.division || '-' }}</p>
                            </div>
                            <div>
                                <Label class="text-[10px] text-muted-foreground">Kontak Hubungan</Label>
                                <p class="font-medium mt-0.5 flex items-center gap-1 text-foreground">
                                    <Phone class="h-3 w-3 text-muted-foreground" />
                                    {{ selectedSubmission.company_contact }}
                                </p>
                            </div>
                            <div>
                                <Label class="text-[10px] text-muted-foreground">Tanggal Pelaksanaan</Label>
                                <p class="font-medium mt-0.5 flex items-center gap-1 text-foreground">
                                    <Calendar class="h-3 w-3 text-muted-foreground" />
                                    {{ formatDate(selectedSubmission.start_date) }} - {{ formatDate(selectedSubmission.end_date) }}
                                </p>
                            </div>
                            <div class="sm:col-span-2 border-t border-border/60 pt-2">
                                <Label class="text-[10px] text-muted-foreground">Alamat Lengkap</Label>
                                <p class="mt-0.5 flex items-start gap-1 text-foreground">
                                    <MapPin class="h-3.5 w-3.5 text-muted-foreground mt-0.5 flex-shrink-0" />
                                    {{ selectedSubmission.company_address }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Anggota List -->
                    <div class="space-y-3">
                        <h3 class="text-xs font-semibold flex items-center gap-1.5 text-primary uppercase tracking-wider">
                            <Users class="h-4 w-4" />
                            Daftar Anggota Kelompok
                        </h3>
                        <div class="divide-y divide-border/60 rounded-xl border border-border/80 bg-background overflow-hidden text-xs">
                            <div
                                v-for="membership in selectedSubmission.submission_memberships"
                                :key="membership.id"
                                class="p-3"
                            >
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/10 text-xs font-semibold text-primary">
                                            {{ membership.user.name.charAt(0).toUpperCase() }}
                                        </div>
                                        <div>
                                            <p class="font-semibold text-foreground">{{ membership.user.name }}</p>
                                            <p class="text-[10px] text-muted-foreground">{{ membership.user.nim }} | Semester {{ membership.user.semester ?? '-' }}</p>
                                        </div>
                                    </div>
                                    <Badge
                                        v-if="membership.user.id === selectedSubmission.group.leader_id"
                                        variant="secondary"
                                        class="text-[9px] py-0.5 px-2"
                                    >
                                        Ketua Kelompok
                                    </Badge>
                                </div>
                                <div class="mt-2 pl-11 grid grid-cols-2 gap-2 text-[10px] text-muted-foreground border-t border-border/20 pt-2">
                                    <div>Email: <span class="font-medium text-foreground">{{ membership.user.email }}</span></div>
                                    <div>Telepon: <span class="font-medium text-foreground">{{ membership.user.phone || '-' }}</span></div>
                                    <div class="col-span-2">Alamat: <span class="font-medium text-foreground">{{ membership.user.address || '-' }}</span></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-between items-center border-t border-border/60 pt-4">
                        <Button
                            variant="outline"
                            class="border-primary/30 text-primary hover:bg-primary/5 cursor-pointer"
                            @click="handleDownloadLetter(selectedSubmission.id)"
                            id="btn-print-letter"
                        >
                            <Printer class="mr-2 h-4 w-4" />
                            Cetak Surat Permohonan
                        </Button>
                        
                        <div class="flex gap-2">
                            <Button variant="outline" @click="showDetailModal = false">Tutup</Button>
                            
                            <Button
                                v-if="selectedSubmission.status === 'letter_published'"
                                variant="default"
                                class="bg-primary hover:bg-primary/95 cursor-pointer"
                                @click="handleMarkApplying(selectedSubmission.id)"
                                :disabled="localProcessing"
                                id="btn-mark-applying"
                            >
                                <Spinner v-if="localProcessing" class="mr-2 h-4 w-4 animate-spin" />
                                Tandai Mengajukan
                            </Button>
                        </div>
                    </div>
                </div>
            </DialogContent>
        </Dialog>

        <!-- ─── MODAL KEPUTUSAN PENEMPATAN UTAMA ─── -->
        <Dialog v-model:open="showDecisionModal">
            <DialogContent class="sm:max-w-[450px]">
                <DialogHeader class="border-b border-border/60 pb-3">
                    <DialogTitle class="text-base font-bold text-foreground">Proses Hasil Balasan Perusahaan</DialogTitle>
                    <DialogDescription class="text-xs">
                        Pilih keputusan akhir penempatan magang berdasarkan surat balasan dari perusahaan tujuan.
                    </DialogDescription>
                </DialogHeader>

                <div v-if="submissionError" class="rounded-lg bg-destructive/15 p-3 text-xs text-destructive border border-destructive/20 mt-2">
                    {{ submissionError }}
                </div>

                <div class="space-y-4 py-4">
                    <div class="grid gap-3">
                        <Button
                            variant="outline"
                            class="h-14 justify-start px-4 text-left border-border/80 hover:bg-green-50/20 hover:border-green-500 hover:text-green-600 dark:hover:bg-green-950/20 dark:hover:border-green-600 transition-all cursor-pointer"
                            @click="selectDecision('all_accepted')"
                            :disabled="localProcessing"
                            id="btn-decision-all-accepted"
                        >
                            <CheckCircle2 class="mr-3 h-5 w-5 text-green-500" />
                            <div>
                                <div class="font-semibold text-foreground text-xs">Semua Diterima</div>
                                <div class="text-[10px] text-muted-foreground font-normal">Seluruh anggota kelompok resmi diterima magang.</div>
                            </div>
                        </Button>

                        <Button
                            variant="outline"
                            class="h-14 justify-start px-4 text-left border-border/80 hover:bg-red-50/20 hover:border-red-500 hover:text-red-600 dark:hover:bg-red-950/20 dark:hover:border-red-600 transition-all cursor-pointer"
                            @click="selectDecision('all_rejected')"
                            :disabled="localProcessing"
                            id="btn-decision-all-rejected"
                        >
                            <XCircle class="mr-3 h-5 w-5 text-red-500" />
                            <div>
                                <div class="font-semibold text-foreground text-xs">Semua Ditolak</div>
                                <div class="text-[10px] text-muted-foreground font-normal">Seluruh anggota kelompok ditolak, kelompok dibebaskan.</div>
                            </div>
                        </Button>

                        <Button
                            variant="outline"
                            class="h-14 justify-start px-4 text-left border-border/80 hover:bg-blue-50/20 hover:border-blue-500 hover:text-blue-600 dark:hover:bg-blue-950/20 dark:hover:border-blue-600 transition-all cursor-pointer"
                            @click="selectDecision('partially_accepted')"
                            :disabled="localProcessing"
                            id="btn-decision-partial"
                        >
                            <Users class="mr-3 h-5 w-5 text-blue-500" />
                            <div>
                                <div class="font-semibold text-foreground text-xs">Sebagian Diterima</div>
                                <div class="text-[10px] text-muted-foreground font-normal">Pilih secara spesifik siapa saja anggota yang diterima/ditolak.</div>
                            </div>
                        </Button>
                    </div>
                </div>

                <DialogFooter class="border-t border-border/60 pt-3">
                    <Button variant="outline" @click="showDecisionModal = false" :disabled="localProcessing">Batal</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- ─── MODAL DETAIL SEBAGIAN DITERIMA ─── -->
        <Dialog v-model:open="showPartialModal">
            <DialogContent class="sm:max-w-[600px] max-h-[85vh] overflow-y-auto">
                <DialogHeader class="border-b border-border/60 pb-3">
                    <DialogTitle class="text-base font-bold text-foreground">Atur Penempatan Sebagian Anggota</DialogTitle>
                    <DialogDescription class="text-xs">
                        Tentukan hasil untuk masing-masing anggota kelompok magang secara spesifik.
                    </DialogDescription>
                </DialogHeader>

                <div v-if="submissionError" class="rounded-lg bg-destructive/15 p-3 text-xs text-destructive border border-destructive/20 mt-1">
                    {{ submissionError }}
                </div>

                <div v-if="selectedSubmission" class="space-y-6 py-4">
                    <!-- Members status selector -->
                    <div class="space-y-4">
                        <Label class="text-xs font-bold uppercase tracking-wider text-muted-foreground">Konfirmasi Anggota</Label>
                        
                        <div class="divide-y divide-border/60 rounded-xl border border-border/80 bg-background overflow-hidden text-xs">
                            <div
                                v-for="membership in selectedSubmission.submission_memberships"
                                :key="membership.id"
                                class="p-4 space-y-3"
                            >
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/10 text-xs font-semibold text-primary">
                                            {{ membership.user.name.charAt(0).toUpperCase() }}
                                        </div>
                                        <div>
                                            <p class="font-semibold text-foreground flex items-center gap-2">
                                                {{ membership.user.name }}
                                                <Badge
                                                    v-slot:default
                                                    v-if="membership.user.id === selectedSubmission.group.leader_id"
                                                    variant="secondary"
                                                    class="text-[8px] py-0 px-1 font-normal"
                                                >
                                                    Ketua
                                                </Badge>
                                            </p>
                                            <p class="text-[10px] text-muted-foreground">{{ membership.user.nim }}</p>
                                        </div>
                                    </div>
                                    
                                    <!-- Radio Toggle -->
                                    <div class="flex gap-2">
                                        <label
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border text-[11px] font-medium transition-all cursor-pointer"
                                            :class="partialDecisions[membership.user.id]?.status === 'accepted'
                                                ? 'bg-green-500/10 text-green-600 border-green-500/30'
                                                : 'bg-muted/10 text-muted-foreground border-border/80'"
                                        >
                                            <input
                                                type="radio"
                                                v-model="partialDecisions[membership.user.id].status"
                                                value="accepted"
                                                class="sr-only"
                                                @change="() => {
                                                    // Auto reset newLeaderId if the leader is set back to accepted
                                                    if (membership.user.id === selectedSubmission.group.leader_id) {
                                                        newLeaderId = null;
                                                    }
                                                }"
                                            />
                                            Diterima
                                        </label>
                                        <label
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border text-[11px] font-medium transition-all cursor-pointer"
                                            :class="partialDecisions[membership.user.id]?.status === 'rejected'
                                                ? 'bg-red-500/10 text-red-600 border-red-500/30 font-semibold'
                                                : 'bg-muted/10 text-muted-foreground border-border/80'"
                                        >
                                            <input
                                                type="radio"
                                                v-model="partialDecisions[membership.user.id].status"
                                                value="rejected"
                                                class="sr-only"
                                            />
                                            Ditolak
                                        </label>
                                    </div>
                                </div>
                                
                                <!-- Rejection Note Input -->
                                <div
                                    v-if="partialDecisions[membership.user.id]?.status === 'rejected'"
                                    class="pl-11 space-y-1.5"
                                >
                                    <Label class="text-[10px] font-semibold text-muted-foreground">Catatan Penolakan Anggota (Wajib)</Label>
                                    <Textarea
                                        v-model="partialDecisions[membership.user.id].rejection_note"
                                        placeholder="Contoh: Kuota posisi magang untuk program studi bersangkutan sudah penuh."
                                        rows="2"
                                        class="text-xs"
                                        :id="`reject-note-${membership.user.id}`"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Change Leader Dropdown (only visible and required if leader is rejected) -->
                    <div
                        v-if="partialDecisions[selectedSubmission.group.leader_id]?.status === 'rejected'"
                        class="space-y-2 border-t border-border/40 pt-4"
                    >
                        <Label class="text-xs font-bold text-destructive">Ketua Kelompok Ditolak, Pilih Ketua Baru!</Label>
                        <p class="text-[10px] text-muted-foreground leading-relaxed">
                            Karena ketua kelompok yang mengajukan ditolak, Anda wajib menunjuk salah satu anggota kelompok yang <strong>diterima</strong> untuk menjadi Ketua baru demi melanjutkan proses kelompok aktif.
                        </p>
                        
                        <div v-if="acceptedMembersForDropdown.length === 0" class="text-xs font-semibold text-destructive mt-1">
                            Peringatan: Tidak ada anggota yang diterima. Silakan ganti keputusan menjadi Semua Ditolak.
                        </div>
                        
                        <div v-else class="mt-2">
                            <select
                                v-model="newLeaderId"
                                class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-hidden focus-visible:ring-1 focus-visible:ring-ring"
                                id="select-new-leader"
                            >
                                <option :value="null" disabled>-- Pilih Ketua Baru --</option>
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

                <DialogFooter class="border-t border-border/60 pt-4 flex flex-col-reverse sm:flex-row sm:justify-between gap-3">
                    <Button variant="outline" @click="handleBackToDecision" :disabled="localProcessing">
                        Kembali
                    </Button>
                    <div class="flex gap-2 w-full sm:w-auto">
                        <Button variant="outline" @click="showPartialModal = false" :disabled="localProcessing">Batal</Button>
                        <Button
                            variant="default"
                            class="bg-green-600 hover:bg-green-700 text-white font-medium cursor-pointer"
                            @click="submitPartialDecision"
                            :disabled="localProcessing"
                            id="btn-submit-partial"
                        >
                            <Spinner v-if="localProcessing" class="mr-2 h-4 w-4 animate-spin" />
                            Simpan Keputusan
                        </Button>
                    </div>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
