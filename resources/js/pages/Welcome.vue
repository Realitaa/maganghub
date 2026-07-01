<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    FileText,
    Users,
    Building2,
    ShieldCheck,
    Activity,
    NotebookPen,
    Menu,
    X,
    Check,
    ArrowRight,
} from '@lucide/vue';
import { ref } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import CountUp from '@/components/landing/CountUp.vue';
import {
    google,
    telkomIndonesia,
    bri,
    bankIndonesia,
    indosatOoredoHutsicon,
    mandiri,
    ibm,
    huawei,
    goto,
} from '@/components/landing/icons';
import ImpactChart from '@/components/landing/ImpactChart.vue';
import LogoLoop from '@/components/landing/LogoLoop.vue';
import {
    Accordion,
    AccordionContent,
    AccordionItem,
    AccordionTrigger,
} from '@/components/ui/accordion';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { ScrollArea } from '@/components/ui/scroll-area';
import WorkflowStepper from '@/components/WorkflowStepper.vue';
import { dashboard, home, login } from '@/routes';

// state
const hoveredStep = ref(6);
const mobileMenuOpen = ref(false);
const toggleMobileMenu = () => {
    mobileMenuOpen.value = !mobileMenuOpen.value;
};

const page = usePage();
const user = page.props.auth?.user;
const role = user?.role;

// Navigation links
const navLinks = [
    { label: 'Beranda', href: '#home' },
    { label: 'Fitur', href: '#features' },
    { label: 'Alur Kerja', href: '#workflow' },
    { label: 'FAQ', href: '#faq' },
];

// Company logos loop items
const companyLogos = [
    {
        node: google,
        title: 'Google',
        href: 'https://google.com/',
    },
    {
        node: telkomIndonesia,
        title: 'Telkom Indonesia',
        href: 'https://www.telkom.co.id/',
    },
    {
        node: bri,
        title: 'BRI',
        href: 'https://www.bri.co.id',
    },
    {
        node: bankIndonesia,
        title: 'Bank Indonesia',
        href: 'https://www.bi.go.id',
    },
    {
        node: indosatOoredoHutsicon,
        title: 'Indosat Ooredoo Hutchison',
        href: 'https://www.ioh.co.id',
    },
    {
        node: mandiri,
        title: 'Bank Mandiri',
        href: 'https://www.bankmandiri.co.id',
    },
    {
        node: ibm,
        title: 'IBM',
        href: 'https://www.ibm.com',
    },
    {
        node: huawei,
        title: 'Huawei',
        href: 'https://www.huawei.com/',
    },
    {
        node: goto,
        title: 'Goto',
        href: 'https://www.goto.com/',
    },
];

// Statistics data for Impact section
const landingStats = {
    global: 40,
    multinational: 75,
    international: 60,
    national: 110,
    regional: 90,
    local: 165,
    havenot: 260,
};

const features = [
    {
        title: 'Pengajuan Magang',
        description:
            'Ajukan program magang secara fleksibel baik secara individu maupun dalam ikatan kelompok terdaftar.',
        icon: FileText,
    },
    {
        title: 'Kelola Kelompok',
        description:
            'Undang sesama rekan mahasiswa ke kelompok magang Anda serta kelola peran masing-masing anggota.',
        icon: Users,
    },
    {
        title: 'Perusahaan Tujuan',
        description:
            'Pantau dan kelola database perusahaan mitra kampus beserta informasi kontak penting untuk pengajuan.',
        icon: Building2,
    },
    {
        title: 'Persetujuan Admin',
        description:
            'Proses review, verifikasi berkas, dan validasi persetujuan surat magang secara digital oleh koordinator.',
        icon: ShieldCheck,
    },
    {
        title: 'Monitoring Status',
        description:
            'Pantau secara real-time status surat pengantar dan konfirmasi balasan penerimaan dari instansi.',
        icon: Activity,
    },
    {
        title: 'Logbook Magang',
        description:
            'Catat laporan aktivitas harian, unggah bukti penugasan magang, dan dapatkan umpan balik dosen.',
        icon: NotebookPen,
    },
];

