<script setup>
import { computed, nextTick, ref, watch } from "vue";
import { router, useForm, usePage } from "@inertiajs/vue3";
import dayjs from "dayjs";
import { Calendar as VCalendar } from "v-calendar";
import { useEventColor } from "@/composables/useEventColor";
import "v-calendar/dist/style.css";
import AppLayout from "@/Layouts/AppLayout.vue";
import Badge from "@/Components/Badge.vue";
import ConfirmationModal from "@/Components/ConfirmationModal.vue";
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
    events: { type: Array, default: () => [] },
    upcoming: { type: Array, default: () => [] },
    eventTypes: { type: Array, default: () => [] },
});

const page = usePage();
const isAdmin = computed(() => page.props.is_admin === true);
// Vanilla is a fixed dark-brown scheme with `dark_mode` always false, so
// the calendar's own light/dark styling must follow the effective theme instead.
const isDark = computed(() => page.props.theme === 'runemanager-dark' || page.props.theme === 'runemanager-vanilla');

// Event colours live in the composable so the calendar, the dashboard and the
// landing page all resolve an event to the same swatch.
const { COLORS, colorFor, eventHex } = useEventColor();

// Highlighted events get a warning-coloured ring rather than a border or a
// background fill. A background would fight `resource-pack-border`'s sprite
// frame (drawn on top of the card, opaque) and a literal `border-*` colour
// would be silently nuked — per-pack.css forces `.resource-pack-border`'s
// `border-color` to `transparent !important` once a pack is active, since the
// sprite itself becomes the edge. A `ring-*` is a box-shadow, not a border —
// it draws outside the sprite frame instead of under or through it, so it
// survives all three themes AND every resource pack untouched.
const highlightRing = (event) =>
    event?.highlighted ? 'ring-2 ring-warning ring-offset-2 ring-offset-base-100' : '';

// Earliest selectable datetime — start of today (no past events).
const todayMin = dayjs().startOf('day').format('YYYY-MM-DDTHH:mm');

// Open on the current month. V-Calendar's month is 1-based, dayjs' is 0-based.
const initialPage = { month: dayjs().month() + 1, year: dayjs().year() };

const isPastDay = (id) => dayjs(id).isBefore(dayjs(), 'day');

// Map each event to a V-Calendar attribute. A multi-day event uses a date range
// so it renders as a connected bar across every day it spans. A second,
// dateless-start attribute mutes every day before today via V-Calendar's own
// `content` glyph class, so past days read as non-interactive without touching
// the `onDayClick` guard that actually blocks creating on them (see below).
const attributes = computed(() => [
    ...props.events.map((event) => ({
        key: event.id,
        customData: event,
        dates: event.ends_at
            ? { start: new Date(event.starts_at), end: new Date(event.ends_at) }
            : new Date(event.starts_at),
        bar: event.color ?? colorFor(event.event_type),
        popover: { visibility: 'hover' },
    })),
    {
        key: 'past-days-muted',
        order: 100,
        dates: { start: null, end: dayjs().subtract(1, 'day').toDate() },
        content: { class: '!cursor-default opacity-40' },
    },
]);

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
    if (isAdmin.value && !isPastDay(day.id)) {
        openCreate(day.id);
    }
};

// --- Create / edit (same form and modal for both) ---
const showForm = ref(false);
const editingId = ref(null);
const editingItem = ref(null); // pre-resolved { id, name, icon } so ItemPicker
                                // can show the current icon without a lookup.
const form = useForm({
    title: '',
    description: '',
    event_type: 'pvm_mass',
    color: 'orange',
    item_id: null,
    starts_at: '',
    ends_at: '',
});

// Suppressed while an edit populates the form, so the auto-suggest below
// doesn't invent an end time for an event that genuinely has none.
let populating = false;

watch(() => form.starts_at, (starts) => {
    if (populating) {
        return;
    }
    if (starts && !form.ends_at) {
        form.ends_at = dayjs(starts).add(1, 'hour').format('YYYY-MM-DDTHH:mm');
    }
});

