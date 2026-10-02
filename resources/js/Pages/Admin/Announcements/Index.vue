<script setup>
import { ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { formatArticleDateTime } from '@/composables/useAdminFormatters.js';
import SettingsLayout from '@/Layouts/SettingsLayout.vue';
import DataTable from '@/Components/Tables/DataTable.vue';
import Badge from '@/Components/Display/Badge.vue';
import DialogModal from '@/Components/Modals/DialogModal.vue';
import InputError from '@/Components/Form/InputError.vue';
import InputLabel from '@/Components/Form/InputLabel.vue';
import PrimaryButton from '@/Components/Buttons/PrimaryButton.vue';
import SecondaryButton from '@/Components/Buttons/SecondaryButton.vue';
import DangerButton from '@/Components/Buttons/DangerButton.vue';
import TextInput from '@/Components/Form/TextInput.vue';

/*
 * Admin → Announcements: every announcement, expired ones included (the
 * public page only shows active ones), with how many accounts have
 * acknowledged each in-game. Creating and deleting reuse the existing
 * `announcements.*` routes, the same fields as Pages/Announcements/Index.
 */
defineProps({
    // [{ id, title, body, expires_at, is_expired, created_at, created_by, acknowledged_count }]
    announcements: { type: Array, default: () => [] },
});

const columns = [
    { key: 'title', label: 'Title' },
    { key: 'status', label: 'Status', sortable: false },
    { key: 'acknowledged_count', label: 'Acknowledged', format: 'number' },
    { key: 'created_by', label: 'Author', sortable: false },
    { key: 'created_at', label: 'Created' },
    { key: 'expires_at', label: 'Expires' },
    { key: 'actions', label: '', align: 'right', sortable: false },
];

const dt = formatArticleDateTime;

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
    router.delete(route('announcements.destroy', announcement.id), { preserveScroll: true });
};
</script>

<template>
    <SettingsLayout title="Announcements">
        <template #actions>
            <PrimaryButton @click="openCreate">New announcement</PrimaryButton>
        </template>

        <DataTable :columns="columns" :rows="announcements" row-key="id">
            <template #cell-title="{ row }">
                <span class="font-medium">{{ row.title }}</span>
            </template>
            <template #cell-status="{ row }">
                <Badge size="sm" :variant="row.is_expired ? 'neutral' : 'success'">
                    {{ row.is_expired ? 'Expired' : 'Active' }}
                </Badge>
            </template>
            <template #cell-created_by="{ row }">{{ row.created_by.name ?? '—' }}</template>
            <template #cell-created_at="{ row }">{{ dt(row.created_at) }}</template>
            <template #cell-expires_at="{ row }">{{ row.expires_at ? dt(row.expires_at) : 'Never' }}</template>
            <template #cell-actions="{ row }">
                <div class="flex justify-end">
                    <DangerButton type="button" class="btn-xs" @click="destroy(row)">Delete</DangerButton>
                </div>
            </template>
            <template #empty>No announcements yet.</template>
        </DataTable>

        <DialogModal :show="showCreate" @close="showCreate = false">
            <template #title>New announcement</template>

            <template #content>
                <div class="space-y-4">
                    <div>
                        <InputLabel for="announcement-title" value="Title" />
                        <TextInput id="announcement-title"
                                   v-model="form.title"
                                   type="text"
                                   class="mt-1 w-full"
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
                                   class="mt-1 w-full"
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
    </SettingsLayout>
</template>
