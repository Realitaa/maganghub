<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { FileText, CheckCircle2, Upload } from '@lucide/vue';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import { uploadResponse } from '@/routes/groups/submissions';

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
</script>

<template>
    <div class="space-y-4">
        <div
            class="flex items-start gap-3 rounded-xl border border-green-200 bg-green-50/30 p-4 dark:border-green-900 dark:bg-green-950/20"
        >
            <div
                class="rounded-full bg-green-100 p-2 text-green-700 dark:bg-green-900 dark:text-green-300"
            >
                <FileText class="h-5 w-5" />
            </div>
            <div>
                <p
                    class="text-sm font-semibold text-green-900 dark:text-green-100"
                >
                    Surat Permohonan Magang Diterbitkan
                </p>
                <p
                    class="mt-0.5 text-xs text-green-700/80 dark:text-green-300/80"
                >
                    Unggah surat balasan resmi setelah disetujui perusahaan.
                </p>
            </div>
        </div>

        <p class="text-sm leading-relaxed text-muted-foreground">
            Surat permohonan untuk kelompok Anda telah diterbitkan. Antar ke
            <strong>{{
                group.active_submission?.company_name ?? 'perusahaan tujuan'
            }}</strong>
            dan upload surat balasannya di sini.
        </p>

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

        <div class="flex flex-wrap gap-3">
            <input
                type="file"
                ref="fileInput"
                class="hidden"
                accept=".pdf,.docx,.png,.jpg,.jpeg"
                @change="handleResponseUpload"
            />
            <Button
                variant="outline"
                class="border-green-600 text-green-700 hover:bg-green-50 dark:border-green-700 dark:text-green-400 dark:hover:bg-green-950"
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
            class="text-xs font-medium text-destructive"
        >
            {{ responseUploadForm.errors.file }}
        </p>
    </div>
</template>
