<script setup>
import { computed, ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import Card from "@/Components/Card.vue";
import DialogModal from "@/Components/DialogModal.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import DangerButton from "@/Components/DangerButton.vue";
import TextInput from "@/Components/TextInput.vue";

const props = defineProps({
    settings: { type: Object, required: true },
    configured: { type: Boolean, default: false },
    modes: { type: Array, required: true },
    packs: { type: Array, default: () => [] },
});

const form = useForm({
    instance_mode: props.settings.instance_mode,
    clan_name: props.settings.clan_name ?? '',
    group_name: props.settings.group_name ?? '',
    resource_pack_id: props.settings.resource_pack_id || '',
    confirm: '',
});

const showConfirm = ref(false);

const modeLabel = (mode) => mode.charAt(0).toUpperCase() + mode.slice(1);

const modeHint = computed(() => ({
    clan: 'Roles mirror the in-game clan; ranks sync from the RuneLite plugin. Only clan members can register.',
    group: 'Every member has admin access. Members are added by the admin.',
    casual: 'Only the owner has admin access. Anyone can register.',
}[form.instance_mode] ?? ''));

// Changing the mode after first-time setup wipes all account data, so it needs
// an explicit typed confirmation.
const modeChanged = computed(() => props.configured && form.instance_mode !== props.settings.instance_mode);

const submit = () => {
    if (modeChanged.value) {
        form.confirm = '';
        form.clearErrors();
        showConfirm.value = true;
        return;
    }
    form.put(route('admin.settings.update'), { preserveScroll: true });
};

const confirmReset = () => {
    form.put(route('admin.settings.update'), {
        preserveScroll: true,
        onSuccess: () => { showConfirm.value = false; },
    });
};
</script>

<template>
    <AppLayout title="Instance settings">
        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <h1 class="header-chatbox-sword text-2xl font-bold">
                    {{ configured ? 'Instance settings' : 'First-time setup' }}
                </h1>

                <div v-if="!configured"
                     class="mt-4 rounded p-4 text-sm pack-bg-card resource-pack-border">
                    Welcome! Choose how this site runs. <strong>Clan</strong> mirrors an in-game clan
                    (the owner's plugin seeds members), <strong>Group</strong> lets you add members
                    by hand, and <strong>Casual</strong> is open registration. You can change this
                    later, but switching mode wipes all account data.
                </div>

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
                            <p v-if="modeChanged" class="mt-1 text-xs font-medium text-error">
                                Switching mode permanently deletes all accounts and their data.
                            </p>
                            <InputError v-if="form.errors.instance_mode" :messages="form.errors.instance_mode" />
                        </div>

                        <div v-if="form.instance_mode === 'clan'">
                            <InputLabel for="clan_name" value="Clan name" />
                            <TextInput id="clan_name"
                                       v-model="form.clan_name"
                                       type="text"
                                       class="mt-1 block w-full"
                                       :error="form.errors.clan_name !== undefined" />
                            <p class="mt-1 text-xs text-base-content/60">
                                Auto-filled from the owner's clan when the plugin syncs the roster.
                            </p>
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
                                {{ configured ? 'Save' : 'Complete setup' }}
                            </PrimaryButton>
                        </div>
                    </form>
                </Card>
            </div>
        </div>

        <DialogModal :show="showConfirm" @close="showConfirm = false">
            <template #title>Reset and switch to {{ modeLabel(form.instance_mode) }} mode?</template>

            <template #content>
                <p class="text-sm text-base-content/80">
                    This permanently deletes <strong>all accounts and their data</strong> (hiscores,
                    bank, loot, inventory, clan ranks). User logins are kept but reset to plain
                    members. This cannot be undone.
                </p>
                <div class="mt-4">
                    <InputLabel for="confirm" :value="`Type &quot;${form.instance_mode}&quot; to confirm`" />
                    <TextInput id="confirm"
                               v-model="form.confirm"
                               type="text"
                               class="mt-1 block w-full"
                               autocomplete="off"
                               :error="form.errors.confirm !== undefined" />
                    <InputError v-if="form.errors.confirm" :messages="form.errors.confirm" />
                </div>
            </template>

            <template #footer>
                <SecondaryButton @click="showConfirm = false">Cancel</SecondaryButton>
                <DangerButton class="ms-3"
                              :class="{ 'opacity-25': form.processing }"
                              :disabled="form.processing || form.confirm !== form.instance_mode"
                              @click="confirmReset">
                    Reset &amp; switch
                </DangerButton>
            </template>
        </DialogModal>
    </AppLayout>
</template>
