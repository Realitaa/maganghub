<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Search, Users, Building2, Calendar, Briefcase } from '@lucide/vue';
import { ref, watch } from 'vue';
import { Badge } from '@/components/ui/badge';
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
import { index as groupsIndex } from '@/routes/review/groups';

// Define layout breadcrumbs
defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Kelompok Magang',
                href: groupsIndex.url(),
            },
        ],
    },
});

// Define Types
interface Member {
    id: number;
    name: string;
    nim: string;
}

interface Membership {
    id: number;
    user: Member;
}

interface ActiveSubmission {
    id: number;
    company_name: string;
    start_date: string;
    end_date: string;
}

interface Group {
    id: number;
    code: string;
    status: string;
    leader_id: number;
    computed_status: 'segera_magang' | 'melaksanakan_magang' | 'selesai_magang';
    leader: Member | null;
    memberships: Membership[];
    active_submission?: ActiveSubmission | null;
    activeSubmission?: ActiveSubmission | null;
}

// Props
const props = defineProps<{
    groups: Group[];
    filters: {
        search?: string;
        status?: string;
    };
}>();

// State
const searchQuery = ref(props.filters.search || '');
const selectedStatus = ref(props.filters.status || 'all');

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
            status:
                selectedStatus.value === 'all'
                    ? undefined
                    : selectedStatus.value,
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

// Format date helper
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

// Status helpers
function getStatusLabel(status?: string) {
    switch (status) {
        case 'segera_magang':
            return 'Segera Magang';
        case 'melaksanakan_magang':
            return 'Melaksanakan Magang';
        case 'selesai_magang':
            return 'Selesai Magang';
        default:
            return '-';
    }
}

function getStatusClass(status?: string) {
    switch (status) {
        case 'segera_magang':
            return 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 border-blue-200/50';
        case 'melaksanakan_magang':
            return 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 border-emerald-200/50';
        case 'selesai_magang':
            return 'bg-gray-50 text-gray-700 dark:bg-gray-900/30 dark:text-gray-400 border-gray-200/50';
        default:
            return 'bg-muted text-muted-foreground border-border';
    }
}
</script>

<template>
    <Head title="Kelompok Magang" />

    <div class="flex-1 space-y-8 p-4 pt-6 md:p-8">
        <div>
            <h1
                class="flex items-center gap-2 text-2xl font-bold tracking-tight text-foreground"
            >
                <Briefcase class="h-6 w-6 text-primary" />
                Kelompok Magang
            </h1>
            <p class="mt-1 text-sm text-muted-foreground">
                Daftar seluruh kelompok magang yang telah menyelesaikan tahap
                administrasi, dikelompokkan berdasarkan waktu pelaksanaan.
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
                            <Users class="h-4 w-4 text-primary" />
                            Status Pelaksanaan Magang
                        </CardTitle>
                        <CardDescription class="text-xs">
                            Pantau kelompok yang akan segera melaksanakan,
                            sedang melaksanakan, atau telah menyelesaikan
                            program magang.
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
                                placeholder="Cari kode, ketua, perusahaan..."
                                class="h-9 pl-9"
                            />
                        </div>
                        <!-- Status Filter -->
                        <div class="w-full sm:w-48">
                            <Select v-model="selectedStatus">
                                <SelectTrigger class="h-9">
                                    <SelectValue placeholder="Pilih Status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all"
                                        >Semua Status</SelectItem
                                    >
                                    <SelectItem value="segera_magang"
                                        >Segera Magang</SelectItem
                                    >
                                    <SelectItem value="melaksanakan_magang"
                                        >Melaksanakan Magang</SelectItem
                                    >
                                    <SelectItem value="selesai_magang"
                                        >Selesai Magang</SelectItem
                                    >
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
                    <Users class="mb-3 h-10 w-10 text-primary opacity-30" />
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
                                    class="w-[120px] font-semibold text-muted-foreground"
                                    >Kode Kelompok</TableHead
                                >
                                <TableHead
                                    class="font-semibold text-muted-foreground"
                                    >Ketua</TableHead
                                >
                                <TableHead
                                    class="font-semibold text-muted-foreground"
                                    >Anggota Kelompok</TableHead
                                >
                                <TableHead
                                    class="font-semibold text-muted-foreground"
                                    >Instansi / Perusahaan</TableHead
                                >
                                <TableHead
                                    class="font-semibold text-muted-foreground"
                                    >Periode Pelaksanaan</TableHead
                                >
                                <TableHead
                                    class="w-[180px] text-center font-semibold text-muted-foreground"
                                    >Status Magang</TableHead
                                >
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="group in groups"
                                :key="group.id"
                                class="transition-colors hover:bg-muted/10"
                            >
                                <TableCell
                                    class="py-4 font-mono text-xs font-bold text-foreground"
                                >
                                    {{ group.code }}
                                </TableCell>
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
                                <TableCell class="py-4">
                                    <div class="space-y-1">
                                        <div
                                            v-for="membership in group.memberships"
                                            :key="membership.id"
                                            class="flex items-center gap-1.5 text-[11px] text-foreground"
                                        >
                                            <span
                                                class="h-1.5 w-1.5 rounded-full bg-muted-foreground/50"
                                            ></span>
                                            <span>{{
                                                membership.user?.name || '-'
                                            }}</span>
                                            <span
                                                class="text-[9px] text-muted-foreground"
                                                >({{
                                                    membership.user?.nim || '-'
                                                }})</span
                                            >
                                            <Badge
                                                v-if="
                                                    membership.user?.id ===
                                                    group.leader_id
                                                "
                                                variant="outline"
                                                class="ml-1 border-muted-foreground/30 px-1 py-0 text-[8px] font-normal text-muted-foreground"
                                            >
                                                Ketua
                                            </Badge>
                                        </div>
                                    </div>
                                </TableCell>
                                <TableCell
                                    class="py-4 font-medium text-foreground"
                                >
                                    <div class="flex items-center gap-1.5">
                                        <Building2
                                            class="h-3.5 w-3.5 flex-shrink-0 text-muted-foreground"
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
                                            class="h-3.5 w-3.5 flex-shrink-0 text-muted-foreground"
                                        />
                                        <span>
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
                                                >s/d</span
                                            >
                                            {{
                                                formatDate(
                                                    group.active_submission
                                                        ?.end_date ||
                                                        group.activeSubmission
                                                            ?.end_date,
                                                )
                                            }}
                                        </span>
                                    </div>
                                </TableCell>
                                <TableCell class="py-4 text-center">
                                    <Badge
                                        :class="
                                            getStatusClass(
                                                group.computed_status,
                                            )
                                        "
                                        class="border px-2.5 py-1 text-[10px] font-medium shadow-2xs transition-all"
                                    >
                                        {{
                                            getStatusLabel(
                                                group.computed_status,
                                            )
                                        }}
                                    </Badge>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