// Statistics counter state & animation
const stats = ref([
    { label: 'Mahasiswa Terdaftar', target: 1200, current: 0, suffix: '+' },
    { label: 'Kelompok Magang', target: 350, current: 0, suffix: '+' },
    { label: 'Perusahaan Mitra', target: 180, current: 0, suffix: '+' },
    { label: 'Pengajuan Diproses', target: 95, current: 0, suffix: '%' },
]);

const faqs = [
    {
        question: 'Bagaimana cara membuat kelompok?',
        answer: 'Mahasiswa dapat membentuk kelompok magang melalui dashboard dengan menavigasi ke menu "Kelola Kelompok". Anda cukup memasukkan NIM rekan mahasiswa yang terdaftar di platform untuk mengundang mereka masuk ke kelompok, atau kirimkan tautan undangan untuk bergabung ke kelompok magang!',
    },
    {
        question:
            'Berapa anggota yang diperlukan dalam satu kelompok untuk magang?',
        answer: 'Satu kelompok magang dapat berisi minimal dua orang, dan tidak ada batas maksimal.',
    },
    {
        question: 'Bagaimana jika pengajuan ditolak?',
        answer: 'Jika pengajuan ditolak oleh administrator program studi atau perusahaan, Anda akan menerima catatan penolakan. Mahasiswa dapat memperbarui data pengajuan, merevisi dokumen, atau mengajukan usulan baru ke perusahaan lain.',
    },
    {
        question: 'Apa itu logbook?',
        answer: 'Logbook adalah catatan aktivitas yang diisi oleh mahasiswa selama masa magang. Logbook ini digunakan oleh dosen pembimbing dan koordinator magang untuk memonitor kontribusi dan perkembangan kompetensi mahasiswa. Fitur ini sedang dalam pengembangan, nantikan ya!',
    },
];
</script>

