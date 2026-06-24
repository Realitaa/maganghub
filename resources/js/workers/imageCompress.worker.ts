/**
 * Image Compression Web Worker
 *
 * Supports two modes:
 *
 * Mode: 'banner'
 *   Resizes the source image to a max width of 1600px (preserving aspect ratio)
 *   and encodes as WebP @ quality 0.8.
 *   Messages IN:  { mode: 'banner'; buffer: ArrayBuffer }
 *   Messages OUT: { mode: 'banner'; blob: Blob } | { error: string }
 *
 * Mode: 'og'
 *   Takes an already-compressed banner Blob and produces a 1200×630 center-cropped
 *   OG image encoded as WebP @ quality 0.75, targeting ≤300KB for WhatsApp.
 *   Messages IN:  { mode: 'og'; buffer: ArrayBuffer }
 *   Messages OUT: { mode: 'og'; blob: Blob } | { error: string }
 */

type InMessage =
    | { mode: 'banner'; buffer: ArrayBuffer }
    | { mode: 'og'; buffer: ArrayBuffer };

const OG_WIDTH = 1200;
const OG_HEIGHT = 630;

self.onmessage = async (event: MessageEvent<InMessage>) => {
    try {
        const { mode, buffer } = event.data;

        const sourceBitmap = await createImageBitmap(new Blob([buffer]));

        if (mode === 'banner') {
            const blob = await compressBanner(sourceBitmap);
            sourceBitmap.close();
            self.postMessage({ mode: 'banner', blob });
        } else {
            const blob = await generateOgImage(sourceBitmap);
            sourceBitmap.close();
            self.postMessage({ mode: 'og', blob });
        }
    } catch (err) {
        self.postMessage({
            error: err instanceof Error ? err.message : String(err),
        });
    }
};

// ─── Banner ──────────────────────────────────────────────────────────────────

async function compressBanner(bitmap: ImageBitmap): Promise<Blob> {
    const MAX_WIDTH = 1600;
    const scale = bitmap.width > MAX_WIDTH ? MAX_WIDTH / bitmap.width : 1;
    const width = Math.round(bitmap.width * scale);
    const height = Math.round(bitmap.height * scale);

    const canvas = new OffscreenCanvas(width, height);
    const ctx = canvas.getContext('2d')!;
    ctx.drawImage(bitmap, 0, 0, width, height);

    return canvas.convertToBlob({ type: 'image/webp', quality: 0.8 });
}

// ─── OG Image ────────────────────────────────────────────────────────────────

async function generateOgImage(bitmap: ImageBitmap): Promise<Blob> {
    const targetRatio = OG_WIDTH / OG_HEIGHT; // 1.9047…
    const sourceRatio = bitmap.width / bitmap.height;

    let sx: number, sy: number, sw: number, sh: number;

    if (sourceRatio > targetRatio) {
        // Source is wider → crop horizontally (center)
        sh = bitmap.height;
        sw = Math.round(sh * targetRatio);
        sx = Math.round((bitmap.width - sw) / 2);
        sy = 0;
    } else {
        // Source is taller → crop vertically (top-biased, not pure center,
        // so the interesting part of a banner is usually at the top)
        sw = bitmap.width;
        sh = Math.round(sw / targetRatio);
        sx = 0;
        sy = Math.round((bitmap.height - sh) * 0.35); // 35% from top
    }

    const canvas = new OffscreenCanvas(OG_WIDTH, OG_HEIGHT);
    const ctx = canvas.getContext('2d')!;
    ctx.drawImage(bitmap, sx, sy, sw, sh, 0, 0, OG_WIDTH, OG_HEIGHT);

    // Target ≤300KB for WhatsApp. Start at q=0.75; if still too large, step down.
    for (const quality of [0.75, 0.65, 0.55, 0.45]) {
        const blob = await canvas.convertToBlob({
            type: 'image/webp',
            quality,
        });

        if (blob.size <= 300 * 1024) {
            return blob;
        }
    }

    // Last resort: lowest quality
    return canvas.convertToBlob({ type: 'image/webp', quality: 0.4 });
}
