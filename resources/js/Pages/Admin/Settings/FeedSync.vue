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
    hiscore_refresh_minutes: props.settings.hiscore_refresh_minutes,
    feed_level_up_thresholds: props.settings.feed_level_up_thresholds ?? '',
    feed_loot_min_value: props.settings.feed_loot_min_value,
});

const refreshOptions = [
    { value: 30, label: 'Every 30 minutes' },
    { value: 60, label: 'Hourly' },
    { value: 180, label: 'Every 3 hours' },
    { value: 360, label: 'Every 6 hours' },
    { value: 720, label: 'Every 12 hours' },
    { value: 1440, label: 'Daily' },
];

const submit = () => form.put(route('admin.config.update'), { preserveScroll: true });
</script>

<template>
    <SettingsLayout title="Feed & sync">
        <Card>
            <form class="space-y-6" @submit.prevent="submit">
                <div>
                    <InputLabel for="hiscore_refresh_minutes" value="Hiscores refresh" />
                    <select id="hiscore_refresh_minutes" v-model.number="form.hiscore_refresh_minutes"
                            class="select select-bordered mt-1 block w-full">
                        <option v-for="opt in refreshOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                    </select>
                    <p class="mt-1 text-xs text-base-content/60">How often stats are pulled from the OSRS hiscores.</p>
                    <InputError v-if="form.errors.hiscore_refresh_minutes" :messages="form.errors.hiscore_refresh_minutes" />
                </div>

                <div>
                    <InputLabel for="feed_level_up_thresholds" value="Level-up milestones" />
                    <TextInput id="feed_level_up_thresholds" v-model="form.feed_level_up_thresholds" type="text"
                               class="mt-1 block w-full" placeholder="50, 60, 70, 80, 90, 99"
                               :error="form.errors.feed_level_up_thresholds !== undefined" />
                    <p class="mt-1 text-xs text-base-content/60">
                        Comma-separated levels (2–99). The feed posts a level-up only when a skill crosses one.
                    </p>
                    <InputError v-if="form.errors.feed_level_up_thresholds" :messages="form.errors.feed_level_up_thresholds" />
                </div>

                <div>
                    <InputLabel for="feed_loot_min_value" value="Minimum loot value (gp)" />
                    <TextInput id="feed_loot_min_value" v-model.number="form.feed_loot_min_value" type="number" min="0"
                               class="mt-1 block w-full" :error="form.errors.feed_loot_min_value !== undefined" />
                    <p class="mt-1 text-xs text-base-content/60">Drops below this GE value don't reach the live feed.</p>
                    <InputError v-if="form.errors.feed_loot_min_value" :messages="form.errors.feed_loot_min_value" />
                </div>

                <div class="flex justify-end">
                    <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">Save</PrimaryButton>
                </div>
            </form>
        </Card>
    </SettingsLayout>
</template>
