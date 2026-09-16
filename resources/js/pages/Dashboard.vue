<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    Users,
    Briefcase,
    Clock,
    Building2,
    ClipboardList,
    FileCheck2,
} from '@lucide/vue';
import { computed } from 'vue';
import GroupStatusChart from '@/components/dashboard/GroupStatusChart.vue';
import StudentInternshipStatusChart from '@/components/dashboard/StudentInternshipStatusChart.vue';
import ImpactChart from '@/components/landing/ImpactChart.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { useIdTimeFormat } from '@/composables/useIdTimeFormat';
import { dashboard } from '@/routes';
import { index as preparationsIndex } from '@/routes/internships/preparations';
import { index as submissionsIndex } from '@/routes/internships/submissions';
import type {
    DashboardSummary,
    DashboardOperational,
    PublicStatisticsData,
} from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});

const props = defineProps<{
    summary: DashboardSummary;
    operational: DashboardOperational;
    publicChart: PublicStatisticsData;
}>();

const page = usePage();
const userName = computed(() => page.props.auth?.user?.name ?? 'Admin');

const { formatDateTime } = useIdTimeFormat();

const formattedUpdatedAt = computed(() => {
    if (!props.publicChart?.updated_at) return '-';
    return formatDateTime(props.publicChart.updated_at);
});
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex-1 space-y-6 p-4 pt-6 md:p-8">
        <!-- 1. Header Sambutan -->
        <div>
            <h1
                class="text-2xl font-bold tracking-tight text-foreground md:text-3xl"
            >
                Selamat datang, {{ userName }} 👋
            </h1>
            <p class="mt-1 text-sm text-muted-foreground">
                Berikut ringkasan aktivitas MagangHub saat ini.
            </p>
        </div>

        <!-- 2. Summary Statistics — 4 Kartu -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Kartu 1: Mahasiswa -->
            <Card>
                <CardHeader
                    class="flex flex-row items-center justify-between space-y-0 pb-2"
                >
                    <CardTitle
                        class="text-sm font-medium text-muted-foreground"
                    >
                        Mahasiswa
                    </CardTitle>
                    <Users class="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-foreground">
                        {{ summary.total_students }}
                    </div>
                    <p class="mt-1 text-xs text-muted-foreground">
                        Mahasiswa terdaftar di sistem
                    </p>
                </CardContent>
            </Card>

            <!-- Kartu 2: Kelompok Magang -->
            <Card>
                <CardHeader
                    class="flex flex-row items-center justify-between space-y-0 pb-2"
                >
                    <CardTitle
                        class="text-sm font-medium text-muted-foreground"
                    >
                        Kelompok Magang
                    </CardTitle>
                    <Briefcase class="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-foreground">
                        {{ summary.total_groups }}
                    </div>
                    <p class="mt-1 text-xs text-muted-foreground">
                        Total kelompok terbentuk
                    </p>
                </CardContent>
            </Card>

            <!-- Kartu 3: Pengajuan Menunggu -->
            <Card>
                <CardHeader
                    class="flex flex-row items-center justify-between space-y-0 pb-2"
                >
                    <CardTitle
                        class="text-sm font-medium text-muted-foreground"
                    >
                        Pengajuan Menunggu
                    </CardTitle>
                    <Clock class="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-foreground">
                        {{ summary.pending_submissions }}
                    </div>
                    <p class="mt-1 text-xs text-muted-foreground">
                        Membutuhkan tindakan admin
                    </p>
                </CardContent>
            </Card>

            <!-- Kartu 4: Perusahaan -->
            <Card>
                <CardHeader
                    class="flex flex-row items-center justify-between space-y-0 pb-2"
                >
                    <CardTitle
                        class="text-sm font-medium text-muted-foreground"
                    >
                        Perusahaan
                    </CardTitle>
                    <Building2 class="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-foreground">
                        {{ summary.total_companies }}
                    </div>
                    <p class="mt-1 text-xs text-muted-foreground">
                        Perusahaan unik tercatat
                    </p>
                </CardContent>
            </Card>
        </div>

        <!-- 3. Operational Overview — 2 Kartu -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- Kartu 1: Yang Perlu Ditangani -->
            <Card class="flex flex-col">
                <CardHeader>
                    <CardTitle class="text-lg font-semibold">
                        Yang Perlu Ditangani
                    </CardTitle>
                    <CardDescription>
                        Shortcut pekerjaan admin yang membutuhkan pemeriksaan
                        atau tindakan lanjutan.
                    </CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                    <!-- Item 1: Pengajuan Magang -->
                    <div
                        class="flex flex-col gap-3 rounded-lg border border-border/70 p-4 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <ClipboardList class="h-4 w-4 text-amber-500" />
                                <span class="font-medium text-foreground">
                                    Pengajuan Magang
                                </span>
                            </div>
                            <p class="text-xs text-muted-foreground">
                                {{ operational.pending_submissions }} pengajuan
                                menunggu pemeriksaan
                            </p>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-2xl font-bold text-foreground">
                                {{ operational.pending_submissions }}
                            </span>
                            <Button as-child variant="outline" size="sm">
                                <Link :href="submissionsIndex.url()">
                                    Lihat Pengajuan Magang &rarr;
                                </Link>
                            </Button>
                        </div>
                    </div>

                    <!-- Item 2: Persiapan Magang -->
                    <div
                        class="flex flex-col gap-3 rounded-lg border border-border/70 p-4 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <FileCheck2 class="h-4 w-4 text-emerald-500" />
                                <span class="font-medium text-foreground">
                                    Persiapan Magang
                                </span>
                            </div>
                            <p class="text-xs text-muted-foreground">
                                {{ operational.waiting_preparations }} kelompok
                                telah menerima respons perusahaan
                            </p>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-2xl font-bold text-foreground">
                                {{ operational.waiting_preparations }}
                            </span>
                            <Button as-child variant="outline" size="sm">
                                <Link :href="preparationsIndex.url()">
                                    Lihat Persiapan Magang &rarr;
                                </Link>
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Kartu 2: Status Kelompok Magang -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-lg font-semibold">
                        Status Kelompok Magang
                    </CardTitle>
                    <CardDescription>
                        Distribusi kelompok magang berdasarkan status aktual
                        saat ini.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <GroupStatusChart
                        :items="operational.group_status_distribution"
                        :total-groups="operational.total_groups"
                    />
                </CardContent>
            </Card>
        </div>

        <!-- 4. Baris Terakhir: Status Mahasiswa terhadap Program Magang & Status Mahasiswa Magang -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- Kartu 1: Status Mahasiswa terhadap Program Magang -->
            <Card class="flex flex-col justify-between">
                <CardHeader>
                    <CardTitle class="text-lg font-semibold">
                        Status Mahasiswa terhadap Program Magang
                    </CardTitle>
                    <CardDescription>
                        Ditampilkan di halaman utama pada bagian
                        &ldquo;Mengapa Magang Penting?&rdquo;
                    </CardDescription>
                </CardHeader>
                <CardContent class="flex flex-1 flex-col justify-between">
                    <div class="flex w-full items-center justify-center">
                        <ImpactChart
                            :statistics="publicChart.pie_chart"
                            :show-havenot-in-legend="true"
                            :show-numeric-legend="true"
                        />
                    </div>
                    <div
                        class="mt-4 flex items-center justify-between border-t border-border/60 pt-3 text-xs text-muted-foreground"
                    >
                        <span>Terakhir diperbarui:</span>
                        <span class="font-medium text-foreground">
                            {{ formattedUpdatedAt }}
                        </span>
                    </div>
                </CardContent>
            </Card>

            <!-- Kartu 2: Status Mahasiswa Magang -->
            <Card class="flex flex-col justify-between">
                <CardHeader>
                    <CardTitle class="text-lg font-semibold">
                        Status Mahasiswa Magang
                    </CardTitle>
                    <CardDescription>
                        Distribusi tahapan mahasiswa: belum magang, sedang
                        mengajukan, hingga konfirmasi magang.
                    </CardDescription>
                </CardHeader>
                <CardContent class="flex flex-1 flex-col justify-between">
                    <div class="flex w-full items-center justify-center">
                        <StudentInternshipStatusChart
                            :items="operational.student_internship_status"
                            :total-students="summary.total_students"
                        />
                    </div>
                    <div
                        class="mt-4 flex items-center justify-between border-t border-border/60 pt-3 text-xs text-muted-foreground"
                    >
                        <span>Total Mahasiswa:</span>
                        <span class="font-medium text-foreground">
                            {{ summary.total_students }} mahasiswa
                        </span>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
