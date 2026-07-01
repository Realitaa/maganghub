<script setup lang="ts">
import {
    UserPlus,
    FilePlus,
    ShieldCheck,
    Mail,
    CheckCircle,
    Briefcase,
    Users,
    FileText,
    Clock,
    FileCheck,
    CheckCircle2,
    XCircle,
    Trophy,
    Check,
    FileSearchCorner,
} from '@lucide/vue';
import { breakpointsTailwind, useBreakpoints } from '@vueuse/core';
import { ref, onMounted, computed, watch, nextTick } from 'vue';
import { ScrollArea } from '@/components/ui/scroll-area';
import {
    Stepper,
    StepperTrigger,
    StepperItem,
    StepperIndicator,
    StepperTitle,
    StepperDescription,
    StepperSeparator,
} from '@/components/ui/stepper';

const props = defineProps<{
    mode: 'landing' | 'dialog';
    status?: string;
    modelValue?: number; // active/hovered step for landing mode
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: number): void;
}>();

// Breakpoints and orientation
const breakpoints = useBreakpoints(breakpointsTailwind);
const lgAndLarger = breakpoints.greaterOrEqual('xl');
const orientation = computed(() =>
    lgAndLarger.value ? 'horizontal' : 'vertical',
);

// Scroll centering refs
const containerRef = ref<HTMLElement | null>(null);
const itemRefs = ref<any[]>([]);

const setItemRef = (el: any, index: number) => {
    if (el) {
        itemRefs.value[index] = el.$el || el;
    }
};

// Mapping status to step index (for dialog mode)
const statusToStepIndex: Record<string, number> = {
    forming: 0,
    submitted: 1,
    letter_published: 2,
    applying: 3,
    loa_review: 4,
    accepted: 5,
    partially_accepted: 5,
    rejected: 5,
    internship_started: 6,
    completed: 7,
};

const currentStepIndex = computed(() => {
    return statusToStepIndex[props.status || 'forming'] ?? 0;
});

const activeStep = computed(() => {
    if (props.mode === 'dialog') {
        return currentStepIndex.value + 1;
    }

    return props.modelValue ?? 6;
});

// Color mapping from GroupStatusDialog.vue
const statusColors: Record<
    string,
    { border: string; bg: string; text: string }
> = {
    forming: {
        border: 'border-blue-500',
        bg: 'bg-blue-500/10',
        text: 'text-blue-500',
    },
    submitted: {
        border: 'border-yellow-500',
        bg: 'bg-yellow-500/10',
        text: 'text-yellow-500',
    },
    letter_published: {
        border: 'border-green-500',
        bg: 'bg-green-500/10',
        text: 'text-green-500',
    },
    applying: {
        border: 'border-yellow-500',
        bg: 'bg-yellow-500/10',
        text: 'text-yellow-500',
    },
    loa_review: {
        border: 'border-yellow-500',
        bg: 'bg-yellow-500/10',
        text: 'text-yellow-500',
    },
    accepted: {
        border: 'border-green-500',
        bg: 'bg-green-500/10',
        text: 'text-green-500',
    },
    partially_accepted: {
        border: 'border-yellow-500',
        bg: 'bg-yellow-500/10',
        text: 'text-yellow-500',
    },
    rejected: {
        border: 'border-destructive',
        bg: 'bg-destructive/10',
        text: 'text-destructive',
    },
    internship_started: {
        border: 'border-primary',
        bg: 'bg-primary/10',
        text: 'text-primary',
    },
    completed: {
        border: 'border-primary',
        bg: 'bg-primary/10',
        text: 'text-primary',
    },
};

