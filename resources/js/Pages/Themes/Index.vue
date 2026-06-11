<script setup>
import { computed, onBeforeUnmount, ref } from "vue";
import { router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import Card from "@/Components/Card.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import ResourcePackPicker from "@/Components/ResourcePackPicker.vue";
import Loader from "@/Components/Loader.vue";

const props = defineProps({
    packs: { type: Array, default: () => [] },
    hubPacks: { type: Array, default: () => [] },
    selectedId: { type: [Number, null], default: null },
    defaultId: { type: [Number, null], default: null },
});

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

// Installing a pack queues an async download. Rather than reload straight away
// (which would flash to the default theme while the job is still running), keep
// the user here under a spinner and poll until the assets land, then reload so
// the server-rendered theme picks them up.
const installingPack = ref(null);
const installTimedOut = ref(false);
const POLL_INTERVAL = 2000;
const POLL_TIMEOUT = 90_000;

let pollTimer = null;
let pollDeadline = 0;

const stopPolling = () => {
    if (pollTimer) {
        clearTimeout(pollTimer);
        pollTimer = null;
    }
};

const pollStatus = (name) => {
    pollTimer = setTimeout(() => {
        window.axios.get(route('user.resource-pack.status'), { params: { name } })
            .then(({ data }) => {
                if (data.installed) {
                    window.location.reload();
                } else if (Date.now() > pollDeadline) {
                    installTimedOut.value = true;
                } else {
                    pollStatus(name);
                }
            })
            .catch(() => {
                if (Date.now() > pollDeadline) {
                    installTimedOut.value = true;
                } else {
                    pollStatus(name);
                }
            });
    }, POLL_INTERVAL);
};

const install = (pack) => {
    if (installingPack.value) {
        return;
    }
    installingPack.value = pack;
    installTimedOut.value = false;

    window.axios.post(route('user.resource-pack.install'), { name: pack.name })
        .then(({ data }) => {
            if (data.installed) {
                window.location.reload();
                return;
            }
            pollDeadline = Date.now() + POLL_TIMEOUT;
            pollStatus(pack.name);
        })
        .catch(() => { installTimedOut.value = true; });
};

const dismissInstall = () => {
    stopPolling();
    installingPack.value = null;
    installTimedOut.value = false;
};

onBeforeUnmount(stopPolling);
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

                <Card>
                    <div class="flex items-baseline justify-between gap-3">
                        <h2 class="header-chatbox-sword text-lg font-bold">Browse packs</h2>
                        <TextInput v-model="search" type="search" placeholder="Search packs…" class="w-44" />
                    </div>
                    <p class="mt-1 text-sm text-base-content/60">
                        Install a community pack from the RuneLite hub to add it to your themes.
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
                                           :class="{ 'opacity-25': installingPack }"
                                           :disabled="!!installingPack"
                                           @click="install(pack)">
                                {{ installingPack?.name === pack.name ? 'Installing…' : 'Install' }}
                            </PrimaryButton>
                        </div>
                    </div>
                </Card>
            </div>
        </div>

        <!-- Centered install modal: circular loading rat while the queued
             download runs, with a graceful fallback if it takes unusually long. -->
        <div v-if="installingPack"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">
            <div class="w-full max-w-sm rounded-lg p-8 text-center shadow-xl pack-bg-card resource-pack-border">
                <Loader v-if="!installTimedOut" bare :loading="true">
                    <h3 class="text-lg font-semibold">Installing {{ installingPack.alias }}…</h3>
                    <p class="text-sm text-base-content/70">
                        Downloading the pack and applying your theme. This usually takes a few seconds.
                    </p>
                </Loader>
                <template v-else>
                    <h3 class="text-lg font-semibold">Still working on it</h3>
                    <p class="mt-1 text-sm text-base-content/70">
                        {{ installingPack.alias }} is taking longer than usual to download. Your theme will
                        apply automatically the next time it's ready — no need to install again.
                    </p>
                    <PrimaryButton type="button" class="mt-4" @click="dismissInstall">
                        Got it
                    </PrimaryButton>
                </template>
            </div>
        </div>
    </AppLayout>
</template>
