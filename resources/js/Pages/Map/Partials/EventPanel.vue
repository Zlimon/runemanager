<script setup>
import { computed, ref } from "vue";
import { Link } from "@inertiajs/vue3";
import Card from "@/Components/Cards/Card.vue";
import Checkbox from "@/Components/Form/Checkbox.vue";
import Search from "@/Components/Form/Search.vue";
import SelectInput from "@/Components/Form/SelectInput.vue";

/*
 * The Live Map's side panel: which accounts' events to plot, an optional event
 * type, and whether the live players stay on the map while browsing. The page
 * owns the selection (it syncs it to the URL and fetches the markers); this
 * panel only edits it and reports how the last load went.
 */
const props = defineProps({
    // [{ id, username, account_type, own, event_count }]; event_count is null for other members' accounts.
    accounts: { type: Array, default: () => [] },
    colours: { type: Object, default: () => ({}) }, // account id -> marker colour
    // [{ value, label }], "" first for every type.
    types: { type: Array, required: true },
    maxAccounts: { type: Number, default: 20 },
    loading: { type: Boolean, default: false },
    failed: { type: Boolean, default: false },
    eventCount: { type: Number, default: 0 },
    placeCount: { type: Number, default: 0 },
    truncated: { type: Boolean, default: false },
});

const selected = defineModel("selected", { type: Array, default: () => [] });
const type = defineModel("type", { type: String, default: "" });
const showLive = defineModel("showLive", { type: Boolean, default: true });


// A search box only earns its space once the list no longer fits at a glance.
const SEARCH_FROM = 8;

const query = ref("");

const filtered = computed(() => {
    const needle = query.value.trim().toLowerCase();
    return needle
        ? props.accounts.filter((account) => account.username.toLowerCase().includes(needle))
        : props.accounts;
});

const groups = computed(() => [
    { label: "Your accounts", accounts: filtered.value.filter((account) => account.own) },
    { label: "Other members", accounts: filtered.value.filter((account) => !account.own) },
].filter((group) => group.accounts.length));

const isSelected = (id) => selected.value.includes(id);

const atLimit = computed(() => selected.value.length >= props.maxAccounts);

const toggle = (id, on) => {
    selected.value = on
        ? [...selected.value, id]
        : selected.value.filter((selectedId) => selectedId !== id);
};
</script>

<template>
    <Card padding="p-4" class="flex flex-col gap-3">
        <div class="flex items-baseline justify-between">
            <h3>Events</h3>
            <button v-if="selected.length" type="button" class="link link-hover text-xs text-base-content/60"
                    @click="selected = []">
                Clear
            </button>
        </div>

        <label class="flex cursor-pointer items-center justify-between gap-2 text-sm">
            <span>Live players</span>
            <input v-model="showLive" type="checkbox" class="toggle toggle-sm toggle-primary">
        </label>

        <SelectInput v-model="type" class="select-sm" aria-label="Event type">
            <option v-for="option in types" :key="option.value" :value="option.value">{{ option.label }}</option>
        </SelectInput>

        <Search v-if="accounts.length >= SEARCH_FROM" v-model="query" class="input-sm" placeholder="Find an account"
                aria-label="Find an account" />

        <div v-if="accounts.length" class="max-h-[45vh] overflow-y-auto pr-1">
            <div v-for="group in groups" :key="group.label" class="mb-2 last:mb-0">
                <p class="mb-1 text-xs font-semibold uppercase tracking-wide text-base-content/60">{{ group.label }}</p>
                <label v-for="account in group.accounts" :key="account.id"
                       class="flex cursor-pointer items-center gap-2 rounded px-1 py-1 text-sm hover:bg-base-300/40"
                       :class="{ 'cursor-not-allowed opacity-50': atLimit && !isSelected(account.id) }">
                    <Checkbox class="checkbox-sm" :checked="isSelected(account.id)"
                              :disabled="atLimit && !isSelected(account.id)"
                              @update:checked="toggle(account.id, $event)" />
                    <span class="inline-block h-2.5 w-2.5 shrink-0 rounded-full border border-black/60"
                          :style="{ background: colours[account.id] }" aria-hidden="true"></span>
                    <span class="min-w-0 flex-1 truncate">{{ account.username }}</span>
                    <span v-if="account.event_count != null" class="shrink-0 text-xs text-base-content/60">{{ account.event_count }}</span>
                </label>
            </div>
            <p v-if="!groups.length" class="py-2 text-center text-sm text-base-content/60">No account matches.</p>
        </div>
        <p v-else class="text-sm text-base-content/60">
            No events with a location to show yet. Members choose to share theirs under
            <Link :href="route('privacy.index')" class="link link-hover">Settings → Privacy</Link>.
        </p>

        <p v-if="atLimit" class="text-xs text-base-content/60">Up to {{ maxAccounts }} accounts at once.</p>

        <div class="text-sm text-base-content/70" aria-live="polite">
            <p v-if="!selected.length && accounts.length">Pick an account to see where its events happened.</p>
            <p v-else-if="loading">Loading events…</p>
            <p v-else-if="failed" class="text-error">The events could not be loaded. Try again in a moment.</p>
            <p v-else-if="selected.length && !eventCount">No events with a location for this selection.</p>
            <template v-else-if="selected.length">
                <p>{{ eventCount }} {{ eventCount === 1 ? 'event' : 'events' }} at {{ placeCount }} {{ placeCount === 1 ? 'place' : 'places' }}.</p>
                <p v-if="truncated" class="text-xs text-base-content/60">Showing the newest {{ eventCount }} events.</p>
            </template>
        </div>

        <Link v-if="accounts.some((account) => account.own)" :href="route('privacy.index')"
              class="link link-hover text-xs text-base-content/60">
            Who can see my events?
        </Link>
    </Card>
</template>
