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
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Carousel,
    CarouselContent,
    CarouselItem,
} from '@/components/ui/carousel';
import type { CarouselApi } from '@/components/ui/carousel';
import { Spinner } from '@/components/ui/spinner';
import { store as groupStore, join as groupJoin } from '@/routes/groups';
import { cancel as cancelRequest } from '@/routes/groups/join-requests';
import { edit as editProfile } from '@/routes/profile';
import { edit as editSecurity } from '@/routes/security';
import type { PendingJoinRequest } from '@/types';

defineProps<{
    isLocked: boolean;
    pendingJoinRequests: PendingJoinRequest[];
    isLeaderInAnyGroup?: boolean;
}>();

const joinCode = ref('');
const isProcessing = ref(false);
const page = usePage();
const requirements = computed(() => (page.props.auth as any)?.requirements);

const containerHeight = computed(() => {
    let subtraction = 0;

    if (!requirements.value) {
        return;
    }

    let hasAlert = false;
    let alertCount = 0;

    if (!requirements.value.password_changed) {
        subtraction += 70; // Approximate height of password alert
        hasAlert = true;
        alertCount++;
    }

    if (!requirements.value.profile_completed) {
        subtraction += 70; // Approximate height of profile alert
        hasAlert = true;
        alertCount++;
    }

    if (hasAlert) {
        subtraction += 8; // Top padding (pt-2 = 8px)
    }

    if (alertCount > 1) {
        subtraction += 12; // Gap between alerts (space-y-3 = 12px)
    }

    return subtraction > 0
        ? `calc(100vh - 5rem - ${subtraction}px)`
        : 'calc(100vh - 5rem)';
});

