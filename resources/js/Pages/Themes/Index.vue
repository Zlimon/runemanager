<script setup>
import { computed, ref } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import Card from "@/Components/Card.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import DangerButton from "@/Components/DangerButton.vue";
import TextInput from "@/Components/TextInput.vue";
import ResourcePackPicker from "@/Components/ResourcePackPicker.vue";
import ResourcePackInstallModal from "@/Components/ResourcePackInstallModal.vue";

const props = defineProps({
    packs: { type: Array, default: () => [] },
    hubPacks: { type: Array, default: () => [] },
    selectedId: { type: [Number, null], default: null },
    defaultId: { type: [Number, null], default: null },
    installLimit: { type: Number, default: 0 },
    installedCount: { type: Number, default: 0 },
    canInstall: { type: Boolean, default: true },
});

const currentUserId = computed(() => usePage().props.auth?.user?.id ?? null);

// Packs this member installed — the only ones they may delete.
const myPacks = computed(() =>
    props.packs.filter((p) => p.installed_by_user_id && p.installed_by_user_id === currentUserId.value));

const removePack = (pack) => {
    if (!confirm(`Delete "${pack.alias}"? Anyone using it falls back to the default theme.`)) {
        return;
    }
    router.delete(route('user.resource-pack.destroy', pack.id), {
        preserveScroll: true,
        onSuccess: () => window.location.reload(),
    });
};

const saving = ref(false);
const selected = ref(props.selectedId ?? '');

// A resource pack is applied via a server-rendered <link> in the Blade root, so
// swapping it needs a full page load — reload once the preference is saved.
const choose = (id) => {
    if (saving.value || String(id ?? '') === String(props.selectedId ?? '')) {
        return;
    }
    selected.value = id;
    saving.value = true;
    router.put(route('user.resource-pack.update'),
        { resource_pack_id: id === '' ? null : id },
        {
            preserveScroll: true,
            onSuccess: () => window.location.reload(),
            onError: () => { saving.value = false; selected.value = props.selectedId ?? ''; },
        });
};

// Browse the community hub. Only packs that aren't installed yet are offered
// here — installed ones live in the picker above.
const search = ref('');

const installable = computed(() => {
    const needle = search.value.toLowerCase();
    return props.hubPacks
        .filter((p) => !p.installed)
        .filter((p) => !needle
            || p.alias.toLowerCase().includes(needle)
            || p.name.toLowerCase().includes(needle));
});

// Installing is handled by ResourcePackInstallModal (POST + poll-to-ready + the
// loading rat). We just hand it the pack once the quota check passes.
const installer = ref(null);

const install = (pack) => {
    if (props.canInstall) {
        installer.value?.start(pack);
    }
};
</script>

<template>
    <AppLayout title="Appearance">
        <div class="py-12">
            <div class="mx-auto max-w-4xl space-y-6 sm:px-6 lg:px-8">
                <div>
                    <h1 class="header-chatbox-sword text-2xl font-bold">Appearance</h1>
                    <p class="mt-1 text-sm text-base-content/60">
                        Pick a resource pack to theme the site for you. Choosing <strong>Default</strong> uses
                        the instance theme. Your in-game pack (via the RuneLite plugin) also sets this automatically.
                    </p>
                </div>

                <Card>
                    <ResourcePackPicker :model-value="selected"
                                        :packs="packs"
                                        :default-id="defaultId"
                                        @update:model-value="choose" />
                    <p v-if="!packs.length" class="text-center text-base-content/60">
                        No resource packs installed yet.
                    </p>
                </Card>

                <Card v-if="myPacks.length">
                    <div class="flex items-baseline justify-between gap-3">
                        <h2 class="text-lg font-bold">Your packs</h2>
                        <span class="text-xs text-base-content/60">{{ installedCount }} of {{ installLimit }} installed</span>
                    </div>
                    <p class="mt-1 text-sm text-base-content/60">
                        Packs you installed from the hub. Deleting one frees a slot.
                    </p>
                    <ul class="mt-4 divide-y divide-base-300">
                        <li v-for="pack in myPacks" :key="pack.id"
                            class="flex items-center justify-between gap-3 py-2">
                            <div class="flex items-center gap-3">
                                <img v-if="pack.icon_url" :src="pack.icon_url" :alt="pack.alias"
                                     class="h-10 w-10 rounded object-cover [image-rendering:pixelated]">
                                <span class="text-sm font-medium">{{ pack.alias }}</span>
                            </div>
                            <DangerButton type="button" class="btn-xs" @click="removePack(pack)">
                                Delete
                            </DangerButton>
                        </li>
                    </ul>
                </Card>

                <Card>
                    <div class="flex items-baseline justify-between gap-3">
                        <h2 class="text-lg font-bold">Browse packs</h2>
                        <TextInput v-model="search" type="search" placeholder="Search packs…" class="w-44" />
                    </div>
                    <p class="mt-1 text-sm text-base-content/60">
                        Install a community pack from the RuneLite hub to add it to your themes.
                    </p>
                    <p v-if="!canInstall" class="mt-1 text-xs font-medium text-error">
                        You've reached your limit of {{ installLimit }} installed packs — delete one above to add another.
                    </p>

                    <div v-if="!hubPacks.length"
                         class="mt-4 rounded p-6 text-center text-base-content/60 pack-bg-card resource-pack-border">
                        Couldn't reach the resource-pack hub right now. Try again later.
                    </div>

                    <div v-else-if="!installable.length"
                         class="mt-4 rounded p-6 text-center text-base-content/60 pack-bg-card resource-pack-border">
                        Nothing new to install — every hub pack is already available above.
                    </div>

                    <div v-else class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">
                        <div v-for="pack in installable" :key="pack.name"
                             class="flex flex-col items-center gap-2 rounded p-3 text-center pack-bg-card resource-pack-border">
                            <img :src="pack.icon_url" :alt="pack.alias"
                                 class="h-16 w-16 rounded object-cover [image-rendering:pixelated]"
                                 loading="lazy" onerror="this.style.visibility='hidden'">
                            <span class="max-w-full truncate text-sm font-medium" :title="pack.alias">{{ pack.alias }}</span>
                            <PrimaryButton type="button" class="btn-xs"
                                           :class="{ 'opacity-25': !canInstall }"
                                           :disabled="!canInstall"
                                           @click="install(pack)">
                                Install
                            </PrimaryButton>
                        </div>
                    </div>
                </Card>
            </div>
        </div>

        <ResourcePackInstallModal ref="installer"
                                  :install-url="route('user.resource-pack.install')"
                                  :status-url="route('user.resource-pack.status')" />
    </AppLayout>
</template>
