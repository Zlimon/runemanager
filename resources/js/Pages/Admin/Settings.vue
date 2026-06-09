<script setup>
import { computed } from "vue";
import { useForm } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import Card from "@/Components/Card.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";

const props = defineProps({
    settings: { type: Object, required: true },
    modes: { type: Array, required: true },
    packs: { type: Array, default: () => [] },
});

const form = useForm({
    instance_mode: props.settings.instance_mode,
    clan_name: props.settings.clan_name ?? '',
    group_name: props.settings.group_name ?? '',
    resource_pack_id: props.settings.resource_pack_id || '',
});

const modeLabel = (mode) => mode.charAt(0).toUpperCase() + mode.slice(1);

const modeHint = computed(() => ({
    clan: 'Roles mirror the in-game clan; ranks sync from the RuneLite plugin.',
    group: 'Every member has admin access.',
    casual: 'Only the owner has admin access.',
}[form.instance_mode] ?? ''));

const submit = () => {
    form.put(route('admin.settings.update'), { preserveScroll: true });
};
</script>

<template>
    <AppLayout title="Instance settings">
        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <h1 class="header-chatbox-sword text-2xl font-bold">Instance settings</h1>

                <Card class="mt-6">
                    <form class="space-y-6" @submit.prevent="submit">
                        <div>
                            <InputLabel for="instance_mode" value="Mode" />
                            <select id="instance_mode"
                                    v-model="form.instance_mode"
                                    class="select select-bordered mt-1 block w-full">
                                <option v-for="mode in modes" :key="mode" :value="mode">
                                    {{ modeLabel(mode) }}
                                </option>
                            </select>
                            <p class="mt-1 text-xs text-base-content/60">{{ modeHint }}</p>
                            <InputError v-if="form.errors.instance_mode" :messages="form.errors.instance_mode" />
                        </div>

                        <div v-if="form.instance_mode === 'clan'">
                            <InputLabel for="clan_name" value="Clan name" />
                            <TextInput id="clan_name"
                                       v-model="form.clan_name"
                                       type="text"
                                       class="mt-1 block w-full"
                                       :error="form.errors.clan_name !== undefined" />
                            <InputError v-if="form.errors.clan_name" :messages="form.errors.clan_name" />
                        </div>

                        <div v-if="form.instance_mode === 'group'">
                            <InputLabel for="group_name" value="Group name" />
                            <TextInput id="group_name"
                                       v-model="form.group_name"
                                       type="text"
                                       class="mt-1 block w-full"
                                       :error="form.errors.group_name !== undefined" />
                            <InputError v-if="form.errors.group_name" :messages="form.errors.group_name" />
                        </div>

                        <div>
                            <InputLabel for="resource_pack_id" value="Default resource pack" />
                            <select id="resource_pack_id"
                                    v-model="form.resource_pack_id"
                                    class="select select-bordered mt-1 block w-full">
                                <option value="">None</option>
                                <option v-for="pack in packs" :key="pack.id" :value="pack.id">
                                    {{ pack.alias }}
                                </option>
                            </select>
                            <InputError v-if="form.errors.resource_pack_id" :messages="form.errors.resource_pack_id" />
                        </div>

                        <div class="flex justify-end">
                            <PrimaryButton :class="{ 'opacity-25': form.processing }"
                                           :disabled="form.processing">
                                Save
                            </PrimaryButton>
                        </div>
                    </form>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
