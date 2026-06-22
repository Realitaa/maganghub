<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
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
    ArrowRight
} from '@lucide/vue';
import { ref } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import CountUp from '@/components/landing/CountUp.vue';
import { google, telkomIndonesia, bri, bankIndonesia, indosatOoredoHutsicon, mandiri, ibm, huawei, goto } from '@/components/landing/icons';
import ImpactChart from '@/components/landing/ImpactChart.vue';
import LogoLoop from '@/components/landing/LogoLoop.vue';
import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from '@/components/ui/accordion';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Stepper, StepperTrigger, StepperItem, StepperIndicator, StepperTitle, StepperDescription, StepperSeparator } from '@/components/ui/stepper';
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
    { label: 'FAQ', href: '#faq' }
];

// Company logos loop items
const companyLogos = [
    {
        node: google,
        title: 'Google',
        href: 'https://google.com/'
    },
    {
        node: telkomIndonesia,
        title: 'Telkom Indonesia',
        href: 'https://www.telkom.co.id/'
    },
    {
        node: bri,
        title: 'BRI',
        href: 'https://www.bri.co.id'
    },
    {
        node: bankIndonesia,
        title: 'Bank Indonesia',
        href: 'https://www.bi.go.id'
    },
    {
        node: indosatOoredoHutsicon,
        title: 'Indosat Ooredoo Hutchison',
        href: 'https://www.ioh.co.id'
    },
    {
        node: mandiri,
        title: 'Bank Mandiri',
        href: 'https://www.bankmandiri.co.id'
    },
    {
        node: ibm,
        title: 'IBM',
        href: 'https://www.ibm.com'
    },
    {
        node: huawei,
        title: 'Huawei',
        href: 'https://www.huawei.com/'
    },
    {
        node: goto,
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
                <AppLogo />

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
                        <Link 
                            :href="user 
                                ? role === 'student' ? home() : dashboard() 
                                : login()"
                        >
                            {{ user 
                                ? role === 'student' ? 'Beranda' : 'Dashboard'
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
                            <Link 
                                :href="user 
                                    ? role === 'student' ? home() : dashboard() 
                                    : login()"
                            >
                                {{ user 
                                    ? role === 'student' ? 'Beranda' : 'Dashboard' 
                                    : 'Masuk' 
                                }}
                            </Link>
                        </Button>
                    </div>
                </div>
            </div>
        </header>

        <!-- Hero Section -->
        <section id="home" class="relative overflow-hidden bg-background min-h-[calc(100vh-2rem)] flex flex-col justify-center py-20 md:py-24">
            <!-- Ambient Glows -->
            <div class="absolute top-0 right-0 -z-10 h-[600px] w-[600px] rounded-full bg-emerald-50/20 blur-3xl dark:bg-emerald-950/5"></div>
            <div class="absolute -top-40 -left-40 -z-10 h-[600px] w-[600px] rounded-full bg-emerald-50/10 blur-3xl dark:bg-emerald-950/5"></div>

            <div class="mx-auto max-w-7xl px-6 lg:pl-8 lg:pr-0 w-full">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">
                    <!-- Left: Hero Text -->
                    <div class="lg:col-span-6 text-center lg:text-left flex flex-col items-center lg:items-start">
                        <!-- Yellow Badge (matching CTA) -->
                        <Badge variant="outline" class="mb-6 bg-yellow-500/10 border-yellow-500/20 text-amber-600 dark:text-yellow-300 dark:bg-yellow-400/10 dark:border-yellow-400/20 px-3.5 py-1 font-semibold hover:bg-yellow-500/10 hover:text-amber-600 dark:hover:text-yellow-300 gap-1.5">
                            <span class="flex h-1.5 w-1.5 rounded-full bg-amber-500 dark:bg-yellow-300 animate-pulse"></span>
                            Platform Pengelolaan Magang Mahasiswa
                        </Badge>

                        <!-- Headline -->
                        <h1 class="text-4xl font-extrabold tracking-tight text-foreground sm:text-5xl lg:text-6xl leading-[1.15]">
                            Kelola Pengajuan Magang Lebih <span class="text-primary">Mudah</span> dan <span class="text-primary">Terstruktur</span>
                        </h1>

                        <!-- Subheadline -->
                        <p class="mt-6 text-base text-muted-foreground sm:text-lg lg:text-xl leading-relaxed">
                            MagangHub membantu mahasiswa, kelompok magang, dan administrator kampus mengelola proses pengajuan, monitoring, dan pelaporan magang dalam satu platform terpadu.
                        </p>

                        <!-- CTA Buttons -->
                        <div class="mt-10 flex flex-wrap items-center justify-center lg:justify-start gap-4">
                            <Button as-child size="lg" class="h-12 px-7 text-base font-medium shadow-sm hover:scale-[1.01] active:scale-[0.99] transition-all duration-150">
                                <Link 
                                    :href="user 
                                        ? role === 'student' ? home() : dashboard() 
                                        : login()"
                                >
                                    Masuk ke 
                                    {{ user 
                                        ? role === 'student' ? 'Beranda' : 'Dashboard' 
                                        : 'Sistem' 
                                    }}
                                    <ArrowRight class="ml-2 h-4 w-4" />
                                </Link>
                            </Button>
                            <Button as-child variant="outline" size="lg" class="h-12 px-7 text-base font-medium transition-all duration-150">
                                <a href="#features">
                                    Pelajari Fitur
                                </a>
                            </Button>
                        </div>
                    </div>

                    <!-- Right: Mockup Dashboard -->
                    <div class="lg:col-span-6 relative w-full flex justify-center lg:justify-start lg:translate-x-[15%] xl:translate-x-[25%] lg:w-[135%] shrink-0 select-none pt-3">
                        <div class="relative w-full max-w-[540px] lg:max-w-none rounded-2xl border-[6px] border-emerald-500/20 bg-emerald-500/5 p-1.5 shadow-2xl dark:border-emerald-400/10 dark:bg-emerald-400/5">
                            <div class="overflow-hidden rounded-xl border-2 border-emerald-500/80 bg-card shadow-lg dark:border-emerald-500/80">
                                <!-- Window Header -->
                                <div class="flex items-center gap-1.5 border-b border-emerald-500/20 bg-muted/60 px-4 py-2.5 dark:border-emerald-500/20">
                                    <div class="h-2.5 w-2.5 rounded-full bg-red-400/80"></div>
                                    <div class="h-2.5 w-2.5 rounded-full bg-yellow-400/80"></div>
                                    <div class="h-2.5 w-2.5 rounded-full bg-green-400/80"></div>
                                    <div class="ml-4 h-4 w-36 rounded bg-muted/80 text-[10px] text-muted-foreground flex items-center justify-center">maganghub.csunimed.com</div>
                                </div>
                                <!-- Image Content -->
                                <div class="relative bg-background">
                                    <img 
                                        src="/assets/images/hero-student-home-preview.png" 
                                        alt="Mockup Dashboard Mahasiswa" 
                                        class="w-full h-auto object-cover object-top hover:scale-[1.01] transition-transform duration-500"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Scrolling Logo Cloud -->
                <div class="mt-24 border-t border-border/55 pt-12 lg:pr-8 text-left w-full">
                    <p class="text-xs font-semibold tracking-wider text-muted-foreground uppercase mb-6 text-left">
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
        <section class="relative overflow-hidden bg-emerald-50/20 dark:bg-emerald-950/5 py-20 md:py-24 border-y border-emerald-100/30 dark:border-emerald-950/20">
            <!-- Decorative light blob -->
            <div class="absolute -right-20 -bottom-20 -z-10 h-72 w-72 rounded-full bg-emerald-100/30 blur-3xl dark:bg-emerald-900/5"></div>
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="grid grid-cols-1 gap-12 lg:grid-cols-2 lg:gap-16 items-center">
                    <!-- Left: Why Internships Matter -->
                    <div>
                        <Badge variant="outline" class="mb-4 bg-yellow-500/10 border-yellow-500/20 text-amber-600 dark:text-yellow-300 dark:bg-yellow-400/10 dark:border-yellow-400/20 px-3.5 py-1 font-semibold hover:bg-yellow-500/10 hover:text-amber-600 dark:hover:text-yellow-300">
                            Dampak & Relevansi
                        </Badge>
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
                    <Badge variant="outline" class="mb-3 bg-yellow-500/10 border-yellow-500/20 text-amber-600 dark:text-yellow-300 dark:bg-yellow-400/10 dark:border-yellow-400/20 px-3.5 py-1 font-semibold hover:bg-yellow-500/10 hover:text-amber-600 dark:hover:text-yellow-300">
                        Fitur Utama
                    </Badge>
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
        <section id="workflow" class="relative overflow-hidden bg-zinc-50/70 dark:bg-zinc-900/10 py-20 md:py-24 border-y border-border/20">
            <!-- Decorative light blob -->
            <div class="absolute -left-20 -bottom-20 -z-10 h-72 w-72 rounded-full bg-zinc-200/30 blur-3xl dark:bg-zinc-800/10"></div>
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <!-- Section Header -->
                <div class="mx-auto max-w-3xl text-center mb-16">
                    <Badge variant="outline" class="mb-3 bg-yellow-500/10 border-yellow-500/20 text-amber-600 dark:text-yellow-300 dark:bg-yellow-400/10 dark:border-yellow-400/20 px-3.5 py-1 font-semibold hover:bg-yellow-500/10 hover:text-amber-600 dark:hover:text-yellow-300">
                        Alur Kerja
                    </Badge>
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

        <!-- Statistics Section -->
        <section class="relative overflow-hidden bg-gradient-to-br from-emerald-600 via-emerald-700 to-teal-800 py-16 md:py-20 text-white dark:from-emerald-900 dark:via-emerald-950 dark:to-teal-950 border-y border-emerald-500/25">
            <!-- Decorative vector patterns inside statistics section for texture -->
            <div class="absolute inset-0 opacity-10 pointer-events-none mix-blend-overlay">
                <svg viewBox="0 0 100 100" class="w-full h-full fill-current">
                    <circle cx="20" cy="20" r="30" />
                    <circle cx="80" cy="80" r="40" />
                </svg>
            </div>

            <div class="mx-auto max-w-7xl px-6 lg:px-8 relative z-10">
                <div class="grid grid-cols-2 gap-y-12 gap-x-8 md:grid-cols-4 text-center divide-x-0 md:divide-x md:divide-white/10">
                    <div v-for="stat in stats" :key="stat.label" class="flex flex-col items-center px-4">
                        <div class="text-5xl font-black tracking-tight text-yellow-300 drop-shadow-md sm:text-6xl">
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
                        <div class="mt-3 text-xs font-bold uppercase tracking-wider text-emerald-100/90">
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
                    <Badge variant="outline" class="mb-3 bg-yellow-500/10 border-yellow-500/20 text-amber-600 dark:text-yellow-300 dark:bg-yellow-400/10 dark:border-yellow-400/20 px-3.5 py-1 font-semibold hover:bg-yellow-500/10 hover:text-amber-600 dark:hover:text-yellow-300">
                        Tanya Jawab
                    </Badge>
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
            <div class="relative overflow-hidden bg-gradient-to-br from-emerald-600 via-emerald-700 to-teal-800 px-8 py-16 text-center text-primary-foreground rounded-2xl shadow-xl dark:from-emerald-900 dark:via-emerald-950 dark:to-teal-950 border border-emerald-500/20">
                <!-- Soft yellow radial glow accents (Unimed identity) -->
                <div class="absolute -top-24 -right-24 h-48 w-48 rounded-full bg-yellow-300/20 blur-3xl pointer-events-none"></div>
                <div class="absolute -bottom-20 -left-20 h-40 w-40 rounded-full bg-yellow-300/10 blur-3xl pointer-events-none"></div>

                <!-- Vector shapes background -->
                <div class="absolute inset-0 opacity-10 pointer-events-none mix-blend-overlay">
                    <svg viewBox="0 0 100 100" class="w-full h-full fill-current">
                        <circle cx="10" cy="10" r="30" />
                        <circle cx="90" cy="90" r="40" />
                    </svg>
                </div>
                
                <div class="relative z-10 max-w-2xl mx-auto flex flex-col items-center">
                    <h2 class="text-3xl font-bold tracking-tight sm:text-4xl">
                        Siap Memulai Magang?
                    </h2>
                    <p class="mt-4 text-base text-emerald-50/90 leading-relaxed">
                        Kelola seluruh proses administrasi magang kampus Anda dalam satu platform yang terintegrasi dan terpantau dengan baik.
                    </p>
                    <div class="mt-8 flex justify-center">
                        <Button as-child size="xl" class="bg-white text-emerald-800 hover:bg-yellow-50 dark:bg-zinc-100 dark:text-emerald-950 dark:hover:bg-yellow-100 font-semibold shadow-md transition-all hover:scale-[1.02] active:scale-[0.98]">
                            <Link 
                                :href="user 
                                    ? role === 'student' ? home() : dashboard() 
                                    : login()"
                            >
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
                        <AppLogo keepLight />
                        <p class="text-xs text-zinc-400 leading-relaxed">
                            Sistem pengelolaan magang digital untuk menghubungkan mahasiswa, perguruan tinggi, dan industri secara efisien dan transparan.
                        </p>
                    </div>

                    <!-- Column 2: Panduan -->
                    <div>
                        <h4 class="text-xs font-bold tracking-wider text-zinc-100 uppercase mb-4">Panduan Pengguna</h4>
                        <ul class="space-y-2 text-xs">
                            <li><a href="#" class="hover:text-white transition-colors duration-150">Panduan Mahasiswa</a></li>
                            <li><a href="#" class="hover:text-white transition-colors duration-150">Panduan Operator</a></li>
                            <li><a href="#" class="hover:text-white transition-colors duration-150">Panduan Administrator</a></li>
                        </ul>
                    </div>

                    <!-- Column 3: Kontak -->
                    <div>
                        <h4 class="text-xs font-bold tracking-wider text-zinc-100 uppercase mb-4">Kontak Kampus</h4>
                        <ul class="space-y-2 text-xs text-zinc-400">
                            <li>Jl. William Iskandar Ps. V</li>
                            <li>Deli Serdang, Sumatera Utara</li>
                            <li>Email: support@csunimed.ac.id</li>
                        </ul>
                    </div>

                    <!-- Column 4: Sosial Media -->
                    <div>
                        <h4 class="text-xs font-bold tracking-wider text-zinc-100 uppercase mb-4">Sosial Media</h4>
                        <ul class="space-y-2 text-xs">
                            <li><a href="https://www.instagram.com/ilmukomputerunimed/" target="_blank" class="hover:text-white transition-colors duration-150">Instagram</a></li>
                        </ul>
                    </div>
                </div>
                
                <div class="border-t border-zinc-900 pt-8 flex flex-col sm:flex-row items-center justify-between text-center gap-4">
                    <p class="text-[11px] text-zinc-500">
                        &copy; 2026 MagangHub. Hak Cipta Dilindungi Undang-Undang.
                    </p>
                    <p class="text-[11px] text-zinc-500">
                        Universitas Negeri Medan
                    </p>
                </div>
            </div>
        </footer>
    </div>
</template>