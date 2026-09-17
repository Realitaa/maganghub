<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Search, Briefcase, Building2, Calendar, ArrowRight } from '@lucide/vue';
import { ref, watch } from 'vue';
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
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { useIdTimeFormat } from '@/composables/useIdTimeFormat';
import { index as groupsIndex, show as groupShow } from '@/routes/internships/groups';
import type { Group } from '@/types';

// Define layout breadcrumbs
defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Manajemen Magang',
                href: groupsIndex.url(),
            },
        ],
    },
});

// Props
const props = defineProps<{
    groups: Group[];
    filters: {
        search?: string;
        status?: string;
    };
}>();

const { formatDate } = useIdTimeFormat();

// State
const searchQuery = ref(props.filters?.search || '');
const selectedStatus = ref(props.filters?.status || 'all');

// Debounce helper
function debounce<T extends (...args: any[]) => any>(fn: T, delay: number) {
    let timeoutId: ReturnType<typeof setTimeout> | undefined;

    return (...args: Parameters<T>) => {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => fn(...args), delay);
    };
}

// Filter Action
const applyFilters = () => {
    router.get(
        groupsIndex.url(),
        {
            search: searchQuery.value || undefined,
            status: selectedStatus.value === 'all' ? undefined : selectedStatus.value,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
};

const debouncedFilter = debounce(applyFilters, 400);

// Watchers
watch(searchQuery, () => {
    debouncedFilter();
});

watch(selectedStatus, () => {
    applyFilters();
});

// Navigate to detail preserving current filters in URL
function goToDetail(group: Group) {
    const params = new URLSearchParams();
    if (searchQuery.value) params.set('search', searchQuery.value);
    if (selectedStatus.value && selectedStatus.value !== 'all') params.set('status', selectedStatus.value);
    const qs = params.toString();
    const url = groupShow.url({ group: group.code }) + (qs ? `?${qs}` : '');
    router.visit(url);
}

// Status helpers
function getStatusLabel(status?: string): string {
    const labels: Record<string, string> = {
        forming: 'Pembentukan',
        submitted: 'Diajukan',
        letter_published: 'Surat Terbit',
        applying: 'Mengajukan',
        loa_review: 'Review LoA',
        accepted: 'Diterima',
        partially_accepted: 'Diterima Sebagian',
        rejected: 'Ditolak',
        internship_started: 'Sedang Magang',
        completed: 'Selesai Magang',
        segera_magang: 'Segera Magang',
        melaksanakan_magang: 'Melaksanakan Magang',
        selesai_magang: 'Selesai Magang',
    };

    return labels[status ?? ''] ?? status ?? '-';
}

function getStatusClass(status?: string): string {
    switch (status) {
        case 'forming':
            return 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 border-blue-200/50';
        case 'submitted':
        case 'applying':
        case 'loa_review':
            return 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 border-amber-200/50';
        case 'letter_published':
        case 'accepted':
        case 'internship_started':
        case 'completed':
        case 'melaksanakan_magang':
        case 'selesai_magang':
            return 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 border-emerald-200/50';
        case 'rejected':
            return 'bg-rose-50 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400 border-rose-200/50';
        default:
            return 'bg-muted text-muted-foreground border-border';
    }
}
</script>

<template>
    <Head title="Manajemen Magang" />

    <div class="flex-1 space-y-8 p-4 pt-6 md:p-8">
        <div>
            <h1
                class="flex items-center gap-2 text-2xl font-bold tracking-tight text-foreground"
            >
                <Briefcase class="h-6 w-6 text-primary" />
                Manajemen Magang
            </h1>
            <p class="mt-1 text-sm text-muted-foreground">
                Daftar seluruh kelompok magang dari berbagai tahap dan status pelaksanaan.
            </p>
        </div>

        <Card class="border-border/80 shadow-xs">
            <CardHeader class="pb-4">
                <div
                    class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div class="flex flex-col gap-1">
                        <CardTitle
                            class="flex items-center gap-2 text-base font-semibold text-foreground"
                        >
                            Daftar Kelompok Magang
                        </CardTitle>
                        <CardDescription class="text-xs">
                            Pantau dan kelola seluruh kelompok magang mahasiswa.
                        </CardDescription>
                    </div>
                    <div
                        class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row"
                    >
                        <!-- Search Input -->
                        <div class="relative w-full sm:w-64">
                            <Search
                                class="absolute top-2.5 left-2.5 h-4 w-4 text-muted-foreground"
                            />
                            <Input
                                v-model="searchQuery"
                                placeholder="Cari ketua, NIM, perusahaan..."
                                class="h-9 pl-9 text-xs"
                            />
                        </div>
                        <!-- Status Filter -->
                        <div>
                            <Select v-model="selectedStatus">
                                <SelectTrigger class="h-9 text-xs">
                                    <SelectValue placeholder="Semua Status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">Semua Status</SelectItem>
                                    <SelectItem value="forming">Pembentukan</SelectItem>
                                    <SelectItem value="submitted">Diajukan</SelectItem>
                                    <SelectItem value="letter_published">Surat Terbit</SelectItem>
                                    <SelectItem value="applying">Mengajukan</SelectItem>
                                    <SelectItem value="loa_review">Review LoA</SelectItem>
                                    <SelectItem value="accepted">Diterima</SelectItem>
                                    <SelectItem value="partially_accepted">Diterima Sebagian</SelectItem>
                                    <SelectItem value="rejected">Ditolak</SelectItem>
                                    <SelectItem value="internship_started">Sedang Magang</SelectItem>
                                    <SelectItem value="completed">Selesai Magang</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                </div>
            </CardHeader>
            <CardContent class="p-0">
                <div
                    v-if="groups.length === 0"
                    class="flex flex-col items-center justify-center p-12 text-center text-muted-foreground"
                >
                    <Briefcase class="mb-3 h-10 w-10 text-primary opacity-30" />
                    <h3 class="text-sm font-semibold text-foreground">
                        Tidak ada kelompok ditemukan
                    </h3>
                    <p class="mt-1 text-xs text-muted-foreground">
                        Silakan coba ubah filter pencarian atau status.
                    </p>
                </div>
                <div v-else class="overflow-x-auto">
                    <Table>
                        <TableHeader>
                            <TableRow class="bg-muted/40 hover:bg-muted/40">
                                <TableHead
                                    class="font-semibold text-muted-foreground"
                                >
                                    Ketua Kelompok
                                </TableHead>
                                <TableHead
                                    class="font-semibold text-muted-foreground"
                                >
                                    Instansi / Perusahaan
                                </TableHead>
                                <TableHead
                                    class="font-semibold text-muted-foreground"
                                >
                                    Periode Pelaksanaan
                                </TableHead>
                                <TableHead
                                    class="text-center font-semibold text-muted-foreground"
                                >
                                    Status Magang
                                </TableHead>
                                <TableHead
                                    class="w-[140px] text-right font-semibold text-muted-foreground"
                                >
                                    Aksi
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="group in groups"
                                :key="group.id"
                                class="transition-colors hover:bg-muted/10"
                            >
                                <TableCell class="py-4">
                                    <div
                                        class="text-xs font-semibold text-foreground"
                                    >
                                        {{ group.leader?.name || '-' }}
                                    </div>
                                    <div
                                        class="text-[10px] text-muted-foreground"
                                    >
                                        {{ group.leader?.nim || '-' }}
                                    </div>
                                </TableCell>
                                <TableCell
                                    class="py-4 font-medium text-foreground"
                                >
                                    <div class="flex items-center gap-1.5 text-xs">
                                        <Building2
                                            class="h-3.5 w-3.5 shrink-0 text-muted-foreground"
                                        />
                                        <span>{{
                                            group.active_submission
                                                ?.company_name ||
                                            group.activeSubmission
                                                ?.company_name ||
                                            '-'
                                        }}</span>
                                    </div>
                                </TableCell>
                                <TableCell class="py-4 text-muted-foreground">
                                    <div
                                        class="flex items-center gap-1.5 text-xs"
                                    >
                                        <Calendar
                                            class="h-3.5 w-3.5 shrink-0 text-muted-foreground"
                                        />
                                        <span v-if="group.active_submission?.start_date || group.activeSubmission?.start_date">
                                            {{
                                                formatDate(
                                                    group.active_submission
                                                        ?.start_date ||
                                                        group.activeSubmission
                                                            ?.start_date,
                                                )
                                            }}
                                            <span
                                                class="mx-1 text-muted-foreground/50"
                                            >s/d</span>
                                            {{
                                                formatDate(
                                                    group.active_submission
                                                        ?.end_date ||
                                                        group.activeSubmission
                                                            ?.end_date,
                                                )
                                            }}
                                        </span>
                                        <span v-else>-</span>
                                    </div>
                                </TableCell>
                                <TableCell class="py-4 text-center">
                                    <Badge
                                        :class="
                                            getStatusClass(
                                                group.status,
                                            )
                                        "
                                        class="border px-2.5 py-1 text-[10px] font-medium shadow-2xs transition-all"
                                    >
                                        {{
                                            getStatusLabel(
                                                group.status,
                                            )
                                        }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="py-4 text-right">
                                    <Button
                                        size="sm"
                                        variant="outline"
                                        class="h-8 cursor-pointer gap-1.5 font-medium text-xs"
                                        @click="goToDetail(group)"
                                        :id="`btn-detail-group-${group.id}`"
                                    >
                                        Detail Kelompok
                                        <ArrowRight class="h-3 w-3" />
                                    </Button>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
