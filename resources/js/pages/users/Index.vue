<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import {
    Plus,
    Upload,
    Search,
    Edit,
    Trash2,
    UserCheck,
    UserX,
    MoreVertical,
} from '@lucide/vue';
import { ref, watch, computed } from 'vue';
import PageHeader from '@/components/PageHeader.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
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
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import UserCreateEditDialog from '@/components/users/UserCreateEditDialog.vue';
import UserDeleteDialog from '@/components/users/UserDeleteDialog.vue';
import UserImportDialog from '@/components/users/UserImportDialog.vue';
import {
    index as userIndex,
    toggleActive as userToggleActive,
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
    classes: { id: number; name: string }[];
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
const userEditing = ref<User | null>(null);
const userToDelete = ref<User | null>(null);

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

        if (
            targetUser.role === 'operator' &&
            targetUser.id !== currentUser.value.id
        ) {
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
            major:
                selectedMajor.value === 'all' ? undefined : selectedMajor.value,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
};

const debouncedFilterList = debounce(applyFilters, 400);

watch(searchQuery, () => {
    debouncedFilterList();
});

watch(selectedRole, () => {
    applyFilters();
});

// Form Actions
const openAddModal = () => {
    userEditing.value = null;
    showAddEditModal.value = true;
};

const openEditModal = (user: User) => {
    userEditing.value = user;
    showAddEditModal.value = true;
};

const toggleUserStatus = (user: any) => {
    router.patch(
        userToggleActive.url(user.id),
        {},
        {
            preserveScroll: true,
        },
    );
};

const confirmDelete = (user: User) => {
    userToDelete.value = user;
    showDeleteConfirmModal.value = true;
};

// Helper translations
const getRoleBadgeVariant = (role: string) => {
    switch (role) {
        case 'administrator':
            return 'destructive';
        case 'operator':
            return 'secondary';
        default:
            return 'outline';
    }
};

const getRoleLabel = (role: string) => {
    switch (role) {
        case 'administrator':
            return 'Administrator';
        case 'operator':
            return 'Operator';
        case 'student':
            return 'Mahasiswa';
        default:
            return role;
    }
};

const handlePageChange = (newPage: number) => {
    router.get(
        userIndex.url(),
        {
            page: newPage,
            search: searchQuery.value || undefined,
            role: selectedRole.value === 'all' ? undefined : selectedRole.value,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
};
</script>

<template>
    <Head title="Manajemen Pengguna" />

    <div class="flex-1 space-y-4 p-4 pt-6 md:p-8">
        <PageHeader
            title="Manajemen Pengguna"
            description="Kelola data administrator, operator, dan mahasiswa serta impor data secara massal."
        >
            <Button
                @click="showImportModal = true"
                variant="outline"
                class="flex items-center gap-2"
            >
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
            <CardContent class="flex flex-col items-end gap-3 p-4 lg:flex-row">
                <div class="w-full space-y-1.5">
                    <Label for="search" class="text-xs font-semibold"
                        >Cari Pengguna</Label
                    >
                    <div class="relative">
                        <Search
                            class="absolute top-3 left-3 h-4 w-4 text-muted-foreground"
                        />
                        <Input
                            id="search"
                            v-model="searchQuery"
                            placeholder="Cari berdasarkan nama, email, atau NIM..."
                            class="pl-9"
                        />
                    </div>
                </div>

                <div class="flex w-full gap-2 lg:w-70">
                    <div class="w-full space-y-1.5">
                        <Label class="text-xs font-semibold">Peran</Label>
                        <Select v-model="selectedRole">
                            <SelectTrigger class="w-full">
                                <SelectValue placeholder="Pilih Peran" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectGroup>
                                    <SelectItem value="all"
                                        >Semua Peran</SelectItem
                                    >
                                    <SelectItem value="administrator"
                                        >Administrator</SelectItem
                                    >
                                    <SelectItem value="operator"
                                        >Operator</SelectItem
                                    >
                                    <SelectItem value="student"
                                        >Mahasiswa</SelectItem
                                    >
                                </SelectGroup>
                            </SelectContent>
                        </Select>
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- User Table -->
        <Card class="-py-0 border border-border/80">
            <CardContent class="p-0">
                <Table>
                    <TableHeader
                        class="rounded-xl border-b border-border/80 bg-muted/50 text-muted-foreground"
                    >
                        <TableRow>
                            <TableHead
                                class="h-10 px-4 text-left align-middle text-xs font-medium tracking-wider uppercase"
                                >Nama</TableHead
                            >
                            <TableHead
                                class="h-10 px-4 text-left align-middle text-xs font-medium tracking-wider uppercase"
                                >Email</TableHead
                            >
                            <TableHead
                                class="h-10 px-4 text-left align-middle text-xs font-medium tracking-wider uppercase"
                                >NIM</TableHead
                            >
                            <TableHead
                                class="h-10 px-4 text-left align-middle text-xs font-medium tracking-wider uppercase"
                                >Kelas</TableHead
                            >
                            <TableHead
                                class="h-10 px-4 text-left align-middle text-xs font-medium tracking-wider uppercase"
                                >Peran</TableHead
                            >
                            <TableHead
                                class="h-10 px-4 text-left align-middle text-xs font-medium tracking-wider uppercase"
                                >Status</TableHead
                            >
                            <TableHead
                                class="h-10 px-4 text-right align-middle text-xs font-medium tracking-wider uppercase"
                                >Aksi</TableHead
                            >
                        </TableRow>
                    </TableHeader>
                    <TableBody class="divide-y divide-border/60">
                        <TableRow v-slot="{}" v-if="users.data.length === 0">
                            <TableCell
                                colspan="7"
                                class="p-8 text-center text-muted-foreground"
                            >
                                Tidak ada pengguna ditemukan.
                            </TableCell>
                        </TableRow>
                        <TableRow
                            v-for="user in users.data"
                            :key="user.id"
                            class="transition-colors hover:bg-muted/30"
                        >
                            <TableCell
                                class="p-4 align-middle font-medium text-foreground"
                                >{{ user.name }}</TableCell
                            >
                            <TableCell
                                class="p-4 align-middle text-muted-foreground"
                                >{{ user.email }}</TableCell
                            >
                            <TableCell
                                class="p-4 align-middle text-muted-foreground"
                                >{{ user.nim || '-' }}</TableCell
                            >
                            <TableCell
                                class="p-4 align-middle text-muted-foreground"
                            >
                                {{
                                    user.student_class
                                        ? user.student_class.name
                                        : '-'
                                }}
                            </TableCell>
                            <TableCell class="p-4 align-middle">
                                <Badge
                                    :variant="getRoleBadgeVariant(user.role)"
                                >
                                    {{ getRoleLabel(user.role) }}
                                </Badge>
                            </TableCell>
                            <TableCell class="p-4 align-middle">
                                <Badge
                                    :variant="
                                        user.is_active ? 'default' : 'secondary'
                                    "
                                >
                                    {{ user.is_active ? 'Aktif' : 'Nonaktif' }}
                                </Badge>
                            </TableCell>
                            <TableCell class="p-4 text-right align-middle">
                                <DropdownMenu
                                    v-if="canEditUser(user) || canDeleteUser()"
                                >
                                    <DropdownMenuTrigger as-child>
                                        <Button
                                            variant="ghost"
                                            class="h-8 w-8 p-0"
                                        >
                                            <span class="sr-only"
                                                >Buka menu</span
                                            >
                                            <MoreVertical class="h-4 w-4" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent
                                        align="end"
                                        class="w-[160px]"
                                    >
                                        <DropdownMenuItem
                                            v-if="canEditUser(user)"
                                            @click="openEditModal(user)"
                                        >
                                            <Edit class="mr-2 h-4 w-4" />
                                            Edit Detail
                                        </DropdownMenuItem>
                                        <DropdownMenuItem
                                            v-if="canEditUser(user)"
                                            @click="toggleUserStatus(user)"
                                        >
                                            <component
                                                :is="
                                                    user.is_active
                                                        ? UserX
                                                        : UserCheck
                                                "
                                                class="mr-2 h-4 w-4"
                                            />
                                            {{
                                                user.is_active
                                                    ? 'Nonaktifkan'
                                                    : 'Aktifkan'
                                            }}
                                        </DropdownMenuItem>
                                        <DropdownMenuSeparator
                                            v-if="
                                                canEditUser(user) &&
                                                canDeleteUser()
                                            "
                                        />
                                        <DropdownMenuItem
                                            v-if="canDeleteUser()"
                                            @click="confirmDelete(user)"
                                            class="text-destructive focus:text-destructive"
                                        >
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
                <div
                    class="flex items-center justify-between border-t border-border/80 px-4 py-4"
                >
                    <div class="text-xs font-medium text-muted-foreground">
                        Menampilkan {{ users.from || 0 }} sampai
                        {{ users.to || 0 }} dari {{ users.total }} pengguna
                    </div>
                    <Pagination
                        v-slot="{ page }"
                        :total="users.total"
                        :items-per-page="users.per_page"
                        :page="users.current_page"
                        @update:page="handlePageChange"
                        :sibling-count="1"
                        show-edges
                        class="mx-0 w-auto justify-end"
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

        <!-- EXTRACTED DIALOGS -->
        <UserCreateEditDialog
            v-model:open="showAddEditModal"
            :user="userEditing"
            :classes="classes"
            @success="router.reload({ only: ['users'] })"
        />

        <UserImportDialog
            v-model:open="showImportModal"
            @success="router.reload({ only: ['users', 'majors'] })"
        />

        <UserDeleteDialog
            v-model:open="showDeleteConfirmModal"
            :user="userToDelete"
            @success="router.reload({ only: ['users'] })"
        />
    </div>
</template>
