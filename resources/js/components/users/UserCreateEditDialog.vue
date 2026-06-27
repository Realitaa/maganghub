<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ChevronDown } from '@lucide/vue';
import { ref, watch } from 'vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import {
    Autocomplete,
    AutocompleteAnchor,
    AutocompleteContent,
    AutocompleteEmpty,
    AutocompleteGroup,
    AutocompleteItem,
    AutocompleteInput,
    AutocompleteTrigger,
    AutocompleteViewport,
} from '@/components/ui/autocomplete';
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
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Spinner } from '@/components/ui/spinner';
import { Textarea } from '@/components/ui/textarea';
import { store as userStore, update as userUpdate } from '@/routes/users';
import type { User } from '@/types';

const props = defineProps<{
    open: boolean;
    user: User | null;
    classes: { id: number; name: string }[];
}>();

const emit = defineEmits<{
    (e: 'update:open', val: boolean): void;
    (e: 'success'): void;
}>();

const form = useForm({
    id: null as number | null,
    name: '',
    email: '',
    role: 'student',
    nim: '',
    gender: 'L' as 'L' | 'P',
    phone: '',
    address: '',
    password: '',
    student_class_id: 'none',
});

// Watch when user prop changes to populate form values
watch(
    () => props.user,
    (val) => {
        form.clearErrors();

        if (val) {
            form.id = val.id;
            form.name = val.name;
            form.email = val.email;
            form.role = val.role;
            form.nim = val.nim || '';
            form.gender = val.gender || 'L';
            form.phone = val.phone || '';
            form.address = val.address || '';
            form.password = ''; // Keep password blank unless changing
            form.student_class_id = val.student_class_id
                ? val.student_class_id.toString()
                : 'none';
        } else {
            form.reset();
            form.id = null;
            form.role = 'student';
            form.gender = 'L';
            form.student_class_id = 'none';
        }
    },
    { immediate: true },
);

const classSearchText = ref('');
const isClassOpen = ref(false);

watch(
    () => form.student_class_id,
    (val) => {
        if (val === 'none') {
            classSearchText.value = '';
        } else {
            const found = props.classes.find((c) => c.id.toString() === val);
            classSearchText.value = found ? found.name : '';
        }
    },
    { immediate: true },
);

watch(classSearchText, (newVal) => {
    if (!newVal) {
        form.student_class_id = 'none';
    }
});

function submitForm() {
    if (form.id) {
        form.patch(userUpdate.url(form.id), {
            onSuccess: () => {
                emit('update:open', false);
                form.reset();
                emit('success');
            },
        });
    } else {
        form.post(userStore.url(), {
            onSuccess: () => {
                emit('update:open', false);
                form.reset();
                emit('success');
            },
        });
    }
}
</script>

