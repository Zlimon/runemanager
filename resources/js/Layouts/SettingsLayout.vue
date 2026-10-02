<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import SettingsNav from '@/Layouts/Partials/SettingsNav.vue';

/*
 * The one frame for every account settings and admin page: SettingsNav on the
 * left, the page title (with an optional `actions` slot for page buttons such
 * as "Add member") and content on the right. Both areas render through this
 * same layout so the sidebar never moves when navigating between them.
 */
defineProps({
    title: { type: String, default: 'Settings' },
});
</script>

<template>
    <AppLayout :title="title">
        <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-8 md:flex-row">
                <SettingsNav />

                <div class="min-w-0 flex-1">
                    <div class="mb-6 flex flex-wrap items-baseline justify-between gap-2">
                        <h1 class="header-chatbox-sword text-2xl font-bold">{{ title }}</h1>
                        <div v-if="$slots.actions" class="flex flex-wrap gap-2">
                            <slot name="actions" />
                        </div>
                    </div>

                    <slot />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
