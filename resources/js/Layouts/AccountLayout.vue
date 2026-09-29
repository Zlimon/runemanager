<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from "vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import Card from "@/Components/Cards/Card.vue";
import Search from "@/Pages/Accounts/Partials/Search.vue";
import Header from "@/Pages/Accounts/Partials/Header.vue";
import Minimap from "@/Pages/Accounts/Partials/Minimap.vue";
import StatusOrbs from "@/Pages/Accounts/Partials/StatusOrbs.vue";

/*
 * Shared chrome for the account tabs (Log, Gains, Items, Collection): the
 * header card with live status orbs and minimap, the tab row, then the tab's
 * own content. Each tab is its own route and page, so the tab row is plain
 * Inertia navigation.
 *
 * The layout owns the account's private channel. Vitals and status arrive
 * with their values inline; DataUpdated names a data set, which the page maps
 * to its own props through `reloadProps` (a set the tab doesn't show is ignored).
 */
const props = defineProps({
    account: { type: Object, required: true },
    position: { type: Object, default: null },
    vitals: { type: Object, default: null },
    location: { type: String, default: null },
    // { [DataUpdated type]: [prop names] } for the current tab.
    reloadProps: { type: Object, default: () => ({}) },
});

const page = usePage();

const liveVitals = ref(props.vitals);
const liveActivity = ref(props.account.activity);
const liveActivityIcon = ref(props.account.activity_icon);
const liveLocation = ref(props.location);
const liveWorld = ref(props.account.world);

// A partial reload of `account` (equipment push) brings fresh header values too.
watch(() => props.account, (account) => {
    liveActivity.value = account.activity;
    liveActivityIcon.value = account.activity_icon;
    liveWorld.value = account.world;
});

const tabs = computed(() => [
    { label: "Adventurer's Log", route: 'accounts.show' },
    ...(page.props.wom_enabled ? [{ label: 'Gains', route: 'accounts.gains' }] : []),
    { label: 'Items', route: 'accounts.items' },
    { label: 'Collection', route: 'accounts.collection' },
]);

const pendingProps = new Set();
let reloadTimer = null;

// Inventory/equipment pushes can arrive in bursts; coalesce them into one reload.
const scheduleReload = (type) => {
    const only = props.reloadProps[type];
    if (!only) {
        return;
    }
    [...only, 'freshness'].forEach((prop) => pendingProps.add(prop));
    if (reloadTimer) {
        return;
    }
    reloadTimer = window.setTimeout(() => {
        router.reload({ only: [...pendingProps], preserveScroll: true, preserveState: true });
        pendingProps.clear();
        reloadTimer = null;
    }, 600);
};

const channel = `account.${props.account.id}`;

onMounted(() => {
    window.Echo.private(channel)
        .listen(".DataUpdated", (event) => scheduleReload(event.type))
        .listen(".VitalsUpdated", (event) => { liveVitals.value = event; })
        .listen(".StatusUpdated", (event) => {
            liveActivity.value = event.activity;
            liveActivityIcon.value = event.activity_icon;
            liveLocation.value = event.location;
            liveWorld.value = event.world;
        });
});

onBeforeUnmount(() => {
    if (reloadTimer) {
        window.clearTimeout(reloadTimer);
    }
    window.Echo.leave(channel);
});
</script>

<template>
    <AppLayout :title="account.username">
        <div class="py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex justify-end">
                    <Search />
                </div>

                <Card class="mt-4" padding="p-6 lg:p-8">
                    <div class="flex items-start justify-between gap-4">
                        <Header :account="account" :activity="liveActivity" :activity-icon="liveActivityIcon" class="flex-1" />
                        <div class="hidden shrink-0 items-start gap-3 md:flex">
                            <StatusOrbs :vitals="liveVitals" />
                            <div class="flex flex-col items-center gap-1">
                                <Minimap :username="account.username" :position="position"
                                         :href="position ? route('map.index', { focus: account.username }) : null" />
                                <p v-if="liveLocation"
                                   class="max-w-[12rem] truncate text-xs text-base-content/70" :title="liveLocation">
                                    <span v-if="!account.online" class="text-base-content/50">Last seen: </span>{{ liveLocation }}<span v-if="account.online && liveWorld" class="text-base-content/50"> · W{{ liveWorld }}</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </Card>

                <nav class="tabs tabs-border mt-4 overflow-x-auto" aria-label="Account sections">
                    <Link v-for="tab in tabs" :key="tab.route"
                          :href="route(tab.route, { account: account.username })"
                          class="tab resource-pack-tab whitespace-nowrap"
                          :class="{ 'tab-active': route().current(tab.route) }"
                          :aria-current="route().current(tab.route) ? 'page' : undefined"
                          preserve-scroll>
                        {{ tab.label }}
                    </Link>
                </nav>

                <div class="mt-4">
                    <slot />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
