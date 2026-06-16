<script setup>
import { useForm } from "@inertiajs/vue3";
import SettingsLayout from "@/Layouts/SettingsLayout.vue";
import Card from "@/Components/Card.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";

const props = defineProps({
    settings: { type: Object, required: true },
});

const form = useForm({
    webhook_url: props.settings.webhook_url ?? '',
});

const submit = () => form.put(route('admin.integrations.update'), { preserveScroll: true });
</script>

<template>
    <SettingsLayout title="Integrations">
        <Card>
            <form class="space-y-6" @submit.prevent="submit">
                <div>
                    <InputLabel for="webhook_url" value="Webhook URL" />
                    <TextInput id="webhook_url" v-model="form.webhook_url" type="url" class="mt-1 block w-full"
                               placeholder="https://discord.com/api/webhooks/..."
                               :error="form.errors.webhook_url !== undefined" />
                    <p class="mt-1 text-xs text-base-content/60">
                        Notable events (new announcements and calendar events) are posted to this
                        webhook — e.g. a Discord channel. Leave blank to disable. In-game events like
                        loot and level-ups are best handled by a dedicated plugin such as Dink.
                    </p>
                    <InputError v-if="form.errors.webhook_url" :messages="form.errors.webhook_url" />
                </div>

                <div class="flex justify-end">
                    <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">Save</PrimaryButton>
                </div>
            </form>
        </Card>
    </SettingsLayout>
</template>
