import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { update as updateBanner } from '@/routes/groups/banner';
import CompressWorker from '@/workers/imageCompress.worker.ts?worker';

type CompressionState = 'idle' | 'compressing' | 'ready';

export interface CropCoordinates {
    left: number;
    top: number;
    width: number;
    height: number;
    naturalWidth: number;
    naturalHeight: number;
}

/**
 * Composable that manages the banner upload pipeline:
 *
 *   selectFile → (background) compress banner + compress OG in parallel
 *   onSubmit   → apply crop to master → upload banner + og_image
 *
 * The UI only ever sees isUploading / reset / selectFile / submit.
 * All compression is internal and invisible.
 */
export function useBannerUpload() {
    const isUploading = ref(false);
    const compressionState = ref<CompressionState>('idle');
    let masterBlob: Blob | null = null;
    let compressionPromise: Promise<Blob | null> | null = null;

    /**
     * Start background compression as soon as a file is selected.
     * Returns an object URL for the cropper preview (no wait needed).
     */
    function selectFile(file: File): string {
        masterBlob = null;
        compressionState.value = 'compressing';

        compressionPromise = new Promise<Blob | null>((resolve) => {
            file.arrayBuffer().then((buffer) => {
                const worker = new CompressWorker();

                worker.onmessage = (
                    event: MessageEvent<{
                        mode?: string;
                        blob?: Blob;
                        error?: string;
                    }>,
                ) => {
                    worker.terminate();

                    if (event.data.blob && event.data.mode === 'banner') {
                        masterBlob = event.data.blob;
                        compressionState.value = 'ready';
                        resolve(masterBlob);
                    } else {
                        compressionState.value = 'ready';
                        resolve(null);
                    }
                };

                worker.onerror = () => {
                    worker.terminate();
                    compressionState.value = 'ready';
                    resolve(null);
                };

                worker.postMessage({ mode: 'banner', buffer }, [buffer]);
            });
        });

        return URL.createObjectURL(file);
    }

    /**
     * Apply crop coordinates to the master blob, generate the OG image in
     * parallel, then upload both to the server.
     * If compression hasn't finished yet, waits for it transparently.
     */
    async function submit(
        cropCoords: CropCoordinates,
        groupId: number,
        onSuccess?: () => void,
    ): Promise<void> {
        isUploading.value = true;

        try {
            // Wait for banner compression if still running
            const base = (await compressionPromise) ?? null;
            const sourceBlob = base ?? masterBlob;

            if (!sourceBlob) {
                throw new Error('Tidak ada gambar yang siap untuk diupload.');
            }

            // Apply crop → banner blob + OG blob in parallel
            const [bannerBlob, ogBlob] = await Promise.all([
                applyCrop(sourceBlob, cropCoords),
                generateOgFromMaster(sourceBlob, cropCoords),
            ]);

            const formData = new FormData();
            formData.append('image', bannerBlob, 'banner.webp');
            formData.append('og_image', ogBlob, 'og.webp');

            router.post(updateBanner.url(groupId), formData, {
                forceFormData: true,
                preserveScroll: true,
                onSuccess: () => {
                    onSuccess?.();
                    reset();
                },
                onFinish: () => {
                    isUploading.value = false;
                },
            });
        } catch {
            isUploading.value = false;
        }
    }

    /**
     * Apply the user's crop to the master blob to produce the final banner.
     */
    async function applyCrop(
        blob: Blob,
        coords: CropCoordinates,
    ): Promise<Blob> {
        const bitmap = await createImageBitmap(blob);

        const scaleX = bitmap.width / coords.naturalWidth;
        const scaleY = bitmap.height / coords.naturalHeight;

        const sx = Math.round(coords.left * scaleX);
        const sy = Math.round(coords.top * scaleY);
        const sw = Math.round(coords.width * scaleX);
        const sh = Math.round(coords.height * scaleY);

        const canvas = document.createElement('canvas');
        canvas.width = sw;
        canvas.height = sh;

        const ctx = canvas.getContext('2d')!;
        ctx.drawImage(bitmap, sx, sy, sw, sh, 0, 0, sw, sh);
        bitmap.close();

        return blobFromCanvas(canvas, 'image/webp', 0.85);
    }

    /**
     * Send the master blob to the Worker's 'og' mode to generate a
     * 1200×630 center-cropped image ≤300KB for WhatsApp.
     * Runs in the Worker so it doesn't block the main thread.
     */
    function generateOgFromMaster(
        blob: Blob,
        _cropCoords: CropCoordinates,
    ): Promise<Blob> {
        return new Promise((resolve, reject) => {
            blob.arrayBuffer().then((buffer) => {
                const worker = new CompressWorker();

                worker.onmessage = (
                    event: MessageEvent<{
                        mode?: string;
                        blob?: Blob;
                        error?: string;
                    }>,
                ) => {
                    worker.terminate();

                    if (event.data.blob) {
                        resolve(event.data.blob);
                    } else {
                        reject(
                            new Error(
                                event.data.error ?? 'OG generation failed',
                            ),
                        );
                    }
                };

                worker.onerror = (err) => {
                    worker.terminate();
                    reject(err);
                };

                worker.postMessage({ mode: 'og', buffer }, [buffer]);
            });
        });
    }

    function blobFromCanvas(
        canvas: HTMLCanvasElement,
        type: string,
        quality: number,
    ): Promise<Blob> {
        return new Promise((resolve, reject) => {
            canvas.toBlob(
                (b) => {
                    if (b) {
                        resolve(b);
                    } else {
                        reject(new Error('Canvas toBlob failed'));
                    }
                },
                type,
                quality,
            );
        });
    }

    function reset() {
        masterBlob = null;
        compressionPromise = null;
        compressionState.value = 'idle';
        isUploading.value = false;
    }

    return {
        isUploading,
        selectFile,
        submit,
        reset,
    };
}
