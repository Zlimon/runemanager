<script setup>
import { computed, ref, watch } from "vue";
import { router, useForm, usePage } from "@inertiajs/vue3";
import dayjs from "dayjs";
import { Calendar as VCalendar } from "v-calendar";
import "v-calendar/dist/style.css";
import AppLayout from "@/Layouts/AppLayout.vue";
import Badge from "@/Components/Badge.vue";
import DialogModal from "@/Components/DialogModal.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import DangerButton from "@/Components/DangerButton.vue";
import TextInput from "@/Components/TextInput.vue";

const props = defineProps({
    events: { type: Array, default: () => [] },
    upcoming: { type: Array, default: () => [] },
    eventTypes: { type: Array, default: () => [] },
});

const page = usePage();
const currentUserId = computed(() => page.props.auth?.user?.id ?? null);
const isAdmin = computed(() => page.props.is_admin === true);
const isDark = computed(() => page.props.dark_mode === true);

// Selectable event colours (V-Calendar palette names + a swatch hex to show).
const COLORS = [
    { name: 'orange', hex: '#f97316' },
    { name: 'red', hex: '#ef4444' },
    { name: 'yellow', hex: '#eab308' },
    { name: 'green', hex: '#22c55e' },
    { name: 'teal', hex: '#14b8a6' },
    { name: 'blue', hex: '#3b82f6' },
    { name: 'indigo', hex: '#6366f1' },
    { name: 'purple', hex: '#a855f7' },
    { name: 'pink', hex: '#ec4899' },
    { name: 'gray', hex: '#6b7280' },
];

// Deterministic per-type fallback colour when an event has no chosen colour.
const colorFor = (type) =>
    COLORS[[...(type ?? '')].reduce((sum, c) => sum + c.charCodeAt(0), 0) % COLORS.length].name;

// Earliest selectable datetime — start of today (no past events).
const todayMin = dayjs().startOf('day').format('YYYY-MM-DDTHH:mm');

// Map each event to a V-Calendar attribute. A multi-day event uses a date range
// so it renders as a connected bar across every day it spans.
const attributes = computed(() =>
    props.events.map((event) => ({
        key: event.id,
        customData: event,
        dates: event.ends_at
            ? { start: new Date(event.starts_at), end: new Date(event.ends_at) }
            : new Date(event.starts_at),
        bar: event.color ?? colorFor(event.event_type),
        popover: { visibility: 'hover' },
    })));

// Reload events for whatever week-range the calendar moves to (multi-month safe).
// Skip the first did-move (fired on mount) — the server already sent that range.
let initialMove = true;
const onMove = (pages) => {
    if (initialMove) {
        initialMove = false;
        return;
    }
    const days = pages.flatMap((p) => p.days);
    if (!days.length) {
        return;
    }
    router.reload({
        only: ['events'],
        data: { from: days[0].id, to: days[days.length - 1].id },
        preserveState: true,
        preserveScroll: true,
    });
};

const onDayClick = (day) => {
    // No creating in the past.
    if (isAdmin.value && !dayjs(day.id).isBefore(dayjs(), 'day')) {
        openCreate(day.id);
    }
};

// --- Create ---
const showCreate = ref(false);
const form = useForm({
    title: '',
    description: '',
    event_type: 'pvm_mass',
    color: 'orange',
    starts_at: '',
    ends_at: '',
});

watch(() => form.starts_at, (starts) => {
    if (starts && !form.ends_at) {
        form.ends_at = dayjs(starts).add(1, 'hour').format('YYYY-MM-DDTHH:mm');
    }
});

const openCreate = (date = null) => {
    form.reset();
    form.clearErrors();
    if (date) {
        form.starts_at = `${date}T18:00`;
    }
    showCreate.value = true;
};

const submit = () => {
    form.post(route('calendar.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showCreate.value = false;
            form.reset();
        },
    });
};

// --- Detail ---
const selected = ref(null);
const dt = (iso) => dayjs(iso).format('ddd MMM D, YYYY h:mm A');

const destroy = (event) => {
    if (!confirm(`Delete "${event.title}"?`)) {
        return;
    }
    router.delete(route('calendar.destroy', event.id), {
        preserveScroll: true,
        onSuccess: () => { selected.value = null; },
    });
};
</script>

