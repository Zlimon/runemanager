<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import AdminNav from '@/Layouts/Partials/AdminNav.vue';

/*
 * Shared chrome for the admin area: AppLayout plus a sub-navigation
 * (AdminNav) grouping every admin surface. Every page under
 * resources/js/Pages/Admin/** renders through this instead of AppLayout
 * directly, so the sub-nav and page heading stay consistent. The optional
 * `actions` slot puts page-specific buttons (e.g. "Add member") next to the
 * heading, replacing the ad-hoc button rows individual pages used to draw
 * themselves.
 */
defineProps({
    title: { type: String, required: true },
});
</script>

<template>
    <AppLayout :title="title">
        <div class="py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6 flex flex-wrap items-baseline justify-between gap-2">
                    <h1 class="header-chatbox-sword text-2xl font-bold">{{ title }}</h1>
                    <div v-if="$slots.actions" class="flex flex-wrap gap-2">
                        <slot name="actions" />
                    </div>
                </div>

                <div class="flex flex-col gap-8 lg:flex-row">
                    <AdminNav />

                    <div class="min-w-0 flex-1">
                        <slot />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
