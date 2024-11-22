<script setup>
import {ref, watch, onMounted} from "vue";
import debounce from 'lodash/debounce';
import * as THREE from 'three';
import {OBJLoader} from 'three/examples/jsm/loaders/OBJLoader';
import {MTLLoader} from 'three/examples/jsm/loaders/MTLLoader';
import {OrbitControls} from 'three/examples/jsm/controls/OrbitControls';
import AppLayout from '@/Layouts/AppLayout.vue';
import {Link, useForm, usePage} from '@inertiajs/vue3';
import CollectionLog from "@/Components/CollectionLog.vue";
import TextInput from "@/Components/TextInput.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import InputLabel from "@/Components/InputLabel.vue";
import dayjs from "dayjs";
import InputError from "@/Components/InputError.vue";
import Bank from "@/Components/RuneManager/Bank.vue";
import Quests from "@/Components/RuneManager/Quests.vue";
import Inventory from "@/Components/RuneManager/Inventory.vue";
import LootingBag from "@/Components/RuneManager/LootingBag.vue";
import Search from "@/Pages/Accounts/Components/Search.vue";
import Header from "@/Pages/Accounts/Components/Header.vue";
import Equipment from "@/Pages/Accounts/Components/Equipment.vue";
import Avatar from "@/Pages/Accounts/Components/Avatar.vue";
import Summary from "@/Pages/Accounts/Components/Summary.vue";

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
                    <Search></Search>
                </div>

                <div class="mt-4 bg-base-100 border border-base-300 rounded p-6 lg:p-8">
                    <div class="grid grid-cols-3 gap-6">
                        <div class="col-span-1">
                            <Header :accountProp="account"></Header>

                            <div class="flex flex-col items-center">
                                <div class="my-4 grid grid-cols-2 gap-4">
                                    <Avatar :accountProp="account"/>

                                    <Equipment :accountProp="account"/>
                                </div>

                                <Summary :accountProp="account"/>
                            </div>

                            <!-- Vertical Inventory and Looting Bag tabs -->
                            <div class="tabs tabs-lifted" role="tablist">
                                <a :class="{ 'tab-active !bg-base-200': activeTab === 'inventory' }"
                                   class="tab"
                                   role="tab"
                                   @click="activeTab = 'inventory'">
                                    Inventory
                                </a>
                                <a :class="{ 'tab-active !bg-base-200': activeTab === 'lootingBag' }"
                                   class="tab"
                                   role="tab"
                                   @click="activeTab = 'lootingBag'">
                                    Looting bag
                                </a>
                            </div>

                            <!-- Inventory or Looting Bag tab content -->
                            <div class="flex bg-base-200 border-x border-b border-base-300 rounded-b">
                                <div v-show="activeTab === 'inventory'">
                                    <Inventory :accountProp="account"/>
                                </div>
                                <div v-show="activeTab === 'lootingBag'">
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

                                    <ul class="mt-4 grid grid-cols-6 gap-1">
                                        <li>
                                            <Link :href="route('accounts.index')"
                                                  class="flex items-center justify-center gap-2 rounded-lg border p-1 shadow bg-beige-300 border-beige-700 dark:border-gray-700 dark:bg-gray-800"
                                                  data-tooltip-placement="bottom"
                                                  data-tooltip-target="total-tooltip-bottom"
                                                  type="button">
                                                <img class="h-6 w-6 object-contain"
                                                     src="/images/skill/total.webp"/>
                                                <span class="text-xs font-semibold capitalize">
                                                    {{ account.level }}
                                                </span>
                                            </Link>

                                            <div id="total-tooltip-bottom"
                                                 class="invisible absolute z-10 inline-block rounded-lg bg-gray-900 px-3 py-2 text-sm font-medium text-white opacity-0 shadow-sm tooltip dark:bg-gray-700"
                                                 role="tooltip">
                                                <p>
                                                    Total level
                                                </p>
                                                <p>
                                                    {{ account.xp.toLocaleString('en-US') }}
                                                </p>
                                                <div class="tooltip-arrow" data-popper-arrow></div>
                                            </div>
                                        </li>
                                        <li v-for="skill in account.skills" :key="skill.name">
                                            <Link :data-tooltip-target="`${skill.slug}-tooltip-bottom`"
                                                  :href="route('hiscores.skills.index', skill.slug)"
                                                  class="flex items-center justify-center gap-2 rounded-lg border p-1 shadow bg-beige-300 border-beige-700 dark:border-gray-700 dark:bg-gray-800"
                                                  data-tooltip-placement="bottom"
                                                  type="button">
                                                <img :src="`/images/skill/${skill.slug}.webp`"
                                                     class="h-6 w-6 object-contain"/>
                                                <span class="text-xs font-semibold capitalize">
                                                    {{ skill.level }}
                                                </span>
                                            </Link>

                                            <div :id="`${skill.slug}-tooltip-bottom`"
                                                 class="invisible absolute z-10 inline-block rounded-lg bg-gray-900 px-3 py-2 text-sm font-medium text-white opacity-0 shadow-sm tooltip dark:bg-gray-700"
                                                 role="tooltip">
                                                <p>
                                                    {{ skill.name }}
                                                </p>
                                                <p>
                                                    {{ skill.xp.toLocaleString('en-US') }}
                                                </p>
                                                <div class="tooltip-arrow" data-popper-arrow></div>
                                            </div>
                                        </li>
                                    </ul>
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
