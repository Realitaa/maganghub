<script setup lang="ts">
import { router, Link, usePage } from '@inertiajs/vue3';
import {
    Lock,
    CheckCircle2,
    XCircle,
    Clock,
    UserX,
    Keyboard,
    Handshake,
    ChevronLeft,
    ChevronRight,
    ShieldAlert,
    Link as LinkIcon,
    Hourglass,
} from '@lucide/vue';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Carousel,
    CarouselContent,
    CarouselItem
    
} from '@/components/ui/carousel';
import type {CarouselApi} from '@/components/ui/carousel';
import { Spinner } from '@/components/ui/spinner';
import { store as groupStore, join as groupJoin } from '@/routes/groups';
import { cancel as cancelRequest } from '@/routes/groups/join-requests';
import { edit as editProfile } from '@/routes/profile';
import { edit as editSecurity } from '@/routes/security';
import type { PendingJoinRequest } from '@/types';

defineProps<{
    isLocked: boolean;
    pendingJoinRequests: PendingJoinRequest[];
}>();

const joinCode = ref('');
const isProcessing = ref(false);
const page = usePage();

// Carousel slides
const slides = [
    {
        title: "Dapatkan link yang bisa Anda bagikan",
        description: "Klik Kelompok Baru untuk membuat kelompok, lalu bagikan kode atau link unik kepada calon anggota kelompok Anda."
    },
    {
        title: "Gabung ke kelompok teman Anda",
        description: "Masukkan kode kelompok yang dibagikan oleh ketua kelompok Anda untuk mengirim permintaan bergabung secara instan."
    },
    {
        title: "Tunggu persetujuan ketua kelompok",
        description: "Setelah mengirim permintaan bergabung, Anda hanya perlu menunggu ketua kelompok menyetujui permintaan Anda."
    }
];

const emblaApi = ref<CarouselApi>();
const currentSlide = ref(0);
const canScrollPrev = ref(false);
const canScrollNext = ref(true);
const showCarouselAnyway = ref(false);

function setApi(val: CarouselApi) {
    if (!val) { 
        return;
    }

    emblaApi.value = val;
    
    currentSlide.value = val.selectedScrollSnap();
    canScrollPrev.value = val.canScrollPrev();
    canScrollNext.value = val.canScrollNext();

    val.on('select', () => {
        currentSlide.value = val.selectedScrollSnap();
        canScrollPrev.value = val.canScrollPrev();
        canScrollNext.value = val.canScrollNext();
    });
}

function createGroup() {
    isProcessing.value = true;
    router.post(
        groupStore.url(),
        {},
        {
            onFinish: () => {
                isProcessing.value = false;
            },
        },
    );
}

function sendJoinRequest() {
    if (!joinCode.value.trim()) {
        return;
    }

    isProcessing.value = true;
    router.post(
        groupJoin.url(),
        { code: joinCode.value.trim().toUpperCase() },
        {
            onFinish: () => {
                isProcessing.value = false;
                joinCode.value = '';
            },
        },
    );
}

function cancelJoinRequest(requestId: number) {
    router.delete(cancelRequest.url(requestId));
}
</script>

