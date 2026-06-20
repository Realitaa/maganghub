<script setup lang="ts">
import { Form, Head, usePage } from '@inertiajs/vue3';
import { CircleQuestionMark } from '@lucide/vue';
import { computed } from 'vue';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { Textarea } from '@/components/ui/textarea';
import { Tooltip, TooltipContent, TooltipTrigger, TooltipProvider } from '@/components/ui/tooltip';
import { edit } from '@/routes/profile';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Pengaturan Profil',
                href: edit(),
            },
        ],
    },
});

const page = usePage();
const user = computed(() => page.props.auth.user);
</script>

<template>
    <Head title="Pengaturan Profil" />

    <h1 class="sr-only">Pengaturan Profil</h1>

    <div class="flex flex-col space-y-6">
        <Heading
            variant="small"
            title="Pengaturan Profil"
            description="Ubah pengaturan profil Anda"
        />

        <Form
            v-bind="ProfileController.update.form()"
            class="space-y-6"
            v-slot="{ errors, processing }"
        >
            <div class="grid gap-2">
                <Label for="name">Nama</Label>
                <Input
                    id="name"
                    class="mt-1 block w-full"
                    name="name"
                    :default-value="user.name"
                    required
                    autocomplete="name"
                    placeholder="Nama Lengkap"
                />
                <InputError class="mt-2" :message="errors.name" />
            </div>

            <div class="grid gap-2">
                <Label for="email">Alamat Email</Label>
                <Input
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    name="email"
                    :default-value="user.email"
                    required
                    autocomplete="username"
                    placeholder="Alamat Email"
                />
                <InputError class="mt-2" :message="errors.email" />
            </div>

            <template v-if="user.role === 'student'">
                <!-- NIM -->
                <div class="grid gap-2">
                    <Label for="nim">NIM</Label>
                    <Input
                        id="nim"
                        class="mt-1 block w-full"
                        name="nim"
                        :default-value="user.nim"
                        required
                        placeholder="Nomor Induk Mahasiswa"
                    />
                    <InputError class="mt-2" :message="errors.nim" />
                </div>



                <!-- Gender (Radio Group) -->
                <div class="grid gap-2">
                    <Label>Jenis Kelamin</Label>
                    <RadioGroup name="gender" :default-value="user.gender || 'L'" class="flex gap-4 mt-1">
                        <div class="flex items-center space-x-2">
                            <RadioGroupItem id="gender-l" value="L" />
                            <Label for="gender-l" class="font-normal cursor-pointer">Laki-laki</Label>
                        </div>
                        <div class="flex items-center space-x-2">
                            <RadioGroupItem id="gender-p" value="P" />
                            <Label for="gender-p" class="font-normal cursor-pointer">Perempuan</Label>
                        </div>
                    </RadioGroup>
                    <InputError class="mt-2" :message="errors.gender" />
                </div>

                <!-- Phone -->
                <div class="grid gap-2">
                    <Label for="phone">Nomor WhatsApp</Label>
                    <Input
                        id="phone"
                        class="mt-1 block w-full"
                        name="phone"
                        :default-value="user.phone"
                        required
                        placeholder="08123456789"
                    />
                    <InputError class="mt-2" :message="errors.phone" />
                </div>

                <!-- Address -->
                <div class="grid gap-2">
                    <Label for="address">Alamat</Label>
                    <Textarea
                        id="address"
                        class="mt-1 block w-full"
                        name="address"
                        :default-value="(user.address as string) || ''"
                        required
                        placeholder="Alamat Lengkap"
                    />
                    <InputError class="mt-2" :message="errors.address" />
                </div>

                <!-- Semester -->
                <div class="grid gap-2">
                    <Label for="semester">Semester</Label>
                    <Input
                        id="semester"
                        type="number"
                        min="1"
                        max="14"
                        class="mt-1 block w-full"
                        name="semester"
                        :default-value="user.semester ? String(user.semester) : ''"
                        required
                        placeholder="Semester"
                    />
                    <InputError class="mt-2" :message="errors.semester" />
                </div>


            </template>

            <div class="flex items-center gap-4">
                <Button :disabled="processing" data-test="update-profile-button"
                    >Simpan</Button
                >
            </div>
        </Form>
    </div>
</template>
