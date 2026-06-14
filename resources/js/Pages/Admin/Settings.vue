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
import ResourcePackPicker from "@/Components/ResourcePackPicker.vue";

const props = defineProps({
    settings: { type: Object, required: true },
    branding: { type: Object, default: () => ({}) },
    configured: { type: Boolean, default: false },
    accountCount: { type: Number, default: 0 },
    modes: { type: Array, required: true },
    packs: { type: Array, default: () => [] },
});

const form = useForm({
    instance_mode: props.settings.instance_mode,
    clan_name: props.settings.clan_name ?? '',
    group_name: props.settings.group_name ?? '',
    instance_description: props.settings.instance_description ?? '',
    resource_pack_id: props.settings.resource_pack_id || '',
    default_dark_mode: props.settings.default_dark_mode ?? '',
    confirm: '',
});

const configForm = useForm({
    hiscore_refresh_minutes: props.settings.hiscore_refresh_minutes,
    feed_level_up_thresholds: props.settings.feed_level_up_thresholds ?? '',
    feed_loot_min_value: props.settings.feed_loot_min_value,
});

const brandingForm = useForm({
    logo: null,
    banner: null,
    remove_logo: false,
    remove_banner: false,
});

const refreshOptions = [
    { value: 30, label: 'Every 30 minutes' },
    { value: 60, label: 'Hourly' },
    { value: 180, label: 'Every 3 hours' },
    { value: 360, label: 'Every 6 hours' },
    { value: 720, label: 'Every 12 hours' },
    { value: 1440, label: 'Daily' },
];

const showConfirm = ref(false);

const submitConfig = () => configForm.put(route('admin.config.update'), { preserveScroll: true });

const submitBranding = () => {
    brandingForm.post(route('admin.branding.update'), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => brandingForm.reset('logo', 'banner', 'remove_logo', 'remove_banner'),
    });
};

const modeLabel = (mode) => mode.charAt(0).toUpperCase() + mode.slice(1);

const modeHint = computed(() => ({
    clan: 'Roles mirror the in-game clan; ranks sync from the RuneLite plugin. Only clan members can register.',
    group: 'Every member has admin access. Members are added by the admin.',
    casual: 'Only the owner has admin access. Anyone can register.',
}[form.instance_mode] ?? ''));

// Switching into a roster mode (clan/group) wipes all account data, so it needs
// an explicit typed confirmation — but only when there's data to lose. Switching
// to casual (or staying put) keeps data.
const destructiveChange = computed(() =>
    form.instance_mode !== props.settings.instance_mode
    && form.instance_mode !== 'casual'
    && props.accountCount > 0);

