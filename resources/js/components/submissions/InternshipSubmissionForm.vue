<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import {
    parseDate,
    DateFormatter,
    getLocalTimeZone,
    today,
} from '@internationalized/date';
import {
    AlertCircle,
    Building2,
    CircleQuestionMark,
    FileText,
    Phone,
    Calendar,
    MapPin,
    Save,
    Send,
    Briefcase,
    Laptop,
} from '@lucide/vue';
import { ref, computed, watch } from 'vue';
import GoogleMapLink from '@/components/GoogleMapLink.vue';
import { Alert, AlertTitle, AlertDescription } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Calendar as CalendarComponent } from '@/components/ui/calendar';
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
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Spinner } from '@/components/ui/spinner';
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip';
import { cn } from '@/lib/utils';
import {
    store as submissionStore,
    submit as submissionSubmit,
} from '@/routes/groups/submissions';
import type { Group } from '@/types';

const props = defineProps<{
    group: Group;
    isLeader: boolean;
    isSubmissionEditable: boolean;
    groupStatusLabel: string;
    statusDescription: string;
}>();

const showSubmitConfirm = ref(false);
const isProcessing = ref(false);

const submissionForm = useForm({
    company_name: props.group?.active_submission?.company_name ?? '',
    company_address: props.group?.active_submission?.company_address ?? '',
    company_contact: props.group?.active_submission?.company_contact ?? '',
    division: props.group?.active_submission?.division ?? '',
    field_of_interest: props.group?.active_submission?.field_of_interest ?? '',
    company_type: props.group?.active_submission?.company_type ?? '',
    working_model: props.group?.active_submission?.working_model ?? '',
    start_date: props.group?.active_submission?.start_date
        ? props.group.active_submission.start_date.substring(0, 10)
        : '',
    end_date: props.group?.active_submission?.end_date
        ? props.group.active_submission.end_date.substring(0, 10)
        : '',
});

watch(
    () => props.group?.active_submission,
    (newSub) => {
        submissionForm.company_name = newSub?.company_name ?? '';
        submissionForm.company_address = newSub?.company_address ?? '';
        submissionForm.company_contact = newSub?.company_contact ?? '';
        submissionForm.division = newSub?.division ?? '';
        submissionForm.field_of_interest = newSub?.field_of_interest ?? '';
        submissionForm.company_type = newSub?.company_type ?? '';
        submissionForm.working_model = newSub?.working_model ?? '';
        submissionForm.start_date = newSub?.start_date
            ? newSub.start_date.substring(0, 10)
            : '';
        submissionForm.end_date = newSub?.end_date
            ? newSub.end_date.substring(0, 10)
            : '';
    },
    { deep: true },
);

const dateFormatter = new DateFormatter('id-ID', {
    dateStyle: 'long',
});

const startDateValue = computed({
    get: () => {
        return submissionForm.start_date
            ? parseDate(submissionForm.start_date)
            : undefined;
    },
    set: (val) => {
        submissionForm.start_date = val ? val.toString() : '';
    },
});

const endDateValue = computed({
    get: () => {
        return submissionForm.end_date
            ? parseDate(submissionForm.end_date)
            : undefined;
    },
    set: (val) => {
        submissionForm.end_date = val ? val.toString() : '';
    },
});

const minStartDate = computed(() => today(getLocalTimeZone()));

const minEndDate = computed(() => {
    if (startDateValue.value) {
        return startDateValue.value.add({ days: 1 });
    }

    return today(getLocalTimeZone()).add({ days: 1 });
});

watch(
    () => submissionForm.start_date,
    (newStartDate) => {
        if (newStartDate && submissionForm.end_date) {
            try {
                const start = parseDate(newStartDate);
                const end = parseDate(submissionForm.end_date);

                if (start.compare(end) >= 0) {
                    submissionForm.end_date = '';
                }
            } catch {
                // ignore
            }
        }
    }
);

function saveSubmissionDraft() {
    isProcessing.value = true;
    submissionForm.post(submissionStore.url(), {
        preserveScroll: true,
        onFinish: () => {
            isProcessing.value = false;
        },
    });
}

