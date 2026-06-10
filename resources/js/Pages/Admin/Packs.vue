<script setup>
import { computed, ref } from "vue";
import { router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import Card from "@/Components/Card.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";

const props = defineProps({
    packs: { type: Array, default: () => [] },
    defaultId: { type: [Number, null], default: null },
});

const search = ref('');
const installing = ref(null);

const filtered = computed(() => {
    if (!search.value) {
        return props.packs;
    }
    const needle = search.value.toLowerCase();
    return props.packs.filter((p) => p.alias.toLowerCase().includes(needle) || p.name.toLowerCase().includes(needle));
});

const install = (pack) => {
    installing.value = pack.name;
    router.post(route('admin.packs.install'), { name: pack.name }, {
        preserveScroll: true,
        onFinish: () => { installing.value = null; },
    });
};
</script>

<template>
    <AppLayout title="Resource packs">
        <div class="py-12">
            <div class="mx-auto max-w-5xl space-y-6 sm:px-6 lg:px-8">
                <div class="flex items-baseline justify-between gap-3">
                    <h1 class="header-chatbox-sword text-2xl font-bold">Resource packs</h1>
                    <TextInput v-model="search" type="search" placeholder="Search packs…" class="w-48" />
                </div>
                <p class="text-sm text-base-content/60">
                    Browse the RuneLite community hub and install packs. Installed packs become selectable as
                    the instance theme and by members on their Appearance page.
                </p>

                <div v-if="!packs.length"
                     class="rounded p-6 text-center text-base-content/60 pack-bg-card resource-pack-border">
                    Couldn't reach the resource-pack hub right now. Try again later.
                </div>

                <div v-else class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">
                    <Card v-for="pack in filtered" :key="pack.name" padding="p-3">
                        <div class="flex flex-col items-center gap-2 text-center">
                            <img :src="pack.icon_url" :alt="pack.alias"
                                 class="h-16 w-16 rounded object-cover [image-rendering:pixelated]"
                                 loading="lazy" onerror="this.style.visibility='hidden'">
                            <span class="max-w-full truncate text-sm font-medium" :title="pack.alias">{{ pack.alias }}</span>
                            <span v-if="pack.installed" class="badge badge-success badge-sm">Installed</span>
                            <PrimaryButton v-else type="button" class="btn-xs"
                                           :class="{ 'opacity-25': installing === pack.name }"
                                           :disabled="installing === pack.name"
                                           @click="install(pack)">
                                {{ installing === pack.name ? 'Installing…' : 'Install' }}
                            </PrimaryButton>
                        </div>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
