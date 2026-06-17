<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from "vue";
import { Deferred, router } from "@inertiajs/vue3";
import AppLayout from '@/Layouts/AppLayout.vue';
import CollectionLog from "@/Components/Game/CollectionLog.vue";
import Loot from "@/Components/Game/Loot.vue";
import Quests from "@/Components/Game/Quests.vue";
import Diaries from "@/Components/Game/Diaries.vue";
import CombatAchievements from "@/Components/Game/CombatAchievements.vue";
import Inventory from "@/Components/Game/Inventory.vue";
import LootingBag from "@/Components/Game/LootingBag.vue";
import AccountFeed from "@/Components/Game/AccountFeed.vue";
import AchievementGallery from "@/Components/Game/AchievementGallery.vue";
import Freshness from "@/Components/Freshness.vue";
import Loader from "@/Components/Loader.vue";
import Search from "@/Pages/Accounts/Partials/Search.vue";
import Header from "@/Pages/Accounts/Partials/Header.vue";
import Equipment from "@/Pages/Accounts/Partials/Equipment.vue";
import Avatar from "@/Pages/Accounts/Partials/Avatar.vue";
import Summary from "@/Pages/Accounts/Partials/Summary.vue";
import Skills from "@/Pages/Accounts/Partials/Skills.vue";
import Bank from "@/Components/Game/Bank.vue";
import Minimap from "@/Components/Game/Minimap.vue";
import StatusOrbs from "@/Components/Game/StatusOrbs.vue";
import TabbedCard from "@/Components/TabbedCard.vue";
import Card from "@/Components/Card.vue";

const props = defineProps({
    account: {
        type: Object,
        required: true,
    },
    inventory: {
        type: Object,
        default: null,
    },
    bank: {
        type: Object,
        default: null,
    },
    lootingBag: {
        type: Object,
        default: null,
    },
    quests: {
        type: Object,
        default: null,
    },
    diaries: {
        type: Object,
        default: () => ({}),
    },
    combatAchievements: {
        type: Object,
        default: null,
    },
    avatar: {
        type: Object,
        default: null,
    },
    collectionLog: {
        type: Object,
        default: null,
    },
    recentLoot: {
        type: Array,
        default: () => [],
    },
    feed: {
        type: Array,
        default: () => [],
    },
    pinnedFeed: {
        type: Array,
        default: () => [],
    },
    position: {
        type: Object,
        default: null,
    },
    location: {
        type: String,
        default: null,
    },
    vitals: {
        type: Object,
        default: null,
    },
    freshness: {
        type: Object,
        default: () => ({ stale_after_minutes: 60 }),
    },
});

// Status-orb values update often, so they arrive as their own broadcast (with
// the values inline) rather than via a prop reload.
const liveVitals = ref(props.vitals);

// Current in-game activity + area — pushed live on the status broadcast.
const liveActivity = ref(props.account.activity);
const liveLocation = ref(props.location);

const activeTab = ref('inventory');

const inventoryTabs = [
    { key: 'inventory', label: 'Inventory' },
    { key: 'looting-bag', label: 'Looting bag' },
];

const activeJournalTab = ref('quests');
const journalTabs = [
    { key: 'quests', label: 'Quests' },
    { key: 'diaries', label: 'Diaries' },
    { key: 'combat-achievements', label: 'Combat' },
];

const staleAfter = computed(() => props.freshness.stale_after_minutes ?? 60);

// When the avatar includes an opponent, give it the whole column and tuck the
// equipment away (toggleable above the model) so the fight has room to breathe.
const hasNpc = computed(() => !!props.avatar?.npc_obj_url);
const showEquipment = ref(true);
watch(hasNpc, (npc) => { showEquipment.value = !npc; }, { immediate: true });

// Live updates: the backend broadcasts a DataUpdated event on the account's
// channel whenever a plugin push changes one of these data sets. We reload only
// the affected Inertia prop (plus freshness) so the open profile updates itself.
const RELOAD_PROPS = {
    inventory: ['inventory', 'freshness'],
    bank: ['bank', 'freshness'],
    equipment: ['account', 'freshness'],
    looting_bag: ['lootingBag', 'freshness'],
    loot: ['recentLoot', 'freshness'],
    avatar: ['avatar', 'freshness'],
    diaries: ['diaries', 'freshness'],
    combat_achievements: ['combatAchievements', 'freshness'],
};

const pendingProps = new Set();
let reloadTimer = null;

