<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { LayoutGrid, House, Users } from '@lucide/vue';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import NavMain from '@/components/NavMain.vue';
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
import { index as userIndex } from '@/routes/users';
import type { NavItem } from '@/types';

const page = usePage();
const userRole = computed(() => page.props.auth?.user?.role);

const mainNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [];

    if (userRole.value === 'student') {
        items.push({
            title: 'Home',
            href: home(),
            icon: House,
        })
    } else {
        items.push({
            title: 'Dashboard',
            href: dashboard(),
            icon: LayoutGrid,
        })
    }

    if (userRole.value === 'administrator' || userRole.value === 'operator') {
        items.push({
            title: 'Manajemen Pengguna',
            href: userIndex(),
            icon: Users,
        });
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
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
