<script setup lang="ts">
import { Head, useForm, useHttp, router, usePage } from '@inertiajs/vue3';
import { 
    Plus, 
    Upload, 
    Search, 
    Edit, 
    Trash2, 
    UserCheck, 
    UserX, 
    MoreVertical, 
    FileDown, 
    AlertTriangle,
    ShieldAlert
} from '@lucide/vue';
import { ref, watch, computed } from 'vue';
import InputError from '@/components/InputError.vue';
import PageHeader from '@/components/PageHeader.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
    DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Pagination,
    PaginationContent,
    PaginationEllipsis,
    PaginationFirst,
    PaginationItem,
    PaginationLast,
    PaginationNext,
    PaginationPrevious,
} from '@/components/ui/pagination';
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Spinner } from '@/components/ui/spinner';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Textarea } from '@/components/ui/textarea'
import { 
    index as userIndex, 
    store as userStore, 
    update as userUpdate, 
    destroy as userDestroy,
    toggleActive as userToggleActive,
    importMethod as userImport
} from '@/routes/users';
import type { User } from '@/types';

const props = defineProps<{
    users: {
        current_page: number;
        data: User[];
        first_page_url: string;
        from: number;
        last_page: number;
        last_page_url: string;
        links: any[];
        next_page_url: string;
        path: string;
        per_page: number;
        prev_page_url: string | null;
        to: number;
        total: number;
    };
    majors: string[];
    filters: {
        search?: string;
        role?: string;
        major?: string;
    };
}>();

// State
const searchQuery = ref(props.filters.search || '');
const selectedRole = ref(props.filters.role || 'all');
const selectedMajor = ref(props.filters.major || 'all');

// Modals
const showAddEditModal = ref(false);
const showImportModal = ref(false);
const showDeleteConfirmModal = ref(false);
const userToDelete = ref<any>(null);

// Forms
const crudForm = useForm({
    id: null as number | null,
    name: '',
    email: '',
    role: 'student',
    nim: '',
    major: '',
    gender: 'L' as 'L' | 'P',
    phone: '',
    address: '',
    password: '',
});

// Import State using useHttp
const importHttp = useHttp({
    file: null as File | null,
});

const importError = ref<string | null>(null);
const importSuccess = ref<string | null>(null);

// Page user and permission helpers
const page = usePage();
const currentUser = computed(() => page.props.auth.user as any);

const canEditUser = (targetUser: any) => {
    if (!currentUser.value) {
return false;
}

    if (currentUser.value.role === 'administrator') {
        return true;
    }

    if (currentUser.value.role === 'operator') {
        if (targetUser.role === 'administrator') {
            return false;
        }

        if (targetUser.role === 'operator' && targetUser.id !== currentUser.value.id) {
            return false;
        }

        return true;
    }

    return false;
};

const canDeleteUser = () => {
    if (!currentUser.value) {
        return false;
    }

    if (currentUser.value.role === 'administrator') {
        return true;
    }

    return false; // Operators can't delete anyone
};

// Debounced search logic
function debounce<T extends (...args: any[]) => any>(fn: T, delay: number) {
    let timeoutId: ReturnType<typeof setTimeout> | undefined;

    return (...args: Parameters<T>) => {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => fn(...args), delay);
    };
}

const applyFilters = () => {
    router.get(
        userIndex.url(),
        {
            search: searchQuery.value || undefined,
            role: selectedRole.value === 'all' ? undefined : selectedRole.value,
            major: selectedMajor.value === 'all' ? undefined : selectedMajor.value,
        },
        {
            preserveState: true,
            replace: true,
        }
    );
};

const debouncedFilterList = debounce(applyFilters, 400);

watch(searchQuery, () => {
    debouncedFilterList();
});

watch(selectedRole, () => {
    applyFilters();
});

watch(selectedMajor, () => {
    applyFilters();
});

// Form Actions
const openAddModal = () => {
    crudForm.reset();
    crudForm.clearErrors();
    crudForm.id = null;
    crudForm.role = 'student';
    crudForm.gender = 'L';
    showAddEditModal.value = true;
};

const openEditModal = (user: any) => {
    crudForm.clearErrors();
    crudForm.id = user.id;
    crudForm.name = user.name;
    crudForm.email = user.email;
    crudForm.role = user.role;
    crudForm.nim = user.nim || '';
    crudForm.major = user.major || '';
    crudForm.gender = user.gender || 'L';
    crudForm.phone = user.phone || '';
    crudForm.address = user.address || '';
    crudForm.password = ''; // Keep password blank unless changing
    showAddEditModal.value = true;
};

