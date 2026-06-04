<script setup>
import { computed, ref } from "vue";
import { Link, router, useForm, usePage } from "@inertiajs/vue3";
import dayjs from "dayjs";
import AppLayout from "@/Layouts/AppLayout.vue";
import DialogModal from "@/Components/DialogModal.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import DangerButton from "@/Components/DangerButton.vue";
import TextInput from "@/Components/TextInput.vue";

const props = defineProps({
    upcoming: { type: Array, default: () => [] },
    past: { type: Array, default: () => [] },
    eventTypes: { type: Array, default: () => [] },
});

const page = usePage();
const currentUserId = computed(() => page.props.auth?.user?.id ?? null);

const showCreate = ref(false);
const form = useForm({
    title: '',
    description: '',
    event_type: 'pvm_mass',
    starts_at: '',
    ends_at: '',
});

const openCreate = () => {
    form.reset();
    form.clearErrors();
    showCreate.value = true;
};

const submit = () => {
    form.post(route('calendar.store'), {
        preserveScroll: true,
        onSuccess: () => { showCreate.value = false; },
    });
};

const destroy = (event) => {
    if (!confirm(`Delete "${event.title}"?`)) {
        return;
    }
    router.delete(route('calendar.destroy', event.id), {
        preserveScroll: true,
    });
};

const dt = (iso) => dayjs(iso).format('ddd MMM D, YYYY h:mm A');
</script>

<template>
    <AppLayout title="Calendar">
        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div class="flex items-baseline justify-between">
                    <h1 class="header-chatbox-sword text-2xl font-bold dark:text-white">Calendar</h1>
                    <PrimaryButton v-if="currentUserId" @click="openCreate">
                        New event
                    </PrimaryButton>
                </div>

                <h2 class="mt-6 mb-2 text-lg font-semibold dark:text-white">Upcoming</h2>
                <div v-if="upcoming.length === 0"
                     class="bg-base-100 border border-base-300 rounded p-6 text-center text-gray-500 dark:text-gray-400">
                    No upcoming events scheduled.
                </div>
                <ul v-else class="space-y-2">
                    <li v-for="event in upcoming" :key="event.id"
                        class="rounded p-4 pack-bg-card resource-pack-border">
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <span class="badge badge-primary badge-lg">{{ event.event_type_label }}</span>
                                <h3 class="text-base font-semibold">{{ event.title }}</h3>
                            </div>
                            <DangerButton v-if="currentUserId === event.created_by.id"
                                          type="button" class="btn-xs"
                                          @click="destroy(event)">
                                Delete
                            </DangerButton>
                        </div>
                        <p v-if="event.description"
                           class="mt-1 text-sm text-base-content/70">
                            {{ event.description }}
                        </p>
                        <p class="mt-1 text-xs text-base-content/60">
                            {{ dt(event.starts_at) }}
                            <template v-if="event.ends_at">
                                → {{ dt(event.ends_at) }}
                            </template>
                            · by {{ event.created_by.name }}
                        </p>
                    </li>
                </ul>

                <h2 v-if="past.length > 0" class="mt-8 mb-2 text-lg font-semibold dark:text-white">Past</h2>
                <ul v-if="past.length > 0" class="space-y-2">
                    <li v-for="event in past" :key="event.id"
                        class="rounded p-4 opacity-70 pack-bg resource-pack-border">
                        <div class="flex items-center gap-2">
                            <span class="badge badge-lg">{{ event.event_type_label }}</span>
                            <h3 class="text-base font-semibold">{{ event.title }}</h3>
                        </div>
                        <p class="mt-1 text-xs text-base-content/60">
                            {{ dt(event.starts_at) }} · by {{ event.created_by.name }}
                        </p>
                    </li>
                </ul>
            </div>
        </div>

        <DialogModal :show="showCreate" @close="showCreate = false">
            <template #title>Create event</template>

            <template #content>
                <div class="space-y-4">
                    <div>
                        <InputLabel for="event-title" value="Title" />
                        <TextInput id="event-title"
                                   v-model="form.title"
                                   type="text"
                                   class="mt-1 block w-full"
                                   :error="form.errors.title !== undefined" />
                        <InputError v-if="form.errors.title" :messages="form.errors.title" />
                    </div>

                    <div>
                        <InputLabel for="event-type" value="Type" />
                        <select id="event-type"
                                v-model="form.event_type"
                                class="select select-bordered mt-1 block w-full">
                            <option v-for="type in eventTypes" :key="type.value" :value="type.value">
                                {{ type.label }}
                            </option>
                        </select>
                        <InputError v-if="form.errors.event_type" :messages="form.errors.event_type" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="event-starts-at" value="Starts" />
                            <TextInput id="event-starts-at"
                                       v-model="form.starts_at"
                                       type="datetime-local"
                                       class="mt-1 block w-full"
                                       :error="form.errors.starts_at !== undefined" />
                            <InputError v-if="form.errors.starts_at" :messages="form.errors.starts_at" />
                        </div>
                        <div>
                            <InputLabel for="event-ends-at" value="Ends (optional)" />
                            <TextInput id="event-ends-at"
                                       v-model="form.ends_at"
                                       type="datetime-local"
                                       class="mt-1 block w-full"
                                       :error="form.errors.ends_at !== undefined" />
                            <InputError v-if="form.errors.ends_at" :messages="form.errors.ends_at" />
                        </div>
                    </div>

                    <div>
                        <InputLabel for="event-description" value="Description" />
                        <textarea id="event-description"
                                  v-model="form.description"
                                  class="mt-1 block w-full textarea textarea-bordered"
                                  rows="3" />
                        <InputError v-if="form.errors.description" :messages="form.errors.description" />
                    </div>
                </div>
            </template>

            <template #footer>
                <SecondaryButton @click="showCreate = false">Cancel</SecondaryButton>
                <PrimaryButton class="ms-3"
                               :class="{ 'opacity-25': form.processing }"
                               :disabled="form.processing"
                               @click="submit">
                    Create
                </PrimaryButton>
            </template>
        </DialogModal>
    </AppLayout>
</template>