const openCreate = (date = null) => {
    editingId.value = null;
    editingItem.value = null;
    form.reset();
    form.clearErrors();
    if (date) {
        form.starts_at = `${date}T18:00`;
    }
    showForm.value = true;
};

const openEdit = (event) => {
    populating = true;
    editingId.value = event.id;
    editingItem.value = event.item;
    form.clearErrors();
    form.title = event.title;
    form.description = event.description ?? '';
    form.event_type = event.event_type;
    form.color = event.color ?? colorFor(event.event_type);
    form.item_id = event.item?.id ?? null;
    form.starts_at = dayjs(event.starts_at).format('YYYY-MM-DDTHH:mm');
    form.ends_at = event.ends_at ? dayjs(event.ends_at).format('YYYY-MM-DDTHH:mm') : '';
    selectedId.value = null;
    showForm.value = true;
    nextTick(() => { populating = false; });
};

const submit = () => {
    const options = {
        preserveScroll: true,
        onSuccess: () => {
            showForm.value = false;
            form.reset();
            editingId.value = null;
            editingItem.value = null;
        },
    };
    if (editingId.value) {
        form.put(route('calendar.update', editingId.value), options);
    } else {
        form.post(route('calendar.store'), options);
    }
};

// --- Detail ---
// Holds only the id, and looks the event back up from the current `events` /
// `upcoming` props on every access — so the modal always reflects the latest
// RSVP list, `can` flags, etc. after a partial reload, rather than a stale
// snapshot taken when it was opened.
const selectedId = ref(null);
const selected = computed(() => {
    if (selectedId.value === null) {
        return null;
    }
    return props.events.find((e) => e.id === selectedId.value)
        ?? props.upcoming.find((e) => e.id === selectedId.value)
        ?? null;
});
const dt = (iso) => dayjs(iso).format('ddd MMM D, YYYY h:mm A');

// --- Delete ---
const confirmingDeleteId = ref(null);
const destroy = () => {
    if (confirmingDeleteId.value === null) {
        return;
    }
    router.delete(route('calendar.destroy', confirmingDeleteId.value), {
        preserveScroll: true,
        onSuccess: () => {
            confirmingDeleteId.value = null;
            selectedId.value = null;
        },
    });
};

// --- RSVP (per account, not per user) ---
const myAccounts = computed(() => page.props.my_accounts ?? []);
const myAvailableAccounts = computed(() => {
    if (!selected.value) {
        return [];
    }
    const attending = new Set(selected.value.rsvps.map((r) => r.account_id));
    return myAccounts.value.filter((a) => !attending.has(a.id));
});

// A member attends as exactly one account, so once one of theirs is on the list
// the rest offer to move the attendance rather than add a second.
const myAttendingAccountId = computed(() =>
    selected.value?.rsvps.find((r) => myAccountIds.value.has(r.account_id))?.account_id ?? null);

// `can.rsvp` is false both for a viewer with no accounts and for an event that
// has been and gone; only the latter is worth explaining in the panel.
const selectedHasPassed = computed(() => {
    if (!selected.value) {
        return false;
    }
    return dayjs(selected.value.ends_at ?? selected.value.starts_at).isBefore(dayjs());
});
const myAccountIds = computed(() => new Set(myAccounts.value.map((a) => a.id)));

const toggleRsvp = (accountId, attending) => {
    if (!selected.value) {
        return;
    }
    if (attending) {
        router.delete(route('calendar.rsvp.destroy', selected.value.id), {
            data: { account_id: accountId },
            preserveScroll: true,
            preserveState: true,
        });
    } else {
        router.post(route('calendar.rsvp', selected.value.id), { account_id: accountId }, {
            preserveScroll: true,
            preserveState: true,
        });
    }
};
</script>

