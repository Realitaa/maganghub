<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    FileText,
    Users,
    Building2,
    ShieldCheck,
    Activity,
    NotebookPen,
    UserPlus,
    FilePlus,
    Mail,
    CheckCircle,
    Briefcase,
    Menu,
    X,
    Check,
    GraduationCap,
    ArrowRight
} from '@lucide/vue';
import bankIndonesiaSvg from 'idn-finlogos/icons/bank-indonesia';
import briSvg from 'idn-finlogos/icons/bri';
import mandiri from 'idn-finlogos/icons/mandiri'
import { ref, h } from 'vue';
import CountUp from '@/components/landing/CountUp.vue';
import Google from '@/components/landing/icons/Google.vue';
import Goto from '@/components/landing/icons/Goto.vue';
import Huawei from '@/components/landing/icons/Huawei.vue';
import Ibm from '@/components/landing/icons/Ibm.vue';
import IndosatOoredoHutsicon from '@/components/landing/icons/IndosatOoredoHutsicon.vue';
import TelkomIndonesia from '@/components/landing/icons/TelkomIndonesia.vue';
import ImpactChart from '@/components/landing/ImpactChart.vue';
import LogoLoop from '@/components/landing/LogoLoop.vue';
import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from '@/components/ui/accordion';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Stepper, StepperTrigger, StepperItem, StepperIndicator, StepperTitle, StepperDescription, StepperSeparator } from '@/components/ui/stepper';
import { cn } from '@/lib/utils';
import { dashboard, login } from '@/routes';

// state
const hoveredStep = ref(6);
const mobileMenuOpen = ref(false);
const toggleMobileMenu = () => {
    mobileMenuOpen.value = !mobileMenuOpen.value;
};

// Navigation links
const navLinks = [
    { label: 'Beranda', href: '#home' },
    { label: 'Fitur', href: '#features' },
    { label: 'Alur Kerja', href: '#workflow' },
    { label: 'FAQ', href: '#faq' }
];

const svgLogoWrapper = (node: any, className?: string) => h('div', {
    class: cn(
        "flex items-center gap-3 h-7 [&>svg]:h-7 [&>svg]:w-auto text-zinc-400 dark:text-zinc-500 select-none grayscale opacity-60 dark:opacity-40 hover:grayscale-0 hover:opacity-100 transition-all duration-200",
        className
    )
}, [
    h(node)
]);

const idnfinlogosWrapper = (logo: any) => {
    return `<div class="flex items-center gap-3 text-zinc-400 dark:text-zinc-500 font-bold text-lg select-none grayscale opacity-60 dark:opacity-40 hover:grayscale-0 hover:opacity-100 transition-all duration-200">
                <div class="h-7 w-auto flex items-center justify-center [&>svg]:h-7 [&>svg]:w-auto">${logo}</div>
            </div>`
};
// Company logos loop items
const companyLogos = [
    {
        node: svgLogoWrapper(Google),
        title: 'Google',
        href: 'https://google.com/'
    },
    {
        node: svgLogoWrapper(TelkomIndonesia),
        title: 'Telkom Indonesia',
        href: 'https://www.telkom.co.id/'
    },
    {
        node: idnfinlogosWrapper(briSvg),
        title: 'BRI',
        href: 'https://www.bri.co.id'
    },
    {
        node: idnfinlogosWrapper(bankIndonesiaSvg),
        title: 'Bank Indonesia',
        href: 'https://www.bi.go.id'
    },
    {
        node: svgLogoWrapper(IndosatOoredoHutsicon),
        title: 'Indosat Ooredoo Hutchison',
        href: 'https://www.ioh.co.id'
    },
    {
        node: idnfinlogosWrapper(mandiri),
        title: 'Bank Mandiri',
        href: 'https://www.bankmandiri.co.id'
    },
    {
        node: svgLogoWrapper(Ibm),
        title: 'IBM',
        href: 'https://www.ibm.com'
    },
    {
        node: svgLogoWrapper(Huawei),
        title: 'Huawei',
        href: 'https://www.huawei.com/'
    },
    {
        node: svgLogoWrapper(Goto),
        title: 'Goto',
        href: 'https://www.goto.com/'
    }
];

// Statistics data for Impact section
const landingStats = {
    global: 40,
    multinational: 75,
    international: 60,
    national: 110,
    regional: 90,
    local: 165,
    havenot: 260
};