const submit = () => {
    if (destructiveChange.value) {
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
                            <p v-if="destructiveChange" class="mt-1 text-xs font-medium text-error">
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
                            <InputLabel for="instance_description" value="Description" />
                            <textarea id="instance_description"
                                      v-model="form.instance_description"
                                      class="textarea textarea-bordered mt-1 block w-full"
                                      rows="3"
                                      placeholder="A short description of your clan/group, shown on the homepage." />
                            <InputError v-if="form.errors.instance_description" :messages="form.errors.instance_description" />
                        </div>

                        <div>
                            <InputLabel value="Default resource pack" />
                            <p class="mt-1 text-xs text-base-content/60">
                                The instance-wide theme, applied to anyone without their own pick.
                            </p>
                            <ResourcePackPicker v-model="form.resource_pack_id" :packs="packs" class="mt-2" />
                            <InputError v-if="form.errors.resource_pack_id" :messages="form.errors.resource_pack_id" />
                        </div>

                        <div>
                            <InputLabel for="default_dark_mode" value="Default appearance" />
                            <select id="default_dark_mode"
                                    v-model="form.default_dark_mode"
                                    class="select select-bordered mt-1 block w-full">
                                <option value="">Follow resource pack</option>
                                <option value="light">Light</option>
                                <option value="dark">Dark</option>
                            </select>
                            <p class="mt-1 text-xs text-base-content/60">
                                Overrides the pack's own light/dark flag for anyone who hasn't
                                picked their own — handy when a pack ships an unreliable flag.
                                Members can still toggle for themselves.
                            </p>
                            <InputError v-if="form.errors.default_dark_mode" :messages="form.errors.default_dark_mode" />
                        </div>

                        <div class="flex justify-end">
                            <PrimaryButton :class="{ 'opacity-25': form.processing }"
                                           :disabled="form.processing">
                                {{ configured ? 'Save' : 'Complete setup' }}
                            </PrimaryButton>
                        </div>
                    </form>
                </Card>

                <!-- Feed & sync config (SPEC §12.4) -->
                <Card v-if="configured" class="mt-6">
                    <h2 class="text-lg font-bold">Feed &amp; sync</h2>
                    <form class="mt-4 space-y-6" @submit.prevent="submitConfig">
                        <div>
                            <InputLabel for="hiscore_refresh_minutes" value="Hiscores refresh" />
                            <select id="hiscore_refresh_minutes"
                                    v-model.number="configForm.hiscore_refresh_minutes"
                                    class="select select-bordered mt-1 block w-full">
                                <option v-for="opt in refreshOptions" :key="opt.value" :value="opt.value">
                                    {{ opt.label }}
                                </option>
                            </select>
                            <p class="mt-1 text-xs text-base-content/60">How often stats are pulled from the OSRS hiscores.</p>
                            <InputError v-if="configForm.errors.hiscore_refresh_minutes" :messages="configForm.errors.hiscore_refresh_minutes" />
                        </div>

                        <div>
                            <InputLabel for="feed_level_up_thresholds" value="Level-up milestones" />
                            <TextInput id="feed_level_up_thresholds"
                                       v-model="configForm.feed_level_up_thresholds"
                                       type="text"
                                       class="mt-1 block w-full"
                                       placeholder="50, 60, 70, 80, 90, 99"
                                       :error="configForm.errors.feed_level_up_thresholds !== undefined" />
                            <p class="mt-1 text-xs text-base-content/60">
                                Comma-separated levels (2–99). The feed posts a level-up only when a skill crosses one.
                            </p>
                            <InputError v-if="configForm.errors.feed_level_up_thresholds" :messages="configForm.errors.feed_level_up_thresholds" />
                        </div>

                        <div>
                            <InputLabel for="feed_loot_min_value" value="Minimum loot value (gp)" />
                            <TextInput id="feed_loot_min_value"
                                       v-model.number="configForm.feed_loot_min_value"
                                       type="number"
                                       min="0"
                                       class="mt-1 block w-full"
                                       :error="configForm.errors.feed_loot_min_value !== undefined" />
                            <p class="mt-1 text-xs text-base-content/60">Drops below this GE value don't reach the live feed.</p>
                            <InputError v-if="configForm.errors.feed_loot_min_value" :messages="configForm.errors.feed_loot_min_value" />
                        </div>

                        <div class="flex justify-end">
                            <PrimaryButton :class="{ 'opacity-25': configForm.processing }" :disabled="configForm.processing">
                                Save
                            </PrimaryButton>
                        </div>
                    </form>
                </Card>

                <!-- Branding (SPEC §12.4) -->
                <Card v-if="configured" class="mt-6">
                    <h2 class="text-lg font-bold">Branding</h2>
                    <form class="mt-4 space-y-6" @submit.prevent="submitBranding">
                        <div>
                            <InputLabel value="Logo" />
                            <img v-if="branding.logo_url" :src="branding.logo_url" alt="Current logo"
                                 class="mt-1 h-12 object-contain">
                            <input type="file" accept="image/*"
                                   class="file-input file-input-bordered mt-1 block w-full"
                                   @input="brandingForm.logo = $event.target.files[0]">
                            <label v-if="branding.logo_url" class="mt-1 flex items-center gap-2 text-xs text-base-content/70">
                                <input type="checkbox" v-model="brandingForm.remove_logo" class="checkbox checkbox-xs"> Remove current logo
                            </label>
                            <InputError v-if="brandingForm.errors.logo" :messages="brandingForm.errors.logo" />
                        </div>

                        <div>
                            <InputLabel value="Banner" />
                            <img v-if="branding.banner_url" :src="branding.banner_url" alt="Current banner"
                                 class="mt-1 h-24 w-full rounded object-cover">
                            <input type="file" accept="image/*"
                                   class="file-input file-input-bordered mt-1 block w-full"
                                   @input="brandingForm.banner = $event.target.files[0]">
                            <label v-if="branding.banner_url" class="mt-1 flex items-center gap-2 text-xs text-base-content/70">
                                <input type="checkbox" v-model="brandingForm.remove_banner" class="checkbox checkbox-xs"> Remove current banner
                            </label>
                            <InputError v-if="brandingForm.errors.banner" :messages="brandingForm.errors.banner" />
                        </div>

                        <div class="flex justify-end">
                            <PrimaryButton :class="{ 'opacity-25': brandingForm.processing }" :disabled="brandingForm.processing">
                                Save branding
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
                    This permanently deletes <strong>all {{ accountCount }} account(s) and their data</strong>
                    (hiscores, bank, loot, inventory, clan ranks). User logins are kept but reset to
                    plain members. This cannot be undone.
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
