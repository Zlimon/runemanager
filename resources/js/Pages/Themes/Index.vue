<script setup>
import { ref } from "vue";
import { router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import Card from "@/Components/Card.vue";
import ResourcePackPicker from "@/Components/ResourcePackPicker.vue";

const props = defineProps({
    packs: { type: Array, default: () => [] },
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
</script>

<template>
    <AppLayout title="Appearance">
        <div class="py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <h1 class="header-chatbox-sword text-2xl font-bold">Appearance</h1>
                <p class="mt-1 text-sm text-base-content/60">
                    Pick a resource pack to theme the site for you. Choosing <strong>Default</strong> uses
                    the instance theme. Your in-game pack (via the RuneLite plugin) also sets this automatically.
                </p>

                <Card class="mt-6">
                    <ResourcePackPicker :model-value="selected"
                                        :packs="packs"
                                        :default-id="defaultId"
                                        @update:model-value="choose" />
                    <p v-if="!packs.length" class="text-center text-base-content/60">
                        No resource packs installed yet.
                    </p>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
