<script setup>
import { computed, ref } from "vue";
import { router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import Card from "@/Components/Card.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import ResourcePackPicker from "@/Components/ResourcePackPicker.vue";

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
const installing = ref(null);

const installable = computed(() => {
    const needle = search.value.toLowerCase();
    return props.hubPacks
        .filter((p) => !p.installed)
        .filter((p) => !needle
            || p.alias.toLowerCase().includes(needle)
            || p.name.toLowerCase().includes(needle));
});

// Installing applies the pack as the user's personal theme and queues the
// download; reload so the new theme + updated lists take effect.
const install = (pack) => {
    installing.value = pack.name;
    router.post(route('user.resource-pack.install'), { name: pack.name }, {
        preserveScroll: true,
        onSuccess: () => window.location.reload(),
        onError: () => { installing.value = null; },
    });
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
                                           :class="{ 'opacity-25': installing === pack.name }"
                                           :disabled="installing === pack.name"
                                           @click="install(pack)">
                                {{ installing === pack.name ? 'Installing…' : 'Install' }}
                            </PrimaryButton>
                        </div>
                    </div>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
