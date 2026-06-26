<script setup lang="ts">
import { useHttp, router } from '@inertiajs/vue3';
import {
    FileDown,
    AlertTriangle,
    UserCheck,
    AlertCircleIcon,
} from '@lucide/vue';
import { ref } from 'vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import {
    importMethod as userImport,
    importTemplate as userImportTemplate,
} from '@/routes/users';

defineProps<{
    open: boolean;
}>();

const emit = defineEmits<{
    (e: 'update:open', val: boolean): void;
    (e: 'success'): void;
}>();

const importHttp = useHttp({
    file: null as File | null,
});

const importError = ref<string | null>(null);
const importSuccess = ref<string | null>(null);

function handleFileChange(e: Event) {
    importError.value = null;
    importSuccess.value = null;
    const target = e.target as HTMLInputElement;

    if (target.files && target.files.length > 0) {
        importHttp.file = target.files[0];
    }
}

function submitImport() {
    if (!importHttp.file) {
        importError.value = 'Silakan pilih file terlebih dahulu.';

        return;
    }

    importError.value = null;
    importSuccess.value = null;

    importHttp.post(userImport.url(), {
        onSuccess: (response: any) => {
            importSuccess.value = response.message || 'Data berhasil diimpor!';
            importHttp.reset();
            const fileInput = document.getElementById(
                'import-file-input',
            ) as HTMLInputElement;

            if (fileInput) {
                fileInput.value = '';
            }

            router.reload({ only: ['users', 'majors'] });
            emit('success');
        },
        onError: (errors: any) => {
            if (errors.file) {
                importError.value = errors.file;
            } else {
                importError.value =
                    'Gagal mengimpor data. Pastikan format file Anda sesuai.';
            }
        },
    });
}
</script>

<template>
    <Dialog :open="open" @update:open="(val) => emit('update:open', val)">
        <DialogContent class="sm:max-w-[450px]">
            <DialogHeader>
                <DialogTitle>Impor Data Mahasiswa</DialogTitle>
                <DialogDescription>
                    Impor data pengguna massal menggunakan file Excel (.xlsx)
                    atau CSV (.csv).
                </DialogDescription>
            </DialogHeader>

            <div class="space-y-4 py-3">
                <div
                    class="space-y-2 rounded-lg border border-emerald-500/20 bg-emerald-500/10 p-4 text-xs text-emerald-800 dark:text-emerald-300"
                >
                    <h4 class="flex items-center gap-2 font-bold">
                        <FileDown
                            class="h-4 w-4 text-emerald-600 dark:text-emerald-400"
                        />
                        Informasi & Ketentuan Impor:
                    </h4>
                    <ul class="list-disc space-y-1 pl-4 leading-relaxed">
                        <li>
                            Impor massal hanya diperuntukkan untuk pengguna
                            dengan peran <strong>Mahasiswa</strong>.
                        </li>
                        <li>
                            Data minimum yang wajib diisi pada baris kolom
                            adalah: <strong>name</strong>, <strong>nim</strong>,
                            dan <strong>kelas</strong>.
                        </li>
                        <li>
                            NIM mahasiswa akan secara otomatis digunakan sebagai
                            password default mereka.
                        </li>
                        <li>
                            Ukuran file maksimal yang didukung adalah
                            <strong>10MB</strong>.
                        </li>
                    </ul>
                    <div class="mt-2 border-t border-emerald-500/20 pt-2">
                        <a
                            :href="userImportTemplate.url()"
                            class="inline-flex items-center gap-1.5 font-bold text-emerald-700 underline transition-colors hover:text-emerald-800 dark:text-emerald-300 dark:hover:text-emerald-200"
                        >
                            <FileDown class="h-4 w-4" />
                            Unduh Template Impor (.xlsx)
                        </a>
                    </div>
                </div>

                <Alert variant="destructive">
                    <AlertCircleIcon />
                    <AlertTitle>XLSX File Parser Bug</AlertTitle>
                    <AlertDescription>
                        <p>
                            Meskipun XLSX didukung, import menggunakan file XLSX
                            cenderung error. Sebaiknya gunakan CSV sebagai
                            gantinya.
                        </p>
                    </AlertDescription>
                </Alert>

                <div class="space-y-2">
                    <Label for="import-file-input">Pilih File XLSX / CSV</Label>
                    <Input
                        id="import-file-input"
                        type="file"
                        accept=".xlsx,.csv"
                        @change="handleFileChange"
                        class="cursor-pointer"
                    />
                </div>

                <!-- Process indicator / Error / Success message -->
                <div
                    v-if="importHttp.processing"
                    class="flex items-center gap-3 rounded bg-muted/30 p-2 text-sm text-muted-foreground"
                >
                    <Spinner class="h-4 w-4 animate-spin" />
                    <span>Sedang mengimpor data, mohon tunggu...</span>
                </div>

                <div
                    v-if="importHttp.progress"
                    class="h-2 w-full overflow-hidden rounded bg-muted"
                >
                    <div
                        class="h-full bg-primary transition-all duration-300"
                        :style="`width: ${importHttp.progress.percentage}%`"
                    />
                </div>

                <div
                    v-if="importError"
                    class="flex items-start gap-2 rounded-lg border border-destructive/20 bg-destructive/10 p-3 text-xs font-semibold text-destructive"
                >
                    <AlertTriangle class="mt-0.5 h-4 w-4 shrink-0" />
                    <span>{{ importError }}</span>
                </div>

                <div
                    v-if="importSuccess"
                    class="flex items-start gap-2 rounded-lg border border-emerald-500/20 bg-emerald-500/10 p-3 text-xs font-semibold text-emerald-700 dark:text-emerald-400"
                >
                    <UserCheck class="mt-0.5 h-4 w-4 shrink-0" />
                    <span>{{ importSuccess }}</span>
                </div>
            </div>

            <DialogFooter>
                <Button
                    type="button"
                    variant="outline"
                    @click="emit('update:open', false)"
                    :disabled="importHttp.processing"
                    >Tutup</Button
                >
                <Button
                    @click="submitImport"
                    :disabled="importHttp.processing || !importHttp.file"
                >
                    <Spinner
                        v-if="importHttp.processing"
                        class="mr-2 h-4 w-4 animate-spin"
                    />
                    Mulai Impor
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