<template>
    <Head title="MagangHub - Platform Pengelolaan Magang Mahasiswa" />

    <ScrollArea class="h-screen w-screen">
        <div
            class="min-h-screen bg-background font-sans text-foreground antialiased selection:bg-primary/20 selection:text-primary"
        >
            <!-- Sticky Navigation -->
            <header
                class="fixed top-0 z-50 w-[99.5%] border-b border-border/40 bg-background/80 backdrop-blur-md transition-all duration-300"
            >
                <div
                    class="mx-auto flex h-16 max-w-7xl items-center justify-between px-6 lg:px-8"
                >
                    <!-- Logo -->
                    <AppLogo />

                    <!-- Desktop Menu -->
                    <nav class="hidden items-center gap-8 md:flex">
                        <a
                            v-for="link in navLinks"
                            :key="link.label"
                            :href="link.href"
                            class="text-sm font-medium text-muted-foreground transition-colors duration-200 hover:text-foreground"
                        >
                            {{ link.label }}
                        </a>
                    </nav>

                    <!-- CTA Button -->
                    <div class="hidden items-center gap-4 md:flex">
                        <Button as-child size="sm" class="h-9.5 px-5">
                            <Link
                                :href="
                                    user
                                        ? role === 'student'
                                            ? home()
                                            : dashboard()
                                        : login()
                                "
                            >
                                {{
                                    user
                                        ? role === 'student'
                                            ? 'Beranda'
                                            : 'Dashboard'
                                        : 'Masuk'
                                }}
                            </Link>
                        </Button>
                    </div>

                    <!-- Mobile menu button -->
                    <button
                        @click="toggleMobileMenu"
                        class="inline-flex items-center justify-center rounded-lg p-2 text-muted-foreground hover:bg-accent hover:text-foreground md:hidden"
                        aria-label="Toggle Menu"
                    >
                        <Menu v-if="!mobileMenuOpen" class="h-6 w-6" />
                        <X v-else class="h-6 w-6" />
                    </button>
                </div>

                <!-- Mobile Menu -->
                <div
                    v-show="mobileMenuOpen"
                    class="border-b border-border/80 bg-background transition-all duration-300 md:hidden"
                >
                    <div class="space-y-1.5 px-4 pt-2 pb-6">
                        <a
                            v-for="link in navLinks"
                            :key="link.label"
                            :href="link.href"
                            @click="mobileMenuOpen = false"
                            class="block rounded-md px-3 py-2 text-base font-medium text-muted-foreground hover:bg-accent hover:text-foreground"
                        >
                            {{ link.label }}
                        </a>
                        <div class="mt-4 border-t border-border px-3 pt-4">
                            <Button as-child class="w-full">
                                <Link
                                    :href="
                                        user
                                            ? role === 'student'
                                                ? home()
                                                : dashboard()
                                            : login()
                                    "
                                >
                                    {{
                                        user
                                            ? role === 'student'
                                                ? 'Beranda'
                                                : 'Dashboard'
                                            : 'Masuk'
                                    }}
                                </Link>
                            </Button>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Hero Section -->
            <section
                id="home"
                class="relative flex min-h-[calc(100vh-2rem)] flex-col justify-center overflow-hidden bg-background py-20 md:py-24"
            >
                <!-- Ambient Glows -->
                <div
                    class="absolute top-0 right-0 -z-10 h-[600px] w-[600px] rounded-full bg-emerald-50/20 blur-3xl dark:bg-emerald-950/5"
                ></div>
                <div
                    class="absolute -top-40 -left-40 -z-10 h-[600px] w-[600px] rounded-full bg-emerald-50/10 blur-3xl dark:bg-emerald-950/5"
                ></div>

                <div class="mx-auto w-full max-w-7xl px-6 lg:px-8">
                    <div
                        class="grid grid-cols-1 items-center gap-12 lg:grid-cols-2 lg:gap-16"
                    >
                        <!-- Left: Hero Text -->
                        <div
                            class="flex flex-col items-center text-center lg:items-start lg:text-left"
                        >
                            <!-- Yellow Badge (matching CTA) -->
                            <Badge
                                variant="outline"
                                class="mb-6 gap-1.5 border-yellow-500/20 bg-yellow-500/10 px-3.5 py-1 font-semibold text-amber-600 hover:bg-yellow-500/10 hover:text-amber-600 dark:border-yellow-400/20 dark:bg-yellow-400/10 dark:text-yellow-300 dark:hover:text-yellow-300"
                            >
                                <span
                                    class="flex h-1.5 w-1.5 animate-pulse rounded-full bg-amber-500 dark:bg-yellow-300"
                                ></span>
                                Platform Pengelolaan Magang Mahasiswa
                            </Badge>

                            <!-- Headline -->
                            <h1
                                class="text-4xl leading-[1.15] font-extrabold tracking-tight text-foreground sm:text-5xl lg:text-6xl"
                            >
                                Kelola Pengajuan Magang Lebih
                                <span class="text-primary">Mudah</span> dan
                                <span class="text-primary">Terstruktur</span>
                            </h1>

                            <!-- Subheadline -->
                            <p
                                class="mt-6 text-base leading-relaxed text-muted-foreground sm:text-lg lg:text-xl"
                            >
                                MagangHub membantu mahasiswa, kelompok magang,
                                dan administrator kampus mengelola proses
                                pengajuan, monitoring, dan pelaporan magang
                                dalam satu platform terpadu.
                            </p>

                            <!-- CTA Buttons -->
                            <div
                                class="mt-10 flex flex-wrap items-center justify-center gap-4 lg:justify-start"
                            >
                                <Button
                                    as-child
                                    size="lg"
                                    class="h-12 px-7 text-base font-medium shadow-sm transition-all duration-150 hover:scale-[1.01] active:scale-[0.99]"
                                >
                                    <Link
                                        :href="
                                            user
                                                ? role === 'student'
                                                    ? home()
                                                    : dashboard()
                                                : login()
                                        "
                                    >
                                        Masuk ke
                                        {{
                                            user
                                                ? role === 'student'
                                                    ? 'Beranda'
                                                    : 'Dashboard'
                                                : 'Sistem'
                                        }}
                                        <ArrowRight class="ml-2 h-4 w-4" />
                                    </Link>
                                </Button>
                                <Button
                                    as-child
                                    variant="outline"
                                    size="lg"
                                    class="h-12 px-7 text-base font-medium transition-all duration-150"
                                >
                                    <a href="#features"> Pelajari Fitur </a>
                                </Button>
                            </div>
                        </div>

                        <!-- Right: Mockup Dashboard -->
                        <div
                            class="relative flex w-full justify-center pt-3 lg:justify-end"
                        >
                            <div class="relative w-full max-w-[590px]">
                                <!-- Shadow di bawah saja ratakan (emerald shadow tebal) -->
                                <div
                                    class="pointer-events-none absolute right-[4%] bottom-[-20px] left-[4%] h-6 rounded-full bg-linear-to-r from-emerald-500/40 via-emerald-500/75 to-emerald-500/40 blur-lg"
                                ></div>

                                <div
                                    class="overflow-hidden rounded-xl border-2 border-emerald-500/30 bg-card shadow-xl dark:border-emerald-500/50"
                                >
                                    <!-- Image Content -->
                                    <div class="relative bg-background">
                                        <img
                                            src="/assets/images/akreditasi-unimed.webp"
                                            alt="Gedung Fakultas Matematika dan Ilmu Pengetahuan Alam"
                                            class="h-auto w-full object-cover object-top transition-transform duration-500 hover:scale-[1.01]"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Scrolling Logo Cloud -->
                    <div
                        class="mt-24 w-full border-t border-border/55 pt-12 text-left"
                    >
                        <p
                            class="mb-6 text-left text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                        >
                            Dipersiapkan untuk Mendukung Kolaborasi Kampus dan
                            Dunia Industri
                        </p>
                        <div class="relative w-full overflow-hidden py-6">
                            <LogoLoop
                                :logos="companyLogos"
                                :speed="40"
                                direction="left"
                                :logoHeight="28"
                                :gap="64"
                                :pauseOnHover="true"
                                :scaleOnHover="true"
                                :fadeOut="true"
                                ariaLabel="Technology partners"
                            />
                        </div>
                    </div>
                </div>
            </section>

            <!-- Impact Section -->
            <section
                class="relative overflow-hidden border-y border-emerald-100/30 bg-emerald-50/20 py-20 md:py-24 dark:border-emerald-950/20 dark:bg-emerald-950/5"
            >
                <!-- Decorative light blob -->
                <div
                    class="absolute -right-20 -bottom-20 -z-10 h-72 w-72 rounded-full bg-emerald-100/30 blur-3xl dark:bg-emerald-900/5"
                ></div>
                <div class="mx-auto max-w-7xl px-6 lg:px-8">
                    <div
                        class="grid grid-cols-1 items-center gap-12 lg:grid-cols-2 lg:gap-16"
                    >
                        <!-- Left: Why Internships Matter -->
                        <div>
                            <Badge
                                variant="outline"
                                class="mb-4 border-yellow-500/20 bg-yellow-500/10 px-3.5 py-1 font-semibold text-amber-600 hover:bg-yellow-500/10 hover:text-amber-600 dark:border-yellow-400/20 dark:bg-yellow-400/10 dark:text-yellow-300 dark:hover:text-yellow-300"
                            >
                                Dampak & Relevansi
                            </Badge>
                            <h2
                                class="text-3xl font-bold tracking-tight text-foreground sm:text-4xl"
                            >
                                Mengapa Magang Penting?
                            </h2>
                            <p
                                class="mt-5 text-base leading-relaxed text-muted-foreground"
                            >
                                Program magang kerja merupakan jembatan emas
                                bagi mahasiswa untuk mentransformasikan ilmu
                                teoritis kampus ke dalam praktik industri riil.
                                Magang berkontribusi nyata dalam mempersiapkan
                                daya saing lulusan sebelum resmi memasuki pasar
                                tenaga kerja.
                            </p>

                            <div class="mt-8 space-y-4">
                                <div class="flex items-start gap-3">
                                    <div
                                        class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400"
                                    >
                                        <Check class="h-3.5 w-3.5" />
                                    </div>
                                    <div>
                                        <h4
                                            class="text-sm font-semibold text-foreground"
                                        >
                                            Kesiapan Kerja dan Industri
                                        </h4>
                                        <p
                                            class="mt-0.5 text-xs text-muted-foreground"
                                        >
                                            Memahami budaya kerja profesional,
                                            kedisiplinan instansi, dan etos
                                            kerja industri.
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3">
                                    <div
                                        class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400"
                                    >
                                        <Check class="h-3.5 w-3.5" />
                                    </div>
                                    <div>
                                        <h4
                                            class="text-sm font-semibold text-foreground"
                                        >
                                            Peningkatan Keterampilan Praktis
                                        </h4>
                                        <p
                                            class="mt-0.5 text-xs text-muted-foreground"
                                        >
                                            Mengasah hard skills teknis lapangan
                                            serta soft skills pemecahan masalah
                                            dan komunikasi.
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3">
                                    <div
                                        class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400"
                                    >
                                        <Check class="h-3.5 w-3.5" />
                                    </div>
                                    <div>
                                        <h4
                                            class="text-sm font-semibold text-foreground"
                                        >
                                            Jejaring Kerja (Networking)
                                        </h4>
                                        <p
                                            class="mt-0.5 text-xs text-muted-foreground"
                                        >
                                            Membangun koneksi awal dengan para
                                            profesional dan supervisor industri
                                            untuk rujukan karir.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right: Donut Chart -->
                        <div
                            class="mx-auto flex w-full max-w-md flex-col items-center justify-center rounded-2xl border border-border/60 bg-background p-4 shadow-sm dark:bg-zinc-950"
                        >
                            <h3
                                class="mb-2 text-center text-sm font-bold tracking-wide text-muted-foreground uppercase"
                            >
                                Rasio Keterlibatan Magang Mahasiswa
                            </h3>

                            <div
                                class="relative flex w-full items-center justify-center"
                            >
                                <ImpactChart :statistics="landingStats" />
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Features Section -->
            <section id="features" class="bg-background py-20 md:py-24">
                <div class="mx-auto max-w-7xl px-6 lg:px-8">
                    <!-- Section Header -->
                    <div class="mx-auto mb-16 max-w-3xl text-center">
                        <Badge
                            variant="outline"
                            class="mb-3 border-yellow-500/20 bg-yellow-500/10 px-3.5 py-1 font-semibold text-amber-600 hover:bg-yellow-500/10 hover:text-amber-600 dark:border-yellow-400/20 dark:bg-yellow-400/10 dark:text-yellow-300 dark:hover:text-yellow-300"
                        >
                            Fitur Utama
                        </Badge>
                        <h2
                            class="text-3xl font-bold tracking-tight text-foreground sm:text-4xl"
                        >
                            Segala Kemudahan dalam Satu Dasbor
                        </h2>
                        <p class="mt-4 text-base text-muted-foreground">
                            MagangHub merampingkan proses administratif magang
                            secara komprehensif, mulai dari tahap pembentukan
                            kelompok hingga pelaporan nilai.
                        </p>
                    </div>

                    <!-- Grid Layout (3 cols x 2 rows) -->
                    <div
                        class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3"
                    >
                        <Card
                            v-for="(feature, index) in features"
                            :key="index"
                            class="group relative rounded-xl border border-border/80 bg-card p-6 shadow-none transition-all duration-300 hover:border-primary/30 hover:shadow-md"
                        >
                            <CardContent class="p-0">
                                <div
                                    class="mb-5 flex h-11 w-11 items-center justify-center rounded-lg bg-primary/10 text-primary transition-transform duration-200 group-hover:scale-105"
                                >
                                    <component
                                        :is="feature.icon"
                                        class="h-5.5 w-5.5"
                                    />
                                </div>
                                <h3
                                    class="mb-2 text-base font-semibold text-foreground"
                                >
                                    {{ feature.title }}
                                </h3>
                                <p
                                    class="text-sm leading-relaxed text-muted-foreground"
                                >
                                    {{ feature.description }}
                                </p>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </section>

            <!-- Workflow Section -->
            <section
                id="workflow"
                class="relative overflow-hidden border-y border-border/20 bg-zinc-50/70 py-20 md:py-24 dark:bg-zinc-900/10"
            >
                <!-- Decorative light blob -->
                <div
                    class="absolute -bottom-20 -left-20 -z-10 h-72 w-72 rounded-full bg-zinc-200/30 blur-3xl dark:bg-zinc-800/10"
                ></div>
                <div class="mx-auto max-w-7xl px-6 lg:px-8">
                    <!-- Section Header -->
                    <div class="mx-auto mb-16 max-w-3xl text-center">
                        <Badge
                            variant="outline"
                            class="mb-3 border-yellow-500/20 bg-yellow-500/10 px-3.5 py-1 font-semibold text-amber-600 hover:bg-yellow-500/10 hover:text-amber-600 dark:border-yellow-400/20 dark:bg-yellow-400/10 dark:text-yellow-300 dark:hover:text-yellow-300"
                        >
                            Alur Kerja
                        </Badge>
                        <h2
                            class="text-3xl font-bold tracking-tight text-foreground sm:text-4xl"
                        >
                            Alur Pengajuan yang Terintegrasi
                        </h2>
                        <p class="mt-4 text-base text-muted-foreground">
                            Langkah mudah mengajukan magang dari awal
                            pembentukan kelompok hingga masa magang resmi
                            dimulai.
                        </p>
                    </div>

                    <!-- Timeline Steps -->
                    <WorkflowStepper mode="landing" v-model="hoveredStep" />
                </div>
            </section>

            <!-- Statistics Section -->
            <section
                class="relative overflow-hidden border-y border-emerald-500/25 bg-linear-to-br from-emerald-600 via-emerald-700 to-teal-800 py-16 text-white md:py-20 dark:from-emerald-900 dark:via-emerald-950 dark:to-teal-950"
            >
                <!-- Decorative vector patterns inside statistics section for texture -->
                <div
                    class="pointer-events-none absolute inset-0 opacity-10 mix-blend-overlay"
                >
                    <svg
                        viewBox="0 0 100 100"
                        class="h-full w-full fill-current"
                    >
                        <circle cx="20" cy="20" r="30" />
                        <circle cx="80" cy="80" r="40" />
                    </svg>
                </div>

                <div class="relative z-10 mx-auto max-w-7xl px-6 lg:px-8">
                    <div
                        class="grid grid-cols-2 gap-x-8 gap-y-12 divide-x-0 text-center md:grid-cols-4 md:divide-x md:divide-white/10"
                    >
                        <div
                            v-for="stat in stats"
                            :key="stat.label"
                            class="flex flex-col items-center px-4"
                        >
                            <div
                                class="text-5xl font-black tracking-tight text-yellow-300 drop-shadow-md sm:text-6xl"
                            >
                                <CountUp
                                    :from="0"
                                    :to="stat.target"
                                    :suffix="stat.suffix"
                                    separator="."
                                    direction="up"
                                    :duration="1.5"
                                    :delay="0.1"
                                />
                            </div>
                            <div
                                class="mt-3 text-xs font-bold tracking-wider text-emerald-100/90 uppercase"
                            >
                                {{ stat.label }}
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- FAQ Section -->
            <section id="faq" class="bg-background py-20 md:py-24">
                <div class="mx-auto max-w-4xl px-6 lg:px-8">
                    <!-- Section Header -->
                    <div class="mx-auto mb-16 max-w-3xl text-center">
                        <Badge
                            variant="outline"
                            class="mb-3 border-yellow-500/20 bg-yellow-500/10 px-3.5 py-1 font-semibold text-amber-600 hover:bg-yellow-500/10 hover:text-amber-600 dark:border-yellow-400/20 dark:bg-yellow-400/10 dark:text-yellow-300 dark:hover:text-yellow-300"
                        >
                            Tanya Jawab
                        </Badge>
                        <h2
                            class="text-3xl font-bold tracking-tight text-foreground sm:text-4xl"
                        >
                            Pertanyaan yang Sering Diajukan
                        </h2>
                    </div>

                    <!-- Custom Accordion -->
                    <Accordion
                        type="single"
                        collapsible
                        class="w-full space-y-4"
                    >
                        <AccordionItem
                            v-for="(faq, index) in faqs"
                            :key="index"
                            :value="'faq-' + index"
                            class="overflow-hidden rounded-xl border border-border/80 bg-card transition-colors duration-200"
                        >
                            <AccordionTrigger
                                class="flex w-full items-center justify-between px-6 py-4.5 text-left text-sm font-semibold hover:bg-muted/30 hover:no-underline focus:outline-hidden sm:text-base"
                            >
                                <span class="text-foreground">{{
                                    faq.question
                                }}</span>
                            </AccordionTrigger>

                            <AccordionContent
                                class="border-t border-border/30 bg-muted/10 px-6 pb-5"
                            >
                                <p
                                    class="pt-4 text-xs leading-relaxed text-muted-foreground sm:text-sm"
                                >
                                    {{ faq.answer }}
                                </p>
                            </AccordionContent>
                        </AccordionItem>
                    </Accordion>
                </div>
            </section>

            <!-- CTA Jumbotron -->
            <section class="mx-6 my-12 max-w-7xl lg:mx-auto">
                <div
                    class="relative overflow-hidden rounded-2xl border border-emerald-500/20 bg-linear-to-br from-emerald-600 via-emerald-700 to-teal-800 px-8 py-16 text-center text-primary-foreground shadow-xl dark:from-emerald-900 dark:via-emerald-950 dark:to-teal-950"
                >
                    <!-- Soft yellow radial glow accents (Unimed identity) -->
                    <div
                        class="pointer-events-none absolute -top-24 -right-24 h-48 w-48 rounded-full bg-yellow-300/20 blur-3xl"
                    ></div>
                    <div
                        class="pointer-events-none absolute -bottom-20 -left-20 h-40 w-40 rounded-full bg-yellow-300/10 blur-3xl"
                    ></div>

                    <!-- Vector shapes background -->
                    <div
                        class="pointer-events-none absolute inset-0 opacity-10 mix-blend-overlay"
                    >
                        <svg
                            viewBox="0 0 100 100"
                            class="h-full w-full fill-current"
                        >
                            <circle cx="10" cy="10" r="30" />
                            <circle cx="90" cy="90" r="40" />
                        </svg>
                    </div>

                    <div
                        class="relative z-10 mx-auto flex max-w-2xl flex-col items-center"
                    >
                        <h2
                            class="text-3xl font-bold tracking-tight sm:text-4xl"
                        >
                            Siap Memulai Magang?
                        </h2>
                        <p
                            class="mt-4 text-base leading-relaxed text-emerald-50/90"
                        >
                            Kelola seluruh proses administrasi magang kampus
                            Anda dalam satu platform yang terintegrasi dan
                            terpantau dengan baik.
                        </p>
                        <div class="mt-8 flex justify-center">
                            <Button
                                as-child
                                size="xl"
                                class="bg-white font-semibold text-emerald-800 shadow-md transition-all hover:scale-[1.02] hover:bg-yellow-50 active:scale-[0.98] dark:bg-zinc-100 dark:text-emerald-950 dark:hover:bg-yellow-100"
                            >
                                <Link
                                    :href="
                                        user
                                            ? role === 'student'
                                                ? home()
                                                : dashboard()
                                            : login()
                                    "
                                >
                                    Masuk ke MagangHub
                                </Link>
                            </Button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Footer -->
            <footer
                class="border-t border-zinc-900 bg-zinc-950 text-zinc-400 dark:bg-black"
            >
                <div class="mx-auto max-w-7xl px-6 py-12 lg:px-8">
                    <div class="mb-12 grid grid-cols-1 gap-8 md:grid-cols-4">
                        <!-- Brand info -->
                        <div class="space-y-4 md:col-span-1">
                            <AppLogo keepLight />
                            <p class="text-xs leading-relaxed text-zinc-400">
                                Sistem pengelolaan magang digital untuk
                                menghubungkan mahasiswa, perguruan tinggi, dan
                                industri secara efisien dan transparan.
                            </p>
                        </div>

                        <!-- Column 2: Panduan -->
                        <div>
                            <h4
                                class="mb-4 text-xs font-bold tracking-wider text-zinc-100 uppercase"
                            >
                                Panduan Pengguna
                            </h4>
                            <ul class="space-y-2 text-xs">
                                <li>
                                    <a
                                        href="#"
                                        class="transition-colors duration-150 hover:text-white"
                                        >Panduan Mahasiswa</a
                                    >
                                </li>
                                <li>
                                    <a
                                        href="#"
                                        class="transition-colors duration-150 hover:text-white"
                                        >Panduan Operator</a
                                    >
                                </li>
                                <li>
                                    <a
                                        href="#"
                                        class="transition-colors duration-150 hover:text-white"
                                        >Panduan Administrator</a
                                    >
                                </li>
                            </ul>
                        </div>

                        <!-- Column 3: Kontak -->
                        <div>
                            <h4
                                class="mb-4 text-xs font-bold tracking-wider text-zinc-100 uppercase"
                            >
                                Kontak Kampus
                            </h4>
                            <ul class="space-y-2 text-xs text-zinc-400">
                                <li>Jl. William Iskandar Ps. V</li>
                                <li>Deli Serdang, Sumatera Utara</li>
                                <li>Email: support@csunimed.ac.id</li>
                            </ul>
                        </div>

                        <!-- Column 4: Sosial Media -->
                        <div>
                            <h4
                                class="mb-4 text-xs font-bold tracking-wider text-zinc-100 uppercase"
                            >
                                Sosial Media
                            </h4>
                            <ul class="space-y-2 text-xs">
                                <li>
                                    <a
                                        href="https://www.instagram.com/ilmukomputerunimed/"
                                        target="_blank"
                                        class="transition-colors duration-150 hover:text-white"
                                        >Instagram</a
                                    >
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div
                        class="flex flex-col items-center justify-between gap-4 border-t border-zinc-900 pt-8 text-center sm:flex-row"
                    >
                        <p class="text-[11px] text-zinc-500">
                            &copy; 2026 MagangHub. Hak Cipta Dilindungi
                            Undang-Undang.
                        </p>
                        <p class="text-[11px] text-zinc-500">
                            Universitas Negeri Medan
                        </p>
                    </div>
                </div>
            </footer>
        </div>
    </ScrollArea>
</template>