const features = [
    {
        title: 'Pengajuan Magang',
        description: 'Ajukan program magang secara fleksibel baik secara individu maupun dalam ikatan kelompok terdaftar.',
        icon: FileText
    },
    {
        title: 'Kelola Kelompok',
        description: 'Undang sesama rekan mahasiswa ke kelompok magang Anda serta kelola peran masing-masing anggota.',
        icon: Users
    },
    {
        title: 'Perusahaan Tujuan',
        description: 'Pantau dan kelola database perusahaan mitra kampus beserta informasi kontak penting untuk pengajuan.',
        icon: Building2
    },
    {
        title: 'Persetujuan Admin',
        description: 'Proses review, verifikasi berkas, dan validasi persetujuan surat magang secara digital oleh koordinator.',
        icon: ShieldCheck
    },
    {
        title: 'Monitoring Status',
        description: 'Pantau secara real-time status surat pengantar dan konfirmasi balasan penerimaan dari instansi.',
        icon: Activity
    },
    {
        title: 'Logbook Magang',
        description: 'Catat laporan aktivitas harian, unggah bukti penugasan magang, dan dapatkan umpan balik dosen.',
        icon: NotebookPen
    }
];

const workflowSteps = [
    {
        step: 1,
        title: 'Buat Kelompok',
        description: 'Bentuk kelompok magang bersama rekan mahasiswa dari program studi yang sama.',
        icon: UserPlus
    },
    {
        step: 2,
        title: 'Ajukan Magang',
        description: 'Masukkan data lengkap perusahaan tujuan dan berkas proposal magang.',
        icon: FilePlus
    },
    {
        step: 3,
        title: 'Review Admin',
        description: 'Persetujuan akademik dan verifikasi berkas oleh koordinator magang program studi.',
        icon: ShieldCheck
    },
    {
        step: 4,
        title: 'Kirim Perusahaan',
        description: 'Penerbitan surat permohonan magang resmi dan pengiriman ke pihak instansi tujuan.',
        icon: Mail
    },
    {
        step: 5,
        title: 'Terima Respons',
        description: 'Instansi memberikan balasan penerimaan dan mahasiswa mengunggah bukti tersebut di portal.',
        icon: CheckCircle
    },
    {
        step: 6,
        title: 'Mulai Magang',
        description: 'Kegiatan magang resmi dimulai. Laporkan perkembangan harian Anda melalui modul logbook digital.',
        icon: Briefcase
    }
];

// Statistics counter state & animation
const stats = ref([
    { label: 'Mahasiswa Terdaftar', target: 1200, current: 0, suffix: '+' },
    { label: 'Kelompok Magang', target: 350, current: 0, suffix: '+' },
    { label: 'Perusahaan Mitra', target: 180, current: 0, suffix: '+' },
    { label: 'Pengajuan Diproses', target: 95, current: 0, suffix: '%' }
]);

const faqs = [
    {
        question: 'Bagaimana cara membuat kelompok?',
        answer: 'Mahasiswa dapat membentuk kelompok magang melalui dashboard dengan menavigasi ke menu "Kelola Kelompok". Anda cukup memasukkan NIM rekan mahasiswa yang terdaftar di platform untuk mengundang mereka masuk ke kelompok, atau kirimkan tautan undangan untuk bergabung ke kelompok magang!'
    },
    {
        question: 'Berapa anggota yang diperlukan dalam satu kelompok untuk magang?',
        answer: 'Satu kelompok magang dapat berisi minimal dua orang, dan tidak ada batas maksimal.'
    },
    {
        question: 'Bagaimana jika pengajuan ditolak?',
        answer: 'Jika pengajuan ditolak oleh administrator program studi atau perusahaan, Anda akan menerima catatan penolakan. Mahasiswa dapat memperbarui data pengajuan, merevisi dokumen, atau mengajukan usulan baru ke perusahaan lain.'
    },
    {
        question: 'Apa itu logbook?',
        answer: 'Logbook adalah catatan aktivitas yang diisi oleh mahasiswa selama masa magang. Logbook ini digunakan oleh dosen pembimbing dan koordinator magang untuk memonitor kontribusi dan perkembangan kompetensi mahasiswa. Fitur ini sedang dalam pengembangan, nantikan ya!'
    }
];
</script>