// Stepper steps definitions
const landingSteps = [
    {
        step: 1,
        title: 'Buat Kelompok',
        description:
            'Bentuk kelompok magang bersama rekan mahasiswa dari prodi yang sama.',
        icon: UserPlus,
    },
    {
        step: 2,
        title: 'Ajukan Magang',
        description:
            'Masukkan data lengkap perusahaan tujuan dan berkas proposal magang.',
        icon: FilePlus,
    },
    {
        step: 3,
        title: 'Review Admin',
        description:
            'Persetujuan akademik dan verifikasi berkas oleh koordinator magang prodi.',
        icon: ShieldCheck,
    },
    {
        step: 4,
        title: 'Kirim Perusahaan',
        description:
            'Penerbitan surat permohonan magang resmi dan pengiriman ke pihak instansi tujuan.',
        icon: Mail,
    },
    {
        step: 5,
        title: 'Terima Respons',
        description:
            'Instansi memberikan balasan penerimaan dan mahasiswa mengunggah bukti tersebut.',
        icon: CheckCircle,
    },
    {
        step: 6,
        title: 'Mulai Magang',
        description:
            'Kegiatan magang resmi dimulai. Laporkan perkembangan harian Anda via logbook.',
        icon: Briefcase,
    },
];

const getDialogSteps = (status: string) => {
    let step6Label = 'Keputusan Perusahaan';
    let step6Icon: any = CheckCircle2;

    if (status === 'accepted') {
        step6Label = 'Diterima Perusahaan';
        step6Icon = CheckCircle2;
    } else if (status === 'partially_accepted') {
        step6Label = 'Diterima Sebagian';
        step6Icon = CheckCircle2;
    } else if (status === 'rejected') {
        step6Label = 'Ditolak Perusahaan';
        step6Icon = XCircle;
    }

    return [
        {
            step: 1,
            title: 'Membentuk Kelompok',
            description:
                'Kelompok sedang dalam tahap pembentukan. Ketua kelompok dapat mengisi data pengajuan magang dan mengundang anggota.',
            icon: Users,
        },
        {
            step: 2,
            title: 'Menunggu Verifikasi',
            description:
                'Pengajuan telah dikirim ke program studi dan sedang menunggu verifikasi oleh admin/operator.',
            icon: FileText,
        },
        {
            step: 3,
            title: 'Surat Terbit',
            description:
                'Surat permohonan magang telah diterbitkan oleh program studi. Silakan ambil lembar surat fisik ke operator/admin.',
            icon: FileCheck,
        },
        {
            step: 4,
            title: 'Menunggu Balasan',
            description:
                'Silakan antar surat pengantar ke perusahaan tujuan dan tunggu surat balasan (LoA).',
            icon: Clock,
        },
        {
            step: 5,
            title: 'Menunggu Review LoA',
            description:
                'Surat balasan dari perusahaan (LoA) sedang diperiksa oleh administrator/operator.',
            icon: FileSearchCorner,
        },
        {
            step: 6,
            title: step6Label,
            description:
                status === 'rejected'
                    ? 'Permohonan magang tidak dapat diterima. Silakan diskusikan dengan pembimbing untuk langkah selanjutnya.'
                    : 'Hasil keputusan penempatan magang dari perusahaan.',
            icon: step6Icon,
        },
        {
            step: 7,
            title: 'Magang Dimulai',
            description: 'Selamat melaksanakan magang di perusahaan tujuan!',
            icon: Trophy,
        },
        {
            step: 8,
            title: 'Selesai',
            description:
                'Masa magang kelompok ini telah selesai. Terima kasih telah menggunakan MagangHub.',
            icon: CheckCircle2,
        },
    ];
};

const steps = computed(() => {
    if (props.mode === 'landing') {
        return landingSteps;
    }

    return getDialogSteps(props.status || 'forming');
});

function handleMouseEnter(step: number) {
    if (props.mode === 'landing') {
        emit('update:modelValue', step);
    }
}

// Center active step when vertical in dialog mode
function centerActiveStep() {
    if (props.mode !== 'dialog' || orientation.value !== 'vertical') {
        return;
    }

    nextTick(() => {
        setTimeout(() => {
            const scrollAreaEl = containerRef.value as any;
            const container =
                scrollAreaEl?.$el?.querySelector(
                    '[data-slot="scroll-area-viewport"]',
                ) ||
                scrollAreaEl?.querySelector(
                    '[data-slot="scroll-area-viewport"]',
                ) ||
                scrollAreaEl;
            const activeIdx = currentStepIndex.value;
            const activeEl = itemRefs.value[activeIdx];

            if (container && activeEl) {
                const containerHeight = container.clientHeight;
                const activeHeight = activeEl.clientHeight;
                const activeTop = activeEl.offsetTop;
                container.scrollTo({
                    top: activeTop - containerHeight / 2 + activeHeight / 2,
                    behavior: 'smooth',
                });
            }
        }, 100);
    });
}

