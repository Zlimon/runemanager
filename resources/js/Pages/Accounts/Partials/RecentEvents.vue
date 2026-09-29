<script setup>
import { onBeforeUnmount, onMounted, ref, watch } from "vue";
import Card from "@/Components/Cards/Card.vue";
import SecondaryButton from "@/Components/Buttons/SecondaryButton.vue";
import RecentEventRow from "@/Pages/Accounts/Partials/RecentEventRow.vue";

/*
 * The Adventurer's Log "Recent Events" list: milestones from the live feed
 * merged with Wise Old Man records and achievements, newest first. The server
 * writes every sentence; this list only pages through them (accounts.events)
 * and picks up new milestones as they are broadcast on the public feed.
 */
const props = defineProps({
    account: { type: Object, required: true },
    // { data: RecentEvent[], next_cursor: string|null }
    events: { type: Object, default: () => ({ data: [], next_cursor: null }) },
});

const items = ref([...(props.events?.data ?? [])]);
const nextCursor = ref(props.events?.next_cursor ?? null);
const loading = ref(false);

// A full reload of the prop (e.g. after a navigation back) starts over.
watch(() => props.events, (events) => {
    items.value = [...(events?.data ?? [])];
    nextCursor.value = events?.next_cursor ?? null;
});

const fetchPage = (before = null) => window.axios.get(
    route('accounts.events', { account: props.account.username }),
    { params: before ? { before } : {} },
).then((response) => response.data);

const loadMore = async () => {
    if (!nextCursor.value || loading.value) {
        return;
    }
    loading.value = true;
    try {
        const page = await fetchPage(nextCursor.value);
        const seen = new Set(items.value.map((e) => e.id));
        items.value = [...items.value, ...page.data.filter((e) => !seen.has(e.id))];
        nextCursor.value = page.next_cursor;
    } finally {
        loading.value = false;
    }
};

// New milestones: fetch the first page again and prepend what isn't shown yet.
// A failed refresh keeps the current list; the next broadcast tries again.
const refreshHead = async () => {
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

        <ul v-if="items.length">
            <RecentEventRow v-for="event in items" :key="event.id" :event="event" :account="account"
                            @pin-changed="setPinned" />
        </ul>
        <p v-else class="py-6 text-center text-sm text-base-content/60">
            Nothing to report yet.
        </p>

        <div v-if="nextCursor" class="mt-3 flex justify-center">
            <SecondaryButton class="btn-sm" :disabled="loading" @click="loadMore">
                {{ loading ? 'Loading…' : 'Show older events' }}
            </SecondaryButton>
        </div>
    </Card>
</template>
