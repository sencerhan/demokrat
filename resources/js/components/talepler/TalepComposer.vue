<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { computed } from 'vue';
import { toast } from 'vue-sonner';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';
import { useAuth } from '@/composables/useAuth';
import { getInitials } from '@/composables/useInitials';
import { store } from '@/routes/talepler';

const { user } = useAuth();
const initials = computed(() => getInitials(user.value?.name ?? ''));

const onSuccess = () => toast.success('Talebiniz oluşturuldu.');
</script>

<template>
    <div class="flex gap-3 border-b border-border/60 px-4 py-3">
        <Avatar
            class="size-10 shrink-0 bg-linear-to-b from-indigo-500 to-violet-600"
        >
            <AvatarFallback
                class="bg-transparent text-sm font-semibold text-white"
            >
                {{ initials }}
            </AvatarFallback>
        </Avatar>

        <Form
            :action="store.url()"
            method="post"
            :options="{ preserveScroll: true }"
            reset-on-success
            class="flex flex-1 flex-col gap-2"
            @success="onSuccess"
            v-slot="{ errors, processing }"
        >
            <Textarea
                name="body"
                rows="2"
                placeholder="Talebiniz nedir?"
                class="resize-none border-0 bg-transparent px-0 text-[15px] shadow-none focus-visible:ring-0"
                :aria-invalid="Boolean(errors.body)"
            />
            <p v-if="errors.body" class="text-sm text-destructive">
                {{ errors.body }}
            </p>
            <div class="flex justify-end border-t border-border/40 pt-2">
                <Button
                    type="submit"
                    :disabled="processing"
                    class="rounded-full px-5"
                >
                    Paylaş
                </Button>
            </div>
        </Form>
    </div>
</template>
