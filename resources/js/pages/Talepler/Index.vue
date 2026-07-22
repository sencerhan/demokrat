<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { useEchoPublic } from '@laravel/echo-vue';
import { useIntersectionObserver } from '@vueuse/core';
import { ref, watch } from 'vue';
import SortModeSelect from '@/components/talepler/SortModeSelect.vue';
import TalepCard from '@/components/talepler/TalepCard.vue';
import TalepComposer from '@/components/talepler/TalepComposer.vue';
import { useAuth } from '@/composables/useAuth';
import { home } from '@/routes';
import type { Talep, TalepSortMode } from '@/types';

const props = defineProps<{
    talepler: Talep[];
    sortMode: TalepSortMode;
    seed: number | null;
    nextCursor: string | null;
    hasMore: boolean;
}>();

const { isAuthenticated, roles } = useAuth();

const items = ref<Talep[]>([...props.talepler]);
const cursor = ref<string | null>(props.nextCursor);
const hasMore = ref<boolean>(props.hasMore);
const mode = ref<TalepSortMode>(props.sortMode);
const loading = ref(false);
const sentinel = ref<HTMLElement | null>(null);

const canModerate = (): boolean =>
    ['moderator', 'admin', 'superadmin'].some((r) => roles.value.includes(r));

const changeMode = (value: TalepSortMode) => {
    mode.value = value;
    router.get(home.url({ query: { mode: value } }), undefined, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        only: ['talepler', 'sortMode', 'seed', 'nextCursor', 'hasMore'],
        onSuccess: () => {
            items.value = [...props.talepler];
            cursor.value = props.nextCursor;
            hasMore.value = props.hasMore;
        },
    });
};

const loadMore = () => {
    if (loading.value || !hasMore.value || cursor.value === null) {
        return;
    }

    loading.value = true;

    const query: Record<string, string> = {
        mode: mode.value,
        cursor: cursor.value,
    };

    if (props.seed !== null) {
        query.seed = String(props.seed);
    }

    router.get(
        home.url({ query }),
        {},
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            only: ['talepler', 'nextCursor', 'hasMore'],
            onSuccess: () => {
                const existing = new Set(items.value.map((t) => t.id));

                for (const talep of props.talepler) {
                    if (!existing.has(talep.id)) {
                        items.value.push(talep);
                    }
                }

                cursor.value = props.nextCursor;
                hasMore.value = props.hasMore;
            },
            onFinish: () => {
                loading.value = false;
            },
        },
    );
};

useIntersectionObserver(sentinel, ([entry]) => {
    if (entry?.isIntersecting) {
        loadMore();
    }
});

// Live updates over Reverb (public "talepler" channel).
useEchoPublic<{ id: number } & Partial<Talep>>(
    'talepler',
    '.talep.vote-cast',
    (e) => {
        const talep = items.value.find((t) => t.id === e.id);

        if (talep) {
            talep.upvotes_count = e.upvotes_count ?? talep.upvotes_count;
            talep.downvotes_count = e.downvotes_count ?? talep.downvotes_count;
            talep.total_votes_count =
                e.total_votes_count ?? talep.total_votes_count;
            talep.score = e.score ?? talep.score;
        }
    },
);

useEchoPublic<{ id: number; status: Talep['status'] }>(
    'talepler',
    '.talep.status-updated',
    (e) => {
        const talep = items.value.find((t) => t.id === e.id);

        if (talep) {
            talep.status = e.status;
        }
    },
);

useEchoPublic<Talep>('talepler', '.talep.created', (e) => {
    if (items.value.some((t) => t.id === e.id)) {
        return;
    }

    items.value.unshift({
        ...e,
        my_vote: null,
        can: {
            update: canModerate(),
            update_status: canModerate(),
            delete: canModerate(),
        },
    });
});

watch(
    () => props.sortMode,
    (value) => {
        mode.value = value;
    },
);
</script>

<template>
    <Head title="Talepler" />

    <div class="mx-auto w-full max-w-2xl flex-1 border-x border-border/60">
        <header
            class="sticky top-0 z-10 flex items-center justify-between gap-2 border-b border-border/60 bg-background/80 px-4 py-3 backdrop-blur-md"
        >
            <h1 class="text-xl font-bold tracking-tight">Talepler</h1>
            <SortModeSelect
                :model-value="mode"
                @update:model-value="changeMode"
            />
        </header>

        <TalepComposer v-if="isAuthenticated" />

        <div class="flex flex-col">
            <TalepCard v-for="talep in items" :key="talep.id" :talep="talep" />
        </div>

        <div
            v-if="items.length === 0"
            class="flex flex-col items-center gap-2 px-4 py-16 text-center"
        >
            <p class="text-base font-semibold">Henüz talep yok.</p>
            <p class="text-sm text-muted-foreground">
                İlk talebi oluşturan siz olun.
            </p>
        </div>

        <div ref="sentinel" class="h-8"></div>

        <p
            v-if="loading"
            class="py-3 text-center text-sm text-muted-foreground"
        >
            Yükleniyor...
        </p>
        <p
            v-else-if="!hasMore && items.length > 0"
            class="py-4 text-center text-xs text-muted-foreground"
        >
            Hepsi bu kadar.
        </p>
    </div>
</template>
