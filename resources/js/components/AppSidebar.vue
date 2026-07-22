<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    LayoutGrid,
    LogIn,
    MessageSquareText,
    ScrollText,
    ShieldCheck,
} from '@lucide/vue';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import NavAdmin from '@/components/NavAdmin.vue';
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
import { useAuth } from '@/composables/useAuth';
import { dashboard, home, login } from '@/routes';
import { index as adminActivityLog } from '@/routes/admin/activity-log';
import { index as adminRoles } from '@/routes/admin/roles';
import type { NavItem } from '@/types';

const { isAuthenticated, isAdmin } = useAuth();

const mainNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [
        {
            title: 'Talepler',
            href: home(),
            icon: MessageSquareText,
        },
    ];

    if (isAuthenticated.value) {
        items.push({
            title: 'Dashboard',
            href: dashboard(),
            icon: LayoutGrid,
        });
    }

    return items;
});

const adminNavItems = computed<NavItem[]>(() => [
    {
        title: 'Roller',
        href: adminRoles(),
        icon: ShieldCheck,
    },
    {
        title: 'Aktivite Günlüğü',
        href: adminActivityLog(),
        icon: ScrollText,
    },
]);
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="home()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
            <NavAdmin v-if="isAdmin" :items="adminNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser v-if="isAuthenticated" />
            <SidebarMenu v-else>
                <SidebarMenuItem>
                    <SidebarMenuButton as-child :tooltip="'Giriş yap'">
                        <Link :href="login()">
                            <LogIn />
                            <span>Giriş yap</span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