// Inventory/equipment pushes can arrive in bursts; coalesce them into one reload.
const scheduleReload = (type) => {
    const only = RELOAD_PROPS[type];
    if (!only) {
        return;
    }
    only.forEach((prop) => pendingProps.add(prop));
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
            liveLocation.value = event.location;
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
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="flex justify-end">
                    <Search />
                </div>

                <Card class="mt-4" padding="p-6 lg:p-8">
                    <div class="flex items-start justify-between gap-4">
                        <Header :account="account" :activity="liveActivity" class="flex-1" />
                        <div class="hidden shrink-0 items-start gap-3 md:flex">
                            <StatusOrbs :vitals="liveVitals" />
                            <div class="flex flex-col items-center gap-1">
                                <Minimap :username="account.username" :position="position"
                                         :href="position ? route('map.index', { focus: account.username }) : null" />
                                <p v-if="account.online && liveLocation"
                                   class="max-w-[12rem] truncate text-xs text-base-content/70" :title="liveLocation">
                                    {{ liveLocation }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Owner-curated achievement gallery -->
                    <div class="mt-4">
                        <AchievementGallery :account="account" :events="pinnedFeed" />
                    </div>

                    <div class="grid grid-cols-3 gap-6">
                        <div class="col-span-1">
                            <div class="flex flex-col items-center">
                                <div class="my-4 w-full">
                                    <div class="mb-2 flex justify-end">
                                        <button type="button"
                                                class="text-xs text-base-content/60 hover:text-base-content"
                                                @click="showEquipment = !showEquipment">
                                            {{ showEquipment ? 'Hide equipment' : 'Show equipment' }}
                                        </button>
                                    </div>

                                    <!-- Side by side when there's no opponent and equipment is shown. -->
                                    <div v-if="!hasNpc && showEquipment" class="grid grid-cols-2 gap-4">
                                        <Avatar :avatar="avatar" />
                                        <Equipment :account="account" />
                                    </div>

                                    <!-- Otherwise the model fills the column; equipment stacks above. -->
                                    <div v-else>
                                        <Equipment v-if="showEquipment" :account="account" class="mb-3" />
                                        <Avatar :avatar="avatar" :expanded="true" />
                                    </div>
                                </div>

                                <Summary :account="account" :freshness="freshness" />
                            </div>

                            <!-- Vertical Inventory and Looting Bag tabs -->
                            <TabbedCard :tabs="inventoryTabs" v-model="activeTab" class="mt-4">
                                <template #inventory>
                                    <div class="flex justify-end">
                                        <Freshness :updated-at="freshness.inventory" :stale-after-minutes="staleAfter" />
                                    </div>
                                    <Inventory :inventory="inventory" />
                                </template>
                                <template #looting-bag>
                                    <div class="flex justify-end">
                                        <Freshness :updated-at="freshness.looting_bag" :stale-after-minutes="staleAfter" />
                                    </div>
                                    <LootingBag :looting-bag="lootingBag" />
                                </template>
                            </TabbedCard>

                            <!-- This account's live feed -->
                            <div class="mt-4">
                                <AccountFeed :account="account" :events="feed" />
                            </div>
                        </div>

                        <div class="col-span-2">
                            <div class="mx-auto mt-6 grid grid-cols-3 gap-2">
                                <div class="col-span-2">
                                    <h3>Skills</h3>
                                    <Skills :account="account" class="mt-4" />
                                </div>

                                <div class="col-span-1">
                                    <h3>Journal</h3>
                                    <TabbedCard :tabs="journalTabs" v-model="activeJournalTab" class="mt-4">
                                        <template #quests>
                                            <div class="flex justify-end">
                                                <Freshness :updated-at="freshness.quests" :stale-after-minutes="staleAfter" />
                                            </div>
                                            <Quests :quests="quests" />
                                        </template>
                                        <template #diaries>
                                            <div class="flex justify-end">
                                                <Freshness :updated-at="freshness.diaries" :stale-after-minutes="staleAfter" />
                                            </div>
                                            <Diaries :diaries="diaries" />
                                        </template>
                                        <template #combat-achievements>
                                            <div class="flex justify-end">
                                                <Freshness :updated-at="freshness.combat_achievements" :stale-after-minutes="staleAfter" />
                                            </div>
                                            <CombatAchievements :combat-achievements="combatAchievements" />
                                        </template>
                                    </TabbedCard>
                                </div>
                            </div>

                            <h3 class="mt-4">Collection Log</h3>
                            <div class="mt-4">
                                <Deferred data="collectionLog">
                                    <template #fallback>
                                        <div class="flex h-96 items-center justify-center">
                                            <Loader :component="true" :loading="true" />
                                        </div>
                                    </template>

                                    <CollectionLog :collection-log="collectionLog" />
                                </Deferred>
                            </div>

                            <div class="mt-4 flex items-baseline justify-between">
                                <h3>Bank</h3>
                                <Freshness :updated-at="freshness.bank" :stale-after-minutes="staleAfter" />
                            </div>
                            <div class="mt-4">
                                <Bank :bank="bank" />
                            </div>

                            <div class="mt-4 flex items-baseline justify-between">
                                <h3>Recent Loot</h3>
                                <Freshness :updated-at="freshness.loot" :stale-after-minutes="staleAfter" />
                            </div>
                            <div class="mt-4">
                                <Loot :entries="recentLoot" />
                            </div>
                        </div>
                    </div>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