watch(
    [activeStep, orientation],
    () => {
        centerActiveStep();
    },
    { immediate: true },
);

onMounted(() => {
    centerActiveStep();
});

// Styling helpers
function getStatusBgClass(status: string): string {
    const map: Record<string, string> = {
        forming: 'bg-blue-500',
        submitted: 'bg-yellow-500',
        letter_published: 'bg-green-500',
        applying: 'bg-yellow-500',
        accepted: 'bg-green-500',
        partially_accepted: 'bg-yellow-500',
        rejected: 'bg-destructive',
        internship_started: 'bg-primary',
        completed: 'bg-primary',
    };

    return map[status] ?? 'bg-blue-500';
}

function getIndicatorClass(index: number) {
    const stepNum = index + 1;

    if (props.mode === 'landing') {
        if (stepNum === activeStep.value) {
            return 'group-data-[state=active]:bg-primary group-data-[state=active]:text-primary-foreground group-data-[state=active]:border-primary/25 border-4 border-background bg-muted text-muted-foreground';
        }

        return 'group-data-[state=completed]:bg-muted group-data-[state=completed]:text-muted-foreground bg-muted text-muted-foreground border-4 border-background';
    } else {
        const activeIdx = currentStepIndex.value;

        if (index < activeIdx) {
            return 'group-data-[state=completed]:bg-primary group-data-[state=completed]:text-primary-foreground bg-primary text-primary-foreground border-none';
        } else if (index === activeIdx) {
            const bgClass = getStatusBgClass(props.status || 'forming');

            return `group-data-[state=active]:${bgClass} group-data-[state=active]:text-white ${bgClass} text-white border-none`;
        } else {
            return 'group-data-[state=inactive]:bg-muted/40 group-data-[state=inactive]:text-muted-foreground/50 bg-muted/40 text-muted-foreground/50 border-none';
        }
    }
}

function getIconClass(index: number) {
    const stepNum = index + 1;

    if (props.mode === 'landing') {
        if (stepNum === activeStep.value) {
            return 'text-primary-foreground';
        }

        return 'text-muted-foreground';
    } else {
        const activeIdx = currentStepIndex.value;

        if (index < activeIdx) {
            return 'text-primary-foreground';
        } else if (index === activeIdx) {
            return 'text-white';
        } else {
            return 'text-muted-foreground/40';
        }
    }
}

function getSeparatorClass(index: number) {
    const stepNum = index + 1;

    if (props.mode === 'landing') {
        if (stepNum < activeStep.value) {
            return 'bg-primary';
        }

        return 'bg-border';
    } else {
        const activeIdx = currentStepIndex.value;

        if (index < activeIdx) {
            return 'bg-primary';
        }

        return 'bg-border';
    }
}

function getTextColorClass(index: number, element: 'span' | 'title' | 'desc') {
    const stepNum = index + 1;

    if (props.mode === 'landing') {
        const isActiveOrComp = stepNum <= activeStep.value;

        if (element === 'span') {
            return isActiveOrComp ? 'text-primary' : 'text-muted-foreground/80';
        } else if (element === 'title') {
            return isActiveOrComp ? 'text-foreground' : 'text-muted-foreground';
        } else {
            return isActiveOrComp
                ? 'text-muted-foreground'
                : 'text-muted-foreground/60';
        }
    } else {
        const activeIdx = currentStepIndex.value;

        if (index < activeIdx) {
            if (element === 'span') {
                return 'text-primary';
            }

            if (element === 'title') {
                return 'text-foreground font-medium';
            }

            return 'text-muted-foreground';
        } else if (index === activeIdx) {
            const colors = statusColors[props.status || 'forming'];

            if (element === 'span') {
                return colors.text;
            }

            if (element === 'title') {
                return `${colors.text} font-bold`;
            }

            return 'text-foreground/90';
        } else {
            if (element === 'span') {
                return 'text-muted-foreground/40';
            }

            if (element === 'title') {
                return 'text-muted-foreground/60';
            }

            return 'text-muted-foreground/40';
        }
    }
}