function submitSubmissionProposal() {
    isProcessing.value = true;
    submissionForm.post(submissionSubmit.url(), {
        preserveScroll: true,
        onSuccess: () => {
            showSubmitConfirm.value = false;
        },
        onFinish: () => {
            isProcessing.value = false;
        },
    });
}
</script>

<template>
    <div class="space-y-6">
        <!-- Status Banner -->
        <Alert
            v-if="
                group.status !== 'forming' &&
                group.status !== 'company_rejected'
            "
            variant="primary"
        >
            <AlertCircle />
            <AlertTitle>
                Status Pengajuan Magang: {{ groupStatusLabel }}
            </AlertTitle>
            <AlertDescription>
                {{ statusDescription }}
            </AlertDescription>
        </Alert>
        <Alert v-else variant="warning">
            <AlertCircle />
            <AlertTitle v-if="isLeader">
                Persiapan Pengajuan Magang
            </AlertTitle>
            <AlertTitle v-else> Menunggu Pengajuan Ketua Kelompok </AlertTitle>
            <AlertDescription v-if="isLeader">
                Isi data instansi/perusahaan tujuan magang di bawah ini. Setelah
                diajukan, data dan keanggotaan akan
                <strong>dikunci</strong>.
            </AlertDescription>
            <AlertDescription v-else>
                Draf data pengajuan sedang diisi oleh ketua kelompok ({{
                    group.leader.name
                }}).
            </AlertDescription>
        </Alert>

        <!-- Form -->
        <form @submit.prevent class="space-y-6">
            <div class="grid gap-4 sm:grid-cols-3">
                <div class="space-y-1.5">
                    <Label
                        for="company_name"
                        class="flex items-center gap-1.5 text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                        required
                    >
                        <Building2 class="h-3.5 w-3.5" />
                        Nama Perusahaan / Instansi
                    </Label>
                    <Input
                        id="company_name"
                        v-model="submissionForm.company_name"
                        placeholder="Contoh: PT Teknologi Nusantara"
                        :disabled="!isSubmissionEditable"
                    />
                    <span
                        v-if="submissionForm.errors.company_name"
                        class="text-xs text-destructive"
                    >
                        {{ submissionForm.errors.company_name }}
                    </span>
                </div>

                <div class="space-y-1.5">
                    <Label
                        for="field_of_interest"
                        class="flex items-center gap-1.5 text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                        required
                    >
                        <FileText class="h-3.5 w-3.5" />
                        Bidang yang Diminati
                    </Label>
                    <Input
                        id="field_of_interest"
                        v-model="submissionForm.field_of_interest"
                        placeholder="Contoh: Web Developer, UI/UX"
                        :disabled="!isSubmissionEditable"
                    />
                    <span
                        v-if="submissionForm.errors.field_of_interest"
                        class="text-xs text-destructive"
                    >
                        {{ submissionForm.errors.field_of_interest }}
                    </span>
                </div>

                <div class="space-y-1.5">
                    <Label
                        for="company_type"
                        class="flex items-center gap-1.5 text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                        required
                    >
                        <Briefcase class="h-3.5 w-3.5" />
                        Tipe Perusahaan
                    </Label>
                    <Select v-model="submissionForm.company_type" :disabled="!isSubmissionEditable">
                        <SelectTrigger id="company_type" class="w-full">
                            <SelectValue placeholder="Pilih Tipe Perusahaan" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectGroup>
                                <SelectItem value="Multinasional">Multinasional</SelectItem>
                                <SelectItem value="Nasional">Nasional</SelectItem>
                                <SelectItem value="Startup Teknologi">Startup Teknologi</SelectItem>
                            </SelectGroup>
                        </SelectContent>
                    </Select>
                    <span
                        v-if="submissionForm.errors.company_type"
                        class="text-xs text-destructive"
                    >
                        {{ submissionForm.errors.company_type }}
                    </span>
                </div>

                <div class="space-y-1.5">
                    <Label
                        for="working_model"
                        class="flex items-center gap-1.5 text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                        required
                    >
                        <Laptop class="h-3.5 w-3.5" />
                        Model Pengerjaan Magang
                        <Tooltip>
                            <TooltipTrigger>
                                <CircleQuestionMark class="size-3.5 cursor-help" />
                            </TooltipTrigger>
                            <TooltipContent>
                                <p>
                                    Model Pengerjaan Magang: 
                                    <br>
                                    WFO: Work From Office (Bekerja di kantor)
                                    <br>
                                    WFA: Work From Anywhere (Bekerja di manapun)
                                    <br>
                                    Hybrid: Campuran WFO dan WFA
                                </p>
                            </TooltipContent>
                        </Tooltip>
                    </Label>
                    <Select v-model="submissionForm.working_model" :disabled="!isSubmissionEditable">
                        <SelectTrigger id="working_model" class="w-full">
                            <SelectValue placeholder="Pilih Model Pengerjaan" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectGroup>
                                <SelectItem value="WFO">WFO</SelectItem>
                                <SelectItem value="WFA">WFA</SelectItem>
                                <SelectItem value="Hybrid">Hybrid</SelectItem>
                            </SelectGroup>
                        </SelectContent>
                    </Select>
                    <span
                        v-if="submissionForm.errors.working_model"
                        class="text-xs text-destructive"
                    >
                        {{ submissionForm.errors.working_model }}
                    </span>
                </div>

                <div class="space-y-1.5">
                    <Label
                        for="division"
                        class="flex items-center gap-1.5 text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                    >
                        <Building2 class="h-3.5 w-3.5" />
                        Divisi Pekerjaan (Opsional)
                    </Label>
                    <Input
                        id="division"
                        v-model="submissionForm.division"
                        placeholder="Contoh: Frontend, Backend"
                        :disabled="!isSubmissionEditable"
                    />
                    <span
                        v-if="submissionForm.errors.division"
                        class="text-xs text-destructive"
                    >
                        {{ submissionForm.errors.division }}
                    </span>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div class="space-y-1.5">
                    <Label
                        for="company_contact"
                        class="flex items-center gap-1.5 text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                        required
                    >
                        <Phone class="h-3.5 w-3.5" />
                        Kontak Instansi (No. Telp / Email)
                    </Label>
                    <Input
                        id="company_contact"
                        v-model="submissionForm.company_contact"
                        placeholder="Contoh: hr@company.com / 021-xxxxxx"
                        :disabled="!isSubmissionEditable"
                    />
                    <span
                        v-if="submissionForm.errors.company_contact"
                        class="text-xs text-destructive"
                    >
                        {{ submissionForm.errors.company_contact }}
                    </span>
                </div>

                <div class="space-y-1.5">
                    <Label
                        for="start_date"
                        class="flex items-center gap-1.5 text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                        required
                    >
                        <Calendar class="h-3.5 w-3.5" />
                        Tanggal Mulai
                    </Label>
                    <Popover v-slot="{ close }">
                        <PopoverTrigger as-child>
                            <Button
                                id="start_date"
                                variant="outline"
                                role="combobox"
                                :class="
                                    cn(
                                        'h-10 w-full justify-start text-left font-normal',
                                        !submissionForm.start_date &&
                                            'text-muted-foreground',
                                    )
                                "
                                :disabled="!isSubmissionEditable"
                            >
                                <Calendar
                                    class="mr-2 h-4 w-4 text-muted-foreground"
                                />
                                <span>
                                    {{
                                        submissionForm.start_date
                                            ? dateFormatter.format(
                                                  startDateValue!.toDate(
                                                      getLocalTimeZone(),
                                                  ),
                                              )
                                            : 'Pilih tanggal...'
                                    }}
                                </span>
                            </Button>
                        </PopoverTrigger>
                        <PopoverContent class="w-auto p-0" align="start">
                            <CalendarComponent
                                locale="id-ID"
                                v-model="startDateValue"
                                :min-value="minStartDate"
                                initial-focus
                                @update:model-value="close"
                            />
                        </PopoverContent>
                    </Popover>
                    <span
                        v-if="submissionForm.errors.start_date"
                        class="text-xs text-destructive"
                    >
                        {{ submissionForm.errors.start_date }}
                    </span>
                </div>

                <div class="space-y-1.5">
                    <Label
                        for="end_date"
                        class="flex items-center gap-1.5 text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                        required
                    >
                        <Calendar class="h-3.5 w-3.5" />
                        Tanggal Selesai
                    </Label>
                    <Popover v-slot="{ close }">
                        <PopoverTrigger as-child>
                            <Button
                                id="end_date"
                                variant="outline"
                                role="combobox"
                                :class="
                                    cn(
                                        'h-10 w-full justify-start text-left font-normal',
                                        !submissionForm.end_date &&
                                            'text-muted-foreground',
                                    )
                                "
                                :disabled="!isSubmissionEditable"
                            >
                                <Calendar
                                    class="mr-2 h-4 w-4 text-muted-foreground"
                                />
                                <span>
                                    {{
                                        submissionForm.end_date
                                            ? dateFormatter.format(
                                                  endDateValue!.toDate(
                                                      getLocalTimeZone(),
                                                  ),
                                              )
                                            : 'Pilih tanggal...'
                                    }}
                                </span>
                            </Button>
                        </PopoverTrigger>
                        <PopoverContent class="w-auto p-0" align="start">
                            <CalendarComponent
                                locale="id-ID"
                                v-model="endDateValue"
                                :min-value="minEndDate"
                                initial-focus
                                @update:model-value="close"
                            />
                        </PopoverContent>
                    </Popover>
                    <span
                        v-if="submissionForm.errors.end_date"
                        class="text-xs text-destructive"
                    >
                        {{ submissionForm.errors.end_date }}
                    </span>
                </div>
            </div>

            <div class="space-y-1.5">
                <Label
                    for="company_address"
                    class="flex items-center gap-1.5 text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                    required
                >
                    <MapPin class="h-3.5 w-3.5" />
                    Alamat Lengkap Perusahaan
                </Label>
                <textarea
                    id="company_address"
                    v-model="submissionForm.company_address"
                    placeholder="Contoh: Jl. Jenderal Sudirman No. 12, Jakarta Selatan"
                    rows="3"
                    class="flex min-h-[80px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                    :disabled="!isSubmissionEditable"
                ></textarea>
                <span
                    v-if="submissionForm.errors.company_address"
                    class="text-xs text-destructive"
                >
                    {{ submissionForm.errors.company_address }}
                </span>
            </div>

                <GoogleMapLink 
                    v-if="submissionForm.company_name"
                    :query="submissionForm.company_name" 
                    :text="`Lihat ${submissionForm.company_name} di Google Maps`"
                    class="mb-2"
                />
                <GoogleMapLink 
                    v-if="submissionForm.company_address"
                    :query="submissionForm.company_address" 
                    :text="`Lihat ${submissionForm.company_address} di Google Maps`"
                />

            <div
                v-if="isSubmissionEditable"
                class="flex justify-end gap-3 border-t border-border pt-4"
            >
                <Button
                    type="button"
                    variant="outline"
                    @click="saveSubmissionDraft"
                    :disabled="isProcessing"
                    id="btn-save-draft"
                >
                    <Spinner
                        v-if="isProcessing"
                        class="mr-2 h-4 w-4 animate-spin"
                    />
                    <Save v-else class="mr-2 h-4 w-4" />
                    Simpan Draf
                </Button>
                <Button
                    type="button"
                    @click="showSubmitConfirm = true"
                    :disabled="isProcessing"
                    id="btn-submit-proposal"
                >
                    <Send class="mr-2 h-4 w-4" />
                    Ajukan Magang
                </Button>
            </div>
        </form>

        <!-- Submit Proposal Confirm Dialog -->
        <Dialog v-model:open="showSubmitConfirm">
            <DialogContent class="sm:max-w-[400px]">
                <DialogHeader>
                    <DialogTitle>Ajukan Permohonan Magang</DialogTitle>
                    <DialogDescription>
                        Apakah Anda yakin ingin mengajukan permohonan magang?
                        <span class="mt-2 block font-semibold text-destructive">
                            Tindakan ini akan mengirimkan data pengajuan ke
                            program studi dan mengunci komposisi anggota
                            kelompok serta data perusahaan.
                        </span>
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button variant="outline" @click="showSubmitConfirm = false"
                        >Batal</Button
                    >
                    <Button
                        id="btn-confirm-submit-proposal"
                        @click="submitSubmissionProposal"
                        :disabled="isProcessing"
                    >
                        <Spinner
                            v-if="isProcessing"
                            class="mr-2 h-4 w-4 animate-spin"
                        />
                        Ya, Ajukan
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
