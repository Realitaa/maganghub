<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
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
import { leave as groupLeave } from '@/routes/groups';

const props = defineProps<{
    open: boolean;
    groupId: number;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
    (e: 'success'): void;
}>();

const isProcessing = ref(false);

function leaveGroup() {
    isProcessing.value = true;
    router.post(
        groupLeave.url(props.groupId),
        {},
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
                <DialogTitle>Keluar dari Kelompok</DialogTitle>
                <DialogDescription>
                    Apakah kamu yakin ingin keluar dari kelompok ini? Kamu harus
                    mengirim permintaan baru jika ingin bergabung lagi.
                </DialogDescription>
            </DialogHeader>
            <DialogFooter>
                <Button variant="outline" @click="emit('update:open', false)"
                    >Batal</Button
                >
                <Button
                    id="btn-confirm-leave"
                    variant="destructive"
                    @click="leaveGroup"
                    :disabled="isProcessing"
                >
                    <Spinner
                        v-if="isProcessing"
                        class="mr-2 h-4 w-4 animate-spin"
                    />
                    Ya, Keluar
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