// Carousel slides
const slides = [
    {
        title: 'Dapatkan link yang bisa Anda bagikan',
        description:
            'Klik Kelompok Baru untuk membuat kelompok, lalu bagikan kode atau link unik kepada calon anggota kelompok Anda.',
    },
    {
        title: 'Gabung ke kelompok teman Anda',
        description:
            'Masukkan kode kelompok yang dibagikan oleh ketua kelompok Anda untuk mengirim permintaan bergabung secara instan.',
    },
    {
        title: 'Tunggu persetujuan ketua kelompok',
        description:
            'Setelah mengirim permintaan bergabung, Anda hanya perlu menunggu ketua kelompok menyetujui permintaan Anda.',
    },
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
    <div
        class="flex min-h-[calc(100vh-5rem)] items-center justify-center bg-background px-4 py-0! lg:min-h-(--container-height)"
        :style="{ '--container-height': containerHeight }"
    >
        <div class="mx-auto w-full max-w-7xl">
            <div
                class="grid grid-cols-1 items-center gap-12 lg:grid-cols-12 lg:gap-16"
            >
                <!-- LEFT COLUMN: Heading & Actions (Col-span 7 on large screens) -->
                <div
                    class="flex flex-col justify-center space-y-8 text-center lg:col-span-7 lg:text-left"
                >
                    <!-- Heading Section -->
                    <div class="space-y-4">
                        <h1
                            class="font-sans text-3xl leading-tight font-normal tracking-tight text-foreground sm:text-4xl md:text-5xl"
                        >
                            <template v-if="isLocked">
                                Akses kelompok magang terkunci
                            </template>
                            <template v-else>
                                Kolaborasi kelompok magang mahasiswa
                            </template>
                        </h1>
                        <p
                            class="mx-auto max-w-2xl text-base leading-relaxed font-light text-muted-foreground sm:text-lg md:text-xl lg:mx-0"
                        >
                            <template v-if="isLocked">
                                Lengkapi persyaratan keamanan dan biodata
                                mahasiswa untuk dapat membuat atau bergabung
                                dengan kelompok magang.
                            </template>
                            <template v-else>
                                Terhubung, berkolaborasi, dan selesaikan
                                administrasi magang dari mana saja dengan sistem
                                kelompok MagangHub.
                            </template>
                        </p>
                    </div>

                    <!-- Actions Section -->
                    <div
                        class="flex flex-col items-stretch justify-center gap-4 pt-2 sm:flex-row sm:items-center lg:justify-start"
                    >
                        <template v-if="isLocked">
                            <!-- Actions for Locked State -->
                            <Link
                                v-if="
                                    !page.props.auth?.requirements
                                        ?.password_changed
                                "
                                :href="editSecurity()"
                                class="w-full sm:w-auto"
                            >
                                <Button
                                    id="btn-lock-password"
                                    variant="destructive"
                                    size="xl"
                                    class="flex w-full cursor-pointer items-center justify-center gap-2.5 rounded-full bg-destructive px-6 font-medium text-white shadow-xs transition-all hover:bg-destructive/90 sm:w-auto"
                                >
                                    <Lock class="h-5 w-5" />
                                    Ubah Password Default
                                </Button>
                            </Link>

                            <Link
                                v-if="
                                    !page.props.auth?.requirements
                                        ?.profile_completed
                                "
                                :href="editProfile()"
                                class="w-full sm:w-auto"
                            >
                                <Button
                                    id="btn-lock-profile"
                                    variant="outline"
                                    size="xl"
                                    class="flex w-full cursor-pointer items-center justify-center gap-2.5 rounded-full border border-primary/30 px-6 font-medium text-primary shadow-xs transition-all hover:bg-primary/5 sm:w-auto"
                                >
                                    Lengkapi Biodata
                                </Button>
                            </Link>
                        </template>

                        <template v-else>
                            <!-- Actions for Unlocked State -->
                            <!-- New Group Button -->
                            <Button
                                v-if="!isLeaderInAnyGroup"
                                id="btn-create-group"
                                size="xl"
                                class="flex w-full cursor-pointer items-center justify-center gap-2.5 rounded-full bg-primary font-semibold text-primary-foreground shadow-xs transition-all hover:bg-primary/95 sm:w-auto"
                                @click="createGroup"
                                :disabled="isProcessing"
                            >
                                <Spinner
                                    v-if="isProcessing"
                                    class="mr-1 h-5 w-5 animate-spin"
                                />
                                <Handshake v-else class="mr-1 h-5 w-5" />
                                Kelompok Baru
                            </Button>

                            <!-- Join Group Input Group -->
                            <div
                                class="flex w-full flex-1 items-center gap-3 sm:w-auto sm:max-w-md"
                            >
                                <div
                                    class="relative flex h-12 flex-1 items-center rounded-full border border-input bg-background px-4 transition-all duration-200 focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20"
                                >
                                    <Keyboard
                                        class="mr-2.5 h-5 w-5 shrink-0 text-muted-foreground"
                                    />
                                    <input
                                        id="join-code"
                                        v-model="joinCode"
                                        type="text"
                                        placeholder="Masukkan kode kelompok"
                                        class="w-full border-0 bg-transparent p-0 font-mono text-sm tracking-wider text-foreground uppercase placeholder:text-muted-foreground focus:ring-0 focus:outline-none"
                                        maxlength="10"
                                        autocomplete="off"
                                        @keydown.enter="sendJoinRequest"
                                    />
                                </div>
                                <button
                                    id="btn-join-group"
                                    class="shrink-0 cursor-pointer px-4 py-2 text-sm font-semibold text-primary transition-colors hover:text-primary/80 disabled:cursor-not-allowed disabled:text-muted-foreground/50"
                                    @click="sendJoinRequest"
                                    :disabled="!joinCode.trim() || isProcessing"
                                >
                                    <Spinner
                                        v-if="isProcessing"
                                        class="h-4 w-4 animate-spin"
                                    />
                                    <span v-else>Gabung</span>
                                </button>
                            </div>
                        </template>
                    </div>

                    <!-- Divider Line -->
                    <hr class="my-6 border-border/60" />

                    <!-- Help/Tip Section -->
                    <div class="text-sm font-light text-muted-foreground">
                        <template v-if="isLocked">
                            <span
                                class="flex items-center justify-center gap-1.5 text-amber-600 lg:justify-start dark:text-amber-400"
                            >
                                <ShieldAlert
                                    class="h-4 w-4 shrink-0 animate-pulse"
                                />
                                Fitur dinonaktifkan demi keamanan akun Anda.
                                Silakan selesaikan langkah penyelesaian.
                            </span>
                        </template>
                        <template v-else>
                            <div
                                class="flex flex-wrap items-center justify-center gap-1.5 lg:justify-start"
                            >
                                <a
                                    href="#"
                                    @click.prevent="showCarouselAnyway = true"
                                    class="inline-flex cursor-pointer items-center gap-1 font-normal text-primary hover:underline"
                                >
                                    Pelajari cara bergabung
                                </a>
                                <span
                                    >atau diskusikan dengan teman sekelompok
                                    Anda untuk pembagian kode.</span
                                >
                            </div>
                        </template>
                    </div>
                </div>

                <!-- RIGHT COLUMN: Visuals / Illustrations / Status (Col-span 5 on large screens) -->
                <div
                    class="flex w-full flex-col items-center justify-center lg:col-span-5 lg:h-(--container-height)"
                >
                    <!-- Custom Right Column Content -->
                    <template v-if="$slots['right-column']">
                        <slot name="right-column" />
                    </template>

                    <!-- Case 1: Locked State (Requirements Checklist) -->
                    <template v-else-if="isLocked">
                        <div
                            class="flex w-full max-w-md flex-col items-center rounded-3xl border border-border/80 bg-muted/20 p-8 shadow-xs dark:bg-muted/5"
                        >
                            <!-- Locked State Visual (Shield/Lock SVG) -->
                            <div
                                class="mb-6 flex h-36 w-36 items-center justify-center rounded-full bg-destructive/5"
                            >
                                <svg
                                    viewBox="0 -25 240 240"
                                    fill="none"
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-36 w-36"
                                >
                                    <path
                                        d="M120 50c30 0 50-15 50-15v50c0 40-20 70-50 85-30-15-50-45-50-85V35s20 15 50 15z"
                                        class="fill-card stroke-destructive/40 dark:fill-card"
                                        stroke-width="3"
                                    />
                                    <rect
                                        x="100"
                                        y="95"
                                        width="40"
                                        height="30"
                                        rx="6"
                                        class="fill-destructive"
                                    />
                                    <path
                                        d="M108 95V83a12 12 0 1124 0v12"
                                        class="stroke-destructive"
                                        stroke-width="4"
                                        stroke-linecap="round"
                                    />
                                    <circle
                                        cx="120"
                                        cy="107"
                                        r="3"
                                        fill="#fff"
                                    />
                                    <path
                                        d="M120 110v6"
                                        stroke="#fff"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                    />
                                </svg>
                            </div>

                            <h2
                                class="mb-4 text-xl font-medium text-foreground"
                            >
                                Langkah Penyelesaian
                            </h2>

                            <div class="w-full space-y-4">
                                <!-- Req 1: Password Changed -->
                                <div
                                    class="flex items-start gap-3.5 rounded-2xl border bg-card/50 p-4 transition-all duration-200"
                                    :class="
                                        page.props.auth?.requirements
                                            ?.password_changed
                                            ? 'border-green-500/20 dark:border-green-500/10'
                                            : 'border-destructive/20 dark:border-destructive/10'
                                    "
                                >
                                    <div class="mt-0.5">
                                        <CheckCircle2
                                            v-if="
                                                page.props.auth?.requirements
                                                    ?.password_changed
                                            "
                                            class="h-5 w-5 text-green-500"
                                        />
                                        <XCircle
                                            v-else
                                            class="h-5 w-5 text-destructive"
                                        />
                                    </div>
                                    <div class="space-y-1">
                                        <p
                                            class="text-sm font-medium"
                                            :class="
                                                page.props.auth?.requirements
                                                    ?.password_changed
                                                    ? 'text-muted-foreground'
                                                    : 'text-foreground'
                                            "
                                        >
                                            Mengubah password default
                                        </p>
                                        <p
                                            class="text-xs leading-normal text-muted-foreground"
                                        >
                                            Password default wajib diubah demi
                                            keamanan privasi dan data kelompok
                                            Anda.
                                        </p>
                                    </div>
                                </div>

                                <!-- Req 2: Profile Completed -->
                                <div
                                    class="flex items-start gap-3.5 rounded-2xl border bg-card/50 p-4 transition-all duration-200"
                                    :class="
                                        page.props.auth?.requirements
                                            ?.profile_completed
                                            ? 'border-green-500/20 dark:border-green-500/10'
                                            : 'border-destructive/20 dark:border-destructive/10'
                                    "
                                >
                                    <div class="mt-0.5">
                                        <CheckCircle2
                                            v-if="
                                                page.props.auth?.requirements
                                                    ?.profile_completed
                                            "
                                            class="h-5 w-5 text-green-500"
                                        />
                                        <XCircle
                                            v-else
                                            class="h-5 w-5 text-destructive"
                                        />
                                    </div>
                                    <div class="space-y-1">
                                        <p
                                            class="text-sm font-medium"
                                            :class="
                                                page.props.auth?.requirements
                                                    ?.profile_completed
                                                    ? 'text-muted-foreground'
                                                    : 'text-foreground'
                                            "
                                        >
                                            Melengkapi data biodata mahasiswa
                                        </p>
                                        <p
                                            class="text-xs leading-normal text-muted-foreground"
                                        >
                                            Lengkapi biodata mahasiswa (NIM,
                                            gender, semester, dll.) secara valid
                                            sebelum melanjutkan.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Case 2: Unlocked State with pending request (Show Pending Requests panel) -->
                    <template
                        v-else-if="
                            pendingJoinRequests.length > 0 &&
                            !showCarouselAnyway
                        "
                    >
                        <div
                            class="flex w-full max-w-md flex-col items-center rounded-3xl border border-border/80 bg-muted/20 p-8 shadow-xs dark:bg-muted/5"
                        >
                            <!-- Pending Request Visual (Clock SVG) -->
                            <div
                                class="mb-6 flex h-48 w-48 items-center justify-center rounded-full bg-amber-500/5"
                            >
                                <svg
                                    viewBox="0 0 240 240"
                                    fill="none"
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-40 w-40"
                                >
                                    <circle
                                        cx="120"
                                        cy="115"
                                        r="45"
                                        class="fill-card stroke-amber-200 dark:fill-card dark:stroke-amber-900/60"
                                        stroke-width="3"
                                    />
                                    <path
                                        d="M120 85v30l15 10"
                                        class="stroke-amber-500"
                                        stroke-width="4"
                                        stroke-linecap="round"
                                    />
                                    <circle
                                        cx="65"
                                        cy="140"
                                        r="16"
                                        class="fill-amber-100 dark:fill-amber-950/40"
                                    />
                                    <path
                                        d="M55 152a12 12 0 0120 0"
                                        class="fill-amber-300 dark:fill-amber-800"
                                    />
                                    <circle
                                        cx="175"
                                        cy="140"
                                        r="16"
                                        class="fill-amber-100 dark:fill-amber-950/40"
                                    />
                                    <path
                                        d="M165 152a12 12 0 0120 0"
                                        class="fill-amber-300 dark:fill-amber-800"
                                    />
                                    <circle
                                        cx="120"
                                        cy="65"
                                        r="15"
                                        class="fill-amber-500"
                                    />
                                    <foreignObject
                                        x="111"
                                        y="56"
                                        width="18"
                                        height="18"
                                    >
                                        <Hourglass
                                            class="h-4.5 w-4.5 text-white"
                                        />
                                    </foreignObject>
                                </svg>
                            </div>

                            <h2
                                class="mb-1 text-center text-xl font-medium text-foreground"
                            >
                                Permintaan Bergabung Terkirim
                            </h2>
                            <p
                                class="mb-6 max-w-xs text-center text-sm leading-relaxed font-light text-muted-foreground"
                            >
                                Permintaan Anda sedang menunggu persetujuan dari
                                ketua kelompok.
                            </p>

                            <div class="w-full space-y-3">
                                <div
                                    v-for="req in pendingJoinRequests"
                                    :key="req.id"
                                    class="flex flex-col gap-4 rounded-2xl border border-border bg-card p-4 shadow-xs sm:flex-row sm:items-center sm:justify-between"
                                >
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="rounded-lg bg-amber-500/10 p-2"
                                        >
                                            <Clock
                                                class="h-5 w-5 animate-pulse text-amber-600 dark:text-amber-400"
                                            />
                                        </div>
                                        <div>
                                            <p
                                                class="text-sm font-semibold text-foreground"
                                            >
                                                Kelompok
                                                <span
                                                    class="font-mono font-bold text-primary"
                                                    >{{ req.group.code }}</span
                                                >
                                            </p>
                                            <p
                                                class="mt-0.5 text-xs text-muted-foreground"
                                            >
                                                Ketua:
                                                {{ req.group.leader.name }}
                                            </p>
                                        </div>
                                    </div>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        class="justify-start rounded-full font-medium text-destructive hover:bg-destructive/10 hover:text-destructive sm:justify-center"
                                        @click="cancelJoinRequest(req.id)"
                                    >
                                        <UserX class="mr-1.5 h-4 w-4" />
                                        Batalkan
                                    </Button>
                                </div>
                            </div>

                            <!-- Back to Guide Link -->
                            <button
                                @click="showCarouselAnyway = true"
                                class="mt-6 cursor-pointer border-0 bg-transparent text-sm font-normal text-primary outline-none hover:underline"
                            >
                                Lihat panduan kelompok
                            </button>
                        </div>
                    </template>

                    <!-- Case 3: Unlocked State (Interactive Carousel) -->
                    <template v-else>
                        <div class="flex w-full max-w-md flex-col items-center">
                            <!-- Carousel Main Area -->
                            <div
                                class="flex w-full items-center justify-between gap-4"
                            >
                                <!-- Left Nav Arrow -->
                                <button
                                    @click="emblaApi?.scrollPrev()"
                                    :disabled="!canScrollPrev"
                                    class="flex h-10 w-10 shrink-0 cursor-pointer items-center justify-center rounded-full border border-border bg-background text-muted-foreground shadow-xs transition-all outline-none hover:bg-muted hover:text-foreground disabled:cursor-not-allowed disabled:opacity-40"
                                    aria-label="Slide sebelumnya"
                                >
                                    <ChevronLeft class="h-5 w-5" />
                                </button>

                                <!-- Carousel with only the content (no buttons inside) -->
                                <Carousel
                                    @init-api="setApi"
                                    :opts="{ loop: true }"
                                    class="w-full max-w-[280px] overflow-hidden sm:max-w-xs"
                                >
                                    <CarouselContent>
                                        <CarouselItem
                                            v-for="(slide, index) in slides"
                                            :key="index"
                                        >
                                            <div
                                                class="flex flex-col items-center select-none"
                                            >
                                                <!-- Circle Illustration -->
                                                <div
                                                    class="relative flex h-56 w-56 items-center justify-center overflow-hidden rounded-full border border-border/10 bg-slate-50 sm:h-64 sm:w-64 dark:bg-slate-900/30"
                                                >
                                                    <!-- Slide 1: Share Link -->
                                                    <svg
                                                        v-if="index === 0"
                                                        viewBox="0 0 240 240"
                                                        fill="none"
                                                        xmlns="http://www.w3.org/2000/svg"
                                                        class="h-full w-full p-4"
                                                    >
                                                        <!-- Left Webcam Feed -->
                                                        <rect
                                                            x="25"
                                                            y="70"
                                                            width="85"
                                                            height="90"
                                                            rx="12"
                                                            class="fill-card stroke-border"
                                                            stroke-width="1.5"
                                                        />
                                                        <g>
                                                            <!-- Left Person (Yellow/Amber shirt, portrait view) -->
                                                            <path
                                                                d="M45 160v-14c0-8 6-14 14-14h17c8 0 14 6 14 14v14H45z"
                                                                class="fill-amber-500"
                                                            />
                                                            <circle
                                                                cx="67.5"
                                                                cy="114"
                                                                r="13"
                                                                class="fill-orange-200 dark:fill-orange-300"
                                                            />
                                                            <path
                                                                d="M54.5 110c0-10 6-13 13-13s13 3 13 13c0 3-3 4-13 4s-13-1-13-4z"
                                                                class="fill-amber-900"
                                                            />
                                                            <!-- Active Indicator (e.g. green status dot) -->
                                                            <circle
                                                                cx="98"
                                                                cy="82"
                                                                r="4"
                                                                class="fill-green-500"
                                                            />
                                                        </g>

                                                        <!-- Right Webcam Feed -->
                                                        <rect
                                                            x="130"
                                                            y="70"
                                                            width="85"
                                                            height="90"
                                                            rx="12"
                                                            class="fill-card stroke-border"
                                                            stroke-width="1.5"
                                                        />
                                                        <g>
                                                            <!-- Right Person (Green shirt, portrait view) -->
                                                            <path
                                                                d="M150 160v-14c0-8 6-14 14-14h17c8 0 14 6 14 14v14h-45z"
                                                                class="fill-emerald-600"
                                                            />
                                                            <circle
                                                                cx="172.5"
                                                                cy="114"
                                                                r="13"
                                                                class="fill-yellow-200 dark:fill-yellow-300"
                                                            />
                                                            <path
                                                                d="M159.5 110c0-10 6-13 13-13s13 3 13 13c0 3-3 4-13 4s-13-1-13-4z"
                                                                class="fill-slate-900"
                                                            />
                                                            <!-- Active Indicator -->
                                                            <circle
                                                                cx="203"
                                                                cy="82"
                                                                r="4"
                                                                class="fill-green-500"
                                                            />
                                                        </g>

                                                        <!-- Link Bubble (overlapping the feeds in the center) -->
                                                        <g>
                                                            <circle
                                                                cx="120"
                                                                cy="115"
                                                                r="20"
                                                                class="fill-primary"
                                                            />
                                                            <foreignObject
                                                                x="108"
                                                                y="103"
                                                                width="24"
                                                                height="24"
                                                            >
                                                                <LinkIcon
                                                                    class="h-6 w-6 text-white"
                                                                />
                                                            </foreignObject>
                                                        </g>
                                                    </svg>

                                                    <!-- Slide 2: Join Group -->
                                                    <svg
                                                        v-else-if="index === 1"
                                                        viewBox="0 0 240 240"
                                                        fill="none"
                                                        xmlns="http://www.w3.org/2000/svg"
                                                        class="h-full w-full p-4"
                                                    >
                                                        <rect
                                                            x="50"
                                                            y="75"
                                                            width="140"
                                                            height="90"
                                                            rx="12"
                                                            class="fill-card stroke-primary/30 dark:stroke-primary/50"
                                                            stroke-width="2"
                                                        />
                                                        <rect
                                                            x="62"
                                                            y="90"
                                                            width="50"
                                                            height="8"
                                                            rx="4"
                                                            class="fill-primary/20 dark:fill-primary/30"
                                                        />
                                                        <circle
                                                            cx="170"
                                                            cy="94"
                                                            r="5"
                                                            class="fill-primary"
                                                        />
                                                        <rect
                                                            x="62"
                                                            y="118"
                                                            width="22"
                                                            height="26"
                                                            rx="4"
                                                            class="fill-muted stroke-border"
                                                            stroke-width="1.5"
                                                        />
                                                        <rect
                                                            x="90"
                                                            y="118"
                                                            width="22"
                                                            height="26"
                                                            rx="4"
                                                            class="fill-muted stroke-border"
                                                            stroke-width="1.5"
                                                        />
                                                        <rect
                                                            x="118"
                                                            y="118"
                                                            width="22"
                                                            height="26"
                                                            rx="4"
                                                            class="fill-muted stroke-border"
                                                            stroke-width="1.5"
                                                        />
                                                        <rect
                                                            x="146"
                                                            y="118"
                                                            width="22"
                                                            height="26"
                                                            rx="4"
                                                            class="fill-muted stroke-border"
                                                            stroke-width="1.5"
                                                        />
                                                        <path
                                                            d="M70 131h6"
                                                            class="stroke-muted-foreground"
                                                            stroke-width="2"
                                                            stroke-linecap="round"
                                                        />
                                                        <path
                                                            d="M98 131h6"
                                                            class="stroke-muted-foreground"
                                                            stroke-width="2"
                                                            stroke-linecap="round"
                                                        />
                                                        <path
                                                            d="M126 131h6"
                                                            class="stroke-muted-foreground"
                                                            stroke-width="2"
                                                            stroke-linecap="round"
                                                        />
                                                        <path
                                                            d="M154 131h6"
                                                            class="stroke-muted-foreground"
                                                            stroke-width="2"
                                                            stroke-linecap="round"
                                                        />
                                                    </svg>

                                                    <!-- Slide 3: Approval -->
                                                    <svg
                                                        v-else-if="index === 2"
                                                        viewBox="0 0 240 240"
                                                        fill="none"
                                                        xmlns="http://www.w3.org/2000/svg"
                                                        class="h-full w-full p-4"
                                                    >
                                                        <circle
                                                            cx="120"
                                                            cy="115"
                                                            r="42"
                                                            class="fill-card stroke-primary/30"
                                                            stroke-width="2"
                                                        />
                                                        <path
                                                            d="M120 85v30l12 12"
                                                            class="stroke-primary"
                                                            stroke-width="4"
                                                            stroke-linecap="round"
                                                        />
                                                        <circle
                                                            cx="60"
                                                            cy="135"
                                                            r="14"
                                                            class="fill-primary/10 dark:fill-primary/20"
                                                        />
                                                        <path
                                                            d="M50 146a10 10 0 0120 0"
                                                            class="fill-primary/40 dark:fill-primary/50"
                                                        />
                                                        <circle
                                                            cx="180"
                                                            cy="135"
                                                            r="14"
                                                            class="fill-primary/10 dark:fill-primary/20"
                                                        />
                                                        <path
                                                            d="M170 146a10 10 0 0120 0"
                                                            class="fill-primary/40 dark:fill-primary/50"
                                                        />
                                                        <circle
                                                            cx="120"
                                                            cy="65"
                                                            r="14"
                                                            class="fill-primary"
                                                        />
                                                        <!-- Check inside circle -->
                                                        <path
                                                            d="M115 65l3 3 7-7"
                                                            stroke="#fff"
                                                            stroke-width="2.5"
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                        />
                                                    </svg>
                                                </div>

                                                <!-- Title & Description (below illustration) -->
                                                <div
                                                    class="mt-6 flex min-h-[90px] flex-col justify-start text-center"
                                                >
                                                    <h3
                                                        class="text-lg font-medium tracking-tight text-foreground md:text-xl"
                                                    >
                                                        {{ slide.title }}
                                                    </h3>
                                                    <p
                                                        class="mt-2 max-w-xs text-sm leading-relaxed font-light text-muted-foreground"
                                                    >
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
                                    class="flex h-10 w-10 shrink-0 cursor-pointer items-center justify-center rounded-full border border-border bg-background text-muted-foreground shadow-xs transition-all outline-none hover:bg-muted hover:text-foreground disabled:cursor-not-allowed disabled:opacity-40"
                                    aria-label="Slide berikutnya"
                                >
                                    <ChevronRight class="h-5 w-5" />
                                </button>
                            </div>

                            <!-- Carousel Dots -->
                            <div class="mt-4 flex justify-center gap-2">
                                <span
                                    v-for="(_, index) in slides"
                                    :key="index"
                                    @click="emblaApi?.scrollTo(index)"
                                    class="h-2 cursor-pointer rounded-full transition-all duration-300"
                                    :class="
                                        currentSlide === index
                                            ? 'w-5 bg-primary'
                                            : 'w-2 bg-muted-foreground/30 hover:bg-muted-foreground/50'
                                    "
                                ></span>
                            </div>

                            <!-- Show Pending request button if it's hidden by override -->
                            <button
                                v-if="pendingJoinRequests.length > 0"
                                @click="showCarouselAnyway = false"
                                class="mt-6 cursor-pointer border-0 bg-transparent text-sm font-normal text-primary outline-none hover:underline"
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
