<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

/*
 * Shared chrome for the settings hub: a left section list + a content slot on
 * the right. Account sections are available to everyone; the Instance group is
 * admin-only. Each section is its own route, so the sidebar is plain Inertia
 * navigation with the active link highlighted.
 */
defineProps({
    title: { type: String, default: 'Settings' },
});

const page = usePage();

const accountSections = computed(() => [
    { label: 'Profile', route: 'profile.show', active: 'profile.show' },
    { label: 'Appearance', route: 'themes.index', active: 'themes.*' },
    ...(page.props.jetstream?.hasApiFeatures
        ? [{ label: 'API tokens', route: 'api-tokens.index', active: 'api-tokens.*' }]
        : []),
]);

const instanceSections = computed(() => page.props.is_admin ? [
    { label: 'General', route: 'admin.settings', active: 'admin.settings' },
    { label: 'Branding', route: 'admin.branding', active: 'admin.branding' },
    { label: 'Feed & sync', route: 'admin.feed', active: 'admin.feed' },
    { label: 'Integrations', route: 'admin.integrations', active: 'admin.integrations' },
] : []);

const groups = computed(() => [
    { heading: 'Account', sections: accountSections.value },
    ...(instanceSections.value.length ? [{ heading: 'Instance', sections: instanceSections.value }] : []),
]);
</script>

<template>
    <AppLayout :title="title">
        <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
            <h1 class="header-chatbox-sword mb-8 text-center text-2xl font-bold">Settings</h1>

            <div class="flex flex-col gap-8 md:flex-row">
                <!-- Section list -->
                <nav class="shrink-0 md:w-56">
                    <div v-for="group in groups" :key="group.heading" class="mb-4">
                        <p class="px-3 pb-1 text-xs font-semibold uppercase tracking-wide text-base-content/50">
                            {{ group.heading }}
                        </p>
                        <ul class="space-y-1">
                            <li v-for="section in group.sections" :key="section.route">
                                <Link :href="route(section.route)"
                                      class="block rounded px-3 py-2 text-sm"
                                      :class="route().current(section.active)
                                          ? 'bg-base-300 font-semibold text-base-content'
                                          : 'text-base-content/70 hover:bg-base-300/50'">
                                    {{ section.label }}
                                </Link>
                            </li>
                        </ul>
                    </div>
                </nav>

                <!-- Section content -->
                <div class="min-w-0 flex-1">
                    <h2 class="mb-4 text-xl font-bold">{{ title }}</h2>
                    <slot />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
