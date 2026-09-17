<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import {
    FileText,
    Upload,
    AlertCircle,
    Calendar,
    HardDrive,
    Copy,
    Check,
    FileCheck2,
    Users,
} from '@lucide/vue';
import { useClipboard } from '@vueuse/core';
import { renderAsync } from 'docx-preview';
import { TabsList, TabsRoot, TabsTrigger } from 'reka-ui';
import { ref, watch, onMounted } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Spinner } from '@/components/ui/spinner';
import { useIdTimeFormat } from '@/composables/useIdTimeFormat';
import {
    index as templateIndex,
    store as templateStore,
    raw as templateRawRoute,
    preview as templatePreviewRoute,
} from '@/routes/internships/templates';
import type {
    TemplateStatus,
    RecentSubmissionGroup,
    TemplatePlaceholder,
} from '@/types';

// Define layout breadcrumbs
defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Kelola Template',
                href: templateIndex.url(),
            },
        ],
    },
});

const { formatDateTime } = useIdTimeFormat();
const { copy } = useClipboard({ legacy: true });

// ─── Props ────────────────────────────────────────────────────────────────────

const props = defineProps<{
    template: TemplateStatus;
    recentGroups: RecentSubmissionGroup[];
    placeholders: TemplatePlaceholder[];
}>();

// ─── Form & File Upload ───────────────────────────────────────────────────────

const form = useForm({
    file: null as File | null,
});

const fileInput = ref<HTMLInputElement | null>(null);

function triggerUpload() {
    fileInput.value?.click();
}

function handleFileChange(event: Event) {
    const target = event.target as HTMLInputElement;

    if (target.files && target.files[0]) {
        const file = target.files[0];
        form.file = file;

        // Upload immediately
        form.post(templateStore.url(), {
            forceFormData: true,
            onSuccess: () => {
                form.reset();

                if (fileInput.value) {
                    fileInput.value.value = '';
                }

                // Refresh preview after new template upload
                updatePreview();
            },
        });
    }
}

// ─── Group Selection & Placeholders Copy ──────────────────────────────────────

const selectedGroupId = ref<string>(
    props.recentGroups.length > 0 ? String(props.recentGroups[0].id) : '',
);

const copiedKey = ref<string | null>(null);

async function handleCopy(key: string) {
    await copy(key);
    copiedKey.value = key;
    setTimeout(() => {
        if (copiedKey.value === key) {
            copiedKey.value = null;
        }
    }, 2000);
}

// ─── Docx Preview State & Logic ───────────────────────────────────────────────

const activePreviewTab = ref<'template' | 'processed'>('template');
const isPreviewLoading = ref(false);
const previewError = ref<string | null>(null);
const previewContainerRef = ref<HTMLElement | null>(null);

async function loadTemplatePreview() {
    if (!props.template.exists) {
        previewError.value = 'Template surat belum diunggah.';

        return;
    }

    isPreviewLoading.value = true;
    previewError.value = null;

    try {
        const response = await fetch(templateRawRoute.url());

        if (!response.ok) {
            throw new Error('Gagal mengambil berkas template surat.');
        }

        const arrayBuffer = await response.arrayBuffer();

        if (previewContainerRef.value) {
            previewContainerRef.value.innerHTML = '';
            await renderAsync(
                arrayBuffer,
                previewContainerRef.value,
                undefined,
                {
                    inWrapper: true,
                    ignoreWidth: true,
                    breakPages: true,
                },
            );
        }
    } catch (err: any) {
        previewError.value =
            err?.message || 'Gagal memuat pratinjau dokumen template.';
    } finally {
        isPreviewLoading.value = false;
    }
}

async function loadProcessedPreview() {
    if (!props.template.exists) {
        previewError.value = 'Template surat belum diunggah.';

        return;
    }

    if (!selectedGroupId.value) {
        previewError.value =
            'Silakan pilih kelompok terlebih dahulu untuk melihat pratinjau hasil penimpaan data.';

        return;
    }

    isPreviewLoading.value = true;
    previewError.value = null;

    try {
        const url = `${templatePreviewRoute.url()}?group_id=${encodeURIComponent(selectedGroupId.value)}`;
        const response = await fetch(url);

        if (!response.ok) {
            const errorText = await response.text();

            throw new Error(
                errorText || 'Gagal memproses penimpaan template dokumen.',
            );
        }

        const arrayBuffer = await response.arrayBuffer();

        if (previewContainerRef.value) {
            previewContainerRef.value.innerHTML = '';
            await renderAsync(
                arrayBuffer,
                previewContainerRef.value,
                undefined,
                {
                    inWrapper: true,
                    ignoreWidth: true,
                    breakPages: true,
                },
            );
        }
    } catch (err: any) {
        previewError.value =
            err?.message || 'Gagal memuat pratinjau dokumen yang diproses.';
    } finally {
        isPreviewLoading.value = false;
    }
}

