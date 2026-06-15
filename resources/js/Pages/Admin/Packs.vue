<script setup>
import { computed, ref } from "vue";
import { router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import Badge from "@/Components/Badge.vue";
import Card from "@/Components/Card.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import DangerButton from "@/Components/DangerButton.vue";
import TextInput from "@/Components/TextInput.vue";
import ResourcePackInstallModal from "@/Components/ResourcePackInstallModal.vue";

const props = defineProps({
    installed: { type: Array, default: () => [] },
    hubPacks: { type: Array, default: () => [] },
    defaultId: { type: [Number, null], default: null },
});

const installer = ref(null);

// --- Installed packs (manage) ---
const installedSearch = ref('');
const sort = ref('alias');

const installedView = computed(() => {
    const needle = installedSearch.value.toLowerCase();
    const list = props.installed.filter((p) =>
        !needle || p.alias.toLowerCase().includes(needle) || p.name.toLowerCase().includes(needle));

    return list.slice().sort((a, b) => {
        if (sort.value === 'most') return b.users_count - a.users_count;
        if (sort.value === 'least') return a.users_count - b.users_count;
        return a.alias.localeCompare(b.alias);
    });
});

const remove = (pack) => {
    if (!confirm(`Delete "${pack.alias}"? Anyone using it falls back to the default theme.`)) {
        return;
    }
    router.delete(route('admin.packs.destroy', pack.id), { preserveScroll: true });
};

// --- Browse the hub (install) ---
const browseSearch = ref('');

const installable = computed(() => {
    const needle = browseSearch.value.toLowerCase();
    return props.hubPacks
        .filter((p) => !p.installed)
        .filter((p) => !needle || p.alias.toLowerCase().includes(needle) || p.name.toLowerCase().includes(needle));
});

const install = (pack) => installer.value?.start(pack);
</script>

<template>
    <AppLayout title="Resource packs">
        <div class="py-12">
            <div class="mx-auto max-w-5xl space-y-6 sm:px-6 lg:px-8">
                <h1 class="header-chatbox-sword text-2xl font-bold">Resource packs</h1>

                <!-- Installed packs management -->
                <Card>
                    <div class="flex flex-wrap items-baseline justify-between gap-3">
                        <h2 class="text-lg font-bold">
                            Installed <span class="text-base-content/50">({{ installed.length }})</span>
                        </h2>
                        <div class="flex items-center gap-2">
                            <select v-model="sort" class="select select-bordered select-sm">
                                <option value="alias">Name A–Z</option>
                                <option value="most">Most used</option>
                                <option value="least">Least used</option>
                            </select>
                            <TextInput v-model="installedSearch" type="search" placeholder="Filter…" class="w-40" />
                        </div>
                    </div>

                    <div class="mt-4 overflow-x-auto">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Pack</th>
                                    <th>Installed by</th>
                                    <th class="text-right">In use</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="pack in installedView" :key="pack.id">
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <img v-if="pack.icon_url" :src="pack.icon_url" :alt="pack.alias"
                                                 class="h-8 w-8 rounded object-cover [image-rendering:pixelated]"
                                                 loading="lazy">
                                            <span class="font-medium">{{ pack.alias }}</span>
                                            <Badge v-if="pack.is_default" size="sm">Default</Badge>
                                        </div>
                                    </td>
                                    <td class="text-base-content/70">{{ pack.installed_by ?? 'Admin' }}</td>
                                    <td class="text-right tabular-nums">{{ pack.users_count }}</td>
                                    <td class="text-right">
                                        <DangerButton v-if="!pack.is_vanilla" type="button" class="btn-xs"
                                                      @click="remove(pack)">
                                            Delete
                                        </DangerButton>
                                    </td>
                                </tr>
                                <tr v-if="!installedView.length">
                                    <td colspan="4" class="text-center text-base-content/60">
                                        No installed packs match.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </Card>

                <!-- Browse the community hub -->
                <Card>
                    <div class="flex items-baseline justify-between gap-3">
                        <h2 class="text-lg font-bold">Browse the hub</h2>
                        <TextInput v-model="browseSearch" type="search" placeholder="Search packs…" class="w-48" />
                    </div>
                    <p class="mt-1 text-sm text-base-content/60">
                        Install a pack from the RuneLite community hub. Installed packs become selectable as the
                        instance theme and by members on their Appearance page.
                    </p>

                    <div v-if="!hubPacks.length"
                         class="mt-4 rounded p-6 text-center text-base-content/60 pack-bg-card resource-pack-border">
                        Couldn't reach the resource-pack hub right now. Try again later.
                    </div>

                    <div v-else-if="!installable.length"
                         class="mt-4 rounded p-6 text-center text-base-content/60 pack-bg-card resource-pack-border">
                        Nothing new to install — every hub pack is already installed.
                    </div>

                    <div v-else class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">
                        <div v-for="pack in installable" :key="pack.name"
                             class="flex flex-col items-center gap-2 rounded p-3 text-center pack-bg-card resource-pack-border">
                            <img :src="pack.icon_url" :alt="pack.alias"
                                 class="h-16 w-16 rounded object-cover [image-rendering:pixelated]"
                                 loading="lazy" onerror="this.style.visibility='hidden'">
                            <span class="max-w-full truncate text-sm font-medium" :title="pack.alias">{{ pack.alias }}</span>
                            <PrimaryButton type="button" class="btn-xs" @click="install(pack)">
                                Install
                            </PrimaryButton>
                        </div>
                    </div>
                </Card>
            </div>
        </div>

        <ResourcePackInstallModal ref="installer"
                                  :install-url="route('admin.packs.install')"
                                  :status-url="route('user.resource-pack.status')" />
    </AppLayout>
</template>
