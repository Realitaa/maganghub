<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    LayoutGrid,
    House,
    Users,
    ClipboardList,
    FileText,
    CheckCircle2,
    Briefcase,
} from '@lucide/vue';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import NavMain from '@/components/NavMain.vue';
import NavNotifications from '@/components/NavNotifications.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { home } from '@/routes';
import { index as groupsIndex } from '@/routes/review/groups';
import { index as readyIndex } from '@/routes/review/ready';
import { index as submissionsIndex } from '@/routes/review/submissions';
import { index as templateIndex } from '@/routes/review/templates';
import { index as userIndex } from '@/routes/users';
import type { NavItem } from '@/types';

const page = usePage();
const userRole = computed(() => page.props.auth?.user?.role);

const mainNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [];

    if (userRole.value === 'student') {
        items.push({
            title: 'Beranda',
            href: home(),
            icon: House,
        });
    } else {
        items.push({
            title: 'Dashboard',
            href: dashboard(),
            icon: LayoutGrid,
        });
    }

    if (userRole.value === 'administrator' || userRole.value === 'operator') {
        items.push(
            {
                title: 'Manajemen Pengguna',
                href: userIndex(),
                icon: Users,
            },
            {
                title: 'Pengajuan Magang',
                href: submissionsIndex(),
                icon: ClipboardList,
            },
            {
                title: 'Siap Magang',
                href: readyIndex(),
                icon: CheckCircle2,
            },
            {
                title: 'Kelompok Magang',
                href: groupsIndex(),
                icon: Briefcase,
            },
            {
                title: 'Kelola Template',
                href: templateIndex(),
                icon: FileText,
            },
        );
    }

    return items;
});
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavNotifications v-if="userRole === 'student'" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