<template>
    <Head title="MagangHub - Platform Pengelolaan Magang Mahasiswa" />

    <div class="min-h-screen bg-background font-sans text-foreground antialiased selection:bg-primary/20 selection:text-primary">
        <!-- Sticky Navigation -->
        <header class="fixed top-0 z-50 w-full border-b border-border/40 bg-background/80 backdrop-blur-md transition-all duration-300">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-6 lg:px-8">
                <!-- Logo -->
                <Link href="/" class="flex items-center gap-2.5 group">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-primary text-primary-foreground group-hover:scale-105 transition-transform duration-200">
                        <GraduationCap class="h-5.5 w-5.5" />
                    </div>
                    <span class="text-xl font-bold tracking-tight text-foreground">
                        Magang<span class="text-primary font-extrabold">Hub</span>
                    </span>
                </Link>

                <!-- Desktop Menu -->
                <nav class="hidden md:flex items-center gap-8">
                    <a 
                        v-for="link in navLinks" 
                        :key="link.label" 
                        :href="link.href"
                        class="text-sm font-medium text-muted-foreground hover:text-foreground transition-colors duration-200"
                    >
                        {{ link.label }}
                    </a>
                </nav>

                <!-- CTA Button -->
                <div class="hidden md:flex items-center gap-4">
                    <Button as-child size="sm" class="h-9.5 px-5">
                        <Link :href="$page.props.auth?.user ? dashboard() : login()">
                            {{ $page.props.auth?.user ? 'Dashboard' : 'Masuk ke Sistem' }}
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
                class="border-b border-border/80 bg-background md:hidden transition-all duration-300"
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
                    <div class="mt-4 px-3 border-t border-border pt-4">
                        <Button as-child class="w-full">
                            <Link :href="$page.props.auth?.user ? dashboard() : login()">
                                {{ $page.props.auth?.user ? 'Dashboard' : 'Masuk ke Sistem' }}
                            </Link>
                        </Button>
                    </div>
                </div>
            </div>
        </header>

        <!-- Hero Section -->
        <section id="home" class="relative overflow-hidden bg-background min-h-[calc(100vh-2rem)] flex flex-col justify-center py-12 md:py-20">
            <div class="mx-auto max-w-7xl px-6 lg:px-8 text-center w-full">
                <!-- Badge -->
                <Badge variant="outline" class="mx-auto mb-8 inline-flex items-center gap-1.5 rounded-full border border-primary/20 bg-primary/5 px-3 py-1 text-xs font-semibold text-primary dark:border-primary/30 dark:bg-primary/10">
                    <span class="flex h-1.5 w-1.5 rounded-full bg-primary animate-pulse"></span>
                    Platform Pengelolaan Magang Mahasiswa
                </Badge>

                <!-- Headline -->
                <h1 class="mx-auto max-w-4xl text-4xl font-extrabold tracking-tight text-foreground sm:text-5xl lg:text-6xl leading-[1.15]">
                    Kelola Pengajuan Magang Lebih <span class="text-primary">Mudah</span> dan <span class="text-primary">Terstruktur</span>
                </h1>

                <!-- Subheadline -->
                <p class="mx-auto mt-8 max-w-3xl text-base text-muted-foreground sm:text-lg lg:text-xl leading-relaxed">
                    MagangHub membantu mahasiswa, kelompok magang, dan administrator kampus mengelola proses pengajuan, monitoring, dan pelaporan magang dalam satu platform terpadu.
                </p>

                <!-- CTA Buttons -->
                <div class="mt-10 flex flex-wrap items-center justify-center gap-4">
                    <Button as-child size="lg" class="h-12 px-7 text-base font-medium shadow-sm hover:scale-[1.01] active:scale-[0.99] transition-all duration-150">
                        <Link :href="$page.props.auth?.user ? dashboard() : login()">
                            {{ $page.props.auth?.user ? 'Masuk ke Dashboard' : 'Masuk ke Sistem' }}
                            <ArrowRight class="ml-2 h-4 w-4" />
                        </Link>
                    </Button>
                    <Button as-child variant="outline" size="lg" class="h-12 px-7 text-base font-medium transition-all duration-150">
                        <a href="#features">
                            Pelajari Fitur
                        </a>
                    </Button>
                </div>

                <!-- Scrolling Logo Cloud -->
                <div class="mt-26 border-t border-border/55 pt-12">
                    <p class="text-xs font-semibold tracking-wider text-muted-foreground uppercase mb-6">
                        Dipersiapkan untuk Mendukung Kolaborasi Kampus dan Dunia Industri
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
        <section class="bg-muted/50 dark:bg-zinc-900/30 py-20 md:py-24 border-y border-border/30">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="grid grid-cols-1 gap-12 lg:grid-cols-2 lg:gap-16 items-center">
                    <!-- Left: Why Internships Matter -->
                    <div>
                        <div class="inline-flex items-center gap-2 rounded-lg bg-primary/10 px-3 py-1 text-xs font-semibold text-primary mb-4">
                            Dampak & Relevansi
                        </div>
                        <h2 class="text-3xl font-bold tracking-tight text-foreground sm:text-4xl">
                            Mengapa Magang Penting?
                        </h2>
                        <p class="mt-5 text-base text-muted-foreground leading-relaxed">
                            Program magang kerja merupakan jembatan emas bagi mahasiswa untuk mentransformasikan ilmu teoritis kampus ke dalam praktik industri riil. Magang berkontribusi nyata dalam mempersiapkan daya saing lulusan sebelum resmi memasuki pasar tenaga kerja.
                        </p>
                        
                        <div class="mt-8 space-y-4">
                            <div class="flex items-start gap-3">
                                <div class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                                    <Check class="h-3.5 w-3.5" />
                                </div>
                                <div>
                                    <h4 class="text-sm font-semibold text-foreground">Kesiapan Kerja dan Industri</h4>
                                    <p class="text-xs text-muted-foreground mt-0.5">Memahami budaya kerja profesional, kedisiplinan instansi, dan etos kerja industri.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                                    <Check class="h-3.5 w-3.5" />
                                </div>
                                <div>
                                    <h4 class="text-sm font-semibold text-foreground">Peningkatan Keterampilan Praktis</h4>
                                    <p class="text-xs text-muted-foreground mt-0.5">Mengasah hard skills teknis lapangan serta soft skills pemecahan masalah dan komunikasi.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                                    <Check class="h-3.5 w-3.5" />
                                </div>
                                <div>
                                    <h4 class="text-sm font-semibold text-foreground">Jejaring Kerja (Networking)</h4>
                                    <p class="text-xs text-muted-foreground mt-0.5">Membangun koneksi awal dengan para profesional dan supervisor industri untuk rujukan karir.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Donut Chart -->
                    <div class="flex flex-col items-center justify-center bg-background border border-border/60 rounded-2xl p-4 shadow-sm max-w-md mx-auto w-full dark:bg-zinc-950">
                        <h3 class="text-sm font-bold tracking-wide text-muted-foreground uppercase mb-2 text-center">
                            Rasio Keterlibatan Magang Mahasiswa
                        </h3>
                        
                        <div class="relative flex items-center justify-center w-full">
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
                <div class="mx-auto max-w-3xl text-center mb-16">
                    <div class="inline-flex items-center gap-2 rounded-lg bg-primary/10 px-3 py-1 text-xs font-semibold text-primary mb-3">
                        Fitur Utama
                    </div>
                    <h2 class="text-3xl font-bold tracking-tight text-foreground sm:text-4xl">
                        Segala Kemudahan dalam Satu Dasbor
                    </h2>
                    <p class="mt-4 text-base text-muted-foreground">
                        MagangHub merampingkan proses administratif magang secara komprehensif, mulai dari tahap pembentukan kelompok hingga pelaporan nilai.
                    </p>
                </div>

                <!-- Grid Layout (3 cols x 2 rows) -->
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <Card 
                        v-for="(feature, index) in features" 
                        :key="index"
                        class="group relative bg-card border border-border/80 hover:border-primary/30 rounded-xl p-6 hover:shadow-md transition-all duration-300 shadow-none"
                    >
                        <CardContent class="p-0">
                            <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-primary/10 text-primary group-hover:scale-105 transition-transform duration-200 mb-5">
                                <component :is="feature.icon" class="h-5.5 w-5.5" />
                            </div>
                            <h3 class="text-base font-semibold text-foreground mb-2">
                                {{ feature.title }}
                            </h3>
                            <p class="text-sm text-muted-foreground leading-relaxed">
                                {{ feature.description }}
                            </p>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </section>

        <!-- Workflow Section -->
        <section id="workflow" class="bg-muted/50 dark:bg-zinc-900/30 py-20 md:py-24 border-y border-border/30">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <!-- Section Header -->
                <div class="mx-auto max-w-3xl text-center mb-16">
                    <div class="inline-flex items-center gap-2 rounded-lg bg-primary/10 px-3 py-1 text-xs font-semibold text-primary mb-3">
                        Alur Kerja
                    </div>
                    <h2 class="text-3xl font-bold tracking-tight text-foreground sm:text-4xl">
                        Alur Pengajuan yang Terintegrasi
                    </h2>
                    <p class="mt-4 text-base text-muted-foreground">
                        Langkah mudah mengajukan magang dari awal pembentukan kelompok hingga masa magang resmi dimulai.
                    </p>
                </div>

                <!-- Timeline Steps -->
                <!-- Desktop: Horizontal Layout -->
                <Stepper :model-value="hoveredStep" class="hidden lg:flex justify-between items-start w-full relative">
                    <template v-for="item in workflowSteps" :key="item.step">
                        <StepperItem
                            :step="item.step"
                            @mouseenter="hoveredStep = item.step"
                            class="flex flex-col items-center text-center group z-10 relative flex-1"
                        >
                            <StepperTrigger class="p-0 flex flex-col items-center gap-0 hover:bg-transparent cursor-default">
                                <StepperIndicator class="flex h-16 w-16 items-center justify-center rounded-full border-4 border-background bg-muted text-muted-foreground transition-all duration-300 group-data-[state=active]:bg-primary group-data-[state=active]:text-primary-foreground group-data-[state=active]:border-primary/25">
                                    <component :is="item.icon" class="h-6 w-6" />
                                </StepperIndicator>
                            </StepperTrigger>
                            
                            <StepperSeparator 
                                v-if="item.step !== workflowSteps.length"
                                class="absolute top-8 left-[calc(50%+2rem)] right-[calc(-50%+2rem)] h-[2px] bg-border group-data-[state=completed]:bg-primary transition-colors duration-300 z-0"
                            />
                            
                            <span class="mt-3 text-xs font-bold text-primary uppercase tracking-wider">Langkah {{ item.step }}</span>
                            <StepperTitle class="mt-2 text-sm font-bold text-foreground">{{ item.title }}</StepperTitle>
                            <StepperDescription class="mt-1 text-xs text-muted-foreground px-2">{{ item.description }}</StepperDescription>
                        </StepperItem>
                    </template>
                </Stepper>

                <!-- Mobile: Vertical Layout -->
                <Stepper :model-value="hoveredStep" orientation="vertical" class="lg:hidden flex flex-col space-y-8 relative before:absolute before:top-2 before:bottom-2 before:left-[27px] before:w-[2px] before:bg-border">
                    <StepperItem
                        v-for="item in workflowSteps"
                        :key="item.step"
                        :step="item.step"
                        @mouseenter="hoveredStep = item.step"
                        class="flex items-start gap-4 group w-full"
                    >
                        <StepperTrigger class="p-0 flex items-center justify-center hover:bg-transparent cursor-default">
                            <StepperIndicator class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full border-4 border-background bg-muted text-muted-foreground transition-all duration-300 group-data-[state=active]:bg-primary group-data-[state=active]:text-primary-foreground group-data-[state=active]:border-primary/25 z-10">
                                <component :is="item.icon" class="h-5.5 w-5.5" />
                            </StepperIndicator>
                        </StepperTrigger>
                        
                        <div class="flex-1 bg-background border border-border/80 rounded-xl p-4.5 dark:bg-zinc-950">
                            <span class="text-xs font-bold text-primary uppercase tracking-wider">Langkah {{ item.step }}</span>
                            <StepperTitle class="text-sm font-bold text-foreground mt-0.5">{{ item.title }}</StepperTitle>
                            <StepperDescription class="text-xs text-muted-foreground mt-1">{{ item.description }}</StepperDescription>
                        </div>
                    </StepperItem>
                </Stepper>
            </div>
        </section>

        <!-- Dashboard Preview Section -->
        <section class="bg-background py-20 md:py-24">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <!-- Section Header -->
                <div class="mx-auto max-w-3xl text-center mb-16">
                    <div class="inline-flex items-center gap-2 rounded-lg bg-primary/10 px-3 py-1 text-xs font-semibold text-primary mb-3">
                        Tinjauan Dasbor
                    </div>
                    <h2 class="text-3xl font-bold tracking-tight text-foreground sm:text-4xl">
                        Dasbor Khusus Berdasarkan Kebutuhan Anda
                    </h2>
                    <p class="mt-4 text-base text-muted-foreground">
                        Sistem dirancang dengan antarmuka yang disesuaikan untuk kebutuhan Mahasiswa maupun Administrator Kampus.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-12 lg:grid-cols-2 lg:gap-8 items-start">
                    <!-- Left: Student Dashboard Preview -->
                    <div class="flex flex-col bg-card border border-border/80 rounded-2xl p-6 shadow-sm dark:bg-zinc-950">
                        <div class="flex items-center justify-between border-b border-border pb-4 mb-6">
                            <div class="flex items-center gap-3">
                                <span class="h-8.5 w-8.5 rounded-full bg-emerald-500/10 flex items-center justify-center text-emerald-600 dark:text-emerald-400 font-bold text-sm">
                                    RM
                                </span>
                                <div>
                                    <h4 class="text-sm font-bold text-foreground leading-none">Reza Mulia Putra</h4>
                                    <span class="text-[11px] text-muted-foreground">Mahasiswa - Ilmu Komputer</span>
                                </div>
                            </div>
                            <span class="inline-flex items-center rounded-full bg-emerald-500/10 px-2 py-0.5 text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                                Magang Aktif
                            </span>
                        </div>

                        <!-- Card Info: Status Pengajuan -->
                        <div class="space-y-4">
                            <div class="bg-muted/40 border border-border/50 rounded-xl p-4 dark:bg-zinc-900/40">
                                <span class="text-[11px] text-muted-foreground uppercase font-bold tracking-wider">Perusahaan Tujuan</span>
                                <h5 class="text-sm font-bold text-foreground mt-0.5">PT Telekomunikasi Indonesia</h5>
                                <div class="mt-3 flex items-center justify-between text-xs text-muted-foreground">
                                    <span>Status Pengusulan</span>
                                    <span class="font-semibold text-emerald-600 dark:text-emerald-400">Diterima Perusahaan</span>
                                </div>
                            </div>

                            <!-- Group Info -->
                            <div class="border border-border/80 rounded-xl p-4">
                                <h5 class="text-xs font-bold text-foreground mb-3">Informasi Kelompok (Kelompok 12)</h5>
                                <div class="space-y-2.5">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="text-muted-foreground">Reza Mulia Putra (Ketua)</span>
                                        <span class="text-emerald-600 dark:text-emerald-400 font-medium">Ready</span>
                                    </div>
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="text-muted-foreground">Jon Doe</span>
                                        <span class="text-emerald-600 dark:text-emerald-400 font-medium">Ready</span>
                                    </div>
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="text-muted-foreground">Jane Doe</span>
                                        <span class="text-emerald-600 dark:text-emerald-400 font-medium">Ready</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Submission Progress -->
                            <div class="border border-border/80 rounded-xl p-4">
                                <h5 class="text-xs font-bold text-foreground mb-3">Progress Pengajuan</h5>
                                <div class="flex items-center justify-between text-[11px] text-muted-foreground mb-2">
                                    <span>Review Akademik</span>
                                    <span class="font-bold text-foreground">Selesai</span>
                                </div>
                                <div class="w-full bg-border rounded-full h-1.5 overflow-hidden">
                                    <div class="bg-primary h-1.5 rounded-full" style="width: 100%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Administrator Dashboard Preview -->
                    <div class="flex flex-col bg-card border border-border/80 rounded-2xl p-6 shadow-sm dark:bg-zinc-950">
                        <div class="flex items-center justify-between border-b border-border pb-4 mb-6">
                            <div class="flex items-center gap-3">
                                <span class="h-8.5 w-8.5 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-sm">
                                    DK
                                </span>
                                <div>
                                    <h4 class="text-sm font-bold text-foreground leading-none">Dedy Kiswanto S.Kom, M.Kom</h4>
                                    <span class="text-[11px] text-muted-foreground">Admin Koordinator Magang</span>
                                </div>
                            </div>
                            <span class="inline-flex items-center rounded-full bg-primary/15 px-2 py-0.5 text-xs font-semibold text-primary">
                                Level Administrator
                            </span>
                        </div>

                        <!-- Statistics Overview Card -->
                        <div class="grid grid-cols-2 gap-3 mb-5">
                            <div class="border border-border/85 rounded-xl p-3 bg-muted/20 dark:bg-zinc-900/20">
                                <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-wide">Butuh Review</span>
                                <div class="text-xl font-extrabold text-foreground mt-0.5">14</div>
                            </div>
                            <div class="border border-border/85 rounded-xl p-3 bg-muted/20 dark:bg-zinc-900/20">
                                <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-wide">Total Kelompok</span>
                                <div class="text-xl font-extrabold text-foreground mt-0.5">112</div>
                            </div>
                        </div>

                        <!-- Recent Activity Stream -->
                        <div class="border border-border/80 rounded-xl p-4">
                            <h5 class="text-xs font-bold text-foreground mb-4">Aktivitas Terbaru Sistem</h5>
                            <div class="space-y-4">
                                <div class="flex gap-3 text-xs">
                                    <span class="h-2 w-2 rounded-full bg-amber-500 mt-1.5 shrink-0"></span>
                                    <div>
                                        <p class="text-muted-foreground"><strong class="text-foreground font-semibold">Kelompok 15 (Teknik Informatika)</strong> mengajukan permohonan magang di PT Solusi Tekno.</p>
                                        <span class="text-[10px] text-muted-foreground mt-1 block">5 menit yang lalu</span>
                                    </div>
                                </div>
                                <div class="flex gap-3 text-xs">
                                    <span class="h-2 w-2 rounded-full bg-emerald-500 mt-1.5 shrink-0"></span>
                                    <div>
                                        <p class="text-muted-foreground"><strong class="text-foreground font-semibold">Admin</strong> menyetujui surat pengantar magang Kelompok 12 ke PT Solusi Awan.</p>
                                        <span class="text-[10px] text-muted-foreground mt-1 block">2 jam yang lalu</span>
                                    </div>
                                </div>
                                <div class="flex gap-3 text-xs">
                                    <span class="h-2 w-2 rounded-full bg-indigo-500 mt-1.5 shrink-0"></span>
                                    <div>
                                        <p class="text-muted-foreground"><strong class="text-foreground font-semibold">Mahasiswa (Rizal Utama)</strong> memperbarui logbook aktivitas hari ke-15.</p>
                                        <span class="text-[10px] text-muted-foreground mt-1 block">4 jam yang lalu</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Statistics Section -->
        <section class="bg-[#f0faf5] dark:bg-[#061f16]/30 py-16 md:py-20 border-y border-emerald-100/50 dark:border-emerald-950/20">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="grid grid-cols-2 gap-y-12 gap-x-8 md:grid-cols-4 text-center">
                    <div v-for="stat in stats" :key="stat.label" class="flex flex-col items-center">
                        <div class="text-4xl font-extrabold tracking-tight text-primary sm:text-5xl">
                            <CountUp
                                :from="0"
                                :to="stat.target"
                                :suffix="stat.suffix"
                                separator="."
                                direction="up"
                                :duration="1"
                                :delay="0.25"
                            />
                        </div>
                        <div class="mt-2 text-sm font-semibold text-muted-foreground">
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
                <div class="mx-auto max-w-3xl text-center mb-16">
                    <div class="inline-flex items-center gap-2 rounded-lg bg-primary/10 px-3 py-1 text-xs font-semibold text-primary mb-3">
                        Tanya Jawab
                    </div>
                    <h2 class="text-3xl font-bold tracking-tight text-foreground sm:text-4xl">
                        Pertanyaan yang Sering Diajukan
                    </h2>
                </div>

                <!-- Custom Accordion -->
                <Accordion type="single" collapsible class="space-y-4 w-full">
                    <AccordionItem 
                        v-for="(faq, index) in faqs" 
                        :key="index"
                        :value="'faq-' + index"
                        class="border border-border/80 rounded-xl overflow-hidden bg-card transition-colors duration-200"
                    >
                        <AccordionTrigger class="flex w-full items-center justify-between px-6 py-4.5 text-left font-semibold text-sm sm:text-base hover:bg-muted/30 focus:outline-hidden hover:no-underline">
                            <span class="text-foreground">{{ faq.question }}</span>
                        </AccordionTrigger>
                        
                        <AccordionContent class="px-6 pb-5 border-t border-border/30 bg-muted/10">
                            <p class="text-xs sm:text-sm text-muted-foreground leading-relaxed pt-4">
                                {{ faq.answer }}
                            </p>
                        </AccordionContent>
                    </AccordionItem>
                </Accordion>
            </div>
        </section>

        <!-- CTA Jumbotron -->
        <section class="mx-6 my-12 max-w-7xl lg:mx-auto">
            <div class="relative overflow-hidden bg-primary px-8 py-16 text-center text-primary-foreground rounded-2xl shadow-xl dark:bg-emerald-950 dark:border dark:border-emerald-900/40">
                <!-- Vector shapes background -->
                <div class="absolute inset-0 opacity-15 pointer-events-none">
                    <svg viewBox="0 0 100 100" class="w-full h-full fill-current">
                        <circle cx="10" cy="10" r="30" />
                        <circle cx="90" cy="90" r="40" />
                    </svg>
                </div>
                
                <div class="relative z-10 max-w-2xl mx-auto">
                    <h2 class="text-3xl font-bold tracking-tight sm:text-4xl">
                        Siap Memulai Magang?
                    </h2>
                    <p class="mt-4 text-base text-primary-foreground/85 leading-relaxed">
                        Kelola seluruh proses administrasi magang kampus Anda dalam satu platform yang terintegrasi dan terpantau dengan baik.
                    </p>
                    <div class="mt-8 flex justify-center">
                        <Button as-child variant="secondary" size="xl" class="bg-background text-primary hover:bg-background/95 font-semibold shadow-sm">
                            <Link :href="$page.props.auth?.user ? dashboard() : login()">
                                Masuk ke MagangHub
                            </Link>
                        </Button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-zinc-950 text-zinc-400 dark:bg-black border-t border-zinc-900">
            <div class="mx-auto max-w-7xl px-6 py-12 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-12">
                    <!-- Brand info -->
                    <div class="space-y-4 md:col-span-1">
                        <div class="flex items-center gap-2">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary text-white">
                                <GraduationCap class="h-4.5 w-4.5" />
                            </div>
                            <span class="text-lg font-bold text-white tracking-tight">MagangHub</span>
                        </div>
                        <p class="text-xs text-zinc-400 leading-relaxed">
                            Sistem pengelolaan magang digital untuk menghubungkan mahasiswa, perguruan tinggi, dan industri secara efisien dan transparan.
                        </p>
                    </div>

                    <!-- Column 2: Panduan -->
                    <div>
                        <h4 class="text-xs font-bold tracking-wider text-zinc-100 uppercase mb-4">Panduan Pengguna</h4>
                        <ul class="space-y-2 text-xs">
                            <li><a href="#" class="hover:text-white transition-colors duration-150">Panduan Mahasiswa</a></li>
                            <li><a href="#" class="hover:text-white transition-colors duration-150">Panduan Koordinator</a></li>
                            <li><a href="#" class="hover:text-white transition-colors duration-150">Panduan Dosen Pembimbing</a></li>
                            <li><a href="#" class="hover:text-white transition-colors duration-150">Kebijakan Akademik</a></li>
                        </ul>
                    </div>

                    <!-- Column 3: Kontak -->
                    <div>
                        <h4 class="text-xs font-bold tracking-wider text-zinc-100 uppercase mb-4">Kontak Kampus</h4>
                        <ul class="space-y-2 text-xs text-zinc-400">
                            <li>Jl. Kampus Merdeka No. 12</li>
                            <li>Jakarta Selatan, DKI Jakarta</li>
                            <li>Email: support@university.ac.id</li>
                            <li>Telp: (021) 800-999-12</li>
                        </ul>
                    </div>

                    <!-- Column 4: Sosial Media -->
                    <div>
                        <h4 class="text-xs font-bold tracking-wider text-zinc-100 uppercase mb-4">Sosial Media</h4>
                        <ul class="space-y-2 text-xs">
                            <li><a href="#" class="hover:text-white transition-colors duration-150">Instagram</a></li>
                            <li><a href="#" class="hover:text-white transition-colors duration-150">LinkedIn Portal</a></li>
                            <li><a href="#" class="hover:text-white transition-colors duration-150">YouTube Channel</a></li>
                            <li><a href="#" class="hover:text-white transition-colors duration-150">Twitter / X</a></li>
                        </ul>
                    </div>
                </div>
                
                <div class="border-t border-zinc-900 pt-8 flex flex-col sm:flex-row items-center justify-between text-center gap-4">
                    <p class="text-[11px] text-zinc-500">
                        &copy; 2026 MagangHub. Hak Cipta Dilindungi Undang-Undang.
                    </p>
                    <p class="text-[11px] text-zinc-500">
                        Dikembangkan oleh Tim IT Universitas Nasional
                    </p>
                </div>
            </div>
        </footer>
    </div>
</template>

<style>
/* Auto-scroll marquee animations */
@keyframes scroll {
  0% { transform: translateX(0); }
  100% { transform: translateX(-50%); }
}
.animate-marquee {
  display: inline-flex;
  animation: scroll 30s linear infinite;
}
</style>
