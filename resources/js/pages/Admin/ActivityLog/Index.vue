<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import { Card } from '@/components/ui/card';
import { index as activityLogIndex } from '@/routes/admin/activity-log';

type Activity = {
    id: number;
    log_name: string | null;
    description: string;
    event: string | null;
    subject_type: string | null;
    subject_id: number | null;
    causer: { id: number; name: string | null } | null;
    properties: Record<string, unknown>;
    created_at: string | null;
};

type Paginated = {
    data: Activity[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
};

defineProps<{
    activities: Paginated;
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Aktivite Günlüğü', href: activityLogIndex() }],
    },
});

const formatDate = (value: string | null): string =>
    value ? new Date(value).toLocaleString('tr-TR') : '';
</script>

<template>
    <Head title="Aktivite Günlüğü" />

    <div class="mx-auto flex w-full max-w-3xl flex-1 flex-col gap-3 p-4">
        <h1 class="text-lg font-semibold">Aktivite Günlüğü</h1>

        <Card
            v-for="activity in activities.data"
            :key="activity.id"
            class="flex flex-col gap-1 p-3"
        >
            <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <Badge variant="outline">{{ activity.log_name }}</Badge>
                    <span class="text-sm font-medium">{{
                        activity.description
                    }}</span>
                </div>
                <span class="text-xs text-muted-foreground">
                    {{ formatDate(activity.created_at) }}
                </span>
            </div>
            <p class="text-xs text-muted-foreground">
                {{ activity.causer?.name ?? 'Sistem' }}
                <template v-if="activity.subject_type">
                    · {{ activity.subject_type }}#{{ activity.subject_id }}
                </template>
            </p>
        </Card>

        <p
            v-if="activities.data.length === 0"
            class="py-8 text-center text-sm text-muted-foreground"
        >
            Kayıt yok.
        </p>
    </div>
</template>
