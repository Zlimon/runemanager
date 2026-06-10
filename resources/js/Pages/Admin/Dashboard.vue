<script setup>
import { Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import Card from "@/Components/Card.vue";

const props = defineProps({
    stats: { type: Object, required: true },
    mode: { type: String, required: true },
    instanceName: { type: String, default: null },
});

const cards = [
    { key: 'users', label: 'Users' },
    { key: 'accounts', label: 'Accounts' },
    { key: 'announcements', label: 'Announcements' },
];

const modeLabel = (mode) => mode.charAt(0).toUpperCase() + mode.slice(1);
</script>

<template>
    <AppLayout title="Admin">
        <div class="py-12">
            <div class="mx-auto max-w-5xl space-y-8 sm:px-6 lg:px-8">
                <div class="flex items-baseline justify-between gap-2">
                    <h1 class="header-chatbox-sword text-2xl font-bold">Admin</h1>
                    <div class="flex flex-wrap gap-2">
                        <Link v-if="mode !== 'casual'" :href="route('admin.members')" class="btn btn-neutral btn-sm">
                            Members
                        </Link>
                        <Link :href="route('admin.packs')" class="btn btn-neutral btn-sm">
                            Resource packs
                        </Link>
                        <Link :href="route('admin.settings')" class="btn btn-neutral btn-sm">
                            Instance settings
                        </Link>
                    </div>
                </div>

                <Card>
                    <p class="text-sm text-base-content/60">Instance mode</p>
                    <p class="mt-1 text-lg font-semibold">
                        {{ modeLabel(mode) }}
                        <span v-if="instanceName" class="text-base-content/70">— {{ instanceName }}</span>
                    </p>
                </Card>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <Card v-for="card in cards" :key="card.key">
                        <p class="text-sm text-base-content/60">{{ card.label }}</p>
                        <p class="mt-1 text-3xl font-bold">{{ stats[card.key] }}</p>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
