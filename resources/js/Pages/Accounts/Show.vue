<script setup>
import {ref} from "vue";
import AppLayout from '@/Layouts/AppLayout.vue';
import CollectionLog from "@/Components/CollectionLog.vue";
import Quests from "@/Components/RuneManager/Quests.vue";
import Inventory from "@/Components/RuneManager/Inventory.vue";
import LootingBag from "@/Components/RuneManager/LootingBag.vue";
import Search from "@/Pages/Accounts/Components/Search.vue";
import Header from "@/Pages/Accounts/Components/Header.vue";
import Equipment from "@/Pages/Accounts/Components/Equipment.vue";
import Avatar from "@/Pages/Accounts/Components/Avatar.vue";
import Summary from "@/Pages/Accounts/Components/Summary.vue";
import Skills from "@/Pages/Accounts/Components/Skills.vue";
import Bank from "@/Components/RuneManager/Bank.vue";

const props = defineProps({
    accountProp: Object,
});

let account = ref(props.accountProp);
let activeTab = ref('inventory');
</script>

<template>
    <AppLayout title="Account">
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="flex justify-end">
                    <Search/>
                </div>

                <div class="mt-4 bg-base-100 border border-base-300 rounded p-6 lg:p-8">
                    <div class="grid grid-cols-3 gap-6">
                        <div class="col-span-1">
                            <Header :accountProp="account"/>

                            <div class="flex flex-col items-center">
                                <div class="my-4 grid grid-cols-2 gap-4">
                                    <Avatar :accountProp="account"/>

                                    <Equipment :accountProp="account"/>
                                </div>

                                <Summary :accountProp="account"/>
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

                            <!-- Inventory or Looting Bag tab content -->
                            <div class="flex bg-base-200 border-x border-b border-base-300 rounded-b">
                                <div v-show="activeTab === 'inventory'">
                                    <Inventory :accountProp="account"/>
                                </div>
                                <div v-show="activeTab === 'looting-bag'">
                                    <LootingBag :accountProp="account"/>
                                </div>
                            </div>
                        </div>

                        <div class="col-span-2">
                            <div class="mx-auto mt-6 grid grid-cols-3 gap-2">
                                <div class="col-span-2">
                                    <h3 class="header-chatbox-sword">
                                        Skills
                                    </h3>

                                    <Skills :accountProp="account"/>
                                </div>

                                <div class="col-span-1">
                                    <h3 class="header-chatbox-sword">
                                        Quests
                                    </h3>

                                    <div class="mt-4 bg-base-200 border border-base-300 rounded">
                                        <Quests :accountProp="account"/>
                                    </div>
                                </div>
                            </div>

                            <h3 class="mt-4 header-chatbox-sword">
                                Collection Log
                            </h3>

                            <div class="mt-4">
                                <CollectionLog :accountProp="account"/>
                            </div>

                            <h3 class="mt-4 header-chatbox-sword">
                                Bank
                            </h3>

                            <div class="mt-4">
                                <Bank :accountProp="account"/>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