<template>
    <div class="h-full flex items-center justify-center px-4 py-8 md:py-16 bg-background">
        <div class="max-w-7xl w-full mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-center">
                
                <!-- LEFT COLUMN: Heading & Actions (Col-span 7 on large screens) -->
                <div class="lg:col-span-7 flex flex-col justify-center space-y-8 text-center lg:text-left">
                    
                    <!-- Heading Section -->
                    <div class="space-y-4">
                        <h1 class="text-3xl sm:text-4xl md:text-5xl font-normal tracking-tight text-foreground leading-tight font-sans">
                            <template v-if="isLocked">
                                Akses kelompok magang terkunci
                            </template>
                            <template v-else>
                                Kolaborasi kelompok magang mahasiswa
                            </template>
                        </h1>
                        <p class="text-base sm:text-lg md:text-xl text-muted-foreground font-light leading-relaxed max-w-2xl mx-auto lg:mx-0">
                            <template v-if="isLocked">
                                Lengkapi persyaratan keamanan dan biodata mahasiswa untuk dapat membuat atau bergabung dengan kelompok magang.
                            </template>
                            <template v-else>
                                Terhubung, berkolaborasi, dan selesaikan administrasi magang dari mana saja dengan sistem kelompok MagangHub.
                            </template>
                        </p>
                    </div>

                    <!-- Actions Section -->
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-center lg:justify-start gap-4 pt-2">
                        <template v-if="isLocked">
                            <!-- Actions for Locked State -->
                            <Link
                                v-if="!page.props.auth?.requirements?.password_changed"
                                :href="editSecurity()"
                                class="w-full sm:w-auto"
                            >
                                <Button
                                    id="btn-lock-password"
                                    variant="destructive"
                                    size="xl"
                                    class="w-full sm:w-auto bg-destructive hover:bg-destructive/90 text-white font-medium px-6 rounded-full flex items-center justify-center gap-2.5 transition-all shadow-xs cursor-pointer"
                                >
                                    <Lock class="h-5 w-5" />
                                    Ubah Password Default
                                </Button>
                            </Link>
                            
                            <Link
                                v-if="!page.props.auth?.requirements?.profile_completed"
                                :href="editProfile()"
                                class="w-full sm:w-auto"
                            >
                                <Button
                                    id="btn-lock-profile"
                                    variant="outline"
                                    size="xl"
                                    class="w-full sm:w-auto border border-primary/30 text-primary hover:bg-primary/5 font-medium px-6 rounded-full flex items-center justify-center gap-2.5 transition-all shadow-xs cursor-pointer"
                                >
                                    Lengkapi Biodata
                                </Button>
                            </Link>
                        </template>
                        
                        <template v-else>
                            <!-- Actions for Unlocked State -->
                            <!-- New Group Button -->
                            <Button
                                id="btn-create-group"
                                size="xl"
                                class="w-full sm:w-auto bg-primary hover:bg-primary/95 text-primary-foreground font-semibold rounded-full flex items-center justify-center gap-2.5 transition-all shadow-xs cursor-pointer"
                                @click="createGroup"
                                :disabled="isProcessing"
                            >
                                <Spinner v-if="isProcessing" class="mr-1 h-5 w-5 animate-spin" />
                                <Handshake v-else class="mr-1 h-5 w-5" />
                                Kelompok Baru
                            </Button>

                            <!-- Join Group Input Group -->
                            <div class="flex items-center gap-3 w-full sm:w-auto sm:max-w-md flex-1">
                                <div class="relative flex-1 flex items-center border border-input bg-background rounded-full focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 h-12 px-4 transition-all duration-200">
                                    <Keyboard class="h-5 w-5 text-muted-foreground mr-2.5 shrink-0" />
                                    <input
                                        id="join-code"
                                        v-model="joinCode"
                                        type="text"
                                        placeholder="Masukkan kode kelompok"
                                        class="w-full bg-transparent border-0 p-0 text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-0 text-sm tracking-wider font-mono uppercase"
                                        maxlength="10"
                                        autocomplete="off"
                                        @keydown.enter="sendJoinRequest"
                                    />
                                </div>
                                <button
                                    id="btn-join-group"
                                    class="text-primary hover:text-primary/80 disabled:text-muted-foreground/50 disabled:cursor-not-allowed font-semibold text-sm px-4 py-2 transition-colors cursor-pointer shrink-0"
                                    @click="sendJoinRequest"
                                    :disabled="!joinCode.trim() || isProcessing"
                                >
                                    <Spinner v-if="isProcessing" class="h-4 w-4 animate-spin" />
                                    <span v-else>Gabung</span>
                                </button>
                            </div>
                        </template>
                    </div>

                    <!-- Divider Line -->
                    <hr class="border-border/60 my-6" />

                    <!-- Help/Tip Section -->
                    <div class="text-sm text-muted-foreground font-light">
                        <template v-if="isLocked">
                            <span class="flex items-center justify-center lg:justify-start gap-1.5 text-amber-600 dark:text-amber-400">
                                <ShieldAlert class="h-4 w-4 shrink-0 animate-pulse" />
                                Fitur dinonaktifkan demi keamanan akun Anda. Silakan selesaikan langkah penyelesaian.
                            </span>
                        </template>
                        <template v-else>
                            <div class="flex flex-wrap items-center justify-center lg:justify-start gap-1.5">
                                <a href="#" @click.prevent="showCarouselAnyway = true" class="text-primary hover:underline font-normal inline-flex items-center gap-1 cursor-pointer">
                                    Pelajari cara bergabung
                                </a>
                                <span>atau diskusikan dengan teman sekelompok Anda untuk pembagian kode.</span>
                            </div>
                        </template>
                    </div>

                </div>

                <!-- RIGHT COLUMN: Visuals / Illustrations / Status (Col-span 5 on large screens) -->
                <div class="lg:col-span-5 flex flex-col items-center justify-center w-full">
                    
                    <!-- Case 1: Locked State (Requirements Checklist) -->
                    <template v-if="isLocked">
                        <div class="w-full max-w-md bg-muted/20 dark:bg-muted/5 rounded-3xl border border-border/80 p-8 shadow-xs flex flex-col items-center">
                            
                            <!-- Locked State Visual (Shield/Lock SVG) -->
                            <div class="w-36 h-36 rounded-full bg-destructive/5 flex items-center justify-center mb-6">
                                <svg viewBox="0 -25 240 240" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-36 h-36">
                                    <path d="M120 50c30 0 50-15 50-15v50c0 40-20 70-50 85-30-15-50-45-50-85V35s20 15 50 15z" class="fill-card stroke-destructive/40 dark:fill-card" stroke-width="3" />
                                    <rect x="100" y="95" width="40" height="30" rx="6" class="fill-destructive" />
                                    <path d="M108 95V83a12 12 0 1124 0v12" class="stroke-destructive" stroke-width="4" stroke-linecap="round" />
                                    <circle cx="120" cy="107" r="3" fill="#fff" />
                                    <path d="M120 110v6" stroke="#fff" stroke-width="2" stroke-linecap="round" />
                                </svg>
                            </div>

                            <h2 class="text-xl font-medium text-foreground mb-4">Langkah Penyelesaian</h2>
                            
                            <div class="w-full space-y-4">
                                <!-- Req 1: Password Changed -->
                                <div class="flex items-start gap-3.5 p-4 rounded-2xl border bg-card/50 transition-all duration-200" :class="page.props.auth?.requirements?.password_changed ? 'border-green-500/20 dark:border-green-500/10' : 'border-destructive/20 dark:border-destructive/10'">
                                    <div class="mt-0.5">
                                        <CheckCircle2 v-if="page.props.auth?.requirements?.password_changed" class="h-5 w-5 text-green-500" />
                                        <XCircle v-else class="h-5 w-5 text-destructive" />
                                    </div>
                                    <div class="space-y-1">
                                        <p class="text-sm font-medium" :class="page.props.auth?.requirements?.password_changed ? 'text-muted-foreground' : 'text-foreground'">
                                            Mengubah password default
                                        </p>
                                        <p class="text-xs text-muted-foreground leading-normal">
                                            Password default wajib diubah demi keamanan privasi dan data kelompok Anda.
                                        </p>
                                    </div>
                                </div>

                                <!-- Req 2: Profile Completed -->
                                <div class="flex items-start gap-3.5 p-4 rounded-2xl border bg-card/50 transition-all duration-200" :class="page.props.auth?.requirements?.profile_completed ? 'border-green-500/20 dark:border-green-500/10' : 'border-destructive/20 dark:border-destructive/10'">
                                    <div class="mt-0.5">
                                        <CheckCircle2 v-if="page.props.auth?.requirements?.profile_completed" class="h-5 w-5 text-green-500" />
                                        <XCircle v-else class="h-5 w-5 text-destructive" />
                                    </div>
                                    <div class="space-y-1">
                                        <p class="text-sm font-medium" :class="page.props.auth?.requirements?.profile_completed ? 'text-muted-foreground' : 'text-foreground'">
                                            Melengkapi data biodata mahasiswa
                                        </p>
                                        <p class="text-xs text-muted-foreground leading-normal">
                                            Lengkapi biodata mahasiswa (NIM, gender, semester, dll.) secara valid sebelum melanjutkan.
                                        </p>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </template>

                    <!-- Case 2: Unlocked State with pending request (Show Pending Requests panel) -->
                    <template v-else-if="pendingJoinRequests.length > 0 && !showCarouselAnyway">
                        <div class="w-full max-w-md bg-muted/20 dark:bg-muted/5 rounded-3xl border border-border/80 p-8 shadow-xs flex flex-col items-center">
                            
                            <!-- Pending Request Visual (Clock SVG) -->
                            <div class="w-48 h-48 rounded-full bg-amber-500/5 flex items-center justify-center mb-6">
                                <svg viewBox="0 0 240 240" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-40 h-40">
                                    <circle cx="120" cy="115" r="45" class="fill-card stroke-amber-200 dark:stroke-amber-900/60 dark:fill-card" stroke-width="3" />
                                    <path d="M120 85v30l15 10" class="stroke-amber-500" stroke-width="4" stroke-linecap="round" />
                                    <circle cx="65" cy="140" r="16" class="fill-amber-100 dark:fill-amber-950/40" />
                                    <path d="M55 152a12 12 0 0120 0" class="fill-amber-300 dark:fill-amber-800" />
                                    <circle cx="175" cy="140" r="16" class="fill-amber-100 dark:fill-amber-950/40" />
                                    <path d="M165 152a12 12 0 0120 0" class="fill-amber-300 dark:fill-amber-800" />
                                    <circle cx="120" cy="65" r="15" class="fill-amber-500" />
                                    <foreignObject x="111" y="56" width="18" height="18">
                                        <Hourglass class="h-4.5 w-4.5 text-white" />
                                    </foreignObject>
                                </svg>
                            </div>

                            <h2 class="text-xl font-medium text-foreground mb-1 text-center">Permintaan Bergabung Terkirim</h2>
                            <p class="text-sm text-muted-foreground text-center mb-6 max-w-xs font-light leading-relaxed">
                                Permintaan Anda sedang menunggu persetujuan dari ketua kelompok.
                            </p>
                            
                            <div class="w-full space-y-3">
                                <div
                                    v-for="req in pendingJoinRequests"
                                    :key="req.id"
                                    class="flex flex-col gap-4 rounded-2xl border border-border bg-card p-4 sm:flex-row sm:items-center sm:justify-between shadow-xs"
                                >
                                    <div class="flex items-center gap-3">
                                        <div class="rounded-lg bg-amber-500/10 p-2">
                                            <Clock
                                                class="h-5 w-5 text-amber-600 dark:text-amber-400 animate-pulse"
                                            />
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-foreground">
                                                Kelompok
                                                <span class="font-mono text-primary font-bold">{{ req.group.code }}</span>
                                            </p>
                                            <p class="text-xs text-muted-foreground mt-0.5">
                                                Ketua: {{ req.group.leader.name }}
                                            </p>
                                        </div>
                                    </div>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        class="justify-start text-destructive hover:bg-destructive/10 hover:text-destructive sm:justify-center rounded-full font-medium"
                                        @click="cancelJoinRequest(req.id)"
                                    >
                                        <UserX class="mr-1.5 h-4 w-4" />
                                        Batalkan
                                    </Button>
                                </div>
                            </div>

                            <!-- Back to Guide Link -->
                            <button @click="showCarouselAnyway = true" class="mt-6 text-sm text-primary hover:underline font-normal cursor-pointer bg-transparent border-0 outline-none">
                                Lihat panduan kelompok
                            </button>

                        </div>
                    </template>

                    <!-- Case 3: Unlocked State (Interactive Carousel) -->
                    <template v-else>
                        <div class="w-full max-w-md flex flex-col items-center">
                            
                            <!-- Carousel Main Area -->
                            <div class="flex items-center justify-between w-full gap-4">
                                
                                <!-- Left Nav Arrow -->
                                <button
                                    @click="emblaApi?.scrollPrev()"
                                    :disabled="!canScrollPrev"
                                    class="w-10 h-10 rounded-full border border-border bg-background flex items-center justify-center hover:bg-muted text-muted-foreground hover:text-foreground disabled:opacity-40 disabled:cursor-not-allowed transition-all cursor-pointer shrink-0 shadow-xs outline-none"
                                    aria-label="Slide sebelumnya"
                                >
                                    <ChevronLeft class="h-5 w-5" />
                                </button>
                                
                                <!-- Carousel with only the content (no buttons inside) -->
                                <Carousel @init-api="setApi" :opts="{ loop: true }" class="w-full max-w-[280px] sm:max-w-xs overflow-hidden">
                                    <CarouselContent>
                                        <CarouselItem v-for="(slide, index) in slides" :key="index">
                                            <div class="flex flex-col items-center select-none">
                                                
                                                <!-- Circle Illustration -->
                                                <div class="relative w-56 h-56 sm:w-64 sm:h-64 rounded-full bg-slate-50 dark:bg-slate-900/30 flex items-center justify-center overflow-hidden border border-border/10">
                                                    
                                                    <!-- Slide 1: Share Link -->
                                                    <svg v-if="index === 0" viewBox="0 0 240 240" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full p-4">
                                                        <!-- Left Webcam Feed -->
                                                        <rect x="25" y="70" width="85" height="90" rx="12" class="fill-card stroke-border" stroke-width="1.5" />
                                                        <g>
                                                            <!-- Left Person (Yellow/Amber shirt, portrait view) -->
                                                            <path d="M45 160v-14c0-8 6-14 14-14h17c8 0 14 6 14 14v14H45z" class="fill-amber-500" />
                                                            <circle cx="67.5" cy="114" r="13" class="fill-orange-200 dark:fill-orange-300" />
                                                            <path d="M54.5 110c0-10 6-13 13-13s13 3 13 13c0 3-3 4-13 4s-13-1-13-4z" class="fill-amber-900" />
                                                            <!-- Active Indicator (e.g. green status dot) -->
                                                            <circle cx="98" cy="82" r="4" class="fill-green-500" />
                                                        </g>

                                                        <!-- Right Webcam Feed -->
                                                        <rect x="130" y="70" width="85" height="90" rx="12" class="fill-card stroke-border" stroke-width="1.5" />
                                                        <g>
                                                            <!-- Right Person (Green shirt, portrait view) -->
                                                            <path d="M150 160v-14c0-8 6-14 14-14h17c8 0 14 6 14 14v14h-45z" class="fill-emerald-600" />
                                                            <circle cx="172.5" cy="114" r="13" class="fill-yellow-200 dark:fill-yellow-300" />
                                                            <path d="M159.5 110c0-10 6-13 13-13s13 3 13 13c0 3-3 4-13 4s-13-1-13-4z" class="fill-slate-900" />
                                                            <!-- Active Indicator -->
                                                            <circle cx="203" cy="82" r="4" class="fill-green-500" />
                                                        </g>
                                                        
                                                        <!-- Link Bubble (overlapping the feeds in the center) -->
                                                        <g>
                                                            <circle cx="120" cy="115" r="20" class="fill-primary" />
                                                            <foreignObject x="108" y="103" width="24" height="24">
                                                                <LinkIcon class="h-6 w-6 text-white" />
                                                            </foreignObject>
                                                        </g>
                                                    </svg>

                                                    <!-- Slide 2: Join Group -->
                                                    <svg v-else-if="index === 1" viewBox="0 0 240 240" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full p-4">
                                                        <rect x="50" y="75" width="140" height="90" rx="12" class="fill-card stroke-primary/30 dark:stroke-primary/50" stroke-width="2" />
                                                        <rect x="62" y="90" width="50" height="8" rx="4" class="fill-primary/20 dark:fill-primary/30" />
                                                        <circle cx="170" cy="94" r="5" class="fill-primary" />
                                                        <rect x="62" y="118" width="22" height="26" rx="4" class="fill-muted stroke-border" stroke-width="1.5" />
                                                        <rect x="90" y="118" width="22" height="26" rx="4" class="fill-muted stroke-border" stroke-width="1.5" />
                                                        <rect x="118" y="118" width="22" height="26" rx="4" class="fill-muted stroke-border" stroke-width="1.5" />
                                                        <rect x="146" y="118" width="22" height="26" rx="4" class="fill-muted stroke-border" stroke-width="1.5" />
                                                        <path d="M70 131h6" class="stroke-muted-foreground" stroke-width="2" stroke-linecap="round" />
                                                        <path d="M98 131h6" class="stroke-muted-foreground" stroke-width="2" stroke-linecap="round" />
                                                        <path d="M126 131h6" class="stroke-muted-foreground" stroke-width="2" stroke-linecap="round" />
                                                        <path d="M154 131h6" class="stroke-muted-foreground" stroke-width="2" stroke-linecap="round" />
                                                    </svg>

                                                    <!-- Slide 3: Approval -->
                                                    <svg v-else-if="index === 2" viewBox="0 0 240 240" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full p-4">
                                                        <circle cx="120" cy="115" r="42" class="fill-card stroke-primary/30" stroke-width="2" />
                                                        <path d="M120 85v30l12 12" class="stroke-primary" stroke-width="4" stroke-linecap="round" />
                                                        <circle cx="60" cy="135" r="14" class="fill-primary/10 dark:fill-primary/20" />
                                                        <path d="M50 146a10 10 0 0120 0" class="fill-primary/40 dark:fill-primary/50" />
                                                        <circle cx="180" cy="135" r="14" class="fill-primary/10 dark:fill-primary/20" />
                                                        <path d="M170 146a10 10 0 0120 0" class="fill-primary/40 dark:fill-primary/50" />
                                                        <circle cx="120" cy="65" r="14" class="fill-primary" />
                                                        <!-- Check inside circle -->
                                                        <path d="M115 65l3 3 7-7" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                                                    </svg>

                                                </div>
                                                
                                                <!-- Title & Description (below illustration) -->
                                                <div class="text-center mt-6 min-h-[90px] flex flex-col justify-start">
                                                    <h3 class="text-lg md:text-xl font-medium text-foreground tracking-tight">
                                                        {{ slide.title }}
                                                    </h3>
                                                    <p class="text-sm text-muted-foreground mt-2 max-w-xs font-light leading-relaxed">
                                                        {{ slide.description }}
                                                    </p>
                                                </div>

                                            </div>
                                        </CarouselItem>
                                    </CarouselContent>
                                </Carousel>

                                <!-- Right Nav Arrow -->
                                <button
                                    @click="emblaApi?.scrollNext()"
                                    :disabled="!canScrollNext"
                                    class="w-10 h-10 rounded-full border border-border bg-background flex items-center justify-center hover:bg-muted text-muted-foreground hover:text-foreground disabled:opacity-40 disabled:cursor-not-allowed transition-all cursor-pointer shrink-0 shadow-xs outline-none"
                                    aria-label="Slide berikutnya"
                                >
                                    <ChevronRight class="h-5 w-5" />
                                </button>

                            </div>

                            <!-- Carousel Dots -->
                            <div class="flex justify-center gap-2 mt-4">
                                <span
                                    v-for="(_, index) in slides"
                                    :key="index"
                                    @click="emblaApi?.scrollTo(index)"
                                    class="h-2 rounded-full transition-all duration-300 cursor-pointer"
                                    :class="currentSlide === index ? 'w-5 bg-primary' : 'w-2 bg-muted-foreground/30 hover:bg-muted-foreground/50'"
                                ></span>
                            </div>

                            <!-- Show Pending request button if it's hidden by override -->
                            <button
                                v-if="pendingJoinRequests.length > 0"
                                @click="showCarouselAnyway = false"
                                class="mt-6 text-sm text-primary hover:underline font-normal cursor-pointer bg-transparent border-0 outline-none"
                            >
                                Lihat status permintaan bergabung
                            </button>

                        </div>
                    </template>

                </div>

            </div>
        </div>
    </div>
</template>
