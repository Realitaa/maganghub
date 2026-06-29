<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { FileText, CheckCircle2, Upload, AlertCircle, Download } from '@lucide/vue';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import { uploadResponse, downloadLetter } from '@/routes/groups/submissions';

import type { Group } from '@/types';

const props = defineProps<{
    group: Group;
}>();

const responseUploadForm = useForm({
    file: null as File | null,
});

const fileInput = ref<HTMLInputElement | null>(null);

function triggerUpload() {
    fileInput.value?.click();
}

function handleResponseUpload(event: Event) {
    const target = event.target as HTMLInputElement;

    if (target.files && target.files[0] && props.group?.active_submission) {
        const file = target.files[0];
        responseUploadForm.file = file;
        responseUploadForm.post(
            uploadResponse.url(props.group.active_submission.id),
            {
                forceFormData: true,
                preserveScroll: true,
                onSuccess: () => {
                    responseUploadForm.reset();

                    if (fileInput.value) {
                        fileInput.value.value = '';
                    }
                },
            },
        );
    }
}

function downloadLetterFile() {
    if (props.group?.active_submission) {
        window.open(
            downloadLetter.url({ submission: props.group.active_submission.id }),
            '_blank',
        );
    }
}
</script>

<template>
    <div class="space-y-6">
        <!-- Status: Letter Published -->
        <template v-if="group.status === 'letter_published'">
            <div
                class="flex items-start gap-3 rounded-xl border border-blue-200 bg-blue-50/30 p-4 dark:border-blue-900 dark:bg-blue-950/20"
            >
                <div
                    class="rounded-full bg-blue-100 p-2 text-blue-700 dark:bg-blue-900 dark:text-blue-350"
                >
                    <FileText class="h-5 w-5" />
                </div>
                <div class="space-y-1">
                    <p class="text-sm font-semibold text-blue-900 dark:text-blue-100">
                        Surat Permohonan Magang Telah Terbit
                    </p>
                    <p class="text-xs text-blue-700 dark:text-blue-300">
                        Surat permohonan magang kelompok Anda telah diterbitkan oleh program studi.
                        Silakan <strong>mengambil lembar surat fisik ke operator/admin program studi</strong>.
                    </p>
                </div>
            </div>

            <div class="rounded-xl border border-border bg-card p-5 space-y-4">
                <div class="space-y-1">
                    <h4 class="text-sm font-semibold text-foreground">
                        Langkah Selanjutnya:
                    </h4>
                    <p class="text-xs text-muted-foreground">
                        Setelah Anda mengambil surat fisik dari operator/admin, status kelompok Anda akan diperbarui oleh admin menjadi <strong>"Menunggu Balasan"</strong> (Applying), di mana Anda dapat mengunggah surat balasan dari perusahaan.
                    </p>
                </div>

                <div class="flex flex-wrap gap-3 pt-2">
                    <Button
                        v-if="group.active_submission?.letter_path"
                        variant="outline"
                        size="sm"
                        class="gap-1.5"
                        @click="downloadLetterFile"
                    >
                        <Download class="h-4 w-4" />
                        Unduh Salinan Surat (PDF)
                    </Button>
                </div>
            </div>
        </template>

        <!-- Status: Applying (Menunggu Balasan) -->
        <template v-else-if="group.status === 'applying'">
            <div
                class="flex items-start gap-3 rounded-xl border border-yellow-200 bg-yellow-50/30 p-4 dark:border-yellow-900/50 dark:bg-yellow-950/10"
            >
                <div
                    class="rounded-full bg-yellow-100 p-2 text-yellow-750 dark:bg-yellow-900 dark:text-yellow-350"
                >
                    <AlertCircle class="h-5 w-5" />
                </div>
                <div class="space-y-1">
                    <p class="text-sm font-semibold text-yellow-900 dark:text-yellow-100">
                        Surat Sedang Diajukan ke Perusahaan
                    </p>
                    <p class="text-xs text-yellow-700 dark:text-yellow-300">
                        Silakan <strong>mengantarkan surat pengantar magang ke pihak perusahaan/instansi tujuan</strong> ({{ group.active_submission?.company_name }}). Setelah mendapatkan surat balasan, silakan unggah di bawah ini.
                    </p>
                </div>
            </div>

            <div class="rounded-xl border border-border bg-card p-5 space-y-4">
                <div class="space-y-2">
                    <h4 class="text-sm font-semibold text-foreground">
                        Unggah Surat Balasan (Letter of Acceptance -- LoA)
                    </h4>
                    <p class="text-xs text-muted-foreground leading-relaxed">
                        Pastikan format file surat balasan dari perusahaan adalah <strong>PDF, DOCX, PNG, JPG, atau JPEG</strong> dengan ukuran tidak melebihi <strong>2MB</strong>.
                    </p>
                </div>

                <!-- Success if already uploaded -->
                <div
                    v-if="group.active_submission?.company_response_path"
                    class="flex items-center gap-2 rounded-lg border border-green-200/50 bg-green-100/50 p-3 text-sm text-green-800 dark:border-green-800/50 dark:bg-green-900/30 dark:text-green-200"
                >
                    <CheckCircle2
                        class="h-4 w-4 shrink-0 text-green-600 dark:text-green-400"
                    />
                    <span>Surat balasan perusahaan telah berhasil diunggah.</span>
                </div>

                <div class="flex flex-wrap gap-3 pt-2">
                    <input
                        type="file"
                        ref="fileInput"
                        class="hidden"
                        accept=".pdf,.docx,.png,.jpg,.jpeg"
                        @change="handleResponseUpload"
                    />
                    <Button
                        variant="outline"
                        class="border-green-600 text-green-700 hover:bg-green-50 dark:border-green-700 dark:text-green-400 dark:hover:bg-green-950/30"
                        @click="triggerUpload"
                        :disabled="responseUploadForm.processing"
                        id="btn-upload-response"
                    >
                        <Spinner
                            v-if="responseUploadForm.processing"
                            class="mr-2 h-4 w-4 animate-spin"
                        />
                        <Upload v-else class="mr-2 h-4 w-4" />
                        {{
                            group.active_submission?.company_response_path
                                ? 'Unggah Ulang Surat Balasan'
                                : 'Unggah Surat Balasan'
                        }}
                    </Button>
                </div>

                <p
                    v-if="responseUploadForm.errors.file"
                    class="text-xs font-medium text-destructive mt-1"
                >
                    {{ responseUploadForm.errors.file }}
                </p>
            </div>
        </template>
    </div>
</template>
