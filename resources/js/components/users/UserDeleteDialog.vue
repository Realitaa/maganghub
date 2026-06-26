<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ShieldAlert } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { destroy as userDestroy } from '@/routes/users';
import type { User } from '@/types';

const props = defineProps<{
    open: boolean;
    user: User | null;
}>();

const emit = defineEmits<{
    (e: 'update:open', val: boolean): void;
    (e: 'success'): void;
}>();

function submitDelete() {
    if (props.user) {
        router.delete(userDestroy.url(props.user.id), {
            onSuccess: () => {
                emit('update:open', false);
                emit('success');
            },
        });
    }
}
</script>

<template>
    <Dialog :open="open" @update:open="(val) => emit('update:open', val)">
        <DialogContent class="sm:max-w-[400px]">
            <DialogHeader>
                <DialogTitle class="flex items-center gap-2 text-destructive">
                    <ShieldAlert class="h-5 w-5" />
                    Konfirmasi Hapus Pengguna
                </DialogTitle>
                <DialogDescription>
                    Apakah Anda yakin ingin menghapus pengguna ini? Tindakan ini
                    tidak dapat dibatalkan dan akan menghapus seluruh data
                    terkait.
                </DialogDescription>
            </DialogHeader>

            <div v-if="user" class="py-2 text-sm">
                <div class="grid grid-cols-3 py-1 text-muted-foreground">
                    <span class="font-semibold">Nama:</span>
                    <span class="col-span-2 text-foreground">{{
                        user.name
                    }}</span>
                </div>
                <div class="grid grid-cols-3 py-1 text-muted-foreground">
                    <span class="font-semibold">Email:</span>
                    <span class="col-span-2 text-foreground">{{
                        user.email
                    }}</span>
                </div>
                <div class="grid grid-cols-3 py-1 text-muted-foreground">
                    <span class="font-semibold">NIM:</span>
                    <span class="col-span-2 text-foreground">{{
                        user.nim || '-'
                    }}</span>
                </div>
            </div>

            <DialogFooter>
                <Button
                    type="button"
                    variant="outline"
                    @click="emit('update:open', false)"
                    >Batal</Button
                >
                <Button variant="destructive" @click="submitDelete"
                    >Hapus</Button
                >
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
