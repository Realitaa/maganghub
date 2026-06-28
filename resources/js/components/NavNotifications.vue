<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { Bell } from '@lucide/vue';
import { computed } from 'vue';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import {
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    useSidebar,
} from '@/components/ui/sidebar';
import { useIdTimeFormat } from '@/composables/useIdTimeFormat';

const page = usePage();
const { formatTimeAgo } = useIdTimeFormat();
const notifications = computed(
    () => (page.props.auth as any)?.notifications || [],
);
const { isMobile, state } = useSidebar();
</script>

<template>
    <SidebarMenu v-if="notifications.length > 0">
        <SidebarMenuItem>
            <Popover>
                <PopoverTrigger as-child>
                    <SidebarMenuButton
                        size="lg"
                        class="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground"
                    >
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-lg text-primary"
                        >
                            <Bell class="h-4 w-4" :class="state === 'collapsed' ? 'ml-2' : ''" />
                        </div>
                        <div
                            class="grid flex-1 text-left text-sm leading-tight"
                        >
                            <span class="truncate font-semibold"
                                >Notifikasi</span
                            >
                            <span
                                class="truncate text-xs text-muted-foreground"
                            >
                                {{ notifications.length }} pesan
                            </span>
                        </div>
                    </SidebarMenuButton>
                </PopoverTrigger>
                <PopoverContent
                    class="w-80 rounded-lg p-4"
                    :side="
                        isMobile
                            ? 'bottom'
                            : state === 'collapsed'
                              ? 'left'
                              : 'top'
                    "
                    align="start"
                    :side-offset="4"
                >
                    <div class="space-y-3">
                        <h4
                            class="border-b pb-2 leading-none font-medium text-foreground"
                        >
                            Notifikasi Personal
                        </h4>
                        <div
                            class="max-h-60 scrollbar-thin space-y-3 overflow-y-auto pr-1"
                        >
                            <div
                                v-for="notif in notifications"
                                :key="notif.id"
                                class="border-b border-border/40 pb-2 text-xs leading-relaxed text-muted-foreground last:border-0 last:pb-0"
                            >
                                {{ notif.data.message }}
                                <div
                                    class="mt-1 text-[10px] text-muted-foreground/60"
                                >
                                    {{ formatTimeAgo(notif.created_at) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </PopoverContent>
            </Popover>
        </SidebarMenuItem>
    </SidebarMenu>
</template>
