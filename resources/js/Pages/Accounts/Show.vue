<script setup>
import { computed, ref } from "vue";
import { Deferred } from "@inertiajs/vue3";
import AppLayout from '@/Layouts/AppLayout.vue';
import CollectionLog from "@/Components/Game/CollectionLog.vue";
import Loot from "@/Components/Game/Loot.vue";
import Quests from "@/Components/Game/Quests.vue";
import Inventory from "@/Components/Game/Inventory.vue";
import LootingBag from "@/Components/Game/LootingBag.vue";
import Freshness from "@/Components/Freshness.vue";
import Loader from "@/Components/Loader.vue";
import Search from "@/Pages/Accounts/Partials/Search.vue";
import Header from "@/Pages/Accounts/Partials/Header.vue";
import Equipment from "@/Pages/Accounts/Partials/Equipment.vue";
import Avatar from "@/Pages/Accounts/Partials/Avatar.vue";
import Summary from "@/Pages/Accounts/Partials/Summary.vue";
import Skills from "@/Pages/Accounts/Partials/Skills.vue";
import Bank from "@/Components/Game/Bank.vue";
import TabbedCard from "@/Components/TabbedCard.vue";

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
    collectionLog: {
        type: Object,
        default: null,
    },
    recentLoot: {
        type: Array,
        default: () => [],
    },
    freshness: {
        type: Object,
        default: () => ({ stale_after_minutes: 60 }),
    },
});

const activeTab = ref('inventory');

const inventoryTabs = [
    { key: 'inventory', label: 'Inventory' },
    { key: 'looting-bag', label: 'Looting bag' },
];

const staleAfter = computed(() => props.freshness.stale_after_minutes ?? 60);
</script>

<template>
    <AppLayout :title="account.username">
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="flex justify-end">
                    <Search />
                </div>

                <div class="mt-4 pack-bg-card border pack-accent-border rounded p-6 lg:p-8">
                    <div class="grid grid-cols-3 gap-6">
                        <div class="col-span-1">
                            <Header :account="account" />

                            <div class="flex flex-col items-center">
                                <div class="my-4 grid grid-cols-2 gap-4">
                                    <Avatar :account="account" />
                                    <Equipment :account="account" />
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
                        </div>

                        <div class="col-span-2">
                            <div class="mx-auto mt-6 grid grid-cols-3 gap-2">
                                <div class="col-span-2">
                                    <h3 class="header-chatbox-sword">Skills</h3>
                                    <Skills :account="account" />
                                </div>

                                <div class="col-span-1">
                                    <div class="flex items-baseline justify-between">
                                        <h3 class="header-chatbox-sword">Quests</h3>
                                        <Freshness :updated-at="freshness.quests" :stale-after-minutes="staleAfter" />
                                    </div>
                                    <div class="mt-4 bg-base-200 border border-base-300 rounded resource-pack-dialog p-2">
                                        <Quests :quests="quests" />
                                    </div>
                                </div>
                            </div>

                            <h3 class="mt-4 header-chatbox-sword">Collection Log</h3>
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
                                <h3 class="header-chatbox-sword">Bank</h3>
                                <Freshness :updated-at="freshness.bank" :stale-after-minutes="staleAfter" />
                            </div>
                            <div class="mt-4">
                                <Bank :bank="bank" />
                            </div>

                            <div class="mt-4 flex items-baseline justify-between">
                                <h3 class="header-chatbox-sword">Recent Loot</h3>
                                <Freshness :updated-at="freshness.loot" :stale-after-minutes="staleAfter" />
                            </div>
                            <div class="mt-4">
                                <Loot :entries="recentLoot" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
