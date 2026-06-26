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
import { destroy as groupDestroy } from '@/routes/groups';

defineProps<{
    open: boolean;
    groupId: number;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
    (e: 'success'): void;
}>();

const isProcessing = ref(false);

function disbandGroup(id: number) {
    isProcessing.value = true;
    router.delete(groupDestroy.url(id), {
        onFinish: () => {
            isProcessing.value = false;
            emit('update:open', false);
            emit('success');
        },
        preserveState: false,
    });
}
</script>

<template>
    <Dialog :open="open" @update:open="(val) => emit('update:open', val)">
        <DialogContent class="sm:max-w-[400px]">
            <DialogHeader>
                <DialogTitle class="text-destructive"
                    >Bubarkan Kelompok</DialogTitle
                >
                <DialogDescription>
                    Tindakan ini akan membubarkan kelompok dan menghapus seluruh
                    data anggota. Ini tidak dapat dibatalkan.
                </DialogDescription>
            </DialogHeader>
            <DialogFooter>
                <Button variant="outline" @click="emit('update:open', false)"
                    >Batal</Button
                >
                <Button
                    id="btn-confirm-disband"
                    variant="destructive"
                    @click="disbandGroup(groupId)"
                    :disabled="isProcessing"
                >
                    <Spinner
                        v-if="isProcessing"
                        class="mr-2 h-4 w-4 animate-spin"
                    />
                    Ya, Bubarkan
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
