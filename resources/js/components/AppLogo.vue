<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';

const name = usePage().props.name;
</script>

<template>
    <div
        class="flex aspect-square size-8 items-center justify-center rounded-lg bg-linear-to-b from-indigo-500 to-violet-600 shadow-sm ring-1 ring-white/10"
    >
        <AppLogoIcon class="size-5 text-white" />
    </div>
    <div class="ml-2 grid flex-1 text-left text-sm">
        <span
            class="mb-0.5 truncate leading-tight font-semibold tracking-tight"
        >
            {{ name }}
        </span>
    </div>
</template>
