<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ChevronDown, ChevronUp } from '@lucide/vue';
import { toast } from 'vue-sonner';
import { useAuth } from '@/composables/useAuth';
import { cn } from '@/lib/utils';
import { store as voteStore } from '@/routes/talepler/votes';
import type { Talep, VoteDirection } from '@/types';

const props = defineProps<{
    talep: Talep;
}>();

const { isAuthenticated } = useAuth();

const vote = (direction: VoteDirection) => {
    if (!isAuthenticated.value) {
        toast.error('Oy vermek için giriş yapmalısınız.');

        return;
    }

    router.post(
        voteStore.url(props.talep.id),
        { direction },
        {
            preserveScroll: true,
            preserveState: true,
            onError: () => toast.error('Oyunuz kaydedilemedi.'),
        },
    );
};
</script>

<template>
    <div class="flex items-center gap-1">
        <button
            type="button"
            :aria-pressed="talep.my_vote === 'up'"
            :class="
                cn(
                    'group flex items-center gap-1.5 rounded-full px-2.5 py-1 text-sm text-muted-foreground transition-colors hover:bg-emerald-500/10 hover:text-emerald-600 dark:hover:text-emerald-400',
                    talep.my_vote === 'up' &&
                        'text-emerald-600 dark:text-emerald-400',
                )
            "
            @click="vote('up')"
        >
            <ChevronUp class="size-4" />
            <span class="tabular-nums">{{ talep.upvotes_count }}</span>
        </button>

        <button
            type="button"
            :aria-pressed="talep.my_vote === 'down'"
            :class="
                cn(
                    'group flex items-center gap-1.5 rounded-full px-2.5 py-1 text-sm text-muted-foreground transition-colors hover:bg-red-500/10 hover:text-red-600 dark:hover:text-red-400',
                    talep.my_vote === 'down' &&
                        'text-red-600 dark:text-red-400',
                )
            "
            @click="vote('down')"
        >
            <ChevronDown class="size-4" />
            <span class="tabular-nums">{{ talep.downvotes_count }}</span>
        </button>

        <span
            class="ml-1 text-xs font-medium tabular-nums"
            :class="
                talep.score > 0
                    ? 'text-emerald-600 dark:text-emerald-400'
                    : talep.score < 0
                      ? 'text-red-600 dark:text-red-400'
                      : 'text-muted-foreground'
            "
        >
            {{ talep.score > 0 ? '+' : '' }}{{ talep.score }}
        </span>
    </div>
</template>
