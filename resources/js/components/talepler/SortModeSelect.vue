<script setup lang="ts">
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type { TalepSortMode } from '@/types';

defineProps<{
    modelValue: TalepSortMode;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: TalepSortMode): void;
}>();

const options: Array<{ value: TalepSortMode; label: string }> = [
    { value: 'random', label: 'Rastgele' },
    { value: 'most_votes', label: 'En çok oylanan' },
    { value: 'most_supported', label: 'En çok desteklenen' },
    { value: 'least_supported', label: 'En az desteklenen' },
];
</script>

<template>
    <Select
        :model-value="modelValue"
        @update:model-value="emit('update:modelValue', $event as TalepSortMode)"
    >
        <SelectTrigger class="w-52">
            <SelectValue />
        </SelectTrigger>
        <SelectContent>
            <SelectItem
                v-for="option in options"
                :key="option.value"
                :value="option.value"
            >
                {{ option.label }}
            </SelectItem>
        </SelectContent>
    </Select>
</template>
