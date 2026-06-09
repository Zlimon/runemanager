<script setup>
import { computed, ref } from "vue";
import { router, useForm, usePage } from "@inertiajs/vue3";
import dayjs from "dayjs";
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
    announcements: { type: Array, default: () => [] },
});

const page = usePage();
const currentUserId = computed(() => page.props.auth?.user?.id ?? null);
const isAdmin = computed(() => page.props.is_admin === true);

const showCreate = ref(false);
const form = useForm({
    title: '',
    body: '',
    expires_at: '',
});

const openCreate = () => {
    form.reset();
    form.clearErrors();
    showCreate.value = true;
};

const submit = () => {
    form.post(route('announcements.store'), {
        preserveScroll: true,
        onSuccess: () => { showCreate.value = false; },
    });
};

const destroy = (announcement) => {
    if (!confirm(`Delete "${announcement.title}"?`)) {
        return;
    }
    router.delete(route('announcements.destroy', announcement.id), {
        preserveScroll: true,
    });
};

const dt = (iso) => dayjs(iso).format('MMM D, YYYY h:mm A');
</script>

<template>
    <AppLayout title="Announcements">
        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div class="flex items-baseline justify-between">
                    <h1 class="header-chatbox-sword text-2xl font-bold">Announcements</h1>
                    <PrimaryButton v-if="isAdmin" @click="openCreate">
                        New announcement
                    </PrimaryButton>
                </div>

                <div v-if="announcements.length === 0"
                     class="mt-6 rounded p-6 text-center text-base-content/60 pack-bg-card resource-pack-border">
                    No announcements right now.
                </div>

                <ul v-else class="mt-6 space-y-4">
                    <li v-for="announcement in announcements" :key="announcement.id">
                        <Card padding="p-4">
                            <div class="flex items-start justify-between gap-2">
                                <h3 class="text-base font-semibold">{{ announcement.title }}</h3>
                                <DangerButton v-if="isAdmin && currentUserId === announcement.created_by.id"
                                              type="button" class="btn-xs"
                                              @click="destroy(announcement)">
                                    Delete
                                </DangerButton>
                            </div>
                            <p class="mt-2 whitespace-pre-line text-sm text-base-content/80">
                                {{ announcement.body }}
                            </p>
                            <p class="mt-2 text-xs text-base-content/60">
                                {{ dt(announcement.created_at) }} · by {{ announcement.created_by.name }}
                                <template v-if="announcement.expires_at">
                                    · expires {{ dt(announcement.expires_at) }}
                                </template>
                            </p>
                        </Card>
                    </li>
                </ul>
            </div>
        </div>

        <DialogModal :show="showCreate" @close="showCreate = false">
            <template #title>New announcement</template>

            <template #content>
                <div class="space-y-4">
                    <div>
                        <InputLabel for="announcement-title" value="Title" />
                        <TextInput id="announcement-title"
                                   v-model="form.title"
                                   type="text"
                                   class="mt-1 block w-full"
                                   :error="form.errors.title !== undefined" />
                        <InputError v-if="form.errors.title" :messages="form.errors.title" />
                    </div>

                    <div>
                        <InputLabel for="announcement-body" value="Message" />
                        <textarea id="announcement-body"
                                  v-model="form.body"
                                  class="textarea textarea-bordered mt-1 block w-full"
                                  rows="5" />
                        <InputError v-if="form.errors.body" :messages="form.errors.body" />
                    </div>

                    <div>
                        <InputLabel for="announcement-expires-at" value="Expires (optional)" />
                        <TextInput id="announcement-expires-at"
                                   v-model="form.expires_at"
                                   type="datetime-local"
                                   class="mt-1 block w-full"
                                   :error="form.errors.expires_at !== undefined" />
                        <InputError v-if="form.errors.expires_at" :messages="form.errors.expires_at" />
                    </div>
                </div>
            </template>

            <template #footer>
                <SecondaryButton @click="showCreate = false">Cancel</SecondaryButton>
                <PrimaryButton class="ms-3"
                               :class="{ 'opacity-25': form.processing }"
                               :disabled="form.processing"
                               @click="submit">
                    Publish
                </PrimaryButton>
            </template>
        </DialogModal>
    </AppLayout>
</template>
