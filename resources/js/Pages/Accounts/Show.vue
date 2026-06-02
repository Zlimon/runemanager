<script setup>
import { ref } from "vue";
import { Deferred } from "@inertiajs/vue3";
import AppLayout from '@/Layouts/AppLayout.vue';
import CollectionLog from "@/Components/Game/CollectionLog.vue";
import Quests from "@/Components/Game/Quests.vue";
import Inventory from "@/Components/Game/Inventory.vue";
import LootingBag from "@/Components/Game/LootingBag.vue";
import Loader from "@/Components/Loader.vue";
import Search from "@/Pages/Accounts/Partials/Search.vue";
import Header from "@/Pages/Accounts/Partials/Header.vue";
import Equipment from "@/Pages/Accounts/Partials/Equipment.vue";
import Avatar from "@/Pages/Accounts/Partials/Avatar.vue";
import Summary from "@/Pages/Accounts/Partials/Summary.vue";
import Skills from "@/Pages/Accounts/Partials/Skills.vue";
import Bank from "@/Components/Game/Bank.vue";

defineProps({
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
});

const activeTab = ref('inventory');
</script>

<template>
    <AppLayout :title="account.username">
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="flex justify-end">
                    <Search />
                </div>

                <div class="mt-4 bg-base-100 border border-base-300 rounded p-6 lg:p-8">
                    <div class="grid grid-cols-3 gap-6">
                        <div class="col-span-1">
                            <Header :account="account" />

                            <div class="flex flex-col items-center">
                                <div class="my-4 grid grid-cols-2 gap-4">
                                    <Avatar :account="account" />
                                    <Equipment :account="account" />
                                </div>

                                <Summary :account="account" />
                            </div>

                            <!-- Vertical Inventory and Looting Bag tabs -->
                            <div class="mt-4 tabs tabs-lifted" role="tablist">
                                <a :class="{ 'tab-active !bg-base-200': activeTab === 'inventory' }"
                                   class="tab"
                                   role="tab"
                                   @click="activeTab = 'inventory'">
                                    Inventory
                                </a>
                                <a :class="{ 'tab-active !bg-base-200': activeTab === 'looting-bag' }"
                                   class="tab"
                                   role="tab"
                                   @click="activeTab = 'looting-bag'">
                                    Looting bag
                                </a>
                            </div>

                            <div class="flex bg-base-200 border-x border-b border-base-300 rounded-b">
                                <div v-show="activeTab === 'inventory'">
                                    <Inventory :inventory="inventory" />
                                </div>
                                <div v-show="activeTab === 'looting-bag'">
                                    <LootingBag :looting-bag="lootingBag" />
                                </div>
                            </div>
                        </div>

                        <div class="col-span-2">
                            <div class="mx-auto mt-6 grid grid-cols-3 gap-2">
                                <div class="col-span-2">
                                    <h3 class="header-chatbox-sword">Skills</h3>
                                    <Skills :account="account" />
                                </div>

                                <div class="col-span-1">
                                    <h3 class="header-chatbox-sword">Quests</h3>
                                    <div class="mt-4 bg-base-200 border border-base-300 rounded">
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

                            <h3 class="mt-4 header-chatbox-sword">Bank</h3>
                            <div class="mt-4">
                                <Bank :bank="bank" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