function updatePreview() {
    if (activePreviewTab.value === 'template') {
        loadTemplatePreview();
    } else {
        loadProcessedPreview();
    }
}

watch(activePreviewTab, () => {
    updatePreview();
});

watch(selectedGroupId, () => {
    if (activePreviewTab.value === 'processed') {
        loadProcessedPreview();
    }
});

onMounted(() => {
    updatePreview();
});
</script>

<template>
    <Head title="Kelola Template Surat" />

    <div class="flex-1 space-y-6 p-4 pt-6 md:p-8">
        <!-- 1. Judul & Subjudul Halaman Utama -->
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-foreground">
                Kelola Template Surat
            </h1>
            <p class="text-sm text-muted-foreground">
                Unggah dan perbarui template surat permohonan magang kelompok
                mahasiswa dalam format Word (.docx).
            </p>
        </div>

        <!-- 2. Responsive Split Layout: Kiri (Kontrol & Metadata) / Kanan (Pratinjau Dokumen) -->
        <div class="grid grid-cols-1 gap-8 xl:grid-cols-12">
            <!-- ─── Kolom Kiri: Status, Upload, Pemilihan Kelompok & Variabel ─── -->
            <div class="space-y-6 xl:col-span-5">
                <Card class="border-border/80">
                    <CardHeader class="space-y-2">
                        <div class="flex items-center justify-between">
                            <CardTitle class="text-base font-semibold">
                                Status Template Aktif
                            </CardTitle>
                            <Badge
                                v-if="template.exists"
                                variant="outline"
                                class="border-green-500/30 bg-green-500/10 text-green-600 dark:text-green-400"
                            >
                                Aktif & Siap
                            </Badge>
                            <Badge v-else variant="destructive">
                                Belum Diunggah
                            </Badge>
                        </div>
                        <CardDescription class="leading-relaxed">
                            Berkas template yang saat ini digunakan oleh sistem
                            untuk menerbitkan surat izin magang. Sistem akan
                            otomatis mengambil berkas ini, mengganti placeholder
                            data kelompok, dan melampirkannya sebagai surat
                            pengantar yang sah.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <!-- Ukuran & Terakhir Diperbarui -->
                        <div
                            class="grid grid-cols-2 gap-4 rounded-xl border border-border/60 bg-muted/20 p-4 text-sm"
                        >
                            <div>
                                <Label
                                    class="mb-1 flex items-center gap-1.5 text-xs text-muted-foreground"
                                >
                                    <HardDrive class="h-3.5 w-3.5" />
                                    Ukuran Berkas
                                </Label>
                                <p class="font-medium text-foreground">
                                    {{
                                        template.exists && template.size
                                            ? template.size
                                            : 'Tidak tersedia'
                                    }}
                                </p>
                            </div>
                            <div>
                                <Label
                                    class="mb-1 flex items-center gap-1.5 text-xs text-muted-foreground"
                                >
                                    <Calendar class="h-3.5 w-3.5" />
                                    Terakhir Diperbarui
                                </Label>
                                <p class="font-medium text-foreground">
                                    {{
                                        template.exists && template.updatedAt
                                            ? formatDateTime(template.updatedAt)
                                            : 'Tidak tersedia'
                                    }}
                                </p>
                            </div>
                        </div>

                        <!-- Form Perbarui Berkas Template -->
                        <div class="space-y-3">
                            <Label
                                class="text-xs font-semibold text-foreground"
                            >
                                Perbarui Berkas Template
                            </Label>
                            <div
                                class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-border/80 p-5 text-center transition-colors hover:bg-muted/10"
                                @click="triggerUpload"
                            >
                                <FileText
                                    class="mb-2 h-8 w-8 text-muted-foreground"
                                />
                                <h4
                                    class="text-xs font-semibold text-foreground"
                                >
                                    Format Dokumen Word (.docx)
                                </h4>
                                <p
                                    class="mt-0.5 text-[11px] text-muted-foreground"
                                >
                                    Gunakan berkas .docx maksimal 2 MB.
                                </p>
                            </div>

                            <input
                                type="file"
                                ref="fileInput"
                                class="hidden"
                                accept=".docx"
                                @change="handleFileChange"
                            />

                            <Button
                                class="w-full gap-2 bg-primary text-primary-foreground hover:bg-primary/95"
                                @click="triggerUpload"
                                :disabled="form.processing"
                                id="btn-upload-template"
                            >
                                <Spinner
                                    v-if="form.processing"
                                    class="h-4 w-4 animate-spin"
                                />
                                <Upload v-else class="h-4 w-4" />
                                Pilih & Unggah Berkas
                            </Button>

                            <p
                                v-if="form.errors.file"
                                class="mt-1 text-center text-xs font-medium text-destructive"
                            >
                                {{ form.errors.file }}
                            </p>
                        </div>

                        <!-- Komponen Select untuk 5 Kelompok Terakhir yang Mengajukan -->
                        <div class="space-y-2 border-t border-border/60 pt-4">
                            <Label
                                class="flex items-center gap-1.5 text-xs font-semibold text-foreground"
                            >
                                <Users class="h-3.5 w-3.5 text-primary" />
                                Pilih Kelompok untuk Simulasi Pratinjau
                            </Label>
                            <Select
                                v-model="selectedGroupId"
                                :disabled="recentGroups.length === 0"
                            >
                                <SelectTrigger class="w-full text-xs">
                                    <SelectValue
                                        placeholder="Pilih kelompok pengajuan magang..."
                                    />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="group in recentGroups"
                                        :key="group.id"
                                        :value="String(group.id)"
                                        class="text-xs"
                                    >
                                        {{ group.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <p
                                v-if="recentGroups.length === 0"
                                class="text-[11px] text-muted-foreground"
                            >
                                Belum ada kelompok yang mengajukan magang di
                                sistem.
                            </p>
                            <p
                                v-else
                                class="text-[11px] leading-relaxed text-muted-foreground"
                            >
                                Pilih kelompok di atas untuk menguji penimpaan
                                variabel otomatis pada tab
                                &ldquo;Diproses&rdquo;.
                            </p>
                        </div>

                        <!-- Daftar Variabel Placeholder (Single Source of Truth) -->
                        <div class="space-y-3 border-t border-border/60 pt-4">
                            <div class="flex items-center justify-between">
                                <Label
                                    class="text-xs font-semibold text-foreground"
                                >
                                    Daftar Variabel yang Disediakan Sistem
                                </Label>
                                <span class="text-[11px] text-muted-foreground">
                                    Klik ikon salin
                                </span>
                            </div>
                            <div class="space-y-2">
                                <div
                                    v-for="p in placeholders"
                                    :key="p.key"
                                    class="flex items-start justify-between gap-2 rounded-lg border border-border/60 bg-muted/20 p-2.5 transition-colors hover:bg-muted/40"
                                >
                                    <div class="min-w-0 flex-1 space-y-0.5">
                                        <div
                                            class="flex flex-wrap items-center gap-1.5"
                                        >
                                            <Badge
                                                variant="secondary"
                                                class="font-mono text-[11px] font-semibold text-foreground"
                                            >
                                                {{ p.key }}
                                            </Badge>
                                            <span
                                                class="text-xs font-medium text-foreground"
                                            >
                                                {{ p.label }}
                                            </span>
                                        </div>
                                        <p
                                            class="text-[11px] leading-tight text-muted-foreground"
                                        >
                                            {{ p.description }}
                                        </p>
                                        <p
                                            class="font-mono text-[10px] text-muted-foreground/80"
                                        >
                                            Contoh: {{ p.example }}
                                        </p>
                                    </div>

                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        class="h-7 w-7 shrink-0 text-muted-foreground hover:text-foreground"
                                        @click="handleCopy(p.key)"
                                        :title="`Salin ${p.key}`"
                                    >
                                        <Check
                                            v-if="copiedKey === p.key"
                                            class="h-3.5 w-3.5 text-green-600 dark:text-green-400"
                                        />
                                        <Copy v-else class="h-3.5 w-3.5" />
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- ─── Kolom Kanan: Pratinjau Dokumen (.docx) ─── -->
            <div class="space-y-4 xl:col-span-7">
                <!-- Header Pratinjau Dokumen -->
                <div class="space-y-1">
                    <h2 class="text-base font-semibold text-foreground">
                        Pratinjau Dokumen Surat
                    </h2>
                    <p class="text-xs leading-relaxed text-muted-foreground">
                        Tampilan ini adalah pratinjau yang mungkin tidak 100%
                        sesuai dengan hasil akhir dari segi tata letak visual
                        dibandingkan Microsoft Word asli, namun menjadi jaminan
                        bahwa kontennya (terutama variabel yang diekspos oleh
                        sistem) benar-benar ditimpa ke dalam template.
                    </p>
                </div>

                <!-- Tabs Kontrol: Template vs Diproses -->
                <div
                    class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"
                >
                    <TabsRoot v-model="activePreviewTab">
                        <TabsList
                            class="inline-flex h-9 items-center rounded-lg bg-muted p-1 text-muted-foreground"
                        >
                            <TabsTrigger
                                value="template"
                                class="inline-flex cursor-pointer items-center gap-1.5 rounded-md px-3 py-1 text-xs font-medium transition-all hover:text-foreground data-[state=active]:bg-background data-[state=active]:text-foreground data-[state=active]:shadow-xs"
                            >
                                <FileText class="h-3.5 w-3.5" />
                                <span>Template</span>
                            </TabsTrigger>
                            <TabsTrigger
                                value="processed"
                                class="inline-flex cursor-pointer items-center gap-1.5 rounded-md px-3 py-1 text-xs font-medium transition-all hover:text-foreground data-[state=active]:bg-background data-[state=active]:text-foreground data-[state=active]:shadow-xs"
                            >
                                <FileCheck2 class="h-3.5 w-3.5" />
                                <span>Diproses</span>
                            </TabsTrigger>
                        </TabsList>
                    </TabsRoot>

                    <p class="text-[11px] text-muted-foreground">
                        <span v-if="activePreviewTab === 'template'">
                            Menampilkan dokumen template asli (.docx).
                        </span>
                        <span v-else>
                            Menampilkan dokumen setelah ditimpa informasi
                            kelompok terpilih.
                        </span>
                    </p>
                </div>

                <!-- Kartu Pratinjau (Background putih, border-0, rounded-none) -->
                <div
                    class="relative min-h-[500px] w-full overflow-hidden rounded-none border-0 bg-white text-zinc-900 shadow-sm dark:bg-white dark:text-zinc-900"
                >
                    <!-- Loading State Overlay -->
                    <div
                        v-if="isPreviewLoading"
                        class="absolute inset-0 z-20 flex flex-col items-center justify-center bg-white/80 p-6 text-center backdrop-blur-xs dark:bg-white/80"
                    >
                        <Spinner class="h-8 w-8 animate-spin text-primary" />
                        <p class="mt-3 text-xs font-medium text-zinc-700">
                            {{
                                activePreviewTab === 'template'
                                    ? 'Memuat berkas template dokumen...'
                                    : 'Memproses dan menimpa dokumen kelompok...'
                            }}
                        </p>
                    </div>

                    <!-- Error State -->
                    <div
                        v-if="previewError && !isPreviewLoading"
                        class="flex min-h-[450px] flex-col items-center justify-center p-8 text-center"
                    >
                        <AlertCircle class="h-10 w-10 text-amber-500" />
                        <h4 class="mt-3 text-sm font-semibold text-zinc-800">
                            Pratinjau Tidak Tersedia
                        </h4>
                        <p class="mt-1 max-w-sm text-xs text-zinc-600">
                            {{ previewError }}
                        </p>
                    </div>

                    <!-- Template Belum Diunggah State -->
                    <div
                        v-else-if="!template.exists && !isPreviewLoading"
                        class="flex min-h-[450px] flex-col items-center justify-center p-8 text-center"
                    >
                        <FileText class="h-12 w-12 text-zinc-400" />
                        <h4 class="mt-3 text-sm font-semibold text-zinc-800">
                            Berkas Template Belum Diunggah
                        </h4>
                        <p class="mt-1 max-w-sm text-xs text-zinc-500">
                            Silakan unggah berkas template (.docx) di panel kiri
                            untuk memuat pratinjau dokumen di sini.
                        </p>
                    </div>

                    <!-- Docx Render View -->
                    <div
                        v-show="template.exists && !previewError"
                        class="flex max-h-[850px] min-h-[500px] w-full flex-col items-center overflow-auto p-4"
                    >
                        <div
                            ref="previewContainerRef"
                            class="flex w-full flex-col items-center"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
:deep(.docx-wrapper) {
    background: transparent !important;
    padding: 16px 8px !important;
    width: 100% !important;
    display: flex;
    flex-direction: column;
    align-items: center;
}

:deep(.docx-wrapper > section.docx) {
    box-shadow:
        0 4px 6px -1px rgb(0 0 0 / 0.08),
        0 2px 4px -2px rgb(0 0 0 / 0.06) !important;
    margin-bottom: 24px !important;
    border: 1px solid #e4e4e7 !important;
    max-width: 100% !important;
    background: #ffffff !important;
    color: #000000 !important;
}
</style>