function getCardClass(index: number) {
    const stepNum = index + 1;

    if (props.mode === 'landing') {
        const isActive = stepNum === activeStep.value;
        const isComp = stepNum < activeStep.value;

        if (isActive) {
            return 'border-border bg-background dark:bg-zinc-950 shadow-sm';
        } else if (isComp) {
            return 'border-border/60 bg-background/50 dark:bg-zinc-950/50';
        }

        return 'border-border/30 bg-muted/10 dark:bg-zinc-900/10 opacity-70';
    } else {
        const activeIdx = currentStepIndex.value;

        if (index < activeIdx) {
            return 'border-border/60 bg-background dark:bg-zinc-950/50';
        } else if (index === activeIdx) {
            const colors = statusColors[props.status || 'forming'];

            return `${colors.border}/30 ${colors.bg} shadow-sm`;
        } else {
            return 'border-border/30 bg-muted/5 dark:bg-zinc-900/5 opacity-50';
        }
    }
}

function getStepIcon(item: any, index: number) {
    if (props.mode === 'landing') {
        return item.icon;
    }

    const activeIdx = currentStepIndex.value;

    if (index < activeIdx) {
        return Check;
    } else if (index === activeIdx) {
        const status = props.status || 'forming';

        if (status === 'rejected') {
            return XCircle;
        }

        if (
            status === 'accepted' ||
            status === 'partially_accepted' ||
            status === 'completed'
        ) {
            return CheckCircle2;
        }

        if (status === 'submitted') {
            return FileText;
        }

        if (status === 'forming') {
            return Users;
        }

        if (status === 'letter_published') {
            return FileCheck;
        }

        if (status === 'applying' || status === 'loa_review') {
            return Clock;
        }

        if (status === 'internship_started') {
            return Trophy;
        }

        return Trophy;
    } else {
        return item.icon;
    }
}
</script>

