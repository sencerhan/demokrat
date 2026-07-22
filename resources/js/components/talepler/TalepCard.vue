<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { MoreHorizontal, Pencil, Trash2 } from '@lucide/vue';
import { computed, ref } from 'vue';
import { toast } from 'vue-sonner';
import VoteButtons from '@/components/talepler/VoteButtons.vue';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Textarea } from '@/components/ui/textarea';
import { getInitials } from '@/composables/useInitials';
import {
    destroy as talepDestroy,
    update as talepUpdate,
} from '@/routes/talepler';
import { update as statusUpdate } from '@/routes/talepler/status';
import type { Talep, TalepStatus } from '@/types';

const props = defineProps<{
    talep: Talep;
}>();

const statusLabels: Record<TalepStatus, string> = {
    pending: 'Beklemede',
    reviewed: 'İncelendi',
    accepted: 'Kabul edildi',
    rejected: 'Reddedildi',
};

const statusVariants: Record<
    TalepStatus,
    'default' | 'secondary' | 'destructive' | 'outline'
> = {
    pending: 'secondary',
    reviewed: 'outline',
    accepted: 'default',
    rejected: 'destructive',
};

const isEditing = ref(false);
const editBody = ref(props.talep.body);

const authorName = computed(() => props.talep.author.name ?? 'Bilinmeyen üye');
const hasControls = computed(
    () =>
        props.talep.can.update ||
        props.talep.can.delete ||
        props.talep.can.update_status,
);

const startEdit = () => {
    editBody.value = props.talep.body;
    isEditing.value = true;
};

const saveEdit = () => {
    router.patch(
        talepUpdate.url(props.talep.id),
        { body: editBody.value },
        {
            preserveScroll: true,
            onSuccess: () => {
                isEditing.value = false;
            },
            onError: () => toast.error('Talep güncellenemedi.'),
        },
    );
};

const changeStatus = (status: string) => {
    router.patch(
        statusUpdate.url(props.talep.id),
        { status },
        {
            preserveScroll: true,
            onError: () => toast.error('Durum güncellenemedi.'),
        },
    );
};

const remove = () => {
    if (!window.confirm('Bu talebi silmek istediğinize emin misiniz?')) {
        return;
    }

    router.delete(talepDestroy.url(props.talep.id), {
        preserveScroll: true,
        onError: () => toast.error('Talep silinemedi.'),
    });
};
</script>

<template>
    <article
        class="flex gap-3 border-b border-border/60 px-4 py-3 transition-colors hover:bg-muted/30"
    >
        <Avatar
            class="size-10 shrink-0 bg-linear-to-b from-indigo-500 to-violet-600"
        >
            <AvatarFallback
                class="bg-transparent text-sm font-semibold text-white"
            >
                {{ getInitials(authorName) }}
            </AvatarFallback>
        </Avatar>

        <div class="flex min-w-0 flex-1 flex-col gap-1.5">
            <div class="flex items-center gap-2">
                <span class="truncate font-semibold">{{ authorName }}</span>
                <Badge :variant="statusVariants[talep.status]" class="shrink-0">
                    {{ statusLabels[talep.status] }}
                </Badge>

                <DropdownMenu v-if="hasControls">
                    <DropdownMenuTrigger as-child>
                        <Button
                            type="button"
                            variant="ghost"
                            size="icon-sm"
                            class="ml-auto shrink-0 text-muted-foreground"
                            aria-label="Seçenekler"
                        >
                            <MoreHorizontal class="size-4" />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" class="w-48">
                        <DropdownMenuItem
                            v-if="talep.can.update"
                            @click="startEdit"
                        >
                            <Pencil class="size-4" /> Düzenle
                        </DropdownMenuItem>

                        <template v-if="talep.can.update_status">
                            <DropdownMenuSeparator />
                            <DropdownMenuLabel>Durum</DropdownMenuLabel>
                            <DropdownMenuItem
                                v-for="(label, value) in statusLabels"
                                :key="value"
                                :disabled="talep.status === value"
                                @click="changeStatus(value)"
                            >
                                {{ label }}
                            </DropdownMenuItem>
                        </template>

                        <template v-if="talep.can.delete">
                            <DropdownMenuSeparator />
                            <DropdownMenuItem
                                class="text-destructive focus:text-destructive"
                                @click="remove"
                            >
                                <Trash2 class="size-4" /> Sil
                            </DropdownMenuItem>
                        </template>
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>

            <template v-if="isEditing">
                <Textarea v-model="editBody" rows="4" />
                <div class="flex items-center gap-2">
                    <Button type="button" size="sm" @click="saveEdit">
                        Kaydet
                    </Button>
                    <Button
                        type="button"
                        size="sm"
                        variant="ghost"
                        @click="isEditing = false"
                    >
                        Vazgeç
                    </Button>
                </div>
            </template>
            <p v-else class="text-[15px] wrap-break-word whitespace-pre-wrap">
                {{ talep.body }}
            </p>

            <div class="mt-1 flex items-center">
                <VoteButtons :talep="talep" />
            </div>
        </div>
    </article>
</template>
