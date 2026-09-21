<script setup>
import { ref } from "vue";
import { router, useForm } from "@inertiajs/vue3";
import dayjs from "dayjs";
import { useEventColor } from "@/composables/useEventColor.js";
import SettingsLayout from "@/Layouts/SettingsLayout.vue";
import Card from "@/Components/Card.vue";
import DataTable from "@/Components/DataTable.vue";
import DialogModal from "@/Components/DialogModal.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import ItemPicker from "@/Components/ItemPicker.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import DangerButton from "@/Components/DangerButton.vue";
import TextInput from "@/Components/TextInput.vue";
import SelectInput from "@/Components/SelectInput.vue";

const props = defineProps({
    upcoming: { type: Array, default: () => [] },
    past: { type: Array, default: () => [] },
    eventTypes: { type: Array, default: () => [] },
    highlightColors: { type: Array, default: () => [] },
});

// Shared V-Calendar palette + swatch resolver (one source of truth, see
// useEventColor.js) — these are literal game/UI-semantic swatches, not raw
// Tailwind palette colours.
const { COLORS, eventHex } = useEventColor();

const dt = (iso) => dayjs(iso).format('MMM D, YYYY h:mm A');

const columns = [
    { key: 'title', label: 'Title' },
    { key: 'event_type_label', label: 'Type' },
    { key: 'starts_at', label: 'Starts', sort: (row) => row.starts_at },
    { key: 'created_by', label: 'Created by', sortable: false },
    { key: 'actions', label: '', sortable: false, align: 'right' },
];

// ------------------------------------------------------------------
// Create / edit
// ------------------------------------------------------------------

const editing = ref(null);
const showForm = ref(false);

const form = useForm({
    title: '',
    description: '',
    event_type: '',
    color: '',
    item_id: null,
    starts_at: '',
    ends_at: '',
});

const openCreate = () => {
    editing.value = null;
    form.reset();
    form.clearErrors();
    form.event_type = props.eventTypes[0]?.value ?? '';
    showForm.value = true;
};

const openEdit = (event) => {
    editing.value = event;
    form.clearErrors();
    form.title = event.title;
    form.description = event.description ?? '';
    form.event_type = event.event_type;
    form.color = event.color ?? '';
    form.item_id = event.item?.id ?? null;
    form.starts_at = dayjs(event.starts_at).format('YYYY-MM-DDTHH:mm');
    form.ends_at = event.ends_at ? dayjs(event.ends_at).format('YYYY-MM-DDTHH:mm') : '';
    showForm.value = true;
};

const submit = () => {
    const options = {
        preserveScroll: true,
        onSuccess: () => { showForm.value = false; },
    };

    if (editing.value) {
        form.put(route('calendar.update', editing.value.id), options);
    } else {
        form.post(route('calendar.store'), options);
    }
};

const destroy = (event) => {
    if (!confirm(`Delete "${event.title}"?`)) {
        return;
    }
    router.delete(route('calendar.destroy', event.id), { preserveScroll: true });
};

// ------------------------------------------------------------------
// Highlight colours (which palette colours mark an event "important")
// ------------------------------------------------------------------

const highlightForm = useForm({
    calendar_highlight_colors: [...props.highlightColors],
});

const toggleHighlight = (name) => {
    const list = highlightForm.calendar_highlight_colors;
    const index = list.indexOf(name);
    if (index === -1) {
        list.push(name);
    } else {
        list.splice(index, 1);
    }
};

const saveHighlights = () => {
    highlightForm.put(route('admin.calendar.settings'), { preserveScroll: true });
};
</script>

