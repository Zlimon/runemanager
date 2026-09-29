<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from "vue";
import { usePage } from "@inertiajs/vue3";
import Card from "@/Components/Cards/Card.vue";
import SecondaryButton from "@/Components/Buttons/SecondaryButton.vue";
import ChoiceChips from "@/Components/Tables/ChoiceChips.vue";
import RecentEventRow from "@/Pages/Accounts/Partials/RecentEventRow.vue";

/*
 * The Adventurer's Log "Recent Events" list: milestones from the live feed
 * merged with Wise Old Man records and achievements, newest first. The server
 * writes every sentence; this list only pages through them (accounts.events)
 * and picks up new milestones as they are broadcast on the public feed.
 *
 * With Wise Old Man on, tabs narrow the list to the in-game milestones (the
 * ones that can be pinned) or to the Wise Old Man records. The server filters,
 * so "Show older events" pages within the chosen tab.
 */
const props = defineProps({
    account: { type: Object, required: true },
    // { data: RecentEvent[], next_cursor: string|null }
    events: { type: Object, default: () => ({ data: [], next_cursor: null }) },
});

const page = usePage();

const FILTERS = [
    { value: 'all', label: 'All' },
    { value: 'game', label: 'Game events' },
    { value: 'records', label: 'Records' },
];

// Records only exist when the instance tracks Wise Old Man.
const showFilters = computed(() => Boolean(page.props.wom_enabled));

const filter = ref('all');
const items = ref([...(props.events?.data ?? [])]);
const nextCursor = ref(props.events?.next_cursor ?? null);
const loading = ref(false);
const failed = ref(false);
// True while a tab's first page loads; the old list stays, dimmed, until then.
const switching = ref(false);

// A full reload of the prop (e.g. after a navigation back) starts over.
watch(() => props.events, (events) => {
    filter.value = 'all';
    items.value = [...(events?.data ?? [])];
    nextCursor.value = events?.next_cursor ?? null;
});

const fetchPage = (before = null) => window.axios.get(
    route('accounts.events', { account: props.account.username }),
    {
        params: {
            ...(before ? { before } : {}),
            ...(filter.value !== 'all' ? { filter: filter.value } : {}),
        },
    },
).then((response) => response.data);

// Switching tabs loads that tab's first page; a late answer for a tab the
// viewer already left is dropped.
let request = 0;
const selectFilter = async (value) => {
    if (value === filter.value) {
        return;
    }
    filter.value = value;
    const current = ++request;
    switching.value = true;
    failed.value = false;
    try {
        const first = await fetchPage();
        if (current === request) {
            items.value = first.data;
            nextCursor.value = first.next_cursor;
        }
    } catch {
        if (current === request) {
            failed.value = true;
        }
    } finally {
        if (current === request) {
            switching.value = false;
        }
    }
};

const loadMore = async () => {
    if (!nextCursor.value || loading.value || switching.value) {
        return;
    }
    loading.value = true;
    const current = request;
    try {
        const page = await fetchPage(nextCursor.value);
        // The viewer switched tabs meanwhile: this page belongs to the old one.
        if (current !== request) {
            return;
        }
        const seen = new Set(items.value.map((e) => e.id));
        items.value = [...items.value, ...page.data.filter((e) => !seen.has(e.id))];
        nextCursor.value = page.next_cursor;
    } finally {
        loading.value = false;
    }
};

// New milestones: fetch the first page again and prepend what isn't shown yet.
// A failed refresh keeps the current list; the next broadcast tries again.
// Milestones are game events, so the Records tab has nothing to add.
const refreshHead = async () => {
    if (filter.value === 'records') {
        return;
    }
    try {
        const page = await fetchPage();
        const seen = new Set(items.value.map((e) => e.id));
        const fresh = page.data.filter((e) => !seen.has(e.id));
        if (fresh.length) {
            items.value = [...fresh, ...items.value];
        }
    } catch {
        // Keep what is shown.
    }
};

// Pinning from a row, or unpinning from the gallery (through the page).
const setPinned = (feedEventId, pinned) => {
    items.value = items.value.map((e) => (e.feed_event_id === feedEventId ? { ...e, pinned } : e));
};

defineExpose({ setPinned });

onMounted(() => {
    window.Echo.channel("feed").listen(".FeedEventCreated", (event) => {
        if (event.account?.username !== props.account.username || !event.milestone) {
            return;
        }
        refreshHead();
    });
});

onBeforeUnmount(() => window.Echo.leave("feed"));
</script>

<template>
    <Card frame="dialog" padding="p-4">
        <div class="mb-2 flex items-baseline justify-between">
            <h3>Recent Events</h3>
            <span class="flex items-center gap-1.5 text-xs text-base-content/60">
                <span class="inline-block h-1.5 w-1.5 rounded-full bg-success"></span>
                Live
            </span>
        </div>

        <ChoiceChips v-if="showFilters" :options="FILTERS" :model-value="filter" label="Show"
                     class="mb-3" @update:model-value="selectFilter" />

        <p v-if="failed" role="alert" class="py-6 text-center text-sm text-error">
            These events could not be loaded. Try again in a moment.
        </p>
        <ul v-else-if="items.length" :class="{ 'opacity-60': switching }">
            <RecentEventRow v-for="event in items" :key="event.id" :event="event" :account="account"
                            @pin-changed="setPinned" />
        </ul>
        <p v-else-if="!switching" class="py-6 text-center text-sm text-base-content/60">
            {{ filter === 'game' ? 'No game events yet.' : filter === 'records' ? 'No Wise Old Man records yet.' : 'Nothing to report yet.' }}
        </p>

        <div v-if="nextCursor" class="mt-3 flex justify-center">
            <SecondaryButton class="btn-sm" :disabled="loading" @click="loadMore">
                {{ loading ? 'Loading…' : 'Show older events' }}
            </SecondaryButton>
        </div>
    </Card>
</template>
