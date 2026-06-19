<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import {
    FileText,
    Upload,
    CheckCircle2,
    AlertCircle,
    Calendar,
    HardDrive,
} from '@lucide/vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import {
    index as templateIndex,
    store as templateStore,
} from '@/routes/operator/templates';

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

// ─── Props ────────────────────────────────────────────────────────────────────

interface TemplateStatus {
    exists: boolean;
    size: string | null;
    updatedAt: string | null;
}

const props = defineProps<{
    template: TemplateStatus;
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
            },
        });
    }
}
</script>

<template>
    <Head title="Kelola Template Surat" />

    <div class="flex-1 space-y-6 p-4 pt-6 md:p-8 max-w-4xl">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Kelola Template Surat</h1>
            <p class="text-sm text-muted-foreground">
                Unggah dan perbarui template surat permohonan magang kelompok mahasiswa dalam format Word (.docx).
            </p>
        </div>

        <div class="grid gap-6 md:grid-cols-3">
            <!-- Left: Current status -->
            <Card class="md:col-span-2 border-border/80">
                <CardHeader>
                    <CardTitle class="text-base font-semibold">Status Template Aktif</CardTitle>
                    <CardDescription>
                        Berkas template yang saat ini digunakan oleh sistem untuk menerbitkan surat izin magang.
                    </CardDescription>
                </CardHeader>
                <CardContent class="space-y-6">
                    <!-- Status Alert -->
                    <div
                        v-if="template.exists"
                        class="flex items-start gap-3 rounded-xl border border-green-200/60 bg-green-50/10 p-4 dark:border-green-900/60 dark:bg-green-950/10"
                    >
                        <CheckCircle2 class="h-5 w-5 text-green-600 dark:text-green-400 mt-0.5 flex-shrink-0" />
                        <div>
                            <h4 class="text-sm font-semibold text-green-900 dark:text-green-100">Template Siap Digunakan</h4>
                            <p class="text-xs text-green-800/80 dark:text-green-300/80 mt-0.5 leading-relaxed">
                                Sistem akan otomatis mengambil berkas ini, mengganti placeholder data kelompok, dan melampirkannya sebagai surat pengantar yang sah.
                            </p>
                        </div>
                    </div>

                    <div
                        v-else
                        class="flex items-start gap-3 rounded-xl border border-destructive/20 bg-destructive/5 p-4"
                    >
                        <AlertCircle class="h-5 w-5 text-destructive mt-0.5 flex-shrink-0" />
                        <div>
                            <h4 class="text-sm font-semibold text-destructive">Template Belum Diunggah</h4>
                            <p class="text-xs text-destructive/80 mt-0.5 leading-relaxed">
                                PENTING: Mahasiswa tidak akan dapat mengajukan surat permohonan magang jika berkas template belum pernah diunggah ke sistem.
                            </p>
                        </div>
                    </div>

                    <!-- Metadata Grid -->
                    <div class="grid gap-4 rounded-xl border border-border/60 bg-muted/20 p-4 text-sm sm:grid-cols-2">
                        <div>
                            <Label class="text-xs text-muted-foreground flex items-center gap-1.5 mb-1">
                                <HardDrive class="h-3.5 w-3.5" />
                                Ukuran Berkas
                            </Label>
                            <p class="font-medium text-foreground">
                                {{ template.exists && template.size ? template.size : 'Tidak tersedia' }}
                            </p>
                        </div>
                        <div>
                            <Label class="text-xs text-muted-foreground flex items-center gap-1.5 mb-1">
                                <Calendar class="h-3.5 w-3.5" />
                                Terakhir Diperbarui
                            </Label>
                            <p class="font-medium text-foreground">
                                {{ template.exists && template.updatedAt ? template.updatedAt : 'Tidak tersedia' }}
                            </p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Right: Upload panel -->
            <Card class="border-border/80">
                <CardHeader>
                    <CardTitle class="text-base font-semibold">Perbarui Berkas</CardTitle>
                    <CardDescription>
                        Unggah berkas baru.
                    </CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="flex flex-col items-center justify-center border-2 border-dashed border-border/80 rounded-xl p-6 text-center hover:bg-muted/10 transition-colors">
                        <FileText class="h-10 w-10 text-muted-foreground mb-3" />
                        <h4 class="text-xs font-semibold">Format Dokumen Word</h4>
                        <p class="text-[10px] text-muted-foreground mt-1 max-w-[150px]">
                            Gunakan ekstensi berkas .docx maksimal 5 MB.
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
                        class="w-full gap-2 bg-primary hover:bg-primary/95 text-primary-foreground"
                        @click="triggerUpload"
                        :disabled="form.processing"
                        id="btn-upload-template"
                    >
                        <Spinner v-if="form.processing" class="h-4 w-4 animate-spin" />
                        <Upload v-else class="h-4 w-4" />
                        Pilih & Unggah Berkas
                    </Button>

                    <p v-if="form.errors.file" class="text-xs text-destructive text-center font-medium mt-1">
                        {{ form.errors.file }}
                    </p>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