const submitCrudForm = () => {
    if (crudForm.id) {
        crudForm.patch(userUpdate.url(crudForm.id), {
            onSuccess: () => {
                showAddEditModal.value = false;
                crudForm.reset();
            },
        });
    } else {
        crudForm.post(userStore.url(), {
            onSuccess: () => {
                showAddEditModal.value = false;
                crudForm.reset();
            },
        });
    }
};

const toggleUserStatus = (user: any) => {
    router.patch(userToggleActive.url(user.id), {}, {
        preserveScroll: true,
    });
};

const confirmDelete = (user: any) => {
    userToDelete.value = user;
    showDeleteConfirmModal.value = true;
};

const deleteUser = () => {
    if (userToDelete.value) {
        router.delete(userDestroy.url(userToDelete.value.id), {
            onSuccess: () => {
                showDeleteConfirmModal.value = false;
                userToDelete.value = null;
            },
        });
    }
};

// Import Handlers using useHttp
const handleFileChange = (e: Event) => {
    importError.value = null;
    importSuccess.value = null;
    const target = e.target as HTMLInputElement;

    if (target.files && target.files.length > 0) {
        importHttp.file = target.files[0];
    }
};

const submitImport = () => {
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
            // Reset the file input manually if needed
            const fileInput = document.getElementById('import-file-input') as HTMLInputElement;

            if (fileInput) {
                fileInput.value = '';
            }

            router.reload({ only: ['users', 'majors'] });
        },
        onError: (errors: any) => {
            if (errors.file) {
                importError.value = errors.file;
            } else {
                importError.value = 'Gagal mengimpor data. Pastikan format file Anda sesuai.';
            }
        }
    });
};

// Helper translations
const getRoleBadgeVariant = (role: string) => {
    switch (role) {
        case 'administrator': return 'destructive';
        case 'operator': return 'secondary';
        default: return 'outline';
    }
};

const getRoleLabel = (role: string) => {
    switch (role) {
        case 'administrator': return 'Administrator';
        case 'operator': return 'Operator';
        case 'student': return 'Mahasiswa';
        default: return role;
    }
};

const handlePageChange = (newPage: number) => {
    router.get(
        userIndex.url(),
        {
            page: newPage,
            search: searchQuery.value || undefined,
            role: selectedRole.value === 'all' ? undefined : selectedRole.value,
            major: selectedMajor.value === 'all' ? undefined : selectedMajor.value,
        },
        {
            preserveState: true,
            replace: true,
        }
    );
};
</script>