<template>
    <SettingsLayout title="Calendar">
        <div class="space-y-6">
            <Card>
                <h3 class="text-sm font-semibold">Important-event colours</h3>
                <p class="mt-1 text-xs text-base-content/60">
                    Events using one of these colours are flagged as important on the public calendar.
                </p>
                <div class="mt-3 flex flex-wrap gap-2">
                    <button v-for="c in COLORS" :key="c.name" type="button"
                            class="h-7 w-7 rounded-full ring-base-content ring-offset-2 ring-offset-base-100 transition"
                            :class="{ 'ring-2': highlightForm.calendar_highlight_colors.includes(c.name) }"
                            :style="{ backgroundColor: c.hex }"
                            :title="c.name"
                            @click="toggleHighlight(c.name)"></button>
                </div>
                <InputError v-if="highlightForm.errors.calendar_highlight_colors"
                            :messages="highlightForm.errors.calendar_highlight_colors" />
                <div class="mt-4 flex justify-end">
                    <PrimaryButton :class="{ 'opacity-25': highlightForm.processing }"
                                    :disabled="highlightForm.processing"
                                    @click="saveHighlights">
                        Save
                    </PrimaryButton>
                </div>
            </Card>

            <div class="flex items-baseline justify-between">
                <h3 class="text-sm font-semibold">Events</h3>
                <PrimaryButton @click="openCreate">New event</PrimaryButton>
            </div>

            <div>
                <h4 class="mb-2 text-xs font-semibold uppercase tracking-wide text-base-content/50">Upcoming</h4>
                <DataTable :columns="columns" :rows="upcoming" row-key="id">
                    <template #cell-title="{ row }">
                        <span class="inline-flex items-center gap-2">
                            <span class="h-2.5 w-2.5 shrink-0 rounded-full"
                                  :style="{ backgroundColor: eventHex(row) }"></span>
                            <span :class="{ 'font-semibold': row.highlighted }">{{ row.title }}</span>
                        </span>
                    </template>
                    <template #cell-starts_at="{ row }">{{ dt(row.starts_at) }}</template>
                    <template #cell-created_by="{ row }">{{ row.created_by.name }}</template>
                    <template #cell-actions="{ row }">
                        <div class="flex justify-end gap-2">
                            <SecondaryButton v-if="row.can?.update !== false" type="button" class="btn-xs"
                                              @click="openEdit(row)">
                                Edit
                            </SecondaryButton>
                            <DangerButton v-if="row.can?.delete !== false" type="button" class="btn-xs"
                                          @click="destroy(row)">
                                Delete
                            </DangerButton>
                        </div>
                    </template>
                    <template #empty>No upcoming events.</template>
                </DataTable>
            </div>

            <div>
                <h4 class="mb-2 text-xs font-semibold uppercase tracking-wide text-base-content/50">Past</h4>
                <DataTable :columns="columns" :rows="past" row-key="id">
                    <template #cell-title="{ row }">
                        <span class="inline-flex items-center gap-2">
                            <span class="h-2.5 w-2.5 shrink-0 rounded-full"
                                  :style="{ backgroundColor: eventHex(row) }"></span>
                            <span :class="{ 'font-semibold': row.highlighted }">{{ row.title }}</span>
                        </span>
                    </template>
                    <template #cell-starts_at="{ row }">{{ dt(row.starts_at) }}</template>
                    <template #cell-created_by="{ row }">{{ row.created_by.name }}</template>
                    <template #cell-actions="{ row }">
                        <div class="flex justify-end gap-2">
                            <SecondaryButton v-if="row.can?.update !== false" type="button" class="btn-xs"
                                              @click="openEdit(row)">
                                Edit
                            </SecondaryButton>
                            <DangerButton v-if="row.can?.delete !== false" type="button" class="btn-xs"
                                          @click="destroy(row)">
                                Delete
                            </DangerButton>
                        </div>
                    </template>
                    <template #empty>No past events.</template>
                </DataTable>
            </div>
        </div>

        <DialogModal :show="showForm" @close="showForm = false">
            <template #title>{{ editing ? 'Edit event' : 'New event' }}</template>

            <template #content>
                <div class="space-y-4">
                    <div>
                        <InputLabel for="event-title" value="Title" />
                        <TextInput id="event-title" v-model="form.title" type="text" class="mt-1 w-full"
                                   :error="form.errors.title !== undefined" />
                        <InputError v-if="form.errors.title" :messages="form.errors.title" />
                    </div>

                    <div>
                        <InputLabel for="event-description" value="Description" />
                        <textarea id="event-description" v-model="form.description"
                                  class="textarea textarea-bordered mt-1 block w-full" rows="3" />
                        <InputError v-if="form.errors.description" :messages="form.errors.description" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="event-type" value="Type" />
                            <SelectInput id="event-type" v-model="form.event_type" class="mt-1"
                                         :error="form.errors.event_type !== undefined">
                                <option v-for="type in eventTypes" :key="type.value" :value="type.value">
                                    {{ type.label }}
                                </option>
                            </SelectInput>
                            <InputError v-if="form.errors.event_type" :messages="form.errors.event_type" />
                        </div>

                        <div>
                            <InputLabel value="Item" />
                            <ItemPicker v-model="form.item_id" class="mt-1"
                                        :model-item="editing?.item ?? null"
                                        :error="form.errors.item_id !== undefined" />
                            <InputError v-if="form.errors.item_id" :messages="form.errors.item_id" />
                        </div>
                    </div>

                    <div>
                        <InputLabel value="Colour" />
                        <div class="mt-2 flex flex-wrap gap-2">
                            <button v-for="c in COLORS" :key="c.name" type="button"
                                    class="h-7 w-7 rounded-full ring-base-content ring-offset-2 ring-offset-base-100 transition"
                                    :class="{ 'ring-2': form.color === c.name }"
                                    :style="{ backgroundColor: c.hex }"
                                    :title="c.name"
                                    @click="form.color = c.name"></button>
                        </div>
                        <InputError v-if="form.errors.color" :messages="form.errors.color" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="event-starts-at" value="Starts" />
                            <TextInput id="event-starts-at" v-model="form.starts_at" type="datetime-local"
                                       class="mt-1 w-full" :error="form.errors.starts_at !== undefined" />
                            <InputError v-if="form.errors.starts_at" :messages="form.errors.starts_at" />
                        </div>
                        <div>
                            <InputLabel for="event-ends-at" value="Ends (optional)" />
                            <TextInput id="event-ends-at" v-model="form.ends_at" type="datetime-local"
                                       class="mt-1 w-full" :error="form.errors.ends_at !== undefined" />
                            <InputError v-if="form.errors.ends_at" :messages="form.errors.ends_at" />
                        </div>
                    </div>
                </div>
            </template>

            <template #footer>
                <SecondaryButton @click="showForm = false">Cancel</SecondaryButton>
                <PrimaryButton class="ms-3" :class="{ 'opacity-25': form.processing }" :disabled="form.processing"
                               @click="submit">
                    {{ editing ? 'Save' : 'Create' }}
                </PrimaryButton>
            </template>
        </DialogModal>
    </SettingsLayout>
</template>
