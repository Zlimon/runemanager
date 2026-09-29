<script setup>
import { useForm } from "@inertiajs/vue3";
import SettingsLayout from "@/Layouts/SettingsLayout.vue";
import Card from "@/Components/Cards/Card.vue";
import InputError from "@/Components/Form/InputError.vue";
import InputLabel from "@/Components/Form/InputLabel.vue";
import PrimaryButton from "@/Components/Buttons/PrimaryButton.vue";
import TextInput from "@/Components/Form/TextInput.vue";

const props = defineProps({
    settings: { type: Object, required: true },
});

const form = useForm({
    webhook_url: props.settings.webhook_url ?? '',
    wom_enabled: props.settings.wom_enabled ?? false,
    wom_active_within_days: props.settings.wom_active_within_days ?? 30,
    wom_progress_public: props.settings.wom_progress_public ?? false,
});

const submit = () => form.put(route('admin.integrations.update'), { preserveScroll: true });
</script>

<template>
    <SettingsLayout title="Integrations">
        <Card>
            <form class="space-y-6" @submit.prevent="submit">
                <div>
                    <InputLabel for="webhook_url" value="Webhook URL" />
                    <TextInput id="webhook_url" v-model="form.webhook_url" type="url" class="mt-1 w-full"
                               placeholder="https://discord.com/api/webhooks/..."
                               :error="form.errors.webhook_url !== undefined" />
                    <p class="mt-1 text-xs text-base-content/60">
                        Notable events (new announcements and calendar events) are posted to this
                        webhook — e.g. a Discord channel. Leave blank to disable. In-game events like
                        loot and level-ups are best handled by a dedicated plugin such as Dink.
                    </p>
                    <InputError v-if="form.errors.webhook_url" :messages="form.errors.webhook_url" />
                </div>

                <div class="space-y-4 border-t border-base-300 pt-6">
                    <div>
                        <InputLabel value="Wise Old Man" />
                        <label class="mt-2 flex items-center gap-2 text-sm">
                            <input type="checkbox" v-model="form.wom_enabled" class="checkbox checkbox-sm">
                            Sync accounts with Wise Old Man
                        </label>
                        <p class="mt-1 text-xs text-base-content/60">
                            Pulls EHP, EHB and a year of progress history for accounts already tracked on
                            wiseoldman.net, once a day and on plugin login. Accounts are never added to Wise Old
                            Man unless their owner opts in from their settings.
                        </p>
                        <InputError v-if="form.errors.wom_enabled" :messages="form.errors.wom_enabled" />
                    </div>

                    <div>
                        <InputLabel for="wom_active_within_days" value="Active window (days)" />
                        <TextInput id="wom_active_within_days" v-model.number="form.wom_active_within_days" type="number"
                                   min="1" max="365" class="mt-1 w-32"
                                   :error="form.errors.wom_active_within_days !== undefined" />
                        <p class="mt-1 text-xs text-base-content/60">
                            An account counts as active on the Progress page when it gained experience within this many days.
                        </p>
                        <InputError v-if="form.errors.wom_active_within_days" :messages="form.errors.wom_active_within_days" />
                    </div>

                    <div>
                        <InputLabel value="Progress page" />
                        <label class="mt-2 flex items-center gap-2 text-sm">
                            <input type="checkbox" v-model="form.wom_progress_public" class="checkbox checkbox-sm">
                            Visible to logged-out visitors
                        </label>
                        <p class="mt-1 text-xs text-base-content/60">
                            When off, only logged-in members can open the Progress page.
                            Usernames are shown unless 'Hide account names' is on.
                        </p>
                        <InputError v-if="form.errors.wom_progress_public" :messages="form.errors.wom_progress_public" />
                    </div>
                </div>

                <div class="flex justify-end">
                    <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">Save</PrimaryButton>
                </div>
            </form>
        </Card>
    </SettingsLayout>
</template>