<template>
    <Dialog :open="open" @update:open="(val) => emit('update:open', val)">
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle>{{
                    form.id ? 'Edit Pengguna' : 'Tambah Pengguna Baru'
                }}</DialogTitle>
                <DialogDescription>
                    Isi form di bawah ini untuk
                    {{
                        form.id
                            ? 'memperbarui data pengguna'
                            : 'menambahkan pengguna baru ke sistem'
                    }}.
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submitForm" class="space-y-4 py-2">
                <div class="space-y-1.5">
                    <Label for="form-name" required>Nama Lengkap</Label>
                    <Input
                        id="form-name"
                        v-model="form.name"
                        placeholder="Nama Lengkap"
                        required
                    />
                    <InputError :message="form.errors.name" />
                </div>

                <div class="space-y-1.5">
                    <Label for="form-email">Email</Label>
                    <Input
                        id="form-email"
                        type="email"
                        v-model="form.email"
                        placeholder="nama@domain.com"
                    />
                    <InputError :message="form.errors.email" />
                </div>

                <div class="space-y-1.5">
                    <Label required>Peran Pengguna</Label>
                    <Select v-model="form.role">
                        <SelectTrigger class="w-full">
                            <SelectValue placeholder="Pilih Peran" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectGroup>
                                <SelectItem value="student"
                                    >Mahasiswa</SelectItem
                                >
                                <SelectItem value="operator"
                                    >Operator</SelectItem
                                >
                                <SelectItem value="administrator"
                                    >Administrator</SelectItem
                                >
                            </SelectGroup>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.role" />
                </div>

                <!-- Dynamic fields for student -->
                <div class="grid grid-cols-2 gap-3" v-if="form.role === 'student'">
                    <div class="space-y-1.5">
                        <Label for="form-nim" required>NIM</Label>
                        <Input
                            id="form-nim"
                            v-model="form.nim"
                            placeholder="Nomor Induk Mahasiswa"
                            required
                        />
                        <InputError :message="form.errors.nim" />
                    </div>
                    <div class="space-y-1.5">
                        <Label>Kelas</Label>
                        <Autocomplete
                            v-model="classSearchText"
                            v-model:open="isClassOpen"
                            :open-on-focus="true"
                            :open-on-click="true"
                        >
                            <AutocompleteAnchor class="w-full">
                                <AutocompleteInput
                                    placeholder="Isi atau pilih kelas..."
                                    class="pr-10"
                                />
                                <AutocompleteTrigger>
                                    <ChevronDown class="h-4 w-4 text-muted-foreground" />
                                </AutocompleteTrigger>
                            </AutocompleteAnchor>

                            <AutocompleteContent>
                                <AutocompleteViewport>
                                    <AutocompleteEmpty>Tidak ada Kelas.</AutocompleteEmpty>
                                    <AutocompleteGroup>
                                        <AutocompleteItem
                                            v-for="c in classes"
                                            :key="c.id"
                                            :value="c.name"
                                            @select="form.student_class_id = c.id.toString()"
                                        >
                                            {{ c.name }}
                                        </AutocompleteItem>
                                    </AutocompleteGroup>
                                </AutocompleteViewport>
                            </AutocompleteContent>
                        </Autocomplete>
                        <InputError :message="form.errors.student_class_id" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1.5">
                        <Label>Jenis Kelamin</Label>
                        <Select v-model="form.gender">
                            <SelectTrigger class="w-full">
                                <SelectValue placeholder="Pilih" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectGroup>
                                    <SelectItem value="L">Laki-laki</SelectItem>
                                    <SelectItem value="P">Perempuan</SelectItem>
                                </SelectGroup>
                            </SelectContent>
                        </Select>
                        <InputError :message="form.errors.gender" />
                    </div>

                    <div class="space-y-1.5">
                        <Label for="form-phone">No. Telepon</Label>
                        <Input
                            id="form-phone"
                            v-model="form.phone"
                            placeholder="08xxxxxxxx"
                        />
                        <InputError :message="form.errors.phone" />
                    </div>
                </div>

                <div class="space-y-1.5">
                    <Label for="form-address">Alamat</Label>
                    <Textarea
                        id="form-address"
                        v-model="form.address"
                        placeholder="Alamat lengkap..."
                    />
                    <InputError :message="form.errors.address" />
                </div>

                <div class="space-y-1.5">
                    <Label for="form-password">
                        Kata Sandi
                        <span
                            v-if="form.role === 'student'"
                            class="text-xs font-normal text-muted-foreground"
                        >
                            (Kosongkan untuk menggunakan NIM)
                        </span>
                    </Label>
                    <PasswordInput
                        id="form-password"
                        v-model="form.password"
                        placeholder="Kata Sandi"
                        :required="form.role !== 'student' && !form.id"
                    />
                    <InputError :message="form.errors.password" />
                </div>

                <DialogFooter class="pt-4">
                    <Button
                        type="button"
                        variant="outline"
                        @click="emit('update:open', false)"
                        >Batal</Button
                    >
                    <Button type="submit" :disabled="form.processing">
                        <Spinner
                            v-if="form.processing"
                            class="mr-2 h-4 w-4 animate-spin"
                        />
                        Simpan
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
