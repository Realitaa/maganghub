<script setup lang="ts">
import { ImageIcon, Upload, X } from '@lucide/vue';
import { ref, watch } from 'vue';
import { Cropper } from 'vue-advanced-cropper';
import type { CropperResult } from 'vue-advanced-cropper';
import 'vue-advanced-cropper/dist/style.css';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
    DialogFooter,
} from '@/components/ui/dialog';
import { Spinner } from '@/components/ui/spinner';
import { useBannerUpload } from '@/composables/useBannerUpload';
import type { CropCoordinates } from '@/composables/useBannerUpload';

// ─── Props ────────────────────────────────────────────────────────────────────

const props = defineProps<{
    open: boolean;
    groupId: number;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
}>();

// ─── State ────────────────────────────────────────────────────────────────────

const { isUploading, selectFile, submit, reset } = useBannerUpload();

const step = ref<'select' | 'crop'>('select');
const previewUrl = ref<string | null>(null);
const cropperRef = ref<InstanceType<typeof Cropper> | null>(null);
const fileInputRef = ref<HTMLInputElement | null>(null);

// ─── Watchers ─────────────────────────────────────────────────────────────────

watch(
    () => props.open,
    (isOpen) => {
        if (!isOpen) {
            handleClose();
        }
    },
);

// ─── Handlers ─────────────────────────────────────────────────────────────────

function triggerFileSelect() {
    fileInputRef.value?.click();
}

function onFileChange(event: Event) {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];

    if (!file) {
        return;
    }

    // Revoke old object URL to prevent memory leaks
    if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value);
    }

    previewUrl.value = selectFile(file);
    step.value = 'crop';

    // Reset file input so the same file can be re-selected
    if (fileInputRef.value) {
        fileInputRef.value.value = '';
    }
}

async function handleSave() {
    if (!cropperRef.value) {
        return;
    }

    const result: CropperResult = cropperRef.value.getResult();
    const coordinates = result.coordinates;

    if (!coordinates) {
        return;
    }

    const cropCoords: CropCoordinates = {
        left: coordinates.left,
        top: coordinates.top,
        width: coordinates.width,
        height: coordinates.height,
        naturalWidth: result.image?.width ?? coordinates.width,
        naturalHeight: result.image?.height ?? coordinates.height,
    };

    await submit(cropCoords, props.groupId, () => {
        emit('update:open', false);
    });
}

function handleClose() {
    step.value = 'select';

    if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value);
        previewUrl.value = null;
    }

    reset();
}
</script>

<template>
    <Dialog :open="open" @update:open="$emit('update:open', $event)">
        <DialogContent class="sm:max-w-2xl">
            <DialogHeader>
                <DialogTitle>
                    {{
                        step === 'select'
                            ? 'Pilih Gambar Banner'
                            : 'Atur Tampilan Banner'
                    }}
                </DialogTitle>
                <DialogDescription>
                    {{
                        step === 'select'
                            ? 'Pilih gambar untuk dijadikan banner halaman kelompok.'
                            : 'Sesuaikan area yang ingin ditampilkan. Proporsi landscape dipertahankan.'
                    }}
                </DialogDescription>
            </DialogHeader>

            <!-- Step 1: File Select -->
            <div
                v-if="step === 'select'"
                class="flex flex-col items-center justify-center"
            >
                <button
                    type="button"
                    class="group flex h-48 w-full cursor-pointer flex-col items-center justify-center gap-3 rounded-xl border-2 border-dashed border-border bg-muted/20 transition-colors hover:border-primary/50 hover:bg-primary/5"
                    @click="triggerFileSelect"
                >
                    <div
                        class="rounded-full bg-primary/10 p-4 text-primary transition-transform group-hover:scale-110"
                    >
                        <ImageIcon class="h-8 w-8" />
                    </div>
                    <div class="text-center">
                        <p class="text-sm font-semibold">
                            Klik untuk memilih gambar
                        </p>
                        <p class="mt-0.5 text-xs text-muted-foreground">
                            PNG, JPG, atau WebP — maks. 10 MB
                        </p>
                    </div>
                </button>
                <input
                    ref="fileInputRef"
                    type="file"
                    accept="image/png,image/jpeg,image/webp"
                    class="hidden"
                    @change="onFileChange"
                />
            </div>

            <!-- Step 2: Cropper -->
            <div
                v-else-if="step === 'crop' && previewUrl"
                class="overflow-hidden rounded-xl"
            >
                <Cropper
                    ref="cropperRef"
                    :src="previewUrl"
                    class="max-h-[420px] w-full"
                    :stencil-props="{
                        aspectRatio: 3 / 1,
                        minAspectRatio: 2 / 1,
                        maxAspectRatio: 5 / 1,
                        handlers: {},
                        movable: true,
                        scalable: true,
                        resizable: true,
                    }"
                    :default-boundaries="'fit'"
                    background-class="bg-muted/40"
                />
                <p class="mt-2 text-center text-xs text-muted-foreground">
                    Geser dan perbesar untuk mengatur area banner
                </p>
            </div>

            <DialogFooter>
                <Button
                    v-if="step === 'crop'"
                    variant="ghost"
                    size="sm"
                    class="mr-auto gap-1.5 text-muted-foreground"
                    @click="step = 'select'"
                    :disabled="isUploading"
                >
                    <X class="h-4 w-4" />
                    Pilih Ulang
                </Button>
                <Button
                    variant="outline"
                    @click="$emit('update:open', false)"
                    :disabled="isUploading"
                >
                    Batal
                </Button>
                <Button
                    v-if="step === 'crop'"
                    id="btn-save-banner"
                    @click="handleSave"
                    :disabled="isUploading"
                >
                    <Spinner
                        v-if="isUploading"
                        class="mr-2 h-4 w-4 animate-spin"
                    />
                    <Upload v-else class="mr-2 h-4 w-4" />
                    {{ isUploading ? 'Menyimpan...' : 'Simpan Banner' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
