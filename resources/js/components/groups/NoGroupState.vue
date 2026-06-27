<script setup lang="ts">
import { router, Link, usePage } from '@inertiajs/vue3';
import {
    Lock,
    CheckCircle2,
    XCircle,
    Plus,
    LogIn,
    Clock,
    UserX,
} from '@lucide/vue';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
    CardDescription,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { store as groupStore, join as groupJoin } from '@/routes/groups';
import { cancel as cancelRequest } from '@/routes/groups/join-requests';
import { edit as editProfile } from '@/routes/profile';
import { edit as editSecurity } from '@/routes/security';

import type { PendingJoinRequest } from '@/types';

defineProps<{
    isLocked: boolean;
    pendingJoinRequests: PendingJoinRequest[];
}>();

const joinCode = ref('');
const isProcessing = ref(false);
const page = usePage();

function createGroup() {
    isProcessing.value = true;
    router.post(
        groupStore.url(),
        {},
        {
            onFinish: () => {
                isProcessing.value = false;
            },
        },
    );
}

function sendJoinRequest() {
    if (!joinCode.value.trim()) {
        return;
    }

    isProcessing.value = true;
    router.post(
        groupJoin.url(),
        { code: joinCode.value.trim().toUpperCase() },
        {
            onFinish: () => {
                isProcessing.value = false;
                joinCode.value = '';
            },
        },
    );
}

function cancelJoinRequest(requestId: number) {
    router.delete(cancelRequest.url(requestId));
}
</script>

