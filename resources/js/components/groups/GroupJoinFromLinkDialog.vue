<script setup lang="ts">
import { router, useHttp } from '@inertiajs/vue3';
import { Crown, Users, Building2, XCircle } from '@lucide/vue';
import { ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Spinner } from '@/components/ui/spinner';
import { byCode as groupByCode, join as groupJoin } from '@/routes/groups';

const props = defineProps<{
    open: boolean;
    code: string;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
    (e: 'success'): void;
}>();

const inviteGroup = ref<any>(null);
const isFetchingInviteGroup = ref(false);
const inviteGroupError = ref('');
const isProcessing = ref(false);

const http = useHttp();

watch(
    [() => props.open, () => props.code],
    ([open, code]) => {
        if (open && code) {
            isFetchingInviteGroup.value = true;
            inviteGroupError.value = '';
            inviteGroup.value = null;

            http.get(groupByCode.url(code), {
                onSuccess: (response: any) => {
                    inviteGroup.value = response.group;
                    isFetchingInviteGroup.value = false;
                },
                onError: (errors: any) => {
                    inviteGroupError.value =
                        errors.error ||
                        'Gagal mengambil data kelompok. Kode undangan mungkin salah atau kelompok sudah dibubarkan.';
                    isFetchingInviteGroup.value = false;
                },
            });
        }
    },
    { immediate: true },
);

function confirmJoinFromLink() {
    isProcessing.value = true;
    router.post(
        groupJoin.url(),
        { code: props.code },
        {
            onFinish: () => {
                isProcessing.value = false;
                emit('update:open', false);
                emit('success');
            },
        },
    );
}
</script>

<template>
    <Dialog :open="open" @update:open="(val) => emit('update:open', val)">
        <DialogContent class="sm:max-w-[400px]">
            <DialogHeader>
                <DialogTitle>Bergabung ke Kelompok</DialogTitle>
                <DialogDescription v-if="!inviteGroupError">
                    Kamu diundang untuk bergabung ke kelompok magang berikut.
                </DialogDescription>
            </DialogHeader>

            <!-- Loading State -->
            <div
                v-if="isFetchingInviteGroup"
                class="flex flex-col items-center justify-center py-8"
            >
                <Spinner class="h-8 w-8 animate-spin text-primary" />
                <p class="mt-2 text-xs text-muted-foreground">
                    Memuat informasi kelompok...
                </p>
            </div>

            <!-- Error State -->
            <div v-else-if="inviteGroupError" class="space-y-4 py-2">
                <div
                    class="flex items-center gap-3 rounded-lg border border-destructive/25 bg-destructive/5 p-4 text-sm text-destructive"
                >
                    <XCircle class="h-5 w-5 shrink-0" />
                    <p>{{ inviteGroupError }}</p>
                </div>
            </div>

            <!-- Loaded State -->
            <div v-else-if="inviteGroup" class="space-y-4">
                <!-- Banner -->
                <div
                    class="relative h-32 w-full overflow-hidden rounded-xl border border-border/80"
                >
                    <img
                        :src="
                            inviteGroup.banner_url ??
                            '/assets/images/default-company-background.png'
                        "
                        alt="Banner Kelompok"
                        class="h-full w-full object-cover"
                    />
                    <div
                        class="absolute inset-0 bg-linear-to-t from-black/70 via-black/20 to-transparent"
                    />
                    <div class="absolute bottom-3 left-3 text-white">
                        <span
                            class="rounded-md bg-black/45 px-2 py-0.5 font-mono text-xs backdrop-blur-xs"
                        >
                            Kode: {{ inviteGroup.code }}
                        </span>
                    </div>
                </div>

                <!-- Info List -->
                <div
                    class="space-y-3 rounded-xl border border-border/80 bg-muted/20 p-4 text-sm"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary/10 text-primary"
                        >
                            <Crown class="h-4 w-4" />
                        </div>
                        <div>
                            <p
                                class="text-[10px] font-bold tracking-wider text-muted-foreground uppercase"
                            >
                                Ketua Kelompok
                            </p>
                            <p class="mt-0.5 font-medium text-foreground">
                                {{ inviteGroup.leader.name }}
                            </p>
                        </div>
                    </div>

                    <div
                        class="flex items-center gap-3 border-t border-border/40 pt-2.5"
                    >
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary/10 text-primary"
                        >
                            <Users class="h-4 w-4" />
                        </div>
                        <div>
                            <p
                                class="text-[10px] font-bold tracking-wider text-muted-foreground uppercase"
                            >
                                Anggota
                            </p>
                            <p class="mt-0.5 font-medium text-foreground">
                                {{ inviteGroup.members_count }} Orang
                            </p>
                        </div>
                    </div>

                    <div
                        v-if="inviteGroup.company_name"
                        class="flex items-center gap-3 border-t border-border/40 pt-2.5"
                    >
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary/10 text-primary"
                        >
                            <Building2 class="h-4 w-4" />
                        </div>
                        <div>
                            <p
                                class="text-[10px] font-bold tracking-wider text-muted-foreground uppercase"
                            >
                                Perusahaan Tujuan
                            </p>
                            <p class="mt-0.5 font-medium text-foreground">
                                {{ inviteGroup.company_name }}
                            </p>
                        </div>
                    </div>
                </div>

                <p class="px-2 text-center text-xs text-muted-foreground">
                    Kirim permintaan bergabung ke kelompok ini?
                </p>
            </div>

            <DialogFooter>
                <Button variant="outline" @click="emit('update:open', false)"
                    >Batal</Button
                >
                <Button
                    v-if="!inviteGroupError"
                    id="btn-confirm-join-from-link"
                    @click="confirmJoinFromLink"
                    :disabled="isProcessing || isFetchingInviteGroup"
                >
                    <Spinner
                        v-if="isProcessing"
                        class="mr-2 h-4 w-4 animate-spin"
                    />
                    Kirim Permintaan
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