<template>
    <AppLayout title="Calendar">
        <div class="py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h1 class="header-chatbox-sword text-2xl font-bold dark:text-white">Calendar</h1>
                    <PrimaryButton v-if="isAdmin" @click="openCreate()">New event</PrimaryButton>
                </div>

                <div class="mt-4 rounded p-3 pack-bg-card resource-pack-border">
                    <VCalendar expanded borderless transparent
                               :is-dark="isDark"
                               :attributes="attributes"
                               title-position="left"
                               @dayclick="onDayClick"
                               @did-move="onMove">
                        <template #day-popover="{ attributes: dayAttrs }">
                            <div class="space-y-1 p-1">
                                <button v-for="attr in dayAttrs" :key="attr.key"
                                        type="button"
                                        class="block w-full truncate rounded px-2 py-1 text-left text-sm hover:bg-white/10"
                                        @click="selected = attr.customData">
                                    {{ attr.customData.title }}
                                </button>
                            </div>
                        </template>
                    </VCalendar>
                </div>
                <p v-if="isAdmin" class="mt-1 text-xs text-base-content/50">
                    Tip: click a day to add an event there.
                </p>

                <!-- Upcoming widget -->
                <h2 class="mt-8 mb-2 text-lg font-semibold dark:text-white">Upcoming</h2>
                <div v-if="upcoming.length === 0"
                     class="rounded p-4 text-center text-sm text-base-content/60 pack-bg-card resource-pack-border">
                    No upcoming events scheduled.
                </div>
                <ul v-else class="space-y-2">
                    <li v-for="event in upcoming" :key="event.id"
                        class="flex cursor-pointer items-center justify-between gap-2 rounded p-3 pack-bg-card resource-pack-border"
                        @click="selected = event">
                        <div class="flex items-center gap-2">
                            <Badge>{{ event.event_type_label }}</Badge>
                            <span class="font-semibold">{{ event.title }}</span>
                        </div>
                        <span class="text-xs text-base-content/60">{{ dt(event.starts_at) }}</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Event detail -->
        <DialogModal :show="selected !== null" @close="selected = null">
            <template #title>
                <div class="flex items-center gap-2">
                    <Badge>{{ selected?.event_type_label }}</Badge>
                    {{ selected?.title }}
                </div>
            </template>
            <template #content>
                <p v-if="selected?.description" class="text-sm text-base-content/80">{{ selected.description }}</p>
                <p class="mt-2 text-sm text-base-content/70">
                    {{ selected ? dt(selected.starts_at) : '' }}
                    <template v-if="selected?.ends_at"> → {{ dt(selected.ends_at) }}</template>
                </p>
                <p class="mt-1 text-xs text-base-content/60">by {{ selected?.created_by.name }}</p>
            </template>
            <template #footer>
                <DangerButton v-if="selected && isAdmin && currentUserId === selected.created_by.id"
                              type="button" @click="destroy(selected)">
                    Delete
                </DangerButton>
                <SecondaryButton class="ms-3" @click="selected = null">Close</SecondaryButton>
            </template>
        </DialogModal>

        <!-- Create -->
        <DialogModal :show="showCreate" @close="showCreate = false">
            <template #title>Create event</template>
            <template #content>
                <div class="space-y-4">
                    <div>
                        <InputLabel for="event-title" value="Title" />
                        <TextInput id="event-title" v-model="form.title" type="text" class="mt-1 block w-full"
                                   :error="form.errors.title !== undefined" />
                        <InputError v-if="form.errors.title" :messages="form.errors.title" />
                    </div>

                    <div>
                        <InputLabel for="event-type" value="Type" />
                        <select id="event-type" v-model="form.event_type" class="select select-bordered mt-1 block w-full">
                            <option v-for="type in eventTypes" :key="type.value" :value="type.value">{{ type.label }}</option>
                        </select>
                        <InputError v-if="form.errors.event_type" :messages="form.errors.event_type" />
                    </div>

                    <div>
                        <InputLabel value="Colour" />
                        <div class="mt-1 flex flex-wrap gap-2">
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
                                       :min="todayMin"
                                       class="mt-1 block w-full" :error="form.errors.starts_at !== undefined" />
                            <InputError v-if="form.errors.starts_at" :messages="form.errors.starts_at" />
                        </div>
                        <div>
                            <div class="flex items-baseline justify-between">
                                <InputLabel for="event-ends-at" value="Ends (optional)" />
                                <button v-if="form.ends_at" type="button"
                                        class="text-xs text-base-content/60 hover:underline"
                                        @click="form.ends_at = ''">
                                    Clear
                                </button>
                            </div>
                            <TextInput id="event-ends-at" v-model="form.ends_at" type="datetime-local"
                                       :min="form.starts_at || todayMin"
                                       class="mt-1 block w-full" :error="form.errors.ends_at !== undefined" />
                            <InputError v-if="form.errors.ends_at" :messages="form.errors.ends_at" />
                        </div>
                    </div>

                    <div>
                        <InputLabel for="event-description" value="Description" />
                        <textarea id="event-description" v-model="form.description" rows="3"
                                  class="textarea textarea-bordered mt-1 block w-full" />
                        <InputError v-if="form.errors.description" :messages="form.errors.description" />
                    </div>
                </div>
            </template>
            <template #footer>
                <SecondaryButton @click="showCreate = false">Cancel</SecondaryButton>
                <PrimaryButton class="ms-3" :class="{ 'opacity-25': form.processing }"
                               :disabled="form.processing" @click="submit">
                    Create
                </PrimaryButton>
            </template>
        </DialogModal>
    </AppLayout>
</template>