<template>
    <div class="space-y-6 p-4 pt-6 md:p-8">
        <!-- Locked State Warning -->
        <div v-if="isLocked" class="flex justify-center py-8">
            <Card
                class="w-full max-w-2xl border-destructive/30 bg-destructive/5"
            >
                <CardHeader class="pb-3 text-center">
                    <div
                        class="mx-auto mb-2 flex h-12 w-12 items-center justify-center rounded-full bg-destructive/10 p-3 text-destructive"
                    >
                        <Lock class="h-6 w-6" />
                    </div>
                    <CardTitle class="text-xl font-bold"
                        >Akses Terkunci</CardTitle
                    >
                    <CardDescription>
                        Anda belum memenuhi persyaratan untuk dapat mengakses
                        fitur kelompok magang.
                    </CardDescription>
                </CardHeader>
                <CardContent class="space-y-6 text-center">
                    <p class="text-sm leading-relaxed text-muted-foreground">
                        Untuk dapat bergabung atau membuat kelompok magang baru,
                        Anda wajib:
                    </p>
                    <div
                        class="flex flex-col items-center gap-3 text-sm font-medium"
                    >
                        <div class="flex items-center gap-2">
                            <component
                                :is="
                                    page.props.auth.requirements
                                        ?.password_changed
                                        ? CheckCircle2
                                        : XCircle
                                "
                                class="h-5 w-5"
                                :class="
                                    page.props.auth.requirements
                                        ?.password_changed
                                        ? 'text-green-500'
                                        : 'text-destructive'
                                "
                            />
                            <span>Mengubah password default</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <component
                                :is="
                                    page.props.auth.requirements
                                        ?.profile_completed
                                        ? CheckCircle2
                                        : XCircle
                                "
                                class="h-5 w-5"
                                :class="
                                    page.props.auth.requirements
                                        ?.profile_completed
                                        ? 'text-green-500'
                                        : 'text-destructive'
                                "
                            />
                            <span>Melengkapi data biodata mahasiswa</span>
                        </div>
                    </div>
                    <div
                        class="flex flex-col justify-center gap-3 pt-4 sm:flex-row"
                    >
                        <Link
                            v-if="
                                !page.props.auth.requirements?.password_changed
                            "
                            :href="editSecurity()"
                        >
                            <Button variant="destructive" id="btn-lock-password"
                                >Ubah Password</Button
                            >
                        </Link>
                        <Link
                            v-if="
                                !page.props.auth.requirements?.profile_completed
                            "
                            :href="editProfile()"
                        >
                            <Button
                                variant="outline"
                                class="border-primary/30 text-primary"
                                id="btn-lock-profile"
                            >
                                Lengkapi Biodata
                            </Button>
                        </Link>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Normal State (Unlocked) -->
        <div v-else class="grid items-start gap-8 lg:grid-cols-2">
            <!-- Left Column: Actions -->
            <div class="space-y-8">
                <div class="space-y-2">
                    <h1 class="text-3xl font-bold tracking-tight">
                        Selamat Datang!
                    </h1>
                    <p class="text-base text-muted-foreground">
                        Buat kelompok magang baru atau bergabung ke kelompok
                        yang sudah ada.
                    </p>
                </div>

                <!-- Create Group Card -->
                <Card
                    class="border-2 border-primary/20 transition-colors hover:border-primary/40"
                >
                    <CardContent class="space-y-4 p-6">
                        <div class="flex items-center gap-3">
                            <div
                                class="rounded-xl bg-primary/10 p-2.5 text-primary"
                            >
                                <Plus class="h-5 w-5" />
                            </div>
                            <div>
                                <h2 class="text-base font-semibold">
                                    Buat Kelompok Baru
                                </h2>
                                <p class="text-xs text-muted-foreground">
                                    Kamu akan menjadi ketua kelompok
                                </p>
                            </div>
                        </div>
                        <Button
                            id="btn-create-group"
                            class="w-full"
                            @click="createGroup"
                            :disabled="isProcessing"
                        >
                            <Spinner
                                v-if="isProcessing"
                                class="mr-2 h-4 w-4 animate-spin"
                            />
                            <Plus v-else class="mr-2 h-4 w-4" />
                            Buat Kelompok Magang
                        </Button>
                    </CardContent>
                </Card>

                <!-- Join Group Card -->
                <Card
                    class="border-2 border-border transition-colors hover:border-primary/20"
                >
                    <CardContent class="space-y-4 p-6">
                        <div class="flex items-center gap-3">
                            <div
                                class="rounded-xl bg-secondary p-2.5 text-secondary-foreground"
                            >
                                <LogIn class="h-5 w-5" />
                            </div>
                            <div>
                                <h2 class="text-base font-semibold">
                                    Gabung ke Kelompok
                                </h2>
                                <p class="text-xs text-muted-foreground">
                                    Masukkan kode kelompok dari teman kamu
                                </p>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <div class="flex-1 space-y-1.5">
                                <Label for="join-code" class="sr-only"
                                    >Kode Kelompok</Label
                                >
                                <Input
                                    id="join-code"
                                    v-model="joinCode"
                                    placeholder="Contoh: ABCDE12345"
                                    class="font-mono tracking-widest uppercase"
                                    maxlength="10"
                                    autocomplete="off"
                                    @keydown.enter="sendJoinRequest"
                                />
                            </div>
                            <Button
                                id="btn-join-group"
                                variant="outline"
                                @click="sendJoinRequest"
                                :disabled="!joinCode.trim() || isProcessing"
                            >
                                <Spinner
                                    v-if="isProcessing"
                                    class="h-4 w-4 animate-spin"
                                />
                                <span v-else>Kirim</span>
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Right Column: Info / Pending requests -->
            <Card
                v-if="pendingJoinRequests.length === 0"
                class="border-primary/20 bg-linear-to-br from-primary/5 to-primary/10"
            >
                <CardContent class="space-y-6 p-8">
                    <div class="space-y-1">
                        <h3 class="text-lg font-semibold">
                            Cara Bergabung ke Kelompok
                        </h3>
                        <p class="text-sm text-muted-foreground">
                            Ikuti langkah-langkah berikut:
                        </p>
                    </div>
                    <ol class="space-y-4 text-sm">
                        <li class="flex gap-3">
                            <span
                                class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary/15 text-xs font-bold text-primary"
                                >1</span
                            >
                            <span class="pt-0.5 text-muted-foreground">
                                Minta ketua kelompok untuk berbagi
                                <strong class="text-foreground"
                                    >kode kelompok</strong
                                >
                                atau link undangan.
                            </span>
                        </li>
                        <li class="flex gap-3">
                            <span
                                class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary/15 text-xs font-bold text-primary"
                                >2</span
                            >
                            <span class="pt-0.5 text-muted-foreground">
                                Masukkan kode di kolom "Gabung ke Kelompok" dan
                                klik
                                <strong class="text-foreground">Kirim</strong>.
                            </span>
                        </li>
                        <li class="flex gap-3">
                            <span
                                class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary/15 text-xs font-bold text-primary"
                                >3</span
                            >
                            <span class="pt-0.5 text-muted-foreground">
                                Tunggu ketua kelompok
                                <strong class="text-foreground"
                                    >menyetujui permintaanmu</strong
                                >.
                            </span>
                        </li>
                    </ol>
                    <div class="border-t border-primary/10 pt-2">
                        <p class="text-xs text-muted-foreground">
                            Setiap mahasiswa hanya dapat bergabung ke
                            <strong>satu kelompok aktif</strong>.
                        </p>
                    </div>
                </CardContent>
            </Card>

            <Card v-else class="border-border">
                <CardHeader class="space-y-1">
                    <CardTitle class="text-lg"
                        >Permintaan Bergabung Terkirim</CardTitle
                    >
                    <CardDescription>
                        Permintaanmu sedang menunggu persetujuan dari ketua
                        kelompok.
                    </CardDescription>
                </CardHeader>
                <CardContent class="space-y-3">
                    <div
                        v-for="req in pendingJoinRequests"
                        :key="req.id"
                        class="flex flex-col gap-4 rounded-xl border border-border/70 bg-muted/20 p-4 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div class="flex items-center gap-3">
                            <div class="rounded-lg bg-yellow-500/10 p-2">
                                <Clock
                                    class="h-5 w-5 text-yellow-600 dark:text-yellow-400"
                                />
                            </div>
                            <div>
                                <p class="text-sm font-semibold">
                                    Kelompok
                                    <span class="font-mono">{{
                                        req.group.code
                                    }}</span>
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    Ketua: {{ req.group.leader.name }}
                                </p>
                            </div>
                        </div>
                        <Button
                            variant="ghost"
                            size="sm"
                            class="justify-start text-destructive hover:bg-destructive/10 hover:text-destructive sm:justify-center"
                            @click="cancelJoinRequest(req.id)"
                        >
                            <UserX class="mr-1.5 h-4 w-4" />
                            Batalkan
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
