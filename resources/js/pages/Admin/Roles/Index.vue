<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { toast } from 'vue-sonner';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { index as rolesIndex, store, destroy } from '@/routes/admin/roles';

type UserRow = {
    id: number;
    name: string;
    email: string;
    roles: string[];
};

type Paginated = {
    data: UserRow[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
};

defineProps<{
    users: Paginated;
    availableRoles: string[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Roller', href: rolesIndex() }],
    },
});

const roleLabels: Record<string, string> = {
    superadmin: 'Süper Admin',
    admin: 'Admin',
    moderator: 'Moderatör',
    member: 'Üye',
};

const selected = ref<Record<number, string>>({});

const assign = (userId: number) => {
    const role = selected.value[userId];

    if (!role) {
        return;
    }

    router.post(
        store.url(userId),
        { role },
        {
            preserveScroll: true,
            onError: () => toast.error('Rol atanamadı.'),
        },
    );
};

const revoke = (userId: number, role: string) => {
    router.delete(destroy.url({ user: userId, role }), {
        preserveScroll: true,
        onError: () => toast.error('Rol kaldırılamadı.'),
    });
};
</script>

<template>
    <Head title="Roller" />

    <div class="mx-auto flex w-full max-w-3xl flex-1 flex-col gap-4 p-4">
        <h1 class="text-lg font-semibold">Rol Yönetimi</h1>

        <Card
            v-for="user in users.data"
            :key="user.id"
            class="flex flex-col gap-3 p-4"
        >
            <div class="flex items-center justify-between gap-2">
                <div>
                    <p class="font-medium">{{ user.name }}</p>
                    <p class="text-sm text-muted-foreground">
                        {{ user.email }}
                    </p>
                </div>
                <div class="flex flex-wrap gap-1">
                    <Badge
                        v-for="role in user.roles"
                        :key="role"
                        variant="secondary"
                        class="cursor-pointer"
                        @click="revoke(user.id, role)"
                    >
                        {{ roleLabels[role] ?? role }} ✕
                    </Badge>
                    <span
                        v-if="user.roles.length === 0"
                        class="text-xs text-muted-foreground"
                    >
                        Rol yok
                    </span>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <Select v-model="selected[user.id]">
                    <SelectTrigger class="h-8 w-44">
                        <SelectValue placeholder="Rol seç" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="role in availableRoles"
                            :key="role"
                            :value="role"
                        >
                            {{ roleLabels[role] ?? role }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <Button type="button" size="sm" @click="assign(user.id)">
                    Ata
                </Button>
            </div>
        </Card>
    </div>
</template>