<template>
    <AppLayout title="Calendar">
        <div class="py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h1 class="header-chatbox-sword text-2xl font-bold text-base-content">Calendar</h1>
                    <PrimaryButton v-if="isAdmin" @click="openCreate()">New event</PrimaryButton>
                </div>

                <div class="mt-4 p-3 pack-bg-card border pack-accent-border rounded resource-pack-border">
                    <VCalendar expanded borderless transparent
                               :is-dark="isDark"
                               :initial-page="initialPage"
                               :attributes="attributes"
                               title-position="left"
                               @dayclick="onDayClick"
                               @did-move="onMove">
                        <template #day-popover="{ attributes: dayAttrs }">
                            <div class="max-w-52 space-y-1 p-1">
                                <button v-for="attr in dayAttrs" :key="attr.key"
                                        type="button"
                                        class="flex w-full items-center gap-1.5 rounded px-2 py-1 text-left text-sm hover:bg-base-content/10"
                                        @click="selectedId = attr.customData.id">
                                    <span class="inline-block h-2 w-2 shrink-0 rounded-full"
                                          :style="{ backgroundColor: eventHex(attr.customData) }"></span>
                                    <img v-if="attr.customData.item?.icon" :src="`data:image/jpeg;base64,${attr.customData.item.icon}`"
                                         class="h-4 w-4 shrink-0 object-contain" alt="">
                                    <Badge size="xs" class="shrink-0">{{ attr.customData.event_type_label }}</Badge>
                                    <span class="truncate">{{ attr.customData.title }}</span>
                                </button>
                            </div>
                        </template>
                    </VCalendar>
                </div>
                <p v-if="isAdmin" class="mt-1 text-xs text-base-content/50">
                    Tip: click a day to add an event there.
                </p>

                <!-- Upcoming widget -->
                <h2 class="mt-8 mb-2 text-lg font-semibold text-base-content">Upcoming</h2>
                <div v-if="upcoming.length === 0"
                     class="p-4 text-center text-sm text-base-content/60 pack-bg-card border pack-accent-border rounded resource-pack-border">
                    No upcoming events scheduled.
                </div>
                <ul v-else class="space-y-2">
                    <li v-for="event in upcoming" :key="event.id"
                        class="flex cursor-pointer items-center justify-between gap-2 p-3 pack-bg-card border pack-accent-border rounded resource-pack-border"
                        :class="highlightRing(event)"
                        @click="selectedId = event.id">
                        <div class="flex min-w-0 items-center gap-2">
                            <span class="inline-block h-2.5 w-2.5 shrink-0 rounded-full"
                                  :style="{ backgroundColor: eventHex(event) }"></span>
                            <img v-if="event.item?.icon" :src="`data:image/jpeg;base64,${event.item.icon}`"
                                 class="h-6 w-6 shrink-0 object-contain" :alt="event.item.name">
                            <Badge>{{ event.event_type_label }}</Badge>
                            <span class="truncate font-semibold">{{ event.title }}</span>
                        </div>
                        <span class="text-xs text-base-content/60">{{ dt(event.starts_at) }}</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Event detail -->
        <DialogModal :show="selected !== null" @close="selectedId = null">
            <template #title>
                <div class="flex items-center gap-2">
                    <span v-if="selected?.highlighted" class="text-warning" title="Highlighted event">★</span>
                    <Badge>{{ selected?.event_type_label }}</Badge>
                    {{ selected?.title }}
                </div>
            </template>
            <template #content>
                <!-- Highlighted events get the same warning ring as the Upcoming row,
                     wrapped around the content rather than the outer modal box since
                     DialogModal doesn't expose that surface to style from here. -->
                <div class="rounded" :class="[highlightRing(selected), { 'p-2': selected?.highlighted }]">
                    <div v-if="selected?.item" class="mb-2 flex items-center gap-2">
                        <img v-if="selected.item.icon" :src="`data:image/jpeg;base64,${selected.item.icon}`"
                             class="h-8 w-8 shrink-0 object-contain" :alt="selected.item.name">
                        <span class="text-sm text-base-content/80">{{ selected.item.name }}</span>
                    </div>
                    <p v-if="selected?.description" class="text-sm text-base-content/80">{{ selected.description }}</p>
                    <p class="mt-2 text-sm text-base-content/70">
                        {{ selected ? dt(selected.starts_at) : '' }}
                        <template v-if="selected?.ends_at"> → {{ dt(selected.ends_at) }}</template>
                    </p>
                    <p class="mt-1 text-xs text-base-content/60">by {{ selected?.created_by.name }}</p>

                    <div v-if="selected && (selected.can.rsvp || selected.rsvps.length)"
                         class="mt-4 border-t border-base-300 pt-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-base-content/50">Attending</p>
                        <ul v-if="selected.rsvps.length" class="mt-2 space-y-1">
                            <li v-for="rsvp in selected.rsvps" :key="rsvp.account_id"
                                class="flex items-center justify-between gap-2 text-sm">
                                <span class="flex items-center gap-1.5">
                                    <img v-if="rsvp.account_type && rsvp.account_type !== 'normal'"
                                         :src="`/images/${rsvp.account_type}.png`"
                                         class="h-4 w-4 object-contain" alt="">
                                    {{ rsvp.username }}
                                </span>
                                <button v-if="selected.can.rsvp && myAccountIds.has(rsvp.account_id)" type="button"
                                        class="text-xs text-base-content/60 hover:text-error hover:underline"
                                        @click="toggleRsvp(rsvp.account_id, true)">
                                    Withdraw
                                </button>
                            </li>
                        </ul>
                        <p v-else class="mt-1 text-xs text-base-content/50">No one attending yet.</p>

                        <p v-if="selected && !selected.can.rsvp && selectedHasPassed"
                           class="mt-2 text-xs text-base-content/50">
                            This event has already taken place.
                        </p>

                        <div v-if="selected.can.rsvp && myAvailableAccounts.length" class="mt-2 flex flex-wrap gap-2">
                            <button v-for="account in myAvailableAccounts" :key="account.id" type="button"
                                    class="badge badge-outline gap-1"
                                    @click="toggleRsvp(account.id, false)">
                                <img v-if="account.account_type && account.account_type !== 'normal'"
                                     :src="`/images/${account.account_type}.png`"
                                     class="h-3 w-3 object-contain" alt="">
                                {{ myAttendingAccountId === null ? 'RSVP as' : 'Switch to' }} {{ account.username }}
                            </button>
                        </div>
                    </div>
                </div>
            </template>
            <template #footer>
                <DangerButton v-if="selected?.can.delete" type="button" @click="confirmingDeleteId = selected.id">
                    Delete
                </DangerButton>
                <SecondaryButton v-if="selected?.can.update" class="ms-3" type="button" @click="openEdit(selected)">
                    Edit
                </SecondaryButton>
                <SecondaryButton class="ms-3" @click="selectedId = null">Close</SecondaryButton>
            </template>
        </DialogModal>

        <!-- Delete confirmation -->
        <ConfirmationModal :show="confirmingDeleteId !== null" @close="confirmingDeleteId = null">
            <template #title>Delete event</template>
            <template #content>
                Are you sure you want to delete this event? This action cannot be undone.
            </template>
            <template #footer>
                <SecondaryButton @click="confirmingDeleteId = null">Cancel</SecondaryButton>
                <DangerButton class="ms-3" @click="destroy">Delete</DangerButton>
            </template>
        </ConfirmationModal>

        <!-- Create / edit -->
        <DialogModal :show="showForm" @close="showForm = false">
            <template #title>{{ editingId ? 'Edit event' : 'Create event' }}</template>
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
                        <SelectInput id="event-type" v-model="form.event_type" class="mt-1 block"
                                     :error="form.errors.event_type !== undefined">
                            <option v-for="type in eventTypes" :key="type.value" :value="type.value">{{ type.label }}</option>
                        </SelectInput>
                        <InputError v-if="form.errors.event_type" :messages="form.errors.event_type" />
                    </div>

                    <div>
                        <InputLabel value="Item icon (optional)" />
                        <ItemPicker v-model="form.item_id" :model-item="editingItem"
                                    class="mt-1" :error="form.errors.item_id !== undefined" />
                        <InputError v-if="form.errors.item_id" :messages="form.errors.item_id" />
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
                <SecondaryButton @click="showForm = false">Cancel</SecondaryButton>
                <PrimaryButton class="ms-3" :class="{ 'opacity-25': form.processing }"
                               :disabled="form.processing" @click="submit">
                    {{ editingId ? 'Save changes' : 'Create' }}
                </PrimaryButton>
            </template>
        </DialogModal>
    </AppLayout>
</template>