<template>
    <Head title="Manajemen Pengguna" />

    <div class="flex-1 space-y-4 p-4 md:p-8 pt-6">
        <PageHeader title="Manajemen Pengguna" description="Kelola data administrator, operator, dan mahasiswa serta impor data secara massal.">
            <Button @click="showImportModal = true" variant="outline" class="flex items-center gap-2">
                <Upload class="h-4 w-4" />
                Impor Pengguna
            </Button>
            <Button @click="openAddModal" class="flex items-center gap-2">
                <Plus class="h-4 w-4" />
                Tambah Pengguna
            </Button>
        </PageHeader>   

        <!-- Filter Card -->
        <Card class="border border-border/80 py-0">
            <CardContent class="p-4 flex flex-col lg:flex-row gap-3 items-end">
                <div class="space-y-1.5 w-full">
                    <Label for="search" class="text-xs font-semibold">Cari Pengguna</Label>
                    <div class="relative">
                        <Search class="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                        <Input
                            id="search"
                            v-model="searchQuery"
                            placeholder="Cari berdasarkan nama, email, atau NIM..."
                            class="pl-9"
                        />
                    </div>
                </div>

                <div class="w-full lg:w-140 flex gap-2">
                    <div class="w-1/2 lg:w-50 space-y-1.5">
                        <Label class="text-xs font-semibold">Program Studi</Label>
                        <Select v-model="selectedMajor">
                            <SelectTrigger class="w-full">
                                <SelectValue placeholder="Pilih Prodi" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectGroup>
                                    <SelectItem value="all">Semua Prodi</SelectItem>
                                    <SelectItem v-for="major in majors" :key="major" :value="major">
                                        {{ major }}
                                    </SelectItem>
                                </SelectGroup>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="w-1/2 lg:w-50 space-y-1.5">
                        <Label class="text-xs font-semibold">Peran</Label>
                        <Select v-model="selectedRole">
                            <SelectTrigger class="w-full">
                                <SelectValue placeholder="Pilih Peran" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectGroup>
                                    <SelectItem value="all">Semua Peran</SelectItem>
                                    <SelectItem value="administrator">Administrator</SelectItem>
                                    <SelectItem value="operator">Operator</SelectItem>
                                    <SelectItem value="student">Mahasiswa</SelectItem>
                                </SelectGroup>
                            </SelectContent>
                        </Select>
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- User Table -->
        <Card class="border border-border/80 -py-0">
            <CardContent class="p-0">
                <Table>
                    <TableHeader class="bg-muted/50 border-b border-border/80 text-muted-foreground rounded-xl">
                        <TableRow>
                            <TableHead class="h-10 px-4 text-left align-middle font-medium text-xs uppercase tracking-wider">Nama</TableHead>
                            <TableHead class="h-10 px-4 text-left align-middle font-medium text-xs uppercase tracking-wider">Email</TableHead>
                            <TableHead class="h-10 px-4 text-left align-middle font-medium text-xs uppercase tracking-wider">NIM</TableHead>
                            <TableHead class="h-10 px-4 text-left align-middle font-medium text-xs uppercase tracking-wider">Program Studi</TableHead>
                            <TableHead class="h-10 px-4 text-left align-middle font-medium text-xs uppercase tracking-wider">Peran</TableHead>
                            <TableHead class="h-10 px-4 text-left align-middle font-medium text-xs uppercase tracking-wider">Status</TableHead>
                            <TableHead class="h-10 px-4 text-right align-middle font-medium text-xs uppercase tracking-wider">Aksi</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody class="divide-y divide-border/60">
                        <TableRow v-slot="{}" v-if="users.data.length === 0">
                            <TableCell colspan="7" class="p-8 text-center text-muted-foreground">
                                Tidak ada pengguna ditemukan.
                            </TableCell>
                        </TableRow>
                        <TableRow v-for="user in users.data" :key="user.id" class="hover:bg-muted/30 transition-colors">
                            <TableCell class="p-4 align-middle font-medium text-foreground">{{ user.name }}</TableCell>
                            <TableCell class="p-4 align-middle text-muted-foreground">{{ user.email }}</TableCell>
                            <TableCell class="p-4 align-middle text-muted-foreground">{{ user.nim || '-' }}</TableCell>
                            <TableCell class="p-4 align-middle text-muted-foreground">{{ user.major || '-' }}</TableCell>
                            <TableCell class="p-4 align-middle">
                                <Badge :variant="getRoleBadgeVariant(user.role)">
                                    {{ getRoleLabel(user.role) }}
                                </Badge>
                            </TableCell>
                            <TableCell class="p-4 align-middle">
                                <Badge :variant="user.is_active ? 'default' : 'secondary'">
                                    {{ user.is_active ? 'Aktif' : 'Nonaktif' }}
                                </Badge>
                            </TableCell>
                            <TableCell class="p-4 align-middle text-right">
                                <DropdownMenu v-if="canEditUser(user) || canDeleteUser()">
                                    <DropdownMenuTrigger as-child>
                                        <Button variant="ghost" class="h-8 w-8 p-0">
                                            <span class="sr-only">Buka menu</span>
                                            <MoreVertical class="h-4 w-4" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end" class="w-[160px]">
                                        <DropdownMenuItem v-if="canEditUser(user)" @click="openEditModal(user)">
                                            <Edit class="mr-2 h-4 w-4" />
                                            Edit Detail
                                        </DropdownMenuItem>
                                        <DropdownMenuItem v-if="canEditUser(user)" @click="toggleUserStatus(user)">
                                            <component :is="user.is_active ? UserX : UserCheck" class="mr-2 h-4 w-4" />
                                            {{ user.is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </DropdownMenuItem>
                                        <DropdownMenuSeparator v-if="canEditUser(user) && canDeleteUser()" />
                                        <DropdownMenuItem v-if="canDeleteUser()" @click="confirmDelete(user)" class="text-destructive focus:text-destructive">
                                            <Trash2 class="mr-2 h-4 w-4" />
                                            Hapus Pengguna
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>

                <!-- Pagination Links -->
                <div class="flex items-center justify-between px-4 py-4 border-t border-border/80">
                    <div class="text-xs text-muted-foreground font-medium">
                        Menampilkan {{ users.from || 0 }} sampai {{ users.to || 0 }} dari {{ users.total }} pengguna
                    </div>
                    <Pagination
                        v-slot="{ page }"
                        :total="users.total"
                        :items-per-page="users.per_page"
                        :page="users.current_page"
                        @update:page="handlePageChange"
                        :sibling-count="1"
                        show-edges
                        class="justify-end w-auto mx-0"
                    >
                        <PaginationContent v-slot="{ items }">
                            <PaginationFirst />
                            <PaginationPrevious />

                            <template v-for="(item, index) in items">
                                <PaginationItem
                                    v-if="item.type === 'page'"
                                    :key="index"
                                    :value="item.value"
                                    :is-active="item.value === page"
                                >
                                    {{ item.value }}
                                </PaginationItem>
                                <PaginationEllipsis
                                    v-else
                                    :key="item.type"
                                    :index="index"
                                />
                            </template>

                            <PaginationNext />
                            <PaginationLast />
                        </PaginationContent>
                    </Pagination>
                </div>
            </CardContent>
        </Card>

        <!-- ADD / EDIT USER DIALOG -->
        <Dialog v-model:open="showAddEditModal">
            <DialogContent class="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>{{ crudForm.id ? 'Edit Pengguna' : 'Tambah Pengguna Baru' }}</DialogTitle>
                    <DialogDescription>
                        Isi form di bawah ini untuk {{ crudForm.id ? 'memperbarui data pengguna' : 'menambahkan pengguna baru ke sistem' }}.
                    </DialogDescription>
                </DialogHeader>

                <form @submit.prevent="submitCrudForm" class="space-y-4 py-2">
                    <div class="space-y-1.5">
                        <Label for="form-name">Nama Lengkap</Label>
                        <Input id="form-name" v-model="crudForm.name" placeholder="Nama Lengkap" required />
                        <InputError :message="crudForm.errors.name" />
                    </div>

                    <div class="space-y-1.5">
                        <Label for="form-email">Email</Label>
                        <Input id="form-email" type="email" v-model="crudForm.email" placeholder="nama@domain.com" required />
                        <InputError :message="crudForm.errors.email" />
                    </div>

                    <div class="space-y-1.5">
                        <Label>Peran Pengguna</Label>
                        <Select v-model="crudForm.role">
                            <SelectTrigger>
                                <SelectValue placeholder="Pilih Peran" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectGroup>
                                    <SelectItem value="student">Mahasiswa</SelectItem>
                                    <SelectItem value="operator">Operator</SelectItem>
                                    <SelectItem value="administrator">Administrator</SelectItem>
                                </SelectGroup>
                            </SelectContent>
                        </Select>
                        <InputError :message="crudForm.errors.role" />
                    </div>

                    <!-- Dynamic fields for student -->
                    <template v-if="crudForm.role === 'student'">
                        <div class="space-y-1.5">
                            <Label for="form-nim">NIM</Label>
                            <Input id="form-nim" v-model="crudForm.nim" placeholder="Nomor Induk Mahasiswa" required />
                            <InputError :message="crudForm.errors.nim" />
                        </div>

                        <div class="space-y-1.5">
                            <Label>Program Studi</Label>
                            <Input id="form-major" v-model="crudForm.major" placeholder="Teknik Informatika" required />
                            <InputError :message="crudForm.errors.major" />
                        </div>
                    </template>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1.5">
                            <Label>Jenis Kelamin</Label>
                            <Select v-model="crudForm.gender">
                                <SelectTrigger>
                                    <SelectValue placeholder="Pilih" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectItem value="L">Laki-laki</SelectItem>
                                        <SelectItem value="P">Perempuan</SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                            <InputError :message="crudForm.errors.gender" />
                        </div>
                        
                        <div class="space-y-1.5">
                            <Label for="form-phone">No. Telepon</Label>
                            <Input id="form-phone" v-model="crudForm.phone" placeholder="08xxxxxxxx" />
                            <InputError :message="crudForm.errors.phone" />
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <Label for="form-address">Alamat</Label>
                        <Textarea id="form-address" v-model="crudForm.address" placeholder="Alamat lengkap..." />
                        <InputError :message="crudForm.errors.address" />
                    </div>

                    <div class="space-y-1.5">
                        <Label for="form-password">
                            Kata Sandi
                            <span v-if="crudForm.role === 'student'" class="text-xs text-muted-foreground font-normal">
                                (Kosongkan untuk menggunakan NIM)
                            </span>
                        </Label>
                        <PasswordInput 
                            id="form-password" 
                            v-model="crudForm.password" 
                            placeholder="Kata Sandi" 
                            :required="crudForm.role !== 'student' && !crudForm.id"
                        />
                        <InputError :message="crudForm.errors.password" />
                    </div>

                    <DialogFooter class="pt-4">
                        <Button type="button" variant="outline" @click="showAddEditModal = false">Batal</Button>
                        <Button type="submit" :disabled="crudForm.processing">
                            <Spinner v-if="crudForm.processing" class="mr-2 h-4 w-4 animate-spin" />
                            Simpan
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- IMPORT USER DIALOG -->
        <Dialog v-model:open="showImportModal">
            <DialogContent class="sm:max-w-[450px]">
                <DialogHeader>
                    <DialogTitle>Impor Data Mahasiswa</DialogTitle>
                    <DialogDescription>
                        Impor data pengguna massal menggunakan file Excel (.xlsx) atau CSV (.csv).
                    </DialogDescription>
                </DialogHeader>

                <div class="space-y-4 py-3">
                    <div class="rounded-lg bg-emerald-500/10 border border-emerald-500/20 p-4 text-xs space-y-2 text-emerald-800 dark:text-emerald-300">
                        <h4 class="font-bold flex items-center gap-2">
                            <FileDown class="h-4 w-4 text-emerald-600 dark:text-emerald-400" />
                            Informasi & Ketentuan Impor:
                        </h4>
                        <ul class="list-disc pl-4 space-y-1 leading-relaxed">
                            <li>Impor massal hanya diperuntukkan untuk pengguna dengan peran <strong>Mahasiswa</strong>.</li>
                            <li>Data minimum yang wajib diisi pada baris kolom adalah: <strong>name</strong>, <strong>nim</strong>, dan <strong>major</strong>.</li>
                            <li>NIM mahasiswa akan secara otomatis digunakan sebagai password default mereka.</li>
                            <li>Ukuran file maksimal yang didukung adalah <strong>10MB</strong>.</li>
                        </ul>
                    </div>

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
                    <div v-if="importHttp.processing" class="flex items-center gap-3 text-sm text-muted-foreground p-2 rounded bg-muted/30">
                        <Spinner class="h-4 w-4 animate-spin" />
                        <span>Sedang mengimpor data, mohon tunggu...</span>
                    </div>

                    <div v-if="importHttp.progress" class="w-full bg-muted h-2 rounded overflow-hidden">
                        <div class="bg-primary h-full transition-all duration-300" :style="`width: ${importHttp.progress.percentage}%`" />
                    </div>

                    <div v-if="importError" class="rounded-lg bg-destructive/10 border border-destructive/20 p-3 text-xs font-semibold text-destructive flex items-start gap-2">
                        <AlertTriangle class="h-4 w-4 shrink-0 mt-0.5" />
                        <span>{{ importError }}</span>
                    </div>

                    <div v-if="importSuccess" class="rounded-lg bg-emerald-500/10 border border-emerald-500/20 p-3 text-xs font-semibold text-emerald-700 dark:text-emerald-400 flex items-start gap-2">
                        <UserCheck class="h-4 w-4 shrink-0 mt-0.5" />
                        <span>{{ importSuccess }}</span>
                    </div>
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="showImportModal = false" :disabled="importHttp.processing">Tutup</Button>
                    <Button @click="submitImport" :disabled="importHttp.processing || !importHttp.file">
                        <Spinner v-if="importHttp.processing" class="mr-2 h-4 w-4 animate-spin" />
                        Mulai Impor
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- DELETE CONFIRM DIALOG -->
        <Dialog v-model:open="showDeleteConfirmModal">
            <DialogContent class="sm:max-w-[400px]">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2 text-destructive">
                        <ShieldAlert class="h-5 w-5" />
                        Konfirmasi Hapus Pengguna
                    </DialogTitle>
                    <DialogDescription>
                        Apakah Anda yakin ingin menghapus pengguna ini? Tindakan ini tidak dapat dibatalkan dan akan menghapus seluruh data terkait.
                    </DialogDescription>
                </DialogHeader>

                <div v-if="userToDelete" class="py-2 text-sm">
                    <div class="grid grid-cols-3 py-1 text-muted-foreground"><span class="font-semibold">Nama:</span><span class="col-span-2 text-foreground">{{ userToDelete.name }}</span></div>
                    <div class="grid grid-cols-3 py-1 text-muted-foreground"><span class="font-semibold">Email:</span><span class="col-span-2 text-foreground">{{ userToDelete.email }}</span></div>
                    <div class="grid grid-cols-3 py-1 text-muted-foreground"><span class="font-semibold">NIM:</span><span class="col-span-2 text-foreground">{{ userToDelete.nim || '-' }}</span></div>
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="showDeleteConfirmModal = false">Batal</Button>
                    <Button variant="destructive" @click="deleteUser">Hapus</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