<template>
    <ScrollArea
        v-if="mode === 'dialog' && orientation === 'vertical'"
        ref="containerRef"
        class="h-[300px] pr-3"
    >
        <Stepper
            :model-value="activeStep"
            :orientation="orientation"
            class="relative flex w-full flex-col space-y-8 p-1 before:absolute before:top-2 before:bottom-2 before:left-[27px] before:w-[2px] before:bg-border"
        >
            <StepperItem
                v-for="(item, index) in steps"
                :key="item.step"
                :step="item.step"
                :ref="(el) => setItemRef(el, index)"
                @mouseenter="handleMouseEnter(item.step)"
                class="group relative z-10 flex w-full items-start gap-4 transition-all duration-300"
            >
                <StepperTrigger
                    class="flex cursor-default items-center justify-center p-0 hover:bg-transparent"
                >
                    <StepperIndicator
                        :class="[
                            'z-10 flex h-14 w-14 shrink-0 items-center justify-center rounded-full transition-all duration-300',
                            getIndicatorClass(index),
                        ]"
                    >
                        <component
                            :is="getStepIcon(item, index)"
                            :class="[
                                'h-5.5 w-5.5 transition-colors',
                                getIconClass(index),
                            ]"
                        />
                    </StepperIndicator>
                </StepperTrigger>

                <div
                    class="flex-1 rounded-xl border p-4.5 transition-all duration-300"
                    :class="getCardClass(index)"
                >
                    <span
                        class="text-xs font-bold tracking-wider uppercase transition-colors"
                        :class="getTextColorClass(index, 'span')"
                    >
                        Langkah {{ item.step }}
                    </span>
                    <StepperTitle
                        class="mt-0.5 text-sm font-bold transition-colors"
                        :class="getTextColorClass(index, 'title')"
                    >
                        {{ item.title }}
                    </StepperTitle>
                    <StepperDescription
                        class="mt-1 text-xs transition-colors"
                        :class="getTextColorClass(index, 'desc')"
                    >
                        {{ item.description }}
                    </StepperDescription>
                </div>
            </StepperItem>
        </Stepper>
    </ScrollArea>

    <div v-else ref="containerRef">
        <Stepper
            :model-value="activeStep"
            :orientation="orientation"
            :class="[
                'relative w-full p-1',
                orientation === 'horizontal'
                    ? 'flex items-start justify-between'
                    : 'flex flex-col space-y-8 before:absolute before:top-2 before:bottom-2 before:left-[27px] before:w-[2px] before:bg-border',
            ]"
        >
            <StepperItem
                v-for="(item, index) in steps"
                :key="item.step"
                :step="item.step"
                :ref="(el) => setItemRef(el, index)"
                @mouseenter="handleMouseEnter(item.step)"
                :class="[
                    'group relative z-10 transition-all duration-300',
                    orientation === 'horizontal'
                        ? 'flex flex-1 flex-col items-center text-center'
                        : 'flex w-full items-start gap-4',
                ]"
            >
                <template v-if="orientation === 'horizontal'">
                    <StepperTrigger
                        class="flex cursor-default flex-col items-center gap-0 p-0 hover:bg-transparent"
                    >
                        <StepperIndicator
                            :class="[
                                'flex h-16 w-16 items-center justify-center rounded-full transition-all duration-300',
                                getIndicatorClass(index),
                            ]"
                        >
                            <component
                                :is="getStepIcon(item, index)"
                                :class="[
                                    'h-6 w-6 transition-colors',
                                    getIconClass(index),
                                ]"
                            />
                        </StepperIndicator>
                    </StepperTrigger>

                    <StepperSeparator
                        v-if="item.step !== steps.length"
                        class="absolute top-8 right-[calc(-50%+2rem)] left-[calc(50%+2rem)] z-0 h-[2px] bg-border transition-colors duration-300"
                        :class="getSeparatorClass(index)"
                    />

                    <span
                        class="mt-3 text-xs font-bold tracking-wider uppercase transition-colors"
                        :class="getTextColorClass(index, 'span')"
                    >
                        Langkah {{ item.step }}
                    </span>
                    <StepperTitle
                        class="mt-2 text-sm font-bold transition-colors"
                        :class="getTextColorClass(index, 'title')"
                    >
                        {{ item.title }}
                    </StepperTitle>
                    <StepperDescription
                        class="mt-1 px-2 text-xs transition-colors"
                        :class="getTextColorClass(index, 'desc')"
                    >
                        {{ item.description }}
                    </StepperDescription>
                </template>

                <template v-else>
                    <StepperTrigger
                        class="flex cursor-default items-center justify-center p-0 hover:bg-transparent"
                    >
                        <StepperIndicator
                            :class="[
                                'z-10 flex h-14 w-14 shrink-0 items-center justify-center rounded-full transition-all duration-300',
                                getIndicatorClass(index),
                            ]"
                        >
                            <component
                                :is="getStepIcon(item, index)"
                                :class="[
                                    'h-5.5 w-5.5 transition-colors',
                                    getIconClass(index),
                                ]"
                            />
                        </StepperIndicator>
                    </StepperTrigger>

                    <div
                        class="flex-1 rounded-xl border p-4.5 transition-all duration-300"
                        :class="getCardClass(index)"
                    >
                        <span
                            class="text-xs font-bold tracking-wider uppercase transition-colors"
                            :class="getTextColorClass(index, 'span')"
                        >
                            Langkah {{ item.step }}
                        </span>
                        <StepperTitle
                            class="mt-0.5 text-sm font-bold transition-colors"
                            :class="getTextColorClass(index, 'title')"
                        >
                            {{ item.title }}
                        </StepperTitle>
                        <StepperDescription
                            class="mt-1 text-xs transition-colors"
                            :class="getTextColorClass(index, 'desc')"
                        >
                            {{ item.description }}
                        </StepperDescription>
                    </div>
                </template>
            </StepperItem>
        </Stepper>
    </div>
</template>
